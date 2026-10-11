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
        var endpoint = c.ajaxUrl || window.ajaxurl;
        if (!endpoint) {
            return Promise.reject(new Error('نشانی درخواست AJAX تنظیم نشده است.'));
        }
        if (!(c.adminNonce || c.nonce)) {
            return Promise.reject(new Error('توکن امنیتی درخواست در دسترس نیست؛ صفحه را تازه‌سازی کنید.'));
        }

        return fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: body
        }).then(function (response) {
            return response.text().then(function (raw) {
                var payload;
                try {
                    payload = JSON.parse(raw);
                } catch (error) {
                    throw new Error('پاسخ سرور معتبر نیست (HTTP ' + response.status + ').');
                }
                if (!response.ok) {
                    var message = payload && payload.data && payload.data.message;
                    throw new Error(message || ('خطای سرور (HTTP ' + response.status + ').'));
                }
                return payload;
            });
        });
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
        var el;
        try {
            el = document.querySelector(selector);
        } catch (error) {
            return;
        }
        if (!el || !('value' in el)) return;
        el.value = String(value);
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
        var host = document.querySelector('.bankai-toast, #bankai-toast');
        if (host) {
            host.textContent = String(msg || '');
            host.setAttribute('role', type === 'error' ? 'alert' : 'status');
            host.hidden = false;
            return;
        }
        console[type === 'error' ? 'error' : 'info']('[Bankai AI]', msg);
    }

    function runTask(task, btn) {
        var c = cfg();
        var title = getEditorTitle();
        var content = getEditorContent();
        var kwEl = document.querySelector('[data-bankai-field="focus_keyword"], #bankai_focus_keyword, input[name="_bankai_seo_focus_keyword"]');
        var kw = kwEl ? kwEl.value : '';

        if (btn && btn.disabled) return;
        showLoader(btn, true);
        ajax('bankai_ai_seo_task', {
            task: task,
            title: title,
            content: content,
            focus_keyword: kw,
            locale: c.locale || document.documentElement.lang || 'fa_IR',
            provider: c.defaultAiProvider || ''
        }).then(function (res) {
            if (!res || typeof res !== 'object') {
                throw new Error('ساختار پاسخ هوش مصنوعی معتبر نیست.');
            }
            var d = res.data || res;
            if (!res.success) {
                toast((d && d.message) || 'AI error', 'error');
                return;
            }
            var text = d.text || d.seo_title || d.description || d.focus_keyword || '';
            if (task === 'meta_title' || task === 'title') {
                var titleVal = d.seo_title || d.title || text;
                setField('[data-bankai-field="seo_title"], #bankai_seo_title, input[name="_bankai_seo_title"], input[name="bankai_seo_title"]', titleVal);
                // Alpine reactive models in sidebar
                document.dispatchEvent(new CustomEvent('bankai-ai-result', { detail: { task: 'meta_title', data: { seo_title: titleVal } } }));
            } else if (task === 'meta_description' || task === 'description') {
                var descVal = d.description || d.meta_description || text;
                setField('[data-bankai-field="description"], #bankai_seo_description, textarea[name="_bankai_seo_description"], textarea[name="bankai_seo_description"]', descVal);
                document.dispatchEvent(new CustomEvent('bankai-ai-result', { detail: { task: 'meta_description', data: { description: descVal } } }));
            } else if (task === 'focus_keyword') {
                setField('[data-bankai-field="focus_keyword"], #bankai_focus_keyword, input[name="_bankai_seo_focus_keyword"]', d.focus_keyword || text);
            } else if (task === 'keywords') {
                var kws = Array.isArray(d.keywords) ? d.keywords.slice() : [];
                if ((!kws || !kws.length) && text) {
                    kws = String(text).split(/[,،]+/).map(function (s) { return s.trim(); }).filter(Boolean);
                }
                // Merge site-wide fixed keywords from config if present
                var fixed = (c.fixedKeywords || c.seoFixedKeywords || []);
                if (typeof fixed === 'string') {
                    fixed = fixed.split(/[,،]+/).map(function (s) { return s.trim(); }).filter(Boolean);
                }
                if (Array.isArray(fixed) && fixed.length) {
                    var seen = new Set();
                    kws = kws.map(function (k) { return String(k).trim(); }).filter(Boolean);
                    kws.forEach(function (k) { seen.add(k.toLocaleLowerCase()); });
                    fixed.forEach(function (f) {
                        f = String(f || '').trim();
                        var key = f.toLocaleLowerCase();
                        if (f && !seen.has(key)) {
                            kws.push(f);
                            seen.add(key);
                        }
                    });
                }
                var box = document.querySelector('[data-bankai-field="keywords"], #bankai_seo_keywords, input[name="_bankai_seo_keywords"], textarea[name="_bankai_seo_keywords"]');
                if (box) {
                    box.value = kws.join(c.isRtl ? '، ' : ', ');
                    box.dispatchEvent(new Event('input', { bubbles: true }));
                    box.dispatchEvent(new Event('change', { bubbles: true }));
                }
                document.dispatchEvent(new CustomEvent('bankai-ai-keywords', { detail: kws }));
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
        var target = e.target;
        if (!target || typeof target.closest !== 'function') return;
        var btn = target.closest('[data-bankai-ai-task]');
        if (!btn) return;
        e.preventDefault();
        var task = btn.getAttribute('data-bankai-ai-task');
        if (task) runTask(task, btn);
    });

    // Expose for Alpine sidebar
    window.bankaiRunSeoAi = runTask;
})();
