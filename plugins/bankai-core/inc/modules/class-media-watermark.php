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
        if ($this->is_sub_active('lazy_loading') && $this->setting('lazy_load', true)) {
            add_filter('the_content', [$this, 'filter_content_lazy'], 20);
            add_filter('post_thumbnail_html', [$this, 'filter_thumb_lazy'], 20, 5);
            add_filter('wp_get_attachment_image_attributes', [$this, 'filter_attachment_attrs'], 20, 3);
            add_filter('wp_content_img_tag', [$this, 'filter_content_img_tag'], 10, 3);
            add_filter('wp_get_attachment_image', [$this, 'filter_attachment_image_html'], 20, 5);
        }

        // Reduce oversized originals & default quality
        if ($this->is_sub_active('webp_avif_converter') && $this->setting('convert_webp', true)) {
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
        add_action('wp_ajax_bankai_update_media_meta', [$this, 'ajax_update_media_meta']);
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

    private function is_animated_webp(string $file, string $mime): bool
    {
        if (strtolower($mime) !== 'image/webp' || !is_readable($file)) {
            return false;
        }

        $handle = @fopen($file, 'rb');
        if (!$handle) {
            return false;
        }
        $header = (string) fread($handle, 1024 * 1024);
        fclose($handle);

        return strpos($header, 'ANIM') !== false || strpos($header, 'ANMF') !== false;
    }

    private function strip_exif(string $file, string $mime): void
    {
        if (!extension_loaded('gd') || $this->is_animated_webp($file, $mime)) {
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
                        $rotated = imagerotate($img, 180, 0);
                        break;
                    case 6:
                        $rotated = imagerotate($img, -90, 0);
                        break;
                    case 8:
                        $rotated = imagerotate($img, 90, 0);
                        break;
                    default:
                        $rotated = false;
                        break;
                }
                if (!empty($rotated)) {
                    imagedestroy($img);
                    $img = $rotated;
                }
            }
        }
        $this->save_image($img, $file, $mime, 90);
        imagedestroy($img);
    }

    private function maybe_create_webp(string $file, string $mime, int $quality): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }
        $img = $this->load_image($file, $mime);
        if (!$img) {
            return false;
        }
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);
        $ok = false;
        if ($webp && $webp !== $file) {
            $ok = (bool) @imagewebp($img, $webp, max(10, min(100, $quality)));
        }
        imagedestroy($img);
        return $ok && is_file((string) $webp) && filesize((string) $webp) > 0;
    }

    /**
     * Apply text or image watermark.
     */
    public function apply_watermark(string $file_path, ?array $settings = null): bool
    {
        if (!is_string($file_path) || $file_path === '' || !file_exists($file_path) || !is_readable($file_path)) {
            return false;
        }
        if (!is_writable($file_path) && !is_writable(dirname($file_path))) {
            return false;
        }

        $s = $settings ?? $this->get_settings();
        if (!is_array($s)) {
            $s = $this->get_settings();
        }
        // Force enabled for explicit apply calls
        $s['enabled'] = true;
        // Empty text fallback
        if (($s['type'] ?? 'text') === 'text' && trim((string) ($s['text'] ?? '')) === '') {
            $s['text'] = get_bloginfo('name') ?: 'BANKAI';
        }

        // Backup original once
        if (!empty($s['keep_original'] ?? true)) {
            $bak = $file_path . '.bk-orig';
            if (!file_exists($bak)) {
                @copy($file_path, $bak);
            }
        }

        $dir = dirname($file_path);
        $tmp = trailingslashit($dir) . 'bk-wm-' . wp_generate_password(10, false) . '.tmp';
        if (!@copy($file_path, $tmp)) {
            return false;
        }

        $ok = $this->apply_watermark_inner($tmp, $s, $file_path);
        if (!$ok || !file_exists($tmp) || filesize($tmp) < 32) {
            @unlink($tmp);
            return false;
        }

        // Prefer an atomic same-directory rename; fall back to copy on platforms
        // that cannot replace an existing destination with rename().
        $written = @rename($tmp, $file_path);
        if (!$written) {
            $written = @copy($tmp, $file_path);
            @unlink($tmp);
        }
        clearstatcache(true, $file_path);
        return $written && file_exists($file_path) && filesize($file_path) > 0;
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

        $mime = (string) $info['mime'];
        if ($mime === 'image/gif' || $this->is_animated_webp($file_path, $mime)) {
            return false;
        }
        $image = $this->load_image($file_path, $mime);
        if (!$image) {
            return false;
        }
        // Convert palette images to truecolor so text/alpha watermark works
        if (function_exists('imageistruecolor') && !imageistruecolor($image)) {
            $w = imagesx($image);
            $h = imagesy($image);
            $true = imagecreatetruecolor($w, $h);
            if ($true) {
                imagealphablending($true, false);
                imagesavealpha($true, true);
                $transparent = imagecolorallocatealpha($true, 0, 0, 0, 127);
                imagefill($true, 0, 0, $transparent);
                imagealphablending($true, true);
                imagecopy($true, $image, 0, 0, 0, 0, $w, $h);
                imagedestroy($image);
                $image = $true;
            }
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
                        $available_w = max(1, $width - ($margin * 2));
                        $available_h = max(1, $height - ($margin * 2));
                        $target_w = max(1, min((int) round($width * ($pct / 100)), $available_w));
                        $target_h = max(1, min((int) round($height * ($pct / 100)), $available_h));
                        $scale = min($target_w / max(1, $ww), $target_h / max(1, $wh));
                        $nw = max(1, (int) round($ww * $scale));
                        $nh = max(1, (int) round($wh * $scale));
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
        $plugin_root = defined('BANKAI_CORE_FILE') ? dirname(BANKAI_CORE_FILE) : dirname(__DIR__);
        $plugin_fonts = trailingslashit($plugin_root) . 'assets/fonts/';

        // Custom uploaded font (TTF/OTF only — GD imagettftext requirement)
        if ($font_key === 'custom' || $font_key === 'custom_font' || str_starts_with($font_key, 'cf_')) {
            if (str_starts_with($font_key, 'cf_')) {
                foreach ((array) ($s['custom_fonts'] ?? []) as $cf) {
                    if (($cf['id'] ?? '') === $font_key) {
                        $path = (string) ($cf['path'] ?? '');
                        if ($path && is_readable($path)) {
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            if (in_array($ext, ['ttf', 'otf'], true)) {
                                return $path;
                            }
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

        if ($font_key === 'custom' && !empty($s['custom_font_name'])) {
            $custom = $this->resolve_custom_font_file($s);
            if ($custom !== '') {
                return $custom;
            }
        }

        $bundled = [
            'default'        => $plugin_fonts . 'Vazirmatn-Regular.ttf',
            'vazir'          => $plugin_fonts . 'Vazirmatn-Regular.ttf',
            'vazirmatn'      => $plugin_fonts . 'Vazirmatn-Regular.ttf',
            'vazirmatn-bold' => $plugin_fonts . 'Vazirmatn-Bold.ttf',
            'vazir-bold'     => $plugin_fonts . 'Vazirmatn-Bold.ttf',
            'dejavu'         => $plugin_fonts . 'DejaVuSans.ttf',
            'dejavu-bold'    => $plugin_fonts . 'DejaVuSans-Bold.ttf',
            'tahoma'         => $plugin_fonts . 'Vazirmatn-Regular.ttf', // system Tahoma rarely on Linux servers
            'arial'          => $plugin_fonts . 'DejaVuSans.ttf',
            'liberation'     => '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            'free-sans'      => '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
        ];

        $path = $bundled[$font_key] ?? '';
        if ($path && is_readable($path)) {
            return $path;
        }

        // System fallbacks (Arabic-capable preferred)
        $fallbacks = [
            $plugin_fonts . 'Vazirmatn-Regular.ttf',
            $plugin_fonts . 'DejaVuSans.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/noto/NotoNaskhArabic-Regular.ttf',
            '/usr/share/fonts/truetype/noto/NotoSansArabic-Regular.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
            'C:/Windows/Fonts/tahoma.ttf',
            'C:/Windows/Fonts/arial.ttf',
        ];
        foreach ($fallbacks as $fb) {
            if ($fb && is_readable($fb)) {
                return $fb;
            }
        }
        return '';
    }

    private function resolve_custom_font_file(array $s): string
    {
        $upload = wp_upload_dir();
        $font_dir = trailingslashit((string) ($upload['basedir'] ?? '')) . 'bankai-fonts';
        $real_dir = realpath($font_dir);
        if (!$real_dir) {
            return '';
        }

        $candidates = [];
        if (!empty($s['custom_font_path'])) {
            $candidates[] = (string) $s['custom_font_path'];
        }
        if (!empty($s['custom_font_name'])) {
            $candidates[] = trailingslashit($font_dir) . sanitize_file_name((string) $s['custom_font_name']);
        }
        if (!empty($s['custom_font_url'])) {
            $url = (string) $s['custom_font_url'];
            $base_url = trailingslashit((string) ($upload['baseurl'] ?? ''));
            if ($base_url !== '' && strpos($url, $base_url) === 0) {
                $relative = rawurldecode(substr($url, strlen($base_url)));
                if (strpos($relative, 'bankai-fonts/') === 0) {
                    $candidates[] = trailingslashit((string) $upload['basedir']) . $relative;
                }
            }
        }

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if (!$real || !is_file($real) || !is_readable($real)) {
                continue;
            }
            $normalized = wp_normalize_path($real);
            $normalized_dir = trailingslashit(wp_normalize_path($real_dir));
            if (strpos($normalized, $normalized_dir) !== 0) {
                continue;
            }
            $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
            if (in_array($ext, ['ttf', 'otf'], true)) {
                return $real;
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
        $len = $this->utf8_strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = $this->utf8_substr($text, $i, 1);
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
        return $this->utf8_codepoint($ch);
    }

    private function utf8_codepoint(string $char): int
    {
        $bytes = array_values(unpack('C*', $char) ?: []);
        $count = count($bytes);
        if ($count === 0) {
            return 0;
        }
        if ($bytes[0] < 0x80) {
            return $bytes[0];
        }
        if ($count >= 2 && ($bytes[0] & 0xE0) === 0xC0) {
            return (($bytes[0] & 0x1F) << 6) | ($bytes[1] & 0x3F);
        }
        if ($count >= 3 && ($bytes[0] & 0xF0) === 0xE0) {
            return (($bytes[0] & 0x0F) << 12) | (($bytes[1] & 0x3F) << 6) | ($bytes[2] & 0x3F);
        }
        if ($count >= 4) {
            return (($bytes[0] & 0x07) << 18) | (($bytes[1] & 0x3F) << 12)
                | (($bytes[2] & 0x3F) << 6) | ($bytes[3] & 0x3F);
        }
        return 0;
    }

    private function utf8_chr(int $codepoint): string
    {
        if ($codepoint <= 0x7F) {
            return chr($codepoint);
        }
        if ($codepoint <= 0x7FF) {
            return chr(0xC0 | ($codepoint >> 6)) . chr(0x80 | ($codepoint & 0x3F));
        }
        if ($codepoint <= 0xFFFF) {
            return chr(0xE0 | ($codepoint >> 12))
                . chr(0x80 | (($codepoint >> 6) & 0x3F))
                . chr(0x80 | ($codepoint & 0x3F));
        }
        return chr(0xF0 | ($codepoint >> 18))
            . chr(0x80 | (($codepoint >> 12) & 0x3F))
            . chr(0x80 | (($codepoint >> 6) & 0x3F))
            . chr(0x80 | ($codepoint & 0x3F));
    }

    private function utf8_strlen(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }
        if (preg_match_all('/./us', $value, $matches)) {
            return count($matches[0]);
        }
        return strlen($value);
    }

    private function utf8_substr(string $value, int $start, ?int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, $start, $length, 'UTF-8');
        }
        if (preg_match_all('/./us', $value, $matches)) {
            $chars = $matches[0];
            return implode('', array_slice($chars, $start, $length));
        }
        return substr($value, $start, $length);
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
        $n = $this->utf8_strlen($run);
        for ($i = 0; $i < $n; $i++) {
            $ch = $this->utf8_substr($run, $i, 1);
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
                $shaped[] = $this->utf8_chr($cp);
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
            $shaped[] = $this->utf8_chr($form);
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
            $tw = imagefontwidth($gd) * $this->utf8_strlen($text);
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

        if ($font_path && function_exists('imagettftext')) {
            // Always use TTF path for UTF-8 / Persian — never imagestring (Latin-only, produces garbage)
            if (!$bbox) {
                $bbox = @imagettfbbox($size, 0, $font_path, $text);
                if (is_array($bbox)) {
                    $th = abs($bbox[7] - $bbox[1]);
                }
            }
            $baseline = $ty + max(1, (int) $th);
            $ok = @imagettftext($image, $size, 0, $tx, $baseline, $text_col, $font_path, $text);
            if ($ok === false) {
                // Retry with bundled Vazirmatn if selected font failed
                $plugin_root = defined('BANKAI_CORE_FILE') ? dirname(BANKAI_CORE_FILE) : dirname(__DIR__);
                $fallback = trailingslashit($plugin_root) . 'assets/fonts/Vazirmatn-Regular.ttf';
                if (is_readable($fallback) && $fallback !== $font_path) {
                    @imagettftext($image, $size, 0, $tx, $baseline, $text_col, $fallback, $text);
                }
            }
        }
        // Intentionally no imagestring fallback for non-Latin text
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
        $x = max(0, min($w - max(1, $ew), $x));
        $y = max(0, min($h - max(1, $eh), $y));
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
    private function load_image(string $file, string $mime = '')
    {
        if ($file === '' || !is_readable($file)) {
            return false;
        }

        $mime = strtolower(trim((string) $mime));
        if ($mime === '' || $mime === 'application/octet-stream') {
            $info = @getimagesize($file);
            if (is_array($info) && !empty($info['mime'])) {
                $mime = strtolower((string) $info['mime']);
            } else {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $map = [
                    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpe' => 'image/jpeg',
                    'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif',
                ];
                $mime = $map[$ext] ?? '';
            }
        }

        // Normalize aliases
        if (in_array($mime, ['image/jpg', 'image/pjpeg', 'image/x-jpeg'], true)) {
            $mime = 'image/jpeg';
        }

        $img = false;
        switch ($mime) {
            case 'image/jpeg':
                if (function_exists('imagecreatefromjpeg')) {
                    $img = @imagecreatefromjpeg($file);
                }
                break;
            case 'image/png':
                if (function_exists('imagecreatefrompng')) {
                    $img = @imagecreatefrompng($file);
                    if ($img) {
                        imagealphablending($img, true);
                        imagesavealpha($img, true);
                    }
                }
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $img = @imagecreatefromwebp($file);
                    if ($img) {
                        imagealphablending($img, true);
                        imagesavealpha($img, true);
                    }
                }
                break;
            case 'image/gif':
                if (function_exists('imagecreatefromgif')) {
                    $img = @imagecreatefromgif($file);
                }
                break;
        }

        // Last resort: let GD sniff the file
        if (!$img && function_exists('imagecreatefromstring')) {
            $raw = @file_get_contents($file);
            if (is_string($raw) && $raw !== '') {
                $img = @imagecreatefromstring($raw);
                if ($img && function_exists('imagealphablending')) {
                    @imagealphablending($img, true);
                    @imagesavealpha($img, true);
                }
            }
        }

        return $img ?: false;
    }

    /** @param resource|\GdImage $image */
    private function save_image($image, string $file, string $mime, int $quality): bool
    {
        $quality = max(10, min(100, (int) $quality));
        $mime = strtolower(trim((string) $mime));
        if (in_array($mime, ['image/jpg', 'image/pjpeg', 'image/x-jpeg'], true)) {
            $mime = 'image/jpeg';
        }
        $ok = false;
        switch ($mime) {
            case 'image/jpeg':
                $ok = function_exists('imagejpeg') && @imagejpeg($image, $file, $quality);
                break;
            case 'image/png':
                $level = (int) max(0, min(9, round((100 - $quality) / 11)));
                $ok = function_exists('imagepng') && @imagepng($image, $file, $level);
                break;
            case 'image/webp':
                $ok = function_exists('imagewebp') && @imagewebp($image, $file, $quality);
                break;
            case 'image/gif':
                $ok = function_exists('imagegif') && @imagegif($image, $file);
                break;
            default:
                // Try by extension
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'jpe'], true) && function_exists('imagejpeg')) {
                    $ok = (bool) @imagejpeg($image, $file, $quality);
                } elseif ($ext === 'png' && function_exists('imagepng')) {
                    $ok = (bool) @imagepng($image, $file, 6);
                } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                    $ok = (bool) @imagewebp($image, $file, $quality);
                }
        }
        return (bool) $ok && file_exists($file) && filesize($file) > 0;
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
        $text = $this->utf8_substr($text, 0, 80);

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
            if (!empty($s['strip_exif'])) {
                $this->strip_exif($path, $mime);
            }
            if (!empty($s['enabled']) && !empty($s['apply_content']) && extension_loaded('gd')) {
                if ($this->apply_watermark($path, $s)) {
                    $watermarked++;
                }
            }
            // Create the sidecar after all source-image transformations so it
            // contains the same orientation and watermark as the original.
            if (!empty($s['convert_webp']) && $this->maybe_create_webp($path, $mime, (int) $s['quality'])) {
                $converted++;
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
            'settings'=> self::instance()->get_settings(),
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
        if ($post_id > 0 && !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => 'اجازه ویرایش این نوشته را ندارید.'], 403);
        }
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
        if ($post_id > 0 && !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => 'اجازه ویرایش این نوشته را ندارید.'], 403);
        }
        if ((!empty($_POST['convert_webp']) || !empty($_POST['resize']) || !empty($_POST['watermark']))
            && !current_user_can('upload_files')) {
            wp_send_json_error(['message' => 'برای تغییر فایل‌های رسانه مجوز بارگذاری فایل لازم است.'], 403);
        }
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
            $att_id = absint(attachment_url_to_postid($src));
            $item['attachment_id'] = $att_id;

            if ($att_id && !current_user_can('edit_post', $att_id)
                && ($do_webp || $do_resize || $do_watermark || $do_alt)) {
                $item['ok'] = false;
                $item['message'] = 'اجازه ویرایش این تصویر رسانه را ندارید.';
                $report[] = $item;
                continue;
            }

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
            if ($do_resize && $info && !empty($info[0]) && ($info[0] > $max_width || ($max_height > 0 && !empty($info[1]) && $info[1] > $max_height))) {
                $resized = $this->resize_image_file($path, $max_width, $quality, $max_height);
                if ($resized) {
                    $item['resized'] = true;
                    $item['actions'][] = 'resize';
                    $info = @getimagesize($path);
                }
            }

            // Watermark
            if ($do_watermark && file_exists($work_path)) {
                $ok = $this->apply_watermark($work_path);
                if ($ok) {
                    $item['watermarked'] = true;
                    $item['actions'][] = 'watermark';
                    if ($att_id) {
                        update_post_meta($att_id, '_bankai_watermarked', 1);
                        update_post_meta($att_id, '_bankai_original_backup', $work_path . '.bk-orig');
                    }
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

            // Alt
            if ($do_alt && trim($item['alt']) === '') {
                $new_alt = $default_alt !== '' ? $default_alt : $this->guess_alt_from_src($new_src, $post_id);
                $item['new_alt'] = $new_alt;
                $updated_html = $this->replace_img_alt($updated_html, $new_src, $new_alt);
                if ($item['original_src'] !== $new_src) {
                    $updated_html = $this->replace_img_alt($updated_html, $item['original_src'], $new_alt);
                }
                $item['actions'][] = 'alt';
                if ($att_id && current_user_can('edit_post', $att_id)) {
                    update_post_meta($att_id, '_wp_attachment_image_alt', $new_alt);
                }
            }

            if ($att_id && (!empty($item['resized']) || !empty($item['watermarked']))) {
                if (!function_exists('wp_generate_attachment_metadata')) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                }
                $attachment_meta = wp_generate_attachment_metadata($att_id, $path);
                if (is_array($attachment_meta)) {
                    wp_update_attachment_metadata($att_id, $attachment_meta);
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
                if (!current_user_can('edit_post', $cover_id)) {
                    $citem['ok'] = false;
                    $citem['message'] = 'اجازه ویرایش تصویر شاخص را ندارید.';
                } elseif ($path && is_file($path) && !$this->path_is_excluded($path, $this->get_settings())) {
                    $citem['size_before'] = (int) @filesize($path);
                    $info = @getimagesize($path);
                    $mime = $info['mime'] ?? '';
                    $citem['format_before'] = $this->mime_to_ext($mime);
                    $citem['format_after'] = $citem['format_before'];

                    if ($do_resize && $info && !empty($info[0]) && ($info[0] > $max_width || ($max_height > 0 && !empty($info[1]) && $info[1] > $max_height))) {
                        if ($this->resize_image_file($path, $max_width, $quality, $max_height)) {
                            $citem['resized'] = true;
                            $citem['actions'][] = 'resize';
                        }
                    }
                    if ($do_watermark) {
                        if ($this->apply_watermark($path)) {
                            $citem['watermarked'] = true;
                            $citem['actions'][] = 'watermark';
                            update_post_meta($cover_id, '_bankai_watermarked', 1);
                            update_post_meta($cover_id, '_bankai_original_backup', $path . '.bk-orig');
                        } else {
                            $citem['ok'] = false;
                            $citem['message'] = 'اعمال واترمارک روی تصویر شاخص ناموفق بود.';
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
                    if ($do_alt && trim($citem['alt']) === '' && current_user_can('edit_post', $cover_id)) {
                        $new_alt = $default_alt !== '' ? $default_alt : $this->guess_alt_from_src($cover_src, $post_id);
                        if ($new_alt !== '') {
                            update_post_meta($cover_id, '_wp_attachment_image_alt', $new_alt);
                            $citem['new_alt'] = $new_alt;
                            $citem['alt'] = $new_alt;
                            $citem['actions'][] = 'alt';
                        }
                    }
                    if (!empty($citem['resized']) || !empty($citem['watermarked'])) {
                        if (!function_exists('wp_generate_attachment_metadata')) {
                            require_once ABSPATH . 'wp-admin/includes/image.php';
                        }
                        $cover_meta = wp_generate_attachment_metadata($cover_id, $path);
                        if (is_array($cover_meta)) {
                            wp_update_attachment_metadata($cover_id, $cover_meta);
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

        wp_send_json_success([
            'report'  => $report,
            'content' => $updated_html,
            'count'   => count($report),
            'message' => sprintf('%d تصویر پردازش شد.', count($report)),
            'cover_id'=> $cover_id,
            'cover_src'=> $cover_src,
        ]);
    }


    public function ajax_update_media_meta(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        $id = absint($_POST['id'] ?? 0);
        if ($id <= 0 || get_post_type($id) !== 'attachment' || !current_user_can('edit_post', $id)) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست یا شناسه نامعتبر است.', 'bankai-core')], 403);
        }
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $alt = sanitize_text_field(wp_unslash($_POST['alt'] ?? ''));
        $caption = sanitize_textarea_field(wp_unslash($_POST['caption'] ?? ''));
        if ($title !== '') {
            wp_update_post(['ID' => $id, 'post_title' => $title]);
        }
        update_post_meta($id, '_wp_attachment_image_alt', $alt);
        wp_update_post(['ID' => $id, 'post_excerpt' => $caption]);
        wp_send_json_success([
            'id' => $id,
            'title' => get_the_title($id),
            'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true),
            'caption' => (string) get_post_field('post_excerpt', $id),
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
        $att_id = absint(attachment_url_to_postid($src));
        if ($att_id && get_post_type($att_id) === 'attachment' && current_user_can('edit_post', $att_id)) {
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
        if (!$att_id && $src !== '') {
            $att_id = absint(attachment_url_to_postid($src));
        }
        if (!$att_id || get_post_type($att_id) !== 'attachment' || !wp_attachment_is_image($att_id)
            || !current_user_can('edit_post', $att_id)) {
            wp_send_json_error(['message' => 'پیوست یافت نشد یا اجازه ویرایش آن را ندارید.'], 404);
        }

        $current = get_attached_file($att_id);
        if (!$current || !is_file($current)) {
            wp_send_json_error(['message' => 'فایل اصلی پیوست در دسترس نیست.'], 404);
        }

        $backup = $current . '.bk-orig';
        if (!is_file($backup)) {
            // Backward compatibility: accept a stored backup path only when it is
            // inside this site's uploads directory and is not the current file.
            $legacy = (string) get_post_meta($att_id, '_bankai_original_backup', true);
            $uploads = wp_get_upload_dir();
            $base = realpath((string) ($uploads['basedir'] ?? ''));
            $real_legacy = $legacy !== '' ? realpath($legacy) : false;
            $real_current = realpath($current);
            if ($base && $real_legacy && $real_current && $real_legacy !== $real_current
                && strpos(wp_normalize_path($real_legacy), trailingslashit(wp_normalize_path($base))) === 0) {
                $backup = $real_legacy;
            }
        }

        $backup_info = is_file($backup) ? @getimagesize($backup) : false;
        $current_mime = (string) get_post_mime_type($att_id);
        if (is_file($backup) && is_readable($backup) && $backup_info
            && (!empty($backup_info['mime']) && $backup_info['mime'] === $current_mime)
            && @copy($backup, $current)) {
            delete_post_meta($att_id, '_bankai_watermarked');
            delete_post_meta($att_id, '_bankai_original_backup');

            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }
            $meta = wp_generate_attachment_metadata($att_id, $current);
            if (is_array($meta)) {
                wp_update_attachment_metadata($att_id, $meta);
            }

            wp_send_json_success([
                'message' => 'واترمارک از نسخه پشتیبان اصلی حذف شد.',
                'src' => (string) wp_get_attachment_url($att_id),
            ]);
        }

        wp_send_json_error(['message' => 'نسخه پشتیبان معتبر پیدا نشد؛ فایل تغییری نکرد.'], 404);
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
        $baseurl = (string) ($uploads['baseurl'] ?? '');
        $basedir = (string) ($uploads['basedir'] ?? '');
        if ($baseurl === '' || $basedir === '') {
            return '';
        }

        $url_parts = wp_parse_url($url);
        $base_parts = wp_parse_url($baseurl);
        if (!$url_parts || !$base_parts) {
            return '';
        }

        $url_host = strtolower((string) ($url_parts['host'] ?? ''));
        $base_host = strtolower((string) ($base_parts['host'] ?? ''));
        if ($url_host !== '' && $base_host !== '' && $url_host !== $base_host) {
            return '';
        }

        $base_path = untrailingslashit((string) ($base_parts['path'] ?? ''));
        $url_path = (string) ($url_parts['path'] ?? '');
        if ($base_path === '' || strpos($url_path, trailingslashit($base_path)) !== 0) {
            return '';
        }

        $relative = rawurldecode(ltrim(substr($url_path, strlen($base_path)), '/'));
        if ($relative === '' || preg_match('#(^|/)\\.\\.(?:/|$)#', $relative)) {
            return '';
        }

        $candidate = trailingslashit($basedir) . $relative;
        $real_base = realpath($basedir);
        $real_file = realpath($candidate);
        if (!$real_base || !$real_file || strpos(wp_normalize_path($real_file), trailingslashit(wp_normalize_path($real_base))) !== 0) {
            return '';
        }

        return is_file($real_file) && is_readable($real_file) ? $real_file : '';
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
        return $this->utf8_substr($name, 0, 120);
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

    private function resize_image_file(string $path, int $max_width, int $quality, int $max_height = 0): bool
    {
        $info = @getimagesize($path);
        if (!$info || empty($info[0]) || empty($info[1])) {
            return false;
        }
        if (($max_width <= 0 || $info[0] <= $max_width) && ($max_height <= 0 || $info[1] <= $max_height)) {
            return false;
        }
        $mime = (string) $info['mime'];
        if ($mime === 'image/gif' || $this->is_animated_webp($path, $mime)) {
            return false;
        }
        $img = $this->load_image($path, $mime);
        if (!$img) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, $max_width > 0 ? ($max_width / max(1, $w)) : 1);
        if ($max_height > 0) {
            $scale = min($scale, $max_height / max(1, $h));
        }
        if ($scale >= 1) {
            imagedestroy($img);
            return false;
        }
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
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
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if ($nonce === '' || (!wp_verify_nonce($nonce, 'bankai_admin_nonce') && !wp_verify_nonce($nonce, 'bankai_core_admin'))) {
            wp_send_json_error(['message' => __('نشست منقضی شده — صفحه را تازه کنید.', 'bankai-core')], 403);
        }

        $page     = max(1, absint($_POST['page'] ?? $_REQUEST['page'] ?? 1));
        $per_page = min(48, max(6, absint($_POST['per_page'] ?? $_REQUEST['per_page'] ?? 18)));

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
            $size_label = $bytes > 0 ? size_format($bytes) : '—';
            $meta = wp_get_attachment_metadata($id);
            $items[] = [
                'id'         => $id,
                'title'      => get_the_title($id) ?: ('#' . $id),
                'alt'        => (string) get_post_meta($id, '_wp_attachment_image_alt', true),
                'caption'    => (string) $post->post_excerpt,
                'url'        => (string) wp_get_attachment_url($id),
                'thumb'      => (string) $thumb,
                'mime'       => $mime,
                'format'     => $format,
                'bytes'      => $bytes,
                'size'       => $size_label,
                'size_label' => $size_label,
                'width'      => (int) ($meta['width'] ?? 0),
                'height'     => (int) ($meta['height'] ?? 0),
                'selected'   => false,
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
        if ($id <= 0 || get_post_type($id) !== 'attachment' || !wp_attachment_is_image($id)
            || !current_user_can('edit_post', $id)) {
            wp_send_json_error(['message' => __('شناسه نامعتبر است یا اجازه ویرایش تصویر را ندارید.', 'bankai-core')], 403);
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
        if (get_post_type($id) !== 'attachment' || !wp_attachment_is_image($id)) {
            return new WP_Error('invalid_attachment', __('شناسه به یک تصویر پیوست‌شده تعلق ندارد.', 'bankai-core'));
        }

        $file = get_attached_file($id);
        if (!$file || !is_file($file) || !is_readable($file)
            || !is_writable($file) || !is_writable(dirname($file))) {
            return new WP_Error('missing', __('فایل یا پوشه تصویر یافت نشد یا قابل نوشتن نیست.', 'bankai-core'));
        }

        $quality = max(40, min(95, $quality));
        $max_width = max(0, min(4000, $max_width));
        $format = sanitize_key($format);
        if (!in_array($format, ['webp', 'png', 'jpeg', 'jpg', 'keep'], true)) {
            return new WP_Error('format', __('فرمت خروجی معتبر نیست.', 'bankai-core'));
        }

        $info = @getimagesize($file);
        if (!$info || empty($info['mime'])) {
            return new WP_Error('invalid_image', __('ساختار فایل تصویر معتبر نیست.', 'bankai-core'));
        }
        $mime = strtolower((string) $info['mime']);
        if ($mime === 'image/gif' || $this->is_animated_webp($file, $mime)) {
            return new WP_Error('animated_image', __('برای جلوگیری از حذف فریم‌ها، فشرده‌سازی GIF یا WebP متحرک با GD انجام نمی‌شود.', 'bankai-core'));
        }
        $before = (int) filesize($file);

        if ($format === 'keep') {
            if ($mime === 'image/png') {
                $format = 'png';
            } elseif ($mime === 'image/webp') {
                $format = 'webp';
            } else {
                $format = 'jpeg';
            }
        }
        if ($format === 'webp' && !function_exists('imagewebp')) {
            return new WP_Error('webp_unavailable', __('سرور GD از خروجی WebP پشتیبانی نمی‌کند.', 'bankai-core'));
        }

        $image = $this->load_image($file, $mime);
        if (!$image) {
            return new WP_Error('load', __('بارگذاری تصویر ممکن نشد؛ فرمت پشتیبانی‌نشده یا فایل خراب است.', 'bankai-core'));
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($max_width > 0 && $width > $max_width) {
            $new_width = $max_width;
            $new_height = max(1, (int) round($height * ($new_width / max(1, $width))));
            $resized = imagecreatetruecolor($new_width, $new_height);
            if (!$resized) {
                imagedestroy($image);
                return new WP_Error('resize', __('ساخت تصویر تغییراندازه‌یافته ناموفق بود.', 'bankai-core'));
            }
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $path_info = pathinfo($file);
        $directory = (string) ($path_info['dirname'] ?? dirname($file));
        $base = (string) ($path_info['filename'] ?? 'image');
        $extension = $format === 'jpeg' || $format === 'jpg' ? 'jpg' : $format;
        $new_mime = $format === 'webp' ? 'image/webp' : ($format === 'png' ? 'image/png' : 'image/jpeg');
        $destination = trailingslashit($directory) . $base . '.' . $extension;

        // Avoid overwriting a different attachment's file.
        if (wp_normalize_path($destination) !== wp_normalize_path($file) && file_exists($destination)) {
            $destination = trailingslashit($directory) . wp_unique_filename($directory, $base . '.' . $extension);
        }

        $temporary = trailingslashit($directory) . '.bankai-' . wp_generate_password(12, false) . '.' . $extension;
        $saved = false;
        if ($format === 'webp') {
            $saved = @imagewebp($image, $temporary, $quality);
        } elseif ($format === 'png') {
            imagesavealpha($image, true);
            $level = (int) max(0, min(9, round((100 - $quality) / 11)));
            $saved = @imagepng($image, $temporary, $level);
        } else {
            $saved = @imagejpeg($image, $temporary, $quality);
        }
        imagedestroy($image);

        if (!$saved || !is_file($temporary) || filesize($temporary) < 32) {
            @unlink($temporary);
            return new WP_Error('save', __('ذخیره فایل فشرده ممکن نشد.', 'bankai-core'));
        }

        $old_backup = $file . '.bk-before-compress';
        if (!file_exists($old_backup) && !@copy($file, $old_backup)) {
            @unlink($temporary);
            return new WP_Error('backup', __('تهیه نسخه پشتیبان پیش از فشرده‌سازی ناموفق بود؛ فایل اصلی تغییر نکرد.', 'bankai-core'));
        }

        $same_path = wp_normalize_path($destination) === wp_normalize_path($file);
        if ($same_path) {
            if (!@rename($temporary, $file)) {
                if (!@copy($temporary, $file)) {
                    @unlink($temporary);
                    return new WP_Error('replace', __('جایگزینی فایل تصویر ناموفق بود.', 'bankai-core'));
                }
                @unlink($temporary);
            }
        } else {
            if (!@rename($temporary, $destination)) {
                if (!@copy($temporary, $destination)) {
                    @unlink($temporary);
                    return new WP_Error('replace', __('انتقال فایل فشرده ناموفق بود.', 'bankai-core'));
                }
                @unlink($temporary);
            }
        }

        if (!$same_path) {
            update_attached_file($id, $destination);
            $updated = wp_update_post([
                'ID' => $id,
                'post_mime_type' => $new_mime,
            ], true);

            if (is_wp_error($updated)) {
                update_attached_file($id, $file);
                @unlink($destination);
                @copy($old_backup, $file);
                return $updated;
            }
        } else {
            // Keep attachment MIME in sync when "keep" maps a legacy extension to JPEG.
            if ((string) get_post_mime_type($id) !== $new_mime) {
                wp_update_post(['ID' => $id, 'post_mime_type' => $new_mime]);
            }
        }

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        $metadata = wp_generate_attachment_metadata($id, $destination);
        if (is_array($metadata)) {
            wp_update_attachment_metadata($id, $metadata);
        }

        if (!$same_path && is_file($file)) {
            @unlink($file);
        }

        clearstatcache(true, $destination);
        $after = is_file($destination) ? (int) filesize($destination) : 0;
        $thumb = wp_get_attachment_image_url($id, 'medium') ?: wp_get_attachment_url($id);

        return [
            'id' => $id,
            'bytes' => $before,
            'bytes_after' => $after,
            'saved' => max(0, $before - $after),
            'format' => $extension,
            'url' => (string) wp_get_attachment_url($id),
            'thumb' => (string) $thumb,
            'message' => sprintf(
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
        if (!empty($s['access_key'])) {
            $s['access_key'] = '********';
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
        if ($s['access_key'] === '********' || $s['access_key'] === '') {
            $s['access_key'] = $prev['access_key'] ?? '';
        }
        if (function_exists('bankai_update_option')) {
            bankai_update_option('cdn_settings', $s);
        } else {
            update_option('bankai_cdn_settings', $s, false);
        }
        return new \WP_REST_Response([
            'success' => true,
            'data' => array_merge($s, [
                'secret_key' => $s['secret_key'] ? '********' : '',
                'access_key' => $s['access_key'] ? '********' : '',
            ]),
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
        if (!current_user_can('manage_options') && !current_user_can('upload_files')) {
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
        $ids = array_values(array_unique(array_filter(array_map('absint', $ids_raw))));
        if (!isset($_POST['apply'])) {
            wp_send_json_error(['message' => __('عملیات واترمارک مشخص نشده است.', 'bankai-core')], 400);
        }
        $apply = in_array((string) wp_unslash($_POST['apply']), ['1', 'true'], true);
        if (count($ids) > 100) {
            wp_send_json_error(['message' => __('در هر نوبت حداکثر ۱۰۰ تصویر انتخاب کنید.', 'bankai-core')], 400);
        }

        if (!$ids) {
            wp_send_json_error(['message' => __('هیچ تصویری انتخاب نشده است.', 'bankai-core')]);
        }

        $s = $this->get_settings();
        // Allow live studio settings override from POST (so bulk uses current UI values)
        if (!empty($_POST['wm_settings']) && current_user_can('manage_options')) {
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
        $errors = [];
        foreach ($ids as $id) {
            if (get_post_type($id) !== 'attachment' || !wp_attachment_is_image($id) || !current_user_can('edit_post', $id)) {
                $skip++;
                continue;
            }
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
                    update_post_meta($id, '_bankai_watermarked', 1);
                    update_post_meta($id, '_bankai_original_backup', $path . '.bk-orig');
                    // Bust intermediate sizes so front/media library show watermarked file
                    if (function_exists('wp_generate_attachment_metadata')) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $meta = wp_generate_attachment_metadata($id, $path);
                        if (is_array($meta) && $meta) {
                            wp_update_attachment_metadata($id, $meta);
                        }
                    }
                    // Touch attachment modified time for cache-busting CDNs
                    wp_update_post([
                        'ID'                => $id,
                        'post_modified'     => current_time('mysql'),
                        'post_modified_gmt' => current_time('mysql', true),
                    ]);
                } else {
                    $fail++;
                    $errors[] = sprintf('#%d: %s', $id, __('اعمال روی فایل ناموفق (مجوز نوشتن/GD/فرمت)', 'bankai-core'));
                }
            } else {
                // restore original if we kept backup
                $backup = $path . '.bk-orig';
                if (is_file($backup) && @copy($backup, $path)) {
                    $ok++;
                    delete_post_meta($id, '_bankai_watermarked');
                    delete_post_meta($id, '_bankai_original_backup');
                    if (!function_exists('wp_generate_attachment_metadata')) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                    }
                    $meta = wp_generate_attachment_metadata($id, $path);
                    if (is_array($meta)) {
                        wp_update_attachment_metadata($id, $meta);
                    }
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

        $payload = [
            'message' => $msg,
            'ok'      => $ok,
            'skip'    => $skip,
            'fail'    => $fail,
        ];
        if (!empty($errors)) {
            $payload['errors'] = array_slice($errors, 0, 5);
            if ($ok === 0 && $fail > 0) {
                $payload['message'] = $msg . ' — ' . $errors[0];
            }
        }
        wp_send_json_success($payload);
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
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        if (empty($_FILES['font']) || !is_array($_FILES['font'])) {
            wp_send_json_error(['message' => __('فایلی ارسال نشد.', 'bankai-core')], 400);
        }

        $file = $_FILES['font'];
        if (!empty($file['error']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            wp_send_json_error(['message' => __('آپلود فونت کامل یا معتبر نیست.', 'bankai-core')], 400);
        }
        if (!empty($file['size']) && (int) $file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error(['message' => __('حداکثر حجم فونت ۱۰ مگابایت است.', 'bankai-core')], 400);
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed_signatures = [
            'ttf'  => ["\x00\x01\x00\x00", 'true', 'typ1'],
            'otf'  => ['OTTO'],
            'woff' => ['wOFF'],
            'woff2'=> ['wOF2'],
        ];
        if (!isset($allowed_signatures[$ext])) {
            wp_send_json_error(['message' => __('فقط فایل‌های TTF، OTF، WOFF و WOFF2 مجاز هستند.', 'bankai-core')], 400);
        }

        $handle = @fopen($file['tmp_name'], 'rb');
        $signature = $handle ? (string) fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }
        if (!in_array($signature, $allowed_signatures[$ext], true)) {
            wp_send_json_error(['message' => __('محتوای فایل با پسوند فونت مطابقت ندارد.', 'bankai-core')], 400);
        }

        $upload = wp_upload_dir();
        if (!empty($upload['error'])) {
            wp_send_json_error(['message' => (string) $upload['error']], 500);
        }
        $dir = trailingslashit($upload['basedir']) . 'bankai-fonts';
        if (!wp_mkdir_p($dir) || !is_writable($dir)) {
            wp_send_json_error(['message' => __('پوشه فونت‌ها قابل نوشتن نیست.', 'bankai-core')], 500);
        }

        $base = sanitize_file_name(pathinfo((string) $file['name'], PATHINFO_FILENAME));
        if ($base === '') {
            $base = 'bankai-font';
        }
        $filename = wp_unique_filename($dir, $base . '.' . $ext);
        $destination = trailingslashit($dir) . $filename;
        if (!@move_uploaded_file($file['tmp_name'], $destination) || !is_file($destination)) {
            wp_send_json_error(['message' => __('ذخیره فونت در پوشه uploads ناموفق بود.', 'bankai-core')], 500);
        }

        $url = trailingslashit($upload['baseurl']) . 'bankai-fonts/' . rawurlencode($filename);
        $settings = $this->get_settings();
        $settings['custom_font_url'] = esc_url_raw($url);
        $settings['custom_font_name'] = $filename;
        $settings['custom_font_path'] = $destination;
        $settings['text_font'] = 'custom';

        $font_id = 'cf_' . md5($filename . '|' . filesize($destination));
        $fonts = is_array($settings['custom_fonts'] ?? null) ? $settings['custom_fonts'] : [];
        $fonts[] = ['id' => $font_id, 'name' => $filename, 'url' => $url, 'path' => $destination];
        $settings['custom_fonts'] = $fonts;
        $this->save_settings($settings);

        wp_send_json_success([
            'url' => $url,
            'name' => $filename,
            'message' => __('فونت معتبر ذخیره و به کتابخانه فونت‌ها اضافه شد.', 'bankai-core'),
            'server_render_supported' => in_array($ext, ['ttf', 'otf'], true),
        ]);
    }


    public function ajax_bulk_compress_media(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }

        $ids_raw = $_POST['ids'] ?? [];
        if (is_string($ids_raw)) {
            $raw = wp_unslash($ids_raw);
            $decoded = json_decode($raw, true);
            $ids_raw = is_array($decoded) ? $decoded : preg_split('/[,\s]+/', $raw);
        }
        $ids = array_values(array_unique(array_filter(array_map('absint', (array) $ids_raw))));
        if (!$ids) {
            wp_send_json_error(['message' => __('هیچ تصویری انتخاب نشده است.', 'bankai-core')], 400);
        }
        if (count($ids) > 100) {
            wp_send_json_error(['message' => __('در هر نوبت حداکثر ۱۰۰ تصویر انتخاب کنید.', 'bankai-core')], 400);
        }

        $quality = max(40, min(95, (int) ($_POST['quality'] ?? 82)));
        $max_width = max(0, min(4000, (int) ($_POST['max_width'] ?? 1600)));
        $format = sanitize_key(wp_unslash($_POST['format'] ?? 'webp'));
        if (!in_array($format, ['webp', 'jpeg', 'jpg', 'png', 'keep'], true)) {
            wp_send_json_error(['message' => __('فرمت خروجی پشتیبانی نمی‌شود.', 'bankai-core')], 400);
        }

        $done = 0;
        $failed = 0;
        $converted = 0;
        $resized = 0;
        $errors = [];

        foreach ($ids as $id) {
            if (get_post_type($id) !== 'attachment' || !wp_attachment_is_image($id)) {
                $failed++;
                continue;
            }

            $path = get_attached_file($id);
            if (!$path || !is_file($path) || !is_readable($path) || !is_writable($path)) {
                $failed++;
                continue;
            }

            $before = (int) filesize($path);
            $info = @getimagesize($path);
            if (!$info || empty($info['mime'])) {
                $failed++;
                continue;
            }

            // JPEG/PNG are real attachment conversions; let the single-image
            // routine make the backup before any resize or format change.
            if (in_array($format, ['jpeg', 'jpg', 'png'], true)) {
                $result = $this->compress_attachment_file($id, $format, $quality, $max_width);
                if (is_wp_error($result)) {
                    $errors[] = sprintf('#%d: %s', $id, $result->get_error_message());
                    $failed++;
                    continue;
                }
                $done++;
                $converted++;
                continue;
            }

            $changed = false;
            if ($max_width > 0 && !empty($info[0]) && $info[0] > $max_width) {
                $resize_backup = $path . '.bk-before-compress';
                if (!file_exists($resize_backup) && !@copy($path, $resize_backup)) {
                    $errors[] = sprintf('#%d: %s', $id, __('تهیه نسخه پشتیبان ناموفق بود.', 'bankai-core'));
                    $failed++;
                    continue;
                }
                if ($this->resize_image_file($path, $max_width, $quality)) {
                    $changed = true;
                    $resized++;
                    if (!function_exists('wp_generate_attachment_metadata')) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                    }
                    $metadata = wp_generate_attachment_metadata($id, $path);
                    if (is_array($metadata)) {
                        wp_update_attachment_metadata($id, $metadata);
                    }
                }
            }

            if ($format === 'webp') {
                $current_info = @getimagesize($path);
                $mime = (string) ($current_info['mime'] ?? '');
                if ($mime !== 'image/webp' && $this->maybe_create_webp($path, $mime, $quality)) {
                    $changed = true;
                    $converted++;
                    update_post_meta($id, '_bankai_webp_path', preg_replace('/\.(jpe?g|png)$/i', '.webp', $path));
                }
            }

            if ($changed) {
                $done++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(__('%1$d تصویر پردازش شد؛ %2$d مورد ناموفق.', 'bankai-core'), $done, $failed),
            'done' => $done,
            'failed' => $failed,
            'converted' => $converted,
            'resized' => $resized,
            'errors' => array_slice($errors, 0, 5),
        ]);
    }

}
