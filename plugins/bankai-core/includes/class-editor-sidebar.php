<?php
/**
 * Editor Sidebar Script Register & Meta Box Handler.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Bankai_Editor_Sidebar {

    /**
     * Initialize editor hooks.
     */
    public static function init() {
        add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
        add_action( 'init', array( __CLASS__, 'register_post_meta' ) );
    }

    /**
     * Register SEO post meta fields for REST API and Gutenberg.
     */
    public static function register_post_meta() {
        $meta_keys = array(
            '_bankai_seo_title'         => 'string',
            '_bankai_seo_description'   => 'string',
            '_bankai_seo_focus_keyword' => 'string',
            '_bankai_schema_type'       => 'string',
        );

        foreach ( $meta_keys as $key => $type ) {
            register_post_meta(
                '',
                $key,
                array(
                    'show_in_rest'      => true,
                    'single'            => true,
                    'type'              => $type,
                    'auth_callback'     => function() {
                        return current_user_can( 'edit_posts' );
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                )
            );
        }
    }

    /**
     * Enqueue block editor assets if present.
     */
    public static function enqueue_editor_assets() {
        if ( class_exists( 'Bankai_Module_Switcher' ) && ! Bankai_Module_Switcher::is_active( 'seo' ) ) {
            return;
        }

        if ( file_exists( BANKAI_CORE_PATH . 'assets/js/bankai-admin.js' ) ) {
            wp_enqueue_script(
                'bankai-editor-sidebar',
                BANKAI_CORE_URL . 'assets/js/bankai-admin.js',
                array( 'wp-element', 'wp-components', 'wp-data' ),
                BANKAI_CORE_VERSION,
                true
            );
        }
    }
}

Bankai_Editor_Sidebar::init();
