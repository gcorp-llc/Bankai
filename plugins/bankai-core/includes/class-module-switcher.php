<?php
/**
 * Module Switcher Class.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Module_Switcher
 */
class Bankai_Module_Switcher {

	/**
	 * Option key stored in WordPress database.
	 */
	const OPTION_KEY = 'bankai_core_active_modules';

	/**
	 * Default modules state.
	 *
	 * @var array
	 */
	private static $default_modules = array(
		'seo'   => true,
		'speed' => true,
		'media' => true,
		'ai'    => true,
	);

	/**
	 * Initialize module switcher hooks and conflict notices.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'check_conflicting_plugins' ) );
	}

	/**
	 * Get all active module statuses.
	 *
	 * @return array Array of module statuses.
	 */
	public static function get_modules() {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return wp_parse_args( $stored, self::$default_modules );
	}

	/**
	 * Check if a specific module is active.
	 *
	 * @param string $module_key Module key (seo, speed, media, ai).
	 * @return bool True if active, false otherwise.
	 */
	public static function is_active( $module_key ) {
		$modules = self::get_modules();
		return isset( $modules[ $module_key ] ) ? (bool) $modules[ $module_key ] : false;
	}

	/**
	 * Update modules status.
	 *
	 * @param array $modules Array of module key => boolean pairs.
	 * @return bool True if updated, false on failure or permission error.
	 */
	public static function update_modules( $modules ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$current  = self::get_modules();
		$sanitized = array();

		foreach ( self::$default_modules as $key => $default_val ) {
			if ( isset( $modules[ $key ] ) ) {
				$sanitized[ $key ] = (bool) $modules[ $key ];
			} else {
				$sanitized[ $key ] = isset( $current[ $key ] ) ? (bool) $current[ $key ] : $default_val;
			}
		}

		return update_option( self::OPTION_KEY, $sanitized );
	}

	/**
	 * Check for conflicting plugins (Rank Math, Yoast, WP Rocket) and display admin notices.
	 */
	public static function check_conflicting_plugins() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$conflicts = array();

		if ( self::is_active( 'seo' ) ) {
			if ( defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) ) {
				$conflicts[] = esc_html__( 'Bankai SEO Module is active, but another SEO plugin (Rank Math or Yoast SEO) is detected. Consider disabling the competing plugin for optimal performance.', 'bankai-core' );
			}
		}

		if ( self::is_active( 'speed' ) ) {
			if ( defined( 'WP_ROCKET_VERSION' ) ) {
				$conflicts[] = esc_html__( 'Bankai Speed Module is active alongside WP Rocket. Disable WP Rocket to prevent caching conflicts.', 'bankai-core' );
			}
		}

		foreach ( $conflicts as $notice ) {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $notice ) . '</p></div>';
		}
	}
}

Bankai_Module_Switcher::init();
