<?php
/**
 * Advanced SEO Tools (llms.txt generator, robots.txt & .htaccess editor, RSS optimizer, Image SEO).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Advanced_SEO_Tools {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_llms_txt_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'render_llms_txt' ) );
		add_filter( 'the_content', array( __CLASS__, 'auto_image_seo' ) );
	}

	public static function add_llms_txt_rewrite() {
		add_rewrite_rule( '^llms\.txt$', 'index.php?bankai_llms=1', 'top' );
		add_rewrite_tag( '%bankai_llms%', '([^&]+)' );
	}

	public static function render_llms_txt() {
		if ( get_query_var( 'bankai_llms' ) ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo "# " . get_bloginfo( 'name' ) . " LLMs.txt
";
			echo "> Site Summary for AI Search Engines & LLM Crawlers

";
			echo "## Main Sections
";
			echo "- Website: " . home_url() . "
";
			echo "- Sitemap: " . home_url( '/sitemap.xml' ) . "

";
			echo "## Content Overview
";
			echo get_bloginfo( 'description' ) . "
";
			die();
		}
	}

	public static function auto_image_seo(  ) {
		if ( is_admin() || empty(  ) ) {
			return ;
		}

		 = get_the_title();
		 = preg_replace_callback( '/<img([^>]+)>/i', function(  ) use (  ) {
			 = [0];
			if ( strpos( , 'alt=' ) === false ) {
				 = str_replace( '<img ', '<img alt="' . esc_attr(  ) . '" ',  );
			}
			return ;
		},  );

		return ;
	}
}

Bankai_Advanced_SEO_Tools::init();
