<?php
/**
 * Bankai Core - REST API Controller
 *
 * Persists ALL plugin settings + core/sub module toggles.
 *
 * @package Bankai
 * @subpackage REST
 */

defined('ABSPATH') || exit;

class Bankai_Rest_API
{
    private static ?Bankai_Rest_API $instance = null;
    private string $namespace = 'bankai/v1';

    /**
     * Whitelist of top-level keys allowed in bankai_core_settings.
     * Arrays (modules, keys, integrations) are accepted as objects.
     */
    private const ALLOWED_SETTINGS = [
        // Core flags
        'active_modules',
        'enable_seo_engine',
        'enable_speed_cache',
        'enable_media_studio',
        'enable_ai_studio',
        // Sub-module maps
        'seo_modules',
        'speed_modules',
        'media_modules',
        'ai_modules',
        // AI providers
        'active_ai_provider',
        'openai_api_key',
        'anthropic_api_key',
        'gemini_api_key',
        'deepseek_api_key',
        'openrouter_api_key',
        'ai_keys',
        // SEO integrations
        'seo_integrations',
        'google_analytics_id',
        'google_tag_manager_id',
        'bing_webmaster',
        'yandex_verification',
        'google_site_verification',
        'robots_txt',
        'sitemap_enabled',
        'seo_fixed_keywords',
        // License / theme
        'license_key',
        'theme_container_width',
        'default_typography',
        // Media
        'watermark_settings',
        // Cache
        'cache_ttl',
        'cache_exclusions',
        'cache_enabled',
    ];

    private const CORE_MODULE_KEYS = [
        'seo_engine',
        'speed_cache',
        'media_watermark',
        'ai_studio',
        'llms_txt',
        'jalali_calendar',
        'settings_license',
        'pwa',
    ];

    public static function instance(): Bankai_Rest_API
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
        // AJAX fallback for environments where REST nonce fails
        add_action('wp_ajax_bankai_toggle_core_module', [$this, 'ajax_toggle_core_module']);
        add_action('wp_ajax_bankai_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_bankai_articles_list', [$this, 'ajax_list_articles']);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/settings', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'check_permissions'],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_settings'],
                'permission_callback' => [$this, 'check_permissions'],
            ],
        ]);

        register_rest_route($this->namespace, '/status', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_system_status'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        // Sub-modules (seo_modules, speed_modules, …)
        
        register_rest_route($this->namespace, '/seo/module-settings', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_seo_module_settings'],
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'save_seo_module_settings'],
                'permission_callback' => static fn() => current_user_can('manage_options'),
            ],
        ]);

