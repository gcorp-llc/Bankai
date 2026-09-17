<?php
/**
 * Bankai Core - SEO Engine & Schema Manager
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

class Bankai_SEO_Engine {

    private static ?Bankai_SEO_Engine $instance = null;

    public static function instance(): Bankai_SEO_Engine {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_SEO_Engine {
        return self::instance();
    }

    private function __construct() {
        add_action('wp_head', [$this, 'render_meta_tags'], 1);
        add_action('wp_head', [$this, 'render_schema_json_ld'], 5);
        add_action('template_redirect', [$this, 'log_404_anomalies']);
        add_action('wp_ajax_bankai_save_301_redirect', [$this, 'ajax_save_301_redirect']);
    }

    public function render_meta_tags(): void {
        if (is_singular()) {
            global $post;
            $meta_desc = get_post_meta($post->ID, '_bankai_meta_description', true);
            if (empty($meta_desc)) {
                $meta_desc = wp_strip_all_tags(has_excerpt($post->ID) ? get_the_excerpt($post->ID) : wp_trim_words($post->post_content, 25));
            }
            if (!empty($meta_desc)) {
                echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
            }
        }
    }

    public function render_schema_json_ld(): void {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => get_bloginfo('name'),
            'url'      => home_url('/'),
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    public function log_404_anomalies(): void {
        if (is_404()) {
            $requested_uri = sanitize_text_field($_SERVER['REQUEST_URI'] ?? '');
            if (empty($requested_uri)) return;

            $logs = get_option('bankai_404_logs', []);
            if (isset($logs[$requested_uri])) {
                $logs[$requested_uri]['hits'] += 1;
                $logs[$requested_uri]['last_hit'] = current_time('mysql');
            } else {
                $logs[$requested_uri] = [
                    'hits'     => 1,
                    'last_hit' => current_time('mysql'),
                ];
            }

            if (count($logs) > 100) {
                array_shift($logs);
            }

            update_option('bankai_404_logs', $logs, false);
        }
    }

    public function ajax_save_301_redirect(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        
        $source = sanitize_text_field($_POST['source_uri'] ?? '');
        $target = sanitize_text_field($_POST['target_uri'] ?? '');

        if ($source && $target) {
            $redirects = get_option('bankai_301_redirects', []);
            $redirects[$source] = $target;
            update_option('bankai_301_redirects', $redirects);

            wp_send_json_success(['message' => __('Redirect created successfully.', 'bankai-core')]);
        }

        wp_send_json_error(['message' => __('Invalid parameters.', 'bankai-core')]);
    }
}
