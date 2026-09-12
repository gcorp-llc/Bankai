<?php
/**
 * Redirections Manager (301, 302, 307, 410, 451) with 404 Logging.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Redirections {

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'check_redirections' ), 1 );
	}

	public static function check_redirections() {
		if ( is_admin() ) {
			return;
		}

		 = isset( ['REQUEST_URI'] ) ? sanitize_text_field( ['REQUEST_URI'] ) : '';
		   = get_option( 'bankai_seo_redirects', array() );

		if ( isset( [  ] ) ) {
			 = [  ]['target'];
			   = isset( [  ]['code'] ) ? intval( [  ]['code'] ) : 301;
			wp_redirect( ,  );
			die();
		}

		if ( is_404() ) {
			self::log_404(  );
		}
	}

	public static function log_404(  ) {
		 = get_option( 'bankai_404_logs', array() );
		[  ] = isset( [  ] ) ? [  ] + 1 : 1;
		if ( count(  ) > 200 ) {
			 = array_slice( , -100, 100, true );
		}
		update_option( 'bankai_404_logs',  );
	}
}

Bankai_Redirections::init();
