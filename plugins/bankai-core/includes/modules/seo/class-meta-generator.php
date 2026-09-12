<?php
/**
 * SEO Meta Generator (Title, Description, OpenGraph, Canonical, Robots, Category Base Stripper).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Bankai_Meta_Generator {

    /**
     * Initialize meta generator hooks.
     */
    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'output_meta_tags' ), 1 );
        add_filter( 'category_rewrite_rules', array( __CLASS__, 'remove_category_base' ) );
    }

    /**
     * Output SEO Meta tags in wp_head.
     */
    public static function output_meta_tags() {
        if ( is_admin() ) {
            return;
        }

        $title       = self::get_title();
        $description = self::get_description();
        $canonical   = self::get_canonical();
        
        // دریافت تصویر شاخص یا لوگوی پیش‌فرض
        $image_url = '';
        if ( is_singular() && has_post_thumbnail() ) {
            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
        }
        if ( empty( $image_url ) && class_exists( 'Bankai_Branding' ) ) {
            $image_url = Bankai_Branding::get_logo_url();
        }

        echo "\n<!-- Bankai Core Advanced SEO Meta Engine -->\n";
        
        if ( ! empty( $description ) ) {
            echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
        }
        if ( ! empty( $canonical ) ) {
            echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
        }

        // OpenGraph Meta
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        if ( ! empty( $description ) ) {
            echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
        }
        echo '<meta property="og:url" content="' . esc_url( $canonical ? $canonical : home_url() ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
        echo '<meta property="og:type" content="' . ( is_single() ? 'article' : 'website' ) . '">' . "\n";
        if ( ! empty( $image_url ) ) {
            echo '<meta property="og:image" content="' . esc_url( $image_url ) . '">' . "\n";
        }

        // Twitter Cards Meta
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        if ( ! empty( $description ) ) {
            echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
        }
        if ( ! empty( $image_url ) ) {
            echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
        }
        
        echo "<!-- / Bankai Core Advanced SEO Meta Engine -->\n\n";
    }

    /**
     * Get dynamic SEO title.
     *
     * @return string
     */
    public static function get_title() {
        if ( is_singular() ) {
            $meta_title = get_post_meta( get_the_ID(), '_bankai_seo_title', true );
            if ( ! empty( $meta_title ) ) {
                return $meta_title;
            }
            return get_the_title();
        }
        return get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
    }

    /**
     * Get dynamic SEO meta description.
     *
     * @return string
     */
    public static function get_description() {
        if ( is_singular() ) {
            $meta_desc = get_post_meta( get_the_ID(), '_bankai_seo_description', true );
            if ( ! empty( $meta_desc ) ) {
                return $meta_desc;
            }
            return wp_strip_all_tags( get_the_excerpt() );
        }
        return get_bloginfo( 'description' );
    }

    /**
     * Get page canonical URL.
     *
     * @return string
     */
    public static function get_canonical() {
        if ( is_singular() ) {
            return get_permalink();
        }
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
        return home_url( $request_uri );
    }

    /**
     * Remove /category/ prefix from WordPress category URLs.
     *
     * @param array $category_rewrite Existing rewrite rules.
     * @return array
     */
    public static function remove_category_base( $category_rewrite = array() ) {
        if ( ! is_array( $category_rewrite ) ) {
            $category_rewrite = array();
        }

        $categories = get_categories( array( 'hide_empty' => false ) );
        foreach ( $categories as $category ) {
            $category_nicename = $category->slug;
            if ( $category->parent != 0 ) {
                $category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;
            }
            $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/embed/?$']                             = 'index.php?category_name=$matches[1]&embed=true';
            $category_rewrite['(' . $category_nicename . ')/(?:page/)?([0-9]+)/?$']                = 'index.php?category_name=$matches[1]&paged=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/?$']                                   = 'index.php?category_name=$matches[1]';
        }
        
        return $category_rewrite;
    }
}

Bankai_Meta_Generator::init();