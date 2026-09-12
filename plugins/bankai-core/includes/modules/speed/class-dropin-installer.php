<?php
/**
 * Safe Drop-in Installer for Advanced/Object Cache.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Dropin_Installer
 */
class Bankai_Dropin_Installer {

	/**
	 * Initialize drop-in installer hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_dropins' ) );
	}

	/**
	 * Safely check for existing drop-ins to prevent conflicts.
	 */
	public static function check_dropins() {
		if ( ! function_exists( '_get_dropins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$dropins = function_exists( '_get_dropins' ) ? _get_dropins() : array();

		// Check if advanced-cache.php is active.
		if ( isset( $dropins['advanced-cache.php'] ) ) {
			// Existing advanced-cache drop-in detected.
			update_option( 'bankai_speed_external_page_cache_detected', true );
		}

		// Check if object-cache.php is active.
		if ( isset( $dropins['object-cache.php'] ) ) {
			// Existing object-cache drop-in detected.
			update_option( 'bankai_speed_external_object_cache_detected', true );
		}
	}
}
