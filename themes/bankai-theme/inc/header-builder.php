<?php
/**
 * Header builder — data + front render
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Default 3-row header layout.
 */
function bankai_header_default_config(): array {
    return [
        'sticky'      => true,
        'transparent' => false,
        'behavior'    => 'sticky', // sticky | transparent | hide-on-scroll | sticky-scroll
        'rows'        => [
            [
                'id'      => 'top',
                'enabled' => false,
                'height'  => 36,
                'bg'      => '',
                'color'   => '',
                'border'  => false,
                'shadow'  => false,
                'cols'    => [
                    ['align' => 'start', 'elements' => []],
                    ['align' => 'center', 'elements' => []],
                    ['align' => 'end', 'elements' => ['social']],
                ],
            ],
            [
                'id'      => 'main',
                'enabled' => true,
                'height'  => 72,
                'bg'      => '',
                'color'   => '',
                'border'  => true,
                'shadow'  => false,
                'cols'    => [
                    ['align' => 'start', 'elements' => ['logo']],
                    ['align' => 'center', 'elements' => ['menu']],
                    ['align' => 'end', 'elements' => ['search']],
                ],
            ],
            [
                'id'      => 'bottom',
                'enabled' => false,
                'height'  => 44,
                'bg'      => '',
                'color'   => '',
                'border'  => false,
                'shadow'  => false,
                'cols'    => [
                    ['align' => 'start', 'elements' => []],
                    ['align' => 'center', 'elements' => []],
                    ['align' => 'end', 'elements' => []],
                ],
            ],
        ],
        'mobile_menu' => [
            'icon' => 'hamburger',
            'style' => 'drawer-right',
            'show_logo' => true,
            'show_search' => true,
            'logo_url' => '',
        ],
        'nav_desktop' => ['style_mode' => 'inherit', 'bg' => '', 'color' => '', 'shadow' => false, 'font' => '', 'font_size' => 0],
        'nav_mobile'  => ['style_mode' => 'inherit', 'bg' => '', 'color' => '', 'shadow' => false, 'font' => '', 'font_size' => 0],
    ];
}

function bankai_header_get_config(): array {
    $raw = get_theme_mod('bankai_header_builder', '');
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && ! empty($decoded['rows'])) {
            return wp_parse_args($decoded, bankai_header_default_config());
        }
    }
    if (is_array($raw) && ! empty($raw['rows'])) {
        return wp_parse_args($raw, bankai_header_default_config());
    }
    return bankai_header_default_config();
}

