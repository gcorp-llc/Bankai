<?php
/**
 * Server and Host Cache Detector.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Host_Detector
 */
class Bankai_Host_Detector {

	/**
	 * Detect if site is running under a managed server caching environment.
	 *
	 * @return array Detected caching layers.
	 */
	public static function detect_host_caching() {
		$detected = array(
			'litespeed'  => isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'litespeed' ),
			'cloudflare' => isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ),
			'wpengine'   => defined( 'WPE_API_KEY' ) || isset( $_SERVER['IS_WPE'] ),
			'kinsta'     => defined( 'KINSTAMU_VERSION' ) || isset( $_SERVER['KINSTA_CACHE_ZONE'] ),
		);

		return $detected;
	}

	/**
	 * Should page caching be disabled to avoid server-level caching conflicts?
	 *
	 * @return bool True if host caching is active, false otherwise.
	 */
	public static function is_server_cache_active() {
		$host_info = self::detect_host_caching();
		return in_array( true, $host_info, true );
	}
}
