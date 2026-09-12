<?php
/**
 * Plugin Name: Bankai Core
 * Plugin URI:  https://gcorp.llc/bankai
 * Description: Core plugin for Bankai ecosystem providing Modular SEO, Speed, Media, and AI capabilities.
 * Version:     1.0.0
 * Author:      GCORP LLC
 * Author URI:  https://gcorp.llc
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bankai-core
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 6.0
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

// Define Constants.
define( 'BANKAI_CORE_VERSION', '1.0.0' );
define( 'BANKAI_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BANKAI_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'BANKAI_CORE_MIN_PHP_VERSION', '7.4' );
define( 'BANKAI_CORE_MIN_WP_VERSION', '6.0' );

/**
 * Check PHP and WordPress version requirements.
 *
 * @return bool True if requirements are met, false otherwise.
 */
function bankai_core_check_requirements() {
	$php_valid = version_compare( PHP_VERSION, BANKAI_CORE_MIN_PHP_VERSION, '>=' );
	$wp_valid  = version_compare( get_bloginfo( 'version' ), BANKAI_CORE_MIN_WP_VERSION, '>=' );

	if ( ! $php_valid || ! $wp_valid ) {
		add_action(
			'admin_notices',
			function() use ( $php_valid, $wp_valid ) {
				$messages = array();
				if ( ! $php_valid ) {
					/* translators: 1: Required PHP version, 2: Current PHP version */
					$messages[] = sprintf( esc_html__( 'Bankai Core requires PHP version %1$s or higher. Your current version is %2$s.', 'bankai-core' ), BANKAI_CORE_MIN_PHP_VERSION, PHP_VERSION );
				}
				if ( ! $wp_valid ) {
					/* translators: 1: Required WP version, 2: Current WP version */
					$messages[] = sprintf( esc_html__( 'Bankai Core requires WordPress version %1$s or higher. Your current version is %2$s.', 'bankai-core' ), BANKAI_CORE_MIN_WP_VERSION, get_bloginfo( 'version' ) );
				}
				echo '<div class="notice notice-error"><p>' . implode( '<br>', array_map( 'esc_html', $messages ) ) . '</p></div>';
			}
		);
		return false;
	}

	return true;
}

if ( ! bankai_core_check_requirements() ) {
	return;
}

/**
 * Bootstrap core includes and initialize modules.
 */
function bankai_core_bootstrap() {
	$includes = array(
		BANKAI_CORE_PATH . 'includes/class-module-switcher.php',
		BANKAI_CORE_PATH . 'includes/class-rest-api.php',
		BANKAI_CORE_PATH . 'includes/class-admin-menu.php',
		BANKAI_CORE_PATH . 'includes/class-editor-sidebar.php',
		BANKAI_CORE_PATH . 'includes/modules/media/class-media-module.php',
		BANKAI_CORE_PATH . 'includes/modules/seo/class-seo-module.php',
		BANKAI_CORE_PATH . 'includes/modules/speed/class-speed-module.php',
		BANKAI_CORE_PATH . 'includes/modules/ai/class-ai-module.php',
	);

	foreach ( $includes as $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

bankai_core_bootstrap();