function bankai_header_sanitize_config($value): string {
    if (is_array($value)) {
        $data = $value;
    } elseif (is_string($value)) {
        $data = json_decode($value, true);
    } else {
        return wp_json_encode(bankai_header_default_config());
    }
    if (! is_array($data)) {
        return wp_json_encode(bankai_header_default_config());
    }
    $default = bankai_header_default_config();
    $behaviors = ['sticky', 'static', 'transparent', 'hide-on-scroll', 'sticky-scroll'];
    $behavior = $data['behavior'] ?? 'sticky';
    if (! in_array($behavior, $behaviors, true)) {
        $behavior = 'sticky';
    }
    $out = [
        'sticky'      => in_array($behavior, ['sticky', 'transparent', 'sticky-scroll'], true),
        'transparent' => ($behavior === 'transparent'),
        'behavior'    => $behavior,
        'mobile_menu' => [
            'icon'   => sanitize_title($data['mobile_menu']['icon'] ?? 'menu'),
            'style'  => in_array($data['mobile_menu']['style'] ?? '', ['drawer-right', 'drawer-left', 'dropdown', 'sheet', 'bottom-sheet'], true)
                ? str_replace('bottom-sheet', 'sheet', $data['mobile_menu']['style']) : 'drawer-right',
            'show_logo'   => ! isset($data['mobile_menu']['show_logo']) || ! empty($data['mobile_menu']['show_logo']),
            'show_search' => ! isset($data['mobile_menu']['show_search']) || ! empty($data['mobile_menu']['show_search']),
            'logo_url'    => esc_url_raw($data['mobile_menu']['logo_url'] ?? ''),
        ],
        'nav_desktop' => [
            'style_mode' => (($data['nav_desktop']['style_mode'] ?? 'inherit') === 'custom') ? 'custom' : 'inherit',
            'bg'     => bankai_sanitize_color_or_special(($data['nav_desktop'] ?? [])['bg'] ?? ''),
            'color'  => bankai_sanitize_color_or_special(($data['nav_desktop'] ?? [])['color'] ?? ''),
            'shadow' => ! empty(($data['nav_desktop'] ?? [])['shadow']),
            'font'   => sanitize_text_field(($data['nav_desktop'] ?? [])['font'] ?? ''),
            'font_size' => max(10, min(28, absint(($data['nav_desktop'] ?? [])['font_size'] ?? 0))),
        ],
        'nav_mobile' => [
            'style_mode' => (($data['nav_mobile']['style_mode'] ?? 'inherit') === 'custom') ? 'custom' : 'inherit',
            'bg'     => bankai_sanitize_color_or_special(($data['nav_mobile'] ?? [])['bg'] ?? ''),
            'color'  => bankai_sanitize_color_or_special(($data['nav_mobile'] ?? [])['color'] ?? ''),
            'shadow' => ! empty(($data['nav_mobile'] ?? [])['shadow']),
            'font'   => sanitize_text_field(($data['nav_mobile'] ?? [])['font'] ?? ''),
            'font_size' => max(10, min(28, absint(($data['nav_mobile'] ?? [])['font_size'] ?? 0))),
        ],
        'rows'        => [],
        'mobile'      => $data['mobile'] ?? null,
    ];
    $allowed_el = ['logo', 'menu', 'search', 'social', 'button', 'html', 'account', 'divider', 'cart', 'phone', 'text'];
    $rows_in = $data['rows'] ?? $default['rows'];
    foreach (array_slice($rows_in, 0, 3) as $i => $row) {
        $cols = [];
        $col_count = max(1, min(3, absint($row['colCount'] ?? count($row['cols'] ?? [1, 1, 1]))));
        foreach (array_slice($row['cols'] ?? [[], [], []], 0, $col_count) as $col) {
            $els = [];
            foreach ((array) ($col['elements'] ?? []) as $el) {
                if (is_string($el)) {
                    $type = sanitize_key($el);
                    $props = [];
                } elseif (is_array($el)) {
                    $type = sanitize_key($el['type'] ?? '');
                    $props = is_array($el['props'] ?? null) ? $el['props'] : [];
                } else {
                    continue;
                }
                if (! in_array($type, $allowed_el, true)) {
                    continue;
                }
                $clean = [];
                foreach ($props as $pk => $pv) {
                    $pk = sanitize_key($pk);
                    if (is_bool($pv)) {
                        $clean[$pk] = $pv;
                    } elseif (is_numeric($pv)) {
                        $clean[$pk] = 0 + $pv;
                    } elseif (is_string($pv)) {
                        if (str_contains($pk, 'color') || $pk === 'bg' || $pk === 'color') {
                            $clean[$pk] = sanitize_hex_color($pv) ?: sanitize_text_field($pv);
                        } elseif ($pk === 'html') {
                            $clean[$pk] = wp_kses_post($pv);
                        } elseif ($pk === 'url') {
                            $clean[$pk] = esc_url_raw($pv);
                        } else {
                            $clean[$pk] = sanitize_text_field($pv);
                        }
                    }
                }
                $els[] = ['type' => $type, 'props' => $clean];
            }
            $align = $col['align'] ?? 'start';
            if (! in_array($align, ['start', 'center', 'end'], true)) {
                $align = 'start';
            }
            $cols[] = ['align' => $align, 'elements' => $els];
        }
        while (count($cols) < $col_count) {
            $cols[] = ['align' => 'start', 'elements' => []];
        }
        $out['rows'][] = [
            'id'         => sanitize_key($row['id'] ?? ('row' . $i)),
            'enabled'    => ! empty($row['enabled']),
            'height'     => max(28, min(160, absint($row['height'] ?? 60))),
            'bg'         => sanitize_hex_color($row['bg'] ?? '') ?: '',
            'color'      => sanitize_hex_color($row['color'] ?? '') ?: '',
            'link_color' => sanitize_hex_color($row['link_color'] ?? '') ?: '',
            'link_hover' => sanitize_hex_color($row['link_hover'] ?? '') ?: '',
            'link_active' => sanitize_hex_color($row['link_active'] ?? '') ?: '',
            'border'     => ! empty($row['border']),
            'shadow'     => ! empty($row['shadow']),
            'colCount'   => $col_count,
            'cols'       => $cols,
        ];
    }
    // Persist mobile rows if present
    if (isset($data['mobile']) && is_array($data['mobile'])) {
        $out['mobile'] = $data['mobile'];
    }
    return wp_json_encode($out);
}

function bankai_sanitize_color_or_special($v): string {
    $v = is_string($v) ? trim($v) : '';
    if ($v === '' || $v === 'none') {
        return '';
    }
    if ($v === 'transparent') {
        return 'transparent';
    }
    $hex = sanitize_hex_color($v);
    return $hex ?: '';
}

function bankai_header_icon_svg(string $name, int $size = 20): string {
    $map = ['hamburger' => 'menu', 'menu' => 'menu'];
    $key = sanitize_key($map[$name] ?? $name);
    if (function_exists('bankai_theme_icon')) {
        $html = bankai_theme_icon($key, ['size' => $size]);
        if (is_string($html) && $html !== '') {
            return $html;
        }
    }
    // Minimal fallback
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' . absint($size) . '" height="' . absint($size) . '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>';
}

/**
 * Render a single header element.
 */
function bankai_header_find_logo_url(array $cfg): string {
    foreach ($cfg['rows'] ?? [] as $row) {
        foreach ($row['cols'] ?? [] as $col) {
            foreach ($col['elements'] ?? [] as $el) {
                $type = is_array($el) ? ($el['type'] ?? '') : $el;
                if ($type !== 'logo') {
                    continue;
                }
                $props = is_array($el) ? ($el['props'] ?? []) : [];
                if (! empty($props['image'])) {
                    return esc_url($props['image']);
                }
            }
        }
    }
    // WP custom logo
    $id = get_theme_mod('custom_logo');
    if ($id) {
        $src = wp_get_attachment_image_url((int) $id, 'full');
        if ($src) {
            return $src;
        }
    }
    return '';
}

