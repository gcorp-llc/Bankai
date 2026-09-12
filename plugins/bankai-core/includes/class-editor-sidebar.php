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
            // استفاده از رشته خالی '' باعث اعمال متا روی همه پست‌تایپ‌ها (Post, Page, CPTs) می‌شود
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
     * Enqueue Gutenberg editor sidebar React scripts.
     */
    public static function enqueue_editor_assets() {
        // بررسی فعال بودن ماژول سئو
        if ( class_exists( 'Bankai_Module_Switcher' ) && ! Bankai_Module_Switcher::is_active( 'seo' ) ) {
            return;
        }

        $asset_file = BANKAI_CORE_PATH . 'admin/build/index.asset.php';

        if ( file_exists( $asset_file ) ) {
            $asset = require $asset_file;

            wp_enqueue_script(
                'bankai-editor-sidebar',
                BANKAI_CORE_URL . 'admin/build/index.js',
                isset( $asset['dependencies'] ) ? $asset['dependencies'] : array( 'wp-plugins', 'wp-element', 'wp-edit-post', 'wp-components', 'wp-data' ),
                isset( $asset['version'] ) ? $asset['version'] : '1.0.0',
                true
            );
        }
    }
}

Bankai_Editor_Sidebar::init();