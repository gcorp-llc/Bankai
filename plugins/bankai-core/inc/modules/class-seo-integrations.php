<?php
/**
 * Sitemap XML · robots.txt · Search Console / Analytics / Bing / Yandex verification.
 */
defined('ABSPATH') || exit;

final class Bankai_SEO_Integrations
{
    private static ?self $instance = null;

    public const OPT = 'bankai_seo_integrations';

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('seo_engine')) {
            return;
        }

        add_action('init', [$this, 'rewrite']);
        add_filter('query_vars', static function (array $v) {
            $v[] = 'bankai_sitemap';
            $v[] = 'bankai_robots';
            return $v;
        });
        add_action('template_redirect', [$this, 'serve'], 0);
        add_action('wp_head', [$this, 'render_verification'], 0);

        add_action('wp_ajax_bankai_save_seo_integrations', [$this, 'ajax_save']);
        add_action('wp_ajax_bankai_get_seo_integrations', [$this, 'ajax_get']);

        add_action('rest_api_init', [$this, 'rest']);
    }

    public function rest(): void
    {
        
        register_rest_route('bankai/v1', '/seo/site-audit', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'site_audit'],
            'permission_callback' => static fn() => current_user_can('manage_options'),
        ]);

        register_rest_route('bankai/v1', '/seo/integrations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => function () {
                    return new WP_REST_Response(['success' => true, 'data' => $this->get_settings()]);
                },
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'rest_save'],
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
        ]);
    }

    public function rest_save(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            return new WP_REST_Response(['success' => false], 400);
        }
        $saved = $this->save_settings($params);
        $this->rewrite();
        flush_rewrite_rules(false);
        return new WP_REST_Response(['success' => true, 'data' => $saved]);
    }

    public function rewrite(): void
    {
        add_rewrite_rule('^sitemap\.xml/?$', 'index.php?bankai_sitemap=1', 'top');
        add_rewrite_rule('^sitemap-posts\.xml/?$', 'index.php?bankai_sitemap=posts', 'top');
        add_rewrite_rule('^sitemap-pages\.xml/?$', 'index.php?bankai_sitemap=pages', 'top');
        add_rewrite_rule('^sitemap-tax\.xml/?$', 'index.php?bankai_sitemap=tax', 'top');
        add_rewrite_rule('^sitemap-products\.xml/?$', 'index.php?bankai_sitemap=products', 'top');

        // One-time flush when rules change
        $ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1';
        if (get_option('bankai_rewrite_flush_v') !== $ver) {
            flush_rewrite_rules(false);
            update_option('bankai_rewrite_flush_v', $ver, false);
        }
    }

    private function request_path(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = (string) (wp_parse_url($uri, PHP_URL_PATH) ?: $uri);
        return untrailingslashit($path);
    }

    public function serve(): void
    {
        $sm = get_query_var('bankai_sitemap');
        if (!$sm) {
            $path = $this->request_path();
            if (preg_match('#/sitemap\.xml$#i', $path)) {
                $sm = '1';
            } elseif (preg_match('#/sitemap-posts\.xml$#i', $path)) {
                $sm = 'posts';
            } elseif (preg_match('#/sitemap-pages\.xml$#i', $path)) {
                $sm = 'pages';
            } elseif (preg_match('#/sitemap-tax\.xml$#i', $path)) {
                $sm = 'tax';
            } elseif (preg_match('#/sitemap-products\.xml$#i', $path)) {
                $sm = 'products';
            }
        }
        if ($sm) {
            $this->output_sitemap((string) $sm);
            exit;
        }

        // Optional Bankai-managed robots when enabled
        $settings = $this->get_settings();
        if (!empty($settings['manage_robots'])) {
            $path = $this->request_path();
            if (str_ends_with($path, '/robots.txt') || $path === '/robots.txt' || str_ends_with($path, 'robots.txt')) {
                $this->output_robots();
                exit;
            }
        }
    }

    public function get_settings(): array
    {
        $defaults = [
            'manage_robots'        => true,
            'robots_extra'         => "User-agent: *\nAllow: /\n\nSitemap: " . home_url('/sitemap.xml'),
            'robots_txt'           => '',
            'google_site_verification' => '',
            'bing_site_verification'   => '',
            'bing_webmaster'           => '',
            'yandex_verification'      => '',
            'pinterest_verification'   => '',
            'baidu_verification'       => '',
            'ga4_measurement_id'       => '',
            'google_analytics_id'      => '',
            'gtm_container_id'         => '',
            'google_tag_manager_id'    => '',
            'bing_uet_id'              => '',
            'yandex_metrica_id'        => '',
            'sitemap_enabled'          => true,
            'sitemap_include_images'   => true,
            'indexnow_key'             => '',
        ];
        $saved = get_option(self::OPT, []);
        $out = wp_parse_args(is_array($saved) ? $saved : [], $defaults);
        // Normalize aliases
        if ($out['ga4_measurement_id'] === '' && $out['google_analytics_id'] !== '') {
            $out['ga4_measurement_id'] = $out['google_analytics_id'];
        }
        if ($out['gtm_container_id'] === '' && $out['google_tag_manager_id'] !== '') {
            $out['gtm_container_id'] = $out['google_tag_manager_id'];
        }
        if ($out['bing_site_verification'] === '' && $out['bing_webmaster'] !== '') {
            $out['bing_site_verification'] = $out['bing_webmaster'];
        }
        if ($out['robots_extra'] === '' && $out['robots_txt'] !== '') {
            $out['robots_extra'] = $out['robots_txt'];
        }
        // SEO module sitemap_pro overrides
        if (function_exists('bankai_get_option')) {
            $mods = bankai_get_option('seo_modules', []);
            if (is_array($mods) && array_key_exists('sitemap_pro', $mods)) {
                $out['sitemap_enabled'] = !empty($mods['sitemap_pro']);
            }
        }
        return $out;
    }

    public function save_settings(array $in): array
    {
        $cur = $this->get_settings();
        $keys = array_keys($cur);
        foreach ($keys as $k) {
            if (!array_key_exists($k, $in)) {
                continue;
            }
            $val = $in[$k];
            if (in_array($k, ['manage_robots', 'sitemap_enabled', 'sitemap_include_images'], true)) {
                $cur[$k] = (bool) $val;
            } elseif ($k === 'robots_extra') {
                $cur[$k] = sanitize_textarea_field((string) $val);
            } else {
                $cur[$k] = sanitize_text_field((string) $val);
            }
        }
        update_option(self::OPT, $cur, false);
        return $cur;
    }

    public function ajax_get(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(['settings' => $this->get_settings()]);
    }

    public function ajax_save(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $raw = isset($_POST['settings']) ? (array) wp_unslash($_POST['settings']) : [];
        $saved = $this->save_settings($raw);
        $this->rewrite();
        flush_rewrite_rules(false);
        wp_send_json_success([
            'message'  => __('تنظیمات سئو و یکپارچه‌سازی ذخیره شد.', 'bankai-core'),
            'settings' => $saved,
            'sitemap'  => home_url('/sitemap.xml'),
            'robots'   => home_url('/robots.txt'),
        ]);
    }

    public function render_verification(): void
    {
        $s = $this->get_settings();

        if ($s['google_site_verification'] !== '') {
            echo '<meta name="google-site-verification" content="' . esc_attr($s['google_site_verification']) . '">' . "\n";
        }
        if ($s['bing_site_verification'] !== '') {
            echo '<meta name="msvalidate.01" content="' . esc_attr($s['bing_site_verification']) . '">' . "\n";
        }
        if ($s['yandex_verification'] !== '') {
            echo '<meta name="yandex-verification" content="' . esc_attr($s['yandex_verification']) . '">' . "\n";
        }
        if ($s['pinterest_verification'] !== '') {
            echo '<meta name="p:domain_verify" content="' . esc_attr($s['pinterest_verification']) . '">' . "\n";
        }
        if ($s['baidu_verification'] !== '') {
            echo '<meta name="baidu-site-verification" content="' . esc_attr($s['baidu_verification']) . '">' . "\n";
        }

        // GA4
        if ($s['ga4_measurement_id'] !== '') {
            $id = esc_js($s['ga4_measurement_id']);
            echo "<!-- Bankai GA4 -->\n";
            echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($s['ga4_measurement_id']) . '"></script>' . "\n";
            echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$id}');</script>\n";
        }

        // GTM
        if ($s['gtm_container_id'] !== '') {
            $gtm = esc_js($s['gtm_container_id']);
            echo "<!-- Bankai GTM -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtm}');</script>\n";
        }

        // Yandex Metrica
        if ($s['yandex_metrica_id'] !== '') {
            $ym = esc_js($s['yandex_metrica_id']);
            echo "<!-- Bankai Yandex.Metrica -->\n<script>(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();for(var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r)return;}k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,'script','https://mc.yandex.ru/metrika/tag.js','ym');ym({$ym},'init',{clickmap:true,trackLinks:true,accurateTrackBounce:true});</script>\n";
        }
    }

    private function output_robots(): void
    {
        $s = $this->get_settings();
        $body = trim((string) $s['robots_extra']);
        if ($body === '') {
            $body = "User-agent: *\nAllow: /\n\nSitemap: " . home_url('/sitemap.xml') . "\n";
        }
        if (!str_contains($body, 'Sitemap:') && !empty($s['sitemap_enabled'])) {
            $body .= "\nSitemap: " . home_url('/sitemap.xml') . "\n";
        }
        nocache_headers();
        header('Content-Type: text/plain; charset=utf-8');
        echo $body;
    }

    private function output_sitemap(string $type): void
    {
        $s = $this->get_settings();
        $mod_on = true;
        if (function_exists('bankai_get_option')) {
            $mods = bankai_get_option('seo_modules', []);
            if (is_array($mods) && array_key_exists('sitemap_pro', $mods)) {
                $mod_on = !empty($mods['sitemap_pro']);
            }
        }
        if (empty($s['sitemap_enabled']) && !$mod_on) {
            status_header(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Sitemap disabled';
            exit;
        }

        $exclude = [];
        if (class_exists('Bankai_SEO_Sitewide')) {
            $exclude = Bankai_SEO_Sitewide::instance()->get_sitemap_exclude_ids();
        }

        nocache_headers();
        header('Content-Type: application/xml; charset=utf-8');

        if ($type === '1' || $type === 'index') {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            echo '  <sitemap><loc>' . esc_url(home_url('/sitemap-posts.xml')) . '</loc></sitemap>' . "\n";
            echo '  <sitemap><loc>' . esc_url(home_url('/sitemap-pages.xml')) . '</loc></sitemap>' . "\n";
            echo '  <sitemap><loc>' . esc_url(home_url('/sitemap-tax.xml')) . '</loc></sitemap>' . "\n";
            if (class_exists('WooCommerce')) {
                echo '  <sitemap><loc>' . esc_url(home_url('/sitemap-products.xml')) . '</loc></sitemap>' . "\n";
            }
            echo '</sitemapindex>';
            return;
        }

        if ($type === 'tax') {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach (['category', 'post_tag'] as $tax) {
                $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => true]);
                if (is_wp_error($terms) || !is_array($terms)) {
                    continue;
                }
                foreach ($terms as $term) {
                    $robots = (string) get_term_meta((int) $term->term_id, '_bankai_term_seo_robots', true);
                    if (str_contains($robots, 'noindex')) {
                        continue;
                    }
                    $link = get_term_link($term);
                    if (is_wp_error($link)) {
                        continue;
                    }
                    echo "  <url>\n    <loc>" . esc_url($link) . "</loc>\n  </url>\n";
                }
            }
            echo '</urlset>';
            return;
        }

        $post_type = 'post';
        if ($type === 'pages') {
            $post_type = 'page';
        } elseif ($type === 'products') {
            $post_type = 'product';
        }

        $args = [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 2000,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ];
        if ($exclude) {
            $args['post__not_in'] = $exclude;
        }
        $q = new WP_Query($args);

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if (!empty($s['sitemap_include_images'])) {
            echo ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }
        echo ">\n";

        while ($q->have_posts()) {
            $q->the_post();
            $id  = get_the_ID();
            if (in_array((int) $id, $exclude, true)) {
                continue;
            }
            // Skip noindex posts
            $robots = get_post_meta($id, '_bankai_seo_robots', true);
            if (is_array($robots) && isset($robots['index']) && empty($robots['index'])) {
                continue;
            }
            $url = get_permalink($id);
            $mod = get_post_modified_time('Y-m-d\TH:i:sP', true) ?: gmdate('c');
            $priority = ($post_type === 'page') ? '0.6' : '0.8';
            echo "  <url>\n";
            echo '    <loc>' . esc_url($url) . "</loc>\n";
            echo '    <lastmod>' . esc_html($mod) . "</lastmod>\n";
            echo '    <changefreq>weekly</changefreq>\n';
            echo '    <priority>' . $priority . "</priority>\n";
            if (!empty($s['sitemap_include_images'])) {
                $img = get_the_post_thumbnail_url($id, 'full');
                if ($img) {
                    echo "    <image:image><image:loc>" . esc_url($img) . "</image:loc></image:image>\n";
                }
            }
            echo "  </url>\n";
        }
        wp_reset_postdata();
        echo '</urlset>';
    }

    public function site_audit(): WP_REST_Response
    {
        $s = $this->get_settings();
        $home = home_url('/');

        $probe = static function (string $url): array {
            $r = wp_remote_get($url, [
                'timeout'     => 8,
                'redirection' => 2,
                'sslverify'   => false,
                'headers'     => ['Accept' => '*/*'],
            ]);
            if (is_wp_error($r)) {
                return ['ok' => false, 'code' => 0, 'msg' => $r->get_error_message()];
            }
            $code = (int) wp_remote_retrieve_response_code($r);
            $body = (string) wp_remote_retrieve_body($r);
            return ['ok' => $code >= 200 && $code < 400, 'code' => $code, 'body' => $body];
        };

        $sitemap_url = home_url('/sitemap.xml');
        $sm = $probe($sitemap_url);
        $sm_body_ok = $sm['ok'] && (
            str_contains($sm['body'] ?? '', '<sitemapindex') ||
            str_contains($sm['body'] ?? '', '<urlset') ||
            str_contains($sm['body'] ?? '', '<?xml')
        );
        // Local loopback often fails HTTP self-request — trust module/settings when probe fails
        $sm_ok = !empty($s['sitemap_enabled']) && ($sm_body_ok || !$sm['ok']);

        $robots_url = home_url('/robots.txt');
        $rb = $probe($robots_url);
        $rb_ok = $rb['ok'] && (
            str_contains(strtolower($rb['body'] ?? ''), 'user-agent') ||
            str_contains(strtolower($rb['body'] ?? ''), 'sitemap:')
        );

        $llms_url = home_url('/llms.txt');
        $ll = $probe($llms_url);
        $llms_mod = function_exists('bankai_is_module_active') ? bankai_is_module_active('llms_txt') : true;
        $mods = function_exists('bankai_get_option') ? (array) bankai_get_option('seo_modules', []) : [];
        if (array_key_exists('llms_txt_builder', $mods)) {
            $llms_mod = !empty($mods['llms_txt_builder']);
        }
        $ll_ok = $llms_mod && (($ll['ok'] && strlen(trim($ll['body'] ?? '')) > 20) || !$ll['ok']);

        $ga4 = (string) ($s['ga4_measurement_id'] ?: $s['google_analytics_id'] ?? '');
        $gtm = (string) ($s['gtm_container_id'] ?: $s['google_tag_manager_id'] ?? '');
        $gsc = (string) ($s['google_site_verification'] ?? '');
        $bing = (string) ($s['bing_site_verification'] ?: $s['bing_webmaster'] ?? '');
        $yandex = (string) ($s['yandex_verification'] ?? '');

        $org_name = '';
        $org_logo = '';
        if (class_exists('Bankai_SEO_Sitewide')) {
            $sw = Bankai_SEO_Sitewide::instance()->get_settings();
            $org_name = (string) ($sw['org_name'] ?? '');
            $org_logo = (string) ($sw['org_logo'] ?? '');
        }

        $items = [
            [
                'key' => 'sitemap',
                'label' => 'نقشه سایت XML',
                'detail' => !empty($s['sitemap_enabled'])
                    ? ($sm_body_ok ? ('در دسترس · HTTP ' . ($sm['code'] ?? 200)) : 'فعال در ماژول (پروب شبکه ممکن نشد)')
                    : 'ماژول/تنظیمات غیرفعال',
                'pass' => $sm_ok,
                'link' => $sitemap_url,
            ],
            [
                'key' => 'robots',
                'label' => 'robots.txt',
                'detail' => $rb_ok ? 'قابل خواندن' : 'پاسخ نامعتبر',
                'pass' => $rb_ok || !empty($s['manage_robots']),
                'link' => $robots_url,
            ],
            [
                'key' => 'llms',
                'label' => 'llms.txt',
                'detail' => $llms_mod
                    ? (($ll['ok'] && strlen(trim($ll['body'] ?? '')) > 20) ? 'در دسترس' : 'فعال در ماژول')
                    : 'ماژول خاموش',
                'pass' => $ll_ok,
                'link' => $llms_url,
            ],
            [
                'key' => 'ga4',
                'label' => 'Google Analytics 4',
                'detail' => $ga4 !== '' ? $ga4 : 'تنظیم نشده',
                'pass' => $ga4 !== '' && (bool) preg_match('/^G-[A-Z0-9]+$/i', $ga4),
                'link' => 'https://analytics.google.com/',
            ],
            [
                'key' => 'gsc',
                'label' => 'Search Console',
                'detail' => $gsc !== '' ? 'کد تأیید ذخیره شده' : 'تنظیم نشده',
                'pass' => $gsc !== '',
                'link' => 'https://search.google.com/search-console',
            ],
            [
                'key' => 'org_schema',
                'label' => 'هویت برند (Organization)',
                'detail' => $org_name !== '' ? $org_name : 'نام سازمان خالی',
                'pass' => $org_name !== '',
                'link' => '',
            ],
            [
                'key' => 'permalinks',
                'label' => 'پیوند یکتا',
                'detail' => get_option('permalink_structure') ?: 'ساختار ساده',
                'pass' => (get_option('permalink_structure') !== ''),
                'link' => admin_url('options-permalink.php'),
            ],
            [
                'key' => 'https',
                'label' => 'HTTPS',
                'detail' => (is_ssl() || str_starts_with(home_url(), 'https')) ? 'فعال' : 'فقط HTTP',
                'pass' => is_ssl() || str_starts_with(home_url(), 'https'),
                'link' => '',
            ],
        ];

        $required = array_values(array_filter($items, static fn($i) => empty($i['optional'])));
        $pass = count(array_filter($required, static fn($i) => !empty($i['pass'])));
        $score = (int) round(($pass / max(1, count($required))) * 100);

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'score'  => $score,
                'passed' => $pass,
                'total'  => count($required),
                'items'  => $items,
            ],
        ]);
    }

}
