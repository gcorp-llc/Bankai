<?php
/**
 * Bankai Store Hub — WooCommerce products, stats, AI SEO wizard
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

final class Bankai_Store
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        add_action('wp_ajax_bankai_store_stats', [$this, 'ajax_stats']);
        add_action('wp_ajax_bankai_store_products', [$this, 'ajax_products']);
        add_action('wp_ajax_bankai_store_categories', [$this, 'ajax_categories']);
        add_action('wp_ajax_bankai_store_wizard_step', [$this, 'ajax_wizard_step']);
        add_action('wp_ajax_bankai_store_wizard_apply', [$this, 'ajax_wizard_apply']);
        add_action('wp_ajax_bankai_store_create_product', [$this, 'ajax_create_product']);
        add_action('wp_ajax_bankai_store_ai_complete', [$this, 'ajax_ai_complete']);
        add_action('wp_ajax_bankai_store_woo_status', [$this, 'ajax_woo_status']);
        add_action('wp_ajax_bankai_store_product_report', [$this, 'ajax_product_report']);
        add_action('wp_ajax_bankai_store_gen_cover', [$this, 'ajax_gen_cover']);
    }

    public static function woo_active(): bool
    {
        return class_exists('WooCommerce') && class_exists('WC_Product');
    }

    /**
     * Three-state WooCommerce dependency.
     * @return array{status:string,installed:bool,active:bool,message:string,action_url:string}
     */
    public static function woo_status(): array
    {
        $installed = false;
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (function_exists('get_plugins')) {
            foreach (get_plugins() as $file => $data) {
                if ($file === 'woocommerce/woocommerce.php' || str_contains((string) $file, 'woocommerce.php')) {
                    $installed = true;
                    break;
                }
            }
        }
        // Fallback: class may exist even if detection failed
        if (!$installed && (class_exists('WooCommerce') || defined('WC_PLUGIN_FILE'))) {
            $installed = true;
        }
        $active = self::woo_active();
        if ($active) {
            return [
                'status' => 'active',
                'installed' => true,
                'active' => true,
                'message' => 'WooCommerce فعال است',
                'action_url' => '',
            ];
        }
        if ($installed) {
            return [
                'status' => 'inactive',
                'installed' => true,
                'active' => false,
                'message' => 'WooCommerce نصب است اما فعال نیست',
                'action_url' => admin_url('plugins.php'),
            ];
        }
        return [
            'status' => 'missing',
            'installed' => false,
            'active' => false,
            'message' => 'WooCommerce نصب نیست',
            'action_url' => admin_url('plugin-install.php?s=woocommerce&tab=search&type=term'),
        ];
    }

    public function ajax_woo_status(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        wp_send_json_success(self::woo_status());
    }

    /** Detect primary SEO plugin for meta ownership. */
    public static function seo_plugin(): string
    {
        if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
            return 'rankmath';
        }
        if (defined('WPSEO_VERSION') || class_exists('WPSEO_Meta')) {
            return 'yoast';
        }
        return 'bankai';
    }

    /** Write SEO title/desc/keyword to the correct meta owner. */
    private function write_seo_meta(int $product_id, string $title, string $desc, string $keyword): void
    {
        $plugin = self::seo_plugin();
        if ($title !== '') {
            update_post_meta($product_id, '_bankai_seo_title', sanitize_text_field($title));
            if ($plugin === 'yoast') {
                update_post_meta($product_id, '_yoast_wpseo_title', sanitize_text_field($title));
            } elseif ($plugin === 'rankmath') {
                update_post_meta($product_id, 'rank_math_title', sanitize_text_field($title));
            }
        }
        if ($desc !== '') {
            update_post_meta($product_id, '_bankai_seo_description', sanitize_textarea_field($desc));
            if ($plugin === 'yoast') {
                update_post_meta($product_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($desc));
            } elseif ($plugin === 'rankmath') {
                update_post_meta($product_id, 'rank_math_description', sanitize_textarea_field($desc));
            }
        }
        if ($keyword !== '') {
            update_post_meta($product_id, '_bankai_seo_focus_keyword', sanitize_text_field($keyword));
            update_post_meta($product_id, '_bankai_seo_keyword', sanitize_text_field($keyword));
            if ($plugin === 'yoast') {
                update_post_meta($product_id, '_yoast_wpseo_focuskw', sanitize_text_field($keyword));
            } elseif ($plugin === 'rankmath') {
                update_post_meta($product_id, 'rank_math_focus_keyword', sanitize_text_field($keyword));
            }
        }
    }

    private function read_seo_meta(int $product_id): array
    {
        $plugin = self::seo_plugin();
        $title = (string) get_post_meta($product_id, '_bankai_seo_title', true);
        $desc  = (string) get_post_meta($product_id, '_bankai_seo_description', true);
        $kw    = (string) get_post_meta($product_id, '_bankai_seo_focus_keyword', true);
        if ($plugin === 'yoast') {
            if ($title === '') $title = (string) get_post_meta($product_id, '_yoast_wpseo_title', true);
            if ($desc === '')  $desc  = (string) get_post_meta($product_id, '_yoast_wpseo_metadesc', true);
            if ($kw === '')    $kw    = (string) get_post_meta($product_id, '_yoast_wpseo_focuskw', true);
        } elseif ($plugin === 'rankmath') {
            if ($title === '') $title = (string) get_post_meta($product_id, 'rank_math_title', true);
            if ($desc === '')  $desc  = (string) get_post_meta($product_id, 'rank_math_description', true);
            if ($kw === '')    $kw    = (string) get_post_meta($product_id, 'rank_math_focus_keyword', true);
        }
        return ['seo_title' => $title, 'seo_description' => $desc, 'focus_keyword' => $kw, 'plugin' => $plugin];
    }

    public function ajax_stats(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive', 'woo' => false, 'woo_status' => self::woo_status()], 400);
        }

        $period = absint($_POST['period'] ?? 30);
        if (!in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $counts = (array) wp_count_posts('product');
        $published = (int) ($counts['publish'] ?? 0);
        $draft = (int) ($counts['draft'] ?? 0);

        $instock = (int) (new WP_Query([
            'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_query' => [['key' => '_stock_status', 'value' => 'instock']],
        ]))->found_posts;

        $outofstock = (int) (new WP_Query([
            'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_query' => [['key' => '_stock_status', 'value' => 'outofstock']],
        ]))->found_posts;

        $low_threshold = (int) get_option('woocommerce_notify_low_stock_amount', 2);
        $lowstock = (int) (new WP_Query([
            'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_manage_stock', 'value' => 'yes'],
                ['key' => '_stock', 'value' => $low_threshold, 'compare' => '<=', 'type' => 'NUMERIC'],
                ['key' => '_stock_status', 'value' => 'instock'],
            ],
        ]))->found_posts;

        $onsale = (int) (new WP_Query([
            'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_sale_price', 'value' => '', 'compare' => '!='],
                ['key' => '_sale_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC'],
            ],
        ]))->found_posts;

        $sales_total = 0.0;
        $orders_count = 0;
        $series = [];
        $order_series = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $day = gmdate('Y-m-d', strtotime("-{$i} days"));
            $series[$day] = 0.0;
            $order_series[$day] = 0;
        }

        $prev_sales = 0.0;
        $prev_orders = 0;

        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders([
                'limit' => 500,
                'status' => ['wc-completed', 'wc-processing'],
                'date_created' => '>' . (time() - $period * DAY_IN_SECONDS),
                'return' => 'objects',
            ]);
            foreach ($orders as $order) {
                $orders_count++;
                $total = (float) $order->get_total();
                $sales_total += $total;
                $d = $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : '';
                if ($d && isset($series[$d])) {
                    $series[$d] += $total;
                    $order_series[$d]++;
                }
            }
            // Previous period comparison
            $prev_orders_list = wc_get_orders([
                'limit' => 500,
                'status' => ['wc-completed', 'wc-processing'],
                'date_created' => (time() - 2 * $period * DAY_IN_SECONDS) . '...' . (time() - $period * DAY_IN_SECONDS),
                'return' => 'objects',
            ]);
            foreach ($prev_orders_list as $order) {
                $prev_orders++;
                $prev_sales += (float) $order->get_total();
            }
        }

        $avg_order = $orders_count > 0 ? round($sales_total / $orders_count, 2) : 0;
        $sales_change = $prev_sales > 0 ? round((($sales_total - $prev_sales) / $prev_sales) * 100, 1) : null;
        $orders_change = $prev_orders > 0 ? round((($orders_count - $prev_orders) / $prev_orders) * 100, 1) : null;

        wp_send_json_success([
            'woo' => true,
            'woo_status' => self::woo_status(),
            'period' => $period,
            'products' => $published,
            'drafts' => $draft,
            'in_stock' => $instock,
            'out_of_stock' => $outofstock,
            'low_stock' => $lowstock,
            'on_sale' => $onsale,
            'orders_30d' => $orders_count,
            'sales_30d' => round($sales_total, 2),
            'avg_order' => $avg_order,
            'sales_change' => $sales_change,
            'orders_change' => $orders_change,
            'currency' => function_exists('get_woocommerce_currency_symbol') ? html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8') : '',
            'series' => array_values(array_map(static function ($k, $v) {
                return ['day' => $k, 'total' => round((float) $v, 2)];
            }, array_keys($series), array_values($series))),
            'order_series' => array_values(array_map(static function ($k, $v) {
                return ['day' => $k, 'count' => (int) $v];
            }, array_keys($order_series), array_values($order_series))),
            'inventory_dist' => [
                'in_stock' => $instock,
                'low_stock' => $lowstock,
                'out_of_stock' => $outofstock,
            ],
        ]);
    }

    public function ajax_products(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive'], 400);
        }

        $search   = sanitize_text_field(wp_unslash($_POST['search'] ?? ''));
        $stock    = sanitize_key($_POST['stock'] ?? 'all');
        $status   = sanitize_key($_POST['status'] ?? 'all');
        $type     = sanitize_key($_POST['type'] ?? 'all');
        $on_sale  = sanitize_key($_POST['on_sale'] ?? 'all');
        $orderby  = sanitize_key($_POST['orderby'] ?? 'date');
        $page     = max(1, absint($_POST['page'] ?? 1));
        $per      = min(48, max(6, absint($_POST['per_page'] ?? 12)));
        $cat      = absint($_POST['category'] ?? 0);
        $price_min = isset($_POST['price_min']) && $_POST['price_min'] !== '' ? (float) wp_unslash($_POST['price_min']) : null;
        $price_max = isset($_POST['price_max']) && $_POST['price_max'] !== '' ? (float) wp_unslash($_POST['price_max']) : null;
        $seo_filter = sanitize_key($_POST['seo_filter'] ?? 'all'); // all|needs_seo|has_seo

        $args = [
            'post_type'      => 'product',
            'post_status'    => ['publish', 'draft', 'private'],
            'posts_per_page' => $per,
            'paged'          => $page,
        ];

        if ($search !== '') {
            // Search title + SKU
            $args['s'] = $search;
            add_filter('posts_search', function ($search_sql, $query) use ($search) {
                global $wpdb;
                if (!$query->is_main_query() && $query->get('post_type') === 'product') {
                    $like = '%' . $wpdb->esc_like($search) . '%';
                    $search_sql .= $wpdb->prepare(" OR ({$wpdb->posts}.ID IN (
                        SELECT post_id FROM {$wpdb->postmeta}
                        WHERE meta_key = '_sku' AND meta_value LIKE %s
                    ))", $like);
                }
                return $search_sql;
            }, 10, 2);
        }

        if ($status === 'publish' || $status === 'draft' || $status === 'private') {
            $args['post_status'] = $status;
        }

        $meta_query = ['relation' => 'AND'];

        if ($stock === 'instock' || $stock === 'outofstock' || $stock === 'onbackorder') {
            $meta_query[] = [
                'key'   => '_stock_status',
                'value' => $stock,
            ];
        } elseif ($stock === 'lowstock') {
            $low_threshold = (int) get_option('woocommerce_notify_low_stock_amount', 2);
            $meta_query[] = ['key' => '_manage_stock', 'value' => 'yes'];
            $meta_query[] = ['key' => '_stock', 'value' => $low_threshold, 'compare' => '<=', 'type' => 'NUMERIC'];
            $meta_query[] = ['key' => '_stock_status', 'value' => 'instock'];
        }

        if ($on_sale === 'yes') {
            $meta_query[] = ['key' => '_sale_price', 'value' => '', 'compare' => '!='];
            $meta_query[] = ['key' => '_sale_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC'];
        } elseif ($on_sale === 'no') {
            $meta_query[] = [
                'relation' => 'OR',
                ['key' => '_sale_price', 'compare' => 'NOT EXISTS'],
                ['key' => '_sale_price', 'value' => ''],
                ['key' => '_sale_price', 'value' => 0, 'type' => 'NUMERIC'],
            ];
        }

        if ($price_min !== null) {
            $meta_query[] = ['key' => '_price', 'value' => $price_min, 'compare' => '>=', 'type' => 'NUMERIC'];
        }
        if ($price_max !== null) {
            $meta_query[] = ['key' => '_price', 'value' => $price_max, 'compare' => '<=', 'type' => 'NUMERIC'];
        }
        if ($seo_filter === 'needs_seo') {
            $meta_query[] = [
                'relation' => 'OR',
                ['key' => '_bankai_seo_title', 'compare' => 'NOT EXISTS'],
                ['key' => '_bankai_seo_title', 'value' => ''],
            ];
        } elseif ($seo_filter === 'has_seo') {
            $meta_query[] = ['key' => '_bankai_seo_title', 'value' => '', 'compare' => '!='];
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        if ($type !== 'all' && in_array($type, ['simple', 'variable', 'grouped', 'external'], true)) {
            $args['tax_query'][] = [
                'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => $type,
            ];
        }

        if ($cat > 0) {
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat,
            ];
        }

        if ($orderby === 'title') {
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
        } elseif ($orderby === 'oldest') {
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
        } elseif ($orderby === 'price_asc') {
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
        } elseif ($orderby === 'price_desc') {
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
        } elseif ($orderby === 'stock') {
            $args['meta_key'] = '_stock';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
        } elseif ($orderby === 'seo') {
            $args['meta_key'] = '_bankai_product_scores';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'DESC';
        } else {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }

        $q = new WP_Query($args);
        $items = [];
        foreach ($q->posts as $post) {
            $items[] = $this->product_card_data($post->ID);
        }

        wp_send_json_success([
            'items' => $items,
            'total' => (int) $q->found_posts,
            'pages' => (int) $q->max_num_pages,
            'page'  => $page,
        ]);
    }


    public function ajax_categories(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive'], 400);
        }
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'number' => 200,
        ]);
        $out = [];
        if (!is_wp_error($terms)) {
            foreach ($terms as $t) {
                $out[] = [
                    'id' => (int) $t->term_id,
                    'name' => $t->name,
                    'count' => (int) $t->count,
                ];
            }
        }
        wp_send_json_success(['items' => $out]);
    }

    private function product_card_data(int $id): array
    {
        $p = wc_get_product($id);
        $img = get_the_post_thumbnail_url($id, 'medium') ?: (function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('medium') : '');
        $regular = $p ? (float) $p->get_regular_price() : 0;
        $sale = $p ? (float) $p->get_sale_price() : 0;
        $price = $p ? (float) $p->get_price() : 0;
        $discount = ($regular > 0 && $sale > 0 && $sale < $regular)
            ? (int) round((1 - $sale / $regular) * 100)
            : 0;

        $scores = $this->product_scores($id);
        $thumb_id = get_post_thumbnail_id($id);
        $current_alt = $thumb_id ? (string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';

        $cats = [];
        $tags = [];
        $term_cats = get_the_terms($id, 'product_cat');
        if (is_array($term_cats)) {
            foreach ($term_cats as $t) {
                $cats[] = ['id' => (int) $t->term_id, 'name' => $t->name];
            }
        }
        $term_tags = get_the_terms($id, 'product_tag');
        if (is_array($term_tags)) {
            foreach ($term_tags as $t) {
                $tags[] = ['id' => (int) $t->term_id, 'name' => $t->name];
            }
        }
        $short_plain = $p ? wp_strip_all_tags((string) $p->get_short_description()) : '';
        if (function_exists('mb_substr')) {
            $short_plain = mb_substr($short_plain, 0, 160);
        } else {
            $short_plain = substr($short_plain, 0, 160);
        }

        return [
            'id' => $id,
            'name' => get_the_title($id),
            'status' => get_post_status($id),
            'permalink' => get_permalink($id),
            'edit_url' => get_edit_post_link($id, 'raw'),
            'image' => $img,
            'price' => $price,
            'regular_price' => $regular,
            'sale_price' => $sale,
            'discount' => $discount,
            'on_sale' => $discount > 0,
            'price_html' => $p ? wp_strip_all_tags($p->get_price_html()) : '',
            'stock_status' => $p ? $p->get_stock_status() : 'outofstock',
            'stock_qty' => $p ? $p->get_stock_quantity() : null,
            'in_stock' => $p ? $p->is_in_stock() : false,
            'sku' => $p ? (string) $p->get_sku() : '',
            'seo' => $scores['seo'],
            'aeo' => $scores['aeo'],
            'geo' => $scores['geo'],
            'categories' => $cats,
            'tags' => $tags,
            'short_description' => $short_plain,
            // Current SEO field values for wizard comparison (respect Yoast/Rank Math)
            'fields' => array_merge($this->read_seo_meta($id), [
                'schema_json' => (string) get_post_meta($id, '_bankai_seo_schema', true),
                'faq_html'    => (string) get_post_meta($id, '_bankai_seo_faq_html', true),
                'geo_html'    => (string) get_post_meta($id, '_bankai_seo_geo_html', true),
                'image_alt'   => $current_alt,
                'short_description' => $p ? (string) $p->get_short_description() : '',
                'description' => $p ? (string) $p->get_description() : '',
                'categories'  => implode(', ', array_map(static fn($c) => $c['name'], $cats)),
                'tags'        => implode(', ', array_map(static fn($t) => $t['name'], $tags)),
            ]),
            'type' => $p ? $p->get_type() : 'simple',
        ];
    }

    private function product_scores(int $id): array
    {
        $title = (string) get_post_meta($id, '_bankai_seo_title', true);
        $desc = (string) get_post_meta($id, '_bankai_seo_description', true);
        $kw = (string) get_post_meta($id, '_bankai_seo_focus_keyword', true);
        $schema = (string) get_post_meta($id, '_bankai_seo_schema', true);
        $faq = (string) get_post_meta($id, '_bankai_seo_faq_html', true);
        $geo = (string) get_post_meta($id, '_bankai_seo_geo_html', true);

        $seo = 20;
        if ($title !== '') {
            $seo += 30;
        }
        if ($desc !== '') {
            $seo += 25;
        }
        if ($kw !== '') {
            $seo += 15;
        }
        if ($schema !== '') {
            $seo += 10;
        }
        $seo = min(100, $seo);

        $aeo = 15;
        if ($faq !== '') {
            $aeo += 55;
        }
        if ($schema !== '' && str_contains($schema, 'FAQPage')) {
            $aeo += 20;
        }
        $aeo = min(100, $aeo);

        $geo_s = 20;
        if ($geo !== '') {
            $geo_s += 50;
        }
        if ($desc !== '' && mb_strlen($desc) > 80) {
            $geo_s += 20;
        }
        $geo_s = min(100, $geo_s);

        $saved = get_post_meta($id, '_bankai_product_scores', true);
        if (is_array($saved) && isset($saved['seo'])) {
            return [
                'seo' => (int) $saved['seo'],
                'aeo' => (int) $saved['aeo'],
                'geo' => (int) $saved['geo'],
            ];
        }

        return ['seo' => $seo, 'aeo' => $aeo, 'geo' => $geo_s];
    }

    private function save_scores(int $id): void
    {
        // force recompute without cache
        delete_post_meta($id, '_bankai_product_scores');
        $s = $this->product_scores($id);
        update_post_meta($id, '_bankai_product_scores', $s);
    }

    public function ajax_wizard_step(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive'], 400);
        }

        $product_id = absint($_POST['product_id'] ?? 0);
        $step = sanitize_key($_POST['step'] ?? '');
        $gen_image = !empty($_POST['gen_image']);
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(['message' => 'محصول یافت نشد'], 404);
        }

        $post = get_post($product_id);
        $name = $post ? $post->post_title : $product->get_name();
        $content = $post ? wp_strip_all_tags($post->post_content) : '';
        $short = $product->get_short_description();
        $price = $product->get_price();

        $draft = [];
        $label = $step;

        try {
            switch ($step) {
                case 'meta':
                    $label = 'تولید متا تگ‌ها';
                    $draft = $this->ai_json(
                        'You are an expert WooCommerce SEO specialist for Persian/RTL stores.',
                        "Product name: {$name}\nPrice: {$price}\nShort: {$short}\nContent: " . mb_substr($content, 0, 1200) . "\n\nReturn JSON only:\n{\"seo_title\":\"max 60 chars\",\"description\":\"meta description 120-155 chars\",\"focus_keyword\":\"main keyword\",\"og_title\":\"\",\"og_description\":\"\"}"
                    );
                    break;

                case 'content':
                    $label = 'تولید توضیحات محصول';
                    $draft = $this->ai_json(
                        'You are a Persian ecommerce copywriter. Improve product short and long descriptions without inventing technical specs.',
                        "Product: {$name}\nPrice: {$price}\nCurrent short: " . mb_substr(wp_strip_all_tags((string)$short), 0, 400) . "\nCurrent content: " . mb_substr($content, 0, 800) . "\n\nReturn JSON only:\n{\"short_description\":\"2-3 sentences Persian\",\"description\":\"HTML paragraphs Persian product intro\",\"slug\":\"url-friendly-slug\"}"
                    );
                    break;

                case 'taxonomies':
                    $label = 'دسته‌بندی و برچسب‌ها';
                    $existing_cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
                    $existing_tags = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'names']);
                    if (is_wp_error($existing_cats)) {
                        $existing_cats = [];
                    }
                    if (is_wp_error($existing_tags)) {
                        $existing_tags = [];
                    }
                    $all_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 80, 'fields' => 'names']);
                    if (is_wp_error($all_cats)) {
                        $all_cats = [];
                    }
                    $draft = $this->ai_json(
                        'You help assign WooCommerce product categories and tags in Persian. Only suggest from existing store categories when possible. Do not invent prices.',
                        "Product: {$name}\nExisting categories: " . implode('، ', $existing_cats) . "\nExisting tags: " . implode('، ', $existing_tags) . "\nAvailable categories: " . implode('، ', array_slice((array) $all_cats, 0, 40)) . "\n\nReturn JSON only:\n{\"categories\":\"comma separated category names\",\"tags\":\"comma separated tags\",\"keywords\":\"comma separated related keywords\"}"
                    );
                    $draft['current_categories'] = implode('، ', $existing_cats);
                    $draft['current_tags'] = implode('، ', $existing_tags);
                    break;

                case 'schema':
                    $label = 'تولید اسکیمای محصول';
                    $draft = $this->ai_json(
                        'Generate Product JSON-LD schema fields.',
                        "Product: {$name}\nPrice: {$price}\nReturn JSON: {\"schema_type\":\"Product\",\"brand\":\"\",\"sku\":\"" . $product->get_sku() . "\",\"extra_props\":{}}"
                    );
                    $draft['schema_json'] = wp_json_encode([
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $name,
                        'sku' => $product->get_sku(),
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => $price,
                            'priceCurrency' => get_woocommerce_currency(),
                            'availability' => $product->is_in_stock()
                                ? 'https://schema.org/InStock'
                                : 'https://schema.org/OutOfStock',
                        ],
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    break;

                case 'aeo':
                    $label = 'بهینه‌سازی AEO / FAQ';
                    $draft = $this->ai_json(
                        'Create FAQ for product page useful for AI answer engines. Persian language.',
                        "Product: {$name}\nReturn JSON: {\"faq\":[{\"q\":\"...\",\"a\":\"...\"},{\"q\":\"...\",\"a\":\"...\"},{\"q\":\"...\",\"a\":\"...\"}]}"
                    );
                    if (!empty($draft['faq']) && is_array($draft['faq'])) {
                        $html = '<div class="bankai-product-faq"><h3>پرسش‌های متداول</h3>';
                        foreach ($draft['faq'] as $row) {
                            $q = esc_html($row['q'] ?? '');
                            $a = esc_html($row['a'] ?? '');
                            $html .= "<details><summary>{$q}</summary><p>{$a}</p></details>";
                        }
                        $html .= '</div>';
                        $draft['faq_html'] = $html;
                    }
                    break;

                case 'geo':
                    $label = 'بهینه‌سازی GEO';
                    $draft = $this->ai_json(
                        'Write a short GEO-friendly product summary with clear definitions for AI overviews. Persian.',
                        "Product: {$name}\nReturn JSON: {\"geo_summary\":\"2-3 paragraphs\",\"key_takeaways\":[\"...\",\"...\"]}"
                    );
                    if (!empty($draft['geo_summary'])) {
                        $html = '<div class="bankai-product-geo"><h3>خلاصه محصول</h3><p>'
                            . esc_html($draft['geo_summary']) . '</p>';
                        if (!empty($draft['key_takeaways']) && is_array($draft['key_takeaways'])) {
                            $html .= '<ul>';
                            foreach ($draft['key_takeaways'] as $t) {
                                $html .= '<li>' . esc_html((string) $t) . '</li>';
                            }
                            $html .= '</ul>';
                        }
                        $html .= '</div>';
                        $draft['geo_html'] = $html;
                    }
                    break;

                case 'links':
                    $label = 'پیشنهاد لینک داخلی';
                    $related = get_posts([
                        'post_type' => 'product',
                        'posts_per_page' => 5,
                        'post__not_in' => [$product_id],
                        'orderby' => 'rand',
                        'fields' => 'ids',
                    ]);
                    $links = [];
                    foreach ($related as $rid) {
                        $links[] = [
                            'id' => $rid,
                            'title' => get_the_title($rid),
                            'url' => get_permalink($rid),
                        ];
                    }
                    $draft = ['internal_links' => $links];
                    break;

                case 'images':
                    $label = 'Alt و فشرده‌سازی تصویر';
                    $thumb_id = get_post_thumbnail_id($product_id);
                    $alt = $name;
                    if ($thumb_id) {
                        $existing = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                        $draft['alt'] = $existing !== '' ? (string) $existing : $alt;
                        $draft['current_alt'] = (string) $existing;
                        $draft['attachment_id'] = (int) $thumb_id;
                        $draft['compress'] = false;
                        $draft['compress_available'] = false;

                        $file = get_attached_file($thumb_id);
                        $meta = wp_get_attachment_metadata($thumb_id);
                        $w = (int) ($meta['width'] ?? 0);
                        $h = (int) ($meta['height'] ?? 0);
                        $bytes = ($file && file_exists($file)) ? (int) filesize($file) : 0;
                        $mime = (string) (get_post_mime_type($thumb_id) ?: '');
                        $draft['analysis'] = [
                            'width'  => $w,
                            'height' => $h,
                            'bytes'  => $bytes,
                            'kb'     => $bytes > 0 ? round($bytes / 1024, 1) : 0,
                            'mime'   => $mime,
                        ];

                        $notes = [];
                        $notes[] = $existing !== ''
                            ? 'Alt فعلی وجود دارد — می‌توانید پیشنهاد جدید را بپذیرید'
                            : 'پیشنهاد Alt بر اساس نام محصول (پس از تأیید اعمال می‌شود)';

                        if ($bytes > 350 * 1024 || $w > 1600) {
                            $draft['compress'] = true;
                            $notes[] = 'حجم یا ابعاد تصویر بالاست — فشرده‌سازی WebP پیشنهاد می‌شود';
                        }
                        if (class_exists('Bankai_Media_Watermark') && method_exists(Bankai_Media_Watermark::instance(), 'compress_attachment_file')) {
                            $draft['compress_available'] = true;
                            $notes[] = 'فشرده‌سازی پس از تأیید از طریق ماژول رسانه اجرا می‌شود (فایل اصلی در صورت تنظیمات حفظ می‌شود).';
                        } else {
                            $notes[] = 'ماژول رسانه برای فشرده‌سازی در دسترس نیست.';
                        }
                        if ($w > 0 && $h > 0) {
                            $notes[] = sprintf('ابعاد فعلی: %d×%d · حجم: %s KB', $w, $h, $draft['analysis']['kb']);
                        }
                        $draft['note'] = implode(' · ', $notes);
                    } else {
                        $draft['note'] = 'تصویر شاخص وجود ندارد';
                    }
                    break;

                case 'cover':
                    $label = 'تولید تصویر کاور (اختیاری)';
                    if (!$gen_image) {
                        $draft = ['skipped' => true, 'note' => 'تولید تصویر رد شد'];
                        break;
                    }
                    $prompt = "Professional ecommerce product photography of \"{$name}\", studio lighting, clean white background, catalog style, no text, no watermark";
                    $draft = ['prompt' => $prompt];
                    if (class_exists('Bankai_AI_Studio')) {
                        $ai = Bankai_AI_Studio::instance();
                        $provider = method_exists($ai, 'get_default_image_provider') ? $ai->get_default_image_provider() : '';
                        $ready_err = method_exists($ai, 'image_provider_readiness_error') ? $ai->image_provider_readiness_error($provider) : 'no method';
                        if ($ready_err === '' && $provider !== '') {
                            try {
                                $bytes = $ai->generate_image_bytes($provider, $prompt, ['width' => 1024, 'height' => 1024]);
                                $bin = is_array($bytes) ? ($bytes['bytes'] ?? $bytes['data'] ?? null) : null;
                                if (is_string($bin) && $bin !== '') {
                                    $upload = wp_upload_bits('bankai-cover-' . $product_id . '-' . time() . '.png', null, $bin);
                                    if (empty($upload['error']) && !empty($upload['file'])) {
                                        $filetype = wp_check_filetype($upload['file'], null);
                                        $attachment = [
                                            'post_mime_type' => $filetype['type'] ?: 'image/png',
                                            'post_title'     => sanitize_file_name($name . ' cover'),
                                            'post_content'   => '',
                                            'post_status'    => 'inherit',
                                        ];
                                        $attach_id = wp_insert_attachment($attachment, $upload['file']);
                                        if ($attach_id && !is_wp_error($attach_id)) {
                                            require_once ABSPATH . 'wp-admin/includes/image.php';
                                            $meta = wp_generate_attachment_metadata($attach_id, $upload['file']);
                                            wp_update_attachment_metadata($attach_id, $meta);
                                            update_post_meta($attach_id, '_wp_attachment_image_alt', sanitize_text_field($name));
                                            $draft['attachment_id'] = (int) $attach_id;
                                            $draft['url'] = wp_get_attachment_url($attach_id);
                                            $draft['note'] = 'تصویر تولید شد — پس از تأیید به‌عنوان تصویر شاخص اعمال می‌شود';
                                        } else {
                                            $draft['note'] = 'تولید شد اما ذخیره در رسانه ناموفق بود';
                                        }
                                    } else {
                                        $draft['note'] = 'خطا در آپلود تصویر: ' . ($upload['error'] ?? 'unknown');
                                    }
                                } else {
                                    $draft['note'] = 'خروجی تصویر خالی بود — پرامپت ذخیره شد';
                                }
                            } catch (Throwable $ie) {
                                $draft['note'] = 'خطای تولید تصویر: ' . $ie->getMessage();
                            }
                        } else {
                            $draft['note'] = $ready_err !== '' ? $ready_err : 'موتور تصویر پیکربندی نشده — فقط پرامپت ذخیره شد';
                        }
                    } else {
                        $draft['note'] = 'ماژول استودیو AI در دسترس نیست — فقط پرامپت ذخیره شد';
                    }
                    break;

                default:
                    wp_send_json_error(['message' => 'گام نامعتبر'], 400);
            }
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage(), 'step' => $step], 500);
        }

        // stash in transient for apply
        $key = 'bankai_store_wiz_' . get_current_user_id() . '_' . $product_id;
        $bag = get_transient($key);
        if (!is_array($bag)) {
            $bag = [];
        }
        // Capture product version baseline on first step of this wizard run
        if (empty($bag['_baseline'])) {
            $bag['_baseline'] = [
                'modified_gmt' => $post ? (string) $post->post_modified_gmt : '',
                'modified'     => $post ? (string) $post->post_modified : '',
                'title'        => $name,
                'captured_at'  => time(),
            ];
        }
        $bag[$step] = $draft;
        set_transient($key, $bag, HOUR_IN_SECONDS);

        wp_send_json_success([
            'step' => $step,
            'label' => $label,
            'draft' => $draft,
            'baseline' => $bag['_baseline'] ?? null,
        ]);
    }

    public function ajax_wizard_apply(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $product_id = absint($_POST['product_id'] ?? 0);
        if ($product_id <= 0 || get_post_type($product_id) !== 'product') {
            wp_send_json_error(['message' => 'محصول نامعتبر'], 400);
        }

        $key = 'bankai_store_wiz_' . get_current_user_id() . '_' . $product_id;
        $stored = get_transient($key);
        // Allow client to send proposed overrides (field selection)
        $client = json_decode(wp_unslash($_POST['proposed'] ?? '{}'), true);
        $bag = is_array($stored) ? $stored : [];
        if (is_array($client) && $client) {
            // Merge selected proposals into stored bag (keep baseline)
            foreach ($client as $ck => $cv) {
                if ($ck === '_baseline') {
                    continue;
                }
                $bag[$ck] = $cv;
            }
        }
        if (!$bag || (count($bag) === 1 && isset($bag['_baseline']))) {
            // client-only payload without transient is still valid
            if (is_array($client) && $client) {
                $bag = $client;
            } else {
                wp_send_json_error(['message' => 'پیش‌نویس یافت نشد'], 400);
            }
        }

        // Version conflict control
        $force = !empty($_POST['force']);
        $baseline = $bag['_baseline'] ?? (is_array($stored) ? ($stored['_baseline'] ?? null) : null);
        $post_now = get_post($product_id);
        if (!$force && is_array($baseline) && !empty($baseline['modified_gmt']) && $post_now) {
            $current_mod = (string) $post_now->post_modified_gmt;
            if ($current_mod !== '' && $current_mod !== (string) $baseline['modified_gmt']) {
                wp_send_json_error([
                    'code' => 'conflict',
                    'message' => 'محصول پس از شروع ویزارد در جای دیگری ویرایش شده است. برای جلوگیری از بازنویسی ناخواسته، تأیید مجدد لازم است.',
                    'baseline' => $baseline,
                    'current' => [
                        'modified_gmt' => $current_mod,
                        'modified' => (string) $post_now->post_modified,
                        'title' => (string) $post_now->post_title,
                    ],
                ], 409);
            }
        }

        $meta = $bag['meta'] ?? [];
        $this->write_seo_meta(
            $product_id,
            (string) ($meta['seo_title'] ?? ''),
            (string) ($meta['description'] ?? ''),
            (string) ($meta['focus_keyword'] ?? '')
        );
        if (!empty($meta['og_title'])) {
            update_post_meta($product_id, '_bankai_seo_og_title', sanitize_text_field($meta['og_title']));
        }
        if (!empty($meta['og_description'])) {
            update_post_meta($product_id, '_bankai_seo_og_description', sanitize_textarea_field($meta['og_description']));
        }

        // Product content (short/long description) — only if explicitly proposed
        $content = $bag['content'] ?? [];
        if (!empty($content['short_description']) || !empty($content['description'])) {
            $product_obj = wc_get_product($product_id);
            if ($product_obj) {
                if (!empty($content['short_description'])) {
                    $product_obj->set_short_description(wp_kses_post($content['short_description']));
                }
                if (!empty($content['description'])) {
                    $product_obj->set_description(wp_kses_post($content['description']));
                }
                $product_obj->save();
            }
        }
        if (!empty($content['slug'])) {
            wp_update_post([
                'ID' => $product_id,
                'post_name' => sanitize_title($content['slug']),
            ]);
        }

        // Categories / tags / related keywords
        $tax = $bag['taxonomies'] ?? [];
        if (!empty($tax['categories'])) {
            $names = array_filter(array_map('trim', explode(',', (string) $tax['categories'])));
            if ($names) {
                wp_set_object_terms($product_id, $names, 'product_cat', false);
            }
        }
        if (!empty($tax['tags'])) {
            $names = array_filter(array_map('trim', explode(',', (string) $tax['tags'])));
            if ($names) {
                wp_set_object_terms($product_id, $names, 'product_tag', false);
            }
        }
        if (!empty($tax['keywords'])) {
            update_post_meta($product_id, '_bankai_seo_related_keywords', sanitize_text_field($tax['keywords']));
            // Merge into focus keyword meta as secondary list
            $fk = (string) get_post_meta($product_id, '_bankai_seo_focus_keyword', true);
            if ($fk === '' && !empty($tax['keywords'])) {
                $first = trim(explode(',', (string) $tax['keywords'])[0]);
                if ($first !== '') {
                    $this->write_seo_meta($product_id, '', '', $first);
                }
            }
        }

        if (!empty($bag['schema']['schema_json'])) {
            update_post_meta($product_id, '_bankai_seo_schema', wp_unslash($bag['schema']['schema_json']));
        }
        if (!empty($bag['aeo']['faq_html'])) {
            update_post_meta($product_id, '_bankai_seo_faq_html', wp_kses_post($bag['aeo']['faq_html']));
        }
        if (!empty($bag['geo']['geo_html'])) {
            update_post_meta($product_id, '_bankai_seo_geo_html', wp_kses_post($bag['geo']['geo_html']));
        }

        // Image alt + optional compression via media module
        if (!empty($bag['images']['alt']) || !empty($bag['images']['do_compress'])) {
            $thumb_id = !empty($bag['images']['attachment_id'])
                ? absint($bag['images']['attachment_id'])
                : get_post_thumbnail_id($product_id);
            if ($thumb_id) {
                if (!empty($bag['images']['alt'])) {
                    update_post_meta($thumb_id, '_wp_attachment_image_alt', sanitize_text_field($bag['images']['alt']));
                }
                if (!empty($bag['images']['do_compress'])
                    && class_exists('Bankai_Media_Watermark')
                    && method_exists(Bankai_Media_Watermark::instance(), 'compress_attachment_file')
                ) {
                    $result = Bankai_Media_Watermark::instance()->compress_attachment_file(
                        $thumb_id,
                        'webp',
                        82,
                        1600
                    );
                    if (is_wp_error($result)) {
                        // Non-fatal: keep other changes
                        update_post_meta($product_id, '_bankai_store_last_compress_error', $result->get_error_message());
                    } else {
                        delete_post_meta($product_id, '_bankai_store_last_compress_error');
                    }
                }
            }
        }

        // Internal links suggestion (store as meta for reference)
        if (!empty($bag['links']['raw'])) {
            update_post_meta($product_id, '_bankai_seo_internal_links_suggest', sanitize_textarea_field($bag['links']['raw']));
        }

        // Cover image: set as featured only after explicit user confirmation
        if (!empty($bag['cover']['attachment_id'])) {
            $cover_id = absint($bag['cover']['attachment_id']);
            if ($cover_id && get_post_type($cover_id) === 'attachment') {
                set_post_thumbnail($product_id, $cover_id);
            }
        }

        $this->save_scores($product_id);
        delete_transient($key);

        wp_send_json_success([
            'message' => 'تغییرات اعمال شد',
            'product' => $this->product_card_data($product_id),
        ]);
    }

    public function ajax_create_product(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive'], 400);
        }

        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $price = wc_format_decimal(wp_unslash($_POST['price'] ?? ''));
        $sale = wc_format_decimal(wp_unslash($_POST['sale_price'] ?? ''));
        $sku = sanitize_text_field(wp_unslash($_POST['sku'] ?? ''));
        $stock = sanitize_key($_POST['stock_status'] ?? 'instock');
        $desc = wp_kses_post(wp_unslash($_POST['description'] ?? ''));
        $short = wp_kses_post(wp_unslash($_POST['short_description'] ?? ''));
        $status = sanitize_key($_POST['status'] ?? 'draft');
        if (!in_array($status, ['draft', 'publish', 'private'], true)) {
            $status = 'draft';
        }
        if ($name === '') {
            wp_send_json_error(['message' => 'عنوان محصول الزامی است'], 400);
        }

        $ptype = sanitize_key($_POST['product_type'] ?? 'simple');
        if (!in_array($ptype, ['simple', 'virtual', 'downloadable'], true)) {
            $ptype = 'simple';
        }

        $product = new WC_Product_Simple();
        $product->set_name($name);
        $product->set_status($status);
        $product->set_description($desc);
        $product->set_short_description($short);
        if ($sku !== '') {
            $product->set_sku($sku);
        }
        if ($price !== '') {
            $product->set_regular_price($price);
        }
        if ($sale !== '') {
            $product->set_sale_price($sale);
        }
        $product->set_stock_status($stock === 'outofstock' ? 'outofstock' : 'instock');
        if ($ptype === 'virtual') {
            $product->set_virtual(true);
        } elseif ($ptype === 'downloadable') {
            $product->set_downloadable(true);
            $product->set_virtual(true);
        } elseif ($ptype === 'external') {
            // External requires WC_Product_External — create simple then note
            $product->set_catalog_visibility('visible');
        }

        $manage_stock = !empty($_POST['manage_stock']);
        $stock_qty = isset($_POST['stock_qty']) ? wc_stock_amount(wp_unslash($_POST['stock_qty'])) : null;
        if ($manage_stock) {
            $product->set_manage_stock(true);
            if ($stock_qty !== null && $stock_qty !== '') {
                $product->set_stock_quantity($stock_qty);
                $product->set_stock_status(((float) $stock_qty > 0) ? 'instock' : 'outofstock');
            }
        }

        $id = $product->save();
        if (!$id) {
            wp_send_json_error(['message' => 'خطا در ایجاد محصول'], 500);
        }

        // Categories
        $cats = isset($_POST['categories']) ? wp_unslash($_POST['categories']) : '';
        if (is_string($cats) && $cats !== '') {
            $decoded = json_decode($cats, true);
            if (is_array($decoded)) {
                $term_ids = array_filter(array_map('absint', $decoded));
                if ($term_ids) {
                    wp_set_object_terms($id, $term_ids, 'product_cat');
                }
            }
        }
        // Tags (comma-separated names)
        $tags = sanitize_text_field(wp_unslash($_POST['tags'] ?? ''));
        if ($tags !== '') {
            $tag_names = array_filter(array_map('trim', explode(',', $tags)));
            if ($tag_names) {
                wp_set_object_terms($id, $tag_names, 'product_tag');
            }
        }

        // Optional AI SEO fields (only if explicitly sent)
        $seo_title = sanitize_text_field(wp_unslash($_POST['seo_title'] ?? ''));
        $seo_desc  = sanitize_textarea_field(wp_unslash($_POST['seo_description'] ?? ''));
        $seo_kw    = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $geo_sum   = sanitize_textarea_field(wp_unslash($_POST['geo_summary'] ?? ''));
        if ($seo_title !== '') {
            update_post_meta($id, '_bankai_seo_title', $seo_title);
        }
        if ($seo_desc !== '') {
            update_post_meta($id, '_bankai_seo_description', $seo_desc);
        }
        if ($seo_kw !== '') {
            update_post_meta($id, '_bankai_seo_focus_keyword', $seo_kw);
        }
        if ($geo_sum !== '') {
            update_post_meta($id, '_bankai_seo_geo_html', '<div class="bankai-product-geo"><p>' . esc_html($geo_sum) . '</p></div>');
        }
        $faq_raw = wp_unslash($_POST['faq_json'] ?? '');
        if ($faq_raw !== '') {
            $faq = json_decode($faq_raw, true);
            if (is_array($faq) && $faq) {
                $html = '<div class="bankai-product-faq"><h3>پرسش‌های متداول</h3>';
                foreach ($faq as $row) {
                    $q = esc_html($row['q'] ?? '');
                    $a = esc_html($row['a'] ?? '');
                    if ($q !== '') {
                        $html .= "<details><summary>{$q}</summary><p>{$a}</p></details>";
                    }
                }
                $html .= '</div>';
                update_post_meta($id, '_bankai_seo_faq_html', $html);
            }
        }
        $this->save_scores($id);

        wp_send_json_success([
            'message' => 'محصول ایجاد شد',
            'product' => $this->product_card_data($id),
        ]);
    }

    public function ajax_ai_complete(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        if ($title === '') {
            wp_send_json_error(['message' => 'عنوان را وارد کنید'], 400);
        }

        $data = $this->ai_json(
            'You are an ecommerce product copywriter for Persian/RTL stores. Never invent exact technical specs, brand claims, warranty, materials, or real prices. Mark uncertain content as suggestion. Return JSON only.',
            "Product title: {$title}\nRules: do NOT invent a real price (leave regular_price empty). Do not invent SKU. Write useful Persian marketing copy based only on the title.\nReturn JSON:\n{\"name\":\"improved title\",\"short_description\":\"2-3 sentences\",\"description\":\"HTML paragraphs product intro\",\"regular_price\":\"\",\"sku\":\"\",\"seo_title\":\"max 60 chars\",\"description_meta\":\"meta description 120-155 chars\",\"focus_keyword\":\"main keyword\",\"faq\":[{\"q\":\"...\",\"a\":\"...\"},{\"q\":\"...\",\"a\":\"...\"}],\"geo_summary\":\"2-3 clear paragraphs for AI retrieval\"}"
        );
        // Safety: never auto-fill price from AI guess
        if (isset($data['regular_price'])) {
            $data['regular_price'] = '';
        }

        wp_send_json_success(['draft' => $data]);
    }

    private function ai_json(string $system, string $user): array
    {
        if (!class_exists('Bankai_AI_Studio')) {
            return $this->fallback_from_user($user);
        }
        $ai = Bankai_AI_Studio::instance();
        $provider = $ai->get_default_provider();
        if ($provider === '' || $ai->text_provider_readiness_error($provider) !== '') {
            return $this->fallback_from_user($user);
        }
        $raw = $ai->chat($provider, $system . "\nRespond with valid JSON only. No markdown.", $user, [
            'temperature' => 0.4,
            'max_tokens' => 1200,
        ]);
        $raw = trim($raw);
        if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
            $raw = $m[0];
        }
        $json = json_decode($raw, true);
        return is_array($json) ? $json : $this->fallback_from_user($user);
    }

    private function fallback_from_user(string $user): array
    {
        // Extract product name heuristic
        $name = '';
        if (preg_match('/Product(?: name| title)?:\s*(.+)/i', $user, $m)) {
            $name = trim($m[1]);
            $name = preg_split('/\n/', $name)[0] ?? $name;
        }
        if ($name === '') {
            $name = 'محصول';
        }
        return [
            'seo_title' => mb_substr($name, 0, 55),
            'description' => 'خرید ' . $name . ' با بهترین کیفیت و ارسال سریع.',
            'focus_keyword' => $name,
            'og_title' => $name,
            'og_description' => 'خرید ' . $name,
            'name' => $name,
            'short_description' => 'معرفی کوتاه ' . $name,
            'description_meta' => 'خرید ' . $name . ' با بهترین کیفیت.',
            'faq' => [
                ['q' => $name . ' چیست؟', 'a' => 'توضیح مختصر درباره ' . $name],
                ['q' => 'نحوه ارسال چگونه است؟', 'a' => 'ارسال در کمترین زمان ممکن انجام می‌شود.'],
            ],
            'geo_summary' => $name . ' یک محصول فروشگاهی است که برای نیازهای روزمره طراحی شده.',
            'note' => 'پاسخ پیش‌فرض — موتور AI پیش‌فرض پیکربندی نشده',
        ];
    }


    public function ajax_product_report(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive'], 400);
        }
        $id = absint($_POST['product_id'] ?? 0);
        $product = $id ? wc_get_product($id) : null;
        if (!$product) {
            wp_send_json_error(['message' => 'محصول یافت نشد'], 404);
        }

        $card = $this->product_card_data($id);

        // Sales last 30 days for this product
        $sold_qty = 0;
        $sold_total = 0.0;
        $series = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = gmdate('Y-m-d', strtotime("-{$i} days"));
            $series[$day] = ['qty' => 0, 'total' => 0.0];
        }
        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders([
                'limit' => 200,
                'status' => ['wc-completed', 'wc-processing'],
                'date_created' => '>' . (time() - 30 * DAY_IN_SECONDS),
                'return' => 'objects',
            ]);
            foreach ($orders as $order) {
                foreach ($order->get_items() as $item) {
                    if ((int) $item->get_product_id() !== $id && (int) $item->get_variation_id() !== $id) {
                        continue;
                    }
                    $q = (int) $item->get_quantity();
                    $t = (float) $item->get_total();
                    $sold_qty += $q;
                    $sold_total += $t;
                    $d = $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : '';
                    if ($d && isset($series[$d])) {
                        $series[$d]['qty'] += $q;
                        $series[$d]['total'] += $t;
                    }
                }
            }
        }

        $views = (int) get_post_meta($id, 'bankai_views', true);
        if ($views <= 0) {
            $views = (int) get_post_meta($id, '_bankai_views', true);
        }

        $gateways = [];
        if (function_exists('WC') && WC()->payment_gateways()) {
            foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gw) {
                $gateways[] = $gw->get_title();
            }
        }

        $shipping = [];
        if (class_exists('WC_Shipping_Zones')) {
            foreach (WC_Shipping_Zones::get_zones() as $zone) {
                $methods = [];
                foreach ($zone['shipping_methods'] as $m) {
                    if ($m->is_enabled()) {
                        $methods[] = $m->get_title();
                    }
                }
                $shipping[] = [
                    'zone' => $zone['zone_name'] ?? '',
                    'methods' => $methods,
                ];
            }
        }

        wp_send_json_success([
            'product' => $card,
            'sold_qty_30d' => $sold_qty,
            'sold_total_30d' => round($sold_total, 2),
            'views' => $views,
            'series' => array_values(array_map(static function ($k, $v) {
                return ['day' => $k, 'qty' => (int) $v['qty'], 'total' => round((float) $v['total'], 2)];
            }, array_keys($series), array_values($series))),
            'payment_methods' => $gateways,
            'shipping' => $shipping,
            'manage_stock' => $product->get_manage_stock(),
            'stock_quantity' => $product->get_stock_quantity(),
            'weight' => $product->get_weight(),
            'dimensions' => [
                'length' => $product->get_length(),
                'width' => $product->get_width(),
                'height' => $product->get_height(),
            ],
            'currency' => function_exists('get_woocommerce_currency_symbol')
                ? html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8')
                : '',
        ]);
    }

    public function ajax_gen_cover(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive'], 400);
        }
        $id = absint($_POST['product_id'] ?? 0);
        $product = $id ? wc_get_product($id) : null;
        if (!$product) {
            wp_send_json_error(['message' => 'محصول یافت نشد'], 404);
        }
        $apply = !empty($_POST['apply']);
        $name = $product->get_name();
        $prompt = sanitize_text_field(wp_unslash($_POST['prompt'] ?? ''));
        if ($prompt === '') {
            $prompt = "Professional ecommerce product photography of \"{$name}\", studio lighting, clean white background, catalog style, no text, no watermark";
        }

        if (!class_exists('Bankai_AI_Studio')) {
            wp_send_json_error(['message' => 'استودیو AI در دسترس نیست', 'prompt' => $prompt]);
        }
        $ai = Bankai_AI_Studio::instance();
        $provider = method_exists($ai, 'get_default_image_provider') ? $ai->get_default_image_provider() : '';
        $ready_err = method_exists($ai, 'image_provider_readiness_error') ? $ai->image_provider_readiness_error($provider) : '';
        if ($ready_err) {
            wp_send_json_error(['message' => $ready_err, 'prompt' => $prompt]);
        }
        if (!method_exists($ai, 'generate_image_bytes')) {
            wp_send_json_error(['message' => 'موتور تولید تصویر پیکربندی نشده', 'prompt' => $prompt]);
        }
        try {
            $result = $ai->generate_image_bytes($provider, $prompt, ['width' => 1024, 'height' => 1024]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage(), 'prompt' => $prompt]);
        }
        $bytes = is_array($result) ? ($result['bytes'] ?? $result['data'] ?? '') : '';
        $mime = is_array($result) ? ($result['mime'] ?? 'image/png') : 'image/png';
        if ($bytes === '') {
            wp_send_json_error(['message' => 'خروجی تصویر خالی بود', 'prompt' => $prompt]);
        }

        $upload = wp_upload_bits('bankai-cover-' . $id . '-' . time() . '.png', null, $bytes);
        if (!empty($upload['error'])) {
            wp_send_json_error(['message' => $upload['error'], 'prompt' => $prompt]);
        }
        $filetype = wp_check_filetype($upload['file'], null);
        $attachment = [
            'post_mime_type' => $filetype['type'] ?: $mime,
            'post_title' => 'Cover: ' . $name,
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $upload['file'], $id);
        if (is_wp_error($attach_id) || !$attach_id) {
            wp_send_json_error(['message' => 'ذخیره پیوست ناموفق', 'prompt' => $prompt]);
        }
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $meta = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $meta);
        update_post_meta($attach_id, '_wp_attachment_image_alt', $name);

        if ($apply) {
            set_post_thumbnail($id, $attach_id);
        }

        wp_send_json_success([
            'attachment_id' => (int) $attach_id,
            'url' => wp_get_attachment_url($attach_id),
            'applied' => (bool) $apply,
            'prompt' => $prompt,
            'product' => $this->product_card_data($id),
        ]);
    }

}
