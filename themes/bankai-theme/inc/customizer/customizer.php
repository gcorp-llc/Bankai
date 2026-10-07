<?php
/**
 * Theme Customizer — منوی اصلی Bankai
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function bankai_theme_customize_register(WP_Customize_Manager $wp_customize): void {
    $blog_ctrl = BANKAI_THEME_DIR . '/inc/customizer/class-control-blog-cards.php';
    if (is_readable($blog_ctrl)) {
        require_once $blog_ctrl;
    }
    $login_ctrl = BANKAI_THEME_DIR . '/inc/customizer/class-control-login-page.php';
    if (is_readable($login_ctrl)) {
        require_once $login_ctrl;
    }
    $single_ctrl = BANKAI_THEME_DIR . '/inc/customizer/class-control-single-layout.php';
    if (is_readable($single_ctrl)) {
        require_once $single_ctrl;
    }
    $c404 = BANKAI_THEME_DIR . '/inc/customizer/class-control-404-page.php';
    if (is_readable($c404)) {
        require_once $c404;
    }
    $vis_ctrl = BANKAI_THEME_DIR . '/inc/customizer/class-control-visual-choice.php';
    if (is_readable($vis_ctrl)) {
        require_once $vis_ctrl;
    }

    if (! class_exists('WP_Customize_Control')) {
        return;
    }

    foreach ([
        BANKAI_THEME_DIR . '/inc/customizer/class-control-theme-generator.php',
        BANKAI_THEME_DIR . '/inc/customizer/class-control-header-builder.php',
    ] as $file) {
        if (is_readable($file)) {
            require_once $file;
        }
    }

    foreach (['title_tagline', 'colors', 'header_image', 'background_image', 'static_front_page', 'custom_css'] as $sid) {
        $wp_customize->remove_section($sid);
    }

    // ——— 1. طرح کلی ———
    $wp_customize->add_section('bankai_tarh', [
        'title'    => __('طرح کلی', 'bankai-theme'),
        'priority' => 10,
    ]);

    bankai_register_design_settings($wp_customize);
    bankai_register_typo_settings($wp_customize);

    if (class_exists('Bankai_Customize_Theme_Generator_Control')) {
        $wp_customize->add_setting('bankai_theme_generator_flag', [
            'default' => 1, 'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control(new Bankai_Customize_Theme_Generator_Control($wp_customize, 'bankai_theme_generator_flag', [
            'section'  => 'bankai_tarh',
            'settings' => array_merge(
                ['default' => 'bankai_theme_generator_flag', 'design' => 'bankai_design_json'],
                bankai_typo_setting_map()
            ),
            'priority' => 1,
        ]));
    }

    // ——— 2. سربرگ ———
    $wp_customize->add_section('bankai_header', [
        'title'    => __('سربرگ', 'bankai-theme'),
        'priority' => 20,
    ]);
    $wp_customize->add_setting('bankai_header_builder', [
        'default'           => wp_json_encode(bankai_header_default_config()),
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'bankai_header_sanitize_config',
    ]);
    $wp_customize->add_setting('header_cta_text', [
        'default' => '', 'type' => 'theme_mod', 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('header_cta_url', [
        'default' => '', 'type' => 'theme_mod', 'transport' => 'postMessage', 'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_setting('header_html_snippet', [
        'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'wp_kses_post',
    ]);
    if (class_exists('Bankai_Customize_Header_Builder_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Header_Builder_Control($wp_customize, 'bankai_header_builder', [
            'section'  => 'bankai_header',
            'settings' => 'bankai_header_builder',
            'priority' => 1,
        ]));
    }

    // ——— 3. صفحه‌ها (پنل) ———
    $wp_customize->add_panel('bankai_pages', [
        'title'    => __('صفحه‌ها', 'bankai-theme'),
        'priority' => 30,
    ]);
    $pages = [
        'home'   => __('صفحه خانه', 'bankai-theme'),
        'blog'   => __('بلاگ و مقالات', 'bankai-theme'),
        'single' => __('مقاله', 'bankai-theme'),
        'search' => __('برگه جستجو', 'bankai-theme'),
        '404'    => __('برگه 404', 'bankai-theme'),
        'login'  => __('برگه ورود', 'bankai-theme'),
    ];
    foreach ($pages as $key => $label) {
        $sid = 'bankai_page_' . $key;
        $wp_customize->add_section($sid, [
            'title' => $label,
            'panel' => 'bankai_pages',
        ]);
        $wp_customize->add_setting("page_{$key}_title", [
            'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage',
        ]);
        $wp_customize->add_control("page_{$key}_title", [
            'label'   => __('عنوان سفارشی', 'bankai-theme'),
            'section' => $sid,
            'type'    => 'text',
        ]);
        $wp_customize->add_setting("page_{$key}_sidebar", [
            'default' => 'default', 'type' => 'theme_mod',
            'sanitize_callback' => static function ($v) {
                return in_array($v, ['default', 'none', 'left', 'right'], true) ? $v : 'default';
            },
        ]);
        /* sidebar visual via bankai_register_page_chrome */
    }



    foreach (array_keys($pages) as $pkey) {
        bankai_register_page_chrome($wp_customize, $pkey, 'bankai_page_' . $pkey);
    }

    // ——— Visual 404 page control ———
    $bool_san = static function ($v) {
        return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
    };
    $settings_404 = [
        'page_404_show_navbar' => true,
        'page_404_show_mobile_bar' => true,
        'page_404_show_footer' => true,
        'page_404_show_latest' => true,
        'page_404_auto_redirect' => false,
    ];
    foreach ($settings_404 as $sid => $def) {
        $wp_customize->add_setting($sid, [
            'default' => $def,
            'type' => 'theme_mod',
            'transport' => 'refresh',
            'sanitize_callback' => $bool_san,
        ]);
    }
    $wp_customize->add_setting('page_404_theme', [
        'default' => 'astronaut',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            $ok = ['astronaut', 'cat', 'milk', 'construction', 'geometric', 'lost'];
            $legacy = ['minimal'=>'lost','soft'=>'geometric','bold'=>'astronaut','space'=>'astronaut','glitch'=>'milk','illustration'=>'cat'];
            if (isset($legacy[$v])) { $v = $legacy[$v]; }
            return in_array($v, $ok, true) ? $v : 'astronaut';
        },
    ]);
    foreach ([
        'page_404_message' => ['default' => '', 'cb' => 'sanitize_textarea_field'],
        'page_404_btn_text' => ['default' => '', 'cb' => 'sanitize_text_field'],
        'page_404_btn_url' => ['default' => '', 'cb' => 'esc_url_raw'],
        'page_404_btn_icon' => ['default' => 'home', 'cb' => 'sanitize_title'],
        'page_404_redirect_url' => ['default' => '', 'cb' => 'esc_url_raw'],
    ] as $sid => $meta) {
        $wp_customize->add_setting($sid, [
            'default' => $meta['default'],
            'type' => 'theme_mod',
            'transport' => 'refresh',
            'sanitize_callback' => $meta['cb'],
        ]);
    }
    $wp_customize->add_setting('page_404_btn_style', [
        'default' => 'primary',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['primary', 'outline', 'ghost'], true) ? $v : 'primary';
        },
    ]);
    $wp_customize->add_setting('page_404_posts_mode', [
        'default' => 'latest',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            if ($v === 'related') { $v = 'random'; }
            return in_array($v, ['latest', 'random'], true) ? $v : 'latest';
        },
    ]);
    $wp_customize->add_setting('page_404_posts_layout', [
        'default' => 'grid',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['grid', 'list', 'carousel'], true) ? $v : 'grid';
        },
    ]);
    $wp_customize->add_setting('page_404_posts_count', [
        'default' => 3,
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return max(2, min(12, absint($v)));
        },
    ]);
    $wp_customize->add_setting('page_404_posts_card_style', [
        'default' => 'default',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            $ok = ['default', 'horizontal', 'overlay', 'minimal', 'bordered', 'elevated', 'soft'];
            return in_array($v, $ok, true) ? $v : 'default';
        },
    ]);
    $wp_customize->add_setting('page_404_redirect_delay', [
        'default' => 8,
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return max(2, min(120, absint($v)));
        },
    ]);
    $wp_customize->add_setting('page_404_redirect_type', [
        'default' => 'custom',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['random', 'custom'], true) ? $v : 'custom';
        },
    ]);
    $wp_customize->add_setting('bankai_404_page_visual', [
        'default' => 1,
        'type' => 'theme_mod',
        'sanitize_callback' => 'absint',
    ]);
    if (class_exists('Bankai_Customize_404_Page_Control')) {
        $wp_customize->add_control(new Bankai_Customize_404_Page_Control($wp_customize, 'bankai_404_page_visual', [
            'section' => 'bankai_page_404',
            'settings' => 'bankai_404_page_visual',
            'priority' => 5,
        ]));
    }


    // بلاگ و مقالات — کارت‌های Flowbite-style
    $blog_section = 'bankai_page_blog';
    $wp_customize->add_setting('blog_card_style', [
        'default'           => 'default',
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => static function ($v) {
            $ok = ['default', 'horizontal', 'overlay', 'minimal', 'bordered'];
            return in_array($v, $ok, true) ? $v : 'default';
        },
    ]);
    if (class_exists('Bankai_Customize_Blog_Cards_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Blog_Cards_Control($wp_customize, 'blog_card_style', [
            'label'       => __('مدل کارت مقالات', 'bankai-theme'),
            'description' => __('انتخاب بصری سبک نمایش لیست مقالات', 'bankai-theme'),
            'section'     => $blog_section,
            'settings'    => 'blog_card_style',
            'priority'    => 20,
        ]));
    } else {
        $wp_customize->add_control('blog_card_style', [
            'label'   => __('مدل کارت مقالات', 'bankai-theme'),
            'section' => $blog_section,
            'type'    => 'select',
            'choices' => [
                'default'    => __('پیش‌فرض', 'bankai-theme'),
                'horizontal' => __('افقی', 'bankai-theme'),
                'overlay'    => __('پوشش', 'bankai-theme'),
                'minimal'    => __('مینیمال', 'bankai-theme'),
                'bordered'   => __('حاشیه‌دار', 'bankai-theme'),
            ],
            'priority' => 20,
        ]);
    }
    $wp_customize->add_setting('blog_posts_per_page', [
        'default'           => (int) get_option('posts_per_page', 10),
        'type'              => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return max(1, min(48, absint($v)));
        },
    ]);
    $wp_customize->add_control('blog_posts_per_page', [
        'label'    => __('تعداد پست در هر صفحه', 'bankai-theme'),
        'section'  => $blog_section,
        'type'     => 'number',
        'input_attrs' => ['min' => 1, 'max' => 48],
        'priority' => 21,
    ]);
    $wp_customize->add_setting('blog_pagination', [
        'default'           => 'numbers',
        'type'              => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['numbers', 'prevnext', 'infinite'], true) ? $v : 'numbers';
        },
    ]);
    /* blog_pagination visual control registered below */
    $wp_customize->add_setting('blog_title_chars', [
        'default' => 80, 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) { return max(20, min(200, absint($v))); },
    ]);
    $wp_customize->add_control('blog_title_chars', [
        'label' => __('حداکثر کاراکتر عنوان', 'bankai-theme'),
        'section' => $blog_section, 'type' => 'number', 'priority' => 23,
        'input_attrs' => ['min' => 20, 'max' => 200],
    ]);
    $wp_customize->add_setting('blog_excerpt_chars', [
        'default' => 140, 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) { return max(40, min(400, absint($v))); },
    ]);
    $wp_customize->add_control('blog_excerpt_chars', [
        'label' => __('حداکثر کاراکتر خلاصه', 'bankai-theme'),
        'section' => $blog_section, 'type' => 'number', 'priority' => 24,
        'input_attrs' => ['min' => 40, 'max' => 400],
    ]);

    $blog_toggle_keys = ['blog_show_author', 'blog_show_date', 'blog_show_cats', 'blog_show_excerpt'];
    foreach ($blog_toggle_keys as $sid) {
        $wp_customize->add_setting($sid, [
            'default'           => true,
            'type'              => 'theme_mod',
            'transport'         => 'refresh',
            'sanitize_callback' => static function ($v) {
                return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
            },
        ]);
    }
    $wp_customize->add_setting('bankai_blog_elements_visual', [
        'default' => 1, 'type' => 'theme_mod', 'sanitize_callback' => 'absint',
    ]);
    if (class_exists('Bankai_Customize_Single_Layout_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Single_Layout_Control($wp_customize, 'bankai_blog_elements_visual', [
            'section'  => $blog_section,
            'priority' => 25,
            'label'    => __('عناصر کارت بلاگ', 'bankai-theme'),
            'description' => __('روی کارت بزنید تا روشن/خاموش شود', 'bankai-theme'),
            'settings' => 'bankai_blog_elements_visual',
            'items'    => [
                'blog_show_author'  => ['label' => __('نویسنده', 'bankai-theme'), 'icon' => 'author'],
                'blog_show_date'    => ['label' => __('تاریخ', 'bankai-theme'), 'icon' => 'date'],
                'blog_show_cats'    => ['label' => __('دسته‌ها', 'bankai-theme'), 'icon' => 'cats'],
                'blog_show_excerpt' => ['label' => __('خلاصه', 'bankai-theme'), 'icon' => 'title'],
            ],
        ]));
    }
    // Blog pagination visual
    if (class_exists('Bankai_Customize_Visual_Choice_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Visual_Choice_Control($wp_customize, 'blog_pagination', [
            'label'   => __('نوع صفحه‌بندی', 'bankai-theme'),
            'section' => $blog_section,
            'settings'=> 'blog_pagination',
            'priority'=> 22,
            'choices_visual' => [
                'numbers'  => ['label' => __('شماره‌ای', 'bankai-theme'), 'icon' => 'numbers'],
                'prevnext' => ['label' => __('قبلی/بعدی', 'bankai-theme'), 'icon' => 'prevnext'],
                'infinite' => ['label' => __('بی‌نهایت', 'bankai-theme'), 'icon' => 'infinite'],
            ],
        ]));
    }
    // Blog colors

    foreach (['blog_card_bg' => '#FFFFFF', 'blog_card_text' => '#1A1A1A', 'blog_card_muted' => '#8A8F98', 'blog_card_link' => '#5C6B7A', 'blog_card_border' => '#E8E8EA'] as $ck => $cdef) {
        $wp_customize->add_setting($ck, [
            'default' => $cdef, 'type' => 'theme_mod', 'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
    }
    $wp_customize->add_setting('blog_style_mode', [
        'default' => 'inherit', 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['inherit', 'custom'], true) ? $v : 'inherit';
        },
    ]);
    $wp_customize->add_setting('blog_font_family', [
        'default' => 'inherit', 'type' => 'theme_mod', 'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('blog_font_size', [
        'default' => 16, 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) { return max(12, min(24, absint($v))); },
    ]);
    $wp_customize->add_setting('blog_card_radius', [
        'default' => '1rem', 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('blog_card_border_w', [
        'default' => 1, 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => static function ($v) { return max(0, min(8, absint($v))); },
    ]);
    if (class_exists('Bankai_Customize_Style_Mode_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Style_Mode_Control($wp_customize, 'blog_style_mode', [
            'label' => __('استایل کارت بلاگ', 'bankai-theme'),
            'section' => $blog_section,
            'settings' => 'blog_style_mode',
            'priority' => 35,
            'colors' => [
                'blog_card_bg' => __('پس‌زمینه', 'bankai-theme'),
                'blog_card_text' => __('متن', 'bankai-theme'),
                'blog_card_muted' => __('متن فرعی', 'bankai-theme'),
                'blog_card_link' => __('لینک', 'bankai-theme'),
                'blog_card_border' => __('کادر', 'bankai-theme'),
            ],
            'font_setting' => 'blog_font_family',
            'size_setting' => 'blog_font_size',
        ]));
    }

    // Transparent header offset (blog)
    /* blog top spacing via page_blog_top_mode */



    
    // ——— مقاله تکی — کنترل بصری ———


    // ——— Login / Register page ———
    $login_section = 'bankai_page_login';
    $wp_customize->add_setting('login_layout', [
        'default' => 'center', 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['center', 'right', 'left', 'split'], true) ? $v : 'center';
        },
    ]);
    $wp_customize->add_setting('login_design_style', [
        'default' => 'premium-minimalism', 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_key',
    ]);
    $wp_customize->add_setting('login_theme', [
        'default' => 'light', 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            $v = sanitize_key($v);
            $presets = function_exists('bankai_theme_presets') ? array_keys(bankai_theme_presets()) : ['light'];
            return in_array($v, $presets, true) ? $v : 'light';
        },
    ]);
    $wp_customize->add_setting('login_title', [
        'default' => __('ورود به پنل', 'bankai-theme'), 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('login_register_title', [
        'default' => __('ثبت‌نام', 'bankai-theme'), 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('login_intro_title', [
        'default' => __('خوش آمدید', 'bankai-theme'), 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('login_intro_text', [
        'default' => __('به پنل مدیریت خوش آمدید. وارد شوید یا حساب جدید بسازید.', 'bankai-theme'), 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_setting('login_logo_id', [
        'default' => 0, 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_setting('login_bg_id', [
        'default' => 0, 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_setting('login_custom_slug', [
        'default' => '', 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            $v = sanitize_title($v);
            $blocked = ['wp-admin', 'wp-login', 'wp-content', 'wp-includes', 'feed', 'comments', 'blog', 'admin'];
            return in_array($v, $blocked, true) ? '' : $v;
        },
    ]);
    $wp_customize->add_setting('register_custom_slug', [
        'default' => '', 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            $v = sanitize_title($v);
            $blocked = ['wp-admin', 'wp-login', 'wp-content', 'wp-includes', 'feed', 'comments', 'blog', 'admin'];
            return in_array($v, $blocked, true) ? '' : $v;
        },
    ]);
    $wp_customize->add_setting('login_block_default_urls', [
        'default' => false, 'type' => 'theme_mod', 'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            return ! empty($v) && $v !== '0' && $v !== 'false';
        },
    ]);
    $wp_customize->add_setting('bankai_login_page_control', [
        'default' => 1, 'type' => 'theme_mod', 'sanitize_callback' => 'absint',
    ]);
    if (class_exists('Bankai_Customize_Login_Page_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Login_Page_Control($wp_customize, 'bankai_login_page_control', [
            'section' => $login_section,
            'settings' => 'bankai_login_page_control',
            'priority' => 10,
        ]));
    }

    // ——— Search page (mirror blog controls) ———
    $search_section = 'bankai_page_search';
    $wp_customize->add_setting('search_card_style', [
        'default' => 'default',
        'type' => 'theme_mod',
        'transport' => 'refresh',
        'sanitize_callback' => static function ($v) {
            $ok = ['default', 'horizontal', 'overlay', 'minimal', 'bordered'];
            return in_array($v, $ok, true) ? $v : 'default';
        },
    ]);
    if (class_exists('Bankai_Customize_Blog_Cards_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Blog_Cards_Control($wp_customize, 'search_card_style', [
            'label' => __('مدل کارت نتایج جستجو', 'bankai-theme'),
            'description' => __('همان سبک کارت بلاگ', 'bankai-theme'),
            'section' => $search_section,
            'settings' => 'search_card_style',
            'priority' => 20,
        ]));
    }
    $wp_customize->add_setting('search_posts_per_page', [
        'default' => (int) get_option('posts_per_page', 10),
        'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return max(1, min(48, absint($v)));
        },
    ]);
    $wp_customize->add_control('search_posts_per_page', [
        'label' => __('تعداد نتیجه در هر صفحه', 'bankai-theme'),
        'section' => $search_section,
        'type' => 'number',
        'input_attrs' => ['min' => 1, 'max' => 48],
        'priority' => 21,
    ]);
    $wp_customize->add_setting('search_pagination', [
        'default' => 'numbers',
        'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['numbers', 'prevnext', 'infinite'], true) ? $v : 'numbers';
        },
    ]);
    if (class_exists('Bankai_Customize_Visual_Choice_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Visual_Choice_Control($wp_customize, 'search_pagination', [
            'label' => __('نوع صفحه‌بندی', 'bankai-theme'),
            'section' => $search_section,
            'settings' => 'search_pagination',
            'priority' => 22,
            'choices_visual' => [
                'numbers' => ['label' => __('شماره‌ای', 'bankai-theme'), 'icon' => 'numbers'],
                'prevnext' => ['label' => __('قبلی/بعدی', 'bankai-theme'), 'icon' => 'prevnext'],
                'infinite' => ['label' => __('بی‌نهایت', 'bankai-theme'), 'icon' => 'infinite'],
            ],
        ]));
    }
    $bool_san = static function ($v) {
        return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
    };
    foreach (['search_show_author' => true, 'search_show_date' => true, 'search_show_cats' => true, 'search_show_excerpt' => true] as $sid => $def) {
        $wp_customize->add_setting($sid, [
            'default' => $def,
            'type' => 'theme_mod',
            'sanitize_callback' => $bool_san,
        ]);
    }
    $wp_customize->add_setting('bankai_search_elements_visual', [
        'default' => 1,
        'type' => 'theme_mod',
        'sanitize_callback' => 'absint',
    ]);
    if (class_exists('Bankai_Customize_Single_Layout_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Single_Layout_Control($wp_customize, 'bankai_search_elements_visual', [
            'section' => $search_section,
            'priority' => 25,
            'label' => __('عناصر کارت نتیجه', 'bankai-theme'),
            'settings' => 'bankai_search_elements_visual',
            'items' => [
                'search_show_author' => ['label' => __('نویسنده', 'bankai-theme'), 'icon' => 'author'],
                'search_show_date' => ['label' => __('تاریخ', 'bankai-theme'), 'icon' => 'date'],
                'search_show_cats' => ['label' => __('دسته‌ها', 'bankai-theme'), 'icon' => 'cats'],
                'search_show_excerpt' => ['label' => __('خلاصه', 'bankai-theme'), 'icon' => 'title'],
            ],
        ]));
    }


    $single_section = 'bankai_page_single';
    $single_keys = [
        'single_show_cover', 'single_show_title', 'single_show_author', 'single_show_date',
        'single_show_cats', 'single_show_tags', 'single_show_related', 'single_show_comments',
    ];
    foreach ($single_keys as $key) {
        $wp_customize->add_setting($key, [
            'default'           => true,
            'type'              => 'theme_mod',
            'transport'         => 'refresh',
            'sanitize_callback' => static function ($v) {
                return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
            },
        ]);
    }
    $wp_customize->add_setting('bankai_single_layout_visual', [
        'default' => 1, 'type' => 'theme_mod', 'sanitize_callback' => 'absint',
    ]);
    if (class_exists('Bankai_Customize_Single_Layout_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Single_Layout_Control($wp_customize, 'bankai_single_layout_visual', [
            'section'  => $single_section,
            'priority' => 15,
            'label'    => __('عناصر نمایش مقاله', 'bankai-theme'),
            'description' => __('روی هر کارت بزنید تا روشن/خاموش شود', 'bankai-theme'),
            'settings' => 'bankai_single_layout_visual',
            'items'    => [
                'single_show_cover'    => ['label' => __('کاور', 'bankai-theme'), 'icon' => 'cover'],
                'single_show_title'    => ['label' => __('عنوان', 'bankai-theme'), 'icon' => 'title'],
                'single_show_author'   => ['label' => __('نویسنده', 'bankai-theme'), 'icon' => 'author'],
                'single_show_date'     => ['label' => __('تاریخ', 'bankai-theme'), 'icon' => 'date'],
                'single_show_cats'     => ['label' => __('دسته‌بندی', 'bankai-theme'), 'icon' => 'cats'],
                'single_show_tags'     => ['label' => __('برچسب‌ها', 'bankai-theme'), 'icon' => 'tags'],
                'single_show_related'  => ['label' => __('مطالب مرتبط', 'bankai-theme'), 'icon' => 'related'],
                'single_show_comments' => ['label' => __('نظرات / گفتگو', 'bankai-theme'), 'icon' => 'comments'],
            ],
        ]));
    }
    // Visual sidebar for single
    /* sidebar visual via bankai_register_page_chrome */

    $wp_customize->add_setting('single_related_mode', [
        'default' => 'related', 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['related', 'latest'], true) ? $v : 'related';
        },
    ]);
    $wp_customize->add_setting('single_related_layout', [
        'default' => 'grid', 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['grid', 'list', 'carousel'], true) ? $v : 'grid';
        },
    ]);
    $wp_customize->add_setting('single_related_count', [
        'default' => 4, 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) { return max(1, min(12, absint($v))); },
    ]);
    if (class_exists('Bankai_Customize_Visual_Choice_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Visual_Choice_Control($wp_customize, 'single_related_mode', [
            'label' => __('نوع بلوک مطالب', 'bankai-theme'),
            'section' => $single_section, 'settings' => 'single_related_mode', 'priority' => 40,
            'choices_visual' => [
                'related' => ['label' => __('مرتبط', 'bankai-theme'), 'icon' => 'related'],
                'latest'  => ['label' => __('آخرین', 'bankai-theme'), 'icon' => 'latest'],
            ],
        ]));
        $wp_customize->add_control(new Bankai_Customize_Visual_Choice_Control($wp_customize, 'single_related_layout', [
            'label' => __('چیدمان مطالب مرتبط', 'bankai-theme'),
            'section' => $single_section, 'settings' => 'single_related_layout', 'priority' => 41,
            'choices_visual' => [
                'grid' => ['label' => __('شبکه', 'bankai-theme'), 'icon' => 'grid'],
                'list' => ['label' => __('لیست', 'bankai-theme'), 'icon' => 'list'],
                'carousel' => ['label' => __('کاروسل', 'bankai-theme'), 'icon' => 'carousel'],
            ],
        ]));
    }
    $wp_customize->add_control('single_related_count', [
        'label' => __('تعداد مطالب مرتبط', 'bankai-theme'),
        'section' => $single_section, 'type' => 'number', 'priority' => 42,
        'input_attrs' => ['min' => 1, 'max' => 12],
    ]);
    // Related card style (reuse blog card styles)
    $wp_customize->add_setting('single_related_card_style', [
        'default' => 'default', 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            $ok = ['default', 'horizontal', 'overlay', 'minimal', 'bordered'];
            return in_array($v, $ok, true) ? $v : 'default';
        },
    ]);
    if (class_exists('Bankai_Customize_Blog_Cards_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Blog_Cards_Control($wp_customize, 'single_related_card_style', [
            'label' => __('سبک کارت مطالب مرتبط', 'bankai-theme'),
            'section' => $single_section, 'settings' => 'single_related_card_style', 'priority' => 43,
        ]));
    }

    foreach ([
        'single_bg' => '#FFFFFF', 'single_text' => '#1A1A1A', 'single_muted' => '#8A8F98',
        'single_link' => '#5C6B7A', 'single_heading' => '#1A1A1A',
    ] as $ck => $cdef) {
        $wp_customize->add_setting($ck, [
            'default' => $cdef, 'type' => 'theme_mod', 'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
    }
    $wp_customize->add_setting('single_style_mode', [
        'default' => 'inherit', 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['inherit', 'custom'], true) ? $v : 'inherit';
        },
    ]);
    $wp_customize->add_setting('single_font_family', [
        'default' => 'inherit', 'type' => 'theme_mod', 'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_setting('single_font_size', [
        'default' => 17, 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) { return max(12, min(24, absint($v))); },
    ]);
    $wp_customize->add_setting('single_radius', [
        'default' => '1rem', 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    if (class_exists('Bankai_Customize_Style_Mode_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Style_Mode_Control($wp_customize, 'single_style_mode', [
            'label' => __('استایل صفحه مقاله', 'bankai-theme'),
            'section' => $single_section,
            'settings' => 'single_style_mode',
            'priority' => 50,
            'colors' => [
                'single_bg' => __('پس‌زمینه', 'bankai-theme'),
                'single_heading' => __('عنوان', 'bankai-theme'),
                'single_text' => __('متن', 'bankai-theme'),
                'single_muted' => __('متن فرعی', 'bankai-theme'),
                'single_link' => __('لینک', 'bankai-theme'),
            ],
            'font_setting' => 'single_font_family',
            'size_setting' => 'single_font_size',
        ]));
    }

    // Author box toggle
    $wp_customize->add_setting('single_show_author_box', [
        'default' => true, 'type' => 'theme_mod',
        'sanitize_callback' => static function ($v) {
            return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
        },
    ]);
    $wp_customize->add_setting('bankai_single_author_visual', [
        'default' => 1, 'type' => 'theme_mod', 'sanitize_callback' => 'absint',
    ]);
    if (class_exists('Bankai_Customize_Single_Layout_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Single_Layout_Control($wp_customize, 'bankai_single_author_visual', [
            'section' => $single_section, 'priority' => 45,
            'label' => __('کادر نویسنده', 'bankai-theme'),
            'settings' => 'bankai_single_author_visual',
            'items' => [
                'single_show_author_box' => ['label' => __('کارت نویسنده + مطالب او', 'bankai-theme'), 'icon' => 'author'],
            ],
        ]));
    }
    /* single top via page_single_top_mode */


    // ——— 4. پابرگ ———
    $wp_customize->add_section('bankai_footer', [
        'title'    => __('پابرگ', 'bankai-theme'),
        'priority' => 40,
    ]);
    $wp_customize->add_setting('bankai_footer_builder', [
        'default'           => wp_json_encode(bankai_footer_default_config()),
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'bankai_footer_sanitize_config',
    ]);
    if (class_exists('Bankai_Customize_Footer_Builder_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Footer_Builder_Control($wp_customize, 'bankai_footer_builder', [
            'section'  => 'bankai_footer',
            'settings' => 'bankai_footer_builder',
            'priority' => 1,
        ]));
    }
    $wp_customize->add_setting('footer_text', [
        'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage',
    ]);
    $wp_customize->add_control('footer_text', [
        'label' => __('متن کپی‌رایت', 'bankai-theme'), 'section' => 'bankai_footer', 'type' => 'text',
    ]);
    $wp_customize->add_setting('footer_columns', [
        'default' => 4, 'type' => 'theme_mod',
        'sanitize_callback' => static fn($v) => max(1, min(4, absint($v))),
    ]);
    $wp_customize->add_control('footer_columns', [
        'label' => __('تعداد ستون', 'bankai-theme'), 'section' => 'bankai_footer', 'type' => 'select',
        'choices' => [1 => '1', 2 => '2', 3 => '3', 4 => '4'],
    ]);
    foreach ([
        'footer_social_telegram' => 'تلگرام',
        'footer_social_instagram' => 'اینستاگرام',
        'footer_social_linkedin' => 'لینکدین',
        'footer_social_x' => 'X',
        'footer_social_youtube' => 'یوتیوب',
    ] as $id => $label) {
        $wp_customize->add_setting($id, [
            'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control($id, [
            'label' => $label, 'section' => 'bankai_footer', 'type' => 'url',
        ]);
    }

    // ——— 5. CSS / JS ———
    $wp_customize->add_section('bankai_code', [
        'title'    => __('CSS و JS', 'bankai-theme'),
        'priority' => 50,
    ]);
    $wp_customize->add_setting('bankai_custom_css', [
        'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'bankai_sanitize_css',
    ]);
    $wp_customize->add_control('bankai_custom_css', [
        'label'   => __('CSS سفارشی', 'bankai-theme'),
        'section' => 'bankai_code',
        'type'    => 'textarea',
    ]);
    $wp_customize->add_setting('bankai_custom_js', [
        'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'bankai_sanitize_js',
    ]);
    $wp_customize->add_control('bankai_custom_js', [
        'label'       => __('JS سفارشی (فوتر)', 'bankai-theme'),
        'description' => __('بدون تگ script', 'bankai-theme'),
        'section'     => 'bankai_code',
        'type'        => 'textarea',
    ]);
}


