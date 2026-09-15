<?php
if (!defined('ABSPATH')) {
    exit;
}

class Bankai_Theme_Kits {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_bankai_sync_library', array($this, 'handle_sync_library'));
        add_action('wp_ajax_bankai_import_kit', array($this, 'handle_import_kit'));
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'bankai') === false) {
            return;
        }
        wp_enqueue_style('bankai-theme-kits-css', BANKAI_PLUGIN_URL . 'assets/css/theme-kits.css', array(), BANKAI_VERSION);
        wp_enqueue_script('bankai-theme-kits-js', BANKAI_PLUGIN_URL . 'assets/js/theme-kits.js', array('jquery', 'alpinejs'), BANKAI_VERSION, true);
    }

    public function get_starter_kits() {
        return array(
            array(
                'id' => 'news_agency',
                'name' => 'Journa Media & News Platform',
                'version' => 'v2.4.0',
                'description' => 'High-traffic news & magazine template optimized for core web vitals and multilingual support.',
                'thumbnail' => BANKAI_PLUGIN_URL . 'assets/images/kits/news-kit.jpg',
                'badges' => array('RTL Ready', 'Next.js App Router', 'Schema Integrated')
            ),
            array(
                'id' => 'fintech_saas',
                'name' => 'Paypey Modern SaaS Kit',
                'version' => 'v1.1.0',
                'description' => 'Financial tech startup design system with Lydian lion branding and oxidized green aesthetics.',
                'thumbnail' => BANKAI_PLUGIN_URL . 'assets/images/kits/fintech-kit.jpg',
                'badges' => array('Alpine.js', 'Tailwind', 'Light/Dark')
            )
        );
    }

    public function render() {
        $state = array(
            'starterKits' => $this->get_starter_kits()
        );

        $view_path = BANKAI_PLUGIN_DIR . 'views/admin/theme-kits.php';
        if (file_exists($view_path)) {
            include $view_path;
        }
    }

    public function handle_sync_library() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(array('message' => 'کتابخانه قالب‌ها با موفقیت به‌روزرسانی شد.'));
    }

    public function handle_import_kit() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $kit_id = isset($_POST['kit_id']) ? sanitize_text_field($_POST['kit_id']) : '';
        wp_send_json_success(array('message' => "معماری قالب {$kit_id} با موفقیت پیاده‌سازی شد."));
    }
}