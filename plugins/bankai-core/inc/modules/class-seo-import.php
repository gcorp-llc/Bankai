<?php
/**
 * Import SEO meta from Rank Math / Yoast into Bankai SEO fields.
 *
 * @package Bankai
 */
if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_SEO_Import
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        add_action('wp_ajax_bankai_seo_import_detect', [$this, 'ajax_detect']);
        add_action('wp_ajax_bankai_seo_import_run', [$this, 'ajax_run_batch']);
        add_action('wp_ajax_bankai_seo_import_missing', [$this, 'ajax_missing']);
        add_action('wp_ajax_bankai_seo_import_single', [$this, 'ajax_import_single']);
        add_action('wp_ajax_bankai_seo_sync_post_from_plugins', [$this, 'ajax_sync_post']);
        add_action('wp_ajax_bankai_seo_ai_post_context', [$this, 'ajax_post_context']);
        add_action('wp_ajax_bankai_seo_ai_apply_draft', [$this, 'ajax_apply_ai_draft']);
        add_action('wp_ajax_bankai_seo_sync_list', [$this, 'ajax_sync_list']);
        add_action('wp_ajax_bankai_seo_sync_process_one', [$this, 'ajax_sync_process_one']);
        add_action('wp_ajax_bankai_seo_export_list', [$this, 'ajax_export_list']);
        add_action('wp_ajax_bankai_seo_export_process_one', [$this, 'ajax_export_process_one']);
        add_action('activated_plugin', [__CLASS__, 'bust_detect_cache']);
        add_action('deactivated_plugin', [__CLASS__, 'bust_detect_cache']);
    }

    public static function detect_sources(bool $with_counts = false): array
    {
        $cache_key = 'bankai_seo_detect_sources_v2';
        $cached = get_transient($cache_key);
        if (is_array($cached) && !$with_counts) {
            return $cached;
        }

        // Instant plugin presence (no DB) — both shown as selectable when active OR always listed
        $rm_active = defined('RANK_MATH_VERSION') || class_exists('RankMath') || function_exists('rank_math');
        $yo_active = defined('WPSEO_VERSION') || class_exists('WPSEO_Options');

        $sources = [
            'rankmath' => [
                'id' => 'rankmath',
                'label' => 'Rank Math',
                'active' => true, // always selectable; real presence flagged below
                'plugin_active' => $rm_active,
                'posts' => is_array($cached) ? (int) ($cached['rankmath']['posts'] ?? 0) : 0,
            ],
            'yoast' => [
                'id' => 'yoast',
                'label' => 'Yoast SEO',
                'active' => true,
                'plugin_active' => $yo_active,
                'posts' => is_array($cached) ? (int) ($cached['yoast']['posts'] ?? 0) : 0,
            ],
        ];

        // Heavy COUNT only when requested (after user picks a source) or cache empty
        if ($with_counts || !is_array($cached)) {
            global $wpdb;
            $rm = (int) $wpdb->get_var(
                "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key IN ('rank_math_title','rank_math_description','rank_math_focus_keyword')"
            );
            $yo = (int) $wpdb->get_var(
                "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key IN ('_yoast_wpseo_title','_yoast_wpseo_metadesc','_yoast_wpseo_focuskw')"
            );
            $sources['rankmath']['posts'] = $rm;
            $sources['yoast']['posts'] = $yo;
            if ($rm > 0) {
                $sources['rankmath']['plugin_active'] = true;
            }
            if ($yo > 0) {
                $sources['yoast']['plugin_active'] = true;
            }
            set_transient($cache_key, $sources, 12 * HOUR_IN_SECONDS);
        }

        return $sources;
    }

    /** Clear detect cache when plugins change. */
    public static function bust_detect_cache(): void
    {
        delete_transient('bankai_seo_detect_sources_v2');
    }

    /**
     * Map one post from RankMath/Yoast → Bankai meta. Returns summary array.
     */
    public function import_post(int $post_id, array $opts = []): array
    {
        $overwrite = !empty($opts['overwrite']);
        $prefer = $opts['prefer'] ?? 'rankmath'; // rankmath|yoast|auto
        $mapped = [];
        $source_used = '';

        $rm = $this->read_rankmath($post_id);
        $yo = $this->read_yoast($post_id);

        $has_rm = $this->has_any($rm);
        $has_yo = $this->has_any($yo);

        if ($prefer === 'rankmath' && $has_rm) {
            $data = $rm;
            $source_used = 'rankmath';
        } elseif ($prefer === 'yoast' && $has_yo) {
            $data = $yo;
            $source_used = 'yoast';
        } elseif ($has_rm && (!$has_yo || $prefer !== 'yoast')) {
            $data = $rm;
            $source_used = 'rankmath';
            // Fill empty fields from Yoast
            if ($has_yo) {
                $data = $this->merge_prefer($data, $yo);
            }
        } elseif ($has_yo) {
            $data = $yo;
            $source_used = 'yoast';
        } else {
            return [
                'post_id' => $post_id,
                'imported' => false,
                'source' => '',
                'fields' => [],
                'message' => 'no_source_meta',
            ];
        }

        $field_map = [
            'seo_title'     => Bankai_SEO_Engine::META_TITLE,
            'description'   => Bankai_SEO_Engine::META_DESCRIPTION,
            'focus_keyword' => Bankai_SEO_Engine::META_KEYWORD,
            'canonical'     => Bankai_SEO_Engine::META_CANONICAL,
            'og_title'      => Bankai_SEO_Engine::META_OG_TITLE,
            'og_description'=> Bankai_SEO_Engine::META_OG_DESC,
            'og_image'      => Bankai_SEO_Engine::META_OG_IMAGE,
            'x_title'       => Bankai_SEO_Engine::META_X_TITLE,
            'x_description' => Bankai_SEO_Engine::META_X_DESC,
            'x_image'       => Bankai_SEO_Engine::META_X_IMAGE,
        ];

        foreach ($field_map as $key => $meta_key) {
            $val = isset($data[$key]) ? trim((string) $data[$key]) : '';
            if ($val === '') {
                continue;
            }
            $existing = (string) get_post_meta($post_id, $meta_key, true);
            if ($existing !== '' && !$overwrite) {
                continue;
            }
            update_post_meta($post_id, $meta_key, sanitize_text_field($val));
            $mapped[] = $key;
        }

        // Keywords JSON
        if (!empty($data['keywords']) && is_array($data['keywords'])) {
            $existing_kw = (string) get_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORDS, true);
            if ($overwrite || $existing_kw === '' || $existing_kw === '[]') {
                $clean = array_values(array_filter(array_map('sanitize_text_field', $data['keywords'])));
                update_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORDS, wp_json_encode($clean, JSON_UNESCAPED_UNICODE));
                $mapped[] = 'keywords';
            }
        }

        // Robots
        if (!empty($data['robots']) && is_array($data['robots'])) {
            $existing_r = get_post_meta($post_id, Bankai_SEO_Engine::META_ROBOTS, true);
            if ($overwrite || empty($existing_r)) {
                update_post_meta($post_id, Bankai_SEO_Engine::META_ROBOTS, $data['robots']);
                $mapped[] = 'robots';
            }
        }

        // Score (approximate)
        if (isset($data['score']) && $data['score'] !== '' && $data['score'] !== null) {
            $existing_s = get_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, true);
            if ($overwrite || $existing_s === '' || (int) $existing_s === 0) {
                update_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, max(0, min(100, (int) $data['score'])));
                $mapped[] = 'score';
            }
        }

        // Mark import source
        update_post_meta($post_id, '_bankai_seo_imported_from', $source_used);
        update_post_meta($post_id, '_bankai_seo_imported_at', time());

        // Re-analyze score if engine available and we have content
        if (class_exists('Bankai_SEO_Engine') && method_exists(Bankai_SEO_Engine::instance(), 'analyze_post')) {
            try {
                $analysis = Bankai_SEO_Engine::instance()->analyze_post($post_id);
                if (is_array($analysis) && isset($analysis['score'])) {
                    update_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, (int) $analysis['score']);
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        return [
            'post_id' => $post_id,
            'imported' => !empty($mapped),
            'source' => $source_used,
            'fields' => $mapped,
            'message' => empty($mapped) ? 'nothing_new' : 'ok',
        ];
    }

    private function has_any(array $data): bool
    {
        foreach (['seo_title', 'description', 'focus_keyword'] as $k) {
            if (!empty($data[$k])) {
                return true;
            }
        }
        return false;
    }

    private function merge_prefer(array $primary, array $fallback): array
    {
        foreach ($fallback as $k => $v) {
            if (is_string($v) && $v !== '' && (empty($primary[$k]) || $primary[$k] === '')) {
                $primary[$k] = $v;
            } elseif (is_array($v) && empty($primary[$k])) {
                $primary[$k] = $v;
            }
        }
        return $primary;
    }

    private function read_rankmath(int $post_id): array
    {
        $kw_raw = (string) get_post_meta($post_id, 'rank_math_focus_keyword', true);
        $keywords = array_values(array_filter(array_map('trim', preg_split('/[,،]+/u', $kw_raw) ?: [])));
        $focus = $keywords[0] ?? '';
        $robots = get_post_meta($post_id, 'rank_math_robots', true);
        if (!is_array($robots)) {
            $robots = [];
        }
        return [
            'seo_title' => (string) get_post_meta($post_id, 'rank_math_title', true),
            'description' => (string) get_post_meta($post_id, 'rank_math_description', true),
            'focus_keyword' => $focus,
            'keywords' => $keywords,
            'canonical' => (string) get_post_meta($post_id, 'rank_math_canonical_url', true),
            'og_title' => (string) get_post_meta($post_id, 'rank_math_facebook_title', true),
            'og_description' => (string) get_post_meta($post_id, 'rank_math_facebook_description', true),
            'og_image' => (string) get_post_meta($post_id, 'rank_math_facebook_image', true),
            'x_title' => (string) get_post_meta($post_id, 'rank_math_twitter_title', true),
            'x_description' => (string) get_post_meta($post_id, 'rank_math_twitter_description', true),
            'x_image' => (string) get_post_meta($post_id, 'rank_math_twitter_image', true),
            'robots' => $robots,
            'score' => get_post_meta($post_id, 'rank_math_seo_score', true),
        ];
    }

    private function read_yoast(int $post_id): array
    {
        $focus = (string) get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        $keywords = $focus !== '' ? [$focus] : [];
        // Yoast secondary keywords (if any)
        $syn = (string) get_post_meta($post_id, '_yoast_wpseo_focuskeywords', true);
        if ($syn !== '') {
            $decoded = json_decode($syn, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (is_array($row) && !empty($row['keyword'])) {
                        $keywords[] = (string) $row['keyword'];
                    } elseif (is_string($row)) {
                        $keywords[] = $row;
                    }
                }
            }
        }
        $keywords = array_values(array_unique(array_filter(array_map('trim', $keywords))));

        $noindex = get_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', true);
        $robots = [];
        if ((string) $noindex === '1') {
            $robots['index'] = 'noindex';
        }

        // Yoast linkdex is roughly 0-100 style score in some versions
        $score = get_post_meta($post_id, '_yoast_wpseo_linkdex', true);
        if ($score === '' || $score === null) {
            $score = get_post_meta($post_id, '_yoast_wpseo_content_score', true);
        }

        return [
            'seo_title' => (string) get_post_meta($post_id, '_yoast_wpseo_title', true),
            'description' => (string) get_post_meta($post_id, '_yoast_wpseo_metadesc', true),
            'focus_keyword' => $focus,
            'keywords' => $keywords,
            'canonical' => (string) get_post_meta($post_id, '_yoast_wpseo_canonical', true),
            'og_title' => (string) get_post_meta($post_id, '_yoast_wpseo_opengraph-title', true),
            'og_description' => (string) get_post_meta($post_id, '_yoast_wpseo_opengraph-description', true),
            'og_image' => (string) get_post_meta($post_id, '_yoast_wpseo_opengraph-image', true),
            'x_title' => (string) get_post_meta($post_id, '_yoast_wpseo_twitter-title', true),
            'x_description' => (string) get_post_meta($post_id, '_yoast_wpseo_twitter-description', true),
            'x_image' => (string) get_post_meta($post_id, '_yoast_wpseo_twitter-image', true),
            'robots' => $robots,
            'score' => $score,
        ];
    }

    /** Post IDs that have RankMath or Yoast SEO meta. */
    public function get_source_post_ids(int $page = 1, int $per_page = 20): array
    {
        global $wpdb;
        $page = max(1, $page);
        $per_page = max(1, min(50, $per_page));
        $offset = ($page - 1) * $per_page;

        $total = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
             WHERE meta_key IN (
                'rank_math_title','rank_math_description','rank_math_focus_keyword',
                '_yoast_wpseo_title','_yoast_wpseo_metadesc','_yoast_wpseo_focuskw'
             )"
        );

        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
             WHERE meta_key IN (
                'rank_math_title','rank_math_description','rank_math_focus_keyword',
                '_yoast_wpseo_title','_yoast_wpseo_metadesc','_yoast_wpseo_focuskw'
             )
             ORDER BY post_id ASC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        return [
            'ids' => array_map('intval', $ids ?: []),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total > 0 ? (int) ceil($total / $per_page) : 0,
        ];
    }

    /** Posts without Bankai SEO title/desc/focus */
    public function get_missing_seo_posts(int $page = 1, int $per_page = 20): array
    {
        global $wpdb;
        $page = max(1, $page);
        $per_page = max(1, min(50, $per_page));
        $offset = ($page - 1) * $per_page;

        $title_key = Bankai_SEO_Engine::META_TITLE;
        $desc_key = Bankai_SEO_Engine::META_DESCRIPTION;
        $focus_key = Bankai_SEO_Engine::META_KEYWORD;

        $types = ['post', 'page'];
        if (class_exists('WooCommerce') || post_type_exists('product')) {
            $types[] = 'product';
        }
        $types_sql = "'" . implode("','", array_map('esc_sql', $types)) . "'";

        $sql_total = $wpdb->prepare(
            "SELECT COUNT(p.ID) FROM {$wpdb->posts} p
             WHERE p.post_type IN ($types_sql) AND p.post_status IN ('publish','draft','future','pending','private')
             AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} m
                WHERE m.post_id = p.ID AND m.meta_key = %s AND m.meta_value <> ''
             )
             AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} m2
                WHERE m2.post_id = p.ID AND m2.meta_key = %s AND m2.meta_value <> ''
             )",
            $title_key,
            $desc_key
        );
        $total = (int) $wpdb->get_var($sql_total);

        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             WHERE p.post_type IN ($types_sql) AND p.post_status IN ('publish','draft','future','pending','private')
             AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} m
                WHERE m.post_id = p.ID AND m.meta_key = %s AND m.meta_value <> ''
             )
             AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} m2
                WHERE m2.post_id = p.ID AND m2.meta_key = %s AND m2.meta_value <> ''
             )
             ORDER BY p.post_modified DESC
             LIMIT %d OFFSET %d",
            $title_key,
            $desc_key,
            $per_page,
            $offset
        ));

        $items = [];
        foreach ($ids ?: [] as $id) {
            $id = (int) $id;
            $post = get_post($id);
            $ptype = $post ? $post->post_type : 'post';
            $type_label = match ($ptype) {
                'page' => 'برگه',
                'product' => 'محصول',
                default => 'مقاله',
            };
            $items[] = [
                'id' => $id,
                'title' => get_the_title($id),
                'edit_url' => get_edit_post_link($id, 'raw'),
                'status' => get_post_status($id),
                'post_type' => $ptype,
                'type_label' => $type_label,
                'score' => (int) get_post_meta($id, Bankai_SEO_Engine::META_SCORE, true),
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total > 0 ? (int) ceil($total / $per_page) : 0,
        ];
    }

    /* ---- AJAX ---- */

    public function ajax_detect(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $with_counts = !empty($_POST['with_counts']);
        $sources = self::detect_sources($with_counts);
        $total = (int) ($sources['rankmath']['posts'] ?? 0) + (int) ($sources['yoast']['posts'] ?? 0);
        wp_send_json_success([
            'sources' => $sources,
            'importable_total' => $total,
        ]);
    }

    public function ajax_run_batch(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }
        $page = max(1, absint($_POST['page'] ?? 1));
        $per_page = max(1, min(30, absint($_POST['per_page'] ?? 15)));
        $overwrite = !empty($_POST['overwrite']);
        $prefer = sanitize_key(wp_unslash($_POST['prefer'] ?? 'auto'));
        if (!in_array($prefer, ['auto', 'rankmath', 'yoast'], true)) {
            $prefer = 'auto';
        }

        $batch = $this->get_source_post_ids($page, $per_page);
        $results = [];
        $imported = 0;
        $skipped = 0;
        foreach ($batch['ids'] as $pid) {
            $r = $this->import_post($pid, ['overwrite' => $overwrite, 'prefer' => $prefer]);
            $results[] = $r;
            if (!empty($r['imported'])) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        wp_send_json_success([
            'page' => $batch['page'],
            'total_pages' => $batch['total_pages'],
            'total' => $batch['total'],
            'imported' => $imported,
            'skipped' => $skipped,
            'results' => $results,
            'done' => $batch['page'] >= $batch['total_pages'] || $batch['total'] === 0,
        ]);
    }

    public function ajax_missing(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $page = max(1, absint($_POST['page'] ?? 1));
        $per_page = max(1, min(50, absint($_POST['per_page'] ?? 20)));
        wp_send_json_success($this->get_missing_seo_posts($page, $per_page));
    }

    public function ajax_import_single(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error(['message' => 'پست نامعتبر']);
        }
        $overwrite = !empty($_POST['overwrite']);
        $r = $this->import_post($post_id, ['overwrite' => $overwrite, 'prefer' => 'auto']);
        wp_send_json_success($r);
    }

    /** Single-post sync for editor sidebar */
    public function ajax_sync_post(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error(['message' => 'پست نامعتبر']);
        }
        $overwrite = !empty($_POST['overwrite']);
        $r = $this->import_post($post_id, ['overwrite' => true, 'prefer' => 'auto']);

        // Return current Bankai SEO snapshot for UI
        $seo = [
            'seo_title' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_TITLE, true),
            'description' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_DESCRIPTION, true),
            'focus_keyword' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORD, true),
            'keywords' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORDS, true),
            'canonical' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_CANONICAL, true),
            'score' => (int) get_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, true),
            'og_title' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_OG_TITLE, true),
            'og_description' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_OG_DESC, true),
            'og_image' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_OG_IMAGE, true),
        ];
        wp_send_json_success(array_merge($r, ['seo' => $seo]));
    }

    /**
     * Context for batch AI SEO on a post (content + images + related).
     */
    public function ajax_post_context(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => 'پست نامعتبر']);
        }

        $content = (string) $post->post_content;
        $title = (string) $post->post_title;
        $focus = class_exists('Bankai_SEO_Engine')
            ? (string) get_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORD, true)
            : '';
        $thumb_id = (int) get_post_thumbnail_id($post_id);
        $thumb_url = $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'full') : '';

        // Images in content
        $images = [];
        if (preg_match_all('/<img[^>]+>/i', $content, $m)) {
            foreach ($m[0] as $i => $tag) {
                $src = '';
                $alt = '';
                if (preg_match('/src=["\']([^"\']+)["\']/i', $tag, $sm)) {
                    $src = $sm[1];
                }
                if (preg_match('/alt=["\']([^"\']*)["\']/i', $tag, $am)) {
                    $alt = $am[1];
                }
                if ($src) {
                    $images[] = [
                        'id' => 'img_' . $i,
                        'src' => $src,
                        'alt' => $alt,
                        'is_cover' => false,
                    ];
                }
            }
        }
        if ($thumb_url) {
            array_unshift($images, [
                'id' => 'cover_' . $thumb_id,
                'src' => $thumb_url,
                'alt' => (string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true),
                'is_cover' => true,
                'attachment_id' => $thumb_id,
            ]);
        }

        // Related posts for internal linking
        $related = [];
        $q = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'post__not_in' => [$post_id],
            'orderby' => 'modified',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);
        foreach ($q->posts as $rid) {
            $related[] = [
                'id' => (int) $rid,
                'title' => get_the_title($rid),
                'url' => get_permalink($rid),
            ];
        }

        wp_send_json_success([
            'post_id' => $post_id,
            'title' => $title,
            'content' => $content,
            'excerpt' => (string) $post->post_excerpt,
            'focus_keyword' => $focus,
            'permalink' => get_permalink($post_id),
            'images' => $images,
            'related' => $related,
            'score' => class_exists('Bankai_SEO_Engine')
                ? (int) get_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, true)
                : 0,
        ]);
    }

    /**
     * Apply approved AI SEO draft to a post.
     */
    public function ajax_apply_ai_draft(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error(['message' => 'پست نامعتبر']);
        }
        if (!class_exists('Bankai_SEO_Engine')) {
            wp_send_json_error(['message' => 'ماژول سئو فعال نیست']);
        }

        $title = sanitize_text_field(wp_unslash($_POST['seo_title'] ?? ''));
        $desc = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
        $focus = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $keywords_raw = wp_unslash($_POST['keywords'] ?? '[]');
        $keywords = is_string($keywords_raw) ? json_decode($keywords_raw, true) : $keywords_raw;
        if (!is_array($keywords)) {
            $keywords = [];
        }
        $schema = wp_unslash($_POST['schema'] ?? '');
        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : null;

        if ($title !== '') {
            update_post_meta($post_id, Bankai_SEO_Engine::META_TITLE, $title);
        }
        if ($desc !== '') {
            update_post_meta($post_id, Bankai_SEO_Engine::META_DESCRIPTION, $desc);
        }
        if ($focus !== '') {
            update_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORD, $focus);
        }
        if ($keywords) {
            $clean = array_values(array_filter(array_map('sanitize_text_field', $keywords)));
            update_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORDS, wp_json_encode($clean, JSON_UNESCAPED_UNICODE));
        }
        if (is_string($schema) && $schema !== '') {
            update_post_meta($post_id, Bankai_SEO_Engine::META_SCHEMA, $schema);
        }
        if ($content !== null && $content !== '') {
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $content,
            ]);
        }

        $cover_id = absint($_POST['cover_id'] ?? 0);
        if ($cover_id > 0) {
            set_post_thumbnail($post_id, $cover_id);
        }

        // Terms
        if (class_exists('Bankai_AI_Studio')) {
            $studio = Bankai_AI_Studio::instance();
            if (method_exists($studio, 'assign_terms_from_keywords') || true) {
                // public ajax path already exists — call private via import keywords
                $this->assign_terms_public($post_id, $keywords, $focus);
            }
        }

        $score = 0;
        try {
            $analysis = Bankai_SEO_Engine::instance()->analyze_post($post_id);
            $score = (int) ($analysis['score'] ?? 0);
            update_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, $score);
        } catch (Throwable $e) {
            $score = (int) get_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, true);
        }

        wp_send_json_success([
            'message' => 'تغییرات سئو ذخیره شد',
            'score' => $score,
            'post_id' => $post_id,
        ]);
    }

    private function assign_terms_public(int $post_id, array $keywords, string $focus): void
    {
        if (!class_exists('Bankai_AI_Studio')) {
            return;
        }
        // Reuse import term finder logic
        $pool = [];
        if ($focus !== '') {
            $pool[] = $focus;
        }
        foreach ($keywords as $kw) {
            $kw = trim((string) $kw);
            if ($kw !== '') {
                $pool[] = $kw;
            }
        }
        $pool = array_values(array_unique($pool));
        if (!$pool) {
            return;
        }
        $cat_id = 0;
        $cat_name = $pool[0];
        $found = term_exists($cat_name, 'category');
        if (!$found) {
            $found = wp_insert_term($cat_name, 'category');
        }
        if (!is_wp_error($found)) {
            $cat_id = is_array($found) ? (int) $found['term_id'] : (int) $found;
            if ($cat_id) {
                wp_set_post_terms($post_id, [$cat_id], 'category', false);
            }
        }
        $tags = array_slice($pool, 0, 10);
        if ($tags) {
            wp_set_post_terms($post_id, $tags, 'post_tag', false);
        }
    }

    /**
     * List posts that have SEO meta from a given source (rankmath|yoast).
     */
    public function ajax_sync_list(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $source = sanitize_key($_POST['source'] ?? 'rankmath');
        if (!in_array($source, ['rankmath', 'yoast'], true)) {
            $source = 'rankmath';
        }
        $page = max(1, absint($_POST['page'] ?? 1));
        $per_page = max(1, min(50, absint($_POST['per_page'] ?? 15)));
        $result = $this->get_source_post_ids_for($source, $page, $per_page);
        $items = [];
        foreach ($result['ids'] as $pid) {
            $post = get_post($pid);
            if (!$post) {
                continue;
            }
            $data = $source === 'yoast' ? $this->read_yoast($pid) : $this->read_rankmath($pid);
            $fields = [];
            foreach (['seo_title', 'description', 'focus_keyword', 'canonical', 'og_title', 'og_description', 'og_image', 'x_title', 'x_description', 'score'] as $fk) {
                $val = $data[$fk] ?? '';
                $has = is_array($val) ? !empty($val) : (string) $val !== '';
                $fields[] = [
                    'id' => $fk,
                    'label' => $this->field_label($fk),
                    'has' => $has,
                    'preview' => is_string($val) ? mb_substr($val, 0, 80) : '',
                ];
            }
            if (!empty($data['keywords']) && is_array($data['keywords'])) {
                $fields[] = [
                    'id' => 'keywords',
                    'label' => $this->field_label('keywords'),
                    'has' => true,
                    'preview' => implode('، ', array_slice($data['keywords'], 0, 5)),
                ];
            }
            $items[] = [
                'id' => $pid,
                'title' => get_the_title($pid) ?: ('#' . $pid),
                'edit_link' => get_edit_post_link($pid, 'raw') ?: '',
                'type' => $post->post_type,
                'status' => $post->post_status,
                'fields' => $fields,
                'source' => $source,
            ];
        }
        wp_send_json_success([
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'source' => $source,
        ]);
    }

    /**
     * Import one post from Rank Math / Yoast → Bankai with field checklist result.
     */
    public function ajax_sync_process_one(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        $source = sanitize_key($_POST['source'] ?? 'rankmath');
        $overwrite = !empty($_POST['overwrite']);
        if ($post_id <= 0) {
            wp_send_json_error(['message' => 'Invalid post'], 400);
        }
        $prefer = in_array($source, ['rankmath', 'yoast'], true) ? $source : 'auto';
        $result = $this->import_post($post_id, [
            'overwrite' => $overwrite,
            'prefer' => $prefer,
        ]);
        $checklist = [];
        $all_fields = ['seo_title', 'description', 'focus_keyword', 'keywords', 'canonical', 'og_title', 'og_description', 'og_image', 'x_title', 'x_description', 'robots', 'score'];
        $mapped = $result['fields'] ?? [];
        foreach ($all_fields as $fk) {
            $checklist[] = [
                'id' => $fk,
                'label' => $this->field_label($fk),
                'status' => in_array($fk, $mapped, true) ? 'done' : 'skip',
            ];
        }
        wp_send_json_success([
            'post_id' => $post_id,
            'title' => get_the_title($post_id),
            'imported' => !empty($result['imported']),
            'source' => $result['source'] ?? $source,
            'fields' => $mapped,
            'checklist' => $checklist,
            'message' => $result['message'] ?? '',
        ]);
    }

    /**
     * List posts that have Bankai SEO meta (for export to Rank Math / Yoast).
     */
    public function ajax_export_list(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $target = sanitize_key($_POST['target'] ?? 'rankmath');
        if (!in_array($target, ['rankmath', 'yoast'], true)) {
            $target = 'rankmath';
        }
        $page = max(1, absint($_POST['page'] ?? 1));
        $per_page = max(1, min(50, absint($_POST['per_page'] ?? 15)));
        $result = $this->get_bankai_post_ids($page, $per_page);
        $items = [];
        foreach ($result['ids'] as $pid) {
            $post = get_post($pid);
            if (!$post) {
                continue;
            }
            $data = $this->read_bankai($pid);
            $fields = [];
            foreach (['seo_title', 'description', 'focus_keyword', 'canonical', 'og_title', 'og_description', 'score'] as $fk) {
                $val = $data[$fk] ?? '';
                $has = (string) $val !== '';
                $fields[] = [
                    'id' => $fk,
                    'label' => $this->field_label($fk),
                    'has' => $has,
                    'preview' => is_string($val) ? mb_substr($val, 0, 80) : '',
                ];
            }
            $items[] = [
                'id' => $pid,
                'title' => get_the_title($pid) ?: ('#' . $pid),
                'edit_link' => get_edit_post_link($pid, 'raw') ?: '',
                'type' => $post->post_type,
                'fields' => $fields,
                'target' => $target,
            ];
        }
        wp_send_json_success([
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'target' => $target,
        ]);
    }

    /**
     * Export one post from Bankai → Rank Math or Yoast.
     */
    public function ajax_export_process_one(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        $target = sanitize_key($_POST['target'] ?? 'rankmath');
        $overwrite = !empty($_POST['overwrite']);
        if ($post_id <= 0) {
            wp_send_json_error(['message' => 'Invalid post'], 400);
        }
        if (!in_array($target, ['rankmath', 'yoast'], true)) {
            $target = 'rankmath';
        }
        $result = $this->export_post($post_id, $target, $overwrite);
        $checklist = [];
        foreach ($result['fields'] ?? [] as $fk) {
            $checklist[] = [
                'id' => $fk,
                'label' => $this->field_label($fk),
                'status' => 'done',
            ];
        }
        // Mark skipped known fields
        $all = ['seo_title', 'description', 'focus_keyword', 'keywords', 'canonical', 'og_title', 'og_description', 'og_image', 'x_title', 'x_description', 'robots', 'score'];
        $mapped = $result['fields'] ?? [];
        $checklist = [];
        foreach ($all as $fk) {
            $checklist[] = [
                'id' => $fk,
                'label' => $this->field_label($fk),
                'status' => in_array($fk, $mapped, true) ? 'done' : 'skip',
            ];
        }
        wp_send_json_success([
            'post_id' => $post_id,
            'title' => get_the_title($post_id),
            'exported' => !empty($result['exported']),
            'target' => $target,
            'fields' => $mapped,
            'checklist' => $checklist,
            'message' => $result['message'] ?? '',
        ]);
    }

    private function field_label(string $key): string
    {
        $map = [
            'seo_title' => 'عنوان سئو',
            'description' => 'متا دیسکریپشن',
            'focus_keyword' => 'کلمه کلیدی اصلی',
            'keywords' => 'کلمات کلیدی فرعی',
            'canonical' => 'Canonical',
            'og_title' => 'عنوان Open Graph',
            'og_description' => 'توضیح Open Graph',
            'og_image' => 'تصویر Open Graph',
            'x_title' => 'عنوان X / Twitter',
            'x_description' => 'توضیح X / Twitter',
            'x_image' => 'تصویر X / Twitter',
            'robots' => 'Robots',
            'score' => 'امتیاز سئو',
        ];
        return $map[$key] ?? $key;
    }

    /** Post IDs with meta from a specific source. */
    public function get_source_post_ids_for(string $source, int $page = 1, int $per_page = 20): array
    {
        global $wpdb;
        $page = max(1, $page);
        $per_page = max(1, min(50, $per_page));
        $offset = ($page - 1) * $per_page;

        if ($source === 'yoast') {
            $keys = "'_yoast_wpseo_title','_yoast_wpseo_metadesc','_yoast_wpseo_focuskw'";
        } else {
            $keys = "'rank_math_title','rank_math_description','rank_math_focus_keyword'";
        }

        $total = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key IN ($keys)"
        );
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
             WHERE meta_key IN ($keys)
             ORDER BY post_id ASC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        return [
            'ids' => array_map('intval', $ids ?: []),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total > 0 ? (int) ceil($total / $per_page) : 0,
        ];
    }

    /** Posts with Bankai SEO meta. */
    public function get_bankai_post_ids(int $page = 1, int $per_page = 20): array
    {
        global $wpdb;
        $page = max(1, $page);
        $per_page = max(1, min(50, $per_page));
        $offset = ($page - 1) * $per_page;
        $keys = "'_bankai_seo_title','_bankai_seo_description','_bankai_seo_focus_keyword'";
        if (class_exists('Bankai_SEO_Engine')) {
            $keys = sprintf(
                "'%s','%s','%s'",
                esc_sql(Bankai_SEO_Engine::META_TITLE),
                esc_sql(Bankai_SEO_Engine::META_DESCRIPTION),
                esc_sql(Bankai_SEO_Engine::META_KEYWORD)
            );
        }
        $total = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key IN ($keys)"
        );
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
             WHERE meta_key IN ($keys)
             ORDER BY post_id ASC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        return [
            'ids' => array_map('intval', $ids ?: []),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total > 0 ? (int) ceil($total / $per_page) : 0,
        ];
    }

    public function read_bankai(int $post_id): array
    {
        if (!class_exists('Bankai_SEO_Engine')) {
            return [];
        }
        $kw_json = (string) get_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORDS, true);
        $keywords = [];
        if ($kw_json !== '') {
            $decoded = json_decode($kw_json, true);
            if (is_array($decoded)) {
                $keywords = $decoded;
            }
        }
        $robots = get_post_meta($post_id, Bankai_SEO_Engine::META_ROBOTS, true);
        if (!is_array($robots)) {
            $robots = [];
        }
        return [
            'seo_title' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_TITLE, true),
            'description' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_DESCRIPTION, true),
            'focus_keyword' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_KEYWORD, true),
            'keywords' => $keywords,
            'canonical' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_CANONICAL, true),
            'og_title' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_OG_TITLE, true),
            'og_description' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_OG_DESC, true),
            'og_image' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_OG_IMAGE, true),
            'x_title' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_X_TITLE, true),
            'x_description' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_X_DESC, true),
            'x_image' => (string) get_post_meta($post_id, Bankai_SEO_Engine::META_X_IMAGE, true),
            'robots' => $robots,
            'score' => get_post_meta($post_id, Bankai_SEO_Engine::META_SCORE, true),
        ];
    }

    /**
     * Export Bankai SEO meta → Rank Math or Yoast meta keys.
     */
    public function export_post(int $post_id, string $target, bool $overwrite = false): array
    {
        $data = $this->read_bankai($post_id);
        if (!$this->has_any($data)) {
            return [
                'post_id' => $post_id,
                'exported' => false,
                'fields' => [],
                'message' => 'no_bankai_meta',
            ];
        }
        $mapped = [];
        if ($target === 'yoast') {
            $map = [
                'seo_title' => '_yoast_wpseo_title',
                'description' => '_yoast_wpseo_metadesc',
                'focus_keyword' => '_yoast_wpseo_focuskw',
                'canonical' => '_yoast_wpseo_canonical',
                'og_title' => '_yoast_wpseo_opengraph-title',
                'og_description' => '_yoast_wpseo_opengraph-description',
                'og_image' => '_yoast_wpseo_opengraph-image',
                'x_title' => '_yoast_wpseo_twitter-title',
                'x_description' => '_yoast_wpseo_twitter-description',
                'x_image' => '_yoast_wpseo_twitter-image',
            ];
            foreach ($map as $key => $meta_key) {
                $val = isset($data[$key]) ? trim((string) $data[$key]) : '';
                if ($val === '') {
                    continue;
                }
                $existing = (string) get_post_meta($post_id, $meta_key, true);
                if ($existing !== '' && !$overwrite) {
                    continue;
                }
                update_post_meta($post_id, $meta_key, $val);
                $mapped[] = $key;
            }
            if (!empty($data['keywords']) && is_array($data['keywords'])) {
                $syn = wp_json_encode(array_map(static function ($k) {
                    return ['keyword' => $k];
                }, $data['keywords']), JSON_UNESCAPED_UNICODE);
                $existing = (string) get_post_meta($post_id, '_yoast_wpseo_focuskeywords', true);
                if ($overwrite || $existing === '') {
                    update_post_meta($post_id, '_yoast_wpseo_focuskeywords', $syn);
                    $mapped[] = 'keywords';
                }
            }
            if (isset($data['score']) && $data['score'] !== '' && $data['score'] !== null) {
                $existing = get_post_meta($post_id, '_yoast_wpseo_linkdex', true);
                if ($overwrite || $existing === '' || (int) $existing === 0) {
                    update_post_meta($post_id, '_yoast_wpseo_linkdex', max(0, min(100, (int) $data['score'])));
                    $mapped[] = 'score';
                }
            }
            if (!empty($data['robots']) && is_array($data['robots'])) {
                $noindex = !empty($data['robots']['noindex']) ? '1' : '0';
                update_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', $noindex);
                $mapped[] = 'robots';
            }
        } else {
            // Rank Math
            $map = [
                'seo_title' => 'rank_math_title',
                'description' => 'rank_math_description',
                'canonical' => 'rank_math_canonical_url',
                'og_title' => 'rank_math_facebook_title',
                'og_description' => 'rank_math_facebook_description',
                'og_image' => 'rank_math_facebook_image',
                'x_title' => 'rank_math_twitter_title',
                'x_description' => 'rank_math_twitter_description',
                'x_image' => 'rank_math_twitter_image',
            ];
            foreach ($map as $key => $meta_key) {
                $val = isset($data[$key]) ? trim((string) $data[$key]) : '';
                if ($val === '') {
                    continue;
                }
                $existing = (string) get_post_meta($post_id, $meta_key, true);
                if ($existing !== '' && !$overwrite) {
                    continue;
                }
                update_post_meta($post_id, $meta_key, $val);
                $mapped[] = $key;
            }
            // Focus keywords: primary + secondary as comma list
            $kw_parts = [];
            if (!empty($data['focus_keyword'])) {
                $kw_parts[] = $data['focus_keyword'];
            }
            if (!empty($data['keywords']) && is_array($data['keywords'])) {
                $kw_parts = array_merge($kw_parts, $data['keywords']);
            }
            $kw_parts = array_values(array_unique(array_filter(array_map('trim', $kw_parts))));
            if ($kw_parts) {
                $existing = (string) get_post_meta($post_id, 'rank_math_focus_keyword', true);
                if ($overwrite || $existing === '') {
                    update_post_meta($post_id, 'rank_math_focus_keyword', implode(', ', $kw_parts));
                    $mapped[] = 'focus_keyword';
                    if (count($kw_parts) > 1) {
                        $mapped[] = 'keywords';
                    }
                }
            }
            if (isset($data['score']) && $data['score'] !== '' && $data['score'] !== null) {
                $existing = get_post_meta($post_id, 'rank_math_seo_score', true);
                if ($overwrite || $existing === '' || (int) $existing === 0) {
                    update_post_meta($post_id, 'rank_math_seo_score', max(0, min(100, (int) $data['score'])));
                    $mapped[] = 'score';
                }
            }
            if (!empty($data['robots']) && is_array($data['robots'])) {
                $existing = get_post_meta($post_id, 'rank_math_robots', true);
                if ($overwrite || empty($existing)) {
                    update_post_meta($post_id, 'rank_math_robots', $data['robots']);
                    $mapped[] = 'robots';
                }
            }
        }

        update_post_meta($post_id, '_bankai_seo_exported_to', $target);
        update_post_meta($post_id, '_bankai_seo_exported_at', time());

        return [
            'post_id' => $post_id,
            'exported' => !empty($mapped),
            'fields' => array_values(array_unique($mapped)),
            'message' => empty($mapped) ? 'nothing_written' : 'ok',
        ];
    }
}
