<?php
defined('ABSPATH') || exit;

class Bankai_Rest_API {

    private static ?Bankai_Rest_API $instance = null;
    private string $namespace = 'bankai/v1';

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
            ],
        ]);

        register_rest_route($this->namespace, '/status', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_system_status'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions(): bool {
        return current_user_can('manage_options');
    }

    public function get_settings(): WP_REST_Response {
        $settings = bankai_get_option();
        return new WP_REST_Response([
            'success' => true,
            'data'    => $settings,
        ], 200);
    }

    public function update_settings(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();
        if (empty($params) || !is_array($params)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('ترافیک ورودی نامعتبر است.', 'bankai-core')
            ], 400);
        }

        foreach ($params as $key => $value) {
            $sanitized_key = sanitize_key($key);
            if (is_array($value)) {
                $sanitized_value = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_value = is_bool($value) ? $value : sanitize_text_field((string)$value);
            }
            bankai_update_option($sanitized_key, $sanitized_value);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('تنظیمات با موفقیت به‌روزرسانی شدند.', 'bankai-core')
        ], 200);
    }

    public function get_system_status(): WP_REST_Response {
        return new WP_REST_Response([
            'success' => true,
            'status'  => [
                'php_version'     => PHP_VERSION,
                'wp_version'      => get_bloginfo('version'),
                'memory_limit'    => WP_MEMORY_LIMIT,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'active_theme'    => wp_get_theme()->get('Name'),
            ]
        ], 200);
    }
}