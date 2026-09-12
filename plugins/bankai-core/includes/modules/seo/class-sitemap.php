<?php
/**
 * Dynamic XML Sitemap Generator.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Sitemap
 */
class Bankai_Sitemap {

	/**
	 * Initialize sitemap rewrite rules and query vars.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'render_sitemap' ) );
	}

	/**
	 * Add sitemap rewrite rule.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?bankai_sitemap=1', 'top' );
	}

	/**
	 * Register sitemap query variable.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'bankai_sitemap';
		return $vars;
	}

	/**
	 * Render XML Sitemap output dynamically.
	 */
	public static function render_sitemap() {
		if ( get_query_var( 'bankai_sitemap' ) ) {
			header( 'Content-Type: text/xml; charset=utf-8' );
			echo '<?xml version="1.0" encoding="UTF-8"?>' . "
";
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "
";

			// Homepage
			echo '  <url>' . "
";
			echo '    <loc>' . esc_url( home_url( '/' ) ) . '</loc>' . "
";
			echo '    <changefreq>daily</changefreq>' . "
";
			echo '    <priority>1.0</priority>' . "
";
			echo '  </url>' . "
";

			// Published Posts
			$posts = get_posts(
				array(
					'numberposts' => 100,
					'post_status' => 'publish',
					'post_type'   => array( 'post', 'page' ),
				)
			);

			foreach ( $posts as $post ) {
				echo '  <url>' . "
";
				echo '    <loc>' . esc_url( get_permalink( $post ) ) . '</loc>' . "
";
				echo '    <lastmod>' . esc_html( get_the_modified_date( 'c', $post ) ) . '</lastmod>' . "
";
				echo '    <changefreq>weekly</changefreq>' . "
";
				echo '    <priority>0.8</priority>' . "
";
				echo '  </url>' . "
";
			}

			echo '</urlset>' . "
";
			exit;
		}
	}
}
