<?php
/** Drop into class-editor-seo enqueue_block_editor_assets */

        $seo_ai = BANKAI_CORE_DIR . 'assets/js/bankai-seo-ai.js';
        if (file_exists($seo_ai)) {
            wp_enqueue_script(
                'bankai-seo-ai',
                bankai_asset_url('js/bankai-seo-ai.js'),
                ['jquery'],
                BANKAI_CORE_VERSION,
                true
            );
            $fixed_kw = [];
            if (class_exists('Bankai_SEO_Engine') && method_exists(Bankai_SEO_Engine::class, 'instance')) {
                try {
                    $fixed_kw = Bankai_SEO_Engine::instance()->get_fixed_keywords_list();
                } catch (Throwable $e) {
                    $fixed_kw = [];
                }
            }
            wp_localize_script('bankai-seo-ai', 'bankaiEditorSeo', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'adminNonce' => wp_create_nonce('bankai_admin_nonce'),
                'locale' => get_user_locale(),
                'isRtl' => is_rtl(),
                'defaultAiProvider' => class_exists('Bankai_AI_Studio') ? Bankai_AI_Studio::instance()->get_default_provider() : 'gemini',
                'fixedKeywords' => $fixed_kw,
                'seoFixedKeywords' => $fixed_kw,
            ]);
        }
