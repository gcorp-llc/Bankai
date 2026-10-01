<?php
/**
 * Bankai Core - AI Generator Wizard Admin Page & UI Hooks
 *
 * Handles button injection on edit.php, admin hidden page registration,
 * admin notice for posts requiring review, pre_get_posts filter, and REST/AJAX endpoints.
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

final class Bankai_AI_Generator_Wizard
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'register_hidden_wizard_page']);
        add_action('admin_head-edit.php', [$this, 'inject_button_script']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_wizard_assets']);
        add_action('admin_notices', [$this, 'render_needs_review_notice']);
        add_filter('views_edit-post', [$this, 'add_needs_review_subsubsub_link']);
        add_action('pre_get_posts', [$this, 'filter_posts_by_needs_review']);

        // REST / AJAX Endpoints
        add_action('wp_ajax_bankai_ai_wizard_suggest_titles', [$this, 'ajax_suggest_titles']);
        add_action('wp_ajax_bankai_ai_wizard_start_batch', [$this, 'ajax_start_batch']);
        add_action('wp_ajax_bankai_ai_wizard_get_progress', [$this, 'ajax_get_progress']);
        add_action('wp_ajax_bankai_ai_wizard_control_job', [$this, 'ajax_control_job']);
        add_action('wp_ajax_bankai_dismiss_needs_review_notice', [$this, 'ajax_dismiss_notice']);
    }

    public function register_hidden_wizard_page(): void
    {
        add_submenu_page(
            null, // Hidden page
            __('تولید مقاله با هوش مصنوعی', 'bankai-core'),
            __('تولید مقاله با هوش مصنوعی', 'bankai-core'),
            'publish_posts',
            'bankai-ai-generator-wizard',
            [$this, 'render_wizard_page']
        );
    }

    public function enqueue_wizard_assets(string $hook_suffix): void
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if ($page !== 'bankai-ai-generator-wizard' && $hook_suffix !== 'edit.php') {
            return;
        }

        // Only enqueue scoped assets for wizard and edit.php
        wp_enqueue_style(
            'bankai-admin-css',
            bankai_asset_url('css/bankai-admin.css'),
            [],
            BANKAI_CORE_VERSION
        );

        if ($page === 'bankai-ai-generator-wizard') {
            wp_enqueue_script(
                'bankai-ai-wizard-js',
                bankai_asset_url('js/bankai-ai-wizard.js'),
                ['jquery'],
                BANKAI_CORE_VERSION . '.' . time(),
                true
            );

            wp_localize_script('bankai-ai-wizard-js', 'bankaiWizardData', [
                'ajaxUrl'    => admin_url('admin-ajax.php'),
                'nonce'      => wp_create_nonce('bankai_admin_nonce'),
                'timeZone'   => function_exists('wp_timezone_string') ? wp_timezone_string() : 'UTC',
                'categories' => get_categories(['hide_empty' => false]),
                'authors'    => get_users(['capability' => 'edit_posts', 'fields' => ['ID', 'display_name']]),
            ]);
        }
    }

    public function inject_button_script(): void
    {
        if (!current_user_can('publish_posts')) {
            return;
        }

        $ai_active = function_exists('bankai_is_module_active') ? bankai_is_module_active('ai_studio') : true;
        $wizard_url = admin_url('admin.php?page=bankai-ai-generator-wizard&_wpnonce=' . wp_create_nonce('bankai_ai_wizard'));

        ?>
        <script id="bankai-ai-btn-injector">
            document.addEventListener('DOMContentLoaded', function() {
                var addNewBtn = document.querySelector('.wrap .page-title-action');
                if (!addNewBtn) return;

                var aiBtn = document.createElement('a');
                aiBtn.href = "<?php echo esc_url($wizard_url); ?>";
                aiBtn.className = "page-title-action bankai-ai-gen-btn";
                aiBtn.style.cssText = "background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; margin-right: 8px; font-weight: 600; box-shadow: 0 2px 6px rgba(99,102,241,0.3); display: inline-flex; align-items: center; gap: 6px;";
                aiBtn.innerHTML = "✨ تولید مقاله با AI";

                <?php if (!$ai_active) : ?>
                aiBtn.href = "<?php echo esc_url(admin_url('admin.php?page=bankai-core#tab-ai-studio')); ?>";
                aiBtn.title = "ماژول AI غیرفعال است. جهت فعال‌سازی کلیک کنید.";
                aiBtn.style.opacity = "0.7";
                <?php endif; ?>

                addNewBtn.parentNode.insertBefore(aiBtn, addNewBtn.nextSibling);
            });
        </script>
        <?php
    }

    public function render_wizard_page(): void
    {
        if (!current_user_can('publish_posts')) {
            wp_die(__('شما دسترسی لازم برای این بخش را ندارید.', 'bankai-core'));
        }

        bankai_render_view('admin/ai-wizard.php', []);
    }

    public function render_needs_review_notice(): void
    {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'edit-post') {
            return;
        }

        if (get_user_meta(get_current_user_id(), 'bankai_dismissed_review_notice', true)) {
            return;
        }

        $query = new WP_Query([
            'post_type'      => 'post',
            'post_status'    => 'draft',
            'meta_key'       => '_bankai_needs_review',
            'meta_value'     => '1',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        $count = $query->post_count;
        if ($count <= 0) {
            return;
        }

        $filter_url = admin_url('edit.php?bankai_needs_review=1');
        ?>
        <div class="notice notice-warning is-dismissible bankai-review-notice">
            <p>
                <strong>تولید مقاله Bankai AI:</strong>
                تعداد <?php echo esc_html($count); ?> مقاله نیاز به بازبینی سئو یا اصلاح دارند.
                <a href="<?php echo esc_url($filter_url); ?>" class="button button-secondary" style="margin-right: 8px;">مشاهده و بازبینی مقالات</a>
            </p>
        </div>
        <?php
    }

    public function add_needs_review_subsubsub_link(array $views): array
    {
        $query = new WP_Query([
            'post_type'      => 'post',
            'post_status'    => 'draft',
            'meta_key'       => '_bankai_needs_review',
            'meta_value'     => '1',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        $count = $query->post_count;
        if ($count > 0) {
            $class = (!empty($_GET['bankai_needs_review'])) ? 'class="current"' : '';
            $url   = admin_url('edit.php?bankai_needs_review=1');
            $views['bankai_needs_review'] = sprintf(
                '<a href="%s" %s>نیازمند بازبینی <span class="count">(%d)</span></a>',
                esc_url($url),
                $class,
                $count
            );
        }

        return $views;
    }

    public function filter_posts_by_needs_review(WP_Query $query): void
    {
        if (is_admin() && $query->is_main_query() && !empty($_GET['bankai_needs_review'])) {
            $query->set('meta_key', '_bankai_needs_review');
            $query->set('meta_value', '1');
        }
    }

    /* ------------------------------------------------------------------
     * AJAX Handlers for Wizard
     * ----------------------------------------------------------------*/

    public function ajax_suggest_titles(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('publish_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $count       = min(20, max(1, absint($_POST['count'] ?? 10)));
        $topic       = sanitize_text_field(wp_unslash($_POST['topic'] ?? ''));
        $category_id = absint($_POST['category_id'] ?? 0);

        // Fetch published/scheduled titles to avoid duplication
        $existing_posts = get_posts([
            'post_type'      => 'post',
            'post_status'    => ['publish', 'future', 'draft'],
            'posts_per_page' => 150,
            'fields'         => 'post_title',
        ]);

        $existing_titles = array_map('sanitize_text_field', $existing_posts);

        $site_name = get_bloginfo('name');
        $site_desc = get_bloginfo('description');

        $ai = Bankai_AI_Studio::instance();

        $prompt_sys = 'You are an expert SEO content planner. Output ONLY a valid JSON array of objects representing article title suggestions. Each object MUST contain: "title" (Persian string), "focus_keyword" (Persian keyword), "category_suggestion" (string), "search_intent" (informational/commercial/transactional), and "reason" (short explanation). Do NOT output markdown fences.';
        $prompt_user = "Site: {$site_name} - {$site_desc}\nTopic focus: {$topic}\nNumber of suggestions: {$count}\nExisting titles to avoid:\n" . implode("\n", array_slice($existing_titles, 0, 50));

        try {
            $raw = $ai->chat($ai->get_default_provider(), $prompt_sys, $prompt_user, [
                'temperature' => 0.7,
                'max_tokens'  => 2000,
            ]);

            // Clean JSON
            $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
            $raw = preg_replace('/\s*```$/', '', $raw);

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                throw new Exception('پاسخ هوش مصنوعی ساختار JSON معتبر نداشت.');
            }

            $suggestions = [];
            foreach ($decoded as $item) {
                if (is_array($item) && !empty($item['title'])) {
                    $t = sanitize_text_field($item['title']);
                    if (!in_array($t, $existing_titles, true)) {
                        $suggestions[] = [
                            'title'               => $t,
                            'focus_keyword'       => sanitize_text_field($item['focus_keyword'] ?? ''),
                            'category_suggestion' => sanitize_text_field($item['category_suggestion'] ?? ''),
                            'search_intent'       => sanitize_text_field($item['search_intent'] ?? 'informational'),
                            'reason'              => sanitize_text_field($item['reason'] ?? ''),
                        ];
                    }
                }
            }

            wp_send_json_success(['suggestions' => $suggestions]);

        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }

    public function ajax_start_batch(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('publish_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $articles = isset($_POST['articles']) && is_array($_POST['articles']) ? wp_unslash($_POST['articles']) : [];
        $options  = isset($_POST['options']) && is_array($_POST['options']) ? wp_unslash($_POST['options']) : [];

        if (empty($articles)) {
            wp_send_json_error(['message' => 'هیچ مقاله‌ای انتخاب نشده است.'], 400);
        }

        $generator = Bankai_AI_Post_Generator::instance();
        $batch = $generator->create_jobs_batch($articles, $options);

        wp_send_json_success($batch);
    }

    public function ajax_get_progress(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('publish_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $jobs_table = Bankai_AI_Job_Schema::get_jobs_table_name();

        $batch_id = sanitize_key($_POST['batch_id'] ?? '');
        $where = $batch_id !== '' ? $wpdb->prepare('WHERE batch_id = %s', $batch_id) : 'ORDER BY id DESC LIMIT 50';

        $jobs = $wpdb->get_results("SELECT * FROM {$jobs_table} {$where}", ARRAY_A);

        // Run fallback processing cycle on polling if cron disabled
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && class_exists('Bankai_AI_Queue_Manager')) {
            Bankai_AI_Queue_Manager::instance()->process_queue(1, 15);
        }

        wp_send_json_success(['jobs' => $jobs ?: []]);
    }

    public function ajax_control_job(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('publish_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $jobs_table = Bankai_AI_Job_Schema::get_jobs_table_name();

        $job_id = absint($_POST['job_id'] ?? 0);
        $action = sanitize_key($_POST['control_action'] ?? '');

        if (!$job_id) {
            wp_send_json_error(['message' => 'شناسه نامعتبر است.'], 400);
        }

        switch ($action) {
            case 'retry':
                $wpdb->update($jobs_table, ['status' => 'pending', 'attempts' => 0, 'locked_until' => null], ['id' => $job_id]);
                if (class_exists('Bankai_AI_Queue_Manager')) {
                    Bankai_AI_Queue_Manager::instance()->schedule_job($job_id);
                }
                break;

            case 'cancel':
                $wpdb->update($jobs_table, ['status' => 'cancelled', 'completed_at' => gmdate('Y-m-d H:i:s')], ['id' => $job_id]);
                break;

            case 'delete':
                $wpdb->delete($jobs_table, ['id' => $job_id]);
                break;
        }

        wp_send_json_success(['message' => 'انجام شد']);
    }

    public function ajax_dismiss_notice(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        update_user_meta(get_current_user_id(), 'bankai_dismissed_review_notice', true);
        wp_send_json_success();
    }
}
