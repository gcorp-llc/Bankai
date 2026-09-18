<?php
/**
 * Bankai Core - SEO Engine & Schema Manager
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

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
        add_action('template_redirect', [$this, 'handle_301_redirects'], 1);
        add_action('wp_ajax_bankai_save_301_redirect', [$this, 'ajax_save_301_redirect']);
        add_action('wp_ajax_bankai_get_404_logs', [$this, 'ajax_get_404_logs']);
    }

    /**
     * Output meta description for singular pages
     */
    public function render_meta_tags(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        if (!$post instanceof WP_Post) {
            return;
        }

        $meta_desc = get_post_meta($post->ID, '_bankai_meta_description', true);

        if (empty($meta_desc)) {
            if (has_excerpt($post->ID)) {
                $meta_desc = get_the_excerpt($post);
            } else {
                $meta_desc = wp_trim_words(wp_strip_all_tags($post->post_content), 28, '…');
            }
        }

        $meta_desc = sanitize_text_field($meta_desc);

        if ($meta_desc !== '') {
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
        }
    }

    /**
     * Basic WebSite + Organization schema
     */
    public function render_schema_json_ld(): void {
        if (is_admin()) {
            return;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => get_bloginfo('name'),
            'url'      => home_url('/'),
            'description' => get_bloginfo('description'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ];

        // Add Organization on front page
        if (is_front_page()) {
            $schema['publisher'] = [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
                'url'   => home_url('/'),
            ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }

    /**
     * Log 404 requests
     */
    public function log_404_anomalies(): void {
        if (!is_404()) {
            return;
        }

        $requested_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if ($requested_uri === '') {
            return;
        }

        // Ignore common bot / asset requests
        if (preg_match('/\.(css|js|map|png|jpe?g|gif|svg|woff2?|ttf|ico|xml)$/i', $requested_uri)) {
            return;
        }

        $logs = get_option('bankai_404_logs', []);
        if (!is_array($logs)) {
            $logs = [];
        }

        if (isset($logs[$requested_uri])) {
            $logs[$requested_uri]['hits']     = (int) $logs[$requested_uri]['hits'] + 1;
            $logs[$requested_uri]['last_hit'] = current_time('mysql');
        } else {
            $logs[$requested_uri] = [
                'hits'     => 1,
                'last_hit' => current_time('mysql'),
            ];
        }

        // Keep only last 150 entries
        if (count($logs) > 150) {
            $logs = array_slice($logs, -150, 150, true);
        }

        update_option('bankai_404_logs', $logs, false);
    }

    /**
     * Apply stored 301 redirects
     */
    public function handle_301_redirects(): void {
        if (is_admin()) {
            return;
        }

        $requested = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if ($requested === '') {
            return;
        }

        $redirects = get_option('bankai_301_redirects', []);
        if (!is_array($redirects) || empty($redirects[$requested])) {
            return;
        }

        $target = esc_url_raw($redirects[$requested]);
        if ($target) {
            wp_redirect($target, 301);
            exit;
        }
    }

    /**
     * AJAX: Save a 301 redirect
     */
    public function ajax_save_301_redirect(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $source = isset($_POST['source_uri']) ? sanitize_text_field(wp_unslash($_POST['source_uri'])) : '';
        $target = isset($_POST['target_uri']) ? esc_url_raw(wp_unslash($_POST['target_uri'])) : '';

        if ($source === '' || $target === '') {
            wp_send_json_error(['message' => __('Invalid parameters.', 'bankai-core')]);
        }

        $redirects = get_option('bankai_301_redirects', []);
        if (!is_array($redirects)) {
            $redirects = [];
        }

        $redirects[$source] = $target;
        update_option('bankai_301_redirects', $redirects, false);

        // Remove from 404 logs if present
        $logs = get_option('bankai_404_logs', []);
        if (is_array($logs) && isset($logs[$source])) {
            unset($logs[$source]);
            update_option('bankai_404_logs', $logs, false);
        }

        wp_send_json_success([
            'message' => __('Redirect created successfully.', 'bankai-core'),
            'source'  => $source,
            'target'  => $target,
        ]);
    }

    /**
     * AJAX: Return 404 logs for admin UI
     */
    public function ajax_get_404_logs(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $logs = get_option('bankai_404_logs', []);
        if (!is_array($logs)) {
            $logs = [];
        }

        // Sort by hits descending
        uasort($logs, static function ($a, $b) {
            return ($b['hits'] ?? 0) <=> ($a['hits'] ?? 0);
        });

        wp_send_json_success(['logs' => $logs]);
    }
}