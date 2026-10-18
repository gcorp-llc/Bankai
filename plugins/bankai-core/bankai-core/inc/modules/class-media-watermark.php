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

        // Content filters — optimized image loading
        if ($this->is_sub_active('lazy_loading') || $this->setting('lazy_load', true)) {
            add_filter('the_content', [$this, 'filter_content_lazy'], 20);
            add_filter('post_thumbnail_html', [$this, 'filter_thumb_lazy'], 20, 5);
            add_filter('wp_get_attachment_image_attributes', [$this, 'filter_attachment_attrs'], 20, 3);
            add_filter('wp_content_img_tag', [$this, 'filter_content_img_tag'], 10, 3);
            add_filter('wp_get_attachment_image', [$this, 'filter_attachment_image_html'], 20, 5);
        }

        // Reduce oversized originals & default quality
        if ($this->is_sub_active('webp_avif_converter') || $this->setting('convert_webp', true)) {
            add_filter('jpeg_quality', [$this, 'filter_jpeg_quality']);
            add_filter('wp_editor_set_quality', [$this, 'filter_jpeg_quality']);
            add_filter('big_image_size_threshold', [$this, 'filter_big_image_threshold']);
        }

        if ($this->is_sub_active('dynamic_watermarking') || $this->setting('apply_content', false)) {
            // Optional: watermark only on upload; content images already processed if uploaded after enable
        }

        add_action('wp_ajax_bankai_bulk_convert_media', [$this, 'ajax_bulk_convert']);
        add_action('wp_ajax_bankai_bulk_watermark_ids', [$this, 'ajax_bulk_watermark_ids']);
        add_action('wp_ajax_bankai_upload_wm_font', [$this, 'ajax_upload_wm_font']);
        add_action('wp_ajax_bankai_save_watermark_settings', [$this, 'ajax_save_watermark_settings']);
        add_action('wp_ajax_bankai_regenerate_thumbs', [$this, 'ajax_regenerate_thumbs']);
        add_action('wp_ajax_bankai_media_env', [$this, 'ajax_media_env']);
        add_action('wp_ajax_bankai_strip_exif_bulk', [$this, 'ajax_strip_exif_bulk']);
        add_action('rest_api_init', [$this, 'register_cdn_routes']);
        add_filter('wp_get_attachment_url', [$this, 'maybe_cdn_url'], 20, 2);
        add_filter('wp_calculate_image_srcset', [$this, 'maybe_cdn_srcset'], 20, 5);
        add_action('wp_ajax_bankai_optimize_post_images', [$this, 'ajax_optimize_post_images']);
        add_action('wp_ajax_bankai_scan_post_images', [$this, 'ajax_scan_post_images']);
        add_action('wp_ajax_bankai_update_image_alt', [$this, 'ajax_update_image_alt']);
        add_action('wp_ajax_bankai_remove_image_watermark', [$this, 'ajax_remove_image_watermark']);
        add_action('wp_ajax_bankai_list_media_library', [$this, 'ajax_list_media_library']);
        add_action('wp_ajax_bankai_list_media', [$this, 'ajax_list_media_library']);
        add_action('wp_ajax_bankai_compress_attachment', [$this, 'ajax_compress_attachment']);
        add_action('wp_ajax_bankai_bulk_compress_media', [$this, 'ajax_bulk_compress_media']);
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
            'enabled'            => true,
            'type'               => 'text', // text | image
            'position'           => 'bottom-right',
            'opacity'            => 75,
            'text'               => '© BANKAI MEDIA',
            'text_size_type'     => 'scale', // scale | fixed
            'text_scale_percent' => 3.5,
            'min_font_size'      => 12,
            'max_font_size'      => 72,
            'margin_percent'     => 2.0,
            'text_size'          => 18,
            'text_color'         => '#FFFFFF',
            'text_font'          => 'default',
            'text_cover'         => 'none', // none | width | height | banner
            'box_enabled'        => true,
            'box_style'          => 'solid', // solid | gradient | none
            'box_padding'        => 8,
            'box_radius'         => 6,
            'box_color'          => '#000000',
            'box_opacity'        => 45,
            'image_id'           => 0,
            'image_url'          => '',
            'image_scale'        => 20,
            'quality'            => 82,
            'min_dimension'      => 0,
            'custom_fonts'       => [],
            'custom_font_url'    => '',
            'custom_font_name'   => '',
            'custom_font_path'   => '',
            'exclude_patterns'   => "logo
