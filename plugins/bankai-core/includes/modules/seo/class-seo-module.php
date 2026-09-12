<?php
/**
 * SEO Module Controller.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_SEO_Module
 */
class Bankai_SEO_Module {

	/**
	 * Initialize SEO Module hooks if active.
	 */
	public static function init() {
		if ( ! class_exists( 'Bankai_Module_Switcher' ) || ! Bankai_Module_Switcher::is_active( 'seo' ) ) {
			return;
		}

		require_once BANKAI_CORE_PATH . 'includes/modules/seo/class-meta-generator.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/seo/class-schema-builder.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/seo/class-sitemap.php';
		require_once BANKAI_CORE_PATH . 'includes/modules/seo/class-post-seo-meta.php';

		Bankai_Meta_Generator::init();
		Bankai_Schema_Builder::init();
		Bankai_Sitemap::init();
		Bankai_Post_SEO_Meta::init();
	}
}

Bankai_SEO_Module::init();
