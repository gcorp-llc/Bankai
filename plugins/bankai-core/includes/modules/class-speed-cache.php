<?php
/**
 * Bankai Speed & Cache Engine Module
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;

class Bankai_Speed_Cache {

    private static ?Bankai_Speed_Cache $instance = null;

    public static function instance(): Bankai_Speed_Cache {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'setup_buffer_optimization']);
        add_filter('script_loader_tag', [$this, 'defer_non_critical_scripts'], 10, 3);
    }

    public function setup_buffer_optimization(): void {
        if (!is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
            ob_start([$this, 'minify_html_buffer']);
        }
    }

    public function minify_html_buffer(string $buffer): string {
        // Strip unnecessary comments and whitespaces while preserving pre/textarea
        if (strlen($buffer) < 100) {
            return $buffer;
        }
        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        ];
        $replace = ['>', '<', '\\1', ''];
        return preg_replace($search, $replace, $buffer);
    }

    public function defer_non_critical_scripts(string $tag, string $handle, string $src): string {
        if (is_admin()) {
            return $tag;
        }
        // Add defer attribute to speed up First Contentful Paint
        if (strpos($tag, 'defer') === false && strpos($handle, 'jquery-core') === false) {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }
}
