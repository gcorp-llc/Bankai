<?php
/**
 * SEO Module Bootstrap.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_SEO_Module {

	public static function init() {
		if ( ! Bankai_Module_Switcher::is_active( 'seo' ) ) {
			return;
		}

		 = BANKAI_CORE_PATH . 'includes/modules/seo/';
		 = array(
			'class-post-seo-meta.php',
			'class-meta-generator.php',
			'class-schema-builder.php',
			'class-sitemap.php',
			'class-redirections.php',
			'class-advanced-seo-tools.php',
		);

		foreach (  as  ) {
			if ( file_exists(  .  ) ) {
				require_once  . ;
			}
		}
	}
}

Bankai_SEO_Module::init();