/**
 * Register visual top spacing + SEO fields for a page section.
 */
function bankai_register_page_chrome(WP_Customize_Manager $wp_customize, string $key, string $section): void {
    $mode_id = "page_{$key}_top_mode";
    $offset_id = "page_{$key}_top_offset";
    $wp_customize->add_setting($mode_id, [
        'default' => 'offset',
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => static function ($v) {
            return in_array($v, ['flush', 'offset'], true) ? $v : 'offset';
        },
    ]);
    $wp_customize->add_setting($offset_id, [
        'default' => 80,
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => static function ($v) {
            return max(0, min(200, absint($v)));
        },
    ]);
    if (class_exists('Bankai_Customize_Top_Spacing_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Top_Spacing_Control($wp_customize, $mode_id, [
            'label' => __('فاصله از نوبار شفاف / چسبان', 'bankai-theme'),
            'description' => __('چسبیده به بالا یا فاصله از هدر', 'bankai-theme'),
            'section' => $section,
            'settings' => $mode_id,
            'priority' => 3,
            'input_attrs' => ['offset_setting' => $offset_id],
        ]));
    }
    // Sidebar visual for applicable pages
    if (in_array($key, ['blog', 'single', 'search', 'home'], true)) {
        $side_id = "page_{$key}_sidebar";
        // setting may already exist from loop
        if (!$wp_customize->get_setting($side_id)) {
            $wp_customize->add_setting($side_id, [
                'default' => 'default',
                'type' => 'theme_mod',
                'transport' => 'postMessage',
                'sanitize_callback' => static function ($v) {
                    return in_array($v, ['default', 'none', 'left', 'right'], true) ? $v : 'default';
                },
            ]);
        }
        if (class_exists('Bankai_Customize_Sidebar_Position_Control')) {
            // Remove default select if present - add visual
            $wp_customize->add_control(new Bankai_Customize_Sidebar_Position_Control($wp_customize, $side_id . '_visual', [
                'label' => __('موقعیت سایدبار', 'bankai-theme'),
                'section' => $section,
                'settings' => $side_id,
                'priority' => 8,
            ]));
        }
    }
    // SEO fields + AI button
    foreach ([
        "page_{$key}_seo_title" => ['', 'sanitize_text_field'],
        "page_{$key}_seo_desc" => ['', 'sanitize_textarea_field'],
        "page_{$key}_seo_keywords" => ['', 'sanitize_text_field'],
    ] as $sid => $cfg) {
        $wp_customize->add_setting($sid, [
            'default' => $cfg[0],
            'type' => 'theme_mod',
            'sanitize_callback' => $cfg[1],
        ]);
    }
    $wp_customize->add_control("page_{$key}_seo_title", [
        'label' => __('عنوان سئو (Title)', 'bankai-theme'),
        'section' => $section,
        'type' => 'text',
        'priority' => 90,
    ]);
    $wp_customize->add_control("page_{$key}_seo_desc", [
        'label' => __('متا دیسکریپشن', 'bankai-theme'),
        'section' => $section,
        'type' => 'textarea',
        'priority' => 91,
    ]);
    $wp_customize->add_control("page_{$key}_seo_keywords", [
        'label' => __('کلمات کلیدی', 'bankai-theme'),
        'section' => $section,
        'type' => 'text',
        'priority' => 92,
    ]);
    $wp_customize->add_setting("page_{$key}_seo_ai_btn", [
        'default' => '', 'type' => 'theme_mod', 'sanitize_callback' => 'sanitize_text_field',
    ]);
    if (class_exists('Bankai_Customize_Seo_Ai_Control')) {
        $wp_customize->add_control(new Bankai_Customize_Seo_Ai_Control($wp_customize, "page_{$key}_seo_ai_btn", [
            'section' => $section,
            'settings' => "page_{$key}_seo_ai_btn",
            'priority' => 89,
            'page_key' => $key,
        ]));
    }
}

