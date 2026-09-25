<?php
/**
 * Bankai Editor SEO — meta box, full panel in Gutenberg sidebar, list column, score toolbar.
 */
defined('ABSPATH') || exit;

class Bankai_Editor_SEO
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        if (!$this->is_seo_enabled()) {
            return;
        }

        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_assets']);

        add_filter('manage_posts_columns', [$this, 'add_score_column']);
        add_filter('manage_pages_columns', [$this, 'add_score_column']);
        add_action('manage_posts_custom_column', [$this, 'render_score_column'], 10, 2);
        add_action('manage_pages_custom_column', [$this, 'render_score_column'], 10, 2);
        add_filter('manage_edit-post_sortable_columns', [$this, 'sortable_score']);
        add_filter('manage_edit-page_sortable_columns', [$this, 'sortable_score']);
    }

    private function is_seo_enabled(): bool
    {
        if (function_exists('bankai_is_module_active')) {
            return bankai_is_module_active('seo_engine');
        }
        return true;
    }

    public function register_meta_box(): void
    {
        $types = get_post_types(['public' => true], 'names');
        foreach ($types as $type) {
            if ($type === 'attachment') {
                continue;
            }
            add_meta_box(
                'bankai-seo-sidebar',
                __('سئوی بنکای', 'bankai-core'),
                [$this, 'render_meta_box'],
                $type,
                'side',
                'high'
            );
        }
    }

    public function render_meta_box($post): void
    {
        $this->render_panel((int) $post->ID);
    }

    /**
     * Capture full panel HTML (used by meta box and Gutenberg inject).
     */
    public function get_panel_html(int $post_id): string
    {
        ob_start();
        $this->render_panel($post_id);
        return (string) ob_get_clean();
    }

    private function render_panel(int $post_id): void
    {
        if (function_exists('bankai_render_view')) {
            bankai_render_view('tab-seo-engine.php', ['post_id' => $post_id]);
        }
    }

    public function enqueue_assets(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php', 'edit.php'], true)) {
            return;
        }

        wp_enqueue_style(
            'bankai-material-symbols',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'bankai-vazirmatn',
            'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap',
            [],
            null
        );

        $ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0';

        wp_enqueue_style(
            'bankai-editor-seo',
            bankai_asset_url('css/editor-seo.css'),
            ['bankai-material-symbols', 'bankai-vazirmatn'],
            $ver . '.' . (string) @filemtime(BANKAI_CORE_DIR . 'assets/css/editor-seo.css')
        );

        if (in_array($hook, ['post.php', 'post-new.php'], true)) {
            // Register Alpine.data BEFORE Alpine auto-starts: load component first, Alpine second.
            $js_ver = $ver . '.' . (string) @filemtime(BANKAI_CORE_DIR . 'assets/js/editor-seo.js');
            wp_enqueue_script(
                'bankai-editor-seo',
                bankai_asset_url('js/editor-seo.js'),
                ['wp-api-fetch'],
                $js_ver,
                true
            );

            wp_enqueue_script(
                'alpinejs',
                bankai_asset_url('js/alpine.min.js'),
                ['bankai-editor-seo'],
                '3.14.1',
                true
            );

            $post_id = 0;
            if (isset($_GET['post'])) {
                $post_id = absint($_GET['post']);
            } elseif (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post) {
                $post_id = (int) $GLOBALS['post']->ID;
            }

            $this->localize_editor_seo((int) $post_id, $ver);

            wp_add_inline_script(
                'bankai-editor-seo',
                'if(window.wp&&wp.apiFetch){wp.apiFetch.use(wp.apiFetch.createNonceMiddleware(bankaiEditorSeo.nonce));}',
                'before'
            );
        }

        if ($hook === 'edit.php') {
            wp_add_inline_style('bankai-editor-seo', $this->list_column_css());
        }
    }

    public function enqueue_block_assets(): void
    {
        $ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0';

        // Same CSS + Alpine + core JS for Gutenberg context.
        wp_enqueue_style(
            'bankai-material-symbols',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'bankai-vazirmatn',
            'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'bankai-editor-seo',
            bankai_asset_url('css/editor-seo.css'),
            ['bankai-material-symbols', 'bankai-vazirmatn'],
            $ver
        );

        wp_enqueue_script(
            'alpinejs',
            'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
            [],
            '3.14.1',
            ['strategy' => 'defer']
        );

        wp_enqueue_script(
            'bankai-editor-seo',
            bankai_asset_url('js/editor-seo.js'),
            ['wp-api-fetch'],
            $ver,
            true
        );

        $post_id = 0;
        if (isset($_GET['post'])) {
            $post_id = absint($_GET['post']);
        }

        $score = $post_id ? (int) get_post_meta($post_id, '_bankai_seo_score', true) : 0;
        $color = function_exists('bankai_seo_score_color')
            ? bankai_seo_score_color($score)
            : '#B8BCC2';

        // Full panel HTML for injection into PluginSidebar.
        $panel_html = '';
        if ($post_id > 0) {
            $panel_html = $this->get_panel_html($post_id);
        } else {
            // New post — still render shell with postId 0; JS will wait.
            $panel_html = $this->get_panel_html(0);
        }

        $this->localize_editor_seo((int) $post_id, $ver);

        wp_add_inline_script(
            'bankai-editor-seo',
            'if(window.wp&&wp.apiFetch){wp.apiFetch.use(wp.apiFetch.createNonceMiddleware(bankaiEditorSeo.nonce));}',
            'before'
        );

        wp_enqueue_script(
            'bankai-editor-seo-gutenberg',
            bankai_asset_url('js/editor-seo-gutenberg.js'),
            [
                'wp-plugins',
                'wp-edit-post',
                'wp-element',
                'wp-components',
                'wp-data',
                'wp-i18n',
                'bankai-editor-seo',
            ],
            $ver,
            true
        );

        wp_localize_script('bankai-editor-seo-gutenberg', 'bankaiGutenbergSeo', [
            'postId'    => $post_id,
            'score'     => $score,
            'color'     => $color,
            'label'     => __('سئو', 'bankai-core'),
            'panelHtml' => $panel_html,
        ]);
    }

    public function add_score_column(array $columns): array
    {
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new['bankai_seo_score'] = __('سئو', 'bankai-core');
            }
        }
        return $new;
    }

    public function render_score_column(string $column, int $post_id): void
    {
        if ($column !== 'bankai_seo_score') {
            return;
        }

        $score = (int) get_post_meta($post_id, '_bankai_seo_score', true);
        $color = function_exists('bankai_seo_score_color')
            ? bankai_seo_score_color($score)
            : '#B8BCC2';

        $r      = 14;
        $c      = 2 * M_PI * $r;
        $offset = $c - ($c * min(100, max(0, $score)) / 100);

        echo '<div class="bankai-list-score" title="' . esc_attr($score) . '%">';
        echo '<svg width="36" height="36" viewBox="0 0 36 36">';
        echo '<circle cx="18" cy="18" r="' . $r . '" fill="none" stroke="#eaeef2" stroke-width="3"/>';
        echo '<circle cx="18" cy="18" r="' . $r . '" fill="none" stroke="' . esc_attr($color) . '" stroke-width="3" ';
        echo 'stroke-dasharray="' . esc_attr((string) $c) . '" stroke-dashoffset="' . esc_attr((string) $offset) . '" ';
        echo 'stroke-linecap="round" transform="rotate(-90 18 18)"/>';
        echo '</svg>';
        echo '<span style="color:' . esc_attr($color) . '">' . esc_html((string) $score) . '</span>';
        echo '</div>';
    }

    public function sortable_score(array $columns): array
    {
        $columns['bankai_seo_score'] = 'bankai_seo_score';
        return $columns;
    }

    private function list_column_css(): string
    {
        return '
        .column-bankai_seo_score { width: 52px; text-align: center; }
        .bankai-list-score {
            position: relative; width: 36px; height: 36px; margin: 0 auto;
            display: flex; align-items: center; justify-content: center;
        }
        .bankai-list-score svg { position: absolute; inset: 0; }
        .bankai-list-score span {
            position: relative; z-index: 1;
            font-size: 10px; font-weight: 700;
            font-family: ui-monospace, monospace;
        }
        ';
    }

    private function localize_editor_seo(int $post_id, string $ver): void
    {
        $ai_providers = [];
        $ai_default = 'gemini';
        if (class_exists('Bankai_AI_Studio')) {
            $studio = Bankai_AI_Studio::instance();
            $ai_default = $studio->get_default_provider();
            foreach ($studio->provider_status_list() as $p) {
                if (!empty($p['has_key'])) {
                    $ai_providers[] = [
                        'id'   => $p['id'],
                        'name' => $p['name'],
                    ];
                }
            }
        }

        $fixed_kw = [];
        if (class_exists('Bankai_SEO_Engine') && method_exists('Bankai_SEO_Engine', 'instance')) {
            try {
                $fixed_kw = Bankai_SEO_Engine::instance()->get_fixed_keywords_list();
            } catch (Throwable $e) {
                $fixed_kw = [];
            }
        } elseif (function_exists('bankai_get_option')) {
            $raw = (string) bankai_get_option('seo_fixed_keywords', '');
            $fixed_kw = array_values(array_filter(array_map('trim', preg_split('/[,،\n]+/u', $raw) ?: [])));
        }

        wp_localize_script('bankai-editor-seo', 'bankaiEditorSeo', [
            'postId'            => $post_id,
            'isRtl'             => is_rtl(),
            'version'           => $ver,
            'restUrl'           => trailingslashit(esc_url_raw(rest_url('bankai/v1/'))),
            'nonce'             => wp_create_nonce('wp_rest'),
            'adminNonce'        => wp_create_nonce('bankai_admin_nonce'),
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'locale'            => get_user_locale(),
            'defaultAiProvider' => $ai_default,
            'aiProviders'       => $ai_providers,
            'fixedKeywords'     => $fixed_kw,
            'seoFixedKeywords'  => $fixed_kw,
            'i18n'              => [
                'button'     => __('سئو', 'bankai-core'),
                'panelTitle' => __('سئوی بنکای', 'bankai-core'),
            ],
        ]);
    }
}
