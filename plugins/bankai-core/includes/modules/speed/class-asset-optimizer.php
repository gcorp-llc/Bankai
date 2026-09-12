<?php
/**
 * Asset Optimizer (Minification, Async/Defer, Lazy Load).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Asset_Optimizer
 */
class Bankai_Asset_Optimizer {

	/**
	 * Register hooks for script/style optimization and lazy loading.
	 */
	public static function init() {
		if ( is_admin() ) {
			return;
		}

		add_filter( 'script_loader_tag', array( __CLASS__, 'add_defer_async' ), 10, 3 );
		add_filter( 'the_content', array( __CLASS__, 'add_lazy_loading' ) );
	}

	/**
	 * Add defer attribute to non-essential scripts.
	 *
	 * @param string $tag    Script HTML tag.
	 * @param string $handle Script handle name.
	 * @param string $src    Script source URL.
	 * @return string Modified script tag.
	 */
	public static function add_defer_async( $tag, $handle, $src ) {
		if ( is_admin() ) {
			return $tag;
		}

		// Skip core jquery to prevent script breakage.
		if ( 'jquery' === $handle || 'jquery-core' === $handle ) {
			return $tag;
		}

		if ( false === strpos( $tag, 'defer' ) && false === strpos( $tag, 'async' ) ) {
			return str_replace( ' src=', ' defer="defer" src=', $tag );
		}

		return $tag;
	}

	/**
	 * Add native loading="lazy" attribute to images in post content.
	 *
	 * @param string $content Post content.
	 * @return string Modified content with lazy loading.
	 */
	public static function add_lazy_loading( $content ) {
		if ( empty( $content ) || is_admin() ) {
			return $content;
		}

		// Inject loading="lazy" into img tags if missing.
		$content = preg_replace( '/<img(?![^>]*loading=)([^>]*)>/i', '<img loading="lazy" $1>', $content );
		return $content;
	}

	/**
	 * Simple CSS minifier helper.
	 *
	 * @param string $css Unminified CSS.
	 * @return string Minified CSS.
	 */
	public static function minify_css( $css ) {
		if ( empty( $css ) ) {
			return '';
		}

		$css = preg_replace( '!/\*.*?\*/!s', '', $css );
		$css = preg_replace( '/\s+/', ' ', $css );
		$css = str_replace( array( ' {', '{ ', ' }', '} ', ' ;', '; ' ), array( '{', '{', '}', '}', ';', ';' ), $css );
		return trim( $css );
	}
}
