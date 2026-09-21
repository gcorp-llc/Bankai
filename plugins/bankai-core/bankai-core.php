<?php

/**
 * Plugin Name: Bankai Core
 * Plugin URI: https://gcorp.io/bankai
 * Description: Enterprise-grade modular ecosystem for WordPress: Autonomous SEO Engine, High-Velocity Speed Cache, Starter Theme Kits, Media & Watermark Studio, and Multi-LLM AI Content Suite.
 * Version: 1.0.0
 * Author: GCORP LLC & Bankai Team
 * Author URI: https://gcorp.io
 * License: GPLv2 or later
 * Text Domain: bankai-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined('ABSPATH') || exit;

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
|
| IMPORTANT:
| BANKAI_CORE_DIR is a filesystem path.
| BANKAI_CORE_URL is a public WordPress URL.
|
| These two values must NEVER be mixed.
|
*/

/**
 * Plugin version.
 */
defined('BANKAI_CORE_VERSION') || define(
    'BANKAI_CORE_VERSION',
    '1.0.0'
);

/**
 * Backward-compatible version alias.
 */
defined('BANKAI_VERSION') || define(
    'BANKAI_VERSION',
    BANKAI_CORE_VERSION
);

/**
 * Main plugin file.
 *
 * This is safe to use for:
 * - register_activation_hook()
 * - register_deactivation_hook()
 * - plugin_basename()
 *
 * Do NOT use it to construct asset URLs in a symlinked environment.
 */
defined('BANKAI_CORE_FILE') || define(
    'BANKAI_CORE_FILE',
    __FILE__
);

/**
 * Physical filesystem path.
 *
 * In a symlinked development environment this may resolve to:
 *
 * C:/Users/GCORPLLC/Desktop/Project/Bankai/plugins/bankai-core/
 *
 * This constant is ONLY for filesystem operations.
 */
defined('BANKAI_CORE_DIR') || define(
    'BANKAI_CORE_DIR',
    plugin_dir_path(BANKAI_CORE_FILE)
);

/**
 * Backward-compatible filesystem path alias.
 */
defined('BANKAI_PLUGIN_DIR') || define(
    'BANKAI_PLUGIN_DIR',
    BANKAI_CORE_DIR
);

/**
 * Public plugin URL.
 *
 * IMPORTANT FOR SYMLINKED PLUGINS:
 *
 * Do NOT use:
 *
 * plugin_dir_url(__FILE__)
 *
 * because __FILE__ can resolve to the real filesystem location
 * instead of the public WordPress plugin path.
 *
 * The public URL must point to:
 *
 * /wp-content/plugins/bankai-core/
 */
defined('BANKAI_CORE_URL') || define(
    'BANKAI_CORE_URL',
    trailingslashit(content_url('plugins/bankai-core'))
);

/**
 * Backward-compatible public URL alias.
 */
defined('BANKAI_PLUGIN_URL') || define(
    'BANKAI_PLUGIN_URL',
    BANKAI_CORE_URL
);

/**
 * Views directory.
 */
defined('BANKAI_CORE_VIEWS_DIR') || define(
    'BANKAI_CORE_VIEWS_DIR',
    BANKAI_CORE_DIR . 'views/'
);


/*
|--------------------------------------------------------------------------
| Bankai Core
|--------------------------------------------------------------------------
*/

final class Bankai_Core
{
    private static ?Bankai_Core $instance = null;

    /**
     * Singleton instance.
     */
    public static function instance(): Bankai_Core
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

       /**
     * Load plugin dependencies.
     */
    private function load_dependencies(): void
    {
        require_once BANKAI_CORE_DIR . 'functions.php';

        /*
         * Architecture core.
         */
        require_once BANKAI_CORE_DIR . 'inc/class-admin-menu.php';
        require_once BANKAI_CORE_DIR . 'inc/class-rest-api.php';

        /*
         * Post SEO meta + Gutenberg editor sidebar
         * (فقط وقتی فایل‌ها وجود دارند لود می‌شوند)
         */
        $editor_files = [
            'inc/meta/class-post-seo-meta.php',
            'inc/class-editor-seo.php',
        ];

        foreach ($editor_files as $relative) {
            $filepath = BANKAI_CORE_DIR . $relative;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }

        /*
         * Modules.
         */
        $modules = [
            'class-theme-kits.php',
            'class-settings-license.php',
            'class-speed-cache.php',
            'class-seo-engine.php',
            'class-media-watermark.php',
            'class-ai-studio.php',
            'class-llms-txt.php',
        ];

        foreach ($modules as $module_file) {
            $filepath = BANKAI_CORE_DIR . 'inc/modules/' . $module_file;

            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks(): void
    {
        add_action('init', [$this, 'load_textdomain']);

        if (is_admin()) {
            Bankai_Admin_Menu::instance();
        }

        Bankai_Rest_API::instance();

        /*
         * SEO Meta + Editor Sidebar
         */
        if (class_exists('Bankai_Post_SEO_Meta') && method_exists('Bankai_Post_SEO_Meta', 'instance')) {
            Bankai_Post_SEO_Meta::instance();
        }

        if (class_exists('Bankai_Editor_SEO') && method_exists('Bankai_Editor_SEO', 'instance')) {
            Bankai_Editor_SEO::instance();
        }

        /*
         * Active modules.
         */
        $modules = [
            'Bankai_Theme_Kits',
            'Bankai_Settings_License',
            'Bankai_Speed_Cache',
            'Bankai_SEO_Engine',
            'Bankai_Media_Watermark',
            'Bankai_AI_Studio',
            'Bankai_LLMS_Txt',
        ];

        foreach ($modules as $class_name) {
            if (!class_exists($class_name)) {
                continue;
            }

            if (method_exists($class_name, 'instance')) {
                $class_name::instance();
            } else {
                new $class_name();
            }
        }
    }

    /**
     * Load plugin translations.
     */
    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'bankai-core',
            false,
            dirname(plugin_basename(BANKAI_CORE_FILE)) . '/languages'
        );
    }

    /**
     * Plugin activation.
     */
    public static function activate(): void
    {
        $default_options = [
            'version' => BANKAI_CORE_VERSION,

            'active_modules' => [
                'theme_kits' => true,
                'settings_license' => true,
                'speed_cache' => true,
                'seo_engine' => true,
                'media_watermark' => true,
                'ai_studio' => true,
                'llms_txt' => true,
            ],

            'theme_container_width' => 1400,

            'default_typography' => 'Vazirmatn',

            'installed_at' => current_time('mysql'),
        ];

        if (!get_option('bankai_core_settings')) {
            update_option(
                'bankai_core_settings',
                $default_options
            );
        }
    }

    /**
     * Plugin deactivation.
     */
    public static function deactivate(): void
    {
        delete_transient(
            'bankai_seo_audit_results'
        );

        delete_transient(
            'bankai_speed_cache_report'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Activation / Deactivation
|--------------------------------------------------------------------------
*/

register_activation_hook(
    BANKAI_CORE_FILE,
    ['Bankai_Core', 'activate']
);

register_deactivation_hook(
    BANKAI_CORE_FILE,
    ['Bankai_Core', 'deactivate']
);


/*
|--------------------------------------------------------------------------
| Global Bootstrap Function
|--------------------------------------------------------------------------
*/

function bankai_core(): Bankai_Core
{
    return Bankai_Core::instance();
}


/*
|--------------------------------------------------------------------------
| Bootstrap
|--------------------------------------------------------------------------
*/

bankai_core();
