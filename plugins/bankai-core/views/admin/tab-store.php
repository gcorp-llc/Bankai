<?php
/**
 * Store Hub — WooCommerce products, stats, AI SEO wizard
 * @package Bankai
 */
defined('ABSPATH') || exit;

$woo_active      = class_exists('WooCommerce');
$plugin_dir      = defined('WP_PLUGIN_DIR') ? WP_PLUGIN_DIR : (dirname(BANKAI_CORE_DIR));
$woo_installed   = $woo_active || file_exists($plugin_dir . '/woocommerce/woocommerce.php');
$woo_install_url = admin_url('plugin-install.php?s=woocommerce&tab=search&type=term');
$woo_plugins_url = admin_url('plugins.php');
$currency        = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
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
      <?php if ($woo_active) : ?>
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="openCreate()">
        <iconify-icon icon="solar:add-circle-bold-duotone" width="16"></iconify-icon>
        افزودن محصول
      </button>
      <?php endif; ?>
    </div>
  </header>

  <?php if (!$woo_active) : ?>
  <div class="bk-store-empty bankai-card">
    <div class="bk-store-empty__icon">
      <iconify-icon icon="solar:bag-cross-bold-duotone" width="56"></iconify-icon>
    </div>
    <?php if ($woo_installed) : ?>
      <h3>ووکامرس نصب است اما غیرفعال است</h3>
      <p>افزونه <strong>WooCommerce</strong> روی سایت شما نصب شده است اما فعال نیست. برای استفاده از امکانات مدیریت فروشگاه هوشمند بنکای، ابتدا ووکامرس را فعال کنید.</p>
      <div class="bk-store-empty__actions">
        <a class="bk-wpr-btn bk-wpr-btn--primary" href="<?php echo esc_url($woo_plugins_url); ?>">
          <iconify-icon icon="solar:check-circle-bold-duotone" width="16"></iconify-icon>
          فعال‌سازی ووکامرس
        </a>
      </div>
    <?php else : ?>
      <h3>ووکامرس نصب نیست</h3>
      <p>برای راه‌اندازی و استفاده از ماژول مدیریت فروشگاه هوشمند بنکای، ابتدا باید افزونه <strong>WooCommerce</strong> را نصب و فعال کنید.</p>
      <div class="bk-store-empty__actions">
        <a class="bk-wpr-btn bk-wpr-btn--primary" href="<?php echo esc_url($woo_install_url); ?>">
          <iconify-icon icon="solar:download-minimalistic-bold-duotone" width="16"></iconify-icon>
          نصب ووکامرس
        </a>
        <a class="bk-wpr-btn bk-wpr-btn--secondary" href="<?php echo esc_url($woo_plugins_url); ?>">مدیریت افزونه‌ها</a>
      </div>
    <?php endif; ?>
  </div>
  <?php else : ?>

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

  <!-- Chart -->
  <div class="bk-store-chart bankai-card">
    <div class="bk-store-chart__head">
      <strong>روند فروش ۳۰ روز اخیر</strong>
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
  <div class="bk-store-toolbar bankai-card">
    <div class="bk-store-search">
      <iconify-icon icon="solar:magnifer-linear" width="16"></iconify-icon>
      <input type="search" placeholder="جستجوی نام، SKU…" x-model="search" @input.debounce.400ms="page=1; loadProducts()" @keydown.enter.prevent="page=1; loadProducts()">
    </div>
    <select class="bk-store-select" x-model="status" @change="page=1; loadProducts()">
      <option value="all">همه وضعیت‌ها</option>
      <option value="publish">منتشرشده</option>
      <option value="draft">پیش‌نویس</option>
      <option value="private">خصوصی</option>
    </select>
    <select class="bk-store-select" x-model="stock" @change="page=1; loadProducts()">
      <option value="all">همه موجودی‌ها</option>
      <option value="instock">موجود</option>
      <option value="lowstock">کم‌موجود</option>
      <option value="outofstock">ناموجود</option>
    </select>
    <select class="bk-store-select" x-model="type" @change="page=1; loadProducts()">
      <option value="all">همه انواع</option>
      <option value="simple">ساده</option>
      <option value="variable">متغیر</option>
      <option value="grouped">گروهی</option>
      <option value="external">خارجی</option>
    </select>
    <select class="bk-store-select" x-model="on_sale" @change="page=1; loadProducts()">
      <option value="all">تخفیف: همه</option>
      <option value="yes">دارای تخفیف</option>
      <option value="no">بدون تخفیف</option>
    </select>
    <select class="bk-store-select" x-model="orderby" @change="page=1; loadProducts()">
      <option value="date">جدیدترین</option>
      <option value="title">نام محصول</option>
      <option value="price_asc">قیمت ↑</option>
      <option value="price_desc">قیمت ↓</option>
      <option value="stock">موجودی</option>
    </select>
    <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="resetFilters()" title="پاک‌کردن فیلترها">
      <iconify-icon icon="solar:restart-bold-duotone" width="16"></iconify-icon>
    </button>
    <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="page=1; loadProducts()" title="بروزرسانی">
      <iconify-icon icon="solar:refresh-bold-duotone" width="16"></iconify-icon>
    </button>
  </div>

  <div class="bk-store-loading" x-show="loading" x-cloak>
    <span class="bk-btn-spin" style="width:22px;height:22px;border-width:3px;border-color:#fc4c2f transparent"></span>
    در حال بارگذاری محصولات…
  </div>

  <!-- Product grid -->
  <div class="bk-store-grid" x-show="!loading">
    <template x-for="item in items" :key="item.id">
      <article class="bk-store-card" :class="{ 'is-busy': wizard.id === item.id && wizard.running }">
        <div class="bk-store-card__media">
          <img :src="item.image" :alt="item.name" loading="lazy">
          <!-- Transparent price / stock overlay -->
          <div class="bk-store-card__glass">
            <div class="bk-store-card__price-block">
              <span class="bk-store-card__price" x-html="item.price_html || formatMoney(item.price)"></span>
              <span class="bk-store-card__disc" x-show="item.discount > 0" x-text="'-' + item.discount + '%'"></span>
            </div>
            <span class="bk-store-card__stock" :class="item.in_stock ? 'is-in' : 'is-out'"
                  x-text="item.in_stock ? 'موجود' : 'ناموجود'"></span>
          </div>
        </div>

        <div class="bk-store-card__body">
          <h3 class="bk-store-card__title" :title="item.name" x-text="item.name"></h3>
          <div class="bk-store-card__meta" x-show="item.sku">
            <span class="bk-store-sku" x-text="'SKU: ' + item.sku"></span>
          </div>

          <div class="bk-store-card__foot">
            <div class="bk-store-scores" role="group" aria-label="SEO scores">
              <div class="bk-s-score">
                <div class="bk-score-ring-wrap">
                  <svg class="bk-score-ring" viewBox="0 0 36 36">
                    <circle class="track" cx="18" cy="18" r="14"></circle>
                    <circle class="val" :class="scoreTone(item.seo)" cx="18" cy="18" r="14"
                            stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(item.seo)"></circle>
                  </svg>
                  <span class="bk-score-ring-num" :class="scoreTone(item.seo)" x-text="item.seo"></span>
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
                  <span class="bk-score-ring-num" :class="scoreTone(item.aeo)" x-text="item.aeo"></span>
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
                  <span class="bk-score-ring-num" :class="scoreTone(item.geo)" x-text="item.geo"></span>
                </div>
                <span class="bk-s-score__lbl">GEO</span>
              </div>
            </div>

            <button type="button" class="bk-store-wiz-btn" @click="startWizard(item)" :disabled="wizard.running">
              <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="16"></iconify-icon>
              بهینه‌سازی با هوش مصنوعی
            </button>
          </div>
        </div>

        <!-- Full-card wizard blur overlay -->
        <div class="bk-store-wiz-cover" x-show="wizard.id === item.id && wizard.running" x-cloak>
          <div class="bk-store-wiz-cover__inner">
            <span class="bk-btn-spin" style="width:22px;height:22px;border-width:2px;border-color:#fff transparent"></span>
            <strong x-text="wizard.currentLabel || 'در حال پردازش…'"></strong>
            <ul class="bk-store-wiz-steps">
              <template x-for="s in wizard.steps" :key="s.id">
                <li :class="'is-' + s.status">
                  <span class="bk-wiz-mark" x-text="s.status === 'done' ? '✓' : (s.status === 'run' ? '◉' : (s.status === 'error' ? '!' : '○'))"></span>
                  <span x-text="s.label"></span>
                </li>
              </template>
            </ul>
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
  <div class="bk-store-modal-bg" x-show="wizard.modal" x-cloak style="display:none" @click.self="wizard.modal=false">
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
              <span x-text="wizard.conflict.current && wizard.conflict.current.modified"></span>
              —
              <span x-text="wizard.conflict.current && wizard.conflict.current.title"></span>
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
                <div class="bk-store-field__val is-current" x-text="field.current || '—'"></div>
              </div>
              <div class="bk-store-field__col">
                <span class="bk-store-field__col-lbl">پیشنهاد AI</span>
                <template x-if="field.preview">
                  <div class="bk-store-field__preview">
                    <img :src="field.preview" alt="preview" loading="lazy">
                  </div>
                </template>
                <textarea class="bk-store-field__edit" rows="3" x-model="field.value" :disabled="!field.selected" x-show="!field.preview || field.key === 'cover_prompt'"></textarea>
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
  <div class="bk-store-modal-bg" x-show="create.open" x-cloak style="display:none" @click.self="create.open=false">
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

  <?php endif; ?>
</div>
