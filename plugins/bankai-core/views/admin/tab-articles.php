<?php
/**
 * Admin: Articles hub — list, AI generate, in-panel editor + SEO sidebar
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

$queue = get_option('bankai_ai_publish_queue', []);
if (!is_array($queue)) {
    $queue = [];
}
$queue_json = wp_json_encode(array_values($queue), JSON_UNESCAPED_UNICODE);
?>

<script type="application/json" id="bk-wiz-providers-json"><?php
$__bp = [];
if (class_exists('Bankai_AI_Studio')) {
    foreach (Bankai_AI_Studio::instance()->provider_status_list() as $pr) {
        if (empty($pr['has_key'])) {
            continue; // فقط موتورهای فعال
        }
        $__bp[] = [
            'id' => $pr['id'] ?? '',
            'name' => $pr['name'] ?? ($pr['id'] ?? ''),
            'has_key' => true,
        ];
    }
}
echo wp_json_encode($__bp, JSON_UNESCAPED_UNICODE);
?></script>

<div id="tab-articles" class="bankai-tab-pane" x-show="activeTab === 'articles'" x-cloak
     x-data="bankaiArticlesHub()"
     x-init="init()">

<style>
#tab-articles{font-family:"Segoe UI",system-ui,"Vazirmatn",sans-serif;color:#1a1a1a}
#tab-articles [x-cloak]{display:none!important}
#tab-articles .solar-icon{width:16px;height:16px;flex-shrink:0}
#tab-articles .bk-art-head{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:16px}
#tab-articles .bk-art-head h2{margin:0;font-size:22px;font-weight:800;display:flex;align-items:center;gap:8px}
#tab-articles .bk-art-head p{margin:4px 0 0;font-size:13px;color:#605e5c}
#tab-articles .bk-art-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(0,0,0,.06);padding-bottom:10px}
#tab-articles .bk-art-tab{border:none;background:transparent;padding:8px 14px;border-radius:999px;font-size:12px;font-weight:700;cursor:pointer;color:#605e5c}
#tab-articles .bk-art-tab.is-active{background:#eef6fc;color:#0078d4}
#tab-articles .bk-art-card{background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:16px;padding:16px;margin-bottom:14px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
#tab-articles .bk-art-toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:12px}
#tab-articles .bk-art-search{display:flex;align-items:center;border:1px solid rgba(0,0,0,.1);border-radius:12px;overflow:hidden;background:#fafafa;flex:1;min-width:200px}
#tab-articles .bk-art-search input{border:none;background:transparent;padding:10px 12px;flex:1;font-size:13px;outline:none}
#tab-articles select,#tab-articles input[type=text],#tab-articles input[type=url],#tab-articles input[type=date],#tab-articles input[type=time],#tab-articles textarea{
  border:1px solid rgba(0,0,0,.12);border-radius:10px;padding:9px 12px;font-size:13px;background:#fff;box-sizing:border-box}
#tab-articles .bk-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid rgba(0,0,0,.1);border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer;background:#fff;color:#1a1a1a;text-decoration:none;line-height:1}
#tab-articles .bk-btn-primary{background:#0078d4;border-color:#0078d4;color:#fff}
#tab-articles .bk-btn-ai{background:#f3e8ff;border-color:#d8b4fe;color:#6b21a8}
#tab-articles .bk-btn-icon{padding:8px;min-width:36px}
#tab-articles table{width:100%;border-collapse:collapse;font-size:13px}
#tab-articles th{text-align:right;padding:10px;background:#f8fafc;font-size:11px;color:#64748b;border-bottom:1px solid #e2e8f0}
#tab-articles td{padding:12px 10px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
#tab-articles .score-pill{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:800}
#tab-articles .bk-actions{display:flex;gap:6px;align-items:center;justify-content:flex-end}
#tab-articles .bk-field{margin-bottom:12px}
#tab-articles .bk-field label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:4px}
#tab-articles .bk-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:800px){#tab-articles .bk-grid-2{grid-template-columns:1fr}}
#tab-articles .bk-wp-timestamp{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:12px;background:#f6f7f7;border:1px solid #c3c4c7;border-radius:4px}
#tab-articles .bk-wp-timestamp .bk-ts-field{display:flex;flex-direction:column;gap:4px}
#tab-articles .bk-wp-timestamp label{font-size:11px;font-weight:600}
#tab-articles .bk-gen-progress{margin-top:14px;padding:14px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0}
#tab-articles .bk-gen-bar{height:8px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin:10px 0}
#tab-articles .bk-gen-bar>i{display:block;height:100%;background:linear-gradient(90deg,#0078d4,#00bcf2);transition:width .35s ease;border-radius:999px}
#tab-articles .bk-gen-steps{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
#tab-articles .bk-gen-step{font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;background:#fff;border:1px solid #e2e8f0;color:#64748b}
#tab-articles .bk-gen-step.is-done{background:#e8f5e9;border-color:#a5d6a7;color:#0f7b3a}
#tab-articles .bk-gen-step.is-run{background:#eef6fc;border-color:#90caf9;color:#0078d4}

/* Full in-panel editor */
#tab-articles .bk-editor-shell{display:none}
#tab-articles .bk-editor-shell.is-open{display:block}
#tab-articles .bk-editor-topbar{
  display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;
  padding:12px 14px;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;margin-bottom:12px;
  position:sticky;top:32px;z-index:20;box-shadow:0 4px 16px rgba(0,0,0,.06);margin-bottom:20px
}
#tab-articles .bk-editor-layout{
  display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start;margin-top:8px
}
@media(max-width:1100px){#tab-articles .bk-editor-layout{grid-template-columns:1fr}}


#tab-articles .bk-ai-progress{margin:10px 0 12px}
#tab-articles .bk-ai-progress-track{height:8px;background:#e2e8f0;border-radius:999px;overflow:hidden}
#tab-articles .bk-ai-progress-fill{height:100%;background:linear-gradient(90deg,#7c3aed,#0078d4);border-radius:999px;transition:width .35s ease}
#tab-articles .bk-ai-progress-meta{display:flex;justify-content:space-between;font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px}
#tab-articles .bk-subtabs{display:flex;gap:6px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-bottom:10px}
#tab-articles .bk-subtabs button{border:none;background:transparent;padding:6px 10px;font-size:11px;font-weight:700;color:#64748b;cursor:pointer;border-radius:8px}
#tab-articles .bk-subtabs button.is-on{background:#eef6fc;color:#0078d4}
#tab-articles .bk-link-stat{display:flex;gap:8px;margin-bottom:10px}
#tab-articles .bk-link-stat span{font-size:11px;font-weight:800;padding:4px 10px;border-radius:999px}
#tab-articles .bk-match-row{border:1px solid #e2e8f0;border-radius:10px;padding:8px;margin-bottom:6px;font-size:11px;background:#fafbfc}
#tab-articles .bk-post-row{display:flex;gap:8px;align-items:flex-start;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:12px}
#tab-articles .bk-img-card{border:1px solid #e2e8f0;border-radius:10px;padding:8px;margin-bottom:8px;background:#fff}
#tab-articles .bk-img-card img{width:100%;max-height:100px;object-fit:cover;border-radius:6px;background:#e2e8f0}
#tab-articles .bk-size-before{color:#c42b1c;font-weight:700}
#tab-articles .bk-size-after{color:#0f7b3a;font-weight:700}
#tab-articles .bk-editor-main{position:relative}
#tab-articles .bk-editor-loader{
  position:absolute;inset:0;z-index:30;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;
  background:rgba(255,255,255,.55);
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border-radius:14px;
  transition:opacity .25s ease,visibility .25s ease
}
#tab-articles .bk-editor-loader[hidden],
#tab-articles .bk-editor-loader.is-hide{opacity:0;visibility:hidden;pointer-events:none}
#tab-articles .bk-editor-loader .bk-spin{
  width:40px;height:40px;border-radius:50%;
  border:3px solid #e2e8f0;border-top-color:#0078d4;
  animation:bkArtSpin .8s linear infinite
}
@keyframes bkArtSpin{to{transform:rotate(360deg)}}
#tab-articles .bk-editor-loader-text{font-size:13px;font-weight:700;color:#475569}
#tab-articles .bk-editor-main.is-loading .bk-lite-editor,
#tab-articles .bk-editor-main.is-loading .bk-editor-title{
  filter:blur(6px);pointer-events:none;user-select:none
}
#tab-articles .bk-editor-main{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;overflow:hidden;min-height:70vh;margin-top:4px}
#tab-articles .bk-editor-title{
  width:100%;border:none;border-bottom:1px solid #eef0f2;padding:16px 18px;font-size:22px;font-weight:800;outline:none
}
#tab-articles .bk-editor-toolbar{
  display:flex;flex-wrap:wrap;gap:4px;padding:8px 10px;background:#f8fafc;border-bottom:1px solid #eef0f2
}
#tab-articles .bk-editor-toolbar button{
  border:1px solid transparent;background:#fff;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:700;cursor:pointer;color:#334155
}
#tab-articles .bk-editor-toolbar button:hover{border-color:#cbd5e1;background:#fff}
#tab-articles .bk-editor-body{
  min-height:480px;padding:16px 18px;outline:none;line-height:1.85;font-size:15px;direction:rtl;
  max-height:calc(100vh - 220px);overflow:auto
}
#tab-articles .bk-tmce-wrap{padding:0 0 8px}
#tab-articles .bk-tmce-wrap .mce-tinymce{border:none!important;box-shadow:none!important}
#tab-articles .bk-tmce-wrap .wp-editor-tools{padding:8px 12px;background:#f8fafc;border-bottom:1px solid #eef0f2}
#tab-articles .bk-tmce-wrap iframe{min-height:420px!important}
#tab-articles .bk-art-tmce{width:100%;min-height:420px}
#tab-articles .bk-wp-editor-wrap .wp-editor-wrap{border:none}
#tab-articles .bk-wp-editor-wrap .wp-editor-tools{padding:8px 12px;background:#f8fafc;border-bottom:1px solid #eef0f2}
#tab-articles .bk-wp-editor-wrap .wp-editor-container{border:none!important}
#tab-articles .bk-wp-editor-wrap .mce-tinymce{box-shadow:none!important;border:none!important}
#tab-articles .bk-wp-editor-wrap iframe{min-height:420px!important}

#tab-articles .mce-ico, #tab-articles .mce-btn i { font-family: tinymce,Arial!important; }
#tab-articles .mce-toolbar .mce-btn { background:#fff; }
#tab-articles .wp-editor-wrap { border:none; }
#tab-articles .wp-editor-tools { padding:10px 12px; background:#f8fafc; border-bottom:1px solid #eef0f2; }
#tab-articles .bk-cover-box { padding:12px 16px; border-bottom:1px solid #eef0f2; background:#fafbfc; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
#tab-articles .bk-cover-box img { width:96px; height:64px; object-fit:cover; border-radius:8px; background:#e2e8f0; }

#tab-articles .bk-editor-body h2{font-size:1.35em;margin:1em 0 .4em}
#tab-articles .bk-editor-body h3{font-size:1.15em;margin:.9em 0 .35em}
#tab-articles .bk-editor-body a{color:#0078d4}

/* SEO sidebar (mirrors post editor sidebox) */
#tab-articles .bk-seo-side{
  background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;overflow:hidden;
  position:sticky;top:100px;max-height:calc(100vh - 120px);display:flex;flex-direction:column
}
#tab-articles .bk-seo-side-head{
  display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #eef0f2;background:#fafbfc
}
#tab-articles .bk-score-ring{position:relative;width:44px;height:44px;flex-shrink:0}
#tab-articles .bk-score-ring svg{position:absolute;inset:0;width:100%;height:100%;transform:rotate(-90deg)}
#tab-articles .bk-score-ring .bk-track{fill:none;stroke:#eaeef2;stroke-width:3}
#tab-articles .bk-score-ring .bk-progress{fill:none;stroke-width:3;stroke-linecap:round}
#tab-articles .bk-score-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800}
#tab-articles .bk-seo-tabs{display:flex;flex-wrap:wrap;gap:4px;padding:8px;border-bottom:1px solid #eef0f2}
#tab-articles .bk-seo-tabs button{
  border:none;background:transparent;padding:6px 10px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;color:#64748b
}
#tab-articles .bk-seo-tabs button.is-on{background:#eef6fc;color:#0078d4}
#tab-articles .bk-seo-panel{padding:12px;overflow:auto;flex:1}
#tab-articles .bk-seo-panel .bk-hint{font-size:11px;color:#94a3b8;margin-top:4px}

#tab-articles .bk-lite-editor{border-top:1px solid #eef0f2}
#tab-articles .bk-lite-toolbar{display:flex;flex-wrap:wrap;gap:4px;padding:8px 10px;background:#f8fafc;border-bottom:1px solid #eef0f2;align-items:center}
#tab-articles .bk-lite-toolbar button{border:1px solid transparent;background:#fff;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:700;cursor:pointer;color:#334155;min-width:32px}
#tab-articles .bk-lite-toolbar button:hover{border-color:#cbd5e1;background:#fff}
#tab-articles .bk-lite-toolbar button.is-on{background:#eef6fc;border-color:#90caf9;color:#0078d4}
#tab-articles .bk-lite-sep{width:1px;height:20px;background:#e2e8f0;margin:0 4px}
#tab-articles .bk-lite-body{min-height:420px;max-height:calc(100vh - 240px);overflow:auto;padding:16px 18px;outline:none;line-height:1.85;font-size:15px;direction:rtl;font-family:Tahoma,"Segoe UI",sans-serif}
#tab-articles .bk-lite-body:empty:before{content:attr(data-placeholder);color:#94a3b8;pointer-events:none}
#tab-articles .bk-lite-body h2{font-size:1.35em;margin:1em 0 .4em}
#tab-articles .bk-lite-body h3{font-size:1.15em;margin:.9em 0 .35em}
#tab-articles .bk-lite-body a{color:#0078d4}
#tab-articles .bk-lite-body img{max-width:100%;height:auto;border-radius:8px}

#tab-articles .bk-lite-toolbar{flex-direction:column;align-items:stretch;gap:6px}
#tab-articles .bk-lite-row{display:flex;flex-wrap:wrap;gap:4px;align-items:center}
#tab-articles .bk-lite-select{border:1px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:12px;background:#fff}
#tab-articles .bk-lite-color{width:28px;height:28px;border:1px solid #e2e8f0;border-radius:6px;padding:0;cursor:pointer;background:#fff}
#tab-articles .bk-lite-adv{display:flex;flex-wrap:wrap;gap:12px;align-items:center;padding:8px 12px;background:#f1f5f9;border-bottom:1px solid #e2e8f0;font-size:12px}
#tab-articles .bk-lite-adv label{display:inline-flex;align-items:center;gap:6px;font-weight:600}
#tab-articles .bk-media-card{border:1px solid #e2e8f0;border-radius:10px;padding:8px;margin-bottom:8px;background:#fafbfc}
#tab-articles .bk-media-card img{width:100%;max-height:90px;object-fit:cover;border-radius:6px;background:#e2e8f0;cursor:pointer}
#tab-articles .bk-media-meta{display:flex;justify-content:space-between;font-size:10px;margin-top:4px;font-weight:700}
#tab-articles .bk-lite-html{width:100%;min-height:420px;padding:12px;border:none;border-top:1px solid #eef0f2;font-family:ui-monospace,monospace;font-size:12px;box-sizing:border-box;direction:ltr}
#tab-articles .bk-cover-prev{width:100%;max-height:140px;object-fit:cover;border-radius:10px;background:#f1f5f9}

