<?php
/**
 * WordPress Heartbeat API Control Module.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Heartbeat_Control {

	public static function init() {
		add_filter( 'heartbeat_settings', array( __CLASS__, 'tune_heartbeat_frequency' ) );
	}

	public static function tune_heartbeat_frequency( $settings ) {
		// Slow down heartbeat frequency to 60s
		$settings['interval'] = 60;
		return $settings;
	}
}

Bankai_Heartbeat_Control::init();
