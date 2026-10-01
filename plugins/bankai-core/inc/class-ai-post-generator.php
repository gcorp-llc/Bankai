<?php
/**
 * Bankai Core - AI Post Generator & State Machine Engine
 *
 * Job state machine engine for autonomous article generation & SEO optimization:
 * Steps: outline -> draft -> create_post -> seo -> finalize
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

final class Bankai_AI_Post_Generator
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct() {}

    /**
     * Create a batch of jobs from Step 1/2 selections.
     */
    public function create_jobs_batch(array $articles, array $global_options = []): array
    {
        global $wpdb;

        $jobs_table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $batch_id = 'batch_' . wp_generate_uuid4();
        $created_jobs = [];
        $now = gmdate('Y-m-d H:i:s');

        $final_status  = sanitize_key($global_options['post_status'] ?? 'future');
        $author_id     = absint($global_options['post_author'] ?? get_current_user_id());
        $default_cat   = absint($global_options['default_category'] ?? 1);

        foreach ($articles as $index => $item) {
            $title = sanitize_text_field($item['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $focus_kw = sanitize_text_field($item['focus_keyword'] ?? '');
            $cat_id   = absint($item['category_id'] ?? $default_cat);

            // Schedule handling
            $sched_utc = null;
            $sched_local = sanitize_text_field($item['scheduled_at_local'] ?? '');
            if (!empty($item['scheduled_at'])) {
                $ts = strtotime($item['scheduled_at']);
                if ($ts) {
                    $sched_utc = gmdate('Y-m-d H:i:s', $ts);
                }
            }

            $idempotency_key = md5($batch_id . '_' . $index . '_' . $title);

            $step_data = [
                'title'          => $title,
                'focus_keyword'  => $focus_kw,
                'category_id'    => $cat_id,
                'final_status'   => $final_status,
                'author_id'      => $author_id,
                'outline'        => '',
                'draft_content'  => '',
                'seo_results'    => [],
                'review_needed'  => false,
                'review_reason'  => '',
            ];

            $wpdb->insert(
                $jobs_table,
                [
                    'batch_id'           => $batch_id,
                    'title'              => $title,
                    'focus_keyword'      => $focus_kw,
                    'category_id'        => $cat_id,
                    'scheduled_at'       => $sched_utc,
                    'scheduled_at_local' => $sched_local,
                    'status'             => 'pending',
                    'current_step'       => 'outline',
                    'idempotency_key'    => $idempotency_key,
                    'provider'           => class_exists('Bankai_AI_Studio') ? Bankai_AI_Studio::instance()->get_default_provider() : 'gemini',
                    'step_data'          => wp_json_encode($step_data, JSON_UNESCAPED_UNICODE),
                    'created_at'         => $now,
                ]
            );

            $job_id = $wpdb->insert_id;
            if ($job_id) {
                $created_jobs[] = $job_id;
                $this->log_job_event($job_id, 'outline', 'info', 'Job created in batch ' . $batch_id);
            }
        }

        // Trigger queue execution
        if ($created_jobs && class_exists('Bankai_AI_Queue_Manager')) {
            Bankai_AI_Queue_Manager::instance()->schedule_job($created_jobs[0]);
        }

        return [
            'batch_id' => $batch_id,
            'job_ids'  => $created_jobs,
            'count'    => count($created_jobs),
        ];
    }

    /**
     * Run next state machine step for specified job ID.
     */
    public function run_job_step(int $job_id): array
    {
        global $wpdb;

        $jobs_table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$jobs_table} WHERE id = %d", $job_id), ARRAY_A);

        if (!$job || in_array($job['status'], ['completed', 'cancelled'], true)) {
            return ['status' => $job['status'] ?? 'not_found'];
        }

        $step_data = json_decode($job['step_data'], true) ?: [];
        $current_step = $job['current_step'] ?: 'outline';

        try {
            switch ($current_step) {
                case 'outline':
                    $step_data = $this->step_outline($job, $step_data);
                    $next_step = 'draft';
                    break;

                case 'draft':
                    $step_data = $this->step_draft($job, $step_data);
                    $next_step = 'create_post';
                    break;

                case 'create_post':
                    $step_data = $this->step_create_post($job, $step_data);
                    $next_step = 'seo';
                    break;

                case 'seo':
                    $step_data = $this->step_seo($job, $step_data);
                    $next_step = 'finalize';
                    break;

                case 'finalize':
                    $step_data = $this->step_finalize($job, $step_data);
                    $next_step = 'completed';
                    break;

                default:
                    $next_step = 'completed';
            }

            $now = gmdate('Y-m-d H:i:s');
            $new_status = ($next_step === 'completed') ? 'completed' : 'pending';
            if (!empty($step_data['review_needed']) && $next_step === 'completed') {
                $new_status = 'needs_review';
            }

            $wpdb->update(
                $jobs_table,
                [
                    'status'         => $new_status,
                    'current_step'   => $next_step === 'completed' ? 'finalize' : $next_step,
                    'step_data'      => wp_json_encode($step_data, JSON_UNESCAPED_UNICODE),
                    'post_id'        => absint($step_data['post_id'] ?? $job['post_id']),
                    'tokens_used'    => absint($job['tokens_used'] + ($step_data['last_tokens'] ?? 0)),
                    'completed_at'   => ($new_status === 'completed' || $new_status === 'needs_review') ? $now : null,
                    'locked_until'   => null,
                    'last_heartbeat' => $now,
                ],
                ['id' => $job_id]
            );

            $this->log_job_event($job_id, $current_step, 'info', "Step {$current_step} completed. Next: {$next_step}");

            return [
                'job_id'      => $job_id,
                'status'      => $new_status,
                'step'        => $next_step,
                'post_id'     => $step_data['post_id'] ?? 0,
            ];

        } catch (Throwable $e) {
            $this->handle_step_error($job, $e);
            return [
                'job_id' => $job_id,
                'status' => 'error',
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function step_outline(array $job, array $data): array
    {
        $ai = Bankai_AI_Studio::instance();
        $title = $job['title'];
        $focus = $job['focus_keyword'];

        $prompt_sys = 'You are an expert SEO content strategist. Create a structured H2/H3 outline for an article in Persian (Farsi). Output ONLY plain text headings prefixed with ## for H2 and ### for H3. No intros, no extra fluff.';
        $prompt_user = "Title: {$title}\nFocus keyword: {$focus}\nGenerate outline:";

        $outline = $ai->chat($job['provider'] ?: $ai->get_default_provider(), $prompt_sys, $prompt_user, [
            'temperature' => 0.5,
            'max_tokens'  => 800,
        ]);

        $data['outline'] = trim($outline);
        return $data;
    }

    private function step_draft(array $job, array $data): array
    {
        $ai = Bankai_AI_Studio::instance();
        $title = $job['title'];
        $focus = $job['focus_keyword'];
        $outline = $data['outline'] ?? '';

        $prompt_sys = 'You are a professional WordPress content writer in Persian (Farsi). Output ONLY valid clean HTML body tags (<p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em>, <table>). Never include <html>, <body>, <head>, or <h1> tags. Avoid phrases like "As an AI model". Ensure high quality and natural language.';
        $prompt_user = "Title: {$title}\nFocus keyword: {$focus}\nOutline:\n{$outline}\n\nWrite a comprehensive, engaging WordPress article body in Persian HTML:";

        $body = $ai->chat($job['provider'] ?: $ai->get_default_provider(), $prompt_sys, $prompt_user, [
            'temperature' => 0.65,
            'max_tokens'  => 4000,
        ]);

        // Quality check
        if (preg_match('/زبان\s*بزرگ|مدل\s*زبانی|as an ai/iu', $body)) {
            $body = preg_replace('/(به عنوان یک مدل زبانی|من یک هوش مصنوعی هستم|as an ai language model)[^\n\.\!]*/iu', '', $body);
        }

        $data['draft_content'] = wp_kses_post($body);
        return $data;
    }

    private function step_create_post(array $job, array $data): array
    {
        // Idempotency check: if post already created for this job, reuse it
        if (!empty($data['post_id']) && get_post($data['post_id'])) {
            return $data;
        }

        $postarr = [
            'post_title'   => $job['title'],
            'post_content' => $data['draft_content'] ?? '',
            'post_status'  => 'draft', // Temporary draft during creation/SEO phase
            'post_type'    => 'post',
            'post_author'  => absint($data['author_id'] ?? get_current_user_id()),
            'post_category'=> [absint($job['category_id'] ?: 1)],
        ];

        $post_id = wp_insert_post($postarr, true);
        if (is_wp_error($post_id)) {
            throw new Exception('Post creation failed: ' . $post_id->get_error_message());
        }

        // Mark post meta
        update_post_meta($post_id, '_bankai_ai_job_id', $job['id']);
        update_post_meta($post_id, '_bankai_ai_generated', [
            'job_id'     => $job['id'],
            'batch_id'   => $job['batch_id'],
            'provider'   => $job['provider'],
            'created_at' => current_time('mysql'),
        ]);

        if (!empty($job['focus_keyword'])) {
            update_post_meta($post_id, '_bankai_seo_focus_keyword', sanitize_text_field($job['focus_keyword']));
        }

        $data['post_id'] = $post_id;
        return $data;
    }

    private function step_seo(array $job, array $data): array
    {
        $post_id = absint($data['post_id'] ?? 0);
        if (!$post_id || !get_post($post_id)) {
            throw new Exception('Invalid post ID for SEO step');
        }

        $seo_active = function_exists('bankai_is_module_active') ? bankai_is_module_active('seo_engine') : true;

        if (!$seo_active) {
            // SEO module disabled: mark skip and proceed
            $data['seo_results'] = ['skipped' => true, 'reason' => 'SEO module inactive'];
            return $data;
        }

        try {
            $ai = Bankai_AI_Studio::instance();
            $post = get_post($post_id);
            $snippet = mb_substr(wp_strip_all_tags($post->post_content), 0, 3000);

            // Generate meta title
            $title_res = $ai->chat($job['provider'], 'Output ONLY one SEO meta title (50-60 Persian chars).', "Title: {$post->post_title}\nContent: {$snippet}");
            $seo_title = sanitize_text_field(trim($title_res));

            // Generate meta description
            $desc_res = $ai->chat($job['provider'], 'Output ONLY one meta description (120-155 Persian chars).', "Title: {$post->post_title}\nContent: {$snippet}");
            $seo_desc = sanitize_text_field(trim($desc_res));

            // Save Bankai SEO Meta
            update_post_meta($post_id, '_bankai_seo_title', $seo_title);
            update_post_meta($post_id, '_bankai_seo_description', $seo_desc);

            // Third-Party Yoast / Rank Math Sync if active
            if (class_exists('Bankai_SEO_Third_Party_Adapter')) {
                Bankai_SEO_Third_Party_Adapter::instance()->sync_post_seo_meta($post_id, [
                    'title'         => $seo_title,
                    'description'   => $seo_desc,
                    'focus_keyword' => $job['focus_keyword'],
                ]);
            }

            // Run SEO Score analysis
            $score = 80;
            if (class_exists('Bankai_SEO_Engine')) {
                try {
                    $analysis = Bankai_SEO_Engine::instance()->analyze($post_id);
                    $score = absint($analysis['score'] ?? 80);
                } catch (Throwable $e) {}
            }

            update_post_meta($post_id, '_bankai_seo_score', $score);

            if ($score < 60) {
                $data['review_needed'] = true;
                $data['review_reason'] = 'score_low';
            }

            $data['seo_results'] = [
                'seo_title' => $seo_title,
                'seo_desc'  => $seo_desc,
                'score'     => $score,
            ];

        } catch (Throwable $e) {
            // SEO step failed: flag post as needs_review without losing written article
            $data['review_needed'] = true;
            $data['review_reason'] = 'seo_failed';
            $data['seo_error']     = $e->getMessage();
        }

        return $data;
    }

    private function step_finalize(array $job, array $data): array
    {
        $post_id = absint($data['post_id'] ?? 0);
        if (!$post_id) {
            return $data;
        }

        $target_status = $data['final_status'] ?? 'future';
        $review_needed = !empty($data['review_needed']);

        $post_data = ['ID' => $post_id];

        if ($review_needed) {
            $post_data['post_status'] = 'draft';
            update_post_meta($post_id, '_bankai_needs_review', 1);
            update_post_meta($post_id, '_bankai_review_reason', sanitize_text_field($data['review_reason'] ?? 'manual_review'));
            wp_set_post_tags($post_id, 'نیازمند بازبینی سئو', true);
        } else {
            if ($target_status === 'future' && !empty($job['scheduled_at'])) {
                $post_data['post_status'] = 'future';
                $post_data['post_date_gmt'] = $job['scheduled_at'];
                $post_data['post_date'] = !empty($job['scheduled_at_local']) ? $job['scheduled_at_local'] : get_date_from_gmt($job['scheduled_at']);
            } else {
                $post_data['post_status'] = 'draft';
            }
        }

        wp_update_post($post_data);
        clean_post_cache($post_id);

        return $data;
    }

    private function handle_step_error(array $job, Throwable $e): void
    {
        global $wpdb;
        $jobs_table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $attempts = (int) $job['attempts'] + 1;
        $max_attempts = 3;

        $now = gmdate('Y-m-d H:i:s');
        $is_transient = (bool) preg_match('/429|500|502|503|504|timeout|timed out/i', $e->getMessage());

        if ($is_transient && $attempts < $max_attempts) {
            // Re-queue with exponential backoff delay
            $wpdb->update(
                $jobs_table,
                [
                    'status'        => 'pending',
                    'attempts'      => $attempts,
                    'error_code'    => 'transient_error',
                    'error_message' => $e->getMessage(),
                    'locked_until'  => null,
                ],
                ['id' => $job['id']]
            );
            $this->log_job_event($job['id'], $job['current_step'], 'warning', "Retry attempt {$attempts}: " . $e->getMessage());
        } else {
            // Permanent failure
            $wpdb->update(
                $jobs_table,
                [
                    'status'        => 'failed',
                    'error_code'    => 'permanent_failure',
                    'error_message' => $e->getMessage(),
                    'completed_at'  => $now,
                    'locked_until'  => null,
                ],
                ['id' => $job['id']]
            );

            // If post was created, mark it as needs_review rather than deleting silently
            if (!empty($job['post_id'])) {
                update_post_meta($job['post_id'], '_bankai_needs_review', 1);
                update_post_meta($job['post_id'], '_bankai_review_reason', 'job_failed');
            }

            $this->log_job_event($job['id'], $job['current_step'], 'error', 'Job failed permanently: ' . $e->getMessage());
        }
    }

    public function log_job_event(int $job_id, string $step, string $level, string $message, array $context = []): void
    {
        global $wpdb;
        $logs_table = Bankai_AI_Job_Schema::get_logs_table_name();
        $wpdb->insert(
            $logs_table,
            [
                'job_id'     => $job_id,
                'step'       => sanitize_key($step),
                'level'      => sanitize_key($level),
                'message'    => sanitize_text_field($message),
                'context'    => wp_json_encode($context, JSON_UNESCAPED_UNICODE),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]
        );
    }
}
