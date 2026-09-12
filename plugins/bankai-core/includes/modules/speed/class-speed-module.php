<?php
/**
 * Speed Module Loader.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Speed_Module {

	public static function init() {
		if ( ! Bankai_Module_Switcher::is_active( 'speed' ) ) {
			return;
		}

		$dir = BANKAI_CORE_PATH . 'includes/modules/speed/';
		$files = array(
			'class-host-detector.php',
			'class-dropin-installer.php',
			'class-static-css-builder.php',
			'class-asset-optimizer.php',
			'class-page-cache.php',
			'class-asset-optimization.php',
			'class-media-lazyload.php',
			'class-database-cleaner.php',
			'class-heartbeat-control.php',
		);

		foreach ( $files as $file ) {
			if ( file_exists( $dir . $file ) ) {
				require_once $dir . $file;
			}
		}
	}
}

Bankai_Speed_Module::init();
