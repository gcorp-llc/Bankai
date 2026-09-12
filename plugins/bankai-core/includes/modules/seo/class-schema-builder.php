<?php
/**
 * JSON-LD Schema Builder.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Schema_Builder
 */
class Bankai_Schema_Builder {

	/**
	 * Register wp_head hook for schema injection.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'render_schema' ), 5 );
	}

	/**
	 * Output JSON-LD schema payload.
	 */
	public static function render_schema() {
		$schemas = array();

		// Website & Organization Schema on Homepage
		if ( is_front_page() || is_home() ) {
			$schemas[] = array(
				'@context' => 'https://schema.org',
				'@type'    => 'WebSite',
				'name'     => get_bloginfo( 'name' ),
				'url'      => home_url( '/' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => home_url( '/?s={search_term_string}' ),
					'query-input' => 'required name=search_term_string',
				),
			);

			$schemas[] = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Organization',
				'name'     => get_bloginfo( 'name' ),
				'url'      => home_url( '/' ),
				'logo'     => get_option( 'site_logo' ) ? wp_get_attachment_url( get_option( 'site_logo' ) ) : '',
			);
		}

		// Article Schema on Posts
		if ( is_single() ) {
			$post = get_post();
			$schemas[] = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'Article',
				'headline'      => get_the_title(),
				'datePublished' => get_the_date( 'c', $post ),
				'dateModified'  => get_the_modified_date( 'c', $post ),
				'author'        => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', $post->post_author ),
				),
				'image'         => has_post_thumbnail( $post->ID ) ? array( get_the_post_thumbnail_url( $post->ID, 'full' ) ) : array(),
			);
		}

		if ( ! empty( $schemas ) ) {
			foreach ( $schemas as $schema ) {
				echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>
';
			}
		}
	}
}
