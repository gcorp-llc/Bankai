<?php
/**
 * One-click demo content installer (self-contained, no external XML)
 *
 * Demos live in the theme (themes/bankai-theme) — not in the Bankai Core plugin.
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * AJAX: import demo (legacy hook — class-theme-admin also registers its own).
 */
function bankai_ajax_import_demo(): void {
    check_ajax_referer('bankai_theme_admin', 'nonce');
    if (! current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-theme')], 403);
    }

    $demo_id = isset($_POST['demo']) ? sanitize_key(wp_unslash($_POST['demo'])) : 'default';
    $log     = bankai_theme_run_demo_import($demo_id);
    update_option('bankai_theme_demo_imported', 1, false);
    update_option('bankai_theme_active_demo', $demo_id, false);

    wp_send_json_success([
        'message' => __('محتوای نمونه با موفقیت نصب شد.', 'bankai-theme'),
        'demo'    => $demo_id,
        'log'     => $log,
    ]);
}
// Avoid double-register if admin class already hooks the same action.
if (! has_action('wp_ajax_bankai_theme_import_demo')) {
    add_action('wp_ajax_bankai_theme_import_demo', 'bankai_ajax_import_demo');
}

/**
 * Run demo import by id.
 *
 * @param string $demo_id Demo slug (shop, shop-astra-test, default, …).
 * @return string[] Log lines.
 */
function bankai_theme_run_demo_import(string $demo_id = 'default'): array {
    $demo_id = $demo_id !== '' ? $demo_id : 'default';
    $log     = [];

    // Shared theme mods
    set_theme_mod('color_primary', '#0969DA');
    set_theme_mod('sticky_header', true);
    set_theme_mod('header_search', true);
    set_theme_mod('show_theme_toggle', true);
    set_theme_mod('footer_columns', 4);
    set_theme_mod('content_layout', 'content');
    $log[] = 'theme_mods';

    if (in_array($demo_id, ['shop', 'shop-astra-test'], true)) {
        $log = array_merge($log, bankai_theme_import_shop_demo($demo_id));
    } else {
        $log = array_merge($log, bankai_theme_import_default_demo($demo_id));
    }

    set_theme_mod('bankai_last_demo', $demo_id);
    update_option('bankai_theme_active_demo', $demo_id, false);
    flush_rewrite_rules(false);
    $log[] = 'done:' . $demo_id;
    return $log;
}

/**
 * Default corporate/blog-style demo (existing behaviour).
 *
 * @return string[]
 */
function bankai_theme_import_default_demo(string $demo_id): array {
    $log = [];

    set_theme_mod('header_cta_text', __('شروع رایگان', 'bankai-theme'));
    set_theme_mod('header_cta_url', home_url('/contact/'));
    set_theme_mod('topbar_text', __('قالب فوق‌سریع Bankai — بهینه برای سئو و سرعت', 'bankai-theme'));

    $samples = [
        [
            'title'   => __('۵ نکته برای سرعت سایت وردپرس', 'bankai-theme'),
            'content' => '<!-- wp:paragraph --><p>' . esc_html__('در این مقاله با روش‌های کاهش حجم CSS و JS آشنا می‌شوید.', 'bankai-theme') . '</p><!-- /wp:paragraph -->',
        ],
        [
            'title'   => __('چرا Core Web Vitals مهم است؟', 'bankai-theme'),
            'content' => '<!-- wp:paragraph --><p>' . esc_html__('گوگل تجربه کاربری را در رتبه‌بندی جدی می‌گیرد؛ LCP و CLS را جدی بگیرید.', 'bankai-theme') . '</p><!-- /wp:paragraph -->',
        ],
        [
            'title'   => __('شروع کار با قالب Bankai', 'bankai-theme'),
            'content' => '<!-- wp:paragraph --><p>' . esc_html__('منو را وصل کنید، لوگو بگذارید و از سفارشی‌ساز رنگ‌ها را تنظیم کنید.', 'bankai-theme') . '</p><!-- /wp:paragraph -->',
        ],
    ];
    foreach ($samples as $s) {
        $existing = get_page_by_title($s['title'], OBJECT, 'post');
        if (! $existing) {
            wp_insert_post([
                'post_title'   => $s['title'],
                'post_content' => $s['content'],
                'post_status'  => 'publish',
                'post_type'    => 'post',
            ]);
        }
    }
    $log[] = 'sample_posts';

    $home_id = bankai_theme_upsert_page('home-bankai', __('خانه', 'bankai-theme'), bankai_theme_markup_default_home());
    $blog_id = bankai_theme_upsert_page('blog', __('وبلاگ', 'bankai-theme'), '<!-- wp:paragraph --><p>' . esc_html__('آرشیو مطالب', 'bankai-theme') . '</p><!-- /wp:paragraph -->');
    $about_id = bankai_theme_upsert_page('about', __('درباره ما', 'bankai-theme'), '<!-- wp:paragraph --><p>' . esc_html__('درباره تیم Bankai', 'bankai-theme') . '</p><!-- /wp:paragraph -->');
    $contact_id = bankai_theme_upsert_page('contact', __('تماس', 'bankai-theme'), '<!-- wp:paragraph --><p>' . esc_html__('با ما در تماس باشید.', 'bankai-theme') . '</p><!-- /wp:paragraph -->');

    if ($home_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }
    if ($blog_id) {
        update_option('page_for_posts', $blog_id);
    }
    $log[] = 'pages';

    bankai_theme_ensure_menu('Bankai Primary', array_filter([
        $home_id => __('خانه', 'bankai-theme'),
        $blog_id => __('وبلاگ', 'bankai-theme'),
        $about_id => __('درباره', 'bankai-theme'),
        $contact_id => __('تماس', 'bankai-theme'),
    ]));
    $log[] = 'menu';

    return $log;
}

