<?php
/**
 * Scheduled Database Cleanup Module.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Database_Cleaner {

	public static function init() {
		add_action( 'bankai_db_cleanup_cron', array( __CLASS__, 'run_cleanup' ) );

		if ( ! wp_next_scheduled( 'bankai_db_cleanup_cron' ) ) {
			wp_schedule_event( time(), 'weekly', 'bankai_db_cleanup_cron' );
		}
	}

	public static function run_cleanup() {
		global $wpdb;

		// Delete revisions
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'" );

		// Delete auto drafts
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );

		// Delete spam comments
		$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam' OR comment_approved = 'trash'" );

		// Delete expired transients
		$time = time();
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '_transient_timeout_%', $time ) );

		return true;
	}
}

Bankai_Database_Cleaner::init();
