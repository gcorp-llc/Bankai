<?php
/**
 * Bankai Core - Third-Party SEO Plugin Sync Adapter
 *
 * Safe sync adapter for Yoast SEO and Rank Math metadata.
 * Only syncs if enabled by user and corresponding plugin is active.
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

final class Bankai_SEO_Third_Party_Adapter
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct() {}

    public function is_sync_enabled(): bool
    {
        $opt = function_exists('bankai_get_option')
            ? bankai_get_option('seo_third_party_sync', true)
            : get_option('bankai_seo_third_party_sync', true);

        return (bool) $opt;
    }

    public function is_yoast_active(): bool
    {
        return defined('WPSEO_VERSION') || class_exists('WPSEO_Options');
    }

    public function is_rank_math_active(): bool
    {
        return defined('RANK_MATH_VERSION') || class_exists('RankMath');
    }

    /**
     * Sync generated SEO title, description, and focus keyword to active plugins.
     * Never overwrites existing non-empty user values unless requested.
     */
    public function sync_post_seo_meta(int $post_id, array $meta): array
    {
        if (!$this->is_sync_enabled()) {
            return ['synced' => false, 'reason' => 'Sync option disabled'];
        }

        $title = sanitize_text_field($meta['title'] ?? '');
        $desc  = sanitize_text_field($meta['description'] ?? '');
        $kw    = sanitize_text_field($meta['focus_keyword'] ?? '');

        $synced_to = [];

        // Yoast SEO Sync
        if ($this->is_yoast_active()) {
            if ($title !== '') {
                $existing = get_post_meta($post_id, '_yoast_wpseo_title', true);
                if (empty($existing)) {
                    update_post_meta($post_id, '_yoast_wpseo_title', $title);
                }
            }
            if ($desc !== '') {
                $existing = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
                if (empty($existing)) {
                    update_post_meta($post_id, '_yoast_wpseo_metadesc', $desc);
                }
            }
            if ($kw !== '') {
                $existing = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
                if (empty($existing)) {
                    update_post_meta($post_id, '_yoast_wpseo_focuskw', $kw);
                }
            }
            $synced_to[] = 'yoast';
        }

        // Rank Math Sync
        if ($this->is_rank_math_active()) {
            if ($title !== '') {
                $existing = get_post_meta($post_id, 'rank_math_title', true);
                if (empty($existing)) {
                    update_post_meta($post_id, 'rank_math_title', $title);
                }
            }
            if ($desc !== '') {
                $existing = get_post_meta($post_id, 'rank_math_description', true);
                if (empty($existing)) {
                    update_post_meta($post_id, 'rank_math_description', $desc);
                }
            }
            if ($kw !== '') {
                $existing = get_post_meta($post_id, 'rank_math_focus_keyword', true);
                if (empty($existing)) {
                    update_post_meta($post_id, 'rank_math_focus_keyword', $kw);
                }
            }
            $synced_to[] = 'rank_math';
        }

        return [
            'synced'    => !empty($synced_to),
            'adapters'  => $synced_to,
        ];
    }
}
