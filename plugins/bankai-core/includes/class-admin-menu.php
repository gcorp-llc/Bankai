<?php
/**
 * Bankai Core Admin Menu & Page Controller
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;

class Bankai_Admin_Menu {

    private static ?Bankai_Admin_Menu $instance = null;

    public static function instance(): Bankai_Admin_Menu {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu'], 9);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_admin_menu(): void {
        // Main Bankai Core Menu Item
        add_menu_page(
            __('Bankai Core Ecosystem', 'bankai-core'),
            __('Bankai Core', 'bankai-core'),
            'manage_options',
            'bankai-core',
            [$this, 'render_admin_page'],
            'dashicons-superhero',
            2 // Position right below Dashboard
        );

        // Submenus for direct navigation
        add_submenu_page(
            'bankai-core',
            __('Overview & Health', 'bankai-core'),
            __('پیشخوان و سلامت', 'bankai-core'),
            'manage_options',
            'bankai-core#overview',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'bankai-core',
            __('Autonomous SEO Engine', 'bankai-core'),
            __('موتور هوشمند سئو', 'bankai-core'),
            'manage_options',
            'bankai-core#seo-engine',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'bankai-core',
            __('Speed & Cache Hub', 'bankai-core'),
            __('شتاب‌دهنده سرعت و کش', 'bankai-core'),
            'manage_options',
            'bankai-core#speed-cache',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'bankai-core',
            __('Media & Watermark', 'bankai-core'),
            __('استودیو رسانه و واترمارک', 'bankai-core'),
            'manage_options',
            'bankai-core#media-watermark',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'bankai-core',
            __('AI Content Suite', 'bankai-core'),
            __('استودیو هوش مصنوعی', 'bankai-core'),
            'manage_options',
            'bankai-core#ai-studio',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'bankai-core',
            __('Settings & License', 'bankai-core'),
            __('تنظیمات و لایسنس', 'bankai-core'),
            'manage_options',
            'bankai-core#settings-license',
            [$this, 'render_admin_page']
        );
    }

    public function enqueue_admin_assets(string $hook): void {
        if (strpos($hook, 'bankai-core') === false) {
            return;
        }

        // Enqueue Google Fonts & Vazirmatn font
        wp_enqueue_style(
            'bankai-admin-fonts',
            'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700;800&display=swap',
            [],
            BANKAI_CORE_VERSION
        );

        // Enqueue Modern GitHub Primer / Clean Tailwind Style
        wp_enqueue_style(
            'bankai-admin-css',
            BANKAI_CORE_URL . 'assets/css/bankai-admin.css',
            [],
            BANKAI_CORE_VERSION
        );

        // Enqueue Alpine.js v3 for reactive state
        wp_enqueue_script(
            'alpine-js',
            'https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js',
            [],
            '3.13.5',
            true
        );

        // Enqueue Bankai Admin JS
        wp_enqueue_script(
            'bankai-admin-js',
            BANKAI_CORE_URL . 'assets/js/bankai-admin.js',
            ['alpine-js'],
            BANKAI_CORE_VERSION,
            true
        );

        // Pass server-side localized configuration to Alpine.js
        $localized_data = [
            'restUrl'        => esc_url_raw(rest_url('bankai/v1')),
            'nonce'          => wp_create_nonce('wp_rest'),
            'isRtl'          => is_rtl(),
            'siteUrl'        => esc_url(site_url()),
            'wpVersion'      => get_bloginfo('version'),
            'phpVersion'     => phpversion(),
            'serverSoftware' => sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? 'Nginx / LiteSpeed'),
            'adminEmail'     => get_option('admin_email'),
            'settings'       => get_option('bankai_core_settings', []),
        ];

        wp_localize_script('bankai-admin-js', 'bankaiData', $localized_data);
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('شما اجازه دسترسی به این صفحه را ندارید.', 'bankai-core'));
        }

        // Render main admin layout
        require_once BANKAI_CORE_TEMPLATE_DIR . 'admin/admin-layout.php';
    }
}
