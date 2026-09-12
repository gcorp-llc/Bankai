<?php
/**
 * Database Installer Class.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_DB_Installer
 */
class Bankai_DB_Installer {

	/**
	 * Install or update custom database tables using dbDelta and seed initial data if needed.
	 */
	public static function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_404       = $wpdb->prefix . 'bankai_404_logs';
		$table_redirects = $wpdb->prefix . 'bankai_redirects';

		$sql = "CREATE TABLE $table_404 (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			requested_uri varchar(2048) NOT NULL,
			hits int(11) UNSIGNED NOT NULL DEFAULT 1,
			source_ip varchar(255) DEFAULT '',
			user_agent varchar(255) DEFAULT '',
			recommended_action varchar(255) DEFAULT '',
			action_type varchar(50) DEFAULT 'apply_301',
			confidence varchar(20) DEFAULT '90%',
			target_uri varchar(2048) DEFAULT '',
			last_detected datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY uri_hash (requested_uri(191))
		) $charset_collate;

		CREATE TABLE $table_redirects (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			source_url varchar(2048) NOT NULL,
			target_url varchar(2048) NOT NULL,
			redirect_code smallint(5) UNSIGNED NOT NULL DEFAULT 301,
			hit_count int(11) UNSIGNED NOT NULL DEFAULT 0,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY source_hash (source_url(191))
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		self::maybe_seed_data();
	}

	/**
	 * Seed default options and initial realistic 404 log entries if empty.
	 */
	public static function maybe_seed_data() {
		global $wpdb;

		// Default Settings.
		if ( false === get_option( 'bankai_core_settings' ) ) {
			$default_settings = array(
				'seo_engine'      => 1,
				'media_optimizer' => 1,
				'smart_redirects' => 1,
				'llm_manifest'    => 1,
				'base_stripper'   => 0,
				'cache_warmer'    => 1,
				'overall_score'   => 94,
			);
			update_option( 'bankai_core_settings', $default_settings );
		}

		$table_404 = $wpdb->prefix . 'bankai_404_logs';

		// Seed initial 404 logs if empty.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_404}'" ) === $table_404 ) {
			$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_404}" );
			if ( 0 === $count ) {
				$seed_data = array(
					array(
						'requested_uri'      => '/old-blog/product-review-2023',
						'hits'               => 48,
						'source_ip'          => '192.168.1.42 / Googlebot Crawler',
						'user_agent'         => 'Googlebot/2.1',
						'recommended_action' => 'Apply 301',
						'action_type'        => 'apply_301',
						'confidence'         => '96%',
						'target_uri'         => '/reviews/product-review-2023',
					),
					array(
						'requested_uri'      => '/pricing-v1',
						'hits'               => 12,
						'source_ip'          => '104.28.19.112 / Direct Referrer',
						'user_agent'         => 'Mozilla/5.0 Chrome/120.0',
						'recommended_action' => 'Map Target',
						'action_type'        => 'map_target',
						'confidence'         => '91%',
						'target_uri'         => '/pricing',
					),
					array(
						'requested_uri'      => '/wp-content/uploads/temp.pdf',
						'hits'               => 6,
						'source_ip'          => '172.56.21.9 / Broken External',
						'user_agent'         => 'Mozilla/5.0 Safari/605.1',
						'recommended_action' => 'Redirect',
						'action_type'        => 'redirect',
						'confidence'         => '80%',
						'target_uri'         => '/',
					),
					array(
						'requested_uri'      => '/.env',
						'hits'               => 31,
						'source_ip'          => '45.154.255.8 / Scanner Bot (Blocked)',
						'user_agent'         => 'Go-http-client/1.1',
						'recommended_action' => 'Auto-Dropped',
						'action_type'        => 'auto_dropped',
						'confidence'         => '100%',
						'target_uri'         => '',
					),
				);

				foreach ( $seed_data as $row ) {
					$wpdb->insert(
						$table_404,
						$row,
						array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
					);
				}
			}
		}
	}
}
