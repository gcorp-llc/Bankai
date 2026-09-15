<?php
defined('ABSPATH') || exit;

class Bankai_Admin_Menu {

    private static ?Bankai_Admin_Menu $instance = null;
    private string $page_hook = '';

    public static function instance(): Bankai_Admin_Menu {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('script_loader_tag', [$this, 'add_defer_attribute'], 10, 2);
    }

    public function register_admin_menu(): void {
        $this->page_hook = add_menu_page(
            __('Bankai Platform', 'bankai-core'),
            __('Bankai Core', 'bankai-core'),
            'manage_options',
            'bankai-core',
            [$this, 'render_admin_layout'],
            'dashicons-bolt',
            2
        );
    }

    public function enqueue_admin_assets(string $hook_suffix): void {
        if (strpos($hook_suffix, 'bankai') === false) {
            return;
        }

        // Fonts
        wp_enqueue_style(
            'bankai-vazirmatn-font',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700;800&display=swap',
            [],
            null
        );

        // Enqueue Core Admin CSS & JS
        wp_enqueue_style(
            'bankai-admin-css',
            bankai_asset_url('css/bankai-admin.css'),
            [],
            BANKAI_CORE_VERSION
        );

        wp_enqueue_script(
            'htmx-js',
            bankai_asset_url('js/htmx.min.js'),
            [],
            '1.9.10',
            true
        );

        wp_enqueue_script(
            'bankai-admin-js',
            bankai_asset_url('js/bankai-admin.js'),
            ['htmx-js'],
            BANKAI_CORE_VERSION,
            true
        );

        wp_enqueue_script(
            'alpine-js',
            bankai_asset_url('js/alpine.min.js'),
            ['bankai-admin-js'],
            '3.13.5',
            true
        );

        $bridge_data = [
            'activeTab'   => 'overview',
            'isRtl'       => is_rtl(),
            'restUrl'     => esc_url_raw(rest_url('bankai/v1/')),
            'nonce'       => wp_create_nonce('bankai_admin_nonce'),
            'restNonce'   => wp_create_nonce('wp_rest'),
            'logoUrl'     => bankai_asset_url('images/logo.jpg')
        ];

        wp_localize_script('bankai-admin-js', 'bankaiData', $bridge_data);
    }

    public function add_defer_attribute(string $tag, string $handle): string {
        if (in_array($handle, ['alpine-js', 'htmx-js'], true)) {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }

    public function render_admin_layout(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'bankai-core'));
        }

        bankai_render_view('admin/layout.php');
    }
}