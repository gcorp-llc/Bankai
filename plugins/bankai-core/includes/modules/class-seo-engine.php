<?php
/**
 * Bankai SEO Engine Module
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;

class Bankai_SEO_Engine {

    private static ?Bankai_SEO_Engine $instance = null;

    public static function instance(): Bankai_SEO_Engine {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', [$this, 'render_meta_tags'], 1);
        add_action('wp_head', [$this, 'render_schema_json_ld'], 2);
        add_action('template_redirect', [$this, 'handle_404_smart_redirect']);
    }

    public function render_meta_tags(): void {
        if (is_singular()) {
            global $post;
            $desc = get_post_meta($post->ID, '_bankai_meta_description', true);
            if (!$desc) {
                $desc = wp_trim_words(strip_tags($post->post_content), 25, '...');
            }
            if ($desc) {
                echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
            }
            echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
            echo '<meta property="og:type" content="article">' . "\n";
            echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
        }
    }

    public function render_schema_json_ld(): void {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => is_singular() ? 'Article' : 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
        ];
        if (is_singular()) {
            global $post;
            $schema['headline'] = get_the_title();
            $schema['datePublished'] = get_the_date('c');
            $schema['dateModified'] = get_the_modified_date('c');
            $schema['author'] = [
                '@type' => 'Person',
                'name' => get_the_author(),
            ];
        }
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    public function handle_404_smart_redirect(): void {
        if (is_404()) {
            // Intelligent 404 heuristic handling
            $uri = sanitize_text_field($_SERVER['REQUEST_URI'] ?? '');
            if (strpos($uri, 'wp-content/themes') !== false || strpos($uri, '.php') !== false) {
                wp_safe_redirect(home_url(), 301);
                exit;
            }
        }
    }
}
