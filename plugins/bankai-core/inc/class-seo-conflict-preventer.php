<?php
/**
 * Bankai Core - Schema Conflict Preventer & SEO Helpers
 *
 * Detects presence of third-party SEO plugins (Yoast SEO, Rank Math, All in One SEO, SEOPress)
 * and disables duplicate JSON-LD schema output automatically.
 *
 * @package Bankai
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_SEO_Conflict_Preventer {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action('wp_head', [$this, 'evaluate_schema_conflict'], 1);
    }

    /**
     * Checks if third-party schema engine is active.
     */
    public function is_third_party_schema_active(): bool {
        // 1. Rank Math
        if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
            return true;
        }

        // 2. Yoast SEO
        if (defined('WPSEO_VERSION') || class_exists('WPSEO_Options')) {
            return true;
        }

        // 3. All in One SEO
        if (defined('AIOSEO_VERSION') || class_exists('AIOSEO\\Plugin\\AIOSEO')) {
            return true;
        }

        // 4. SEOPress
        if (defined('SEOPRESS_VERSION')) {
            return true;
        }

        return false;
    }

    /**
     * Disables Bankai JSON-LD schema if third-party SEO plugin active
     */
    public function evaluate_schema_conflict(): void {
        if ($this->is_third_party_schema_active()) {
            add_filter('bankai_enable_schema_output', '__return_false');
        }
    }
}

Bankai_SEO_Conflict_Preventer::instance();
