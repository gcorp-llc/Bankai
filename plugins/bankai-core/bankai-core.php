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

// Define Constants & Global Compatibility Aliases
define('BANKAI_CORE_VERSION', '1.0.0');
define('BANKAI_VERSION', BANKAI_CORE_VERSION);
define('BANKAI_CORE_FILE', __FILE__);
define('BANKAI_CORE_DIR', plugin_dir_path(__FILE__));
define('BANKAI_PLUGIN_DIR', BANKAI_CORE_DIR);
define('BANKAI_CORE_URL', plugin_dir_url(__FILE__));
define('BANKAI_PLUGIN_URL', BANKAI_CORE_URL);
define('BANKAI_CORE_VIEWS_DIR', BANKAI_CORE_DIR . 'views/');

final class Bankai_Core {

    private static ?Bankai_Core $instance = null;

    public static function instance(): Bankai_Core {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies(): void {
        // Load Global Helpers First
        require_once BANKAI_CORE_DIR . 'includes/helpers.php';
        
        // Load Architecture Core
        require_once BANKAI_CORE_DIR . 'includes/class-admin-menu.php';
        require_once BANKAI_CORE_DIR . 'includes/class-rest-api.php';

        // Load Modules from includes/modules/
        $modules = [
            'class-theme-kits.php',
            'class-settings-license.php',
            'class-speed-cache.php',
            'class-seo-engine.php',
            'class-media-watermark.php',
            'class-ai-studio.php',
            'class-llms-txt.php'
        ];

        foreach ($modules as $module_file) {
            $filepath = BANKAI_CORE_DIR . 'includes/modules/' . $module_file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }

    private function init_hooks(): void {
        add_action('init', [$this, 'load_textdomain']);
        
        if (is_admin()) {
            Bankai_Admin_Menu::instance();
        }

        Bankai_Rest_API::instance();

        // Boot Active Modules conditionally
        $modules = [
            'Bankai_Theme_Kits',
            'Bankai_Settings_License',
            'Bankai_Speed_Cache',
            'Bankai_SEO_Engine',
            'Bankai_Media_Watermark',
            'Bankai_AI_Studio',
            'Bankai_LLMS_Txt'
        ];

        foreach ($modules as $class_name) {
            if (class_exists($class_name)) {
                if (method_exists($class_name, 'instance')) {
                    $class_name::instance();
                } else {
                    new $class_name();
                }
            }
        }
    }

    public function load_textdomain(): void {
        load_plugin_textdomain('bankai-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public static function activate(): void {
        $default_options = [
            'version'               => BANKAI_CORE_VERSION,
            'active_modules'        => [
                'theme_kits'        => true,
                'settings_license'  => true,
                'speed_cache'       => true,
                'seo_engine'        => true,
                'media_watermark'   => true,
                'ai_studio'         => true,
                'llms_txt'          => true,
            ],
            'theme_container_width' => 1400,
            'default_typography'    => 'Vazirmatn',
            'installed_at'          => current_time('mysql'),
        ];

        if (!get_option('bankai_core_settings')) {
            update_option('bankai_core_settings', $default_options);
        }
    }

    public static function deactivate(): void {
        delete_transient('bankai_seo_audit_results');
        delete_transient('bankai_speed_cache_report');
    }
}

register_activation_hook(__FILE__, ['Bankai_Core', 'activate']);
register_deactivation_hook(__FILE__, ['Bankai_Core', 'deactivate']);

function bankai_core(): Bankai_Core {
    return Bankai_Core::instance();
}
bankai_core();