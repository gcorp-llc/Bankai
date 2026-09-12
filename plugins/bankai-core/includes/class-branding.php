<?php
/**
 * Branding and Logo Manager for Bankai Core & Theme.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Branding
 */
class Bankai_Branding {

	/**
	 * Initialize branding synchronization and hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'ensure_logo_synced' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'localize_branding_data' ) );
	}

	/**
	 * Get root logo path if exists.
	 *
	 * @return string|bool
	 */
	public static function get_root_logo_path() {
		$root_path = ABSPATH;
		// Check common root or parent directory of ABSPATH/wp-content
		$possible_paths = array(
			ABSPATH . 'logo.jpg',
			dirname( ABSPATH ) . '/logo.jpg',
			dirname( BANKAI_CORE_PATH, 3 ) . '/logo.jpg', // Repository root
		);

		foreach ( $possible_paths as $path ) {
			if ( file_exists( $path ) ) {
				return $path;
			}
		}

		return false;
	}

	/**
	 * Ensure root logo.jpg is copied to plugin and theme asset directories.
	 */
	public static function ensure_logo_synced() {
		$root_logo = self::get_root_logo_path();
		if ( ! $root_logo ) {
			return;
		}

		// Target directories
		$plugin_assets = BANKAI_CORE_PATH . 'assets/images';
		if ( ! file_exists( $plugin_assets ) ) {
			wp_mkdir_p( $plugin_assets );
		}

		$plugin_target = $plugin_assets . '/logo.jpg';
		if ( ! file_exists( $plugin_target ) || filemtime( $root_logo ) > filemtime( $plugin_target ) ) {
			copy( $root_logo, $plugin_target );
		}

		// Theme assets
		$theme_dir = get_template_directory();
		if ( file_exists( $theme_dir ) ) {
			$theme_assets = $theme_dir . '/assets/images';
			if ( ! file_exists( $theme_assets ) ) {
				wp_mkdir_p( $theme_assets );
			}
			$theme_target = $theme_assets . '/logo.jpg';
			if ( ! file_exists( $theme_target ) || filemtime( $root_logo ) > filemtime( $theme_target ) ) {
				copy( $root_logo, $theme_target );
			}
		}
	}

	/**
	 * Get absolute URL to the branding logo.
	 *
	 * @return string
	 */
	public static function get_logo_url() {
		$plugin_logo_path = BANKAI_CORE_PATH . 'assets/images/logo.jpg';
		if ( file_exists( $plugin_logo_path ) ) {
			return BANKAI_CORE_URL . 'assets/images/logo.jpg';
		}

		return BANKAI_CORE_URL . 'logo.jpg';
	}

	/**
	 * Localize logo data for React Admin and frontend metadata.
	 */
	public static function localize_branding_data() {
		wp_localize_script(
			'bankai-core-admin-app',
			'bankaiBranding',
			array(
				'logoUrl'     => self::get_logo_url(),
				'company'     => 'GCORP LLC',
				'brandName'   => 'Bankai Core Ecosystem',
				'logoExists'  => file_exists( BANKAI_CORE_PATH . 'assets/images/logo.jpg' ),
			)
		);
	}
}

Bankai_Branding::init();