add_action('customize_register', 'bankai_theme_customize_register', 20);

function bankai_sanitize_css($css): string {
    return wp_strip_all_tags((string) $css);
}
function bankai_sanitize_js($js): string {
    return (string) $js; // stored for admin only; output with care
}

function bankai_register_design_settings(WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_setting('bankai_design_json', [
        'default' => '', 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => static function ($value) {
            if (! is_string($value) || $value === '') return '';
            $d = json_decode($value, true);
            if (! is_array($d) || ! function_exists('bankai_theme_sanitize_design')) return '';
            return wp_json_encode(bankai_theme_sanitize_design($d));
        },
    ]);
    foreach ([
        'site_layout' => ['wide', static fn($v) => in_array($v, ['boxed', 'wide', 'full', 'content-sidebar'], true) ? $v : 'wide'],
        'container_layout' => ['boxed', static fn($v) => in_array($v, ['boxed', 'full'], true) ? $v : 'boxed'],
        'container_style' => ['default', static fn($v) => in_array($v, ['default', 'framed'], true) ? $v : 'default'],
    ] as $id => $m) {
        $wp_customize->add_setting($id, [
            'default' => $m[0], 'type' => 'theme_mod', 'transport' => 'postMessage', 'sanitize_callback' => $m[1],
        ]);
    }
    $wp_customize->add_setting('container_width', [
        'default' => 1280, 'type' => 'theme_mod', 'transport' => 'postMessage', 'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_setting('bankai_custom_fonts', [
        'default' => [], 'type' => 'theme_mod', 'transport' => 'postMessage',
        'sanitize_callback' => static function ($v) {
            if (is_string($v)) $v = json_decode($v, true);
            if (! is_array($v)) return [];
            $out = [];
            foreach ($v as $f) {
                if (empty($f['url']) || empty($f['label'])) continue;
                $out[] = [
                    'id' => sanitize_key($f['id'] ?? uniqid('cf_')),
                    'label' => sanitize_text_field($f['label']),
                    'stack' => sanitize_text_field($f['stack'] ?? ''),
                    'url' => esc_url_raw($f['url']),
                    'format' => sanitize_key($f['format'] ?? 'woff2'),
                ];
            }
            return $out;
        },
    ]);
}

