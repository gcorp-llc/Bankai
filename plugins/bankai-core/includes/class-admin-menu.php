<?php
/**
 * Admin Menu and Asset Enqueue Handler.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Admin_Menu
 */
class Bankai_Admin_Menu {

	/**
	 * Page slug for Bankai admin menu.
	 */
	const PAGE_SLUG = 'bankai-core';

	/**
	 * Initialize admin menu hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register top-level admin menu page.
	 */
	public static function register_admin_menu() {
		add_menu_page(
			esc_html__( 'Bankai Core', 'bankai-core' ),
			esc_html__( 'Bankai Core', 'bankai-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_admin_page' ),
			'dashicons-shield',
			30
		);
	}

	/**
	 * Render root HTML container for React app.
	 */
	public static function render_admin_page() {
		echo '<div class="wrap"><div id="bankai-admin-root"></div></div>';
	}

	/**
	 * Enqueue React build assets and inject localized data.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		$asset_file = BANKAI_CORE_PATH . 'admin/build/index.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;

			wp_enqueue_script(
				'bankai-core-admin',
				BANKAI_CORE_URL . 'admin/build/index.js',
				isset( $asset['dependencies'] ) ? $asset['dependencies'] : array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ),
				isset( $asset['version'] ) ? $asset['version'] : BANKAI_CORE_VERSION,
				true
			);

			if ( file_exists( BANKAI_CORE_PATH . 'admin/build/index.css' ) ) {
				wp_enqueue_style(
					'bankai-core-admin-style',
					BANKAI_CORE_URL . 'admin/build/index.css',
					array( 'wp-components' ),
					isset( $asset['version'] ) ? $asset['version'] : BANKAI_CORE_VERSION
				);
			}

			wp_localize_script(
				'bankai-core-admin',
				'bankaiData',
				array(
					'restUrl'       => esc_url_raw( rest_url( Bankai_Rest_API::NAMESPACE ) ),
					'nonce'         => wp_create_nonce( 'wp_rest' ),
					'activeModules' => Bankai_Module_Switcher::get_modules(),
					'isRtl'         => is_rtl(),
				)
			);
		}
	}
}

Bankai_Admin_Menu::init();
