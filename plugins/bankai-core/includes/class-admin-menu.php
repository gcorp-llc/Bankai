<?php
/**
 * Admin Menu & Asset Loader Class
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Bankai_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * ثبت منوی اصلی بانکای در پیشخوان وردپرس
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
     * رندر کانتینر ریشه که React روی آن سوار می‌شود
     */
    public function render_admin_page() {
        // شناسه دقیقا باید با bankai-admin-root در src/index.js یکسان باشد
        echo '<div id="bankai-admin-root"></div>';
    }

    /**
     * لود اسکریپت‌ها، استایل‌ها و ارسال داده‌های REST API به React
     */
    public function enqueue_admin_assets( $hook ) {
        // فقط در صفحه اختصاصی بانکای لود شود
        if ( strpos( $hook, 'bankai-core' ) === false ) {
            return;
        }

        $asset_file = BANKAI_CORE_PATH . 'build/index.asset.php';

        if ( file_exists( $asset_file ) ) {
            $assets = include $asset_file;

            // ۱. لود استایل‌های پایه کامپوننت‌های وردپرس
            wp_enqueue_style( 'wp-components' );

            // ۲. لود اسکریپت اصلی React کمپایل‌شده
            wp_enqueue_script(
                'bankai-admin-app',
                BANKAI_CORE_URL . 'build/index.js',
                $assets['dependencies'],
                $assets['version'],
                true
            );

            // ۳. لود استایل اختصاصی بانکای در صورت وجود
            if ( file_exists( BANKAI_CORE_PATH . 'build/style-index.css' ) ) {
                wp_enqueue_style(
                    'bankai-admin-style',
                    BANKAI_CORE_URL . 'build/style-index.css',
                    array(),
                    $assets['version']
                );
            }

            // ۴. ارسال داده‌های window.bankaiData به React جهت اعتبارسنجی REST API
            wp_localize_script(
                'bankai-admin-app',
                'bankaiData',
                array(
                    'restUrl' => esc_url_raw( rest_url( 'bankai/v1' ) ),
                    'nonce'   => wp_create_nonce( 'wp_rest' ),
                )
            );
        }
    }
}

new Bankai_Admin_Menu();