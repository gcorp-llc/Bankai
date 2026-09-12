<?php
/**
 * Bankai Theme Customizer Settings.
 *
 * @package BankaiTheme
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Theme_Customizer
 */
class Bankai_Theme_Customizer {

	/**
	 * Register Customizer options.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer Manager instance.
	 */
	public static function register( $wp_customize ) {
		// Section: Color Palette
		$wp_customize->add_section(
			'bankai_colors_section',
			array(
				'title'    => esc_html__( 'پالت رنگی Bankai', 'bankai-theme' ),
				'priority' => 30,
			)
		);

		// Primary Color
		$wp_customize->add_setting(
			'bankai_primary_color',
			array(
				'default'           => '#0f172a',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'bankai_primary_color',
				array(
					'label'    => esc_html__( 'رنگ اصلی (Primary Color)', 'bankai-theme' ),
					'section'  => 'bankai_colors_section',
					'settings' => 'bankai_primary_color',
				)
			)
		);

		// Accent Color
		$wp_customize->add_setting(
			'bankai_accent_color',
			array(
				'default'           => '#6366f1',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'bankai_accent_color',
				array(
					'label'    => esc_html__( 'رنگ تاکید (Accent Color)', 'bankai-theme' ),
					'section'  => 'bankai_colors_section',
					'settings' => 'bankai_accent_color',
				)
			)
		);

		// Section: Typography & Custom Fonts
		$wp_customize->add_section(
			'bankai_typography_section',
			array(
				'title'    => esc_html__( 'تایپوگرافی و فونت‌ها', 'bankai-theme' ),
				'priority' => 35,
			)
		);

		// Font Family Setting
		$wp_customize->add_setting(
			'bankai_font_family',
			array(
				'default'           => 'Vazirmatn, IRANSans, system-ui',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'bankai_font_family',
			array(
				'label'       => esc_html__( 'خانواده فونت (Font Family)', 'bankai-theme' ),
				'section'     => 'bankai_typography_section',
				'type'        => 'text',
				'description' => esc_html__( 'نام فونت سفارشی یا فونت سیستم نظیر Vazirmatn, IRANSans, system-ui', 'bankai-theme' ),
			)
		);
	}

	/**
	 * Enqueue custom CSS to modernize Customizer Controls UI with RTL support.
	 */
	public static function enqueue_customizer_styles() {
		wp_enqueue_style(
			'bankai-customizer-modern-ui',
			get_template_directory_uri() . '/assets/css/customizer-modern.css',
			array(),
			'1.0.0'
		);
	}
}

add_action( 'customize_register', array( 'Bankai_Theme_Customizer', 'register' ) );
add_action( 'customize_controls_enqueue_scripts', array( 'Bankai_Theme_Customizer', 'enqueue_customizer_styles' ) );
