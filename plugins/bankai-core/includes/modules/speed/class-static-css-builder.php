<?php
/**
 * Static CSS Builder for Dynamic Variables.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Static_CSS_Builder
 */
class Bankai_Static_CSS_Builder {

	/**
	 * Initialize static CSS hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_static_css' ), 99 );
	}

	/**
	 * Build static CSS file in upload dir or inject inline style fallback.
	 */
	public static function enqueue_static_css() {
		$upload_dir = wp_upload_dir();
		$bankai_dir = $upload_dir['basedir'] . '/bankai-static';
		$bankai_url = $upload_dir['baseurl'] . '/bankai-static';

		$css_content = ":root { --bankai-speed-optimized: 1; }
";
		$file_hash   = md5( $css_content );
		$file_name   = "variables-{$file_hash}.css";
		$file_path   = "{$bankai_dir}/{$file_name}";
		$file_url    = "{$bankai_url}/{$file_name}";

		if ( ! file_exists( $bankai_dir ) ) {
			wp_mkdir_p( $bankai_dir );
		}

		if ( file_exists( $bankai_dir ) && is_writable( $bankai_dir ) ) {
			if ( ! file_exists( $file_path ) ) {
				file_put_contents( $file_path, $css_content );
			}
			wp_enqueue_style( 'bankai-static-vars', $file_url, array(), $file_hash );
		} else {
			// Fallback: Inline CSS injection if uploads directory is not writable.
			wp_register_style( 'bankai-static-vars-fallback', false );
			wp_enqueue_style( 'bankai-static-vars-fallback' );
			wp_add_inline_style( 'bankai-static-vars-fallback', $css_content );
		}
	}
}
