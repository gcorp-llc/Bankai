<?php
/**
 * Asset Optimization Module (CSS/JS Minify & Combine, Defer JS, Delay JS Execution, Google Fonts Optimization).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Asset_Optimization {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'optimize_google_fonts' ), 999 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'add_defer_delay_attributes' ), 10, 3 );
		add_action( 'wp_footer', array( __CLASS__, 'inject_delay_js_loader' ), 9999 );
	}

	public static function optimize_google_fonts() {
		global $wp_styles;
		if ( ! ( $wp_styles instanceof WP_Styles ) ) {
			return;
		}

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
				if ( strpos( $style->src, 'display=' ) === false ) {
					$style->src = add_query_arg( 'display', 'swap', $style->src );
				}
			}
		}
	}

	public static function add_defer_delay_attributes( $tag, $handle, $src ) {
		if ( is_admin() || empty( $src ) ) {
			return $tag;
		}

		if ( strpos( $handle, 'jquery-core' ) !== false || strpos( $handle, 'bankai' ) !== false ) {
			return $tag;
		}

		// Apply defer
		if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
			$tag = str_replace( ' src=', ' defer src=', $tag );
		}

		return $tag;
	}

	public static function inject_delay_js_loader() {
		if ( is_admin() ) {
			return;
		}
		?>
		<script id="bankai-delay-js">
		(function() {
			var jsEvents = ['keydown', 'mousemove', 'wheel', 'touchmove', 'touchstart'];
			function triggerDelayJS() {
				jsEvents.forEach(function(e) { window.removeEventListener(e, triggerDelayJS); });
				document.querySelectorAll('script[type="bankai-delayed-script"]').forEach(function(s) {
					var script = document.createElement('script');
					if (s.src) { script.src = s.src; } else { script.textContent = s.textContent; }
					document.body.appendChild(script);
				});
			}
			jsEvents.forEach(function(e) { window.addEventListener(e, triggerDelayJS, {passive: true}); });
		})();
		</script>
		<?php
	}
}

Bankai_Asset_Optimization::init();
