<?php
/**
 * Dashboard — Health & overview (orange theme + Solar icons)
 * @package Bankai
 */
defined('ABSPATH') || exit;

$posts_pub = (int) (wp_count_posts('post')->publish ?? 0);
$pages_pub = (int) (wp_count_posts('page')->publish ?? 0);
$indexable = $posts_pub + $pages_pub;
$wp_ver    = get_bloginfo('version');
$php_ver   = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
$plugin_ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '—';
$mem_limit = ini_get('memory_limit') ?: '—';
$site_name = get_bloginfo('name');
?>
<div id="tab-overview" class="bankai-tab-pane bk-orange-shell" :class="{ 'is-active-tab': activeTab === 'overview' }" x-show="activeTab === 'overview'" x-cloak x-init="initDashboard && initDashboard()">

  <header class="bk-top-head">
    <div class="bk-top-head__brand">
      <span class="bk-top-head__logo"><iconify-icon icon="solar:widget-2-bold-duotone" width="24" height="24"></iconify-icon></span>
      <div>
        <strong x-text="isRtl ? ('پیشخوان · ' + '<?php echo esc_js($site_name); ?>') : ('Dashboard · <?php echo esc_js($site_name); ?>')">پیشخوان</strong>
        <small>سلامت سیستم · KPI · میانبرها · ماژول‌ها</small>
      </div>
    </div>
    <div class="bk-top-head__actions">
      <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="loadDashboardVisits && loadDashboardVisits()" :disabled="dashVisitsLoading">
        <iconify-icon icon="solar:refresh-bold-duotone" width="16"></iconify-icon>
        <span x-text="dashVisitsLoading ? '…' : (isRtl ? 'تازه‌سازی آمار' : 'Refresh stats')"></span>
      </button>
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="setTab('articles')">
        <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="16"></iconify-icon>
        <span x-text="isRtl ? 'تولید محتوا' : 'Create content'"></span>
      </button>
    </div>
  </header>

  <div class="bk-top-body bk-dash-body">

    <!-- KPI row -->
    <div class="bk-dash-kpis">
      <div class="bk-dash-kpi">
        <div class="bk-dash-kpi__ico" style="background:#fff5f3;color:#fc4c2f">
          <iconify-icon icon="solar:document-text-bold-duotone" width="22"></iconify-icon>
        </div>
        <div>
          <span class="bk-dash-kpi__lab" x-text="isRtl ? 'محتوای قابل ایندکس' : 'Indexable content'"></span>
          <strong class="bk-dash-kpi__val"><?php echo esc_html((string) ($state['stats']['indexable'] ?? $indexable)); ?></strong>
          <span class="bk-dash-kpi__sub"><?php echo esc_html($posts_pub); ?> مقاله · <?php echo esc_html($pages_pub); ?> برگه</span>
        </div>
      </div>
      <div class="bk-dash-kpi">
        <div class="bk-dash-kpi__ico" style="background:#ecfdf5;color:#059669">
          <iconify-icon icon="solar:chart-bold-duotone" width="22"></iconify-icon>
        </div>
        <div>
          <span class="bk-dash-kpi__lab">SEO Score</span>
          <strong class="bk-dash-kpi__val"><?php echo esc_html((string) ($state['stats']['avg_seo'] ?? $state['stats']['seo_score'] ?? '—')); ?></strong>
          <span class="bk-dash-kpi__sub" x-text="isRtl ? 'میانگین امتیاز سئو' : 'Average score'"></span>
        </div>
      </div>
      <div class="bk-dash-kpi">
        <div class="bk-dash-kpi__ico" style="background:#eff6ff;color:#2563eb">
          <iconify-icon icon="solar:server-bold-duotone" width="22"></iconify-icon>
        </div>
        <div>
          <span class="bk-dash-kpi__lab">Uptime</span>
          <strong class="bk-dash-kpi__val"><?php echo esc_html($state['stats']['uptime'] ?? '99.9%'); ?></strong>
          <span class="bk-dash-kpi__sub" x-text="isRtl ? 'پایداری سرویس' : 'Service stability'"></span>
        </div>
      </div>
      <div class="bk-dash-kpi">
        <div class="bk-dash-kpi__ico" style="background:#fff7ed;color:#ea580c">
          <iconify-icon icon="solar:bolt-bold-duotone" width="22"></iconify-icon>
        </div>
        <div>
          <span class="bk-dash-kpi__lab">TTFB</span>
          <strong class="bk-dash-kpi__val"><?php
            $ttfb = $state['stats']['avg_ttfb'] ?? $state['stats']['avg_latency'] ?? null;
            echo esc_html($ttfb !== null && $ttfb !== '' ? (is_numeric($ttfb) ? $ttfb . 'ms' : (string) $ttfb) : '—');
          ?></strong>
          <span class="bk-dash-kpi__sub" x-text="isRtl ? 'میانگین تأخیر' : 'Avg latency'"></span>
        </div>
      </div>
    </div>

    <div class="bk-dash-grid">
      <!-- Visits -->
      <div class="bk-dash-card">
        <div class="bk-dash-card__head">
          <div class="bk-dash-card__title">
            <iconify-icon icon="solar:graph-up-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
            <strong x-text="isRtl ? 'بازدید ۷ روز اخیر' : 'Visits (7 days)'"></strong>
          </div>
          <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" style="padding:6px 10px;font-size:11px" @click="loadDashboardVisits()" :disabled="dashVisitsLoading">
            <iconify-icon icon="solar:refresh-bold-duotone" width="14"></iconify-icon>
          </button>
        </div>
        <div class="bk-dash-visits">
          <div class="bk-dash-visits__num" x-text="formatDashNum(dashVisits.total)">—</div>
          <div class="bk-dash-visits__change">
            <span x-text="isRtl ? 'نسبت به هفته قبل' : 'vs previous week'"></span>
            <strong :class="(dashVisits.change||0) >= 0 ? 'is-up' : 'is-down'"
                    x-text="((dashVisits.change||0) >= 0 ? '+' : '') + (dashVisits.change||0) + '%'"></strong>
          </div>
        </div>
        <div class="bk-dash-devices">
          <div class="bk-dash-device">
            <span><iconify-icon icon="solar:monitor-bold-duotone" width="14"></iconify-icon> <span x-text="isRtl ? 'رایانه' : 'Desktop'"></span></span>
            <strong x-text="formatDashNum(dashVisits.desktop)">0</strong>
            <div class="bk-dash-bar"><i :style="'width:' + Math.min(100, (dashVisits.desktop && dashVisits.total) ? Math.round(dashVisits.desktop/dashVisits.total*100) : 0) + '%'"></i></div>
          </div>
          <div class="bk-dash-device">
            <span><iconify-icon icon="solar:smartphone-bold-duotone" width="14"></iconify-icon> <span x-text="isRtl ? 'موبایل' : 'Mobile'"></span></span>
            <strong x-text="formatDashNum(dashVisits.mobile)">0</strong>
            <div class="bk-dash-bar is-teal"><i :style="'width:' + Math.min(100, (dashVisits.mobile && dashVisits.total) ? Math.round(dashVisits.mobile/dashVisits.total*100) : 0) + '%'"></i></div>
          </div>
          <div class="bk-dash-device">
            <span><iconify-icon icon="solar:tablet-bold-duotone" width="14"></iconify-icon> <span x-text="isRtl ? 'تبلت' : 'Tablet'"></span></span>
            <strong x-text="formatDashNum(dashVisits.tablet)">0</strong>
            <div class="bk-dash-bar is-amber"><i :style="'width:' + Math.min(100, (dashVisits.tablet && dashVisits.total) ? Math.round(dashVisits.tablet/dashVisits.total*100) : 0) + '%'"></i></div>
          </div>
        </div>
        <p class="bk-dash-error" x-show="dashVisits.error" x-text="dashVisits.error"></p>
      </div>

      <!-- Quick links -->
      <div class="bk-dash-card">
        <div class="bk-dash-card__head">
          <div class="bk-dash-card__title">
            <iconify-icon icon="solar:map-arrow-right-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
            <strong x-text="isRtl ? 'میانبرهای سریع' : 'Quick actions'"></strong>
          </div>
        </div>
        <div class="bk-dash-quick">
          <button type="button" class="bk-dash-quick__item" @click="setTab('articles')">
            <span class="bk-dash-quick__ico"><iconify-icon icon="solar:document-text-bold-duotone" width="20"></iconify-icon></span>
            <span x-text="isRtl ? 'مدیریت محتوا' : 'Content'"></span>
          </button>
          <button type="button" class="bk-dash-quick__item" @click="setTab('seo-engine')">
            <span class="bk-dash-quick__ico"><iconify-icon icon="solar:magnifer-bold-duotone" width="20"></iconify-icon></span>
            <span x-text="isRtl ? 'موتور سئو' : 'SEO Engine'"></span>
          </button>
          <button type="button" class="bk-dash-quick__item" @click="setTab('ai-studio')">
            <span class="bk-dash-quick__ico"><iconify-icon icon="solar:ghost-bold-duotone" width="20"></iconify-icon></span>
            <span x-text="isRtl ? 'استودیو AI' : 'AI Studio'"></span>
          </button>
          <button type="button" class="bk-dash-quick__item" @click="setTab('speed-cache')">
            <span class="bk-dash-quick__ico"><iconify-icon icon="solar:bolt-bold-duotone" width="20"></iconify-icon></span>
            <span x-text="isRtl ? 'کش و سرعت' : 'Cache & Speed'"></span>
          </button>
          <button type="button" class="bk-dash-quick__item" @click="setTab('media-watermark')">
            <span class="bk-dash-quick__ico"><iconify-icon icon="solar:gallery-bold-duotone" width="20"></iconify-icon></span>
            <span x-text="isRtl ? 'رسانه' : 'Media'"></span>
          </button>
          <button type="button" class="bk-dash-quick__item" @click="setTab('settings-license')">
            <span class="bk-dash-quick__ico"><iconify-icon icon="solar:settings-bold-duotone" width="20"></iconify-icon></span>
            <span x-text="isRtl ? 'تنظیمات' : 'Settings'"></span>
          </button>
        </div>
      </div>

      <!-- System health -->
      <div class="bk-dash-card bk-dash-health">
        <div class="bk-dash-card__head">
          <div class="bk-dash-card__title">
            <iconify-icon icon="solar:heart-pulse-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
            <strong x-text="isRtl ? 'سلامت سیستم' : 'System health'"></strong>
          </div>
          <span class="bk-dash-badge is-ok">OK</span>
        </div>
        <div class="bk-dash-health-grid">
          <div class="bk-dash-health-row">
            <span><iconify-icon icon="solar:code-bold-duotone" width="16"></iconify-icon> PHP</span>
            <strong><?php echo esc_html($php_ver); ?></strong>
          </div>
          <div class="bk-dash-health-row">
            <span><iconify-icon icon="solar:notebook-bold-duotone" width="16"></iconify-icon> WordPress</span>
            <strong><?php echo esc_html($wp_ver); ?></strong>
          </div>
          <div class="bk-dash-health-row">
            <span><iconify-icon icon="solar:cpu-bolt-bold-duotone" width="16"></iconify-icon> Memory</span>
            <strong><?php echo esc_html($mem_limit); ?></strong>
          </div>
          <div class="bk-dash-health-row">
            <span><iconify-icon icon="solar:box-bold-duotone" width="16"></iconify-icon> Bankai</span>
            <strong>v<?php echo esc_html($plugin_ver); ?></strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Core modules -->
    <div class="bk-dash-card" style="margin-top:16px">
      <div class="bk-dash-card__head">
        <div class="bk-dash-card__title">
          <iconify-icon icon="solar:widget-4-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
          <strong x-text="isRtl ? 'ماژول‌های هسته' : 'Core modules'"></strong>
        </div>
        <span class="bk-dash-hint" x-text="isRtl ? 'فعال / غیرفعال کردن ماژول‌ها' : 'Toggle modules on or off'"></span>
      </div>
      <div class="bk-dash-mods">
        <template x-for="mod in coreModuleCards" :key="mod.key">
          <div class="bk-dash-mod" :class="{ 'is-on': moduleActive[mod.key] !== false }">
            <div class="bk-dash-mod__top">
              <div class="bk-dash-mod__ico">
                <iconify-icon :icon="mod.icon || 'solar:widget-bold-duotone'" width="20"></iconify-icon>
              </div>
              <label class="bankai-switch">
                <input type="checkbox"
                       :checked="moduleActive[mod.key] !== false"
                       @change="toggleCoreModule(mod.key, $event.target.checked)">
                <span class="bankai-slider"></span>
              </label>
            </div>
            <div class="bk-dash-mod__name" x-text="isRtl ? mod.title_fa : mod.title"></div>
            <div class="bk-dash-mod__desc" x-text="isRtl ? mod.desc_fa : mod.desc"></div>
            <div class="bk-dash-mod__status"
                 :class="moduleActive[mod.key] !== false ? 'is-on' : 'is-off'"
                 x-text="moduleActive[mod.key] !== false ? (isRtl ? 'فعال' : 'Active') : (isRtl ? 'غیرفعال' : 'Off')"></div>
          </div>
        </template>
      </div>
    </div>

  </div>
</div>
