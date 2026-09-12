<?php
/**
 * Media Lazy Load & YouTube Preview Poster Module.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Bankai_Media_Lazyload {

	public static function init() {
		add_filter( 'the_content', array( __CLASS__, 'process_content_lazyload' ), 99 );
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'process_image_lazyload' ), 99 );
	}

	public static function process_content_lazyload( $content ) {
		if ( is_admin() || empty( $content ) ) {
			return $content;
		}

		// YouTube iframe poster replacement
		$content = preg_replace_callback(
			'/<iframe[^>]+src=["'](?:https?:)?\/\/(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:embed\/)?([a-zA-Z0-9_-]+)["'][^>]*><\/iframe>/i',
			array( __CLASS__, 'replace_youtube_iframe' ),
			$content
		);

		// Image lazy loading
		$content = preg_replace( '/<img(?!.*loading=)([^>]+)>/i', '<img loading="lazy" $1>', $content );

		return $content;
	}

	public static function process_image_lazyload( $html ) {
		if ( is_admin() || empty( $html ) ) {
			return $html;
		}
		if ( strpos( $html, 'loading=' ) === false ) {
			$html = str_replace( '<img ', '<img loading="lazy" ', $html );
		}
		return $html;
	}

	public static function replace_youtube_iframe( $matches ) {
		$video_id = $matches[1];
		$poster_url = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
		$embed_url  = "https://www.youtube.com/embed/{$video_id}?autoplay=1";

		return '<div class="bankai-yt-lazy" data-embed="' . esc_attr( $embed_url ) . '" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#000;cursor:pointer;">' .
			'<img src="' . esc_url( $poster_url ) . '" alt="YouTube Video" style="width:100%;height:100%;position:absolute;top:0;left:0;object-fit:cover;opacity:0.8;">' .
			'<button style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#ff0000;color:#fff;border:none;padding:12px 24px;border-radius:6px;font-weight:bold;cursor:pointer;">▶ Play</button>' .
			'</div>';
	}
}

Bankai_Media_Lazyload::init();
