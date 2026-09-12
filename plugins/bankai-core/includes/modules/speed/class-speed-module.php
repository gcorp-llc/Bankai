<?php
/**
 * Speed Module Controller.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Speed_Module
 */
class Bankai_Speed_Module {

	/**
	 * Initialize Speed Module hooks if active.
	 */
	public static function init() {
		if ( ! class_exists( 'Bankai_Module_Switcher' ) || ! Bankai_Module_Switcher::is_active( 'speed' ) ) {
			return;
		}

		require_once BANKAI_CORE_PATH . 'includes/modules/speed/class-dropin-installer.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/speed/class-host-detector.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/speed/class-asset-optimizer.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/speed/class-static-css-builder.php';

		Bankai_Dropin_Installer::init();
		Bankai_Asset_Optimizer::init();
		Bankai_Static_CSS_Builder::init();
	}
}

Bankai_Speed_Module::init();
