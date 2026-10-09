<?php
/**
 * Lucide Icons integration for Bankai Theme.
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Lucide name aliases used across the theme UI.
 */
function bankai_theme_lucide_alias(string $name): string {
    $map = [
        'close'      => 'x',
        'hamburger'  => 'hamburger',
        'menu'       => 'menu',
        'cart'       => 'shopping-cart',
        'chat'       => 'message-circle',
        'warning'    => 'triangle-alert',
        'telegram'   => 'send',
        'share'      => 'share-2',
        'dots'       => 'ellipsis',
        'arrow-up'   => 'arrow-up',
        'arrow-down' => 'arrow-down',
        'arrow-left' => 'arrow-left',
        'arrow-right'=> 'arrow-right',
        'phone'      => 'phone',
        'search'     => 'search',
        'user'       => 'user',
        'home'       => 'home',
        'mail'       => 'mail',
        'heart'      => 'heart',
        'star'       => 'star',
        'plus'       => 'plus',
        'check'      => 'check',
        'settings'   => 'settings',
        'link'       => 'link',
        'globe'      => 'globe',
        'map-pin'    => 'map-pin',
        'calendar'   => 'calendar',
        'image'      => 'image',
        'play'       => 'play',
        'info'       => 'info',
        'instagram'  => 'instagram',
    ];
    $key = sanitize_title($name);
    return $map[$key] ?? $key;
}

/**
 * Load Lucide icon nodes (cached).
 *
 * @return array<string, array>
 */
function bankai_theme_lucide_nodes(bool $full = false): array {
    static $core = null;
    static $all  = null;

    if ($full) {
        if ($all !== null) {
            return $all;
        }
        $path = get_template_directory() . '/assets/icons/lucide/icon-nodes.json';
        if (is_readable($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            $all = is_array($decoded) ? $decoded : [];
        } else {
            $all = [];
        }
        return $all;
    }

    if ($core !== null) {
        return $core;
    }
    $path = get_template_directory() . '/assets/icons/lucide/core-nodes.json';
    if (is_readable($path)) {
        $decoded = json_decode((string) file_get_contents($path), true);
        $core = is_array($decoded) ? $decoded : [];
    } else {
        $core = [];
    }
    return $core;
}

/**
 * Catalog of available icon ids (for pickers).
 *
 * @return string[]
 */
function bankai_theme_icon_catalog(): array {
    $nodes = bankai_theme_lucide_nodes(true);
    if ($nodes) {
        return array_keys($nodes);
    }
    return array_keys(bankai_theme_lucide_nodes(false));
}

/**
 * Build inner SVG markup from Lucide node list.
 */
function bankai_theme_lucide_inner(array $nodes): string {
    $html = '';
    foreach ($nodes as $node) {
        if (! is_array($node) || empty($node[0])) {
            continue;
        }
        $tag = preg_replace('/[^a-zA-Z]/', '', (string) $node[0]);
        $attrs = isset($node[1]) && is_array($node[1]) ? $node[1] : [];
        $parts = [];
        foreach ($attrs as $k => $v) {
            if (! is_scalar($v)) {
                continue;
            }
            $k = preg_replace('/[^a-zA-Z0-9\-_:]/', '', (string) $k);
            if ($k === '') {
                continue;
            }
            $parts[] = $k . '="' . esc_attr((string) $v) . '"';
        }
        $html .= '<' . $tag . ( $parts ? ' ' . implode(' ', $parts) : '' ) . '/>';
    }
    return $html;
}

/**
 * Render a Lucide icon as SVG string.
 *
 * @param array{color?:string,size?:int,class?:string,stroke?:float,margin_left?:int,margin_right?:int} $args
 */
function bankai_theme_icon(string $name, array $args = []): string {
    $lucide = bankai_theme_lucide_alias($name);
    $nodes  = bankai_theme_lucide_nodes(false);
    if (! isset($nodes[$lucide])) {
        // Fallback to full catalog (admin / rare icons).
        $all = bankai_theme_lucide_nodes(true);
        if (isset($all[$lucide])) {
            $nodes = $all;
        }
    }
    if (! isset($nodes[$lucide])) {
        $lucide = 'info';
        if (! isset($nodes[$lucide])) {
            return '';
        }
    }

    $size   = max(12, min(64, absint($args['size'] ?? 20)));
    $color  = ! empty($args['color']) ? (sanitize_hex_color($args['color']) ?: 'currentColor') : 'currentColor';
    $stroke = isset($args['stroke']) ? (float) $args['stroke'] : 2;
    $class  = sanitize_html_class($args['class'] ?? 'bankai-theme-icon');
    $ml     = isset($args['margin_left']) ? (int) $args['margin_left'] : 0;
    $mr     = isset($args['margin_right']) ? (int) $args['margin_right'] : 0;
    $style  = 'width:' . $size . 'px;height:' . $size . 'px;color:' . esc_attr($color) . ';';
    if ($ml) {
        $style .= 'margin-left:' . $ml . 'px;';
    }
    if ($mr) {
        $style .= 'margin-right:' . $mr . 'px;';
    }

    return sprintf(
        '<svg class="%1$s" xmlns="http://www.w3.org/2000/svg" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%3$s" stroke-linecap="round" stroke-linejoin="round" style="%4$s" aria-hidden="true">%5$s</svg>',
        esc_attr($class),
        $size,
        esc_attr((string) $stroke),
        esc_attr($style),
        bankai_theme_lucide_inner($nodes[$lucide])
    );
}