function bankai_header_render_element($el): void {

    $type = is_array($el) ? (string) ($el['type'] ?? 'html') : (string) $el;
    $props = is_array($el) ? (array) ($el['props'] ?? []) : [];
    $style_bits = [];
    if (! empty($props['color'])) {
        $style_bits[] = 'color:' . esc_attr($props['color']);
    }
    if (! empty($props['bg'])) {
        $style_bits[] = 'background:' . esc_attr($props['bg']);
    }
    if (isset($props['radius']) && $props['radius'] !== '') {
        $style_bits[] = 'border-radius:' . (int) $props['radius'] . 'px';
    }
    $style_attr = $style_bits ? ' style="' . esc_attr(implode(';', $style_bits)) . '"' : '';

    switch ($type) {
        case 'logo':
            $w = absint($props['width'] ?? 0);
            $h = absint($props['height'] ?? 0);
            $mode = $props['mode'] ?? 'auto'; // auto | image | text
            echo '<div class="bankai-brand"' . ($w || $h ? ' style="' . ($w ? 'max-width:' . $w . 'px;' : '') . ($h ? 'max-height:' . $h . 'px;' : '') . '"' : '') . '>';
            if ($mode === 'text' || (! has_custom_logo() && empty($props['image']))) {
                $label = $props['label'] ?? get_bloginfo('name');
                echo '<a class="bankai-site-title" href="' . esc_url(home_url('/')) . '"' . $style_attr . '>' . esc_html($label) . '</a>';
            } elseif (! empty($props['image'])) {
                echo '<a href="' . esc_url(home_url('/')) . '"><img src="' . esc_url($props['image']) . '" alt="' . esc_attr(get_bloginfo('name')) . '"' .
                    ($w ? ' width="' . $w . '"' : '') . ($h ? ' height="' . $h . '"' : '') . ' style="max-width:100%;height:auto;object-fit:contain;" /></a>';
            } else {
                the_custom_logo();
            }
            echo '</div>';
            break;

        case 'menu':
            $active = ! empty($props['active_color']) ? sanitize_hex_color($props['active_color']) : '';
            $link_c = ! empty($props['color']) ? sanitize_hex_color($props['color']) : '';
            $style_menu = '';
            if ($link_c) {
                $style_menu .= '--bankai-header-link:' . esc_attr($link_c) . ';';
            }
            if ($active) {
                $style_menu .= '--bankai-header-link-active:' . esc_attr($active) . ';';
            }
            echo '<nav class="bankai-nav"' . ($style_menu ? ' style="' . esc_attr($style_menu) . '"' : '') . ' aria-label="' . esc_attr__('منوی اصلی', 'bankai-theme') . '">';
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'bankai-menu',
                'fallback_cb'    => 'bankai_fallback_menu',
                'depth'          => 3,
            ]);
            echo '</nav>';
            break;

        case 'search':
            $icon = sanitize_key($props['icon'] ?? 'search');
            $size = absint($props['size'] ?? 20) ?: 20;
            $box  = $props['box'] ?? 'icon'; // icon | expand | full
            $ph   = $props['placeholder'] ?? __('جستجو…', 'bankai-theme');
            $icon_html = ! empty($props['icon_image'])
                ? '<img src="' . esc_url($props['icon_image']) . '" alt="" width="' . $size . '" height="' . $size . '" style="object-fit:contain" />'
                : bankai_header_icon_svg($icon, $size);
            if ($box === 'full') {
                echo '<form class="bankai-header-search bankai-header-search--full" role="search" method="get" action="' . esc_url(home_url('/')) . '"' . $style_attr . '>';
                echo '<input type="search" name="s" placeholder="' . esc_attr($ph) . '" value="' . esc_attr(get_search_query()) . '" />';
                echo '<button type="submit" aria-label="' . esc_attr__('جستجو', 'bankai-theme') . '">' . $icon_html . '</button>';
                echo '</form>';
            } else {
                echo '<button type="button" class="bankai-icon-btn bankai-header-search-btn" data-search-box="' . esc_attr($box) . '" aria-label="' . esc_attr__('جستجو', 'bankai-theme') . '"' . $style_attr . '>';
                echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '</button>';
            }
            break;

        case 'button':
            $text = $props['label'] ?? bankai_theme_mod('header_cta_text', __('تماس', 'bankai-theme'));
            $url  = $props['url'] ?? bankai_theme_mod('header_cta_url', home_url('/'));
            $btn_style = $props['style'] ?? 'solid';
            echo '<a class="bankai-btn bankai-btn--' . esc_attr($btn_style) . ' bankai-header-cta" href="' . esc_url($url) . '"' . $style_attr . '>' . esc_html($text) . '</a>';
            break;


        case 'account':
            $size = absint($props['size'] ?? 20) ?: 20;
            $url = is_user_logged_in() ? admin_url() : wp_login_url();
            $label = is_user_logged_in() ? __('حساب', 'bankai-theme') : __('ورود', 'bankai-theme');
            echo '<a class="bankai-header-account bankai-icon-btn" href="' . esc_url($url) . '"' . $style_attr . '>';
            echo bankai_header_icon_svg('user', $size);
            if (! empty($props['show_label'])) {
                echo ' <span>' . esc_html($label) . '</span>';
            }
            echo '</a>';
            break;

        case 'cart':
            $size = absint($props['size'] ?? 20) ?: 20;
            $url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart');
            echo '<a class="bankai-header-cart bankai-icon-btn" href="' . esc_url($url) . '"' . $style_attr . '>' . bankai_header_icon_svg('cart', $size) . '</a>';
            break;

        case 'phone':
            $tel = $props['label'] ?? bankai_theme_mod('header_phone', '');
            $size = absint($props['size'] ?? 18) ?: 18;
            if ($tel) {
                echo '<a class="bankai-header-phone" href="tel:' . esc_attr(preg_replace('/\s+/', '', $tel)) . '"' . $style_attr . '>';
                echo bankai_header_icon_svg('phone', $size) . ' <span>' . esc_html($tel) . '</span></a>';
            }
            break;

        case 'text':
            $txt = $props['label'] ?? $props['html'] ?? '';
            if ($txt) {
                echo '<span class="bankai-header-text"' . $style_attr . '>' . esc_html($txt) . '</span>';
            }
            break;

        case 'social':
            $links = array_filter([
                bankai_theme_mod('footer_social_telegram', ''),
                bankai_theme_mod('footer_social_instagram', ''),
            ]);
            echo '<div class="bankai-header-social"' . $style_attr . '>';
            foreach ($links as $url) {
                echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">•</a>';
            }
            if (! $links) {
                echo '<span class="bankai-muted">Social</span>';
            }
            echo '</div>';
            break;

        case 'html':
            $html = $props['html'] ?? bankai_theme_mod('header_html_snippet', '');
            if ($html) {
                echo '<div class="bankai-header-html">' . wp_kses_post($html) . '</div>';
            }
            break;

        case 'divider':
            echo '<span class="bankai-header-divider" aria-hidden="true"></span>';
            break;
    }
}

