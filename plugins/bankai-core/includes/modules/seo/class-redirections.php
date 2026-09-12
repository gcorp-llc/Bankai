<?php
/**
 * Redirections Manager (301, 302, 307, 410, 451) with 404 Logging.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Bankai_Redirections {

    /**
     * Initialize redirection hooks.
     */
    public static function init() {
        add_action( 'template_redirect', array( __CLASS__, 'check_redirections' ), 1 );
    }

    /**
     * Check requested URL against redirection rules and log 404s.
     */
    public static function check_redirections() {
        if ( is_admin() ) {
            return;
        }

        $requested_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $redirects     = get_option( 'bankai_seo_redirects', array() );

        // پاک‌سازی Query String برای تطبیق دقیق آدرس
        $path_only = wp_parse_url( $requested_uri, PHP_URL_PATH );

        if ( ! empty( $path_only ) && isset( $redirects[ $path_only ] ) ) {
            $target = $redirects[ $path_only ]['target'];
            $code   = isset( $redirects[ $path_only ]['code'] ) ? intval( $redirects[ $path_only ]['code'] ) : 301;

            wp_redirect( $target, $code );
            exit;
        }

        if ( is_404() && ! empty( $requested_uri ) ) {
            self::log_404( $requested_uri );
        }
    }

    /**
     * Log 404 requests and prune old records if log size exceeds threshold.
     *
     * @param string $uri The 404 requested URI.
     */
    public static function log_404( $uri ) {
        $logs = get_option( 'bankai_404_logs', array() );

        $logs[ $uri ] = isset( $logs[ $uri ] ) ? $logs[ $uri ] + 1 : 1;

        // محدود نگه‌داشتن حجم لاگ‌ها در دیتابیس (حداکثر ۲۰۰ مورد)
        if ( count( $logs ) > 200 ) {
            $logs = array_slice( $logs, -100, 100, true );
        }

        update_option( 'bankai_404_logs', $logs );
    }
}

Bankai_Redirections::init();