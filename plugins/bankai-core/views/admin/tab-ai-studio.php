<?php
/**
 * Tab: AI — Bankai Multi-LLM Control Center
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

$ai_mods = [
    [
        'id' => 'auto_seo_meta',
        'title_fa' => 'سئو خودکار و اسکیمای هوشمند',
        'description_fa' => 'تولید برخط برچسب‌های عنوان، توضیحات متا و استراتژی‌های JSON-LD هنگام انتشار پست.',
        'badge' => 'SEO Engine',
        'color' => '#0078d4',
    ],
    [
        'id' => 'alt_generator',
        'title_fa' => 'تولید هوشمند Alt-Text تصاویر',
        'description_fa' => 'آنالیز بصری چندوجهی تصاویر کتابخانه و تزریق مستقیم به دیتابیس پست‌متا.',
        'badge' => 'Media AI',
        'color' => '#0f7b3a',
    ],
    [
        'id' => 'content_expander',
        'title_fa' => 'دستیار بلوک گوتنبرگ',
        'description_fa' => 'افزودن دکمه‌های ری‌رایت، لحن رسمی/محاوره‌ای و گسترش پاراگراف در تولبار ویرایشگر.',
        'badge' => 'Editor Kit',
        'color' => '#6b4eff',
    ],
];
$saved_mods = get_option(Bankai_AI_Studio::OPT_MODULES, []);
if (!is_array($saved_mods)) {
    $saved_mods = [];
}
?>

<style>
#tab-ai-studio{--bk-ink:#1a1a1a;--bk-muted:#605e5c;--bk-soft:#8a8886;--bk-line:rgba(0,0,0,.06);--bk-card:#fff;--bk-accent:#0078d4;--bk-purple:#6b4eff;--bk-green:#0f7b3a;--bk-radius:16px;font-family:"Segoe UI",system-ui,"Vazirmatn",sans-serif;color:var(--bk-ink)}
#tab-ai-studio *{box-sizing:border-box}
#tab-ai-studio .bk-ai-topbar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:16px}
#tab-ai-studio .bk-ai-title{margin:0;font-size:26px;font-weight:800;letter-spacing:-.02em}
#tab-ai-studio .bk-ai-breadcrumb{font-size:11px;color:var(--bk-soft);margin-bottom:6px;font-weight:600}
#tab-ai-studio .bk-ai-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
#tab-ai-studio .bk-btn{display:inline-flex;align-items:center;gap:6px;border:none;cursor:pointer;border-radius:999px;padding:10px 16px;font-size:12px;font-weight:700;transition:.15s}
#tab-ai-studio .bk-btn-primary{background:linear-gradient(135deg,#0078d4,#6b4eff);color:#fff}
#tab-ai-studio .bk-btn-ghost{background:#fff;border:1px solid var(--bk-line);color:var(--bk-ink)}
#tab-ai-studio .bk-btn-soft{background:#eef6fc;color:var(--bk-accent);border:1px solid #d0e7f8}
#tab-ai-studio .bk-btn:disabled{opacity:.55;cursor:not-allowed}
#tab-ai-studio .bk-vault{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;background:linear-gradient(135deg,#eef6fc,#f3e8ff);border:1px solid #d0e7f8;border-radius:14px;padding:14px 16px;margin-bottom:14px}
#tab-ai-studio .bk-vault h4{margin:0;font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px}
#tab-ai-studio .bk-vault p{margin:4px 0 0;font-size:11px;color:var(--bk-muted)}
#tab-ai-studio .bk-field{margin-bottom:10px}
#tab-ai-studio .bk-field label{display:block;font-size:11px;font-weight:700;color:var(--bk-muted);margin-bottom:5px}
#tab-ai-studio .bk-field input,#tab-ai-studio .bk-field select,#tab-ai-studio .bk-field textarea{width:100%;border:1px solid var(--bk-line);border-radius:10px;background:#fafafa;padding:9px 12px;font-size:12px;outline:none}
#tab-ai-studio .bk-key-row{display:flex;gap:6px}
#tab-ai-studio .bk-key-row input{flex:1}
#tab-ai-studio .bk-provider-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
#tab-ai-studio .bk-provider-card{position:relative;overflow:hidden;border:1px solid var(--bk-line);border-radius:18px;padding:16px;background:#fff;box-shadow:0 4px 18px rgba(15,23,42,.06)}
#tab-ai-studio .bk-provider-card.is-ready{background:linear-gradient(180deg,rgba(0,120,212,.05),#fff)}
#tab-ai-studio .bk-provider-head{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px}
#tab-ai-studio .bk-provider-avatar{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:14px;background:var(--pc-accent,#0078d4)}
#tab-ai-studio .bk-provider-name{font-size:14px;font-weight:800}
#tab-ai-studio .bk-chip-row{display:flex;flex-wrap:wrap;gap:4px;margin:8px 0}
#tab-ai-studio .bk-chip{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:#f5f5f5;color:var(--bk-muted)}
#tab-ai-studio .bk-chip.is-green{background:#e8f5e9;color:var(--bk-green)}
#tab-ai-studio .bk-chip.is-blue{background:#eef6fc;color:var(--bk-accent)}
#tab-ai-studio .bk-card-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
#tab-ai-studio .bk-modules-section{margin-top:28px}
#tab-ai-studio .bk-mod-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
#tab-ai-studio .bk-mod-card{border:1px solid var(--bk-line);border-radius:14px;padding:16px;background:#fff}
#tab-ai-studio .bk-mod-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px}
#tab-ai-studio .bk-mod-title{font-size:13px;font-weight:800}
#tab-ai-studio .bk-mod-badge{display:inline-block;margin-top:6px;font-size:10px;font-weight:800;padding:2px 8px;border-radius:999px;background:#f5f5f5;color:var(--bk-muted)}
@media(max-width:900px){#tab-ai-studio .bk-provider-grid,#tab-ai-studio .bk-mod-grid{grid-template-columns:1fr}}
</style>

<div id="tab-ai-studio" class="bankai-tab-pane">

  <div class="bk-ai-topbar">
    <div>
      <div class="bk-ai-breadcrumb">AI</div>
      <h2 class="bk-ai-title">موتورهای هوش مصنوعی AI</h2>
      <p style="margin:6px 0 0;font-size:13px;color:var(--bk-muted);font-weight:600">تنظیم کلیدهای API، ارائه‌دهنده پیش‌فرض و ماژول‌های هوشمند AI</p>
    </div>
    <div class="bk-ai-actions">
      <button type="button" class="bk-btn bk-btn-soft" id="btn-test-all-ai">
        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        تست همه اتصالات AI
      </button>
    </div>
  </div>

  <div class="bk-vault">
    <div>
      <h4>
        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        خزانه کلیدهای API و ارائه‌دهنده پیش‌فرض
      </h4>
      <p>کلیدها به‌صورت رمزنگاری‌شده در دیتابیس امن ذخیره می‌گردند.</p>
    </div>
    <div class="bk-field" style="margin:0;min-width:220px">
      <label>ارائه‌دهنده پیش‌فرض AI</label>
      <select id="select-ai-default-provider">
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
      $label = $st['label'] ?? __('بدون کلید API', 'bankai-core');
      $masked = $st['masked_key'] ?? '';
      $has_key = !empty($st['has_key']);
      $models_list = array_values($meta['models'] ?? []);
      $initial = mb_substr($meta['name'] ?? $id, 0, 1);
    ?>
    <div class="bk-provider-card <?php echo $has_key ? 'is-ready' : ''; ?>" style="--pc-accent:<?php echo esc_attr($accent); ?>" data-provider-id="<?php echo esc_attr($id); ?>">
      <div class="bk-provider-head">
        <div style="display:flex;gap:10px;align-items:flex-start">
          <div class="bk-provider-avatar"><?php echo esc_html($initial); ?></div>
          <div>
            <div class="bk-provider-name"><?php echo esc_html($meta['name'] ?? $id); ?></div>
            <div class="bk-chip-row">
              <?php if (!empty($meta['free_tier'])): ?><span class="bk-chip is-green">رایگان</span><?php endif; ?>
              <span class="bk-chip is-blue provider-status-label"><?php echo esc_html($label); ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="bk-field">
        <label>مدل فعال</label>
        <select class="ai-provider-model-select" data-provider="<?php echo esc_attr($id); ?>">
          <?php foreach ($models_list as $m): ?>
            <option value="<?php echo esc_attr($m); ?>" <?php selected($sel_model, $m); ?>><?php echo esc_html($m); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="bk-field">
        <label>کلید API</label>
        <div class="bk-key-row">
          <input type="password" class="ai-provider-key-input" data-provider="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($masked); ?>" placeholder="API Key">
        </div>
      </div>

      <div class="bk-card-actions">
        <button type="button" class="bk-btn bk-btn-primary btn-save-ai-provider" data-provider="<?php echo esc_attr($id); ?>" style="padding:8px 12px">ذخیره</button>
        <button type="button" class="bk-btn bk-btn-ghost btn-test-ai-provider" data-provider="<?php echo esc_attr($id); ?>" style="padding:8px 12px">تست اتصال</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- MODULES SECTION -->
  <div class="bk-modules-section">
    <div class="bk-vault">
      <div>
        <h4>
          <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18"><path d="M7 7V3h4v4h4v4h4v4h-4v4H7v-4H3v-4h4V7z"/></svg>
          ماژول‌ها و ابزارهای هوش مصنوعی AI
        </h4>
        <p>تولید هوشمند متاداده، Alt-Text تصاویر و دستیار محتوا</p>
      </div>
      <span class="bk-chip is-green">AI Active</span>
    </div>
    <div class="bk-mod-grid">
      <?php foreach ($ai_mods as $mod):
        $mod_id = esc_attr($mod['id']);
        $is_active = !empty($saved_mods[$mod_id]);
      ?>
      <div class="bk-mod-card">
        <div class="bk-mod-top">
          <div>
            <div class="bk-mod-title" style="display:flex;align-items:center;gap:8px">
              <span style="width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr($mod['color']); ?>"></span>
              <?php echo esc_html($mod['title_fa']); ?>
            </div>
            <span class="bk-mod-badge"><?php echo esc_html($mod['badge']); ?></span>
          </div>
          <label class="bankai-switch">
            <input type="checkbox" class="bk-module-toggle" data-id="<?php echo $mod_id; ?>" <?php checked($is_active); ?>>
            <span class="bankai-slider"></span>
          </label>
        </div>
        <p><?php echo esc_html($mod['description_fa']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>
