<?php
/**
 * Dynamic XML Sitemap Generator (General, News, Video, Images).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Bankai_Sitemap {

    /**
     * Initialize sitemap hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'add_sitemap_rewrite' ) );
        add_action( 'template_redirect', array( __CLASS__, 'render_sitemap' ) );
    }

    /**
     * Add rewrite rule for /sitemap.xml
     */
    public static function add_sitemap_rewrite() {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?bankai_sitemap=1', 'top' );
        add_rewrite_tag( '%bankai_sitemap%', '([^&]+)' );
    }

    /**
     * Render XML sitemap output.
     */
    public static function render_sitemap() {
        if ( get_query_var( 'bankai_sitemap' ) ) {
            // پاک‌سازی بافر قبلی جهت جلوگیری از خطای Header
            if ( ob_get_length() ) {
                ob_clean();
            }

            header( 'Content-Type: application/xml; charset=utf-8' );
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // افزودن آدرس صفحه اصلی سایت
            echo "  <url>\n";
            echo '    <loc>' . esc_url( home_url( '/' ) ) . "</loc>\n";
            echo '    <changefreq>daily</changefreq>' . "\n";
            echo '    <priority>1.0</priority>' . "\n";
            echo "  </url>\n";

            // دریافت نوشته‌ها و برگه‌ها
            $posts = get_posts( array(
                'numberposts' => 100,
                'post_status' => 'publish',
                'post_type'   => array( 'post', 'page' ),
                'orderby'     => 'modified',
                'order'       => 'DESC',
            ) );

            foreach ( $posts as $post ) {
                echo "  <url>\n";
                echo '    <loc>' . esc_url( get_permalink( $post->ID ) ) . "</loc>\n";
                echo '    <lastmod>' . esc_html( get_the_modified_date( 'c', $post->ID ) ) . "</lastmod>\n";
                echo "    <changefreq>weekly</changefreq>\n";
                echo "    <priority>0.8</priority>\n";
                echo "  </url>\n";
            }

            echo '</urlset>';
            exit;
        }
    }
}

Bankai_Sitemap::init();