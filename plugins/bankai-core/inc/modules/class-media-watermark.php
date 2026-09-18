<?php
/**
 * Bankai Core - Next-Gen Media Engine & Dynamic Watermark Studio
 *
 * @package Bankai
 * @subpackage Modules
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

    public static function get_instance(): Bankai_Media_Watermark {
        return self::instance();
    }

    private function __construct() {
        add_filter('wp_handle_upload', [$this, 'process_uploaded_image'], 20);
        add_action('wp_ajax_bankai_bulk_convert_media', [$this, 'ajax_bulk_convert']);
        add_action('wp_ajax_bankai_save_watermark_settings', [$this, 'ajax_save_watermark_settings']);
    }

    /**
     * Process uploaded images (watermark + optional future WebP)
     */
    public function process_uploaded_image(array $upload): array {
        // Only process successful image uploads
        if (empty($upload['file']) || empty($upload['type'])) {
            return $upload;
        }

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($upload['type'], $allowed_mimes, true)) {
            return $upload;
        }

        $file_path = $upload['file'];
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return $upload;
        }

        $settings = $this->get_watermark_settings();

        if (!empty($settings['enabled']) && extension_loaded('gd')) {
            $this->apply_text_watermark($file_path, $settings);
        }

        return $upload;
    }

    /**
     * Get watermark settings with defaults
     */
    private function get_watermark_settings(): array {
        $defaults = [
            'enabled'  => true,
            'position' => 'bottom-right',
            'opacity'  => 75,
            'text'     => '© BANKAI MEDIA',
        ];

        $saved = get_option('bankai_watermark_settings', []);
        return wp_parse_args(is_array($saved) ? $saved : [], $defaults);
    }

    /**
     * Apply simple text watermark using GD
     */
    private function apply_text_watermark(string $file_path, array $settings): void {
        $info = @getimagesize($file_path);
        if (!$info || empty($info['mime'])) {
            return;
        }

        $mime = $info['mime'];
        $image = null;

        switch ($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($file_path);
                if ($image) {
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($file_path);
                }
                break;
        }

        if (!$image) {
            return;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        $text        = (string) ($settings['text'] ?? 'BANKAI');
        $font_size   = 5; // GD built-in font
        $font_width  = imagefontwidth($font_size) * strlen($text);
        $font_height = imagefontheight($font_size);
        $margin      = 15;

        $x = $margin;
        $y = $margin;

        switch ($settings['position'] ?? 'bottom-right') {
            case 'top-center':
                $x = (int) (($width - $font_width) / 2);
                break;
            case 'top-right':
                $x = $width - $font_width - $margin;
                break;
            case 'center-left':
                $y = (int) (($height - $font_height) / 2);
                break;
            case 'center':
                $x = (int) (($width - $font_width) / 2);
                $y = (int) (($height - $font_height) / 2);
                break;
            case 'center-right':
                $x = $width - $font_width - $margin;
                $y = (int) (($height - $font_height) / 2);
                break;
            case 'bottom-left':
                $y = $height - $font_height - $margin;
                break;
            case 'bottom-center':
                $x = (int) (($width - $font_width) / 2);
                $y = $height - $font_height - $margin;
                break;
            case 'bottom-right':
            default:
                $x = $width - $font_width - $margin;
                $y = $height - $font_height - $margin;
                break;
        }

        // White text with slight transparency simulation (GD limitation)
        $color = imagecolorallocatealpha($image, 255, 255, 255, 30);
        if ($color === false) {
            $color = imagecolorallocate($image, 255, 255, 255);
        }

        imagestring($image, $font_size, (int) $x, (int) $y, $text, $color);

        // Save back
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($image, $file_path, 88);
                break;
            case 'image/png':
                imagepng($image, $file_path, 7);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($image, $file_path, 85);
                }
                break;
        }

        imagedestroy($image);
    }

    /**
     * Save watermark studio settings via AJAX
     */
    public function ajax_save_watermark_settings(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $allowed_positions = [
            'top-left', 'top-center', 'top-right',
            'center-left', 'center', 'center-right',
            'bottom-left', 'bottom-center', 'bottom-right',
        ];

        $position = isset($_POST['position']) ? sanitize_text_field(wp_unslash($_POST['position'])) : 'bottom-right';
        if (!in_array($position, $allowed_positions, true)) {
            $position = 'bottom-right';
        }

        $opacity = isset($_POST['opacity']) ? (int) $_POST['opacity'] : 75;
        $opacity = max(10, min(100, $opacity));

        $text = isset($_POST['text']) ? sanitize_text_field(wp_unslash($_POST['text'])) : '© BANKAI MEDIA';
        $text = mb_substr($text, 0, 80);

        $settings = [
            'enabled'  => true,
            'position' => $position,
            'opacity'  => $opacity,
            'text'     => $text,
        ];

        update_option('bankai_watermark_settings', $settings, false);

        wp_send_json_success([
            'message'  => __('Watermark studio settings updated.', 'bankai-core'),
            'settings' => $settings,
        ]);
    }

    /**
     * Bulk convert / optimize media (placeholder for background job)
     */
    public function ajax_bulk_convert(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        // In a real implementation this would queue a background process
        wp_send_json_success([
            'message' => __('Bulk image optimization started in background.', 'bankai-core'),
            'status'  => 'queued',
        ]);
    }
}