function bankai_typo_setting_map(): array {
    $keys = [
        'font_color_mode', 'font_preset', 'font_body', 'font_body_size', 'font_body_weight',
        'font_heading', 'font_h1_size', 'font_h2_size', 'text_direction',
        'font_nav', 'font_nav_size', 'font_title', 'font_title_size',
        'font_h3_size', 'font_h4_size', 'custom_fonts',
        'font_body_color', 'font_body_color_hover', 'font_nav_color', 'font_nav_color_hover',
        'font_title_color', 'font_title_color_hover', 'font_h1_color', 'font_h1_color_hover',
        'font_h2_color', 'font_h2_color_hover', 'font_h3_color', 'font_h3_color_hover',
        'font_h4_color', 'font_h4_color_hover',
    ];
    $map = [];
    foreach ($keys as $k) {
        $map[$k] = ($k === 'custom_fonts') ? 'bankai_custom_fonts' : $k;
    }
    $map['site_layout'] = 'site_layout';
    $map['container_layout'] = 'container_layout';
    $map['container_style'] = 'container_style';
    $map['container_width'] = 'container_width';
    return $map;
}

function bankai_register_typo_settings(WP_Customize_Manager $wp_customize): void {
    $defs = [
        'font_color_mode' => ['inherit', static function ($v) { return in_array($v, ['inherit', 'custom'], true) ? $v : 'inherit'; }],
        'font_preset' => ['vazirmatn', 'sanitize_key'],
        'font_body' => ['Vazirmatn, Tahoma, sans-serif', 'sanitize_text_field'],
        'font_body_size' => [16, 'absint'],
        'font_body_weight' => ['400', 'sanitize_text_field'],
        'font_heading' => ['Vazirmatn, Tahoma, sans-serif', 'sanitize_text_field'],
        'font_nav' => ['Vazirmatn, Tahoma, sans-serif', 'sanitize_text_field'],
        'font_nav_size' => [15, 'absint'],
        'font_title' => ['Vazirmatn, Tahoma, sans-serif', 'sanitize_text_field'],
        'font_title_size' => [28, 'absint'],
        'font_h1_size' => [32, 'absint'],
        'font_h2_size' => [24, 'absint'],
        'font_h3_size' => [20, 'absint'],
        'font_h4_size' => [18, 'absint'],
        'text_direction' => ['rtl', static fn($v) => in_array($v, ['rtl', 'ltr'], true) ? $v : 'rtl'],
        'font_body_color' => ['#1E293B', 'sanitize_hex_color'],
        'font_body_color_hover' => ['#0F172A', 'sanitize_hex_color'],
        'font_nav_color' => ['#334155', 'sanitize_hex_color'],
        'font_nav_color_hover' => ['#5B8DEF', 'sanitize_hex_color'],
        'font_title_color' => ['#0F172A', 'sanitize_hex_color'],
        'font_title_color_hover' => ['#1E293B', 'sanitize_hex_color'],
        'font_h1_color' => ['#0F172A', 'sanitize_hex_color'],
        'font_h1_color_hover' => ['#1E293B', 'sanitize_hex_color'],
        'font_h2_color' => ['#0F172A', 'sanitize_hex_color'],
        'font_h2_color_hover' => ['#1E293B', 'sanitize_hex_color'],
        'font_h3_color' => ['#1E293B', 'sanitize_hex_color'],
        'font_h3_color_hover' => ['#334155', 'sanitize_hex_color'],
        'font_h4_color' => ['#334155', 'sanitize_hex_color'],
        'font_h4_color_hover' => ['#475569', 'sanitize_hex_color'],
    ];
    foreach ($defs as $id => $m) {
        $wp_customize->add_setting($id, [
            'default' => $m[0], 'type' => 'theme_mod', 'transport' => 'postMessage', 'sanitize_callback' => $m[1],
        ]);
    }
}

