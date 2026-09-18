<?php
/**
 * Bankai Core - Starter Theme Kits Manager
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

class Bankai_Theme_Kits {

    private static ?Bankai_Theme_Kits $instance = null;

    public static function instance(): Bankai_Theme_Kits {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_Theme_Kits {
        return self::instance();
    }

    private function __construct() {
        add_action('wp_ajax_bankai_sync_library', [$this, 'handle_sync_library']);
        add_action('wp_ajax_bankai_import_kit', [$this, 'handle_import_kit']);
    }

    /**
     * Available starter kits
     */
    public function get_starter_kits(): array {
        return [
            [
                'id'          => 'corporate_pro',
                'name'        => 'Corporate Pro',
                'version'     => 'v2.1',
                'description' => 'Full business site with services, team and contact pages. RTL-ready.',
                'thumbnail'   => bankai_asset_url('images/kit-corporate.jpg'),
                'badges'      => ['RTL', 'WooCommerce'],
            ],
            [
                'id'          => 'blog_magazine',
                'name'        => 'Blog Magazine',
                'version'     => 'v1.8',
                'description' => 'High-performance magazine layout with category hubs and AI outlines.',
                'thumbnail'   => bankai_asset_url('images/kit-blog.jpg'),
                'badges'      => ['SEO', 'Fast'],
            ],
            [
                'id'          => 'ecommerce_starter',
                'name'        => 'E-Commerce Starter',
                'version'     => 'v3.0',
                'description' => 'WooCommerce-ready store with product schema and conversion-focused design.',
                'thumbnail'   => bankai_asset_url('images/kit-shop.jpg'),
                'badges'      => ['WooCommerce', 'Schema'],
            ],
            [
                'id'          => 'news_agency',
                'name'        => 'Journa Media & News Platform',
                'version'     => 'v2.4.0',
                'description' => 'High-traffic news & magazine template optimized for Core Web Vitals and multilingual support.',
                'thumbnail'   => bankai_asset_url('images/logo.jpg'),
                'badges'      => ['RTL Ready', 'Schema Integrated'],
            ],
            [
                'id'          => 'fintech_saas',
                'name'        => 'Paypey Modern SaaS Kit',
                'version'     => 'v1.1.0',
                'description' => 'Financial tech startup design system with modern aesthetics and dark mode.',
                'thumbnail'   => bankai_asset_url('images/logo.jpg'),
                'badges'      => ['Alpine.js', 'Tailwind', 'Light/Dark'],
            ],
        ];
    }

    /**
     * AJAX: Sync kit library
     */
    public function handle_sync_library(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $kits = $this->get_starter_kits();

        wp_send_json_success([
            'message' => __('کتابخانه قالب‌ها با موفقیت به‌روزرسانی شد.', 'bankai-core'),
            'kits'    => $kits,
            'count'   => count($kits),
        ]);
    }

    /**
     * AJAX: Import a starter kit (simulated)
     */
    public function handle_import_kit(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $kit_id = isset($_POST['kit_id']) ? sanitize_key(wp_unslash($_POST['kit_id'])) : '';

        if ($kit_id === '') {
            wp_send_json_error(['message' => __('Invalid kit ID.', 'bankai-core')]);
        }

        $kits = $this->get_starter_kits();
        $found = null;
        foreach ($kits as $kit) {
            if ($kit['id'] === $kit_id) {
                $found = $kit;
                break;
            }
        }

        if (!$found) {
            wp_send_json_error(['message' => __('Kit not found in library.', 'bankai-core')]);
        }

        // In a real implementation this would:
        // 1. Download demo content
        // 2. Import posts/pages/menus
        // 3. Configure ACF / theme mods
        // 4. Activate required plugins

        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: kit name */
                __('معماری قالب %s با موفقیت پیاده‌سازی شد.', 'bankai-core'),
                $found['name']
            ),
            'kit' => $found,
        ]);
    }
}