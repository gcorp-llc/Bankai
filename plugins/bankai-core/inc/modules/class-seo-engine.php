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
            'permission_callback' => static fn(): bool => current_user_can('edit_posts'),
        ]);
    }

    public function permission_check(WP_REST_Request $request): bool
    {
        $post_id = absint($request['id'] ?? 0);
        return $post_id > 0 && current_user_can('edit_post', $post_id);
    }

    public function api_get(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);
        return new WP_REST_Response([
            'success' => true,
            'data'    => $this->get_seo_data($post_id),
        ]);
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
        
        if (empty($overrides['content'])) {
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
        if (mb_strlen($q) < 2) {
            return new WP_REST_Response(['success' => true, 'data' => []]);
        }

        $exclude = absint($request->get_param('post_id') ?? ($params['post_id'] ?? 0));
        $posts = get_posts([
            'post_type'      => ['post', 'page'],
            'post_status'    => 'publish',
            'posts_per_page' => 15,
            'post__not_in'   => $exclude > 0 ? [$exclude] : [],
            's'              => $q,
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
        ]);

        $data = array_map(function (\WP_Post $p): array {
            return [
                'id'            => $p->ID,
                'title'         => get_the_title($p),
                'permalink'     => get_permalink($p),
                'type'          => $p->post_type,
                'focus_keyword' => (string) get_post_meta($p->ID, self::META_KEYWORD, true),
            ];
        }, $posts);

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
            'permalink'     => get_permalink($post_id),
            'slug'          => $post->post_name,
            'content'       => $post->post_content,
            'seo_title'     => $seo_title !== '' ? $seo_title : get_the_title($post_id),
            'description'   => $description !== '' ? $description : wp_trim_words(wp_strip_all_tags($post->post_content), 25, '...'),
            'focus_keyword' => $focus,
            'keywords'      => array_values($keywords),
            'canonical'     => ($canonical !== '' && preg_match('#^https?://#i', $canonical))
                ? $canonical
                : (string) get_permalink($post_id),
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

        $raw_content = $overrides['content'] ?? $post->post_content;
        $clean_text  = wp_strip_all_tags($raw_content);
        $title       = get_the_title($post_id);
        $seo_title   = (string) ($overrides['seo_title'] ?? get_post_meta($post_id, self::META_TITLE, true));
        $description = (string) ($overrides['description'] ?? get_post_meta($post_id, self::META_DESCRIPTION, true));
        $keyword     = trim((string) ($overrides['focus_keyword'] ?? get_post_meta($post_id, self::META_KEYWORD, true)));
        $slug        = $post->post_name;
        $permalink   = get_permalink($post_id);

        $checks   = [];
        $groups   = [
            'basic'    => ['label' => 'بررسی‌های پایه سئو', 'items' => []],
            'advanced' => ['label' => 'بررسی‌های تکمیلی', 'items' => []],
            'title'    => ['label' => 'خوانایی عنوان', 'items' => []],
            'content'  => ['label' => 'خوانایی محتوا', 'items' => []],
        ];

        $kw_lower = mb_strtolower($keyword);
        $has_kw   = $keyword !== '';

        // Accurate Multilingual Word Count (Persian/English)
        $words = preg_split('/\s+/u', trim($clean_text), -1, PREG_SPLIT_NO_EMPTY);
        $word_count = is_array($words) ? count($words) : 0;

        // --- Basic ---
        $checks[] = $this->check('focus_keyword', 'کلمه کلیدی اصلی تنظیم شده', $has_kw,
            $has_kw ? 'کلمه کلیدی اصلی تنظیم شده است.' : 'یک کلمه کلیدی اصلی برای این محتوا تنظیم کنید.', 'basic');

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
        $sec_list = array_values(array_filter($sec_list, static function ($k) use ($keyword) {
            return $k !== '' && mb_strtolower($k) !== mb_strtolower($keyword);
        }));
        $sec_in_body = 0;
        foreach ($sec_list as $sk) {
            if (mb_stripos($clean_text, $sk) !== false || mb_stripos($seo_title ?: $title, $sk) !== false) {
                $sec_in_body++;
            }
        }
        $sec_ok = count($sec_list) >= 1 && $sec_in_body >= min(1, count($sec_list));
        $checks[] = $this->check(
            'secondary_keywords',
            'کلیدواژه‌های فرعی',
            $sec_ok,
            $sec_ok
                ? sprintf('%d کلیدواژه فرعی و حضور در محتوا تایید شد.', count($sec_list))
                : (count($sec_list) ? 'حداقل یک کلیدواژه فرعی را در عنوان یا متن به کار ببرید.' : '۲–۳ کلیدواژه فرعی مرتبط اضافه کنید.'),
            'basic'
        );

        $checks[] = $this->check('kw_in_title', 'کلمه کلیدی در عنوان سئو',
            $has_kw && mb_stripos($seo_title ?: $title, $keyword) !== false,
            'کلمه کلیدی اصلی را به عنوان سئو اضافه کنید.', 'basic');

        $checks[] = $this->check('kw_in_desc', 'کلمه کلیدی در توضیحات متا',
            $has_kw && mb_stripos($description, $keyword) !== false,
            'کلمه کلیدی اصلی را به توضیحات متا اضافه کنید.', 'basic');

        $checks[] = $this->check('kw_in_url', 'کلمه کلیدی در URL',
            $has_kw && (mb_stripos($slug, sanitize_title($keyword)) !== false || mb_stripos($permalink, $keyword) !== false),
            'از کلمه کلیدی اصلی در URL استفاده کنید.', 'basic');

        $first_pct = mb_substr($clean_text, 0, (int) max(1, mb_strlen($clean_text) * 0.1));
        $checks[]  = $this->check('kw_in_intro', 'کلمه کلیدی در ۱۰٪ ابتدایی',
            $has_kw && mb_stripos($first_pct, $keyword) !== false,
            'در ابتدای محتوای خود از کلمه کلیدی اصلی استفاده کنید.', 'basic');

        $checks[] = $this->check('kw_in_content', 'کلمه کلیدی در محتوا',
            $has_kw && mb_stripos($clean_text, $keyword) !== false,
            'در محتوا از کلمه کلیدی اصلی استفاده کنید.', 'basic');

        $checks[] = $this->check('content_length', 'طول محتوا',
            $word_count >= 300,
            sprintf('طول محتوا %d کلمه است.%s', $word_count, $word_count >= 300 ? ' عالی!' : ' حداقل ۳۰۰ کلمه پیشنهاد می‌شود.'), 'basic');

        // --- Advanced ---
        preg_match_all('/<h[2-6]\b[^>]*>(.*?)<\/h[2-6]>/is', $raw_content, $headings);
        $heading_text = implode(' ', array_map('wp_strip_all_tags', $headings[1] ?? []));
        $checks[]     = $this->check('kw_in_headings', 'کلمه کلیدی در زیرتیترها',
            $has_kw && mb_stripos($heading_text, $keyword) !== false,
            'استفاده از کلمه کلیدی اصلی در زیرتیترها (H2, H3, …).', 'advanced');

        preg_match_all('/<img\b[^>]*>/i', $raw_content, $images);
        $img_count  = count($images[0] ?? []);
        $has_kw_alt = false;
        if ($img_count > 0 && $has_kw) {
            foreach ($images[0] as $img) {
                if (preg_match('/alt=["\']([^"\']*)["\']/i', $img, $am) && mb_stripos($am[1], $keyword) !== false) {
                    $has_kw_alt = true;
                    break;
                }
            }
        }
        $checks[] = $this->check('kw_in_alt', 'کلیدواژه در alt تصاویر',
            $img_count === 0 || $has_kw_alt,
            $img_count === 0 ? 'تصویری در محتوا نیست.' : 'تصویری با کلمه کلیدی اصلی به‌عنوان alt اضافه کنید.', 'advanced');

        $density = 0.0;
        $kw_occurrences = 0;
        if ($has_kw && $word_count > 0) {
            // Phrase-level count (Unicode); better for multi-word Persian keywords
            $kw_occurrences = mb_substr_count(mb_strtolower($clean_text), $kw_lower);
            $kw_words = max(1, count(preg_split('/\s+/u', trim($keyword), -1, PREG_SPLIT_NO_EMPTY) ?: [1]));
            // Approximate density: (occurrences * keyword-word-count) / total words * 100
            $density = round((($kw_occurrences * $kw_words) / $word_count) * 100, 2);
        }
        $checks[] = $this->check('kw_density', 'چگالی کلمه کلیدی',
            $has_kw && $density >= 0.5 && $density <= 2.5,
            !$has_kw
                ? 'ابتدا کلمه کلیدی اصلی را تنظیم کنید.'
                : sprintf('چگالی کلمه کلیدی %.2f٪ است (%d بار در متن). هدف حدود ۰٫۵–۲٫۵٪.', $density, $kw_occurrences),
            'advanced');

        $url_path = (string) (wp_parse_url($permalink, PHP_URL_PATH) ?: $permalink);
        $url_len  = mb_strlen(rawurldecode($url_path));
        $checks[] = $this->check('url_length', 'طول URL',
            $url_len <= 100,
            sprintf('مسیر آدرس %d کاراکتر است.%s', $url_len, $url_len <= 100 ? ' مناسب است.' : ' کوتاه‌تر پیشنهاد می‌شود (زیر ۱۰۰ کاراکتر مسیر).'), 'advanced');

        $extracted_links = $this->extract_links_from_html($raw_content);
        // SEO-relevant internal links: exclude pure in-page anchors (#section)
        $internal_seo = array_values(array_filter(
            $extracted_links['internal'],
            static function ($item) {
                $h = trim((string) ($item['href'] ?? ''));
                return $h !== '' && !preg_match('/^#/', $h);
            }
        ));
        $internal = count($internal_seo);
        $external = count($extracted_links['external']);

        $checks[] = $this->check('internal_links', 'لینک داخلی',
            $internal >= 1,
            $internal > 0 ? sprintf('%d لینک داخلی یافت شد.', $internal) : 'لینک داخلی پیدا نشد. به صفحات مرتبط لینک دهید.', 'advanced');

        $checks[] = $this->check('external_links', 'لینک خارجی',
            $external >= 1,
            $external > 0 ? sprintf('%d لینک خارجی یافت شد.', $external) : 'هیچ پیوند خروجی یافت نشد. به منابع خارجی پیوند دهید.', 'advanced');

        // --- Title readability ---
        $title_use = $seo_title ?: $title;
        $checks[]  = $this->check('kw_start_title', 'کلیدواژه در ابتدای عنوان',
            $has_kw && mb_stripos(mb_strtolower($title_use), $kw_lower) === 0,
            'از کلمه کلیدی اصلی در ابتدای عنوان سئو استفاده کنید.', 'title');

        $title_len = mb_strlen($title_use);
        $checks[]  = $this->check('title_length', 'طول عنوان سئو',
            $title_len >= 30 && $title_len <= 60,
            sprintf('طول عنوان: %d کاراکتر (هدف ۳۰–۶۰).', $title_len), 'title');

        $has_num = (bool) preg_match('/\d/u', $title_use);
        $checks[] = $this->check('title_has_number', 'عدد در عنوان',
            true, // توصیه اختیاری — روی امتیاز اجباری اثر نگذارد
            $has_num ? 'عنوان شامل عدد است؛ جذابیت بیشتری دارد.' : 'پیشنهاد: افزودن عدد به عنوان (مثلاً «۵ روش…») کلیک را افزایش می‌دهد.', 'title');

        // --- Content readability ---
        $desc_len = mb_strlen($description);
        $checks[] = $this->check('desc_length', 'طول توضیحات متا',
            $desc_len >= 120 && $desc_len <= 160,
            sprintf('طول توضیحات: %d کاراکتر (هدف ۱۲۰–۱۶۰).', $desc_len), 'content');

        preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $raw_content, $paras);
        $long_para = false;
        foreach ($paras[1] ?? [] as $p) {
            $p_words = preg_split('/\s+/u', trim(wp_strip_all_tags($p)), -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($p_words) && count($p_words) > 120) {
                $long_para = true;
                break;
            }
        }
        $checks[] = $this->check('short_paragraphs', 'پاراگراف‌های کوتاه',
            !$long_para,
            $long_para ? 'حداقل یک پاراگراف طولانی است. پاراگراف‌های کوتاه پیشنهاد می‌شود.' : 'طول پاراگراف‌ها مناسب است.', 'content');

        // Lists
        $has_list = (bool) preg_match('/<(ul|ol)\b/i', $raw_content);
        $checks[] = $this->check('has_lists', 'استفاده از فهرست',
            $has_list,
            $has_list ? 'فهرست (لیست) در محتوا وجود دارد.' : 'برای خوانایی بهتر از لیست شماره‌دار یا نقطه‌ای استفاده کنید.', 'content');

        // Sentence length (approx for FA/EN)
        $sentences = preg_split('/(?<=[.!?…۔])\s+/u', $clean_text, -1, PREG_SPLIT_NO_EMPTY);
        $long_sent = 0;
        $sent_count = is_array($sentences) ? count($sentences) : 0;
        if (is_array($sentences)) {
            foreach ($sentences as $s) {
                $sw = preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY);
                if (is_array($sw) && count($sw) > 40) {
                    $long_sent++;
                }
            }
        }
        $sent_ok = $sent_count === 0 || ($long_sent / max(1, $sent_count)) < 0.35;
        $checks[] = $this->check('sentence_length', 'طول جملات',
            $sent_ok,
            $sent_ok ? 'طول جملات در محدوده مناسب است.' : sprintf('%d جمله بسیار طولانی است؛ کوتاه‌تر بنویسید.', $long_sent), 'content');

        // Heading ratio vs words
        $h_count = count($headings[0] ?? []);
        $need_h = $word_count > 300 ? max(1, (int) floor($word_count / 350)) : 0;
        $h_ok = $need_h === 0 || $h_count >= $need_h;
        $checks[] = $this->check('heading_ratio', 'نسبت زیرتیتر به طول متن',
            $h_ok,
            $h_ok ? 'تعداد زیرتیترها با طول محتوا هماهنگ است.' : sprintf('برای %d کلمه حدود %d زیرتیتر پیشنهاد می‌شود (الان: %d).', $word_count, $need_h, $h_count), 'content');

        // Duplicate title / slug across site
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
                if (mb_strtolower(get_the_title((int) $hid)) === mb_strtolower($title)) {
                    $dup_ok = false;
                    $dup_msg = 'عنوان دقیقاً با پست دیگری یکسان است.';
                    break;
                }
            }
        }
        $checks[] = $this->check('unique_title', 'یکتایی عنوان/اسلاگ',
            $dup_ok, $dup_msg, 'advanced');

        $has_media = $img_count > 0 || (bool) preg_match('/<(video|iframe|embed)\b/i', $raw_content);
        $checks[]  = $this->check('rich_media', 'رسانه غنی',
            $has_media,
            $has_media ? 'رسانه در محتوا وجود دارد.' : 'از تصاویر یا ویدئو استفاده کنید.', 'content');

        foreach ($checks as $c) {
            $g = $c['group'] ?? 'basic';
            if (!isset($groups[$g])) {
                $g = 'basic';
            }
            $groups[$g]['items'][] = $c;
        }

        $passed = count(array_filter($checks, static fn($c) => $c['passed']));
        $total  = count($checks);
        $score  = $total > 0 ? (int) round(($passed / $total) * 100) : 0;

        return [
            'score'  => $score,
            'passed' => $passed,
            'total'  => $total,
            'checks' => $checks,
            'groups' => $groups,
            'stats'  => [
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

    private function check(string $key, string $label, bool $passed, string $message, string $group = 'basic'): array
    {
        return compact('key', 'label', 'passed', 'message', 'group');
    }


    /**
     * 301 redirect for single posts with redirect meta set.
     */

    /**
     * Lightweight related posts by keyword / title for on-save suggestions.
     *
     * @return list<array{id:int,title:string,permalink:string}>
     */
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
            $nodes[] = [
                '@type'         => $post->post_type === 'post' ? 'Article' : 'WebPage',
                'headline'      => get_the_title($post_id),
                'description'   => get_the_excerpt($post_id),
                'url'           => get_permalink($post_id),
                'datePublished' => get_the_date(DATE_W3C, $post_id),
                'dateModified'  => get_the_modified_date(DATE_W3C, $post_id),
                'author'        => [
                    '@type' => 'Person',
                    'name'  => get_the_author_meta('display_name', (int) $post->post_author),
                ],
            ];
            $image = get_the_post_thumbnail_url($post_id, 'full');
            if ($image) {
                $nodes[0]['image'] = [$image];
            }
        }

        // Always attach BreadcrumbList if not already present
        $has_bc = false;
        foreach ($nodes as $n) {
            if (($n['@type'] ?? '') === 'BreadcrumbList') {
                $has_bc = true;
                break;
            }
        }
        if (!$has_bc) {
            $bc = $this->build_breadcrumb_schema($post_id);
            unset($bc['@context']);
            $nodes[] = $bc;
        }

        $payload = [
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ];

        echo '<script type="application/ld+json">'
            . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "
";
    }

    public function api_articles_list(WP_REST_Request $request): WP_REST_Response
    {
        $page     = max(1, absint($request->get_param('page') ?: 1));
        $per_page = min(100, max(5, absint($request->get_param('per_page') ?: 25)));
        $search   = sanitize_text_field($request->get_param('search') ?? '');
        $orderby  = sanitize_key($request->get_param('orderby') ?? 'modified');
        $order    = strtoupper(sanitize_text_field($request->get_param('order') ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $args = [
            'post_type'      => ['post', 'page'],
            'post_status'    => ['publish', 'draft', 'pending', 'future'],
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'order'          => $order,
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
        $params   = $request->get_json_params() ?: [];
        $post_id  = absint($params['post_id'] ?? 0);
        $keywords = $params['keywords'] ?? [];
        $content  = isset($params['content']) ? (string) $params['content'] : '';

        if (!is_array($keywords)) {
            $keywords = preg_split('/[,،]+/u', (string) $keywords) ?: [];
        }

        $keywords = array_values(array_filter(array_map(static fn($k) => sanitize_text_field(trim((string) $k)), $keywords)));

        if ($post_id > 0 && empty($keywords)) {
            $focus = (string) get_post_meta($post_id, self::META_KEYWORD, true);
            if ($focus !== '') {
                $keywords[] = $focus;
            }
            $kw_raw = (string) get_post_meta($post_id, self::META_KEYWORDS, true);
            if ($kw_raw !== '') {
                $decoded = json_decode($kw_raw, true);
                if (is_array($decoded)) {
                    $keywords = array_merge($keywords, $decoded);
                }
            }
        }

        $fixed = $this->get_fixed_keywords_list();

        if ($content === '' && $post_id > 0) {
            $post    = get_post($post_id);
            $content = $post ? $post->post_content : '';
        }

        $clean_content = wp_strip_all_tags($content);
        $suggestions   = [];
        $seen_ids      = [$post_id => true];

        // Optimized single query search
        $search_terms = array_slice(array_filter($keywords, static fn($k) => mb_strlen($k) >= 2), 0, 5);
        if (!empty($search_terms)) {
            $found_posts = get_posts([
                'post_type'      => ['post', 'page'],
                'post_status'    => 'publish',
                'posts_per_page' => 15,
                'post__not_in'   => array_keys($seen_ids),
                's'              => implode(' ', $search_terms),
            ]);

            foreach ($found_posts as $p) {
                if (isset($seen_ids[$p->ID])) {
                    continue;
                }
                $seen_ids[$p->ID] = true;
                $target_kw        = (string) get_post_meta($p->ID, self::META_KEYWORD, true);
                $anchor           = $target_kw !== '' ? $target_kw : get_the_title($p);

                $anchor_in_content = null;
                foreach (array_merge([$anchor, get_the_title($p)], $keywords) as $cand) {
                    $cand = trim((string) $cand);
                    if ($cand !== '' && mb_stripos($clean_content, $cand) !== false) {
                        $anchor_in_content = $cand;
                        break;
                    }
                }

                $suggestions[] = [
                    'id'               => $p->ID,
                    'title'            => get_the_title($p),
                    'permalink'        => get_permalink($p),
                    'focus_keyword'    => $target_kw,
                    'suggested_anchor' => $anchor_in_content ?: $anchor,
                    'in_content'       => $anchor_in_content !== null,
                ];
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'suggestions' => $suggestions,
                'keywords'    => $keywords,
                'fixed'       => $fixed,
            ],
        ]);
    }

    public function maybe_create_views_table(): void
    {
        if (get_option('bankai_views_table_ver') === '1') {
            return;
        }

        global $wpdb;
        $table   = $wpdb->prefix . 'bankai_post_views';
        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "CREATE TABLE {$table} (
            post_id BIGINT UNSIGNED NOT NULL,
            view_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            last_viewed_at DATETIME NULL,
            PRIMARY KEY (post_id),
            KEY view_count (view_count)
        ) {$charset};";

        dbDelta($sql);
        update_option('bankai_views_table_ver', '1', false);
    }

    public function get_views_map(array $ids): array
    {
        $ids = array_filter(array_map('absint', $ids));
        if (empty($ids)) {
            return [];
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'bankai_post_views';
        $in_sql = implode(',', $ids);

        // Cached query execution
        $rows = $wpdb->get_results("SELECT post_id, view_count FROM {$table} WHERE post_id IN ({$in_sql})", OBJECT_K);

        $map = [];
        foreach ($ids as $id) {
            $map[$id] = isset($rows[$id]) ? (int) $rows[$id]->view_count : (int) get_post_meta($id, self::META_VIEWS, true);
        }
        return $map;
    }

    public function api_track_view(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);
        if ($post_id <= 0 || get_post_status($post_id) !== 'publish') {
            return new WP_REST_Response(['counted' => false], 200);
        }

        $ua   = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $bots = ['googlebot', 'bingbot', 'yandex', 'baiduspider', 'duckduckbot', 'slurp', 'bot/', 'crawler', 'spider'];
        foreach ($bots as $b) {
            if (str_contains($ua, $b)) {
                return new WP_REST_Response(['counted' => false, 'reason' => 'bot'], 200);
            }
        }

        $ip      = $_SERVER['REMOTE_ADDR'] ?? '0';
        $ip_hash = md5($ip . gmdate('Y-m-d'));
        $tkey    = "bankai_view_{$post_id}_{$ip_hash}";

        if (get_transient($tkey)) {
            return new WP_REST_Response(['counted' => false, 'reason' => 'dedupe'], 200);
        }
        set_transient($tkey, 1, DAY_IN_SECONDS);

        global $wpdb;
        $table = $wpdb->prefix . 'bankai_post_views';
        
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$table} (post_id, view_count, last_viewed_at) VALUES (%d, 1, %s)
             ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed_at = VALUES(last_viewed_at)",
            $post_id,
            current_time('mysql', true)
        ));

        $count = (int) get_post_meta($post_id, self::META_VIEWS, true) + 1;
        update_post_meta($post_id, self::META_VIEWS, $count);

        return new WP_REST_Response(['counted' => true, 'views' => $count], 200);
    }

    public function print_view_tracker(): void
    {
        if (!is_singular(['post', 'page'])) {
            return;
        }
        $id = get_queried_object_id();
        if ($id <= 0) {
            return;
        }
        $url = esc_url_raw(rest_url('bankai/v1/track-view/' . $id));
        printf(
            "<script>(function(){try{fetch(%s,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:'{}'});}catch(e){}})();</script>\n",
            wp_json_encode($url)
        );
    }

    /**
     * Helper to merge and deduplicate keyword arrays (Unicode Aware)
     */
    private function merge_unique_keywords(array $keywords): array
    {
        $seen   = [];
        $merged = [];
        foreach ($keywords as $kw) {
            $kw = trim((string) $kw);
            if ($kw === '') {
                continue;
            }
            $lower = mb_strtolower($kw);
            if (isset($seen[$lower])) {
                continue;
            }
            $seen[$lower] = true;
            $merged[]     = $kw;
        }
        return $merged;
    }

    /**
     * Extract internal / external links from HTML (Gutenberg + classic).
     */
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
}
