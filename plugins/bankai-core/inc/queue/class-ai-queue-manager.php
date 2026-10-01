<?php
/**
 * Bankai Core - AI Queue Manager
 *
 * Chooses active driver in priority order:
 * 1. Action Scheduler (if available on site)
 * 2. Fallback Cron driver (WP-Cron + atomic lock DB loop)
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

final class Bankai_AI_Queue_Manager
{
    private static ?self $instance = null;
    private ?Bankai_AI_Queue_Driver_Interface $driver = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        $this->init_driver();
        add_action(Bankai_AI_Queue_Fallback_Cron::CRON_HOOK, [$this, 'run_fallback_cron']);
        add_action(Bankai_AI_Queue_Action_Scheduler::HOOK_NAME, [$this, 'run_action_scheduler_job'], 10, 1);
    }

    private function init_driver(): void
    {
        $as_driver = new Bankai_AI_Queue_Action_Scheduler();
        if ($as_driver->is_available()) {
            $this->driver = $as_driver;
            return;
        }

        $this->driver = new Bankai_AI_Queue_Fallback_Cron();
    }

    public function get_driver(): Bankai_AI_Queue_Driver_Interface
    {
        if (!$this->driver) {
            $this->init_driver();
        }
        return $this->driver;
    }

    public function schedule_job(int $job_id, int $delay_seconds = 0): bool
    {
        return $this->get_driver()->schedule_job($job_id, $delay_seconds);
    }

    public function cancel_job(int $job_id): bool
    {
        return $this->get_driver()->cancel_job($job_id);
    }

    public function process_queue(int $limit = 3, int $time_budget_sec = 25): array
    {
        return $this->get_driver()->process_queue($limit, $time_budget_sec);
    }

    public function run_fallback_cron(): void
    {
        $fallback = new Bankai_AI_Queue_Fallback_Cron();
        $fallback->process_queue();
    }

    public function run_action_scheduler_job(int $job_id): void
    {
        if (class_exists('Bankai_AI_Post_Generator')) {
            Bankai_AI_Post_Generator::instance()->run_job_step($job_id);
        }
    }
}
