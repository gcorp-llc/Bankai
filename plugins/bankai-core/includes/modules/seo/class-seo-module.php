<?php
/**
 * SEO Module Bootstrap.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Bankai_SEO_Module
 */
class Bankai_SEO_Module {

    /**
     * Initialize SEO sub-modules.
     */
    public static function init() {
        // بررسی وجود کلاس و فعال بودن ماژول سئو
        if ( class_exists( 'Bankai_Module_Switcher' ) && ! Bankai_Module_Switcher::is_active( 'seo' ) ) {
            return;
        }

        $seo_dir   = BANKAI_CORE_PATH . 'includes/modules/seo/';
        $seo_files = array(
            'class-post-seo-meta.php',
            'class-meta-generator.php',
            'class-schema-builder.php',
            'class-sitemap.php',
            'class-redirections.php',
            'class-advanced-seo-tools.php',
        );

        foreach ( $seo_files as $file ) {
            $file_path = $seo_dir . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }
}

Bankai_SEO_Module::init();