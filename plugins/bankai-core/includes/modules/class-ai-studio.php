<?php
/**
 * Bankai AI Studio Module
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;

class Bankai_AI_Studio {

    private static ?Bankai_AI_Studio $instance = null;

    public static function instance(): Bankai_AI_Studio {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_attachment', [$this, 'auto_generate_alt_text']);
    }

    public function auto_generate_alt_text(int $attachment_id): void {
        if (!wp_attachment_is_image($attachment_id)) {
            return;
        }
        $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if (empty($existing_alt)) {
            $filename = get_the_title($attachment_id);
            $clean_alt = ucwords(str_replace(['-', '_'], ' ', $filename));
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $clean_alt);
        }
    }
}
