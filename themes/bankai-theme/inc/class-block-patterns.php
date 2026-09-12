<?php
/**
 * Bankai Block Patterns Registration.
 *
 * @package BankaiTheme
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Block_Patterns
 */
class Bankai_Block_Patterns {

	/**
	 * Register Block Patterns category and patterns.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_patterns' ) );
	}

	/**
	 * Register patterns.
	 */
	public static function register_patterns() {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		register_block_pattern_category(
			'bankai',
			array( 'label' => esc_html__( 'Bankai Ecosystem Patterns', 'bankai-theme' ) )
		);

		register_block_pattern(
			'bankai/hero-banner',
			array(
				'title'       => esc_html__( 'Hero Section', 'bankai-theme' ),
				'categories'  => array( 'bankai' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"60px","bottom":"60px"}}},"backgroundColor":"primary","layout":{"type":"constrained"}} --><div class="wp-block-group has-primary-background-color has-background" style="padding-top:60px;padding-bottom:60px"><!-- wp:heading {"textAlign":"center","textColor":"white"} --><h2 class="wp-block-heading has-text-align-center has-white-color">خوش آمدید به اکوسیستم Bankai</h2><!-- /wp:heading --></div><!-- /wp:group -->',
			)
		);
	}
}

Bankai_Block_Patterns::init();
