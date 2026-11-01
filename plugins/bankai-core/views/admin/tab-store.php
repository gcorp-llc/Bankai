<?php
/**
 * Store Hub — WooCommerce products, stats, AI SEO wizard
 * @package Bankai
 */
defined('ABSPATH') || exit;

$woo           = class_exists('WooCommerce');
$woo_install   = admin_url('plugin-install.php?s=woocommerce&tab=search&type=term');
$woo_plugins   = admin_url('plugins.php');
$currency      = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
?>
<div id="tab-store"
     class="bankai-tab-pane bk-orange-shell"
     x-show="activeTab === 'store'"
     :class="{ 'is-active-tab': activeTab === 'store' }"
     x-cloak
     x-data="bankaiStoreHub()"
     x-init="init()">

  <header class="bk-top-head">
    <div class="bk-top-head__brand">
      <span class="bk-top-head__logo">
        <iconify-icon icon="solar:shop-bold-duotone" width="24" height="24"></iconify-icon>
      </span>
      <div>
        <strong>فروشگاه</strong>
        <small>محصولات ووکامرس · آمار فروش · ویزارد سئوی AI</small>
      </div>
    </div>
    <div class="bk-top-head__actions">
      <?php if ($woo) : ?>
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="openCreate()">
        <iconify-icon icon="solar:add-circle-bold-duotone" width="16"></iconify-icon>
        افزودن محصول
      </button>
      <?php endif; ?>
    </div>
  </header>

  <?php
  $woo_file = 'woocommerce/woocommerce.php';
  $woo_installed = false;
  if (!function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }
  if (function_exists('get_plugins')) {
      $all_plugins = get_plugins();
      $woo_installed = isset($all_plugins[$woo_file]) || class_exists('WooCommerce');
  }
  ?>
  <?php if (!$woo) : ?>
  <div class="bk-store-empty bankai-card">
    <div class="bk-store-empty__icon">
      <iconify-icon icon="solar:bag-cross-bold-duotone" width="56"></iconify-icon>
    </div>
    <?php if ($woo_installed) : ?>
      <h3>ووکامرس نصب است اما فعال نیست</h3>
      <p>افزونه WooCommerce روی سایت نصب شده ولی فعال نیست. برای استفاده از فروشگاه آن را فعال کنید.</p>
      <div class="bk-store-empty__actions">
        <a class="bk-wpr-btn bk-wpr-btn--primary" href="<?php echo esc_url($woo_plugins); ?>">
          <iconify-icon icon="solar:play-circle-bold-duotone" width="16"></iconify-icon>
          فعال‌سازی از مدیریت افزونه‌ها
        </a>
      </div>
    <?php else : ?>
      <h3>ووکامرس نصب نیست</h3>
      <p>برای استفاده از بخش فروشگاه، افزونه <strong>WooCommerce</strong> را نصب و فعال کنید. سپس از پیشخوان یا تنظیمات، ماژول فروشگاه را روشن کنید.</p>
      <div class="bk-store-empty__actions">
        <a class="bk-wpr-btn bk-wpr-btn--primary" href="<?php echo esc_url($woo_install); ?>">
          <iconify-icon icon="solar:download-minimalistic-bold-duotone" width="16"></iconify-icon>
          نصب ووکامرس
        </a>
        <a class="bk-wpr-btn bk-wpr-btn--secondary" href="<?php echo esc_url($woo_plugins); ?>">مدیریت افزونه‌ها</a>
      </div>
    <?php endif; ?>
  </div>
  <?php else : ?>

  <div class="bk-store-error" x-show="statsError" x-text="statsError" x-cloak></div>
  <div class="bk-store-error" x-show="productsError" x-text="productsError" x-cloak></div>

  <!-- ===== STATS ROW ===== -->
  <div class="bk-store-stats">
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico"><iconify-icon icon="solar:box-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="stats.products ?? '—'">—</span>
        <span class="bk-store-stat__l">محصول منتشرشده</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico"><iconify-icon icon="solar:graph-up-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="formatMoney(stats.sales_30d)">—</span>
        <span class="bk-store-stat__l">فروش ۳۰ روز</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico"><iconify-icon icon="solar:bill-list-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="stats.orders_30d ?? '—'">—</span>
        <span class="bk-store-stat__l">سفارش ۳۰ روز</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico"><iconify-icon icon="solar:wallet-money-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="formatMoney(stats.avg_order)">—</span>
        <span class="bk-store-stat__l">میانگین سفارش</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico is-ok"><iconify-icon icon="solar:check-circle-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="stats.in_stock ?? '—'">—</span>
        <span class="bk-store-stat__l">موجود</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico is-warn"><iconify-icon icon="solar:danger-triangle-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="stats.low_stock ?? '—'">—</span>
        <span class="bk-store-stat__l">کم‌موجود</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico is-danger"><iconify-icon icon="solar:close-circle-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="stats.out_of_stock ?? '—'">—</span>
        <span class="bk-store-stat__l">ناموجود</span>
      </div>
    </div>
    <div class="bk-store-stat">
      <div class="bk-store-stat__ico"><iconify-icon icon="solar:tag-price-bold-duotone" width="22"></iconify-icon></div>
      <div>
        <span class="bk-store-stat__n" x-text="stats.on_sale ?? '—'">—</span>
        <span class="bk-store-stat__l">دارای تخفیف</span>
      </div>
    </div>
  </div>

  <!-- Period + Chart -->
  <div class="bk-store-period-bar">
    <span>بازه آمار:</span>
    <template x-for="p in [7,30,90]" :key="p">
      <button type="button" class="bk-store-period-btn" :class="{ 'is-active': period === p }" @click="setPeriod(p)" x-text="p + ' روز'"></button>
    </template>
    <span class="bk-store-period-change" x-show="stats.sales_change !== null && stats.sales_change !== undefined">
      تغییر فروش:
      <strong :class="(stats.sales_change||0) >= 0 ? 'is-up' : 'is-down'" x-text="((stats.sales_change||0) >= 0 ? '+' : '') + (stats.sales_change ?? '—') + '%'"></strong>
    </span>
  </div>
  <div class="bk-store-chart bankai-card">
    <div class="bk-store-chart__head">
      <strong x-text="'روند فروش ' + period + ' روز اخیر'"></strong>
      <small x-text="formatMoney(stats.sales_30d) + ' مجموع'"></small>
    </div>
    <div class="bk-store-bars" x-show="(stats.series||[]).length">
      <template x-for="(p,i) in (stats.series||[])" :key="i">
        <div class="bk-store-bar" :title="(p.day||'') + ' · ' + formatMoney(p.total)">
          <i :style="'height:' + barHeight(p.total) + '%'"></i>
        </div>
      </template>
    </div>
    <div x-show="!(stats.series||[]).length" class="bk-store-chart__empty">داده‌ای برای نمودار نیست</div>
  </div>

  <!-- Toolbar -->
  <div class="bk-store-toolbar">
    <div class="bk-store-toolbar__head">
      <div class="bk-store-toolbar__title">
        <span class="bk-store-toolbar__badge"><iconify-icon icon="solar:filter-bold-duotone" width="16"></iconify-icon></span>
        <div>
          <strong>فیلتر و جستجوی محصولات</strong>
          <small>دسته‌بندی، موجودی، قیمت و وضعیت سئو</small>
        </div>
      </div>
       <div class="bk-store-toolbar__search">
      <iconify-icon icon="solar:magnifer-linear" width="18"></iconify-icon>
      <input type="search" placeholder="جستجوی نام محصول، SKU یا کد…" x-model="search" @input.debounce.400ms="page=1; loadProducts()" @keydown.enter.prevent="page=1; loadProducts()">
    </div>
      <div class="bk-store-toolbar__actions">
        <button type="button" class="bk-store-icon-btn" @click="resetFilters()" title="پاک‌کردن فیلترها">
          <iconify-icon icon="solar:restart-bold-duotone" width="18"></iconify-icon>
          <span>بازنشانی</span>
        </button>
        <button type="button" class="bk-store-icon-btn is-primary" @click="page=1; loadProducts()" title="بروزرسانی">
          <iconify-icon icon="solar:refresh-bold-duotone" width="18"></iconify-icon>
          <span>بروزرسانی</span>
        </button>
      </div>
    </div>

   

    <div class="bk-store-filters">
      <label class="bk-store-field">
        <span>وضعیت انتشار</span>
        <select x-model="status" @change="page=1; loadProducts()">
          <option value="all">همه وضعیت‌ها</option>
          <option value="publish">منتشرشده</option>
          <option value="draft">پیش‌نویس</option>
          <option value="private">خصوصی</option>
        </select>
      </label>
      <label class="bk-store-field">
        <span>موجودی</span>
        <select x-model="stock" @change="page=1; loadProducts()">
          <option value="all">همه موجودی‌ها</option>
          <option value="instock">موجود</option>
          <option value="lowstock">کم‌موجود</option>
          <option value="outofstock">ناموجود</option>
        </select>
      </label>
      <label class="bk-store-field">
        <span>نوع محصول</span>
        <select x-model="type" @change="page=1; loadProducts()">
          <option value="all">همه انواع</option>
          <option value="simple">ساده</option>
          <option value="variable">متغیر</option>
          <option value="grouped">گروهی</option>
          <option value="external">خارجی</option>
        </select>
      </label>
      <label class="bk-store-field">
        <span>تخفیف</span>
        <select x-model="on_sale" @change="page=1; loadProducts()">
          <option value="all">همه</option>
          <option value="yes">دارای تخفیف</option>
          <option value="no">بدون تخفیف</option>
        </select>
      </label>
      <label class="bk-store-field">
        <span>دسته‌بندی</span>
        <select x-model="category" @change="page=1; loadProducts()">
          <option value="0">همه دسته‌ها</option>
          <template x-for="c in categories" :key="c.id">
            <option :value="c.id" x-text="c.name + ' (' + c.count + ')'"></option>
          </template>
        </select>
      </label>
      <label class="bk-store-field">
        <span>وضعیت SEO</span>
        <select x-model="seo_filter" @change="page=1; loadProducts()">
          <option value="all">همه</option>
          <option value="needs_seo">نیازمند بهینه‌سازی</option>
          <option value="has_seo">دارای SEO</option>
        </select>
      </label>
      <label class="bk-store-field">
        <span>حداقل قیمت</span>
        <input type="number" class="bk-store-price-input" placeholder="0" x-model="price_min" @change="page=1; loadProducts()" dir="ltr" min="0">
      </label>
      <label class="bk-store-field">
        <span>حداکثر قیمت</span>
        <input type="number" class="bk-store-price-input" placeholder="∞" x-model="price_max" @change="page=1; loadProducts()" dir="ltr" min="0">
      </label>
      <label class="bk-store-field">
        <span>مرتب‌سازی</span>
        <select x-model="orderby" @change="page=1; loadProducts()">
          <option value="date">جدیدترین</option>
          <option value="oldest">قدیمی‌ترین</option>
          <option value="title">نام محصول</option>
          <option value="price_asc">قیمت ↑</option>
          <option value="price_desc">قیمت ↓</option>
          <option value="stock">موجودی</option>
          <option value="seo">امتیاز SEO</option>
        </select>
      </label>
    </div>
  </div>

  <div class="bk-store-loading" x-show="loading" x-cloak>
    <div class="bk-store-loading__orb" aria-hidden="true">
      <span class="bk-store-loading__spin"></span>
    </div>
    <div class="bk-store-loading__text">
      <strong>در حال بارگذاری محصولات…</strong>
      <span>فیلترها اعمال می‌شوند و فهرست به‌روز می‌گردد</span>
    </div>
    <div class="bk-store-loading__skeleton" aria-hidden="true">
      <i></i><i></i><i></i><i></i>
    </div>
  </div>

  <!-- Product grid -->
  <div class="bk-store-grid" x-show="!loading">
    <template x-for="item in items" :key="item.id">
      <article class="bk-store-card" :class="{ 'is-busy': wizard.id === item.id && wizard.running }">
        <!-- Wizard overlay -->
        <div class="bk-store-wiz-cover" x-show="wizard.id === item.id && wizard.running" x-cloak>
          <div class="bk-store-wiz-cover__inner">
            <span class="bk-btn-spin"></span>
            <strong x-text="wizard.stepLabel || 'در حال بهینه‌سازی…'"></strong>
            <ul class="bk-store-wiz-steps">
              <template x-for="s in wizard.steps" :key="s.id">
                <li :class="{ 'is-run': s.status==='running', 'is-done': s.status==='done', 'is-error': s.status==='error' }">
                  <span class="bk-wiz-mark" x-text="s.status==='done' ? '✓' : (s.status==='running' ? '…' : (s.status==='error' ? '!' : '○'))"></span>
                  <span x-text="s.label"></span>
                </li>
              </template>
            </ul>
          </div>
        </div>

        <div class="bk-store-card__media">
          <img :src="item.image || ''" :alt="item.name" loading="lazy" @error="$el.style.display='none'">
          <div class="bk-store-card__ph" x-show="!item.image">
            <iconify-icon icon="solar:gallery-bold-duotone" width="36"></iconify-icon>
          </div>
          <!-- Stock pin top -->
          <span class="bk-store-badge-stock"
                :class="item.in_stock ? 'is-in' : 'is-out'"
                x-text="item.in_stock ? (item.stock_qty != null ? ('موجود · ' + item.stock_qty) : 'موجود') : 'ناموجود'"></span>
          <!-- Price pin bottom -->
          <div class="bk-store-badge-price" x-show="item.price || item.regular_price">
            <template x-if="item.on_sale && item.regular_price">
              <span class="bk-store-badge-price__old" x-text="formatMoney(item.regular_price)"></span>
            </template>
            <span class="bk-store-badge-price__now" x-text="formatMoney(item.price || item.regular_price)"></span>
            <span class="bk-store-badge-price__off" x-show="item.discount > 0" x-text="item.discount + '%'"></span>
          </div>
        </div>

        <div class="bk-store-card__body">
          <h3 class="bk-store-card__title" :title="item.name" x-text="item.name"></h3>
          <div class="bk-store-card__cats" x-show="item.categories && item.categories.length">
            <template x-for="c in (item.categories || []).slice(0,3)" :key="c.id">
              <span x-text="c.name"></span>
            </template>
          </div>

          <!-- SEO scores -->
          <div class="bk-store-scores" role="group" aria-label="SEO scores">
            <div class="bk-s-score">
              <div class="bk-score-ring-wrap">
                <svg class="bk-score-ring" viewBox="0 0 36 36">
                  <circle class="track" cx="18" cy="18" r="14"></circle>
                  <circle class="val" :class="scoreTone(item.seo)" cx="18" cy="18" r="14"
                          stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(item.seo)"></circle>
                </svg>
                <span class="bk-score-ring-num" x-text="item.seo"></span>
              </div>
              <span class="bk-s-score__lbl">SEO</span>
            </div>
            <div class="bk-s-score">
              <div class="bk-score-ring-wrap">
                <svg class="bk-score-ring" viewBox="0 0 36 36">
                  <circle class="track" cx="18" cy="18" r="14"></circle>
                  <circle class="val" :class="scoreTone(item.aeo)" cx="18" cy="18" r="14"
                          stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(item.aeo)"></circle>
                </svg>
                <span class="bk-score-ring-num" x-text="item.aeo"></span>
              </div>
              <span class="bk-s-score__lbl">AEO</span>
            </div>
            <div class="bk-s-score">
              <div class="bk-score-ring-wrap">
                <svg class="bk-score-ring" viewBox="0 0 36 36">
                  <circle class="track" cx="18" cy="18" r="14"></circle>
                  <circle class="val" :class="scoreTone(item.geo)" cx="18" cy="18" r="14"
                          stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(item.geo)"></circle>
                </svg>
                <span class="bk-score-ring-num" x-text="item.geo"></span>
              </div>
              <span class="bk-s-score__lbl">GEO</span>
            </div>
            <button type="button" class="bk-store-wiz-btn" @click="startWizard(item)" :disabled="wizard.running">
              <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="14"></iconify-icon>
              ویزارد AI
            </button>
          </div>

          <!-- Short description -->
          <p class="bk-store-card__excerpt" x-show="item.short_description" x-text="item.short_description"></p>
          <p class="bk-store-card__excerpt is-muted" x-show="!item.short_description">توضیح کوتاه ثبت نشده</p>

          <!-- Actions -->
          <div class="bk-store-card__actions">
            <button type="button" class="bk-store-act bk-store-act--report" @click="openReport(item)" title="گزارش محصول">
              <iconify-icon icon="solar:chart-2-bold-duotone" width="16"></iconify-icon>
              <span>گزارش</span>
            </button>
            <a class="bk-store-act" :href="item.permalink" target="_blank" rel="noopener" title="نمایش">
              <iconify-icon icon="solar:eye-bold-duotone" width="16"></iconify-icon>
              <span>نمایش</span>
            </a>
            <a class="bk-store-act" :href="item.edit_url" target="_blank" rel="noopener" title="ویرایش">
              <iconify-icon icon="solar:pen-bold-duotone" width="16"></iconify-icon>
              <span>ویرایش</span>
            </a>
            <button type="button" class="bk-store-act bk-store-act--cover" @click="genCover(item)" :disabled="coverBusy === item.id" title="کاور AI">
              <iconify-icon icon="solar:gallery-add-bold-duotone" width="16"></iconify-icon>
              <span x-text="coverBusy === item.id ? '…' : 'کاور'"></span>
            </button>
          </div>
        </div>
      </article>
    </template>
  </div>

  <div class="bk-store-empty-list" x-show="!loading && !(items||[]).length" x-cloak>
    <iconify-icon icon="solar:box-minimalistic-bold-duotone" width="36" style="color:#cbd5e1"></iconify-icon>
    <p>محصولی یافت نشد.</p>
  </div>

  <div class="bk-store-pager" x-show="!loading && total > 0">
    <span class="bk-store-pager__total" x-text="total + ' محصول'"></span>
    <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" :disabled="page<=1" @click="page--; loadProducts()">قبلی</button>
    <span x-text="page + ' / ' + pages"></span>
    <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" :disabled="page>=pages" @click="page++; loadProducts()">بعدی</button>
  </div>

  <!-- Wizard result modal — field-level review -->
  <div class="bk-store-modal-bg" x-show="wizard.modal" x-cloak @click.self="wizard.modal=false">
    <div class="bk-store-modal bk-store-modal--wide" @click.stop>
      <div class="bk-store-modal__head">
        <div>
          <strong>بررسی پیشنهادهای هوش مصنوعی</strong>
          <small x-show="wizard.product" x-text="wizard.product && wizard.product.name" style="display:block;color:#94a3b8;font-size:11px;margin-top:2px"></small>
        </div>
        <button type="button" class="bk-store-modal__x" @click="wizard.modal=false">✕</button>
      </div>
      <div class="bk-store-modal__body">
        <div class="bk-store-wiz-summary">
          <span class="bk-store-wiz-summary__ok" x-text="wizardDoneCount() + ' موفق'"></span>
          <span class="bk-store-wiz-summary__err" x-show="wizardErrorCount() > 0" x-text="wizardErrorCount() + ' ناموفق'"></span>
          <label class="bk-store-opt-img" style="margin:0;margin-right:auto">
            <input type="checkbox" x-model="wizard.genImage">
            در پردازش مجدد، تصویر کاور هم تولید شود
          </label>
        </div>

        <div class="bk-store-conflict" x-show="wizard.conflict" x-cloak>
          <div class="bk-store-conflict__icon">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" width="22"></iconify-icon>
          </div>
          <div class="bk-store-conflict__body">
            <strong>تعارض نسخه محصول</strong>
            <p x-text="wizard.conflict && wizard.conflict.message"></p>
            <small x-show="wizard.conflict && wizard.conflict.current">
              آخرین ویرایش فعلی:
              <span x-text="wizard.conflict && wizard.conflict.current && wizard.conflict.current.modified"></span>
              —
              <span x-text="wizard.conflict && wizard.conflict.current && wizard.conflict.current.title"></span>
            </small>
            <div class="bk-store-conflict__actions">
              <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary bk-wpr-btn--sm" @click="wizard.conflict=null">انصراف</button>
              <button type="button" class="bk-wpr-btn bk-wpr-btn--primary bk-wpr-btn--sm" @click="forceApplyWizard()" :disabled="wizard.applying">
                اعمال اجباری روی نسخه فعلی
              </button>
            </div>
          </div>
        </div>

        <div class="bk-store-field-actions">
          <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary bk-wpr-btn--sm" @click="selectAllFields(true)">انتخاب همه</button>
          <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary bk-wpr-btn--sm" @click="selectAllFields(false)">لغو انتخاب همه</button>
        </div>

        <template x-for="field in wizardFields" :key="field.key">
          <div class="bk-store-field" :class="{ 'is-selected': field.selected, 'is-skipped': field.skipped }">
            <div class="bk-store-field__head">
              <label class="bk-store-field__check">
                <input type="checkbox" x-model="field.selected" :disabled="field.skipped">
                <span x-text="field.label"></span>
              </label>
              <span class="bk-store-field__badge" :class="field.skipped ? 'is-skip' : 'is-new'" x-text="field.skipped ? 'رد شده' : 'پیشنهاد جدید'"></span>
            </div>
            <div class="bk-store-field__body" x-show="!field.skipped">
              <div class="bk-store-field__col" x-show="field.current">
                <span class="bk-store-field__col-lbl">مقدار فعلی</span>
                <div class="bk-store-field__val is-current"
                     x-show="!isHtmlField(field)"
                     x-text="stripTags(field.current) || '—'"></div>
                <div class="bk-store-field__html is-current"
                     x-show="isHtmlField(field)"
                     x-html="sanitizePreview(field.current) || '—'"></div>
              </div>
              <div class="bk-store-field__col">
                <span class="bk-store-field__col-lbl">پیشنهاد AI</span>
                <template x-if="field.preview">
                  <div class="bk-store-field__preview">
                    <img :src="field.preview" alt="preview" loading="lazy">
                  </div>
                </template>
                <!-- Rendered HTML preview for rich fields -->
                <div class="bk-store-field__html"
                     x-show="!field.preview && isHtmlField(field)"
                     x-html="sanitizePreview(field.value)"></div>
                <textarea class="bk-store-field__edit" rows="3"
                          x-model="field.value"
                          :disabled="!field.selected"
                          x-show="!field.preview || field.key === 'cover_prompt'"></textarea>
              </div>
            </div>
            <div class="bk-store-field__note" x-show="field.note" x-text="field.note"></div>
          </div>
        </template>

        <div class="bk-store-empty-list" x-show="!(wizardFields||[]).length" style="padding:24px">
          <p>پیشنهادی برای نمایش وجود ندارد.</p>
        </div>
      </div>
      <div class="bk-store-modal__foot">
        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="wizard.modal=false; wizard.conflict=null">لغو</button>
        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="rerunWizard()">پردازش مجدد</button>
        <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="applyWizard(false)" :disabled="wizard.applying || !hasSelectedFields()">
          <iconify-icon icon="solar:diskette-bold-duotone" width="14"></iconify-icon>
          <span x-text="wizard.applying ? 'در حال اعمال…' : ('اعمال ' + selectedFieldCount() + ' مورد انتخاب‌شده')"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Create product modal -->
  <div class="bk-store-modal-bg" x-show="create.open" x-cloak @click.self="create.open=false">
    <div class="bk-store-modal bk-store-modal--form" @click.stop>
      <div class="bk-store-modal__head">
        <strong>افزودن محصول جدید</strong>
        <button type="button" class="bk-store-modal__x" @click="create.open=false">✕</button>
      </div>
      <div class="bk-store-modal__body">
        <label class="bk-seo-label">عنوان محصول *</label>
        <input type="text" class="bk-seo-input" x-model="create.name" placeholder="مثلاً کفش ورزشی نایک…">

        <label class="bk-seo-label">نوع محصول</label>
        <select class="bk-seo-input" x-model="create.product_type">
          <option value="simple">ساده</option>
          <option value="virtual">مجازی</option>
          <option value="downloadable">دانلودی</option>
        </select>
        <p class="bk-store-form-hint">برای محصول متغیر یا گروهی، پس از ایجاد از «ویرایش در ووکامرس» تنظیمات پیشرفته را کامل کنید.</p>

        <div class="bk-store-ai-actions">
          <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="aiComplete()" :disabled="create.aiBusy || !create.name">
            <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="14"></iconify-icon>
            <span x-text="create.aiBusy ? 'در حال تکمیل…' : 'تکمیل با هوش مصنوعی'"></span>
          </button>
          <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="genCoverPrompt()" :disabled="!create.name">
            <iconify-icon icon="solar:gallery-add-bold-duotone" width="14"></iconify-icon>
            تولید تصویر کاور
          </button>
        </div>
        <div class="bk-store-cover-hint" x-show="create.coverPrompt" x-cloak>
          <strong>پرامپت کاور:</strong>
          <span x-text="create.coverPrompt"></span>
        </div>

        <label class="bk-seo-label">توضیح کوتاه</label>
        <textarea class="bk-seo-input" rows="2" x-model="create.short_description"></textarea>
        <label class="bk-seo-label">توضیحات کامل</label>
        <textarea class="bk-seo-input" rows="4" x-model="create.description"></textarea>
        <div class="bk-store-form-row">
          <div>
            <label class="bk-seo-label">قیمت عادی</label>
            <input type="text" class="bk-seo-input" dir="ltr" x-model="create.price" placeholder="0">
          </div>
          <div>
            <label class="bk-seo-label">قیمت فروش ویژه</label>
            <input type="text" class="bk-seo-input" dir="ltr" x-model="create.sale_price" placeholder="اختیاری">
          </div>
        </div>
        <div class="bk-store-form-row">
          <div>
            <label class="bk-seo-label">SKU</label>
            <input type="text" class="bk-seo-input" dir="ltr" x-model="create.sku">
          </div>
          <div>
            <label class="bk-seo-label">وضعیت موجودی</label>
            <select class="bk-seo-input" x-model="create.stock_status">
              <option value="instock">موجود</option>
              <option value="outofstock">ناموجود</option>
            </select>
          </div>
        </div>
        <div class="bk-store-form-row">
          <div>
            <label class="bk-seo-label">
              <input type="checkbox" x-model="create.manage_stock"> مدیریت موجودی
            </label>
            <input type="number" class="bk-seo-input" dir="ltr" x-model="create.stock_qty" placeholder="تعداد" x-show="create.manage_stock" min="0">
          </div>
          <div>
            <label class="bk-seo-label">برچسب‌ها (با کاما)</label>
            <input type="text" class="bk-seo-input" x-model="create.tags" placeholder="مثلاً جدید، پرفروش">
          </div>
        </div>
        <label class="bk-seo-label">دسته‌بندی</label>
        <select class="bk-seo-input" x-model="create.category">
          <option value="0">بدون دسته</option>
          <template x-for="c in categories" :key="c.id">
            <option :value="c.id" x-text="c.name"></option>
          </template>
        </select>
        <label class="bk-seo-label">وضعیت انتشار</label>
        <select class="bk-seo-input" x-model="create.status">
          <option value="draft">پیش‌نویس</option>
          <option value="publish">انتشار فوری</option>
        </select>
      </div>
      <div class="bk-store-modal__foot">
        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="create.open=false">انصراف</button>
        <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="saveProduct()" :disabled="create.saving || !create.name">
          <span x-text="create.saving ? 'در حال ثبت…' : 'ثبت محصول در ووکامرس'"></span>
        </button>
      </div>
    </div>
  </div>


  <!-- Product CRM Report Modal -->
  <div class="bk-store-modal-bg" x-show="report.open" x-cloak @click.self="report.open=false">
    <div class="bk-store-modal bk-store-modal--wide">
      <div class="bk-store-modal__head">
        <div>
          <strong>گزارش محصول</strong>
          <small style="display:block;color:#94a3b8;font-size:11px;margin-top:2px" x-text="report.data && report.data.product ? report.data.product.name : ''"></small>
        </div>
        <button type="button" class="bk-store-modal__x" @click="report.open=false">✕</button>
      </div>
      <div class="bk-store-modal__body">
        <div x-show="report.loading" class="bk-store-loading"><span class="bk-btn-spin"></span> در حال بارگذاری گزارش…</div>
        <template x-if="!report.loading && report.data">
          <div class="bk-store-report">
            <div class="bk-store-report__grid">
              <div class="bk-store-report__stat">
                <span class="bk-store-report__n" x-text="formatMoney(report.data.sold_total_30d)"></span>
                <span class="bk-store-report__l">فروش ۳۰ روز</span>
              </div>
              <div class="bk-store-report__stat">
                <span class="bk-store-report__n" x-text="report.data.sold_qty_30d ?? 0"></span>
                <span class="bk-store-report__l">تعداد فروش</span>
              </div>
              <div class="bk-store-report__stat">
                <span class="bk-store-report__n" x-text="report.data.views ?? 0"></span>
                <span class="bk-store-report__l">بازدید</span>
              </div>
              <div class="bk-store-report__stat">
                <span class="bk-store-report__n" x-text="formatMoney(report.data && report.data.product && (report.data.product.price || report.data.product.regular_price))"></span>
                <span class="bk-store-report__l">قیمت فعلی</span>
              </div>
            </div>

            <div class="bk-store-report__section">
              <strong>روند فروش ۳۰ روز اخیر</strong>
              <div class="bk-store-bars" style="height:80px;margin-top:10px">
                <template x-for="(b,i) in (report.data.series || [])" :key="i">
                  <div class="bk-store-bar" :title="b.day + ': ' + b.qty">
                    <i :style="'height:' + reportBarH(b) + '%'"></i>
                  </div>
                </template>
              </div>
            </div>

            <div class="bk-store-report__cols">
              <div class="bk-store-report__section">
                <strong>موجودی و انبار</strong>
                <ul class="bk-store-report__list">
                  <li>وضعیت: <b x-text="report.data && report.data.product && report.data.product.in_stock ? 'موجود' : 'ناموجود'"></b></li>
                  <li>مدیریت موجودی: <b x-text="report.data.manage_stock ? 'فعال' : 'غیرفعال'"></b></li>
                  <li>تعداد انبار: <b x-text="report.data.stock_quantity != null ? report.data.stock_quantity : '—'"></b></li>
                  <li>SKU: <b x-text="(report.data && report.data.product && report.data.product.sku) || '—'"></b></li>
                </ul>
              </div>
              <div class="bk-store-report__section">
                <strong>دسته‌ها و برچسب‌ها</strong>
                <div class="bk-store-report__tags">
                  <template x-for="c in (report.data && report.data.product && report.data.product.categories) || []" :key="'c'+c.id">
                    <span x-text="c.name"></span>
                  </template>
                  <template x-for="t in (report.data && report.data.product && report.data.product.tags) || []" :key="'t'+t.id">
                    <span class="is-tag" x-text="t.name"></span>
                  </template>
                </div>
              </div>
            </div>

            <div class="bk-store-report__cols">
              <div class="bk-store-report__section">
                <strong>روش‌های پرداخت فعال</strong>
                <ul class="bk-store-report__list">
                  <template x-for="(g,i) in (report.data.payment_methods || [])" :key="i">
                    <li x-text="g"></li>
                  </template>
                  <li x-show="!(report.data.payment_methods || []).length">—</li>
                </ul>
              </div>
              <div class="bk-store-report__section">
                <strong>مناطق ارسال</strong>
                <ul class="bk-store-report__list">
                  <template x-for="(z,i) in (report.data.shipping || [])" :key="i">
                    <li>
                      <b x-text="z.zone"></b>
                      <span x-text="(z.methods || []).join('، ')"></span>
                    </li>
                  </template>
                  <li x-show="!(report.data.shipping || []).length">—</li>
                </ul>
              </div>
            </div>

            <div class="bk-store-report__section">
              <strong>امتیاز سئو</strong>
              <div class="bk-store-scores" style="justify-content:flex-start;margin-top:8px">
                <div class="bk-s-score">
                  <span class="bk-score-ring-num" style="position:static" x-text="report.data && report.data.product && report.data.product.seo"></span>
                  <span class="bk-s-score__lbl">SEO</span>
                </div>
                <div class="bk-s-score">
                  <span class="bk-score-ring-num" style="position:static" x-text="report.data && report.data.product && report.data.product.aeo"></span>
                  <span class="bk-s-score__lbl">AEO</span>
                </div>
                <div class="bk-s-score">
                  <span class="bk-score-ring-num" style="position:static" x-text="report.data && report.data.product && report.data.product.geo"></span>
                  <span class="bk-s-score__lbl">GEO</span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
      <div class="bk-store-modal__foot">
        <a class="bk-wpr-btn bk-wpr-btn--secondary" x-show="report.data && report.data.product" :href="(report.data && report.data.product && report.data.product.edit_url) || '#'" target="_blank" rel="noopener">ویرایش در ووکامرس</a>
        <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="report.open=false">بستن</button>
      </div>
    </div>
  </div>

  <?php endif; ?>
</div>
