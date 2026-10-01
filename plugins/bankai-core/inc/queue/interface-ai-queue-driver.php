<?php
/**
 * Bankai Core - Queue Driver Interface
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

interface Bankai_AI_Queue_Driver_Interface
{
    /**
     * Get unique identifier for the driver.
     */
    public function get_name(): string;

    /**
     * Check if driver is supported in current WP environment.
     */
    public function is_available(): bool;

    /**
     * Schedule a job execution or process cycle.
     */
    public function schedule_job(int $job_id, int $delay_seconds = 0): bool;

    /**
     * Process batch of queued jobs up to max concurrency / time budget.
     */
    public function process_queue(int $limit = 3, int $time_budget_sec = 25): array;

    /**
     * Cancel scheduled action/job execution.
     */
    public function cancel_job(int $job_id): bool;
}
