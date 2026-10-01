<?php
/**
 * Bankai SEO — site-wide layer
 * Taxonomy SEO · Archives · Redirects · 404 log · Global schema · Sitemap extras · hreflang · Woo
 *
 * @package Bankai
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_SEO_Sitewide
{
    private static ?self $instance = null;

    public const OPT_SETTINGS   = 'bankai_seo_sitewide';
    public const OPT_REDIRECTS  = 'bankai_seo_redirects';
    public const OPT_404        = 'bankai_seo_404_log';
    public const TERM_META_TITLE = '_bankai_term_seo_title';
    public const TERM_META_DESC  = '_bankai_term_seo_desc';
    public const TERM_META_ROBOTS = '_bankai_term_seo_robots';

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('seo_engine')) {
            return;
        }

        add_action('init', [$this, 'register_term_meta'], 20);
        add_action('wp_head', [$this, 'render_global_schema'], 15);
        add_action('wp_head', [$this, 'render_taxonomy_meta'], 2);
        add_action('wp_head', [$this, 'render_hreflang'], 3);
        add_action('template_redirect', [$this, 'handle_global_redirects'], 0);
        add_action('template_redirect', [$this, 'log_404'], 99);
        add_filter('document_title_parts', [$this, 'filter_title_parts'], 20);
        add_filter('pre_get_document_title', [$this, 'filter_document_title'], 20);

        // Term edit UI (categories & tags)
        foreach (['category', 'post_tag'] as $tax) {
            add_action("{$tax}_add_form_fields", [$this, 'term_add_fields']);
            add_action("{$tax}_edit_form_fields", [$this, 'term_edit_fields'], 10, 2);
            add_action("created_{$tax}", [$this, 'save_term_meta']);
            add_action("edited_{$tax}", [$this, 'save_term_meta']);
        }

        if (class_exists('WooCommerce')) {
            add_action('woocommerce_product_options_general_product_data', [$this, 'woo_product_fields']);
            add_action('woocommerce_process_product_meta', [$this, 'woo_save_product']);
        }

        add_action('rest_api_init', [$this, 'register_rest']);
    }

    public function get_settings(): array
    {
        $defaults = [
            'org_name'           => get_bloginfo('name'),
            'org_logo'           => '',
            'org_type'           => 'Organization',
            'org_same_as'        => '',
            'website_search'     => true,
            'noindex_author'     => true,
            'noindex_date'       => true,
            'noindex_tag'        => false,
            'noindex_search'     => true,
            'noindex_attach'     => true,
            'sitemap_exclude_ids'=> '',
            'hreflang_enabled'   => false,
            'hreflang_default'   => '',
            'gsc_property'       => '',
            'indexing_token'     => '',
        ];
        $saved = function_exists('bankai_get_option')
            ? bankai_get_option(self::OPT_SETTINGS, [])
            : get_option(self::OPT_SETTINGS, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return array_merge($defaults, $saved);
    }

    public function save_settings(array $in): array
    {
        $cur = $this->get_settings();
        foreach ($cur as $k => $v) {
            if (!array_key_exists($k, $in)) {
                continue;
            }
            if (in_array($k, ['website_search', 'noindex_author', 'noindex_date', 'noindex_tag', 'noindex_search', 'noindex_attach', 'hreflang_enabled'], true)) {
                $cur[$k] = !empty($in[$k]);
            } elseif ($k === 'org_same_as' || $k === 'sitemap_exclude_ids') {
                $cur[$k] = sanitize_textarea_field((string) $in[$k]);
            } elseif ($k === 'indexing_token') {
                $cur[$k] = sanitize_text_field((string) $in[$k]);
            } else {
                $cur[$k] = sanitize_text_field((string) $in[$k]);
            }
        }
        if (function_exists('bankai_update_option')) {
            bankai_update_option(self::OPT_SETTINGS, $cur);
        } else {
            update_option(self::OPT_SETTINGS, $cur, false);
        }
        return $cur;
    }

    public function get_redirects(): array
    {
        $rows = get_option(self::OPT_REDIRECTS, []);
        return is_array($rows) ? array_values($rows) : [];
    }

    public function save_redirects(array $rows): array
    {
        $clean = [];
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $from = isset($r['from']) ? $this->normalize_path((string) $r['from']) : '';
            $to   = isset($r['to']) ? esc_url_raw((string) $r['to']) : '';
            $code = isset($r['code']) ? (int) $r['code'] : 301;
            if ($from === '' || $to === '') {
                continue;
            }
            if (!in_array($code, [301, 302, 307, 410], true)) {
                $code = 301;
            }
            $clean[] = [
                'id'   => !empty($r['id']) ? sanitize_key((string) $r['id']) : uniqid('r', false),
                'from' => $from,
                'to'   => $to,
                'code' => $code,
            ];
        }
        update_option(self::OPT_REDIRECTS, $clean, false);
        return $clean;
    }

    private function normalize_path(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            $p = wp_parse_url($path, PHP_URL_PATH);
            $path = is_string($p) ? $p : '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return untrailingslashit($path) ?: '/';
    }

    public function register_term_meta(): void
    {
        foreach ([self::TERM_META_TITLE, self::TERM_META_DESC, self::TERM_META_ROBOTS] as $key) {
            register_meta('term', $key, [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'auth_callback'     => static fn() => current_user_can('manage_categories'),
            ]);
        }
    }

    public function term_add_fields(): void
    {
        echo '<div class="form-field"><label>عنوان سئو (بنکای)</label>';
        echo '<input type="text" name="bankai_term_seo_title" value=""></div>';
        echo '<div class="form-field"><label>توضیحات متا (بنکای)</label>';
        echo '<textarea name="bankai_term_seo_desc" rows="3"></textarea></div>';
        echo '<div class="form-field"><label><input type="checkbox" name="bankai_term_noindex" value="1"> noindex این آرشیو</label></div>';
    }

    public function term_edit_fields($term): void
    {
        $id = (int) $term->term_id;
        $title = (string) get_term_meta($id, self::TERM_META_TITLE, true);
        $desc  = (string) get_term_meta($id, self::TERM_META_DESC, true);
        $robots = (string) get_term_meta($id, self::TERM_META_ROBOTS, true);
        $noindex = ($robots === 'noindex' || str_contains($robots, 'noindex'));
        echo '<tr class="form-field"><th><label>عنوان سئو (بنکای)</label></th><td>';
        echo '<input type="text" name="bankai_term_seo_title" value="' . esc_attr($title) . '" class="regular-text"></td></tr>';
        echo '<tr class="form-field"><th><label>توضیحات متا (بنکای)</label></th><td>';
        echo '<textarea name="bankai_term_seo_desc" rows="3" class="large-text">' . esc_textarea($desc) . '</textarea></td></tr>';
        echo '<tr class="form-field"><th>Robots</th><td><label><input type="checkbox" name="bankai_term_noindex" value="1" ' . checked($noindex, true, false) . '> noindex</label></td></tr>';
    }

    public function save_term_meta(int $term_id): void
    {
        if (isset($_POST['bankai_term_seo_title'])) {
            update_term_meta($term_id, self::TERM_META_TITLE, sanitize_text_field(wp_unslash((string) $_POST['bankai_term_seo_title'])));
        }
        if (isset($_POST['bankai_term_seo_desc'])) {
            update_term_meta($term_id, self::TERM_META_DESC, sanitize_textarea_field(wp_unslash((string) $_POST['bankai_term_seo_desc'])));
        }
        $noindex = !empty($_POST['bankai_term_noindex']);
        update_term_meta($term_id, self::TERM_META_ROBOTS, $noindex ? 'noindex,follow' : 'index,follow');
    }

    public function filter_document_title($title)
    {
        if (!is_string($title) || is_admin()) {
            return $title;
        }
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term && !empty($term->term_id)) {
                $custom = (string) get_term_meta((int) $term->term_id, self::TERM_META_TITLE, true);
                if ($custom !== '') {
                    return $custom;
                }
            }
        }
        return $title;
    }

    public function filter_title_parts(array $parts): array
    {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term && !empty($term->term_id)) {
                $custom = (string) get_term_meta((int) $term->term_id, self::TERM_META_TITLE, true);
                if ($custom !== '') {
                    $parts['title'] = $custom;
                }
            }
        }
        return $parts;
    }

    public function render_taxonomy_meta(): void
    {
        if (is_admin()) {
            return;
        }
        $s = $this->get_settings();

        // Archive noindex rules
        $noindex = false;
        if (is_author() && !empty($s['noindex_author'])) {
            $noindex = true;
        }
        if ((is_date() || is_year() || is_month() || is_day()) && !empty($s['noindex_date'])) {
            $noindex = true;
        }
        if (is_search() && !empty($s['noindex_search'])) {
            $noindex = true;
        }
        if (is_attachment() && !empty($s['noindex_attach'])) {
            $noindex = true;
        }
        if (is_tag() && !empty($s['noindex_tag'])) {
            $noindex = true;
        }

        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term && !empty($term->term_id)) {
                $desc = (string) get_term_meta((int) $term->term_id, self::TERM_META_DESC, true);
                if ($desc !== '') {
                    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
                }
                $robots = (string) get_term_meta((int) $term->term_id, self::TERM_META_ROBOTS, true);
                if (str_contains($robots, 'noindex')) {
                    $noindex = true;
                }
            }
        }

        if ($noindex) {
            echo '<meta name="robots" content="noindex, follow">' . "\n";
        }
    }

    public function render_global_schema(): void
    {
        if (is_admin() || !is_front_page()) {
            // Still emit Organization sitewide once on all public pages is ok; keep on front + home
            if (is_admin() || !(is_front_page() || is_home())) {
                return;
            }
        }
        $s = $this->get_settings();
        $nodes = [];

        $org = [
            '@type' => $s['org_type'] ?: 'Organization',
            '@id'   => home_url('/#organization'),
            'name'  => $s['org_name'] ?: get_bloginfo('name'),
            'url'   => home_url('/'),
        ];
        if (!empty($s['org_logo'])) {
            $org['logo'] = [
                '@type' => 'ImageObject',
                'url'   => $s['org_logo'],
            ];
        }
        $same = array_filter(array_map('trim', preg_split('/[\n,]+/', (string) $s['org_same_as']) ?: []));
        if ($same) {
            $org['sameAs'] = array_values($same);
        }
        $nodes[] = $org;

        $website = [
            '@type'     => 'WebSite',
            '@id'       => home_url('/#website'),
            'url'       => home_url('/'),
            'name'      => get_bloginfo('name'),
            'publisher' => ['@id' => home_url('/#organization')],
        ];
        if (!empty($s['website_search'])) {
            $website['potentialAction'] = [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => home_url('/?s={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }
        $nodes[] = $website;

        $payload = [
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ];
        echo '<script type="application/ld+json">'
            . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }

    public function render_hreflang(): void
    {
        $s = $this->get_settings();
        if (empty($s['hreflang_enabled'])) {
            // Auto if Polylang / WPML
            if (function_exists('pll_the_languages')) {
                $langs = pll_the_languages(['raw' => 1]);
                if (is_array($langs)) {
                    foreach ($langs as $lang) {
                        if (!empty($lang['url']) && !empty($lang['slug'])) {
                            printf(
                                '<link rel="alternate" hreflang="%s" href="%s">' . "\n",
                                esc_attr($lang['slug']),
                                esc_url($lang['url'])
                            );
                        }
                    }
                }
                return;
            }
            if (defined('ICL_SITEPRESS_VERSION') && function_exists('apply_filters')) {
                $languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
                if (is_array($languages)) {
                    foreach ($languages as $lang) {
                        if (!empty($lang['url']) && !empty($lang['language_code'])) {
                            printf(
                                '<link rel="alternate" hreflang="%s" href="%s">' . "\n",
                                esc_attr($lang['language_code']),
                                esc_url($lang['url'])
                            );
                        }
                    }
                }
            }
            return;
        }
        $def = $s['hreflang_default'] ?: substr(get_locale(), 0, 2);
        if (is_singular()) {
            printf(
                '<link rel="alternate" hreflang="%s" href="%s">' . "\n",
                esc_attr($def),
                esc_url(get_permalink())
            );
            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/')) . '">' . "\n";
        }
    }

    public function handle_global_redirects(): void
    {
        if (is_admin()) {
            return;
        }
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
        $path = (string) (wp_parse_url($uri, PHP_URL_PATH) ?: '/');
        $path = untrailingslashit($path) ?: '/';

        foreach ($this->get_redirects() as $r) {
            $from = $r['from'] ?? '';
            if ($from === '' || $from !== $path) {
                continue;
            }
            $code = (int) ($r['code'] ?? 301);
            $to   = (string) ($r['to'] ?? '');
            if ($code === 410) {
                status_header(410);
                nocache_headers();
                echo 'Gone';
                exit;
            }
            if ($to !== '') {
                wp_redirect($to, $code);
                exit;
            }
        }
    }

    public function log_404(): void
    {
        if (!is_404() || is_admin()) {
            return;
        }
        $uri = isset($_SERVER['REQUEST_URI']) ? substr(sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI'])), 0, 500) : '';
        if ($uri === '') {
            return;
        }
        $log = get_option(self::OPT_404, []);
        if (!is_array($log)) {
            $log = [];
        }
        $key = md5($uri);
        if (isset($log[$key])) {
            $log[$key]['count'] = (int) $log[$key]['count'] + 1;
            $log[$key]['last']  = time();
        } else {
            $log[$key] = [
                'uri'   => $uri,
                'count' => 1,
                'first' => time(),
                'last'  => time(),
            ];
        }
        // Cap log size
        if (count($log) > 200) {
            uasort($log, static fn($a, $b) => ($b['last'] ?? 0) <=> ($a['last'] ?? 0));
            $log = array_slice($log, 0, 200, true);
        }
        update_option(self::OPT_404, $log, false);
    }

    public function get_404_log(): array
    {
        $log = get_option(self::OPT_404, []);
        if (!is_array($log)) {
            return [];
        }
        $rows = array_values($log);
        usort($rows, static fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
        return $rows;
    }

    public function clear_404_log(): void
    {
        delete_option(self::OPT_404);
    }

    /** IDs excluded from sitemap (helper for integrations). */
    public function get_sitemap_exclude_ids(): array
    {
        $s = $this->get_settings();
        $raw = (string) ($s['sitemap_exclude_ids'] ?? '');
        $ids = array_filter(array_map('absint', preg_split('/[\s,]+/', $raw) ?: []));
        return array_values(array_unique($ids));
    }

    public function woo_product_fields(): void
    {
        echo '<div class="options_group">';
        woocommerce_wp_text_input([
            'id'          => '_bankai_seo_title',
            'label'       => 'عنوان سئو (بنکای)',
            'desc_tip'    => true,
            'description' => 'در صورت خالی بودن از عنوان محصول استفاده می‌شود.',
        ]);
        woocommerce_wp_textarea_input([
            'id'          => '_bankai_seo_description',
            'label'       => 'متا دیسکریپشن (بنکای)',
            'desc_tip'    => true,
            'description' => 'توضیحات متا محصول',
        ]);
        echo '</div>';
    }

    public function woo_save_product(int $post_id): void
    {
        if (isset($_POST['_bankai_seo_title'])) {
            update_post_meta($post_id, '_bankai_seo_title', sanitize_text_field(wp_unslash((string) $_POST['_bankai_seo_title'])));
        }
        if (isset($_POST['_bankai_seo_description'])) {
            update_post_meta($post_id, '_bankai_seo_description', sanitize_textarea_field(wp_unslash((string) $_POST['_bankai_seo_description'])));
        }
    }

    public function register_rest(): void
    {
        $ns = 'bankai/v1';
        register_rest_route($ns, '/seo/sitewide', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'api_get'],
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'api_save'],
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
        ]);
        register_rest_route($ns, '/seo/redirects', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => static function () {
                    return new WP_REST_Response(['success' => true, 'data' => self::instance()->get_redirects()]);
                },
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'api_save_redirects'],
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
        ]);
        register_rest_route($ns, '/seo/404-log', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => static function () {
                    return new WP_REST_Response(['success' => true, 'data' => self::instance()->get_404_log()]);
                },
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => static function () {
                    self::instance()->clear_404_log();
                    return new WP_REST_Response(['success' => true]);
                },
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
        ]);
    }

    public function api_get(): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'settings'  => $this->get_settings(),
                'redirects' => $this->get_redirects(),
                'log404'    => array_slice($this->get_404_log(), 0, 50),
            ],
        ]);
    }

    public function api_save(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            return new WP_REST_Response(['success' => false], 400);
        }
        $settings = isset($params['settings']) && is_array($params['settings'])
            ? $this->save_settings($params['settings'])
            : $this->get_settings();
        return new WP_REST_Response(['success' => true, 'data' => ['settings' => $settings]]);
    }

    public function api_save_redirects(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        $rows = [];
        if (isset($params['redirects']) && is_array($params['redirects'])) {
            $rows = $params['redirects'];
        } elseif (is_array($params)) {
            $rows = $params;
        }
        $saved = $this->save_redirects($rows);
        return new WP_REST_Response(['success' => true, 'data' => $saved]);
    }
}
