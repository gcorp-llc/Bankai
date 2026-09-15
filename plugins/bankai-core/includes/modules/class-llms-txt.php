<?php
/**
 * Bankai LLMs.txt & Crawler Manifest Module
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;

class Bankai_LLMS_Txt {

    private static ?Bankai_LLMS_Txt $instance = null;

    public static function instance(): Bankai_LLMS_Txt {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_action('template_redirect', [$this, 'render_llms_txt']);
    }

    public function add_rewrite_rules(): void {
        add_rewrite_rule('^llms\.txt$', 'index.php?bankai_llms=1', 'top');
        add_rewrite_rule('^llms-full\.txt$', 'index.php?bankai_llms=full', 'top');
    }

    public function render_llms_txt(): void {
        $uri = sanitize_text_field($_SERVER['REQUEST_URI'] ?? '');
        if (strpos($uri, '/llms.txt') !== false) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "# " . get_bloginfo('name') . "\n";
            echo "> " . get_bloginfo('description') . "\n\n";
            echo "## Core Sections\n";
            echo "- [Home](" . home_url('/') . "): Main landing page.\n";
            $recent = get_posts(['numberposts' => 15, 'post_status' => 'publish']);
            if ($recent) {
                echo "\n## Recent Content\n";
                foreach ($recent as $post) {
                    echo "- [" . esc_html($post->post_title) . "](" . get_permalink($post->ID) . ")\n";
                }
            }
            exit;
        }
    }
}
