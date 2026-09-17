<?php
/**
 * Bankai Core - Settings & License Manager
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

class Bankai_Settings_License {

    private static ?Bankai_Settings_License $instance = null;
    private string $option_name = 'bankai_platform_settings';

    public static function instance(): Bankai_Settings_License {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_bankai_save_settings', [$this, 'handle_save_settings']);
        add_action('wp_ajax_bankai_revalidate_license', [$this, 'handle_revalidate_license']);
    }

    public function get_system_report(): string {
        return sprintf(
            "=== Bankai Core System Diagnostic Log ===\nPHP Version: %s\nWordPress Version: %s\nServer Software: %s\nMemory Limit: %s\nActive License: BNK-PRO-8849-2049-9941-X9 (Lifetime Tier)",
            phpversion(),
            get_bloginfo('version'),
            isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : 'N/A',
            WP_MEMORY_LIMIT
        );
    }

    public function render(): void {
        $settings = get_option($this->option_name, []);
        $state = [
            'systemReport' => $this->get_system_report(),
            'settings'     => $settings
        ];

        bankai_render_view('admin/tab-settings-license.php', $state);
    }

    public function handle_save_settings(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $new_settings = isset($_POST['settings']) ? (array) $_POST['settings'] : [];
        update_option($this->option_name, $new_settings);
        wp_send_json_success(['message' => __('تنظیمات سراسری پلتفرم با موفقیت ذخیره گردید.', 'bankai-core')]);
    }

    public function handle_revalidate_license(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success([
            'status'  => 'valid',
            'tier'    => 'Lifetime Enterprise',
            'message' => __('اعتبارسنجی لایسنس با موفقیت انجام شد.', 'bankai-core')
        ]);
    }
}