function bankai_header_render(): void {
    $cfg = bankai_header_get_config();
    $classes = ['bankai-header', 'bankai-header--builder'];
    $behavior = $cfg['behavior'] ?? 'sticky';
    if ($behavior === 'sticky' || $behavior === 'transparent' || ! empty($cfg['sticky'])) {
        $classes[] = 'is-sticky';
    }
    if ($behavior === 'transparent' || ! empty($cfg['transparent'])) {
        $classes[] = 'is-transparent';
    }
    if ($behavior === 'hide-on-scroll') {
        $classes[] = 'is-hide-on-scroll';
    }
    if ($behavior === 'sticky-scroll') {
        $classes[] = 'is-sticky-scroll';
    }
    echo '<header id="masthead" class="' . esc_attr(implode(' ', $classes)) . '" data-header-behavior="' . esc_attr($cfg['behavior'] ?? 'sticky') . '">';
    foreach ($cfg['rows'] as $row) {
        if (empty($row['enabled'])) {
            continue;
        }
        $style = '';
        if (! empty($row['height'])) {
            $style .= 'min-height:' . (int) $row['height'] . 'px;';
        }
        if (! empty($row['bg']) && ! in_array(strtolower(trim($row['bg'])), ['#fff', '#ffffff', 'white'], true)) {
            $style .= 'background:' . esc_attr($row['bg']) . ';';
        }
        if (! empty($row['color'])) {
            $style .= 'color:' . esc_attr($row['color']) . ';';
        }
        if (! empty($row['link_color'])) {
            $style .= '--bankai-header-link:' . esc_attr($row['link_color']) . ';';
        }
        if (! empty($row['link_hover'])) {
            $style .= '--bankai-header-link-hover:' . esc_attr($row['link_hover']) . ';';
        }
        if (! empty($row['link_active'])) {
            $style .= '--bankai-header-link-active:' . esc_attr($row['link_active']) . ';';
        }
        $row_class = 'bankai-header-row bankai-header-row--' . esc_attr($row['id']);
        if (! empty($row['border'])) {
            $row_class .= ' has-border';
        }
        if (! empty($row['shadow'])) {
            $row_class .= ' has-shadow';
        }
        echo '<div class="' . esc_attr($row_class) . '" style="' . esc_attr($style) . '">';
        echo '<div class="' . esc_attr(bankai_container_class()) . ' bankai-header-row__inner">';
        foreach ($row['cols'] as $col) {
            $align = $col['align'] ?? 'start';
            echo '<div class="bankai-header-col bankai-header-col--' . esc_attr($align) . '">';
            foreach ($col['elements'] as $el) {
                bankai_header_render_element($el);
            }
            echo '</div>';
        }
        echo '</div></div>';
    }
    /* —— Mobile: single row ☰ | LOGO | 🔍 + shared menu (4 modes) —— */
    $mm = $cfg['mobile_menu'] ?? ['icon' => 'hamburger', 'style' => 'drawer-right'];
    $mm_style = $mm['style'] ?? 'drawer-right';
    if ($mm_style === 'bottom-sheet') {
        $mm_style = 'sheet';
    }
    $mm_icon = $mm['icon'] ?? 'hamburger';
    $allowed_mm = ['drawer-right', 'drawer-left', 'dropdown', 'sheet'];
    if (! in_array($mm_style, $allowed_mm, true)) {
        $mm_style = 'drawer-right';
    }

    $show_logo = ! isset($mm['show_logo']) || ! empty($mm['show_logo']);
    $show_search = ! isset($mm['show_search']) || ! empty($mm['show_search']);
    $logo_url = ! empty($mm['logo_url']) ? $mm['logo_url'] : bankai_header_find_logo_url($cfg);
    $nd = $cfg['nav_desktop'] ?? [];
    $nm = $cfg['nav_mobile'] ?? [];

    // Desktop header nav skin — only when style_mode is custom
    $desk_style = '';
    if (($nd['style_mode'] ?? 'inherit') === 'custom') {
        if (! empty($nd['bg']) && $nd['bg'] !== 'none') { $desk_style .= 'background:' . esc_attr($nd['bg']) . ';'; }
        if (! empty($nd['color'])) { $desk_style .= 'color:' . esc_attr($nd['color']) . ';'; }
        if (! empty($nd['font'])) { $desk_style .= 'font-family:' . esc_attr($nd['font']) . ';'; }
        if (! empty($nd['font_size'])) { $desk_style .= 'font-size:' . (int) $nd['font_size'] . 'px;'; }
        if (! empty($nd['shadow'])) { $desk_style .= 'box-shadow:0 4px 20px rgba(0,0,0,.08);'; }
    }

    $mob_style = '';
    if (($nm['style_mode'] ?? 'inherit') === 'custom') {
        if (! empty($nm['bg'])) { $mob_style .= 'background:' . esc_attr($nm['bg']) . ';'; }
        if (! empty($nm['color'])) { $mob_style .= 'color:' . esc_attr($nm['color']) . ';'; }
        if (! empty($nm['font'])) { $mob_style .= 'font-family:' . esc_attr($nm['font']) . ';'; }
        if (! empty($nm['font_size'])) { $mob_style .= 'font-size:' . (int) $nm['font_size'] . 'px;'; }
        if (! empty($nm['shadow'])) { $mob_style .= 'box-shadow:0 4px 16px rgba(0,0,0,.1);'; }
    }

    // Apply desktop skin on header element via inline when set
    if ($desk_style !== '') {
        // already opened header — inject via style tag scoped
        echo '<style id="bankai-nav-desktop-skin">.bankai-header--builder>.bankai-header-row{' . $desk_style . '}</style>';
    }

    echo '<div class="bankai-mobile-bar" data-menu-style="' . esc_attr($mm_style) . '"' . ($mob_style ? ' style="' . esc_attr($mob_style) . '"' : '') . '>';
    echo '<div class="' . esc_attr(bankai_container_class()) . ' bankai-mobile-bar__inner">';

    // 1) Hamburger — always (menu control)
    echo '<button type="button" class="bankai-icon-btn bankai-mobile-nav-toggle" aria-controls="bankai-mobile-nav" aria-expanded="false" data-menu-style="' . esc_attr($mm_style) . '" aria-label="' . esc_attr__('منو', 'bankai-theme') . '">';
    // Always honor selected Lucide icon; only pure empty falls back to menu
    $icon_key = ($mm_icon === '' || $mm_icon === null) ? 'menu' : $mm_icon;
    if ($icon_key === 'hamburger') {
        $icon_key = 'menu';
    }
    echo bankai_header_icon_svg($icon_key, 22);
    echo '</button>';

    // 2) Brand logo — only if enabled AND logo exists (no forced site title)
    echo '<div class="bankai-mobile-bar__brand">';
    if ($show_logo && $logo_url) {
        echo '<a class="bankai-mobile-bar__logo-link" href="' . esc_url(home_url('/')) . '">';
        echo '<img class="bankai-mobile-bar__logo" src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" />';
        echo '</a>';
    }
    echo '</div>';

    // 3) Search — only if enabled
    if ($show_search) {
        echo '<button type="button" class="bankai-icon-btn bankai-mobile-search-toggle" aria-controls="bankai-mobile-search" aria-expanded="false" aria-label="' . esc_attr__('جستجو', 'bankai-theme') . '">';
        echo bankai_header_icon_svg('search', 22);
        echo '</button>';
    } else {
        echo '<span class="bankai-mobile-bar__spacer" aria-hidden="true"></span>';
    }

    echo '</div>'; // inner
    echo '</div>'; // bar

    // Mobile search panel (inline under bar)
    echo '<div id="bankai-mobile-search" class="bankai-mobile-search" hidden>';
    echo '<form class="bankai-mobile-search__form" role="search" method="get" action="' . esc_url(home_url('/')) . '">';
    echo '<input type="search" class="bankai-mobile-search__input" name="s" placeholder="' . esc_attr__('جستجو…', 'bankai-theme') . '" value="' . esc_attr(get_search_query()) . '" />';
    echo '<button type="submit" class="bankai-icon-btn" aria-label="' . esc_attr__('جستجو', 'bankai-theme') . '">' . bankai_header_icon_svg('search', 20) . '</button>';
    echo '</form></div>';

    // Mobile nav is rendered in wp_footer (fixed overlay over full page)
    echo '</header>';
}



