<?php
/**
 * Bankai Media & Watermark Studio Module
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;

class Bankai_Media_Watermark {

    private static ?Bankai_Media_Watermark $instance = null;

    public static function instance(): Bankai_Media_Watermark {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('wp_handle_upload', [$this, 'process_uploaded_image']);
        add_filter('image_editor_output_format', [$this, 'enable_webp_avif_format']);
    }

    public function enable_webp_avif_format(array $formats): array {
        $formats['image/jpeg'] = 'image/webp';
        $formats['image/png']  = 'image/webp';
        return $formats;
    }

    public function process_uploaded_image(array $upload): array {
        // Automatic watermark and optimization can be applied here using GD / Imagick
        return $upload;
    }
}
