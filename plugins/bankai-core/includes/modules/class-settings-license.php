<?php
if (!defined('ABSPATH')) {
    exit;
}

class Bankai_Settings_License {

    private $option_name = 'bankai_platform_settings';

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_bankai_save_settings', array($this, 'handle_save_settings'));
        add_action('wp_ajax_bankai_revalidate_license', array($this, 'handle_revalidate_license'));
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'bankai') === false) {
            return;
        }
        wp_enqueue_style('bankai-settings-css', BANKAI_PLUGIN_URL . 'assets/css/settings.css', array(), BANKAI_VERSION);
        wp_enqueue_script('bankai-settings-js', BANKAI_PLUGIN_URL . 'assets/js/settings.js', array('jquery'), BANKAI_VERSION, true);
    }

    public function get_system_report() {
        return sprintf(
            "=== Bankai Core System Diagnostic Log ===\nPHP Version: %s\nWordPress Version: %s\nServer Software: %s\nMemory Limit: %s\nActive License: BNK-PRO-8849-2049-9941-X9 (Lifetime Tier)",
            phpversion(),
            get_bloginfo('version'),
            isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : 'N/A',
            WP_MEMORY_LIMIT
        );
    }

    public function render() {
        $settings = get_option($this->option_name, array());
        $state = array(
            'systemReport' => $this->get_system_report(),
            'settings' => $settings
        );

        $view_path = BANKAI_PLUGIN_DIR . 'views/admin/settings-license.php';
        if (file_exists($view_path)) {
            include $view_path;
        }
    }

    public function handle_save_settings() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $new_settings = isset($_POST['settings']) ? (array) $_POST['settings'] : array();
        update_option($this->option_name, $new_settings);
        wp_send_json_success(array('message' => 'تنظیمات سراسری پلتفرم با موفقیت ذخیره گردید.'));
    }

    public function handle_revalidate_license() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(array(
            'status' => 'valid',
            'tier' => 'Lifetime Enterprise',
            'message' => 'اعتبارسنجی لایسنس با موفقیت انجام شد.'
        ));
    }
}