<?php
/**
 * Bankai Theme Customizer Settings (Bankai Framework Controls)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

function bankai_theme_customize_register(WP_Customize_Manager $wp_customize): void {
    // 1. Panels: Header & Footer
    $wp_customize->add_panel('bankai_header_panel', [
        'title'       => __('هدرساز اختصاصی (Header Builder)', 'bankai-theme'),
        'priority'    => 25,
        'description' => __('تنظیمات اجزای هدر، لوگو، منو و هدر شفاف', 'bankai-theme'),
    ]);

    $wp_customize->add_panel('bankai_footer_panel', [
        'title'       => __('فوترساز اختصاصی (Footer Builder)', 'bankai-theme'),
        'priority'    => 26,
        'description' => __('تنظیمات کپی‌رایت، ستون‌های ابزارک و منوی فوتر', 'bankai-theme'),
    ]);

    // 2. Global Colors Section
    $wp_customize->add_section('bankai_colors_section', [
        'title'    => __('پالت رنگ‌های سراسری (Global Colors)', 'bankai-theme'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('bankai_primary_color', [
        'default'           => '#0969DA',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'bankai_primary_color', [
        'label'    => __('رنگ سازمانی و اصلی (Theme Primary Color)', 'bankai-theme'),
        'section'  => 'bankai_colors_section',
        'settings' => 'bankai_primary_color',
    ]));

    // 3. Typography Section
    $wp_customize->add_section('bankai_typography_section', [
        'title'    => __('تایپوگرافی و فونت‌ها (Typography)', 'bankai-theme'),
        'priority' => 35,
    ]);

    $wp_customize->add_setting('bankai_base_font', [
        'default'           => 'Vazirmatn',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('bankai_base_font', [
        'label'    => __('فونت بدنه (Body Font)', 'bankai-theme'),
        'section'  => 'bankai_typography_section',
        'type'     => 'select',
        'choices'  => [
            'Vazirmatn'         => 'وزیرمتن (Vazirmatn - بهینه‌شده)',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans (مدرن انگلیسی)',
            'System'            => 'فونت سیستم پیش‌فرض (System Font)',
        ],
    ]);

    // 4. Layout & Container Width
    $wp_customize->add_section('bankai_layout_section', [
        'title'    => __('طرح‌بندی و عرض کانتینر (Layout)', 'bankai-theme'),
        'priority' => 40,
    ]);

    $wp_customize->add_setting('bankai_container_width', [
        'default'           => 1280,
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control('bankai_container_width', [
        'label'       => __('حداکثر عرض کانتینر (پیکسل)', 'bankai-theme'),
        'section'     => 'bankai_layout_section',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 1000,
            'max'  => 1920,
            'step' => 10,
        ],
    ]);
}
add_action('customize_register', 'bankai_theme_customize_register');
