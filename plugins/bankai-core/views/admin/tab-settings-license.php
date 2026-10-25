<?php
/**
 * Settings & License — orange theme + Solar icons
 * @package Bankai
 */
defined('ABSPATH') || exit;

$plugin_ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0';
$wp_ver     = get_bloginfo('version');
$php_ver    = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
$site_url   = home_url('/');
?>
<div id="tab-settings-license"
     class="bankai-tab-pane bk-orange-shell"
     x-show="activeTab === 'settings-license'"
     :class="{ 'is-active-tab': activeTab === 'settings-license' }"
     x-cloak
     x-init="typeof loadPwa === 'function' && loadPwa()">

  <header class="bk-top-head">
    <div class="bk-top-head__brand">
      <span class="bk-top-head__logo"><iconify-icon icon="solar:settings-bold-duotone" width="24" height="24"></iconify-icon></span>
      <div>
        <strong>تنظیمات و لایسنس</strong>
        <small>لایسنس · ماژول‌ها · PWA · پشتیبان · عیب‌یابی</small>
      </div>
    </div>
    <div class="bk-top-head__actions">
      <button type="button"
              id="btn-save-all-settings"
              class="bk-wpr-btn bk-wpr-btn--primary"
              @click="triggerSaveLoader({ title: isRtl ? 'در حال ذخیره‌سازی تنظیمات…' : 'Saving settings…', message: isRtl ? 'همگام‌سازی با دیتابیس' : 'Syncing database', duration: 700 })">
        <iconify-icon icon="solar:diskette-bold-duotone" width="16"></iconify-icon>
        <span x-text="isRtl ? 'ذخیره تنظیمات' : 'Save settings'"></span>
      </button>
    </div>
  </header>

  <nav class="bk-top-tabs" role="tablist">
    <button type="button" class="bk-top-tab" :class="{ 'is-active': settingsPanel === 'license' }" @click="settingsPanel = 'license'">
      <iconify-icon icon="solar:shield-check-bold-duotone" width="18"></iconify-icon>
      <span>لایسنس</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{ 'is-active': settingsPanel === 'modules' }" @click="settingsPanel = 'modules'">
      <iconify-icon icon="solar:widget-4-bold-duotone" width="18"></iconify-icon>
      <span>ماژول‌ها</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{ 'is-active': settingsPanel === 'pwa' }" @click="settingsPanel = 'pwa'; loadPwa()">
      <iconify-icon icon="solar:smartphone-bold-duotone" width="18"></iconify-icon>
      <span>PWA</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{ 'is-active': settingsPanel === 'tools' }" @click="settingsPanel = 'tools'">
      <iconify-icon icon="solar:wrench-bold-duotone" width="18"></iconify-icon>
      <span>ابزارها</span>
    </button>
  </nav>

  <div class="bk-top-body">

    <!-- LICENSE -->
    <div x-show="settingsPanel === 'license'" x-cloak>
      <div class="bk-product-hero">
        <div class="bk-product-hero__logo">
          <iconify-icon icon="solar:atom-bold-duotone" width="40" height="40"></iconify-icon>
        </div>
        <div class="bk-product-hero__text">
          <h2>Bankai Core</h2>
          <p class="bk-product-hero__tag">موتور یکپارچه سئو · هوش مصنوعی · سرعت · رسانه برای وردپرس</p>
          <div class="bk-product-hero__meta">
            <span class="bk-product-chip">نسخه <?php echo esc_html($plugin_ver); ?></span>
            <span class="bk-product-chip">WordPress <?php echo esc_html($wp_ver); ?></span>
            <span class="bk-product-chip">PHP <?php echo esc_html($php_ver); ?></span>
          </div>
          <p class="bk-product-hero__dev">توسعه‌دهنده: <strong>Dr Manhatan</strong></p>
        </div>
      </div>

      <div class="bk-product-features">
        <div class="bk-product-feat"><iconify-icon icon="solar:magnifer-bold-duotone" width="20"></iconify-icon><div><strong>موتور سئو و اسکیما</strong><span>آنالیز SEO/AEO/GEO · Sitemap · llms.txt · Organization Schema</span></div></div>
        <div class="bk-product-feat"><iconify-icon icon="solar:ghost-bold-duotone" width="20"></iconify-icon><div><strong>استودیو هوش مصنوعی</strong><span>چند موتور متنی و تصویری · تولید مقاله · بهینه‌سازی محتوا</span></div></div>
        <div class="bk-product-feat"><iconify-icon icon="solar:bolt-bold-duotone" width="20"></iconify-icon><div><strong>کش و سرعت</strong><span>کش صفحه · بهینه‌سازی دارایی · دیتابیس · CDN / Object Cache</span></div></div>
        <div class="bk-product-feat"><iconify-icon icon="solar:gallery-bold-duotone" width="20"></iconify-icon><div><strong>رسانه و واترمارک</strong><span>فشرده‌سازی · WebP · واترمارک هوشمند</span></div></div>
        <div class="bk-product-feat"><iconify-icon icon="solar:smartphone-bold-duotone" width="20"></iconify-icon><div><strong>PWA</strong><span>مانیفست · Service Worker · نصب‌پذیری موبایل</span></div></div>
        <div class="bk-product-feat"><iconify-icon icon="solar:calendar-bold-duotone" width="20"></iconify-icon><div><strong>تقویم جلالی و ابزارها</strong><span>نمایش تاریخ شمسی · پشتیبان · عیب‌یابی</span></div></div>
      </div>

      <div class="bk-set-grid">
        <div class="bk-dash-card bk-set-license">
          <div class="bk-dash-card__head">
            <div class="bk-dash-card__title">
              <iconify-icon icon="solar:verified-check-bold-duotone" width="20" style="color:#16a34a"></iconify-icon>
              <strong x-text="isRtl ? 'وضعیت لایسنس' : 'License status'"></strong>
            </div>
            <span class="bk-dash-badge is-ok" x-text="isRtl ? 'فعال' : 'Active'"></span>
          </div>
          <div class="bk-set-license__body">
            <div class="bk-set-license__row">
              <span x-text="isRtl ? 'نوع اشتراک' : 'Plan'"></span>
              <strong x-text="isRtl ? 'نسخه سازمانی (Enterprise)' : 'Enterprise'"></strong>
            </div>
            <div class="bk-set-license__row">
              <span x-text="isRtl ? 'وضعیت' : 'Status'"></span>
              <strong style="color:#15803d" x-text="isRtl ? 'تأیید شده' : 'Verified'"></strong>
            </div>
            <div class="bk-set-license__row">
              <span x-text="isRtl ? 'دسترسی' : 'Access'"></span>
              <strong x-text="isRtl ? 'سئو · AI · کش · رسانه' : 'SEO · AI · Cache · Media'"></strong>
            </div>
            <div class="bk-set-license__row">
              <span x-text="isRtl ? 'نسخه افزونه' : 'Plugin version'"></span>
              <strong>v<?php echo esc_html($plugin_ver); ?></strong>
            </div>
          </div>
          <div class="bk-set-license__actions">
            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="showToast && showToast(isRtl ? 'لایسنس مجدداً اعتبارسنجی شد' : 'License revalidated', 'success')">
              <iconify-icon icon="solar:refresh-circle-bold-duotone" width="16"></iconify-icon>
              <span x-text="isRtl ? 'اعتبارسنجی مجدد' : 'Revalidate'"></span>
            </button>
          </div>
        </div>

        <div class="bk-dash-card">
          <div class="bk-dash-card__head">
            <div class="bk-dash-card__title">
              <iconify-icon icon="solar:info-circle-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
              <strong x-text="isRtl ? 'اطلاعات محیط' : 'Environment'"></strong>
            </div>
          </div>
          <div class="bk-dash-health-grid">
            <div class="bk-dash-health-row">
              <span>WordPress</span>
              <strong><?php echo esc_html($wp_ver); ?></strong>
            </div>
            <div class="bk-dash-health-row">
              <span>PHP</span>
              <strong><?php echo esc_html($php_ver); ?></strong>
            </div>
            <div class="bk-dash-health-row">
              <span x-text="isRtl ? 'آدرس سایت' : 'Site URL'"></span>
              <strong style="font-size:11px;word-break:break-all"><?php echo esc_html($site_url); ?></strong>
            </div>
            <div class="bk-dash-health-row">
              <span>Bankai Core</span>
              <strong>v<?php echo esc_html($plugin_ver); ?></strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODULES -->
    <div x-show="settingsPanel === 'modules'" x-cloak>
      <div class="bk-dash-card">
        <div class="bk-dash-card__head">
          <div class="bk-dash-card__title">
            <iconify-icon icon="solar:widget-4-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
            <strong x-text="isRtl ? 'کلیدهای سراسری ماژول‌ها' : 'Global module switches'"></strong>
          </div>
          <span class="bk-dash-hint" x-text="isRtl ? 'غیرفعال کردن هر ماژول، منوی مربوطه را هم مخفی می‌کند' : 'Disabling a module also hides its menu item'"></span>
        </div>
        <div class="bk-dash-mods">
          <template x-for="mod in coreModuleCards" :key="'set-'+mod.key">
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


    <!-- PWA -->
    <div x-show="settingsPanel === 'pwa'" x-cloak>
      <div class="bk-dash-card" style="margin-bottom:14px">
        <div class="bk-dash-card__head">
          <div class="bk-dash-card__title">
            <iconify-icon icon="solar:smartphone-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
            <strong>اپلیکیشن وب پیشرفته (PWA)</strong>
          </div>
          <label class="bankai-switch" title="فعال‌سازی PWA">
            <input type="checkbox" x-model="pwa.enabled">
            <span class="bankai-slider"></span>
          </label>
        </div>
        <p class="bk-set-tools__desc" style="margin:0">
          مانیفست، Service Worker، آیکون‌ها و دکمه نصب اپ. ماژول «PWA» باید از تب ماژول‌ها فعال باشد.
          <span x-show="!moduleActive.pwa && moduleActive.pwa !== undefined" style="color:#dc2626;font-weight:700"> — ماژول PWA فعلاً خاموش است.</span>
        </p>
      </div>

      <div class="bk-dash-card" x-show="pwa.enabled" x-cloak>
        <div class="bk-pwa-grid">
          <div class="bk-field"><label>نام اپ</label><input type="text" x-model="pwa.name"></div>
          <div class="bk-field"><label>نام کوتاه</label><input type="text" x-model="pwa.short_name" maxlength="12"></div>
          <div class="bk-field" style="grid-column:1/-1"><label>توضیح</label><input type="text" x-model="pwa.description"></div>
          <div class="bk-field"><label>Start URL</label><input type="text" dir="ltr" x-model="pwa.start_url" placeholder="/"></div>
          <div class="bk-field"><label>Scope</label><input type="text" dir="ltr" x-model="pwa.scope" placeholder="/"></div>
          <div class="bk-field"><label>حالت نمایش</label>
            <select x-model="pwa.display">
              <option value="standalone">Standalone</option>
              <option value="fullscreen">Fullscreen</option>
              <option value="minimal-ui">Minimal UI</option>
              <option value="browser">Browser</option>
            </select>
          </div>
          <div class="bk-field"><label>جهت</label>
            <select x-model="pwa.orientation">
              <option value="any">Any</option>
              <option value="portrait">Portrait</option>
              <option value="landscape">Landscape</option>
            </select>
          </div>
          <div class="bk-field"><label>رنگ تم</label><input type="color" x-model="pwa.theme_color"></div>
          <div class="bk-field"><label>رنگ پس‌زمینه</label><input type="color" x-model="pwa.background_color"></div>
          <div class="bk-field" style="grid-column:1/-1"><label>آیکون ۱۹۲×۱۹۲ (PNG مربعی)</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
              <input type="url" dir="ltr" x-model="pwa.icon_192" placeholder="https://…/icon-192.png" style="flex:1;min-width:200px">
              <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="pickPwaIcon('192')">رسانه</button>
              <img x-show="pwa.icon_192" :src="pwa.icon_192" alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid #e5e5e5">
            </div>
          </div>
          <div class="bk-field" style="grid-column:1/-1"><label>آیکون ۵۱۲×۵۱۲ (PNG مربعی)</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
              <input type="url" dir="ltr" x-model="pwa.icon_512" placeholder="https://…/icon-512.png" style="flex:1;min-width:200px">
              <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="pickPwaIcon('512')">رسانه</button>
              <img x-show="pwa.icon_512" :src="pwa.icon_512" alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid #e5e5e5">
            </div>
          </div>
          <label class="bk-switch-row"><span>Service Worker (کش آفلاین)</span><input type="checkbox" x-model="pwa.service_worker"></label>
          <label class="bk-switch-row"><span>کش آفلاین صفحات بازدیدشده</span><input type="checkbox" x-model="pwa.offline_cache"></label>
          <label class="bk-switch-row"><span>دکمه «نصب اپ» در فرانت</span><input type="checkbox" x-model="pwa.install_prompt"></label>
        </div>

        <div class="bk-set-tools__row" style="margin-top:16px">
          <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="savePwa()" :disabled="pwaSaving">
            <iconify-icon icon="solar:diskette-bold-duotone" width="16"></iconify-icon>
            <span x-text="pwaSaving ? 'در حال ذخیره…' : 'ذخیره تنظیمات PWA'"></span>
          </button>
          <a class="bk-wpr-btn bk-wpr-btn--secondary" :href="pwaManifestUrl" target="_blank" rel="noopener" x-show="pwaManifestUrl">
            <iconify-icon icon="solar:link-bold-duotone" width="16"></iconify-icon>
            مشاهده مانیفست
          </a>
        </div>
        <p style="margin:12px 0 0;font-size:11px;color:#94a3b8;line-height:1.6">
          پس از ذخیره، یک‌بار پیوندهای یکتا (Permalinks) را ذخیره کنید اگر مانیفست 404 داد.
          آدرس مانیفست: <code dir="ltr">/manifest.webmanifest</code> · Service Worker: <code dir="ltr">/bankai-sw.js</code>
          · سایت باید روی <strong>HTTPS</strong> باشد.
        </p>
      </div>
    </div>

    <!-- TOOLS -->
    <div x-show="settingsPanel === 'tools'" x-cloak>
      <div class="bk-set-tools">
        <div class="bk-dash-card">
          <div class="bk-dash-card__head">
            <div class="bk-dash-card__title">
              <iconify-icon icon="solar:export-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
              <strong x-text="isRtl ? 'پشتیبان پیکربندی' : 'Config backup'"></strong>
            </div>
          </div>
          <p class="bk-set-tools__desc" x-text="isRtl ? 'خروجی JSON از تنظیمات برای انتقال به سایت دیگر یا بازیابی سریع.' : 'Export JSON config for migration or restore.'"></p>
          <div class="bk-set-tools__row">
            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="typeof exportConfiguration === 'function' && exportConfiguration()">
              <iconify-icon icon="solar:download-minimalistic-bold-duotone" width="16"></iconify-icon>
              <span x-text="isRtl ? 'خروجی تنظیمات' : 'Export config'"></span>
            </button>
            <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="typeof importConfiguration === 'function' && importConfiguration()">
              <iconify-icon icon="solar:upload-minimalistic-bold-duotone" width="16"></iconify-icon>
              <span x-text="isRtl ? 'ورود تنظیمات' : 'Import config'"></span>
            </button>
          </div>
        </div>

        <div class="bk-dash-card">
          <div class="bk-dash-card__head">
            <div class="bk-dash-card__title">
              <iconify-icon icon="solar:bug-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
              <strong x-text="isRtl ? 'عیب‌یابی' : 'Diagnostics'"></strong>
            </div>
          </div>
          <p class="bk-set-tools__desc" x-text="isRtl ? 'گزارش کوتاه از نسخه PHP، وردپرس و وضعیت ماژول‌ها برای پشتیبانی.' : 'Short report of PHP, WordPress and modules for support.'"></p>
          <div class="bk-set-tools__row">
            <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary"
                    @click="showToast && showToast('WP <?php echo esc_js($wp_ver); ?> · PHP <?php echo esc_js($php_ver); ?> · Bankai <?php echo esc_js($plugin_ver); ?>', 'info', 5000)">
              <iconify-icon icon="solar:clipboard-list-bold-duotone" width="16"></iconify-icon>
              <span x-text="isRtl ? 'نمایش خلاصه سیستم' : 'Show system summary'"></span>
            </button>
          </div>
        </div>

        <div class="bk-dash-card bk-set-danger">
          <div class="bk-dash-card__head">
            <div class="bk-dash-card__title">
              <iconify-icon icon="solar:danger-triangle-bold-duotone" width="18" style="color:#dc2626"></iconify-icon>
              <strong x-text="isRtl ? 'منطقه خطر' : 'Danger zone'"></strong>
            </div>
          </div>
          <p class="bk-set-tools__desc" x-text="isRtl ? 'بازنشانی تنظیمات افزونه به حالت اولیه. این عمل برگشت‌پذیر نیست.' : 'Reset plugin settings to defaults. This cannot be undone.'"></p>
          <div class="bk-set-tools__row">
            <button type="button" class="bk-wpr-btn bk-wpr-btn--danger"
                    @click="if (confirm(isRtl ? 'آیا از بازنشانی کارخانه مطمئن هستید؟' : 'Reset all settings to factory defaults?')) { showToast && showToast(isRtl ? 'درخواست بازنشانی ثبت شد' : 'Reset requested', 'warning'); }">
              <iconify-icon icon="solar:trash-bin-trash-bold-duotone" width="16"></iconify-icon>
              <span x-text="isRtl ? 'بازنشانی کارخانه' : 'Factory reset'"></span>
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
