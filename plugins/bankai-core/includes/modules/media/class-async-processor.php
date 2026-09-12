<?php
/**
 * Async Background Image Processor.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Async_Processor
 */
class Bankai_Async_Processor {

	/**
	 * Action Hook Name.
	 */
	const ACTION_HOOK = 'bankai_process_async_image';

	/**
	 * Initialize Async Processor hooks.
	 */
	public static function init() {
		add_action( self::ACTION_HOOK, array( __CLASS__, 'process_image' ), 10, 1 );
	}

	/**
	 * Enqueue image processing using Action Scheduler or fallback WP-Cron.
	 *
	 * @param string $file_path Absolute path to uploaded image.
	 */
	public static function enqueue_image_processing( $file_path ) {
		if ( function_exists( 'as_enqueue_async_action' ) ) {
			as_enqueue_async_action( self::ACTION_HOOK, array( 'file_path' => $file_path ), 'bankai-media' );
		} else {
			wp_schedule_single_event( time(), self::ACTION_HOOK, array( 'file_path' => $file_path ) );
		}
	}

	/**
	 * Process image: Convert to WebP/AVIF and apply Watermark.
	 *
	 * @param string $file_path Absolute path to image.
	 */
	public static function process_image( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return;
		}

		// 1. Convert image to WebP format.
		Bankai_Image_Converter::convert_to_webp( $file_path );

		// 2. Apply Watermark if enabled in settings.
		$watermark_enabled = get_option( 'bankai_media_watermark_enabled', false );
		if ( $watermark_enabled ) {
			Bankai_Watermark::apply( $file_path );
		}
	}
}
