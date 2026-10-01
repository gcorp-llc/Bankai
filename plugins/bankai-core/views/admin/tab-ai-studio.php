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
?>


<style>
#tab-ai-studio{--bk-ink:#1a1a1a;--bk-muted:#605e5c;--bk-soft:#8a8886;--bk-line:rgba(0,0,0,.06);--bk-card:#fff;--bk-accent:#0078d4;--bk-purple:#6b4eff;--bk-green:#0f7b3a;--bk-radius:16px;font-family:"Segoe UI",system-ui,"Vazirmatn",sans-serif;color:var(--bk-ink)}
#tab-ai-studio *{box-sizing:border-box}
#tab-ai-studio .bk-ai-topbar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:16px}
#tab-ai-studio .bk-ai-title{margin:0;font-size:26px;font-weight:800;letter-spacing:-.02em}
#tab-ai-studio .bk-ai-breadcrumb{font-size:11px;color:var(--bk-soft);margin-bottom:6px;font-weight:600}
#tab-ai-studio .bk-ai-badges{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:8px}
#tab-ai-studio .bk-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:11px;font-weight:700;border:1px solid var(--bk-line);background:#fff}
#tab-ai-studio .bk-pill.is-ok{background:#e8f5e9;color:var(--bk-green);border-color:#c8e6c9}
#tab-ai-studio .bk-pill.is-preview{background:#f3e8ff;color:var(--bk-purple);border-color:#e0d4ff}
#tab-ai-studio .bk-ai-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
#tab-ai-studio .bk-ai-search{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--bk-line);border-radius:999px;padding:8px 14px;min-width:200px}
#tab-ai-studio .bk-ai-search input{border:none;outline:none;background:transparent;font-size:12px;width:100%}
#tab-ai-studio .bk-btn{display:inline-flex;align-items:center;gap:6px;border:none;cursor:pointer;border-radius:999px;padding:10px 16px;font-size:12px;font-weight:700;transition:.15s}
#tab-ai-studio .bk-btn .solar-icon{font-size:18px}
#tab-ai-studio .bk-btn-primary{background:linear-gradient(135deg,#0078d4,#6b4eff);color:#fff}
#tab-ai-studio .bk-btn-ghost{background:#fff;border:1px solid var(--bk-line);color:var(--bk-ink)}
#tab-ai-studio .bk-btn-success{background:#0f7b3a;color:#fff}
#tab-ai-studio .bk-btn-soft{background:#eef6fc;color:var(--bk-accent);border:1px solid #d0e7f8}
#tab-ai-studio .bk-btn:disabled{opacity:.55;cursor:not-allowed}
#tab-ai-studio .bk-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
#tab-ai-studio .bk-stat{background:var(--bk-card);border:1px solid var(--bk-line);border-radius:14px;padding:14px 16px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
#tab-ai-studio .bk-stat-label{font-size:11px;color:var(--bk-muted);font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px}
#tab-ai-studio .bk-stat-label .solar-icon{font-size:16px;color:var(--bk-accent)}
#tab-ai-studio .bk-stat-value{font-size:22px;font-weight:800;letter-spacing:-.02em}
#tab-ai-studio .bk-stat-sub{font-size:11px;color:var(--bk-soft);margin-top:4px;font-weight:600}
#tab-ai-studio .bk-stat-tag{display:inline-block;margin-top:6px;font-size:10px;font-weight:800;padding:2px 8px;border-radius:999px;background:#eef6fc;color:var(--bk-accent)}
#tab-ai-studio .bk-subtabs{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:18px;border-bottom:1px solid var(--bk-line)}
#tab-ai-studio .bk-subtab{display:inline-flex;align-items:center;gap:6px;padding:12px 16px;border:none;background:transparent;cursor:pointer;font-size:12px;font-weight:700;color:var(--bk-muted);border-bottom:2px solid transparent;margin-bottom:-1px}
#tab-ai-studio .bk-subtab .solar-icon{font-size:18px}
#tab-ai-studio .bk-subtab:hover{color:var(--bk-ink)}
#tab-ai-studio .bk-subtab.is-active{color:var(--bk-accent);border-bottom-color:var(--bk-accent)}
#tab-ai-studio .bk-card{background:var(--bk-card);border:1px solid var(--bk-line);border-radius:var(--bk-radius);padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
#tab-ai-studio .bk-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px;flex-wrap:wrap}
#tab-ai-studio .bk-card-head h3{margin:0;font-size:14px;font-weight:800;display:flex;align-items:center;gap:8px}
#tab-ai-studio .bk-card-head p{margin:4px 0 0;font-size:12px;color:var(--bk-muted)}
#tab-ai-studio .bk-engines-layout{display:grid;grid-template-columns:280px 1fr;gap:14px;align-items:start}
#tab-ai-studio .bk-provider-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
#tab-ai-studio .bk-provider-card{background:#fff;border:1px solid var(--bk-line);border-radius:14px;padding:14px;transition:box-shadow .15s}
#tab-ai-studio .bk-provider-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.06)}
#tab-ai-studio .bk-provider-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px}
#tab-ai-studio .bk-provider-name{font-size:14px;font-weight:800;display:flex;align-items:center;gap:8px}
#tab-ai-studio .bk-provider-name .dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
#tab-ai-studio .bk-provider-tag{font-size:11px;color:var(--bk-muted);font-weight:600;margin-top:2px}
#tab-ai-studio .bk-chip-row{display:flex;flex-wrap:wrap;gap:4px;margin:8px 0}
#tab-ai-studio .bk-chip{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:#f5f5f5;color:var(--bk-muted)}
#tab-ai-studio .bk-chip.is-green{background:#e8f5e9;color:var(--bk-green)}
#tab-ai-studio .bk-chip.is-blue{background:#eef6fc;color:var(--bk-accent)}
#tab-ai-studio .bk-chip.is-purple{background:#f3e8ff;color:var(--bk-purple)}
#tab-ai-studio .bk-field{margin-bottom:10px}
#tab-ai-studio .bk-field label{display:block;font-size:11px;font-weight:700;color:var(--bk-muted);margin-bottom:5px}
#tab-ai-studio .bk-field input,#tab-ai-studio .bk-field select,#tab-ai-studio .bk-field textarea{width:100%;border:1px solid var(--bk-line);border-radius:10px;background:#fafafa;padding:9px 12px;font-size:12px;outline:none}
#tab-ai-studio .bk-field input:focus,#tab-ai-studio .bk-field select:focus,#tab-ai-studio .bk-field textarea:focus{border-color:#a6d1f2;background:#fff;box-shadow:0 0 0 3px rgba(0,120,212,.12)}
#tab-ai-studio .bk-key-row{display:flex;gap:6px}
#tab-ai-studio .bk-key-row input{flex:1}
#tab-ai-studio .bk-icon-btn{appearance:none;border:1px solid var(--bk-line);background:#fff;border-radius:10px;width:36px;height:36px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--bk-muted)}
#tab-ai-studio .bk-card-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
#tab-ai-studio .bk-health{text-align:center;padding:8px 0 12px}
#tab-ai-studio .bk-ring{width:120px;height:120px;margin:0 auto 12px;position:relative}
#tab-ai-studio .bk-ring svg{transform:rotate(-90deg)}
#tab-ai-studio .bk-ring-text{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
#tab-ai-studio .bk-ring-text strong{font-size:22px;font-weight:800}
#tab-ai-studio .bk-ring-text span{font-size:10px;color:var(--bk-muted);font-weight:700}
#tab-ai-studio .bk-health-list{text-align:start;font-size:11px;font-weight:600;color:var(--bk-muted)}
#tab-ai-studio .bk-health-list div{display:flex;justify-content:space-between;padding:4px 0}
#tab-ai-studio .bk-vault{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;background:linear-gradient(135deg,#eef6fc,#f3e8ff);border:1px solid #d0e7f8;border-radius:14px;padding:14px 16px;margin-bottom:14px}
#tab-ai-studio .bk-vault h4{margin:0;font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px}
#tab-ai-studio .bk-vault p{margin:4px 0 0;font-size:11px;color:var(--bk-muted)}
#tab-ai-studio .bk-custom-layout{display:grid;grid-template-columns:1.35fr 1fr;gap:14px;align-items:start}
#tab-ai-studio .bk-protocol-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px}
#tab-ai-studio .bk-protocol{border:1px solid var(--bk-line);border-radius:12px;padding:12px 8px;text-align:center;cursor:pointer;background:#fff}
#tab-ai-studio .bk-protocol.is-active{border-color:var(--bk-accent);background:#eef6fc;box-shadow:0 0 0 2px rgba(0,120,212,.15)}
#tab-ai-studio .bk-protocol .solar-icon{font-size:22px;color:var(--bk-accent);display:block;margin:0 auto 6px}
#tab-ai-studio .bk-protocol span{font-size:10px;font-weight:700;color:var(--bk-muted)}
#tab-ai-studio .bk-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#tab-ai-studio .bk-span-2{grid-column:span 2}
#tab-ai-studio .bk-agent-list{display:flex;flex-direction:column;gap:10px}
#tab-ai-studio .bk-agent-item{border:1px solid var(--bk-line);border-radius:12px;padding:12px;background:#fafafa}
#tab-ai-studio .bk-agent-meta{font-size:11px;color:var(--bk-muted);margin-top:4px;direction:ltr;text-align:start}
#tab-ai-studio .bk-empty{text-align:center;padding:32px 12px;color:var(--bk-soft)}
#tab-ai-studio .bk-empty .solar-icon{font-size:42px;display:block;margin-bottom:8px;color:#c8c6c4}
#tab-ai-studio .bk-sandbox-toolbar{display:grid;grid-template-columns:1.2fr 1fr;gap:12px;margin-bottom:12px}
#tab-ai-studio .bk-sandbox-grid{display:grid;grid-template-columns:1fr 1.15fr;gap:12px}
#tab-ai-studio .bk-sandbox-input textarea{width:100%;min-height:180px;border:1px solid var(--bk-line);border-radius:12px;background:#fafafa;padding:12px;font-size:13px;resize:vertical}
#tab-ai-studio .bk-prompt-chips{display:flex;flex-wrap:wrap;gap:6px;margin:10px 0}
#tab-ai-studio .bk-prompt-chips button{border:1px solid var(--bk-line);background:#fff;border-radius:999px;padding:6px 10px;font-size:11px;font-weight:700;cursor:pointer;color:var(--bk-muted)}
#tab-ai-studio .bk-result-box{border:1px solid var(--bk-line);border-radius:12px;background:#fff;min-height:280px;padding:14px;font-size:13px;line-height:1.75;white-space:pre-wrap}
#tab-ai-studio .bk-result-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
#tab-ai-studio .bk-mod-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
#tab-ai-studio .bk-mod-card{border:1px solid var(--bk-line);border-radius:14px;padding:16px;background:#fff}
#tab-ai-studio .bk-mod-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px}
#tab-ai-studio .bk-mod-title{font-size:13px;font-weight:800}
#tab-ai-studio .bk-mod-badge{display:inline-block;margin-top:6px;font-size:10px;font-weight:800;padding:2px 8px;border-radius:999px;background:#f5f5f5;color:var(--bk-muted)}
#tab-ai-studio .bk-mod-card p{margin:0;font-size:12px;color:var(--bk-muted);line-height:1.6}
#tab-ai-studio .bk-switch{position:relative;width:42px;height:24px;display:inline-block}
#tab-ai-studio .bk-switch input{opacity:0;width:0;height:0}
#tab-ai-studio .bk-switch span{position:absolute;inset:0;background:#c8c6c4;border-radius:999px;cursor:pointer;transition:.2s}
#tab-ai-studio .bk-switch span:before{content:'';position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;top:3px;inset-inline-start:3px;transition:.2s;box-shadow:0 1px 2px rgba(0,0,0,.2)}
#tab-ai-studio .bk-switch input:checked + span{background:var(--bk-green)}
#tab-ai-studio .bk-switch input:checked + span:before{transform:translateX(18px)}
html[dir="rtl"] #tab-ai-studio .bk-switch input:checked + span:before{transform:translateX(-18px)}
#tab-ai-studio .bankai-toast{position:fixed;bottom:24px;inset-inline-end:24px;z-index:100000;background:#1a1a1a;color:#fff;padding:12px 18px;border-radius:12px;font-size:12px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.2)}

