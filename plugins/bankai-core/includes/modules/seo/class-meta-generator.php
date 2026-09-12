<?php
/**
 * Dynamic Meta Tags Generator.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Meta_Generator
 */
class Bankai_Meta_Generator {

	/**
	 * Register wp_head hook.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'render_meta_tags' ), 1 );
	}

	/**
	 * Render dynamic SEO Meta Tags in <head>.
	 */
	public static function render_meta_tags() {
		$title       = self::get_title();
		$description = self::get_description();
		$canonical   = self::get_canonical_url();
		$og_image    = self::get_og_image();

		echo '<!-- Bankai SEO Module -->
';
		if ( ! empty( $description ) ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '" />
';
		}

		if ( ! empty( $canonical ) ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />
';
		}

		// OpenGraph Tags
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />
';
		if ( ! empty( $description ) ) {
			echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />
';
		}
		echo '<meta property="og:url" content="' . esc_url( $canonical ) . '" />
';
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />
';
		echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '" />
';

		if ( ! empty( $og_image ) ) {
			echo '<meta property="og:image" content="' . esc_url( $og_image ) . '" />
';
		}

		// Twitter Card Tags
		echo '<meta name="twitter:card" content="summary_large_image" />
';
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />
';
		if ( ! empty( $description ) ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />
';
		}
		if ( ! empty( $og_image ) ) {
			echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '" />
';
		}
		echo '<!-- /Bankai SEO Module -->
';
	}

	/**
	 * Get page title.
	 */
	private static function get_title() {
		if ( is_singular() ) {
			$post_id   = get_the_ID();
			$custom_title = get_post_meta( $post_id, '_bankai_seo_title', true );
			if ( ! empty( $custom_title ) ) {
				return $custom_title;
			}
			return get_the_title( $post_id );
		}
		return get_bloginfo( 'name' );
	}

	/**
	 * Get meta description.
	 */
	private static function get_description() {
		if ( is_singular() ) {
			$post_id     = get_the_ID();
			$custom_desc = get_post_meta( $post_id, '_bankai_seo_description', true );
			if ( ! empty( $custom_desc ) ) {
				return $custom_desc;
			}
			$post = get_post( $post_id );
			if ( $post && ! empty( $post->post_excerpt ) ) {
				return wp_strip_all_tags( $post->post_excerpt );
			}
			if ( $post && ! empty( $post->post_content ) ) {
				return wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '...' );
			}
		}
		return get_bloginfo( 'description' );
	}

	/**
	 * Get canonical URL.
	 */
	private static function get_canonical_url() {
		if ( is_singular() ) {
			return get_permalink();
		}
		return home_url( '/' );
	}

	/**
	 * Get OpenGraph image URL.
	 */
	private static function get_og_image() {
		if ( is_singular() && has_post_thumbnail() ) {
			return get_the_post_thumbnail_url( get_the_ID(), 'full' );
		}
		return get_option( 'bankai_seo_default_og_image', '' );
	}
}
