<?php
/**
 * Bankai Core - Fallback WP-Cron + Atomic Lock Queue Driver
 *
 * Driver providing atomic DB lock, lock timeout recovery, time budget,
 * and background execution via WP-Cron or REST polling fallback.
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

class Bankai_AI_Queue_Fallback_Cron implements Bankai_AI_Queue_Driver_Interface
{
    public const CRON_HOOK = 'bankai_ai_process_fallback_queue';
    public const LOCK_TTL_SECONDS = 120;

    public function get_name(): string
    {
        return 'fallback_cron';
    }

    public function is_available(): bool
    {
        return true; // Always available as built-in fallback
    }

    public function schedule_job(int $job_id, int $delay_seconds = 0): bool
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + max(0, $delay_seconds), self::CRON_HOOK);
        }
        return true;
    }

    public function cancel_job(int $job_id): bool
    {
        global $wpdb;
        $table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $wpdb->update(
            $table,
            ['status' => 'cancelled', 'completed_at' => gmdate('Y-m-d H:i:s')],
            ['id' => $job_id]
        );
        return true;
    }

    /**
     * Claim and process pending jobs under strict atomic lock.
     */
    public function process_queue(int $limit = 3, int $time_budget_sec = 25): array
    {
        $start_time = microtime(true);
        $processed = [];

        // 1. Recover stale/orphaned locked jobs
        $this->recover_stale_locks();

        // 2. Fetch and claim queued jobs
        while ((microtime(true) - $start_time) < $time_budget_sec) {
            $job = $this->claim_next_job();
            if (!$job) {
                break; // No more pending jobs
            }

            // Execute job step using generator engine
            if (class_exists('Bankai_AI_Post_Generator')) {
                try {
                    $result = Bankai_AI_Post_Generator::instance()->run_job_step((int) $job['id']);
                    $processed[] = ['job_id' => $job['id'], 'result' => $result];
                } catch (Throwable $e) {
                    $processed[] = ['job_id' => $job['id'], 'error' => $e->getMessage()];
                }
            }

            if (count($processed) >= $limit) {
                break;
            }
        }

        // If remaining pending jobs exist, re-schedule event
        if ($this->has_pending_jobs()) {
            $this->schedule_job(0, 5);
        }

        return [
            'driver'    => 'fallback_cron',
            'processed' => $processed,
            'elapsed'   => round(microtime(true) - $start_time, 2),
        ];
    }

    /**
     * Atomically claim next job using SQL UPDATE with row-count check.
     */
    private function claim_next_job(): ?array
    {
        global $wpdb;
        $table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $now = gmdate('Y-m-d H:i:s');
        $lock_until = gmdate('Y-m-d H:i:s', time() + self::LOCK_TTL_SECONDS);

        // Find candidate pending job
        $candidate_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table}
                WHERE status IN ('pending', 'running')
                  AND (scheduled_at IS NULL OR scheduled_at <= %s)
                  AND (locked_until IS NULL OR locked_until < %s)
                ORDER BY scheduled_at ASC, id ASC LIMIT 1",
                $now,
                $now
            )
        );

        if (!$candidate_id) {
            return null;
        }

        // Atomic lock attempt
        $affected = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table}
                SET status = 'running',
                    locked_until = %s,
                    last_heartbeat = %s,
                    started_at = COALESCE(started_at, %s),
                    attempts = attempts + 1
                WHERE id = %d
                  AND (locked_until IS NULL OR locked_until < %s)",
                $lock_until,
                $now,
                $now,
                $candidate_id,
                $now
            )
        );

        if ($affected > 0) {
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $candidate_id), ARRAY_A);
            return is_array($row) ? $row : null;
        }

        return null;
    }

    /**
     * Release expired locks for dead/crashed background workers.
     */
    private function recover_stale_locks(): void
    {
        global $wpdb;
        $table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $now = gmdate('Y-m-d H:i:s');

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table}
                SET status = 'pending', locked_until = NULL
                WHERE status = 'running' AND locked_until < %s",
                $now
            )
        );
    }

    private function has_pending_jobs(): bool
    {
        global $wpdb;
        $table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $now = gmdate('Y-m-d H:i:s');

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE status IN ('pending', 'running') AND (scheduled_at IS NULL OR scheduled_at <= %s)",
                $now
            )
        );
        return (int) $count > 0;
    }
}
