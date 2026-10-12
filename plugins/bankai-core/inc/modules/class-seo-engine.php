<?php
/**
 * Bankai Core - SEO Engine
 *
 * Meta · Analysis · Robots · Canonical · JSON-LD · OG · Twitter · REST
 *
 * @package Bankai
 * @version 2.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Persian text normalizer for SEO comparisons (pure PHP).
$__bankai_seo_norm = __DIR__ . '/class-seo-text-normalizer.php';
if (is_readable($__bankai_seo_norm)) {
    require_once $__bankai_seo_norm;
}
$__bankai_seo_rank = __DIR__ . '/class-seo-link-ranker.php';
if (is_readable($__bankai_seo_rank)) {
    require_once $__bankai_seo_rank;
}


final class Bankai_SEO_Engine
{
    private static ?self $instance = null;

    // Meta Keys Constants
    public const META_TITLE       = '_bankai_seo_title';
    public const META_DESCRIPTION = '_bankai_seo_description';
    public const META_KEYWORD     = '_bankai_seo_focus_keyword';
    public const META_KEYWORDS    = '_bankai_seo_keywords'; // JSON array of secondary keywords
    public const META_CANONICAL   = '_bankai_seo_canonical';
    public const META_ROBOTS      = '_bankai_seo_robots';
    public const META_OG_TITLE    = '_bankai_seo_og_title';
    public const META_OG_DESC     = '_bankai_seo_og_description';
    public const META_OG_IMAGE    = '_bankai_seo_og_image';
    public const META_X_TITLE     = '_bankai_seo_x_title';
    public const META_X_DESC      = '_bankai_seo_x_description';
    public const META_X_IMAGE     = '_bankai_seo_x_image';
    public const META_SCHEMA      = '_bankai_seo_schema';
    public const META_REDIRECT    = '_bankai_seo_redirect';
    public const META_SCORE       = '_bankai_seo_score';
    public const META_VIEWS       = '_bankai_post_views';

    /**
     * Field mappings for fast payload processing
     */
    private const FIELD_MAP = [
        'seo_title'      => self::META_TITLE,
        'description'    => self::META_DESCRIPTION,
        'focus_keyword'  => self::META_KEYWORD,
        'keywords'       => self::META_KEYWORDS,
        'canonical'      => self::META_CANONICAL,
        'og_title'       => self::META_OG_TITLE,
        'og_description' => self::META_OG_DESC,
        'og_image'       => self::META_OG_IMAGE,
        'x_title'        => self::META_X_TITLE,
        'x_description'  => self::META_X_DESC,
        'x_image'        => self::META_X_IMAGE,
        'redirect'       => self::META_REDIRECT,
    ];

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('seo_engine')) {
            return;
        }

        add_action('init', [$this, 'register_meta']);
        add_action('init', [$this, 'maybe_create_views_table'], 20);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('wp_head', [$this, 'render_meta'], 1);
        // جلوگیری از تگ canonical تکراری وردپرس
        remove_action('wp_head', 'rel_canonical');
        add_action('wp_head', [$this, 'suppress_competing_seo'], 0);
        add_action('send_headers', [$this, 'send_security_headers'], 1);
        add_filter('the_content', [$this, 'filter_noopener_links'], 20);
        add_action('init', [$this, 'register_web_manifest']);
        add_action('template_redirect', [$this, 'serve_web_manifest'], 0);
        add_filter('site_icon_meta_tags', [$this, 'ensure_single_description_guard'], 99);
        add_action('wp_head', [$this, 'render_schema'], 20);
        add_action('template_redirect', [$this, 'handle_post_redirect'], 1);
        add_action('wp_head', [$this, 'render_analytics'], 5);
        add_filter('pre_get_document_title', [$this, 'filter_document_title'], 20);
        add_action('wp_footer', [$this, 'print_view_tracker'], 99);
    }

    public function register_meta(): void
    {
        $string_fields = array_values(self::FIELD_MAP);
        $string_fields[] = self::META_SCHEMA;

        foreach (array_unique($string_fields) as $meta_key) {
            register_post_meta('', $meta_key, [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [$this, 'sanitize_meta'],
                'auth_callback'     => static fn(): bool => current_user_can('edit_posts'),
            ]);
        }

        register_post_meta('', self::META_ROBOTS, [
            'type'              => 'object',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'index'  => ['type' => 'boolean'],
                        'follow' => ['type' => 'boolean'],
                    ],
                ],
            ],
            'default'           => ['index' => true, 'follow' => true],
            'sanitize_callback' => [$this, 'sanitize_robots'],
            'auth_callback'     => static fn(): bool => current_user_can('edit_posts'),
        ]);

        register_post_meta('', self::META_REDIRECT, [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback'     => static fn(): bool => current_user_can('edit_posts'),
        ]);

        register_post_meta('', self::META_SCORE, [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'auth_callback'     => static fn(): bool => current_user_can('edit_posts'),
        ]);
    }

    public function sanitize_meta(mixed $value): string
    {
        return sanitize_text_field(wp_unslash((string) $value));
    }

    public function sanitize_robots(mixed $value): array
    {
        $value = is_array($value) ? $value : [];
        $out = [
            'index'             => !empty($value['index']),
            'follow'            => !empty($value['follow']),
            'noarchive'         => !empty($value['noarchive']),
            'nosnippet'         => !empty($value['nosnippet']),
            'noimageindex'      => !empty($value['noimageindex']),
            'max_snippet'       => isset($value['max_snippet']) ? (int) $value['max_snippet'] : -1,
            'max_image_preview' => in_array(($value['max_image_preview'] ?? 'large'), ['none', 'standard', 'large'], true)
                ? (string) $value['max_image_preview']
                : 'large',
            'hide_date'         => !empty($value['hide_date']),
        ];
        return $out;
    }

    public function register_rest_routes(): void
    {
        $namespace = 'bankai/v1';

        register_rest_route($namespace, '/seo/analyze/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'api_analyze'],
                'permission_callback' => [$this, 'permission_check'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'api_analyze'],
                'permission_callback' => [$this, 'permission_check'],
            ],
        ]);

        register_rest_route($namespace, '/seo/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'api_get'],
                'permission_callback' => [$this, 'permission_check'],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'api_save'],
                'permission_callback' => [$this, 'permission_check'],
            ],
        ]);

        register_rest_route($namespace, '/seo/search-posts', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'api_search_posts'],
                'permission_callback' => static fn(): bool => current_user_can('edit_posts'),
                'args'                => [
                    'q' => ['type' => 'string', 'required' => false, 'sanitize_callback' => 'sanitize_text_field'],
                    's' => ['type' => 'string', 'required' => false, 'sanitize_callback' => 'sanitize_text_field'],
                ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'api_search_posts'],
                'permission_callback' => static fn(): bool => current_user_can('edit_posts'),
            ],
        ]);

        register_rest_route($namespace, '/seo/links/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'api_extract_links'],
            'permission_callback' => [$this, 'permission_check'],
        ]);

        register_rest_route($namespace, '/seo/articles', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'api_articles_list'],
            'permission_callback' => static fn(): bool => current_user_can('edit_posts'),
            'args'                => [
                'page'     => ['type' => 'integer', 'default' => 1, 'sanitize_callback' => 'absint'],
                'per_page' => ['type' => 'integer', 'default' => 20, 'sanitize_callback' => 'absint'],
                'search'   => ['type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'orderby'  => ['type' => 'string', 'default' => 'modified', 'sanitize_callback' => 'sanitize_key'],
                'order'    => ['type' => 'string', 'default' => 'DESC', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route($namespace, '/track-view/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'api_track_view'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($namespace, '/seo/articles/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'api_save'],
                'permission_callback' => [$this, 'permission_check'],
            ],
        ]);

        register_rest_route($namespace, '/seo/fixed-keywords', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'api_get_fixed_keywords'],
                'permission_callback' => static fn(): bool => current_user_can('manage_options'),
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'api_save_fixed_keywords'],
                'permission_callback' => static fn(): bool => current_user_can('manage_options'),
            ],
        ]);

        register_rest_route($namespace, '/seo/suggest-links', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'api_suggest_internal_links'],
            'permission_callback' => [$this, 'permission_suggest_links'],
        ]);
    }

    public function permission_check(WP_REST_Request $request): bool
    {
        if (!current_user_can('edit_posts')) {
            return false;
        }
        $post_id = absint($request['id'] ?? $request->get_param('id') ?? 0);
        // New / unsaved posts: allow analyze when user can edit_posts
        if ($post_id <= 0) {
            return true;
        }
        return current_user_can('edit_post', $post_id);
    }

    /**
     * Phase E.1 — suggest-links: edit_posts baseline; if post_id given, must edit that post.
     */
    public function permission_suggest_links(WP_REST_Request $request): bool
    {
        if (!current_user_can('edit_posts')) {
            return false;
        }
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = $request->get_params();
        }
        $post_id = absint(is_array($params) ? ($params['post_id'] ?? 0) : 0);
        if ($post_id > 0) {
            return current_user_can('edit_post', $post_id);
        }
        return true;
    }

    /**
     * Optional daily views log table (post totals still live in post meta).
     * Safe no-op when already installed.
     */
    public function maybe_create_views_table(): void
    {
        $ver = '1';
        if (get_option('bankai_seo_views_table_ver') === $ver) {
            return;
        }
        global $wpdb;
        $table   = $wpdb->prefix . 'bankai_post_views_log';
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            viewed_on DATE NOT NULL,
            hits INT UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY post_day (post_id, viewed_on),
            KEY viewed_on (viewed_on)
        ) {$charset};";
        dbDelta($sql);
        update_option('bankai_seo_views_table_ver', $ver, false);
    }

    /**
     * Bulk read view counts from post meta.
     *
     * @param list<int> $post_ids
     * @return array<int,int>
     */
    public function get_views_map(array $post_ids): array
    {
        $post_ids = array_values(array_filter(array_map('absint', $post_ids)));
        if (!$post_ids) {
            return [];
        }
        $map = [];
        foreach ($post_ids as $id) {
            $map[$id] = (int) get_post_meta($id, self::META_VIEWS, true);
        }
        // Prefer a single query when possible
        global $wpdb;
        $ids_sql = implode(',', $post_ids);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- IDs are absint-filtered
        $rows = $wpdb->get_results(
            "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '" . esc_sql(self::META_VIEWS) . "' AND post_id IN ({$ids_sql})",
            ARRAY_A
        );
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $pid = (int) ($row['post_id'] ?? 0);
                if ($pid > 0) {
                    $map[$pid] = (int) ($row['meta_value'] ?? 0);
                }
            }
        }
        return $map;
    }

    /**
     * REST: increment post view counter (public beacon).
     */
    public function api_track_view(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id'] ?? 0);
        if ($post_id <= 0 || get_post_status($post_id) !== 'publish') {
            return new WP_REST_Response(['success' => false, 'message' => 'Invalid post'], 400);
        }
        // Skip obvious bots
        $ua = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        if ($ua !== '' && preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview/i', $ua)) {
            return new WP_REST_Response(['success' => true, 'skipped' => 'bot']);
        }

        $current = (int) get_post_meta($post_id, self::META_VIEWS, true);
        $next    = $current + 1;
        update_post_meta($post_id, self::META_VIEWS, $next);

        // Best-effort daily log (ignore failures)
        global $wpdb;
        $table = $wpdb->prefix . 'bankai_post_views_log';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists === $table) {
            $today = gmdate('Y-m-d');
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table} (post_id, viewed_on, hits) VALUES (%d, %s, 1)
                 ON DUPLICATE KEY UPDATE hits = hits + 1",
                $post_id,
                $today
            ));
        }

        return new WP_REST_Response(['success' => true, 'views' => $next]);
    }

    /**
     * Frontend view tracker (single post / page).
     */
    public function print_view_tracker(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }
        $post_id = (int) get_queried_object_id();
        if ($post_id <= 0) {
            return;
        }
        $url = esc_url_raw(rest_url('bankai/v1/track-view/' . $post_id));
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON flags
        $url_js = wp_json_encode($url);
        echo "<script>(function(){try{if(window.navigator&&navigator.sendBeacon){navigator.sendBeacon({$url_js},'');}else{fetch({$url_js},{method:'POST',credentials:'same-origin',keepalive:true});}}catch(e){}})();</script>\n";
    }


    public function api_get(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id'] ?? $request->get_param('id') ?? 0);
        try {
            $data = $this->get_seo_data($post_id);
            return new WP_REST_Response([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Throwable $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'SEO load failed',
                'error'   => defined('WP_DEBUG') && WP_DEBUG ? $e->getMessage() : 'internal_error',
            ], 500);
        }
    }

    public function api_save(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);
        $params  = $request->get_json_params();

        if (!is_array($params)) {
            return new WP_REST_Response(['success' => false, 'message' => 'پیام درخواست نا معتبر است.'], 400);
        }

        foreach (self::FIELD_MAP as $short => $field) {
            if (!array_key_exists($short, $params)) {
                continue;
            }

            $val = $params[$short];
            if ($short === 'keywords' && is_array($val)) {
                $sanitized = array_map('sanitize_text_field', $val);
                $fixed     = $this->get_fixed_keywords_list();
                $merged    = $this->merge_unique_keywords(array_merge($sanitized, $fixed));
                $val       = wp_json_encode($merged, JSON_UNESCAPED_UNICODE);
            } else {
                $val = sanitize_text_field(wp_unslash((string) $val));
            }
            update_post_meta($post_id, $field, $val);
        }

        if (isset($params['robots']) && is_array($params['robots'])) {
            update_post_meta($post_id, self::META_ROBOTS, $this->sanitize_robots($params['robots']));
        }

        if (isset($params['schema'])) {
            $schema = is_string($params['schema'])
                ? $params['schema']
                : wp_json_encode($params['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            update_post_meta($post_id, self::META_SCHEMA, $schema);
        }

        $analysis = $this->analyze($post_id);
        update_post_meta($post_id, self::META_SCORE, (int) ($analysis['score'] ?? 0));

        $link_suggestions = [];
        try {
            $kw = (string) get_post_meta($post_id, self::META_KEYWORD, true);
            if ($kw !== '' && method_exists($this, 'suggest_related_posts')) {
                $link_suggestions = $this->suggest_related_posts($post_id, $kw, 5);
            } elseif ($kw !== '') {
                $link_suggestions = $this->quick_related_posts($post_id, $kw, 5);
            }
        } catch (\Throwable $e) {
            $link_suggestions = [];
        }

        return new WP_REST_Response([
            'success'           => true,
            'data'              => $this->get_seo_data($post_id),
            'analysis'          => $analysis,
            'link_suggestions'  => $link_suggestions,
        ]);
    }

    public function api_analyze(WP_REST_Request $request): WP_REST_Response
    {
        $post_id   = absint($request['id']);
        $params    = $request->get_json_params() ?: [];
        $overrides = [];

        if (!empty($params['content'])) {
            $overrides['content'] = wp_kses_post($params['content']);
        }

        foreach (['seo_title', 'description', 'focus_keyword'] as $k) {
            if (isset($params[$k])) {
                $overrides[$k] = sanitize_text_field(wp_unslash((string) $params[$k]));
            }
        }

        $analysis = $this->analyze($post_id, $overrides);

        // Always keep sidebar / list / toolbar score in sync
        if ($post_id > 0) {
            update_post_meta($post_id, self::META_SCORE, (int) ($analysis['score'] ?? 0));
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => $analysis,
        ]);
    }

    public function api_search_posts(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }
        $q = sanitize_text_field((string) ($request->get_param('q') ?? $request->get_param('s') ?? $params['q'] ?? $params['s'] ?? ''));
        $exclude = absint($request->get_param('post_id') ?? ($params['post_id'] ?? 0));
        $limit = min(25, max(5, absint($request->get_param('limit') ?? ($params['limit'] ?? 15))));

        $found = [];
        $ids = [];

        // 1) Title/content search (no meta AND restriction)
        if (mb_strlen($q) >= 2) {
            foreach (get_posts([
                'post_type'      => ['post', 'page'],
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => $exclude > 0 ? [$exclude] : [],
                's'              => $q,
                'orderby'        => 'relevance',
            ]) as $p) {
                $ids[$p->ID] = true;
                $found[] = $p;
            }
            // 2) Keyword meta search
            foreach (get_posts([
                'post_type'      => ['post', 'page'],
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => $exclude > 0 ? [$exclude] : [],
                'meta_query'     => [
                    'relation' => 'OR',
                    [
                        'key'     => self::META_KEYWORD,
                        'value'   => $q,
                        'compare' => 'LIKE',
                    ],
                    [
                        'key'     => self::META_KEYWORDS,
                        'value'   => $q,
                        'compare' => 'LIKE',
                    ],
                ],
            ]) as $p) {
                if (!isset($ids[$p->ID])) {
                    $ids[$p->ID] = true;
                    $found[] = $p;
                }
            }
        }

        // 3) Fallback: recent published posts when few results
        if (count($found) < 5) {
            foreach (get_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => $exclude > 0 ? [$exclude] : [],
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]) as $p) {
                if (!isset($ids[$p->ID])) {
                    $ids[$p->ID] = true;
                    $found[] = $p;
                }
                if (count($found) >= $limit) {
                    break;
                }
            }
        }

        $data = array_map(static function (\WP_Post $p): array {
            return [
                'id'            => $p->ID,
                'title'         => get_the_title($p),
                'permalink'     => get_permalink($p),
                'type'          => $p->post_type,
                'focus_keyword' => (string) get_post_meta($p->ID, self::META_KEYWORD, true),
            ];
        }, array_slice($found, 0, $limit));

        return new WP_REST_Response(['success' => true, 'data' => $data]);
    }

    public function api_extract_links(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);
        $post    = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'message' => 'مطلب یافت نشد.'], 404);
        }

        $extracted = $this->extract_links_from_html($post->post_content);

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'internal' => $extracted['internal'],
                'external' => $extracted['external'],
                'counts'   => [
                    'internal' => count($extracted['internal']),
                    'external' => count($extracted['external']),
                ],
            ],
        ]);
    }

    public function get_seo_data(int $post_id): array
    {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }

        $seo_title    = (string) get_post_meta($post_id, self::META_TITLE, true);
        $description  = (string) get_post_meta($post_id, self::META_DESCRIPTION, true);
        $keywords_raw = (string) get_post_meta($post_id, self::META_KEYWORDS, true);
        
        $keywords = [];
        if ($keywords_raw !== '') {
            $decoded  = json_decode($keywords_raw, true);
            $keywords = is_array($decoded) ? $decoded : [];
        }

        $focus    = (string) get_post_meta($post_id, self::META_KEYWORD, true);
        $robots   = get_post_meta($post_id, self::META_ROBOTS, true);
        if (is_string($robots) && $robots !== '') {
            $decoded_robots = json_decode($robots, true);
            if (is_array($decoded_robots)) {
                $robots = $decoded_robots;
            }
        }
        $canonical= (string) get_post_meta($post_id, self::META_CANONICAL, true);

        // Ensure keywords list includes focus keyword when stored only as focus
        if ($focus !== '') {
            $has_focus = false;
            foreach ($keywords as $kw) {
                if (mb_strtolower((string) $kw) === mb_strtolower($focus)) {
                    $has_focus = true;
                    break;
                }
            }
            if (!$has_focus) {
                array_unshift($keywords, $focus);
            }
        }
        // Merge site-wide fixed keywords for display
        $fixed = $this->get_fixed_keywords_list();
        foreach ($fixed as $fkw) {
            $exists = false;
            foreach ($keywords as $kw) {
                if (mb_strtolower((string) $kw) === mb_strtolower($fkw)) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $keywords[] = $fkw;
            }
        }

        return [
            'id'            => $post_id,
            'type'          => $post->post_type,
            'status'        => $post->post_status,
            'title'         => get_the_title($post_id),
            'permalink'     => $this->resolve_pretty_permalink($post_id, $post),
            'slug'          => $post->post_name,
            'content'       => $post->post_content,
            'seo_title'     => $seo_title !== '' ? $seo_title : get_the_title($post_id),
            'description'   => $description !== '' ? $description : wp_trim_words(wp_strip_all_tags($post->post_content), 25, '...'),
            'focus_keyword' => $focus,
            'keywords'      => array_values($keywords),
            'canonical'     => ($canonical !== '' && preg_match('#^https?://#i', $canonical) && strpos($canonical, '?p=') === false)
                ? $canonical
                : $this->resolve_pretty_permalink($post_id, $post),
            'robots'        => array_merge([
                'index' => true, 'follow' => true, 'noarchive' => false, 'nosnippet' => false,
                'noimageindex' => false, 'max_snippet' => -1, 'max_image_preview' => 'large', 'hide_date' => false,
            ], is_array($robots) ? $robots : []),
            'og_title'      => (string) get_post_meta($post_id, self::META_OG_TITLE, true),
            'og_description'=> (string) get_post_meta($post_id, self::META_OG_DESC, true),
            'og_image'      => (string) get_post_meta($post_id, self::META_OG_IMAGE, true),
            'x_title'       => (string) get_post_meta($post_id, self::META_X_TITLE, true),
            'x_description' => (string) get_post_meta($post_id, self::META_X_DESC, true),
            'x_image'       => (string) get_post_meta($post_id, self::META_X_IMAGE, true),
            'schema'        => (string) get_post_meta($post_id, self::META_SCHEMA, true),
            'redirect'      => (string) get_post_meta($post_id, self::META_REDIRECT, true),
            'score'         => (int) get_post_meta($post_id, self::META_SCORE, true),
        ];
    }



    /**
     * Inject GA4 / GTM from saved integration settings (real IDs only).
     */
    public function render_analytics(): void
    {
        if (is_admin()) {
            return;
        }
        $si = [];
        if (function_exists('bankai_get_option')) {
            $si = bankai_get_option('seo_integrations', []);
        }
        if (!is_array($si) || empty($si)) {
            $si = get_option('bankai_seo_integrations', []);
        }
        if (!is_array($si)) {
            $si = [];
        }
        $ga4 = trim((string) ($si['ga4_measurement_id'] ?? $si['google_analytics_id'] ?? ''));
        $gtm = trim((string) ($si['google_tag_manager_id'] ?? ''));
        $gsc = trim((string) ($si['google_site_verification'] ?? ''));

        if ($gsc !== '') {
            printf(
                '<meta name="google-site-verification" content="%s" />' . "\n",
                esc_attr($gsc)
            );
        }

        if ($gtm !== '' && preg_match('/^GTM-[A-Z0-9]+$/i', $gtm)) {
            // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
            printf(
                "<!-- Bankai GTM -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>\n",
                esc_js($gtm)
            );
        }

        if ($ga4 !== '' && preg_match('/^G-[A-Z0-9]+$/i', $ga4)) {
            // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
            printf(
                "<!-- Bankai GA4 -->\n<script async src=\"https://www.googletagmanager.com/gtag/js?id=%s\"></script>\n<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','%s');</script>\n",
                esc_attr($ga4),
                esc_js($ga4)
            );
        }
    }

    public function analyze(int $post_id, array $overrides = []): array
    {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }

        $raw_content = (string) ($overrides['content'] ?? $post->post_content);
        $clean_text  = wp_strip_all_tags($raw_content);
        $title       = (string) get_the_title($post_id);
        $seo_title   = (string) ($overrides['seo_title'] ?? get_post_meta($post_id, self::META_TITLE, true));
        $description = (string) ($overrides['description'] ?? get_post_meta($post_id, self::META_DESCRIPTION, true));
        $keyword     = trim((string) ($overrides['focus_keyword'] ?? get_post_meta($post_id, self::META_KEYWORD, true)));
        $slug        = (string) $post->post_name;
        $permalink   = (string) get_permalink($post_id);

        $N = class_exists('Bankai_SEO_Text_Normalizer') ? Bankai_SEO_Text_Normalizer::class : null;

        $checks = [];
        $groups = [
            'basic'    => ['label' => 'بررسی‌های پایه سئو', 'items' => []],
            'advanced' => ['label' => 'بررسی‌های تکمیلی', 'items' => []],
            'title'    => ['label' => 'خوانایی عنوان', 'items' => []],
            'content'  => ['label' => 'خوانایی محتوا', 'items' => []],
        ];

        $has_kw = $keyword !== '';
        $word_count = $N
            ? Bankai_SEO_Text_Normalizer::word_count($clean_text)
            : count(preg_split('/\s+/u', trim($clean_text), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        $contains = static function (string $hay, string $needle) use ($N): bool {
            if ($needle === '') {
                return false;
            }
            return $N
                ? Bankai_SEO_Text_Normalizer::contains($hay, $needle)
                : (mb_stripos($hay, $needle) !== false);
        };
        $starts = static function (string $hay, string $needle) use ($N): bool {
            if ($needle === '') {
                return false;
            }
            return $N
                ? Bankai_SEO_Text_Normalizer::starts_with($hay, $needle)
                : (mb_stripos(mb_strtolower($hay), mb_strtolower($needle)) === 0);
        };
        $occ = static function (string $hay, string $needle) use ($N): int {
            if ($needle === '') {
                return 0;
            }
            return $N
                ? Bankai_SEO_Text_Normalizer::count_occurrences($hay, $needle)
                : mb_substr_count(mb_strtolower($hay), mb_strtolower($needle));
        };

        // --- Basic ---
        $checks[] = $this->check(
            'focus_keyword',
            'کلمه کلیدی اصلی تنظیم شده',
            $has_kw,
            $has_kw ? 'کلمه کلیدی اصلی تنظیم شده است.' : 'یک کلمه کلیدی اصلی برای این محتوا تنظیم کنید.',
            'basic',
            [
                'status'         => $has_kw ? 'pass' : 'fail',
                'severity'       => $has_kw ? 'info' : 'high',
                'evidence'       => $has_kw ? ['keyword' => $keyword] : ['keyword' => ''],
                'recommendation' => $has_kw ? '' : 'کلمه کلیدی اصلی را در سایدبار سئو وارد کنید.',
                'auto_fixable'  => false,
            ]
        );

        // Secondary keywords
        $sec_raw = $overrides['keywords'] ?? get_post_meta($post_id, self::META_KEYWORDS, true);
        $sec_list = [];
        if (is_array($sec_raw)) {
            $sec_list = $sec_raw;
        } elseif (is_string($sec_raw) && $sec_raw !== '') {
            $decoded = json_decode($sec_raw, true);
            $sec_list = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $sec_raw)));
        }
        $sec_list = array_values(array_filter(array_map(static function ($k) {
            return is_string($k) ? trim($k) : '';
        }, $sec_list)));
        $sec_list = array_values(array_filter($sec_list, static function ($k) use ($keyword, $N) {
            if ($k === '') {
                return false;
            }
            if ($keyword === '') {
                return true;
            }
            if ($N) {
                return Bankai_SEO_Text_Normalizer::normalize($k) !== Bankai_SEO_Text_Normalizer::normalize($keyword);
            }
            return mb_strtolower($k) !== mb_strtolower($keyword);
        }));
        $sec_in_body = 0;
        foreach ($sec_list as $sk) {
            if ($contains($clean_text, $sk) || $contains($seo_title ?: $title, $sk)) {
                $sec_in_body++;
            }
        }
        if (!$has_kw && !$sec_list) {
            $checks[] = $this->check(
                'secondary_keywords',
                'کلیدواژه‌های فرعی',
                false,
                'ابتدا کلمه کلیدی اصلی را تنظیم کنید؛ سپس ۲–۳ کلیدواژه فرعی اضافه کنید.',
                'basic',
                [
                    'status'         => 'unknown',
                    'severity'       => 'medium',
                    'evidence'       => ['secondary_count' => 0, 'in_body' => 0],
                    'recommendation' => 'پس از تعیین کلمه کلیدی اصلی، کلیدواژه‌های فرعی مرتبط اضافه کنید.',
                    'auto_fixable'  => true,
                ]
            );
        } else {
            $sec_ok = count($sec_list) >= 1 && $sec_in_body >= min(1, count($sec_list));
            $checks[] = $this->check(
                'secondary_keywords',
                'کلیدواژه‌های فرعی',
                $sec_ok,
                $sec_ok
                    ? sprintf('%d کلیدواژه فرعی و حضور در محتوا تایید شد.', count($sec_list))
                    : (count($sec_list) ? 'حداقل یک کلیدواژه فرعی را در عنوان یا متن به کار ببرید.' : '۲–۳ کلیدواژه فرعی مرتبط اضافه کنید.'),
                'basic',
                [
                    'status'         => $sec_ok ? 'pass' : 'warning',
                    'severity'       => 'medium',
                    'evidence'       => ['secondary_count' => count($sec_list), 'in_body' => $sec_in_body, 'list' => array_slice($sec_list, 0, 10)],
                    'recommendation' => $sec_ok ? '' : 'کلیدواژه‌های فرعی را در عنوان سئو یا بندهای اصلی متن بگنجانید.',
                    'auto_fixable'  => true,
                ]
            );
        }

        // KW-dependent helpers
        $kw_dep = static function (string $key, string $label, bool $ok, string $ok_msg, string $fail_msg, string $group, array $evidence = [], string $rec = '') use ($has_kw) {
            if (!$has_kw) {
                return [
                    'ok' => false,
                    'extra' => [
                        'status'         => 'unknown',
                        'severity'       => 'medium',
                        'evidence'       => array_merge(['keyword_set' => false], $evidence),
                        'recommendation' => 'ابتدا کلمه کلیدی اصلی را تنظیم کنید.',
                        'auto_fixable'  => false,
                    ],
                    'msg' => 'کلمه کلیدی اصلی تنظیم نشده؛ این معیار قابل ارزیابی نیست.',
                ];
            }
            return [
                'ok' => $ok,
                'extra' => [
                    'status'         => $ok ? 'pass' : 'fail',
                    'severity'       => $ok ? 'info' : 'high',
                    'evidence'       => array_merge(['keyword_set' => true], $evidence),
                    'recommendation' => $ok ? '' : $rec,
                    'auto_fixable'  => true,
                ],
                'msg' => $ok ? $ok_msg : $fail_msg,
            ];
        };

        $r = $kw_dep(
            'kw_in_title',
            'کلمه کلیدی در عنوان سئو',
            $contains($seo_title ?: $title, $keyword),
            'کلمه کلیدی در عنوان سئو یافت شد.',
            'کلمه کلیدی اصلی را به عنوان سئو اضافه کنید.',
            'basic',
            ['seo_title' => mb_substr($seo_title ?: $title, 0, 120)],
            'عنوان سئو را طوری بازنویسی کنید که شامل کلمه کلیدی اصلی باشد.'
        );
        $checks[] = $this->check('kw_in_title', 'کلمه کلیدی در عنوان سئو', $r['ok'], $r['msg'], 'basic', $r['extra']);

        $r = $kw_dep(
            'kw_in_desc',
            'کلمه کلیدی در توضیحات متا',
            $contains($description, $keyword),
            'کلمه کلیدی در توضیحات متا یافت شد.',
            'کلمه کلیدی اصلی را به توضیحات متا اضافه کنید.',
            'basic',
            ['description_len' => mb_strlen($description)],
            'توضیحات متا را طوری بنویسید که کلمه کلیدی به‌صورت طبیعی در آن باشد.'
        );
        $checks[] = $this->check('kw_in_desc', 'کلمه کلیدی در توضیحات متا', $r['ok'], $r['msg'], 'basic', $r['extra']);

        $url_hay = $slug . ' ' . $permalink;
        $slug_kw = $has_kw ? sanitize_title($keyword) : '';
        $kw_in_url = $has_kw && (
            ($slug_kw !== '' && (str_contains($slug, $slug_kw) || str_contains(rawurldecode($permalink), $slug_kw)))
            || $contains($url_hay, $keyword)
        );
        $r = $kw_dep(
            'kw_in_url',
            'کلمه کلیدی در URL',
            $kw_in_url,
            'نشانهٔ کلمه کلیدی در URL دیده می‌شود.',
            'از کلمه کلیدی اصلی در URL استفاده کنید.',
            'basic',
            ['slug' => $slug],
            'اسلاگ را کوتاه و مرتبط با کلمه کلیدی تنظیم کنید (برای پست منتشرشده فقط با تأیید).'
        );
        $checks[] = $this->check('kw_in_url', 'کلمه کلیدی در URL', $r['ok'], $r['msg'], 'basic', $r['extra']);

        $first_pct = mb_substr($clean_text, 0, (int) max(1, mb_strlen($clean_text) * 0.1));
        $r = $kw_dep(
            'kw_in_intro',
            'کلمه کلیدی در ۱۰٪ ابتدایی',
            $contains($first_pct, $keyword),
            'کلمه کلیدی در ابتدای محتوا یافت شد.',
            'در ابتدای محتوای خود از کلمه کلیدی اصلی استفاده کنید.',
            'basic',
            ['intro_sample' => mb_substr($first_pct, 0, 160)],
            'جملهٔ اول یا بند آغازین را با اشاره طبیعی به کلمه کلیدی بازنویسی کنید.'
        );
        $checks[] = $this->check('kw_in_intro', 'کلمه کلیدی در ۱۰٪ ابتدایی', $r['ok'], $r['msg'], 'basic', $r['extra']);

        $r = $kw_dep(
            'kw_in_content',
            'کلمه کلیدی در محتوا',
            $contains($clean_text, $keyword),
            'کلمه کلیدی در محتوا یافت شد.',
            'در محتوا از کلمه کلیدی اصلی استفاده کنید.',
            'basic',
            ['words' => $word_count],
            'کلمه کلیدی را حداقل یک‌بار به‌صورت طبیعی در بدنه متن به‌کار ببرید.'
        );
        $checks[] = $this->check('kw_in_content', 'کلمه کلیدی در محتوا', $r['ok'], $r['msg'], 'basic', $r['extra']);

        $len_ok = $word_count >= 300;
        $checks[] = $this->check(
            'content_length',
            'طول محتوا',
            $len_ok,
            sprintf('طول محتوا %d کلمه است.%s', $word_count, $len_ok ? ' عالی!' : ' حداقل ۳۰۰ کلمه پیشنهاد می‌شود.'),
            'basic',
            [
                'status'         => $len_ok ? 'pass' : 'warning',
                'severity'       => $len_ok ? 'info' : 'medium',
                'evidence'       => ['words' => $word_count, 'min' => 300],
                'recommendation' => $len_ok ? '' : 'محتوا را با جزئیات و زیرموضوع‌های مرتبط گسترش دهید.',
                'auto_fixable'  => true,
            ]
        );

        // --- Advanced ---
        preg_match_all('/<h[2-6]\b[^>]*>(.*?)<\/h[2-6]>/is', $raw_content, $headings);
        $heading_text = implode(' ', array_map('wp_strip_all_tags', $headings[1] ?? []));
        $r = $kw_dep(
            'kw_in_headings',
            'کلمه کلیدی در زیرتیترها',
            $contains($heading_text, $keyword),
            'کلمه کلیدی در زیرتیترها یافت شد.',
            'استفاده از کلمه کلیدی اصلی در زیرتیترها (H2, H3, …).',
            'advanced',
            ['heading_count' => count($headings[0] ?? [])],
            'حداقل یک زیرتیتر مرتبط با کلمه کلیدی اضافه کنید.'
        );
        $checks[] = $this->check('kw_in_headings', 'کلمه کلیدی در زیرتیترها', $r['ok'], $r['msg'], 'advanced', $r['extra']);

        preg_match_all('/<img\b[^>]*>/i', $raw_content, $images);
        $img_count = count($images[0] ?? []);
        $has_kw_alt = false;
        $alts = [];
        if ($img_count > 0) {
            foreach ($images[0] as $img) {
                if (preg_match('/alt=["\']([^"\']*)["\']/i', $img, $am)) {
                    $alts[] = $am[1];
                    if ($has_kw && $contains($am[1], $keyword)) {
                        $has_kw_alt = true;
                    }
                } else {
                    $alts[] = '';
                }
            }
        }
        if ($img_count === 0) {
            $checks[] = $this->check(
                'kw_in_alt',
                'کلیدواژه در alt تصاویر',
                false,
                'تصویری در محتوا نیست؛ این معیار فعلاً اعمال نمی‌شود.',
                'advanced',
                [
                    'status'         => 'not_applicable',
                    'severity'       => 'info',
                    'evidence'       => ['images' => 0],
                    'recommendation' => 'در صورت افزودن تصویر، alt توصیفی و مرتبط بنویسید.',
                    'auto_fixable'  => false,
                ]
            );
        } elseif (!$has_kw) {
            $checks[] = $this->check(
                'kw_in_alt',
                'کلیدواژه در alt تصاویر',
                false,
                'کلمه کلیدی تنظیم نشده؛ بررسی alt قابل ارزیابی نیست.',
                'advanced',
                [
                    'status'         => 'unknown',
                    'severity'       => 'medium',
                    'evidence'       => ['images' => $img_count],
                    'recommendation' => 'پس از تنظیم کلمه کلیدی، alt تصاویر را بررسی کنید.',
                    'auto_fixable'  => true,
                ]
            );
        } else {
            $checks[] = $this->check(
                'kw_in_alt',
                'کلیدواژه در alt تصاویر',
                $has_kw_alt,
                $has_kw_alt ? 'حداقل یک تصویر با alt مرتبط با کلمه کلیدی دارد.' : 'تصویری با کلمه کلیدی اصلی به‌عنوان alt اضافه کنید.',
                'advanced',
                [
                    'status'         => $has_kw_alt ? 'pass' : 'fail',
                    'severity'       => $has_kw_alt ? 'info' : 'medium',
                    'evidence'       => ['images' => $img_count, 'alts_sample' => array_slice($alts, 0, 5)],
                    'recommendation' => $has_kw_alt ? '' : 'برای تصویر شاخص یا یکی از تصاویر، alt طبیعی شامل موضوع/کلمه کلیدی بنویسید.',
                    'auto_fixable'  => true,
                ]
            );
        }

        $density = 0.0;
        $kw_occurrences = 0;
        if ($has_kw && $word_count > 0) {
            $kw_occurrences = $occ($clean_text, $keyword);
            $kw_words = max(1, count($N ? Bankai_SEO_Text_Normalizer::words($keyword) : (preg_split('/\s+/u', trim($keyword), -1, PREG_SPLIT_NO_EMPTY) ?: [1])));
            $density = round((($kw_occurrences * $kw_words) / $word_count) * 100, 2);
        }
        if (!$has_kw) {
            $checks[] = $this->check(
                'kw_density',
                'چگالی کلمه کلیدی',
                false,
                'ابتدا کلمه کلیدی اصلی را تنظیم کنید.',
                'advanced',
                [
                    'status'         => 'unknown',
                    'severity'       => 'medium',
                    'evidence'       => ['density' => 0, 'occurrences' => 0],
                    'recommendation' => 'پس از تنظیم کلمه کلیدی، چگالی را در محدوده ۰٫۵–۲٫۵٪ نگه دارید.',
                    'auto_fixable'  => false,
                ]
            );
        } else {
            $den_ok = $density >= 0.5 && $density <= 2.5;
            $checks[] = $this->check(
                'kw_density',
                'چگالی کلمه کلیدی',
                $den_ok,
                sprintf('چگالی کلمه کلیدی %.2f٪ است (%d بار در متن). هدف حدود ۰٫۵–۲٫۵٪.', $density, $kw_occurrences),
                'advanced',
                [
                    'status'         => $den_ok ? 'pass' : 'warning',
                    'severity'       => 'medium',
                    'evidence'       => ['density' => $density, 'occurrences' => $kw_occurrences, 'words' => $word_count],
                    'recommendation' => $den_ok ? '' : ($density < 0.5 ? 'چند اشاره طبیعی بیشتر به موضوع در متن بگنجانید.' : 'تکرار کلمه کلیدی را کاهش دهید تا متن طبیعی بماند.'),
                    'auto_fixable'  => true,
                ]
            );
        }

        $url_path = (string) (wp_parse_url($permalink, PHP_URL_PATH) ?: $permalink);
        $url_len  = mb_strlen(rawurldecode($url_path));
        $url_ok   = $url_len <= 100;
        $checks[] = $this->check(
            'url_length',
            'طول URL',
            $url_ok,
            sprintf('مسیر آدرس %d کاراکتر است.%s', $url_len, $url_ok ? ' مناسب است.' : ' کوتاه‌تر پیشنهاد می‌شود (زیر ۱۰۰ کاراکتر مسیر).'),
            'advanced',
            [
                'status'         => $url_ok ? 'pass' : 'warning',
                'severity'       => 'low',
                'evidence'       => ['path_length' => $url_len, 'path' => mb_substr($url_path, 0, 120)],
                'recommendation' => $url_ok ? '' : 'اسلاگ کوتاه‌تری انتخاب کنید (برای پست منتشرشده با احتیاط).',
                'auto_fixable'  => false,
            ]
        );

        $extracted_links = $this->extract_links_from_html($raw_content);
        $internal_seo = array_values(array_filter(
            $extracted_links['internal'],
            static function ($item) {
                $h = trim((string) ($item['href'] ?? ''));
                return $h !== '' && !preg_match('/^#/', $h);
            }
        ));
        $internal = count($internal_seo);
        $external = count($extracted_links['external']);
        $checks[] = $this->check(
            'internal_links',
            'لینک داخلی',
            $internal >= 1,
            $internal > 0 ? sprintf('%d لینک داخلی یافت شد.', $internal) : 'لینک داخلی پیدا نشد. به صفحات مرتبط لینک دهید.',
            'advanced',
            [
                'status'         => $internal >= 1 ? 'pass' : 'fail',
                'severity'       => 'high',
                'evidence'       => ['internal' => $internal],
                'recommendation' => $internal >= 1 ? '' : 'حداقل به یک مقاله یا صفحه مرتبط داخلی لینک دهید.',
                'auto_fixable'  => true,
            ]
        );
        $checks[] = $this->check(
            'external_links',
            'لینک خارجی',
            $external >= 1,
            $external > 0 ? sprintf('%d لینک خارجی یافت شد.', $external) : 'هیچ پیوند خروجی یافت نشد. به منابع خارجی پیوند دهید.',
            'advanced',
            [
                'status'         => $external >= 1 ? 'pass' : 'warning',
                'severity'       => 'medium',
                'evidence'       => ['external' => $external],
                'recommendation' => $external >= 1 ? '' : 'در صورت وجود منبع معتبر، یک لینک خارجی مرتبط اضافه کنید.',
                'auto_fixable'  => false,
            ]
        );

        // --- Title readability ---
        $title_use = $seo_title !== '' ? $seo_title : $title;
        $r = $kw_dep(
            'kw_start_title',
            'کلیدواژه در ابتدای عنوان',
            $starts($title_use, $keyword),
            'کلمه کلیدی در ابتدای عنوان سئو قرار دارد.',
            'از کلمه کلیدی اصلی در ابتدای عنوان سئو استفاده کنید.',
            'title',
            ['title' => mb_substr($title_use, 0, 120)],
            'عنوان سئو را با کلمه کلیدی شروع کنید اگر طبیعی است.'
        );
        $checks[] = $this->check('kw_start_title', 'کلیدواژه در ابتدای عنوان', $r['ok'], $r['msg'], 'title', $r['extra']);

        $title_len = mb_strlen($title_use);
        $title_len_ok = $title_len >= 30 && $title_len <= 60;
        $checks[] = $this->check(
            'title_length',
            'طول عنوان سئو',
            $title_len_ok,
            sprintf('طول عنوان: %d کاراکتر (هدف ۳۰–۶۰).', $title_len),
            'title',
            [
                'status'         => $title_len_ok ? 'pass' : 'warning',
                'severity'       => 'medium',
                'evidence'       => ['length' => $title_len],
                'recommendation' => $title_len_ok ? '' : ($title_len < 30 ? 'عنوان را کمی کامل‌تر کنید.' : 'عنوان را کوتاه‌تر کنید.'),
                'auto_fixable'  => true,
            ]
        );

        $has_num = (bool) preg_match('/\d/u', $title_use);
        $checks[] = $this->check(
            'title_has_number',
            'عدد در عنوان',
            $has_num,
            $has_num ? 'عنوان شامل عدد است؛ جذابیت بیشتری دارد.' : 'پیشنهاد: افزودن عدد به عنوان (مثلاً «۵ روش…») در صورت تناسب با موضوع.',
            'title',
            [
                'status'         => $has_num ? 'pass' : 'warning',
                'severity'       => 'low',
                'evidence'       => ['has_number' => $has_num, 'title' => mb_substr($title_use, 0, 120)],
                'recommendation' => $has_num ? '' : 'فقط اگر با موضوع سازگار است عدد اضافه کنید؛ اجباری نیست.',
                'auto_fixable'  => false,
            ]
        );

        // --- Content readability ---
        $desc_len = mb_strlen($description);
        if ($description === '') {
            $checks[] = $this->check(
                'desc_length',
                'طول توضیحات متا',
                false,
                'توضیحات متا خالی است.',
                'content',
                [
                    'status'         => 'fail',
                    'severity'       => 'high',
                    'evidence'       => ['length' => 0],
                    'recommendation' => 'یک Meta Description بین ۱۲۰ تا ۱۶۰ کاراکتر بنویسید.',
                    'auto_fixable'  => true,
                ]
            );
        } else {
            $desc_ok = $desc_len >= 120 && $desc_len <= 160;
            $checks[] = $this->check(
                'desc_length',
                'طول توضیحات متا',
                $desc_ok,
                sprintf('طول توضیحات: %d کاراکتر (هدف ۱۲۰–۱۶۰).', $desc_len),
                'content',
                [
                    'status'         => $desc_ok ? 'pass' : 'warning',
                    'severity'       => 'medium',
                    'evidence'       => ['length' => $desc_len],
                    'recommendation' => $desc_ok ? '' : ($desc_len < 120 ? 'توضیحات را کمی کامل‌تر کنید.' : 'توضیحات را کوتاه‌تر کنید.'),
                    'auto_fixable'  => true,
                ]
            );
        }

        preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $raw_content, $paras);
        $long_para = false;
        foreach ($paras[1] ?? [] as $p) {
            $pw = $N ? Bankai_SEO_Text_Normalizer::word_count(wp_strip_all_tags($p)) : count(preg_split('/\s+/u', trim(wp_strip_all_tags($p)), -1, PREG_SPLIT_NO_EMPTY) ?: []);
            if ($pw > 120) {
                $long_para = true;
                break;
            }
        }
        // If no <p> tags but long plain text, still evaluate
        if (!$paras[1] && $word_count > 120) {
            // treat as single block — warning only if huge
            $long_para = $word_count > 400 && substr_count($raw_content, "\n") < 3;
        }
        $checks[] = $this->check(
            'short_paragraphs',
            'پاراگراف‌های کوتاه',
            !$long_para,
            $long_para ? 'حداقل یک پاراگراف طولانی است. پاراگراف‌های کوتاه پیشنهاد می‌شود.' : 'طول پاراگراف‌ها مناسب است.',
            'content',
            [
                'status'         => !$long_para ? 'pass' : 'warning',
                'severity'       => 'low',
                'evidence'       => ['paragraph_tags' => count($paras[1] ?? [])],
                'recommendation' => $long_para ? 'پاراگراف‌های بلند را به چند بند کوتاه‌تر تقسیم کنید.' : '',
                'auto_fixable'  => true,
            ]
        );

        $has_list = (bool) preg_match('/<(ul|ol)\b/i', $raw_content);
        // Lists are recommended for long how-to content; N/A for very short posts
        if ($word_count < 150) {
            $checks[] = $this->check(
                'has_lists',
                'استفاده از فهرست',
                false,
                'محتوای کوتاه است؛ فهرست الزامی نیست.',
                'content',
                [
                    'status'         => 'not_applicable',
                    'severity'       => 'info',
                    'evidence'       => ['words' => $word_count, 'has_list' => $has_list],
                    'recommendation' => '',
                    'auto_fixable'  => false,
                ]
            );
        } else {
            $checks[] = $this->check(
                'has_lists',
                'استفاده از فهرست',
                $has_list,
                $has_list ? 'فهرست (لیست) در محتوا وجود دارد.' : 'برای خوانایی بهتر از لیست شماره‌دار یا نقطه‌ای استفاده کنید.',
                'content',
                [
                    'status'         => $has_list ? 'pass' : 'warning',
                    'severity'       => 'low',
                    'evidence'       => ['has_list' => $has_list],
                    'recommendation' => $has_list ? '' : 'در صورت مناسب بودن موضوع، یک فهرست مراحل یا نکات اضافه کنید.',
                    'auto_fixable'  => true,
                ]
            );
        }

        // Sentence length — Persian punctuation included
        $sentences = preg_split('/(?<=[.!?…۔؟!])\s+/u', $clean_text, -1, PREG_SPLIT_NO_EMPTY);
        $long_sent = 0;
        $sent_count = is_array($sentences) ? count($sentences) : 0;
        if (is_array($sentences)) {
            foreach ($sentences as $s) {
                $sw = $N ? Bankai_SEO_Text_Normalizer::word_count($s) : count(preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY) ?: []);
                if ($sw > 40) {
                    $long_sent++;
                }
            }
        }
        $sent_ok = $sent_count === 0 || ($long_sent / max(1, $sent_count)) < 0.35;
        $checks[] = $this->check(
            'sentence_length',
            'طول جملات',
            $sent_ok,
            $sent_ok ? 'طول جملات در محدوده مناسب است.' : sprintf('%d جمله بسیار طولانی است؛ کوتاه‌تر بنویسید.', $long_sent),
            'content',
            [
                'status'         => $sent_ok ? 'pass' : 'warning',
                'severity'       => 'low',
                'evidence'       => ['sentences' => $sent_count, 'long_sentences' => $long_sent],
                'recommendation' => $sent_ok ? '' : 'جملات بلند را به دو یا چند جمله کوتاه‌تر بشکنید.',
                'auto_fixable'  => true,
            ]
        );

        $h_count = count($headings[0] ?? []);
        $need_h = $word_count > 300 ? max(1, (int) floor($word_count / 350)) : 0;
        if ($need_h === 0) {
            $checks[] = $this->check(
                'heading_ratio',
                'نسبت زیرتیتر به طول متن',
                false,
                'برای متن کوتاه‌تر از ۳۰۰ کلمه، نسبت زیرتیتر الزامی نیست.',
                'content',
                [
                    'status'         => 'not_applicable',
                    'severity'       => 'info',
                    'evidence'       => ['headings' => $h_count, 'words' => $word_count],
                    'recommendation' => '',
                    'auto_fixable'  => false,
                ]
            );
        } else {
            $h_ok = $h_count >= $need_h;
            $checks[] = $this->check(
                'heading_ratio',
                'نسبت زیرتیتر به طول متن',
                $h_ok,
                $h_ok ? 'تعداد زیرتیترها با طول محتوا هماهنگ است.' : sprintf('برای %d کلمه حدود %d زیرتیتر پیشنهاد می‌شود (الان: %d).', $word_count, $need_h, $h_count),
                'content',
                [
                    'status'         => $h_ok ? 'pass' : 'warning',
                    'severity'       => 'medium',
                    'evidence'       => ['headings' => $h_count, 'needed' => $need_h, 'words' => $word_count],
                    'recommendation' => $h_ok ? '' : 'زیرتیترهای H2/H3 مرتبط اضافه کنید.',
                    'auto_fixable'  => true,
                ]
            );
        }

        // Unique title/slug
        $dup_msg = 'عنوان و آدرس یکتا هستند.';
        $dup_ok = true;
        if ($slug !== '') {
            $by_slug = get_posts([
                'name'           => $slug,
                'post_type'      => $post->post_type,
                'post_status'    => ['publish', 'draft', 'pending', 'future'],
                'exclude'        => [$post_id],
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);
            if (!empty($by_slug)) {
                $dup_ok = false;
                $dup_msg = 'اسلاگ تکراری با پست دیگری وجود دارد.';
            }
        }
        if ($dup_ok && $title !== '') {
            $title_hits = get_posts([
                's'              => $title,
                'post_type'      => $post->post_type,
                'post_status'    => 'publish',
                'exclude'        => [$post_id],
                'posts_per_page' => 8,
                'fields'         => 'ids',
            ]);
            foreach ($title_hits as $hid) {
                $other = get_the_title((int) $hid);
                $same = $N
                    ? (Bankai_SEO_Text_Normalizer::normalize($other) === Bankai_SEO_Text_Normalizer::normalize($title))
                    : (mb_strtolower($other) === mb_strtolower($title));
                if ($same) {
                    $dup_ok = false;
                    $dup_msg = 'عنوان دقیقاً با پست دیگری یکسان است.';
                    break;
                }
            }
        }
        $checks[] = $this->check(
            'unique_title',
            'یکتایی عنوان/اسلاگ',
            $dup_ok,
            $dup_msg,
            'advanced',
            [
                'status'         => $dup_ok ? 'pass' : 'fail',
                'severity'       => 'high',
                'evidence'       => ['slug' => $slug, 'title' => mb_substr($title, 0, 120)],
                'recommendation' => $dup_ok ? '' : 'عنوان یا اسلاگ را متمایز کنید.',
                'auto_fixable'  => false,
            ]
        );

        $has_media = $img_count > 0 || (bool) preg_match('/<(video|iframe|embed)\b/i', $raw_content);
        $checks[] = $this->check(
            'rich_media',
            'رسانه غنی',
            $has_media,
            $has_media ? 'رسانه در محتوا وجود دارد.' : 'از تصاویر یا ویدئو استفاده کنید.',
            'content',
            [
                'status'         => $has_media ? 'pass' : 'warning',
                'severity'       => 'medium',
                'evidence'       => ['images' => $img_count, 'has_media' => $has_media],
                'recommendation' => $has_media ? '' : 'حداقل یک تصویر مرتبط اضافه کنید.',
                'auto_fixable'  => true,
            ]
        );

        foreach ($checks as $c) {
            $g = $c['group'] ?? 'basic';
            if (!isset($groups[$g])) {
                $g = 'basic';
            }
            $groups[$g]['items'][] = $c;
        }

        // Scoring (Phase B):
        // - Exclude not_applicable from denominator (does not unfairly lower score)
        // - unknown is NOT counted as pass
        // - pass → passed; warning/fail/unknown → not passed
        // Formula documented: score = round(pass_count / scored_total * 100)
        // Legacy fields: passed = pass_count, total = scored_total (eligible)
        $pass_count = 0;
        $scored_total = 0;
        foreach ($checks as $c) {
            $st = (string) ($c['status'] ?? '');
            if ($st === 'not_applicable') {
                continue;
            }
            $scored_total++;
            if ($st === 'pass') {
                $pass_count++;
            }
        }
        $score = $scored_total > 0 ? (int) round(($pass_count / $scored_total) * 100) : 0;

        $seo_pass = 0; $seo_total = 0;
        $aeo_pass = 0; $aeo_total = 0;
        $geo_pass = 0; $geo_total = 0;

        foreach ($checks as $c) {
            $st = (string) ($c['status'] ?? '');
            if ($st === 'not_applicable') {
                continue;
            }
            $g = (string) ($c['group'] ?? 'basic');
            $k = (string) ($c['key'] ?? '');
            $is_pass = ($st === 'pass');

            if (in_array($g, ['basic', 'title'], true) || str_contains($k, 'kw') || str_contains($k, 'desc') || str_contains($k, 'url') || str_contains($k, 'title')) {
                $seo_total++;
                if ($is_pass) $seo_pass++;
            }
            if ($g === 'content' || str_contains($k, 'list') || str_contains($k, 'heading') || str_contains($k, 'para') || str_contains($k, 'sentence') || str_contains($k, 'faq')) {
                $aeo_total++;
                if ($is_pass) $aeo_pass++;
            }
            if ($g === 'advanced' || str_contains($k, 'media') || str_contains($k, 'unique') || str_contains($k, 'density') || str_contains($k, 'length')) {
                $geo_total++;
                if ($is_pass) $geo_pass++;
            }
        }

        $seo_sub = $seo_total > 0 ? (int) round(($seo_pass / $seo_total) * 100) : $score;
        $aeo_sub = $aeo_total > 0 ? (int) round(($aeo_pass / $aeo_total) * 100) : max(0, $score - 5);
        $geo_sub = $geo_total > 0 ? (int) round(($geo_pass / $geo_total) * 100) : (int) round($score * 0.9);

        return [
            'score'           => $score,
            'sub_scores'      => [
                'seo' => min(100, max(0, $seo_sub)),
                'aeo' => min(100, max(0, $aeo_sub)),
                'geo' => min(100, max(0, $geo_sub)),
            ],
            'passed'          => $pass_count,
            'total'           => $scored_total,
            'checks'          => $checks,
            'groups'          => $groups,
            'scoring_version' => 'b1',
            'stats'           => [
                'words'       => $word_count,
                'description' => $desc_len,
                'images'      => $img_count,
                'links'       => $internal + $external,
                'internal'    => $internal,
                'external'    => $external,
                'headings'    => count($headings[0] ?? []),
                'density'     => $density,
            ],
        ];
    }

    /**
     * Build a checklist item. Keeps legacy `passed` for UI/REST consumers.
     *
     * @param array<string,mixed> $extra status, severity, evidence, recommendation, auto_fixable
     * @return array<string,mixed>
     */
    private function check(string $key, string $label, bool $passed, string $message, string $group = 'basic', array $extra = []): array
    {
        $status = (string) ($extra['status'] ?? ($passed ? 'pass' : 'fail'));
        // Align passed with status when status is authoritative
        if ($status === 'pass') {
            $passed = true;
        } elseif (in_array($status, ['fail', 'warning', 'unknown', 'not_applicable'], true)) {
            // not_applicable: passed=false but excluded from score
            $passed = false;
        }

        return [
            'key'            => $key,
            'id'             => $key,
            'label'          => $label,
            'passed'         => $passed,
            'status'         => $status,
            'severity'       => (string) ($extra['severity'] ?? ($passed ? 'info' : 'medium')),
            'message'        => $message,
            'evidence'       => is_array($extra['evidence'] ?? null) ? $extra['evidence'] : [],
            'recommendation' => (string) ($extra['recommendation'] ?? ''),
            'auto_fixable'  => (bool) ($extra['auto_fixable'] ?? false),
            'group'          => $group,
        ];
    }


    public function quick_related_posts(int $post_id, string $keyword, int $limit = 5): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }
        $q = new \WP_Query([
            'post_type'              => ['post', 'page'],
            'post_status'            => 'publish',
            's'                      => $keyword,
            'post__not_in'           => [$post_id],
            'posts_per_page'         => $limit,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);
        $out = [];
        foreach ($q->posts as $p) {
            $out[] = [
                'id'        => (int) $p->ID,
                'title'     => get_the_title($p),
                'permalink' => get_permalink($p),
            ];
        }
        return $out;
    }

    public function handle_post_redirect(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }
        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }
        $target = trim((string) get_post_meta($post_id, self::META_REDIRECT, true));
        if ($target === '' || !preg_match('#^https?://#i', $target)) {
            return;
        }
        $current = get_permalink($post_id);
        if ($current && untrailingslashit($current) === untrailingslashit($target)) {
            return;
        }
        wp_safe_redirect($target, 301);
        exit;
    }

    /**
     * BreadcrumbList JSON-LD for current singular content.
     */
    public function build_breadcrumb_schema(int $post_id): array
    {
        $items = [];
        $pos = 1;
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_bloginfo('name'),
            'item'     => home_url('/'),
        ];
        $cats = get_the_category($post_id);
        if (!empty($cats) && !is_wp_error($cats)) {
            $cat = $cats[0];
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $cat->name,
                'item'     => get_category_link($cat->term_id),
            ];
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos,
            'name'     => get_the_title($post_id),
            'item'     => get_permalink($post_id),
        ];
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public function filter_document_title(string $title): string
    {
        if (!is_singular()) {
            return $title;
        }
        $post_id = get_queried_object_id();
        $custom  = (string) get_post_meta($post_id, self::META_TITLE, true);
        return $custom !== '' ? $custom : $title;
    }

    public function render_meta(): void
    {
        if (!is_singular()) {
            return;
        }
        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }
        $data = $this->get_seo_data($post_id);
        if (!$data) {
            return;
        }

        $robots      = is_array($data['robots'] ?? null) ? $data['robots'] : [];
        $robot_parts = [
            !empty($robots['index']) ? 'index' : 'noindex',
            !empty($robots['follow']) ? 'follow' : 'nofollow',
        ];
        if (!empty($robots['noarchive'])) {
            $robot_parts[] = 'noarchive';
        }
        if (!empty($robots['nosnippet'])) {
            $robot_parts[] = 'nosnippet';
        }
        if (!empty($robots['noimageindex'])) {
            $robot_parts[] = 'noimageindex';
        }
        if (isset($robots['max_snippet']) && (int) $robots['max_snippet'] >= 0) {
            $robot_parts[] = 'max-snippet:' . (int) $robots['max_snippet'];
        }
        $mip = $robots['max_image_preview'] ?? 'large';
        if (in_array($mip, ['none', 'standard', 'large'], true)) {
            $robot_parts[] = 'max-image-preview:' . $mip;
        }
        
        printf('<meta name="robots" content="%s">' . "\n", esc_attr(implode(', ', $robot_parts)));

        if (!empty($data['description'])) {
            printf('<meta name="description" content="%s">' . "\n", esc_attr($data['description']));
        }

        printf('<link rel="canonical" href="%s">' . "\n", esc_url($data['canonical']));

        $og_title = $data['og_title'] ?: $data['seo_title'];
        $og_desc  = $data['og_description'] ?: $data['description'];
        $og_image = $data['og_image'] ?: get_the_post_thumbnail_url($post_id, 'full');

        echo '<meta property="og:type" content="article">' . "\n";
        printf('<meta property="og:title" content="%s">' . "\n", esc_attr($og_title));
        printf('<meta property="og:description" content="%s">' . "\n", esc_attr($og_desc));
        printf('<meta property="og:url" content="%s">' . "\n", esc_url($data['permalink']));
        if ($og_image) {
            printf('<meta property="og:image" content="%s">' . "\n", esc_url($og_image));
        }

        $x_title = $data['x_title'] ?: $og_title;
        $x_desc  = $data['x_description'] ?: $og_desc;
        $x_image = $data['x_image'] ?: $og_image;

        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        printf('<meta name="twitter:title" content="%s">' . "\n", esc_attr($x_title));
        printf('<meta name="twitter:description" content="%s">' . "\n", esc_attr($x_desc));
        if ($x_image) {
            printf('<meta name="twitter:image" content="%s">' . "\n", esc_url($x_image));
        }
    }

    public function render_schema(): void
    {
        if (!is_singular()) {
            return;
        }
        $post_id = get_queried_object_id();
        $post    = get_post($post_id);
        if (!$post) {
            return;
        }

        $nodes = [];

        $custom = (string) get_post_meta($post_id, self::META_SCHEMA, true);
        if ($custom !== '') {
            $decoded = json_decode($custom, true);
            if (is_array($decoded)) {
                // @graph or single node
                if (isset($decoded['@graph']) && is_array($decoded['@graph'])) {
                    foreach ($decoded['@graph'] as $node) {
                        if (is_array($node)) {
                            $nodes[] = $node;
                        }
                    }
                } else {
                    $nodes[] = $decoded;
                }
            }
        }

        if (!$nodes) {
            $nodes[] = $this->build_default_article_node($post);
        }

        // تکمیل فیلدهای اجباری Article برای تمام نودها
        $nodes = array_map(function ($n) use ($post) {
            return $this->enrich_article_node(is_array($n) ? $n : [], $post);
        }, $nodes);

        // Always attach BreadcrumbList if not already present
        $has_bc = false;
        foreach ($nodes as $n) {
            $type = $n['@type'] ?? '';
            if ($type === 'BreadcrumbList' || (is_array($type) && in_array('BreadcrumbList', $type, true))) {
                $has_bc = true;
                break;
            }
        }
        if (!$has_bc) {
            $bc = $this->build_breadcrumb_schema((int) $post->ID);
            unset($bc['@context']);
            $nodes[] = $bc;
        }

        // یک بلوک JSON-LD واحد (@graph) برای جلوگیری از اسکیمای پراکنده
        $payload = [
            '@context' => 'https://schema.org',
            '@graph'   => array_values(array_filter($nodes)),
        ];

        echo '<script type="application/ld+json">'
            . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }

    private function build_default_article_node(\WP_Post $post): array
    {
        $post_id = (int) $post->ID;
        $node = [
            '@type'            => $post->post_type === 'post' ? 'Article' : 'WebPage',
            '@id'              => get_permalink($post_id) . '#article',
            'headline'         => get_the_title($post_id),
            'description'      => wp_strip_all_tags(get_the_excerpt($post_id)),
            'url'              => get_permalink($post_id),
            'mainEntityOfPage' => get_permalink($post_id),
            'datePublished'    => get_the_date(DATE_W3C, $post_id),
            'dateModified'     => get_the_modified_date(DATE_W3C, $post_id),
            'author'           => [
                '@type' => 'Person',
                'name'  => get_the_author_meta('display_name', (int) $post->post_author),
                'url'   => get_author_posts_url((int) $post->post_author),
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
                'url'   => home_url('/'),
            ],
        ];
        $logo = $this->get_site_logo_url();
        if ($logo) {
            $node['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url'   => $logo,
            ];
        }
        $image = get_the_post_thumbnail_url($post_id, 'full');
        if ($image) {
            $node['image'] = [$image];
        }
        return $node;
    }

    private function enrich_article_node(array $n, \WP_Post $post): array
    {
        $type = $n['@type'] ?? '';
        $is_article = $type === 'Article' || $type === 'BlogPosting' || $type === 'NewsArticle'
            || (is_array($type) && array_intersect($type, ['Article', 'BlogPosting', 'NewsArticle']));
        if (!$is_article) {
            return $n;
        }
        $post_id = (int) $post->ID;
        if (empty($n['datePublished'])) {
            $n['datePublished'] = get_the_date(DATE_W3C, $post_id);
        }
        if (empty($n['dateModified'])) {
            $n['dateModified'] = get_the_modified_date(DATE_W3C, $post_id);
        }
        if (empty($n['author'])) {
            $n['author'] = [
                '@type' => 'Person',
                'name'  => get_the_author_meta('display_name', (int) $post->post_author),
            ];
        } elseif (is_array($n['author']) && empty($n['author']['name']) && empty($n['author']['@type'])) {
            $n['author'] = [
                '@type' => 'Person',
                'name'  => is_string($n['author']) ? $n['author'] : get_the_author_meta('display_name', (int) $post->post_author),
            ];
        }
        if (empty($n['headline'])) {
            $n['headline'] = get_the_title($post_id);
        }
        if (empty($n['image'])) {
            $img = get_the_post_thumbnail_url($post_id, 'full');
            if ($img) {
                $n['image'] = [$img];
            }
        }
        return $n;
    }

    private function get_site_logo_url(): string
    {
        $custom = (int) get_theme_mod('custom_logo');
        if ($custom) {
            $url = wp_get_attachment_image_url($custom, 'full');
            if ($url) {
                return $url;
            }
        }
        $icon = (int) get_option('site_icon');
        if ($icon) {
            $url = wp_get_attachment_image_url($icon, 'full');
            if ($url) {
                return $url;
            }
        }
        return '';
    }

    /** جلوگیری از تگ‌های تکراری Rank Math / Yoast روی فرانت */
    public function suppress_competing_seo(): void
    {
        // Yoast
        if (defined('WPSEO_VERSION')) {
            add_filter('wpseo_frontend_presenter_classes', static fn() => []);
            add_filter('wpseo_meta_description', '__return_false', 100);
            add_filter('wpseo_canonical', '__return_false', 100);
            add_filter('wpseo_json_ld_output', '__return_false', 100);
            add_filter('wpseo_opengraph_title', '__return_false', 100);
        }
        // Rank Math
        if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
            add_filter('rank_math/frontend/disable_adjacent_rel_links', '__return_true');
            remove_all_actions('rank_math/head');
            add_filter('rank_math/json_ld', static fn() => [], 99);
            add_filter('rank_math/frontend/description', '__return_false', 99);
            add_filter('rank_math/frontend/canonical', '__return_false', 99);
        }
    }

    public function send_security_headers(): void
    {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
            // HSTS فقط روی HTTPS
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        }
    }

    public function filter_noopener_links(string $content): string
    {
        if ($content === '' || stripos($content, 'target=') === false) {
            return $content;
        }
        return preg_replace_callback(
            '/<a\b([^>]*target=["\']_blank["\'][^>]*)>/i',
            static function ($m) {
                $attrs = $m[1];
                if (preg_match('/\brel=/i', $attrs)) {
                    $attrs = preg_replace_callback(
                        '/\brel=(["\'])(.*?)\1/i',
                        static function ($rm) {
                            $rels = preg_split('/\s+/', trim($rm[2]));
                            foreach (['noopener', 'noreferrer'] as $need) {
                                if (!in_array($need, $rels, true)) {
                                    $rels[] = $need;
                                }
                            }
                            return 'rel=' . $rm[1] . implode(' ', $rels) . $rm[1];
                        },
                        $attrs,
                        1
                    );
                } else {
                    $attrs .= ' rel="noopener noreferrer"';
                }
                return '<a' . $attrs . '>';
            },
            $content
        ) ?: $content;
    }

    public function register_web_manifest(): void
    {
        add_rewrite_rule('^manifest\.webmanifest$', 'index.php?bankai_manifest=1', 'top');
        add_filter('query_vars', static function ($v) {
            $v[] = 'bankai_manifest';
            return $v;
        });
        add_action('wp_head', static function () {
            echo '<link rel="manifest" href="' . esc_url(home_url('/manifest.webmanifest')) . '">' . "\n";
        }, 4);
    }

    public function serve_web_manifest(): void
    {
        if ((int) get_query_var('bankai_manifest') !== 1) {
            // path fallback
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (!preg_match('#/manifest\.webmanifest/?$#', (string) $uri)) {
                return;
            }
        }
        $name = get_bloginfo('name') ?: 'Site';
        $desc = get_bloginfo('description') ?: $name;
        $icon = $this->get_site_logo_url();
        $icons = [];
        if ($icon) {
            $icons[] = [
                'src'   => $icon,
                'sizes' => '512x512',
                'type'  => 'image/png',
                'purpose' => 'any maskable',
            ];
        }
        $manifest = [
            'name'             => $name,
            'short_name'       => mb_substr($name, 0, 12),
            'description'      => $desc,
            'start_url'        => home_url('/'),
            'scope'            => home_url('/'),
            'display'          => 'standalone',
            'background_color' => '#ffffff',
            'theme_color'      => '#2563eb',
            'lang'             => get_bloginfo('language') ?: 'fa',
            'dir'              => is_rtl() ? 'rtl' : 'ltr',
            'icons'            => $icons,
        ];
        nocache_headers();
        header('Content-Type: application/manifest+json; charset=utf-8');
        echo wp_json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /** placeholder guard — meta description فقط از render_meta */
    public function ensure_single_description_guard($tags)
    {
        return $tags;
    }


        public function api_articles_list(WP_REST_Request $request): WP_REST_Response
    {
        $page     = max(1, absint($request->get_param('page') ?: 1));
        $per_page = min(100, max(5, absint($request->get_param('per_page') ?: 25)));
        $search   = sanitize_text_field($request->get_param('search') ?? '');
        $orderby  = sanitize_key($request->get_param('orderby') ?? 'modified');
        $order    = strtoupper(sanitize_text_field($request->get_param('order') ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $args = [
            'post_type'              => ['post', 'page'],
            'post_status'            => ['publish', 'draft', 'pending', 'future', 'private'],
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'order'                  => $order,
            'ignore_sticky_posts'    => true,
            'suppress_filters'       => false,
        ];

        if ($search !== '') {
            $args['s'] = $search;
        }

        switch ($orderby) {
            case 'seo_score':
                $args['meta_key'] = self::META_SCORE;
                $args['orderby']  = 'meta_value_num';
                break;
            case 'views':
                $args['meta_key'] = self::META_VIEWS;
                $args['orderby']  = 'meta_value_num';
                break;
            case 'title':
            case 'date':
                $args['orderby'] = $orderby;
                break;
            default:
                $args['orderby'] = 'modified';
                break;
        }

        $q        = new WP_Query($args);
        $items    = [];
        $post_ids = wp_list_pluck($q->posts, 'ID');
        $view_map = $this->get_views_map($post_ids);
        $fixed    = $this->get_fixed_keywords_list();

        foreach ($q->posts as $p) {
            $id        = $p->ID;
            $extracted = $this->extract_links_from_html($p->post_content);

            $kw_raw   = (string) get_post_meta($id, self::META_KEYWORDS, true);
            $keywords = [];
            if ($kw_raw !== '') {
                $decoded  = json_decode($kw_raw, true);
                $keywords = is_array($decoded) ? $decoded : [];
            }

            $merged_keywords = $this->merge_unique_keywords(array_merge($keywords, $fixed));

            $items[] = [
                'id'             => $id,
                'title'          => get_the_title($p),
                'status'         => $p->post_status,
                'type'           => $p->post_type,
                'permalink'      => get_permalink($p),
                'edit_url'       => get_edit_post_link($id, 'raw'),
                'seo_title'      => (string) get_post_meta($id, self::META_TITLE, true),
                'description'    => (string) get_post_meta($id, self::META_DESCRIPTION, true),
                'focus_keyword'  => (string) get_post_meta($id, self::META_KEYWORD, true),
                'keywords'       => $merged_keywords,
                'score'          => (int) get_post_meta($id, self::META_SCORE, true),
                'views'          => (int) ($view_map[$id] ?? get_post_meta($id, self::META_VIEWS, true)),
                'internal_links' => count($extracted['internal']),
                'external_links' => count($extracted['external']),
                'seo_audit'      => (int) get_post_meta($id, '_bankai_audit_seo', true),
                'aeo_audit'      => (int) get_post_meta($id, '_bankai_audit_aeo', true),
                'geo_audit'      => (int) get_post_meta($id, '_bankai_audit_geo', true),
                'audit_score'    => (int) get_post_meta($id, '_bankai_audit_score', true),
                'cover'          => get_the_post_thumbnail_url($id, 'thumbnail') ?: '',
                'modified'       => get_the_modified_date('Y-m-d H:i', $p),
                'date'           => get_the_date('Y-m-d H:i', $p),
                'status_label'   => get_post_status_object($p->post_status)->label ?? $p->post_status,
            ];
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'items'       => $items,
                'total'       => (int) $q->found_posts,
                'total_pages' => (int) $q->max_num_pages,
                'page'        => $page,
                'per_page'    => $per_page,
                'orderby'     => $orderby,
                'order'       => $order,
            ],
        ]);
    }

    public function api_get_fixed_keywords(): WP_REST_Response
    {
        $list = $this->get_fixed_keywords_list();
        return new WP_REST_Response([
            'success' => true,
            'data'    => ['keywords' => $list, 'raw' => implode('، ', $list)],
        ]);
    }

    public function api_save_fixed_keywords(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params() ?: [];
        $raw    = $params['keywords'] ?? $params['raw'] ?? '';

        if (is_array($raw)) {
            $list = array_values(array_filter(array_map('sanitize_text_field', $raw)));
        } else {
            $parts = preg_split('/[,،\n]+/u', (string) $raw);
            $list  = [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $list[] = sanitize_text_field($p);
                }
            }
        }

        $list = $this->merge_unique_keywords($list);

        if (function_exists('bankai_update_option')) {
            bankai_update_option('seo_fixed_keywords', implode('، ', $list));
        } else {
            update_option('bankai_seo_fixed_keywords', implode('، ', $list), false);
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => ['keywords' => $list],
            'message' => 'ذخیره شد',
        ]);
    }

    public function get_fixed_keywords_list(): array
    {
        $raw = function_exists('bankai_get_option')
            ? bankai_get_option('seo_fixed_keywords', '')
            : get_option('bankai_seo_fixed_keywords', '');

        if (is_array($raw)) {
            return array_values(array_filter(array_map('sanitize_text_field', $raw)));
        }

        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $parts = preg_split('/[,،\n]+/u', $raw);
        $out   = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $out[] = sanitize_text_field($p);
            }
        }
        return $this->merge_unique_keywords($out);
    }

    public function api_suggest_internal_links(WP_REST_Request $request): WP_REST_Response
    {
        $params  = $request->get_json_params() ?: $request->get_params();
        if (!is_array($params)) {
            $params = [];
        }
        $post_id = absint($params['post_id'] ?? 0);
        $limit   = min(15, max(1, absint($params['limit'] ?? 8)));

        if ($post_id > 0 && !current_user_can('edit_post', $post_id)) {
            return new WP_REST_Response(['success' => false, 'message' => 'Permission denied'], 403);
        }

        $result = $this->suggest_ranked_internal_links($post_id, [
            'keywords' => $params['keywords'] ?? [],
            'content'  => isset($params['content']) ? (string) $params['content'] : '',
            'title'    => isset($params['title']) ? sanitize_text_field((string) $params['title']) : '',
            'limit'    => $limit,
        ]);

        return new WP_REST_Response([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Phase E — ranked internal link suggestions (deterministic).
     *
     * @param array{keywords?:mixed,content?:string,title?:string,limit?:int} $opts
     * @return array{suggestions:list<array<string,mixed>>,keywords:list<string>,source:array<string,mixed>,limits:array<string,int>}
     */
    public function suggest_ranked_internal_links(int $post_id, array $opts = []): array
    {
        $limit = min(15, max(1, absint($opts['limit'] ?? 8)));
        $post  = $post_id > 0 ? get_post($post_id) : null;

        $title = (string) ($opts['title'] ?? '');
        if ($title === '' && $post) {
            $title = get_the_title($post);
        }
        $content = (string) ($opts['content'] ?? '');
        if ($content === '' && $post) {
            $content = (string) $post->post_content;
        }
        $clean = wp_strip_all_tags($content);

        $keywords = $opts['keywords'] ?? [];
        if (!is_array($keywords)) {
            $keywords = preg_split('/[,،]+/u', (string) $keywords) ?: [];
        }
        $keywords = array_values(array_filter(array_map(static fn($k) => sanitize_text_field(trim((string) $k)), $keywords)));

        $focus = '';
        $secondary = [];
        if ($post_id > 0) {
            $focus = trim((string) get_post_meta($post_id, self::META_KEYWORD, true));
            $kw_raw = get_post_meta($post_id, self::META_KEYWORDS, true);
            if (is_array($kw_raw)) {
                $secondary = $kw_raw;
            } elseif (is_string($kw_raw) && $kw_raw !== '') {
                $decoded = json_decode($kw_raw, true);
                $secondary = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $kw_raw)));
            }
        }
        if ($focus === '' && $keywords) {
            $focus = (string) $keywords[0];
        }
        foreach ($keywords as $k) {
            if ($k !== '' && $k !== $focus) {
                $secondary[] = $k;
            }
        }
        $secondary = array_values(array_unique(array_filter(array_map('strval', $secondary))));

        // Extract headings
        preg_match_all('/<h[2-6]\b[^>]*>(.*?)<\/h[2-6]>/is', $content, $hm);
        $headings = array_map(static fn($h) => trim(wp_strip_all_tags($h)), $hm[1] ?? []);

        // Existing internal destinations (URLs) — resolve post IDs robustly
        $extracted = $this->extract_links_from_html($content);
        $existing_urls = [];
        $existing_ids = [];
        foreach ($extracted['internal'] as $item) {
            $href = (string) ($item['href'] ?? '');
            if ($href === '' || str_starts_with($href, '#')) {
                continue;
            }
            $existing_urls[] = $href;
            $tid = $this->resolve_internal_href_to_post_id($href);
            if ($tid > 0) {
                $existing_ids[$tid] = true;
            }
        }

        $src_cats = $post_id ? wp_get_post_terms($post_id, 'category', ['fields' => 'names']) : [];
        $src_tags = $post_id ? wp_get_post_terms($post_id, 'post_tag', ['fields' => 'names']) : [];
        if (is_wp_error($src_cats)) {
            $src_cats = [];
        }
        if (is_wp_error($src_tags)) {
            $src_tags = [];
        }

        $source = [
            'title'      => $title,
            'content'    => $clean,
            'focus'      => $focus,
            'secondary'  => $secondary,
            'headings'   => $headings,
            'categories' => array_values((array) $src_cats),
            'tags'       => array_values((array) $src_tags),
            'existing_urls' => $existing_urls,
        ];

        // Candidate discovery: limited, publish-only, exclude self
        $candidate_ids = $this->discover_link_candidates($post_id, $focus, $secondary, $title, 40);
        $scored = [];

        foreach ($candidate_ids as $cid) {
            $cid = (int) $cid;
            if ($cid <= 0 || $cid === $post_id) {
                continue;
            }
            if (isset($existing_ids[$cid])) {
                continue; // already linked
            }
            $tp = get_post($cid);
            if (!$tp || $tp->post_status !== 'publish') {
                continue;
            }
            if (!in_array($tp->post_type, ['post', 'page'], true)) {
                continue;
            }
            if (function_exists('is_post_publicly_viewable') && !is_post_publicly_viewable($tp)) {
                continue;
            }

            $t_clean = wp_strip_all_tags((string) $tp->post_content);
            preg_match_all('/<h[2-6]\b[^>]*>(.*?)<\/h[2-6]>/is', (string) $tp->post_content, $thm);
            $t_heads = array_map(static fn($h) => trim(wp_strip_all_tags($h)), $thm[1] ?? []);

            $t_focus = trim((string) get_post_meta($cid, self::META_KEYWORD, true));
            $t_sec_raw = get_post_meta($cid, self::META_KEYWORDS, true);
            $t_sec = [];
            if (is_array($t_sec_raw)) {
                $t_sec = $t_sec_raw;
            } elseif (is_string($t_sec_raw) && $t_sec_raw !== '') {
                $d = json_decode($t_sec_raw, true);
                $t_sec = is_array($d) ? $d : [];
            }

            $t_cats = wp_get_post_terms($cid, 'category', ['fields' => 'names']);
            $t_tags = wp_get_post_terms($cid, 'post_tag', ['fields' => 'names']);
            if (is_wp_error($t_cats)) {
                $t_cats = [];
            }
            if (is_wp_error($t_tags)) {
                $t_tags = [];
            }

            $permalink = get_permalink($cid);
            if (!$permalink) {
                continue;
            }

            $target = [
                'id'         => $cid,
                'title'      => get_the_title($tp),
                'content'    => $t_clean,
                'focus'      => $t_focus,
                'secondary'  => array_values(array_filter(array_map('strval', $t_sec))),
                'headings'   => $t_heads,
                'categories' => array_values((array) $t_cats),
                'tags'       => array_values((array) $t_tags),
                'permalink'  => $permalink,
            ];

            $rank = class_exists('Bankai_SEO_Link_Ranker')
                ? Bankai_SEO_Link_Ranker::score($source, $target)
                : ['score' => 0.0, 'factors' => [], 'reasons' => [], 'anchor' => $t_focus ?: get_the_title($tp), 'evidence' => []];

            // Minimum relevance gate: avoid title-only noise
            if ((float) ($rank['score'] ?? 0) < 12.0) {
                continue;
            }

            $anchor = (string) ($rank['anchor'] ?? '');
            $anchor_in_source = !empty($rank['evidence']['anchor_in_source']);
            $scored[] = [
                'id'                 => $cid,
                'target_post_id'     => $cid,
                'title'              => $target['title'],
                'target_title'       => $target['title'],
                'permalink'          => $permalink,
                'target_url'         => $permalink,
                'focus_keyword'      => $t_focus,
                'suggested_anchor'   => $anchor,
                'keyword'            => $anchor, // backward compat for JS
                'anchor_in_content'  => $anchor_in_source,
                'in_content'         => $anchor_in_source,
                'score'              => (float) ($rank['score'] ?? 0),
                'factors'            => $rank['factors'] ?? [],
                'reasons'            => $rank['reasons'] ?? [],
                'reason'             => implode(' ', $rank['reasons'] ?? []),
                'evidence'           => $rank['evidence'] ?? [],
                'link_status'        => 'new',
                'requires_approval'  => true,
                'insertion_confidence' => $anchor_in_source ? 'high' : 'low',
            ];
        }

        usort($scored, static function ($a, $b) {
            return ($b['score'] <=> $a['score']);
        });
        $scored = array_slice($scored, 0, $limit);

        return [
            'suggestions' => $scored,
            'keywords'    => array_values(array_filter(array_merge($focus !== '' ? [$focus] : [], $secondary))),
            'source'      => [
                'post_id' => $post_id,
                'focus'   => $focus,
                'title'   => $title,
            ],
            'limits'      => [
                'candidates_scanned' => count($candidate_ids),
                'returned'           => count($scored),
                'max'                => $limit,
            ],
        ];
    }

    /**
     * Discover candidate post IDs (publish only, capped).
     *
     * @param list<string> $secondary
     * @return list<int>
     */
    private function discover_link_candidates(int $exclude_id, string $focus, array $secondary, string $title, int $cap = 40): array
    {
        $ids = [];
        $terms = array_values(array_filter(array_merge(
            $focus !== '' ? [$focus] : [],
            array_slice($secondary, 0, 4),
            $title !== '' ? [mb_substr($title, 0, 40)] : []
        )));
        if (!$terms) {
            // No keywords: recent related by category only if exclude_id has cats
            $q = new \WP_Query([
                'post_type'              => ['post', 'page'],
                'post_status'            => 'publish',
                'posts_per_page'         => min(15, $cap),
                'post__not_in'           => $exclude_id > 0 ? [$exclude_id] : [],
                'orderby'                => 'modified',
                'order'                  => 'DESC',
                'fields'                 => 'ids',
                'no_found_rows'          => true,
                'ignore_sticky_posts'    => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => true,
            ]);
            return array_map('intval', $q->posts ?: []);
        }

        foreach (array_slice($terms, 0, 5) as $term) {
            if (count($ids) >= $cap) {
                break;
            }
            $q = new \WP_Query([
                'post_type'              => ['post', 'page'],
                'post_status'            => 'publish',
                's'                      => $term,
                'posts_per_page'         => 12,
                'post__not_in'           => array_values(array_unique(array_merge($exclude_id > 0 ? [$exclude_id] : [], array_map('intval', array_keys($ids))))),
                'orderby'                => 'relevance', // WP core search ranking when `s` is set; not semantic ML
                'order'                  => 'DESC',
                'fields'                 => 'ids',
                'no_found_rows'          => true,
                'ignore_sticky_posts'    => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]);
            foreach ($q->posts ?: [] as $pid) {
                $ids[(int) $pid] = true;
                if (count($ids) >= $cap) {
                    break 2;
                }
            }
        }

        // Meta keyword match (focus keyword of others)
        if ($focus !== '' && count($ids) < $cap) {
            $q = new \WP_Query([
                'post_type'              => ['post', 'page'],
                'post_status'            => 'publish',
                'posts_per_page'         => 15,
                'post__not_in'           => array_values(array_unique(array_merge($exclude_id > 0 ? [$exclude_id] : [], array_keys($ids)))),
                'fields'                 => 'ids',
                'no_found_rows'          => true,
                'meta_query'             => [
                    [
                        'key'     => self::META_KEYWORD,
                        'value'   => $focus,
                        'compare' => 'LIKE',
                    ],
                ],
            ]);
            foreach ($q->posts ?: [] as $pid) {
                $ids[(int) $pid] = true;
                if (count($ids) >= $cap) {
                    break;
                }
            }
        }

        return array_keys($ids);
    }


    /**
     * Resolve an internal href (absolute, relative, or pretty permalink) to a post ID.
     * Phase E.2 — stronger than raw url_to_postid alone.
     */
    private function resolve_internal_href_to_post_id(string $href): int
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
            return 0;
        }
        $tid = url_to_postid($href);
        if ($tid > 0) {
            return $tid;
        }
        // Relative path → absolute
        if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
            $tid = url_to_postid(home_url($href));
            if ($tid > 0) {
                return $tid;
            }
        }
        if (str_starts_with($href, '//')) {
            $tid = url_to_postid('https:' . $href);
            if ($tid > 0) {
                return $tid;
            }
        }
        // Path-only match against known query is expensive; skip unbounded scans.
        return 0;
    }

    private function extract_links_from_html(string $content): array
    {
        $internal = [];
        $external = [];
        if ($content === '' || $content === null) {
            return compact('internal', 'external');
        }

        $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
        $home_host = $home_host ? mb_strtolower((string) $home_host) : '';
        $home_host = preg_replace('/^www\./i', '', $home_host);

        // Gutenberg sometimes stores href on different quote styles / multiline
        if (!preg_match_all('/<a\b([^>]*)>([\s\S]*?)<\/a>/iu', $content, $matches, PREG_SET_ORDER)) {
            return compact('internal', 'external');
        }

        $seen = [];
        foreach ($matches as $m) {
            $attrs = $m[1] ?? '';
            $inner = $m[2] ?? '';
            if (!preg_match('/\bhref\s*=\s*(["\'])([^"\']+)\1/i', $attrs, $hm)
                && !preg_match('/\bhref\s*=\s*([^\s>]+)/i', $attrs, $hm2)) {
                continue;
            }
            $href = isset($hm[2]) ? trim($hm[2]) : trim((string) ($hm2[1] ?? ''), "\"'");
            $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($href === '' || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            $text = trim(wp_strip_all_tags($inner));
            $nofollow = (bool) preg_match('/\brel\s*=\s*["\'][^"\']*nofollow/i', $attrs);
            $item = [
                'href'     => $href,
                'text'     => $text,
                'nofollow' => $nofollow,
            ];

            $key = mb_strtolower($href) . '|' . mb_strtolower($text);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            if ($this->is_internal_href($href, $home_host)) {
                $internal[] = $item;
            } else {
                $external[] = $item;
            }
        }

        return compact('internal', 'external');
    }

    /**
     * Decide whether an href points to this site.
     */
    private function is_internal_href(string $href, string $home_host = ''): bool
    {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, '#')) {
            return true; // in-page anchors count as internal navigation
        }
        // Relative paths
        if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
            return true;
        }
        // Protocol-relative or absolute
        if (str_starts_with($href, '//')) {
            $href = 'https:' . $href;
        }
        $parts = wp_parse_url($href);
        if (!is_array($parts) || empty($parts['host'])) {
            // bare relative like "page.html"
            if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $href)) {
                return true;
            }
            return false;
        }
        $host = mb_strtolower((string) $parts['host']);
        $host = preg_replace('/^www\./i', '', $host);
        if ($home_host === '') {
            $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
            $home_host = $home_host ? preg_replace('/^www\./i', '', mb_strtolower((string) $home_host)) : '';
        }
        return $home_host !== '' && $host === $home_host;
    }


    /**
     * Merge keyword lists, case-insensitive unique, preserve first occurrence order.
     *
     * @param list<mixed> $keywords
     * @return list<string>
     */
    private function merge_unique_keywords(array $keywords): array
    {
        $out = [];
        $seen = [];
        foreach ($keywords as $kw) {
            if (!is_scalar($kw)) {
                continue;
            }
            $kw = trim((string) $kw);
            if ($kw === '') {
                continue;
            }
            $key = function_exists('mb_strtolower')
                ? mb_strtolower($kw, 'UTF-8')
                : strtolower($kw);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $kw;
        }
        return $out;
    }

    private function resolve_pretty_permalink(int $post_id, $post = null): string
    {
        $post = $post instanceof WP_Post ? $post : get_post($post_id);
        $fallback = (string) get_permalink($post_id);
        if (!$post || empty($post->post_name)) {
            return $fallback;
        }
        if (function_exists('get_sample_permalink')) {
            $sample = get_sample_permalink($post_id);
            if (is_array($sample) && !empty($sample[0])) {
                $pretty = str_replace(['%pagename%', '%postname%'], $post->post_name, $sample[0]);
                if (is_string($pretty) && $pretty !== '' && strpos($pretty, '?p=') === false) {
                    return $pretty;
                }
            }
        }
        if (strpos($fallback, '?p=') !== false) {
            $struct = (string) get_option('permalink_structure');
            if ($struct !== '') {
                return trailingslashit(home_url(user_trailingslashit($post->post_name)));
            }
        }
        return $fallback;
    }

}
