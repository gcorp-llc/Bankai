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
        add_rewrite_rule('^sitemap\.xml$', 'index.php?bankai_sitemap=1', 'top');
        add_rewrite_rule('^sitemap-posts\.xml$', 'index.php?bankai_sitemap=posts', 'top');
        add_rewrite_rule('^sitemap-pages\.xml$', 'index.php?bankai_sitemap=pages', 'top');
    }

    public function serve(): void
    {
        $sm = get_query_var('bankai_sitemap');
        if ($sm) {
            $this->output_sitemap((string) $sm);
            exit;
        }

        // Optional Bankai-managed robots when enabled
        $settings = $this->get_settings();
        if (!empty($settings['manage_robots']) && isset($_SERVER['REQUEST_URI'])) {
            $path = (string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
            if (rtrim($path, '/') === '/robots.txt' || str_ends_with($path, '/robots.txt')) {
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
            'google_site_verification' => '',
            'bing_site_verification'   => '',
            'yandex_verification'      => '',
            'pinterest_verification'   => '',
            'baidu_verification'       => '',
            'ga4_measurement_id'       => '',
            'gtm_container_id'         => '',
            'bing_uet_id'              => '',
            'yandex_metrica_id'        => '',
            'sitemap_enabled'          => true,
            'sitemap_include_images'   => true,
            'indexnow_key'             => '',
        ];
        $saved = get_option(self::OPT, []);
        return wp_parse_args(is_array($saved) ? $saved : [], $defaults);
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
        if (empty($s['sitemap_enabled'])) {
            status_header(404);
            echo 'Sitemap disabled';
            return;
        }

        nocache_headers();
        header('Content-Type: application/xml; charset=utf-8');

        if ($type === '1' || $type === 'index') {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            echo '  <sitemap><loc>' . esc_url(home_url('/sitemap-posts.xml')) . '</loc></sitemap>' . "\n";
            echo '  <sitemap><loc>' . esc_url(home_url('/sitemap-pages.xml')) . '</loc></sitemap>' . "\n";
            echo '</sitemapindex>';
            return;
        }

        $post_type = $type === 'pages' ? 'page' : 'post';
        $q = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 1000,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ]);

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if (!empty($s['sitemap_include_images'])) {
            echo ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }
        echo ">\n";

        while ($q->have_posts()) {
            $q->the_post();
            $id  = get_the_ID();
            $url = get_permalink($id);
            $mod = get_the_modified_time('c');
            echo "  <url>\n";
            echo '    <loc>' . esc_url($url) . "</loc>\n";
            echo '    <lastmod>' . esc_html($mod) . "</lastmod>\n";
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
        $items = [
            [
                'key' => 'sitemap',
                'label' => 'نقشه سایت XML فعال است',
                'label_en' => 'XML Sitemap enabled',
                'pass' => !empty($s['sitemap_enabled']),
                'link' => $home . 'sitemap.xml',
            ],
            [
                'key' => 'robots',
                'label' => 'robots.txt مدیریت‌شده',
                'label_en' => 'Managed robots.txt',
                'pass' => !empty($s['manage_robots']) || !empty($s['robots_extra']),
                'link' => $home . 'robots.txt',
            ],
            [
                'key' => 'llms',
                'label' => 'مانیفست llms.txt',
                'label_en' => 'llms.txt manifest',
                'pass' => function_exists('bankai_is_module_active') ? bankai_is_module_active('llms_txt') : true,
                'link' => $home . 'llms.txt',
            ],
            [
                'key' => 'google_ver',
                'label' => 'تأیید گوگل سرچ کنسول',
                'label_en' => 'Google verification',
                'pass' => !empty($s['google_site_verification']),
                'link' => 'https://search.google.com/search-console',
            ],
            [
                'key' => 'bing_ver',
                'label' => 'تأیید بینگ',
                'label_en' => 'Bing verification',
                'pass' => !empty($s['bing_site_verification']),
                'link' => 'https://www.bing.com/webmasters',
            ],
            [
                'key' => 'yandex_ver',
                'label' => 'تأیید یاندکس',
                'label_en' => 'Yandex verification',
                'pass' => !empty($s['yandex_verification']),
                'link' => 'https://webmaster.yandex.com/',
            ],
            [
                'key' => 'ga4',
                'label' => 'Google Analytics 4',
                'label_en' => 'GA4 connected',
                'pass' => !empty($s['ga4_measurement_id']),
                'link' => 'https://analytics.google.com/',
            ],
            [
                'key' => 'gtm',
                'label' => 'Google Tag Manager',
                'label_en' => 'GTM',
                'pass' => !empty($s['gtm_container_id']),
                'link' => 'https://tagmanager.google.com/',
            ],
            [
                'key' => 'indexnow',
                'label' => 'کلید IndexNow',
                'label_en' => 'IndexNow key',
                'pass' => !empty($s['indexnow_key']),
                'link' => 'https://www.indexnow.org/',
            ],
            [
                'key' => 'seo_module',
                'label' => 'ماژول اصلی سئو فعال',
                'label_en' => 'SEO engine active',
                'pass' => function_exists('bankai_is_module_active') ? bankai_is_module_active('seo_engine') : true,
                'link' => '',
            ],
            [
                'key' => 'schema',
                'label' => 'تولید اسکیما',
                'label_en' => 'Schema generator',
                'pass' => (function_exists('bankai_get_option') ? !empty(bankai_get_option('seo_modules', [])['auto_meta'] ?? true) : true),
                'link' => '',
            ],
            [
                'key' => 'permalinks',
                'label' => 'پیوند یکتا غیرساده',
                'label_en' => 'Pretty permalinks',
                'pass' => (get_option('permalink_structure') !== ''),
                'link' => admin_url('options-permalink.php'),
            ],
        ];
        $pass = count(array_filter($items, static fn($i) => !empty($i['pass'])));
        $score = (int) round(($pass / max(1, count($items))) * 100);
        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'score' => $score,
                'passed' => $pass,
                'total' => count($items),
                'items' => $items,
            ],
        ]);
    }

}
