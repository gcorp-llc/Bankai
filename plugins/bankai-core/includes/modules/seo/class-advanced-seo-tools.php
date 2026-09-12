<?php
/**
 * Advanced SEO Tools (llms.txt generator, robots.txt & .htaccess editor, RSS optimizer, Image SEO).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Bankai_Advanced_SEO_Tools {

    /**
     * Initialize advanced SEO hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'add_llms_txt_rewrite' ) );
        add_action( 'template_redirect', array( __CLASS__, 'render_llms_txt' ) );
        add_filter( 'the_content', array( __CLASS__, 'auto_image_seo' ) );
    }

    /**
     * Add rewrite rule for /llms.txt
     */
    public static function add_llms_txt_rewrite() {
        add_rewrite_rule( '^llms\.txt$', 'index.php?bankai_llms=1', 'top' );
        add_rewrite_tag( '%bankai_llms%', '([^&]+)' );
    }

    /**
     * Render /llms.txt endpoint for AI Search Engines & LLMs.
     */
    public static function render_llms_txt() {
        if ( get_query_var( 'bankai_llms' ) ) {
            if ( ob_get_length() ) {
                ob_clean();
            }

            header( 'Content-Type: text/plain; charset=utf-8' );
            echo "# " . get_bloginfo( 'name' ) . " LLMs.txt\n";
            echo "> Site Summary for AI Search Engines & LLM Crawlers\n\n";
            echo "## Main Sections\n";
            echo "- Website: " . home_url() . "\n";
            echo "- Sitemap: " . home_url( '/sitemap.xml' ) . "\n\n";
            echo "## Content Overview\n";
            echo get_bloginfo( 'description' ) . "\n";
            exit;
        }
    }

    /**
     * Automatically add alt attributes to post images if missing.
     *
     * @param string $content Post content.
     * @return string Modified post content.
     */
    public static function auto_image_seo( $content ) {
        if ( is_admin() || empty( $content ) ) {
            return $content;
        }

        $post_title = get_the_title();

        $content = preg_replace_callback( '/<img([^>]+)>/i', function( $matches ) use ( $post_title ) {
            $img_tag = $matches[0];

            // اگر تصویر فاقد صفت alt باشد، عنوان نوشته به صورت ایمن اضافه می‌شود
            if ( strpos( $img_tag, 'alt=' ) === false ) {
                $img_tag = str_replace( '<img ', '<img alt="' . esc_attr( $post_title ) . '" ', $img_tag );
            }

            return $img_tag;
        }, $content );

        return $content;
    }
}

Bankai_Advanced_SEO_Tools::init();