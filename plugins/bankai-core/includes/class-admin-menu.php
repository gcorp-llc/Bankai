<?php
/**
 * Admin Menu & Native SSR Asset Loader Class
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

class Bankai_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Register Bankai Core Menu in WP Admin
     */
    public function register_admin_menu() {
        add_menu_page(
            __( 'Bankai Core', 'bankai-core' ),
            __( 'Bankai Core', 'bankai-core' ),
            'manage_options',
            'bankai-core',
            array( $this, 'render_admin_page' ),
            'dashicons-shield',
            30
        );
    }

    /**
     * Render direct Native PHP SSR View Template
     */
    public function render_admin_page() {
        $template_file = BANKAI_CORE_PATH . 'templates/admin/admin-dashboard.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Bankai Core admin view template not found.', 'bankai-core' ) . '</p></div>';
        }
    }

    /**
     * Enqueue Alpine.js, HTMX, Cyberpunk Dark CSS and pass window.bankaiData
     */
    public function enqueue_admin_assets( $hook ) {
        // Enqueue only on Bankai Core admin pages
        if ( strpos( $hook, 'bankai-core' ) === false ) {
            return;
        }

        // 1. Enqueue HTMX Library
        if ( file_exists( BANKAI_CORE_PATH . 'assets/js/htmx.min.js' ) ) {
            wp_enqueue_script(
                'bankai-htmx',
                BANKAI_CORE_URL . 'assets/js/htmx.min.js',
                array(),
                '1.9.10',
                true
            );
        }

        // 2. Enqueue Alpine.js Library
        if ( file_exists( BANKAI_CORE_PATH . 'assets/js/alpine.min.js' ) ) {
            wp_enqueue_script(
                'bankai-alpine',
                BANKAI_CORE_URL . 'assets/js/alpine.min.js',
                array(),
                '3.13.5',
                true
            );
        }

        // 3. Enqueue Bankai Core Admin Custom CSS
        if ( file_exists( BANKAI_CORE_PATH . 'assets/css/bankai-admin.css' ) ) {
            wp_enqueue_style(
                'bankai-admin-style',
                BANKAI_CORE_URL . 'assets/css/bankai-admin.css',
                array(),
                BANKAI_CORE_VERSION
            );
        }

        // 4. Localize REST API URL and Security Nonce
        $script_handle = file_exists( BANKAI_CORE_PATH . 'assets/js/alpine.min.js' ) ? 'bankai-alpine' : 'bankai-htmx';
        wp_localize_script(
            $script_handle,
            'bankaiData',
            array(
                'restUrl' => esc_url_raw( rest_url( 'bankai/v1' ) ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            )
        );
    }
}

new Bankai_Admin_Menu();
