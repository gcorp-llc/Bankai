<?php
/**
 * Frontend assets — minimal, performance-first
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Enqueue front styles/scripts.
 */
function bankai_theme_scripts(): void {
    $ver = BANKAI_THEME_VERSION;

    // Main CSS only (variables + components). style.css is WP header.
    wp_enqueue_style(
        'bankai-theme-main',
        BANKAI_THEME_URI . '/assets/css/main.css',
        [],
        $ver
    );

    // Google font from طرح کلی preset (skip Tahoma system stack).
    if (function_exists('bankai_theme_font_catalog')) {
        $preset = (string) bankai_theme_mod('font_preset', 'vazirmatn');
        $catalog = bankai_theme_font_catalog();
        if (! empty($catalog[$preset]['google'])) {
            $g = $catalog[$preset]['google'];
            $fonts_url = 'https://fonts.googleapis.com/css2?family=' . $g . '&display=swap';
            wp_enqueue_style('bankai-theme-fonts', $fonts_url, [], null);
            add_filter('wp_resource_hints', static function ($urls, $relation) {
                if ($relation === 'preconnect') {
                    $urls[] = 'https://fonts.googleapis.com';
                    $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
                }
                return $urls;
            }, 10, 2);
        }
    }

    // Tiny vanilla JS (mobile menu) — defer
    wp_enqueue_script(
        'bankai-theme',
        BANKAI_THEME_URI . '/assets/js/theme.js',
        [],
        $ver,
        ['strategy' => 'defer', 'in_footer' => true]
    );
    wp_localize_script('bankai-theme', 'bankaiTheme', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'i18n' => [
            'loading' => __('در حال بارگذاری…', 'bankai-theme'),
            'end' => __('همه مطالب نمایش داده شد', 'bankai-theme'),
        ],
    ]);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'bankai_theme_scripts', 20);

/**
 * Inline critical CSS variables from Customizer (no extra HTTP).
 */