/* Sandbox interactive capsule (mockup) */
#tab-ai-studio .bk-sb-banner{
  display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;
  background:linear-gradient(135deg,#e8f8f0,#eef6fc);border:1px solid #c8e6c9;
  border-radius:14px;padding:12px 16px;margin-bottom:14px;
}
#tab-ai-studio .bk-sb-banner h4{margin:0;font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px;color:#0f7b3a}
#tab-ai-studio .bk-sb-banner p{margin:2px 0 0;font-size:11px;color:var(--bk-muted)}
#tab-ai-studio .bk-sb-credit{
  display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--bk-line);
  border-radius:999px;padding:6px 12px;font-size:11px;font-weight:800;color:#0f7b3a;
}
#tab-ai-studio .bk-sb-settings{
  background:#fff;border:1px solid var(--bk-line);border-radius:16px;padding:16px;margin-bottom:14px;
  box-shadow:0 1px 3px rgba(0,0,0,.04);
}
#tab-ai-studio .bk-sb-settings-head{
  display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;
}
#tab-ai-studio .bk-sb-settings-head h3{margin:0;font-size:14px;font-weight:800;display:flex;align-items:center;gap:8px}
#tab-ai-studio .bk-sb-settings-head p{margin:4px 0 0;font-size:12px;color:var(--bk-muted)}
#tab-ai-studio .bk-sb-model-row{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:14px}
#tab-ai-studio .bk-sb-model-row select{
  min-width:220px;border:1px solid var(--bk-line);border-radius:10px;padding:9px 12px;
  font-size:12px;font-weight:700;background:#fafafa;
}
#tab-ai-studio .bk-sb-params{
  display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-bottom:14px;
}
#tab-ai-studio .bk-sb-param{
  background:#fafafa;border:1px solid var(--bk-line);border-radius:12px;padding:12px;
}
#tab-ai-studio .bk-sb-param label{
  display:flex;align-items:center;justify-content:space-between;gap:8px;
  font-size:11px;font-weight:800;color:var(--bk-muted);margin-bottom:8px;
}
#tab-ai-studio .bk-sb-param .val{font-size:13px;font-weight:800;color:var(--bk-ink)}
#tab-ai-studio .bk-sb-param input[type=range]{width:100%;accent-color:#0078d4}
#tab-ai-studio .bk-sb-param .hints{display:flex;justify-content:space-between;font-size:10px;color:var(--bk-soft);margin-top:4px}
#tab-ai-studio .bk-sb-sys{
  background:#f8fafc;border:1px solid var(--bk-line);border-radius:12px;padding:12px;
}
#tab-ai-studio .bk-sb-sys label{display:block;font-size:11px;font-weight:800;color:var(--bk-muted);margin-bottom:6px}
#tab-ai-studio .bk-sb-sys textarea{
  width:100%;min-height:72px;border:1px solid var(--bk-line);border-radius:10px;
  background:#fff;padding:10px 12px;font-size:12px;line-height:1.6;resize:vertical;
}
#tab-ai-studio .bk-sb-main{
  display:grid;grid-template-columns:1fr 1.15fr;gap:14px;align-items:start;
}
#tab-ai-studio .bk-sb-panel{
  background:#fff;border:1px solid var(--bk-line);border-radius:16px;
  box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;
}
#tab-ai-studio .bk-sb-panel-head{
  display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;
  padding:12px 14px;border-bottom:1px solid var(--bk-line);background:#fafafa;
}
#tab-ai-studio .bk-sb-panel-head h3{margin:0;font-size:13px;font-weight:800;display:flex;align-items:center;gap:6px}
#tab-ai-studio .bk-sb-panel-body{padding:14px}
#tab-ai-studio .bk-sb-tabs{display:flex;gap:4px;flex-wrap:wrap}
#tab-ai-studio .bk-sb-tab{
  border:none;background:transparent;padding:6px 10px;border-radius:8px;cursor:pointer;
  font-size:11px;font-weight:700;color:var(--bk-muted);display:inline-flex;align-items:center;gap:4px;
}
#tab-ai-studio .bk-sb-tab.is-active{background:#eef6fc;color:#0078d4}
#tab-ai-studio .bk-sb-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px}
#tab-ai-studio .bk-sb-chips button{
  border:1px solid var(--bk-line);background:#fff;border-radius:999px;padding:6px 10px;
  font-size:11px;font-weight:700;cursor:pointer;color:var(--bk-muted);
}
#tab-ai-studio .bk-sb-chips button:hover{background:#eef6fc;color:#0078d4}
#tab-ai-studio .bk-sb-input{
  width:100%;min-height:160px;border:1px solid var(--bk-line);border-radius:12px;
  background:#fafafa;padding:12px;font-size:13px;line-height:1.7;resize:vertical;
}
#tab-ai-studio .bk-sb-input:focus{outline:none;border-color:#a6d1f2;background:#fff;box-shadow:0 0 0 3px rgba(0,120,212,.12)}
#tab-ai-studio .bk-sb-meta{
  display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;
  margin-top:8px;font-size:11px;color:var(--bk-soft);font-weight:600;
}
#tab-ai-studio .bk-sb-footer{
  display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;
  margin-top:12px;padding-top:12px;border-top:1px solid var(--bk-line);
}
#tab-ai-studio .bk-sb-toggle{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:var(--bk-muted)}
#tab-ai-studio .bk-sb-run{
  display:inline-flex;align-items:center;gap:8px;border:none;cursor:pointer;
  border-radius:12px;padding:12px 18px;font-size:13px;font-weight:800;color:#fff;
  background:linear-gradient(135deg,#0f7b3a,#0078d4);box-shadow:0 4px 14px rgba(15,123,58,.25);
}
#tab-ai-studio .bk-sb-run:disabled{opacity:.55;cursor:not-allowed}
#tab-ai-studio .bk-sb-status{
  display:flex;gap:12px;flex-wrap:wrap;align-items:center;font-size:11px;font-weight:700;color:var(--bk-muted);
  margin-bottom:10px;
}
#tab-ai-studio .bk-sb-status .dot{width:8px;height:8px;border-radius:50%;background:#0f7b3a;display:inline-block}
#tab-ai-studio .bk-sb-preview{
  border:1px solid var(--bk-line);border-radius:12px;background:#fff;min-height:320px;
  padding:14px;font-size:13px;line-height:1.8;
}
#tab-ai-studio .bk-sb-preview.pre{white-space:pre-wrap;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;direction:ltr;text-align:left}
#tab-ai-studio .bk-sb-seo-card{
  background:linear-gradient(135deg,#f0fdf4,#eff6ff);border:1px solid #bbf7d0;
  border-radius:12px;padding:12px;margin-bottom:12px;
}
#tab-ai-studio .bk-sb-seo-card .score{font-size:12px;font-weight:800;color:#0f7b3a;margin-bottom:4px}
#tab-ai-studio .bk-sb-seo-card h4{margin:0 0 6px;font-size:14px;font-weight:800}
#tab-ai-studio .bk-sb-seo-card p{margin:0;font-size:12px;color:var(--bk-muted);line-height:1.6}
#tab-ai-studio .bk-sb-code{
  background:#0f172a;color:#e2e8f0;border-radius:12px;padding:12px;margin:12px 0;
  font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;line-height:1.6;
  direction:ltr;text-align:left;overflow:auto;max-height:220px;white-space:pre;
}
#tab-ai-studio .bk-sb-code-head{
  display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;
  font-size:11px;font-weight:700;color:#94a3b8;
}
#tab-ai-studio .bk-sb-actions{
  display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--bk-line);
}
#tab-ai-studio .bk-sb-actions .bk-btn-insert{
  background:linear-gradient(135deg,#0078d4,#6b4eff);color:#fff;border:none;
  border-radius:999px;padding:10px 16px;font-size:12px;font-weight:800;cursor:pointer;
  display:inline-flex;align-items:center;gap:6px;
}
@media (max-width:1100px){
  #tab-ai-studio .bk-sb-params{grid-template-columns:1fr 1fr}
  #tab-ai-studio .bk-sb-main{grid-template-columns:1fr}
}

