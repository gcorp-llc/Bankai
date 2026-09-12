<?php
/**
 * Image Format Converter (WebP & AVIF).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Image_Converter
 */
class Bankai_Image_Converter {

	/**
	 * Convert image file to WebP format.
	 *
	 * @param string $file_path Absolute path to image file.
	 * @param int    $quality   WebP quality (0-100).
	 * @return string|bool Path to WebP image or false on failure.
	 */
	public static function convert_to_webp( $file_path, $quality = 82 ) {
		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		$info = pathinfo( $file_path );
		$output_path = $info['dirname'] . '/' . $info['filename'] . '.webp';

		// 1. Try Imagick library if available.
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			try {
				$image = new Imagick( $file_path );
				$image->setImageFormat( 'webp' );
				$image->setImageCompressionQuality( $quality );
				$image->writeImage( $output_path );
				$image->clear();
				$image->destroy();
				return $output_path;
			} catch ( Exception $e ) {
				// Fallback to GD if Imagick fails.
			}
		}

		// 2. Fallback to GD library.
		if ( function_exists( 'imagewebp' ) ) {
			$mime_type = mime_content_type( $file_path );
			$resource  = false;

			switch ( $mime_type ) {
				case 'image/jpeg':
				case 'image/jpg':
					$resource = imagecreatefromjpeg( $file_path );
					break;
				case 'image/png':
					$resource = imagecreatefrompng( $file_path );
					imagepalettetotruecolor( $resource );
					imagealphablending( $resource, true );
					imagesavealpha( $resource, true );
					break;
			}

			if ( $resource ) {
				$result = imagewebp( $resource, $output_path, $quality );
				imagedestroy( $resource );
				if ( $result ) {
					return $output_path;
				}
			}
		}

		return false;
	}
}
