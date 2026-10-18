/**
 * Bankai Global Toast Engine (vanilla, stackable, modern)
 * API: window.bankaiShowToast(message, type?, durationMs?)
 *      type: success | error | warning | warn | info
 */
(function () {
  'use strict';

  var MAX_TOASTS = 4;
  var DEFAULT_MS = 3600;

  function ensureContainer() {
    var el = document.getElementById('bankai-toast-container');
    if (!el) {
      el = document.createElement('div');
      el.id = 'bankai-toast-container';
      el.className = 'bankai-toast-container';
      el.setAttribute('aria-live', 'polite');
      el.setAttribute('aria-atomic', 'false');
      document.body.appendChild(el);
    }
    return el;
  }

  function iconSvg(type) {
    if (type === 'error') {
      return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>';
    }
    if (type === 'warning' || type === 'warn') {
      return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>';
    }
    if (type === 'info') {
      return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>';
    }
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M20 6L9 17l-5-5"/></svg>';
  }

  function labelFa(type, rtl) {
    if (type === 'error') return rtl ? 'خطا' : 'Error';
    if (type === 'warning' || type === 'warn') return rtl ? 'هشدار' : 'Warning';
    if (type === 'info') return rtl ? 'اطلاع' : 'Info';
    return rtl ? 'موفق' : 'Success';
  }

  function normalizeType(type) {
    type = (type || 'success').toLowerCase();
    if (type === 'warn') type = 'warning';
    if (['success', 'error', 'warning', 'info'].indexOf(type) === -1) type = 'success';
    return type;
  }

  function isRtl() {
    var app = document.getElementById('bankai-admin-app');
    if (app && (app.getAttribute('dir') === 'rtl' || app.classList.contains('rtl'))) return true;
    return document.documentElement.getAttribute('dir') === 'rtl' || document.body.classList.contains('rtl');
  }

  function dismiss(card) {
    if (!card || card._leaving) return;
    card._leaving = true;
    card.classList.add('is-leaving');
    clearTimeout(card._timer);
    setTimeout(function () {
      if (card.parentNode) card.parentNode.removeChild(card);
    }, 280);
  }

  function bankaiShowToast(message, type, duration) {
    message = (message == null ? '' : String(message)).trim();
    if (!message) return null;

    type = normalizeType(type);
    var ms = typeof duration === 'number' && duration > 0 ? duration : (type === 'error' ? 5200 : DEFAULT_MS);
    var rtl = isRtl();
    var box = ensureContainer();

    // Cap stack
    while (box.querySelectorAll('.bankai-toast-card').length >= MAX_TOASTS) {
      var first = box.querySelector('.bankai-toast-card');
      if (first) dismiss(first);
      else break;
    }

    var card = document.createElement('div');
    card.className = 'bankai-toast-card bankai-toast-' + type + ' is-enter';
    card.setAttribute('role', type === 'error' || type === 'warning' ? 'alert' : 'status');
    card.innerHTML =
      '<span class="bankai-toast-accent" aria-hidden="true"></span>' +
      '<div class="bankai-toast-icon-badge bankai-toast-icon-' + type + '" aria-hidden="true">' + iconSvg(type) + '</div>' +
      '<div class="bankai-toast-body">' +
        '<span class="bankai-toast-label">' + labelFa(type, rtl) + '</span>' +
        '<span class="bankai-toast-text"></span>' +
      '</div>' +
      '<button type="button" class="bankai-toast-dismiss" aria-label="Dismiss">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>' +
      '</button>' +
      '<div class="bankai-toast-progress" aria-hidden="true"><div class="bankai-toast-progress-bar is-running" style="--toast-ms:' + ms + 'ms"></div></div>';

    card.querySelector('.bankai-toast-text').textContent = message;

    var closeBtn = card.querySelector('.bankai-toast-dismiss');
    closeBtn.addEventListener('click', function () { dismiss(card); });
    card.addEventListener('click', function (e) {
      if (e.target.closest('.bankai-toast-dismiss')) return;
      // click body keeps it; double-click dismiss
    });

    box.appendChild(card);
    requestAnimationFrame(function () {
      card.classList.remove('is-enter');
      card.classList.add('is-visible');
    });

    card._timer = setTimeout(function () { dismiss(card); }, ms);
    return card;
  }

  window.bankaiShowToast = bankaiShowToast;
  // aliases used across tabs
  window.showToast = function (msg, type, duration) {
    return bankaiShowToast(msg, type, duration);
  };
})();

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
        if (typeof window.bankaiShowToast === 'function') {
            window.bankaiShowToast(msg, type || 'success');
            return;
        }
        if (typeof window.showToast === 'function' && window.showToast !== toast) {
            window.showToast(msg, type || 'success');
        }
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
