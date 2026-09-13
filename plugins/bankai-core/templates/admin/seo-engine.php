<?php
/**
 * Bankai Core Admin - View 3: SEO Engine View
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$seo_modules = array(
    array(
        'id'          => 'ai_search_visibility',
        'title'       => 'AI Search Visibility',
        'badge'       => 'NEW',
        'badge_color' => '#6366F1',
        'icon'        => '🤖',
        'description' => 'Monitor brand mentions, citations, and sentiment across LLMs (ChatGPT, Claude, Perplexity).',
        'enabled'     => true,
    ),
    array(
        'id'          => 'ai_meta_assistant',
        'title'       => 'AI Content & Meta Assistant',
        'badge'       => 'AI POWERED',
        'badge_color' => '#38BDF8',
        'icon'        => '📝',
        'description' => 'Country-targeted (80+ locations) keyword and question analyzer with auto meta description generation.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'ai_link_genius',
        'title'       => 'AI Link Genius',
        'badge'       => 'AUTONOMOUS',
        'badge_color' => '#10B981',
        'icon'        => '🔗',
        'description' => 'Automated internal and external link anchor builder with keyword density protection.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'instant_indexing',
        'title'       => 'Instant Indexing Engine',
        'badge'       => 'API LIVE',
        'badge_color' => '#10B981',
        'icon'        => '⚡',
        'description' => 'Bing, Yandex, and Google Indexing API instant submission logs with latency telemetry.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'xml_sitemaps',
        'title'       => 'XML Sitemaps Hub',
        'badge'       => 'PRO',
        'badge_color' => '#F59E0B',
        'icon'        => '🗺️',
        'description' => 'General, News, Video, and Image sitemaps with multi-language hreflang tag support.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'schema_builder',
        'title'       => 'Schema.org Builder',
        'badge'       => '18+ TYPES',
        'badge_color' => '#6366F1',
        'icon'        => '🧱',
        'description' => 'Visual selector for 18+ JSON-LD schema types (Article, Product, FAQ, LocalBusiness, Podcast).',
        'enabled'     => true,
    ),
    array(
        'id'          => 'monitor_404',
        'title'       => '404 Monitor & Redirects',
        'badge'       => 'HEURISTIC',
        'badge_color' => '#EF4444',
        'icon'        => '⚠️',
        'description' => 'Real-time 404 error hit logging with auto-301 recommendation engine and regex rule engine.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'image_seo',
        'title'       => 'Image SEO Optimizer',
        'badge'       => 'AUTOMATED',
        'badge_color' => '#38BDF8',
        'icon'        => '🖼️',
        'description' => 'Auto ALT tag generation, dynamic image title attribute injection, and WebP fallback attributes.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'acf_integration',
        'title'       => 'ACF Meta Integration',
        'badge'       => 'RANKMATH-GRADE',
        'badge_color' => '#10B981',
        'icon'        => '📦',
        'description' => 'Deep integration with Advanced Custom Fields to analyze dynamic content blocks for SEO density.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'woocommerce_seo',
        'title'       => 'WooCommerce SEO Suite',
        'badge'       => 'ECOMMERCE',
        'badge_color' => '#F59E0B',
        'icon'        => '🛒',
        'description' => 'Product GTIN/MPN schema fields, brand taxonomies, price currency metadata, and canonical rules.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'local_seo',
        'title'       => 'Local SEO Knowledge Graph',
        'badge'       => 'KNOWLEDGE GRAPH',
        'badge_color' => '#6366F1',
        'icon'        => '📍',
        'description' => 'Geo-coordinates, opening hours JSON-LD, business organization graphs, and Google Maps embed.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'llms_txt_builder',
        'title'       => 'llms.txt Manifest Builder',
        'badge'       => 'AI SPEC v1.2',
        'badge_color' => '#10B981',
        'icon'        => '📄',
        'description' => 'Generates standardized /llms.txt and /llms-full.txt files for AI agents and web crawlers.',
        'enabled'     => true,
    ),
);
?>

<!-- View 3 Header & Audit Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    🔍 <?php esc_html_e( 'Autonomous SEO & Schema Architecture', 'bankai-core' ); ?>
                </h2>
                <span style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #10B981; font-size: 11px; padding: 2px 10px; border-radius: 12px; font-weight: 700;">
                    <?php esc_html_e( '98/100 - Optimal', 'bankai-core' ); ?>
                </span>
            </div>
            <p style="font-size: 12px; color: #94A3B8; margin: 0;">
                <?php esc_html_e( 'Next-generation AI keyword optimization, 18+ JSON-LD schemas, instant indexing & LLM search visibility.', 'bankai-core' ); ?>
            </p>
        </div>

        <div style="display: flex; gap: 12px;">
            <button @click="runSeoAudit()"
                    style="background-color: #1E2D4A; border: 1px solid #334155; color: #F8FAFC; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                ⚡ <?php esc_html_e( 'Run 28-Point Audit', 'bankai-core' ); ?>
            </button>

            <button @click="openSeoWizard()"
                    style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
                🪄 <?php esc_html_e( 'SEO Setup Wizard', 'bankai-core' ); ?>
            </button>
        </div>
    </div>

    <!-- Modular Feature Grid (12 Cards) -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px;">
        <?php foreach ( $seo_modules as $mod ) : ?>
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; transition: border-color 0.2s;"
                 onmouseover="this.style.borderColor='#38BDF8';"
                 onmouseout="this.style.borderColor='#1E2D4A';">
                
                <div>
                    <!-- Top title + badge + switch -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 20px;"><?php echo esc_html( $mod['icon'] ); ?></span>
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #F8FAFC; display: flex; align-items: center; gap: 6px;">
                                    <?php echo esc_html( $mod['title'] ); ?>
                                </div>
                                <span style="background-color: <?php echo esc_attr( $mod['badge_color'] ); ?>20; color: <?php echo esc_attr( $mod['badge_color'] ); ?>; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">
                                    <?php echo esc_html( $mod['badge'] ); ?>
                                </span>
                            </div>
                        </div>

                        <label class="bankai-switch">
                            <input type="checkbox"
                                   x-model="seoState.<?php echo esc_attr( $mod['id'] ); ?>"
                                   @change="toggleSeoModule('<?php echo esc_js( $mod['id'] ); ?>')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>

                    <!-- Description -->
                    <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin: 0 0 16px 0;">
                        <?php echo esc_html( $mod['description'] ); ?>
                    </p>
                </div>

                <!-- Action / Settings Drawer Trigger -->
                <div style="border-top: 1px solid #1E2D4A; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; color: #64748B;"
                          x-text="seoState.<?php echo esc_attr( $mod['id'] ); ?> ? 'Active' : 'Disabled'">
                        Active
                    </span>

                    <button @click="openSeoDrawer('<?php echo esc_js( $mod['id'] ); ?>', '<?php echo esc_js( $mod['title'] ); ?>')"
                            style="background-color: #1E2D4A; border: none; color: #38BDF8; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; cursor: pointer;">
                        ⚙️ <?php esc_html_e( 'Configure', 'bankai-core' ); ?> &rarr;
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- SEO Module Settings Modal Drawer (Alpine.js x-show) -->
    <div x-show="seoDrawer.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="seoDrawer.show = false"
             style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 16px; width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #1E2D4A; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    ⚙️ <span x-text="seoDrawer.title + ' Settings'"></span>
                </h3>
                <button @click="seoDrawer.show = false" style="background: none; border: none; color: #64748B; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                        <?php esc_html_e( 'Primary Targeting Country / Region', 'bankai-core' ); ?>
                    </label>
                    <select style="width: 100%; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 10px; border-radius: 8px; font-size: 13px;">
                        <option value="global">Global (Worldwide LLM & Search Indexing)</option>
                        <option value="ir">Iran (IR / Persian Vazirmatn Standard)</option>
                        <option value="us">United States (US / English)</option>
                        <option value="eu">European Union (EU)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                        <?php esc_html_e( 'Auto-Optimization Intensity & Safety Level', 'bankai-core' ); ?>
                    </label>
                    <div style="display: flex; gap: 12px;">
                        <button style="flex: 1; background-color: #0B0F19; border: 1px solid #10B981; color: #10B981; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                            Safe Mode (Recommended)
                        </button>
                        <button style="flex: 1; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #94A3B8; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                            Aggressive Ranker
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="seoDrawer.show = false" style="background-color: #1E2D4A; border: none; color: #F8FAFC; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button @click="saveSeoDrawerSettings()" style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(16,185,129,0.3);">
                    Save Module Configuration
                </button>
            </div>
        </div>
    </div>
</div>
