<?php
/**
 * Bankai Core - Action Scheduler Queue Driver
 *
 * Driver using Action Scheduler (as_schedule_single_action) if available.
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

class Bankai_AI_Queue_Action_Scheduler implements Bankai_AI_Queue_Driver_Interface
{
    public const HOOK_NAME = 'bankai_ai_process_single_job';

    public function get_name(): string
    {
        return 'action_scheduler';
    }

    public function is_available(): bool
    {
        return function_exists('as_schedule_single_action') && function_exists('as_unschedule_all_actions');
    }

    public function schedule_job(int $job_id, int $delay_seconds = 0): bool
    {
        if (!$this->is_available()) {
            return false;
        }

        $timestamp = time() + max(0, $delay_seconds);
        as_schedule_single_action($timestamp, self::HOOK_NAME, ['job_id' => $job_id], 'bankai-ai-generator');
        return true;
    }

    public function process_queue(int $limit = 3, int $time_budget_sec = 25): array
    {
        // Action Scheduler manages its own runner loop via hooks
        return ['processed' => 0, 'driver' => 'action_scheduler'];
    }

    public function cancel_job(int $job_id): bool
    {
        if (!$this->is_available()) {
            return false;
        }

        as_unschedule_all_actions(self::HOOK_NAME, ['job_id' => $job_id], 'bankai-ai-generator');
        return true;
    }
}