banner
icon
sprite
favicon
watermark",
            'apply_upload'       => true,
            'apply_content'      => false,
            'lazy_load'          => true,
            'strip_exif'         => true,
            'convert_webp'       => true,
            'keep_original'      => true,
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

    /**
     * Persist watermark settings (shared by AJAX handlers).
     */
    public function save_settings(array $settings): array
    {
        $current = $this->get_settings();
        $merged  = array_merge($current, $settings);
        update_option('bankai_watermark_settings', $merged, false);
        if (function_exists('bankai_update_option')) {
            bankai_update_option('watermark_settings', $merged);
        }
        return $merged;
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
        // Rotate image based on EXIF orientation if JPEG
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file);
            if (!empty($exif['Orientation'])) {
                switch ((int) $exif['Orientation']) {
                    case 3:
                        $img = imagerotate($img, 180, 0);
                        break;
                    case 6:
                        $img = imagerotate($img, -90, 0);
                        break;
                    case 8:
                        $img = imagerotate($img, 90, 0);
                        break;
                }
            }
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
        if (!file_exists($file_path) || !is_writable($file_path)) {
            return false;
        }

        $s = $settings ?? $this->get_settings();

        // Backup original once for restore
        if (!empty($s['keep_original'] ?? true)) {
            $bak = $file_path . '.bk-orig';
            if (!file_exists($bak)) {
                @copy($file_path, $bak);
            }
        }

        // Process on temporary working file first to guarantee atomic write & non-destructive failure
        $tmp_file = $file_path . '.tmp-' . wp_generate_password(6, false);
        if (!@copy($file_path, $tmp_file)) {
            return false;
        }

        $ok = $this->apply_watermark_inner($tmp_file, $s, $file_path);
        if ($ok && file_exists($tmp_file) && filesize($tmp_file) > 0) {
            @rename($tmp_file, $file_path);
            return true;
        }

        if (file_exists($tmp_file)) {
            @unlink($tmp_file);
        }
        return false;
    }

    private function apply_watermark_inner(string $file_path, array $s, string $original_ref_path = ''): bool
    {
        $text_cover = $s['text_cover'] ?? 'none';
        $box_style = $s['box_style'] ?? 'solid';
        $info = @getimagesize($file_path);
        if (!$info || empty($info['mime'])) {
            return false;
        }

        $min = (int) ($s['min_dimension'] ?? 0);
        if ($min > 0 && ($info[0] < $min || $info[1] < $min)) {
            return false;
        }

        // Skip files matching exclude patterns (logo, banner, …)
        $ref_for_exclude = $original_ref_path !== '' ? $original_ref_path : $file_path;
        if ($this->path_is_excluded($ref_for_exclude, $s)) {
            return false;
        }

        $mime  = $info['mime'];
        $image = $this->load_image($file_path, $mime);
        if (!$image) {
            return false;
        }
        if (function_exists('imagealphablending')) {
            imagealphablending($image, true);
        }
        if (function_exists('imagesavealpha')) {
            imagesavealpha($image, true);
        }

        $width  = imagesx($image);
        $height = imagesy($image);
        $opacity = max(10, min(100, (int) ($s['opacity'] ?? 75)));
        // GD alpha: 0 opaque, 127 transparent
        $alpha = (int) round((100 - $opacity) / 100 * 127);

        $type = $s['type'] ?? 'text';

        // Margin calculation: percentage of host image width (default 2%) with max bound
        $margin_pct = (float) ($s['margin_percent'] ?? 2.0);
        $margin = (int) max(4, round($width * ($margin_pct / 100)));

        if ($type === 'image' && !empty($s['image_id'])) {
            $wm_path = get_attached_file((int) $s['image_id']);
            // Prevent source image self-watermarking
            if ($wm_path && file_exists($wm_path) && realpath($wm_path) !== realpath($ref_for_exclude)) {
                $wm_info = @getimagesize($wm_path);
                if ($wm_info) {
                    $wm = $this->load_image($wm_path, $wm_info['mime']);
                    if ($wm) {
                        $ww = imagesx($wm);
                        $wh = imagesy($wm);
                        $pct = max(5, min(50, (int) ($s['image_scale'] ?? 20)));
                        $target_w = max(20, (int) ($width * ($pct / 100)));
                        // Cap watermark width so it doesn't overflow
                        $target_w = min($target_w, max(10, $width - ($margin * 2)));
                        $scale    = $target_w / max(1, $ww);
                        $nw       = (int) max(10, round($ww * $scale));
                        $nh       = (int) max(10, round($wh * $scale));
                        $wm_scaled = imagecreatetruecolor($nw, $nh);
                        imagealphablending($wm_scaled, false);
                        imagesavealpha($wm_scaled, true);
                        $transparent = imagecolorallocatealpha($wm_scaled, 0, 0, 0, 127);
                        imagefill($wm_scaled, 0, 0, $transparent);
                        imagecopyresampled($wm_scaled, $wm, 0, 0, 0, 0, $nw, $nh, $ww, $wh);
                        imagedestroy($wm);

                        [$x, $y] = $this->calc_position($s['position'] ?? 'bottom-right', $width, $height, $nw, $nh, $margin);
                        $this->imagecopymerge_alpha($image, $wm_scaled, $x, $y, 0, 0, $nw, $nh, $opacity);
                        imagedestroy($wm_scaled);
                    }
                }
            }
        } else {
            $this->draw_text_watermark($image, $width, $height, $s, $alpha, $margin);
        }

        $ok = $this->save_image($image, $file_path, $mime, (int) ($s['quality'] ?? 88));
        imagedestroy($image);
        return $ok;
    }

    /**
     * @return array{0:int,1:int}
     */

    private function path_is_excluded(string $file_path, array $s): bool
    {
        $raw = (string) ($s['exclude_patterns'] ?? '');
        if ($raw === '') {
            return false;
        }
        $name = strtolower(basename($file_path));
        $parts = preg_split('/[\r\n,]+/', $raw) ?: [];
        foreach ($parts as $pat) {
            $pat = strtolower(trim($pat));
            if ($pat !== '' && str_contains($name, $pat)) {
                return true;
            }
        }
        return false;
    }


    private function sanitize_custom_fonts($raw): array
    {
        $current = $this->get_settings();
        $list = is_array($current['custom_fonts'] ?? null) ? $current['custom_fonts'] : [];
        if ($raw === null) {
            return $list;
        }
        if (is_string($raw)) {
            $decoded = json_decode(wp_unslash($raw), true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) {
            return $list;
        }
        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) continue;
            $name = sanitize_file_name((string) ($item['name'] ?? ''));
            $url  = esc_url_raw((string) ($item['url'] ?? ''));
            $path = sanitize_text_field((string) ($item['path'] ?? ''));
            if ($name === '' && $url === '') continue;
            $out[] = [
                'id'   => sanitize_key((string) ($item['id'] ?? ('cf_' . md5($name . $url)))),
                'name' => $name,
                'url'  => $url,
                'path' => $path,
            ];
        }
        return $out;
    }

    private function resolve_font_path(string $font_key): string
    {
        $s = $this->get_settings();
        // Custom uploaded font (TTF/OTF only — GD imagettftext requirement)
        if ($font_key === 'custom' || $font_key === 'custom_font' || str_starts_with($font_key, 'cf_')) {
            // try list entry by id
            if (str_starts_with($font_key, 'cf_')) {
                foreach ((array) ($s['custom_fonts'] ?? []) as $cf) {
                    if (($cf['id'] ?? '') === $font_key) {
                        $path = (string) ($cf['path'] ?? '');
                        if ($path && is_readable($path)) {
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            if (in_array($ext, ['ttf', 'otf'], true)) return $path;
                        }
                    }
                }
            }
            $custom = $this->resolve_custom_font_file($s);
            if ($custom !== '') {
                $ext = strtolower(pathinfo($custom, PATHINFO_EXTENSION));
                if (in_array($ext, ['ttf', 'otf'], true)) {
                    return $custom;
                }
            }
        }
        // If settings force custom_font_url and text_font is custom
        if (!empty($s['custom_font_url']) && ($font_key === 'custom' || empty($font_key) || $font_key === 'default')) {
            // only prefer custom when explicitly selected as custom
        }
        if ($font_key === 'custom' && !empty($s['custom_font_name'])) {
            $custom = $this->resolve_custom_font_file($s);
            if ($custom !== '') {
                return $custom;
            }
        }

        $map = [
            'default' => '',
            'vazirmatn' => '',
            'vazirmatn-bold' => '',
            'tahoma' => '',
            'arial' => '',
            'georgia' => '',
            'roboto' => '',
            'inter' => '',
            'dejavu'  => '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            'dejavu-bold' => '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            'liberation' => '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            'free-sans' => '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
        ];
        $path = $map[$font_key] ?? '';
        if ($path && is_readable($path)) {
            return $path;
        }
        // system fallbacks useful for Persian glyphs
        $fallbacks = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
            '/usr/share/fonts/truetype/noto/NotoSansArabic-Regular.ttf',
            'C:/Windows/Fonts/tahoma.ttf',
            'C:/Windows/Fonts/arial.ttf',
        ];
        foreach ($fallbacks as $p) {
            if ($p && is_readable($p)) {
                return $p;
            }
        }
        return '';
    }

    /**
     * Map custom_font_url / custom_font_name to local filesystem path for GD.
     */
    private function resolve_custom_font_file(array $s): string
    {
        if (!empty($s['custom_font_path']) && is_readable((string) $s['custom_font_path'])) {
            $ext = strtolower(pathinfo((string) $s['custom_font_path'], PATHINFO_EXTENSION));
            if (in_array($ext, ['ttf', 'otf'], true)) {
                return (string) $s['custom_font_path'];
            }
        }
        $name = (string) ($s['custom_font_name'] ?? '');
        $url  = (string) ($s['custom_font_url'] ?? '');
        $upload = wp_upload_dir();
        $dir = trailingslashit($upload['basedir']) . 'bankai-fonts';

        if ($name !== '') {
            $candidate = $dir . '/' . ltrim($name, '/');
            if (is_readable($candidate)) {
                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                if (in_array($ext, ['ttf', 'otf', 'woff', 'woff2'], true)) {
                    return $candidate;
                }
            }
        }
        if ($url !== '') {
            // Convert URL under uploads to path
            $baseurl = trailingslashit($upload['baseurl']);
            if (str_starts_with($url, $baseurl)) {
                $rel = substr($url, strlen($baseurl));
                $candidate = trailingslashit($upload['basedir']) . ltrim($rel, '/');
                if (is_readable($candidate)) {
                    $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                    if (in_array($ext, ['ttf', 'otf'], true)) {
                        return $candidate;
                    }
                }
            }
            // Absolute path stored by mistake
            if (is_readable($url)) {
                return $url;
            }
        }
        // last: any ttf in bankai-fonts
        if (is_dir($dir)) {
            foreach (array_merge(glob($dir . '/*.ttf') ?: [], glob($dir . '/*.otf') ?: [], glob($dir . '/*.woff') ?: [], glob($dir . '/*.woff2') ?: []) as $f) {
                if (is_readable($f)) {
                    return $f;
                }
            }
        }
        return '';
    }

    private function parse_hex_color(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return [255, 255, 255];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }


    /**
     * Shape Arabic/Persian for GD imagettftext (visual LTR order of presentation forms).
     */
    private function shape_rtl_text(string $text): string
    {
        if ($text === '' || !preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text)) {
            return $text;
        }
        // Isolate Arabic runs and reshape each
        $out = '';
        $buf = '';
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($text, $i, 1, 'UTF-8');
            $o = $this->utf8_ord($ch);
            $is_ar = ($o >= 0x0600 && $o <= 0x06FF) || ($o >= 0x0750 && $o <= 0x077F)
                || ($o >= 0x08A0 && $o <= 0x08FF) || ($o >= 0xFB50 && $o <= 0xFDFF)
                || ($o >= 0xFE70 && $o <= 0xFEFF) || $ch === ' ' || $ch === "\u{200C}" || $ch === "\u{200D}";
            if ($is_ar) {
                $buf .= $ch;
            } else {
                if ($buf !== '') {
                    $out .= $this->reshape_arabic_run($buf);
                    $buf = '';
                }
                $out .= $ch;
            }
        }
        if ($buf !== '') {
            $out .= $this->reshape_arabic_run($buf);
        }
        return $out;
    }

    private function utf8_ord(string $ch): int
    {
        $u = unpack('N', mb_convert_encoding($ch, 'UCS-4BE', 'UTF-8'));
        return (int) ($u[1] ?? 0);
    }

    private function reshape_arabic_run(string $run): string
    {
        // Map isolated/initial/medial/final forms for common Persian/Arabic letters
        static $forms = null;
        if ($forms === null) {
            // [isolated, final, initial, medial]
            $forms = [
                0x0627 => [0xFE8D, 0xFE8E, 0xFE8D, 0xFE8E], // ا
                0x0628 => [0xFE8F, 0xFE90, 0xFE91, 0xFE92], // ب
                0x067E => [0xFB56, 0xFB57, 0xFB58, 0xFB59], // پ
                0x062A => [0xFE95, 0xFE96, 0xFE97, 0xFE98], // ت
                0x062B => [0xFE99, 0xFE9A, 0xFE9B, 0xFE9C], // ث
                0x062C => [0xFE9D, 0xFE9E, 0xFE9F, 0xFEA0], // ج
                0x0686 => [0xFB7A, 0xFB7B, 0xFB7C, 0xFB7D], // چ
                0x062D => [0xFEA1, 0xFEA2, 0xFEA3, 0xFEA4], // ح
                0x062E => [0xFEA5, 0xFEA6, 0xFEA7, 0xFEA8], // خ
                0x062F => [0xFEA9, 0xFEAA, 0xFEA9, 0xFEAA], // د
                0x0630 => [0xFEAB, 0xFEAC, 0xFEAB, 0xFEAC], // ذ
                0x0631 => [0xFEAD, 0xFEAE, 0xFEAD, 0xFEAE], // ر
                0x0632 => [0xFEAF, 0xFEB0, 0xFEAF, 0xFEB0], // ز
                0x0698 => [0xFB8A, 0xFB8B, 0xFB8A, 0xFB8B], // ژ
                0x0633 => [0xFEB1, 0xFEB2, 0xFEB3, 0xFEB4], // س
                0x0634 => [0xFEB5, 0xFEB6, 0xFEB7, 0xFEB8], // ش
                0x0635 => [0xFEB9, 0xFEBA, 0xFEBB, 0xFEBC], // ص
                0x0636 => [0xFEBD, 0xFEBE, 0xFEBF, 0xFEC0], // ض
                0x0637 => [0xFEC1, 0xFEC2, 0xFEC3, 0xFEC4], // ط
                0x0638 => [0xFEC5, 0xFEC6, 0xFEC7, 0xFEC8], // ظ
                0x0639 => [0xFEC9, 0xFECA, 0xFECB, 0xFECC], // ع
                0x063A => [0xFECD, 0xFECE, 0xFECF, 0xFED0], // غ
                0x0641 => [0xFED1, 0xFED2, 0xFED3, 0xFED4], // ف
                0x0642 => [0xFED5, 0xFED6, 0xFED7, 0xFED8], // ق
                0x06A9 => [0xFB8E, 0xFB8F, 0xFB90, 0xFB91], // ک
                0x0643 => [0xFED9, 0xFEDA, 0xFEDB, 0xFEDC], // ك
                0x06AF => [0xFB92, 0xFB93, 0xFB94, 0xFB95], // گ
                0x0644 => [0xFEDD, 0xFEDE, 0xFEDF, 0xFEE0], // ل
                0x0645 => [0xFEE1, 0xFEE2, 0xFEE3, 0xFEE4], // م
                0x0646 => [0xFEE5, 0xFEE6, 0xFEE7, 0xFEE8], // ن
                0x0648 => [0xFEED, 0xFEEE, 0xFEED, 0xFEEE], // و
                0x0647 => [0xFEE9, 0xFEEA, 0xFEEB, 0xFEEC], // ه
                0x06CC => [0xFBFC, 0xFBFD, 0xFBFE, 0xFBFF], // ی
                0x064A => [0xFEF1, 0xFEF2, 0xFEF3, 0xFEF4], // ي
                0x0622 => [0xFE81, 0xFE82, 0xFE81, 0xFE82], // آ
                0x0629 => [0xFE93, 0xFE94, 0xFE93, 0xFE94], // ة
                0x0621 => [0xFE80, 0xFE80, 0xFE80, 0xFE80], // ء
            ];
        }
        // letters that don't connect to next
        static $non_connect = null;
        if ($non_connect === null) {
            $non_connect = array_flip([
                0x0627, 0x062F, 0x0630, 0x0631, 0x0632, 0x0698, 0x0648, 0x0622, 0x0629, 0x0621, 0xFE80, 0xFE81, 0xFE82,
            ]);
        }

        $chars = [];
        $n = mb_strlen($run, 'UTF-8');
        for ($i = 0; $i < $n; $i++) {
            $ch = mb_substr($run, $i, 1, 'UTF-8');
            $chars[] = $this->utf8_ord($ch);
        }

        $shaped = [];
        for ($i = 0; $i < count($chars); $i++) {
            $cp = $chars[$i];
            if ($cp === 0x20 || $cp === 0x200C || $cp === 0x200D) {
                $shaped[] = $cp === 0x20 ? ' ' : '';
                continue;
            }
            if (!isset($forms[$cp])) {
                $shaped[] = mb_convert_encoding(pack('N', $cp), 'UTF-8', 'UCS-4BE');
                continue;
            }
            $prev = null;
            for ($j = $i - 1; $j >= 0; $j--) {
                if ($chars[$j] === 0x20 || $chars[$j] === 0x200C) break;
                if ($chars[$j] === 0x200D) continue;
                $prev = $chars[$j];
                break;
            }
            $next = null;
            for ($j = $i + 1; $j < count($chars); $j++) {
                if ($chars[$j] === 0x20 || $chars[$j] === 0x200C) break;
                if ($chars[$j] === 0x200D) continue;
                $next = $chars[$j];
                break;
            }
            $joins_prev = $prev !== null && isset($forms[$prev]) && !isset($non_connect[$prev]);
            $joins_next = $next !== null && isset($forms[$next]);
            if ($joins_prev && $joins_next) {
                $form = $forms[$cp][3]; // medial
            } elseif ($joins_prev) {
                $form = $forms[$cp][1]; // final
            } elseif ($joins_next) {
                $form = $forms[$cp][2]; // initial
            } else {
                $form = $forms[$cp][0]; // isolated
            }
            $shaped[] = mb_convert_encoding(pack('N', $form), 'UTF-8', 'UCS-4BE');
        }
        // Visual order for RTL: reverse the shaped glyphs
        return implode('', array_reverse($shaped));
    }

    private function draw_text_watermark($image, int $width, int $height, array $s, int $alpha, int $margin = 15): void
    {
        $text = (string) ($s['text'] ?? 'BANKAI');
        if ($text === '') {
            return;
        }
        $text = $this->shape_rtl_text($text);

        // Scalable font calculation algorithm: fontSize = clamp(imageWidth * fontScalePercent / 100, minFont, maxFont)
        $size_type = (string) ($s['text_size_type'] ?? 'scale');
        $min_font = (int) max(8, ($s['min_font_size'] ?? 12));
        $max_font = (int) min(180, ($s['max_font_size'] ?? 72));

        if ($size_type === 'scale') {
            $scale_pct = (float) ($s['text_scale_percent'] ?? 3.5);
            $calculated = (int) round($width * ($scale_pct / 100));
            $size = max($min_font, min($max_font, $calculated));
        } else {
            $size = max($min_font, min($max_font, (int) ($s['text_size'] ?? 18)));
        }

        $font_key = (string) ($s['text_font'] ?? 'default');
        $font_path = $this->resolve_font_path($font_key);
        [$tr, $tg, $tb] = $this->parse_hex_color((string) ($s['text_color'] ?? '#FFFFFF'));
        $pad = max(2, min(50, (int) ($s['box_padding'] ?? 8)));
        $box_on = !empty($s['box_enabled']);
        $box_style = (string) ($s['box_style'] ?? 'solid');
        $box_op = max(0, min(100, (int) ($s['box_opacity'] ?? 45)));
        [$br, $bg, $bb] = $this->parse_hex_color((string) ($s['box_color'] ?? '#000000'));

        $tw = $th = 0;
        $bbox = null;
        if ($font_path && function_exists('imagettfbbox')) {
            $bbox = @imagettfbbox($size, 0, $font_path, $text);
            if (is_array($bbox)) {
                $tw = abs($bbox[2] - $bbox[0]);
                $th = abs($bbox[7] - $bbox[1]);
            }
        }
        if ($tw <= 0) {
            $gd = min(5, max(1, (int) round($size / 6)));
            $tw = imagefontwidth($gd) * mb_strlen($text, 'UTF-8');
            $th = imagefontheight($gd);
        }

        $box_w = $tw + ($box_on && $box_style !== 'none' ? $pad * 2 : 0);
        $box_h = $th + ($box_on && $box_style !== 'none' ? $pad * 2 : 0);
        [$x, $y] = $this->calc_position($s['position'] ?? 'bottom-right', $width, $height, $box_w, $box_h, $margin);

        if ($box_on && $box_style !== 'none') {
            $box_alpha = (int) round((100 - $box_op) / 100 * 127);
            $box_col = imagecolorallocatealpha($image, $br, $bg, $bb, $box_alpha);
            if ($box_col !== false) {
                imagefilledrectangle($image, (int) $x, (int) $y, (int) ($x + $box_w), (int) ($y + $box_h), $box_col);
            }
        }

        $tx = (int) ($x + ($box_on && $box_style !== 'none' ? $pad : 0));
        $ty = (int) ($y + ($box_on && $box_style !== 'none' ? $pad : 0));
        $text_col = imagecolorallocatealpha($image, $tr, $tg, $tb, $alpha);
        if ($text_col === false) {
            $text_col = imagecolorallocate($image, $tr, $tg, $tb);
        }

        if ($font_path && $bbox && function_exists('imagettftext')) {
            $baseline = $ty + $th;
            @imagettftext($image, $size, 0, $tx, $baseline, $text_col, $font_path, $text);
        } else {
            $gd = min(5, max(1, (int) round($size / 6)));
            imagestring($image, $gd, $tx, $ty, $text, $text_col);
        }
    }

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
        if ($pct >= 100) {
            imagecopy($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
            return;
        }

        $cut = imagecreatetruecolor($src_w, $src_h);
        imagealphablending($cut, false);
        imagesavealpha($cut, true);

        // Copy destination snippet into cut
        imagecopy($cut, $dst, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

        // Blend source over cut with global opacity
        $opacity_factor = $pct / 100;
        for ($x = 0; $x < $src_w; $x++) {
            for ($y = 0; $y < $src_h; $y++) {
                $src_color = imagecolorat($src, $x + $src_x, $y + $src_y);
                $src_a = ($src_color >> 24) & 0x7F; // 0=opaque, 127=transparent
                if ($src_a >= 127) {
                    continue;
                }
                $src_r = ($src_color >> 16) & 0xFF;
                $src_g = ($src_color >> 8) & 0xFF;
                $src_b = $src_color & 0xFF;

                // Adjust alpha with pct
                $effective_alpha = 127 - (int) round((127 - $src_a) * $opacity_factor);
                $effective_alpha = max(0, min(127, $effective_alpha));

                $dst_color = imagecolorat($cut, $x, $y);
                $dst_a = ($dst_color >> 24) & 0x7F;
                $dst_r = ($dst_color >> 16) & 0xFF;
                $dst_g = ($dst_color >> 8) & 0xFF;
                $dst_b = $dst_color & 0xFF;

                // Simple alpha composite
                $sa = (127 - $effective_alpha) / 127;
                $da = (127 - $dst_a) / 127;
                $out_a_val = $sa + $da * (1 - $sa);

                if ($out_a_val <= 0) {
                    $out_color = imagecolorallocatealpha($cut, 0, 0, 0, 127);
                } else {
                    $out_r = (int) round(($src_r * $sa + $dst_r * $da * (1 - $sa)) / $out_a_val);
                    $out_g = (int) round(($src_g * $sa + $dst_g * $da * (1 - $sa)) / $out_a_val);
                    $out_b = (int) round(($src_b * $sa + $dst_b * $da * (1 - $sa)) / $out_a_val);
                    $out_a = 127 - (int) round($out_a_val * 127);

                    $out_r = max(0, min(255, $out_r));
                    $out_g = max(0, min(255, $out_g));
                    $out_b = max(0, min(255, $out_b));
                    $out_a = max(0, min(127, $out_a));

                    $out_color = imagecolorallocatealpha($cut, $out_r, $out_g, $out_b, $out_a);
                }

                if ($out_color !== false) {
                    imagesetpixel($cut, $x, $y, $out_color);
                }
            }
        }

        imagecopy($dst, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h);
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
        if ($content === '' || is_admin() || (function_exists('wp_is_json_request') && wp_is_json_request())) {
            return $content;
        }

        $index = 0;
        $result = preg_replace_callback('/<img\b([^>]*?)>/i', function ($m) use (&$index) {
            $index++;
            $attrs = $m[1];

            // Skip 1x1 trackers
            if (preg_match('/width=["\']1["\']/', $attrs) && preg_match('/height=["\']1["\']/', $attrs)) {
                return $m[0];
            }

            // First content image ≈ LCP candidate → eager + high priority
            $is_lcp = ($index === 1 && (is_singular() || is_front_page()));

            // Normalize loading
            if (preg_match('/\sloading=["\'][^"\']*["\']/i', $attrs)) {
                $attrs = preg_replace(
                    '/\sloading=["\'][^"\']*["\']/i',
                    $is_lcp ? ' loading="eager"' : ' loading="lazy"',
                    $attrs
                );
            } else {
                $attrs .= $is_lcp ? ' loading="eager"' : ' loading="lazy"';
            }

            // decoding
            if (!preg_match('/\sdecoding=/i', $attrs)) {
                $attrs .= ' decoding="async"';
            }

            // fetchpriority
            if ($is_lcp) {
                if (preg_match('/\sfetchpriority=/i', $attrs)) {
                    $attrs = preg_replace('/\sfetchpriority=["\'][^"\']*["\']/i', ' fetchpriority="high"', $attrs);
                } else {
                    $attrs .= ' fetchpriority="high"';
                }
            } elseif (!preg_match('/\sfetchpriority=/i', $attrs)) {
                // leave default (auto) for others
            }

            return '<img' . $attrs . '>';
        }, $content);

        return is_string($result) ? $result : $content;
    }

    public function filter_thumb_lazy(string $html, $post_id = null, $post_thumbnail_id = null, $size = null, $attr = null): string
    {
        if ($html === '') {
            return $html;
        }
        // Featured image often LCP on singular
        $is_lcp = is_singular() && in_the_loop() && is_main_query();
        if ($is_lcp) {
            $html = preg_replace('/\sloading=["\'][^"\']*["\']/i', '', $html) ?? $html;
            $html = preg_replace('/\sfetchpriority=["\'][^"\']*["\']/i', '', $html) ?? $html;
            if (stripos($html, '<img ') !== false) {
                $html = str_replace('<img ', '<img loading="eager" fetchpriority="high" decoding="async" ', $html);
            }
            return $html;
        }
        if (stripos($html, 'loading=') === false) {
            $html = str_replace('<img ', '<img loading="lazy" decoding="async" ', $html);
        } elseif (stripos($html, 'decoding=') === false) {
            $html = str_replace('<img ', '<img decoding="async" ', $html);
        }
        return $html;
    }

    public function filter_attachment_attrs(array $attr, $attachment = null, $size = null): array
    {
        $is_lcp = is_singular() && empty($attr['loading']);
        // Default lazy; callers for featured can override via filter order
        if (empty($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        if (empty($attr['decoding'])) {
            $attr['decoding'] = 'async';
        }
        return $attr;
    }

    /**
     * WP 6.0+ content img tag filter — precise per-image control.
     */
    public function filter_content_img_tag(string $filtered_image, string $context, int $attachment_id): string
    {
        if ($filtered_image === '' || is_admin()) {
            return $filtered_image;
        }
        static $count = 0;
        $count++;
        $is_lcp = ($count === 1 && is_singular());

        if ($is_lcp) {
            $filtered_image = preg_replace('/\sloading=["\'][^"\']*["\']/i', ' loading="eager"', $filtered_image) ?? $filtered_image;
            if (!preg_match('/\sfetchpriority=/i', $filtered_image)) {
                $filtered_image = str_replace('<img ', '<img fetchpriority="high" ', $filtered_image);
            }
            if (!preg_match('/\sdecoding=/i', $filtered_image)) {
                $filtered_image = str_replace('<img ', '<img decoding="async" ', $filtered_image);
            }
        } else {
            if (!preg_match('/\sloading=/i', $filtered_image)) {
                $filtered_image = str_replace('<img ', '<img loading="lazy" ', $filtered_image);
            }
            if (!preg_match('/\sdecoding=/i', $filtered_image)) {
                $filtered_image = str_replace('<img ', '<img decoding="async" ', $filtered_image);
            }
        }
        return $filtered_image;
    }

    public function filter_attachment_image_html(string $html, $attachment_id, $size, $icon, $attr): string
    {
        if ($html === '' || is_admin()) {
            return $html;
        }
        if (stripos($html, 'decoding=') === false) {
            $html = str_replace('<img ', '<img decoding="async" ', $html);
        }
        return $html;
    }

    public function filter_jpeg_quality($quality)
    {
        $q = (int) $this->setting('quality', 82);
        return max(60, min(92, $q > 0 ? $q : 82));
    }

    public function filter_big_image_threshold($threshold)
    {
        // Cap very large uploads so scaled versions are generated sooner
        $max_w = (int) $this->setting('max_width', 2560);
        if ($max_w > 0) {
            return $max_w;
        }
        return $threshold;
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

        $text_size_type = isset($_POST['text_size_type']) ? sanitize_key(wp_unslash($_POST['text_size_type'])) : 'scale';
        if (!in_array($text_size_type, ['scale', 'fixed'], true)) {
            $text_size_type = 'scale';
        }
        $text_scale_percent = isset($_POST['text_scale_percent']) ? max(0.5, min(25.0, (float) $_POST['text_scale_percent'])) : 3.5;
        $min_font_size = isset($_POST['min_font_size']) ? max(8, min(100, absint($_POST['min_font_size']))) : 12;
        $max_font_size = isset($_POST['max_font_size']) ? max(12, min(200, absint($_POST['max_font_size']))) : 72;
        $margin_percent = isset($_POST['margin_percent']) ? max(0.0, min(15.0, (float) $_POST['margin_percent'])) : 2.0;

        $text_size = max(10, min(120, absint($_POST['text_size'] ?? 18)));
        $text_color = sanitize_hex_color(wp_unslash($_POST['text_color'] ?? '#FFFFFF')) ?: '#FFFFFF';
        $text_font = sanitize_key(wp_unslash($_POST['text_font'] ?? 'default'));
        $box_enabled = !empty($_POST['box_enabled']);
        $box_padding = max(2, min(50, absint($_POST['box_padding'] ?? 8)));
        $box_radius = max(0, min(25, absint($_POST['box_radius'] ?? 6)));
        $box_color = sanitize_hex_color(wp_unslash($_POST['box_color'] ?? '#000000')) ?: '#000000';
        $box_opacity = max(0, min(100, absint($_POST['box_opacity'] ?? 45)));
        $image_scale = max(5, min(50, absint($_POST['image_scale'] ?? 20)));
        $exclude_patterns = sanitize_textarea_field(wp_unslash($_POST['exclude_patterns'] ?? ''));

        $settings = [
            'enabled'            => !empty($_POST['enabled']),
            'type'               => $type,
            'position'           => $position,
            'opacity'            => $opacity,
            'text'               => $text,
            'text_size_type'     => $text_size_type,
            'text_scale_percent' => $text_scale_percent,
            'min_font_size'      => $min_font_size,
            'max_font_size'      => $max_font_size,
            'margin_percent'     => $margin_percent,
            'image_id'           => $image_id,
            'image_url'          => $image_url,
            'image_scale'        => $image_scale,
            'text_size'          => $text_size,
            'text_color'         => $text_color,
            'text_font'          => $text_font,
            'box_enabled'        => $box_enabled,
            'box_padding'        => $box_padding,
            'box_radius'         => $box_radius,
            'box_color'          => $box_color,
            'box_opacity'        => $box_opacity,
            'exclude_patterns'   => $exclude_patterns,
            'quality'            => $quality,
            'min_dimension'      => $min_dim,
            'apply_upload'       => !empty($_POST['apply_upload']),
            'apply_content'      => !empty($_POST['apply_content']),
            'lazy_load'          => !empty($_POST['lazy_load']),
            'strip_exif'         => !empty($_POST['strip_exif']),
            'convert_webp'       => !empty($_POST['convert_webp']),
            'keep_original'      => !empty($_POST['keep_original']),
        ];

        // Preserve font library unless explicitly posted
        $prev = $this->get_settings();
        if (empty($settings['custom_fonts']) && !empty($prev['custom_fonts'])) {
            $settings['custom_fonts'] = $prev['custom_fonts'];
        }
        if (empty($settings['custom_font_url']) && !empty($prev['custom_font_url'])) {
            $settings['custom_font_url'] = $prev['custom_font_url'];
            $settings['custom_font_name'] = $prev['custom_font_name'] ?? '';
            $settings['custom_font_path'] = $prev['custom_font_path'] ?? '';
        }
        if (!empty($_POST['custom_font_url'])) {
            $settings['custom_font_url'] = esc_url_raw(wp_unslash($_POST['custom_font_url']));
        }
        if (!empty($_POST['custom_font_name'])) {
            $settings['custom_font_name'] = sanitize_file_name(wp_unslash($_POST['custom_font_name']));
        }
        if (!empty($_POST['custom_font_path'])) {
            $settings['custom_font_path'] = sanitize_text_field(wp_unslash($_POST['custom_font_path']));
        }
        if (isset($_POST['custom_fonts'])) {
            $settings['custom_fonts'] = $this->sanitize_custom_fonts($_POST['custom_fonts']);
        }
        if (!empty($_POST['box_style'])) {
            $settings['box_style'] = sanitize_key(wp_unslash($_POST['box_style']));
        }
        if (!empty($_POST['text_cover'])) {
            $settings['text_cover'] = sanitize_key(wp_unslash($_POST['text_cover']));
        }

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

    /**
     * Scan images in post content for Optimize panel.
     */
    public function ajax_scan_post_images(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        $html    = isset($_POST['content']) ? wp_unslash((string) $_POST['content']) : '';
        if ($html === '' && $post_id > 0) {
            $post = get_post($post_id);
            $html = $post ? (string) $post->post_content : '';
        }

        $images = $this->parse_content_images($html);

        // Prepend featured/cover image when available
        if ($post_id > 0) {
            $fid = (int) get_post_thumbnail_id($post_id);
            if ($fid > 0) {
                $furl = (string) (wp_get_attachment_image_url($fid, 'large') ?: wp_get_attachment_url($fid));
                if ($furl !== '') {
                    $norm = preg_replace('/\?.*$/', '', $furl);
                    $exists = false;
                    foreach ($images as &$im) {
                        $s = preg_replace('/\?.*$/', '', (string) ($im['src'] ?? ''));
                        if ($s === $norm) {
                            $im['is_cover'] = true;
                            $im['attachment_id'] = $fid;
                            $exists = true;
                            break;
                        }
                    }
                    unset($im);
                    if (!$exists) {
                        $path = get_attached_file($fid);
                        $size = ($path && is_file($path)) ? (int) filesize($path) : 0;
                        $mime = (string) get_post_mime_type($fid);
                        $fmt  = $mime ? strtoupper(preg_replace('#^image/#', '', $mime)) : strtoupper(pathinfo($furl, PATHINFO_EXTENSION));
                        array_unshift($images, [
                            'id'            => 'cover_' . $fid,
                            'src'           => $furl,
                            'original_src'  => $furl,
                            'alt'           => (string) get_post_meta($fid, '_wp_attachment_image_alt', true),
                            'format'        => $fmt,
                            'size'          => $size,
                            'is_cover'      => true,
                            'attachment_id' => $fid,
                            'watermarked'   => false,
                        ]);
                    }
                }
            }
        }

        wp_send_json_success(['images' => $images, 'count' => count($images)]);
    }

    /**
     * Optimize images in post: format, resize, alt, watermark.
     */
    public function ajax_optimize_post_images(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $post_id      = absint($_POST['post_id'] ?? 0);
        $html         = isset($_POST['content']) ? wp_unslash((string) $_POST['content']) : '';
        $do_webp      = !empty($_POST['convert_webp']);
        $do_resize    = !empty($_POST['resize']);
        $do_watermark = !empty($_POST['watermark']);
        $do_alt       = !empty($_POST['fill_alt']);
        $max_width    = max(320, min(2560, absint($_POST['max_width'] ?? 1600)));
        $max_height   = max(0, min(3000, absint($_POST['max_height'] ?? 0)));
        $quality      = max(40, min(95, absint($_POST['quality'] ?? 82)));
        $default_alt  = sanitize_text_field(wp_unslash($_POST['default_alt'] ?? ''));

        if ($html === '' && $post_id > 0) {
            $post = get_post($post_id);
            $html = $post ? (string) $post->post_content : '';
        }
        if ($html === '') {
            wp_send_json_error(['message' => 'محتوای مقاله خالی است.'], 400);
        }

        $report = [];
        $updated_html = $html;
        $matches = [];
        if (preg_match_all('/<img\b([^>]*?)>/i', $html, $m_all, PREG_SET_ORDER)) {
            $matches = $m_all;
        }

        // Always include featured/cover image even if not in content
        $cover_id = $post_id > 0 ? (int) get_post_thumbnail_id($post_id) : 0;
        $cover_src = '';
        if ($cover_id) {
            $cover_src = (string) (wp_get_attachment_image_url($cover_id, 'full') ?: wp_get_attachment_url($cover_id));
        }

        if (!$matches && !$cover_src) {
            wp_send_json_success(['images' => [], 'report' => [], 'content' => $html, 'message' => 'تصویری یافت نشد.']);
        }

        foreach ($matches as $idx => $m) {
            $tag = $m[0];
            $attrs = $m[1];
            $src = '';
            if (preg_match('/\bsrc=["\']([^"\']+)["\']/i', $attrs, $sm)) {
                $src = $sm[1];
            }
            if ($src === '') {
                continue;
            }

            $item = [
                'src'            => $src,
                'original_src'   => $src,
                'alt'            => '',
                'new_alt'        => '',
                'format_before'  => '',
                'format_after'   => '',
                'size_before'    => 0,
                'size_after'     => 0,
                'resized'        => false,
                'watermarked'    => false,
                'converted'      => false,
                'attachment_id'  => 0,
                'actions'        => [],
                'ok'             => true,
                'message'        => '',
            ];

            if (preg_match('/\balt=["\']([^"\']*)["\']/i', $attrs, $am)) {
                $item['alt'] = $am[1];
            }

            $path = $this->url_to_path($src);
            $att_id = attachment_url_to_postid($src);
            $item['attachment_id'] = $att_id;

            if (!$path || !file_exists($path)) {
                // Remote or missing — only alt fill possible
                if ($do_alt && trim($item['alt']) === '') {
                    $new_alt = $default_alt !== '' ? $default_alt : $this->guess_alt_from_src($src, $post_id);
                    $item['new_alt'] = $new_alt;
                    $updated_html = $this->replace_img_alt($updated_html, $src, $new_alt);
                    $item['actions'][] = 'alt';
                }
                $item['message'] = 'فایل محلی یافت نشد (فقط alt)';
                $report[] = $item;
                continue;
            }

            $item['size_before'] = (int) @filesize($path);
            $info = @getimagesize($path);
            $mime = $info['mime'] ?? '';
            $item['format_before'] = $this->mime_to_ext($mime);

            $work_path = $path;
            $new_src = $src;

            // Resize
            if ($do_resize && $info && !empty($info[0]) && $info[0] > $max_width) {
                $resized = $this->resize_image_file($path, $max_width, $quality);
                if ($resized) {
                    $item['resized'] = true;
                    $item['actions'][] = 'resize';
                    $info = @getimagesize($path);
                }
            }

            // Convert to WebP
            if ($do_webp && $mime && $mime !== 'image/webp') {
                $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
                if ($webp_path && $webp_path !== $path) {
                    $converted = $this->convert_to_webp_file($path, $webp_path, $quality);
                    if ($converted && file_exists($webp_path)) {
                        $new_url = $this->path_to_url($webp_path);
                        if ($new_url) {
                            $updated_html = str_replace($src, $new_url, $updated_html);
                            $item['converted'] = true;
                            $item['actions'][] = 'webp';
                            $item['format_after'] = 'webp';
                            $work_path = $webp_path;
                            $new_src = $new_url;
                            $src = $new_url;
                            $mime = 'image/webp';
                            if ($att_id) {
                                update_post_meta($att_id, '_bankai_webp_path', $webp_path);
                            }
                        }
                    }
                }
            } else {
                $item['format_after'] = $item['format_before'];
            }

            // Watermark
            if ($do_watermark && file_exists($work_path)) {
                $ok = $this->apply_watermark($work_path);
                if ($ok) {
                    $item['watermarked'] = true;
                    $item['actions'][] = 'watermark';
                    if ($att_id) {
                        update_post_meta($att_id, '_bankai_watermarked', 1);
                        update_post_meta($att_id, '_bankai_original_backup', $path);
                    }
                }
            }

            // Alt
            if ($do_alt && trim($item['alt']) === '') {
                $new_alt = $default_alt !== '' ? $default_alt : $this->guess_alt_from_src($new_src, $post_id);
                $item['new_alt'] = $new_alt;
                $updated_html = $this->replace_img_alt($updated_html, $new_src, $new_alt);
                if ($item['original_src'] !== $new_src) {
                    $updated_html = $this->replace_img_alt($updated_html, $item['original_src'], $new_alt);
                }
                $item['actions'][] = 'alt';
                if ($att_id) {
                    update_post_meta($att_id, '_wp_attachment_image_alt', $new_alt);
                }
            }

            $item['size_after'] = (int) @filesize($work_path);
            $item['src'] = $new_src;
            if (!$item['format_after']) {
                $item['format_after'] = $item['format_before'];
            }
            $report[] = $item;
        }


        // Process featured/cover separately (not in post content)
        if ($cover_id && $cover_src) {
            $already = false;
            foreach ($report as $r) {
                if (!empty($r['is_cover']) || (int) ($r['attachment_id'] ?? 0) === $cover_id) {
                    $already = true;
                    break;
                }
                if (!empty($r['original_src']) && $r['original_src'] === $cover_src) {
                    $already = true;
                    break;
                }
            }
            if (!$already) {
                $citem = [
                    'src'            => $cover_src,
                    'original_src'   => $cover_src,
                    'alt'            => (string) get_post_meta($cover_id, '_wp_attachment_image_alt', true),
                    'new_alt'        => '',
                    'format_before'  => '',
                    'format_after'   => '',
                    'size_before'    => 0,
                    'size_after'     => 0,
                    'resized'        => false,
                    'watermarked'    => false,
                    'converted'      => false,
                    'attachment_id'  => $cover_id,
                    'is_cover'       => true,
                    'actions'        => [],
                    'ok'             => true,
                    'message'        => '',
                ];
                $path = get_attached_file($cover_id);
                if ($path && is_file($path) && !$this->path_is_excluded($path, $this->get_settings())) {
                    $citem['size_before'] = (int) @filesize($path);
                    $info = @getimagesize($path);
                    $mime = $info['mime'] ?? '';
                    $citem['format_before'] = $this->mime_to_ext($mime);
                    $citem['format_after'] = $citem['format_before'];

                    if ($do_resize && $info && !empty($info[0]) && $info[0] > $max_width) {
                        if ($this->resize_image_file($path, $max_width, $quality)) {
                            $citem['resized'] = true;
                            $citem['actions'][] = 'resize';
                        }
                    }
                    if ($do_webp && $mime && $mime !== 'image/webp') {
                        // For cover: convert sidecar WebP but KEEP original attachment file/URL
                        // so featured image is never deleted or unlinked.
                        $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
                        if ($webp_path && $webp_path !== $path) {
                            $converted = $this->convert_to_webp_file($path, $webp_path, $quality);
                            if ($converted && file_exists($webp_path)) {
                                update_post_meta($cover_id, '_bankai_webp_path', $webp_path);
                                $citem['converted'] = true;
                                $citem['actions'][] = 'webp';
                                $citem['format_after'] = 'webp';
                                // do not change attachment URL / featured media
                            }
                        }
                    }
                    if ($do_watermark) {
                        $this->apply_watermark($path);
                        $citem['watermarked'] = true;
                        $citem['actions'][] = 'watermark';
                    }
                    if ($do_alt && trim($citem['alt']) === '') {
                        $new_alt = $default_alt !== '' ? $default_alt : $this->guess_alt_from_src($cover_src, $post_id);
                        if ($new_alt !== '') {
                            update_post_meta($cover_id, '_wp_attachment_image_alt', $new_alt);
                            $citem['new_alt'] = $new_alt;
                            $citem['alt'] = $new_alt;
                            $citem['actions'][] = 'alt';
                        }
                    }
                    $citem['size_after'] = is_file($path) ? (int) @filesize($path) : $citem['size_before'];
                    $citem['src'] = $cover_src; // keep stable URL for UI
                } else {
                    $citem['message'] = 'کاور: فایل در دسترس نیست یا در استثناهاست';
                }
                array_unshift($report, $citem);
            }
        }

        // Never clear featured image
        if ($post_id > 0 && $cover_id) {
            $still = (int) get_post_thumbnail_id($post_id);
            if ($still !== $cover_id) {
                set_post_thumbnail($post_id, $cover_id);
            }
        }

        wp_send_json_success([
            'report'  => $report,
            'content' => $updated_html,
            'count'   => count($report),
            'message' => sprintf('%d تصویر پردازش شد.', count($report)),
            'cover_id'=> $cover_id,
            'cover_src'=> $cover_src,
        ]);
    }

    public function ajax_update_image_alt(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $src = esc_url_raw(wp_unslash($_POST['src'] ?? ''));
        $alt = sanitize_text_field(wp_unslash($_POST['alt'] ?? ''));
        $html = isset($_POST['content']) ? wp_unslash((string) $_POST['content']) : '';
        if ($src === '' || $html === '') {
            wp_send_json_error(['message' => 'پارامتر ناقص'], 400);
        }
        $updated = $this->replace_img_alt($html, $src, $alt);
        $att_id = attachment_url_to_postid($src);
        if ($att_id) {
            update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
        }
        wp_send_json_success(['content' => $updated, 'alt' => $alt]);
    }

    public function ajax_remove_image_watermark(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $src = esc_url_raw(wp_unslash($_POST['src'] ?? ''));
        $att_id = absint($_POST['attachment_id'] ?? 0);
        if (!$att_id && $src) {
            $att_id = attachment_url_to_postid($src);
        }
        if (!$att_id) {
            wp_send_json_error(['message' => 'پیوست یافت نشد'], 404);
        }
        $backup = (string) get_post_meta($att_id, '_bankai_original_backup', true);
        $current = get_attached_file($att_id);
        if ($backup && file_exists($backup) && $current) {
            @copy($backup, $current);
            delete_post_meta($att_id, '_bankai_watermarked');
            wp_send_json_success(['message' => 'واترمارک حذف شد (بازگردانی از نسخه پشتیبان).', 'src' => wp_get_attachment_url($att_id)]);
        }
        // Soft flag remove if no backup
        delete_post_meta($att_id, '_bankai_watermarked');
        wp_send_json_success(['message' => 'پرچم واترمارک حذف شد. فایل اصلی در دسترس نبود.', 'src' => $src]);
    }

    private function parse_content_images(string $html): array
    {
        $out = [];
        if (!preg_match_all('/<img\b([^>]*?)>/i', $html, $matches, PREG_SET_ORDER)) {
            return $out;
        }
        foreach ($matches as $i => $m) {
            $attrs = $m[1];
            $src = '';
            $alt = '';
            if (preg_match('/\bsrc=["\']([^"\']+)["\']/i', $attrs, $sm)) {
                $src = $sm[1];
            }
            if (preg_match('/\balt=["\']([^"\']*)["\']/i', $attrs, $am)) {
                $alt = $am[1];
            }
            if ($src === '') {
                continue;
            }
            $att_id = attachment_url_to_postid($src);
            $path = $this->url_to_path($src);
            $size = ($path && file_exists($path)) ? (int) filesize($path) : 0;
            $ext = pathinfo(parse_url($src, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);
            $out[] = [
                'id'            => 'img_' . $i,
                'src'           => $src,
                'alt'           => $alt,
                'attachment_id' => $att_id,
                'size'          => $size,
                'format'        => strtolower($ext),
                'watermarked'   => $att_id ? (bool) get_post_meta($att_id, '_bankai_watermarked', true) : false,
                'has_alt'       => trim($alt) !== '',
            ];
        }
        return $out;
    }

    private function url_to_path(string $url): string
    {
        $uploads = wp_get_upload_dir();
        $baseurl = $uploads['baseurl'] ?? '';
        $basedir = $uploads['basedir'] ?? '';
        if ($baseurl && $basedir && str_starts_with($url, $baseurl)) {
            $rel = substr($url, strlen($baseurl));
            $path = $basedir . $rel;
            return file_exists($path) ? $path : '';
        }
        $home = home_url('/');
        if (str_starts_with($url, $home)) {
            $rel = substr($url, strlen($home));
            $path = ABSPATH . ltrim($rel, '/');
            return file_exists($path) ? $path : '';
        }
        return '';
    }

    private function path_to_url(string $path): string
    {
        $uploads = wp_get_upload_dir();
        $basedir = $uploads['basedir'] ?? '';
        $baseurl = $uploads['baseurl'] ?? '';
        if ($basedir && $baseurl && str_starts_with($path, $basedir)) {
            return $baseurl . str_replace('\\', '/', substr($path, strlen($basedir)));
        }
        return '';
    }

    private function mime_to_ext(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => '',
        };
    }

    private function guess_alt_from_src(string $src, int $post_id = 0): string
    {
        $name = pathinfo(parse_url($src, PHP_URL_PATH) ?: '', PATHINFO_FILENAME);
        $name = preg_replace('/[-_]+/', ' ', (string) $name);
        $name = trim(preg_replace('/\s+/', ' ', (string) $name));
        if ($name === '' && $post_id) {
            $name = get_the_title($post_id);
        }
        return mb_substr($name, 0, 120);
    }

    private function replace_img_alt(string $html, string $src, string $alt): string
    {
        $alt_esc = esc_attr($alt);
        $src_q = preg_quote($src, '/');
        // img with existing alt
        $html = preg_replace(
            '/(<img\b[^>]*\bsrc=["\']' . $src_q . '["\'][^>]*\balt=["\'])([^"\']*)(["\'])/i',
            '$1' . $alt_esc . '$3',
            $html,
            1
        );
        // if still no change, add alt attribute
        if (!preg_match('/<img\b[^>]*\bsrc=["\']' . $src_q . '["\'][^>]*\balt=/i', $html)) {
            $html = preg_replace(
                '/(<img\b[^>]*\bsrc=["\']' . $src_q . '["\'])/i',
                '$1 alt="' . $alt_esc . '"',
                $html,
                1
            );
        }
        return $html;
    }

    private function resize_image_file(string $path, int $max_width, int $quality): bool
    {
        $info = @getimagesize($path);
        if (!$info || empty($info[0]) || $info[0] <= $max_width) {
            return false;
        }
        $mime = $info['mime'];
        $img = $this->load_image($path, $mime);
        if (!$img) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $nw = $max_width;
        $nh = (int) round($h * ($nw / max(1, $w)));
        $dst = imagecreatetruecolor($nw, $nh);
        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $ok = $this->save_image($dst, $path, $mime, $quality);
        imagedestroy($dst);
        return $ok;
    }

    private function convert_to_webp_file(string $src_path, string $dest_path, int $quality): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }
        $info = @getimagesize($src_path);
        if (!$info) {
            return false;
        }
        $img = $this->load_image($src_path, $info['mime']);
        if (!$img) {
            return false;
        }
        imagealphablending($img, true);
        imagesavealpha($img, true);
        $ok = imagewebp($img, $dest_path, $quality);
        imagedestroy($img);
        return (bool) $ok;
    }

    /**
     * Paginated media library list for compress UI.
     */
    public function ajax_list_media_library(): void
    {
        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $page     = max(1, (int) ($_POST['page'] ?? 1));
        $per_page = min(48, max(6, (int) ($_POST['per_page'] ?? 18)));

        $q = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $items = [];
        foreach ($q->posts as $post) {
            $id    = (int) $post->ID;
            $file  = get_attached_file($id);
            $mime  = get_post_mime_type($id) ?: '';
            $bytes = ($file && file_exists($file)) ? (int) filesize($file) : 0;
            $format = 'img';
            if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $format = 'jpeg';
            } elseif (str_contains($mime, 'png')) {
                $format = 'png';
            } elseif (str_contains($mime, 'webp')) {
                $format = 'webp';
            } elseif (str_contains($mime, 'gif')) {
                $format = 'gif';
            }
            $thumb = wp_get_attachment_image_url($id, 'medium') ?: wp_get_attachment_url($id);
            $items[] = [
                'id'     => $id,
                'title'  => get_the_title($id) ?: ('#' . $id),
                'url'    => (string) wp_get_attachment_url($id),
                'thumb'  => (string) $thumb,
                'mime'   => $mime,
                'format' => $format,
                'bytes'  => $bytes,
            ];
        }

        wp_send_json_success([
            'items'    => $items,
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => (int) $q->found_posts,
            'has_more' => $page < (int) $q->max_num_pages,
        ]);
    }

    /**
     * Compress / convert a single attachment.
     */
    public function ajax_compress_attachment(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('upload_files')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $id = absint($_POST['id'] ?? 0);
        if ($id <= 0) {
            wp_send_json_error(['message' => __('شناسه نامعتبر.', 'bankai-core')]);
        }

        $format    = sanitize_key($_POST['format'] ?? 'webp');
        $quality   = max(40, min(95, (int) ($_POST['quality'] ?? 82)));
        $max_width = max(0, (int) ($_POST['max_width'] ?? 0));

        $result = $this->compress_attachment_file($id, $format, $quality, $max_width);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        wp_send_json_success($result);
    }

    /**
     * Core compress logic for one attachment.
     *
     * @return array|\WP_Error
     */
    public function compress_attachment_file(int $id, string $format = 'webp', int $quality = 82, int $max_width = 0)
    {
        if (!extension_loaded('gd')) {
            return new WP_Error('no_gd', __('افزونه GD در دسترس نیست.', 'bankai-core'));
        }

        $file = get_attached_file($id);
        if (!$file || !file_exists($file)) {
            return new WP_Error('missing', __('فایل یافت نشد.', 'bankai-core'));
        }

        $mime   = get_post_mime_type($id) ?: '';
        $before = (int) filesize($file);
        $image  = $this->load_image($file, $mime);
        if (!$image) {
            return new WP_Error('load', __('بارگذاری تصویر ممکن نشد.', 'bankai-core'));
        }

        $w = imagesx($image);
        $h = imagesy($image);
        if ($max_width > 0 && $w > $max_width) {
            $nw = $max_width;
            $nh = (int) round($h * ($max_width / $w));
            $resized = imagecreatetruecolor($nw, $nh);
            if ($resized) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($image);
                $image = $resized;
            }
        }

        $pathinfo = pathinfo($file);
        $dir  = $pathinfo['dirname'] ?? dirname($file);
        $base = $pathinfo['filename'] ?? 'image';

        if ($format === 'keep') {
            if (str_contains($mime, 'png')) {
                $format = 'png';
            } elseif (str_contains($mime, 'webp')) {
                $format = 'webp';
            } else {
                $format = 'jpeg';
            }
        }

        $new_path = $file;
        $new_mime = $mime;
        $ext = 'jpg';
        $ok  = false;

        if ($format === 'webp' && function_exists('imagewebp')) {
            $new_path = $dir . '/' . $base . '.webp';
            $ok = imagewebp($image, $new_path, $quality);
            $new_mime = 'image/webp';
            $ext = 'webp';
        } elseif ($format === 'png') {
            $new_path = $dir . '/' . $base . '.png';
            imagesavealpha($image, true);
            $ok = imagepng($image, $new_path, (int) max(0, min(9, round((100 - $quality) / 11))));
            $new_mime = 'image/png';
            $ext = 'png';
        } else {
            $new_path = $dir . '/' . $base . '.jpg';
            $ok = imagejpeg($image, $new_path, $quality);
            $new_mime = 'image/jpeg';
            $ext = 'jpg';
        }

        imagedestroy($image);

        if (!$ok || !file_exists($new_path)) {
            return new WP_Error('save', __('ذخیره فایل فشرده ممکن نشد.', 'bankai-core'));
        }

        if (wp_normalize_path($new_path) !== wp_normalize_path($file)) {
            update_attached_file($id, $new_path);
            wp_update_post([
                'ID'             => $id,
                'post_mime_type' => $new_mime,
            ]);
            if (file_exists($file) && wp_normalize_path($file) !== wp_normalize_path($new_path)) {
                @unlink($file);
            }
        }

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        $meta = wp_generate_attachment_metadata($id, $new_path);
        if (is_array($meta)) {
            wp_update_attachment_metadata($id, $meta);
        }

        $after = (int) filesize($new_path);
        $thumb = wp_get_attachment_image_url($id, 'medium') ?: wp_get_attachment_url($id);

        return [
            'id'          => $id,
            'bytes'       => $before,
            'bytes_after' => $after,
            'saved'       => max(0, $before - $after),
            'format'      => $ext,
            'url'         => (string) wp_get_attachment_url($id),
            'thumb'       => (string) $thumb,
            'message'     => sprintf(
                __('فشرده شد: %1$s → %2$s', 'bankai-core'),
                size_format($before),
                size_format($after)
            ),
        ];
    }


    
    public function register_cdn_routes(): void {
        register_rest_route('bankai/v1', '/media/cdn', [
            'methods' => 'GET',
            'callback' => [$this, 'api_get_cdn'],
            'permission_callback' => static function () { return current_user_can('manage_options'); },
        ]);
        register_rest_route('bankai/v1', '/media/cdn', [
            'methods' => 'POST',
            'callback' => [$this, 'api_save_cdn'],
            'permission_callback' => static function () { return current_user_can('manage_options'); },
        ]);
    }

    public static function get_cdn_settings(): array {
        $defaults = [
            'provider' => 'custom',
            'base_url' => '',
            'path_prefix' => '',
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'rewrite_urls' => true,
        ];
        $opt = function_exists('bankai_get_option') ? bankai_get_option('cdn_settings', []) : get_option('bankai_cdn_settings', []);
        if (!is_array($opt)) {
            $opt = [];
        }
        return array_merge($defaults, $opt);
    }

    public function api_get_cdn(): \WP_REST_Response {
        $s = self::get_cdn_settings();
        if (!empty($s['secret_key'])) {
            $s['secret_key'] = '********';
        }
        return new \WP_REST_Response($s, 200);
    }

    public function api_save_cdn(\WP_REST_Request $req): \WP_REST_Response {
        $body = $req->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }
        $prev = self::get_cdn_settings();
        $s = [
            'provider' => sanitize_text_field($body['provider'] ?? 'custom'),
            'base_url' => esc_url_raw($body['base_url'] ?? ''),
            'path_prefix' => trim(sanitize_text_field($body['path_prefix'] ?? ''), '/'),
            'access_key' => sanitize_text_field($body['access_key'] ?? ''),
            'secret_key' => sanitize_text_field($body['secret_key'] ?? ''),
            'bucket' => sanitize_text_field($body['bucket'] ?? ''),
            'rewrite_urls' => !empty($body['rewrite_urls']),
        ];
        if ($s['secret_key'] === '********' || $s['secret_key'] === '') {
            $s['secret_key'] = $prev['secret_key'] ?? '';
        }
        if (function_exists('bankai_update_option')) {
            bankai_update_option('cdn_settings', $s);
        } else {
            update_option('bankai_cdn_settings', $s, false);
        }
        return new \WP_REST_Response([
            'success' => true,
            'data' => array_merge($s, ['secret_key' => $s['secret_key'] ? '********' : '']),
        ], 200);
    }

    public function maybe_cdn_url(string $url, int $post_id = 0): string {
        $s = self::get_cdn_settings();
        if (empty($s['rewrite_urls']) || empty($s['base_url'])) {
            return $url;
        }
        $uploads = wp_get_upload_dir();
        $base = trailingslashit($uploads['baseurl']);
        if (strpos($url, $base) !== 0) {
            return $url;
        }
        $rel = ltrim(substr($url, strlen($base)), '/');
        $prefix = !empty($s['path_prefix']) ? trailingslashit($s['path_prefix']) : '';
        return trailingslashit($s['base_url']) . $prefix . $rel;
    }

    public function maybe_cdn_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        if (!is_array($sources)) {
            return $sources;
        }
        foreach ($sources as $w => $src) {
            if (!empty($src['url'])) {
                $sources[$w]['url'] = $this->maybe_cdn_url($src['url'], (int) $attachment_id);
            }
        }
        return $sources;
    }

    /**
     * Apply or remove watermark on selected attachment IDs.
     */
    public function ajax_bulk_watermark_ids(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        @set_time_limit(120);
        @ini_set('max_execution_time', '120');

        $ids_raw = $_POST['ids'] ?? [];
        if (is_string($ids_raw)) {
            $decoded = json_decode(wp_unslash($ids_raw), true);
            $ids_raw = is_array($decoded) ? $decoded : preg_split('/[,\s]+/', $ids_raw);
        }
        if (!is_array($ids_raw)) {
            $ids_raw = [];
        }
        $ids = array_values(array_filter(array_map('absint', $ids_raw)));
        $apply = !empty($_POST['apply']) && ($_POST['apply'] === '1' || $_POST['apply'] === 'true' || $_POST['apply'] === true);

        if (!$ids) {
            wp_send_json_error(['message' => __('هیچ تصویری انتخاب نشده است.', 'bankai-core')]);
        }

        $s = $this->get_settings();
        // Allow live studio settings override from POST (so bulk uses current UI values)
        if (!empty($_POST['wm_settings'])) {
            $raw = wp_unslash($_POST['wm_settings']);
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $s = array_merge($s, $decoded);
                    // persist so next time is consistent
                    $this->save_settings($s);
                }
            } elseif (is_array($raw)) {
                $s = array_merge($s, $raw);
                $this->save_settings($s);
            }
        }
        $s['enabled'] = true;
        // Never skip bulk by min_dimension unless explicitly set high
        if (!isset($_POST['respect_min_dim'])) {
            $s['min_dimension'] = 0;
        }
        if ($apply && !extension_loaded('gd')) {
            wp_send_json_error(['message' => __('افزونه GD روی سرور فعال نیست.', 'bankai-core')]);
        }

        $ok = 0;
        $skip = 0;
        $fail = 0;
        foreach ($ids as $id) {
            $path = get_attached_file($id);
            if (!$path || !file_exists($path)) {
                $fail++;
                continue;
            }
            $file = basename($path);
            if ($apply && $this->should_exclude_file($file, $s)) {
                $skip++;
                continue;
            }
            if ($apply) {
                if ($this->apply_watermark($path, $s)) {
                    $ok++;
                    // regenerate metadata so sizes refresh
                    if (function_exists('wp_generate_attachment_metadata')) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $meta = wp_generate_attachment_metadata($id, $path);
                        if ($meta) {
                            wp_update_attachment_metadata($id, $meta);
                        }
                    }
                } else {
                    $fail++;
                }
            } else {
                // restore original if we kept backup
                $backup = $path . '.bk-orig';
                if (file_exists($backup)) {
                    @copy($backup, $path);
                    $ok++;
                } else {
                    $skip++;
                }
            }
        }

        if ($apply) {
            $msg = sprintf(
                /* translators: %d counts */
                __('واترمارک: %1$d موفق · %2$d رد (استثنا) · %3$d ناموفق', 'bankai-core'),
                $ok, $skip, $fail
            );
        } else {
            $msg = sprintf(
                __('حذف واترمارک: %1$d بازگردانی · %2$d بدون نسخه اصلی', 'bankai-core'),
                $ok, $skip
            );
        }

        // optional simultaneous compress (single/batch ids)
        $also = !empty($_POST['also_compress']) && ($_POST['also_compress'] === '1' || $_POST['also_compress'] === 'true');
        if ($also && $ok > 0 && extension_loaded('gd')) {
            foreach ($ids as $id) {
                $path = get_attached_file($id);
                if (!$path || !file_exists($path)) continue;
                $mime = get_post_mime_type($id) ?: '';
                $quality = (int) ($s['quality'] ?? 82);
                if (method_exists($this, 'maybe_create_webp')) {
                    $this->maybe_create_webp($path, $mime, $quality);
                }
            }
        }

        wp_send_json_success([
            'message' => $msg,
            'ok'      => $ok,
            'skip'    => $skip,
            'fail'    => $fail,
        ]);
    }


    private function should_exclude_file(string $filename, array $s): bool
    {
        $patterns = $s['exclude_patterns'] ?? '';
        if (is_array($patterns)) {
            $lines = $patterns;
        } else {
            $lines = preg_split('/\r\n|\r|\n/', (string) $patterns);
        }
        $name = strtolower($filename);
        foreach ($lines as $line) {
            $line = trim(strtolower((string) $line));
            if ($line !== '' && strpos($name, $line) !== false) {
                return true;
            }
        }
        $min = (int) ($s['min_dimension'] ?? 0);
        if ($min > 0 && function_exists('getimagesize')) {
            $info = @getimagesize($filename);
            // filename only — skip dim check here
        }
        return false;
    }


    public function ajax_upload_wm_font(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (empty($_FILES['font'])) {
            wp_send_json_error(['message' => __('فایلی ارسال نشد.', 'bankai-core')]);
        }
        $file = $_FILES['font'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['ttf', 'otf', 'woff', 'woff2'], true)) {
            wp_send_json_error(['message' => __('فقط ttf/otf/woff مجاز است.', 'bankai-core')]);
        }
        // WOFF/WOFF2 allowed for CSS live preview; GD raster uses TTF/OTF or system fallback
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $overrides = ['test_form' => false, 'mimes' => [
            'ttf' => 'font/ttf', 'otf' => 'font/otf', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
        ]];
        // WP may reject font mimes — store manually in uploads/bankai-fonts
        $upload = wp_upload_dir();
        $dir = trailingslashit($upload['basedir']) . 'bankai-fonts';
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        $safe = sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $ext;
        $dest = $dir . '/' . $safe;
        $ok = false;
        if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $ok = @move_uploaded_file($file['tmp_name'], $dest);
        }
        if (!$ok && !empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
            $ok = @copy($file['tmp_name'], $dest);
            @unlink($file['tmp_name']);
        }
        if (!$ok || !file_exists($dest)) {
            wp_send_json_error(['message' => __('آپلود ناموفق بود. مجوز پوشه uploads را بررسی کنید.', 'bankai-core')]);
        }
        $url = trailingslashit($upload['baseurl']) . 'bankai-fonts/' . $safe;
        $s = $this->get_settings();
        $s['custom_font_url'] = $url;
        $s['custom_font_name'] = $safe;
        $s['custom_font_path'] = $dest;
        $s['text_font'] = 'custom';
        $list = is_array($s['custom_fonts'] ?? null) ? $s['custom_fonts'] : [];
        $id = 'cf_' . md5($safe);
        $found = false;
        foreach ($list as &$it) {
            if (($it['name'] ?? '') === $safe || ($it['id'] ?? '') === $id) {
                $it = ['id' => $id, 'name' => $safe, 'url' => $url, 'path' => $dest];
                $found = true;
                break;
            }
        }
        unset($it);
        if (!$found) {
            $list[] = ['id' => $id, 'name' => $safe, 'url' => $url, 'path' => $dest];
        }
        $s['custom_fonts'] = $list;
        $this->save_settings($s);
        $hint = in_array($ext, ['woff', 'woff2'], true)
            ? ' ' . __('(WOFF برای پیش‌نمایش؛ رندر فایل روی سرور با فونت سیستم تکمیل می‌شود)', 'bankai-core')
            : '';
        wp_send_json_success([
            'url'  => $url,
            'name' => $safe,
            'path' => $dest,
            'message' => __('فونت ذخیره شد و برای واترمارک ثبت شد.', 'bankai-core') . $hint,
        ]);
    }


    public function ajax_bulk_compress_media(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $ids_raw = $_POST['ids'] ?? [];
        if (is_string($ids_raw)) {
            $decoded = json_decode(wp_unslash($ids_raw), true);
            $ids_raw = is_array($decoded) ? $decoded : preg_split('/[,\s]+/', $ids_raw);
        }
        $ids = array_values(array_filter(array_map('absint', (array) $ids_raw)));
        $quality = max(40, min(95, (int) ($_POST['quality'] ?? 82)));
        $max_w = max(400, min(4000, (int) ($_POST['max_width'] ?? 1600)));
        $format = sanitize_key($_POST['format'] ?? 'webp');
        $done = 0;
        foreach ($ids as $id) {
            $path = get_attached_file($id);
            if (!$path || !file_exists($path)) {
                continue;
            }
            $mime = get_post_mime_type($id) ?: '';
            if ($format === 'webp' && method_exists($this, 'maybe_create_webp')) {
                $this->maybe_create_webp($path, $mime, $quality);
            }
            $info = @getimagesize($path);
            if ($info && !empty($info[0]) && $info[0] > $max_w && extension_loaded('gd') && method_exists($this, 'resize_image_file')) {
                $this->resize_image_file($path, $mime, $max_w, $quality);
            } elseif ($info && !empty($info[0]) && $info[0] > $max_w && extension_loaded('gd')) {
                // inline resize
                $img = $this->load_image($path, $mime);
                if ($img) {
                    $w = imagesx($img); $h = imagesy($img);
                    $nw = $max_w; $nh = (int) round($h * ($max_w / max(1,$w)));
                    $dst = imagecreatetruecolor($nw, $nh);
                    imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    $this->save_image($dst, $path, $mime, $quality);
                    imagedestroy($img); imagedestroy($dst);
                }
            }
            $done++;
        }
        wp_send_json_success([
            'message' => sprintf(__('%d تصویر پردازش شد.', 'bankai-core'), $done),
            'done' => $done,
        ]);
    }

}
