<?php
/**
 * Gutenberg Editor Sidebar Enqueue.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Editor_Sidebar
 */
class Bankai_Editor_Sidebar {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
	}

	/**
	 * Enqueue sidebar scripts for Block Editor.
	 */
	public static function enqueue_editor_assets() {
		$asset_file = BANKAI_CORE_PATH . 'admin/build/index.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;

			wp_enqueue_script(
				'bankai-editor-sidebar',
				BANKAI_CORE_URL . 'admin/build/index.js',
				isset( $asset['dependencies'] ) ? $asset['dependencies'] : array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ),
				isset( $asset['version'] ) ? $asset['version'] : BANKAI_CORE_VERSION,
				true
			);
		}
	}
}

Bankai_Editor_Sidebar::init();
