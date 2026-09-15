<?php
/**
 * Bankai Core Admin Menu Class
 * Registers the WP Admin menu in position 2 and enqueues scripts & styles.
 */

if (!defined('ABSPATH')) {
    die('Direct access forbidden');
}

class Bankai_Admin_Menu {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_admin_menu'), 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Register main menu item in second position (position 2)
     */
    public function register_admin_menu() {
        $icon_url = defined('BANKAI_CORE_URL')
            ? BANKAI_CORE_URL . 'public/images/logo.jpg'
            : plugins_url('public/images/logo.jpg', dirname(__FILE__));

        add_menu_page(
            __('Bankai Core', 'bankai-core'),
            __('Bankai Core', 'bankai-core'),
            'manage_options',
            'bankai-core',
            array($this, 'render_admin_layout'),
            $icon_url,
            2
        );
    }

    /**
     * Enqueue CSS/JS static assets only on Bankai admin pages
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'bankai-') === false) {
            return;
        }

        $base_url = defined('BANKAI_CORE_URL') ? BANKAI_CORE_URL : plugin_dir_url(dirname(__FILE__));
        $version  = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '4.0.0';

        // Alpine.js and HTMX
        wp_enqueue_script('bankai-alpine', $base_url . 'public/js/alpine.min.js', array(), $version, true);
        wp_enqueue_script('bankai-htmx', $base_url . 'public/js/htmx.min.js', array(), $version, true);

        // Bankai Admin CSS & JS
        wp_enqueue_style('bankai-admin-css', $base_url . 'public/css/bankai-admin.css', array(), $version);
        wp_enqueue_script('bankai-admin-js', $base_url . 'public/js/bankai-admin.js', array('bankai-alpine'), $version, true);

        // Localized Script Data
        wp_localize_script('bankai-admin-js', 'bankaiData', array(
            'apiUrl'   => esc_url_raw(rest_url('bankai/v1')),
            'nonce'    => wp_create_nonce('wp_rest'),
            'isRtl'    => is_rtl(),
            'activeTab'=> isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview'
        ));
    }

    /**
     * Render Master Layout Template
     */
    public function render_admin_layout() {
        $layout_path = defined('BANKAI_CORE_PATH')
            ? BANKAI_CORE_PATH . 'views/admin/layout.php'
            : dirname(dirname(__FILE__)) . '/views/admin/layout.php';

        if (file_exists($layout_path)) {
            include $layout_path;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Bankai Layout template missing.', 'bankai-core') . '</p></div>';
        }
    }
}

new Bankai_Admin_Menu();
