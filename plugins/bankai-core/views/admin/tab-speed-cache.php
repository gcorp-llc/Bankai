<?php
/**
 * Tab: Speed & Cache — GitHub Light visual system
 */
defined('ABSPATH') || exit;

/** @var array $state */
$stats = is_array($state['speedStats'] ?? null) ? $state['speedStats'] : [];
if (!$stats && class_exists('Bankai_Speed_Cache')) {
    $stats = Bankai_Speed_Cache::collect_stats();
}
$hit   = esc_html($stats['hit_ratio'] ?? '—');
$ttfb  = esc_html($stats['ttfb'] ?? '—');
$redis = esc_html($stats['redis_latency'] ?? '—');

$cfg = function_exists('bankai_get_option') ? (bankai_get_option('speed_config', []) ?: []) : [];
if (!is_array($cfg)) { $cfg = []; }
$cfg_json = wp_json_encode($cfg, JSON_UNESCAPED_UNICODE);
?>
<div id="tab-speed-cache" class="bankai-tab-pane bk-speed-shell"
     x-show="activeTab === 'speed-cache'"
     :class="{ 'is-active-tab': activeTab === 'speed-cache' }"
     x-cloak>
<div x-data="bankaiSpeedTab(<?php echo htmlspecialchars($cfg_json, ENT_QUOTES, 'UTF-8'); ?>)"
     x-init="typeof initSpeed === 'function' && initSpeed()">

    <div class="bk-lg-header bk-wm-hero bk-page-hero">
        <div class="bk-wm-hero__main">
            <div class="bk-wm-hero__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div>
                <div class="bk-wm-hero__title-row">
                    <h2>کش و سرعت</h2>
                    <span class="bk-wm-pill">Performance Lab</span>
                </div>
                <p>کش صفحه، بهینه‌سازی CSS/JS، LazyLoad، Redis و پاکسازی دیتابیس</p>
            </div>
        </div>
        <div class="bk-wm-hero__actions" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <button type="button" class="bk-btn" @click="rebuildAssets()" :disabled="busyRebuild" style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.3);font-size:12px">
                <span class="bk-btn-spin" x-show="busyRebuild" x-cloak></span>
                بازسازی فایل‌های بهینه‌شده
            </button>
            <button type="button" class="bk-btn" @click="benchmarkVitals()" :disabled="busyBenchmark" style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.3);font-size:12px">
                <span class="bk-btn-spin" x-show="busyBenchmark" x-cloak></span>
                بنچمارک Vitals
            </button>
            <div class="bk-gh-stats" style="gap:6px">
                <div class="bk-gh-stat" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.25);min-width:70px;padding:6px 10px"><span style="color:rgba(255,255,255,.7)">TTFB</span><strong style="color:#fff;font-size:12px" x-text="speedStats.ttfb || '—'">—</strong></div>
                <div class="bk-gh-stat" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.25);min-width:70px;padding:6px 10px"><span style="color:rgba(255,255,255,.7)">Hit</span><strong style="color:#fff;font-size:12px" x-text="speedStats.hit_ratio || '—'">—</strong></div>
            </div>
        </div>
    </div>
    <nav class="bk-capsule-tabs bk-art-tabs bk-gh-tabs" role="tablist">
        <button type="button" class="bk-capsule-tab" :class="{ 'is-active': speedTab === 'config' }" @click="speedTab = 'config'" title="پیکربندی کش">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <span class="bk-capsule-label">پیکربندی کش</span>
        </button>
        <button type="button" class="bk-capsule-tab" :class="{ 'is-active': speedTab === 'redis' }" @click="speedTab = 'redis'; loadRedisSettings && loadRedisSettings(); testRedisQuiet && testRedisQuiet()" title="Redis">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg>
            <span class="bk-capsule-label">Redis</span>
            <span class="bk-gh-redis-badge" x-show="redisStatus.ok" x-cloak title="Redis فعال"><i></i></span>
            <span class="bk-gh-redis-badge is-off" x-show="redisStatus.checked && !redisStatus.ok" x-cloak><i></i></span>
        </button>
    </nav>

    
    <div x-show="speedTab === 'config'" x-cloak>
    <div class="bk-cwv-banner" style="margin:0 0 14px;padding:14px 16px;border-radius:12px;border:1px solid #c7d2fe;background:#eef2ff">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
            <div style="min-width:200px;flex:1">
                <strong style="display:block;font-size:13px;color:#312e81;margin-bottom:4px">پیشنهاد Lighthouse — اعمال یک‌کلیکه</strong>
                <span style="font-size:12px;color:#64748b;line-height:1.5">Font-display، CLS تصاویر، Async CSS، Delay شخص‌ثالث، سقف Preconnect=۴، Minify و Defer JS</span>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" @click="applyLighthouseDefaults()" :disabled="busySave['cwv_lighthouse']">
                اعمال تنظیمات پیشنهادی
            </button>
        </div>
    </div>
    <div class="bk-seo-mod-grid">
        <template x-for="opt in speedOptions" :key="opt.id">
            <div class="bk-seo-mod-card" :class="{ 'is-on': isOptOn(opt.id) }">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;gap:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="bk-seo-mod-icon" x-html="opt.svg || ''"></div>
                            <div>
                                <div style="font-weight:700;font-size:13px;color:#0F172A;line-height:1.35;" x-text="opt.title"></div>
                                <span class="bk-seo-badge" :style="opt.badgeStyle || 'background:#10B9811A;color:#10B981'" x-text="opt.badge"></span>
                            </div>
                        </div>
                        <label class="bankai-switch" @click.stop>
                            <input type="checkbox" :checked="isOptOn(opt.id)" @change="toggleOpt(opt.id, $event.target.checked)">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <p style="font-size:12px;color:#64748B;line-height:1.6;margin:0 0 14px;" x-text="opt.desc"></p>
                </div>
                <div class="bk-seo-mod-foot">
                    <span :style="isOptOn(opt.id) ? 'color:#16A34A' : 'color:#94A3B8'" x-text="isOptOn(opt.id) ? 'فعال' : 'غیرفعال'"></span>
                    <button type="button" class="bk-seo-btn-sm" @click="openOpt = (openOpt === opt.id ? '' : opt.id)"
                            x-text="openOpt === opt.id ? 'بستن' : 'تنظیمات'"></button>
                </div>
                <div class="bk-gh-card__panel" x-show="openOpt === opt.id" x-cloak style="margin:0 -6px;padding-top:8px;border-top:1px solid #f1f5f9">
                    <template x-if="opt.id === 'page_caching'">
                        <div class="bk-gh-fields">
                            <label>عمر کش (ثانیه)<input type="number" min="60" x-model.number="cfg.page_caching.ttl"></label>
                            <label>کاربران واردشده<select x-model="cfg.page_caching.skip_logged_in"><option value="1">کش نشود</option><option value="0">کش شود</option></select></label>
                            <label>Query String<select x-model="cfg.page_caching.skip_query"><option value="1">کش نشود</option><option value="0">کش شود</option></select></label>
                            <label class="full">استثنا URL<textarea rows="2" x-model="cfg.page_caching.exclusions"></textarea></label>
                            <div class="bk-gh-actions full">
                                <button type="button" class="bk-gh-btn-primary" :disabled="busySave[opt.id]" @click="saveOpt('page_caching')">ذخیره</button>
                                <button type="button" class="bk-gh-btn" :disabled="busyPurge" @click="purgePageCache()">پاکسازی</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'asset_optimization'">
                        <div class="bk-gh-fields">
                            <label class="chk"><input type="checkbox" x-model="cfg.asset_optimization.minify_css"> Minify CSS</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.asset_optimization.minify_js"> Minify JS</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.asset_optimization.combine_css"> ترکیب CSS</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.asset_optimization.defer_js"> Defer JS</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.asset_optimization.delay_js"> Delay JS</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.asset_optimization.remove_unused_css"> Unused CSS</label>
                            <div class="bk-gh-actions full">
                                <button type="button" class="bk-gh-btn-primary" :disabled="busySave[opt.id]" @click="saveOpt('asset_optimization')">ذخیره</button>
                                <button type="button" class="bk-gh-btn" :disabled="busyRebuild" @click="rebuildAssets()">بازسازی فایل‌های بهینه‌شده</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'cwv_lighthouse'">
                        <div class="bk-gh-fields">
                            <p class="full" style="margin:0 0 8px;font-size:12px;color:#64748b;line-height:1.55">
                                تنظیمات مبتنی بر گزارش Lighthouse شما: TBT، CLS، Font-display، Render-blocking، Preconnect و تصاویر بدون ابعاد.
                            </p>
                            <label class="chk"><input type="checkbox" x-model="cfg.cwv_lighthouse.font_display"> Font-display: swap</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.cwv_lighthouse.cls_images"> ابعاد صریح تصاویر (کاهش CLS)</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.cwv_lighthouse.async_css"> Async CSS غیرحیاتی</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.cwv_lighthouse.delay_third_party"> Delay اسکریپت‌های شخص‌ثالث</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.cwv_lighthouse.unused_css_js"> حذف CSS/JS استفاده‌نشده</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.cwv_lighthouse.composited_animations"> انیمیشن‌های Composited</label>
                            <label>سقف Preconnect<input type="number" min="1" max="6" x-model.number="cfg.cwv_lighthouse.max_preconnect"></label>
                            <div class="bk-gh-actions full">
                                <button type="button" class="bk-gh-btn-primary" :disabled="busySave[opt.id]" @click="saveOpt('cwv_lighthouse')">ذخیره و اعمال</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'media_lazy'">
                        <div class="bk-gh-fields">
                            <label class="chk"><input type="checkbox" x-model="cfg.media_lazy.images"> تصاویر</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.media_lazy.iframes"> iframe</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.media_lazy.youtube"> YouTube</label>
                            <label>آستانه (px)<input type="number" x-model.number="cfg.media_lazy.threshold"></label>
                            <div class="bk-gh-actions full"><button type="button" class="bk-gh-btn-primary" @click="saveOpt('media_lazy')">ذخیره</button></div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'preload'">
                        <div class="bk-gh-fields">
                            <label class="chk"><input type="checkbox" x-model="cfg.preload.fonts"> فونت</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.preload.links"> Prefetch</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.preload.dns"> DNS</label>
                            <label class="full">Preconnect<textarea rows="2" x-model="cfg.preload.preconnect"></textarea></label>
                            <div class="bk-gh-actions full"><button type="button" class="bk-gh-btn-primary" @click="saveOpt('preload')">ذخیره</button></div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'browser_cache'">
                        <div class="bk-gh-fields">
                            <label>TTL<input type="number" x-model.number="cfg.browser_cache.ttl"></label>
                            <label class="chk"><input type="checkbox" x-model="cfg.browser_cache.etag"> ETag</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.browser_cache.gzip"> Gzip</label>
                            <div class="bk-gh-actions full"><button type="button" class="bk-gh-btn-primary" @click="saveOpt('browser_cache')">ذخیره</button></div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'database_optimizer'">
                        <div class="bk-gh-fields">
                            <label class="chk"><input type="checkbox" x-model="cfg.database_optimizer.revisions"> رونوشت</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.database_optimizer.auto_drafts"> پیش‌نویس</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.database_optimizer.trashed"> زباله‌دان</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.database_optimizer.spam"> اسپم</label>
                            <label class="chk"><input type="checkbox" x-model="cfg.database_optimizer.transients"> Transient</label>
                            <div class="bk-gh-actions full">
                                <button type="button" class="bk-gh-btn-primary" @click="saveOpt('database_optimizer')">ذخیره</button>
                                <button type="button" class="bk-gh-btn" @click="runDbOptimize()">اجرا</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'heartbeat'">
                        <div class="bk-gh-fields">
                            <label>ادمین (ثانیه)<input type="number" x-model.number="cfg.heartbeat.admin_interval"></label>
                            <label>فرانت<input type="number" x-model.number="cfg.heartbeat.front_interval"></label>
                            <label class="chk"><input type="checkbox" x-model="cfg.heartbeat.disable_front"> غیرفعال فرانت</label>
                            <div class="bk-gh-actions full"><button type="button" class="bk-gh-btn-primary" @click="saveOpt('heartbeat')">ذخیره</button></div>
                        </div>
                    </template>
                    <template x-if="opt.id === 'object_cache'">
                        <div class="bk-gh-fields">
                            <p class="full" style="margin:0;font-size:13px;color:#94a3b8">پیکربندی در تب Redis</p>
                            <div class="bk-gh-actions full"><button type="button" class="bk-gh-btn" @click="speedTab='redis'; loadRedisSettings()">رفتن به Redis</button></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
    </div>

