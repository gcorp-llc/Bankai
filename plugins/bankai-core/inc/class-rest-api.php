<?php
/**
 * Bankai Core - REST API Controller
 *
 * @package Bankai
 * @subpackage REST
 */

defined('ABSPATH') || exit;

class Bankai_Rest_API {

    private static ?Bankai_Rest_API $instance = null;
    private string $namespace = 'bankai/v1';

    private const ALLOWED_SETTINGS = [
        'enable_seo_engine',
        'enable_speed_cache',
        'enable_media_studio',
        'enable_ai_studio',
        'seo_modules',
        'speed_modules',
        'media_modules',
        'ai_modules',
        'active_ai_provider',
        'openai_api_key',
        'anthropic_api_key',
        'gemini_api_key',
        'deepseek_api_key',
        'openrouter_api_key',
        'license_key',
    ];

    public static function instance(): Bankai_Rest_API {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
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
                'args'                => [
                    'settings' => [
                        'required' => true,
                        'type'     => 'object',
                    ],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/status', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_system_status'],
            'permission_callback' => [$this, 'check_permissions'],
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
    }

    public function check_permissions(): bool {
        return current_user_can('manage_options');
    }

    public function get_settings(): WP_REST_Response {
        $settings = bankai_get_option();
        if (!is_array($settings)) {
            $settings = [];
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => $settings,
        ], 200);
    }

    public function update_settings(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();

        if (isset($params['settings']) && is_array($params['settings'])) {
            $params = $params['settings'];
        }

        if (empty($params) || !is_array($params)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('ترافیک ورودی نامعتبر است.', 'bankai-core'),
            ], 400);
        }

        $updated  = [];
        $rejected = [];

        foreach ($params as $key => $value) {
            $sanitized_key = sanitize_key($key);

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
            'message' => __('تنظیمات با موفقیت به‌روزرسانی شدند.', 'bankai-core'),
            'updated' => $updated,
        ];

        if (!empty($rejected)) {
            $response['rejected'] = $rejected;
            $response['message'] .= ' ' . sprintf(
                __('(%d کلید نادیده گرفته شد)', 'bankai-core'),
                count($rejected)
            );
        }

        return new WP_REST_Response($response, 200);
    }

    private function sanitize_value($value, string $key) {
        if (strpos($key, 'api_key') !== false || $key === 'license_key') {
            return is_string($value) ? trim($value) : '';
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

    private function sanitize_array(array $arr): array {
        $clean = [];
        foreach ($arr as $k => $v) {
            $clean_key = is_string($k) ? sanitize_key($k) : $k;
            if (is_array($v)) {
                $clean[$clean_key] = $this->sanitize_array($v);
            } elseif (is_bool($v) || is_numeric($v)) {
                $clean[$clean_key] = $v;
            } else {
                $clean[$clean_key] = sanitize_text_field((string) $v);
            }
        }
        return $clean;
    }

    public function get_system_status(): WP_REST_Response {
        $status = [
            'php_version'    => PHP_VERSION,
            'wp_version'     => get_bloginfo('version'),
            'memory_limit'   => WP_MEMORY_LIMIT,
            'memory_usage'   => size_format(memory_get_usage(true)),
            'server'         => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'active_modules' => 7,
            'engine_status'  => 'OPTIMAL',
            'is_rtl'         => is_rtl(),
            'locale'         => get_user_locale(),
            'plugin_version' => defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0',
        ];

        return new WP_REST_Response([
            'success' => true,
            'data'    => $status,
        ], 200);
    }

    public function toggle_module(WP_REST_Request $request): WP_REST_Response {
        $module_id = sanitize_key($request->get_param('id'));
        $enabled   = (bool) $request->get_param('enabled');

        if (empty($module_id)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('شناسه ماژول نامعتبر است.', 'bankai-core'),
            ], 400);
        }

        $module_groups = [
            'seo_modules'   => ['auto_meta', 'sitemap_pro', 'canonical_guard', 'open_graph_ai', 'local_seo_schema', 'llms_txt_builder'],
            'speed_modules' => ['page_caching', 'asset_optimization', 'database_optimizer', 'object_cache', 'server_compression', 'fonts_localizer'],
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
}