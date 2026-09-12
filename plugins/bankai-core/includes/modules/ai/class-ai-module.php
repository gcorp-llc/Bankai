<?php
/**
 * AI Module Controller.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_AI_Module
 */
class Bankai_AI_Module {

	/**
	 * Initialize AI Module hooks if active.
	 */
	public static function init() {
		if ( ! class_exists( 'Bankai_Module_Switcher' ) || ! Bankai_Module_Switcher::is_active( 'ai' ) ) {
			return;
		}

		require_once BANKAI_CORE_PATH . 'includes/modules/ai/class-key-encryptor.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/ai/class-ai-client.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/ai/class-ai-helpers.php';

		Bankai_AI_Helpers::init();
	}
}

Bankai_AI_Module::init();
