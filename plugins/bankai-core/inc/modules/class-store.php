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
        add_action('wp_ajax_bankai_store_wizard_step', [$this, 'ajax_wizard_step']);
        add_action('wp_ajax_bankai_store_wizard_apply', [$this, 'ajax_wizard_apply']);
        add_action('wp_ajax_bankai_store_create_product', [$this, 'ajax_create_product']);
        add_action('wp_ajax_bankai_store_ai_complete', [$this, 'ajax_ai_complete']);
    }

    public static function woo_active(): bool
    {
        return class_exists('WooCommerce') && class_exists('WC_Product');
    }

    public function ajax_stats(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (!self::woo_active()) {
            wp_send_json_error(['message' => 'WooCommerce inactive', 'woo' => false], 400);
        }

        $counts = (array) wp_count_posts('product');
        $published = (int) ($counts['publish'] ?? 0);
        $draft = (int) ($counts['draft'] ?? 0);

        $instock = 0;
        $outofstock = 0;
        $lowstock = 0;
        $onsale = 0;

        // Prefer direct meta counts when possible to avoid loading 500 products
        $instock = (int) (new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [['key' => '_stock_status', 'value' => 'instock']],
        ]))->found_posts;

        $outofstock = (int) (new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [['key' => '_stock_status', 'value' => 'outofstock']],
        ]))->found_posts;

        // Low stock (managed stock with qty <= threshold)
        $low_threshold = function_exists('get_option') ? (int) get_option('woocommerce_notify_low_stock_amount', 2) : 2;
        $lowstock = (int) (new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_manage_stock', 'value' => 'yes'],
                ['key' => '_stock', 'value' => $low_threshold, 'compare' => '<=', 'type' => 'NUMERIC'],
                ['key' => '_stock_status', 'value' => 'instock'],
            ],
        ]))->found_posts;

        // On sale products
        $onsale = (int) (new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_sale_price', 'value' => '', 'compare' => '!='],
                ['key' => '_sale_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC'],
            ],
        ]))->found_posts;

        // Sales last 30 days
        $sales_total = 0.0;
        $orders_count = 0;
        $series = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = gmdate('Y-m-d', strtotime("-{$i} days"));
            $series[$day] = 0.0;
        }

        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders([
                'limit' => 300,
                'status' => ['wc-completed', 'wc-processing'],
                'date_created' => '>' . (time() - 30 * DAY_IN_SECONDS),
                'return' => 'objects',
            ]);
            foreach ($orders as $order) {
                $orders_count++;
                $total = (float) $order->get_total();
                $sales_total += $total;
                $d = $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : '';
                if ($d && isset($series[$d])) {
                    $series[$d] += $total;
                }
            }
        }

        $avg_order = $orders_count > 0 ? round($sales_total / $orders_count, 2) : 0;

        wp_send_json_success([
            'woo' => true,
            'products' => $published,
            'drafts' => $draft,
            'in_stock' => $instock,
            'out_of_stock' => $outofstock,
            'low_stock' => $lowstock,
            'on_sale' => $onsale,
            'orders_30d' => $orders_count,
            'sales_30d' => round($sales_total, 2),
            'avg_order' => $avg_order,
            'currency' => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '',
            'series' => array_values(array_map(static function ($k, $v) {
                return ['day' => $k, 'total' => round((float) $v, 2)];
            }, array_keys($series), array_values($series))),
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
            'price_html' => $p ? wp_strip_all_tags($p->get_price_html()) : '',
            'stock_status' => $p ? $p->get_stock_status() : 'outofstock',
            'stock_qty' => $p ? $p->get_stock_quantity() : null,
            'in_stock' => $p ? $p->is_in_stock() : false,
            'sku' => $p ? (string) $p->get_sku() : '',
            'seo' => $scores['seo'],
            'aeo' => $scores['aeo'],
            'geo' => $scores['geo'],
            // Current SEO field values for wizard comparison
            'fields' => [
                'seo_title'       => (string) get_post_meta($id, '_bankai_seo_title', true),
                'seo_description' => (string) get_post_meta($id, '_bankai_seo_description', true),
                'focus_keyword'   => (string) get_post_meta($id, '_bankai_seo_focus_keyword', true),
                'schema_json'     => (string) get_post_meta($id, '_bankai_seo_schema', true),
                'faq_html'        => (string) get_post_meta($id, '_bankai_seo_faq_html', true),
                'geo_html'        => (string) get_post_meta($id, '_bankai_seo_geo_html', true),
                'image_alt'       => $current_alt,
            ],
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
        if (!empty($meta['seo_title'])) {
            update_post_meta($product_id, '_bankai_seo_title', sanitize_text_field($meta['seo_title']));
        }
        if (!empty($meta['description'])) {
            update_post_meta($product_id, '_bankai_seo_description', sanitize_textarea_field($meta['description']));
        }
        if (!empty($meta['focus_keyword'])) {
            update_post_meta($product_id, '_bankai_seo_focus_keyword', sanitize_text_field($meta['focus_keyword']));
            update_post_meta($product_id, '_bankai_seo_keyword', sanitize_text_field($meta['focus_keyword']));
        }
        if (!empty($meta['og_title'])) {
            update_post_meta($product_id, '_bankai_seo_og_title', sanitize_text_field($meta['og_title']));
        }
        if (!empty($meta['og_description'])) {
            update_post_meta($product_id, '_bankai_seo_og_description', sanitize_textarea_field($meta['og_description']));
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
        }
        $id = $product->save();
        if (!$id) {
            wp_send_json_error(['message' => 'خطا در ایجاد محصول'], 500);
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
}
