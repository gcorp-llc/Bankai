<?php
/**
 * One-click demo content installer (self-contained, no external XML)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * AJAX: import demo.
 */
function bankai_ajax_import_demo(): void {
    check_ajax_referer('bankai_theme_admin', 'nonce');
    if (! current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-theme')], 403);
    }
    if (get_option('bankai_theme_demo_imported')) {
        wp_send_json_error(['message' => __('دمو قبلاً نصب شده است.', 'bankai-theme')]);
    }

    $log = bankai_theme_run_demo_import();
    update_option('bankai_theme_demo_imported', 1, false);
    wp_send_json_success([
        'message' => __('محتوای نمونه با موفقیت نصب شد.', 'bankai-theme'),
        'log'     => $log,
    ]);
}
add_action('wp_ajax_bankai_theme_import_demo', 'bankai_ajax_import_demo');

/**
 * Run demo import.
 *
 * @return string[] Log lines.
 */
function bankai_theme_run_demo_import(): array {
    $log = [];

    // Theme mods
    set_theme_mod('color_primary', '#0969DA');
    set_theme_mod('sticky_header', true);
    set_theme_mod('header_search', true);
    set_theme_mod('show_theme_toggle', true);
    set_theme_mod('header_cta_text', __('شروع رایگان', 'bankai-theme'));
    set_theme_mod('header_cta_url', home_url('/contact/'));
    set_theme_mod('topbar_text', __('قالب فوق‌سریع Bankai — بهینه برای سئو و سرعت', 'bankai-theme'));
    set_theme_mod('footer_columns', 4);
    set_theme_mod('footer_social_telegram', 'https://t.me/');
    set_theme_mod('footer_social_instagram', 'https://instagram.com/');
    set_theme_mod('content_layout', 'content');
    $log[] = 'theme_mods';

    // Sample posts
    $post_ids = [];
    $samples  = [
        [
            'title'   => __('۵ نکته برای سرعت سایت وردپرس', 'bankai-theme'),
            'content' => "<!-- wp:paragraph --><p>" . __('در این مقاله با روش‌های کاهش حجم CSS و JS آشنا می‌شوید.', 'bankai-theme') . "</p><!-- /wp:paragraph -->",
        ],
        [
            'title'   => __('چرا Core Web Vitals مهم است؟', 'bankai-theme'),
            'content' => "<!-- wp:paragraph --><p>" . __('گوگل تجربه کاربری را در رتبه‌بندی جدی می‌گیرد؛ LCP و CLS را جدی بگیرید.', 'bankai-theme') . "</p><!-- /wp:paragraph -->",
        ],
        [
            'title'   => __('شروع کار با قالب Bankai', 'bankai-theme'),
            'content' => "<!-- wp:paragraph --><p>" . __('منو را وصل کنید، لوگو بگذارید و از سفارشی‌ساز رنگ‌ها را تنظیم کنید.', 'bankai-theme') . "</p><!-- /wp:paragraph -->",
        ],
    ];
    foreach ($samples as $s) {
        $id = wp_insert_post([
            'post_title'   => $s['title'],
            'post_content' => $s['content'],
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_author'  => get_current_user_id() ?: 1,
        ], true);
        if (! is_wp_error($id)) {
            $post_ids[] = $id;
        }
    }
    $log[] = 'posts:' . count($post_ids);

    // Home page with blocks
    $home_content = '<!-- wp:bankai/hero {"title":"به Bankai خوش آمدید","subtitle":"قالب سبک و پرسرعت برای وردپرس فارسی","ctaText":"مشاهده نوشته‌ها","ctaUrl":"' . esc_url(home_url('/blog/')) . '"} /-->'
        . '<!-- wp:bankai/features /-->'
        . '<!-- wp:bankai/posts {"count":3} /-->'
        . '<!-- wp:bankai/cta {"title":"آماده ساخت سایت هستید؟","btnText":"تماس","btnUrl":"' . esc_url(home_url('/contact/')) . '"} /-->';

    $home_id = wp_insert_post([
        'post_title'   => __('خانه', 'bankai-theme'),
        'post_name'    => 'home',
        'post_content' => $home_content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => get_current_user_id() ?: 1,
    ], true);

    $blog_id = wp_insert_post([
        'post_title'   => __('وبلاگ', 'bankai-theme'),
        'post_name'    => 'blog',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => get_current_user_id() ?: 1,
    ], true);

    $about_id = wp_insert_post([
        'post_title'   => __('درباره ما', 'bankai-theme'),
        'post_name'    => 'about',
        'post_content' => '<!-- wp:paragraph --><p>' . __('ما Bankai را برای سرعت و سادگی ساختیم.', 'bankai-theme') . '</p><!-- /wp:paragraph -->',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => get_current_user_id() ?: 1,
    ], true);

    $contact_id = wp_insert_post([
        'post_title'   => __('تماس با ما', 'bankai-theme'),
        'post_name'    => 'contact',
        'post_content' => '<!-- wp:paragraph --><p>' . __('از فرم تماس یا شبکه‌های اجتماعی پیام دهید.', 'bankai-theme') . '</p><!-- /wp:paragraph -->',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => get_current_user_id() ?: 1,
    ], true);

    if (! is_wp_error($home_id) && ! is_wp_error($blog_id)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
        update_option('page_for_posts', $blog_id);
        $log[] = 'front_pages';
    }

    // Menu
    $menu_name = 'Bankai Primary';
    $menu_id   = wp_create_nav_menu($menu_name);
    if (! is_wp_error($menu_id)) {
        $items = array_filter([
            $home_id,
            $blog_id,
            $about_id,
            $contact_id,
        ], static fn($id) => $id && ! is_wp_error($id));
        foreach ($items as $pid) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => get_the_title($pid),
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $pid,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
        $locations            = get_theme_mod('nav_menu_locations', []);
        $locations['primary'] = $menu_id;
        $locations['footer']  = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
        $log[] = 'menu';
    }

    // Footer widgets text
    $sidebars = get_option('sidebars_widgets', []);
    if (! is_array($sidebars)) {
        $sidebars = [];
    }
    for ($i = 1; $i <= 4; $i++) {
        $widget_id = "text-bankai-footer-$i";
        // Use native text widget instances
    }
    // Register simple text via option
    $text_widgets = get_option('widget_text', []);
    if (! is_array($text_widgets)) {
        $text_widgets = [];
    }
    $base = max(array_keys(array_filter($text_widgets, 'is_array')) ?: [1]) + 1;
    $footer_texts = [
        ['title' => __('درباره', 'bankai-theme'), 'text' => __('Bankai Theme — سریع و مینیمال.', 'bankai-theme')],
        ['title' => __('لینک‌ها', 'bankai-theme'), 'text' => __('خانه · وبلاگ · تماس', 'bankai-theme')],
        ['title' => __('پشتیبانی', 'bankai-theme'), 'text' => __('docs@example.com', 'bankai-theme')],
        ['title' => __('خبرنامه', 'bankai-theme'), 'text' => __('به‌زودی…', 'bankai-theme')],
    ];
    $footer_widget_ids = [];
    foreach ($footer_texts as $i => $ft) {
        $idx = $base + $i;
        $text_widgets[$idx] = [
            'title'  => $ft['title'],
            'text'   => $ft['text'],
            'filter' => true,
        ];
        $footer_widget_ids[] = 'text-' . $idx;
        $sidebars['footer-' . ($i + 1)] = ['text-' . $idx];
    }
    $text_widgets['_multiwidget'] = 1;
    update_option('widget_text', $text_widgets);
    update_option('sidebars_widgets', $sidebars);
    $log[] = 'footer_widgets';

    flush_rewrite_rules(false);
    $log[] = 'done';
    return $log;
}