/**
 * Default footer builder config (3 rows).
 */
function bankai_mobile_nav_markup(): void {
    if (! function_exists('bankai_header_get_config')) {
        return;
    }
    $cfg = bankai_header_get_config();
    $mm = $cfg['mobile_menu'] ?? ['icon' => 'hamburger', 'style' => 'drawer-right'];
    $mm_style = $mm['style'] ?? 'drawer-right';
    if ($mm_style === 'bottom-sheet') {
        $mm_style = 'sheet';
    }
    if (! in_array($mm_style, ['drawer-right', 'drawer-left', 'dropdown', 'sheet'], true)) {
        $mm_style = 'drawer-right';
    }
    echo '<div id="bankai-mobile-nav" class="bankai-mobile-nav bankai-mobile-nav--' . esc_attr($mm_style) . '" hidden>';
    echo '<div class="bankai-mobile-nav__backdrop" tabindex="-1"></div>';
    echo '<div class="bankai-mobile-nav__panel" role="dialog" aria-modal="true" aria-label="' . esc_attr__('منوی موبایل', 'bankai-theme') . '">';
    if ($mm_style === 'sheet') {
        echo '<div class="bankai-mobile-nav__handle" aria-hidden="true"></div>';
    }
    // Close via backdrop click only (no close button)
    echo '<nav class="bankai-mobile-nav__body" aria-label="' . esc_attr__('منوی اصلی', 'bankai-theme') . '">';
    wp_nav_menu([
        'theme_location' => 'primary',
        'container' => false,
        'menu_class' => 'bankai-mobile-menu',
        'fallback_cb' => 'bankai_fallback_menu',
        'depth' => 3,
    ]);
    echo '</nav></div></div>';
}
add_action('wp_footer', 'bankai_mobile_nav_markup', 5);


/**
 * Render footer builder rows + fixed credit bar.
 */
