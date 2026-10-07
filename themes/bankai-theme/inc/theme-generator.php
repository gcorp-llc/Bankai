<?php
/**
 * Theme design tokens + presets
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Built-in theme presets.
 *
 * @return array<string, array{label:string,scheme:string,colors:array<string,string>,radius:array<string,string>}>
 */
function bankai_theme_presets(): array {
    return [
        'light' => [
            'label'  => 'Light',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#570DF8', 'secondary' => '#F000B8', 'accent' => '#37CDBE',
                'neutral' => '#3D4451', 'base' => '#FFFFFF', 'base_content' => '#1F2937',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'dark' => [
            'label'  => 'Dark',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#661AE6', 'secondary' => '#D926AA', 'accent' => '#1FB2A6',
                'neutral' => '#191D24', 'base' => '#2A303C', 'base_content' => '#A6ADBB',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'cupcake' => [
            'label'  => 'Cupcake',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#65C3C8', 'secondary' => '#EF9FBC', 'accent' => '#EEAF3A',
                'neutral' => '#291334', 'base' => '#FAF7F5', 'base_content' => '#291334',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '2rem', 'selector' => '1rem'],
        ],
        'bumblebee' => [
            'label'  => 'Bumblebee',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#E0A82E', 'secondary' => '#F9D72F', 'accent' => '#181830',
                'neutral' => '#181830', 'base' => '#FFFFFF', 'base_content' => '#333333',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'emerald' => [
            'label'  => 'Emerald',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#66CC8A', 'secondary' => '#377CFB', 'accent' => '#F68067',
                'neutral' => '#333C4D', 'base' => '#FFFFFF', 'base_content' => '#333C4D',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'corporate' => [
            'label'  => 'Corporate',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#4B6BFB', 'secondary' => '#7B92B2', 'accent' => '#67CBA0',
                'neutral' => '#181A2A', 'base' => '#FFFFFF', 'base_content' => '#181A2A',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '.25rem', 'field' => '.25rem', 'selector' => '.25rem'],
        ],
        'synthwave' => [
            'label'  => 'Synthwave',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#E779C1', 'secondary' => '#58C7F3', 'accent' => '#F3CC30',
                'neutral' => '#20134E', 'base' => '#2D1B69', 'base_content' => '#F9F7FD',
                'info' => '#53C0F3', 'success' => '#71EAD2', 'warning' => '#F3CC30', 'error' => '#E24056',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'retro' => [
            'label'  => 'Retro',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#EF9995', 'secondary' => '#A4CBB4', 'accent' => '#DC8850',
                'neutral' => '#2E282A', 'base' => '#E4D8B4', 'base_content' => '#2E282A',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '.5rem', 'field' => '.25rem', 'selector' => '.25rem'],
        ],
        'cyberpunk' => [
            'label'  => 'Cyberpunk',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#FF7598', 'secondary' => '#75D1F0', 'accent' => '#C07EEC',
                'neutral' => '#1A103D', 'base' => '#FFEE00', 'base_content' => '#1A103D',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '0', 'field' => '0', 'selector' => '0'],
        ],
        'valentine' => [
            'label'  => 'Valentine',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#E96D7B', 'secondary' => '#A991F7', 'accent' => '#88DBDD',
                'neutral' => '#AF4670', 'base' => '#F0D6E8', 'base_content' => '#632C3B',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '2rem', 'selector' => '1rem'],
        ],
        'halloween' => [
            'label'  => 'Halloween',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#F28C18', 'secondary' => '#6D3A9C', 'accent' => '#51A800',
                'neutral' => '#1B1D1D', 'base' => '#212121', 'base_content' => '#E0E0E0',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'garden' => [
            'label'  => 'Garden',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#5C7F67', 'secondary' => '#ECAE7B', 'accent' => '#B4CCB9',
                'neutral' => '#2A3230', 'base' => '#E9E7E7', 'base_content' => '#2A3230',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'forest' => [
            'label'  => 'Forest',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#1EB854', 'secondary' => '#1DB88E', 'accent' => '#1DB8AB',
                'neutral' => '#19362D', 'base' => '#171212', 'base_content' => '#E0E0E0',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '1rem', 'selector' => '1rem'],
        ],
        'aqua' => [
            'label'  => 'Aqua',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#09ECF4', 'secondary' => '#966FB4', 'accent' => '#EDE75F',
                'neutral' => '#0B2D3B', 'base' => '#345DA7', 'base_content' => '#C5D8F0',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'lofi' => [
            'label'  => 'Lofi',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#0D0D0D', 'secondary' => '#1A1919', 'accent' => '#262626',
                'neutral' => '#000000', 'base' => '#FFFFFF', 'base_content' => '#000000',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '.25rem', 'field' => '.25rem', 'selector' => '.25rem'],
        ],
        'pastel' => [
            'label'  => 'Pastel',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#D1C1D7', 'secondary' => '#F6CBD1', 'accent' => '#B4E9D6',
                'neutral' => '#70ACC7', 'base' => '#FFFFFF', 'base_content' => '#3D3D3D',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '2rem', 'selector' => '1rem'],
        ],
        'fantasy' => [
            'label'  => 'Fantasy',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#6E0B75', 'secondary' => '#007EBD', 'accent' => '#F8860D',
                'neutral' => '#1F2937', 'base' => '#FFFFFF', 'base_content' => '#1F2937',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'wireframe' => [
            'label'  => 'Wireframe',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#B8B8B8', 'secondary' => '#B8B8B8', 'accent' => '#B8B8B8',
                'neutral' => '#EBEBEB', 'base' => '#FFFFFF', 'base_content' => '#000000',
                'info' => '#0000FF', 'success' => '#008000', 'warning' => '#A6A659', 'error' => '#FF0000',
            ],
            'radius' => ['box' => '.25rem', 'field' => '.25rem', 'selector' => '.25rem'],
        ],
        'black' => [
            'label'  => 'Black',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#343232', 'secondary' => '#343232', 'accent' => '#343232',
                'neutral' => '#000000', 'base' => '#000000', 'base_content' => '#FFFFFF',
                'info' => '#0000FF', 'success' => '#008000', 'warning' => '#A6A659', 'error' => '#FF0000',
            ],
            'radius' => ['box' => '0', 'field' => '0', 'selector' => '0'],
        ],
        'luxury' => [
            'label'  => 'Luxury',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#FFFFFF', 'secondary' => '#152747', 'accent' => '#513448',
                'neutral' => '#331A00', 'base' => '#09090B', 'base_content' => '#DCA54C',
                'info' => '#66C6FF', 'success' => '#87D039', 'warning' => '#E2D562', 'error' => '#FF6F6F',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'dracula' => [
            'label'  => 'Dracula',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#FF79C6', 'secondary' => '#BD93F9', 'accent' => '#FFB86C',
                'neutral' => '#414558', 'base' => '#282A36', 'base_content' => '#F8F8F2',
                'info' => '#8BE9FD', 'success' => '#50FA7B', 'warning' => '#F1FA8C', 'error' => '#FF5555',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'cmyk' => [
            'label'  => 'CMYK',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#45AEEE', 'secondary' => '#E8488A', 'accent' => '#FFF230',
                'neutral' => '#1A1A1A', 'base' => '#FFFFFF', 'base_content' => '#1A1A1A',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'autumn' => [
            'label'  => 'Autumn',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#8C0327', 'secondary' => '#D85251', 'accent' => '#D59B6A',
                'neutral' => '#826A5C', 'base' => '#F1F1F1', 'base_content' => '#2A1D17',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'business' => [
            'label'  => 'Business',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#1C4E80', 'secondary' => '#7C909A', 'accent' => '#EA6947',
                'neutral' => '#23282E', 'base' => '#212121', 'base_content' => '#E0E0E0',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '.25rem', 'field' => '.25rem', 'selector' => '.25rem'],
        ],
        'acid' => [
            'label'  => 'Acid',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#FF00F4', 'secondary' => '#FF7400', 'accent' => '#C8FF00',
                'neutral' => '#1A103D', 'base' => '#FAFAFA', 'base_content' => '#1A103D',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '1rem', 'selector' => '1rem'],
        ],
        'lemonade' => [
            'label'  => 'Lemonade',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#519903', 'secondary' => '#E9E92F', 'accent' => '#F7F9CA',
                'neutral' => '#333333', 'base' => '#FFFFFF', 'base_content' => '#333333',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '1rem', 'selector' => '1rem'],
        ],
        'night' => [
            'label'  => 'Night',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#38BDF8', 'secondary' => '#818CF8', 'accent' => '#F471B5',
                'neutral' => '#1E293B', 'base' => '#0F172A', 'base_content' => '#C8D1E0',
                'info' => '#0CA5E9', 'success' => '#2DD4BF', 'warning' => '#F4BF50', 'error' => '#FB7085',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'coffee' => [
            'label'  => 'Coffee',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#DB924B', 'secondary' => '#263E3F', 'accent' => '#10576C',
                'neutral' => '#120C0A', 'base' => '#20161F', 'base_content' => '#D0BAA8',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'winter' => [
            'label'  => 'Winter',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#047AFF', 'secondary' => '#463AA2', 'accent' => '#C148AC',
                'neutral' => '#021431', 'base' => '#FFFFFF', 'base_content' => '#394E6A',
                'info' => '#2563EB', 'success' => '#16A34A', 'warning' => '#D97706', 'error' => '#DC2626',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'dim' => [
            'label'  => 'Dim',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#9FE88D', 'secondary' => '#FF7D5C', 'accent' => '#00DCDC',
                'neutral' => '#1F2937', 'base' => '#2A303C', 'base_content' => '#A6ADBB',
                'info' => '#3ABFF8', 'success' => '#36D399', 'warning' => '#FBBD23', 'error' => '#F87272',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'nord' => [
            'label'  => 'Nord',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#5E81AC', 'secondary' => '#81A1C1', 'accent' => '#88C0D0',
                'neutral' => '#4C566A', 'base' => '#2E3440', 'base_content' => '#D8DEE9',
                'info' => '#81A1C1', 'success' => '#A3BE8C', 'warning' => '#EBCB8B', 'error' => '#BF616A',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'sunset' => [
            'label'  => 'Sunset',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#FF865B', 'secondary' => '#FD6F9C', 'accent' => '#B259FF',
                'neutral' => '#1E1B26', 'base' => '#1E1B26', 'base_content' => '#F0E7E1',
                'info' => '#89E0EB', 'success' => '#ADD747', 'warning' => '#E4C558', 'error' => '#FF6F6F',
            ],
            'radius' => ['box' => '1rem', 'field' => '1rem', 'selector' => '1rem'],
        ],
        'abyss' => [
            'label'  => 'Abyss',
            'scheme' => 'dark',
            'colors' => [
                'primary' => '#1C6DD0', 'secondary' => '#6B4CE6', 'accent' => '#00C9A7',
                'neutral' => '#0A0E17', 'base' => '#0B1120', 'base_content' => '#C7D2FE',
                'info' => '#38BDF8', 'success' => '#34D399', 'warning' => '#FBBF24', 'error' => '#F87171',
            ],
            'radius' => ['box' => '1rem', 'field' => '.5rem', 'selector' => '1rem'],
        ],
        'silk' => [
            'label'  => 'Silk',
            'scheme' => 'light',
            'colors' => [
                'primary' => '#8B5CF6', 'secondary' => '#EC4899', 'accent' => '#14B8A6',
                'neutral' => '#4B5563', 'base' => '#FAFAF9', 'base_content' => '#1C1917',
                'info' => '#0EA5E9', 'success' => '#22C55E', 'warning' => '#EAB308', 'error' => '#EF4444',
            ],
            'radius' => ['box' => '1.5rem', 'field' => '1rem', 'selector' => '9999px'],
        ],
    ];
}

/**
 * Default design tokens.
 *
 * @return array{preset:string,scheme:string,colors:array<string,string>,radius:array<string,string>}
 */

/**
 * UI design styles (global look) — coordinated with color themes.
 *
 * @return array<string, array{label:string,desc:string}>
 */
function bankai_theme_design_styles(): array {
    return [
        'premium-minimalism' => [
            'label' => 'Premium Minimalism',
            'desc'  => 'ساده، تمیز، فاصله‌دار',
        ],
        'bento' => [
            'label' => 'Bento',
            'desc'  => 'کارت‌های شبکه‌ای نرم',
        ],
        'liquid-glass' => [
            'label' => 'Liquid Glass',
            'desc'  => 'شیشه‌ای و بلور',
        ],
        'dark-luxe' => [
            'label' => 'Dark Luxe',
            'desc'  => 'تاریک لوکس',
        ],
        'editorial-swiss' => [
            'label' => 'Editorial Swiss',
            'desc'  => 'تایپوگرافی شبکه‌ای',
        ],
        'ai-native' => [
            'label' => 'AI-Native',
            'desc'  => 'گرادیان و مدرن AI',
        ],
        'spatial-ui' => [
            'label' => 'Spatial UI',
            'desc'  => 'عمق و لایه فضایی',
        ],
        'neo-brutalism' => [
            'label' => 'Neo-Brutalism',
            'desc'  => 'لبه‌های سخت و سایه ضخیم',
        ],
        'neumorphism' => [
            'label' => 'Neumorphism',
            'desc'  => 'سایه نرم توکار',
        ],
        'claymorphism' => [
            'label' => 'Claymorphism',
            'desc'  => 'نرم سه‌بعدی رنگی',
        ],
        'y2k' => [
            'label' => 'Y2K',
            'desc'  => 'براق و دهه ۲۰۰۰',
        ],
        'kinetic-editorial' => [
            'label' => 'Kinetic Editorial',
            'desc'  => 'حرکتی و تحریری',
        ],
    ];
}

function bankai_theme_default_design(): array {
    $presets = bankai_theme_presets();
    $light   = $presets['light'];
    return [
        'preset' => 'light',
        'style'  => 'premium-minimalism',
        'scheme' => $light['scheme'],
        'colors' => $light['colors'],
        'radius' => $light['radius'],
    ];
}

/**
 * Get saved design tokens.
 *
 * @return array{preset:string,scheme:string,colors:array<string,string>,radius:array<string,string>}
 */
function bankai_theme_get_design(): array {
    $default = bankai_theme_default_design();
    $saved   = null;

    // 1) Theme mod from Customizer Publish (authoritative)
    $mod_json = get_theme_mod('bankai_design_json', '');
    if (is_string($mod_json) && $mod_json !== '') {
        $decoded = json_decode($mod_json, true);
        if (is_array($decoded) && ! empty($decoded['colors'])) {
            $saved = $decoded;
        }
    }

    // 2) Fallback option (AJAX / legacy)
    if ($saved === null) {
        $opt = get_option('bankai_theme_design', null);
        if (is_array($opt) && ! empty($opt['colors'])) {
            $saved = $opt;
        }
    }

    if ($saved === null) {
        return $default;
    }

    return bankai_theme_sanitize_design($saved);
}

/**
 * Sanitize design payload.
 *
 * @param array $data Raw.
 * @return array
 */
function bankai_theme_sanitize_design(array $data): array {
    $default = bankai_theme_default_design();
    $colors  = [];
    foreach ($default['colors'] as $key => $fallback) {
        $val = isset($data['colors'][$key]) ? sanitize_hex_color((string) $data['colors'][$key]) : null;
        $colors[$key] = $val ?: $fallback;
    }
    $radius = [];
    foreach (['box', 'field', 'selector'] as $rk) {
        $raw = isset($data['radius'][$rk]) ? (string) $data['radius'][$rk] : $default['radius'][$rk];
        if (preg_match('/^[\d.]+(rem|px|em|%)$/', $raw) || $raw === '0') {
            $radius[$rk] = $raw;
        } else {
            $radius[$rk] = $default['radius'][$rk];
        }
    }
    $preset = sanitize_key($data['preset'] ?? 'custom');
    $scheme = in_array($data['scheme'] ?? '', ['light', 'dark'], true) ? $data['scheme'] : 'light';
    $styles = array_keys(bankai_theme_design_styles());
    $style  = sanitize_key($data['style'] ?? 'premium-minimalism');
    if (! in_array($style, $styles, true)) {
        $style = 'premium-minimalism';
    }
    // Style-driven radius defaults when radius not customized heavily
    $style_radius = bankai_theme_style_radius($style);
    foreach (['box', 'field', 'selector'] as $rk) {
        if (empty($data['radius'][$rk]) && isset($style_radius[$rk])) {
            $radius[$rk] = $style_radius[$rk];
        }
    }
    return compact('preset', 'style', 'scheme', 'colors', 'radius');
}

/**
 * Default radius hints per design style.
 */
function bankai_theme_style_radius(string $style): array {
    $map = [
        'premium-minimalism' => ['box' => '0.75rem', 'field' => '0.5rem', 'selector' => '0.5rem'],
        'bento'              => ['box' => '1.25rem', 'field' => '0.75rem', 'selector' => '1rem'],
        'liquid-glass'       => ['box' => '1.5rem', 'field' => '1rem', 'selector' => '999px'],
        'dark-luxe'          => ['box' => '0.5rem', 'field' => '0.375rem', 'selector' => '0.375rem'],
        'editorial-swiss'    => ['box' => '0', 'field' => '0', 'selector' => '0'],
        'ai-native'          => ['box' => '1rem', 'field' => '0.75rem', 'selector' => '999px'],
        'spatial-ui'         => ['box' => '1.25rem', 'field' => '0.875rem', 'selector' => '1rem'],
        'neo-brutalism'      => ['box' => '0', 'field' => '0', 'selector' => '0'],
        'neumorphism'        => ['box' => '1.25rem', 'field' => '1rem', 'selector' => '1rem'],
        'claymorphism'       => ['box' => '1.75rem', 'field' => '1.25rem', 'selector' => '1.5rem'],
        'y2k'                => ['box' => '1rem', 'field' => '999px', 'selector' => '999px'],
        'kinetic-editorial'  => ['box' => '0.25rem', 'field' => '0.25rem', 'selector' => '0.25rem'],
    ];
    return $map[$style] ?? $map['premium-minimalism'];
}

/**
 * AJAX save design.
 */
function bankai_ajax_save_design(): void {
    check_ajax_referer('bankai_theme_admin', 'nonce');
    if (! current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }
    $raw = isset($_POST['design']) ? wp_unslash($_POST['design']) : '';
    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
    } else {
        $decoded = is_array($raw) ? $raw : [];
    }
    if (! is_array($decoded)) {
        wp_send_json_error(['message' => 'invalid']);
    }
    $design = bankai_theme_sanitize_design($decoded);
    update_option('bankai_theme_design', $design, false);
    set_theme_mod('bankai_design_json', wp_json_encode($design));

    // Sync classic theme mods for compatibility
    set_theme_mod('color_primary', $design['colors']['primary']);
    set_theme_mod('color_bg', $design['colors']['base']);
    set_theme_mod('color_text', $design['colors']['base_content']);
    set_theme_mod('color_scheme', $design['scheme']);
    $box = $design['radius']['box'];
    if (str_ends_with($box, 'rem')) {
        set_theme_mod('border_radius', (int) round((float) $box * 16));
    }

    wp_send_json_success(['design' => $design, 'message' => __('تم ذخیره شد.', 'bankai-theme')]);
}
add_action('wp_ajax_bankai_theme_save_design', 'bankai_ajax_save_design');

/**
 * Front-end CSS variables from design tokens.
 */
function bankai_theme_design_css(): string {
    $d = bankai_theme_get_design();
    $c = $d['colors'];
    $r = $d['radius'];
    $style = $d['style'] ?? 'premium-minimalism';
    $root = sprintf(
        ':root{--bankai-primary:%1$s;--bankai-secondary:%2$s;--bankai-accent:%3$s;--bankai-neutral:%4$s;--bankai-bg:%5$s;--bankai-text:%6$s;--bankai-info:%7$s;--bankai-success:%8$s;--bankai-warning:%9$s;--bankai-error:%10$s;--bankai-radius:%11$s;--bankai-radius-field:%12$s;--bankai-radius-selector:%13$s;--p:%1$s;--s:%2$s;--a:%3$s;--n:%4$s;--b1:%5$s;--bc:%6$s;}',
        $c['primary'],
        $c['secondary'],
        $c['accent'],
        $c['neutral'],
        $c['base'],
        $c['base_content'],
        $c['info'],
        $c['success'],
        $c['warning'],
        $c['error'],
        $r['box'],
        $r['field'],
        $r['selector']
    );
    // Emit every style block so live body-class switching works in Customizer + front
    $all_styles = array_keys(bankai_theme_design_styles());
    foreach ($all_styles as $sid) {
        $root .= bankai_theme_style_css($sid, $c, $r);
    }
    // Hide page titles globally as requested
    $root .= '.bankai-page__title,.bankai-page__header{display:none!important;}';
    return $root;
}

/**
 * Component CSS for a design style — applies to WP elements site-wide.
 */
function bankai_theme_style_css(string $style, array $c, array $r): string {
    $p = $c['primary'] ?? '#5C6B7A';
    $sel = 'body.bankai-style-' . sanitize_html_class($style);
    // Shared targets for every style
    $targets = $sel . ' .bankai-card,' . $sel . ' .widget,' . $sel . ' .wp-block-group,'
        . $sel . ' .bankai-post-card,' . $sel . ' .bankai-author-box,' . $sel . ' .bankai-sidebar__block,'
        . $sel . ' .comments-area,' . $sel . ' .comment-respond,' . $sel . ' .bankai-footer,'
        . $sel . ' .bankai-footer__bar,' . $sel . ' .bankai-mobile-bar,' . $sel . ' .bankai-mobile-nav__panel,'
        . $sel . ' .bankai-header,' . $sel . ' .bankai-header--builder,' . $sel . ' .bankai-header-row,'
        . $sel . ' .bankai-bottom-nav,' . $sel . ' .bankai-scroll-top,' . $sel . ' .bankai-fab-contact';
    $btns = $sel . ' .bankai-btn,' . $sel . ' .wp-element-button,' . $sel . ' .wp-block-button__link,'
        . $sel . ' button[type=submit],' . $sel . ' input[type=submit],' . $sel . ' .button,'
        . $sel . ' .bankai-scroll-top,' . $sel . ' .bankai-fab-contact';
    $inputs = $sel . ' input[type=text],' . $sel . ' input[type=email],' . $sel . ' input[type=search],'
        . $sel . ' input[type=password],' . $sel . ' input[type=url],' . $sel . ' input[type=tel],'
        . $sel . ' textarea,' . $sel . ' select,' . $sel . ' .bankai-search-field,'
        . $sel . ' .wp-block-search__input,' . $sel . ' .bankai-mobile-search__input';

    $base = $sel . '{--bankai-radius:' . esc_attr($r['box'] ?? '0.75rem') . ';--bankai-radius-field:' . esc_attr($r['field'] ?? '0.5rem') . ';--bankai-radius-selector:' . esc_attr($r['selector'] ?? '0.5rem') . ';}'
        . $targets . '{border-radius:var(--bankai-radius);}'
        . $inputs . '{border-radius:var(--bankai-radius-field);}'
        . $btns . '{border-radius:var(--bankai-radius-selector);}';

    switch ($style) {
        case 'bento':
            return $base
                . $targets . '{background:var(--bankai-bg);border:1px solid color-mix(in srgb,var(--bankai-text) 8%,transparent);box-shadow:0 1px 2px rgba(0,0,0,.04);}'
                . $btns . '{font-weight:700;border:1px solid color-mix(in srgb,var(--bankai-text) 10%,transparent);}';
        case 'liquid-glass':
            return $base
                . $sel . '{background:linear-gradient(160deg,color-mix(in srgb,var(--bankai-primary) 12%,var(--bankai-bg)),var(--bankai-bg));}'
                . $targets . '{background:color-mix(in srgb,var(--bankai-bg) 70%,transparent)!important;backdrop-filter:blur(16px) saturate(160%);-webkit-backdrop-filter:blur(16px) saturate(160%);border:1px solid color-mix(in srgb,#fff 45%,transparent);box-shadow:0 8px 32px rgba(0,0,0,.08);}'
                . $btns . '{backdrop-filter:blur(8px);border:1px solid color-mix(in srgb,#fff 40%,transparent);}'
                . $inputs . '{background:color-mix(in srgb,var(--bankai-bg) 60%,transparent);border:1px solid color-mix(in srgb,#fff 35%,transparent);}';
        case 'dark-luxe':
            return $base
                . $sel . '{background:#0c0c0e;color:#f4f0e8;}'
                . $targets . '{background:#141418;border:1px solid #2a2a32;box-shadow:0 12px 40px rgba(0,0,0,.45);color:#f4f0e8;}'
                . $btns . '{background:linear-gradient(135deg,var(--bankai-primary),color-mix(in srgb,var(--bankai-primary) 60%,#c9a227));color:#fff;border:none;letter-spacing:.04em;}'
                . $inputs . '{background:#1a1a22;border:1px solid #333;color:#f4f0e8;}';
        case 'editorial-swiss':
            return $base
                . $targets . ',' . $btns . ',' . $inputs . '{border-radius:0!important;border:1px solid var(--bankai-text);box-shadow:none;}'
                . $sel . ' h1,' . $sel . ' h2{text-transform:uppercase;letter-spacing:.06em;font-weight:800;}'
                . $sel . ' .bankai-header,' . $sel . ' .bankai-footer{border-top:2px solid var(--bankai-text);border-bottom:2px solid var(--bankai-text);}';
        case 'ai-native':
            return $base
                . $sel . '{background:radial-gradient(1200px 600px at 10% -10%,color-mix(in srgb,var(--bankai-primary) 22%,transparent),var(--bankai-bg));}'
                . $targets . '{border:1px solid color-mix(in srgb,var(--bankai-primary) 25%,transparent);background:color-mix(in srgb,var(--bankai-bg) 92%,var(--bankai-primary));box-shadow:0 10px 40px color-mix(in srgb,var(--bankai-primary) 12%,transparent);}'
                . $btns . '{background:linear-gradient(120deg,var(--bankai-primary),var(--bankai-accent),var(--bankai-secondary));color:#fff;border:none;}'
                . $inputs . '{border:1px solid color-mix(in srgb,var(--bankai-primary) 30%,transparent);}';
        case 'spatial-ui':
            return $base
                . $targets . '{box-shadow:0 18px 50px rgba(0,0,0,.12),0 2px 6px rgba(0,0,0,.06);}'
                . $btns . '{box-shadow:0 8px 20px color-mix(in srgb,var(--bankai-primary) 35%,transparent);}';
        case 'neo-brutalism':
            return $base
                . $targets . ',' . $btns . ',' . $inputs . '{border:2px solid #111!important;border-radius:0!important;box-shadow:4px 4px 0 #111!important;}'
                . $btns . ':hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #111!important;}'
                . $sel . ' h1,' . $sel . ' h2{font-weight:900;}';
        case 'neumorphism':
            return $base
                . $sel . '{background:color-mix(in srgb,var(--bankai-bg) 92%,var(--bankai-neutral));}'
                . $targets . ',' . $inputs . '{background:color-mix(in srgb,var(--bankai-bg) 92%,var(--bankai-neutral));border:none;box-shadow:8px 8px 16px color-mix(in srgb,#000 12%,transparent),-8px -8px 16px color-mix(in srgb,#fff 70%,transparent);}'
                . $btns . '{border:none;box-shadow:6px 6px 12px color-mix(in srgb,#000 14%,transparent),-6px -6px 12px color-mix(in srgb,#fff 65%,transparent);}';
        case 'claymorphism':
            return $base
                . $targets . '{background:color-mix(in srgb,var(--bankai-bg) 85%,var(--bankai-primary));border:2px solid color-mix(in srgb,#fff 55%,transparent);box-shadow:0 12px 24px color-mix(in srgb,var(--bankai-primary) 22%,transparent),inset 0 -4px 12px color-mix(in srgb,#000 8%,transparent),inset 0 4px 12px color-mix(in srgb,#fff 50%,transparent);}'
                . $btns . '{border:2px solid color-mix(in srgb,#fff 40%,transparent);box-shadow:0 8px 16px color-mix(in srgb,var(--bankai-primary) 30%,transparent),inset 0 2px 0 color-mix(in srgb,#fff 40%,transparent);}';
        case 'y2k':
            return $base
                . $sel . '{background:linear-gradient(180deg,#e8f0ff,var(--bankai-bg));}'
                . $targets . '{background:linear-gradient(145deg,#fff,color-mix(in srgb,var(--bankai-primary) 12%,#fff));border:2px solid color-mix(in srgb,var(--bankai-primary) 40%,#fff);box-shadow:0 0 0 2px #fff,0 8px 0 color-mix(in srgb,var(--bankai-primary) 35%,#888);}'
                . $btns . '{background:linear-gradient(180deg,#fff,color-mix(in srgb,var(--bankai-primary) 30%,#ccc));border:1px solid #fff;box-shadow:inset 0 1px 0 #fff,0 3px 0 #888;color:var(--bankai-text);}';
        case 'kinetic-editorial':
            return $base
                . $sel . ' .bankai-card{border-inline-start:4px solid var(--bankai-primary);}'
                . $btns . '{position:relative;overflow:hidden;}'
                . $btns . '::after{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent 30%,color-mix(in srgb,#fff 35%,transparent) 50%,transparent 70%);transform:translateX(-120%);transition:transform .5s;}'
                . $btns . ':hover::after{transform:translateX(120%);}'
                . $sel . ' h1,' . $sel . ' h2{font-weight:800;line-height:1.1;}';
        case 'premium-minimalism':
        default:
            return $base
                . $targets . '{border:1px solid color-mix(in srgb,var(--bankai-text) 8%,transparent);box-shadow:0 1px 2px rgba(0,0,0,.03);}'
                . $btns . '{font-weight:600;}';
    }
}



/**
 * Built-in font catalog (Iranian / Arabic friendly).
 *
 * @return array<string, array{label:string,stack:string,google?:string}>
 */
function bankai_theme_font_catalog(): array {
    return [
        'vazirmatn' => [
            'label'  => 'Vazirmatn',
            'stack'  => 'Vazirmatn, Tahoma, sans-serif',
            'google' => 'Vazirmatn:wght@300;400;500;600;700',
        ],
        'noto_arabic' => [
            'label'  => 'Noto Sans Arabic',
            'stack'  => '"Noto Sans Arabic", Tahoma, sans-serif',
            'google' => 'Noto+Sans+Arabic:wght@300;400;500;600;700',
        ],
        'ibm_arabic' => [
            'label'  => 'IBM Plex Arabic',
            'stack'  => '"IBM Plex Sans Arabic", Tahoma, sans-serif',
            'google' => 'IBM+Plex+Sans+Arabic:wght@300;400;500;600;700',
        ],
        'cairo' => [
            'label'  => 'Cairo',
            'stack'  => 'Cairo, Tahoma, sans-serif',
            'google' => 'Cairo:wght@300;400;500;600;700',
        ],
        'tajawal' => [
            'label'  => 'Tajawal',
            'stack'  => 'Tajawal, Tahoma, sans-serif',
            'google' => 'Tajawal:wght@300;400;500;700',
        ],
        'tahoma' => [
            'label' => 'Tahoma',
            'stack' => 'Tahoma, Arial, sans-serif',
        ],
    ];
}

/**
 * Custom uploaded fonts (theme_mod).
 *
 * @return array<int, array{id:string,label:string,stack:string,url:string,format:string}>
 */
function bankai_theme_get_custom_fonts(): array {
    $fonts = get_theme_mod('bankai_custom_fonts', []);
    return is_array($fonts) ? $fonts : [];
}

/**
 * @font-face CSS for custom fonts.
 */
function bankai_theme_custom_fonts_css(): string {
    $css = '';
    foreach (bankai_theme_get_custom_fonts() as $font) {
        if (empty($font['url']) || empty($font['label'])) {
            continue;
        }
        $family = sanitize_text_field($font['label']);
        $url    = esc_url_raw($font['url']);
        $format = sanitize_key($font['format'] ?? 'woff2');
        $fmt_map = [
            'woff2' => 'woff2',
            'woff'  => 'woff',
            'ttf'   => 'truetype',
            'otf'   => 'opentype',
        ];
        $fmt = $fmt_map[$format] ?? 'woff2';
        $css .= "@font-face{font-family:'{$family}';src:url('{$url}') format('{$fmt}');font-display:swap;font-weight:100 900;font-style:normal;}";
    }
    return $css;
}
