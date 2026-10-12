<?php
/**
 * Bankai Core - SEO Engine
 *
 * Handles:
 * - SEO meta
 * - SEO analysis
 * - Robots
 * - Canonical
 * - JSON-LD Schema
 * - Open Graph
 * - Twitter/X cards
 * - REST API
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
    private const META_CANONICAL   = '_bankai_seo_canonical';
    private const META_ROBOTS      = '_bankai_seo_robots';
    private const META_OG_TITLE    = '_bankai_seo_og_title';
    private const META_OG_DESC     = '_bankai_seo_og_description';
    private const META_OG_IMAGE    = '_bankai_seo_og_image';
    private const META_X_TITLE     = '_bankai_seo_x_title';
    private const META_X_DESC      = '_bankai_seo_x_description';
    private const META_X_IMAGE     = '_bankai_seo_x_image';
    private const META_SCHEMA      = '_bankai_seo_schema';

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'register_meta']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        add_action('wp_head', [$this, 'render_meta'], 1);
        add_action('wp_head', [$this, 'render_schema'], 20);
    }

    /**
     * Register SEO post meta.
     */
    public function register_meta(): void
    {
        $meta_fields = [
            self::META_TITLE,
            self::META_DESCRIPTION,
            self::META_KEYWORD,
            self::META_CANONICAL,
            self::META_OG_TITLE,
            self::META_OG_DESC,
            self::META_OG_IMAGE,
            self::META_X_TITLE,
            self::META_X_DESC,
            self::META_X_IMAGE,
            self::META_SCHEMA,
        ];

        foreach ($meta_fields as $meta_key) {
            register_post_meta(
                '',
                $meta_key,
                [
                    'type'              => 'string',
                    'single'            => true,
                    'show_in_rest'      => true,
                    'sanitize_callback' => [$this, 'sanitize_meta'],
                    'auth_callback'     => function () {
                        return current_user_can('edit_posts');
                    },
                ]
            );
        }

        register_post_meta(
            '',
            self::META_ROBOTS,
            [
                'type'              => 'array',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => [
                    'index'  => true,
                    'follow' => true,
                ],
                'sanitize_callback' => [$this, 'sanitize_robots'],
                'auth_callback'     => function () {
                    return current_user_can('edit_posts');
                },
            ]
        );
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

    /**
     * REST API.
     */
    public function register_rest_routes(): void
    {
        register_rest_route(
            'bankai/v1',
            '/seo/analyze/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'api_analyze'],
                'permission_callback' => [$this, 'permission_check'],
            ]
        );

        register_rest_route(
            'bankai/v1',
            '/seo/(?P<id>\d+)',
            [
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
            ]
        );
    }

    public function permission_check(WP_REST_Request $request): bool
    {
        $post_id = absint($request['id']);

        return $post_id > 0 && current_user_can('edit_post', $post_id);
    }

    /**
     * GET SEO data.
     */
    public function api_get(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => $this->get_seo_data($post_id),
            ]
        );
    }

    /**
     * SAVE SEO data.
     */
    public function api_save(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);
        $params  = $request->get_json_params();

        if (!is_array($params)) {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Invalid payload.'],
                400
            );
        }

        $fields = [
            self::META_TITLE,
            self::META_DESCRIPTION,
            self::META_KEYWORD,
            self::META_CANONICAL,
            self::META_OG_TITLE,
            self::META_OG_DESC,
            self::META_OG_IMAGE,
            self::META_X_TITLE,
            self::META_X_DESC,
            self::META_X_IMAGE,
        ];

        foreach ($fields as $field) {
            $short_key = str_replace('_bankai_seo_', '', $field);

            if (array_key_exists($short_key, $params)) {
                update_post_meta(
                    $post_id,
                    $field,
                    sanitize_text_field(
                        wp_unslash((string) $params[$short_key])
                    )
                );
            }
        }

        if (isset($params['robots']) && is_array($params['robots'])) {
            update_post_meta(
                $post_id,
                self::META_ROBOTS,
                $this->sanitize_robots($params['robots'])
            );
        }

        if (isset($params['schema'])) {
            $schema = wp_json_encode(
                $params['schema'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            update_post_meta(
                $post_id,
                self::META_SCHEMA,
                $schema
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => $this->get_seo_data($post_id),
            ]
        );
    }

    /**
     * SEO analysis API.
     */
    public function api_analyze(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = absint($request['id']);

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => $this->analyze($post_id),
            ]
        );
    }

    /**
     * Return complete SEO state.
     */
    public function get_seo_data(int $post_id): array
    {
        $post = get_post($post_id);

        if (!$post) {
            return [];
        }

        $seo_title = get_post_meta(
            $post_id,
            self::META_TITLE,
            true
        );

        $description = get_post_meta(
            $post_id,
            self::META_DESCRIPTION,
            true
        );

        return [
            'id'          => $post_id,
            'type'        => $post->post_type,
            'status'      => $post->post_status,
            'title'       => get_the_title($post_id),
            'permalink'   => get_permalink($post_id),
            'slug'        => $post->post_name,

            'seo_title'   => $seo_title ?: get_the_title($post_id),

            'description' => $description
                ?: wp_trim_words(
                    wp_strip_all_tags($post->post_content),
                    25,
                    '...'
                ),

            'focus_keyword' =>
                get_post_meta(
                    $post_id,
                    self::META_KEYWORD,
                    true
                ),

            'canonical' =>
                get_post_meta(
                    $post_id,
                    self::META_CANONICAL,
                    true
                ) ?: get_permalink($post_id),

            'robots' =>
                get_post_meta(
                    $post_id,
                    self::META_ROBOTS,
                    true
                ) ?: [
                    'index'  => true,
                    'follow' => true,
                ],

            'og_title' =>
                get_post_meta(
                    $post_id,
                    self::META_OG_TITLE,
                    true
                ),

            'og_description' =>
                get_post_meta(
                    $post_id,
                    self::META_OG_DESC,
                    true
                ),

            'og_image' =>
                get_post_meta(
                    $post_id,
                    self::META_OG_IMAGE,
                    true
                ),

            'x_title' =>
                get_post_meta(
                    $post_id,
                    self::META_X_TITLE,
                    true
                ),

            'x_description' =>
                get_post_meta(
                    $post_id,
                    self::META_X_DESC,
                    true
                ),

            'x_image' =>
                get_post_meta(
                    $post_id,
                    self::META_X_IMAGE,
                    true
                ),
        ];
    }

    /**
     * Analyze content.
     */
    public function analyze(int $post_id): array
    {
        $post = get_post($post_id);

        if (!$post) {
            return [];
        }

        $content = wp_strip_all_tags($post->post_content);
        $title   = get_the_title($post_id);

        $seo_title = get_post_meta(
            $post_id,
            self::META_TITLE,
            true
        );

        $description = get_post_meta(
            $post_id,
            self::META_DESCRIPTION,
            true
        );

        $keyword = trim(
            get_post_meta(
                $post_id,
                self::META_KEYWORD,
                true
            )
        );

        $checks = [];

        /*
         * 1. SEO title.
         */
        $checks[] = [
            'key'     => 'seo_title',
            'label'   => 'عنوان سئو',
            'passed'  => $seo_title !== '',
            'message' => $seo_title
                ? 'عنوان سئو تنظیم شده است.'
                : 'عنوان سئو تعریف نشده است.',
        ];

        /*
         * 2. Meta description.
         */
        $description_length = mb_strlen($description);

        $checks[] = [
            'key'     => 'meta_description',
            'label'   => 'توضیحات متا',
            'passed'  => $description_length >= 120 &&
                         $description_length <= 170,
            'message' => sprintf(
                'طول توضیحات: %d کاراکتر',
                $description_length
            ),
        ];

        /*
         * 3. Focus keyword.
         */
        $checks[] = [
            'key'     => 'focus_keyword',
            'label'   => 'کلمه کلیدی اصلی',
            'passed'  => $keyword !== '',
            'message' => $keyword
                ? 'کلمه کلیدی اصلی تنظیم شده است.'
                : 'کلمه کلیدی اصلی وارد نشده است.',
        ];

        /*
         * 4. Keyword in title.
         */
        $checks[] = [
            'key'     => 'keyword_title',
            'label'   => 'کلمه کلیدی در عنوان',
            'passed'  => $keyword !== '' &&
                        mb_stripos(
                            $seo_title ?: $title,
                            $keyword
                        ) !== false,
            'message' => 'بررسی حضور کلمه کلیدی در عنوان.',
        ];

        /*
         * 5. Keyword in content.
         */
        $checks[] = [
            'key'     => 'keyword_content',
            'label'   => 'کلمه کلیدی در محتوا',
            'passed'  => $keyword !== '' &&
                        mb_stripos($content, $keyword) !== false,
            'message' => 'بررسی حضور کلمه کلیدی در محتوا.',
        ];

        /*
         * 6. Content length.
         */
        $word_count = str_word_count(
            preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content)
        );

        $checks[] = [
            'key'     => 'content_length',
            'label'   => 'طول محتوا',
            'passed'  => $word_count >= 300,
            'message' => sprintf(
                '%d کلمه',
                $word_count
            ),
        ];

        /*
         * 7. H2.
         */
        preg_match_all(
            '/<h2\b[^>]*>/i',
            $post->post_content,
            $h2
        );

        $checks[] = [
            'key'     => 'headings',
            'label'   => 'ساختار Heading',
            'passed'  => count($h2[0]) > 0,
            'message' => count($h2[0]) > 0
                ? 'ساختار Heading وجود دارد.'
                : 'حداقل یک H2 پیشنهاد می‌شود.',
        ];

        /*
         * 8. Images.
         */
        preg_match_all(
            '/<img\b[^>]*>/i',
            $post->post_content,
            $images
        );

        $checks[] = [
            'key'     => 'images',
            'label'   => 'تصاویر',
            'passed'  => count($images[0]) === 0 ||
                        preg_match(
                            '/alt=["\'][^"\']+["\']/i',
                            implode(' ', $images[0])
                        ),
            'message' => count($images[0])
                ? 'تصاویر بررسی شدند.'
                : 'تصویری در محتوا وجود ندارد.',
        ];

        /*
         * 9. Internal links.
         */
        preg_match_all(
            '/<a\b[^>]*href=["\'][^"\']+["\']/i',
            $post->post_content,
            $links
        );

        $checks[] = [
            'key'     => 'internal_links',
            'label'   => 'لینک داخلی',
            'passed'  => count($links[0]) > 0,
            'message' => count($links[0]) > 0
                ? 'لینک داخلی شناسایی شد.'
                : 'لینک داخلی پیدا نشد.',
        ];

        $passed = count(
            array_filter(
                $checks,
                static fn($check) => $check['passed']
            )
        );

        $total = count($checks);

        $score = $total > 0
            ? (int) round(($passed / $total) * 100)
            : 0;

        return [
            'score'  => $score,
            'passed' => $passed,
            'total'  => $total,
            'checks' => $checks,
            'stats'  => [
                'words'       => $word_count,
                'description' => $description_length,
                'images'      => count($images[0]),
                'links'       => count($links[0]),
                'headings'    => count($h2[0]),
            ],
        ];
    }

    /**
     * Render frontend meta.
     */
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

        if (!$robots['index']) {
            echo '<meta name="robots" content="noindex,nofollow">' . "\n";
        }

        echo '<link rel="canonical" href="' .
            esc_url($data['canonical']) .
            '">' . "\n";

        echo '<meta property="og:title" content="' .
            esc_attr(
                $data['og_title'] ?: $data['seo_title']
            ) .
            '">' . "\n";

        echo '<meta property="og:description" content="' .
            esc_attr(
                $data['og_description'] ?: $data['description']
            ) .
            '">' . "\n";

        echo '<meta property="og:url" content="' .
            esc_url($data['permalink']) .
            '">' . "\n";

        if (!empty($data['og_image'])) {
            echo '<meta property="og:image" content="' .
                esc_url($data['og_image']) .
                '">' . "\n";
        }

        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

        echo '<meta name="twitter:title" content="' .
            esc_attr(
                $data['x_title'] ?: $data['seo_title']
            ) .
            '">' . "\n";

        echo '<meta name="twitter:description" content="' .
            esc_attr(
                $data['x_description'] ?: $data['description']
            ) .
            '">' . "\n";
    }

    /**
     * Render JSON-LD.
     */
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

        $custom_schema = get_post_meta(
            $post_id,
            self::META_SCHEMA,
            true
        );

        if ($custom_schema) {
            $decoded = json_decode($custom_schema, true);

            if (is_array($decoded)) {
                echo '<script type="application/ld+json">';
                echo wp_json_encode(
                    $decoded,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                );
                echo '</script>';
                return;
            }
        }

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => $post->post_type === 'post'
                ? 'Article'
                : 'WebPage',

            'headline'    => get_the_title($post_id),
            'description' => get_the_excerpt($post_id),
            'url'         => get_permalink($post_id),
            'datePublished' =>
                get_the_date(DATE_W3C, $post_id),
            'dateModified' =>
                get_the_modified_date(DATE_W3C, $post_id),
        ];

        $image = get_the_post_thumbnail_url(
            $post_id,
            'full'
        );

        if ($image) {
            $schema['image'] = [$image];
        }

        echo '<script type="application/ld+json">';
        echo wp_json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
        echo '</script>';
    }
}