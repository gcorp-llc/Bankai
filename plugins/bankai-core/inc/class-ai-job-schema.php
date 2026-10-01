<?php
/**
 * Bankai Core - AI Job Schema & Database Migration
 *
 * Handles creation, migration, and retention cleanup for Bankai AI Jobs & Logs tables.
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

final class Bankai_AI_Job_Schema
{
    public const DB_VERSION_OPTION = 'bankai_ai_jobs_db_version';
    public const CURRENT_DB_VERSION = '1.0.0';

    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        // Hook into activation or init check
        add_action('admin_init', [$this, 'maybe_update_db']);
    }

    public static function get_jobs_table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'bankai_ai_jobs';
    }

    public static function get_logs_table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'bankai_ai_job_logs';
    }

    public function maybe_update_db(): void
    {
        $installed = get_option(self::DB_VERSION_OPTION, '0.0.0');
        if (version_compare($installed, self::CURRENT_DB_VERSION, '<')) {
            $this->create_tables();
        }
    }

    public function create_tables(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $jobs_table = self::get_jobs_table_name();
        $logs_table = self::get_logs_table_name();

        $sql_jobs = "CREATE TABLE {$jobs_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            batch_id varchar(64) NOT NULL DEFAULT '',
            post_id bigint(20) unsigned NOT NULL DEFAULT 0,
            title text NOT NULL,
            focus_keyword varchar(255) NOT NULL DEFAULT '',
            category_id bigint(20) unsigned NOT NULL DEFAULT 0,
            scheduled_at datetime DEFAULT NULL,
            scheduled_at_local varchar(64) NOT NULL DEFAULT '',
            status varchar(32) NOT NULL DEFAULT 'pending',
            current_step varchar(32) NOT NULL DEFAULT 'outline',
            attempts int(11) NOT NULL DEFAULT 0,
            idempotency_key varchar(128) NOT NULL DEFAULT '',
            provider varchar(64) NOT NULL DEFAULT '',
            model varchar(128) NOT NULL DEFAULT '',
            tokens_used int(11) NOT NULL DEFAULT 0,
            error_code varchar(64) NOT NULL DEFAULT '',
            error_message text NOT NULL,
            step_data longtext NOT NULL,
            created_at datetime NOT NULL,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            locked_until datetime DEFAULT NULL,
            last_heartbeat datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY status_sched (status, scheduled_at),
            KEY batch_id (batch_id),
            KEY post_id (post_id),
            UNIQUE KEY idempotency_key (idempotency_key)
        ) {$charset_collate};";

        $sql_logs = "CREATE TABLE {$logs_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            job_id bigint(20) unsigned NOT NULL,
            step varchar(32) NOT NULL DEFAULT '',
            level varchar(16) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY job_id (job_id)
        ) {$charset_collate};";

        dbDelta($sql_jobs);
        dbDelta($sql_logs);

        update_option(self::DB_VERSION_OPTION, self::CURRENT_DB_VERSION, false);
    }

    /**
     * Clean up completed, failed, or cancelled jobs older than retention threshold.
     * Does NOT purge active jobs ('running', 'pending') or 'needs_review' posts/jobs.
     */
    public function purge_old_jobs(int $retention_days = 30): int
    {
        global $wpdb;

        if ($retention_days < 1) {
            $retention_days = 30;
        }

        $jobs_table = self::get_jobs_table_name();
        $logs_table = self::get_logs_table_name();

        $cutoff = gmdate('Y-m-d H:i:s', time() - ($retention_days * DAY_IN_SECONDS));

        // Find old completed/failed/cancelled job IDs
        $old_job_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$jobs_table} WHERE status IN ('completed', 'failed', 'cancelled') AND completed_at IS NOT NULL AND completed_at < %s LIMIT 500",
                $cutoff
            )
        );

        if (empty($old_job_ids)) {
            return 0;
        }

        $id_placeholders = implode(',', array_fill(0, count($old_job_ids), '%d'));

        // Delete logs for these jobs
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$logs_table} WHERE job_id IN ({$id_placeholders})",
                ...$old_job_ids
            )
        );

        // Delete jobs
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$jobs_table} WHERE id IN ({$id_placeholders})",
                ...$old_job_ids
            )
        );

        return (int) $deleted;
    }

    /**
     * Drop plugin tables on uninstall if option permits.
     */
    public static function drop_tables(): void
    {
        global $wpdb;
        $jobs_table = self::get_jobs_table_name();
        $logs_table = self::get_logs_table_name();

        $wpdb->query("DROP TABLE IF EXISTS {$logs_table}");
        $wpdb->query("DROP TABLE IF EXISTS {$jobs_table}");
        delete_option(self::DB_VERSION_OPTION);
    }
}
