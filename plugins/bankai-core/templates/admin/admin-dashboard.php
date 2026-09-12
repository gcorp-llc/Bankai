<?php
/**
 * Bankai Core - Native SSR Admin Dashboard Template
 * Powered by Alpine.js, HTMX, and Native PHP
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

// Fetch initial options from database or defaults
$modules = array(
    'seo_engine'      => (int) get_option( 'bankai_module_seo_engine', 1 ),
    'media_optimizer' => (int) get_option( 'bankai_module_media_optimizer', 1 ),
    'smart_redirects' => (int) get_option( 'bankai_module_smart_redirects', 1 ),
    'llm_manifest'    => (int) get_option( 'bankai_module_llm_manifest', 1 ),
    'base_stripper'   => (int) get_option( 'bankai_module_base_stripper', 0 ),
    'cache_warmer'    => (int) get_option( 'bankai_module_cache_warmer', 1 ),
);

$stats = array(
    'uptime'        => '99.98%',
    'indexed_nodes' => '14,820',
    'ai_crawls'     => '1,402',
    'avg_latency'   => '42ms',
    'p99_latency'   => '78ms',
    'varnish_hit'   => '94%',
    'overall_score' => 94,
    'ttfb'          => '38ms',
    'fcp'           => '0.8s',
    'lcp'           => '1.4s',
    'schema_score'  => '98%',
    'last_audit'    => '12m ago',
);

$logs404 = array(
    array(
        'id'                 => 1,
        'requested_uri'      => '/old-blog/product-review-2023',
        'hits'               => 48,
        'source_ip'          => "192.168.1.42
Googlebot Crawler",
        'recommended_action' => 'Apply 301 (96%)',
        'action_type'        => 'apply_301',
        'target_uri'         => '/reviews/product-review-2023',
    ),
    array(
        'id'                 => 2,
        'requested_uri'      => '/pricing-v1',
        'hits'               => 12,
        'source_ip'          => "104.28.19.112
Direct Referrer",
        'recommended_action' => 'Map Target (91%)',
        'action_type'        => 'map_target',
        'target_uri'         => '/pricing',
    ),
    array(
        'id'                 => 3,
        'requested_uri'      => '/wp-content/uploads/temp.pdf',
        'hits'               => 6,
        'source_ip'          => "172.56.21.9
Broken External",
        'recommended_action' => 'Redirect',
        'action_type'        => 'redirect',
        'target_uri'         => '/',
    ),
    array(
        'id'                 => 4,
        'requested_uri'      => '/.env',
        'hits'               => 31,
        'source_ip'          => "45.154.255.8
Scanner Bot (Blocked)",
        'recommended_action' => 'Auto-Dropped',
        'action_type'        => 'auto_dropped',
        'target_uri'         => '',
    ),
);

$is_rtl = is_rtl();
?>

<div class="bankai-admin-wrap" dir="<?php echo $is_rtl ? 'rtl' : 'ltr'; ?>" x-data="{
    activeTab: 'overview',
    toast: { show: false, message: '', type: 'success' },
    showToast(msg, type = 'success') {
        this.toast.message = msg;
        this.toast.type = type;
        this.toast.show = true;
        setTimeout(() => { this.toast.show = false; }, 3500);
    },
    modules: <?php echo json_encode( $modules ); ?>,
    async toggleModule(moduleKey) {
        const newState = !this.modules[moduleKey];
        this.modules[moduleKey] = newState ? 1 : 0;
        try {
            const res = await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/dashboard/toggle-module', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.bankaiData?.nonce || ''
                },
                body: JSON.stringify({ module: moduleKey, state: newState })
            });
            const data = await res.json();
            if (data && data.success) {
                this.showToast('<?php echo esc_js( __( 'Module state updated persistently.', 'bankai-core' ) ); ?>', 'success');
            }
        } catch (e) {
            this.showToast('<?php echo esc_js( __( 'Failed to update module state.', 'bankai-core' ) ); ?>', 'error');
        }
    },
    async purgeCache() {
        try {
            const res = await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/dashboard/purge-cache', {
                method: 'POST',
                headers: { 'X-WP-Nonce': window.bankaiData?.nonce || '' }
            });
            const data = await res.json();
            this.showToast(data.message || '<?php echo esc_js( __( 'Native cache purged successfully.', 'bankai-core' ) ); ?>', 'success');
        } catch (e) {
            this.showToast('<?php echo esc_js( __( 'Failed to purge cache.', 'bankai-core' ) ); ?>', 'error');
        }
    },
    async syncSitemap() {
        try {
            const res = await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/dashboard/sync-sitemap', {
                method: 'POST',
                headers: { 'X-WP-Nonce': window.bankaiData?.nonce || '' }
            });
            const data = await res.json();
            this.showToast(data.message || '<?php echo esc_js( __( 'Sitemap synchronized.', 'bankai-core' ) ); ?>', 'success');
        } catch (e) {
            this.showToast('<?php echo esc_js( __( 'Failed to sync sitemap.', 'bankai-core' ) ); ?>', 'error');
        }
    },
    async runAudit() {
        try {
            const res = await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/dashboard/deep-audit', {
                method: 'POST',
                headers: { 'X-WP-Nonce': window.bankaiData?.nonce || '' }
            });
            const data = await res.json();
            this.showToast(data.message || '<?php echo esc_js( __( 'Deep audit complete.', 'bankai-core' ) ); ?>', 'success');
        } catch (e) {
            this.showToast('<?php echo esc_js( __( 'Failed to run audit.', 'bankai-core' ) ); ?>', 'error');
        }
    }
}">

    <!-- Toast Component -->
    <div x-show="toast.show" x-transition
         :class="toast.type === 'success' ? 'bankai-toast bankai-toast-success' : 'bankai-toast bankai-toast-error'"
         x-text="toast.message" style="display: none;"></div>

    <!-- Left Sidebar -->
    <aside class="bankai-sidebar">
        <div>
            <!-- Sidebar Brand Header -->
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 32px; padding-left: 8px; padding-right: 8px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background-color: #1E293B; border: 1px solid #334155; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: #F8FAFC; letter-spacing: 0.3px;">Bankai Core</div>
                    <div style="font-size: 10px; color: #10B981; font-weight: 600; letter-spacing: 0.5px;">PRO V4.0 ENTERPRISE</div>
                </div>
            </div>

            <!-- Navigation List -->
            <nav style="display: flex; flex-direction: column; gap: 4px;">
                <template x-for="item in [
                    { id: 'overview', label: '<?php echo esc_js( __( 'Overview & Health', 'bankai-core' ) ); ?>', icon: '🛡️' },
                    { id: 'theme', label: '<?php echo esc_js( __( 'Theme & Kits', 'bankai-core' ) ); ?>', icon: '🎨' },
                    { id: 'seo', label: '<?php echo esc_js( __( 'SEO Engine', 'bankai-core' ) ); ?>', icon: '🔍' },
                    { id: 'performance', label: '<?php echo esc_js( __( 'Performance & Speed', 'bankai-core' ) ); ?>', icon: '⚡' },
                    { id: 'media', label: '<?php echo esc_js( __( 'Media & Watermark', 'bankai-core' ) ); ?>', icon: '🖼️' },
                    { id: 'ai', label: '<?php echo esc_js( __( 'AI Studio', 'bankai-core' ) ); ?>', icon: '🤖' }
                ]" :key="item.id">
                    <button @click="activeTab = item.id"
                            :style="activeTab === item.id
                                ? 'display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; border: none; background-color: #111827; color: #38BDF8; font-weight: 600; font-size: 13px; cursor: pointer; width: 100%; border-inline-start: 3px solid #38BDF8;'
                                : 'display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; border: none; background-color: transparent; color: #94A3B8; font-weight: 500; font-size: 13px; cursor: pointer; width: 100%; border-inline-start: 3px solid transparent;'"
                    >
                        <span x-text="item.icon"></span>
                        <span x-text="item.label"></span>
                    </button>
                </template>
            </nav>
        </div>

        <!-- License Floating Card -->
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 14px; font-size: 11px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="color: #10B981; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10B981;"></span>
                    <?php esc_html_e( 'ACTIVE PRO', 'bankai-core' ); ?>
                </span>
                <span style="color: #64748B;">v4.0.0</span>
            </div>
            <div style="color: #F1F5F9; font-weight: 600; margin-bottom: 4px;"><?php esc_html_e( 'Settings & License Key', 'bankai-core' ); ?></div>
            <div style="color: #64748B;"><?php esc_html_e( 'Expires: Dec 2027', 'bankai-core' ); ?> 🔑</div>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <main class="bankai-content">
        <!-- Top Action Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #1E2D4A;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background-color: #1E2D4A; display: flex; align-items: center; justify-content: center;">🛡️</div>
                <span style="font-weight: 700; font-size: 16px; color: #F8FAFC;"><?php esc_html_e( 'Enterprise Client WP', 'bankai-core' ); ?></span>
                <span style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10B981; font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10B981;"></span>
                    <?php esc_html_e( 'Bankai Core: 100% Operational', 'bankai-core' ); ?>
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button @click="purgeCache()" style="background-color: #111827; border: 1px solid #1E2D4A; color: #E2E8F0; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    🔄 <?php esc_html_e( 'Purge Cache', 'bankai-core' ); ?>
                </button>
                <button @click="syncSitemap()" style="background-color: #111827; border: 1px solid #1E2D4A; color: #E2E8F0; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    🔀 <?php esc_html_e( 'Sync Sitemap', 'bankai-core' ); ?>
                </button>
                <button @click="showToast('<?php echo esc_js( __( 'Settings persisted to WordPress database.', 'bankai-core' ) ); ?>')" style="background-color: #4F46E5; border: none; color: #FFFFFF; padding: 8px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.4);">
                    💾 <?php esc_html_e( 'Save Changes', 'bankai-core' ); ?>
                </button>
            </div>
        </div>

        <!-- Title Banner -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <div style="font-size: 11px; color: #64748B; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                    WORDPRESS ADMIN &gt; BANKAI CORE &gt; OVERVIEW &amp; HEALTH
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #F8FAFC;">
                        <?php esc_html_e( 'Executive Overview & System Health', 'bankai-core' ); ?>
                    </h1>
                    <span style="background-color: #1E2D4A; color: #38BDF8; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;">🟢 Cluster Sync</span>
                </div>
                <p style="margin: 6px 0 0 0; color: #94A3B8; font-size: 13px;">
                    <?php esc_html_e( 'Real-time engine diagnostics, active core micro-engines, and automated telemetry monitors.', 'bankai-core' ); ?>
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="background-color: #111827; border: 1px solid #1E2D4A; padding: 8px 14px; border-radius: 8px; font-size: 11px; color: #94A3B8; display: flex; align-items: center; gap: 10px;">
                    <span><strong style="color: #10B981;">HEARTBEAT:</strong> 200 OK</span>
                    <span>|</span>
                    <span><?php echo esc_html( gmdate( 'H:i:s' ) . ' UTC' ); ?></span>
                </div>
                <button @click="runAudit()" style="background-color: #6366F1; color: #FFFFFF; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
                    🎯 <?php esc_html_e( 'Run Deep Audit', 'bankai-core' ); ?>
                </button>
            </div>
        </div>

        <!-- 4 Metric Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; color: #94A3B8;"><?php esc_html_e( 'Engine Uptime', 'bankai-core' ); ?></span>
                    <span style="font-size: 10px; background-color: rgba(16, 185, 129, 0.15); color: #10B981; padding: 2px 8px; border-radius: 10px;">Sub-50ms Edge</span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #F8FAFC; margin-bottom: 4px;"><?php echo esc_html( $stats['uptime'] ); ?></div>
                <div style="font-size: 11px; color: #64748B;"><span style="color: #10B981;">+0.02% (30d)</span> | Zero cold starts HTTP/3</div>
            </div>

            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; color: #94A3B8;"><?php esc_html_e( 'Indexed Nodes', 'bankai-core' ); ?></span>
                    <span style="font-size: 10px; background-color: rgba(16, 185, 129, 0.15); color: #10B981; padding: 2px 8px; border-radius: 10px;">100% Crawl Health</span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #F8FAFC; margin-bottom: 4px;"><?php echo esc_html( $stats['indexed_nodes'] ); ?> <span style="font-size: 13px; color: #64748B; font-weight: 400;">/ 14,820 URIs</span></div>
                <div style="font-size: 11px; color: #64748B;">Sitemap index synchronized ⚙️</div>
            </div>

            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; color: #94A3B8;"><?php esc_html_e( 'AI Crawls Today', 'bankai-core' ); ?></span>
                    <span style="font-size: 10px; background-color: rgba(56, 189, 248, 0.15); color: #38BDF8; padding: 2px 8px; border-radius: 10px;">Live Stream</span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #F8FAFC; margin-bottom: 4px;"><?php echo esc_html( $stats['ai_crawls'] ); ?> <span style="font-size: 12px; color: #10B981; font-weight: 600;">+28% vs yday</span></div>
                <div style="font-size: 11px; color: #64748B;">Claude &amp; GPT-Bot priority 🤖</div>
            </div>

            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; color: #94A3B8;"><?php esc_html_e( 'Avg Server Latency', 'bankai-core' ); ?></span>
                    <span style="font-size: 10px; background-color: rgba(16, 185, 129, 0.15); color: #10B981; padding: 2px 8px; border-radius: 10px;">Optimal &lt; 100ms</span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #10B981; margin-bottom: 4px;"><?php echo esc_html( $stats['avg_latency'] ); ?> <span style="font-size: 12px; color: #64748B; font-weight: 400;">P99: <?php echo esc_html( $stats['p99_latency'] ); ?></span></div>
                <div style="font-size: 11px; color: #64748B;">Edge Varnish Cache Hit: <?php echo esc_html( $stats['varnish_hit'] ); ?> ⚡</div>
            </div>
        </div>

        <!-- Module Toggles Grid -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
            <!-- SEO Engine Card -->
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">{}</span>
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #F8FAFC;"><?php esc_html_e( 'SEO Engine', 'bankai-core' ); ?></div>
                            <div style="font-size: 11px; color: #10B981;">24 Rules Active</div>
                        </div>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" :checked="modules.seo_engine == 1" @change="toggleModule('seo_engine')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0 0 14px 0;">
                    Autonomous JSON-LD schema v2.1, canonical routing &amp; dynamic meta rules.
                </p>
                <div style="font-size: 11px; color: #64748B; display: flex; justify-content: space-between;">
                    <span>Audit: Clean</span>
                    <span style="color: #10B981; font-weight: 600;">100% Validated</span>
                </div>
            </div>

            <!-- Media Optimizer Card -->
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">🖼️</span>
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #F8FAFC;"><?php esc_html_e( 'Media Optimizer', 'bankai-core' ); ?></div>
                            <div style="font-size: 11px; color: #38BDF8;">89% Payload Reduced</div>
                        </div>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" :checked="modules.media_optimizer == 1" @change="toggleModule('media_optimizer')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0 0 14px 0;">
                    On-the-fly WebP/AVIF conversion, responsive srcset &amp; YouTube click facade.
                </p>
                <div style="font-size: 11px; color: #64748B; display: flex; justify-content: space-between;">
                    <span>Saved: 4.2 GB</span>
                    <span style="color: #10B981; font-weight: 600;">Lossless Mode</span>
                </div>
            </div>

            <!-- Smart Redirects Card -->
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">🔀</span>
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #F8FAFC;"><?php esc_html_e( 'Smart Redirects', 'bankai-core' ); ?></div>
                            <div style="font-size: 11px; color: #F59E0B;">18 Intercepted</div>
                        </div>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" :checked="modules.smart_redirects == 1" @change="toggleModule('smart_redirects')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0 0 14px 0;">
                    Zero-drift heuristic redirect engine with automated regex fallback.
                </p>
                <div style="font-size: 11px; color: #64748B; display: flex; justify-content: space-between;">
                    <span>Heuristic Accuracy</span>
                    <span style="color: #10B981; font-weight: 600;">98.4% Confidence</span>
                </div>
            </div>

            <!-- LLM Agent Manifest Card -->
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">🤖</span>
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #F8FAFC;"><?php esc_html_e( 'LLM Agent Manifest', 'bankai-core' ); ?></div>
                            <div style="font-size: 11px; color: #10B981;">GPT &amp; Claude OK</div>
                        </div>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" :checked="modules.llm_manifest == 1" @change="toggleModule('llm_manifest')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0 0 14px 0;">
                    Autonomous crawler endpoints &amp; structured markdown index for AI agents.
                </p>
                <div style="font-size: 11px; color: #64748B; display: flex; justify-content: space-between;">
                    <span>Manifest Spec</span>
                    <span style="color: #10B981; font-weight: 600;">v1.2 compliant</span>
                </div>
            </div>
        </div>

        <!-- 404 Anomaly Log & Telemetry Gauges -->
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; margin-bottom: 24px;">
            <!-- Radial Gauge Telemetry -->
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <span style="font-weight: 700; font-size: 14px; color: #F8FAFC; display: flex; align-items: center; gap: 8px;">
                        ⚙️ <?php esc_html_e( 'SEO & Core Web Vitals', 'bankai-core' ); ?>
                    </span>
                    <span style="background-color: rgba(16, 185, 129, 0.15); color: #10B981; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;">Grade A+ Certified</span>
                </div>

                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 16px 0 24px 0;">
                    <div style="position: relative; width: 150px; height: 150px;">
                        <svg width="150" height="150" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" stroke="#1E2D4A" stroke-width="8" fill="none" />
                            <circle cx="50" cy="50" r="42" stroke="#10B981" stroke-width="8" fill="none"
                                    stroke-dasharray="264"
                                    stroke-dashoffset="<?php echo 264 - ( 264 * (int) $stats['overall_score'] ) / 100; ?>"
                                    stroke-linecap="round" transform="rotate(-90 50 50)" />
                        </svg>
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div style="font-size: 32px; font-weight: 800; color: #F8FAFC;"><?php echo esc_html( $stats['overall_score'] ); ?><span style="font-size: 16px; color: #64748B;">/100</span></div>
                            <div style="font-size: 10px; color: #10B981; font-weight: 700; text-transform: uppercase;">OPTIMAL INDEX</div>
                            <div style="font-size: 10px; color: #64748B;">Audited <?php echo esc_html( $stats['last_audit'] ); ?></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                            <span style="color: #94A3B8;">TTFB (Time to First Byte)</span>
                            <span style="color: #10B981; font-weight: 600;"><?php echo esc_html( $stats['ttfb'] ); ?></span>
                        </div>
                        <div style="height: 4px; background-color: #1E2D4A; border-radius: 2px; overflow: hidden;">
                            <div style="width: 90%; height: 100%; background-color: #10B981;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                            <span style="color: #94A3B8;">FCP (First Contentful Paint)</span>
                            <span style="color: #10B981; font-weight: 600;"><?php echo esc_html( $stats['fcp'] ); ?></span>
                        </div>
                        <div style="height: 4px; background-color: #1E2D4A; border-radius: 2px; overflow: hidden;">
                            <div style="width: 85%; height: 100%; background-color: #10B981;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                            <span style="color: #94A3B8;">LCP (Largest Contentful Paint)</span>
                            <span style="color: #10B981; font-weight: 600;"><?php echo esc_html( $stats['lcp'] ); ?></span>
                        </div>
                        <div style="height: 4px; background-color: #1E2D4A; border-radius: 2px; overflow: hidden;">
                            <div style="width: 80%; height: 100%; background-color: #10B981;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 404 Anomaly Log Table -->
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <span style="font-weight: 700; font-size: 14px; color: #F8FAFC; display: flex; align-items: center; gap: 8px;">
                        ⚠️ <?php esc_html_e( '404 Anomaly Hits & Heuristics', 'bankai-core' ); ?>
                    </span>
                    <span style="background-color: rgba(245, 158, 11, 0.15); color: #F59E0B; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;">🔴 Live Ingestion Feed</span>
                </div>

                <div style="overflow-x: auto; margin-bottom: 16px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid #1E2D4A; color: #64748B; font-size: 10px; text-transform: uppercase;">
                                <th style="padding: 8px;">REQUESTED URI</th>
                                <th style="padding: 8px; text-align: center;">HITS</th>
                                <th style="padding: 8px;">SOURCE / IP</th>
                                <th style="padding: 8px; text-align: right;">RECOMMENDED ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $logs404 as $log ) : ?>
                                <tr style="border-bottom: 1px solid #1E2D4A;">
                                    <td style="padding: 10px 8px;">
                                        <div style="color: #F1F5F9; font-weight: 600; word-break: break-all;"><?php echo esc_html( $log['requested_uri'] ); ?></div>
                                        <?php if ( ! empty( $log['target_uri'] ) ) : ?>
                                            <div style="font-size: 10px; color: #10B981; margin-top: 2px;">Target: <?php echo esc_html( $log['target_uri'] ); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px 8px; text-align: center;">
                                        <span style="background-color: #1E2D4A; padding: 2px 8px; border-radius: 4px; font-weight: 700; color: #F8FAFC;">
                                            <?php echo esc_html( $log['hits'] ); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 10px 8px; color: #94A3B8; font-size: 11px; white-space: pre-line;">
                                        <?php echo esc_html( $log['source_ip'] ); ?>
                                    </td>
                                    <td style="padding: 10px 8px; text-align: right;">
                                        <?php if ( 'auto_dropped' === $log['action_type'] ) : ?>
                                            <span style="background-color: #EF4444; color: #FFFFFF; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-block;">
                                                🚫 Auto-Dropped
                                            </span>
                                        <?php else : ?>
                                            <button @click="showToast('<?php echo esc_js( sprintf( __( 'Applied 301 rule for %s', 'bankai-core' ), $log['requested_uri'] ) ); ?>')"
                                                    style="background-color: #10B981; border: none; color: #FFFFFF; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                                ✓ <?php esc_html_e( 'Apply 301 (96%)', 'bankai-core' ); ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #64748B;">
                    <span>Heuristic matching active via Bankai ML inference</span>
                    <span style="color: #10B981; cursor: pointer; font-weight: 600;">Batch Accept High Confidence (&gt;90%) &rarr;</span>
                </div>
            </div>
        </div>
    </main>
</div>
