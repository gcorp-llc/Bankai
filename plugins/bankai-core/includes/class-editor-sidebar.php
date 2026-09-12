<?php
/**
 * Editor Sidebar Script Register & Meta Box Handler.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Editor_Sidebar {

	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'init', array( __CLASS__, 'register_post_meta' ) );
	}

	public static function register_post_meta() {
		register_post_meta(
			'post',
			'_bankai_seo_title',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'post',
			'_bankai_seo_description',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'post',
			'_bankai_seo_focus_keyword',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'post',
			'_bankai_schema_type',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
	}

	public static function enqueue_editor_assets() {
		if ( ! Bankai_Module_Switcher::is_active( 'seo' ) ) {
			return;
		}

		 = BANKAI_CORE_PATH . 'admin/build/index.asset.php';
		if ( file_exists(  ) ) {
			 = require ;
			wp_enqueue_script(
				'bankai-editor-sidebar',
				BANKAI_CORE_URL . 'admin/build/index.js',
				['dependencies'],
				['version'],
				true
			);
		}
	}
}

Bankai_Editor_Sidebar::init();
