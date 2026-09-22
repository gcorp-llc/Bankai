<?php
/**
 * Bankai Core - Media Engine, Watermark, Compression & Lazy Load
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

class Bankai_Media_Watermark
{
    private static ?Bankai_Media_Watermark $instance = null;

    public static function instance(): Bankai_Media_Watermark
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_Media_Watermark
    {
        return self::instance();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('media_watermark')) {
            return;
        }

        add_filter('wp_handle_upload', [$this, 'process_uploaded_image'], 20);

        // Content filters
        if ($this->is_sub_active('lazy_loading') || $this->setting('lazy_load', true)) {
            add_filter('the_content', [$this, 'filter_content_lazy'], 20);
            add_filter('post_thumbnail_html', [$this, 'filter_thumb_lazy'], 20, 5);
            add_filter('wp_get_attachment_image_attributes', [$this, 'filter_attachment_attrs'], 20, 3);
        }

        if ($this->is_sub_active('dynamic_watermarking') || $this->setting('apply_content', false)) {
            // Optional: watermark only on upload; content images already processed if uploaded after enable
        }

        add_action('wp_ajax_bankai_bulk_convert_media', [$this, 'ajax_bulk_convert']);
        add_action('wp_ajax_bankai_save_watermark_settings', [$this, 'ajax_save_watermark_settings']);
        add_action('wp_ajax_bankai_regenerate_thumbs', [$this, 'ajax_regenerate_thumbs']);
        add_action('wp_ajax_bankai_media_env', [$this, 'ajax_media_env']);
        add_action('wp_ajax_bankai_strip_exif_bulk', [$this, 'ajax_strip_exif_bulk']);
    }

    private function is_sub_active(string $id): bool
    {
        $mods = function_exists('bankai_get_option') ? bankai_get_option('media_modules', []) : [];
        if (!is_array($mods) || !array_key_exists($id, $mods)) {
            return true;
        }
        return (bool) $mods[$id];
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    private function setting(string $key, $default = null)
    {
        $s = $this->get_settings();
        return array_key_exists($key, $s) ? $s[$key] : $default;
    }

    public function get_settings(): array
    {
        $defaults = [
            'enabled'         => true,
            'type'            => 'text', // text | image
            'position'        => 'bottom-right',
            'opacity'         => 75,
            'text'            => '© BANKAI MEDIA',
            'image_id'        => 0,
            'image_url'       => '',
            'quality'         => 82,
            'min_dimension'   => 300,
            'apply_upload'    => true,
            'apply_content'   => false,
            'lazy_load'       => true,
            'strip_exif'      => true,
            'convert_webp'    => true,
            'keep_original'   => true,
        ];

        $saved = get_option('bankai_watermark_settings', []);
        if (!is_array($saved)) {
            $saved = [];
        }
        // Also merge from bankai_core_settings.watermark_settings if present
        if (function_exists('bankai_get_option')) {
            $nested = bankai_get_option('watermark_settings', []);
            if (is_array($nested)) {
                $saved = array_merge($saved, $nested);
            }
        }
        return wp_parse_args($saved, $defaults);
    }

    public function process_uploaded_image(array $upload): array
    {
        if (empty($upload['file']) || empty($upload['type'])) {
            return $upload;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($upload['type'], $allowed, true)) {
            return $upload;
        }

        $file = $upload['file'];
        if (!file_exists($file) || !is_readable($file)) {
            return $upload;
        }

        $s = $this->get_settings();

        // EXIF strip
        if (!empty($s['strip_exif']) && $this->is_sub_active('exif_metadata_scrubber')) {
            $this->strip_exif($file, $upload['type']);
        }

        // Watermark on upload
        if (!empty($s['enabled']) && !empty($s['apply_upload']) && extension_loaded('gd')) {
            if ($this->is_sub_active('dynamic_watermarking')) {
                $this->apply_watermark($file, $s);
            }
        }

        // WebP sidecar (keep original)
        if (!empty($s['convert_webp']) && $this->is_sub_active('webp_avif_converter')) {
            $this->maybe_create_webp($file, $upload['type'], (int) $s['quality']);
        }

        return $upload;
    }

    private function strip_exif(string $file, string $mime): void
    {
        if (!extension_loaded('gd')) {
            return;
        }
        $img = $this->load_image($file, $mime);
        if (!$img) {
            return;
        }
        $this->save_image($img, $file, $mime, 90);
        imagedestroy($img);
    }

    private function maybe_create_webp(string $file, string $mime, int $quality): void
    {
        if (!function_exists('imagewebp')) {
            return;
        }
        $img = $this->load_image($file, $mime);
        if (!$img) {
            return;
        }
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);
        if ($webp && $webp !== $file) {
            imagewebp($img, $webp, max(10, min(100, $quality)));
        }
        imagedestroy($img);
    }

    /**
     * Apply text or image watermark.
     */
    public function apply_watermark(string $file_path, ?array $settings = null): bool
    {
        $s = $settings ?? $this->get_settings();
        $info = @getimagesize($file_path);
        if (!$info || empty($info['mime'])) {
            return false;
        }

        $min = (int) ($s['min_dimension'] ?? 0);
        if ($min > 0 && ($info[0] < $min || $info[1] < $min)) {
            return false;
        }

        $mime  = $info['mime'];
        $image = $this->load_image($file_path, $mime);
        if (!$image) {
            return false;
        }

        $width  = imagesx($image);
        $height = imagesy($image);
        $opacity = max(10, min(100, (int) ($s['opacity'] ?? 75)));
        // GD alpha: 0 opaque, 127 transparent
        $alpha = (int) round((100 - $opacity) / 100 * 127);

        $type = $s['type'] ?? 'text';

        if ($type === 'image' && !empty($s['image_id'])) {
            $wm_path = get_attached_file((int) $s['image_id']);
            if ($wm_path && file_exists($wm_path)) {
                $wm_info = @getimagesize($wm_path);
                if ($wm_info) {
                    $wm = $this->load_image($wm_path, $wm_info['mime']);
                    if ($wm) {
                        $ww = imagesx($wm);
                        $wh = imagesy($wm);
                        // Scale watermark to ~20% of host width
                        $target_w = max(40, (int) ($width * 0.2));
                        $scale    = $target_w / max(1, $ww);
                        $nw       = (int) ($ww * $scale);
                        $nh       = (int) ($wh * $scale);
                        $wm_scaled = imagecreatetruecolor($nw, $nh);
                        imagealphablending($wm_scaled, false);
                        imagesavealpha($wm_scaled, true);
                        $transparent = imagecolorallocatealpha($wm_scaled, 0, 0, 0, 127);
                        imagefill($wm_scaled, 0, 0, $transparent);
                        imagecopyresampled($wm_scaled, $wm, 0, 0, 0, 0, $nw, $nh, $ww, $wh);
                        imagedestroy($wm);

                        [$x, $y] = $this->calc_position($s['position'] ?? 'bottom-right', $width, $height, $nw, $nh, 15);
                        $this->imagecopymerge_alpha($image, $wm_scaled, $x, $y, 0, 0, $nw, $nh, $opacity);
                        imagedestroy($wm_scaled);
                    }
                }
            }
        } else {
            $text = (string) ($s['text'] ?? 'BANKAI');
            $font_size = 5;
            $font_w = imagefontwidth($font_size) * strlen($text);
            $font_h = imagefontheight($font_size);
            [$x, $y] = $this->calc_position($s['position'] ?? 'bottom-right', $width, $height, $font_w, $font_h, 15);
            $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
            if ($color === false) {
                $color = imagecolorallocate($image, 255, 255, 255);
            }
            imagestring($image, $font_size, (int) $x, (int) $y, $text, $color);
        }

        $ok = $this->save_image($image, $file_path, $mime, (int) ($s['quality'] ?? 88));
        imagedestroy($image);
        return $ok;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function calc_position(string $pos, int $w, int $h, int $ew, int $eh, int $margin): array
    {
        $x = $margin;
        $y = $margin;
        switch ($pos) {
            case 'top-center':
                $x = (int) (($w - $ew) / 2);
                break;
            case 'top-right':
                $x = $w - $ew - $margin;
                break;
            case 'center-left':
                $y = (int) (($h - $eh) / 2);
                break;
            case 'center':
                $x = (int) (($w - $ew) / 2);
                $y = (int) (($h - $eh) / 2);
                break;
            case 'center-right':
                $x = $w - $ew - $margin;
                $y = (int) (($h - $eh) / 2);
                break;
            case 'bottom-left':
                $y = $h - $eh - $margin;
                break;
            case 'bottom-center':
                $x = (int) (($w - $ew) / 2);
                $y = $h - $eh - $margin;
                break;
            case 'bottom-right':
            default:
                $x = $w - $ew - $margin;
                $y = $h - $eh - $margin;
                break;
        }
        return [$x, $y];
    }

    private function imagecopymerge_alpha($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct): void
    {
        $pct = max(0, min(100, $pct));
        $cut = imagecreatetruecolor($src_w, $src_h);
        imagecopy($cut, $dst, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        imagecopy($cut, $src, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
        imagedestroy($cut);
    }

    /** @return resource|\GdImage|false */
    private function load_image(string $file, string $mime)
    {
        switch ($mime) {
            case 'image/jpeg':
                return @imagecreatefromjpeg($file);
            case 'image/png':
                $img = @imagecreatefrompng($file);
                if ($img) {
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                }
                return $img;
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false;
            default:
                return false;
        }
    }

    /** @param resource|\GdImage $image */
    private function save_image($image, string $file, string $mime, int $quality): bool
    {
        switch ($mime) {
            case 'image/jpeg':
                return (bool) imagejpeg($image, $file, max(10, min(100, $quality)));
            case 'image/png':
                return (bool) imagepng($image, $file, (int) round((100 - $quality) / 10));
            case 'image/webp':
                return function_exists('imagewebp') && imagewebp($image, $file, max(10, min(100, $quality)));
            default:
                return false;
        }
    }

    /* ---------- Lazy load ---------- */

    public function filter_content_lazy(string $content): string
    {
        if (is_admin() || (function_exists('wp_is_json_request') && wp_is_json_request())) {
            return $content;
        }
        return preg_replace_callback('/<img\b([^>]*?)>/i', function ($m) {
            $attrs = $m[1];
            if (stripos($attrs, 'loading=') !== false) {
                return $m[0];
            }
            // Skip tiny tracking pixels
            if (preg_match('/width=["\']1["\']/', $attrs) && preg_match('/height=["\']1["\']/', $attrs)) {
                return $m[0];
            }
            return '<img loading="lazy" decoding="async"' . $attrs . '>';
        }, $content) ?? $content;
    }

    public function filter_thumb_lazy(string $html): string
    {
        if ($html === '' || stripos($html, 'loading=') !== false) {
            return $html;
        }
        return str_replace('<img ', '<img loading="lazy" decoding="async" ', $html);
    }

    public function filter_attachment_attrs(array $attr): array
    {
        if (empty($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        if (empty($attr['decoding'])) {
            $attr['decoding'] = 'async';
        }
        return $attr;
    }

    /* ---------- AJAX ---------- */

    public function ajax_save_watermark_settings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $allowed_pos = [
            'top-left', 'top-center', 'top-right',
            'center-left', 'center', 'center-right',
            'bottom-left', 'bottom-center', 'bottom-right',
        ];

        $position = isset($_POST['position']) ? sanitize_text_field(wp_unslash($_POST['position'])) : 'bottom-right';
        if (!in_array($position, $allowed_pos, true)) {
            $position = 'bottom-right';
        }

        $type = isset($_POST['type']) ? sanitize_key(wp_unslash($_POST['type'])) : 'text';
        if (!in_array($type, ['text', 'image'], true)) {
            $type = 'text';
        }

        $opacity = isset($_POST['opacity']) ? (int) $_POST['opacity'] : 75;
        $opacity = max(10, min(100, $opacity));
        $quality = isset($_POST['quality']) ? (int) $_POST['quality'] : 82;
        $quality = max(10, min(100, $quality));
        $min_dim = isset($_POST['min_dimension']) ? absint($_POST['min_dimension']) : 300;

        $text = isset($_POST['text']) ? sanitize_text_field(wp_unslash($_POST['text'])) : '© BANKAI MEDIA';
        $text = mb_substr($text, 0, 80);

        $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
        $image_url = $image_id ? (string) wp_get_attachment_url($image_id) : '';

        $settings = [
            'enabled'       => !empty($_POST['enabled']),
            'type'          => $type,
            'position'      => $position,
            'opacity'       => $opacity,
            'text'          => $text,
            'image_id'      => $image_id,
            'image_url'     => $image_url,
            'quality'       => $quality,
            'min_dimension' => $min_dim,
            'apply_upload'  => !empty($_POST['apply_upload']),
            'apply_content' => !empty($_POST['apply_content']),
            'lazy_load'     => !empty($_POST['lazy_load']),
            'strip_exif'    => !empty($_POST['strip_exif']),
            'convert_webp'  => !empty($_POST['convert_webp']),
            'keep_original' => !empty($_POST['keep_original']),
        ];

        update_option('bankai_watermark_settings', $settings, false);
        if (function_exists('bankai_update_option')) {
            bankai_update_option('watermark_settings', $settings);
        }

        wp_send_json_success([
            'message'  => __('تنظیمات رسانه و واترمارک ذخیره شد.', 'bankai-core'),
            'settings' => $settings,
        ]);
    }

    public function ajax_bulk_convert(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $s = $this->get_settings();
        $q = new WP_Query([
            'post_type'      => 'attachment',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'post_status'    => 'inherit',
            'posts_per_page' => 40,
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $converted = 0;
        $watermarked = 0;
        foreach ($q->posts as $id) {
            $path = get_attached_file($id);
            if (!$path || !file_exists($path)) {
                continue;
            }
            $mime = get_post_mime_type($id) ?: '';
            if (!empty($s['convert_webp'])) {
                $this->maybe_create_webp($path, $mime, (int) $s['quality']);
                $converted++;
            }
            if (!empty($s['enabled']) && !empty($s['apply_content']) && extension_loaded('gd')) {
                if ($this->apply_watermark($path, $s)) {
                    $watermarked++;
                }
            }
            if (!empty($s['strip_exif'])) {
                $this->strip_exif($path, $mime);
            }
        }

        wp_send_json_success([
            'message'     => sprintf(
                __('بهینه‌سازی انجام شد: %d WebP · %d واترمارک.', 'bankai-core'),
                $converted,
                $watermarked
            ),
            'converted'   => $converted,
            'watermarked' => $watermarked,
            'scanned'     => count($q->posts),
        ]);
    }

    public function ajax_regenerate_thumbs(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $ids = get_posts([
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => 25,
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $done = 0;
        foreach ($ids as $id) {
            $file = get_attached_file($id);
            if (!$file || !file_exists($file)) {
                continue;
            }
            $meta = wp_generate_attachment_metadata($id, $file);
            if ($meta) {
                wp_update_attachment_metadata($id, $meta);
                $done++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(__('%d تصویر بازتولید شد.', 'bankai-core'), $done),
            'count'   => $done,
        ]);
    }

    public function ajax_strip_exif_bulk(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $ids = get_posts([
            'post_type'      => 'attachment',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => 30,
            'fields'         => 'ids',
            'post_status'    => 'inherit',
        ]);
        $n = 0;
        foreach ($ids as $id) {
            $path = get_attached_file($id);
            $mime = get_post_mime_type($id) ?: '';
            if ($path && file_exists($path)) {
                $this->strip_exif($path, $mime);
                $n++;
            }
        }
        wp_send_json_success(['message' => sprintf(__('EXIF از %d فایل حذف شد.', 'bankai-core'), $n), 'count' => $n]);
    }

    public function ajax_media_env(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        wp_send_json_success(['env' => self::environment()]);
    }

    public static function environment(): array
    {
        $gd = extension_loaded('gd');
        $imagick = extension_loaded('imagick');
        $webp = $gd && function_exists('imagewebp');
        $avif = $imagick || (function_exists('imageavif'));

        return [
            'gd'      => $gd,
            'gd_ver'  => $gd && defined('GD_VERSION') ? GD_VERSION : ($gd ? 'yes' : 'no'),
            'imagick' => $imagick,
            'webp'    => $webp,
            'avif'    => $avif,
            'settings'=> (new self())->get_settings(),
        ];
    }
}
