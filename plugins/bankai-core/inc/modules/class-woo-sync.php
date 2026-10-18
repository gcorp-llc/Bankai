<?php
/**
 * Bankai WooCommerce Sync Module
 * Secure external sync (inspired by Youtix pattern) for product SEO.
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

class Bankai_Woo_Sync {
    const VERSION = '1.0.0';
    const NAMESPACE = 'bankai/v1';
    const META_MANAGED = '_bankai_woo_managed';

    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('admin_init', [self::class, 'register_settings']);
        if (is_admin()) {
            add_action('add_meta_boxes', [self::class, 'add_meta_box']);
        }
    }

    public static function register_routes() {
        register_rest_route(self::NAMESPACE, '/sync/woo-seo', [
            'methods' => 'POST',
            'callback' => [self::class, 'apply_product_seo'],
            'permission_callback' => [self::class, 'verify_sync_request'],
            'args' => [
                'product_id' => ['required' => true, 'type' => 'integer'],
                'seo_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'short_description' => ['type' => 'string'],
                'attributes' => ['type' => 'array'],
            ]
        ]);
    }

    public static function verify_sync_request($request) {
        // HMAC + timestamp + replay protection
        $signature = $request->get_header('X-Bankai-Signature');
        $timestamp = $request->get_header('X-Bankai-Timestamp');
        $payload = $request->get_body();

        if (!$signature || !$timestamp) {
            return new WP_Error('missing_auth', 'Missing signature', ['status' => 401]);
        }

        // Simple replay check (extend with DB token table in production)
        if (abs(time() - (int)$timestamp) > 300) { // 5 min window
            return new WP_Error('expired', 'Request expired', ['status' => 401]);
        }

        // In production: load API key and verify HMAC
        $key = get_option('bankai_sync_api_key', '');
        $expected = hash_hmac('sha256', $timestamp . $payload, $key);
        if (!hash_equals($expected, $signature)) {
            return new WP_Error('invalid_sig', 'Invalid signature', ['status' => 401]);
        }

        return true;
    }

    public static function apply_product_seo($request) {
        if (!class_exists('WC_Product')) {
            return new WP_Error('no_woo', 'WooCommerce not active');
        }

        $product_id = absint($request['product_id']);
        $product = wc_get_product($product_id);
        if (!$product) {
            return new WP_Error('invalid_product', 'Product not found');
        }

        update_post_meta($product_id, self::META_MANAGED, '1');

        if (!empty($request['seo_title'])) {
            update_post_meta($product_id, '_yoast_wpseo_title', sanitize_text_field($request['seo_title']));
        }
        if (!empty($request['meta_description'])) {
            update_post_meta($product_id, '_yoast_wpseo_metadesc', sanitize_text_field($request['meta_description']));
        }
        if (!empty($request['short_description'])) {
            $product->set_short_description(wp_kses_post($request['short_description']));
        }

        // Attributes (safe add only)
        if (!empty($request['attributes']) && is_array($request['attributes'])) {
            foreach ($request['attributes'] as $attr) {
                if (empty($attr['name']) || empty($attr['value'])) continue;
                $product->update_meta_data('_bankai_attr_' . sanitize_key($attr['name']), sanitize_text_field($attr['value']));
            }
        }

        $product->save();

        return [
            'success' => true,
            'product_id' => $product_id,
            'message' => 'SEO applied successfully'
        ];
    }

    public static function register_settings() {
        register_setting('bankai_sync', 'bankai_sync_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }

    public static function add_meta_box() {
        if (!class_exists('WC_Product')) return;
        add_meta_box(
            'bankai_woo_sync',
            'Bankai Sync',
            [self::class, 'render_meta_box'],
            'product',
            'side'
        );
    }

    public static function render_meta_box($post) {
        $managed = get_post_meta($post->ID, self::META_MANAGED, true);
        ?>
        <p><strong>وضعیت:</strong> <?php echo $managed ? 'مدیریت‌شده توسط Bankai Sync' : 'مستقل'; ?></p>
        <?php
    }
}

// Auto init
add_action('init', ['Bankai_Woo_Sync', 'init']);