/**
 * Shop demo inspired by marketplace Astra Pro shop previews (original Bankai markup).
 * Reference listing only: https://rtlr.ir/287550 — not a file copy of Astra.
 *
 * @return string[]
 */
function bankai_theme_import_shop_demo(string $demo_id): array {
    $log = [];

    set_theme_mod('header_cta_text', __('مشاهده فروشگاه', 'bankai-theme'));
    set_theme_mod('header_cta_url', home_url('/shop-catalog/'));
    set_theme_mod('topbar_text', __('ارسال رایگان برای سفارش‌های بالای ۵۰۰ هزار تومان', 'bankai-theme'));
    set_theme_mod('color_primary', '#2563eb');

    $home_id = bankai_theme_upsert_page(
        'shop-home',
        __('خانه فروشگاه', 'bankai-theme'),
        bankai_theme_markup_shop_home()
    );
    $shop_id = bankai_theme_upsert_page(
        'shop-catalog',
        __('فروشگاه', 'bankai-theme'),
        bankai_theme_markup_simple(
            __('فروشگاه', 'bankai-theme'),
            __('لیست محصولات و فیلتر دسته — اسکلت دموی فروشگاهی Bankai Theme.', 'bankai-theme')
        )
    );
    $about_id = bankai_theme_upsert_page(
        'shop-about',
        __('درباره فروشگاه', 'bankai-theme'),
        bankai_theme_markup_simple(
            __('درباره ما', 'bankai-theme'),
            __('این صفحه بخشی از دموی تست فروشگاهی قالب Bankai است.', 'bankai-theme')
        )
    );
    $contact_id = bankai_theme_upsert_page(
        'shop-contact',
        __('تماس فروشگاه', 'bankai-theme'),
        bankai_theme_markup_simple(
            __('تماس با ما', 'bankai-theme'),
            __('پشتیبانی، آدرس و فرم تماس — دموی تست.', 'bankai-theme')
        )
    );

    foreach ([$home_id, $shop_id, $about_id, $contact_id] as $pid) {
        if ($pid) {
            update_post_meta($pid, '_bankai_theme_demo', $demo_id);
        }
    }

    if ($home_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }
    $log[] = 'shop_pages';

    bankai_theme_ensure_menu('Bankai Shop', array_filter([
        $home_id    => __('خانه', 'bankai-theme'),
        $shop_id    => __('فروشگاه', 'bankai-theme'),
        $about_id   => __('درباره', 'bankai-theme'),
        $contact_id => __('تماس', 'bankai-theme'),
    ]));
    $log[] = 'shop_menu';

    update_option('bankai_theme_last_shop_import', [
        'demo'    => $demo_id,
        'at'      => time(),
        'home_id' => $home_id,
        'shop_id' => $shop_id,
    ], false);

    return $log;
}

/**
 * @param array<int,string> $items page_id => label
 */