register_rest_route($this->namespace, '/module/(?P<id>[a-zA-Z0-9_-]+)', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'toggle_module'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'id'      => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                ],
                'enabled' => [
                    'required' => true,
                    'type'     => 'boolean',
                ],
            ],
        ]);

        // Core engine modules → bankai_core_settings.active_modules
        
        // Articles list — always available (not gated by SEO module load)
        register_rest_route($this->namespace, '/articles', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'api_articles_list'],
            'permission_callback' => static fn(): bool => current_user_can('edit_posts'),
            'args'                => [
                'page'      => ['type' => 'integer', 'default' => 1, 'sanitize_callback' => 'absint'],
                'per_page'  => ['type' => 'integer', 'default' => 20, 'sanitize_callback' => 'absint'],
                'search'    => ['type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'orderby'   => ['type' => 'string', 'default' => 'date', 'sanitize_callback' => 'sanitize_key'],
                'order'     => ['type' => 'string', 'default' => 'DESC', 'sanitize_callback' => 'sanitize_text_field'],
                'post_type' => ['type' => 'string', 'default' => 'post', 'sanitize_callback' => 'sanitize_key'],
            ],
        ]);
        register_rest_route($this->namespace, '/seo/articles', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'api_articles_list'],
            'permission_callback' => static fn(): bool => current_user_can('edit_posts'),
            'args'                => [
                'page'     => ['type' => 'integer', 'default' => 1, 'sanitize_callback' => 'absint'],
                'per_page' => ['type' => 'integer', 'default' => 20, 'sanitize_callback' => 'absint'],
                'search'   => ['type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'orderby'  => ['type' => 'string', 'default' => 'date', 'sanitize_callback' => 'sanitize_key'],
                'order'    => ['type' => 'string', 'default' => 'DESC', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route($this->namespace, '/core-module/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'toggle_core_module'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'key'     => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                ],
                'enabled' => [
                    'required' => true,
                    'type'     => 'boolean',
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/telemetry', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_telemetry'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions(): bool
    {
        return current_user_can('manage_options');
    }

    public function get_settings(): WP_REST_Response
    {
        $settings = bankai_get_option();
        if (!is_array($settings)) {
            $settings = [];
        }

        // Never expose raw secrets in full — mask long keys for UI
        $masked = $settings;
        foreach (['openai_api_key', 'anthropic_api_key', 'gemini_api_key', 'deepseek_api_key', 'openrouter_api_key', 'license_key'] as $secret) {
            if (!empty($masked[$secret]) && is_string($masked[$secret]) && strlen($masked[$secret]) > 8) {
                $masked[$secret . '_set'] = true;
                $masked[$secret]          = substr($masked[$secret], 0, 4) . str_repeat('*', 8);
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => $masked,
        ], 200);
    }

    public function update_settings(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }
        if (isset($params['settings']) && is_array($params['settings'])) {
            $params = $params['settings'];
        }

        if ($params === []) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('ترافیک ورودی نامعتبر است.', 'bankai-core'),
            ], 400);
        }

        $updated  = [];
        $rejected = [];

        foreach ($params as $key => $value) {
            $sanitized_key = sanitize_key((string) $key);

            if (!in_array($sanitized_key, self::ALLOWED_SETTINGS, true)) {
                $rejected[] = $sanitized_key;
                continue;
            }

            $sanitized_value = $this->sanitize_value($value, $sanitized_key);
            bankai_update_option($sanitized_key, $sanitized_value);
            $updated[] = $sanitized_key;
        }

        $response = [
            'success' => true,
            'message' => __('تنظیمات با موفقیت ذخیره شد.', 'bankai-core'),
            'updated' => $updated,
        ];

        if (!empty($rejected)) {
            $response['rejected'] = $rejected;
            $response['message'] .= ' ' . sprintf(
                /* translators: %d: number of rejected keys */
                __('(%d کلید نادیده گرفته شد)', 'bankai-core'),
                count($rejected)
            );
        }

        return new WP_REST_Response($response, 200);
    }

    /**
     * Sanitize a setting value by key.
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitize_value($value, string $key)
    {
        // Boolean maps / arrays
        if (in_array($key, ['active_modules', 'seo_modules', 'speed_modules', 'media_modules', 'ai_modules', 'ai_keys', 'seo_integrations', 'watermark_settings', 'jalali_settings'], true)) {
            return $this->sanitize_array(is_array($value) ? $value : []);
        }

        if (in_array($key, ['sitemap_enabled', 'cache_enabled'], true)) {
            return (bool) $value;
        }

        if (in_array($key, ['theme_container_width', 'cache_ttl'], true)) {
            return absint($value);
        }

        if ($key === 'robots_txt') {
            return sanitize_textarea_field((string) $value);
        }

        // API keys — allow empty (clear) or store as-is after light sanitize
        if (str_contains($key, 'api_key') || $key === 'license_key') {
            return sanitize_text_field((string) $value);
        }

        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return $value + 0;
        }
        if (is_array($value)) {
            return $this->sanitize_array($value);
        }

        return sanitize_text_field((string) $value);
    }

    /**
     * @param array<mixed> $arr
     * @return array<mixed>
     */
    private function sanitize_array(array $arr): array
    {
        $clean = [];
        foreach ($arr as $k => $v) {
            $clean_key = is_string($k) ? sanitize_key($k) : $k;
            if (is_array($v)) {
                $clean[$clean_key] = $this->sanitize_array($v);
            } elseif (is_bool($v)) {
                $clean[$clean_key] = $v;
            } elseif (is_numeric($v)) {
                $clean[$clean_key] = $v + 0;
            } else {
                $clean[$clean_key] = sanitize_text_field((string) $v);
            }
        }
        return $clean;
    }

    public function get_system_status(): WP_REST_Response
    {
        $status = [
            'php_version'    => PHP_VERSION,
            'wp_version'     => get_bloginfo('version'),
            'memory_limit'   => WP_MEMORY_LIMIT,
            'memory_usage'   => size_format(memory_get_usage(true)),
            'server'         => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'active_modules' => function_exists('bankai_count_active_modules')
                ? bankai_count_active_modules()
                : $this->count_active_core_modules(),
            'engine_status'  => 'OK',
            'telemetry'      => class_exists('Bankai_Dashboard_Stats')
                ? Bankai_Dashboard_Stats::telemetry_stats()
                : [],
            'is_rtl'         => is_rtl(),
            'locale'         => get_user_locale(),
            'plugin_version' => defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0',
        ];

        return new WP_REST_Response([
            'success' => true,
            'data'    => $status,
        ], 200);
    }

    public function get_telemetry(): WP_REST_Response
    {
        $data = class_exists('Bankai_Dashboard_Stats')
            ? Bankai_Dashboard_Stats::telemetry_stats()
            : [];

        return new WP_REST_Response([
            'success' => true,
            'data'    => $data,
        ], 200);
    }

    /**
     * Toggle sub-module inside seo_modules / speed_modules / …
     */
    
    public function get_seo_module_settings(WP_REST_Request $request): WP_REST_Response
    {
        $id = sanitize_key((string) $request->get_param('id'));
        $all = get_option('bankai_seo_module_settings', []);
        if (!is_array($all)) {
            $all = [];
        }
        $data = is_array($all[$id] ?? null) ? $all[$id] : [];
        // Merge live flags
        if ($id === 'sitemap_pro' && class_exists('Bankai_SEO_Integrations')) {
            $s = Bankai_SEO_Integrations::instance()->get_settings();
            $data['enabled'] = !empty($s['sitemap_enabled']);
            $data['include_images'] = !empty($s['sitemap_include_images']);
        }
        if ($id === 'llms_txt_builder' && function_exists('bankai_is_module_active')) {
            $data['enabled'] = bankai_is_module_active('llms_txt');
        }
        return new WP_REST_Response(['success' => true, 'data' => $data], 200);
    }

    public function save_seo_module_settings(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            return new WP_REST_Response(['success' => false, 'message' => 'داده نامعتبر'], 400);
        }
        $id = sanitize_key((string) ($params['id'] ?? ''));
        $settings = is_array($params['settings'] ?? null) ? $params['settings'] : [];
        if ($id === '') {
            return new WP_REST_Response(['success' => false, 'message' => 'شناسه خالی'], 400);
        }
        $clean = [];
        foreach ($settings as $k => $v) {
            $k = sanitize_key((string) $k);
            if (is_bool($v) || $v === '0' || $v === '1' || $v === 0 || $v === 1) {
                $clean[$k] = (bool) $v;
            } elseif (is_numeric($v)) {
                $clean[$k] = 0 + $v;
            } else {
                $clean[$k] = sanitize_text_field((string) $v);
            }
        }
        $all = get_option('bankai_seo_module_settings', []);
        if (!is_array($all)) {
            $all = [];
        }
        $all[$id] = $clean;
        update_option('bankai_seo_module_settings', $all, false);

        // Side effects
        if ($id === 'sitemap_pro' && class_exists('Bankai_SEO_Integrations')) {
            $si = Bankai_SEO_Integrations::instance()->get_settings();
            if (array_key_exists('enabled', $clean)) {
                $si['sitemap_enabled'] = !empty($clean['enabled']);
            }
            if (array_key_exists('include_images', $clean)) {
                $si['sitemap_include_images'] = !empty($clean['include_images']);
            }
            Bankai_SEO_Integrations::instance()->save_settings($si);
            $mods = bankai_get_option('seo_modules', []);
            if (!is_array($mods)) {
                $mods = [];
            }
            $mods['sitemap_pro'] = !empty($clean['enabled']);
            bankai_update_option('seo_modules', $mods);
            flush_rewrite_rules(false);
        }
        if ($id === 'llms_txt_builder' && function_exists('bankai_set_module_active')) {
            bankai_set_module_active('llms_txt', !empty($clean['enabled']));
            $mods = bankai_get_option('seo_modules', []);
            if (!is_array($mods)) {
                $mods = [];
            }
            $mods['llms_txt_builder'] = !empty($clean['enabled']);
            bankai_update_option('seo_modules', $mods);
            flush_rewrite_rules(false);
        }

        return new WP_REST_Response(['success' => true, 'data' => $clean, 'message' => 'ذخیره شد'], 200);
    }

