<?php
/**
 * Customizer SEO AI generation (uses Bankai Core AI when available)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

add_action('wp_ajax_bankai_theme_seo_ai', 'bankai_theme_seo_ai_ajax');

function bankai_theme_seo_ai_ajax(): void {
    check_ajax_referer('bankai_theme_seo_ai', 'nonce');
    if (! current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }
    $page = sanitize_key(wp_unslash($_POST['page'] ?? 'home'));
    $labels = [
        'home'   => 'صفحه خانه',
        'blog'   => 'بلاگ و مقالات',
        'single' => 'صفحه مقاله',
        'search' => 'جستجو',
        '404'    => 'صفحه ۴۰۴',
        'login'  => 'ورود',
    ];
    $label = $labels[$page] ?? $page;
    $site = get_bloginfo('name');
    $tagline = get_bloginfo('description');

    $title = '';
    $desc = '';
    $keywords = '';

    // Try Bankai Core AI if present
    $prompt = "برای سایت «{$site}» ({$tagline}) بخش «{$label}» را سئو کن. "
        . "خروجی فقط JSON با کلیدهای title (۵۰-۶۰ کاراکتر)، description (۱۴۰-۱۶۰ کاراکتر)، keywords (۵ کلمه کلیدی با کاما) به فارسی.";

    if (class_exists('Bankai_AI_Studio') || function_exists('bankai_ai_complete') || class_exists('Bankai_Core_AI')) {
        $text = '';
        if (function_exists('bankai_ai_complete')) {
            $text = (string) bankai_ai_complete($prompt);
        } elseif (class_exists('Bankai_AI_Studio') && method_exists('Bankai_AI_Studio', 'chat')) {
            try {
                $text = (string) Bankai_AI_Studio::chat($prompt);
            } catch (Throwable $e) {
                $text = '';
            }
        }
        if ($text !== '') {
            if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
                $json = json_decode($m[0], true);
                if (is_array($json)) {
                    $title = sanitize_text_field($json['title'] ?? '');
                    $desc = sanitize_textarea_field($json['description'] ?? '');
                    $keywords = sanitize_text_field($json['keywords'] ?? '');
                }
            }
        }
    }

    // Fallback heuristic SEO
    if ($title === '') {
        $title = trim($site . ' | ' . $label);
        if (mb_strlen($title) > 60) {
            $title = mb_substr($title, 0, 57) . '…';
        }
    }
    if ($desc === '') {
        $desc = $tagline !== ''
            ? mb_substr($tagline . ' — ' . $label . ' در ' . $site, 0, 155)
            : mb_substr("مشاهده {$label} در {$site}. محتوای به‌روز و کاربردی.", 0, 155);
    }
    if ($keywords === '') {
        $keywords = implode('، ', array_filter([$site, $label, 'وردپرس', 'محتوا', 'سئو']));
    }

    // Persist theme mods
    set_theme_mod("page_{$page}_seo_title", $title);
    set_theme_mod("page_{$page}_seo_desc", $desc);
    set_theme_mod("page_{$page}_seo_keywords", $keywords);

    wp_send_json_success([
        'title'       => $title,
        'description' => $desc,
        'keywords'    => $keywords,
        'message'     => __('سئو تولید شد. برای اعمال، انتشار را بزنید.', 'bankai-theme'),
    ]);
}

/**
 * Output meta tags from theme SEO mods when on matching templates.
 */
function bankai_theme_seo_meta(): void {
    // اگر پلاگین Bankai Core فعال است، متا فرانت را به موتور سئوی پلاگین بسپار (جلوگیری از تگ تکراری)
    if (class_exists('Bankai_SEO_Engine') || class_exists('Bankai_SEO_Sitewide')) {
        if (is_singular()) {
            return;
        }
    }
    $key = null;
    if (is_front_page()) {
        $key = 'home';
    } elseif (is_home() || is_archive()) {
        $key = 'blog';
    } elseif (is_singular('post')) {
        return;
    } elseif (is_search()) {
        $key = 'search';
    } elseif (is_404()) {
        $key = '404';
    }
    if (! $key) {
        return;
    }
    $title = (string) get_theme_mod("page_{$key}_seo_title", '');
    $desc = (string) get_theme_mod("page_{$key}_seo_desc", '');
    $kw = (string) get_theme_mod("page_{$key}_seo_keywords", '');
    if ($desc !== '') {
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }
    if ($kw !== '') {
        echo '<meta name="keywords" content="' . esc_attr($kw) . '">' . "\n";
    }
    if ($title !== '') {
        // document title filter is cleaner
    }
}
add_action('wp_head', 'bankai_theme_seo_meta', 1);

add_filter('pre_get_document_title', static function ($title) {
    $key = null;
    if (is_front_page()) {
        $key = 'home';
    } elseif (is_home()) {
        $key = 'blog';
    } elseif (is_search()) {
        $key = 'search';
    } elseif (is_404()) {
        $key = '404';
    }
    if ($key) {
        $custom = (string) get_theme_mod("page_{$key}_seo_title", '');
        if ($custom !== '') {
            return $custom;
        }
    }
    return $title;
}, 20);
