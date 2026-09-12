<?php
/**
 * SEO Meta Generator (Title, Description, OpenGraph, Canonical, Robots, Category Base Stripper).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Meta_Generator {

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output_meta_tags' ), 1 );
		add_filter( 'category_rewrite_rules', array( __CLASS__, 'remove_category_base' ) );
	}

	public static function output_meta_tags() {
		if ( is_admin() ) {
			return;
		}

		       = self::get_title();
		 = self::get_description();
		   = self::get_canonical();
		        = Bankai_Branding::get_logo_url();

		echo "
<!-- Bankai Core Advanced SEO Meta Engine -->
";
		if (  ) {
			echo '<meta name="description" content="' . esc_attr(  ) . '">' . "
";
		}
		if (  ) {
			echo '<link rel="canonical" href="' . esc_url(  ) . '">' . "
";
		}

		// OpenGraph
		echo '<meta property="og:title" content="' . esc_attr(  ) . '">' . "
";
		if (  ) {
			echo '<meta property="og:description" content="' . esc_attr(  ) . '">' . "
";
		}
		echo '<meta property="og:url" content="' . esc_url(  ?  : home_url() ) . '">' . "
";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "
";
		echo '<meta property="og:type" content="' . ( is_single() ? 'article' : 'website' ) . '">' . "
";
		echo '<meta property="og:image" content="' . esc_url(  ) . '">' . "
";

		// Twitter
		echo '<meta name="twitter:card" content="summary_large_image">' . "
";
		echo '<meta name="twitter:title" content="' . esc_attr(  ) . '">' . "
";
		if (  ) {
			echo '<meta name="twitter:description" content="' . esc_attr(  ) . '">' . "
";
		}
		echo '<meta name="twitter:image" content="' . esc_url(  ) . '">' . "
";
		echo "<!-- / Bankai Core Advanced SEO Meta Engine -->

";
	}

	public static function get_title() {
		if ( is_singular() ) {
			 = get_post_meta( get_the_ID(), '_bankai_seo_title', true );
			if ( ! empty(  ) ) {
				return ;
			}
			return get_the_title();
		}
		return get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
	}

	public static function get_description() {
		if ( is_singular() ) {
			 = get_post_meta( get_the_ID(), '_bankai_seo_description', true );
			if ( ! empty(  ) ) {
				return ;
			}
			return wp_strip_all_tags( get_the_excerpt() );
		}
		return get_bloginfo( 'description' );
	}

	public static function get_canonical() {
		if ( is_singular() ) {
			return get_permalink();
		}
		return home_url( ['REQUEST_URI'] );
	}

	public static function remove_category_base(  ) {
		 = array();
		 = get_categories( array( 'hide_empty' => false ) );
		foreach (  as  ) {
			 = ->slug;
			if ( ->parent != 0 ) {
				 = get_category_parents( ->parent, false, '/', true ) . ;
			}
			['(' .  . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=[1]&feed=[2]';
			['(' .  . ')/embed/?$'] = 'index.php?category_name=[1]&embed=true';
			['(' .  . ')/(?:page/)?([0-9]+)/?$'] = 'index.php?category_name=[1]&paged=[2]';
			['(' .  . ')/?$'] = 'index.php?category_name=[1]';
		}
		return ;
	}
}

Bankai_Meta_Generator::init();