/* WP-style schedule popover — Jalali UI, Gregorian output */
.bk-sched-toggle{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1px solid #d0d7de;border-radius:10px;background:#fff;font-size:13px;font-weight:600;cursor:pointer;color:#1f2328;max-width:100%;text-align:right}
.bk-sched-toggle:hover{border-color:#0078d4;color:#0078d4}
.bk-sched-pop{position:fixed;z-index:1000000;width:min(340px,calc(100vw - 16px));max-height:min(520px,calc(100vh - 24px));overflow:auto;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 16px 48px rgba(15,23,42,.18);padding:14px;direction:rtl;box-sizing:border-box}
.bk-sched-pop-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.bk-sched-pop-head strong{font-size:14px}
.bk-sched-pop-actions{display:flex;gap:6px;align-items:center}
.bk-sched-now{border:none;background:transparent;color:#0078d4;font-weight:700;font-size:12px;cursor:pointer;padding:4px 8px}
.bk-sched-close{border:none;background:#f1f5f9;width:28px;height:28px;border-radius:8px;cursor:pointer;font-size:16px;line-height:1}
.bk-sched-legend{display:block;font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px}
.bk-sched-time-row,.bk-sched-date-row{margin-bottom:12px}
.bk-sched-time-fields,.bk-sched-date-fields{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.bk-sched-num{width:52px;padding:8px;border:1px solid #d0d7de;border-radius:8px;text-align:center;font-weight:700;font-size:13px}
.bk-sched-year{width:72px}
.bk-sched-month{flex:1;min-width:110px;padding:8px;border:1px solid #d0d7de;border-radius:8px;font-size:12px;font-weight:600}
.bk-sched-ampm{display:inline-flex;border:1px solid #d0d7de;border-radius:8px;overflow:hidden}
.bk-sched-ampm button{border:none;background:#f8fafc;padding:8px 10px;font-size:11px;font-weight:700;cursor:pointer}
.bk-sched-ampm button.is-on{background:#0078d4;color:#fff}
.bk-sched-tz{font-size:11px;color:#94a3b8;font-weight:600;margin-right:4px}
.bk-sched-cal{border:1px solid #eef2f7;border-radius:12px;padding:10px;background:#fafbfc}
.bk-sched-cal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.bk-sched-cal-head button{border:1px solid #e2e8f0;background:#fff;width:32px;height:32px;border-radius:8px;cursor:pointer;font-weight:800}
.bk-sched-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center}
.bk-sched-cal-grid .dow{font-size:10px;color:#94a3b8;font-weight:700;padding:4px 0}
.bk-sched-cal-grid button.day{border:none;background:transparent;height:34px;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600}
.bk-sched-cal-grid button.day:hover:not(:disabled){background:#eef6fc;color:#0078d4}
.bk-sched-cal-grid button.day.is-today{box-shadow:inset 0 0 0 1px #0078d4}
.bk-sched-cal-grid button.day.is-sel{background:#0078d4;color:#fff}
.bk-sched-cal-grid button.day.muted{opacity:.25;cursor:default}
.bk-sched-hint{margin:10px 0 0;font-size:11px;color:#64748b}
.bk-sched-hint code{font-size:10px;background:#f1f5f9;padding:2px 6px;border-radius:4px;direction:ltr;display:inline-block}

#tab-articles .bk-wiz-hero{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-bottom:12px;border-bottom:1px solid #d8dee4;margin-bottom:14px}
#tab-articles .bk-wiz-hero h3{margin:0;font-size:16px;font-weight:700;color:#1f2328}
#tab-articles .bk-wiz-hero p{margin:4px 0 0;font-size:12px;color:#656d76}
#tab-articles .bk-wiz-steps{display:flex;gap:6px;flex-wrap:wrap}
#tab-articles .bk-wiz-step{border:1px solid #d0d7de;background:#f6f8fa;color:#1f2328;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer}
#tab-articles .bk-wiz-step.is-on{background:#0969da;border-color:#0969da;color:#fff}
#tab-articles .bk-wiz-suggest-row{display:flex;gap:8px;align-items:flex-start;padding:8px 10px;border:1px solid #d8dee4;border-radius:8px;margin-bottom:6px;background:#f6f8fa;font-size:13px}
#tab-articles .bk-wiz-item{border:1px solid #d0d7de;border-radius:10px;padding:12px;margin-bottom:10px;background:#fff}
#tab-articles .bk-wiz-item-title{display:flex;gap:8px;align-items:center}
#tab-articles .bk-wiz-job{border:1px solid #d0d7de;border-radius:10px;padding:12px;margin-bottom:10px;background:#f6f8fa}
#tab-articles .bk-wiz-job.is-done{background:#dafbe1;border-color:#4ac26b}
#tab-articles .bk-wiz-job.is-error{background:#ffebe9;border-color:#ff8182}
#tab-articles .bk-wiz-job.is-run{background:#ddf4ff;border-color:#54aeff}
#tab-articles .bk-wiz-job-head{display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:8px}
#tab-articles .bk-wiz-badge{font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;background:#fff;border:1px solid #d0d7de}
#tab-articles .bk-btn-primary{background:#0969da!important;color:#fff!important;border:1px solid #0969da!important}


/* === AI Wizard full theme (GitHub Light) === */
#tab-articles .bk-wiz{border:1px solid #d0d7de;border-radius:12px;background:#fff;padding:18px 20px;box-shadow:0 1px 0 rgba(31,35,40,.04)}
#tab-articles .bk-wiz-topbar{margin-bottom:16px}
#tab-articles .bk-wiz-topbar h3{margin:0;font-size:17px;font-weight:700;color:#1f2328;letter-spacing:-.01em}
#tab-articles .bk-wiz-topbar p{margin:4px 0 0;font-size:12px;color:#656d76;line-height:1.5}
#tab-articles .bk-wiz-global-progress{margin-top:12px;padding:10px 12px;background:#f6f8fa;border:1px solid #d0d7de;border-radius:10px}
#tab-articles .bk-wiz-engines{margin-bottom:18px}
#tab-articles .bk-wiz-engines-label{font-size:11px;font-weight:700;color:#656d76;margin-bottom:8px;text-transform:none}
#tab-articles .bk-wiz-engine-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(132px,1fr));gap:8px}
#tab-articles .bk-wiz-engine-card{display:flex;flex-direction:column;align-items:flex-start;gap:3px;padding:12px;border:1px solid #d0d7de;border-radius:10px;background:#f6f8fa;cursor:pointer;text-align:start;transition:border-color .15s,box-shadow .15s,background .15s}
#tab-articles .bk-wiz-engine-card:hover:not(:disabled){border-color:#0969da;background:#fff}
#tab-articles .bk-wiz-engine-card strong{font-size:12px;color:#1f2328;font-weight:700}
#tab-articles .bk-wiz-engine-card small{font-size:10px;color:#656d76}
#tab-articles .bk-wiz-engine-card.is-active{border-color:#0969da;background:#ddf4ff;box-shadow:0 0 0 1px #0969da}
#tab-articles .bk-wiz-engine-card.is-disabled,#tab-articles .bk-wiz-engine-card:disabled{opacity:.5;cursor:not-allowed}
#tab-articles .bk-wiz-engine-dot{width:8px;height:8px;border-radius:50%;margin-bottom:2px}
#tab-articles .bk-wiz-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:700px){#tab-articles .bk-wiz-row2{grid-template-columns:1fr}}
#tab-articles .bk-wiz textarea,#tab-articles .bk-wiz input[type=text],#tab-articles .bk-wiz input[type=number],#tab-articles .bk-wiz input[type=datetime-local],#tab-articles .bk-wiz select{
  width:100%;box-sizing:border-box;border:1px solid #d0d7de;border-radius:8px;padding:8px 10px;font-size:13px;background:#fff;color:#1f2328;font-family:inherit}
#tab-articles .bk-wiz textarea:focus,#tab-articles .bk-wiz input:focus,#tab-articles .bk-wiz select:focus{outline:none;border-color:#0969da;box-shadow:0 0 0 3px rgba(9,105,218,.15)}
#tab-articles .bk-wiz-loader{text-align:center;padding:48px 16px}
#tab-articles .bk-wiz-spinner{width:40px;height:40px;border:3px solid #d0d7de;border-top-color:#0969da;border-radius:50%;margin:0 auto 14px;animation:bkWizSpin .7s linear infinite}
@keyframes bkWizSpin{to{transform:rotate(360deg)}}
#tab-articles .bk-wiz-loader p{margin:0;font-size:13px;color:#656d76}
#tab-articles .bk-wiz-title-card{border:1px solid #d0d7de;border-radius:12px;padding:14px 16px;margin-bottom:12px;background:#fff;transition:border-color .15s,background .15s}
#tab-articles .bk-wiz-title-card.is-checked{border-color:#1a7f37;background:#f0fff4}
#tab-articles .bk-wiz-check{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;margin-bottom:10px;color:#1f2328}
#tab-articles .bk-wiz-title-input{width:100%;font-weight:700;font-size:14px;border:1px solid #d0d7de;border-radius:8px;padding:10px 12px;margin-bottom:10px;box-sizing:border-box}
#tab-articles .bk-wiz-card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
@media(max-width:800px){#tab-articles .bk-wiz-card-grid{grid-template-columns:1fr}}
#tab-articles .bk-wiz-checklist{margin:10px 0 0;padding:0;list-style:none;font-size:11px;color:#656d76}
#tab-articles .bk-wiz-checklist li{padding:4px 0 4px 18px;position:relative}
#tab-articles .bk-wiz-checklist li:before{content:'○';position:absolute;inset-inline-start:0;color:#8c959f}
#tab-articles .bk-wiz-checklist li.is-done{color:#1a7f37}
#tab-articles .bk-wiz-checklist li.is-done:before{content:'●'}
#tab-articles .bk-ai-progress{margin-top:6px}
#tab-articles .bk-ai-progress-meta{display:flex;justify-content:space-between;font-size:11px;font-weight:600;color:#656d76;margin-bottom:4px}
#tab-articles .bk-ai-progress-track{height:8px;background:#eaeef2;border-radius:999px;overflow:hidden}
#tab-articles .bk-ai-progress-fill{height:100%;background:linear-gradient(90deg,#0969da,#7c3aed);border-radius:999px;transition:width .35s ease}
#tab-articles .bk-wiz-paused{display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:12px 14px;margin:12px 0;background:#fff8c5;border:1px solid #d4a72c;border-radius:10px}
#tab-articles .bk-wiz-paused strong{font-size:13px;color:#7d4e00}
#tab-articles .bk-wiz-paused p{margin:0;font-size:12px;color:#7d4e00;flex:1 1 200px}


#tab-articles .bk-art-main{min-height:0}
#tab-articles .bk-btn-primary{background:linear-gradient(135deg,#0969da,#7c3aed)!important;border:none!important;color:#fff!important;box-shadow:0 1px 2px rgba(9,105,218,.2)}
#tab-articles .bk-btn-primary:hover{filter:brightness(1.05)}
#tab-articles a.bk-btn{text-decoration:none;display:inline-flex;align-items:center;gap:6px}
#tab-articles .bk-art-tab.is-active{background:linear-gradient(135deg,#ddf4ff,#fbefff);color:#0969da}
#tab-articles .bk-art-card{border-color:#d0d7de}

</style>

    <!-- LIST / GENERATE / SCHEDULE (hidden while editor open) -->
    <div class="bk-art-main">
        <div class="bk-art-head">
            <div>
                <h2>
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    مدیریت مقالات
                </h2>
                <p>فهرست مقالات، تولید و زمان‌بندی با هوش مصنوعی — ویرایش در وردپرس</p>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" @click="panel='generate'">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
                تولید مقاله با AI
            </button>
        </div>

        <div class="bk-art-tabs">
            <button type="button" class="bk-art-tab" :class="{'is-active': panel==='list'}" @click="panel='list'; loadArticles()">مشاهده مقالات</button>
            <button type="button" class="bk-art-tab" :class="{'is-active': panel==='missing-seo'}" @click="panel='missing-seo'; initMissingSeo()">مقالات بدون سئو</button>
            <button type="button" class="bk-art-tab" :class="{'is-active': panel==='generate'}" @click="panel='generate'">تولید با AI</button>
            <button type="button" class="bk-art-tab" :class="{'is-active': panel==='schedule'}" @click="panel='schedule'">زمان‌بندی</button>
        </div>

        <div class="bk-art-card" x-show="panel==='list'" x-cloak>
            <div class="bk-art-toolbar">
                <div class="bk-art-search">
                    <input type="search" x-model="searchQ" @keydown.enter="page=1;loadArticles()" placeholder="جستجوی عنوان…">
                    <button type="button" class="bk-btn-icon" @click="page=1;loadArticles()" style="border:none;background:transparent;cursor:pointer;color:#0078d4">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </div>
                <select x-model="orderby" @change="loadArticles()">
                    <option value="date">تاریخ انتشار (جدید → قدیم)</option>
                    <option value="modified">آخرین ویرایش</option>
                    <option value="seo_score">امتیاز سئو</option>
                    <option value="views">بازدید</option>
                    <option value="title">عنوان</option>
                </select>
                <select x-model="order" @change="loadArticles()">
                    <option value="DESC">نزولی</option>
                    <option value="ASC">صعودی</option>
                </select>
                <select x-model="perPage" @change="page=1;loadArticles()">
                    <option value="10">۱۰</option>
                    <option value="25">۲۵</option>
                    <option value="50">۵۰</option>
                </select>
                <button type="button" class="bk-btn" @click="loadArticles()">تازه‌سازی</button>
            </div>
            <div style="overflow:auto">
                <table>
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>سئو</th>
                            <th>بازدید</th>
                            <th>لینک داخلی</th>
                            <th>لینک خارجی</th>
                            <th>وضعیت</th>
                            <th style="text-align:left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr x-show="loading"><td colspan="7" style="text-align:center;padding:24px;color:#8a8886">در حال بارگذاری…</td></tr>
                        <tr x-show="!loading && !articles.length"><td colspan="7" style="text-align:center;padding:24px;color:#8a8886">مقاله‌ای یافت نشد.</td></tr>
                        <template x-for="row in articles" :key="row.id">
                            <tr>
                                <td>
                                    <strong x-text="row.title"></strong>
                                    <div style="font-size:11px;color:#8a8886;margin-top:2px" x-text="row.date || row.modified"></div>
                                </td>
                                <td><span class="score-pill" :style="'background:' + scoreBg(row.score) + ';color:' + scoreColor(row.score)" x-text="(row.score||0)+'%'"></span></td>
                                <td x-text="row.views ?? '—'"></td>
                                <td x-text="row.internal_links ?? row.stats?.internal ?? '—'"></td>
                                <td x-text="row.external_links ?? row.stats?.external ?? '—'"></td>
                                <td x-text="row.status_label || row.status"></td>
                                <td>
                                    <div class="bk-actions">
                                        <a class="bk-btn bk-btn-primary" :href="wpEditUrl(row.id)" title="ویرایش در وردپرس">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                            ویرایش مقاله
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;font-size:12px;font-weight:600;color:#605e5c">
                <span x-text="'صفحه ' + page + ' از ' + Math.max(1, totalPages)"></span>
                <div style="display:flex;gap:8px">
                    <button type="button" class="bk-btn" :disabled="page<=1" @click="page--;loadArticles()">قبلی</button>
                    <button type="button" class="bk-btn" :disabled="page>=totalPages" @click="page++;loadArticles()">بعدی</button>
                </div>
            </div>
        </div>


        <!-- Missing SEO AI Assistant -->
        <div class="bk-art-card bk-import-ai" x-show="panel==='missing-seo'" x-cloak style="padding:0;overflow:hidden;">
            <div class="bk-ai-assistant-shell" style="border:none;box-shadow:none;margin:0;">
                <header class="bk-modal-head">
                    <div class="bk-modal-title">
                        <div class="bk-ai-logo-icon">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        </div>
                        <div>
                            <strong>دستیار هوشمند سئو و محتوا Bankai</strong>
                            <small>سئوی خودکار مقالات، برگه‌ها و محصولات بدون متای Bankai — متا، کلیدواژه، لینک، تصویر، اسکیما و دسته/برچسب</small>
                        </div>
                    </div>
                </header>

                <div class="bk-ai-engine-bar">
                    <div class="bk-ai-engine-label">انتخاب موتور AI</div>
                    <div class="bk-ai-engine-scroll">
                        <template x-for="p in (ms.textProviders || [])" :key="'mt'+p.id">
                            <button type="button" class="bk-ai-engine-card"
                                :class="{ 'is-active': ms.textProvider === p.id, 'is-disabled': !p.ready }"
                                @click="ms.textProvider = p.id">
                                <span class="bk-ai-engine-name" x-text="p.name"></span>
                                <span class="bk-ai-engine-status" x-text="ms.textProvider === p.id ? 'فعال' : (p.ready ? 'انتخاب' : 'بدون کلید')"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="bk-import-toolbar">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <strong style="font-size:13px;">مقالات بدون سئوی Bankai</strong>
                        <span style="font-size:12px;color:#64748b;" x-text="(ms.total||0) + ' مورد'"></span>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button type="button" class="bk-btn-ghost" @click="msToggleAll()" :disabled="ms.aiRunning" x-text="ms.allSelected ? 'لغو انتخاب همه' : 'انتخاب همه'"></button>
                        <button type="button" class="bk-btn-ghost" @click="loadMissingSeoList()" :disabled="ms.aiRunning">تازه‌سازی</button>
                        <button type="button" class="bk-btn-primary" @click="runMissingSeoBatch()"
                            :disabled="ms.aiRunning || !msSelectedCount() || !ms.textProvider"
                            x-text="ms.aiRunning ? ('در حال اجرا… '+(ms.aiProgress||0)+'%') : ('اجرای یک‌پارچه ('+msSelectedCount()+')')"></button>
                        <button type="button" class="bk-btn-ghost" x-show="ms.aiRunning" @click="ms.aiAbort=true">توقف</button>
                        <button type="button" class="bk-btn-success" x-show="!ms.aiRunning && msReadyCount()>0"
                            @click="msApproveAll()" x-text="'اعمال تغییرات تاییدشده ('+msReadyCount()+')'"></button>
                    </div>
                </div>

                <div style="padding:14px 16px;">
                    <template x-if="ms.loaded && !(ms.items||[]).length">
                        <div style="padding:28px 16px;text-align:center;border:1px dashed #86efac;border-radius:14px;background:#f0fdf4;">
                            <div style="font-size:28px;margin-bottom:8px;">✓</div>
                            <strong style="display:block;font-size:15px;color:#166534;margin-bottom:6px;">عالی — همه چیز سئو شده است</strong>
                            <p style="margin:0;font-size:13px;color:#15803d;line-height:1.6;">مقاله، برگه یا محصولی بدون سئوی Bankai باقی نمانده است.</p>
                        </div>
                    </template>
                    <template x-if="!ms.loaded">
                        <p style="margin:0;color:#64748b;font-size:13px;">در حال بررسی مقالات، برگه‌ها و محصولات…</p>
                    </template>
                    <template x-for="row in (ms.items||[])" :key="row.id">
                        <div class="bk-post-ai-card"
                             :class="{'is-running':row.aiPhase==='running','is-review':row.aiPhase==='review','is-done':row.aiPhase==='done','is-error':row.aiPhase==='error'}">
                            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                                <div style="display:flex;gap:10px;align-items:flex-start;">
                                    <input type="checkbox" :value="row.id" x-model="ms.selected" :disabled="ms.aiRunning && row.aiPhase==='running'">
                                    <div>
                                        <div style="font-weight:800;font-size:14px;" x-text="row.title"></div>
                                        <div style="font-size:12px;color:#64748B;margin-top:2px;">
                                            <span x-show="row.type_label" x-text="row.type_label" style="margin-inline-end:6px;font-weight:700;color:#6b4eff;"></span>
                                            <span x-text="row.status"></span>
                                            <span x-show="row.score"> · امتیاز: <strong x-text="row.score"></strong></span>
                                            <span x-show="row.aiPhase" style="margin-inline-start:6px;" x-text="msPhaseLabel(row.aiPhase)"></span>
                                        </div>
                                    </div>
                                </div>
                                <a :href="row.edit_url" target="_blank" rel="noopener" style="font-size:12px;">ویرایش مطلب ↗</a>
                            </div>
                            <div x-show="row.aiPhase==='running' || (row.aiProgress||0)>0" style="margin-top:12px;">
                                <div style="display:flex;justify-content:space-between;font-size:12px;color:#475569;">
                                    <span x-text="row.aiStepText||''"></span>
                                    <span x-text="(row.aiProgress||0)+'%'"></span>
                                </div>
                                <div class="bk-progress-track"><div class="bk-progress-fill" :style="'width:'+(row.aiProgress||0)+'%'"></div></div>
                            </div>
                            <ul class="bk-checklist-grid" x-show="row.checklist && row.checklist.length">
                                <template x-for="item in row.checklist" :key="item.id">
                                    <li>
                                        <span x-show="item.status==='pending'" style="opacity:.35;">○</span>
                                        <span x-show="item.status==='loading'">…</span>
                                        <span x-show="item.status==='success'" style="color:#16a34a;font-weight:800;">✓</span>
                                        <span x-show="item.status==='error'" style="color:#dc2626;font-weight:800;">✕</span>
                                        <span x-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                            <div x-show="row.aiPhase==='review' || row.aiPhase==='done' || row.aiPhase==='error'" style="margin-top:14px;padding-top:12px;border-top:1px solid #E2E8F0;">
                                <h5 style="margin:0 0 8px;font-size:13px;font-weight:800;">خلاصه نتایج (Review & Approve)</h5>
                                <div class="bk-review-box" x-show="row.draft">
                                    <div><strong>عنوان سئو:</strong> <span x-text="(row.draft&&row.draft.seo_title)||'—'"></span></div>
                                    <div><strong>متا:</strong> <span x-text="(row.draft&&row.draft.description)||'—'"></span></div>
                                    <div><strong>کلیدواژه:</strong> <span x-text="(row.draft&&row.draft.focus_keyword)||'—'"></span></div>
                                    <div><strong>کلیدواژه‌ها:</strong> <span x-text="(row.draft&&row.draft.keywords&&row.draft.keywords.join('، '))||'—'"></span></div>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;" x-show="row.aiPhase==='review'">
                                    <button type="button" class="bk-btn-success" @click="msApprove(row)" :disabled="row.saving" x-text="row.saving?'در حال ذخیره…':'اعمال تغییرات تاییدشده'"></button>
                                    <button type="button" class="bk-btn-ghost" @click="msRerun(row)" :disabled="ms.aiRunning">بازتولید</button>
                                    <button type="button" class="bk-btn-ghost" @click="msCancel(row)">لغو</button>
                                </div>
                                <div x-show="row.aiPhase==='done'" style="margin-top:8px;color:#166534;font-weight:700;font-size:13px;">✓ ذخیره شد — امتیاز: <span x-text="row.score"></span></div>
                                <div x-show="row.aiPhase==='error'" style="margin-top:8px;color:#991b1b;font-size:13px;" x-text="row.aiError||'خطا'"></div>
                            </div>
                        </div>
                    </template>
                    <div style="margin-top:10px;display:flex;gap:8px;align-items:center;" x-show="ms.pages>1">
                        <button type="button" class="bk-btn-ghost" :disabled="ms.page<=1||ms.aiRunning" @click="ms.page--; loadMissingSeoList()">قبلی</button>
                        <span x-text="ms.page+' / '+ms.pages"></span>
                        <button type="button" class="bk-btn-ghost" :disabled="ms.page>=ms.pages||ms.aiRunning" @click="ms.page++; loadMissingSeoList()">بعدی</button>
                    </div>
                </div>
            </div>
        </div>

                        <div class="bk-art-card bk-wiz" x-show="panel==='generate'" x-cloak>
            <!-- Global progress -->
            <div class="bk-wiz-topbar">
                <div>
                    <h3>ویزارد هوشمند تولید و زمان‌بندی مقاله با AI</h3>
                    <p>پیشنهاد عنوان بر اساس مقالات موجود، نگارش و بهینه‌سازی کامل سئو</p>
                </div>
                <div class="bk-wiz-global-progress" x-show="batchBusy || wizBusy || wiz.globalProgress > 0" x-cloak>
                    <div class="bk-ai-progress-meta">
                        <span x-text="wiz.globalLabel || 'در حال پردازش…'"></span>
                        <span x-text="(wiz.globalProgress||0) + '%'"></span>
                    </div>
                    <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" :style="'width:' + (wiz.globalProgress||0) + '%'"></div></div>
                </div>
            </div>

            <!-- AI engines as cards -->
            <div class="bk-wiz-engines">
                <div class="bk-wiz-engines-label">موتور AI فعال</div>
                <div class="bk-wiz-engine-grid">
                    <template x-for="p in wizProviders" :key="p.id">
                        <button type="button" class="bk-wiz-engine-card"
                                :class="{'is-active': wiz.provider===p.id, 'is-disabled': !p.has_key}"
                                :disabled="!p.has_key"
                                @click="wiz.provider=p.id">
                            <span class="bk-wiz-engine-dot" :style="p.has_key ? 'background:#1a7f37' : 'background:#cf222e'"></span>
                            <strong x-text="p.name || p.id"></strong>
                            <small x-text="p.has_key ? 'آماده' : 'بدون کلید'"></small>
                        </button>
                    </template>
                </div>
            </div>

            <!-- SETUP FORM (hidden while loading titles or after titles ready) -->
            <div x-show="!wizBusy && wiz.phase==='setup'" x-cloak>
                <div class="bk-field">
                    <label>راهنمای انتخاب / تولید عنوان‌ها</label>
                    <textarea x-model="wiz.topic" rows="3" placeholder="موضوع، زاویه محتوا یا کلمه کلیدی… عناوین و کلیدواژه‌های مقالات قبلی سایت به‌صورت خودکار به AI ارسال می‌شود."></textarea>
                </div>
                <div class="bk-wiz-row2">
                    <div class="bk-field">
                        <label>تعداد پیشنهاد</label>
                        <select x-model.number="wiz.count">
                            <option :value="5">۵</option>
                            <option :value="10">۱۰</option>
                            <option :value="15">۱۵</option>
                            <option :value="20">۲۰</option>
                        </select>
                    </div>
                    <div class="bk-field">
                        <label>طول پیش‌فرض مقاله (کلمه)</label>
                        <select x-model="wiz.defaultLength">
                            <option value="1500">۱٬۵۰۰</option>
                            <option value="3000">۳٬۰۰۰</option>
                            <option value="5000">۵٬۰۰۰</option>
                            <option value="7000">۷٬۰۰۰</option>
                        </select>
                    </div>
                </div>
                <div class="bk-field">
                    <label>دستورالعمل سراسری (اختیاری)</label>
                    <textarea x-model="wiz.defaultNotes" rows="3" placeholder="لحن، مخاطب، نکات اجباری…"></textarea>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
                    <button type="button" class="bk-btn bk-btn-primary" @click="suggestTitles()" :disabled="!wiz.topic.trim()">
                        دریافت پیشنهاد عنوان از AI
                    </button>
                    <button type="button" class="bk-btn" @click="addManualTitle()">افزودن عنوان دستی</button>
                </div>
            </div>

            <!-- LOADING titles -->
            <div class="bk-wiz-loader" x-show="wizBusy" x-cloak>
                <div class="bk-wiz-spinner"></div>
                <p>در حال تولید عناوین بر اساس مقالات و کلیدواژه‌های سایت…</p>
            </div>

            <!-- TITLE CARDS -->
            <div x-show="!wizBusy && wiz.phase==='titles'" x-cloak>
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap">
                    <strong style="font-size:13px">عناوین پیشنهادی — تأیید و تنظیم کنید</strong>
                    <button type="button" class="bk-btn" @click="wiz.phase='setup'; wiz.selected=[]; wiz.suggestions=[]">بازگشت به فرم</button>
                </div>
                <template x-for="(item, idx) in wiz.selected" :key="'card'+idx">
                    <div class="bk-wiz-title-card" :class="{'is-checked': item.approved}">
                        <label class="bk-wiz-check">
                            <input type="checkbox" x-model="item.approved">
                            <span>تأیید برای تولید</span>
                        </label>
                        <input type="text" class="bk-wiz-title-input" x-model="item.title">
                        <div class="bk-wiz-card-grid">
                            <div class="bk-field">
                                <label>طول مقاله</label>
                                <select x-model="item.length">
                                    <option value="1500">۱٬۵۰۰</option>
                                    <option value="3000">۳٬۰۰۰</option>
                                    <option value="5000">۵٬۰۰۰</option>
                                    <option value="7000">۷٬۰۰۰</option>
                                </select>
                            </div>
                            <div class="bk-field">
                                <label>وضعیت انتشار</label>
                                <select x-model="item.pubStatus">
                                    <option value="draft">پیش‌نویس</option>
                                    <option value="publish">انتشار فوری</option>
                                    <option value="future">زمان‌بندی</option>
                                </select>
                            </div>
                            <div class="bk-field" x-show="item.pubStatus==='future'" x-cloak style="grid-column:1/-1">
                                <label>تاریخ انتشار (جلالی)</label>
                                <button type="button" class="bk-sched-toggle" @click="openWizItemSchedule(idx)"
                                        :aria-expanded="wizSchedIdx===idx && wizItemSchedOpen ? 'true' : 'false'">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    <span x-text="wizItemScheduleLabel(item)"></span>
                                </button>
                            </div>
                        </div>
                        <div class="bk-field" style="margin-top:6px">
                            <label>دستورالعمل این عنوان</label>
                            <input type="text" x-model="item.notes" :placeholder="wiz.defaultNotes || 'ارث از سراسری'">
                        </div>
                    </div>
                </template>
                <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
                    <button type="button" class="bk-btn bk-btn-primary" @click="startProductionFromCards()"
                            :disabled="batchBusy || !wiz.selected.filter(s=>s.approved).length">
                        تهیه و تولید مقاله
                    </button>
                    <span style="font-size:12px;color:#656d76;align-self:center"
                          x-text="wiz.selected.filter(s=>s.approved).length + ' عنوان تأیید شده'"></span>
                </div>
            </div>

            <!-- PRODUCTION QUEUE -->
            <div x-show="wiz.phase==='produce'" x-cloak style="margin-top:8px">
                <div class="bk-wiz-paused" x-show="wiz.paused" x-cloak>
                    <strong>توقف به‌خاطر خطا</strong>
                    <p x-text="wiz.pauseMessage || 'یک مقاله با خطا متوقف شد. اتصال AI را بررسی و ادامه دهید.'"></p>
                    <button type="button" class="bk-btn bk-btn-primary" @click="continueBatchAfterCheck()" :disabled="batchBusy || wiz.checkingConn">
                        <span x-text="wiz.checkingConn ? 'بررسی اتصال…' : 'ادامه فرایند'"></span>
                    </button>
                    <button type="button" class="bk-btn" @click="cancelWizardProcess()" :disabled="batchBusy">
                        لغو و شروع مجدد
                    </button>
                </div>
                <template x-for="(job, ji) in wiz.queue" :key="'job'+ji">
                    <div class="bk-wiz-job" :class="'is-' + job.status">
                        <div class="bk-wiz-job-head">
                            <strong x-text="job.title"></strong>
                            <span class="bk-wiz-badge" x-text="jobStatusLabel(job.status)"></span>
                        </div>
                        <div class="bk-ai-progress">
                            <div class="bk-ai-progress-meta">
                                <span x-text="job.stepLabel || 'آماده'"></span>
                                <span x-text="(job.progress||0) + '%'"></span>
                            </div>
                            <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" :style="'width:' + (job.progress||0) + '%'"></div></div>
                        </div>
                        <ul class="bk-wiz-checklist">
                            <li :class="{'is-done': job.progress>=15}">تحلیل محتوا و ساختار مقاله</li>
                            <li :class="{'is-done': job.progress>=35}">تولید خودکار عنوان سئو و متا دیسکریپشن</li>
                            <li :class="{'is-done': job.progress>=50}">استخراج کلمات کلیدی + ادغام ثابت‌ها</li>
                            <li :class="{'is-done': job.progress>=65}">لینک‌سازی هوشمند داخلی با مقالات مرتبط</li>
                            <li :class="{'is-done': job.progress>=80}">سئوی تصاویر و تولید Alt Text خودکار</li>
                            <li :class="{'is-done': job.progress>=100}">دسته و برچسب خودکار (موجود یا ایجاد جدید)</li>
                        </ul>
                        <div style="font-size:11px;color:#656d76;margin-top:4px" x-show="job.message" x-text="job.message"></div>
                        <div style="margin-top:6px" x-show="job.post_id">
                            <a class="bk-btn bk-btn-primary" :href="wpEditUrl(job.post_id)">ویرایش مقاله</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

<div class="bk-art-card" x-show="panel==='schedule'" x-cloak>
            <h3 style="margin:0 0 12px;font-size:16px;font-weight:800">صف زمان‌بندی</h3>
            <template x-if="!queue.length"><p style="color:#8a8886;font-size:13px">موردی در صف نیست.</p></template>
            <template x-for="(item, idx) in queue" :key="'q'+idx">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f5f9;gap:10px;flex-wrap:wrap">
                    <div>
                        <strong x-text="item.title || item.topic"></strong>
                        <div style="font-size:11px;color:#8a8886" x-text="item.schedule_at || '—'"></div>
                    </div>
                    <div class="bk-actions">
                        <a class="bk-btn bk-btn-primary" x-show="item.post_id" :href="wpEditUrl(item.post_id)">ویرایش مقاله</a>
                        <button type="button" class="bk-btn" @click="removeQueueItem(idx)">حذف از صف</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>


<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bankaiArticlesHub', () => ({
        panel: 'list',
        ms: {
            items: [], total: 0, page: 1, pages: 1, selected: [], allSelected: false,
            textProvider: '', textProviders: [],
            aiRunning: false, aiProgress: 0, aiAbort: false, loaded: false
        },
        articles: [],
        loading: false,
        page: 1,
        perPage: 10,
        totalPages: 1,
        total: 0,
        searchQ: '',
        orderby: 'date',
        order: 'DESC',
        queue: <?php echo $queue_json ?: '[]'; ?>,
        gen: {
            topic: '', focus: '', length: '3000', status: 'draft',
            schedule_date: '', schedule_time: '10:00', hour12: 10, minute: 0, ampm: 'AM',
            jyear: 1404, jmonth: 7, jday: 6, notes: '', do_seo: true
        },
        genBusy: false, schedOpen: false, wsSchedOpen: false, tzLabel: 'UTC+3:30', _schedPosBound: null,
        jMonths: ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'],
        genMessage: '',
        genProgress: 0,
        genStepLabel: '',
        wizStep: 1,
        wizBusy: false,
        batchBusy: false,
        wizItemSchedOpen: false,
        wizSchedIdx: -1,
        wizSchedDraft: { jyear: 1404, jmonth: 7, jday: 1, hour12: 10, minute: 0, ampm: 'AM' },
        wizProviders: [],
        wiz: {
            topic: '', count: 10, category: '', provider: '',
            defaultLength: '3000', defaultNotes: '', status: 'draft', gapHours: 24,
            suggestions: [], selected: [], queue: [],
            phase: 'setup', globalProgress: 0, globalLabel: '', paused: false, pauseMessage: '', checkingConn: false
        },
        ws: {
            open: false, tab: 'seo', id: 0, title: '', status: 'draft',
            content: '', seo_title: '', description: '', focus_keyword: '', keywords_str: '',
            keywordList: [], kwDraft: '',
            canonical: '', permalink: '', cover: '', cover_id: 0, score: 0,
            robots_index: true, robots_follow: true,
            og_title: '', og_description: '', og_image: '',
            x_title: '', x_description: '', x_image: '',
            schema: '', schemaType: 'Article', faqItems: [{q:'',a:''}],
            analysis: { score: 0, passed: 0, total: 0, groups: {}, checks: [] },
            saving: false,
            jyear: 1404, jmonth: 7, jday: 6, hour12: 10, minute: 0, ampm: 'AM', date_iso: '', aiBusy: false, aiMsg: '', aiTask: '', aiProgress: 0, aiProgressLabel: '', rewriteStyle: 'seo',
            linkQuery: '', linkResults: [], linkList: [], linkData: { internal: [], external: [], counts: { internal: 0, external: 0 } },
            postResults: [], contentMatchesInternal: [], contentMatchesExternal: [], linksSubTab: 'builder', linkAnchor: '', linkBusy: false, linkSearchLabel: 'جستجو', linkStatusMsg: '', linkApplyBusy: false, extFindBusy: false, extApplyBusy: false,
            linkStats: { internal: 0, external: 0 },
            extAnchor: '', extUrl: '', extNofollow: true,
            postImages: [], optBusy: false, optProgress: 0, optMaxW: 1600, optQuality: 82, optWebp: true, optAlt: ''
        },
        get scoreCirc() { return 2 * Math.PI * 14; },
        get scoreOff() {
            const s = Math.min(100, Math.max(0, this.ws.score || 0));
            return this.scoreCirc - (this.scoreCirc * s / 100);
        },
        init() {
            try { this.initScheduleFromNow(); } catch (e) {}
            try { this.initWizFromQuery(); } catch (e) {}
            try { this.bindMissingSeoEvents(); } catch (e) {}
            this._schedPosRaf = 0;
            this._schedPosBound = () => {
                if (this._schedPosRaf) return;
                this._schedPosRaf = requestAnimationFrame(() => {
                    this._schedPosRaf = 0;
                    if (this.schedOpen) this.positionSchedulePop();
                    if (this.wsSchedOpen) this.positionWsSchedulePop();
                });
            };
            window.addEventListener('scroll', this._schedPosBound, { capture: true, passive: true });
            window.addEventListener('resize', this._schedPosBound, { passive: true });
            this.loadArticles();
        },
        scoreColor(s) {
            s = parseInt(s, 10) || 0;
            if (s >= 80) return '#0f7b3a';
            if (s >= 50) return '#d97706';
            return '#c42b1c';
        },
        scoreBg(s) {
            s = parseInt(s, 10) || 0;
            if (s >= 80) return '#e8f5e9';
            if (s >= 50) return '#fff7ed';
            return '#fef2f2';
        },
        restBase() {
            return (window.bankaiCoreData && bankaiCoreData.restUrl)
                ? bankaiCoreData.restUrl
                : '/wp-json/bankai/v1/';
        },
        restHeaders(json) {
            const h = {
                'X-WP-Nonce': (window.bankaiCoreData && bankaiCoreData.nonce)
                    || (window.wpApiSettings && wpApiSettings.nonce)
                    || ''
            };
            if (json) h['Content-Type'] = 'application/json';
            return h;
        },
        adminNonce() {
            return (window.bankaiCoreData && bankaiCoreData.adminNonce)
                || window.bankaiAdminNonce
                || '';
        },
        async loadArticles() {
            this.loading = true;
            try {
                const q = new URLSearchParams({
                    page: String(this.page),
                    per_page: String(this.perPage),
                    orderby: this.orderby || 'date',
                    order: this.order || 'DESC',
                    search: this.searchQ || ''
                });
                const r = await fetch(this.restBase() + 'seo/articles?' + q.toString(), {
                    credentials: 'same-origin', headers: this.restHeaders(false)
                });
                const j = await r.json();
                const items = (j && j.data && j.data.items) ? j.data.items : (j.items || []);
                this.articles = Array.isArray(items) ? items : [];
                const total = (j && j.data && j.data.total != null) ? j.data.total : (j.total || this.articles.length);
                this.total = total;
                this.totalPages = Math.max(1, Math.ceil(total / Number(this.perPage)));
            } catch (e) {
                console.error(e);
                this.articles = [];
            } finally {
                this.loading = false;
            }
        },
        /* ---- Jalali schedule (UI جلالی → خروجی میلادی برای WP) ---- */
        g2j(gy, gm, gd) {
            if (window.BankaiJalaliDatepicker && BankaiJalaliDatepicker.g2j) {
                return BankaiJalaliDatepicker.g2j(gy, gm, gd);
            }
            const g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
            const gy2 = (gm > 2) ? (gy + 1) : gy;
            let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
                + Math.floor((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
            let jy = -1595 + (33 * Math.floor(days / 12053));
            days %= 12053;
            jy += 4 * Math.floor(days / 1461);
            days %= 1461;
            if (days > 365) { jy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
            let jm, jd;
            if (days < 186) { jm = 1 + Math.floor(days / 31); jd = 1 + (days % 31); }
            else { jm = 7 + Math.floor((days - 186) / 30); jd = 1 + ((days - 186) % 30); }
            return [jy, jm, jd];
        },
        j2g(jy, jm, jd) {
            if (window.BankaiJalaliDatepicker && BankaiJalaliDatepicker.j2g) {
                return BankaiJalaliDatepicker.j2g(jy, jm, jd);
            }
            jy += 1595;
            let days = -355668 + (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor(((jy % 33) + 3) / 4) + jd
                + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30 + 186));
            let gy = 400 * Math.floor(days / 146097);
            days %= 146097;
            if (days > 36524) {
                gy += 100 * Math.floor(--days / 36524);
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * Math.floor(days / 1461);
            days %= 1461;
            if (days > 365) { gy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
            let gd = days + 1;
            const sal_a = [0,31,((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,31,30,31,30,31,31,30,31,30,31];
            let gm = 1;
            for (; gm <= 12 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
            return [gy, gm, gd];
        },
        jDaysInMonth(jy, jm) {
            if (jm <= 6) return 31;
            if (jm <= 11) return 30;
            const a = jy - (jy > 0 ? 474 : 473);
            const b = a % 2820 + 474;
            return (((b + 38) * 682) % 2816) < 682 ? 30 : 29;
        },
        pad2(n) { return (n < 10 ? '0' : '') + n; },
        initScheduleFromNow() {
            const now = new Date();
            let h = now.getHours();
            this.gen.ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12; if (h === 0) h = 12;
            this.gen.hour12 = h;
            this.gen.minute = now.getMinutes();
            const j = this.g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
            this.gen.jyear = j[0]; this.gen.jmonth = j[1]; this.gen.jday = j[2];
            try {
                const off = -now.getTimezoneOffset() / 60;
                const sign = off >= 0 ? '+' : '-';
                const ah = Math.floor(Math.abs(off));
                const am = Math.round((Math.abs(off) - ah) * 60);
                this.tzLabel = 'UTC' + sign + ah + (am ? ':' + this.pad2(am) : '');
            } catch (e) {}
            this.syncScheduleFromUi();
        },
        toggleSchedulePop() {
            if (!this.schedOpen && (!this.gen.schedule_date || !this.gen.jyear)) {
                this.initScheduleFromNow();
            }
            this.schedOpen = !this.schedOpen;
            if (this.schedOpen) {
                this.$nextTick(() => this.positionSchedulePop());
            }
        },
        positionSchedulePop() {
            const btn = this.$refs.schedToggle;
            const pop = this.$refs.schedPop;
            if (!btn || !pop) return;
            const r = btn.getBoundingClientRect();
            const gap = 8;
            const pw = Math.min(340, window.innerWidth - 16);
            pop.style.width = pw + 'px';
            pop.style.visibility = 'hidden';
            pop.style.display = 'block';
            const ph = pop.offsetHeight || 420;
            let left = r.right - pw;
            if (left < 8) left = 8;
            if (left + pw > window.innerWidth - 8) left = Math.max(8, window.innerWidth - pw - 8);
            let top = r.bottom + gap;
            if (top + ph > window.innerHeight - 8) {
                top = r.top - ph - gap;
                if (top < 8) top = 8;
            }
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
            pop.style.right = 'auto';
            pop.style.visibility = '';
        },
        setScheduleNow() {
            this.initScheduleFromNow();
        },
        wsScheduleLabel() {
            const jy = this.ws.jyear, jm = this.ws.jmonth, jd = this.ws.jday;
            const mname = this.jMonths[(jm || 1) - 1] || '';
            const h = this.wsHour24();
            const mi = this.pad2(this.ws.minute || 0);
            if (!jy) return 'تاریخ انتشار';
            return mname + ' ' + jd + '، ' + jy + ' — ' + this.pad2(h) + ':' + mi;
        },
        wsHour24() {
            let h = Number(this.ws.hour12) || 12;
            if (h < 1) h = 1; if (h > 12) h = 12;
            if (this.ws.ampm === 'AM') return h === 12 ? 0 : h;
            return h === 12 ? 12 : h + 12;
        },
        toggleWsSchedulePop() {
            this.wsSchedOpen = !this.wsSchedOpen;
            if (this.wsSchedOpen) {
                if (!this.ws.jyear) this.setWsScheduleFromDate(new Date());
                this.$nextTick(() => this.positionWsSchedulePop());
            }
        },
        positionWsSchedulePop() {
            const btn = this.$refs.wsSchedToggle;
            const pop = this.$refs.wsSchedPop;
            if (!btn || !pop) return;
            const r = btn.getBoundingClientRect();
            const gap = 8;
            const pw = Math.min(340, window.innerWidth - 16);
            pop.style.width = pw + 'px';
            pop.style.visibility = 'hidden';
            pop.style.display = 'block';
            const ph = pop.offsetHeight || 420;
            let left = r.right - pw;
            if (left < 8) left = 8;
            if (left + pw > window.innerWidth - 8) left = Math.max(8, window.innerWidth - pw - 8);
            let top = r.bottom + gap;
            if (top + ph > window.innerHeight - 8) {
                top = r.top - ph - gap;
                if (top < 8) top = 8;
            }
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
            pop.style.right = 'auto';
            pop.style.visibility = '';
        },
        setWsScheduleNow() {
            this.setWsScheduleFromDate(new Date());
        },
        setWsScheduleFromDate(dt) {
            if (!(dt instanceof Date) || isNaN(dt.getTime())) dt = new Date();
            const j = this.g2j(dt.getFullYear(), dt.getMonth() + 1, dt.getDate());
            this.ws.jyear = j[0]; this.ws.jmonth = j[1]; this.ws.jday = j[2];
            let h = dt.getHours();
            this.ws.ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12; if (h === 0) h = 12;
            this.ws.hour12 = h;
            this.ws.minute = dt.getMinutes();
            this.syncWsScheduleFromUi();
        },
        syncWsScheduleFromUi() {
            const jy = Number(this.ws.jyear) || 1400;
            const jm = Number(this.ws.jmonth) || 1;
            let jd = Number(this.ws.jday) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            if (jd > dim) { jd = dim; this.ws.jday = dim; }
            const g = this.j2g(jy, jm, jd);
            const h = this.wsHour24();
            const m = Math.min(59, Math.max(0, Number(this.ws.minute) || 0));
            this.ws.minute = m;
            this.ws.date_iso = g[0] + '-' + this.pad2(g[1]) + '-' + this.pad2(g[2]) + 'T' + this.pad2(h) + ':' + this.pad2(m) + ':00';
        },
        onWsJalaliFieldsChange() { this.syncWsScheduleFromUi(); },
        shiftWsJMonth(delta) {
            let m = Number(this.ws.jmonth) + delta;
            let y = Number(this.ws.jyear);
            if (m > 12) { m = 1; y++; }
            if (m < 1) { m = 12; y--; }
            this.ws.jmonth = m; this.ws.jyear = y;
            this.syncWsScheduleFromUi();
        },
        pickWsJDay(cell) {
            if (!cell || cell.muted || !cell.d) return;
            this.ws.jyear = cell.jy; this.ws.jmonth = cell.jm; this.ws.jday = cell.d;
            this.syncWsScheduleFromUi();
        },
        wsCalCells() {
            const jy = Number(this.ws.jyear) || 1400;
            const jm = Number(this.ws.jmonth) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            const gFirst = this.j2g(jy, jm, 1);
            const first = new Date(gFirst[0], gFirst[1] - 1, gFirst[2]);
            const startDow = (first.getDay() + 1) % 7;
            const today = new Date();
            const tj = this.g2j(today.getFullYear(), today.getMonth() + 1, today.getDate());
            const cells = [];
            for (let i = 0; i < startDow; i++) cells.push({ muted: true, d: 0 });
            for (let d = 1; d <= dim; d++) {
                cells.push({
                    d, jy, jm,
                    sel: Number(this.ws.jday) === d,
                    today: tj[0] === jy && tj[1] === jm && tj[2] === d,
                    muted: false
                });
            }
            return cells;
        },
        hour24() {
            let h = Number(this.gen.hour12) || 12;
            if (h < 1) h = 1; if (h > 12) h = 12;
            if (this.gen.ampm === 'AM') return h === 12 ? 0 : h;
            return h === 12 ? 12 : h + 12;
        },
        syncScheduleFromUi() {
            const jy = Number(this.gen.jyear) || 1400;
            const jm = Number(this.gen.jmonth) || 1;
            let jd = Number(this.gen.jday) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            if (jd > dim) { jd = dim; this.gen.jday = dim; }
            const g = this.j2g(jy, jm, jd);
            this.gen.schedule_date = g[0] + '-' + this.pad2(g[1]) + '-' + this.pad2(g[2]);
            const h = this.hour24();
            const m = Math.min(59, Math.max(0, Number(this.gen.minute) || 0));
            this.gen.minute = m;
            this.gen.schedule_time = this.pad2(h) + ':' + this.pad2(m);
        },
        onJalaliFieldsChange() {
            this.syncScheduleFromUi();
        },
        shiftJMonth(delta) {
            let m = Number(this.gen.jmonth) + delta;
            let y = Number(this.gen.jyear);
            if (m > 12) { m = 1; y++; }
            if (m < 1) { m = 12; y--; }
            this.gen.jmonth = m; this.gen.jyear = y;
            this.syncScheduleFromUi();
        },
        pickJDay(cell) {
            if (!cell || cell.muted || !cell.d) return;
            this.gen.jyear = cell.jy; this.gen.jmonth = cell.jm; this.gen.jday = cell.d;
            this.syncScheduleFromUi();
        },
        jCalendarCells() {
            const jy = Number(this.gen.jyear) || 1400;
            const jm = Number(this.gen.jmonth) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            const gFirst = this.j2g(jy, jm, 1);
            const first = new Date(gFirst[0], gFirst[1] - 1, gFirst[2]);
            const startDow = (first.getDay() + 1) % 7; // شنبه = 0
            const now = new Date();
            const todayJ = this.g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
            const cells = [];
            for (let i = 0; i < startDow; i++) cells.push({ key: 'm'+i, d: 0, muted: true, sel: false, today: false });
            for (let d = 1; d <= dim; d++) {
                cells.push({
                    key: 'd'+d, d, jy, jm, muted: false,
                    sel: d === Number(this.gen.jday),
                    today: todayJ[0] === jy && todayJ[1] === jm && todayJ[2] === d
                });
            }
            return cells;
        },
        scheduleLabel() {
            if (!this.gen.schedule_date) return 'انتخاب زمان انتشار…';
            const mName = this.jMonths[(Number(this.gen.jmonth) || 1) - 1] || '';
            const h = Number(this.gen.hour12) || 12;
            const min = this.pad2(Number(this.gen.minute) || 0);
            const ap = this.gen.ampm === 'PM' ? 'ب.ظ' : 'ق.ظ';
            return (this.tzLabel ? this.tzLabel + ' ' : '') + this.gen.jday + ' ' + mName + ' ' + this.gen.jyear + ' — ' + this.pad2(h) + ':' + min + ' ' + ap;
        },
        scheduleIso() {
            if (!this.gen.schedule_date) return '';
            // Always Gregorian for WordPress: Y-m-d H:i:s
            return this.gen.schedule_date + ' ' + (this.gen.schedule_time || '12:00') + ':00';
        },


        bindMissingSeoEvents() {
            window.addEventListener('bankai-open-missing-seo', () => {
                this.panel = 'missing-seo';
                this.initMissingSeo();
            });
            const params = new URLSearchParams(window.location.search);
            if ((params.get('subtab') || '') === 'missing-seo') {
                this.panel = 'missing-seo';
                this.initMissingSeo();
            }
        },
        initMissingSeo() {
            this.loadMissingSeoProviders();
            this.loadMissingSeoList();
        },
        loadMissingSeoProviders() {
            const st = window.bankaiState || {};
            const norm = (list) => (list || []).map(p => {
                if (typeof p === 'string') return { id: p, name: p, ready: true };
                const statusish = ['کلید تنظیم شده', 'بدون کلید API', 'بدون کلید', 'ready', 'missing_key'];
                let name = p.name || '';
                if (!name || statusish.includes(name)) {
                    name = (p.label && !statusish.includes(p.label)) ? p.label : (p.id || p.key || '');
                }
                // map common ids to readable names
                const idNames = {
                    openrouter: 'OpenRouter', gemini: 'Google Gemini', openai: 'OpenAI',
                    anthropic: 'Anthropic Claude', deepseek: 'DeepSeek', huggingface: 'Hugging Face',
                    groq: 'Groq', cloudflare: 'Cloudflare Workers AI'
                };
                if (!name || statusish.includes(name)) name = idNames[p.id] || p.id || '';
                return {
                    id: p.id || p.key || '',
                    name: name,
                    ready: !!(p.ready || p.has_key || p.hasKey || p.configured || p.status === 'ready' || p.masked_key)
                };
            }).filter(p => p.id);
            // فقط موتورهای متنی فعال (دارای کلید)
            this.ms.textProviders = norm(st.providers || []).filter(p => p.ready);
            if (!this.ms.textProvider && this.ms.textProviders.length) {
                this.ms.textProvider = this.ms.textProviders[0].id;
            } else if (this.ms.textProvider && !this.ms.textProviders.some(p => p.id === this.ms.textProvider)) {
                this.ms.textProvider = this.ms.textProviders[0] ? this.ms.textProviders[0].id : '';
            }
        },
        msCfg() {
            return window.bankaiData || window.bankaiCoreData || {};
        },
        msAjax() {
            const c = this.msCfg();
            return c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php';
        },
        msNonce() {
            const c = this.msCfg();
            return c.adminNonce || c.nonce || '';
        },
        msSelectedCount() { return (this.ms.selected || []).length; },
        msReadyCount() { return (this.ms.items || []).filter(r => r.aiPhase === 'review' && r.draft).length; },
        msPhaseLabel(phase) {
            return ({ pending:'در صف', running:'در حال اجرا', review:'آماده تأیید', done:'ذخیره شد', error:'خطا', cancelled:'لغو شد' })[phase] || phase || '';
        },
        msToggleAll() {
            if (this.ms.allSelected) {
                this.ms.selected = [];
                this.ms.allSelected = false;
            } else {
                this.ms.selected = (this.ms.items || []).map(r => r.id);
                this.ms.allSelected = true;
            }
        },
        async loadMissingSeoList() {
            const fd = new FormData();
            fd.append('action', 'bankai_seo_import_missing');
            fd.append('nonce', this.msNonce());
            fd.append('page', String(this.ms.page || 1));
            fd.append('per_page', '10');
            try {
                const r = await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
                const j = await r.json();
                if (j && j.success && j.data) {
                    this.ms.items = (j.data.items || []).map(row => Object.assign({
                        aiPhase: '', aiProgress: 0, aiStepText: '', checklist: [], draft: null, saving: false, aiError: ''
                    }, row));
                    this.ms.total = j.data.total || 0;
                    this.ms.pages = j.data.total_pages || j.data.pages || 1;
                    this.ms.page = j.data.page || 1;
                }
                this.ms.loaded = true;
            } catch (e) {
                this.ms.loaded = true;
                if (window.showToast) window.showToast('خطا در بارگذاری موارد بدون سئو', 'error');
            }
        },
        msChecklist() {
            return [
                { id: 'analyze', label: 'تحلیل محتوا و ساختار مقاله', status: 'pending' },
                { id: 'meta', label: 'تولید خودکار عنوان سئو و متا دیسکریپشن', status: 'pending' },
                { id: 'keywords', label: 'استخراج کلمات کلیدی + ادغام ثابت‌ها', status: 'pending' },
                { id: 'links', label: 'لینک‌سازی هوشمند داخلی با مقالات مرتبط', status: 'pending' },
                { id: 'images', label: 'سئوی تصاویر و تولید Alt Text خودکار', status: 'pending' },
                { id: 'optimize', label: 'بهینه‌سازی و فشرده‌سازی تصاویر مقاله', status: 'pending' },
                { id: 'schema', label: 'تولید اسکیما JSON-LD مقاله', status: 'pending' },
                { id: 'terms', label: 'تولید دسته و برچسب خودکار', status: 'pending' }
            ];
        },
        msSetCheck(row, id, status) {
            const item = (row.checklist || []).find(c => c.id === id);
            if (item) item.status = status;
        },
        async msCallTask(task, payload) {
            const fd = new FormData();
            fd.append('action', 'bankai_ai_seo_task');
            fd.append('nonce', this.msNonce());
            fd.append('task', task);
            fd.append('provider', this.ms.textProvider || '');
            Object.keys(payload || {}).forEach(k => {
                const v = payload[k];
                if (v == null) return;
                fd.append(k, typeof v === 'object' ? JSON.stringify(v) : String(v));
            });
            const r = await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
            const j = await r.json();
            if (!j || !j.success) throw new Error((j && j.data && j.data.message) || ('خطا در ' + task));
            return j.data || {};
        },
        async msContext(postId) {
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_post_context');
            fd.append('nonce', this.msNonce());
            fd.append('post_id', String(postId));
            const r = await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
            const j = await r.json();
            if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'خطا در دریافت محتوا');
            return j.data || {};
        },
        async msRunRow(row) {
            row.aiPhase = 'running';
            row.aiProgress = 0;
            row.aiError = '';
            row.checklist = this.msChecklist();
            row.draft = { seo_title: '', description: '', focus_keyword: '', keywords: [], schema: '' };
            row.saving = false;
            const steps = [
                { id: 'analyze', pct: 8, text: 'گام ۱/۸: تحلیل محتوا…' },
                { id: 'meta', pct: 18, text: 'گام ۲/۸: تولید عنوان و متا…' },
                { id: 'keywords', pct: 28, text: 'گام ۳/۸: کلیدواژه‌ها…' },
                { id: 'links', pct: 40, text: 'گام ۴/۸: لینک‌سازی داخلی…' },
                { id: 'images', pct: 52, text: 'گام ۵/۸: Alt تصاویر…' },
                { id: 'optimize', pct: 70, text: 'گام ۶/۸: بهینه‌سازی تصاویر…' },
                { id: 'schema', pct: 85, text: 'گام ۷/۸: اسکیما…' },
                { id: 'terms', pct: 97, text: 'گام ۸/۸: دسته و برچسب…' }
            ];
            const ctx = await this.msContext(row.id);
            for (const step of steps) {
                if (this.ms.aiAbort) { row.aiPhase = 'cancelled'; row.aiStepText = 'متوقف شد'; return; }
                row.aiStepText = step.text;
                row.aiProgress = step.pct;
                this.msSetCheck(row, step.id, 'loading');
                try {
                    if (step.id === 'analyze') {
                        await new Promise(r => setTimeout(r, 150));
                        this.msSetCheck(row, 'analyze', 'success');
                    } else if (step.id === 'meta') {
                        const basePayload = {
                            title: ctx.title || row.title || '',
                            content: ctx.content || '',
                            focus_keyword: ctx.focus_keyword || '',
                            locale: 'fa_IR'
                        };
                        // همان taskهای معتبر ادیتور: meta_title + meta_description
                        const [tRes, dRes] = await Promise.all([
                            this.msCallTask('meta_title', basePayload),
                            this.msCallTask('meta_description', basePayload)
                        ]);
                        row.draft.seo_title = (tRes.seo_title || tRes.title || tRes.text || '').toString().trim();
                        row.draft.description = (dRes.description || dRes.meta_description || dRes.text || '').toString().trim();
                        if (!row.draft.focus_keyword && (ctx.focus_keyword || '')) {
                            row.draft.focus_keyword = ctx.focus_keyword;
                        }
                        if (!row.draft.seo_title && !row.draft.description) {
                            throw new Error('عنوان یا متا خالی برگشت');
                        }
                        this.msSetCheck(row, 'meta', 'success');
                    } else if (step.id === 'keywords') {
                        const kw = await this.msCallTask('keywords', {
                            title: ctx.title || row.title || '',
                            content: ctx.content || '',
                            focus_keyword: row.draft.focus_keyword || ctx.focus_keyword || '',
                            locale: 'fa_IR'
                        });
                        let list = kw.keywords || kw.list || [];
                        if (typeof list === 'string') {
                            try { list = JSON.parse(list); } catch (e) { list = list.split(/[,،\n]+/); }
                        }
                        if (!Array.isArray(list)) list = [];
                        list = list.map(v => String(v || '').trim()).filter(Boolean);
                        row.draft.keywords = list.filter((v, i, a) => a.indexOf(v) === i);
                        if (!row.draft.focus_keyword && list[0]) row.draft.focus_keyword = list[0];
                        this.msSetCheck(row, 'keywords', 'success');
                    } else if (step.id === 'links') {
                        try {
                            const articles = ctx.related || ctx.articles || [];
                            await this.msCallTask('smart_internal_links', {
                                title: ctx.title || row.title || '',
                                content: ctx.content || '',
                                focus_keyword: row.draft.focus_keyword || '',
                                articles_data: typeof articles === 'string' ? articles : JSON.stringify(articles),
                                locale: 'fa_IR'
                            });
                            this.msSetCheck(row, 'links', 'success');
                        } catch (e) { this.msSetCheck(row, 'links', 'error'); }
                    } else if (step.id === 'images') {
                        const imgs = ctx.images || [];
                        for (let i = 0; i < Math.min(imgs.length, 5); i++) {
                            const img = imgs[i];
                            if (img.alt && String(img.alt).trim()) continue;
                            try {
                                await this.msCallTask('image_alt_text', {
                                    title: ctx.title || row.title || '',
                                    content: ctx.content || '',
                                    image_url: img.src || img.url || '',
                                    image_context: (ctx.title || '') + ' ' + (row.draft.focus_keyword || ''),
                                    locale: 'fa_IR'
                                });
                            } catch (e) {}
                        }
                        this.msSetCheck(row, 'images', 'success');
                    } else if (step.id === 'optimize') {
                        try {
                            const fd = new FormData();
                            fd.append('action', 'bankai_optimize_post_images');
                            fd.append('nonce', this.msNonce());
                            fd.append('post_id', String(row.id));
                            await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
                            this.msSetCheck(row, 'optimize', 'success');
                        } catch (e) { this.msSetCheck(row, 'optimize', 'error'); }
                    } else if (step.id === 'schema') {
                        row.draft.schema = JSON.stringify({
                            '@context': 'https://schema.org', '@type': 'Article',
                            headline: row.draft.seo_title || ctx.title || row.title,
                            description: row.draft.description || '',
                            mainEntityOfPage: ctx.permalink || '',
                            keywords: (row.draft.keywords || []).join(', ')
                        }, null, 2);
                        this.msSetCheck(row, 'schema', 'success');
                    } else if (step.id === 'terms') {
                        try {
                            const fd = new FormData();
                            fd.append('action', 'bankai_ai_assign_terms');
                            fd.append('nonce', this.msNonce());
                            fd.append('post_id', String(row.id));
                            fd.append('focus', row.draft.focus_keyword || '');
                            fd.append('keywords', JSON.stringify(row.draft.keywords || []));
                            await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
                            this.msSetCheck(row, 'terms', 'success');
                        } catch (e) { this.msSetCheck(row, 'terms', 'error'); }
                    }
                } catch (err) {
                    this.msSetCheck(row, step.id, 'error');
                    if (step.id === 'meta' || step.id === 'keywords') {
                        row.aiPhase = 'error';
                        row.aiError = (err && err.message) || 'خطا';
                        throw err;
                    }
                }
            }
            row.aiProgress = 100;
            row.aiStepText = 'آماده تأیید و ذخیره';
            row.aiPhase = 'review';
        },
        async runMissingSeoBatch() {
            const ids = (this.ms.selected || []).slice();
            if (!ids.length) { if (window.showToast) window.showToast('هیچ مقاله‌ای انتخاب نشده', 'error'); return; }
            if (!this.ms.textProvider) { if (window.showToast) window.showToast('موتور AI متنی را انتخاب کنید', 'error'); return; }
            this.ms.aiRunning = true;
            this.ms.aiAbort = false;
            this.ms.aiProgress = 0;
            let done = 0, failed = 0;
            for (let i = 0; i < ids.length; i++) {
                if (this.ms.aiAbort) break;
                const row = (this.ms.items || []).find(r => String(r.id) === String(ids[i]));
                if (!row) continue;
                try {
                    await this.msRunRow(row);
                    if (row.aiPhase === 'review') done++;
                    else if (row.aiPhase === 'error') failed++;
                } catch (e) {
                    failed++;
                    row.aiPhase = 'error';
                    row.aiError = (e && e.message) || 'خطا';
                }
                this.ms.aiProgress = Math.round(((i + 1) / ids.length) * 100);
            }
            this.ms.aiRunning = false;
            if (window.showToast) {
                window.showToast(this.ms.aiAbort ? ('متوقف شد — موفق: ' + done) : ('پایان صف — آماده تأیید: ' + done + ' · خطا: ' + failed), done ? 'success' : 'error');
            }
        },
        msApprove(row) {
            if (!row || !row.draft) return;
            row.saving = true;
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_apply_draft');
            fd.append('nonce', this.msNonce());
            fd.append('post_id', String(row.id));
            fd.append('seo_title', row.draft.seo_title || '');
            fd.append('description', row.draft.description || '');
            fd.append('focus_keyword', row.draft.focus_keyword || '');
            fd.append('keywords', JSON.stringify(row.draft.keywords || []));
            fd.append('schema', row.draft.schema || '');
            fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(j => {
                    row.saving = false;
                    if (j && j.success) {
                        row.aiPhase = 'done';
                        row.score = (j.data && j.data.score) || row.score || 0;
                        if (window.showToast) window.showToast('ذخیره شد: ' + (row.title || ''), 'success');
                    } else if (window.showToast) window.showToast((j && j.data && j.data.message) || 'خطا در ذخیره', 'error');
                })
                .catch(() => { row.saving = false; if (window.showToast) window.showToast('خطا در ذخیره', 'error'); });
        },
        msCancel(row) {
            if (!row) return;
            row.aiPhase = 'cancelled';
            row.draft = null;
            row.checklist = [];
            row.aiProgress = 0;
            row.aiStepText = '';
        },
        async msRerun(row) {
            if (!row || this.ms.aiRunning) return;
            this.ms.aiRunning = true;
            this.ms.aiAbort = false;
            try { await this.msRunRow(row); } catch (e) { row.aiPhase = 'error'; row.aiError = (e && e.message) || 'خطا'; }
            this.ms.aiRunning = false;
        },
        msApproveAll() {
            const rows = (this.ms.items || []).filter(r => r.aiPhase === 'review' && r.draft);
            rows.forEach((row, i) => setTimeout(() => this.msApprove(row), i * 350));
            if (window.showToast) window.showToast('تأیید گروهی برای ' + rows.length + ' مقاله', 'success');
        },

        initWizFromQuery() {
            const params = new URLSearchParams(window.location.search);
            const sub = params.get('subtab') || '';
            if (sub === 'generate' || sub === 'ai-generate') {
                this.panel = 'generate';
                this.wizStep = 1;
            }
            // Providers from localized or fetch status via existing options
            const cfg = window.bankaiCoreData || {};
            if (Array.isArray(cfg.providers) && cfg.providers.length) {
                this.wizProviders = cfg.providers;
            } else if (Array.isArray(this.providers) && this.providers.length) {
                this.wizProviders = this.providers;
            }
            // PHP-injected fallback
            const el = document.getElementById('bk-wiz-providers-json');
            if (el && el.textContent) {
                try { this.wizProviders = JSON.parse(el.textContent); } catch (e) {}
            }
            if (!this.wiz.provider && this.wizProviders.length) {
                const ready = this.wizProviders.find(p => p.has_key) || this.wizProviders[0];
                this.wiz.provider = ready.id || '';
            }
        },
        jobStatusLabel(st) {
            return ({ pending:'در صف', run:'در حال اجرا', done:'انجام شد', error:'خطا' })[st] || st;
        },
        async suggestTitles() {
            if (!this.wiz.topic.trim()) { alert('موضوع را وارد کنید'); return; }
            this.wizBusy = true;
            this.wiz.globalProgress = 10;
            this.wiz.globalLabel = 'دریافت پیشنهاد عنوان…';
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_ai_suggest_titles');
                fd.append('nonce', window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || '');
                fd.append('topic', this.wiz.topic);
                fd.append('count', String(this.wiz.count || 10));
                fd.append('category', this.wiz.category || '');
                fd.append('provider', this.wiz.provider || '');
                const r = await fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' });
                const j = await r.json();
                if (j.success && j.data && j.data.titles) {
                    this.wiz.suggestions = j.data.titles;
                    this.wiz.selected = j.data.titles.map((title) => ({
                        title,
                        length: this.wiz.defaultLength || '3000',
                        notes: this.wiz.defaultNotes || '',
                        approved: true,
                        pubStatus: 'draft',
                        schedule_at: '',
                        jyear: null, jmonth: null, jday: null,
                        hour12: 10, minute: 0, ampm: 'AM'
                    }));
                    this.wiz.phase = 'titles';
                } else {
                    alert((j.data && j.data.message) || 'پیشنهادی دریافت نشد');
                }
            } catch (e) {
                alert('خطا در ارتباط');
            } finally {
                this.wizBusy = false;
                this.wiz.globalProgress = 0;
            }
        },
        addManualTitle() {
            const t = prompt('عنوان مقاله را وارد کنید');
            if (!t || !t.trim()) return;
            this.wiz.selected.push({
                title: t.trim(),
                length: this.wiz.defaultLength || '3000',
                notes: this.wiz.defaultNotes || '',
                approved: true,
                pubStatus: 'draft',
                schedule_at: '',
                jyear: null, jmonth: null, jday: null,
                hour12: 10, minute: 0, ampm: 'AM'
            });
            this.wiz.phase = 'titles';
        },
        toggleSuggestion(title, on) {
            if (on) {
                if (!this.wiz.selected.some(s => s.title === title)) {
                    this.wiz.selected.push({
                        title,
                        length: this.wiz.defaultLength || '3000',
                        notes: this.wiz.defaultNotes || ''
                    });
                }
            } else {
                this.wiz.selected = this.wiz.selected.filter(s => s.title !== title);
            }
        },
        prepareQueue() {
            const gap = Math.max(1, parseInt(this.wiz.gapHours || 24, 10));
            const base = Date.now() + gap * 3600 * 1000;
            this.wiz.queue = this.wiz.selected.map((s, i) => {
                let schedule_at = '';
                if (this.wiz.status === 'future') {
                    const d = new Date(base + i * gap * 3600 * 1000);
                    schedule_at = d.toISOString().slice(0, 19).replace('T', ' ');
                }
                return {
                    title: s.title,
                    focus: this.wiz.topic || '',
                    length: s.length || this.wiz.defaultLength || '3000',
                    notes: (s.notes || this.wiz.defaultNotes || ''),
                    status: 'pending',
                    progress: 0,
                    stepLabel: 'آماده',
                    message: '',
                    post_id: 0,
                    schedule_at,
                    pubStatus: this.wiz.status || 'draft',
                    provider: this.wiz.provider || ''
                };
            });
        },

        startProductionFromCards() {
            const approved = this.wiz.selected.filter(s => s.approved && s.title && s.title.trim());
            if (!approved.length) { alert('حداقل یک عنوان را تأیید کنید'); return; }
            this.wiz.queue = approved.map((s) => {
                let schedule_at = s.schedule_at || '';
                if (s.pubStatus === 'future' && !schedule_at && s.jyear) {
                    schedule_at = this.jalaliDraftToGregorianString(s) || '';
                }
                return {
                    title: s.title.trim(),
                    focus: this.wiz.topic || '',
                    length: s.length || this.wiz.defaultLength || '3000',
                    notes: s.notes || this.wiz.defaultNotes || '',
                    status: 'pending',
                    progress: 0,
                    stepLabel: 'آماده',
                    message: '',
                    post_id: 0,
                    schedule_at,
                    pubStatus: s.pubStatus || 'draft',
                    provider: this.wiz.provider || ''
                };
            });
            this.wiz.phase = 'produce';
            this.runBatchQueue();
        },
        async runBatchQueue() {
            if (this.batchBusy || !this.wiz.queue.length) return;
            this.batchBusy = true;
            for (let i = 0; i < this.wiz.queue.length; i++) {
                const job = this.wiz.queue[i];
                if (job.status === 'done') continue;
                job.status = 'run';
                job.progress = 8;
                job.stepLabel = 'گام ۱/۶: تولید محتوا…';
                job.message = '';
                this.wiz.globalLabel = 'تولید: ' + job.title;
                this.wiz.globalProgress = Math.round((i / this.wiz.queue.length) * 100);
                try {
                    const fd = new FormData();
                    fd.append('action', 'bankai_ai_generate_article');
                    fd.append('nonce', window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || '');
                    fd.append('topic', job.title);
                    fd.append('focus', job.focus || this.wiz.topic || '');
                    fd.append('length', String(job.length || 3000));
                    fd.append('notes', job.notes || '');
                    fd.append('status', job.pubStatus || 'draft');
                    fd.append('do_seo', '1');
                    fd.append('provider', job.provider || this.wiz.provider || '');
                    if (job.schedule_at) fd.append('schedule_at', job.schedule_at);
                    // Fake progressive UI while waiting
                    const tick = setInterval(() => {
                        if (job.progress < 88) {
                            job.progress += 2;
                            if (job.progress < 20) job.stepLabel = 'گام ۱/۶: تولید محتوا…';
                            else if (job.progress < 35) job.stepLabel = 'گام ۲/۶: تولید عنوان سئو، متادیسکریپشن و کلیدواژه‌ها…';
                            else if (job.progress < 50) job.stepLabel = 'گام ۳/۶: استخراج کلمات کلیدی + ادغام ثابت‌ها…';
                            else if (job.progress < 65) job.stepLabel = 'گام ۴/۶: لینک‌سازی هوشمند داخلی…';
                            else if (job.progress < 80) job.stepLabel = 'گام ۵/۶: سئوی تصاویر و Alt…';
                            else job.stepLabel = 'گام ۶/۶: دسته و برچسب خودکار…';
                        }
                    }, 500);
                    const r = await fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' });
                    clearInterval(tick);
                    const j = await r.json();
                    if (j.success) {
                        job.progress = 100;
                        job.status = 'done';
                        job.stepLabel = 'تکمیل شد';
                        job.post_id = (j.data && j.data.post_id) || 0;
                        job.message = (j.data && j.data.message) || 'موفق';
                        if (j.data && j.data.queue) this.queue = j.data.queue;
                        this.wiz.globalProgress = Math.round(((i + 1) / this.wiz.queue.length) * 100);
                    } else {
                        job.status = 'error';
                        job.stepLabel = 'خطا';
                        job.message = (j.data && j.data.message) || 'ناموفق';
                        this.wiz.paused = true;
                        this.wiz.pauseMessage = 'خطا در «' + job.title + '»: ' + job.message;
                        this.batchBusy = false;
                        this.wiz.globalLabel = 'متوقف شد — ' + job.message;
                        return;
                    }
                } catch (e) {
                    job.status = 'error';
                    job.message = (e && e.message) || 'خطای شبکه';
                    this.wiz.paused = true;
                    this.wiz.pauseMessage = 'خطا در «' + job.title + '»: ' + job.message;
                    this.batchBusy = false;
                    this.wiz.globalLabel = 'متوقف شد — ' + job.message;
                    return;
                }
            }
            this.batchBusy = false;
            this.wiz.paused = false;
            this.wiz.globalProgress = 100;
            const doneCount = (this.wiz.queue || []).filter(j => j.status === 'done').length;
            const errCount = (this.wiz.queue || []).filter(j => j.status === 'error').length;
            this.wiz.globalLabel = 'صف تولید تکمیل شد';
            this.loadArticles && this.loadArticles();
            // بازگشت به فرم اصلی
            this.wiz.phase = 'setup';
            this.wiz.selected = [];
            this.wiz.suggestions = [];
            this.wiz.queue = [];
            if (window.showToast) {
                window.showToast(
                    doneCount
                        ? (doneCount + ' مقاله با موفقیت تولید شد' + (errCount ? (' · ' + errCount + ' خطا') : ''))
                        : 'هیچ مقاله‌ای تولید نشد',
                    doneCount ? 'success' : 'error'
                );
            }
        },

        async continueBatchAfterCheck() {
            if (this.batchBusy || this.wiz.checkingConn) return;
            this.wiz.checkingConn = true;
            this.wiz.globalLabel = 'بررسی اتصال موتور AI…';
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_ai_test_connection');
                fd.append('nonce', window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || '');
                fd.append('provider', this.wiz.provider || '');
                const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                    method: 'POST', body: fd, credentials: 'same-origin'
                });
                const j = await r.json().catch(() => ({}));
                if (!j || !j.success) {
                    const msg = (j && j.data && j.data.message) || 'اتصال برقرار نیست. کلید API و موتور را بررسی کنید.';
                    this.wiz.pauseMessage = msg;
                    alert(msg);
                    return;
                }
                // Reset errored job to pending so it retries
                this.wiz.queue.forEach((job) => {
                    if (job.status === 'error') {
                        job.status = 'pending';
                        job.progress = 0;
                        job.stepLabel = 'آماده برای تلاش مجدد';
                        job.message = '';
                    }
                });
                this.wiz.paused = false;
                this.wiz.pauseMessage = '';
                await this.runBatchQueue();
            } catch (e) {
                const msg = 'بررسی اتصال ناموفق بود: ' + ((e && e.message) || 'شبکه');
                this.wiz.pauseMessage = msg;
                alert(msg);
            } finally {
                this.wiz.checkingConn = false;
            }
        },

        async generateArticle() {
            if (!this.gen.topic.trim()) { this.genMessage = 'موضوع را وارد کنید'; return; }
            this.genBusy = true;
            this.genMessage = '';
            this.genProgress = 5;
            this.genStepLabel = 'آماده‌سازی…';
            const tick = setInterval(() => {
                if (this.genProgress < 35) {
                    this.genProgress += 2;
                    this.genStepLabel = 'تولید محتوا توسط AI…';
                }
            }, 800);
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_ai_generate_article');
                fd.append('nonce', this.adminNonce());
                fd.append('topic', this.gen.topic);
                fd.append('focus', this.gen.focus);
                fd.append('length', this.gen.length);
                fd.append('status', this.gen.status);
                fd.append('schedule_at', this.scheduleIso());
                fd.append('notes', this.gen.notes || '');
                if (this.gen.do_seo) fd.append('do_seo', '1');
                const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                    method: 'POST', body: fd, credentials: 'same-origin'
                });
                clearInterval(tick);
                this.genProgress = this.gen.do_seo ? 55 : 85;
                this.genStepLabel = this.gen.do_seo ? 'تکمیل سئو…' : 'ذخیره…';
                const j = await r.json();
                if (j.success) {
                    this.genProgress = 100;
                    this.genStepLabel = 'تمام شد';
                    this.genMessage = (j.data && j.data.message) || 'مقاله ساخته شد';
                    if (j.data && j.data.queue) this.queue = j.data.queue;
                    const pid = j.data && j.data.post_id;
                    if (pid) {
                        /* openWorkspace removed — use WP editor */
                    } else if (this.gen.status === 'future') {
                        this.panel = 'schedule';
                    } else {
                        this.loadArticles();
                    }
                } else {
                    this.genMessage = (j.data && j.data.message) || j.message || 'خطا';
                    this.genProgress = 0;
                }
            } catch (e) {
                clearInterval(tick);
                this.genMessage = 'خطای ارتباط';
                this.genProgress = 0;
            } finally {
                this.genBusy = false;
            }
        },
        async removeQueueItem(idx) {
            const fd = new FormData();
            fd.append('action', 'bankai_ai_remove_queue_item');
            fd.append('nonce', this.adminNonce());
            fd.append('index', String(idx));
            const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                method: 'POST', body: fd, credentials: 'same-origin'
            });
            const j = await r.json();
            if (j.success && j.data && j.data.queue) this.queue = j.data.queue;
            else this.queue.splice(idx, 1);
        },
        editorId: 'bk_art_editor',
        editorReady: true,
        editorLoading: false,
        htmlMode: false,
        editorAdvOpen: false,
        editorPrefs: { pastePlain: false, autoDir: true, fontSize: '15px' },
        getEditorContent() {
            if (this.htmlMode) {
                const ta = document.getElementById('bk_art_editor_html');
                return ta ? ta.value : (this.ws.content || '');
            }
            const el = document.getElementById(this.editorId);
            return el ? el.innerHTML : (this.ws.content || '');
        },
        setEditorContent(html) {
            const val = html || '';
            this.ws.content = val;
            const el = document.getElementById(this.editorId);
            if (el) el.innerHTML = val;
            const ta = document.getElementById('bk_art_editor_html');
            if (ta) ta.value = val;
            this.editorReady = true;
        },
        onEditorInput() {
            this.ws.content = this.getEditorContent();
        },
        destroyEditor() {
            this.htmlMode = false;
            const el = document.getElementById(this.editorId);
            const ta = document.getElementById('bk_art_editor_html');
            if (el) el.style.display = '';
            if (ta) ta.style.display = 'none';
        },
        initRichEditor() {
            const self = this;
            const el = document.getElementById(this.editorId);
            if (!el) return;
            this.setEditorContent(this.ws.content || '');
            this.editorReady = true;
            this.applyEditorPrefs();
            const wrap = el.closest('.bk-lite-editor') || el.parentElement;
            const bar = wrap && wrap.querySelector('.bk-lite-toolbar');
            if (bar && !bar.dataset.bound) {
                bar.dataset.bound = '1';
                bar.addEventListener('click', function (ev) {
                    const btn = ev.target.closest('button');
                    if (!btn) return;
                    ev.preventDefault();
                    if (btn.dataset.mode === 'html') { self.toggleHtmlMode(); return; }
                    if (self.htmlMode) return;
                    const cmd = btn.dataset.cmd;
                    if (cmd === 'openAdv') { self.editorAdvOpen = !self.editorAdvOpen; return; }
                    if (cmd === 'openMediaPanel') {
                        self.ws.tab = 'media';
                        self.scanWsImages();
                        return;
                    }
                    if (cmd === 'insertGallery') { self.insertGalleryFromMedia(); return; }
                    if (cmd === 'insertVideo') { self.insertVideoEmbed(); return; }
                    if (cmd === 'insertImage') { self.insertImageFromMedia(); return; }
                    if (cmd === 'createLink') {
                        el.focus();
                        const url = window.prompt('آدرس لینک:', 'https://');
                        if (url) document.execCommand('createLink', false, url);
                        self.onEditorInput();
                        return;
                    }
                    if (cmd === 'formatBlock') return;
                    el.focus();
                    if (cmd) document.execCommand(cmd, false, null);
                    self.onEditorInput();
                });
                bar.addEventListener('change', function (ev) {
                    const t = ev.target;
                    if (t.matches('select[data-cmd="formatBlock"]') && t.value) {
                        el.focus();
                        document.execCommand('formatBlock', false, t.value);
                        t.value = '';
                        self.onEditorInput();
                    }
                    if (t.matches('input[type="color"][data-cmd]')) {
                        el.focus();
                        const c = t.dataset.cmd;
                        if (c === 'hiliteColor') {
                            try { document.execCommand('hiliteColor', false, t.value); }
                            catch (e) { document.execCommand('backColor', false, t.value); }
                        } else {
                            document.execCommand(c, false, t.value);
                        }
                        self.onEditorInput();
                    }
                });
            }
            if (!el.dataset.bound) {
                el.dataset.bound = '1';
                el.addEventListener('input', function () { self.onEditorInput(); });
                el.addEventListener('blur', function () { self.onEditorInput(); });
                el.addEventListener('paste', function (ev) {
                    if (!self.editorPrefs.pastePlain) return;
                    ev.preventDefault();
                    const text = (ev.clipboardData || window.clipboardData).getData('text/plain');
                    document.execCommand('insertText', false, text);
                    self.onEditorInput();
                });
            }
        },
        toggleHtmlMode() {
            const el = document.getElementById(this.editorId);
            const ta = document.getElementById('bk_art_editor_html');
            if (!el || !ta) return;
            if (!this.htmlMode) {
                ta.value = el.innerHTML;
                el.style.display = 'none';
                ta.style.display = 'block';
                this.htmlMode = true;
            } else {
                el.innerHTML = ta.value;
                this.ws.content = ta.value;
                ta.style.display = 'none';
                el.style.display = '';
                this.htmlMode = false;
            }
        },
        insertImageFromMedia() {
            const self = this;
            if (!window.wp || !wp.media) {
                const url = window.prompt('آدرس تصویر:', 'https://');
                if (!url) return;
                document.execCommand('insertHTML', false, '<p><img src="' + url + '" alt=""></p>');
                self.onEditorInput();
                return;
            }
            const frame = wp.media({ title: 'درج تصویر', button: { text: 'درج' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                const src = att.url || '';
                const alt = (att.alt || att.title || '').replace(/"/g, '&quot;');
                const el = document.getElementById(self.editorId);
                if (el) el.focus();
                document.execCommand('insertHTML', false, '<p><img src="' + src + '" alt="' + alt + '"></p>');
                self.onEditorInput();
                self.scanWsImages();
            });
            frame.open();
        },
        syncEditorFromModel() {
            this.setEditorContent(this.ws.content || '');
        },
        openWorkspace(row, tab) {
            const id = row && (row.id || row.post_id);
            if (id) window.location.href = this.wpEditUrl(id);
        },
        wpEditUrl(id) {
            if (!id) return '#';
            const base = (window.bankaiCoreData && bankaiCoreData.adminUrl)
                || (typeof ajaxurl !== 'undefined' ? ajaxurl.replace(/admin-ajax\.php.*/, '') : '/wp-admin/');
            return base + 'post.php?post=' + id + '&action=edit';
        },
        cancelWizardProcess() {
            this.batchBusy = false;
            this.wizBusy = false;
            this.wiz.paused = false;
            this.wiz.pauseMessage = '';
            this.wiz.checkingConn = false;
            this.wiz.queue = [];
            this.wiz.selected = [];
            this.wiz.suggestions = [];
            this.wiz.globalProgress = 0;
            this.wiz.globalLabel = '';
            this.wiz.phase = 'setup';
            this.panel = 'generate';
        },
        openWizItemSchedule(idx) {
            this.wizSchedIdx = idx;
            const item = this.wiz.selected[idx];
            if (!item) return;
            // seed draft from item or now
            if (item.jyear) {
                this.wizSchedDraft = {
                    jyear: item.jyear, jmonth: item.jmonth, jday: item.jday,
                    hour12: item.hour12 || 10, minute: item.minute || 0, ampm: item.ampm || 'AM'
                };
            } else {
                try { this.initScheduleFromNow(); } catch (e) {}
                this.wizSchedDraft = {
                    jyear: this.gen.jyear, jmonth: this.gen.jmonth, jday: this.gen.jday,
                    hour12: this.gen.hour12, minute: this.gen.minute, ampm: this.gen.ampm
                };
            }
            this.wizItemSchedOpen = true;
            this.$nextTick(() => {
                try {
                    const pop = this.$refs.wizItemSchedPop;
                    if (!pop) return;
                    // center-ish fixed
                    pop.style.position = 'fixed';
                    pop.style.top = '20vh';
                    pop.style.left = '50%';
                    pop.style.transform = 'translateX(-50%)';
                } catch (e) {}
            });
        },
        setWizItemScheduleNow() {
            try { this.initScheduleFromNow(); } catch (e) {}
            this.wizSchedDraft = {
                jyear: this.gen.jyear, jmonth: this.gen.jmonth, jday: this.gen.jday,
                hour12: this.gen.hour12, minute: this.gen.minute, ampm: this.gen.ampm
            };
        },
        applyWizItemSchedule() {
            const idx = this.wizSchedIdx;
            if (idx < 0 || !this.wiz.selected[idx]) return;
            const d = this.wizSchedDraft;
            const item = this.wiz.selected[idx];
            item.jyear = d.jyear; item.jmonth = d.jmonth; item.jday = d.jday;
            item.hour12 = d.hour12; item.minute = d.minute; item.ampm = d.ampm;
            // Convert Jalali → Gregorian schedule_at for WP
            item.schedule_at = this.jalaliDraftToGregorianString(d);
            this.wizItemSchedOpen = false;
        },
        jalaliDraftToGregorianString(d) {
            // Prefer existing project helpers if present
            if (typeof this.jalaliToGregorian === 'function') {
                try {
                    const g = this.jalaliToGregorian(d.jyear, d.jmonth, d.jday);
                    let h = parseInt(d.hour12, 10) || 12;
                    const min = Math.max(0, Math.min(59, parseInt(d.minute, 10) || 0));
                    if (d.ampm === 'PM' && h < 12) h += 12;
                    if (d.ampm === 'AM' && h === 12) h = 0;
                    const pad = (n) => String(n).padStart(2, '0');
                    return g.y + '-' + pad(g.m) + '-' + pad(g.d) + ' ' + pad(h) + ':' + pad(min) + ':00';
                } catch (e) {}
            }
            // Fallback: BankaiJdp or simple approximation via known gen sync
            if (typeof this.j2g === 'function') {
                const g = this.j2g(d.jyear, d.jmonth, d.jday);
                let h = parseInt(d.hour12, 10) || 12;
                const min = Math.max(0, Math.min(59, parseInt(d.minute, 10) || 0));
                if (d.ampm === 'PM' && h < 12) h += 12;
                if (d.ampm === 'AM' && h === 12) h = 0;
                const pad = (n) => String(n).padStart(2, '0');
                return g[0] + '-' + pad(g[1]) + '-' + pad(g[2]) + ' ' + pad(h) + ':' + pad(min) + ':00';
            }
            // Last resort: use gen.schedule fields if synced
            return '';
        },
        wizItemScheduleLabel(item) {
            if (!item) return 'انتخاب تاریخ';
            if (item.jyear && item.jmonth && item.jday) {
                const mname = this.jMonths[(item.jmonth || 1) - 1] || '';
                let h = item.hour12 || 10;
                const min = String(item.minute || 0).padStart(2, '0');
                const ap = item.ampm === 'PM' ? 'ب.ظ' : 'ق.ظ';
                return item.jday + ' ' + mname + ' ' + item.jyear + ' — ' + h + ':' + min + ' ' + ap;
            }
            if (item.schedule_at) return item.schedule_at;
            return 'انتخاب تاریخ جلالی';
        },

        closeWorkspace() { this.ws.open = false; },
        async saveWorkspace() {
            if (!this.ws.id) return;
            this.onEditorInput();
            this.ws.saving = true;
            try {
                const keywords = (this.ws.keywordList && this.ws.keywordList.length)
                    ? this.ws.keywordList.slice()
                    : String(this.ws.keywords_str || '').split(/[,،]+/).map(s => s.trim()).filter(Boolean);
                await fetch(this.restBase() + 'seo/' + this.ws.id, {
                    method: 'POST', credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({
                        seo_title: this.ws.seo_title,
                        description: this.ws.description,
                        focus_keyword: this.ws.focus_keyword,
                        keywords: keywords,
                        canonical: this.ws.canonical,
                        robots: { index: !!this.ws.robots_index, follow: !!this.ws.robots_follow },
                        schema: this.ws.schema,
                        og_title: this.ws.og_title,
                        og_description: this.ws.og_description,
                        og_image: this.ws.og_image,
                        x_title: this.ws.x_title,
                        x_description: this.ws.x_description,
                        x_image: this.ws.x_image
                    })
                });
                this.syncWsScheduleFromUi();
                const postBody = {
                    title: this.ws.title,
                    content: this.ws.content,
                    status: this.ws.status || 'draft'
                };
                if (this.ws.date_iso) {
                    postBody.date = this.ws.date_iso;
                }
                if (this.ws.cover_id) postBody.featured_media = this.ws.cover_id;
                if (!this.ws.cover) postBody.featured_media = 0;
                await fetch('/wp-json/wp/v2/posts/' + this.ws.id, {
                    method: 'POST', credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify(postBody)
                });
                await this.refreshScore();
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('ذخیره شد', 'success');
            } catch (e) {
                console.error(e);
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('خطا در ذخیره', 'error');
            } finally {
                this.ws.saving = false;
            }
        },
        async refreshScore() {
            if (!this.ws.id) return;
            this.onEditorInput();
            try {
                const r = await fetch(this.restBase() + 'seo/analyze/' + this.ws.id, {
                    method: 'POST', credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({
                        content: this.ws.content,
                        seo_title: this.ws.seo_title,
                        description: this.ws.description,
                        focus_keyword: this.ws.focus_keyword
                    })
                });
                const j = await r.json();
                const an = j.analysis || (j.data && j.data.analysis) || j.data || j;
                if (an && an.score != null) this.ws.score = an.score;
                if (an && (an.groups || an.checks)) {
                    this.ws.analysis = {
                        score: an.score || 0,
                        passed: an.passed || 0,
                        total: an.total || 0,
                        groups: an.groups || {},
                        checks: an.checks || []
                    };
                }
            } catch (e) {}
        },
        addKeyword() {
            const parts = String(this.ws.kwDraft || '').split(/[,،]+/).map(s => s.trim()).filter(Boolean);
            parts.forEach(p => {
                if (!this.ws.keywordList.includes(p)) this.ws.keywordList.push(p);
            });
            this.ws.kwDraft = '';
            if (!this.ws.focus_keyword && this.ws.keywordList.length) this.ws.focus_keyword = this.ws.keywordList[0];
        },
        removeKeyword(i) {
            if (i >= 0 && i < this.ws.keywordList.length) this.ws.keywordList.splice(i, 1);
        },
        rebuildWsSchema() {
            const type = this.ws.schemaType || 'Article';
            const schema = {
                '@context': 'https://schema.org',
                '@type': type,
                headline: this.ws.seo_title || this.ws.title || '',
                description: this.ws.description || '',
                url: this.ws.canonical || ''
            };
            if (type === 'FAQPage') {
                schema.mainEntity = (this.ws.faqItems || []).filter(i => (i.q||'').trim()).map(i => ({
                    '@type': 'Question', name: i.q, acceptedAnswer: { '@type': 'Answer', text: i.a || '' }
                }));
            }
            this.ws.schema = JSON.stringify(schema, null, 2);
        },

        wsToast(msg, type) {
            if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast(msg, type || 'info');
            else if (window.bankaiEditorSeo && typeof bankaiEditorSeo.showToast === 'function') {}
            this.ws.aiMsg = msg;
        },
        afterWsLinkApplied(msg) {
            this.wsToast(msg || 'لینک ثبت شد', 'success');
            this.loadWsLinks();
            this.refreshScore();
        },
        formatBytes(n) {
            n = Number(n) || 0;
            if (n < 1024) return n + ' B';
            if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
            return (n / 1048576).toFixed(2) + ' MB';
        },
        findMatchesInContent(term) {
            term = String(term || '').trim();
            if (!term) return [];
            this.onEditorInput();
            const plain = String(this.ws.content || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
            const matches = [];
            const low = plain.toLowerCase();
            const t = term.toLowerCase();
            let from = 0;
            while (matches.length < 40) {
                const idx = low.indexOf(t, from);
                if (idx === -1) break;
                const start = Math.max(0, idx - 28);
                const end = Math.min(plain.length, idx + term.length + 28);
                const snippet = (start > 0 ? '…' : '') + plain.slice(start, end).trim() + (end < plain.length ? '…' : '');
                matches.push({ text: term, index: idx, snippet, selected: true, targetId: '' });
                from = idx + Math.max(1, term.length);
            }
            return matches;
        },
        insertLinkIntoEditor(keyword, url, title, nofollow, bold) {
            if (!url) return false;
            const kw = keyword || title || url;
            const rel = nofollow ? ' rel="nofollow noopener"' : ' rel="noopener"';
            const target = nofollow || /^https?:\/\//i.test(url) && url.indexOf(location.origin) !== 0 ? ' target="_blank"' : '';
            const inner = bold !== false ? ('<strong>' + kw + '</strong>') : kw;
            // Try replace first occurrence of plain keyword in content
            this.onEditorInput();
            let html = this.ws.content || '';
            const plain = kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const re = new RegExp('(?![^<]*>)(' + plain + ')', 'i');
            if (re.test(html.replace(/<a[\s\S]*?<\/a>/gi, ''))) {
                // only replace outside existing anchors - simple approach: replace first text occurrence
                let done = false;
                html = html.replace(new RegExp('(<a[\\s\\S]*?<\\/a>)|(' + plain + ')', 'i'), function (m, a, t) {
                    if (a) return a;
                    if (!done && t) {
                        done = true;
                        return '<a href="' + url + '"' + target + rel + '>' + inner + '</a>';
                    }
                    return m;
                });
                if (done) {
                    this.setEditorContent(html);
                    this.loadWsLinks();
                    return true;
                }
            }
            this.insertHtmlAtCursor('<a href="' + url + '"' + target + rel + '>' + inner + '</a>');
            this.loadWsLinks();
            return true;
        },
        async loadWsLinks() {
            if (!this.ws.id) {
                // local scan
                this.scanLinksLocal();
                return;
            }
            try {
                const r = await fetch(this.restBase() + 'seo/links/' + this.ws.id, {
                    credentials: 'same-origin', headers: this.restHeaders(false)
                });
                const j = await r.json();
                const data = (j && j.data) ? j.data : j;
                if (data && (data.internal || data.external)) {
                    this.ws.linkData = {
                        internal: Array.isArray(data.internal) ? data.internal : [],
                        external: Array.isArray(data.external) ? data.external : [],
                        counts: {
                            internal: Array.isArray(data.internal) ? data.internal.length : 0,
                            external: Array.isArray(data.external) ? data.external.length : 0
                        }
                    };
                    this.ws.linkStats = this.ws.linkData.counts;
                    return;
                }
            } catch (e) {}
            this.scanLinksLocal();
        },
        scanLinksLocal() {
            this.onEditorInput();
            const html = this.ws.content || '';
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const internal = [], external = [];
            const origin = location.origin || '';
            doc.querySelectorAll('a[href]').forEach((a) => {
                const href = a.getAttribute('href') || '';
                const item = { href, text: (a.textContent || '').trim(), anchor: (a.textContent || '').trim() };
                if (href.startsWith('/') || href.indexOf(origin) === 0 || href.indexOf('#') === 0) internal.push(item);
                else if (/^https?:\/\//i.test(href)) external.push(item);
            });
            this.ws.linkData = { internal, external, counts: { internal: internal.length, external: external.length } };
            this.ws.linkStats = this.ws.linkData.counts;
        },
        async runInternalSearch() {
            const term = (this.ws.linkAnchor || this.ws.focus_keyword || this.ws.linkQuery || '').trim();
            if (!term) {
                this.wsToast('کلیدواژه را وارد کنید', 'warn');
                return;
            }
            this.ws.linkAnchor = term;
            this.ws.linkBusy = true;
            this.ws.linkSearchLabel = 'در حال جستجو…';
            this.ws.linkStatusMsg = 'در حال جستجوی مقالات مرتبط…';
            this.ws.postResults = [];
            try {
                const r = await fetch(this.restBase() + 'seo/search-posts?s=' + encodeURIComponent(term) + '&post_id=' + (this.ws.id || 0), {
                    credentials: 'same-origin', headers: this.restHeaders(false)
                });
                let posts = [];
                if (r.ok) {
                    const j = await r.json();
                    posts = (j.data && (j.data.posts || j.data)) || j.posts || [];
                    if (!Array.isArray(posts)) posts = [];
                }
                if (!posts.length) {
                    const r2 = await fetch('/wp-json/wp/v2/posts?search=' + encodeURIComponent(term) + '&per_page=10', {
                        credentials: 'same-origin', headers: this.restHeaders(false)
                    });
                    const arr = await r2.json();
                    posts = (Array.isArray(arr) ? arr : []).map(p => ({
                        id: p.id,
                        title: p.title && p.title.rendered ? p.title.rendered.replace(/<[^>]+>/g, '') : '',
                        permalink: p.link
                    }));
                }
                try {
                    const r3 = await fetch(this.restBase() + 'seo/suggest-links', {
                        method: 'POST', credentials: 'same-origin', headers: this.restHeaders(true),
                        body: JSON.stringify({
                            post_id: this.ws.id,
                            keywords: [term].concat(this.ws.keywordList || []),
                            content: (this.ws.content || '').slice(0, 4000)
                        })
                    });
                    const j3 = await r3.json();
                    if (j3.success && j3.data && Array.isArray(j3.data.suggestions)) {
                        j3.data.suggestions.forEach(s => {
                            if (!posts.some(p => p.id === s.id)) {
                                posts.push({ id: s.id, title: s.title, permalink: s.permalink, match_reason: s.in_content ? 'کلیدواژه در محتوا' : 'مرتبط با سئو' });
                            }
                        });
                    }
                } catch (e2) {}
                this.ws.postResults = posts.filter(p => p.id !== this.ws.id).map(p => Object.assign({}, p, {
                    accepted: false, rejected: false,
                    match_reason: p.match_reason || ((p.title && String(p.title).toLowerCase().includes(term.toLowerCase())) ? 'تطابق عنوان' : 'جستجوی سایت')
                }));
                if (this.ws.postResults.length) {
                    this.ws.linkSearchLabel = this.ws.postResults.length + ' یافت شد';
                    this.ws.linkStatusMsg = this.ws.postResults.length + ' مقاله یافت شد — عنوان را تایید کنید';
                    this.wsToast(this.ws.postResults.length + ' مقاله یافت شد', 'success');
                } else {
                    this.ws.linkSearchLabel = 'یافت نشد';
                    this.ws.linkStatusMsg = 'مقاله مرتبطی پیدا نشد';
                    this.wsToast('مقاله مرتبطی یافت نشد', 'info');
                }
            } catch (e) {
                this.ws.postResults = [];
                this.ws.linkStatusMsg = 'خطا در جستجو';
                this.wsToast('خطا در جستجوی مقالات', 'error');
            } finally {
                this.ws.linkBusy = false;
                setTimeout(() => { this.ws.linkSearchLabel = 'جستجو'; }, 2000);
            }
        },
        acceptedInternalPosts() {
            return (this.ws.postResults || []).filter(p => p.accepted && !p.rejected);
        },
        acceptAllInternalPosts() {
            (this.ws.postResults || []).forEach(p => { p.accepted = true; p.rejected = false; });
        },
        loadInternalContentMatches() {
            const term = (this.ws.linkAnchor || this.ws.focus_keyword || '').trim();
            if (!term) {
                this.wsToast('کلیدواژه خالی است', 'warn');
                return;
            }
            if (!this.acceptedInternalPosts().length) {
                this.wsToast('ابتدا حداقل یک مقاله را تایید کنید', 'warn');
                return;
            }
            this.ws.contentMatchesInternal = this.findMatchesInContent(term);
            const posts = this.acceptedInternalPosts();
            this.ws.contentMatchesInternal.forEach((m, i) => {
                if (posts.length) {
                    m.targetId = String(posts[i % posts.length].id);
                    m.selected = true;
                }
            });
            if (this.ws.contentMatchesInternal.length) {
                this.ws.linkStatusMsg = this.ws.contentMatchesInternal.length + ' محل در متن یافت شد';
                this.wsToast(this.ws.contentMatchesInternal.length + ' تطابق در متن', 'success');
            } else {
                this.ws.linkStatusMsg = 'عبارتی در متن برای لینک یافت نشد';
                this.wsToast('عبارتی در متن یافت نشد', 'warn');
            }
        },
        confirmInternalLinking() {
            const selected = (this.ws.contentMatchesInternal || []).filter(m => m.selected && m.targetId);
            const posts = this.acceptedInternalPosts();
            if (!selected.length) {
                this.wsToast('ابتدا تطابق متن و مقاله هدف را انتخاب کنید', 'warn');
                return;
            }
            this.ws.linkApplyBusy = true;
            this.ws.linkStatusMsg = 'در حال ثبت لینک داخلی…';
            let applied = 0;
            selected.forEach(m => {
                const p = posts.find(x => String(x.id) === String(m.targetId)) || posts[0];
                if (!p || !p.permalink) return;
                if (this.insertLinkIntoEditor(m.text || this.ws.linkAnchor, p.permalink, p.title, false, true)) applied++;
            });
            this.ws.linkApplyBusy = false;
            if (applied) {
                this.ws.contentMatchesInternal = [];
                this.afterWsLinkApplied(applied + ' لینک داخلی ثبت شد');
            } else {
                this.wsToast('ثبت لینک داخلی ناموفق بود', 'error');
            }
        },
        applyInternalRandom() {
            const posts = this.acceptedInternalPosts();
            if (!posts.length) {
                this.wsToast('ابتدا مقالات را تایید کنید', 'warn');
                return;
            }
            if (!this.ws.contentMatchesInternal.length) this.loadInternalContentMatches();
            let applied = 0;
            (this.ws.contentMatchesInternal || []).filter(m => m.selected).forEach(m => {
                const p = posts[Math.floor(Math.random() * posts.length)];
                if (p && this.insertLinkIntoEditor(m.text || this.ws.linkAnchor, p.permalink, p.title, false, true)) applied++;
            });
            if (applied) {
                this.ws.contentMatchesInternal = [];
                this.afterWsLinkApplied(applied + ' لینک داخلی (رندوم) ثبت شد');
            } else {
                this.wsToast('لینک رندوم اعمال نشد', 'warn');
            }
        },
        loadExternalContentMatches() {
            const term = (this.ws.extAnchor || '').trim();
            if (!term) {
                this.wsToast('عبارت انکر را وارد کنید', 'warn');
                return;
            }
            this.ws.extFindBusy = true;
            this.ws.linkStatusMsg = 'جستجو در متن مقاله…';
            this.ws.contentMatchesExternal = this.findMatchesInContent(term);
            this.ws.extFindBusy = false;
            if (this.ws.contentMatchesExternal.length) {
                this.ws.linkStatusMsg = this.ws.contentMatchesExternal.length + ' تطابق در متن یافت شد';
                this.wsToast(this.ws.contentMatchesExternal.length + ' عبارت در متن یافت شد', 'success');
            } else {
                this.ws.linkStatusMsg = 'عبارتی در متن یافت نشد — می‌توانید مستقیم ثبت کنید';
                this.wsToast('عبارتی در متن یافت نشد', 'info');
            }
        },
        confirmExternalLinking() {
            if (!this.ws.extUrl) {
                this.wsToast('آدرس URL را وارد کنید', 'warn');
                return;
            }
            this.ws.extApplyBusy = true;
            this.ws.linkStatusMsg = 'در حال ثبت لینک خارجی…';
            const selected = (this.ws.contentMatchesExternal || []).filter(m => m.selected);
            let applied = 0;
            if (selected.length) {
                selected.forEach(m => {
                    if (this.insertLinkIntoEditor(m.text || this.ws.extAnchor, this.ws.extUrl, '', !!this.ws.extNofollow, true)) applied++;
                });
            } else {
                if (this.insertLinkIntoEditor(this.ws.extAnchor || this.ws.extUrl, this.ws.extUrl, '', !!this.ws.extNofollow, true)) applied++;
            }
            this.ws.extApplyBusy = false;
            if (applied) {
                this.ws.contentMatchesExternal = [];
                this.afterWsLinkApplied(applied + ' لینک خارجی ثبت شد');
            } else {
                this.wsToast('ثبت لینک خارجی ناموفق بود', 'error');
                this.ws.linkStatusMsg = 'ثبت ناموفق';
            }
        },
        removeLinkFromContent(link) {
            if (!link || !link.href) return;
            this.onEditorInput();
            let html = this.ws.content || '';
            try {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                let removed = 0;
                doc.querySelectorAll('a[href]').forEach(a => {
                    if ((a.getAttribute('href') || '') === link.href) {
                        const textNode = doc.createTextNode(a.textContent || '');
                        if (a.parentNode) { a.parentNode.replaceChild(textNode, a); removed++; }
                    }
                });
                if (removed) {
                    this.setEditorContent(doc.body ? doc.body.innerHTML : html);
                    this.afterWsLinkApplied('لینک حذف شد');
                } else {
                    this.wsToast('لینک در متن یافت نشد', 'warn');
                }
            } catch (e) {}
        },
        async aiAltForImages() {
            if (!(this.ws.postImages || []).length) this.scanWsImages();
            for (const img of (this.ws.postImages || [])) {
                if (img.has_alt && (img.alt || '').trim()) continue;
                this.ws.aiBusy = true;
                this.ws.aiProgressLabel = 'تولید Alt…';
                try {
                    const fd = new FormData();
                    fd.append('action', 'bankai_ai_seo_task');
                    fd.append('nonce', this.adminNonce());
                    fd.append('task', 'image_alt_text');
                    fd.append('title', this.ws.title || '');
                    fd.append('content', (this.ws.content || '').slice(0, 2000));
                    fd.append('focus_keyword', this.ws.focus_keyword || '');
                    fd.append('locale', 'fa_IR');
                    fd.append('image_url', img.src || '');
                    const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', { method: 'POST', body: fd, credentials: 'same-origin' });
                    const j = await r.json();
                    const alt = (j.data && (j.data.alt || j.data.text || j.data.image_alt)) || '';
                    if (alt) this.updateWsImgAlt(img, String(alt).trim());
                } catch (e) {}
            }
            this.ws.aiBusy = false;
        },


        applyEditorPrefs() {
            const el = document.getElementById(this.editorId);
            if (!el) return;
            el.style.fontSize = this.editorPrefs.fontSize || '15px';
            if (this.editorPrefs.autoDir) el.setAttribute('dir', 'rtl');
        },
        insertGalleryFromMedia() {
            const self = this;
            if (!window.wp || !wp.media) {
                alert('کتابخانه رسانه در دسترس نیست');
                return;
            }
            const frame = wp.media({ title: 'انتخاب چند تصویر', button: { text: 'درج گالری' }, multiple: true, library: { type: 'image' } });
            frame.on('select', function () {
                const sel = frame.state().get('selection');
                let html = '<div class="bk-gallery" style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin:12px 0">';
                sel.each(function (att) {
                    const j = att.toJSON();
                    const src = j.url || '';
                    const alt = (j.alt || j.title || '').replace(/"/g, '&quot;');
                    html += '<img src="' + src + '" alt="' + alt + '" style="width:100%;border-radius:8px">';
                });
                html += '</div>';
                const el = document.getElementById(self.editorId);
                if (el) el.focus();
                document.execCommand('insertHTML', false, html);
                self.onEditorInput();
                self.scanWsImages();
            });
            frame.open();
        },
        insertVideoEmbed() {
            const url = window.prompt('لینک ویدیو (YouTube / مستقیم):', 'https://');
            if (!url) return;
            let embed = '';
            const yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]{6,})/);
            if (yt) {
                embed = '<p><iframe width="100%" height="360" src="https://www.youtube.com/embed/' + yt[1] + '" frameborder="0" allowfullscreen loading="lazy"></iframe></p>';
            } else if (/\.(mp4|webm)(\?|$)/i.test(url)) {
                embed = '<p><video controls style="max-width:100%" src="' + url + '"></video></p>';
            } else {
                embed = '<p><a href="' + url + '" target="_blank" rel="noopener">' + url + '</a></p>';
            }
            const el = document.getElementById(this.editorId);
            if (el) el.focus();
            document.execCommand('insertHTML', false, embed);
            this.onEditorInput();
        },
        setAsCover(img) {
            if (!img || !img.src) return;
            this.ws.cover = img.src;
            if (img.attachment_id) this.ws.cover_id = img.attachment_id;
            this.ws.tab = 'cover';
            if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('به‌عنوان کاور تنظیم شد', 'success');
        },
        replaceImageInContent(img) {
            const self = this;
            if (!window.wp || !wp.media) return;
            const frame = wp.media({ title: 'جایگزینی تصویر', button: { text: 'جایگزین کن' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                const newSrc = att.url || '';
                if (!newSrc || !img.src) return;
                let html = self.getEditorContent();
                html = html.split(img.src).join(newSrc);
                self.setEditorContent(html);
                self.scanWsImages();
            });
            frame.open();
        },
        removeImageFromContent(img) {
            if (!img || !img.src) return;
            if (!confirm('این تصویر از محتوای مقاله حذف شود؟')) return;
            const el = document.getElementById(this.editorId);
            if (!el) return;
            el.querySelectorAll('img').forEach(function (node) {
                if ((node.getAttribute('src') || '') === img.src) {
                    const p = node.parentElement;
                    node.remove();
                    if (p && p.tagName === 'P' && !p.textContent.trim() && !p.querySelector('img')) p.remove();
                }
            });
            this.onEditorInput();
            this.scanWsImages();
        },
        previewMedia(img) {
            if (img && img.src) window.open(img.src, '_blank', 'noopener');
        },

        pickCover() {
            this.pickMediaField('cover');
        },
        pickMediaField(field) {
            if (!window.wp || !wp.media) {
                alert('کتابخانه رسانه در دسترس نیست');
                return;
            }
            const frame = wp.media({ title: 'انتخاب تصویر', button: { text: 'انتخاب' }, multiple: false, library: { type: 'image' } });
            const self = this;
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                if (field === 'cover') {
                    self.ws.cover = att.url;
                    self.ws.cover_id = att.id;
                } else if (field === 'og_image') {
                    self.ws.og_image = att.url;
                } else if (field === 'x_image') {
                    self.ws.x_image = att.url;
                }
            });
            frame.open();
        }
    }));
});
</script>