function bankai_footer_builder_render(): void {
    $cfg = bankai_footer_get_config();
    $rows = $cfg['rows'] ?? [];
    echo '<footer id="colophon" class="bankai-footer bankai-footer--builder">';
    $any = false;
    foreach ($rows as $row) {
        if (empty($row['enabled'])) {
            continue;
        }
        $any = true;
        $style = '';
        if (! empty($row['height'])) {
            $style .= 'min-height:' . (int) $row['height'] . 'px;';
        }
        if (! empty($row['bg']) && ! in_array(strtolower(trim((string) $row['bg'])), ['#fff', '#ffffff', 'white'], true)) {
            $style .= 'background:' . esc_attr($row['bg']) . ';';
        }
        if (! empty($row['color'])) {
            $style .= 'color:' . esc_attr($row['color']) . ';';
        }
        $cls = 'bankai-footer-row bankai-footer-row--' . esc_attr($row['id'] ?? 'row');
        if (! empty($row['border'])) {
            $cls .= ' has-border';
        }
        if (! empty($row['shadow'])) {
            $cls .= ' has-shadow';
        }
        $cols = $row['cols'] ?? [];
        $n = max(1, min(4, count($cols) ?: 1));
        echo '<div class="' . esc_attr($cls) . '" style="' . esc_attr($style) . '">';
        echo '<div class="' . esc_attr(bankai_container_class()) . ' bankai-footer-row__inner bankai-footer-row__inner--cols-' . (int) $n . '">';
        foreach ($cols as $col) {
            $align = $col['align'] ?? 'start';
            echo '<div class="bankai-footer-col bankai-footer-col--' . esc_attr($align) . '">';
            foreach ($col['elements'] ?? [] as $el) {
                bankai_footer_render_element($el);
            }
            echo '</div>';
        }
        echo '</div></div>';
    }
    // Fixed credit — always present
    echo '<div class="bankai-footer__credit">';
    echo '<div class="' . esc_attr(bankai_container_class()) . ' bankai-footer__credit-inner">';
    echo '<a class="bankai-footer__brand" href="https://gcorp.cc" target="_blank" rel="noopener noreferrer">GCORP LLC</a>';
    echo '<span class="bankai-footer__powered">Powered Dr Manhattan</span>';
    echo '</div></div>';
    echo '</footer>';
}

function bankai_footer_render_element($el): void {
    if (! is_array($el)) {
        $el = ['type' => (string) $el, 'props' => []];
    }
    $type = $el['type'] ?? '';
    $props = $el['props'] ?? [];
    switch ($type) {
        case 'widget':
            $id = absint($props['sidebar'] ?? 0);
            if ($id >= 1 && $id <= 4 && is_active_sidebar('footer-' . $id)) {
                dynamic_sidebar('footer-' . $id);
            } elseif (is_active_sidebar('footer-1')) {
                dynamic_sidebar('footer-1');
            }
            break;
        case 'menu':
            wp_nav_menu([
                'theme_location' => 'footer',
                'container' => false,
                'menu_class' => 'bankai-menu bankai-menu--footer',
                'depth' => 1,
                'fallback_cb' => false,
            ]);
            break;
        case 'social':
            echo '<ul class="bankai-social bankai-social--footer">';
            $nets = ['telegram' => 'send', 'instagram' => 'instagram', 'linkedin' => 'linkedin', 'x' => 'twitter', 'youtube' => 'youtube'];
            foreach ($nets as $net => $ico) {
                $url = $props[$net] ?? '';
                if ($url === '') {
                    continue;
                }
                echo '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . bankai_header_icon_svg($ico, 18) . '<span>' . esc_html(ucfirst($net)) . '</span></a></li>';
            }
            echo '</ul>';
            break;
        case 'copyright':
            echo '<p class="bankai-footer__copy">&copy; ' . esc_html(date_i18n('Y')) . ' ' . esc_html(get_bloginfo('name')) . '</p>';
            break;
        case 'html':
            echo wp_kses_post($props['html'] ?? '');
            break;
        case 'divider':
            echo '<hr class="bankai-footer-divider" />';
            break;
        case 'text':
            echo '<div class="bankai-footer-text">' . esc_html($props['text'] ?? '') . '</div>';
            break;
        default:
            break;
    }
}


