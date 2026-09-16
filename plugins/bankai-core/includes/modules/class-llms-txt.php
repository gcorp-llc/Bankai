<?php
/**
 * Bankai Core - LLMS.txt Generator & AI Crawler Optimizer
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

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
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'render_llms_txt']);
        add_action('wp_ajax_bankai_generate_llms_txt', [$this, 'ajax_generate_llms_txt']);
    }

    public function add_rewrite_rules(): void {
        add_rewrite_rule('^llms\.txt$', 'index.php?bankai_llms_txt=1', 'top');
    }

    public function add_query_vars(array $vars): array {
        $vars[] = 'bankai_llms_txt';
        return $vars;
    }

    public function render_llms_txt(): void {
        if (get_query_var('bankai_llms_txt')) {
            header('Content-Type: text/plain; charset=utf-8');
            echo $this->generate_llms_content();
            die;
        }
    }

    public function generate_llms_content(): string {
        $site_name = get_bloginfo('name');
        $site_desc = get_bloginfo('description');
        $site_url  = home_url('/');

        $output  = "# " . $site_name . " - LLMS.txt\n";
        $output .= "> " . $site_desc . "\n\n";
        $output .= "## Canonical URL\n";
        $output .= "- " . $site_url . "\n\n";

        $output .= "## Core Pages & Documentation\n";
        $pages = get_pages(['number' => 10, 'post_status' => 'publish']);
        if (is_array($pages)) {
            foreach ($pages as $page) {
                $permalink = get_permalink($page->ID);
                $title     = get_the_title($page->ID);
                $output   .= "- [" . $title . "](" . $permalink . ")\n";
            }
        }

        $output .= "\n## Recent Posts\n";
        $posts = get_posts(['numberposts' => 10, 'post_status' => 'publish']);
        if (is_array($posts)) {
            foreach ($posts as $post) {
                $permalink = get_permalink($post->ID);
                $title     = get_the_title($post->ID);
                $output   .= "- [" . $title . "](" . $permalink . ")\n";
            }
        }

        $output .= "\n# Generated dynamically by Bankai Core Engine\n";

        return $output;
    }

    public function ajax_generate_llms_txt(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $content = $this->generate_llms_content();
        wp_send_json_success([
            'message' => __('llms.txt successfully generated.', 'bankai-core'),
            'content' => $content,
            'url'     => home_url('/llms.txt')
        ]);
    }
}
