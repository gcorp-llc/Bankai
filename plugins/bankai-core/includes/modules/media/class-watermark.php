<?php
/**
 * Image Watermark Processor.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Watermark
 */
class Bankai_Watermark {

	/**
	 * Apply watermark overlay to target image file.
	 *
	 * @param string $file_path Absolute path to target image file.
	 * @return bool True on success, false on failure.
	 */
	public static function apply( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		$watermark_path = get_option( 'bankai_media_watermark_image_path', '' );
		if ( empty( $watermark_path ) || ! file_exists( $watermark_path ) ) {
			return false;
		}

		$position = get_option( 'bankai_media_watermark_position', 'bottom-right' );
		$opacity  = (int) get_option( 'bankai_media_watermark_opacity', 80 );

		// Process with GD if supported.
		if ( function_exists( 'imagecreatefromstring' ) ) {
			$main_img = imagecreatefromstring( file_get_contents( $file_path ) );
			$wm_img   = imagecreatefromstring( file_get_contents( $watermark_path ) );

			if ( ! $main_img || ! $wm_img ) {
				return false;
			}

			$main_w = imagesx( $main_img );
			$main_h = imagesy( $main_img );
			$wm_w   = imagesx( $wm_img );
			$wm_h   = imagesy( $wm_img );

			switch ( $position ) {
				case 'top-left':
					$dest_x = 20;
					$dest_y = 20;
					break;
				case 'top-right':
					$dest_x = $main_w - $wm_w - 20;
					$dest_y = 20;
					break;
				case 'center':
					$dest_x = (int) ( ( $main_w - $wm_w ) / 2 );
					$dest_y = (int) ( ( $main_h - $wm_h ) / 2 );
					break;
				case 'bottom-left':
					$dest_x = 20;
					$dest_y = $main_h - $wm_h - 20;
					break;
				case 'bottom-right':
				default:
					$dest_x = $main_w - $wm_w - 20;
					$dest_y = $main_h - $wm_h - 20;
					break;
			}

			imagecopymerge( $main_img, $wm_img, $dest_x, $dest_y, 0, 0, $wm_w, $wm_h, $opacity );

			$mime_type = mime_content_type( $file_path );
			switch ( $mime_type ) {
				case 'image/jpeg':
				case 'image/jpg':
					imagejpeg( $main_img, $file_path, 90 );
					break;
				case 'image/png':
					imagepng( $main_img, $file_path, 9 );
					break;
			}

			imagedestroy( $main_img );
			imagedestroy( $wm_img );

			return true;
		}

		return false;
	}
}
