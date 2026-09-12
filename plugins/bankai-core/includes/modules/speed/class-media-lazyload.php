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

    /**
     * Initialize media optimization hooks.
     */
    public static function init() {
        add_filter( 'the_content', array( __CLASS__, 'process_content_lazyload' ), 99 );
        add_filter( 'post_thumbnail_html', array( __CLASS__, 'process_image_lazyload' ), 99 );
        add_action( 'wp_footer', array( __CLASS__, 'render_lazy_yt_script' ) );
    }

    /**
     * Process content for YouTube lazy load posters and missing image lazy loading attributes.
     *
     * @param string $content Post content.
     * @return string Processed content.
     */
    public static function process_content_lazyload( $content ) {
        if ( is_admin() || empty( $content ) ) {
            return $content;
        }

        // YouTube iframe poster replacement with escaped quotes in regex
        $content = preg_replace_callback(
            '/<iframe[^>]+src=["\'](?:https?:)?\/\/(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:embed\/)?([a-zA-Z0-9_-]+)["\'][^>]*><\/iframe>/i',
            array( __CLASS__, 'replace_youtube_iframe' ),
            $content
        );

        // Add loading="lazy" to images that don't already have it
        $content = preg_replace( '/<img(?!.*loading=)([^>]+)>/i', '<img loading="lazy" $1>', $content );

        return $content;
    }

    /**
     * Process featured image html for lazy loading.
     *
     * @param string $html Image HTML tag.
     * @return string Modified HTML tag.
     */
    public static function process_image_lazyload( $html ) {
        if ( is_admin() || empty( $html ) ) {
            return $html;
        }

        if ( strpos( $html, 'loading=' ) === false ) {
            $html = str_replace( '<img ', '<img loading="lazy" ', $html );
        }

        return $html;
    }

    /**
     * Callback to convert YouTube iFrame to lightweight poster with play button.
     *
     * @param array $matches Regex matches.
     * @return string Generated HTML wrapper.
     */
    public static function replace_youtube_iframe( $matches ) {
        $video_id   = esc_attr( $matches[1] );
        $poster_url = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
        $embed_url  = "https://www.youtube.com/embed/{$video_id}?autoplay=1";

        return '<div class="bankai-yt-lazy" data-embed="' . esc_attr( $embed_url ) . '" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#000;cursor:pointer;border-radius:8px;">' .
            '<img src="' . esc_url( $poster_url ) . '" alt="YouTube Video" loading="lazy" style="width:100%;height:100%;position:absolute;top:0;left:0;object-fit:cover;opacity:0.85;">' .
            '<button aria-label="Play Video" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#ff0000;color:#fff;border:none;padding:12px 24px;border-radius:6px;font-weight:bold;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,0.3);">▶ Play</button>' .
            '</div>';
    }

    /**
     * Client-side Handler for loading real YouTube iFrame on user interaction.
     */
    public static function render_lazy_yt_script() {
        if ( is_admin() ) {
            return;
        }
        ?>
        <script id="bankai-lazy-yt-js">
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.bankai-yt-lazy').forEach(function(el) {
                    el.addEventListener('click', function() {
                        var embedUrl = this.getAttribute('data-embed');
                        if (embedUrl) {
                            var iframe = document.createElement('iframe');
                            iframe.setAttribute('src', embedUrl);
                            iframe.setAttribute('frameborder', '0');
                            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
                            iframe.setAttribute('allowfullscreen', '1');
                            iframe.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;';
                            this.innerHTML = '';
                            this.appendChild(iframe);
                        }
                    });
                });
            });
        </script>
        <?php
    }
}

Bankai_Media_Lazyload::init();