<div x-show="speedTab === 'redis'" x-cloak class="bk-lg-card bk-gh-redis" style="padding:20px">
        <div class="bk-gh-redis__status" :class="redisStatus.ok ? 'is-ok' : (redisStatus.checked ? 'is-bad' : '')">
            <template x-if="redisStatus.ok">
                <span>✓ Redis روی سرور در دسترس است — <span x-text="redisStatus.message"></span></span>
            </template>
            <template x-if="redisStatus.checked && !redisStatus.ok">
                <span>✗ اتصال Redis برقرار نشد — <span x-text="redisStatus.message"></span></span>
            </template>
            <template x-if="!redisStatus.checked">
                <span>برای بررسی وضعیت، «تست اتصال» را بزنید یا تنظیمات را ذخیره کنید.</span>
            </template>
        </div>
        <div class="bk-gh-fields bk-gh-redis-grid">
            <label class="chk full"><input type="checkbox" x-model="redisLocal.enabled"> فعال‌سازی Redis Object Cache</label>
            <label>Host<input type="text" dir="ltr" x-model="redisLocal.host"></label>
            <label>Port<input type="number" dir="ltr" x-model.number="redisLocal.port"></label>
            <label>Password<input type="text" autocomplete="new-password" dir="ltr" x-model="redisLocal.password"></label>
            <label>DB<input type="number" dir="ltr" x-model.number="redisLocal.db"></label>
            <label>Prefix<input type="text" dir="ltr" x-model="redisLocal.prefix"></label>
            <label>Timeout<input type="number" step="0.1" x-model.number="redisLocal.timeout"></label>
        </div>
        <div class="bk-gh-actions" style="margin-top:14px">
            <button type="button" class="bk-gh-btn" :disabled="busyRedisTest" @click="testRedisConnection()">
                <span class="bk-btn-spin" x-show="busyRedisTest" x-cloak></span> تست اتصال
            </button>
            <button type="button" class="bk-gh-btn-primary" :disabled="redisSaving || busyRedisSave" @click="saveRedisSettings()">
                <span class="bk-btn-spin" x-show="redisSaving || busyRedisSave" x-cloak></span>
                <span x-text="(redisSaving || busyRedisSave) ? '…' : 'ذخیره Redis'"></span>
            </button>
        </div>
        <div class="bk-gh-help">
            <strong>راهنما</strong>
            <ol>
                <li><code>sudo apt install redis-server php-redis</code></li>
                <li><code>sudo systemctl enable --now redis-server</code></li>
                <li>Host معمولاً <code>127.0.0.1</code> · Port <code>6379</code></li>
            </ol>
        </div>
    </div>

<!-- /tab-speed-cache.php close -->
</div><!-- /speed inner x-data -->
</div><!-- /tab-speed-cache -->