public function toggle_module(WP_REST_Request $request): WP_REST_Response
    {
        $module_id = sanitize_key((string) $request->get_param('id'));
        $enabled   = (bool) $request->get_param('enabled');

        if ($module_id === '') {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('شناسه ماژول نامعتبر است.', 'bankai-core'),
            ], 400);
        }

        $module_groups = [
            'seo_modules'   => ['auto_meta', 'sitemap_pro', 'canonical_guard', 'open_graph_ai', 'local_seo_schema', 'llms_txt_builder'],
            'speed_modules' => ['page_caching', 'asset_optimization', 'database_optimizer', 'object_cache', 'server_compression', 'fonts_localizer', 'browser_cache'],
            'media_modules' => ['webp_avif_converter', 'dynamic_watermarking', 'exif_metadata_scrubber', 'cloud_offload_cdn', 'retina_generator', 'svg_sanitizer'],
            'ai_modules'    => ['smart_excerpt_generator', 'llm_manifest_auto', 'meta_desc_auto', 'bulk_content_enricher', 'faq_schema_ai', 'brand_voice_tuning'],
        ];

        $target_group = null;
        foreach ($module_groups as $group => $ids) {
            if (in_array($module_id, $ids, true)) {
                $target_group = $group;
                break;
            }
        }

        if (!$target_group) {
            // Maybe it's a core module id sent to the wrong endpoint — try core
            if (in_array($module_id, self::CORE_MODULE_KEYS, true)) {
                return $this->toggle_core_module_internal($module_id, $enabled);
            }
            return new WP_REST_Response([
                'success' => false,
                'message' => __('ماژول یافت نشد.', 'bankai-core'),
            ], 404);
        }

        $modules = bankai_get_option($target_group, []);
        if (!is_array($modules)) {
            $modules = [];
        }
        $modules[$module_id] = $enabled;
        bankai_update_option($target_group, $modules);

        if ($target_group === 'speed_modules' && in_array($module_id, ['browser_cache', 'server_compression'], true)) {
            if (class_exists('Bankai_Speed_Cache')) {
                Bankai_Speed_Cache::sync_htaccess_rules_static();
            }
        }

        // Sync SEO feature flags with real endpoints
        if ($target_group === 'seo_modules' && $module_id === 'sitemap_pro' && class_exists('Bankai_SEO_Integrations')) {
            $si = Bankai_SEO_Integrations::instance()->get_settings();
            $si['sitemap_enabled'] = $enabled;
            Bankai_SEO_Integrations::instance()->save_settings($si);
            flush_rewrite_rules(false);
        }
        if ($target_group === 'seo_modules' && $module_id === 'llms_txt_builder' && function_exists('bankai_set_module_active')) {
            bankai_set_module_active('llms_txt', $enabled);
            flush_rewrite_rules(false);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => $enabled
                ? __('ماژول فعال شد.', 'bankai-core')
                : __('ماژول غیرفعال شد.', 'bankai-core'),
            'module'  => $module_id,
            'enabled' => $enabled,
            'group'   => $target_group,
        ], 200);
    }

    /**
     * Toggle core engine module (seo_engine, ai_studio, …).
     * Persists to bankai_core_settings.active_modules.
     * When seo_engine is disabled, editor sidebar + frontend meta/schema stop on next request.
     */
    public function toggle_core_module(WP_REST_Request $request): WP_REST_Response
    {
        $key     = sanitize_key((string) $request->get_param('key'));
        $enabled = (bool) $request->get_param('enabled');

        return $this->toggle_core_module_internal($key, $enabled);
    }

    private function toggle_core_module_internal(string $key, bool $enabled): WP_REST_Response
    {
        if (!in_array($key, self::CORE_MODULE_KEYS, true)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('ماژول یافت نشد.', 'bankai-core'),
            ], 404);
        }

        if (function_exists('bankai_set_module_active')) {
            bankai_set_module_active($key, $enabled);
        } else {
            $settings = get_option('bankai_core_settings', []);
            if (!is_array($settings)) {
                $settings = [];
            }
            if (!isset($settings['active_modules']) || !is_array($settings['active_modules'])) {
                $settings['active_modules'] = [];
            }
            $settings['active_modules'][$key] = $enabled;
            update_option('bankai_core_settings', $settings, false);
        }

        $count = function_exists('bankai_count_active_modules')
            ? bankai_count_active_modules()
            : $this->count_active_core_modules();

        $extra = '';
        if ($key === 'seo_engine' && !$enabled) {
            $extra = ' ' . __('ابزار سئو در ویرایشگر پست و خروجی متا غیرفعال شد.', 'bankai-core');
        }

        return new WP_REST_Response([
            'success'      => true,
            'message'      => ($enabled
                ? __('ماژول فعال شد.', 'bankai-core')
                : __('ماژول غیرفعال شد.', 'bankai-core')) . $extra,
            'key'          => $key,
            'enabled'      => $enabled,
            'active_count' => $count,
        ], 200);
    }

    private function count_active_core_modules(): int
    {
        $keys = ['seo_engine', 'speed_cache', 'media_watermark', 'ai_studio', 'llms_txt', 'jalali_calendar'];
        $n    = 0;
        foreach ($keys as $k) {
            if (function_exists('bankai_is_module_active') && bankai_is_module_active($k)) {
                $n++;
            }
        }
        return $n;
    }

    /* ---------- AJAX fallbacks ---------- */

    public function ajax_toggle_core_module(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $key     = isset($_POST['key']) ? sanitize_key(wp_unslash($_POST['key'])) : '';
        $enabled = isset($_POST['enabled']) && ($_POST['enabled'] === '1' || $_POST['enabled'] === 'true' || $_POST['enabled'] === true);

        $response = $this->toggle_core_module_internal($key, $enabled);
        $data     = $response->get_data();
        if (!empty($data['success'])) {
            wp_send_json_success($data);
        }
        wp_send_json_error($data);
    }

    public function ajax_save_settings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $raw = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $params  = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $params = $raw;
        } else {
            $params = [];
        }

        $updated = [];
        foreach ($params as $key => $value) {
            $sk = sanitize_key((string) $key);
            if (!in_array($sk, self::ALLOWED_SETTINGS, true)) {
                continue;
            }
            bankai_update_option($sk, $this->sanitize_value($value, $sk));
            $updated[] = $sk;
        }

        wp_send_json_success([
            'message' => __('تنظیمات ذخیره شد.', 'bankai-core'),
            'updated' => $updated,
        ]);
    }

    /**
     * List posts/pages for Articles hub (works even if SEO module class is offline).
     */
    public function api_articles_list(\WP_REST_Request $request): \WP_REST_Response
    {
        $page     = max(1, absint($request->get_param('page') ?: 1));
        $per_page = min(100, max(5, absint($request->get_param('per_page') ?: 10)));
        $search   = sanitize_text_field((string) ($request->get_param('search') ?? ''));
        $orderby  = sanitize_key((string) ($request->get_param('orderby') ?? 'date'));
        $order    = strtoupper(sanitize_text_field((string) ($request->get_param('order') ?? 'DESC'))) === 'ASC' ? 'ASC' : 'DESC';

        $post_type = sanitize_key((string) ($request->get_param('post_type') ?? 'post'));
        if ($post_type === 'any') {
            $types = ['post', 'page'];
        } elseif (in_array($post_type, ['post', 'page'], true)) {
            $types = [$post_type];
        } else {
            $types = ['post', 'page'];
        }

        $args = [
            'post_type'              => $types,
            'post_status'            => ['publish', 'draft', 'pending', 'future', 'private'],
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'order'                  => $order,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => false,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ];
        if ($search !== '') {
            $args['s'] = $search;
        }

        // Avoid meta_key orderby excluding posts without meta
        $allowed_order = ['date', 'modified', 'title', 'ID'];
        if (in_array($orderby, $allowed_order, true)) {
            $args['orderby'] = $orderby;
        } elseif ($orderby === 'seo_score') {
            $args['meta_query'] = [
                'relation' => 'OR',
                ['key' => '_bankai_seo_score', 'compare' => 'EXISTS'],
                ['key' => '_bankai_seo_score', 'compare' => 'NOT EXISTS'],
            ];
            $args['orderby'] = 'meta_value_num date';
            $args['meta_key'] = '_bankai_seo_score';
        } else {
            $args['orderby'] = 'date';
        }

        $q = new \WP_Query($args);
        $items = [];
        foreach ($q->posts as $p) {
            $id = (int) $p->ID;
            $status_obj = get_post_status_object($p->post_status);
            $items[] = [
                'id'             => $id,
                'title'          => get_the_title($p) ?: ('#' . $id),
                'status'         => $p->post_status,
                'status_label'   => $status_obj->label ?? $p->post_status,
                'type'           => $p->post_type,
                'permalink'      => get_permalink($p),
                'edit_url'       => get_edit_post_link($id, 'raw'),
                'edit_link'      => get_edit_post_link($id, 'raw'),
                'seo_title'      => (string) get_post_meta($id, '_bankai_seo_title', true),
                'description'    => (string) get_post_meta($id, '_bankai_seo_description', true),
                'focus_keyword'  => (string) (get_post_meta($id, '_bankai_seo_focus_keyword', true) ?: get_post_meta($id, '_bankai_focus_keyword', true)),
                'views'          => (int) get_post_meta($id, '_bankai_post_views', true),
                // Unified scores: audit group scores are source of truth for SEO·AEO·GEO
                'seo_audit'      => (int) (get_post_meta($id, '_bankai_audit_seo', true) ?: get_post_meta($id, '_bankai_seo_score', true)),
                'aeo_audit'      => (int) (get_post_meta($id, '_bankai_audit_aeo', true) ?: get_post_meta($id, '_bankai_aeo_score', true)),
                'geo_audit'      => (int) (get_post_meta($id, '_bankai_audit_geo', true) ?: get_post_meta($id, '_bankai_geo_score', true)),
                'seo_score'      => (int) (get_post_meta($id, '_bankai_audit_seo', true) ?: get_post_meta($id, '_bankai_seo_score', true)),
                'score'          => (int) (get_post_meta($id, '_bankai_audit_score', true) ?: get_post_meta($id, '_bankai_seo_score', true)),
                'audit_score'    => (int) get_post_meta($id, '_bankai_audit_score', true),
                'cover'          => get_the_post_thumbnail_url($id, 'thumbnail') ?: '',
                'modified'       => get_the_modified_date('Y-m-d H:i', $p),
                'date'           => get_the_date('Y-m-d H:i', $p),
                'internal_links' => 0,
                'external_links' => 0,
            ];
        }

        return new \WP_REST_Response([
            'success' => true,
            'data'    => [
                'items'       => $items,
                'total'       => (int) $q->found_posts,
                'total_pages' => (int) $q->max_num_pages,
                'page'        => $page,
                'per_page'    => $per_page,
            ],
        ]);
    }



    public function ajax_list_articles(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $req = new \WP_REST_Request('GET');
        $req->set_param('page', absint($_POST['page'] ?? 1));
        $req->set_param('per_page', absint($_POST['per_page'] ?? 10));
        $req->set_param('search', sanitize_text_field(wp_unslash($_POST['search'] ?? '')));
        $req->set_param('orderby', sanitize_key($_POST['orderby'] ?? 'date'));
        $req->set_param('order', sanitize_text_field($_POST['order'] ?? 'DESC'));
        $req->set_param('post_type', sanitize_key($_POST['post_type'] ?? 'post'));
        $res = $this->api_articles_list($req);
        $data = $res->get_data();
        if (!empty($data['success'])) {
            wp_send_json_success($data['data'] ?? $data);
        }
        wp_send_json_error(['message' => 'Failed']);
    }

}