@media (max-width:1100px){
#tab-ai-studio .bk-stats{grid-template-columns:1fr 1fr}
#tab-ai-studio .bk-engines-layout,#tab-ai-studio .bk-custom-layout,#tab-ai-studio .bk-sandbox-toolbar,#tab-ai-studio .bk-sandbox-grid{grid-template-columns:1fr}
#tab-ai-studio .bk-provider-grid,#tab-ai-studio .bk-mod-grid{grid-template-columns:1fr}
#tab-ai-studio .bk-protocol-grid{grid-template-columns:1fr 1fr}
#tab-ai-studio .bk-span-2{grid-column:auto}
}
@keyframes spin{to{transform:rotate(360deg)}}

#tab-ai-studio .bk-provider-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
#tab-ai-studio .bk-provider-card{position:relative;overflow:hidden;border:1px solid transparent;border-radius:18px;padding:16px;background:#fff;box-shadow:0 4px 18px rgba(15,23,42,.06);transition:transform .18s,box-shadow .18s}
#tab-ai-studio .bk-provider-card:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,23,42,.1)}
#tab-ai-studio .bk-provider-card::before{content:"";position:absolute;inset:0 0 auto 0;height:4px;background:var(--pc-accent,#0078d4)}
#tab-ai-studio .bk-provider-card.is-ready{background:linear-gradient(180deg,color-mix(in srgb,var(--pc-accent) 8%,#fff),#fff)}
#tab-ai-studio .bk-provider-head{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px}
#tab-ai-studio .bk-provider-avatar{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:14px;background:var(--pc-accent,#0078d4);box-shadow:0 6px 16px color-mix(in srgb,var(--pc-accent) 35%,transparent)}
#tab-ai-studio .bk-model-list{display:flex;flex-wrap:wrap;gap:6px;margin:8px 0 12px;max-height:88px;overflow:auto}
#tab-ai-studio .bk-model-pill{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;padding:4px 8px;border-radius:999px;background:#f1f5f9;color:#334155;direction:ltr}
#tab-ai-studio .bk-model-pill button{border:none;background:transparent;cursor:pointer;color:#c42b1c;font-size:12px;line-height:1;padding:0 2px}
#tab-ai-studio .bk-modal-shell{position:fixed;inset:0;z-index:100000;display:flex;align-items:flex-start;justify-content:center;padding:48px 16px;background:rgba(15,23,42,.35);backdrop-filter:blur(4px)}
#tab-ai-studio .bk-modal-panel{width:min(640px,96vw);max-height:min(88vh,900px);overflow:auto;background:#fff;border-radius:18px;box-shadow:0 24px 64px rgba(15,23,42,.22);padding:20px}
#tab-ai-studio .bk-modal-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}
#tab-ai-studio .bk-modal-head h3{margin:0;font-size:16px;font-weight:800}
#tab-ai-studio .bk-code{width:100%;min-height:160px;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:11px;direction:ltr;text-align:left;border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#0d1117;color:#e6edf3;resize:vertical}
#tab-ai-studio .bk-modules-section{margin-top:28px}
#tab-ai-studio .bk-engines-layout{display:block}
#tab-ai-studio .bk-provider-grid{width:100%}
@media(max-width:900px){#tab-ai-studio .bk-provider-grid{grid-template-columns:1fr}}
</style>


<div id="tab-ai-studio" class="bankai-tab-pane" x-data="bankaiAiStudioRoot()" x-show="activeTab === 'ai-studio'" x-cloak>

  <div class="bankai-toast" x-show="toast.visible" x-transition x-cloak><span x-text="toast.message"></span></div>

  <div class="bk-ai-topbar">
    <div>
      <div class="bk-ai-breadcrumb">استودیو هوش مصنوعی بنکای</div>
      <h2 class="bk-ai-title">موتورهای هوش مصنوعی</h2>
      <p style="margin:6px 0 0;font-size:13px;color:var(--bk-muted);font-weight:600">کلید API، مدل فعال و ایجنت‌های سفارشی</p>
    </div>
    <div class="bk-ai-actions">
      <button type="button" class="bk-btn bk-btn-soft" @click="testAllConnections()" :disabled="busy">
        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        تست همه اتصالات
      </button>
      <button type="button" class="bk-btn bk-btn-primary" @click="openAddModal()">
        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
        افزودن هوش مصنوعی جدید
      </button>
    </div>
  </div>

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
          <input :type="showKey ? 'text' : 'password'" x-model="key"
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
          <span x-text="saving ? '…' : 'ذخیره'"></span>
        </button>
        <button type="button" class="bk-btn bk-btn-ghost" style="padding:8px 12px" :disabled="testing" @click="test()">
          <span x-text="testing ? 'تست…' : 'تست اتصال'"></span>
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
        <div class="bk-field"><label>API Key</label><input type="password" x-model="customForm.api_key" autocomplete="off"></div>
        <div class="bk-field bk-span-2"><label>مدل‌ها (هر خط یکی)</label><textarea x-model="customForm.models" rows="2" dir="ltr"></textarea></div>
        <div class="bk-field"><label>هدر Auth</label><input type="text" x-model="customForm.auth_header" dir="ltr"></div>
        <div class="bk-field"><label>پیشوند Auth</label><input type="text" x-model="customForm.auth_prefix" dir="ltr"></div>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;flex-wrap:wrap">
        <button type="button" class="bk-btn bk-btn-ghost" @click="addModal=false">انصراف</button>
        <button type="button" class="bk-btn bk-btn-success" @click="saveCustomAgent()" :disabled="busy">ذخیره ایجنت</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  if (!window.bankaiAdminNonce) {
    window.bankaiAdminNonce = (window.bankaiCoreData && window.bankaiCoreData.adminNonce)
      || '<?php echo esc_js(wp_create_nonce('bankai_admin_nonce')); ?>';
  }
  if (typeof ajaxurl === 'undefined') {
    window.ajaxurl = (window.bankaiCoreData && window.bankaiCoreData.ajaxUrl)
      || '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
  }
})();
document.addEventListener('alpine:init', () => {
  Alpine.data('bankaiAiStudioRoot', () => ({
    busy: false,
    addModal: false,
    defaultProvider: '<?php echo esc_js($default_p); ?>',
    defaultProviderName: '<?php echo esc_js($default_name); ?>',
    providerNames: <?php echo wp_json_encode(array_map(static fn($m) => $m['name'] ?? $m['label'] ?? '', $catalog), JSON_UNESCAPED_UNICODE); ?>,
    toast: { visible: false, message: '' },
    customForm: {
      id: '', name: '', type: 'openai_compat', endpoint: '', models: '',
      default_model: '', auth_header: 'Authorization', auth_prefix: 'Bearer ',
      extra_headers: '', api_key: '', tagline: 'ایجنت سفارشی', curl: ''
    },
    showToast(msg) {
      this.toast.message = msg;
      this.toast.visible = true;
      setTimeout(() => { this.toast.visible = false; }, 3200);
    },
    openAddModal() {
      this.resetCustomForm();
      this.addModal = true;
    },
    resetCustomForm() {
      this.customForm = {
        id: '', name: '', type: 'openai_compat', endpoint: '', models: '',
        default_model: '', auth_header: 'Authorization', auth_prefix: 'Bearer ',
        extra_headers: '', api_key: '', tagline: 'ایجنت سفارشی', curl: ''
      };
    },
    parseCurl() {
      const c = this.customForm.curl || '';
      const urlM = c.match(/curl\s+(?:-X\s+POST\s+)?["']([^"']+)["']/i) || c.match(/curl\s+-X\s+POST\s+(\S+)/i);
      if (urlM) this.customForm.endpoint = urlM[1];
      const dataM = c.match(/--data(?:-raw)?\s+['"]([\s\S]*?)['"]\s*$/m) || c.match(/--data(?:-raw)?\s+'([\s\S]*?)'/);
      let jsonStr = dataM ? dataM[1] : '';
      if (!jsonStr) {
        const brace = c.match(/\{[\s\S]*"model"[\s\S]*\}/);
        if (brace) jsonStr = brace[0];
      }
      try {
        const j = JSON.parse(jsonStr.replace(/\\'/g, "'"));
        if (j.model) {
          this.customForm.default_model = j.model;
          if (!this.customForm.models) this.customForm.models = j.model;
        }
      } catch (e) {}
      const authM = c.match(/-H\s+["']Authorization:\s*([^"']+)["']/i);
      if (authM) {
        const v = authM[1].trim();
        if (v.toLowerCase().startsWith('bearer ')) {
          this.customForm.auth_prefix = 'Bearer ';
          this.customForm.api_key = v.slice(7).trim();
        } else {
          this.customForm.api_key = v;
        }
      }
      if (!this.customForm.name && this.customForm.default_model) {
        this.customForm.name = this.customForm.default_model.split('/').pop();
      }
      this.showToast('از curl استخراج شد');
    },
    testAllConnections() {
      this.busy = true;
      const data = new FormData();
      data.append('action', 'bankai_test_ai_connections');
      data.append('nonce', window.bankaiAdminNonce);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          this.showToast(res.success ? 'تست انجام شد' : ((res.data && res.data.message) || 'خطا'));
          if (res.success) setTimeout(() => location.reload(), 600);
        })
        .catch(() => this.showToast('خطا در ارتباط'))
        .finally(() => { this.busy = false; });
    },
    updateDefaultProvider() {
      const names = this.providerNames || {};
      this.defaultProviderName = names[this.defaultProvider] || this.defaultProvider;
      const data = new FormData();
      data.append('action', 'bankai_set_default_provider');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.defaultProvider);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => this.showToast(res.success ? ('پیش‌فرض: ' + this.defaultProviderName) : 'خطا'))
        .catch(() => this.showToast('خطا'));
    },
    saveCustomAgent() {
      const f = this.customForm;
      if (!f.name || !f.name.trim()) { this.showToast('نام الزامی است'); return; }
      if (!f.endpoint || !f.endpoint.trim()) { this.showToast('Endpoint الزامی است'); return; }
      this.busy = true;
      const data = new FormData();
      data.append('action', 'bankai_save_custom_provider');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('id', f.id || '');
      data.append('name', f.name);
      data.append('type', 'openai_compat');
      data.append('endpoint', f.endpoint || '');
      data.append('models', f.models || f.default_model || '');
      data.append('default_model', f.default_model || '');
      data.append('auth_header', f.auth_header || 'Authorization');
      data.append('auth_prefix', f.auth_prefix || 'Bearer ');
      data.append('api_key', f.api_key || '');
      data.append('tagline', f.tagline || 'ایجنت سفارشی');
      data.append('curl_snippet', f.curl || '');
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.showToast(res.data?.message || 'ذخیره شد');
            setTimeout(() => location.reload(), 700);
          } else this.showToast((res.data && res.data.message) || 'خطا');
        })
        .catch(() => this.showToast('خطا در ارتباط'))
        .finally(() => { this.busy = false; });
    },
    toggleModule(modId, state) {
      const data = new FormData();
      data.append('action', 'bankai_toggle_ai_module');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('module_id', modId);
      data.append('active', state ? '1' : '0');
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => { if (res.success) this.showToast('وضعیت ماژول تغییر کرد'); });
    }
  }));

  Alpine.data('bankaiProviderCard', (config) => ({
    id: config.id,
    key: config.key || '',
    keyDirty: false,
    model: config.model || '',
    status: config.status,
    label: config.label,
    models: Array.isArray(config.models) ? config.models.slice() : [],
    isCustom: !!config.isCustom,
    showKey: false,
    saving: false,
    testing: false,
    editModels: false,
    newModel: '',
    busyModel: false,
    openModelsEditor() { this.editModels = !this.editModels; },
    addModel() {
      const m = (this.newModel || '').trim();
      if (!m) return;
      this.busyModel = true;
      const data = new FormData();
      data.append('action', 'bankai_add_provider_model');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', m);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            if (!this.models.includes(m)) this.models.push(m);
            this.newModel = '';
            this.notify('مدل اضافه شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        })
        .finally(() => { this.busyModel = false; });
    },
    removeModel(m) {
      if (!m || !confirm('حذف مدل «' + m + '»؟')) return;
      const data = new FormData();
      data.append('action', 'bankai_remove_provider_model');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', m);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.models = this.models.filter(x => x !== m);
            if (this.model === m && this.models.length) this.model = this.models[0];
            this.notify('مدل حذف شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        });
    },
    save() {
      this.saving = true;
      const data = new FormData();
      data.append('action', 'bankai_save_ai_keys');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('key', this.keyDirty ? this.key : '__unchanged__');
      data.append('model', this.model);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.status = res.data.status;
            this.label = res.data.label;
            if (res.data.masked_key) this.key = res.data.masked_key;
            this.keyDirty = false;
            this.notify('ذخیره شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        })
        .catch(() => this.notify('خطا'))
        .finally(() => { this.saving = false; });
    },
    clearKey() {
      if (!confirm('حذف کلید؟')) return;
      const data = new FormData();
      data.append('action', 'bankai_clear_ai_key');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.key = ''; this.keyDirty = false;
            this.status = res.data.status; this.label = res.data.label;
          }
        });
    },
    test() {
      this.testing = true;
      const data = new FormData();
      data.append('action', 'bankai_test_ai_connections');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          const list = (res.success && res.data && res.data.providers) ? res.data.providers : [];
          if (list.length) {
            const item = list[0];
            this.status = item.status || (item.ok ? 'ready' : 'error');
            this.label = item.label || (item.ok ? 'متصل ✓' : 'خطا');
            this.notify(item.ok ? ('موفق: ' + (item.preview || '')) : ('خطا: ' + (item.message || '')));
          } else this.notify((res.data && res.data.message) || 'ناموفق');
        })
        .catch(() => this.notify('خطای شبکه'))
        .finally(() => { this.testing = false; });
    },
    notify(msg) {
      try {
        const root = this.$el && this.$el.closest('#tab-ai-studio');
        if (root && root._x_dataStack && root._x_dataStack[0] && root._x_dataStack[0].showToast) {
          root._x_dataStack[0].showToast(msg); return;
        }
      } catch (e) {}
    }
  }));
});
document.addEventListener('bk-delete-provider', (e) => {
  const id = e.detail && e.detail.id;
  if (!id || !confirm('حذف این ایجنت؟')) return;
  const data = new FormData();
  data.append('action', 'bankai_delete_custom_provider');
  data.append('nonce', window.bankaiAdminNonce);
  data.append('id', id);
  fetch(ajaxurl, { method: 'POST', body: data })
    .then(r => r.json())
    .then(res => { if (res.success) location.reload(); else alert(res.data?.message || 'خطا'); });
});
</script>
