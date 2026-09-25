<?php
/**
 * Plugin Name: Bankai Core
 * Description: Enterprise SEO, Speed, AI & Media suite for WordPress.
 * Version: 1.0.0
 * Author: Bankai
 * Text Domain: bankai-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined('ABSPATH') || exit;

/**
 * Main plugin file path.
 */
defined('BANKAI_CORE_FILE') || define('BANKAI_CORE_FILE', __FILE__);

/**
 * Physical filesystem path.
 */
defined('BANKAI_CORE_DIR') || define(
    'BANKAI_CORE_DIR',
    plugin_dir_path(__FILE__)
);

/**
 * Public URL — avoid plugin_dir_url under symlink.
 */
defined('BANKAI_CORE_URL') || define(
    'BANKAI_CORE_URL',
    trailingslashit(content_url('plugins/bankai-core'))
);

defined('BANKAI_PLUGIN_URL') || define('BANKAI_PLUGIN_URL', BANKAI_CORE_URL);

defined('BANKAI_CORE_VIEWS_DIR') || define(
    'BANKAI_CORE_VIEWS_DIR',
    BANKAI_CORE_DIR . 'views/'
);

defined('BANKAI_CORE_VERSION') || define('BANKAI_CORE_VERSION', '1.0.18');

/*
|--------------------------------------------------------------------------
| Bankai Core
|--------------------------------------------------------------------------
*/

final class Bankai_Core
{
    private static ?Bankai_Core $instance = null;

    public static function instance(): Bankai_Core
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies(): void
    {
        require_once BANKAI_CORE_DIR . 'functions.php';

        // Architecture core (always loaded)
        $core = [
            'inc/class-admin-menu.php',
            'inc/class-rest-api.php',
            'inc/class-dashboard-stats.php',
        ];
        foreach ($core as $relative) {
            $filepath = BANKAI_CORE_DIR . $relative;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }

        // Editor SEO (gated later by is_module_active inside class)
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

        // Feature modules — files always required; constructors gate on active_modules
        $modules = [
            'class-theme-kits.php',
            'class-settings-license.php',
            'class-speed-cache.php',
            'class-seo-engine.php',
            'class-seo-integrations.php',
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

    private function init_hooks(): void
    {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'boot_modules'], 5);
    }

    /**
     * Instantiate modules only when active.
     * SEO Engine + Editor SEO respect bankai_is_module_active('seo_engine').
     */
    public function boot_modules(): void
    {
        // Always: admin + REST + stats
        if (class_exists('Bankai_Admin_Menu') && method_exists('Bankai_Admin_Menu', 'instance')) {
            Bankai_Admin_Menu::instance();
        }
        if (class_exists('Bankai_Rest_API') && method_exists('Bankai_Rest_API', 'instance')) {
            Bankai_Rest_API::instance();
        }
        if (class_exists('Bankai_Dashboard_Stats') && method_exists('Bankai_Dashboard_Stats', 'instance')) {
            Bankai_Dashboard_Stats::instance();
        }

        // Map class → module key (empty key = always on)
        $map = [
            'Bankai_Settings_License' => 'settings_license',
            'Bankai_Theme_Kits'       => 'theme_kits',
            'Bankai_Speed_Cache'      => 'speed_cache',
            'Bankai_SEO_Engine'       => 'seo_engine',
            'Bankai_SEO_Integrations' => 'seo_engine',
            'Bankai_Media_Watermark'  => 'media_watermark',
            'Bankai_AI_Studio'        => 'ai_studio',
            'Bankai_LLMS_Txt'         => 'llms_txt',
            'Bankai_Editor_SEO'       => 'seo_engine',
            'Bankai_Post_SEO_Meta'    => 'seo_engine',
        ];

        foreach ($map as $class_name => $module_key) {
            if (!class_exists($class_name)) {
                continue;
            }
            if ($module_key !== '' && function_exists('bankai_is_module_active') && !bankai_is_module_active($module_key)) {
                continue;
            }
            if (method_exists($class_name, 'instance')) {
                $class_name::instance();
            } elseif (method_exists($class_name, 'get_instance')) {
                $class_name::get_instance();
            }
        }
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'bankai-core',
            false,
            dirname(plugin_basename(BANKAI_CORE_FILE)) . '/languages'
        );
    }

    public static function activate(): void
    {
        $default_options = [
            'version' => defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0',
            'active_modules' => [
                'theme_kits'        => true,
                'settings_license'  => true,
                'speed_cache'       => true,
                'seo_engine'        => true,
                'media_watermark'   => true,
                'ai_studio'         => true,
                'llms_txt'          => true,
            ],
            'seo_modules' => [
                'auto_meta'         => true,
                'sitemap_pro'       => true,
                'canonical_guard'   => true,
                'open_graph_ai'     => true,
                'local_seo_schema'  => true,
                'llms_txt_builder'  => true,
            ],
            'speed_modules' => [
                'page_caching'        => true,
                'asset_optimization'  => true,
                'database_optimizer'  => false,
                'object_cache'        => false,
                'server_compression'  => true,
                'fonts_localizer'     => true,
            ],
            'media_modules' => [
                'webp_avif_converter'     => true,
                'dynamic_watermarking'    => false,
                'exif_metadata_scrubber'  => true,
                'cloud_offload_cdn'       => false,
                'retina_generator'        => false,
                'svg_sanitizer'           => true,
            ],
            'ai_modules' => [
                'smart_excerpt_generator' => true,
                'llm_manifest_auto'       => true,
                'meta_desc_auto'          => true,
                'bulk_content_enricher'   => false,
                'faq_schema_ai'           => true,
                'brand_voice_tuning'      => false,
            ],
            'theme_container_width' => 1400,
            'default_typography'    => 'Vazirmatn',
            'sitemap_enabled'       => true,
            'cache_enabled'         => true,
            'cache_ttl'             => 3600,
            'installed_at'          => current_time('mysql'),
        ];

        $existing = get_option('bankai_core_settings', null);
        if ($existing === null || $existing === false) {
            update_option('bankai_core_settings', $default_options, false);
        } else {
            // Merge missing keys without overwriting user choices
            if (!is_array($existing)) {
                $existing = [];
            }
            $merged = array_replace_recursive($default_options, $existing);
            // Ensure active_modules keys exist
            if (!isset($merged['active_modules']) || !is_array($merged['active_modules'])) {
                $merged['active_modules'] = $default_options['active_modules'];
            } else {
                foreach ($default_options['active_modules'] as $k => $v) {
                    if (!array_key_exists($k, $merged['active_modules'])) {
                        $merged['active_modules'][$k] = $v;
                    }
                }
            }
            update_option('bankai_core_settings', $merged, false);
        }

        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules(false);
        }
    }

    public static function deactivate(): void
    {
        delete_transient('bankai_seo_audit_results');
        delete_transient('bankai_speed_cache_report');
        flush_rewrite_rules(false);
    }
}

register_activation_hook(BANKAI_CORE_FILE, ['Bankai_Core', 'activate']);
register_deactivation_hook(BANKAI_CORE_FILE, ['Bankai_Core', 'deactivate']);

function bankai_core(): Bankai_Core
{
    return Bankai_Core::instance();
}

bankai_core();
