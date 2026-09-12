<?php
/**
 * Disk-based Page Caching Module with auto-purge and sitemap preloading.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Page_Cache
 */
class Bankai_Page_Cache {

	/**
	 * Cache directory path.
	 *
	 * @var string
	 */
	private static $cache_dir;

	/**
	 * Initialize page cache hooks.
	 */
	public static function init() {
		self::$cache_dir = WP_CONTENT_DIR . '/cache/bankai-page-cache/';

		add_action( 'save_post', array( __CLASS__, 'purge_post_cache' ) );
		add_action( 'comment_post', array( __CLASS__, 'purge_post_cache_on_comment' ), 10, 2 );
		add_action( 'bankai_preload_cache_cron', array( __CLASS__, 'preload_cache' ) );

		if ( ! wp_next_scheduled( 'bankai_preload_cache_cron' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'bankai_preload_cache_cron' );
		}

		add_action( 'template_redirect', array( __CLASS__, 'start_buffering' ), 0 );
	}

	/**
	 * Start output buffering for front-end caching if user is not logged in.
	 */
	public static function start_buffering() {
		if ( is_user_logged_in() || is_admin() || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			return;
		}

		$cache_file = self::get_cache_file_path();
		if ( file_exists( $cache_file ) && ( time() - filemtime( $cache_file ) < 86400 ) ) {
			header( 'X-Bankai-Cache: HIT' );
			readfile( $cache_file );
			die();
		}

		header( 'X-Bankai-Cache: MISS' );
		ob_start( array( __CLASS__, 'save_cache_buffer' ) );
	}

	/**
	 * Save output buffer to cache file.
	 *
	 * @param string $buffer HTML content.
	 * @return string Original content.
	 */
	public static function save_cache_buffer( $buffer ) {
		if ( strlen( $buffer ) < 250 || http_response_code() !== 200 ) {
			return $buffer;
		}

		$cache_file = self::get_cache_file_path();
		$dir = dirname( $cache_file );

		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$footer_signature = "
<!-- Cached by Bankai Core Speed Engine on " . date( 'Y-m-d H:i:s' ) . " -->";
		file_put_contents( $cache_file, $buffer . $footer_signature );

		return $buffer;
	}

	/**
	 * Generate unique cache path for current request URL.
	 *
	 * @return string
	 */
	private static function get_cache_file_path() {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : 'localhost';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '/';
		$hash = md5( $host . $uri );

		return self::$cache_dir . $hash . '.html';
	}

	/**
	 * Purge cache when post is updated or published.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function purge_post_cache( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		self::purge_all();
	}

	/**
	 * Purge cache on new comment.
	 *
	 * @param int $comment_id Comment ID.
	 * @param int|string $approved Comment status.
	 */
	public static function purge_post_cache_on_comment( $comment_id, $approved ) {
		if ( 1 === $approved || '1' === $approved ) {
			self::purge_all();
		}
	}

	/**
	 * Purge all page cache files.
	 *
	 * @return bool
	 */
	public static function purge_all() {
		if ( ! file_exists( self::$cache_dir ) ) {
			return true;
		}

		$files = glob( self::$cache_dir . '*.html' );
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
		}

		return true;
	}

	/**
	 * Preload page cache using XML Sitemap URLs.
	 */
	public static function preload_cache() {
		$sitemap_url = home_url( '/sitemap.xml' );
		$response    = wp_remote_get( $sitemap_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		preg_match_all( '/<loc>(.*?)<\/loc>/i', $body, $matches );

		if ( ! empty( $matches[1] ) ) {
			$urls = array_slice( array_unique( $matches[1] ), 0, 20 );
			foreach ( $urls as $url ) {
				wp_remote_get( $url, array( 'blocking' => false, 'headers' => array( 'X-Bankai-Preload' => '1' ) ) );
			}
		}
	}
}

Bankai_Page_Cache::init();
