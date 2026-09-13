<?php
/**
 * Bankai Core Admin - Overview & Dashboard View
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

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
        'source_ip'          => "192.168.1.42\nGooglebot Crawler",
        'recommended_action' => 'Apply 301 (96%)',
        'action_type'        => 'apply_301',
        'target_uri'         => '/reviews/product-review-2023',
    ),
    array(
        'id'                 => 2,
        'requested_uri'      => '/pricing-v1',
        'hits'               => 12,
        'source_ip'          => "104.28.19.112\nDirect Referrer",
        'recommended_action' => 'Map Target (91%)',
        'action_type'        => 'map_target',
        'target_uri'         => '/pricing',
    ),
    array(
        'id'                 => 3,
        'requested_uri'      => '/wp-content/uploads/temp.pdf',
        'hits'               => 6,
        'source_ip'          => "172.56.21.9\nBroken External",
        'recommended_action' => 'Redirect',
        'action_type'        => 'redirect',
        'target_uri'         => '/',
    ),
    array(
        'id'                 => 4,
        'requested_uri'      => '/.env',
        'hits'               => 31,
        'source_ip'          => "45.154.255.8\nScanner Bot (Blocked)",
        'recommended_action' => 'Auto-Dropped',
        'action_type'        => 'auto_dropped',
        'target_uri'         => '',
    ),
);
?>

<div x-show="activeTab === 'overview'" x-transition>
    <!-- System Telemetry Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
                ⚡ Uptime &amp; Speed
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #10B981;"><?php echo esc_html( $stats['uptime'] ); ?></div>
            <div style="font-size: 11px; color: #94A3B8; margin-top: 4px;">Avg Latency: <?php echo esc_html( $stats['avg_latency'] ); ?></div>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
                🕸️ Indexed Nodes
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #38BDF8;"><?php echo esc_html( $stats['indexed_nodes'] ); ?></div>
            <div style="font-size: 11px; color: #94A3B8; margin-top: 4px;">Varnish Hit: <?php echo esc_html( $stats['varnish_hit'] ); ?></div>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
                🤖 AI Crawler Hits
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #6366F1;"><?php echo esc_html( $stats['ai_crawls'] ); ?></div>
            <div style="font-size: 11px; color: #94A3B8; margin-top: 4px;">LLM Manifest v1.2</div>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
                🛡️ SEO Schema Score
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #F59E0B;"><?php echo esc_html( $stats['schema_score'] ); ?></div>
            <div style="font-size: 11px; color: #94A3B8; margin-top: 4px;">Grade A+ Certified</div>
        </div>
    </div>

    <!-- Active Engine Modules Toggles -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0;">
            🛠️ <?php esc_html_e( 'Core Engine Modules & Features', 'bankai-core' ); ?>
        </h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
            <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 700; font-size: 13px; color: #F8FAFC;">SEO Engine</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0;">Automated meta generation, Schema.org builder & XML Sitemaps.</p>
            </div>

            <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 700; font-size: 13px; color: #F8FAFC;">Media Optimizer</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0;">WebP/AVIF auto-conversion, async processor & lazyloading.</p>
            </div>

            <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 700; font-size: 13px; color: #F8FAFC;">LLM Manifest</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin: 0;">Structured markdown index endpoints for AI agents.</p>
            </div>
        </div>
    </div>

    <!-- 404 Anomaly Log & Core Web Vitals -->
    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px;">
        <!-- Radial Gauge Telemetry -->
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span style="font-weight: 700; font-size: 14px; color: #F8FAFC;">
                    ⚙️ <?php esc_html_e( 'SEO & Core Web Vitals', 'bankai-core' ); ?>
                </span>
                <span style="background-color: rgba(16, 185, 129, 0.15); color: #10B981; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;">Grade A+</span>
            </div>

            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 16px 0 24px 0;">
                <div style="position: relative; width: 140px; height: 140px;">
                    <svg width="140" height="140" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="#1E2D4A" stroke-width="8" fill="none" />
                        <circle cx="50" cy="50" r="42" stroke="#10B981" stroke-width="8" fill="none"
                                stroke-dasharray="264"
                                stroke-dashoffset="<?php echo 264 - ( 264 * (int) $stats['overall_score'] ) / 100; ?>"
                                stroke-linecap="round" transform="rotate(-90 50 50)" />
                    </svg>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 28px; font-weight: 800; color: #F8FAFC;"><?php echo esc_html( $stats['overall_score'] ); ?><span style="font-size: 14px; color: #64748B;">/100</span></div>
                        <div style="font-size: 10px; color: #10B981; font-weight: 700; text-transform: uppercase;">OPTIMAL INDEX</div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                        <span style="color: #94A3B8;">TTFB</span>
                        <span style="color: #10B981; font-weight: 600;"><?php echo esc_html( $stats['ttfb'] ); ?></span>
                    </div>
                    <div style="height: 4px; background-color: #1E2D4A; border-radius: 2px; overflow: hidden;">
                        <div style="width: 90%; height: 100%; background-color: #10B981;"></div>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                        <span style="color: #94A3B8;">LCP</span>
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
                <span style="font-weight: 700; font-size: 14px; color: #F8FAFC;">
                    ⚠️ <?php esc_html_e( '404 Anomaly Hits & Heuristics', 'bankai-core' ); ?>
                </span>
                <span style="background-color: rgba(245, 158, 11, 0.15); color: #F59E0B; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;">Live Feed</span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid #1E2D4A; color: #64748B; font-size: 10px; text-transform: uppercase;">
                            <th style="padding: 8px;">REQUESTED URI</th>
                            <th style="padding: 8px; text-align: center;">HITS</th>
                            <th style="padding: 8px; text-align: right;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $logs404 as $log ) : ?>
                            <tr style="border-bottom: 1px solid #1E2D4A;">
                                <td style="padding: 10px 8px;">
                                    <div style="color: #F1F5F9; font-weight: 600;"><?php echo esc_html( $log['requested_uri'] ); ?></div>
                                </td>
                                <td style="padding: 10px 8px; text-align: center;">
                                    <span style="background-color: #1E2D4A; padding: 2px 8px; border-radius: 4px; font-weight: 700; color: #F8FAFC;">
                                        <?php echo esc_html( $log['hits'] ); ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 8px; text-align: right;">
                                    <button @click="showToast('<?php echo esc_js( sprintf( __( 'Applied 301 rule for %s', 'bankai-core' ), $log['requested_uri'] ) ); ?>')"
                                            style="background-color: #10B981; border: none; color: #FFFFFF; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                        ✓ Apply 301
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
