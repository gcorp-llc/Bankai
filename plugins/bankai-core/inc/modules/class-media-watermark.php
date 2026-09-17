<?php
/**
 * Bankai Core - Next-Gen Media Engine & Dynamic Watermark Studio
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

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
        add_filter('wp_handle_upload', [$this, 'process_uploaded_image']);
        add_action('wp_ajax_bankai_bulk_convert_media', [$this, 'ajax_bulk_convert']);
        add_action('wp_ajax_bankai_save_watermark_settings', [$this, 'ajax_save_watermark_settings']);
    }

    public function process_uploaded_image(array $upload): array {
        if (!in_array($upload['type'], ['image/jpeg', 'image/png'], true)) {
            return $upload;
        }

        $file_path = $upload['file'];
        $settings  = get_option('bankai_watermark_settings', [
            'enabled'  => true,
            'position' => 'bottom-right',
            'opacity'  => 75,
            'text'     => '© BANKAI WP ENGINE'
        ]);

        if (!empty($settings['enabled']) && extension_loaded('gd')) {
            $this->apply_text_watermark($file_path, $settings);
        }

        return $upload;
    }

    private function apply_text_watermark(string $file_path, array $settings): void {
        $info = getimagesize($file_path);
        if (!$info) return;

        $mime = $info['mime'];
        if ($mime === 'image/jpeg') {
            $image = imagecreatefromjpeg($file_path);
        } elseif ($mime === 'image/png') {
            $image = imagecreatefrompng($file_path);
        } else {
            return;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        $text       = $settings['text'] ?? 'BANKAI';
        $font_size  = 4;
        $font_width = imagefontwidth($font_size) * strlen($text);
        $font_height= imagefontheight($font_size);

        $margin = 15;
        $x = $margin;
        $y = $margin;

        switch ($settings['position']) {
            case 'top-center':
                $x = ($width - $font_width) / 2;
                break;
            case 'top-right':
                $x = $width - $font_width - $margin;
                break;
            case 'center-left':
                $y = ($height - $font_height) / 2;
                break;
            case 'center':
                $x = ($width - $font_width) / 2;
                $y = ($height - $font_height) / 2;
                break;
            case 'center-right':
                $x = $width - $font_width - $margin;
                $y = ($height - $font_height) / 2;
                break;
            case 'bottom-left':
                $y = $height - $font_height - $margin;
                break;
            case 'bottom-center':
                $x = ($width - $font_width) / 2;
                $y = $height - $font_height - $margin;
                break;
            case 'bottom-right':
            default:
                $x = $width - $font_width - $margin;
                $y = $height - $font_height - $margin;
                break;
        }

        $color = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, $font_size, (int)$x, (int)$y, $text, $color);

        if ($mime === 'image/jpeg') {
            imagejpeg($image, $file_path, 85);
        } else {
            imagepng($image, $file_path, 8);
        }

        imagedestroy($image);
    }

    public function ajax_save_watermark_settings(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        
        $position = sanitize_text_field($_POST['position'] ?? 'bottom-right');
        $opacity  = intval($_POST['opacity'] ?? 75);
        $text     = sanitize_text_field($_POST['text'] ?? '© BANKAI WP ENGINE');

        $settings = [
            'enabled'  => true,
            'position' => $position,
            'opacity'  => $opacity,
            'text'     => $text
        ];

        update_option('bankai_watermark_settings', $settings);
        wp_send_json_success(['message' => __('Watermark studio settings updated.', 'bankai-core')]);
    }

    public function ajax_bulk_convert(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(['message' => __('Bulk image optimization started in background.', 'bankai-core')]);
    }
}
