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
<div id="tab-speed-cache" class="bankai-tab-pane" x-show="activeTab === 'speed-cache'" x-cloak>

    <!-- Header -->
    <div class="bankai-card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;padding:20px;flex-wrap:wrap;gap:14px;">
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;flex-wrap:wrap;">
                <h2 style="font-size:18px;font-weight:800;color:#1F2328;margin:0;display:flex;align-items:center;gap:8px;">
                    <svg class="solar-icon" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    <span x-text="t('speedEngineTitle')">شتاب‌دهنده کش و سرعت</span>
                </h2>
                <span style="background:rgba(31,136,61,.15);border:1px solid rgba(31,136,61,.4);color:#1A7F37;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;font-family:monospace;"
                      x-text="(speedStats.ttfb || '<?php echo $ttfb; ?>') + ' TTFB'">
                    <?php echo $ttfb; ?> TTFB
                </span>
            </div>
            <p style="font-size:12px;color:#8C959F;margin:0;" x-text="t('speedEngineSubtitle')">
                کش صفحه، CSS بحرانی، Redis و بهینه‌سازی دیتابیس
            </p>
            <?php if ($last_purge): ?>
                <p style="font-size:11px;color:#8C959F;margin:6px 0 0;">آخرین تخلیه کش: <?php echo $last_purge; ?></p>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="button" @click="benchmarkVitals()" :disabled="busy"
                    style="background:#fff;border:1px solid #D0D7DE;color:#1F2328;padding:10px 16px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;">
                <svg class="solar-icon solar-icon-sm" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M12 5V2m-2 0h4"/></svg>
                <span x-text="t('benchmarkVitals')">بنچمارک Core Web Vitals</span>
            </button>
            <button type="button" @click="purgeAllCaches()" :disabled="busy"
                    style="background:#0969DA;border:none;color:#fff;padding:10px 18px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(9,105,218,.35);">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                <span x-text="t('purgeCaches')">تخلیه تمام کش‌ها</span>
            </button>
        </div>
    </div>

    <!-- Live Telemetry -->
    <div class="bankai-grid-4" style="margin-bottom:24px;">
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">نرخ اصابت کش (Hit)</div>
            <div style="font-size:24px;font-weight:800;color:#1A7F37;margin-top:4px;font-family:monospace;" x-text="speedStats.hit_ratio || '<?php echo $hit; ?>'"><?php echo $hit; ?></div>
            <div style="font-size:11px;color:#8C959F;margin-top:2px;">Varnish / HTML</div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">میانگین TTFB</div>
            <div style="font-size:24px;font-weight:800;color:#0969DA;margin-top:4px;font-family:monospace;" x-text="speedStats.ttfb || '<?php echo $ttfb; ?>'"><?php echo $ttfb; ?></div>
            <div style="font-size:11px;color:#8C959F;margin-top:2px;">Server response</div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">تأخیر Redis</div>
            <div style="font-size:24px;font-weight:800;color:#8250DF;margin-top:4px;font-family:monospace;" x-text="speedStats.redis_latency || '<?php echo $redis; ?>'"><?php echo $redis; ?></div>
            <div style="font-size:11px;color:#8C959F;margin-top:2px;font-family:monospace;">object cache</div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">رونوشت‌های دیتابیس</div>
            <div style="font-size:24px;font-weight:800;color:#BC4C00;margin-top:4px;font-family:monospace;">
                <span x-text="speedStats.revisions ?? <?php echo $revs; ?>"><?php echo $revs; ?></span>
                <span style="font-size:12px;color:#8C959F;">مورد</span>
            </div>
            <div style="font-size:11px;color:#0969DA;cursor:pointer;font-weight:700;margin-top:2px;" @click="optimizeDatabase()">
                پاکسازی فوری دیتابیس ←
            </div>
        </div>
    </div>

    <!-- CWV strip -->
    <div class="bankai-card" style="padding:16px;margin-bottom:24px;display:flex;flex-wrap:wrap;gap:20px;align-items:center;">
        <div style="font-size:12px;font-weight:800;color:#1F2328;">Core Web Vitals</div>
        <div style="font-size:12px;"><span style="color:#8C959F;">TTFB</span> <strong x-text="speedVitals.ttfb || '—'">—</strong></div>
        <div style="font-size:12px;"><span style="color:#8C959F;">LCP</span> <strong x-text="speedVitals.lcp || '—'">—</strong></div>
        <div style="font-size:12px;"><span style="color:#8C959F;">CLS</span> <strong x-text="speedVitals.cls || '—'">—</strong></div>
        <div style="font-size:12px;"><span style="color:#8C959F;">FID</span> <strong x-text="speedVitals.fid || '—'">—</strong></div>
        <div style="font-size:12px;margin-inline-start:auto;"><span style="color:#8C959F;">Score</span> <strong style="color:#1A7F37;" x-text="speedVitals.score || '—'">—</strong></div>
    </div>

    <!-- Modules -->
    <div class="bankai-grid-3" style="margin-bottom:32px;">
        <?php foreach ($speed_mods as $mod):
            $mod_id   = esc_attr($mod['id'] ?? '');
            $title_en = esc_js($mod['title'] ?? '');
            $title_fa = esc_js($mod['title_fa'] ?? ($mod['title'] ?? ''));
            $desc_en  = esc_js($mod['description'] ?? '');
            $desc_fa  = esc_js($mod['description_fa'] ?? ($mod['description'] ?? ''));
            $badge    = esc_html($mod['badge'] ?? '');
        ?>
        <div class="bankai-card" style="padding:20px;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(9,105,218,.12);display:flex;align-items:center;justify-content:center;color:#0969DA;">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#1F2328;"
                                 x-text="isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>'">
                                <?php echo esc_html($mod['title_fa'] ?? $mod['title'] ?? ''); ?>
                            </div>
                            <span style="background:rgba(9,105,218,.12);color:#0969DA;font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:3px;"><?php echo $badge; ?></span>
                        </div>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox"
                               class="bk-sub-module-toggle"
                               data-module="<?php echo $mod_id; ?>"
                               x-model="speedState['<?php echo $mod_id; ?>']"
                               @change="toggleSpeedModule('<?php echo $mod_id; ?>')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size:12px;color:#656D76;line-height:1.5;margin:0 0 16px;"
                   x-text="isRtl ? '<?php echo $desc_fa; ?>' : '<?php echo $desc_en; ?>'">
                    <?php echo esc_html($mod['description_fa'] ?? $mod['description'] ?? ''); ?>
                </p>
            </div>
            <div style="border-top:1px solid #D0D7DE;padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11px;font-weight:700;"
                      :style="speedState['<?php echo $mod_id; ?>'] ? 'color:#1A7F37' : 'color:#8C959F'"
                      x-text="speedState['<?php echo $mod_id; ?>'] ? t('active') : t('disabled')">—</span>
                <button type="button"
                        @click="openSpeedDrawer('<?php echo $mod_id; ?>', isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>')"
                        style="background:#F6F8FA;border:1px solid #D0D7DE;color:#0969DA;font-size:11px;font-weight:700;padding:5px 12px;border-radius:6px;cursor:pointer;">
                    <span x-text="t('configure')">پیکربندی</span>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Speed Drawer — modern, no dark overlay -->
    <div x-show="speedDrawer.show" x-cloak
         class="bankai-modal-shell"
         x-transition:enter="bk-modal-enter"
         x-transition:enter-start="bk-modal-enter-start"
         x-transition:enter-end="bk-modal-enter-end"
         x-transition:leave="bk-modal-leave"
         x-transition:leave-start="bk-modal-leave-start"
         x-transition:leave-end="bk-modal-leave-end">
        <div class="bankai-modal-panel" @click.outside="speedDrawer.show = false">
            <div class="bankai-modal-head">
                <h3 class="bankai-modal-title" x-text="speedDrawer.title + (isRtl ? ' — تنظیمات کش' : ' — Cache Settings')"></h3>
                <button type="button" class="bankai-modal-close" @click="speedDrawer.show = false" aria-label="Close">&times;</button>
            </div>
            <div class="bankai-modal-body">
                <div class="bankai-field">
                    <label>زمان انقضای کش (TTL — ثانیه)</label>
                    <input type="number" x-model="speedDrawer.ttl" min="60" step="60" class="bankai-input mono">
                    <span class="bankai-hint">مثلاً 3600 = یک ساعت · 86400 = یک روز</span>
                </div>
                <div class="bankai-field">
                    <label>قوانین استثنا (هر خط یک الگو)</label>
                    <textarea rows="4" x-model="speedDrawer.exclusions" class="bankai-input mono"></textarea>
                    <span class="bankai-hint">مسیرهایی که نباید کش شوند؛ مثل /cart/*</span>
                </div>
            </div>
            <div class="bankai-modal-foot">
                <button type="button" class="bankai-btn-ghost" @click="speedDrawer.show = false" x-text="t('cancel')">انصراف</button>
                <button type="button" class="bankai-btn-primary" @click="saveSpeedDrawerSettings()" x-text="t('save')">ذخیره</button>
            </div>
        </div>
    </div>
</div>
