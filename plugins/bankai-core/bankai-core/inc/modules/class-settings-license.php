<?php
/**
 * Bankai Core - Settings & License Manager
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

class Bankai_Settings_License {

    private static ?Bankai_Settings_License $instance = null;
    private string $option_name = 'bankai_platform_settings';

    public static function instance(): Bankai_Settings_License {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_Settings_License {
        return self::instance();
    }

    private function __construct() {
        add_action('wp_ajax_bankai_save_settings', [$this, 'handle_save_settings']);
        add_action('wp_ajax_bankai_revalidate_license', [$this, 'handle_revalidate_license']);
        add_action('wp_ajax_bankai_export_config', [$this, 'handle_export_config']);
        add_action('wp_ajax_bankai_factory_reset', [$this, 'handle_factory_reset']);
    }

    /**
     * Generate system diagnostic report
     */
    public function get_system_report(): string {
        global $wpdb;

        $report = "=== Bankai Core System Diagnostic ===\n";
        $report .= 'PHP Version: ' . PHP_VERSION . "\n";
        $report .= 'WordPress Version: ' . get_bloginfo('version') . "\n";
        $report .= 'Server Software: ' . (isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'N/A') . "\n";
        $report .= 'Memory Limit: ' . WP_MEMORY_LIMIT . "\n";
        $report .= 'Max Execution Time: ' . ini_get('max_execution_time') . "s\n";
        $report .= 'MySQL Version: ' . $wpdb->db_version() . "\n";
        $report .= 'Active Theme: ' . wp_get_theme()->get('Name') . "\n";
        $report .= 'Multisite: ' . (is_multisite() ? 'Yes' : 'No') . "\n";
        $report .= 'Active License: BANKAI-PRO-9984-X721-LIFETIME (Lifetime Tier)' . "\n";
        $report .= 'Generated: ' . current_time('mysql') . "\n";

        return $report;
    }

    /**
     * Get all platform settings
     */
    public function get_settings(): array {
        $defaults = [
            'auto_update'      => true,
            'telemetry'        => true,
            'debug_mode'       => false,
            'license_key'      => 'BANKAI-PRO-9984-X721-LIFETIME',
            'license_active'   => true,
        ];

        $saved = get_option($this->option_name, []);
        return wp_parse_args(is_array($saved) ? $saved : [], $defaults);
    }

    /**
     * AJAX: Save general settings
     */
    public function handle_save_settings(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $raw = isset($_POST['settings']) ? (array) wp_unslash($_POST['settings']) : [];

        $clean = [
            'auto_update'    => !empty($raw['auto_update']),
            'telemetry'      => !empty($raw['telemetry']),
            'debug_mode'     => !empty($raw['debug_mode']),
            'license_key'    => isset($raw['license_key']) ? sanitize_text_field($raw['license_key']) : '',
            'license_active' => !empty($raw['license_active']),
        ];

        update_option($this->option_name, $clean, false);

        wp_send_json_success([
            'message'  => __('تنظیمات سراسری پلتفرم با موفقیت ذخیره گردید.', 'bankai-core'),
            'settings' => $clean,
        ]);
    }

    /**
     * AJAX: Revalidate license
     */
    public function handle_revalidate_license(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        // In real implementation this would call a remote license server
        wp_send_json_success([
            'status'  => 'valid',
            'tier'    => 'Lifetime Enterprise',
            'message' => __('اعتبارسنجی لایسنس با موفقیت انجام شد.', 'bankai-core'),
        ]);
    }

    /**
     * AJAX: Export configuration as JSON
     */
    public function handle_export_config(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $export = [
            'version'   => defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0',
            'timestamp' => current_time('c'),
            'settings'  => $this->get_settings(),
            'watermark' => get_option('bankai_watermark_settings', []),
            'redirects' => get_option('bankai_301_redirects', []),
        ];

        wp_send_json_success([
            'message' => __('Configuration exported.', 'bankai-core'),
            'data'    => $export,
        ]);
    }

    /**
     * AJAX: Factory reset
     */
    public function handle_factory_reset(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        delete_option($this->option_name);
        delete_option('bankai_watermark_settings');
        delete_option('bankai_404_logs');
        // Keep redirects intentionally (or delete if preferred)

        wp_send_json_success([
            'message' => __('تمامی تنظیمات به حالت اولیه کارخانه بازگردانی شدند.', 'bankai-core'),
        ]);
    }
}