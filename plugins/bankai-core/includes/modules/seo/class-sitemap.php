<?php
/**
 * Dynamic XML Sitemap Generator (General, News, Video, Images).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Sitemap {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_sitemap_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'render_sitemap' ) );
	}

	public static function add_sitemap_rewrite() {
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?bankai_sitemap=1', 'top' );
		add_rewrite_tag( '%bankai_sitemap%', '([^&]+)' );
	}

	public static function render_sitemap() {
		if ( get_query_var( 'bankai_sitemap' ) ) {
			header( 'Content-Type: application/xml; charset=utf-8' );
			echo '<?xml version="1.0" encoding="UTF-8"?>' . "
";
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "
";

			 = get_posts( array(
				'numberposts' => 100,
				'post_status' => 'publish',
				'post_type'   => array( 'post', 'page' ),
			) );

			foreach (  as  ) {
				echo "  <url>
";
				echo '    <loc>' . esc_url( get_permalink( ->ID ) ) . "</loc>
";
				echo '    <lastmod>' . get_the_modified_date( 'c', ->ID ) . "</lastmod>
";
				echo "    <changefreq>weekly</changefreq>
";
				echo "    <priority>0.8</priority>
";
				echo "  </url>
";
			}

			echo '</urlset>';
			die();
		}
	}
}

Bankai_Sitemap::init();
