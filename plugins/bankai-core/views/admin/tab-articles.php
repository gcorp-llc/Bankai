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
</style>

    <!-- LIST / GENERATE / SCHEDULE (hidden while editor open) -->
    <div x-show="!ws.open">
        <div class="bk-art-head">
            <div>
                <h2>
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    مدیریت مقالات
                </h2>
                <p>ویرایش درون‌پنلی، تولید AI و سئوی کامل بدون خروج از بنکای</p>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" @click="panel='generate'">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
                تولید مقاله با AI
            </button>
        </div>

        <div class="bk-art-tabs">
            <button type="button" class="bk-art-tab" :class="{'is-active': panel==='list'}" @click="panel='list'; loadArticles()">مشاهده مقالات</button>
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
                                        <button type="button" class="bk-btn bk-btn-primary" @click="openWorkspace(row)" title="ویرایش در پنل بنکای">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                            ویرایش
                                        </button>
                                        <button type="button" class="bk-btn bk-btn-ai bk-btn-icon" @click="openWorkspace(row, 'seo')" title="سئو و AI">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                                        </button>
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

        <div class="bk-art-card" x-show="panel==='generate'" x-cloak>
            <h3 style="margin:0 0 12px;font-size:16px;font-weight:800">تولید مقاله با هوش مصنوعی</h3>
            <div class="bk-field">
                <label>موضوع / عنوان</label>
                <input type="text" x-model="gen.topic" placeholder="موضوع مقاله…" style="width:100%">
            </div>
            <div class="bk-grid-2">
                <div class="bk-field">
                    <label>کلمه کلیدی اصلی</label>
                    <input type="text" x-model="gen.focus" style="width:100%">
                </div>
                <div class="bk-field">
                    <label>طول متن (کلمه)</label>
                    <select x-model="gen.length" style="width:100%">
                        <option value="1500">۱۵۰۰</option>
                        <option value="3000">۳۰۰۰</option>
                        <option value="5000">۵۰۰۰</option>
                        <option value="7000">۷۰۰۰</option>
                    </select>
                </div>
            </div>
            <div class="bk-field">
                <label>دستورالعمل اضافه (اختیاری)</label>
                <textarea x-model="gen.notes" rows="3" style="width:100%"></textarea>
            </div>
            <div class="bk-grid-2">
                <div class="bk-field">
                    <label>وضعیت انتشار</label>
                    <select x-model="gen.status" style="width:100%">
                        <option value="draft">پیش‌نویس</option>
                        <option value="future">زمان‌بندی‌شده</option>
                        <option value="publish">انتشار فوری</option>
                    </select>
                </div>
                <div class="bk-field" x-show="gen.status==='future'" x-cloak>
                    <label>زمان انتشار</label>
                    <button type="button" class="bk-sched-toggle" x-ref="schedToggle" @click="toggleSchedulePop()"
                            :aria-expanded="schedOpen ? 'true' : 'false'">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span x-text="scheduleLabel()"></span>
                    </button>
                    <div class="bk-sched-pop" x-ref="schedPop" x-show="schedOpen" x-cloak x-transition.opacity
                         @click.outside="schedOpen=false">
                        <div class="bk-sched-pop-head">
                            <strong>انتشار</strong>
                            <div class="bk-sched-pop-actions">
                                <button type="button" class="bk-sched-now" @click="setScheduleNow()">اکنون</button>
                                <button type="button" class="bk-sched-close" @click="schedOpen=false" aria-label="بستن">×</button>
                            </div>
                        </div>
                        <div class="bk-sched-time-row">
                            <span class="bk-sched-legend">زمان</span>
                            <div class="bk-sched-time-fields">
                                <input type="number" min="1" max="12" x-model.number="gen.hour12" @change="syncScheduleFromUi()" class="bk-sched-num">
                                <span>:</span>
                                <input type="number" min="0" max="59" x-model.number="gen.minute" @change="syncScheduleFromUi()" class="bk-sched-num">
                                <div class="bk-sched-ampm">
                                    <button type="button" :class="{'is-on': gen.ampm==='AM'}" @click="gen.ampm='AM'; syncScheduleFromUi()">ق.ظ</button>
                                    <button type="button" :class="{'is-on': gen.ampm==='PM'}" @click="gen.ampm='PM'; syncScheduleFromUi()">ب.ظ</button>
                                </div>
                                <span class="bk-sched-tz" x-text="tzLabel"></span>
                            </div>
                        </div>
                        <div class="bk-sched-date-row">
                            <span class="bk-sched-legend">تاریخ (جلالی)</span>
                            <div class="bk-sched-date-fields">
                                <input type="number" min="1" max="31" x-model.number="gen.jday" @change="onJalaliFieldsChange()" class="bk-sched-num" title="روز">
                                <select x-model.number="gen.jmonth" @change="onJalaliFieldsChange()" class="bk-sched-month">
                                    <template x-for="(m,i) in jMonths" :key="'jm'+i">
                                        <option :value="i+1" x-text="m"></option>
                                    </template>
                                </select>
                                <input type="number" min="1300" max="1500" x-model.number="gen.jyear" @change="onJalaliFieldsChange()" class="bk-sched-num bk-sched-year" title="سال">
                            </div>
                        </div>
                        <div class="bk-sched-cal">
                            <div class="bk-sched-cal-head">
                                <button type="button" @click="shiftJMonth(-1)" aria-label="ماه قبل">‹</button>
                                <strong x-text="(jMonths[gen.jmonth-1]||'') + ' ' + gen.jyear"></strong>
                                <button type="button" @click="shiftJMonth(1)" aria-label="ماه بعد">›</button>
                            </div>
                            <div class="bk-sched-cal-grid">
                                <template x-for="d in ['ش','ی','د','س','چ','پ','ج']" :key="'dow'+d"><span class="dow" x-text="d"></span></template>
                                <template x-for="cell in jCalendarCells()" :key="'c'+cell.key">
                                    <button type="button" class="day"
                                            :class="{'is-sel': cell.sel, 'is-today': cell.today, 'muted': cell.muted}"
                                            :disabled="cell.muted"
                                            @click="pickJDay(cell)"
                                            x-text="cell.d || ''"></button>
                                </template>
                            </div>
                        </div>
                        <p class="bk-sched-hint">خروجی ذخیره‌شده برای وردپرس: <code x-text="scheduleIso() || '—'"></code> (میلادی)</p>
                    </div>
                </div>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:12px">
                <input type="checkbox" x-model="gen.do_seo"> تکمیل خودکار سئو با AI پس از تولید
            </label>
            <button type="button" class="bk-btn bk-btn-primary" @click="generateArticle()" :disabled="genBusy">
                <span x-text="genBusy ? 'در حال تولید…' : 'شروع تولید مقاله'"></span>
            </button>
            <p x-show="genMessage" style="margin:10px 0 0;font-size:13px;font-weight:600" x-text="genMessage"></p>
            <div class="bk-gen-progress" x-show="genBusy || genProgress > 0" x-cloak>
                <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700">
                    <span x-text="genStepLabel"></span>
                    <span x-text="genProgress + '%'"></span>
                </div>
                <div class="bk-gen-bar"><i :style="'width:' + genProgress + '%'"></i></div>
                <div class="bk-gen-steps">
                    <span class="bk-gen-step" :class="{'is-done': genProgress>=20, 'is-run': genProgress>0 && genProgress<20}">۱. تولید محتوا</span>
                    <span class="bk-gen-step" :class="{'is-done': genProgress>=45, 'is-run': genProgress>=20 && genProgress<45}">۲. ذخیره پست</span>
                    <span class="bk-gen-step" :class="{'is-done': genProgress>=70, 'is-run': genProgress>=45 && genProgress<70}">۳. کلیدواژه و متا</span>
                    <span class="bk-gen-step" :class="{'is-done': genProgress>=100, 'is-run': genProgress>=70 && genProgress<100}">۴. نهایی‌سازی</span>
                </div>
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
                        <button type="button" class="bk-btn" x-show="item.post_id" @click="openWorkspace({id:item.post_id,title:item.title||item.topic})">ویرایش در پنل</button>
                        <button type="button" class="bk-btn" @click="removeQueueItem(idx)">حذف از صف</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- IN-PANEL EDITOR + SEO SIDEBAR -->
    <div class="bk-editor-shell" :class="{'is-open': ws.open}" x-show="ws.open" x-cloak>
        <div class="bk-editor-topbar">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <button type="button" class="bk-btn" @click="closeWorkspace()">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    بازگشت به لیست
                </button>
                <span style="font-size:12px;color:#64748b" x-text="'#' + ws.id"></span>
                <select x-model="ws.status" style="padding:6px 10px;font-size:12px">
                    <option value="draft">پیش‌نویس</option>
                    <option value="pending">در انتظار</option>
                    <option value="future">زمان‌بندی</option>
                    <option value="publish">منتشرشده</option>
                </select>
                <div style="position:relative;display:inline-flex;align-items:center">
                    <button type="button" class="bk-sched-toggle" x-ref="wsSchedToggle" @click="toggleWsSchedulePop()"
                            :aria-expanded="wsSchedOpen ? 'true' : 'false'" title="تاریخ انتشار">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span x-text="wsScheduleLabel()"></span>
                    </button>
                    <div class="bk-sched-pop" x-ref="wsSchedPop" x-show="wsSchedOpen" x-cloak x-transition.opacity
                         @click.outside="wsSchedOpen=false">
                        <div class="bk-sched-pop-head">
                            <strong>تاریخ انتشار</strong>
                            <div class="bk-sched-pop-actions">
                                <button type="button" class="bk-sched-now" @click="setWsScheduleNow()">اکنون</button>
                                <button type="button" class="bk-sched-close" @click="wsSchedOpen=false" aria-label="بستن">×</button>
                            </div>
                        </div>
                        <div class="bk-sched-time-row">
                            <span class="bk-sched-legend">زمان</span>
                            <div class="bk-sched-time-fields">
                                <input type="number" min="1" max="12" x-model.number="ws.hour12" @change="syncWsScheduleFromUi()" class="bk-sched-num">
                                <span>:</span>
                                <input type="number" min="0" max="59" x-model.number="ws.minute" @change="syncWsScheduleFromUi()" class="bk-sched-num">
                                <div class="bk-sched-ampm">
                                    <button type="button" :class="{'is-on': ws.ampm==='AM'}" @click="ws.ampm='AM'; syncWsScheduleFromUi()">ق.ظ</button>
                                    <button type="button" :class="{'is-on': ws.ampm==='PM'}" @click="ws.ampm='PM'; syncWsScheduleFromUi()">ب.ظ</button>
                                </div>
                            </div>
                        </div>
                        <div class="bk-sched-date-row">
                            <span class="bk-sched-legend">تاریخ شمسی</span>
                            <div class="bk-sched-date-fields">
                                <input type="number" x-model.number="ws.jyear" @change="onWsJalaliFieldsChange()" class="bk-sched-num bk-sched-year">
                                <select x-model.number="ws.jmonth" @change="onWsJalaliFieldsChange()" class="bk-sched-month">
                                    <template x-for="(m,i) in jMonths" :key="'wsm'+i">
                                        <option :value="i+1" x-text="m"></option>
                                    </template>
                                </select>
                                <input type="number" min="1" max="31" x-model.number="ws.jday" @change="onWsJalaliFieldsChange()" class="bk-sched-num">
                            </div>
                        </div>
                        <div class="bk-sched-cal">
                            <div class="bk-sched-cal-head">
                                <button type="button" @click="shiftWsJMonth(-1)">‹</button>
                                <strong x-text="(jMonths[(ws.jmonth||1)-1]||'') + ' ' + (ws.jyear||'')"></strong>
                                <button type="button" @click="shiftWsJMonth(1)">›</button>
                            </div>
                            <div class="bk-sched-cal-grid">
                                <div class="dow">ش</div><div class="dow">ی</div><div class="dow">د</div><div class="dow">س</div><div class="dow">چ</div><div class="dow">پ</div><div class="dow">ج</div>
                                <template x-for="(cell, ci) in wsCalCells()" :key="'wsc'+ci">
                                    <button type="button" class="day" :class="{'is-sel': cell.sel, 'is-today': cell.today, 'muted': cell.muted}"
                                            :disabled="cell.muted" @click="pickWsJDay(cell)" x-text="cell.d || ''"></button>
                                </template>
                            </div>
                        </div>
                        <p class="bk-sched-hint">ذخیره میلادی برای وردپرس: <code x-text="ws.date_iso || '—'" dir="ltr"></code></p>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="button" class="bk-btn" @click="refreshScore()" :disabled="ws.saving">تحلیل سئو</button>
                <button type="button" class="bk-btn bk-btn-primary" @click="saveWorkspace()" :disabled="ws.saving">
                    <span x-text="ws.saving ? 'در حال ذخیره…' : 'ذخیره مقاله'"></span>
                </button>
            </div>
        </div>

        <div class="bk-editor-layout">
            <div class="bk-editor-main" :class="{'is-loading': editorLoading}">
                <div class="bk-editor-loader" x-show="editorLoading" x-cloak>
                    <div class="bk-spin" aria-hidden="true"></div>
                    <div class="bk-editor-loader-text">در حال بارگذاری محتوا…</div>
                </div>
                <input type="text" class="bk-editor-title" x-model="ws.title" placeholder="عنوان مقاله" :disabled="editorLoading">
                <div class="bk-lite-editor">
                    <div class="bk-lite-toolbar" role="toolbar">
                        <div class="bk-lite-row">
                            <button type="button" data-cmd="undo" title="بازگردانی">↶</button>
                            <button type="button" data-cmd="redo" title="بازانجام">↷</button>
                            <span class="bk-lite-sep"></span>
                            <select class="bk-lite-select" data-cmd="formatBlock" title="قالب">
                                <option value="">قالب</option>
                                <option value="p">پاراگراف</option>
                                <option value="h2">عنوان H2</option>
                                <option value="h3">عنوان H3</option>
                                <option value="h4">عنوان H4</option>
                                <option value="blockquote">نقل‌قول</option>
                                <option value="pre">کد</option>
                            </select>
                            <span class="bk-lite-sep"></span>
                            <button type="button" data-cmd="bold" title="پررنگ"><b>B</b></button>
                            <button type="button" data-cmd="italic" title="کج"><i>I</i></button>
                            <button type="button" data-cmd="underline" title="زیرخط"><u>U</u></button>
                            <button type="button" data-cmd="strikeThrough" title="خط‌خورده"><s>S</s></button>
                            <span class="bk-lite-sep"></span>
                            <input type="color" class="bk-lite-color" data-cmd="foreColor" title="رنگ متن" value="#1a1a1a">
                            <input type="color" class="bk-lite-color" data-cmd="hiliteColor" title="هایلایت" value="#fff59d">
                        </div>
                        <div class="bk-lite-row">
                            <button type="button" data-cmd="justifyRight" title="راست">☰R</button>
                            <button type="button" data-cmd="justifyCenter" title="وسط">☰C</button>
                            <button type="button" data-cmd="justifyLeft" title="چپ">☰L</button>
                            <button type="button" data-cmd="justifyFull" title="کامل">☰J</button>
                            <span class="bk-lite-sep"></span>
                            <button type="button" data-cmd="insertUnorderedList" title="لیست">•</button>
                            <button type="button" data-cmd="insertOrderedList" title="عددی">1.</button>
                            <button type="button" data-cmd="indent" title="تورفتگی">⇥</button>
                            <button type="button" data-cmd="outdent" title="کاهش تورفتگی">⇤</button>
                            <span class="bk-lite-sep"></span>
                            <button type="button" data-cmd="createLink" title="لینک">لینک</button>
                            <button type="button" data-cmd="unlink" title="حذف لینک">حذف لینک</button>
                            <button type="button" data-cmd="insertImage" title="درج تصویر">تصویر</button>
                            <button type="button" data-cmd="insertGallery" title="چند تصویر">گالری</button>
                            <button type="button" data-cmd="insertVideo" title="ویدیو/embed">ویدیو</button>
                            <span class="bk-lite-sep"></span>
                            <button type="button" data-cmd="removeFormat" title="پاک‌سازی قالب">پاک‌سازی</button>
                            <button type="button" data-cmd="selectAll" title="انتخاب همه">همه</button>
                            <button type="button" data-mode="html" class="bk-lite-html-btn" title="HTML">HTML</button>
                            <button type="button" data-cmd="openMediaPanel" title="مدیریت رسانه">رسانه</button>
                            <button type="button" data-cmd="openAdv" title="تنظیمات ادیتور">⚙</button>
                        </div>
                    </div>
                    <div class="bk-lite-adv" x-show="editorAdvOpen" x-cloak>
                        <label><input type="checkbox" x-model="editorPrefs.pastePlain"> چسباندن ساده (بدون استایل)</label>
                        <label><input type="checkbox" x-model="editorPrefs.autoDir"> جهت خودکار RTL</label>
                        <label>اندازه فونت
                            <select x-model="editorPrefs.fontSize" @change="applyEditorPrefs()">
                                <option value="14px">۱۴</option>
                                <option value="15px">۱۵</option>
                                <option value="16px">۱۶</option>
                                <option value="18px">۱۸</option>
                            </select>
                        </label>
                        <button type="button" class="bk-btn" @click="editorAdvOpen=false">بستن</button>
                    </div>
                    <div id="bk_art_editor"
                         class="bk-lite-body"
                         contenteditable="true"
                         data-placeholder="متن مقاله را بنویسید…"
                         dir="rtl"></div>
                    <textarea id="bk_art_editor_html" class="bk-lite-html" style="display:none" dir="ltr" rows="16"></textarea>
                </div>
            </div>

            <aside class="bk-seo-side">
                <div class="bk-seo-side-head">
                    <div class="bk-score-ring">
                        <svg viewBox="0 0 36 36">
                            <circle class="bk-track" cx="18" cy="18" r="14"></circle>
                            <circle class="bk-progress" cx="18" cy="18" r="14"
                                :stroke="scoreColor(ws.score)"
                                :stroke-dasharray="scoreCirc"
                                :stroke-dashoffset="scoreOff"></circle>
                        </svg>
                        <span class="bk-score-num" x-text="ws.score||0"></span>
                    </div>
                    <div style="flex:1;min-width:0">
                        <strong style="font-size:13px">سئوی بنکای</strong>
                        <div style="font-size:11px;color:#64748b" x-text="(ws.analysis.passed||0) + ' از ' + (ws.analysis.total||0) + ' مورد'"></div>
                    </div>
                    <button type="button" class="bk-btn bk-btn-icon" @click="refreshScore()" title="تحلیل مجدد">↻</button>
                </div>
                <div class="bk-seo-tabs">
                    <button type="button" :class="{'is-on': ws.tab==='seo'}" @click="ws.tab='seo'">سئو</button>
                    <button type="button" :class="{'is-on': ws.tab==='links'}" @click="ws.tab='links'; loadWsLinks()">لینک‌ها</button>
                    <button type="button" :class="{'is-on': ws.tab==='cover'}" @click="ws.tab='cover'">کاور</button>
                    <button type="button" :class="{'is-on': ws.tab==='media'}" @click="ws.tab='media'; scanWsImages()">تصاویر</button>
                    <button type="button" :class="{'is-on': ws.tab==='schema'}" @click="ws.tab='schema'">اسکیما</button>
                    <button type="button" :class="{'is-on': ws.tab==='social'}" @click="ws.tab='social'">سوشال</button>
                    <button type="button" :class="{'is-on': ws.tab==='ai'}" @click="ws.tab='ai'">AI</button>
                </div>

                <!-- SEO -->
                <div class="bk-seo-panel" x-show="ws.tab==='seo'">
                    <div class="bk-serp-mini" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px;margin-bottom:12px">
                        <div style="font-size:11px;color:#0f7b3a;direction:ltr;text-align:left;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" x-text="ws.canonical || '…'"></div>
                        <div style="font-size:14px;font-weight:700;color:#1a0dab;margin:4px 0" x-text="ws.seo_title || ws.title || 'عنوان'"></div>
                        <div style="font-size:12px;color:#4d5156;line-height:1.4" x-text="ws.description || 'توضیحات متا…'"></div>
                    </div>
                    <div class="bk-field">
                        <label>عنوان سئو</label>
                        <div style="display:flex;gap:6px">
                            <input type="text" x-model="ws.seo_title" maxlength="70" style="flex:1" @change="refreshScore()">
                            <button type="button" class="bk-btn bk-btn-ai bk-btn-icon" @click="wsAi('meta_title')" :disabled="ws.aiBusy">AI</button>
                        </div>
                        <div class="bk-hint" x-text="(ws.seo_title||'').length + '/70'"></div>
                    </div>
                    <div class="bk-field">
                        <label>متا دیسکریپشن</label>
                        <div style="display:flex;gap:6px;align-items:flex-start">
                            <textarea x-model="ws.description" rows="3" maxlength="170" style="flex:1" @change="refreshScore()"></textarea>
                            <button type="button" class="bk-btn bk-btn-ai bk-btn-icon" @click="wsAi('meta_description')" :disabled="ws.aiBusy">AI</button>
                        </div>
                        <div class="bk-hint" x-text="(ws.description||'').length + '/160'"></div>
                    </div>
                    <div class="bk-field">
                        <label>کلمه کلیدی اصلی</label>
                        <div style="display:flex;gap:6px">
                            <input type="text" x-model="ws.focus_keyword" style="flex:1" @change="refreshScore()">
                            <button type="button" class="bk-btn bk-btn-ai bk-btn-icon" @click="wsAi('focus_keyword')" :disabled="ws.aiBusy">AI</button>
                        </div>
                    </div>
                    <div class="bk-field">
                        <label>کلیدواژه‌ها</label>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px">
                            <template x-for="(kw, ki) in ws.keywordList" :key="'kw'+ki">
                                <span style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:3px 8px;font-size:11px;font-weight:700">
                                    <span x-text="kw"></span>
                                    <button type="button" @click="removeKeyword(ki)" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;padding:0;line-height:1">×</button>
                                </span>
                            </template>
                        </div>
                        <div style="display:flex;gap:6px">
                            <input type="text" x-model="ws.kwDraft" @keydown.enter.prevent="addKeyword()" placeholder="کلیدواژه + Enter" style="flex:1">
                            <button type="button" class="bk-btn" @click="addKeyword()">+</button>
                            <button type="button" class="bk-btn bk-btn-ai bk-btn-icon" @click="wsAi('keywords')" :disabled="ws.aiBusy">AI</button>
                        </div>
                    </div>
                    <div class="bk-field">
                        <label>Canonical (بر اساس پیوند یکتا)</label>
                        <div style="display:flex;gap:6px">
                            <input type="url" dir="ltr" x-model="ws.canonical" style="flex:1" placeholder="https://…">
                            <button type="button" class="bk-btn" @click="resetCanonical()" title="بازنشانی به پیوند یکتا">بازنشانی</button>
                        </div>
                        <div class="bk-hint" dir="ltr" x-text="ws.permalink || ''"></div>
                    </div>
                    <div class="bk-field">
                        <label style="display:flex;justify-content:space-between"><span>اجازه ایندکس</span><input type="checkbox" x-model="ws.robots_index"></label>
                        <label style="display:flex;justify-content:space-between;margin-top:6px"><span>دنبال کردن لینک</span><input type="checkbox" x-model="ws.robots_follow"></label>
                    </div>

                    <template x-for="(group, gkey) in ws.analysis.groups || {}" :key="gkey">
                        <div style="margin-top:12px">
                            <div style="font-size:11px;font-weight:800;color:#475569;margin-bottom:6px" x-text="group.label || gkey"></div>
                            <template x-for="(check, ci) in (group.items || [])" :key="gkey+ci">
                                <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:11px">
                                    <span :style="'color:' + (check.passed ? '#0f7b3a' : '#d97706')" x-text="check.passed ? '✓' : '!'"></span>
                                    <div>
                                        <strong x-text="check.label"></strong>
                                        <div style="color:#64748b" x-text="check.message"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <p x-show="ws.aiMsg" style="font-size:12px;color:#0078d4;margin:8px 0 0" x-text="ws.aiMsg"></p>
                </div>

                <!-- LINKS -->
                <div class="bk-seo-panel" x-show="ws.tab==='links'">
                    <div class="bk-link-stat">
                        <span style="background:#eef6fc;color:#0078d4" x-text="'داخلی: ' + (ws.linkData.counts.internal||0)"></span>
                        <span style="background:#fef3c7;color:#b45309" x-text="'خارجی: ' + (ws.linkData.counts.external||0)"></span>
                    </div>
                    <div class="bk-subtabs">
                        <button type="button" :class="{'is-on': ws.linksSubTab==='active'}" @click="ws.linksSubTab='active'">لینک‌های فعال</button>
                        <button type="button" :class="{'is-on': ws.linksSubTab==='builder'}" @click="ws.linksSubTab='builder'">لینک‌ساز</button>
                    </div>
                    <div x-show="ws.linksSubTab==='active'">
                        <div style="font-size:11px;font-weight:800;margin:4px 0 6px">داخلی</div>
                        <template x-for="(L,i) in ws.linkData.internal" :key="'li'+i">
                            <div class="bk-post-row">
                                <div style="flex:1;min-width:0">
                                    <strong x-text="L.text || L.anchor || 'لینک'"></strong>
                                    <div style="font-size:10px;color:#64748b;direction:ltr;overflow:hidden;text-overflow:ellipsis" x-text="L.href"></div>
                                </div>
                                <a :href="L.href" target="_blank" rel="noopener" class="bk-btn" style="padding:4px 8px;font-size:10px">مشاهده</a>
                                <button type="button" class="bk-btn" style="padding:4px 8px;font-size:10px;color:#c42b1c" @click="removeLinkFromContent(L)">حذف</button>
                            </div>
                        </template>
                        <p x-show="!(ws.linkData.internal||[]).length" style="font-size:11px;color:#94a3b8">لینک داخلی نیست</p>
                        <div style="font-size:11px;font-weight:800;margin:12px 0 6px">خارجی</div>
                        <template x-for="(L,i) in ws.linkData.external" :key="'le'+i">
                            <div class="bk-post-row">
                                <div style="flex:1;min-width:0">
                                    <strong x-text="L.text || L.anchor || 'لینک'"></strong>
                                    <div style="font-size:10px;color:#64748b;direction:ltr;overflow:hidden;text-overflow:ellipsis" x-text="L.href"></div>
                                </div>
                                <a :href="L.href" target="_blank" rel="noopener" class="bk-btn" style="padding:4px 8px;font-size:10px">مشاهده</a>
                                <button type="button" class="bk-btn" style="padding:4px 8px;font-size:10px;color:#c42b1c" @click="removeLinkFromContent(L)">حذف</button>
                            </div>
                        </template>
                        <p x-show="!(ws.linkData.external||[]).length" style="font-size:11px;color:#94a3b8">لینک خارجی نیست</p>
                        <button type="button" class="bk-btn" style="margin-top:8px;width:100%" @click="loadWsLinks()">تازه‌سازی</button>
                    </div>
                    <div x-show="ws.linksSubTab==='builder'">
                        <div style="font-size:12px;font-weight:800;margin-bottom:6px;color:#0078d4">لینک‌ساز داخلی</div>
                        <p style="font-size:11px;color:#64748b;line-height:1.5;margin:0 0 8px">۱) کلیدواژه → ۲) تایید مقالات → ۳) محل در متن → ۴) اعمال</p>
                        <div style="display:flex;gap:6px;margin-bottom:8px">
                            <input type="text" x-model="ws.linkAnchor" style="flex:1" placeholder="کلیدواژه / انکر…" @keydown.enter.prevent="runInternalSearch()">
                            <button type="button" class="bk-btn" @click="runInternalSearch()" :disabled="ws.linkBusy"
                                x-text="ws.linkBusy ? 'در حال جستجو…' : (ws.linkSearchLabel || 'جستجو')"></button>
                        </div>
                        <p x-show="ws.linkStatusMsg" style="font-size:11px;font-weight:700;color:#0078d4;margin:0 0 8px" x-text="ws.linkStatusMsg"></p>
                        <div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap">
                            <button type="button" class="bk-btn" @click="acceptAllInternalPosts()" x-show="(ws.postResults||[]).length">تایید همه</button>
                            <button type="button" class="bk-btn" @click="loadInternalContentMatches()" x-show="acceptedInternalPosts().length">یافتن در متن</button>
                            <button type="button" class="bk-btn bk-btn-primary" @click="confirmInternalLinking()" x-show="(ws.contentMatchesInternal||[]).length"
                                :disabled="ws.linkApplyBusy"
                                x-text="ws.linkApplyBusy ? 'در حال ثبت…' : 'ثبت لینک داخلی'"></button>
                            <button type="button" class="bk-btn" @click="applyInternalRandom()" x-show="acceptedInternalPosts().length">رندوم</button>
                        </div>
                        <template x-for="p in ws.postResults" :key="'pr'+p.id">
                            <div class="bk-post-row">
                                <label style="display:flex;gap:6px;flex:1;min-width:0;cursor:pointer">
                                    <input type="checkbox" :checked="p.accepted" @change="p.accepted=!!$event.target.checked;p.rejected=!p.accepted">
                                    <strong x-text="p.title"></strong>
                                </label>
                            </div>
                        </template>
                        <div x-show="(ws.contentMatchesInternal||[]).length" style="margin-top:10px">
                            <div style="font-size:11px;font-weight:800;margin-bottom:6px">تطابق در متن مقاله</div>
                            <template x-for="(m,mi) in ws.contentMatchesInternal" :key="'cm'+mi">
                                <div class="bk-match-row">
                                    <label style="display:flex;gap:6px;align-items:center"><input type="checkbox" x-model="m.selected"><span x-text="m.snippet||m.text"></span></label>
                                    <select x-model="m.targetId" style="width:100%;margin-top:6px;font-size:11px">
                                        <option value="">مقاله هدف…</option>
                                        <template x-for="p in acceptedInternalPosts()" :key="'t'+p.id+mi">
                                            <option :value="String(p.id)" x-text="p.title"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                        <div style="border-top:1px solid #e2e8f0;margin:14px 0 10px"></div>
                        <div style="font-size:12px;font-weight:800;margin-bottom:6px;color:#b45309">لینک خارجی</div>
                        <div class="bk-field"><label>URL</label><input type="url" dir="ltr" x-model="ws.extUrl" style="width:100%" placeholder="https://"></div>
                        <div class="bk-field"><label>عبارت در متن</label><input type="text" x-model="ws.extAnchor" style="width:100%"></div>
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px;font-size:12px">
                            <label><input type="radio" :checked="!ws.extNofollow" @change="ws.extNofollow=false"> follow</label>
                            <label><input type="radio" :checked="ws.extNofollow" @change="ws.extNofollow=true"> nofollow</label>
                            <button type="button" class="bk-btn" @click="loadExternalContentMatches()"
                                x-text="ws.extFindBusy ? 'در حال یافتن…' : 'یافتن در متن'" :disabled="ws.extFindBusy"></button>
                            <button type="button" class="bk-btn bk-btn-primary" @click="confirmExternalLinking()"
                                :disabled="ws.extApplyBusy || !ws.extUrl"
                                x-text="ws.extApplyBusy ? 'در حال ثبت…' : 'ثبت لینک خارجی'"></button>
                        </div>
                        <template x-for="(m,mi) in ws.contentMatchesExternal" :key="'em'+mi">
                            <div class="bk-match-row">
                                <label style="display:flex;gap:6px;align-items:center"><input type="checkbox" x-model="m.selected"><span x-text="m.snippet||m.text"></span></label>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bk-seo-panel" x-show="ws.tab==='cover'">
                    <div class="bk-field">
                        <label>تصویر شاخص (کاور مقاله)</label>
                        <div style="border:1px dashed #cbd5e1;border-radius:12px;padding:12px;text-align:center;background:#f8fafc">
                            <img class="bk-cover-prev" x-show="ws.cover" :src="ws.cover" alt="کاور" style="width:100%;max-height:180px;object-fit:cover;border-radius:10px;margin-bottom:10px">
                            <div x-show="!ws.cover" style="padding:28px 8px;color:#94a3b8;font-size:12px">کاور انتخاب نشده</div>
                            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                                <button type="button" class="bk-btn bk-btn-primary" @click="pickCover()">انتخاب از رسانه</button>
                                <button type="button" class="bk-btn" x-show="ws.cover" @click="ws.cover='';ws.cover_id=0">حذف کاور</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bk-seo-panel" x-show="ws.tab==='media'">
                    <div style="font-size:12px;font-weight:800;margin-bottom:8px">بهینه‌ساز تصاویر مقاله</div>
                    <div class="bk-grid-2" style="margin-bottom:8px">
                        <div class="bk-field" style="margin:0"><label>حداکثر عرض</label><input type="number" x-model.number="ws.optMaxW" style="width:100%"></div>
                        <div class="bk-field" style="margin:0"><label>کیفیت</label><input type="number" x-model.number="ws.optQuality" min="40" max="95" style="width:100%"></div>
                    </div>
                    <label style="display:flex;gap:6px;align-items:center;font-size:12px;margin-bottom:6px"><input type="checkbox" x-model="ws.optWebp"> تبدیل به WebP</label>
                    <div class="bk-field"><label>Alt پیش‌فرض / AI</label>
                        <div style="display:flex;gap:6px">
                            <input type="text" x-model="ws.optAlt" style="flex:1" :placeholder="ws.focus_keyword || 'alt'">
                            <button type="button" class="bk-btn bk-btn-ai" @click="aiAltForImages()" :disabled="ws.aiBusy||ws.optBusy">AI</button>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;margin-bottom:10px">
                        <button type="button" class="bk-btn" @click="scanWsImages()" :disabled="ws.optBusy">⟳</button>
                        <button type="button" class="bk-btn bk-btn-primary" style="flex:1" @click="optimizeWsImages()" :disabled="ws.optBusy">
                            <span x-text="ws.optBusy ? 'در حال بهینه‌سازی…' : 'Optimize تصاویر'"></span>
                        </button>
                        <button type="button" class="bk-btn" @click="insertImageFromMedia()">درج</button>
                        <button type="button" class="bk-btn" @click="insertGalleryFromMedia()">گالری</button>
                    </div>
                    <div x-show="ws.optBusy" class="bk-ai-progress">
                        <div class="bk-ai-progress-meta"><span>پردازش تصاویر</span><span x-text="(ws.optProgress||0)+'%'"></span></div>
                        <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" :style="'width:'+(ws.optProgress||0)+'%'"></div></div>
                    </div>
                    <template x-for="(img, ii) in ws.postImages" :key="'img'+ii">
                        <div class="bk-img-card">
                            <img :src="img.src" alt="" loading="lazy">
                            <div style="display:flex;justify-content:space-between;font-size:10px;margin-top:6px">
                                <span class="bk-size-before" x-text="(img.format_before||img.format||'—')+' · '+formatBytes(img.size_before||img.size||0)"></span>
                                <span class="bk-size-after" x-show="img.size_after||img.format_after" x-text="(img.format_after||'')+' · '+formatBytes(img.size_after||0)"></span>
                            </div>
                            <input type="text" :value="img.alt" @change="updateWsImgAlt(img,$event.target.value)" placeholder="ویرایش alt" style="width:100%;margin-top:6px;font-size:11px">
                            <div style="display:flex;gap:4px;margin-top:6px;flex-wrap:wrap">
                                <button type="button" class="bk-btn" style="padding:4px 8px;font-size:10px" @click="setAsCover(img)">کاور</button>
                                <button type="button" class="bk-btn" style="padding:4px 8px;font-size:10px" @click="replaceImageInContent(img)">جایگزین</button>
                                <button type="button" class="bk-btn" style="padding:4px 8px;font-size:10px;color:#c42b1c" @click="removeImageFromContent(img)">حذف</button>
                            </div>
                        </div>
                    </template>
                    <p x-show="!ws.postImages.length" style="font-size:12px;color:#94a3b8">تصویری نیست — اسکن یا درج کنید.</p>
                </div>

