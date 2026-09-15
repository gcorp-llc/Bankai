<?php
/**
 * Bankai Core - SEO Engine & Schema Manager
 * 
 * @package Bankai
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Bankai_SEO_Engine {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_head', array($this, 'render_meta_tags'), 1);
        add_action('wp_head', array($this, 'render_schema_json_ld'), 5);
        add_action('template_redirect', array($this, 'log_404_anomalies'));
        add_action('wp_ajax_bankai_save_301_redirect', array($this, 'ajax_save_301_redirect'));
    }

    /**
     * Render dynamic SEO Meta Tags in Head
     */
    public function render_meta_tags() {
        if (is_singular()) {
            global $post;
            $meta_desc = get_post_meta($post->ID, '_bankai_meta_description', true);
            if (empty($meta_desc)) {
                $meta_desc = wp_strip_all_tags(has_excerpt($post->ID) ? get_the_excerpt($post->ID) : wp_trim_words($post->post_content, 25));
            }
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
            echo '<meta property="og:title" content="' . esc_attr(get_the_title($post->ID)) . '" />' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($meta_desc) . '" />' . "\n";
        }
    }

    /**
     * Output Schema.org JSON-LD Structured Data
     */
    public function render_schema_json_ld() {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => is_front_page() ? 'WebSite' : (is_single() ? 'Article' : 'WebPage'),
            'name'     => get_bloginfo('name'),
            'url'      => home_url('/'),
        );

        if (is_single()) {
            global $post;
            $schema['headline'] = get_the_title($post->ID);
            $schema['datePublished'] = get_the_date('c', $post->ID);
            $schema['dateModified']  = get_the_modified_date('c', $post->ID);
        }

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    /**
     * Track 404 URI hits and heuristics
     */
    public function log_404_anomalies() {
        if (is_404()) {
            $requested_uri = sanitize_text_field($_SERVER['REQUEST_URI'] ?? '');
            if (empty($requested_uri)) return;

            $logs = get_option('bankai_404_logs', array());
            if (isset($logs[$requested_uri])) {
                $logs[$requested_uri]['hits'] += 1;
                $logs[$requested_uri]['last_hit'] = current_time('mysql');
            } else {
                $logs[$requested_uri] = array(
                    'hits'     => 1,
                    'last_hit' => current_time('mysql'),
                );
            }

            // Keep top 100 entries
            if (count($logs) > 100) {
                array_shift($logs);
            }

            update_option('bankai_404_logs', $logs, false);
        }
    }

    /**
     * AJAX action to save 301 redirects for 404s
     */
    public function ajax_save_301_redirect() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        
        $source = sanitize_text_field($_POST['source_uri'] ?? '');
        $target = sanitize_text_field($_POST['target_uri'] ?? '');

        if ($source && $target) {
            $redirects = get_option('bankai_301_redirects', array());
            $redirects[$source] = $target;
            update_option('bankai_301_redirects', $redirects);

            wp_send_json_success(array('message' => 'Redirect created successfully.'));
        }

        wp_send_json_error(array('message' => 'Invalid parameters.'));
    }
}

Bankai_SEO_Engine::get_instance();