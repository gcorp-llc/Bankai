<?php
/**
 * Bankai Framework Theme Options & Dashboard Controller
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || die;

class Bankai_Theme_Admin {

    private static ?Bankai_Theme_Admin $instance = null;

    public static function instance(): Bankai_Theme_Admin {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_theme_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_bankai_toggle_theme_module', [$this, 'ajax_toggle_theme_module']);
    }

    public function register_theme_admin_menu(): void {
        // Register under "Appearance" (نمایش -> گزینه‌های قالب Bankai)
        add_theme_page(
            __('تنظیمات و پیشخوان قالب Bankai', 'bankai-theme'),
            __('گزینه‌های قالب Bankai', 'bankai-theme'),
            'edit_theme_options',
            'bankai-theme',
            [$this, 'render_theme_dashboard_page']
        );
    }

    public function enqueue_admin_assets(string $hook): void {
        $screen = get_current_screen();
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';

        if ($page !== 'bankai-theme' && $hook !== 'appearance_page_bankai-theme' && (!$screen || strpos($screen->id, 'bankai-theme') === false)) {
            return;
        }

        // Fonts
        wp_enqueue_style(
            'bankai-theme-admin-fonts',
            'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700;800&display=swap',
            [],
            BANKAI_THEME_VERSION
        );

        // Core admin styling & Theme dashboard CSS
        if (function_exists('bankai_asset_url')) {
            wp_enqueue_style(
                'bankai-admin-css',
                bankai_asset_url('css/bankai-admin.css'),
                [],
                defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : BANKAI_THEME_VERSION
            );
        }

        wp_enqueue_style(
            'bankai-theme-admin-css',
            BANKAI_THEME_URI . '/assets/css/theme-admin.css',
            [],
            BANKAI_THEME_VERSION
        );

        // Alpine.js for interactive module toggles & customizer shortcuts
        wp_enqueue_script(
            'alpine-js',
            'https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js',
            [],
            '3.13.5',
            true
        );

        // Theme admin script
        wp_enqueue_script(
            'bankai-theme-admin-js',
            BANKAI_THEME_URI . '/assets/js/theme-admin.js',
            ['alpine-js'],
            BANKAI_THEME_VERSION,
            true
        );

        wp_localize_script('bankai-theme-admin-js', 'bankaiThemeData', [
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('bankai_theme_nonce'),
            'customizeUrl'  => admin_url('customize.php'),
            'themeVersion'  => BANKAI_THEME_VERSION,
            'isRtl'         => is_rtl(),
            'modules'       => get_option('bankai_theme_modules', [
                'transparent_header' => true,
                'sticky_header'      => true,
                'mega_menu'          => true,
                'white_label'        => false,
                'custom_fonts'       => true,
                'woocommerce_boost'  => true,
                'scroll_to_top'      => true,
                'page_headers'       => true,
            ]),
        ]);
    }

    public function render_theme_dashboard_page(): void {
        require_once BANKAI_THEME_DIR . '/inc/admin/theme-dashboard.php';
    }

    public function ajax_toggle_theme_module(): void {
        check_ajax_referer('bankai_theme_nonce', 'nonce');
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Unauthorized');
        }

        $module = sanitize_key($_POST['module'] ?? '');
        $state  = (bool)($_POST['state'] ?? false);

        $modules = get_option('bankai_theme_modules', []);
        $modules[$module] = $state;
        update_option('bankai_theme_modules', $modules);

        wp_send_json_success([
            'module' => $module,
            'state'  => $state,
            'message' => __('وضعیت ماژول قالب به‌روزرسانی شد.', 'bankai-theme')
        ]);
    }
}

Bankai_Theme_Admin::instance();
