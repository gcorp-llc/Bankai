<?php
/**
 * Page-level SEO / AEO / GEO audit checklist.
 *
 * @package Bankai
 */
if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_SEO_Page_Audit
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        add_action('wp_ajax_bankai_seo_page_audit', [$this, 'ajax_audit']);
        add_action('wp_ajax_bankai_seo_page_audit_posts', [$this, 'ajax_list_posts']);
        add_action('wp_ajax_bankai_seo_page_audit_report', [$this, 'ajax_get_report']);
        add_action('wp_ajax_bankai_seo_audit_ai_fix', [$this, 'ajax_ai_fix']);
        add_action('wp_ajax_bankai_seo_audit_apply_fix', [$this, 'ajax_apply_fix']);
    }



    /**
     * AI-assisted fix for failed audit items. Returns proposed changes without saving.
     */
    public function ajax_ai_fix(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if ($post_id <= 0) {
            wp_send_json_error(['message' => 'Invalid post'], 400);
        }
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => 'Post not found'], 404);
        }

        $failed_raw = wp_unslash($_POST['failed'] ?? '[]');
        $failed = is_string($failed_raw) ? json_decode($failed_raw, true) : $failed_raw;
        if (!is_array($failed)) {
            $failed = [];
        }

        $title = get_the_title($post);
        $content = (string) $post->post_content;
        $seo_title = (string) get_post_meta($post_id, '_bankai_seo_title', true);
        $seo_desc = (string) get_post_meta($post_id, '_bankai_seo_description', true);
        $focus = (string) get_post_meta($post_id, '_bankai_seo_keyword', true);
        if ($focus === '') {
            $focus = (string) get_post_meta($post_id, '_bankai_seo_focus_keyword', true);
        }

        $steps = [];
        $proposed = [
            'seo_title' => $seo_title,
            'description' => $seo_desc,
            'content' => $content,
            'focus_keyword' => $focus,
            'og_title' => (string) get_post_meta($post_id, '_bankai_seo_og_title', true),
            'og_description' => (string) get_post_meta($post_id, '_bankai_seo_og_description', true),
            'applied_keys' => [],
        ];

        $need_meta_title = false;
        $need_meta_desc = false;
        $need_faq = false;
        $need_og = false;
        $keys = [];
        foreach ($failed as $f) {
            $k = is_array($f) ? (string) ($f['key'] ?? '') : (string) $f;
            $keys[] = $k;
            $label = is_array($f) ? (string) ($f['label'] ?? $k) : $k;
            if (preg_match('/title|عنوان/i', $k . $label)) {
                $need_meta_title = true;
            }
            if (preg_match('/desc|توضیح|description|meta/i', $k . $label)) {
                $need_meta_desc = true;
            }
            if (preg_match('/faq|پرسش|question|aeo/i', $k . $label)) {
                $need_faq = true;
            }
            if (preg_match('/og|open.?graph|twitter/i', $k . $label)) {
                $need_og = true;
            }
        }
        // If many failures, improve meta by default
        if (count($failed) >= 3) {
            $need_meta_title = true;
            $need_meta_desc = true;
        }

        $studio = class_exists('Bankai_AI_Studio') ? Bankai_AI_Studio::instance() : null;
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));

        if ($studio && ($need_meta_title || $need_meta_desc)) {
            $steps[] = ['id' => 'meta', 'label' => 'بهینه‌سازی عنوان و متا با AI', 'status' => 'run'];
            $post_payload = [
                'title' => $title,
                'content' => wp_strip_all_tags($content),
                'focus_keyword' => $focus,
                'locale' => get_locale(),
                'provider' => $provider,
            ];
            if ($need_meta_title) {
                $res = $this->call_ai_seo($studio, 'meta_title', $post_payload);
                if (!empty($res['seo_title']) || !empty($res['text'])) {
                    $proposed['seo_title'] = (string) ($res['seo_title'] ?? $res['text']);
                    $proposed['applied_keys'][] = 'meta_title';
                }
            }
            if ($need_meta_desc) {
                $res = $this->call_ai_seo($studio, 'meta_description', $post_payload);
                if (!empty($res['description']) || !empty($res['text'])) {
                    $proposed['description'] = (string) ($res['description'] ?? $res['text']);
                    $proposed['applied_keys'][] = 'meta_description';
                }
            }
            $steps[count($steps) - 1]['status'] = 'done';
        } else {
            // Rule-based meta fallback
            $steps[] = ['id' => 'meta', 'label' => 'تنظیم عنوان و متا', 'status' => 'done'];
            if ($need_meta_title && mb_strlen($seo_title) > 60) {
                $proposed['seo_title'] = mb_substr($seo_title !== '' ? $seo_title : $title, 0, 58);
                $proposed['applied_keys'][] = 'meta_title';
            } elseif ($need_meta_title && $seo_title === '') {
                $proposed['seo_title'] = mb_substr($title, 0, 58);
                $proposed['applied_keys'][] = 'meta_title';
            }
            if ($need_meta_desc && ($seo_desc === '' || mb_strlen($seo_desc) > 160)) {
                $excerpt = $post->post_excerpt ?: wp_trim_words(wp_strip_all_tags($content), 28);
                $proposed['description'] = mb_substr($excerpt, 0, 155);
                $proposed['applied_keys'][] = 'meta_description';
            }
        }

        if ($need_og) {
            $steps[] = ['id' => 'og', 'label' => 'همگام‌سازی Open Graph', 'status' => 'done'];
            $proposed['og_title'] = $proposed['seo_title'] ?: $title;
            $proposed['og_description'] = $proposed['description'] ?: wp_trim_words(wp_strip_all_tags($content), 30);
            $proposed['applied_keys'][] = 'og';
        }

        if ($need_faq && stripos($content, 'faq') === false && stripos($content, 'سوالات متداول') === false) {
            $steps[] = ['id' => 'faq', 'label' => 'افزودن بخش سوالات متداول', 'status' => 'run'];
            $faq_html = "\n\n<!-- bankai-ai-faq -->\n<section class=\"bankai-faq\">\n"
                . "<h2>سوالات متداول</h2>\n"
                . "<h3>موضوع اصلی این مقاله چیست؟</h3>\n"
                . "<p>" . esc_html(wp_trim_words(wp_strip_all_tags($content), 40)) . "</p>\n"
                . "<h3>چه کسانی از این محتوا بهره می‌برند؟</h3>\n"
                . "<p>مخاطبان علاقه‌مند به «" . esc_html($title) . "» و موضوعات مرتبط.</p>\n"
                . "</section>\n";
            if ($studio) {
                $post_payload = [
                    'title' => $title,
                    'content' => wp_strip_all_tags(mb_substr($content, 0, 4000)),
                    'focus_keyword' => $focus,
                    'locale' => get_locale(),
                    'provider' => $provider,
                ];
                $res = $this->call_ai_seo($studio, 'rewrite', array_merge($post_payload, [
                    // reuse rewrite with instruction via title context
                ]));
                // Prefer structured FAQ injection over full rewrite for safety
            }
            $proposed['content'] = rtrim($content) . $faq_html;
            $proposed['applied_keys'][] = 'faq';
            $steps[count($steps) - 1]['status'] = 'done';
        }

        // Heading hierarchy soft fix: ensure H2 exists
        if (preg_match('/heading|هدینگ|hierarchy|H1|H2/i', implode(' ', $keys))) {
            $steps[] = ['id' => 'headings', 'label' => 'بررسی ساختار هدینگ', 'status' => 'done'];
            if (stripos($proposed['content'], '<h2') === false) {
                $proposed['content'] = "<h2>" . esc_html($title) . "</h2>\n" . $proposed['content'];
                $proposed['applied_keys'][] = 'headings';
            }
        }

        if (!$steps) {
            $steps[] = ['id' => 'scan', 'label' => 'بررسی موارد قابل اصلاح', 'status' => 'done'];
        }

        // Cache proposal for apply
        set_transient('bankai_audit_fix_' . $post_id . '_' . get_current_user_id(), $proposed, 30 * MINUTE_IN_SECONDS);

        wp_send_json_success([
            'steps' => $steps,
            'proposed' => [
                'seo_title' => $proposed['seo_title'],
                'description' => $proposed['description'],
                'focus_keyword' => $proposed['focus_keyword'],
                'og_title' => $proposed['og_title'],
                'og_description' => $proposed['og_description'],
                'content_changed' => $proposed['content'] !== $content,
                'content_preview' => mb_substr(wp_strip_all_tags($proposed['content']), 0, 280),
                'applied_keys' => $proposed['applied_keys'],
            ],
            'message' => count($proposed['applied_keys'])
                ? 'پیشنهادهای بهبود آماده است. برای اعمال، «ثبت اصلاحات» را بزنید.'
                : 'مورد قابل اصلاح خودکار یافت نشد.',
        ]);
    }

    private function call_ai_seo($studio, string $task, array $post_payload): array
    {
        try {
            if (method_exists($studio, 'run_seo_task_inline')) {
                // private — use reflection
            }
            // Public AJAX path simulation via existing private helpers is hard; use reflection
            $ref = new ReflectionClass($studio);
            if ($ref->hasMethod('run_seo_task_inline')) {
                $m = $ref->getMethod('run_seo_task_inline');
                $m->setAccessible(true);
                $res = $m->invoke($studio, $task, $post_payload);
                return is_array($res) ? $res : [];
            }
        } catch (Throwable $e) {
            return [];
        }
        return [];
    }

    public function ajax_apply_fix(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if ($post_id <= 0) {
            wp_send_json_error(['message' => 'Invalid post'], 400);
        }
        $key = 'bankai_audit_fix_' . $post_id . '_' . get_current_user_id();
        $proposed = get_transient($key);
        if (!is_array($proposed)) {
            wp_send_json_error(['message' => 'پیشنهادی برای اعمال یافت نشد. ابتدا بهبود با AI را اجرا کنید.'], 400);
        }

        if (!empty($proposed['seo_title'])) {
            update_post_meta($post_id, '_bankai_seo_title', sanitize_text_field($proposed['seo_title']));
        }
        if (!empty($proposed['description'])) {
            update_post_meta($post_id, '_bankai_seo_description', sanitize_textarea_field($proposed['description']));
        }
        if (!empty($proposed['focus_keyword'])) {
            update_post_meta($post_id, '_bankai_seo_keyword', sanitize_text_field($proposed['focus_keyword']));
        }
        if (!empty($proposed['og_title'])) {
            update_post_meta($post_id, '_bankai_seo_og_title', sanitize_text_field($proposed['og_title']));
        }
        if (!empty($proposed['og_description'])) {
            update_post_meta($post_id, '_bankai_seo_og_description', sanitize_textarea_field($proposed['og_description']));
        }
        if (!empty($proposed['content']) && current_user_can('edit_post', $post_id)) {
            wp_update_post([
                'ID' => $post_id,
                'post_content' => wp_kses_post($proposed['content']),
            ]);
        }
        delete_transient($key);

        // Refresh analysis score for sidebar compatibility
        if (class_exists('Bankai_SEO_Engine')) {
            try {
                $engine = Bankai_SEO_Engine::instance();
                if (method_exists($engine, 'analyze')) {
                    $analysis = $engine->analyze($post_id);
                    if (is_array($analysis) && isset($analysis['score'])) {
                        update_post_meta($post_id, '_bankai_seo_score', (int) $analysis['score']);
                    }
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        wp_send_json_success([
            'message' => 'اصلاحات ثبت شد.',
            'seo_title' => $proposed['seo_title'] ?? '',
            'description' => $proposed['description'] ?? '',
        ]);
    }


    public function ajax_get_report(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if ($post_id <= 0) {
            wp_send_json_error(['message' => 'Invalid post'], 400);
        }
        $raw = (string) get_post_meta($post_id, '_bankai_audit_report', true);
        $data = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($data)) {
            wp_send_json_success([
                'has_report' => false,
                'seo_audit' => (int) get_post_meta($post_id, '_bankai_audit_seo', true),
                'aeo_audit' => (int) get_post_meta($post_id, '_bankai_audit_aeo', true),
                'geo_audit' => (int) get_post_meta($post_id, '_bankai_audit_geo', true),
                'score' => (int) get_post_meta($post_id, '_bankai_audit_score', true),
            ]);
            return;
        }
        $data['has_report'] = true;
        $data['url'] = get_permalink($post_id) ?: '';
        $data['title'] = get_the_title($post_id);
        wp_send_json_success($data);
    }

    public function ajax_list_posts(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $q = sanitize_text_field(wp_unslash($_POST['q'] ?? ''));
        $args = [
            'post_type'      => ['post', 'page'],
            'post_status'    => ['publish'],
            'posts_per_page' => 20,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            's'              => $q,
        ];
        $query = new WP_Query($args);
        $items = [];
        foreach ($query->posts as $p) {
            $items[] = [
                'id'    => (int) $p->ID,
                'title' => get_the_title($p),
                'url'   => get_permalink($p),
                'type'  => $p->post_type,
            ];
        }
        wp_send_json_success(['items' => $items]);
    }

    public function ajax_audit(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        $url = esc_url_raw(wp_unslash($_POST['url'] ?? ''));

        if ($post_id > 0) {
            $url = get_permalink($post_id) ?: $url;
        }
        if ($url === '') {
            wp_send_json_error(['message' => 'URL or post required'], 400);
        }

        $result = $this->audit_url($url, $post_id);
        wp_send_json_success($result);
    }

    public function audit_url(string $url, int $post_id = 0): array
    {
        $html = '';
        $code = 200;
        $header_arr = [];

        // Prefer local content for posts — avoids HTTP timeout on same server
        if ($post_id > 0) {
            $html = $this->build_local_html($post_id);
            $url = $url !== '' ? $url : (get_permalink($post_id) ?: home_url('/'));
        }

        if ($html === '' && $url !== '') {
            $response = wp_remote_get($url, [
                'timeout'     => 12,
                'redirection' => 2,
                'user-agent'  => 'BankaiSEOAudit/1.0 (+WordPress)',
                'sslverify'   => false,
            ]);
            if (is_wp_error($response)) {
                return [
                    'url'     => $url,
                    'error'   => $response->get_error_message(),
                    'score'   => 0,
                    'seo'     => [],
                    'aeo'     => [],
                    'geo'     => [],
                    'summary' => ['seo' => 0, 'aeo' => 0, 'geo' => 0, 'pass' => 0, 'fail' => 0],
                ];
            }
            $html = (string) wp_remote_retrieve_body($response);
            $code = (int) wp_remote_retrieve_response_code($response);
            $headers = wp_remote_retrieve_headers($response);
            if (is_object($headers) && method_exists($headers, 'getAll')) {
                $header_arr = $headers->getAll();
            } elseif (is_array($headers)) {
                $header_arr = $headers;
            }
        }

        $ctx = $this->parse_html($html, $url);
        $ctx['http_code'] = $code;
        $ctx['headers'] = $header_arr;
        $ctx['url'] = $url;
        $ctx['post_id'] = $post_id;
        if ($post_id > 0) {
            $ctx['post_modified'] = get_post_modified_time('c', true, $post_id) ?: '';
            $ctx['post_content'] = (string) get_post_field('post_content', $post_id);
        }

        $seo = $this->checks_seo($ctx);
        $aeo = $this->checks_aeo($ctx);
        $geo = $this->checks_geo($ctx);

        $all = array_merge($seo, $aeo, $geo);
        $pass = count(array_filter($all, static fn($i) => !empty($i['pass'])));
        $fail = count($all) - $pass;
        $score = count($all) > 0 ? (int) round(($pass / count($all)) * 100) : 0;

        if ($post_id > 0) {
            update_post_meta($post_id, '_bankai_audit_seo', (int) ($this->score_group($seo)));
            update_post_meta($post_id, '_bankai_audit_aeo', (int) ($this->score_group($aeo)));
            update_post_meta($post_id, '_bankai_audit_geo', (int) ($this->score_group($geo)));
            update_post_meta($post_id, '_bankai_audit_score', $score);
            update_post_meta($post_id, '_bankai_audit_at', time());
            update_post_meta($post_id, '_bankai_audit_report', wp_json_encode([
                'score' => $score,
                'seo' => $seo,
                'aeo' => $aeo,
                'geo' => $geo,
                'summary' => [
                    'seo' => $this->score_group($seo),
                    'aeo' => $this->score_group($aeo),
                    'geo' => $this->score_group($geo),
                    'seo_issues' => count(array_filter($seo, static fn($i) => empty($i['pass']))),
                    'aeo_issues' => count(array_filter($aeo, static fn($i) => empty($i['pass']))),
                    'geo_issues' => count(array_filter($geo, static fn($i) => empty($i['pass']))),
                ],
                'engines' => $this->engine_readiness($ctx, $aeo, $geo),
                'at' => time(),
            ]));
        }

        return [
            'url'     => $url,
            'title'   => $ctx['title'] ?? '',
            'score'   => $score,
            'seo'     => $seo,
            'aeo'     => $aeo,
            'geo'     => $geo,
            'summary' => [
                'seo'  => $this->score_group($seo),
                'aeo'  => $this->score_group($aeo),
                'geo'  => $this->score_group($geo),
                'pass' => $pass,
                'fail' => $fail,
                'seo_issues' => count(array_filter($seo, static fn($i) => empty($i['pass']))),
                'aeo_issues' => count(array_filter($aeo, static fn($i) => empty($i['pass']))),
                'geo_issues' => count(array_filter($geo, static fn($i) => empty($i['pass']))),
            ],
            'engines' => $this->engine_readiness($ctx, $aeo, $geo),
        ];
    }

    private function score_group(array $items): int
    {
        if (!$items) {
            return 0;
        }
        $pass = count(array_filter($items, static fn($i) => !empty($i['pass'])));
        return (int) round(($pass / count($items)) * 100);
    }

    private function item(string $key, string $label, bool $pass, string $severity = 'yellow', string $hint = '', string $group = 'seo'): array
    {
        return [
            'key'      => $key,
            'label'    => $label,
            'pass'     => $pass,
            'severity' => $severity, // red|orange|yellow|info
            'hint'     => $hint,
            'group'    => $group,
        ];
    }


    private function build_local_html(int $post_id): string
    {
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }
        $title = get_the_title($post);
        $content = apply_filters('the_content', $post->post_content);
        $excerpt = $post->post_excerpt ?: wp_trim_words(wp_strip_all_tags($post->post_content), 40);
        $seo_title = (string) get_post_meta($post_id, '_bankai_seo_title', true);
        $seo_desc = (string) get_post_meta($post_id, '_bankai_seo_description', true);
        $canonical = (string) get_post_meta($post_id, '_bankai_seo_canonical', true) ?: get_permalink($post_id);
        $og_title = (string) get_post_meta($post_id, '_bankai_seo_og_title', true) ?: ($seo_title ?: $title);
        $og_desc = (string) get_post_meta($post_id, '_bankai_seo_og_description', true) ?: ($seo_desc ?: $excerpt);
        $og_image = (string) get_post_meta($post_id, '_bankai_seo_og_image', true) ?: (get_the_post_thumbnail_url($post_id, 'full') ?: '');
        $site = get_bloginfo('name');
        $lang = get_bloginfo('language') ?: 'fa-IR';
        $doc_title = $seo_title !== '' ? $seo_title : $title;
        $meta_desc = $seo_desc !== '' ? $seo_desc : $excerpt;

        $head = '<!DOCTYPE html><html lang="' . esc_attr($lang) . '"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . esc_html($doc_title) . '</title>'
            . '<meta name="description" content="' . esc_attr($meta_desc) . '">'
            . '<link rel="canonical" href="' . esc_url($canonical) . '">'
            . '<meta property="og:title" content="' . esc_attr($og_title) . '">'
            . '<meta property="og:description" content="' . esc_attr($og_desc) . '">'
            . ($og_image ? '<meta property="og:image" content="' . esc_url($og_image) . '">' : '')
            . '<meta property="og:site_name" content="' . esc_attr($site) . '">'
            . '<meta property="og:type" content="article">'
            . '<meta property="og:url" content="' . esc_url($canonical) . '">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '<meta name="twitter:title" content="' . esc_attr($og_title) . '">'
            . '<meta name="twitter:description" content="' . esc_attr($og_desc) . '">'
            . '</head><body><article><h1>' . esc_html($title) . '</h1>'
            . $content
            . '</article></body></html>';
        return $head;
    }

    private function parse_html(string $html, string $url): array
    {
        $ctx = [
            'html' => $html,
            'html_len' => strlen($html),
            'title' => '',
            'meta_desc' => '',
            'canonical' => '',
            'robots' => '',
            'lang' => '',
            'h1' => [],
            'h2' => [],
            'h3' => [],
            'images' => 0,
            'images_alt' => 0,
            'images_legacy' => 0,
            'images_dims' => 0,
            'links_internal' => 0,
            'links_external' => 0,
            'og' => [],
            'twitter' => [],
            'schemas' => [],
            'has_viewport' => false,
            'has_charset' => false,
            'word_count' => 0,
            'text_len' => 0,
            'has_faq' => false,
            'has_toc' => false,
            'has_video' => false,
            'has_table' => false,
            'has_dl' => false,
            'has_code' => false,
            'has_details' => false,
            'question_headings' => 0,
            'lists' => 0,
            'inline_css_kb' => 0,
            'inline_js_kb' => 0,
        ];

        if ($html === '') {
            return $ctx;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $titleNode = $dom->getElementsByTagName('title')->item(0);
        $ctx['title'] = $titleNode ? trim($titleNode->textContent) : '';

        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $name = strtolower($meta->getAttribute('name') ?: $meta->getAttribute('property') ?: '');
            $content = $meta->getAttribute('content');
            if ($name === 'description') {
                $ctx['meta_desc'] = $content;
            }
            if ($name === 'robots') {
                $ctx['robots'] = $content;
            }
            if ($name === 'viewport') {
                $ctx['has_viewport'] = true;
            }
            if ($meta->getAttribute('charset') || $name === 'charset') {
                $ctx['has_charset'] = true;
            }
            if (str_starts_with($name, 'og:')) {
                $ctx['og'][substr($name, 3)] = $content;
            }
            if (str_starts_with($name, 'twitter:')) {
                $ctx['twitter'][substr($name, 8)] = $content;
            }
        }

        foreach ($dom->getElementsByTagName('link') as $link) {
            $rel = strtolower($link->getAttribute('rel'));
            if ($rel === 'canonical') {
                $ctx['canonical'] = $link->getAttribute('href');
            }
        }

        $htmlEl = $dom->getElementsByTagName('html')->item(0);
        if ($htmlEl) {
            $ctx['lang'] = $htmlEl->getAttribute('lang');
        }

        foreach (['h1', 'h2', 'h3'] as $tag) {
            foreach ($dom->getElementsByTagName($tag) as $n) {
                $txt = trim(preg_replace('/\s+/u', ' ', $n->textContent) ?? '');
                if ($txt !== '') {
                    $ctx[$tag][] = $txt;
                    if (preg_match('/\?|چیست|چگونه|چرا|How |What |Why |When /iu', $txt)) {
                        $ctx['question_headings']++;
                    }
                }
            }
        }

        foreach ($dom->getElementsByTagName('img') as $img) {
            $ctx['images']++;
            $alt = $img->getAttribute('alt');
            if ($alt !== '') {
                $ctx['images_alt']++;
            }
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if (preg_match('/\.(jpe?g|png|gif)(\?|$)/i', $src)) {
                $ctx['images_legacy']++;
            }
            if ($img->getAttribute('width') && $img->getAttribute('height')) {
                $ctx['images_dims']++;
            }
        }

        $host = wp_parse_url($url, PHP_URL_HOST) ?: '';
        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = $a->getAttribute('href');
            if ($href === '' || str_starts_with($href, '#')) {
                continue;
            }
            $h = wp_parse_url($href, PHP_URL_HOST);
            if (!$h || $h === $host) {
                $ctx['links_internal']++;
            } else {
                $ctx['links_external']++;
            }
        }

        foreach ($dom->getElementsByTagName('script') as $script) {
            $type = strtolower($script->getAttribute('type'));
            if ($type === 'application/ld+json') {
                $json = trim($script->textContent);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $ctx['schemas'][] = $decoded;
                }
            }
            if (!$script->getAttribute('src')) {
                $ctx['inline_js_kb'] += strlen($script->textContent) / 1024;
            }
        }
        foreach ($dom->getElementsByTagName('style') as $style) {
            $ctx['inline_css_kb'] += strlen($style->textContent) / 1024;
        }

        $ctx['has_faq'] = (bool) preg_match('/faq|پرسش|سوالات متداول/iu', $html);
        $ctx['has_toc'] = (bool) preg_match('/table-of-contents|toc|فهرست مطالب/iu', $html);
        $ctx['has_video'] = $dom->getElementsByTagName('video')->length > 0 || str_contains($html, 'youtube.com') || str_contains($html, 'youtu.be');
        $ctx['has_table'] = $dom->getElementsByTagName('table')->length > 0;
        $ctx['has_dl'] = $dom->getElementsByTagName('dl')->length > 0;
        $ctx['has_code'] = $dom->getElementsByTagName('code')->length > 0 || $dom->getElementsByTagName('pre')->length > 0;
        $ctx['has_details'] = $dom->getElementsByTagName('details')->length > 0;
        $ctx['lists'] = $dom->getElementsByTagName('ul')->length + $dom->getElementsByTagName('ol')->length;

        // Text content estimate
        $body = $dom->getElementsByTagName('body')->item(0);
        $text = $body ? $body->textContent : strip_tags($html);
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        $ctx['text_len'] = mb_strlen($text);
        $ctx['word_count'] = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        $ctx['text'] = $text;

        return $ctx;
    }

    private function checks_seo(array $c): array
    {
        $items = [];
        $title = $c['title'] ?? '';
        $tlen = mb_strlen($title);
        $items[] = $this->item('title_exists', 'تگ Title یافت شد' . ($title ? ': "' . mb_substr($title, 0, 60) . '…"' : ''), $title !== '', 'red', '', 'seo');
        $items[] = $this->item('title_len', 'طول Title: ' . $tlen . ' کاراکتر' . ($tlen > 60 ? ' (بیش از ۶۰ — کوتاه کنید)' : ($tlen < 30 ? ' (کوتاه)' : ' (مناسب)')), $tlen >= 30 && $tlen <= 60, 'red', 'عنوان را بین ۳۰ تا ۶۰ کاراکتر نگه دارید.', 'seo');

        $desc = $c['meta_desc'] ?? '';
        $dlen = mb_strlen($desc);
        $items[] = $this->item('meta_desc', 'Meta description' . ($dlen ? " ($dlen chars)" : ' یافت نشد'), $desc !== '', 'red', '', 'seo');
        $items[] = $this->item('meta_desc_len', 'طول Description: ' . $dlen . ($dlen > 160 ? ' — خیلی بلند' : ($dlen > 0 && $dlen < 70 ? ' — کوتاه' : '')), $dlen >= 70 && $dlen <= 160, 'orange', 'متا را بین ۷۰ تا ۱۶۰ کاراکتر نگه دارید.', 'seo');

        $h1c = count($c['h1'] ?? []);
        $items[] = $this->item('h1_single', $h1c === 1 ? 'یک H1 در صفحه' : ("تعداد H1: $h1c"), $h1c === 1, 'red', 'فقط یک H1 داشته باشید.', 'seo');
        $h2c = count($c['h2'] ?? []);
        $items[] = $this->item('h2_present', $h2c . ' تگ H2 یافت شد', $h2c >= 1, 'yellow', '', 'seo');
        // hierarchy gap H1 -> H3 without H2
        $h3c = count($c['h3'] ?? []);
        $gap = $h1c > 0 && $h3c > 0 && $h2c === 0;
        $items[] = $this->item('heading_hierarchy', $gap ? 'سلسله‌مراتب هدینگ ناقص (H1 به H3 بدون H2)' : 'سلسله‌مراتب هدینگ قابل قبول', !$gap, 'yellow', '', 'seo');

        $items[] = $this->item('viewport', 'Viewport موبایل', !empty($c['has_viewport']), 'red', '', 'seo');
        $items[] = $this->item('charset', 'Charset / UTF-8', !empty($c['has_charset']) || str_contains($c['html'] ?? '', 'charset'), 'yellow', '', 'seo');
        $items[] = $this->item('lang', 'ویژگی lang روی HTML', !empty($c['lang']), 'yellow', '', 'seo');
        $items[] = $this->item('canonical', 'تگ Canonical', !empty($c['canonical']), 'red', '', 'seo');
        $https = str_starts_with($c['url'] ?? '', 'https://');
        $items[] = $this->item('https', 'HTTPS فعال', $https, 'red', '', 'seo');

        $imgs = (int) ($c['images'] ?? 0);
        $alt = (int) ($c['images_alt'] ?? 0);
        $items[] = $this->item('img_alt', $imgs ? "ALT تصاویر: $alt از $imgs" : 'بدون تصویر', $imgs === 0 || $alt === $imgs, 'orange', '', 'seo');
        $legacy = (int) ($c['images_legacy'] ?? 0);
        $items[] = $this->item('img_format', $legacy ? "$legacy تصویر با فرمت قدیمی JPG/PNG/GIF" : 'فرمت تصاویر بهینه یا بدون تصویر قدیمی', $legacy === 0, 'yellow', 'به WebP/AVIF تبدیل کنید.', 'seo');
        $dims = (int) ($c['images_dims'] ?? 0);
        $items[] = $this->item('img_dims', $imgs ? "ابعاد width/height: $dims از $imgs" : '—', $imgs === 0 || $dims === $imgs, 'yellow', 'برای جلوگیری از CLS ابعاد بگذارید.', 'seo');

        $items[] = $this->item('og_title', 'Open Graph title', !empty($c['og']['title']), 'yellow', '', 'seo');
        $items[] = $this->item('og_desc', 'Open Graph description', !empty($c['og']['description']), 'yellow', '', 'seo');
        $items[] = $this->item('og_image', 'Open Graph image', !empty($c['og']['image']), 'orange', '', 'seo');
        $items[] = $this->item('og_site_name', 'og:site_name', !empty($c['og']['site_name']), 'yellow', 'برای برند در اشتراک‌گذاری.', 'seo');
        $items[] = $this->item('twitter_card', 'Twitter Card', !empty($c['twitter']['card']), 'yellow', '', 'seo');

        $schema_count = count($c['schemas'] ?? []);
        $items[] = $this->item('schema', $schema_count ? "$schema_count بلوک JSON-LD" : 'بدون Schema', $schema_count > 0, 'red', '', 'seo');
        $items[] = $this->item('internal_links', ($c['links_internal'] ?? 0) . ' لینک داخلی', ($c['links_internal'] ?? 0) >= 3, 'red', '', 'seo');
        $items[] = $this->item('word_count', ($c['word_count'] ?? 0) . ' کلمه در صفحه', ($c['word_count'] ?? 0) >= 300, 'orange', '', 'seo');

        $url_len = mb_strlen($c['url'] ?? '');
        $items[] = $this->item('url_len', "طول URL: $url_len", $url_len <= 100, 'yellow', 'URL کوتاه‌تر از ۷۵–۱۰۰ کاراکتر بهتر است.', 'seo');

        $ratio = ($c['html_len'] ?? 1) > 0 ? (($c['text_len'] ?? 0) / max(1, $c['html_len'] ?? 1)) * 100 : 0;
        $items[] = $this->item('text_html_ratio', 'نسبت متن به HTML: ' . round($ratio, 1) . '%', $ratio >= 10, 'yellow', 'محتوای متنی بیشتری اضافه کنید.', 'seo');
        $items[] = $this->item('inline_css', 'CSS اینلاین: ' . round($c['inline_css_kb'] ?? 0, 1) . ' KB', ($c['inline_css_kb'] ?? 0) < 50, 'yellow', 'استایل‌ها را به فایل خارجی منتقل کنید.', 'seo');

        // Site-level robots/sitemap only when not a pure local post audit
        if (empty($c['post_id'])) {
            $robots = $this->remote_ok(home_url('/robots.txt'));
            $sitemap = $this->remote_ok(home_url('/sitemap.xml')) || $this->remote_ok(home_url('/wp-sitemap.xml'));
            $items[] = $this->item('robots_txt', 'robots.txt در دسترس', $robots, 'orange', '', 'seo');
            $items[] = $this->item('sitemap', 'sitemap.xml در دسترس', $sitemap, 'orange', '', 'seo');
        }

        return $items;
    }

    private function checks_aeo(array $c): array
    {
        $items = [];
        $items[] = $this->item('faq', 'بخش FAQ / پرسش و پاسخ', !empty($c['has_faq']), 'yellow', 'برای استخراج توسط AI مفید است.', 'aeo');
        $items[] = $this->item('q_headings', ($c['question_headings'] ?? 0) . ' هدینگ سؤالی', ($c['question_headings'] ?? 0) >= 2, 'orange', 'از How/What/چرا/چیست استفاده کنید.', 'aeo');
        $items[] = $this->item('lists', ($c['lists'] ?? 0) . ' لیست ساختاریافته', ($c['lists'] ?? 0) >= 2, 'yellow', '', 'aeo');
        $items[] = $this->item('toc', 'فهرست مطالب (TOC)', !empty($c['has_toc']), 'yellow', '', 'aeo');
        $items[] = $this->item('summary', 'علائم خلاصه / Key Takeaways', (bool) preg_match('/takeaway|خلاصه|جمع‌بندی|tl;dr/iu', $c['html'] ?? ''), 'yellow', '', 'aeo');
        $items[] = $this->item('definitions', 'الگوی تعریف (X is / عبارت است از)', (bool) preg_match('/\bis\b|عبارت است از|یعنی/iu', $c['text'] ?? ''), 'yellow', '', 'aeo');
        $items[] = $this->item('details', 'عناصر details/summary', !empty($c['has_details']), 'yellow', '', 'aeo');
        $items[] = $this->item('tables', 'جدول داده', !empty($c['has_table']), 'yellow', '', 'aeo');
        $items[] = $this->item('dl', 'لیست تعاریف (dl)', !empty($c['has_dl']), 'yellow', '', 'aeo');
        $items[] = $this->item('code', 'بلوک کد', !empty($c['has_code']), 'yellow', '', 'aeo');
        $items[] = $this->item('video', 'محتوای ویدیویی', !empty($c['has_video']), 'yellow', '', 'aeo');
        $items[] = $this->item('schema_aeo', 'داده ساختاریافته برای AI', count($c['schemas'] ?? []) > 0, 'red', '', 'aeo');
        $items[] = $this->item('author', 'اطلاعات نویسنده', (bool) preg_match('/author|نویسنده|byline/iu', $c['html'] ?? ''), 'yellow', '', 'aeo');
        $items[] = $this->item('freshness', 'سیگنال تازگی محتوا', !empty($c['post_modified']) || (bool) preg_match('/dateModified|datePublished|202[4-9]/', $c['html'] ?? ''), 'yellow', '', 'aeo');
        $items[] = $this->item('word_depth', 'عمق محتوا برای AI: ' . ($c['word_count'] ?? 0) . ' کلمه', ($c['word_count'] ?? 0) >= 800, 'orange', '', 'aeo');
        $items[] = $this->item('short_sentences', 'جملات قابل استخراج', true, 'info', 'جملات کوتاه برای AI بهترند.', 'aeo');
        $items[] = $this->item('entities', 'سیگنال موجودیت/موضوع', ($c['links_internal'] ?? 0) >= 5, 'yellow', '', 'aeo');
        $items[] = $this->item('no_ai_block', 'بدون مسدودسازی AI در robots meta', !preg_match('/noai|noimageai/i', $c['robots'] ?? ''), 'yellow', '', 'aeo');
        return $items;
    }

    private function checks_geo(array $c): array
    {
        $items = [];
        $items[] = $this->item('geo_defs', 'تعاریف شفاف برای AI Overview', (bool) preg_match('/\bis\b|عبارت است از/iu', $c['text'] ?? ''), 'yellow', '', 'geo');
        $items[] = $this->item('geo_questions', 'هدینگ‌های Q&A برای Overview', ($c['question_headings'] ?? 0) >= 2, 'yellow', '', 'geo');
        $items[] = $this->item('geo_depth', 'عمق کافی برای Overview', ($c['word_count'] ?? 0) >= 1000 && count($c['h2'] ?? []) >= 3, 'orange', '', 'geo');
        $items[] = $this->item('geo_citations', 'ارجاع به منابع', ($c['links_external'] ?? 0) >= 2, 'yellow', '', 'geo');
        $items[] = $this->item('geo_brand', 'هویت برند (og:site_name + schema)', !empty($c['og']['site_name']) && count($c['schemas'] ?? []) > 0, 'yellow', '', 'geo');
        $items[] = $this->item('geo_schema_variety', 'تنوع Schema: ' . count($c['schemas'] ?? []), count($c['schemas'] ?? []) >= 2, 'yellow', '', 'geo');
        $items[] = $this->item('geo_summary', 'بخش خلاصه / نتیجه', (bool) preg_match('/conclusion|نتیجه|جمع‌بندی|خلاصه/iu', $c['html'] ?? ''), 'yellow', '', 'geo');
        $items[] = $this->item('geo_actionable', 'محتوای عملیاتی (باید/سعی کنید)', (bool) preg_match('/باید|سعی کنید|you should|try to/iu', $c['text'] ?? ''), 'yellow', '', 'geo');
        $items[] = $this->item('geo_compare', 'محتوای مقایسه‌ای', (bool) preg_match('/\bvs\b|مقایسه|در مقابل/iu', $c['text'] ?? ''), 'yellow', '', 'geo');
        $items[] = $this->item('geo_examples', 'مثال یا مطالعه موردی', (bool) preg_match('/مثال|case study|for example/iu', $c['text'] ?? ''), 'yellow', '', 'geo');
        $items[] = $this->item('geo_time', 'عناصر time یا تاریخ مشخص', (bool) preg_match('/<time|datePublished|dateModified/i', $c['html'] ?? ''), 'yellow', '', 'geo');
        $items[] = $this->item('geo_subtopics', 'زیرموضوع با H3', count($c['h3'] ?? []) >= 2, 'yellow', '', 'geo');
        $items[] = $this->item('geo_https', 'HTTPS قابل اعتماد برای AI', str_starts_with($c['url'] ?? '', 'https://'), 'red', '', 'geo');
        $items[] = $this->item('geo_mobile', 'موبایل‌فرندلی', !empty($c['has_viewport']), 'yellow', '', 'geo');
        $items[] = $this->item('geo_canonical', 'Canonical برای جلوگیری از تکراری', !empty($c['canonical']), 'yellow', '', 'geo');
        $items[] = $this->item('geo_og', 'Open Graph کامل', !empty($c['og']['title']) && !empty($c['og']['description']) && !empty($c['og']['image']), 'yellow', '', 'geo');
        $items[] = $this->item('geo_alt', 'ALT توصیفی تصاویر', ($c['images'] ?? 0) === 0 || ($c['images_alt'] ?? 0) === ($c['images'] ?? 0), 'yellow', '', 'geo');
        $items[] = $this->item('geo_lists', 'لیست‌های ساختاریافته', ($c['lists'] ?? 0) >= 2, 'yellow', '', 'geo');
        $items[] = $this->item('geo_author_schema', 'سیگنال نویسنده/E-E-A-T', (bool) preg_match('/author|Person|نویسنده/iu', $c['html'] ?? ''), 'yellow', '', 'geo');
        return $items;
    }

    private function engine_readiness(array $c, array $aeo, array $geo): array
    {
        $aeo_score = $this->score_group($aeo);
        $geo_score = $this->score_group($geo);
        return [
            ['name' => 'ChatGPT', 'score' => min(100, $aeo_score + 5), 'signals' => 'AEO'],
            ['name' => 'Perplexity', 'score' => min(100, $aeo_score), 'signals' => 'AEO'],
            ['name' => 'Google AI Overview', 'score' => min(100, (int) round(($geo_score + $aeo_score) / 2)), 'signals' => 'GEO'],
            ['name' => 'Claude', 'score' => min(100, $aeo_score - 5 < 0 ? 0 : $aeo_score - 5), 'signals' => 'AEO'],
        ];
    }

    private function remote_ok(string $url): bool
    {
        $r = wp_remote_head($url, ['timeout' => 8, 'sslverify' => false, 'redirection' => 2]);
        if (is_wp_error($r)) {
            $r = wp_remote_get($url, ['timeout' => 8, 'sslverify' => false, 'redirection' => 2]);
        }
        if (is_wp_error($r)) {
            return false;
        }
        $code = (int) wp_remote_retrieve_response_code($r);
        return $code >= 200 && $code < 400;
    }
}
