<?php
/**
 * AI Integration Helpers for SEO and Media Modules.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_AI_Helpers
 */
class Bankai_AI_Helpers {

	/**
	 * Initialize AI Helper hooks.
	 */
	public static function init() {
		// Module integration hooks.
	}

	/**
	 * Generate SEO Meta Title and Description via AI.
	 *
	 * @param string $post_content Post content.
	 * @param string $provider     Selected AI provider.
	 * @return array|WP_Error Generated title and description array.
	 */
	public static function generate_seo_meta( $post_content, $provider = 'openai' ) {
		$prompt = "Based on the following article content, generate a concise SEO title (under 60 characters) and a compelling meta description (under 160 characters). Return format as JSON with keys 'title' and 'description':

" . substr( wp_strip_all_tags( $post_content ), 0, 2000 );

		$result = Bankai_AI_Client::query( $provider, $prompt );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$json_data = json_decode( $result['text'], true );
		if ( is_array( $json_data ) && isset( $json_data['title'], $json_data['description'] ) ) {
			return $json_data;
		}

		return array(
			'title'       => '',
			'description' => $result['text'],
		);
	}

	/**
	 * Generate image Alt Text via AI based on post title/content.
	 *
	 * @param string $context_text Post title or description.
	 * @param string $provider     Selected AI provider.
	 * @return string|WP_Error Generated Alt text.
	 */
	public static function generate_image_alt_text( $context_text, $provider = 'openai' ) {
		$prompt = "Generate a clear, descriptive image alt text (under 125 characters) appropriate for an article with this context:

" . sanitize_text_field( $context_text );

		$result = Bankai_AI_Client::query( $provider, $prompt );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return sanitize_text_field( $result['text'] );
	}
}
