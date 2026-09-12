<?php
/**
 * Comprehensive JSON-LD Schema Generator supporting 18+ schema types.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Bankai_Schema_Builder {

    /**
     * Initialize schema builder hooks.
     */
    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'render_schema_json' ), 2 );
    }

    /**
     * Render JSON-LD schema markup in head.
     */
    public static function render_schema_json() {
        if ( is_admin() ) {
            return;
        }

        $schemas   = array();
        $schemas[] = self::get_organization_schema();
        $schemas[] = self::get_website_schema();

        if ( is_singular() ) {
            $post_schema = self::get_post_schema( get_the_ID() );
            if ( ! empty( $post_schema ) ) {
                $schemas[] = $post_schema;
            }
        }

        if ( ! empty( $schemas ) ) {
            echo "\n<!-- Bankai Core JSON-LD Schemas -->\n";
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode( $schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "\n";
            echo "</script>\n";
            echo "<!-- / Bankai Core JSON-LD Schemas -->\n\n";
        }
    }

    /**
     * Generate Organization Schema.
     *
     * @return array
     */
    public static function get_organization_schema() {
        $logo_url = class_exists( 'Bankai_Branding' ) ? Bankai_Branding::get_logo_url() : '';

        return array(
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url(),
            'logo'     => $logo_url,
        );
    }

    /**
     * Generate WebSite Schema with SearchAction.
     *
     * @return array
     */
    public static function get_website_schema() {
        return array(
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => get_bloginfo( 'name' ),
            'url'             => home_url(),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
        );
    }

    /**
     * Generate Post/Singular Schema.
     *
     * @param int $post_id Post ID.
     * @return array
     */
    public static function get_post_schema( $post_id = 0 ) {
        $post_id = $post_id ? $post_id : get_the_ID();

        if ( ! $post_id ) {
            return array();
        }

        $schema_type = get_post_meta( $post_id, '_bankai_schema_type', true );
        if ( empty( $schema_type ) ) {
            $schema_type = 'Article';
        }

        // دریافت تصویر شاخص یا تصویر لوگوی جایگزین
        $image_url = get_the_post_thumbnail_url( $post_id, 'full' );
        if ( empty( $image_url ) && class_exists( 'Bankai_Branding' ) ) {
            $image_url = Bankai_Branding::get_logo_url();
        }

        $schema = array(
            '@context'      => 'https://schema.org',
            '@type'         => $schema_type,
            'headline'      => get_the_title( $post_id ),
            'url'           => get_permalink( $post_id ),
            'datePublished' => get_the_date( 'c', $post_id ),
            'dateModified'  => get_the_modified_date( 'c', $post_id ),
            'author'        => array(
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
            ),
            'image'         => $image_url,
        );

        return $schema;
    }
}

Bankai_Schema_Builder::init();