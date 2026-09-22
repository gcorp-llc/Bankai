/**
 * Bankai Admin Hooks
 * - Core module toggles → REST /core-module/{key} (AJAX fallback)
 * - Sub-module toggles → REST /module/{id}
 * - Bulk settings save → REST /settings
 * - AI keys save
 */
(function () {
    'use strict';

    function cfg() {
        return window.bankaiCoreData || window.bankaiData || {};
    }

    function toast(msg, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type || 'success');
            return;
        }
        // Minimal fallback
        var el = document.getElementById('bankai-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'bankai-toast';
            el.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:99999;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,.15);transition:opacity .3s;';
            document.body.appendChild(el);
        }
        el.style.background = '#FFFFFF';
        el.style.color = type === 'error' ? '#A6122D' : '#1F2328';
        el.style.border = '1px solid ' + (type === 'error' ? '#A6122D' : '#D0D7DE');
        el.textContent = msg;
        el.style.opacity = '1';
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.style.opacity = '0'; }, 2800);
    }

    function rest(path, options) {
        var c = cfg();
        var base = (c.restUrl || '').replace(/\/$/, '');
        return fetch(base + path, Object.assign({
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': c.nonce || ''
            }
        }, options || {})).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) {
                    data = data || {};
                    data.success = false;
                    data.message = data.message || ('HTTP ' + r.status);
                }
                return data;
            });
        });
    }

    function ajax(action, data) {
        var c = cfg();
        var body = new FormData();
        body.append('action', action);
        body.append('nonce', c.adminNonce || '');
        Object.keys(data || {}).forEach(function (k) {
            var v = data[k];
            if (typeof v === 'object') {
                body.append(k, JSON.stringify(v));
            } else {
                body.append(k, v);
            }
        });
        return fetch(c.ajaxUrl || window.ajaxurl || '', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        }).then(function (r) { return r.json(); });
    }

    function updateCoreCount(n) {
        var chip = document.getElementById('bk-core-mod-count');
        if (!chip || typeof n !== 'number') return;
        chip.innerHTML = n + ' / 6 <span>فعال</span>';
    }

    // Core module toggles (overview switches)
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input || !input.classList || !input.classList.contains('bk-core-module-toggle')) {
            return;
        }
        var key = input.getAttribute('data-module') || input.getAttribute('data-key');
        if (!key) return;
        var enabled = !!input.checked;
        input.disabled = true;

        rest('/core-module/' + encodeURIComponent(key), {
            method: 'POST',
            body: JSON.stringify({ enabled: enabled })
        }).then(function (data) {
            if (data && data.success) {
                toast(data.message || 'ذخیره شد', 'success');
                if (typeof data.active_count === 'number') {
                    updateCoreCount(data.active_count);
                }
            } else {
                // AJAX fallback
                return ajax('bankai_toggle_core_module', {
                    key: key,
                    enabled: enabled ? '1' : '0'
                }).then(function (res) {
                    var d = res.data || res;
                    if (res.success || d.success) {
                        toast(d.message || 'ذخیره شد', 'success');
                        if (typeof d.active_count === 'number') updateCoreCount(d.active_count);
                    } else {
                        input.checked = !enabled;
                        toast((d && d.message) || 'خطا در ذخیره', 'error');
                    }
                });
            }
        }).catch(function () {
            input.checked = !enabled;
            toast('خطا در ارتباط با سرور', 'error');
        }).finally(function () {
            input.disabled = false;
        });
    });

    // Sub-module toggles (seo/speed/media/ai tabs)
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input || !input.classList) return;
        if (!input.classList.contains('bk-sub-module-toggle') && !input.classList.contains('bankai-module-switch')) {
            return;
        }
        var id = input.getAttribute('data-module') || input.getAttribute('data-id');
        if (!id) return;
        var enabled = !!input.checked;
        input.disabled = true;

        rest('/module/' + encodeURIComponent(id), {
            method: 'POST',
            body: JSON.stringify({ enabled: enabled })
        }).then(function (data) {
            if (data && data.success) {
                toast(data.message || 'ذخیره شد', 'success');
            } else {
                input.checked = !enabled;
                toast((data && data.message) || 'خطا', 'error');
            }
        }).catch(function () {
            input.checked = !enabled;
            toast('خطا در ارتباط', 'error');
        }).finally(function () {
            input.disabled = false;
        });
    });

    // Generic settings save button [data-bankai-save-settings]
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-bankai-save-settings]');
        if (!btn) return;
        e.preventDefault();

        var form = btn.closest('form') || btn.closest('[data-bankai-settings-form]') || document;
        var payload = {};
        form.querySelectorAll('[data-bankai-setting]').forEach(function (el) {
            var key = el.getAttribute('data-bankai-setting');
            if (!key) return;
            if (el.type === 'checkbox') {
                payload[key] = !!el.checked;
            } else {
                payload[key] = el.value;
            }
        });

        btn.disabled = true;
        rest('/settings', {
            method: 'POST',
            body: JSON.stringify({ settings: payload })
        }).then(function (data) {
            if (data && data.success) {
                toast(data.message || 'تنظیمات ذخیره شد', 'success');
            } else {
                toast((data && data.message) || 'خطا در ذخیره', 'error');
            }
        }).catch(function () {
            toast('خطا در ارتباط با سرور', 'error');
        }).finally(function () {
            btn.disabled = false;
        });
    });

    // Save AI keys button
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('#bankai-save-ai-keys, [data-bankai-save-ai-keys]');
        if (!btn) return;
        e.preventDefault();

        var keys = {};
        ['openai_api_key', 'anthropic_api_key', 'gemini_api_key', 'deepseek_api_key', 'openrouter_api_key'].forEach(function (k) {
            var el = document.querySelector('[name="' + k + '"], [data-bankai-setting="' + k + '"], #' + k);
            if (el && el.value && el.value.indexOf('****') === -1) {
                keys[k] = el.value.trim();
            }
        });
        var provider = document.querySelector('[name="active_ai_provider"], [data-bankai-setting="active_ai_provider"]');
        if (provider) {
            keys.active_ai_provider = provider.value;
        }

        btn.disabled = true;
        rest('/settings', {
            method: 'POST',
            body: JSON.stringify({ settings: keys })
        }).then(function (data) {
            toast((data && data.message) || (data && data.success ? 'ذخیره شد' : 'خطا'), data && data.success ? 'success' : 'error');
        }).catch(function () {
            toast('خطا در ذخیره کلیدها', 'error');
        }).finally(function () {
            btn.disabled = false;
        });
    });
})();