add_action('customize_save_after', static function (WP_Customize_Manager $wp_customize) {
    $json = $wp_customize->get_setting('bankai_design_json');
    $raw  = $json ? $json->value() : '';
    // Also try posted value
    if ((! is_string($raw) || $raw === '') && isset($_POST['customized'])) {
        $customized = json_decode(wp_unslash((string) $_POST['customized']), true);
        if (is_array($customized) && ! empty($customized['bankai_design_json'])) {
            $raw = $customized['bankai_design_json'];
        }
    }
    if (is_string($raw) && $raw !== '' && function_exists('bankai_theme_sanitize_design')) {
        $d = json_decode($raw, true);
        if (is_array($d)) {
            $design = bankai_theme_sanitize_design($d);
            update_option('bankai_theme_design', $design, false);
            set_theme_mod('bankai_design_json', wp_json_encode($design));
            if (! empty($design['colors']['primary'])) {
                set_theme_mod('color_primary', $design['colors']['primary']);
            }
            if (! empty($design['colors']['base'])) {
                set_theme_mod('color_bg', $design['colors']['base']);
            }
            if (! empty($design['colors']['base_content'])) {
                set_theme_mod('color_text', $design['colors']['base_content']);
            }
            if (! empty($design['radius']['box'])) {
                $px = (int) round((float) $design['radius']['box'] * (str_contains((string) $design['radius']['box'], 'rem') ? 16 : 1));
                if ($px > 0) {
                    set_theme_mod('border_radius', $px);
                }
            }
        }
    }
});