function bankai_theme_ensure_menu(string $menu_name, array $items): void {
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu($menu_name);
    if (! $menu_id || is_wp_error($menu_id)) {
        return;
    }
    $existing = wp_get_nav_menu_items($menu_id);
    if (empty($existing)) {
        foreach ($items as $pid => $label) {
            if (! $pid) {
                continue;
            }
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => $label,
                'menu-item-object'    => 'page',
                'menu-item-object-id' => (int) $pid,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    if (! is_array($locations)) {
        $locations = [];
    }
    $locations['primary'] = $menu_id;
    $locations['footer']  = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

function bankai_theme_upsert_page(string $slug, string $title, string $content): int {
    $existing = get_page_by_path($slug);
    if ($existing instanceof WP_Post) {
        wp_update_post([
            'ID'           => $existing->ID,
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
        ]);
        return (int) $existing->ID;
    }
    $id = wp_insert_post([
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);
    return is_wp_error($id) ? 0 : (int) $id;
}

function bankai_theme_markup_simple(string $heading, string $text): string {
    $h = esc_html($heading);
    $p = esc_html($text);
    return "<!-- wp:heading {\"textAlign\":\"center\"} -->\n<h2 class=\"wp-block-heading has-text-align-center\">{$h}</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">{$p}</p>\n<!-- /wp:paragraph -->";
}

function bankai_theme_markup_default_home(): string {
    return '<!-- wp:heading {\"textAlign\":\"center\"} -->\n<h1 class=\"wp-block-heading has-text-align-center\">' .
        esc_html__('به Bankai Theme خوش آمدید', 'bankai-theme') .
        '</h1>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">' .
        esc_html__('قالب سبک و سریع برای سایت شرکتی و محتوایی.', 'bankai-theme') .
        '</p>\n<!-- /wp:paragraph -->';
}

function bankai_theme_markup_shop_home(): string {
    return <<<'HTML'
<!-- wp:group {"align":"full","style":{"color":{"background":"#0f172a"},"spacing":{"padding":{"top":"64px","bottom":"64px"}}}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#0f172a;padding-top:64px;padding-bottom:64px">
<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#ffffff"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="color:#ffffff">فروشگاه مدرن Bankai</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#cbd5e1"}}} -->
<p class="has-text-align-center" style="color:#cbd5e1">دموی تست فروشگاهی داخل قالب Bankai Theme — الهام از چیدمان دموهای فروشگاهی بازار (نه کپی فایل قالب دیگر).</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop-catalog/">مشاهده محصولات</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">دسته‌بندی‌های محبوب</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:group {"style":{"color":{"background":"#f8fafc"},"spacing":{"padding":{"top":"24px","bottom":"24px"}},"border":{"radius":"12px"}}} -->
<div class="wp-block-group has-background" style="border-radius:12px;background-color:#f8fafc;padding-top:24px;padding-bottom:24px"><!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">پوشاک</h3>
<!-- /wp:heading --><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">کالکشن فصل</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:group {"style":{"color":{"background":"#f8fafc"},"spacing":{"padding":{"top":"24px","bottom":"24px"}},"border":{"radius":"12px"}}} -->
<div class="wp-block-group has-background" style="border-radius:12px;background-color:#f8fafc;padding-top:24px;padding-bottom:24px"><!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">الکترونیک</h3>
<!-- /wp:heading --><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">گجت و لوازم</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:group {"style":{"color":{"background":"#f8fafc"},"spacing":{"padding":{"top":"24px","bottom":"24px"}},"border":{"radius":"12px"}}} -->
<div class="wp-block-group has-background" style="border-radius:12px;background-color:#f8fafc;padding-top:24px;padding-bottom:24px"><!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">خانه و زندگی</h3>
<!-- /wp:heading --><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">دکوراسیون</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div><!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">محصولات ویژه</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">اسکلت UI — در نسخه کامل به ووکامرس وصل می‌شود.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:group {"style":{"border":{"width":"1px","color":"#e2e8f0","radius":"12px"},"spacing":{"padding":{"top":"16px","bottom":"16px","left":"16px","right":"16px"}}}} -->
<div class="wp-block-group" style="border-color:#e2e8f0;border-width:1px;border-radius:12px;padding:16px"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">محصول نمونه ۱</h4>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>۱٬۲۹۰٬۰۰۰ تومان</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:group {"style":{"border":{"width":"1px","color":"#e2e8f0","radius":"12px"},"spacing":{"padding":{"top":"16px","bottom":"16px","left":"16px","right":"16px"}}}} -->
<div class="wp-block-group" style="border-color:#e2e8f0;border-width:1px;border-radius:12px;padding:16px"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">محصول نمونه ۲</h4>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>۸۹۰٬۰۰۰ تومان</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:group {"style":{"border":{"width":"1px","color":"#e2e8f0","radius":"12px"},"spacing":{"padding":{"top":"16px","bottom":"16px","left":"16px","right":"16px"}}}} -->
<div class="wp-block-group" style="border-color:#e2e8f0;border-width:1px;border-radius:12px;padding:16px"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">محصول نمونه ۳</h4>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>۲٬۱۵۰٬۰۰۰ تومان</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div><!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:group {"align":"full","style":{"color":{"background":"#eff6ff"},"spacing":{"padding":{"top":"40px","bottom":"40px"}}}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#eff6ff;padding-top:40px;padding-bottom:40px">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">اپلیکیشن فروشگاهی</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">بنر معرفی اپ — بخش تست دموی فروشگاهی قالب.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"color":{"background":"#111827","text":"#e5e7eb"},"spacing":{"padding":{"top":"32px","bottom":"32px"}}}} -->
<div class="wp-block-group alignfull has-background has-text-color" style="color:#e5e7eb;background-color:#111827;padding-top:32px;padding-bottom:32px">
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">© Bankai Theme Shop Demo — پشتیبانی | قوانین | تماس</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;
}
