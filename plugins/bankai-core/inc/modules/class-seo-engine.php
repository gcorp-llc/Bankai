<?php
/**
 * Bankai Core - SEO Engine
 *
 * Meta · Analysis · Robots · Canonical · JSON-LD · OG · Twitter · REST
 */
if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_SEO_Engine
{
    private static ?self $instance = null;

    private const META_TITLE       = '_bankai_seo_title';
    private const META_DESCRIPTION = '_bankai_seo_description';
    private const META_KEYWORD     = '_bankai_seo_focus_keyword';
    private const META_KEYWORDS    = '_bankai_seo_keywords'; // JSON array of secondary
    private const META_CANONICAL   = '_bankai_seo_canonical';
    private const META_ROBOTS      = '_bankai_seo_robots';
    private const META_OG_TITLE    = '_bankai_seo_og_title';
    private const META_OG_DESC     = '_bankai_seo_og_description';
    private const META_OG_IMAGE    = '_bankai_seo_og_image';
    private const META_X_TITLE     = '_bankai_seo_x_title';
    private const META_X_DESC      = '_bankai_seo_x_description';
    private const META_X_IMAGE     = '_bankai_seo_x_image';
    private const META_SCHEMA      = '_bankai_seo_schema';
    private const META_SCORE       = '_bankai_seo_score';

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        // Respect core module switch — when seo_engine is off, no meta/schema/title.
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('seo_engine')) {
            return;
        }

        add_action('init', [$this, 'register_meta']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('wp_head', [$this, 'render_meta'], 1);
        add_action('wp_head', [$this, 'render_schema'], 20);
        add_filter('pre_get_document_title', [$this, 'filter_document_title'], 20);
    }

    public function register_meta(): void
    {
        $string_fields = [
            self::META_TITLE,
            self::META_DESCRIPTION,
            self::META_KEYWORD,
            self::META_KEYWORDS,
            self::META_CANONICAL,
            self::META_OG_TITLE,
            self::META_OG_DESC,
            self::META_OG_IMAGE,
            self::META_X_TITLE,
            self::META_X_DESC,
            self::META_X_IMAGE,
            self::META_SCHEMA,
        ];

        foreach ($string_fields as $meta_key) {
            register_post_meta('', $meta_key, [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [$this, 'sanitize_meta'],
                'auth_callback'     => static fn() => current_user_can('edit_posts'),
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
            'auth_callback'     => static fn() => current_user_can('edit_posts'),
        ]);

        register_post_meta('', self::META_SCORE, [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'auth_callback'     => static fn() => current_user_can('edit_posts'),
        ]);
    }

    public function sanitize_meta($value): string
    {
        return sanitize_text_field(wp_unslash((string) $value));
    }

    public function sanitize_robots($value): array
    {
        $value = is_array($value) ? $value : [];
        return [
            'index'  => !empty($value['index']),
            'follow' => !empty($value['follow']),
        ];
    }

    public function register_rest_routes(): void
    {
        register_rest_route('bankai/v1', '/seo/analyze/(?P<id>\d+)', [
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

        register_rest_route('bankai/v1', '/seo/(?P<id>\d+)', [
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

        register_rest_route('bankai/v1', '/seo/search-posts', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'api_search_posts'],
            'permission_callback' => static fn() => current_user_can('edit_posts'),
            'args'                => [
                'q' => ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route('bankai/v1', '/seo/links/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'api_extract_links'],
            'permission_callback' => [$this, 'permission_check'],
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
            return new WP_REST_Response(['success' => false, 'message' => 'Invalid payload.'], 400);
        }

        $map = [
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
        ];

        foreach ($map as $short => $field) {
            if (array_key_exists($short, $params)) {
                $val = $params[$short];
                if ($short === 'keywords' && is_array($val)) {
                    $val = wp_json_encode(array_map('sanitize_text_field', $val), JSON_UNESCAPED_UNICODE);
                } else {
                    $val = sanitize_text_field(wp_unslash((string) $val));
                }
                update_post_meta($post_id, $field, $val);
            }
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

        // Re-analyze and store score.
        $analysis = $this->analyze($post_id);
        update_post_meta($post_id, self::META_SCORE, (int) ($analysis['score'] ?? 0));

        return new WP_REST_Response([
            'success' => true,
            'data'    => $this->get_seo_data($post_id),
            'analysis'=> $analysis,
        ]);
    }

    public function api_analyze(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);

        // Optional live overrides from editor (unsaved content / meta).
        $params  = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }
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
        // Only persist score when analyzing saved state (no content override).
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
        $q = sanitize_text_field($request->get_param('q') ?? '');
        if (mb_strlen($q) < 2) {
            return new WP_REST_Response(['success' => true, 'data' => []]);
        }

        $posts = get_posts([
            's'              => $q,
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'orderby'        => 'relevance',
        ]);

        $data = array_map(static function ($p) {
            return [
                'id'        => $p->ID,
                'title'     => get_the_title($p),
                'permalink' => get_permalink($p),
                'type'      => $p->post_type,
            ];
        }, $posts);

        return new WP_REST_Response(['success' => true, 'data' => $data]);
    }

    public function api_extract_links(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);
        $post    = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'data' => []], 404);
        }

        $content = $post->post_content;
        $home    = home_url();

        preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER);

        $internal = [];
        $external = [];

        foreach ($matches as $m) {
            $href  = $m[1];
            $text  = wp_strip_all_tags($m[2]);
            $item  = ['href' => $href, 'text' => $text, 'nofollow' => (bool) preg_match('/rel=["\'][^"\']*nofollow/i', $m[0])];

            if (str_starts_with($href, $home) || str_starts_with($href, '/')) {
                $internal[] = $item;
            } else {
                $external[] = $item;
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'internal' => $internal,
                'external' => $external,
                'counts'   => [
                    'internal' => count($internal),
                    'external' => count($external),
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

        $seo_title   = get_post_meta($post_id, self::META_TITLE, true);
        $description = get_post_meta($post_id, self::META_DESCRIPTION, true);
        $keywords_raw = get_post_meta($post_id, self::META_KEYWORDS, true);
        $keywords = [];
        if ($keywords_raw) {
            $decoded = json_decode($keywords_raw, true);
            $keywords = is_array($decoded) ? $decoded : [];
        }

        $focus = get_post_meta($post_id, self::META_KEYWORD, true);

        return [
            'id'            => $post_id,
            'type'          => $post->post_type,
            'status'        => $post->post_status,
            'title'         => get_the_title($post_id),
            'permalink'     => get_permalink($post_id),
            'slug'          => $post->post_name,
            'content'       => $post->post_content,
            'seo_title'     => $seo_title ?: get_the_title($post_id),
            'description'   => $description ?: wp_trim_words(wp_strip_all_tags($post->post_content), 25, '...'),
            'focus_keyword' => $focus,
            'keywords'      => $keywords,
            'canonical'     => get_post_meta($post_id, self::META_CANONICAL, true) ?: get_permalink($post_id),
            'robots'        => get_post_meta($post_id, self::META_ROBOTS, true) ?: ['index' => true, 'follow' => true],
            'og_title'      => get_post_meta($post_id, self::META_OG_TITLE, true),
            'og_description'=> get_post_meta($post_id, self::META_OG_DESC, true),
            'og_image'      => get_post_meta($post_id, self::META_OG_IMAGE, true),
            'x_title'       => get_post_meta($post_id, self::META_X_TITLE, true),
            'x_description' => get_post_meta($post_id, self::META_X_DESC, true),
            'x_image'       => get_post_meta($post_id, self::META_X_IMAGE, true),
            'schema'        => get_post_meta($post_id, self::META_SCHEMA, true),
            'score'         => (int) get_post_meta($post_id, self::META_SCORE, true),
        ];
    }

    public function analyze(int $post_id, array $overrides = []): array
    {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }

        $raw_content = $overrides['content'] ?? $post->post_content;
        $content     = wp_strip_all_tags($raw_content);
        $title       = get_the_title($post_id);
        $seo_title   = (string) ($overrides['seo_title'] ?? get_post_meta($post_id, self::META_TITLE, true));
        $description = (string) ($overrides['description'] ?? get_post_meta($post_id, self::META_DESCRIPTION, true));
        $keyword     = trim((string) ($overrides['focus_keyword'] ?? get_post_meta($post_id, self::META_KEYWORD, true)));
        $slug        = $post->post_name;
        $permalink   = get_permalink($post_id);

        $checks   = [];
        $groups   = [
            'basic'      => ['label' => 'بررسی‌های پایه سئو', 'items' => []],
            'advanced'   => ['label' => 'بررسی‌های تکمیلی', 'items' => []],
            'title'      => ['label' => 'خوانایی عنوان', 'items' => []],
            'content'    => ['label' => 'خوانایی محتوا', 'items' => []],
        ];

        $kw_lower = mb_strtolower($keyword);
        $has_kw   = $keyword !== '';

        // --- Basic ---
        $checks[] = $this->check('focus_keyword', 'کلمه کلیدی اصلی تنظیم شده', $has_kw,
            $has_kw ? 'کلمه کلیدی اصلی تنظیم شده است.' : 'یک کلمه کلیدی اصلی برای این محتوا تنظیم کنید.', 'basic');

        $checks[] = $this->check('kw_in_title', 'کلمه کلیدی در عنوان سئو',
            $has_kw && mb_stripos($seo_title ?: $title, $keyword) !== false,
            'کلمه کلیدی اصلی را به عنوان سئو اضافه کنید.', 'basic');

        $checks[] = $this->check('kw_in_desc', 'کلمه کلیدی در توضیحات متا',
            $has_kw && mb_stripos($description, $keyword) !== false,
            'کلمه کلیدی اصلی را به توضیحات متا اضافه کنید.', 'basic');

        $checks[] = $this->check('kw_in_url', 'کلمه کلیدی در URL',
            $has_kw && (mb_stripos($slug, sanitize_title($keyword)) !== false || mb_stripos($permalink, $keyword) !== false),
            'از کلمه کلیدی اصلی در URL استفاده کنید.', 'basic');

        $first_pct = mb_substr($content, 0, (int) max(1, mb_strlen($content) * 0.1));
        $checks[] = $this->check('kw_in_intro', 'کلمه کلیدی در ۱۰٪ ابتدایی',
            $has_kw && mb_stripos($first_pct, $keyword) !== false,
            'در ابتدای محتوای خود از کلمه کلیدی اصلی استفاده کنید.', 'basic');

        $checks[] = $this->check('kw_in_content', 'کلمه کلیدی در محتوا',
            $has_kw && mb_stripos($content, $keyword) !== false,
            'در محتوا از کلمه کلیدی اصلی استفاده کنید.', 'basic');

        $word_count = str_word_count(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content));
        $checks[] = $this->check('content_length', 'طول محتوا',
            $word_count >= 300,
            sprintf('طول محتوا %d کلمه است.%s', $word_count, $word_count >= 300 ? ' عالی!' : ' حداقل ۳۰۰ کلمه پیشنهاد می‌شود.'), 'basic');

        // --- Advanced ---
        preg_match_all('/<h[2-6]\b[^>]*>(.*?)<\/h[2-6]>/is', $raw_content, $headings);
        $heading_text = implode(' ', array_map('wp_strip_all_tags', $headings[1] ?? []));
        $checks[] = $this->check('kw_in_headings', 'کلمه کلیدی در زیرتیترها',
            $has_kw && mb_stripos($heading_text, $keyword) !== false,
            'استفاده از کلمه کلیدی اصلی در زیرتیترها (H2, H3, …).', 'advanced');

        preg_match_all('/<img\b[^>]*>/i', $raw_content, $images);
        $img_count = count($images[0] ?? []);
        $has_kw_alt = false;
        if ($img_count && $has_kw) {
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

        // Density
        $density = 0.0;
        if ($has_kw && $word_count > 0) {
            $kw_count = mb_substr_count(mb_strtolower($content), $kw_lower);
            $density  = round(($kw_count / $word_count) * 100, 2);
        }
        $checks[] = $this->check('kw_density', 'چگالی کلمه کلیدی',
            $has_kw && $density >= 0.5 && $density <= 2.5,
            sprintf('چگالی کلمه کلیدی %.2f٪ است. هدف حدود ۱٪.', $density), 'advanced');

        $url_len = mb_strlen($permalink);
        $checks[] = $this->check('url_length', 'طول URL',
            $url_len <= 75,
            sprintf('آدرس %d کاراکتر است.%s', $url_len, $url_len <= 75 ? ' خوب!' : ' کوتاه‌تر پیشنهاد می‌شود.'), 'advanced');

        preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\']/i', $raw_content, $links);
        $home = home_url();
        $internal = 0;
        $external = 0;
        $nofollow_ext = 0;
        foreach ($links[1] ?? [] as $href) {
            if (str_starts_with($href, $home) || str_starts_with($href, '/')) {
                $internal++;
            } else {
                $external++;
            }
        }
        preg_match_all('/<a\b[^>]*rel=["\'][^"\']*nofollow[^"\']*["\'][^>]*>/i', $raw_content, $nf);
        $nofollow_ext = count($nf[0] ?? []);

        $checks[] = $this->check('internal_links', 'لینک داخلی',
            $internal >= 1,
            $internal > 0 ? sprintf('%d لینک داخلی یافت شد.', $internal) : 'لینک داخلی پیدا نشد. به صفحات مرتبط لینک دهید.', 'advanced');

        $checks[] = $this->check('external_links', 'لینک خارجی',
            $external >= 1,
            $external > 0 ? sprintf('%d لینک خارجی یافت شد.', $external) : 'هیچ پیوند خروجی یافت نشد. به منابع خارجی پیوند دهید.', 'advanced');

        // --- Title readability ---
        $title_use = $seo_title ?: $title;
        $checks[] = $this->check('kw_start_title', 'کلیدواژه در ابتدای عنوان',
            $has_kw && mb_stripos(mb_strtolower($title_use), $kw_lower) === 0,
            'از کلمه کلیدی اصلی در ابتدای عنوان سئو استفاده کنید.', 'title');

        $title_len = mb_strlen($title_use);
        $checks[] = $this->check('title_length', 'طول عنوان سئو',
            $title_len >= 30 && $title_len <= 60,
            sprintf('طول عنوان: %d کاراکتر (هدف ۳۰–۶۰).', $title_len), 'title');

        $checks[] = $this->check('title_has_number', 'عدد در عنوان',
            (bool) preg_match('/\d/', $title_use),
            'عنوان سئو شما عدد ندارد. با هوش مصنوعی رفع کنید.', 'title');

        // --- Content readability ---
        $desc_len = mb_strlen($description);
        $checks[] = $this->check('desc_length', 'طول توضیحات متا',
            $desc_len >= 120 && $desc_len <= 160,
            sprintf('طول توضیحات: %d کاراکتر (هدف ۱۲۰–۱۶۰).', $desc_len), 'content');

        // Long paragraphs
        preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $raw_content, $paras);
        $long_para = false;
        foreach ($paras[1] ?? [] as $p) {
            if (str_word_count(wp_strip_all_tags($p)) > 120) {
                $long_para = true;
                break;
            }
        }
        $checks[] = $this->check('short_paragraphs', 'پاراگراف‌های کوتاه',
            !$long_para,
            $long_para ? 'حداقل یک پاراگراف طولانی است. پاراگراف‌های کوتاه پیشنهاد می‌شود.' : 'طول پاراگراف‌ها مناسب است.', 'content');

        $has_media = $img_count > 0 || (bool) preg_match('/<(video|iframe|embed)\b/i', $raw_content);
        $checks[] = $this->check('rich_media', 'رسانه غنی',
            $has_media,
            $has_media ? 'رسانه در محتوا وجود دارد.' : 'از تصاویر یا ویدئو استفاده کنید.', 'content');

        // Group
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
            'score'   => $score,
            'passed'  => $passed,
            'total'   => $total,
            'checks'  => $checks,
            'groups'  => $groups,
            'stats'   => [
                'words'       => $word_count,
                'description' => $desc_len,
                'images'      => $img_count,
                'links'       => count($links[0] ?? []),
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

    public function filter_document_title(string $title): string
    {
        if (!is_singular()) {
            return $title;
        }
        $post_id = get_queried_object_id();
        $custom  = get_post_meta($post_id, self::META_TITLE, true);
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

        $robots = $data['robots'];
        $robot_parts = [];
        $robot_parts[] = !empty($robots['index']) ? 'index' : 'noindex';
        $robot_parts[] = !empty($robots['follow']) ? 'follow' : 'nofollow';
        echo '<meta name="robots" content="' . esc_attr(implode(',', $robot_parts)) . '">' . "\n";

        if (!empty($data['description'])) {
            echo '<meta name="description" content="' . esc_attr($data['description']) . '">' . "\n";
        }

        echo '<link rel="canonical" href="' . esc_url($data['canonical']) . '">' . "\n";

        $og_title = $data['og_title'] ?: $data['seo_title'];
        $og_desc  = $data['og_description'] ?: $data['description'];
        $og_image = $data['og_image'] ?: get_the_post_thumbnail_url($post_id, 'full');

        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($og_desc) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($data['permalink']) . '">' . "\n";
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        }

        $x_title = $data['x_title'] ?: $og_title;
        $x_desc  = $data['x_description'] ?: $og_desc;
        $x_image = $data['x_image'] ?: $og_image;

        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($x_title) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($x_desc) . '">' . "\n";
        if ($x_image) {
            echo '<meta name="twitter:image" content="' . esc_url($x_image) . '">' . "\n";
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

        $custom = get_post_meta($post_id, self::META_SCHEMA, true);
        if ($custom) {
            $decoded = json_decode($custom, true);
            if (is_array($decoded)) {
                echo '<script type="application/ld+json">'
                    . wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    . '</script>';
                return;
            }
        }

        $schema = [
            '@context'      => 'https://schema.org',
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
            $schema['image'] = [$image];
        }

        echo '<script type="application/ld+json">'
            . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';
    }
}