add_action('customize_preview_init', static function () {
    wp_enqueue_script(
        'bankai-theme-customizer',
        BANKAI_THEME_URI . '/assets/js/customizer-preview.js',
        ['customize-preview'],
        BANKAI_THEME_VERSION,
        true
    );
});

add_action('customize_controls_enqueue_scripts', static function () {
    wp_enqueue_style(
        'bankai-customizer-controls',
        BANKAI_THEME_URI . '/assets/css/customizer-controls.css',
        [],
        BANKAI_THEME_VERSION
    );
    wp_enqueue_script(
        'bankai-customizer-search-nav',
        BANKAI_THEME_URI . '/assets/js/customizer-search-nav.js',
        ['customize-controls', 'jquery'],
        BANKAI_THEME_VERSION,
        true
    );
    wp_localize_script('bankai-customizer-search-nav', 'bankaiSearchNav', [
        'homeUrl' => home_url('/'),
        'blogUrl' => get_permalink(get_option('page_for_posts')) ?: home_url('/'),
    ]);
});

add_action('wp_ajax_bankai_theme_upload_font', 'bankai_ajax_upload_font');
add_action('wp_ajax_bankai_theme_delete_font', 'bankai_ajax_delete_font');

function bankai_ajax_upload_font(): void {
    check_ajax_referer('bankai_theme_admin', 'nonce');
    if (! current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }
    if (empty($_FILES['font_file'])) {
        wp_send_json_error(['message' => __('فایلی ارسال نشد.', 'bankai-theme')]);
    }
    $file = $_FILES['font_file'];
    $ext  = strtolower(pathinfo(sanitize_file_name($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (! in_array($ext, ['woff2', 'woff', 'ttf', 'otf'], true)) {
        wp_send_json_error(['message' => __('فقط woff2/woff/ttf/otf', 'bankai-theme')]);
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    add_filter('upload_mimes', static function ($m) {
        $m['woff2'] = 'font/woff2'; $m['woff'] = 'font/woff'; $m['ttf'] = 'font/ttf'; $m['otf'] = 'font/otf';
        return $m;
    });
    $upload = wp_handle_upload($file, ['test_form' => false]);
    if (! empty($upload['error'])) {
        wp_send_json_error(['message' => $upload['error']]);
    }
    $label = sanitize_text_field(wp_unslash($_POST['font_label'] ?? ''));
    if ($label === '') {
        $label = pathinfo($file['name'], PATHINFO_FILENAME);
    }
    $font = [
        'id' => 'cf_' . wp_generate_password(8, false, false),
        'label' => $label,
        'stack' => "'" . $label . "', Tahoma, sans-serif",
        'url' => $upload['url'],
        'format' => $ext,
    ];
    $fonts = function_exists('bankai_theme_get_custom_fonts') ? bankai_theme_get_custom_fonts() : [];
    $fonts[] = $font;
    set_theme_mod('bankai_custom_fonts', $fonts);
    wp_send_json_success(['font' => $font, 'fonts' => $fonts]);
}

function bankai_ajax_delete_font(): void {
    check_ajax_referer('bankai_theme_admin', 'nonce');
    if (! current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }
    $id = sanitize_key(wp_unslash($_POST['font_id'] ?? ''));
    $fonts = function_exists('bankai_theme_get_custom_fonts') ? bankai_theme_get_custom_fonts() : [];
    $fonts = array_values(array_filter($fonts, static fn($f) => ($f['id'] ?? '') !== $id));
    set_theme_mod('bankai_custom_fonts', $fonts);
    wp_send_json_success(['fonts' => $fonts]);
}


add_action('customize_save_after', static function () {
    $n = (int) get_theme_mod('blog_posts_per_page', 0);
    if ($n > 0) {
        update_option('posts_per_page', max(1, min(48, $n)));
    }
});
