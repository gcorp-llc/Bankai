<?php
/**
 * Bankai Core - llms.txt Generator & AI Crawler Optimizer
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

class Bankai_LLMS_Txt
{
    private static ?Bankai_LLMS_Txt $instance = null;

    public static function instance(): Bankai_LLMS_Txt
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function get_instance(): Bankai_LLMS_Txt
    {
        return self::instance();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('llms_txt')) {
            return;
        }

        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'render_llms_txt'], 0);
        add_action('wp_ajax_bankai_generate_llms_txt', [$this, 'ajax_generate_llms_txt']);
    }

    public function add_rewrite_rules(): void
    {
        add_rewrite_rule('^llms\.txt/?$', 'index.php?bankai_llms_txt=basic', 'top');
        add_rewrite_rule('^llms-full\.txt/?$', 'index.php?bankai_llms_txt=full', 'top');

        $version = defined('BANKAI_CORE_VERSION') ? (string) BANKAI_CORE_VERSION : '1';
        if ((string) get_option('bankai_llms_rewrite_v', '') !== $version) {
            flush_rewrite_rules(false);
            update_option('bankai_llms_rewrite_v', $version, false);
        }
    }

    public function add_query_vars(array $vars): array
    {
        $vars[] = 'bankai_llms_txt';
        return $vars;
    }

    public function render_llms_txt(): void
    {
        $mode = (string) get_query_var('bankai_llms_txt');

        // Fallback for installations where another plugin/theme interferes with rewrite vars.
        if ($mode === '') {
            $request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
            $path = (string) (wp_parse_url($request_uri, PHP_URL_PATH) ?: '');
            $path = untrailingslashit($path);

            if (preg_match('#/llms-full\.txt$#i', $path)) {
                $mode = 'full';
            } elseif (preg_match('#/llms\.txt$#i', $path)) {
                $mode = 'basic';
            }
        }

        if (!in_array($mode, ['basic', '1', 'full'], true)) {
            return;
        }

        nocache_headers();
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        echo $this->generate_llms_content($mode === 'full');
        exit;
    }

    /**
     * Generate the public llms.txt document.
     *
     * @param bool $full Include a larger content index and taxonomy links.
     */
    public function generate_llms_content(bool $full = false): string
    {
        $site_name = $this->clean_line((string) get_bloginfo('name'));
        $site_desc = $this->clean_line((string) get_bloginfo('description'));
        $site_url  = esc_url_raw(home_url('/'));

        $output  = '# ' . ($site_name !== '' ? $site_name : 'Website') . "\n";
        if ($site_desc !== '') {
            $output .= '> ' . $site_desc . "\n";
        }

        $output .= "\n## Canonical\n";
        $output .= '- ' . $site_url . "\n";

        $output .= "\n## Important Pages\n";
        $pages = get_pages([
            'number'      => $full ? 40 : 12,
            'post_status' => 'publish',
            'sort_column' => 'menu_order,post_title',
            'hierarchical' => false,
        ]);

        foreach ((array) $pages as $page) {
            $line = $this->markdown_link(
                (string) get_the_title($page->ID),
                (string) get_permalink($page->ID)
            );
            if ($line !== '') {
                $output .= '- ' . $line . "\n";
            }
        }

        $output .= "\n## Recent Content\n";
        $public_types = get_post_types(['public' => true], 'names');
        unset($public_types['attachment']);

        // Keep the index focused on canonical public content, not pages already listed above.
        $public_types = array_values(array_diff($public_types, ['page']));
        if (!$public_types) {
            $public_types = ['post'];
        }

        $posts = get_posts([
            'post_type'              => $public_types,
            'post_status'            => 'publish',
            'numberposts'             => $full ? 50 : 10,
            'orderby'                 => 'date',
            'order'                   => 'DESC',
            'ignore_sticky_posts'     => true,
            'no_found_rows'           => true,
            'suppress_filters'        => false,
            'update_post_meta_cache'  => false,
            'update_post_term_cache'  => false,
        ]);

        foreach ((array) $posts as $post) {
            $line = $this->markdown_link(
                (string) get_the_title($post->ID),
                (string) get_permalink($post->ID)
            );
            if ($line !== '') {
                $output .= '- ' . $line . "\n";
            }
        }

        if ($full) {
            $taxonomies = get_taxonomies(['public' => true], 'objects');
            foreach ($taxonomies as $taxonomy) {
                if (empty($taxonomy->name) || in_array($taxonomy->name, ['post_format'], true)) {
                    continue;
                }

                $terms = get_terms([
                    'taxonomy'   => $taxonomy->name,
                    'hide_empty' => true,
                    'number'     => 30,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ]);

                if (is_wp_error($terms) || empty($terms)) {
                    continue;
                }

                $heading = $this->clean_line((string) ($taxonomy->labels->name ?? $taxonomy->name));
                $output .= "\n## " . ($heading !== '' ? $heading : 'Topics') . "\n";

                foreach ($terms as $term) {
                    $url = get_term_link($term);
                    if (is_wp_error($url)) {
                        continue;
                    }
                    $line = $this->markdown_link((string) $term->name, (string) $url);
                    if ($line !== '') {
                        $output .= '- ' . $line . "\n";
                    }
                }
            }
        }

        $output .= "\n---\n";
        $output .= '<!-- Generated by Bankai Core. Publicly published content only. -->' . "\n";

        /**
         * Allow extensions to append carefully curated public resources.
         *
         * @param string $output Current llms.txt content.
         * @param bool   $full   Whether this is llms-full.txt.
         */
        $output = (string) apply_filters('bankai_llms_txt_content', $output, $full);

        return $output;
    }

    private function clean_line(string $value): string
    {
        $value = wp_strip_all_tags($value, true);
        $value = preg_replace('/[\r\n\t]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s{2,}/u', ' ', $value) ?? $value;
        return trim($value);
    }

    private function markdown_link(string $title, string $url): string
    {
        $title = $this->clean_line($title);
        $url   = esc_url_raw($url);

        if ($title === '' || $url === '') {
            return '';
        }

        // Escape Markdown delimiters in labels and parentheses in destinations.
        $title = str_replace(['\\', '[', ']', '`'], ['\\\\', '\\[', '\\]', '\\`'], $title);
        $url   = str_replace([' ', ')', '('], ['%20', '%29', '%28'], $url);

        return '[' . $title . '](' . $url . ')';
    }

    public function ajax_generate_llms_txt(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }

        wp_send_json_success([
            'message'  => __('llms.txt successfully generated.', 'bankai-core'),
            'content'  => $this->generate_llms_content(false),
            'full_content' => $this->generate_llms_content(true),
            'url'      => home_url('/llms.txt'),
            'full_url' => home_url('/llms-full.txt'),
        ]);
    }
}