<div class="bk-seo-panel" x-show="ws.tab==='schema'">
                    <div class="bk-field">
                        <label>نوع اسکیما</label>
                        <select x-model="ws.schemaType" @change="rebuildWsSchema()" style="width:100%">
                            <option value="Article">Article</option>
                            <option value="BlogPosting">BlogPosting</option>
                            <option value="FAQPage">FAQPage</option>
                            <option value="HowTo">HowTo</option>
                            <option value="WebPage">WebPage</option>
                        </select>
                    </div>
                    <div class="bk-field" x-show="ws.schemaType==='FAQPage'">
                        <label>سوالات FAQ</label>
                        <template x-for="(item, fi) in ws.faqItems" :key="'f'+fi">
                            <div style="margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #f1f5f9">
                                <input type="text" x-model="item.q" placeholder="سوال" style="width:100%;margin-bottom:4px" @change="rebuildWsSchema()">
                                <textarea x-model="item.a" rows="2" placeholder="پاسخ" style="width:100%" @change="rebuildWsSchema()"></textarea>
                                <button type="button" class="bk-btn" style="margin-top:4px" @click="ws.faqItems.splice(fi,1);rebuildWsSchema()">حذف</button>
                            </div>
                        </template>
                        <button type="button" class="bk-btn" @click="ws.faqItems.push({q:'',a:''})">+ سوال</button>
                    </div>
                    <div class="bk-field">
                        <label>JSON-LD</label>
                        <textarea x-model="ws.schema" rows="10" dir="ltr" style="width:100%;font-family:monospace;font-size:11px"></textarea>
                    </div>
                </div>

                <!-- SOCIAL -->
                <div class="bk-seo-panel" x-show="ws.tab==='social'">
                    <div class="bk-field"><label>عنوان OG</label><input type="text" x-model="ws.og_title" style="width:100%"></div>
                    <div class="bk-field"><label>توضیحات OG</label><textarea x-model="ws.og_description" rows="2" style="width:100%"></textarea></div>
                    <div class="bk-field">
                        <label>تصویر OG</label>
                        <div style="display:flex;gap:6px">
                            <input type="url" dir="ltr" x-model="ws.og_image" style="flex:1">
                            <button type="button" class="bk-btn" @click="pickMediaField('og_image')">رسانه</button>
                        </div>
                    </div>
                    <div class="bk-field"><label>عنوان X</label><input type="text" x-model="ws.x_title" style="width:100%"></div>
                    <div class="bk-field"><label>توضیحات X</label><textarea x-model="ws.x_description" rows="2" style="width:100%"></textarea></div>
                    <div class="bk-field">
                        <label>تصویر X</label>
                        <div style="display:flex;gap:6px">
                            <input type="url" dir="ltr" x-model="ws.x_image" style="flex:1">
                            <button type="button" class="bk-btn" @click="pickMediaField('x_image')">رسانه</button>
                        </div>
                    </div>
                </div>

                <!-- AI -->
                <div class="bk-seo-panel" x-show="ws.tab==='ai'">
                    <div style="font-size:12px;font-weight:800;margin-bottom:8px;color:#6b21a8">بازنویسی مقاله با AI</div>
                    <div class="bk-ai-progress" x-show="ws.aiBusy" x-cloak>
                        <div class="bk-ai-progress-meta">
                            <span x-text="ws.aiProgressLabel || 'در حال پردازش AI…'"></span>
                            <span x-text="(ws.aiProgress||0) + '%'"></span>
                        </div>
                        <div class="bk-ai-progress-track">
                            <div class="bk-ai-progress-fill" :style="'width:' + (ws.aiProgress||0) + '%'"></div>
                        </div>
                    </div>
                    <div class="bk-field">
                        <label>سبک بازنویسی</label>
                        <select x-model="ws.rewriteStyle" style="width:100%">
                            <option value="seo">سئو محور (ساختار و کلیدواژه)</option>
                            <option value="engaging">جذاب و روان</option>
                            <option value="formal">رسمی</option>
                            <option value="simple">ساده و همه‌فهم</option>
                            <option value="expand">گسترش محتوا</option>
                            <option value="shorten">کوتاه‌سازی</option>
                        </select>
                    </div>
                    <button type="button" class="bk-btn bk-btn-ai" style="width:100%;margin-bottom:8px" @click="rewriteArticle()" :disabled="ws.aiBusy">
                        <span x-text="ws.aiBusy && ws.aiTask==='rewrite' ? 'در حال بازنویسی…' : 'بازنویسی کامل مقاله'"></span>
                    </button>
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 12px;line-height:1.5">متن فعلی ادیتور برای AI ارسال می‌شود و در صورت تأیید، جایگزین محتوا می‌گردد.</p>
                    <div style="border-top:1px solid #e2e8f0;padding-top:12px;margin-bottom:8px;font-size:12px;font-weight:800;color:#475569">متا و کلیدواژه</div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        <button type="button" class="bk-btn bk-btn-ai" @click="wsAi('meta_title')" :disabled="ws.aiBusy">تولید عنوان سئو</button>
                        <button type="button" class="bk-btn bk-btn-ai" @click="wsAi('meta_description')" :disabled="ws.aiBusy">تولید متا دیسکریپشن</button>
                        <button type="button" class="bk-btn bk-btn-ai" @click="wsAi('focus_keyword')" :disabled="ws.aiBusy">پیشنهاد کلیدواژه اصلی</button>
                        <button type="button" class="bk-btn bk-btn-ai" @click="wsAi('keywords')" :disabled="ws.aiBusy">کلیدواژه‌های فرعی</button>
                        <button type="button" class="bk-btn" @click="wsAi('outline')" :disabled="ws.aiBusy">پیشنهاد سرفصل‌ها</button>
                    </div>
                    <p x-show="ws.aiMsg" style="font-size:12px;color:#0078d4;margin-top:10px" x-text="ws.aiMsg"></p>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bankaiArticlesHub', () => ({
        panel: 'list',
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
                        setTimeout(() => this.openWorkspace({ id: pid, title: this.gen.topic }), 300);
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
        async openWorkspace(row, tab) {
            const id = row.id || row.post_id;
            this.ws.open = true;
            this.editorLoading = true;
            this.ws.tab = tab || 'seo';
            this.ws.id = id;
            this.ws.title = row.title || '';
            this.ws.content = '';
            this.ws.aiMsg = '';
            this.ws.linkResults = [];
            this.htmlMode = false;
            this.$nextTick(() => this.initRichEditor());
            try {
                const r = await fetch(this.restBase() + 'seo/' + id, {
                    credentials: 'same-origin', headers: this.restHeaders(false)
                });
                const j = await r.json();
                const d = (j && j.data) ? j.data : j;
                this.ws.title = d.title || this.ws.title;
                this.ws.content = d.content || '';
                this.ws.seo_title = d.seo_title || d.title || '';
                this.ws.description = d.description || '';
                this.ws.focus_keyword = d.focus_keyword || '';
                this.ws.keywords_str = Array.isArray(d.keywords) ? d.keywords.join('، ') : (d.keywords || '');
                this.ws.keywordList = Array.isArray(d.keywords) ? d.keywords.slice() : String(this.ws.keywords_str||'').split(/[,،]+/).map(s=>s.trim()).filter(Boolean);
                if (d.robots) {
                    this.ws.robots_index = d.robots.index !== false;
                    this.ws.robots_follow = d.robots.follow !== false;
                }
                this.ws.schema = d.schema || '';
                this.ws.x_image = d.x_image || '';
                this.ws.permalink = d.permalink || '';
                // Always prefer real post permalink as canonical baseline
                const perm = d.permalink || '';
                const can = (d.canonical || '').trim();
                this.ws.canonical = (can && can.indexOf('http') === 0) ? can : perm;
                this.ws.score = d.score || row.score || 0;
                this.ws.og_title = d.og_title || '';
                this.ws.og_description = d.og_description || '';
                this.ws.og_image = d.og_image || '';
                this.ws.x_title = d.x_title || '';
                this.ws.x_description = d.x_description || '';
                this.ws.status = d.status || 'draft';
                this.$nextTick(() => { this.setEditorContent(this.ws.content || ''); this.initRichEditor(); });
                this.refreshScore();
                this.loadWsLinks();

                try {
                    const pr = await fetch('/wp-json/wp/v2/posts/' + id + '?context=edit', {
                        credentials: 'same-origin', headers: this.restHeaders(false)
                    });
                    if (pr.ok) {
                        const post = await pr.json();
                        if (post.title && post.title.raw) this.ws.title = post.title.raw;
                        if (post.content && post.content.raw) {
                            this.ws.content = post.content.raw;
                            this.$nextTick(() => this.setEditorContent(this.ws.content || ''));
                        }
                        if (post.status) this.ws.status = post.status;
                        if (post.date) {
                            try {
                                const dt = new Date(post.date);
                                if (!isNaN(dt.getTime())) this.setWsScheduleFromDate(dt);
                            } catch (e) {}
                        }
                        if (post.link) {
                            this.ws.permalink = post.link;
                            if (!this.ws.canonical || this.ws.canonical.indexOf('http') !== 0) {
                                this.ws.canonical = post.link;
                            }
                        }
                        if (post.featured_media) {
                            this.ws.cover_id = post.featured_media;
                            const mr = await fetch('/wp-json/wp/v2/media/' + post.featured_media, {
                                credentials: 'same-origin', headers: this.restHeaders(false)
                            });
                            if (mr.ok) {
                                const media = await mr.json();
                                this.ws.cover = media.source_url || '';
                            }
                        }
                    }
                } catch (e2) {}
            } catch (e) {
                console.error(e);
            } finally {
                this.$nextTick(() => {
                    this.setEditorContent(this.ws.content || '');
                    this.initRichEditor();
                    // brief blur so paint completes
                    setTimeout(() => { this.editorLoading = false; }, 180);
                });
            }
        },
        closeWorkspace() {
            this.onEditorInput();
            this.editorLoading = false;
            this.ws.open = false;
            this.loadArticles();
        },
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
