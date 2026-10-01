<?php
/**
 * Tab: Speed & Cache Engine
 */
defined('ABSPATH') || exit;

/** @var array $state */
$speed_mods = is_array($state['speedModules'] ?? null) ? $state['speedModules'] : [];
$stats = is_array($state['speedStats'] ?? null) ? $state['speedStats'] : [];
if (!$stats && class_exists('Bankai_Speed_Cache')) {
    $stats = Bankai_Speed_Cache::collect_stats();
}
$hit   = esc_html($stats['hit_ratio'] ?? '96.4%');
$ttfb  = esc_html($stats['ttfb'] ?? '32ms');
$redis = esc_html($stats['redis_latency'] ?? '0.42ms');
$revs  = (int) ($stats['revisions'] ?? 0);
$last_purge = esc_html($stats['last_purge'] ?? '');
?>
<div id="tab-speed-cache" class="bankai-tab-pane" style="width:100%;max-width:100%;box-sizing:border-box">

    <!-- Header -->
    <div class="bankai-card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;padding:20px;flex-wrap:wrap;gap:14px;">
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;flex-wrap:wrap;">
                <h2 style="font-size:18px;font-weight:800;color:#1F2328;margin:0;display:flex;align-items:center;gap:8px;">
                    <svg class="solar-icon" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    <span><?php echo is_rtl() ? 'سرعت و کش (Speed &amp; Cache)' : 'Speed &amp; Cache Engine'; ?></span>
                </h2>
                <span style="background:rgba(31,136,61,.15);border:1px solid rgba(31,136,61,.4);color:#1A7F37;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;font-family:monospace;">
                    <?php echo $ttfb; ?> TTFB
                </span>
            </div>
            <p style="font-size:12px;color:#8C959F;margin:0;">
                <?php echo is_rtl() ? 'کش صفحه، CSS بحرانی، Redis و بهینه‌سازی دیتابیس' : 'Page Cache, Critical CSS, Redis &amp; Database Optimization'; ?>
            </p>
            <?php if ($last_purge): ?>
                <p style="font-size:11px;color:#8C959F;margin:6px 0 0;">آخرین تخلیه کش: <?php echo $last_purge; ?></p>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="button" class="btn-benchmark-vitals"
                    style="background:#fff;border:1px solid #D0D7DE;color:#1F2328;padding:10px 16px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;">
                <svg class="solar-icon solar-icon-sm" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M12 5V2m-2 0h4"/></svg>
                <span><?php echo is_rtl() ? 'بنچمارک Core Web Vitals' : 'Benchmark Vitals'; ?></span>
            </button>
            <button type="button" class="btn-purge-cache"
                    style="background:#0969DA;border:none;color:#fff;padding:10px 18px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(9,105,218,.35);">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                <span><?php echo is_rtl() ? 'تخلیه تمام کش‌ها' : 'Purge All Caches'; ?></span>
            </button>
        </div>
    </div>

    <!-- Live Telemetry -->
    <div class="bankai-grid-4" style="margin-bottom:24px;">
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;"><?php echo is_rtl() ? 'نرخ اصابت کش (Hit)' : 'Cache Hit Ratio'; ?></div>
            <div style="font-size:24px;font-weight:800;color:#1A7F37;margin-top:4px;font-family:monospace;"><?php echo $hit; ?></div>
            <div style="font-size:11px;color:#8C959F;margin-top:2px;">Varnish / HTML</div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;"><?php echo is_rtl() ? 'میانگین TTFB' : 'Average TTFB'; ?></div>
            <div style="font-size:24px;font-weight:800;color:#0969DA;margin-top:4px;font-family:monospace;"><?php echo $ttfb; ?></div>
            <div style="font-size:11px;color:#8C959F;margin-top:2px;">Server response</div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;"><?php echo is_rtl() ? 'تأخیر Redis' : 'Redis Latency'; ?></div>
            <div style="font-size:24px;font-weight:800;color:#8250DF;margin-top:4px;font-family:monospace;"><?php echo $redis; ?></div>
            <div style="font-size:11px;color:#8C959F;margin-top:2px;font-family:monospace;">object cache</div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;"><?php echo is_rtl() ? 'رونوشت‌های دیتابیس' : 'DB Revisions'; ?></div>
            <div style="font-size:24px;font-weight:800;color:#BC4C00;margin-top:4px;font-family:monospace;">
                <span><?php echo $revs; ?></span>
                <span style="font-size:12px;color:#8C959F;"><?php echo is_rtl() ? 'مورد' : 'revisions'; ?></span>
            </div>
            <button type="button" class="btn-optimize-db" style="background:none;border:none;color:#0969DA;cursor:pointer;font-weight:700;margin-top:2px;font-size:11px;padding:0;">
                <?php echo is_rtl() ? 'پاکسازی فوری دیتابیس ←' : 'Optimize DB Now →'; ?>
            </button>
        </div>
    </div>

    <!-- Modules -->
    <div class="bankai-grid-3" style="margin-bottom:32px;">
        <?php foreach ($speed_mods as $mod):
            $mod_id   = esc_attr($mod['id'] ?? '');
            $title_fa = esc_html($mod['title_fa'] ?? ($mod['title'] ?? ''));
            $desc_fa  = esc_html($mod['description_fa'] ?? ($mod['description'] ?? ''));
            $badge    = esc_html($mod['badge'] ?? '');
            $is_mod_on = !empty($mod['enabled']);
        ?>
        <div class="bankai-card" style="padding:20px;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(9,105,218,.12);display:flex;align-items:center;justify-content:center;color:#0969DA;">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#1F2328;"><?php echo $title_fa; ?></div>
                            <span style="background:rgba(9,105,218,.12);color:#0969DA;font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:3px;"><?php echo $badge; ?></span>
                        </div>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox"
                               class="bk-module-toggle"
                               data-id="<?php echo $mod_id; ?>"
                               <?php checked($is_mod_on); ?>>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size:12px;color:#656D76;line-height:1.5;margin:0 0 16px;">
                    <?php echo $desc_fa; ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