function bankai_theme_inline_css(): void {
    // Prefer design tokens from طرح کلی when available
    $design = function_exists('bankai_theme_get_design') ? bankai_theme_get_design() : [];
    $dc = is_array($design['colors'] ?? null) ? $design['colors'] : [];
    $dr = is_array($design['radius'] ?? null) ? $design['radius'] : [];

    $primary   = sanitize_hex_color($dc['primary'] ?? bankai_theme_mod('color_primary', '#0969DA')) ?: '#0969DA';
    $text      = sanitize_hex_color($dc['base_content'] ?? bankai_theme_mod('color_text', '#1F2328')) ?: '#1F2328';
    $bg        = sanitize_hex_color($dc['base'] ?? bankai_theme_mod('color_bg', '#FFFFFF')) ?: '#FFFFFF';
    $container = absint(bankai_theme_mod('container_width', 1280));
    $radius_raw = $dr['box'] ?? '';
    if ($radius_raw !== '' && is_string($radius_raw)) {
        $radius_css = $radius_raw;
    } else {
        $radius_css = absint(bankai_theme_mod('border_radius', 10)) . 'px';
    }
    $fs        = absint(bankai_theme_mod('font_body_size', bankai_theme_mod('font_size_base', 16)));
    $lh        = absint(bankai_theme_mod('line_height', 165));
    $hw        = sanitize_text_field((string) bankai_theme_mod('heading_weight', '700'));

    $css = ":root{--bankai-primary:{$primary};--bankai-text:{$text};--bankai-bg:{$bg};--bankai-container:{$container}px;--bankai-radius:{$radius_css};--bankai-fs:{$fs}px;--bankai-lh:" . ($lh / 100) . ";--bankai-hw:{$hw};}";
    if (function_exists('bankai_theme_custom_fonts_css')) {
        $css .= bankai_theme_custom_fonts_css();
    }
    if (function_exists('bankai_theme_design_css')) {
        $css .= bankai_theme_design_css();
    }
    $fb = sanitize_text_field((string) bankai_theme_mod('font_body', 'Vazirmatn, Tahoma, sans-serif'));
    $fh = sanitize_text_field((string) bankai_theme_mod('font_heading', $fb));
    $fs = absint(bankai_theme_mod('font_body_size', 16));
    $fw = sanitize_text_field((string) bankai_theme_mod('font_body_weight', '400'));
    $h1 = absint(bankai_theme_mod('font_h1_size', 32));
    $h2 = absint(bankai_theme_mod('font_h2_size', 24));
    $css .= ":root{--bankai-font-body:{$fb};--bankai-font-heading:{$fh};--bankai-fs:{$fs}px;--bankai-fw:{$fw};--bankai-h1:{$h1}px;--bankai-h2:{$h2}px;}";
    $nav = sanitize_text_field((string) bankai_theme_mod('font_nav', $fb));
    $nav_s = absint(bankai_theme_mod('font_nav_size', 15));
    $title_f = sanitize_text_field((string) bankai_theme_mod('font_title', $fh));
    $title_s = absint(bankai_theme_mod('font_title_size', 28));
    $h3 = absint(bankai_theme_mod('font_h3_size', 20));
    $h4 = absint(bankai_theme_mod('font_h4_size', 18));
    $css .= ":root{--bankai-font-nav:{$nav};--bankai-nav-fs:{$nav_s}px;--bankai-font-title:{$title_f};--bankai-title-fs:{$title_s}px;--bankai-h3:{$h3}px;--bankai-h4:{$h4}px;}";
    $css .= "body{font-family:var(--bankai-font-body);font-size:var(--bankai-fs);font-weight:var(--bankai-fw);}h1,h2,h3,h4,h5,h6{font-family:var(--bankai-font-heading);}h1{font-size:var(--bankai-h1);}h2{font-size:var(--bankai-h2);}h3{font-size:var(--bankai-h3);}h4{font-size:var(--bankai-h4);}";
    
    $font_color_mode = (string) bankai_theme_mod('font_color_mode', 'inherit');
    if ($font_color_mode === 'custom') {
        $bc = sanitize_hex_color((string) bankai_theme_mod('font_body_color', '#1E293B')) ?: '#1E293B';
        $nc = sanitize_hex_color((string) bankai_theme_mod('font_nav_color', '#334155')) ?: '#334155';
        $nch = sanitize_hex_color((string) bankai_theme_mod('font_nav_color_hover', '#5B8DEF')) ?: '#5B8DEF';
        $tc = sanitize_hex_color((string) bankai_theme_mod('font_title_color', '#0F172A')) ?: '#0F172A';
        $h1c = sanitize_hex_color((string) bankai_theme_mod('font_h1_color', '#0F172A')) ?: '#0F172A';
        $h2c = sanitize_hex_color((string) bankai_theme_mod('font_h2_color', '#0F172A')) ?: '#0F172A';
        $css .= "body{color:{$bc};}.bankai-menu a,.bankai-nav a{color:{$nc};}.bankai-menu a:hover,.bankai-nav a:hover{color:{$nch};}.bankai-single__title,.bankai-page__title,.bankai-archive-title{color:{$tc};}h1{color:{$h1c};}h2{color:{$h2c};}";
    } else {
        // Inherit text colors from design tokens (طرح کلی)
        $css .= 'body{color:var(--bankai-text,var(--bc,#1A1A1A));}.bankai-menu a,.bankai-nav a{color:var(--bankai-text,var(--bc,#1A1A1A));}.bankai-menu a:hover,.bankai-nav a:hover{color:var(--bankai-primary,var(--p,#5C6B7A));}h1,h2,h3,h4,.bankai-single__title,.bankai-page__title{color:var(--bankai-text,var(--bc,#1A1A1A));}';
    }
    
    $fbtn = sanitize_text_field((string) bankai_theme_mod('font_button', ''));
    $fbtns = absint(bankai_theme_mod('font_button_size', 15));
    $fbtnc = sanitize_hex_color((string) bankai_theme_mod('font_button_color', '')) ?: '';
    $flist = sanitize_text_field((string) bankai_theme_mod('font_list', ''));
    $flists = absint(bankai_theme_mod('font_list_size', 15));
    $flistc = sanitize_hex_color((string) bankai_theme_mod('font_list_color', '')) ?: '';
    if ($fbtn !== '') {
        $css .= ".bankai-btn,.wp-block-button__link,button[type=submit],input[type=submit],.button{font-family:{$fbtn};font-size:{$fbtns}px;}";
        if ($fbtnc !== '') $css .= ".bankai-btn,.wp-block-button__link,button[type=submit],input[type=submit],.button{color:{$fbtnc};}";
    }
    if ($flist !== '') {
        $css .= "ul,ol,.bankai-menu,li{font-family:{$flist};}";
        $css .= "ul,ol,li{font-size:{$flists}px;}";
        if ($flistc !== '') $css .= "ul,ol,li{color:{$flistc};}";
    }
    
    wp_add_inline_style('bankai-theme-main', $css);
}
add_action('wp_enqueue_scripts', 'bankai_theme_inline_css', 30);


add_action('wp_enqueue_scripts', static function () {
    $custom = bankai_theme_mod('bankai_custom_css', '');
    if (is_string($custom) && $custom !== '') {
        wp_add_inline_style('bankai-theme-main', wp_strip_all_tags($custom));
    }
}, 40);

add_action('wp_footer', static function () {
    $js = bankai_theme_mod('bankai_custom_js', '');
    if (is_string($js) && trim($js) !== '') {
        echo '<script id="bankai-custom-js">' . $js . '</script>'; // phpcs:ignore
    }
}, 99);


/**
 * Inject design tokens so cards/single inherit theme colors.
 */
