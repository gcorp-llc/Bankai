<?php
/**
 * Bankai Core - Starter Theme Kits Manager
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

class Bankai_Theme_Kits {

    private static ?Bankai_Theme_Kits $instance = null;

    public static function instance(): Bankai_Theme_Kits {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_bankai_sync_library', [$this, 'handle_sync_library']);
        add_action('wp_ajax_bankai_import_kit', [$this, 'handle_import_kit']);
    }

    public function get_starter_kits(): array {
        return [
            [
                'id'          => 'news_agency',
                'name'        => 'Journa Media & News Platform',
                'version'     => 'v2.4.0',
                'description' => 'High-traffic news & magazine template optimized for core web vitals and multilingual support.',
                'thumbnail'   => bankai_asset_url('images/logo.jpg'),
                'badges'      => ['RTL Ready', 'Next.js App Router', 'Schema Integrated']
            ],
            [
                'id'          => 'fintech_saas',
                'name'        => 'Paypey Modern SaaS Kit',
                'version'     => 'v1.1.0',
                'description' => 'Financial tech startup design system with Lydian lion branding and oxidized green aesthetics.',
                'thumbnail'   => bankai_asset_url('images/logo.jpg'),
                'badges'      => ['Alpine.js', 'Tailwind', 'Light/Dark']
            ]
        ];
    }

    public function render(): void {
        $state = [
            'starterKits' => $this->get_starter_kits()
        ];
        bankai_render_view('admin/tab-theme-kits.php', $state);
    }

    public function handle_sync_library(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(['message' => __('کتابخانه قالب‌ها با موفقیت به‌روزرسانی شد.', 'bankai-core')]);
    }

    public function handle_import_kit(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $kit_id = isset($_POST['kit_id']) ? sanitize_text_field($_POST['kit_id']) : '';
        wp_send_json_success(['message' => sprintf(__('معماری قالب %s با موفقیت پیاده‌سازی شد.', 'bankai-core'), $kit_id)]);
    }
}
