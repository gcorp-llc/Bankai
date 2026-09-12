<?php
/**
 * Media Module Controller.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Media_Module
 */
class Bankai_Media_Module {

	/**
	 * Initialize Media Module hooks if active.
	 */
	public static function init() {
		if ( ! class_exists( 'Bankai_Module_Switcher' ) || ! Bankai_Module_Switcher::is_active( 'media' ) ) {
			return;
		}

		require_once BANKAI_CORE_PATH . 'includes/modules/media/class-async-processor.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/media/class-image-converter.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/media/class-watermark.php';

		Bankai_Async_Processor::init();

		add_filter( 'wp_handle_upload', array( __CLASS__, 'handle_media_upload' ), 10, 2 );
	}

	/**
	 * Intercept uploaded file and enqueue async background processing.
	 *
	 * @param array  $fileinfo Array of upload data (file, url, type).
	 * @param string $context  Upload context.
	 * @return array Modified or original fileinfo.
	 */
	public static function handle_media_upload( $fileinfo, $context ) {
		if ( empty( $fileinfo['file'] ) || empty( $fileinfo['type'] ) ) {
			return $fileinfo;
		}

		// Only process JPEG/PNG image uploads.
		$allowed_types = array( 'image/jpeg', 'image/png', 'image/jpg' );
		if ( in_array( $fileinfo['type'], $allowed_types, true ) ) {
			Bankai_Async_Processor::enqueue_image_processing( $fileinfo['file'] );
		}

		return $fileinfo;
	}
}

Bankai_Media_Module::init();
