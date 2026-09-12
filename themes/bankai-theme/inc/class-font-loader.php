<?php
/**
 * Bankai Custom Font Loader & Dynamic CSS Generator.
 *
 * @package BankaiTheme
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Font_Loader
 */
class Bankai_Font_Loader {

	/**
	 * Register hooks for inline dynamic CSS and font loading.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'render_dynamic_css' ), 10 );
	}

	/**
	 * Output dynamic CSS variables and @font-face rules in <head>.
	 */
	public static function render_dynamic_css() {
		$primary_color = get_theme_mod( 'bankai_primary_color', '#007cba' );
		$accent_color  = get_theme_mod( 'bankai_accent_color', '#00a0d2' );
		$font_family   = get_theme_mod( 'bankai_font_family', 'system-ui' );

		$custom_font_url = get_option( 'bankai_custom_font_url', '' );

		echo '<style id="bankai-dynamic-css">' . "
";

		if ( ! empty( $custom_font_url ) ) {
			echo '@font-face {' . "
";
			echo '  font-family: "' . esc_attr( $font_family ) . '";' . "
";
			echo '  src: url("' . esc_url( $custom_font_url ) . '");' . "
";
			echo '  font-display: swap;' . "
";
			echo '}' . "
";
		}

		echo ':root {' . "
";
		echo '  --bankai-primary-color: ' . esc_attr( $primary_color ) . ';' . "
";
		echo '  --bankai-accent-color: ' . esc_attr( $accent_color ) . ';' . "
";
		echo '  --bankai-font-family: ' . esc_attr( $font_family ) . ', system-ui, -apple-system, sans-serif;' . "
";
		echo '}' . "
";

		echo 'body {' . "
";
		echo '  font-family: var(--bankai-font-family);' . "
";
		echo '  color: #1e293b;' . "
";
		echo '}' . "
";

		echo 'a {' . "
";
		echo '  color: var(--bankai-primary-color);' . "
";
		echo '}' . "
";

		echo '</style>' . "
";
	}
}

Bankai_Font_Loader::init();
