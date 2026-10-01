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
?>
<div id="tab-articles" class="bankai-tab-pane">

<style>
#tab-articles{font-family:"Segoe UI",system-ui,"Vazirmatn",sans-serif;color:#1a1a1a}
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
#tab-articles select,#tab-articles input[type=text],#tab-articles input[type=url],#tab-articles textarea{
  border:1px solid rgba(0,0,0,.12);border-radius:10px;padding:9px 12px;font-size:13px;background:#fff;box-sizing:border-box}
#tab-articles .bk-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid rgba(0,0,0,.1);border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer;background:#fff;color:#1a1a1a;text-decoration:none;line-height:1}
#tab-articles .bk-btn-primary{background:#0078d4;border-color:#0078d4;color:#fff}
#tab-articles .bk-btn-ai{background:#f3e8ff;border-color:#d8b4fe;color:#6b21a8}
#tab-articles table{width:100%;border-collapse:collapse;font-size:13px}
#tab-articles th{text-align:right;padding:10px;background:#f8fafc;font-size:11px;color:#64748b;border-bottom:1px solid #e2e8f0}
#tab-articles td{padding:12px 10px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
#tab-articles .score-pill{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:800}
#tab-articles .bk-actions{display:flex;gap:6px;align-items:center;justify-content:flex-end}
#tab-articles .bk-field{margin-bottom:12px}
#tab-articles .bk-field label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:4px}
#tab-articles .bk-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:800px){#tab-articles .bk-grid-2{grid-template-columns:1fr}}
</style>

    <!-- LIST / GENERATE / SCHEDULE -->
    <div id="articles-hub-main-view">
        <div class="bk-art-head">
            <div>
                <h2>
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    مدیریت مقالات
                </h2>
                <p>ویرایش درون‌پنلی، تولید AI و سئوی کامل بدون خروج از بنکای</p>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" id="btn-switch-to-ai-gen">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
                تولید مقاله با AI
            </button>
        </div>

        <div class="bk-art-card">
            <div class="bk-art-toolbar">
                <div class="bk-art-search">
                    <input type="search" id="art-search-input" placeholder="جستجوی عنوان…">
                    <button type="button" id="btn-search-art" style="border:none;background:transparent;cursor:pointer;color:#0078d4;padding:8px">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </div>
                <button type="button" class="bk-btn" id="btn-refresh-articles">تازه‌سازی</button>
            </div>
            <div style="overflow:auto">
                <table id="tbl-articles-list">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>سئو</th>
                            <th>وضعیت</th>
                            <th style="text-align:left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-articles-tbody">
                        <tr><td colspan="4" style="text-align:center;padding:24px;color:#8a8886">در حال دریافت مقالات…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bk-art-card" id="card-ai-generate" style="display:none;">
            <h3 style="margin:0 0 12px;font-size:16px;font-weight:800">تولید مقاله با هوش مصنوعی AI</h3>
            <div class="bk-field">
                <label>موضوع / عنوان مقاله</label>
                <input type="text" id="ai-gen-topic" placeholder="موضوع مقاله…" style="width:100%">
            </div>
            <div class="bk-grid-2">
                <div class="bk-field">
                    <label>کلمه کلیدی اصلی</label>
                    <input type="text" id="ai-gen-focus" style="width:100%">
                </div>
                <div class="bk-field">
                    <label>طول متن (کلمه)</label>
                    <select id="ai-gen-length" style="width:100%">
                        <option value="1500">۱۵۰۰ کلمه</option>
                        <option value="3000" selected>۳۰۰۰ کلمه</option>
                        <option value="5000">۵۰۰۰ کلمه</option>
                    </select>
                </div>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" id="btn-start-ai-gen">شروع تولید مقاله با AI</button>
        </div>
    </div>
</div>

<script>
(function() {
    function loadArticles() {
        var tbody = document.getElementById('tbl-articles-tbody');
        if (!tbody) return;
        var searchQ = document.getElementById('art-search-input') ? document.getElementById('art-search-input').value : '';
        var url = (window.bankaiCoreData && window.bankaiCoreData.restUrl ? window.bankaiCoreData.restUrl : '/wp-json/bankai/v1/').replace(/\/$/, '') + '/seo/articles?per_page=20&search=' + encodeURIComponent(searchQ);

        fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-WP-Nonce': (window.bankaiCoreData && window.bankaiCoreData.nonce) || '' }
        }).then(function(r) { return r.json(); }).then(function(res) {
            var items = (res && res.data && res.data.items) ? res.data.items : (res && res.items ? res.items : []);
            if (!items.length) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:24px;color:#8a8886">مقاله‌ای یافت نشد.</td></tr>';
                return;
            }
            var html = '';
            items.forEach(function(row) {
                var score = row.score || 0;
                var bg = score >= 80 ? '#e8f5e9' : (score >= 50 ? '#fff7ed' : '#fef2f2');
                var color = score >= 80 ? '#0f7b3a' : (score >= 50 ? '#d97706' : '#c42b1c');
                html += '<tr>';
                html += '<td><strong>' + (row.title || 'بدون عنوان') + '</strong><div style="font-size:11px;color:#8a8886">' + (row.date || '') + '</div></td>';
                html += '<td><span class="score-pill" style="background:' + bg + ';color:' + color + '">' + score + '%</span></td>';
                html += '<td>' + (row.status || 'draft') + '</td>';
                html += '<td><div class="bk-actions"><a href="/wp-admin/post.php?post=' + row.id + '&action=edit" target="_blank" class="bk-btn bk-btn-primary">ویرایش</a></div></td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
        }).catch(function() {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:24px;color:#c42b1c">خطا در بارگذاری لیست مقالات</td></tr>';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadArticles();

        var btnSearch = document.getElementById('btn-search-art');
        if (btnSearch) btnSearch.addEventListener('click', loadArticles);

        var btnRefresh = document.getElementById('btn-refresh-articles');
        if (btnRefresh) btnRefresh.addEventListener('click', loadArticles);

        var btnAiGen = document.getElementById('btn-switch-to-ai-gen');
        var cardAi = document.getElementById('card-ai-generate');
        if (btnAiGen && cardAi) {
            btnAiGen.addEventListener('click', function() {
                cardAi.style.display = cardAi.style.display === 'none' ? 'block' : 'none';
            });
        }

        var btnStartAi = document.getElementById('btn-start-ai-gen');
        if (btnStartAi) {
            btnStartAi.addEventListener('click', function() {
                var topic = document.getElementById('ai-gen-topic') ? document.getElementById('ai-gen-topic').value : '';
                if (!topic) {
                    if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('لطفاً موضوع را وارد کنید', 'error');
                    return;
                }
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('در حال شروع تولید مقاله با AI...', 'info');
                var body = new FormData();
                body.append('action', 'bankai_ai_generate_article');
                body.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || '');
                body.append('topic', topic);

                fetch(window.bankaiCoreData && window.bankaiCoreData.ajaxUrl ? window.bankaiCoreData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                    method: 'POST', body: body, credentials: 'same-origin'
                }).then(function(r) { return r.json(); }).then(function(res) {
                    if (res && res.success) {
                        if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('مقاله تولید شد', 'success');
                        loadArticles();
                    } else {
                        if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('خطا در تولید مقاله با AI', 'error');
                    }
                });
            });
        }
    });
})();
</script>