function bankai_footer_extras_markup(): void {
    $cfg = bankai_footer_get_config();
    $scroll = $cfg['scroll_top'] ?? ['enabled' => true, 'icon' => 'arrow-up', 'position' => 'right', 'size' => 48];
    $contact = $cfg['contact_fab'] ?? ['enabled' => false];
    $bottom = $cfg['bottom_nav'] ?? ['enabled' => false, 'items' => []];

    if (! empty($scroll['enabled'])) {
        $st = '';
        $sm = (($scroll['style_mode'] ?? 'inherit') === 'custom') ? 'custom' : 'inherit';
        if ($sm === 'custom') {
            if (! empty($scroll['bg'])) {
                $st .= 'background:' . esc_attr($scroll['bg']) . ';';
            }
            if (! empty($scroll['color'])) {
                $st .= 'color:' . esc_attr($scroll['color']) . ';';
            }
        }
        if (! empty($scroll['size'])) {
            $st .= 'width:' . (int) $scroll['size'] . 'px;height:' . (int) $scroll['size'] . 'px;';
        }
        $rad = isset($scroll['radius']) ? (int) $scroll['radius'] : 999;
        $st .= 'border-radius:' . $rad . 'px;';
        $side = ($scroll['position'] ?? 'right') === 'left' ? 'left' : 'right';
        $ico_sz = max(14, (int) ($scroll['icon_size'] ?? 22));
        $ico_col = $scroll['icon_color'] ?? '';
        echo '<button type="button" class="bankai-scroll-top bankai-scroll-top--' . esc_attr($side) . ' bankai-fab--' . esc_attr($sm) . '" style="' . esc_attr($st) . '" aria-label="top" hidden>';
        echo bankai_header_icon_svg($scroll['icon'] ?? 'arrow-up', $ico_sz, $ico_col);
        echo '</button>';
    }

    if (! empty($contact['enabled']) && ! empty($contact['url'])) {
        $st = '';
        $sm = (($contact['style_mode'] ?? 'inherit') === 'custom') ? 'custom' : 'inherit';
        if ($sm === 'custom') {
            if (! empty($contact['bg'])) {
                $st .= 'background:' . esc_attr($contact['bg']) . ';';
            }
            if (! empty($contact['color'])) {
                $st .= 'color:' . esc_attr($contact['color']) . ';';
            }
        }
        if (! empty($contact['size'])) {
            $st .= 'width:' . (int) $contact['size'] . 'px;height:' . (int) $contact['size'] . 'px;';
        }
        $rad = isset($contact['radius']) ? (int) $contact['radius'] : 999;
        $st .= 'border-radius:' . $rad . 'px;';
        $side = (! empty($scroll['enabled']) && ($scroll['position'] ?? 'right') === 'right') ? 'left' : 'right';
        $ico_sz = max(14, (int) ($contact['icon_size'] ?? 22));
        $ico_col = $contact['icon_color'] ?? '';
        echo '<a class="bankai-fab-contact bankai-fab-contact--' . esc_attr($side) . ' bankai-fab--' . esc_attr($sm) . '" href="' . esc_url($contact['url']) . '" style="' . esc_attr($st) . '">';
        echo bankai_header_icon_svg($contact['icon'] ?? 'phone', $ico_sz, $ico_col);
        echo '</a>';
    }

    if (! empty($bottom['enabled']) && ! empty($bottom['items'])) {
        $mode = ($bottom['style_mode'] ?? 'inherit') === 'custom' ? 'custom' : 'inherit';
        $cols = in_array((int) ($bottom['columns'] ?? 3), [3, 4, 5], true) ? (int) $bottom['columns'] : 3;
        $layout = in_array($bottom['item_layout'] ?? '', ['icon', 'icon-text', 'icon-text-below'], true) ? $bottom['item_layout'] : 'icon-text-below';
        $st = '';
        if ($mode === 'custom') {
            if (! empty($bottom['bg'])) {
                $st .= 'background:' . esc_attr($bottom['bg']) . ';';
            }
            if (! empty($bottom['color'])) {
                $st .= 'color:' . esc_attr($bottom['color']) . ';';
            }
        }
        $items = array_values($bottom['items'] ?? []);
        while (count($items) < $cols) {
            $items[] = ['icon' => 'circle', 'label' => '', 'url' => '#'];
        }
        // Inherit = no inline custom colors
        if ($mode !== 'custom') {
            $st = '';
        }
        echo '<nav class="bankai-bottom-nav bankai-bottom-nav--' . esc_attr($mode) . ($mode === 'custom' ? ' bankai-bottom-nav--custom' : '') . ' bankai-bottom-nav--cols-' . esc_attr((string) $cols) . ' bankai-bottom-nav--' . esc_attr($layout) . '" style="' . esc_attr($st) . '">';
        foreach (array_slice($items, 0, $cols) as $item) {
            echo '<a class="bankai-bottom-nav__item" href="' . esc_url(($item['url'] ?? '') !== '' ? $item['url'] : '#') . '">';
            echo bankai_header_icon_svg($item['icon'] ?? 'home', 22);
            if ($layout !== 'icon' && ! empty($item['label'])) {
                echo '<span>' . esc_html($item['label']) . '</span>';
            }
            echo '</a>';
        }
        echo '</nav>';
    }
}
add_action('wp_footer', 'bankai_footer_extras_markup', 8);

function bankai_footer_default_config(): array {
    return [
        'scroll_top' => [
            'enabled' => true,
            'style_mode' => 'inherit',
            'icon' => 'arrow-up',
            'bg' => '',
            'color' => '',
            'size' => 48,
            'radius' => 999,
            'position' => 'right',
        ],
        'contact_fab' => [
            'enabled' => false,
            'style_mode' => 'inherit',
            'icon' => 'phone',
            'url' => '',
            'bg' => '',
            'color' => '',
            'size' => 48,
            'radius' => 999,
        ],
        'bottom_nav' => [
            'enabled' => false,
            'style_mode' => 'inherit',
            'bg' => '',
            'color' => '',
            'columns' => 3,
            'item_layout' => 'icon-text-below',
            'items' => [
                ['icon' => 'home', 'label' => 'خانه', 'url' => '/'],
                ['icon' => 'search', 'label' => 'جستجو', 'url' => '/?s='],
                ['icon' => 'user', 'label' => 'حساب', 'url' => '/'],
            ],
        ],
        'rows' => [
            [
                'id' => 'top', 'enabled' => false, 'height' => 48, 'bg' => '', 'border' => false, 'shadow' => false,
                'cols' => [
                    ['align' => 'start', 'elements' => []],
                    ['align' => 'center', 'elements' => []],
                    ['align' => 'end', 'elements' => ['social']],
                ],
            ],
            [
                'id' => 'main', 'enabled' => true, 'height' => 120, 'bg' => '', 'border' => false, 'shadow' => false,
                'cols' => [
                    ['align' => 'start', 'elements' => ['widget']],
                    ['align' => 'center', 'elements' => ['widget']],
                    ['align' => 'end', 'elements' => ['widget']],
                ],
            ],
            [
                'id' => 'bottom', 'enabled' => true, 'height' => 48, 'bg' => '', 'border' => true, 'shadow' => false,
                'cols' => [
                    ['align' => 'center', 'elements' => ['copyright']],
                    ['align' => 'center', 'elements' => []],
                    ['align' => 'end', 'elements' => []],
                ],
            ],
        ],
    ];
}

