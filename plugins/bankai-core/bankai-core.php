<?php
/**
 * Plugin Name: Bankai Core
 * Plugin URI: https://gcorp.io/bankai
 * Description: Enterprise-grade modular ecosystem for WordPress: Autonomous SEO Engine, High-Velocity Speed Cache, Media & Watermark Studio, and Multi-LLM AI Content Suite.
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

// Define Plugin Constants
define('BANKAI_CORE_VERSION', '1.0.0');
define('BANKAI_CORE_FILE', __FILE__);
define('BANKAI_CORE_DIR', plugin_dir_path(__FILE__));
define('BANKAI_CORE_URL', plugin_dir_url(__FILE__));
define('BANKAI_CORE_TEMPLATE_DIR', BANKAI_CORE_DIR . 'templates/');

/**
 * Main Bankai Core Plugin Singleton Class
 */
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
        require_once BANKAI_CORE_DIR . 'includes/class-admin-menu.php';
        require_once BANKAI_CORE_DIR . 'includes/class-rest-api.php';
        require_once BANKAI_CORE_DIR . 'includes/modules/class-seo-engine.php';
        require_once BANKAI_CORE_DIR . 'includes/modules/class-speed-cache.php';
        require_once BANKAI_CORE_DIR . 'includes/modules/class-media-watermark.php';
        require_once BANKAI_CORE_DIR . 'includes/modules/class-ai-studio.php';
        require_once BANKAI_CORE_DIR . 'includes/modules/class-llms-txt.php';
    }

    private function init_hooks(): void {
        add_action('init', [$this, 'load_textdomain']);
        
        // Initialize Admin Menu
        if (is_admin()) {
            Bankai_Admin_Menu::instance();
        }

        // Initialize REST API
        Bankai_Rest_API::instance();

        // Initialize Core Modules
        Bankai_SEO_Engine::instance();
        Bankai_Speed_Cache::instance();
        Bankai_Media_Watermark::instance();
        Bankai_AI_Studio::instance();
        Bankai_LLMS_Txt::instance();
    }

    public function load_textdomain(): void {
        load_plugin_textdomain('bankai-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public static function activate(): void {
        $default_options = [
            'version' => BANKAI_CORE_VERSION,
            'active_modules' => [
                'seo_engine' => true,
                'speed_cache' => true,
                'media_watermark' => true,
                'ai_studio' => true,
                'llms_txt' => true,
                'instant_indexing' => true,
            ],
            'theme_container_width' => 1400,
            'default_typography' => 'Vazirmatn',
            'installed_at' => current_time('mysql'),
        ];
        if (!get_option('bankai_core_settings')) {
            update_option('bankai_core_settings', $default_options);
        }
    }

    public static function deactivate(): void {
        // Clean up transient caches on deactivation
        delete_transient('bankai_seo_audit_results');
        delete_transient('bankai_speed_cache_report');
    }
}

register_activation_hook(__FILE__, ['Bankai_Core', 'activate']);
register_deactivation_hook(__FILE__, ['Bankai_Core', 'deactivate']);

// Boot the plugin
function bankai_core(): Bankai_Core {
    return Bankai_Core::instance();
}
bankai_core();
