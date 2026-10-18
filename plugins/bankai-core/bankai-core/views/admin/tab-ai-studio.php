<?php
/**
 * Tab: AI Studio — Bankai Multi-LLM Control Center (Fluent redesign)
 * Layout inspired by product mockups: engines / custom agents / sandbox / modules
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

$studio      = Bankai_AI_Studio::instance();
$catalog     = Bankai_AI_Studio::all_providers_catalog();
$custom_raw  = Bankai_AI_Studio::get_custom_providers_raw();
$keys        = $studio->get_keys();
$models      = $studio->get_selected_models();
$default_p   = $studio->get_default_provider();
$status_list = $studio->provider_status_list();
$status_map  = [];
foreach ($status_list as $row) {
    $status_map[$row['id']] = $row;
}
$ready_count = 0;
foreach ($status_list as $row) {
    if (!empty($row['has_key'])) {
        $ready_count++;
    }
}
$total_providers = max(1, count($catalog));
$ready_pct = round(($ready_count / $total_providers) * 100, 1);

$ai_mods = [
    [
        'id' => 'auto_seo_meta',
        'title_fa' => 'سئو خودکار و اسکیمای هوشمند',
        'description_fa' => 'تولید برخط برچسب‌های عنوان، توضیحات متا و استراتژی‌های JSON-LD هنگام انتشار پست.',
        'badge' => 'SEO Engine',
        'icon' => 'travel_explore',
        'color' => '#0078d4',
    ],
    [
        'id' => 'alt_generator',
        'title_fa' => 'تولید هوشمند Alt-Text تصاویر',
        'description_fa' => 'آنالیز بصری چندوجهی تصاویر کتابخانه و تزریق مستقیم به دیتابیس پست‌متا.',
        'badge' => 'Media AI',
        'icon' => 'image',
        'color' => '#0f7b3a',
    ],
    [
        'id' => 'content_expander',
        'title_fa' => 'دستیار بلوک گوتنبرگ',
        'description_fa' => 'افزودن دکمه‌های ری‌رایت، لحن رسمی/محاوره‌ای و گسترش پاراگراف در تولبار ویرایشگر.',
        'badge' => 'Editor Kit',
        'icon' => 'edit_note',
        'color' => '#6b4eff',
    ],
];
$saved_mods = get_option(Bankai_AI_Studio::OPT_MODULES, []);
if (!is_array($saved_mods)) {
    $saved_mods = [];
}
$nonce = wp_create_nonce('bankai_admin_nonce');
$default_name = $catalog[$default_p]['name'] ?? $catalog[$default_p]['label'] ?? $default_p;
$version = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.19';
$image_catalog = Bankai_AI_Studio::image_providers_catalog();
$image_keys = $studio->get_image_keys();
$image_models = $studio->get_image_models();
$image_default = $studio->get_default_image_provider();
$image_status = $studio->image_provider_status_list();
$image_status_map = [];
foreach ($image_status as $row) {
    $image_status_map[$row['id']] = $row;
}
?>





<div id="tab-ai-studio" class="bankai-tab-pane" :class="{ 'is-active-tab': activeTab === 'ai-studio' }" x-show="activeTab === 'ai-studio'" x-cloak>
<div class="bankai-ai-studio-root" x-data="bankaiAiStudioRoot" x-init="defaultProvider = '<?php echo esc_js($default_p); ?>'; defaultProviderName = '<?php echo esc_js($default_name); ?>'; imageDefault = '<?php echo esc_js($image_default); ?>'; imgProvider = imageDefault || ''; providerNames = <?php echo wp_json_encode(array_map(function($m){ return $m['name'] ?? ($m['label'] ?? ''); }, $catalog), JSON_UNESCAPED_UNICODE); ?>">
    <div class="bk-lg-header bk-wm-hero bk-page-hero">
        <div class="bk-wm-hero__main">
            <div class="bk-wm-hero__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a4 4 0 014 4v1h1a3 3 0 010 6h-1v1a4 4 0 11-8 0v-1H7a3 3 0 010-6h1V6a4 4 0 014-4z"/><circle cx="12" cy="12" r="1.5"/></svg>
            </div>
            <div>
                <div class="bk-wm-hero__title-row">
                    <h2>استودیو هوش مصنوعی</h2>
                    <span class="bk-wm-pill">Multi-LLM</span>
                </div>
                <p>کلید API، مدل فعال، ایجنت‌های سفارشی و موتورهای متنی/تصویری</p>
            </div>
        </div>
        <div class="bk-wm-hero__actions">
            <button type="button" class="bk-wm-chip-btn" @click="testAllConnections()" :disabled="busy" x-show="aiMode==='text'">
                <span x-show="!busy">تست همه اتصالات</span>
                <span x-show="busy" x-cloak>در حال تست…</span>
            </button>
            <button type="button" class="bk-wm-chip-btn" @click="openAddModal()" x-show="aiMode==='text'" style="background:rgba(255,255,255,.95)!important;color:#5b21b6!important">افزودن هوش مصنوعی</button>
        </div>
    </div>
    <div class="bk-capsule-tabs bk-art-tabs bk-ai-mode-tabs" role="tablist">
    <button type="button" class="bk-capsule-tab" :class="{'is-active': aiMode==='text'}" @click="aiMode='text'" title="هوش‌های مصنوعی متنی">
      <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18"><path d="M4 6h16M4 12h10M4 18h14"/></svg>
      <span class="bk-capsule-label">متنی</span>
    </button>
    <button type="button" class="bk-capsule-tab" :class="{'is-active': aiMode==='image'}" @click="aiMode='image'" title="هوش مصنوعی تصویری">
      <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 16l-5-5-4 4-2-2-5 5"/></svg>
      <span class="bk-capsule-label">تصویری</span>
    </button>
  </div>

  <div x-show="aiMode==='text'" style="display:block">
  <div class="bk-vault">
    <div>
      <h4>
        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        خزانه کلیدهای API
      </h4>
      <p>کلیدها در دیتابیس وردپرس ذخیره می‌شوند · موتور پیش‌فرض از همین‌جا انتخاب می‌شود</p>
    </div>
    <div class="bk-field" style="margin:0;min-width:200px">
      <label>موتور پیش‌فرض</label>
      <select x-model="defaultProvider" @change="updateDefaultProvider()">
        <?php foreach ($catalog as $id => $meta): ?>
        <option value="<?php echo esc_attr($id); ?>" <?php selected($default_p, $id); ?>><?php echo esc_html($meta['name'] ?? $id); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="bk-provider-grid">
    <?php foreach ($catalog as $id => $meta):
      $st = $status_map[$id] ?? [];
      $sel_model = $models[$id] ?? ($meta['default'] ?? '');
      $accent = $meta['accent'] ?? '#0078d4';
      $status_key = $st['status'] ?? 'missing_key';
      $label = $st['label'] ?? __('بدون کلید API', 'bankai-core');
      $masked = $st['masked_key'] ?? '';
      $is_custom = !empty($meta['custom']);
      $has_key = !empty($st['has_key']);
      $models_list = array_values($meta['models'] ?? []);
      $initial = mb_substr($meta['name'] ?? $id, 0, 1);
    ?>
    <div class="bk-provider-card <?php echo $has_key ? 'is-ready' : ''; ?>"
         style="--pc-accent:<?php echo esc_attr($accent); ?>"
         x-data="bankaiProviderCard({
           id:'<?php echo esc_js($id); ?>',
           key:'<?php echo esc_js($masked); ?>',
           model:'<?php echo esc_js($sel_model); ?>',
           status:'<?php echo esc_js($status_key); ?>',
           label:'<?php echo esc_js($label); ?>',
           models: <?php echo esc_attr(wp_json_encode($models_list, JSON_UNESCAPED_UNICODE)); ?>,
           isCustom: <?php echo $is_custom ? 'true' : 'false'; ?>
         })">
      <div class="bk-provider-head">
        <div style="display:flex;gap:10px;align-items:flex-start">
          <div class="bk-provider-avatar"><?php echo esc_html($initial); ?></div>
          <div>
            <div class="bk-provider-name"><?php echo esc_html($meta['name'] ?? $id); ?></div>
            <div class="bk-provider-tag"><?php echo esc_html($meta['tagline'] ?? ''); ?></div>
            <div class="bk-chip-row">
              <?php if (!empty($meta['free_tier'])): ?><span class="bk-chip is-green">رایگان</span><?php endif; ?>
              <?php if ($is_custom): ?><span class="bk-chip is-purple">سفارشی</span><?php endif; ?>
              <span class="bk-chip is-blue" x-text="label"><?php echo esc_html($label); ?></span>
            </div>
          </div>
        </div>
        <button type="button" class="bk-btn bk-btn-ghost" style="padding:6px 10px" @click="openModelsEditor()" title="ویرایش مدل‌ها">
          <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
          ویرایش
        </button>
      </div>

      <div class="bk-field">
        <label>مدل فعال</label>
        <select x-model="model">
          <template x-for="m in models" :key="m">
            <option :value="m" x-text="m === 'openrouter/auto' ? '⚡ خودکار' : (m === 'openrouter/free' ? '⚡ خودکار رایگان' : m)"></option>
          </template>
        </select>
      </div>

      <div class="bk-model-list" x-show="models.length">
        <template x-for="m in models" :key="'p'+m">
          <span class="bk-model-pill">
            <span x-text="m"></span>
          </span>
        </template>
      </div>

      <div class="bk-field">
        <label>کلید API</label>
        <div class="bk-key-row">
          <input :type="'text'" data-bk-secret="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-form-type="other" :style="showKey ? '' : 'text-security:disc;-webkit-text-security:disc'" x-model="key"
                 @input="keyDirty=true"
                 @focus="if(!keyDirty && key.indexOf('•')!==-1){key='';keyDirty=true;}"
                 placeholder="<?php echo esc_attr($meta['key_hint'] ?? 'API Key'); ?>" autocomplete="off">
          <button type="button" class="bk-icon-btn" @click="showKey=!showKey" title="نمایش">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
          <button type="button" class="bk-icon-btn" @click="clearKey()" title="حذف کلید">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
      </div>

      <?php if (!empty($meta['docs'])): ?>
      <a href="<?php echo esc_url($meta['docs']); ?>" target="_blank" rel="noopener" style="font-size:11px;font-weight:700;color:var(--bk-accent);text-decoration:none">دریافت API Key ↗</a>
      <?php endif; ?>

      <div class="bk-card-actions">
        <button type="button" class="bk-btn bk-btn-primary" style="padding:8px 12px" :disabled="saving" @click="save()">
          <span class="bk-btn-spin" x-show="saving" x-cloak></span>
          <span x-show="!saving">ذخیره</span>
          <span x-show="saving" x-cloak>در حال ذخیره…</span>
        </button>
        <button type="button" class="bk-btn bk-btn-ghost" style="padding:8px 12px" :disabled="testing" @click="test()">
          <span class="bk-btn-spin" x-show="testing" x-cloak></span>
          <span x-show="!testing">تست اتصال</span>
          <span x-show="testing" x-cloak>در حال تست…</span>
        </button>
        <?php if ($is_custom): ?>
        <button type="button" class="bk-btn bk-btn-ghost" style="padding:8px 12px;color:#c42b1c" @click="$dispatch('bk-delete-provider', {id: id})">حذف ایجنت</button>
        <?php endif; ?>
      </div>

      <!-- models editor (inline expand) -->
      <div x-show="editModels" x-cloak style="margin-top:12px;padding-top:12px;border-top:1px solid var(--bk-line)">
        <div class="bk-field">
          <label>افزودن شناسه مدل جدید</label>
          <div class="bk-key-row">
            <input type="text" x-model="newModel" dir="ltr" placeholder="model-id">
            <button type="button" class="bk-btn bk-btn-primary" style="padding:8px 12px" @click="addModel()" :disabled="busyModel">+</button>
          </div>
        </div>
        <div class="bk-model-list">
          <template x-for="m in models" :key="'e'+m">
            <span class="bk-model-pill">
              <span x-text="m"></span>
              <button type="button" @click="removeModel(m)" title="حذف">×</button>
            </span>
          </template>
        </div>
        <button type="button" class="bk-btn bk-btn-ghost" style="padding:6px 10px;margin-top:6px" @click="editModels=false">بستن</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- MODULES at end of main AI page -->
  <div class="bk-modules-section">
    <div class="bk-vault">
      <div>
        <h4>
          <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 7V3h4v4h4v4h4v4h-4v4H7v-4H3v-4h4V7z"/></svg>
          ماژول‌ها و ابزارهای خودکار وردپرس
        </h4>
        <p>اتوماسیون محتوا، سئو و هوک‌های بلادرنگ</p>
      </div>
      <span class="bk-chip is-green">WP-Cron &amp; Hooks</span>
    </div>
    <div class="bk-mod-grid">
      <?php foreach ($ai_mods as $mod):
        $mod_id = esc_attr($mod['id']);
        $is_active = !empty($saved_mods[$mod_id]);
      ?>
      <div class="bk-mod-card" x-data="{active: <?php echo $is_active ? 'true' : 'false'; ?>}">
        <div class="bk-mod-top">
          <div>
            <div class="bk-mod-title" style="display:flex;align-items:center;gap:8px">
              <span style="width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr($mod['color']); ?>"></span>
              <?php echo esc_html($mod['title_fa']); ?>
            </div>
            <span class="bk-mod-badge"><?php echo esc_html($mod['badge']); ?></span>
          </div>
          <label class="bk-switch"><input type="checkbox" x-model="active" @change="toggleModule('<?php echo $mod_id; ?>', active)"><span></span></label>
        </div>
        <p><?php echo esc_html($mod['description_fa']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>


  </div><!-- end text AI mode -->

  <div x-show="aiMode==='image'" style="display:none">
    <div class="bk-vault">
      <div>
        <h4>
          <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 16l-5-5-4 4-2-2-5 5"/></svg>
          موتورهای تولید تصویر
        </h4>
        <p>ساخت کاور پست و تصاویر داخل متن مقاله · ذخیره در کتابخانه رسانه</p>
      </div>
      <div class="bk-field" style="margin:0;min-width:220px">
        <label>موتور تصویری پیش‌فرض</label>
        <select x-model="imageDefault" @change="setImageDefault()">
          <?php foreach ($image_catalog as $id => $meta): ?>
          <option value="<?php echo esc_attr($id); ?>" <?php selected($image_default, $id); ?>><?php echo esc_html($meta['name'] ?? $id); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

        <div class="bk-provider-grid">
      <?php foreach ($image_catalog as $id => $meta):
        $st = $image_status_map[$id] ?? [];
        $sel_model = $image_models[$id] ?? ($meta['default'] ?? '');
        $accent = $meta['accent'] ?? '#0078d4';
        $has = !empty($st['has_key']);
        $label = $st['label'] ?? __('بدون کلید API', 'bankai-core');
        $status_key = $st['status'] ?? 'missing_key';
        $masked = $st['masked_key'] ?? '';
        $all_models = Bankai_AI_Studio::get_image_provider_models($id);
        $models_list = array_values($all_models);
        $initial = mb_substr($meta['name'] ?? $id, 0, 1);
      ?>
      <div class="bk-provider-card <?php echo $has ? 'is-ready' : ''; ?>"
           style="--pc-accent:<?php echo esc_attr($accent); ?>"
           x-data="bankaiImageProviderCard({
             id:'<?php echo esc_js($id); ?>',
             key:'<?php echo esc_js($masked); ?>',
             model:'<?php echo esc_js($sel_model); ?>',
             status:'<?php echo esc_js($status_key); ?>',
             label:'<?php echo esc_js($label); ?>',
             models: <?php echo esc_attr(wp_json_encode($models_list, JSON_UNESCAPED_UNICODE)); ?>,
             hasKey: <?php echo $has ? 'true' : 'false'; ?>,
             masked:'<?php echo esc_js($masked); ?>'
           })">
        <div class="bk-provider-head">
          <div style="display:flex;gap:10px;align-items:flex-start">
            <div class="bk-provider-avatar"><?php echo esc_html($initial); ?></div>
            <div>
              <div class="bk-provider-name"><?php echo esc_html($meta['name'] ?? $id); ?></div>
              <div class="bk-provider-tag"><?php echo esc_html($meta['tagline'] ?? ''); ?></div>
              <div class="bk-chip-row">
                <?php if (!empty($meta['free_tier'])): ?><span class="bk-chip is-green">رایگان</span><?php endif; ?>
                <span class="bk-chip is-purple">تصویر</span>
                <span class="bk-chip is-blue" x-text="label"><?php echo esc_html($label); ?></span>
              </div>
            </div>
          </div>
          <button type="button" class="bk-btn bk-btn-ghost" style="padding:6px 10px" @click="openModelsEditor()" title="ویرایش مدل‌ها">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
            ویرایش
          </button>
        </div>

        <div class="bk-field">
          <label>مدل فعال</label>
          <select x-model="model" @change="saveModelOnly()">
            <template x-for="m in models" :key="m">
              <option :value="m" x-text="m"></option>
            </template>
          </select>
        </div>

        <div class="bk-model-list" x-show="models.length">
          <template x-for="m in models" :key="'ip'+m">
            <span class="bk-model-pill"><span x-text="m"></span></span>
          </template>
        </div>

        <div class="bk-field">
          <label>کلید API</label>
          <div class="bk-key-row">
            <input :type="'text'" data-bk-secret="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-form-type="other" :style="showKey ? '' : 'text-security:disc;-webkit-text-security:disc'" x-model="key"
                   @input="keyDirty=true"
                   @focus="if(!keyDirty && key && key.indexOf('•')!==-1){key='';keyDirty=true;}"
                   :placeholder="hasKey ? '•••• برای تغییر بنویسید' : '<?php echo esc_attr($meta['key_hint'] ?? 'API Key'); ?>'"
                   autocomplete="off">
            <button type="button" class="bk-btn bk-btn-ghost" style="padding:8px" @click="showKey=!showKey" title="نمایش">
              <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            <button type="button" class="bk-btn bk-btn-ghost" style="padding:8px;color:#c42b1c" x-show="hasKey" @click="clearKey()" title="حذف کلید">
              <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
            </button>
          </div>
        </div>

        <?php if (!empty($meta['docs'])): ?>
        <a href="<?php echo esc_url($meta['docs']); ?>" target="_blank" rel="noopener" style="font-size:11px;font-weight:700;color:var(--bk-accent);text-decoration:none">دریافت API Key ↗</a>
        <?php endif; ?>

        <div class="bk-card-actions">
          <button type="button" class="bk-btn bk-btn-primary" style="padding:8px 12px" :disabled="saving || busy" @click="save()">
          <span class="bk-btn-spin" x-show="saving" x-cloak></span>
          <span x-show="!saving">ذخیره</span>
          <span x-show="saving" x-cloak>در حال ذخیره…</span>
        </button>
          <button type="button" class="bk-btn bk-btn-ghost" style="padding:8px 12px" :disabled="testing || busy" @click="test()">
          <span class="bk-btn-spin" x-show="testing" x-cloak></span>
          <span x-show="!testing">تست اتصال</span>
          <span x-show="testing" x-cloak>در حال تست…</span>
        </button>
        </div>

        <div x-show="editModels" x-cloak style="margin-top:12px;padding-top:12px;border-top:1px solid var(--bk-line)">
          <div class="bk-field">
            <label>افزودن شناسه مدل جدید</label>
            <div class="bk-key-row">
              <input type="text" x-model="newModel" dir="ltr" placeholder="model-id">
              <button type="button" class="bk-btn bk-btn-primary" style="padding:8px 12px" @click="addModel()" :disabled="busy">+</button>
            </div>
          </div>
          <div class="bk-model-list">
            <template x-for="m in models" :key="'ie'+m">
              <span class="bk-model-pill">
                <span x-text="m"></span>
                <button type="button" @click="removeModel(m)" title="حذف">×</button>
              </span>
            </template>
          </div>
          <button type="button" class="bk-btn bk-btn-ghost" style="padding:6px 10px;margin-top:6px" @click="editModels=false">بستن</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="bk-img-tools">
      <div class="bk-card">
        <div class="bk-card-head">
          <strong>تولید تصویر با AI</strong>
          <span class="bk-chip is-purple">کاور · محتوای مقاله</span>
        </div>
        <div class="bk-img-purpose">
          <button type="button" :class="{'is-on': imgPurpose==='cover'}" @click="imgPurpose='cover'">کاور پست (۱۶:۹)</button>
          <button type="button" :class="{'is-on': imgPurpose==='inline'}" @click="imgPurpose='inline'">تصویر داخل مقاله</button>
        </div>
        <div class="bk-field">
          <label>موتور</label>
          <select x-model="imgProvider">
            <?php foreach ($image_catalog as $id => $meta): ?>
            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($meta['name'] ?? $id); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="bk-field">
          <label>شناسه پست (اختیاری — برای ست‌کردن کاور)</label>
          <input type="number" min="0" x-model="imgPostId" placeholder="مثلاً 42">
        </div>
        <div class="bk-field">
          <label>پرامپت تصویر</label>
          <textarea x-model="imgPrompt" rows="4" placeholder="موضوع، سبک بصری، نورپردازی… از متن ناخوانا روی تصویر پرهیز کنید"></textarea>
        </div>
        <button type="button" class="bk-btn bk-btn-primary" style="width:100%" @click="generateImage()" :disabled="imgBusy || !imgPrompt.trim()">
          <span x-text="imgBusy ? 'در حال تولید…' : (imgPurpose==='cover' ? 'ساخت کاور و ذخیره در رسانه' : 'ساخت تصویر محتوا')"></span>
        </button>
        <p style="margin:10px 0 0;font-size:11px;color:var(--bk-muted);line-height:1.55">
          خروجی در کتابخانه رسانه ذخیره می‌شود. اگر شناسه پست وارد شود و حالت کاور باشد، به‌عنوان تصویر شاخص ست می‌گردد.
        </p>
      </div>
      <div class="bk-card">
        <div class="bk-card-head"><strong>پیش‌نمایش</strong></div>
        <div class="bk-img-preview">
          <template x-if="imgResultUrl"><img :src="imgResultUrl" alt="AI"></template>
          <template x-if="!imgResultUrl"><span style="font-size:12px;color:var(--bk-soft);padding:16px;text-align:center">پس از تولید اینجا نمایش داده می‌شود</span></template>
        </div>
        <div style="margin-top:10px;font-size:12px;color:var(--bk-muted)" x-show="imgMessage" x-text="imgMessage"></div>
        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap" x-show="imgResultUrl">
          <a class="bk-btn bk-btn-soft" :href="imgResultUrl" target="_blank" rel="noopener">باز کردن</a>
          <button type="button" class="bk-btn bk-btn-ghost" x-show="imgHtml" @click="copyImgHtml()">کپی HTML درج در مقاله</button>
        </div>
      </div>
    </div>
  </div>


  <!-- ADD PROVIDER MODAL -->
  <div class="bk-modal-shell" x-show="addModal" x-cloak @click.self="addModal=false">
    <div class="bk-modal-panel" @click.stop>
      <div class="bk-modal-head">
        <h3>پیکربندی ایجنت هوشمند جدید</h3>
        <button type="button" class="bk-icon-btn" @click="addModal=false">×</button>
      </div>
      <p style="margin:0 0 12px;font-size:12px;color:var(--bk-muted);line-height:1.6">
        کد اتصال را به‌صورت <code dir="ltr">curl</code> یا فرم زیر وارد کنید. اندپوینت OpenAI-compatible پشتیبانی می‌شود.
      </p>
      <div class="bk-field">
        <label>کد اتصال (curl)</label>
        <textarea class="bk-code" x-model="customForm.curl" placeholder='curl -X POST "http://localhost:8000/v1/chat/completions" \
  -H "Content-Type: application/json" \
  --data '"'"'{"model":"XiaomiMiMo/MiMo-V2.6-Pro-RL","messages":[{"role":"user","content":"..."}]}'"'"'></textarea>
        <button type="button" class="bk-btn bk-btn-soft" style="margin-top:8px" @click="parseCurl()">استخراج از curl</button>
      </div>
      <div class="bk-form-grid">
        <div class="bk-field"><label>نام نمایشی *</label><input type="text" x-model="customForm.name" placeholder="دستیار سئو"></div>
        <div class="bk-field"><label>شناسه یکتا</label><input type="text" x-model="customForm.id" dir="ltr" placeholder="my-agent"></div>
        <div class="bk-field bk-span-2"><label>Endpoint *</label><input type="url" x-model="customForm.endpoint" dir="ltr" placeholder="http://127.0.0.1:8000/v1/chat/completions"></div>
        <div class="bk-field"><label>Model ID</label><input type="text" x-model="customForm.default_model" dir="ltr" placeholder="model-name"></div>
        <div class="bk-field"><label>API Key</label><input type="text" data-bk-secret="1" style="text-security:disc;-webkit-text-security:disc" x-model="customForm.api_key" autocomplete="off"></div>
        <div class="bk-field bk-span-2"><label>مدل‌ها (هر خط یکی)</label><textarea x-model="customForm.models" rows="2" dir="ltr"></textarea></div>
        <div class="bk-field"><label>هدر Auth</label><input type="text" x-model="customForm.auth_header" dir="ltr"></div>
        <div class="bk-field"><label>پیشوند Auth</label><input type="text" x-model="customForm.auth_prefix" dir="ltr"></div>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;flex-wrap:wrap">
        <button type="button" class="bk-btn bk-btn-ghost" @click="addModal=false">انصراف</button>
        <button type="button" class="bk-btn bk-btn-success" @click="saveCustomAgent()" :disabled="busy">
          <span class="bk-btn-spin" x-show="busy" x-cloak></span>
          <span x-show="!busy">ذخیره ایجنت</span>
          <span x-show="busy" x-cloak>در حال ذخیره…</span>
        </button>
      </div>
    </div>
  </div>
</div><!-- /bankaiAiStudioRoot -->

<!-- /tab-ai-studio -->

<!-- /tab-ai-studio close -->
</div>
<!-- /tab-ai-studio root -->