function bankai_footer_get_config(): array {
    $defaults = bankai_footer_default_config();
    $raw = get_theme_mod('bankai_footer_builder', '');
    if (is_string($raw) && $raw !== '') {
        $d = json_decode($raw, true);
        if (is_array($d) && ! empty($d['rows'])) {
            return array_replace_recursive($defaults, $d);
        }
    }
    if (is_array($raw) && ! empty($raw['rows'])) {
        return array_replace_recursive($defaults, $raw);
    }
    return $defaults;
}

function bankai_footer_sanitize_config($value): string {
    if (is_array($value)) {
        $data = $value;
    } elseif (is_string($value)) {
        $data = json_decode($value, true);
    } else {
        return wp_json_encode(bankai_footer_default_config());
    }
    if (! is_array($data) || empty($data['rows'])) {
        return wp_json_encode(bankai_footer_default_config());
    }
    $allowed = ['widget', 'social', 'copyright', 'menu', 'html', 'divider'];
    $st = is_array($data['scroll_top'] ?? null) ? $data['scroll_top'] : [];
    $ct = is_array($data['contact_fab'] ?? null) ? $data['contact_fab'] : [];
    $bn = is_array($data['bottom_nav'] ?? null) ? $data['bottom_nav'] : [];
    $out = [
        'scroll_top' => [
            'enabled' => ! empty($st['enabled']),
            'style_mode' => (($st['style_mode'] ?? '') === 'custom') ? 'custom' : 'inherit',
            'icon' => sanitize_key($st['icon'] ?? 'arrow-up'),
            'bg' => sanitize_hex_color($st['bg'] ?? '') ?: '',
            'color' => sanitize_hex_color($st['color'] ?? '') ?: '',
            'size' => max(36, min(72, absint($st['size'] ?? 48))),
            'radius' => max(0, min(999, absint($st['radius'] ?? 999))),
            'position' => in_array($st['position'] ?? 'right', ['left', 'right'], true) ? ($st['position'] ?? 'right') : 'right',
            'icon_stroke' => floatval($st['icon_stroke'] ?? 2),
            'icon_size' => max(14, min(40, absint($st['icon_size'] ?? 22))),
            'icon_color' => sanitize_hex_color($st['icon_color'] ?? '') ?: '',
        ],
        'contact_fab' => [
            'enabled' => ! empty($ct['enabled']),
            'style_mode' => (($ct['style_mode'] ?? '') === 'custom') ? 'custom' : 'inherit',
            'icon' => sanitize_key($ct['icon'] ?? 'phone'),
            'url' => esc_url_raw($ct['url'] ?? ''),
            'bg' => sanitize_hex_color($ct['bg'] ?? '') ?: '',
            'color' => sanitize_hex_color($ct['color'] ?? '') ?: '',
            'size' => max(36, min(72, absint($ct['size'] ?? 48))),
            'radius' => max(0, min(999, absint($ct['radius'] ?? 999))),
            'icon_stroke' => floatval($ct['icon_stroke'] ?? 2),
            'icon_size' => max(14, min(40, absint($ct['icon_size'] ?? 22))),
            'icon_color' => sanitize_hex_color($ct['icon_color'] ?? '') ?: '',
        ],
        'bottom_nav' => [
            'enabled' => ! empty($bn['enabled']),
            'style_mode' => (($bn['style_mode'] ?? '') === 'custom') ? 'custom' : 'inherit',
            'bg' => sanitize_hex_color($bn['bg'] ?? '') ?: '',
            'color' => sanitize_hex_color($bn['color'] ?? '') ?: '',
            'columns' => in_array((int) ($bn['columns'] ?? 3), [3, 4, 5], true) ? (int) $bn['columns'] : 3,
            'item_layout' => in_array($bn['item_layout'] ?? '', ['icon', 'icon-text', 'icon-text-below'], true) ? $bn['item_layout'] : 'icon-text-below',
            'items' => [],
        ],
        'rows' => [],
    ];
    foreach (array_slice($bn['items'] ?? [], 0, 5) as $it) {
        if (! is_array($it)) {
            continue;
        }
        $out['bottom_nav']['items'][] = [
            'icon' => sanitize_key($it['icon'] ?? 'home'),
            'label' => sanitize_text_field($it['label'] ?? ''),
            'url' => esc_url_raw($it['url'] ?? ''),
        ];
    }
    foreach (array_slice($data['rows'] ?? [], 0, 3) as $i => $row) {
        $cols = [];
        foreach (array_slice($row['cols'] ?? [[], [], []], 0, 3) as $col) {
            $els = [];
            foreach ((array) ($col['elements'] ?? []) as $el) {
                $el = sanitize_key($el);
                if (in_array($el, $allowed, true)) {
                    $els[] = $el;
                }
            }
            $align = in_array($col['align'] ?? '', ['start', 'center', 'end'], true) ? $col['align'] : 'start';
            $cols[] = ['align' => $align, 'elements' => $els];
        }
        while (count($cols) < 3) {
            $cols[] = ['align' => 'start', 'elements' => []];
        }
        $out['rows'][] = [
            'id' => sanitize_key($row['id'] ?? ('row' . $i)),
            'enabled' => ! empty($row['enabled']),
            'height' => max(28, min(200, absint($row['height'] ?? 60))),
            'bg' => sanitize_hex_color($row['bg'] ?? '') ?: '',
            'border' => ! empty($row['border']),
            'shadow' => ! empty($row['shadow']),
            'cols' => $cols,
        ];
    }
    return wp_json_encode($out);
}
