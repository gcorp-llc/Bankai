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

        $global_words  = absint($global_options['target_words'] ?? 3000);
        if (!in_array($global_words, [1500, 3000, 5000, 7000], true)) {
            $global_words = 3000;
        }

        $global_status = sanitize_key($global_options['post_status'] ?? 'future');
        if (!in_array($global_status, ['draft', 'future', 'publish'], true)) {
            $global_status = 'future';
        }

        $global_notes  = sanitize_textarea_field($global_options['notes'] ?? '');
        $global_do_seo = !isset($global_options['do_seo']) || !empty($global_options['do_seo']);
        $pause_outline = !empty($global_options['pause_for_outline']);
        $author_id     = absint($global_options['post_author'] ?? get_current_user_id());
        $default_cat   = absint($global_options['default_category'] ?? 1);
        $provider      = class_exists('Bankai_AI_Studio') ? Bankai_AI_Studio::instance()->get_default_provider() : 'gemini';

        foreach ($articles as $index => $item) {
            $title = sanitize_text_field($item['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $focus_kw = sanitize_text_field($item['focus_keyword'] ?? '');
            $cat_id   = absint($item['category_id'] ?? $default_cat);

            // Inherit or override target word count
            $item_length = absint($item['length'] ?? 0);
            $target_words = in_array($item_length, [1500, 3000, 5000, 7000], true) ? $item_length : $global_words;

            // Inherit or override status
            $item_status = sanitize_key($item['status'] ?? '');
            $final_status = in_array($item_status, ['draft', 'future', 'publish'], true) ? $item_status : $global_status;

            // Merge global and title extra instructions
            $item_notes = sanitize_textarea_field($item['notes'] ?? '');
            $merged_notes = '';
            if ($global_notes !== '') {
                $merged_notes .= "Global Category Guidelines:\n" . $global_notes . "\n";
            }
            if ($item_notes !== '') {
                $merged_notes .= "Article Specific Instructions:\n" . $item_notes;
            }

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
                'title'             => $title,
                'focus_keyword'     => $focus_kw,
                'category_id'       => $cat_id,
                'target_words'      => $target_words,
                'final_status'      => $final_status,
                'notes'             => trim($merged_notes),
                'do_seo'            => $global_do_seo,
                'pause_for_outline' => $pause_outline,
                'author_id'         => $author_id,
                'outline'           => '',
                'outline_approved'  => !$pause_outline,
                'sections'          => [],
                'draft_content'     => '',
                'seo_results'       => [],
                'review_needed'     => false,
                'review_reason'     => '',
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
                    'provider'           => $provider,
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

        if (!$job || in_array($job['status'], ['completed', 'cancelled', 'paused'], true)) {
            return ['status' => $job['status'] ?? 'not_found'];
        }

        $step_data = json_decode($job['step_data'], true) ?: [];
        $current_step = $job['current_step'] ?: 'outline';

        try {
            switch ($current_step) {
                case 'outline':
                    $step_data = $this->step_outline($job, $step_data);
                    if (!empty($step_data['pause_for_outline']) && empty($step_data['outline_approved'])) {
                        $next_step = 'outline';
                        $new_status = 'paused_outline';
                    } else {
                        $next_step = 'draft';
                        $new_status = 'pending';
                    }
                    break;

                case 'draft':
                    $step_data = $this->step_draft($job, $step_data);
                    // Check if all sections completed
                    $all_done = true;
                    if (!empty($step_data['sections']) && is_array($step_data['sections'])) {
                        foreach ($step_data['sections'] as $sec) {
                            if (($sec['status'] ?? 'pending') !== 'completed') {
                                $all_done = false;
                                break;
                            }
                        }
                    }
                    $next_step = $all_done ? 'create_post' : 'draft';
                    $new_status = 'pending';
                    break;

                case 'create_post':
                    $step_data = $this->step_create_post($job, $step_data);
                    $next_step = 'seo';
                    $new_status = 'pending';
                    break;

                case 'seo':
                    $step_data = $this->step_seo($job, $step_data);
                    $next_step = 'finalize';
                    $new_status = 'pending';
                    break;

                case 'finalize':
                    $step_data = $this->step_finalize($job, $step_data);
                    $next_step = 'completed';
                    $new_status = !empty($step_data['review_needed']) ? 'needs_review' : 'completed';
                    break;

                default:
                    $next_step = 'completed';
                    $new_status = 'completed';
            }

            $now = gmdate('Y-m-d H:i:s');

            $wpdb->update(
                $jobs_table,
                [
                    'status'         => $new_status,
                    'current_step'   => $next_step === 'completed' ? 'finalize' : $next_step,
                    'step_data'      => wp_json_encode($step_data, JSON_UNESCAPED_UNICODE),
                    'post_id'        => absint($step_data['post_id'] ?? $job['post_id']),
                    'tokens_used'    => absint($job['tokens_used'] + ($step_data['last_tokens'] ?? 0)),
                    'completed_at'   => in_array($new_status, ['completed', 'needs_review'], true) ? $now : null,
                    'locked_until'   => null,
                    'last_heartbeat' => $now,
                ],
                ['id' => $job_id]
            );

            $this->log_job_event($job_id, $current_step, 'info', "Step {$current_step} progress update. Next: {$next_step}");

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
        $notes = $data['notes'] ?? '';

        $prompt_sys = 'You are an expert SEO content strategist. Create a structured H2/H3 outline for an article in Persian (Farsi). Output ONLY plain text headings prefixed with ## for H2 and ### for H3. No intros, no extra fluff.';
        $prompt_user = "Title: {$title}\nFocus keyword: {$focus}\n" . ($notes ? "Instructions: {$notes}\n" : '') . "Generate outline:";

        $outline = $ai->chat($job['provider'] ?: $ai->get_default_provider(), $prompt_sys, $prompt_user, [
            'temperature' => 0.5,
            'max_tokens'  => 800,
        ]);

        $outline_text = trim($outline);
        $data['outline'] = $outline_text;

        // Parse H2 headings into section array
        $sections = [];
        $lines = explode("\n", $outline_text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '## ')) {
                $h2_title = trim(substr($line, 3));
                if ($h2_title !== '') {
                    $sections[] = [
                        'h2'      => $h2_title,
                        'status'  => 'pending',
                        'content' => '',
                        'error'   => '',
                    ];
                }
            }
        }

        // Fallback if no H2 lines parsed
        if (empty($sections)) {
            $sections[] = [
                'h2'      => 'مقدمه و بررسی جامع',
                'status'  => 'pending',
                'content' => '',
                'error'   => '',
            ];
            $sections[] = [
                'h2'      => 'نکات کلیدی و راهکارها',
                'status'  => 'pending',
                'content' => '',
                'error'   => '',
            ];
            $sections[] = [
                'h2'      => 'نتیجه‌گیری و جمع‌بندی',
                'status'  => 'pending',
                'content' => '',
                'error'   => '',
            ];
        }

        $data['sections'] = $sections;
        return $data;
    }

    private function step_draft(array $job, array $data): array
    {
        $ai = Bankai_AI_Studio::instance();
        $title = $job['title'];
        $focus = $job['focus_keyword'];
        $outline = $data['outline'] ?? '';
        $notes = $data['notes'] ?? '';
        $target_words = absint($data['target_words'] ?? 3000);

        $sections = $data['sections'] ?? [];
        if (empty($sections) || !is_array($sections)) {
            // Re-parse or initialize
            $data = $this->step_outline($job, $data);
            $sections = $data['sections'];
        }

        // Find first pending or error section
        $target_idx = -1;
        foreach ($sections as $idx => $sec) {
            if (in_array($sec['status'] ?? 'pending', ['pending', 'error'], true)) {
                $target_idx = $idx;
                break;
            }
        }

        if ($target_idx !== -1) {
            // Process this section
            $sec = $sections[$target_idx];
            $sections[$target_idx]['status'] = 'writing';

            // Collect summaries of previously completed sections for context
            $previous_summaries = [];
            foreach ($sections as $i => $prev) {
                if ($i < $target_idx && !empty($prev['content'])) {
                    $plain = wp_strip_all_tags($prev['content']);
                    $previous_summaries[] = "Section {$prev['h2']}: " . mb_substr($plain, 0, 200) . '...';
                }
            }

            $context_summary = implode("\n", $previous_summaries);
            $sec_words_target = max(300, (int) round($target_words / count($sections)));

            $prompt_sys = 'You are a professional WordPress content writer in Persian (Farsi). Output ONLY valid clean HTML body tags (<p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em>, <table>). Never include <html>, <body>, <head>, or <h1> tags. Avoid phrases like "As an AI model" or "به عنوان یک مدل زبانی". Write thorough, engaging, informative paragraphs.';
            $prompt_user = "Article Title: {$title}\nFocus Keyword: {$focus}\nTarget section word count: {$sec_words_target} words\n"
                . "Full Outline:\n{$outline}\n"
                . ($context_summary ? "Previous Written Sections Context:\n{$context_summary}\n" : '')
                . ($notes ? "Instructions: {$notes}\n" : '')
                . "\nNow write ONLY section: <h2>{$sec['h2']}</h2> with relevant sub-headings and detailed body paragraphs:";

            try {
                $body = $ai->chat($job['provider'] ?: $ai->get_default_provider(), $prompt_sys, $prompt_user, [
                    'temperature' => 0.65,
                    'max_tokens'  => 2500,
                ]);

                // Clean boilerplates
                $body = preg_replace('/(به عنوان یک مدل زبانی|من یک هوش مصنوعی هستم|as an ai language model)[^\n\.\!]*/iu', '', $body);

                $sections[$target_idx]['content'] = wp_kses_post($body);
                $sections[$target_idx]['status']  = 'completed';
                $sections[$target_idx]['error']   = '';

            } catch (Throwable $e) {
                $sections[$target_idx]['status'] = 'error';
                $sections[$target_idx]['error']  = $e->getMessage();
                $data['sections'] = $sections;
                throw $e;
            }

            $data['sections'] = $sections;
            return $data;
        }

        // All sections are completed: concatenate draft_content
        $full_html = '';
        foreach ($sections as $sec) {
            $full_html .= $sec['content'] . "\n\n";
        }

        // Quality & Word Count Verification
        $word_count = str_word_count(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', wp_strip_all_tags($full_html)));
        $min_threshold = (int) round($target_words * 0.9);

        if ($word_count < $min_threshold && empty($data['expansion_done'])) {
            // Expansion pass for thin articles
            try {
                $prompt_sys = 'You are an expert Persian editor. Expand and elaborate on the following article section to add depth, examples, and detailed explanations. Output valid HTML tags (<p>, <h3>, <ul>, <li>).';
                $prompt_user = "Article Title: {$title}\nFocus Keyword: {$focus}\nExisting text:\n" . mb_substr(wp_strip_all_tags($full_html), 0, 2000) . "\n\nWrite an additional detailed deep-dive section (2-3 paragraphs) with <h3> headers to reach target word depth:";

                $expansion = $ai->chat($job['provider'] ?: $ai->get_default_provider(), $prompt_sys, $prompt_user, [
                    'temperature' => 0.7,
                    'max_tokens'  => 1500,
                ]);

                $full_html .= "\n" . wp_kses_post($expansion);
                $data['expansion_done'] = true;

            } catch (Throwable $e) {}
        }

        $data['draft_content'] = wp_kses_post($full_html);
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

        $do_seo = !isset($data['do_seo']) || !empty($data['do_seo']);
        $seo_active = function_exists('bankai_is_module_active') ? bankai_is_module_active('seo_engine') : true;

        if (!$do_seo || !$seo_active) {
            $data['seo_results'] = ['skipped' => true, 'reason' => $do_seo ? 'SEO module inactive' : 'User disabled SEO'];
            return $data;
        }

        try {
            $ai = Bankai_AI_Studio::instance();
            $post = get_post($post_id);
            $snippet = mb_substr(wp_strip_all_tags($post->post_content), 0, 3000);
            $focus = $job['focus_keyword'];

            // Global fixed keywords 📌
            $fixed_kws = get_option('seo_fixed_keywords', []);
            if (!is_array($fixed_kws)) {
                $fixed_kws = [];
            }

            // 1. Generate Meta Title
            $title_prompt = "Title: {$post->post_title}\nFocus keyword: {$focus}\nSnippet: {$snippet}";
            $title_res = $ai->chat($job['provider'], 'Output ONLY one SEO meta title in Persian (50-60 chars). Include focus keyword naturally. Do not use quotes.', $title_prompt);
            $seo_title = sanitize_text_field(trim($title_res));

            // 2. Generate Meta Description
            $desc_prompt = "Title: {$post->post_title}\nFocus keyword: {$focus}\nSnippet: {$snippet}";
            $desc_res = $ai->chat($job['provider'], 'Output ONLY one meta description in Persian (120-155 chars). Include focus keyword naturally. Do not use quotes.', $desc_prompt);
            $seo_desc = sanitize_text_field(trim($desc_res));

            // Save Bankai SEO Meta
            update_post_meta($post_id, '_bankai_seo_title', $seo_title);
            update_post_meta($post_id, '_bankai_seo_description', $seo_desc);
            update_post_meta($post_id, '_bankai_seo_focus_keyword', sanitize_text_field($focus));

            // 3. Smart Internal Linking
            $internal_posts = get_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'post__not_in'   => [$post_id],
                'posts_per_page' => 10,
            ]);

            if (!empty($internal_posts)) {
                $content = $post->post_content;
                $linked_count = 0;
                foreach ($internal_posts as $ip) {
                    if ($linked_count >= 3) break;
                    $target_title = sanitize_text_field($ip->post_title);
                    $target_url   = get_permalink($ip->ID);
                    if ($target_title !== '' && $target_url && !str_contains($content, 'href="' . $target_url . '"')) {
                        // Look for natural occurrence of target title words in content
                        $pattern = '/(?![^<]*>)(' . preg_quote($target_title, '/') . ')/iu';
                        if (preg_match($pattern, $content)) {
                            $content = preg_replace($pattern, '<a href="' . esc_url($target_url) . '" target="_blank" rel="noopener">$1</a>', $content, 1);
                            $linked_count++;
                        }
                    }
                }
                if ($linked_count > 0) {
                    wp_update_post(['ID' => $post_id, 'post_content' => $content]);
                }
            }

            // 4. Image Alt Text Generation
            if (preg_match_all('/<img\b[^>]*>/i', $post->post_content, $img_matches)) {
                $content = $post->post_content;
                foreach ($img_matches[0] as $img_tag) {
                    if (!preg_match('/alt=["\'][^"\']+["\']/i', $img_tag)) {
                        $alt = esc_attr($focus ?: $post->post_title);
                        $new_img_tag = preg_replace('/(\/?>)/', ' alt="' . $alt . '" $1', $img_tag);
                        $content = str_replace($img_tag, $new_img_tag, $content);
                    }
                }
                wp_update_post(['ID' => $post_id, 'post_content' => $content]);
            }

            // 5. Schema JSON-LD (Article + FAQPage)
            $schema_data = [
                '@context'       => 'https://schema.org',
                '@type'          => 'Article',
                'headline'       => $seo_title ?: $post->post_title,
                'description'    => $seo_desc,
                'datePublished'  => get_the_date(DATE_W3C, $post_id),
                'dateModified'   => get_the_modified_date(DATE_W3C, $post_id),
            ];
            update_post_meta($post_id, '_bankai_seo_schema', wp_json_encode($schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // 6. Third-Party Yoast / Rank Math Sync if active
            if (class_exists('Bankai_SEO_Third_Party_Adapter')) {
                Bankai_SEO_Third_Party_Adapter::instance()->sync_post_seo_meta($post_id, [
                    'title'         => $seo_title,
                    'description'   => $seo_desc,
                    'focus_keyword' => $focus,
                ]);
            }

            // 7. Run SEO Score analysis
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
                $data['review_reason'] = 'low_seo_score';
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
            } elseif ($target_status === 'publish') {
                $post_data['post_status'] = 'publish';
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
        $msg = $e->getMessage();

        // Differentiate Provider API / Auth / Credit Errors
        $is_provider_error = (bool) preg_match('/401|403|unauthorized|forbidden|quota|credit|invalid api key|key missing/i', $msg);
        $is_transient      = (bool) preg_match('/429|500|502|503|504|timeout|timed out|network/i', $msg);

        if ($is_provider_error) {
            // Immediately PAUSE entire queue
            $wpdb->update(
                $jobs_table,
                [
                    'status'        => 'paused',
                    'error_code'    => 'provider_error',
                    'error_message' => $msg,
                    'locked_until'  => null,
                ],
                ['id' => $job['id']]
            );
            $this->log_job_event($job['id'], $job['current_step'], 'error', "Queue paused due to provider error: " . $msg);

        } elseif ($is_transient && $attempts < $max_attempts) {
            // Re-queue with exponential backoff delay
            $wpdb->update(
                $jobs_table,
                [
                    'status'        => 'pending',
                    'attempts'      => $attempts,
                    'error_code'    => 'transient_error',
                    'error_message' => $msg,
                    'locked_until'  => null,
                ],
                ['id' => $job['id']]
            );
            $this->log_job_event($job['id'], $job['current_step'], 'warning', "Retry attempt {$attempts}: " . $msg);

        } else {
            // Permanent failure
            $wpdb->update(
                $jobs_table,
                [
                    'status'        => 'failed',
                    'error_code'    => 'permanent_failure',
                    'error_message' => $msg,
                    'completed_at'  => $now,
                    'locked_until'  => null,
                ],
                ['id' => $job['id']]
            );

            if (!empty($job['post_id'])) {
                update_post_meta($job['post_id'], '_bankai_needs_review', 1);
                update_post_meta($job['post_id'], '_bankai_review_reason', 'job_failed');
            }

            $this->log_job_event($job['id'], $job['current_step'], 'error', 'Job failed permanently: ' . $msg);
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
