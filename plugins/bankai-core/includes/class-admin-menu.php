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
     * Register Bankai Core Menu & Submenus in WP Admin
     */
    public function register_admin_menu() {
        // Main parent menu
        add_menu_page(
            __( 'Bankai Core', 'bankai-core' ),
            __( 'Bankai Core', 'bankai-core' ),
            'manage_options',
            'bankai-core',
            array( $this, 'render_admin_page' ),
            'dashicons-shield',
            30
        );

        // Submenus
        add_submenu_page(
            'bankai-core',
            __( 'Overview & Health', 'bankai-core' ),
            __( 'Overview & Health', 'bankai-core' ),
            'manage_options',
            'bankai-core',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'bankai-core',
            __( 'Theme & Starter Kits', 'bankai-core' ),
            __( 'Theme & Kits', 'bankai-core' ),
            'manage_options',
            'bankai-theme-kits',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'bankai-core',
            __( 'SEO Engine', 'bankai-core' ),
            __( 'SEO Engine', 'bankai-core' ),
            'manage_options',
            'bankai-seo-engine',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'bankai-core',
            __( 'Speed & Cache', 'bankai-core' ),
            __( 'Speed & Cache', 'bankai-core' ),
            'manage_options',
            'bankai-speed-cache',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'bankai-core',
            __( 'Media & Watermark', 'bankai-core' ),
            __( 'Media & Watermark', 'bankai-core' ),
            'manage_options',
            'bankai-media',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'bankai-core',
            __( 'AI Content Studio', 'bankai-core' ),
            __( 'AI Studio & LLMs', 'bankai-core' ),
            'manage_options',
            'bankai-ai-manifests',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'bankai-core',
            __( 'Settings & License', 'bankai-core' ),
            __( 'Settings & License', 'bankai-core' ),
            'manage_options',
            'bankai-settings',
            array( $this, 'render_admin_page' )
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
        // Enqueue on any bankai-* admin pages
        $page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
        if ( strpos( $hook, 'bankai-' ) === false && strpos( $page, 'bankai-' ) !== 0 ) {
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

        // 3. Enqueue Bankai Core Custom JS logic
        if ( file_exists( BANKAI_CORE_PATH . 'assets/js/bankai-admin.js' ) ) {
            wp_enqueue_script(
                'bankai-admin-script',
                BANKAI_CORE_URL . 'assets/js/bankai-admin.js',
                array( 'bankai-alpine', 'bankai-htmx' ),
                BANKAI_CORE_VERSION,
                true
            );
        }

        // 4. Enqueue Vazirmatn Font for Persian RTL support if RTL
        if ( is_rtl() ) {
            wp_enqueue_style(
                'bankai-vazirmatn-font',
                'https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css',
                array(),
                '33.0.3'
            );
        }

        // 5. Enqueue Bankai Core Admin Custom CSS
        if ( file_exists( BANKAI_CORE_PATH . 'assets/css/bankai-admin.css' ) ) {
            wp_enqueue_style(
                'bankai-admin-style',
                BANKAI_CORE_URL . 'assets/css/bankai-admin.css',
                array(),
                BANKAI_CORE_VERSION
            );
        }

        // Map page slug to active tab
        $current_tab = 'overview';
        if ( 'bankai-theme-kits' === $page ) {
            $current_tab = 'theme-kits';
        } elseif ( 'bankai-seo-engine' === $page ) {
            $current_tab = 'seo';
        } elseif ( 'bankai-speed-cache' === $page ) {
            $current_tab = 'speed';
        } elseif ( 'bankai-media' === $page ) {
            $current_tab = 'media';
        } elseif ( 'bankai-ai-manifests' === $page || 'bankai-ai-studio' === $page ) {
            $current_tab = 'ai';
        } elseif ( 'bankai-settings' === $page ) {
            $current_tab = 'settings';
        }

        // 6. Localize REST API URL, Security Nonce, Active Tab and RTL status
        $script_handle = file_exists( BANKAI_CORE_PATH . 'assets/js/bankai-admin.js' ) ? 'bankai-admin-script' : ( file_exists( BANKAI_CORE_PATH . 'assets/js/alpine.min.js' ) ? 'bankai-alpine' : 'bankai-htmx' );
        wp_localize_script(
            $script_handle,
            'bankaiData',
            array(
                'restUrl'   => esc_url_raw( rest_url( 'bankai/v1' ) ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'activeTab' => $current_tab,
                'isRtl'     => is_rtl(),
            )
        );
    }
}

new Bankai_Admin_Menu();
