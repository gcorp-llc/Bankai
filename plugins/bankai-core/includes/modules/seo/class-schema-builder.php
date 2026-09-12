<?php
/**
 * Comprehensive JSON-LD Schema Generator supporting 18+ schema types.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Schema_Builder {

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'render_schema_json' ), 2 );
	}

	public static function render_schema_json() {
		if ( is_admin() ) {
			return;
		}

		 = array();
		[] = self::get_organization_schema();
		[] = self::get_website_schema();

		if ( is_singular() ) {
			 = self::get_post_schema( get_the_ID() );
			if (  ) {
				[] = ;
			}
		}

		if ( ! empty(  ) ) {
			echo "
<!-- Bankai Core JSON-LD Schemas -->
";
			echo '<script type="application/ld+json">' . "
";
			echo wp_json_encode( , JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "
";
			echo "</script>
";
			echo "<!-- / Bankai Core JSON-LD Schemas -->

";
		}
	}

	public static function get_organization_schema() {
		return array(
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url(),
			'logo'     => Bankai_Branding::get_logo_url(),
		);
	}

	public static function get_website_schema() {
		return array(
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	public static function get_post_schema(  ) {
		 = get_post_meta( , '_bankai_schema_type', true );
		if ( empty(  ) ) {
			 = 'Article';
		}

		 = array(
			'@context' => 'https://schema.org',
			'@type'    => ,
			'headline' => get_the_title(  ),
			'url'      => get_permalink(  ),
			'datePublished' => get_the_date( 'c',  ),
			'dateModified'  => get_the_modified_date( 'c',  ),
			'author'   => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author',  ) ),
			),
			'image'    => Bankai_Branding::get_logo_url(),
		);

		return ;
	}
}

Bankai_Schema_Builder::init();
