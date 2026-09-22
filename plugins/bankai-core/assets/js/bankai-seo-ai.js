/**
 * Bankai SEO × AI bridge for post editor (Gutenberg / classic meta).
 * Listens for [data-bankai-ai-task] clicks and fills target fields.
 */
(function () {
    'use strict';

    function cfg() {
        return window.bankaiEditorSeo || window.bankaiData || window.bankaiCoreData || {};
    }

    function ajax(action, data) {
        var c = cfg();
        var body = new FormData();
        body.append('action', action);
        body.append('nonce', c.adminNonce || c.nonce || '');
        Object.keys(data || {}).forEach(function (k) {
            body.append(k, data[k] == null ? '' : data[k]);
        });
        return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        }).then(function (r) { return r.json(); });
    }

    function getEditorContent() {
        try {
            if (window.wp && wp.data && wp.data.select) {
                var blocks = wp.data.select('core/editor');
                if (blocks && blocks.getEditedPostContent) {
                    return blocks.getEditedPostContent() || '';
                }
            }
        } catch (e) {}
        var ta = document.getElementById('content');
        return ta ? ta.value : '';
    }

    function getEditorTitle() {
        try {
            if (window.wp && wp.data && wp.data.select) {
                var b = wp.data.select('core/editor');
                if (b && b.getEditedPostAttribute) {
                    return b.getEditedPostAttribute('title') || '';
                }
            }
        } catch (e) {}
        var t = document.getElementById('title');
        return t ? t.value : '';
    }

    function setField(selector, value) {
        if (!selector || value == null) return;
        var el = document.querySelector(selector);
        if (!el) return;
        el.value = value;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
        // Alpine / React friendly
        if (window.Alpine && el._x_model) {
            try { el._x_model.set(value); } catch (e) {}
        }
    }

    function showLoader(btn, on) {
        if (!btn) return;
        if (on) {
            btn.dataset._label = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="bankai-ai-btn-spin"></span> AI…';
        } else {
            btn.disabled = false;
            if (btn.dataset._label) btn.innerHTML = btn.dataset._label;
        }
    }

    function toast(msg, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type || 'success');
            return;
        }
        // Prefer existing white toast if present
        var host = document.querySelector('.bankai-toast, #bankai-toast, .notice');
        console.log('[Bankai AI]', type, msg);
    }

    function runTask(task, btn) {
        var c = cfg();
        var title = getEditorTitle();
        var content = getEditorContent();
        var kwEl = document.querySelector('[data-bankai-field="focus_keyword"], #bankai_focus_keyword, input[name="_bankai_seo_focus_keyword"]');
        var kw = kwEl ? kwEl.value : '';

        showLoader(btn, true);
        ajax('bankai_ai_seo_task', {
            task: task,
            title: title,
            content: content,
            focus_keyword: kw,
            locale: c.locale || document.documentElement.lang || 'fa_IR',
            provider: c.defaultAiProvider || ''
        }).then(function (res) {
            var d = res.data || res;
            if (!res.success) {
                toast((d && d.message) || 'AI error', 'error');
                return;
            }
            var text = d.text || d.seo_title || d.description || d.focus_keyword || '';
            if (task === 'meta_title') {
                setField('[data-bankai-field="seo_title"], #bankai_seo_title, input[name="_bankai_seo_title"]', d.seo_title || text);
            } else if (task === 'meta_description') {
                setField('[data-bankai-field="description"], #bankai_seo_description, textarea[name="_bankai_seo_description"]', d.description || text);
            } else if (task === 'focus_keyword') {
                setField('[data-bankai-field="focus_keyword"], #bankai_focus_keyword, input[name="_bankai_seo_focus_keyword"]', d.focus_keyword || text);
            } else if (task === 'keywords' && d.keywords) {
                var box = document.querySelector('[data-bankai-field="keywords"]');
                if (box) {
                    box.value = (d.keywords || []).join(', ');
                    box.dispatchEvent(new Event('input', { bubbles: true }));
                }
                // chip UI
                document.dispatchEvent(new CustomEvent('bankai-ai-keywords', { detail: d.keywords }));
            } else if (task === 'rewrite' || task === 'outline' || task === 'alt_text') {
                var out = document.querySelector('[data-bankai-ai-output="' + task + '"]');
                if (out) out.textContent = text;
            }
            toast(c.isRtl ? 'پاسخ هوش مصنوعی اعمال شد' : 'AI result applied', 'success');
            document.dispatchEvent(new CustomEvent('bankai-ai-result', { detail: { task: task, data: d } }));
        }).catch(function (e) {
            toast((e && e.message) || 'AI error', 'error');
        }).finally(function () {
            showLoader(btn, false);
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-bankai-ai-task]');
        if (!btn) return;
        e.preventDefault();
        var task = btn.getAttribute('data-bankai-ai-task');
        if (task) runTask(task, btn);
    });

    // Expose for Alpine sidebar
    window.bankaiRunSeoAi = runTask;
})();