function bankai_front_design_css(): void {
    $p = '#5C6B7A';
    $s = '#8B9BB4';
    $b1 = '#ffffff';
    $bc = '#1A1A1A';
    $nc = '#8A8F98';
    if (function_exists('bankai_theme_get_design')) {
        $d = bankai_theme_get_design();
        $p = $d['colors']['primary'] ?? ($d['p'] ?? $p);
        $s = $d['colors']['secondary'] ?? ($d['s'] ?? $s);
        $b1 = $d['colors']['base'] ?? ($d['b1'] ?? $b1);
        $bc = $d['colors']['text'] ?? ($d['bc'] ?? $bc);
        $nc = $d['colors']['muted'] ?? ($d['nc'] ?? $nc);
    }
    $blog_bg = get_theme_mod('blog_card_bg', $b1) ?: $b1;
    $blog_text = get_theme_mod('blog_card_text', $bc) ?: $bc;
    $blog_muted = get_theme_mod('blog_card_muted', $nc) ?: $nc;
    $blog_link = get_theme_mod('blog_card_link', $p) ?: $p;
    $blog_border = get_theme_mod('blog_card_border', '#E8E8EA') ?: '#E8E8EA';
    $single_bg = get_theme_mod('single_bg', $b1) ?: $b1;
    $single_text = get_theme_mod('single_text', $bc) ?: $bc;
    $single_muted = get_theme_mod('single_muted', $nc) ?: $nc;
    $single_link = get_theme_mod('single_link', $p) ?: $p;
    $single_heading = get_theme_mod('single_heading', $bc) ?: $bc;
    $blog_off = function_exists('bankai_page_top_offset') ? 0 : 0; // applied via body class
    $single_off = 0;
    $top = function_exists('bankai_page_top_offset') ? bankai_page_top_offset() : 0;
    $blog_mode = get_theme_mod('blog_style_mode', 'inherit');
    $single_mode = get_theme_mod('single_style_mode', 'inherit');
    $blog_font = get_theme_mod('blog_font_family', 'inherit');
    $blog_size = (int) get_theme_mod('blog_font_size', 16);
    $single_font = get_theme_mod('single_font_family', 'inherit');
    $single_size = (int) get_theme_mod('single_font_size', 17);
    $css = sprintf(
        ':root{--p:%1$s;--s:%2$s;--b1:%3$s;--bc:%4$s;--nc:%5$s;--bankai-primary:%1$s;--bankai-card:%3$s;--bankai-text:%4$s;--bankai-muted:%5$s;--bankai-border:color-mix(in srgb,%1$s 18%%,transparent);'
        . '--blog-card-bg:%6$s;--blog-card-text:%7$s;--blog-card-muted:%8$s;--blog-card-link:%9$s;--blog-card-border:%10$s;'
        . '--single-bg:%11$s;--single-text:%12$s;--single-muted:%13$s;--single-link:%14$s;--single-heading:%15$s;}'
        . '.bankai-post-card{--pc-bg:var(--blog-card-bg);--pc-text:var(--blog-card-text);--pc-muted:var(--blog-card-muted);--pc-accent:var(--blog-card-link);--pc-border:var(--blog-card-border);color:var(--pc-text);}'
        . '.bankai-post-card__title a{color:var(--blog-card-text);} .bankai-post-card__title a:hover,.bankai-post-card__cats a{color:var(--blog-card-link);}'
        . '.bankai-single{background:var(--single-bg);color:var(--single-text);}'
        . '.bankai-single__title{color:var(--single-heading);}'
        . '.bankai-single a{color:var(--single-link);}'
        . '.bankai-single__meta,.bankai-single__excerpt{color:var(--single-muted);}'
        . '.bankai-main{padding-top:%16$dpx;}'
        . ($blog_mode === 'custom' ? sprintf('.bankai-post-card{font-family:%s;font-size:%dpx;}', $blog_font === 'inherit' ? 'inherit' : esc_attr($blog_font), $blog_size) : '')
        . ($single_mode === 'custom' ? sprintf('.bankai-single{font-family:%s;}.bankai-single .bankai-entry-content{font-size:%dpx;}', $single_font === 'inherit' ? 'inherit' : esc_attr($single_font), $single_size) : '')
        . ($blog_mode !== 'custom' ? '.bankai-post-card{--pc-bg:var(--b1);--pc-text:var(--bc);--pc-muted:var(--nc);--pc-accent:var(--p);--pc-border:var(--bankai-border);}' : '')
        . ($single_mode !== 'custom' ? '.bankai-single{background:transparent;color:var(--bc);}.bankai-single__title{color:var(--bc);}.bankai-single a{color:var(--p);}' : ''),
        esc_attr($p), esc_attr($s), esc_attr($b1), esc_attr($bc), esc_attr($nc),
        esc_attr($blog_bg), esc_attr($blog_text), esc_attr($blog_muted), esc_attr($blog_link), esc_attr($blog_border),
        esc_attr($single_bg), esc_attr($single_text), esc_attr($single_muted), esc_attr($single_link), esc_attr($single_heading),
        $top
    );
    wp_add_inline_style('bankai-theme-main', $css);
}
add_action('wp_enqueue_scripts', 'bankai_front_design_css', 30);
