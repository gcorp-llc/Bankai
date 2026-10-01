/**
 * Bankai Core Admin - Vanilla JS Controller
 * Clean, lightweight, zero framework dependencies
 */
(function () {
    'use strict';

    function cfg() {
        return window.bankaiData || window.bankaiCoreData || {};
    }

    function rest(path, options) {
        var c = cfg();
        var base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/$/, '');
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

    function ajax(action, data, timeoutMs) {
        var c = cfg();
        var body = new FormData();
        body.append('action', action);
        body.append('nonce', c.adminNonce || c.nonce || '');
        Object.keys(data || {}).forEach(function (k) {
            var v = data[k];
            if (v === undefined || v === null) return;
            if (typeof v === 'object') {
                body.append(k, JSON.stringify(v));
            } else {
                body.append(k, v);
            }
        });
        var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var timer = null;
        var ms = timeoutMs || 20000;
        if (controller) {
            timer = setTimeout(function () { try { controller.abort(); } catch (e) {} }, ms);
        }
        return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
            signal: controller ? controller.signal : undefined
        }).then(function (r) {
            return r.json().catch(function () {
                return { success: false, data: { message: 'Response format error' } };
            });
        }).catch(function (err) {
            var msg = (err && err.name === 'AbortError') ? 'Request timed out' : ((err && err.message) || 'Network error');
            return { success: false, data: { message: msg } };
        }).finally(function () {
            if (timer) clearTimeout(timer);
        });
    }

    function showToast(message, type) {
        type = type || 'success';
        var host = document.getElementById('bankai-toast-host');
        var msgEl = document.getElementById('bankai-toast-message');
        if (!host || !msgEl) return;

        msgEl.textContent = message;
        if (type === 'error') {
            msgEl.style.borderColor = '#FECACA';
            msgEl.style.color = '#B91C1C';
        } else {
            msgEl.style.borderColor = '#D0D7DE';
            msgEl.style.color = '#1F2328';
        }
        host.style.display = 'block';

        clearTimeout(window._bankaiToastTimer);
        window._bankaiToastTimer = setTimeout(function () {
            host.style.display = 'none';
        }, 3600);
    }

    function setTab(tabId) {
        if (!tabId) return;

        // Map tab IDs to panes
        var targetPaneId = 'tab-' + tabId;
        var targetPane = document.getElementById(targetPaneId);

        if (!targetPane) {
            // Try fallback
            targetPane = document.querySelector('.bankai-tab-pane[id*="' + tabId + '"]');
        }

        // Hide all panes
        var panes = document.querySelectorAll('.bankai-tab-pane');
        panes.forEach(function (pane) {
            pane.style.display = 'none';
            pane.classList.remove('active');
        });

        // Deactivate all nav buttons
        var navBtns = document.querySelectorAll('.bankai-nav-btn');
        navBtns.forEach(function (btn) {
            btn.classList.remove('active');
        });

        // Activate target pane
        if (targetPane) {
            targetPane.style.display = 'block';
            targetPane.classList.add('active');
        }

        // Activate corresponding nav btn
        var activeBtn = document.querySelector('.bankai-nav-btn[data-tab="' + tabId + '"]') ||
                        document.getElementById('nav-tab-' + tabId);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        // Close mobile overlay
        var mobileOverlay = document.querySelector('.bankai-mobile-overlay');
        var sidebar = document.getElementById('bankai-admin-sidebar');
        if (mobileOverlay) mobileOverlay.style.display = 'none';
        if (sidebar) sidebar.classList.remove('mobile-open');

        // Scroll to top of main content
        var main = document.getElementById('bankai-main-content');
        if (main) main.scrollTop = 0;
    }

    function initEvents() {
        // Nav tab click listener
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.bankai-nav-btn, [data-tab]');
            if (btn) {
                var tab = btn.getAttribute('data-tab') || btn.id.replace('nav-tab-', '');
                if (tab) {
                    e.preventDefault();
                    setTab(tab);
                }
            }

            // Mobile menu toggle
            var mobileBtn = e.target.closest('#btn-mobile-menu');
            if (mobileBtn) {
                var sidebar = document.getElementById('bankai-admin-sidebar');
                var mobileOverlay = document.querySelector('.bankai-mobile-overlay');
                if (sidebar) sidebar.classList.toggle('mobile-open');
                if (mobileOverlay) mobileOverlay.style.display = sidebar && sidebar.classList.contains('mobile-open') ? 'block' : 'none';
            }

            // Close mobile menu
            var closeMobile = e.target.closest('#btn-close-mobile-menu, .bankai-mobile-overlay');
            if (closeMobile) {
                var sidebar = document.getElementById('bankai-admin-sidebar');
                var mobileOverlay = document.querySelector('.bankai-mobile-overlay');
                if (sidebar) sidebar.classList.remove('mobile-open');
                if (mobileOverlay) mobileOverlay.style.display = 'none';
            }

            // Purge cache button
            var purgeBtn = e.target.closest('#btn-purge-cache, .btn-purge-cache');
            if (purgeBtn) {
                e.preventDefault();
                showToast('در حال تخلیه تمام کش‌ها...', 'info');
                ajax('bankai_purge_speed_cache', {}).then(function (res) {
                    if (res && res.success) {
                        showToast('کش‌ها با موفقیت تخلیه شدند', 'success');
                    } else {
                        showToast((res && res.data && res.data.message) || 'خطا در تخلیه کش', 'error');
                    }
                });
            }

            // Optimize DB button
            var optDbBtn = e.target.closest('.btn-optimize-db');
            if (optDbBtn) {
                e.preventDefault();
                showToast('در حال بهینه‌سازی دیتابیس...', 'info');
                ajax('bankai_optimize_database', {}).then(function (res) {
                    if (res && res.success) {
                        showToast('دیتابیس بهینه شد', 'success');
                    } else {
                        showToast((res && res.data && res.data.message) || 'خطا در بهینه‌سازی', 'error');
                    }
                });
            }

            // Benchmark Vitals button
            var vitalsBtn = e.target.closest('.btn-benchmark-vitals');
            if (vitalsBtn) {
                e.preventDefault();
                showToast('در حال سنجش سرعت...', 'info');
                ajax('bankai_benchmark_vitals', {}).then(function (res) {
                    if (res && res.success) {
                        showToast('بنچمارک با موفقیت انجام شد', 'success');
                    } else {
                        showToast('خطا در سنجش سرعت', 'error');
                    }
                });
            }

            // Save settings form button
            var saveSettingsBtn = e.target.closest('#btn-save-settings, .btn-save-settings');
            if (saveSettingsBtn) {
                e.preventDefault();
                var form = saveSettingsBtn.closest('form') || document.getElementById('bankai-settings-form');
                if (form) {
                    var formData = new FormData(form);
                    var settings = {};
                    formData.forEach(function (val, key) {
                        settings[key] = val;
                    });
                    showToast('در حال ذخیره تنظیمات...', 'info');
                    rest('/settings', {
                        method: 'POST',
                        body: JSON.stringify({ settings: settings })
                    }).then(function (res) {
                        if (res && res.success !== false) {
                            showToast('تنظیمات با موفقیت ذخیره شد', 'success');
                        } else {
                            showToast((res && res.message) || 'خطا در ذخیره', 'error');
                        }
                    });
                }
            }
        });

        // Module Toggle Switches
        document.addEventListener('change', function (e) {
            var switchInput = e.target.closest('.bk-core-module-toggle');
            if (switchInput) {
                var modKey = switchInput.getAttribute('data-module');
                var enabled = switchInput.checked;
                if (modKey) {
                    rest('/core-module/' + encodeURIComponent(modKey), {
                        method: 'POST',
                        body: JSON.stringify({ enabled: enabled })
                    }).then(function (res) {
                        if (res && res.success) {
                            showToast(res.message || 'وضعیت ماژول بروزرسانی شد', 'success');
                        } else {
                            switchInput.checked = !enabled;
                            showToast((res && res.message) || 'خطا در تغییر وضعیت', 'error');
                        }
                    });
                }
            }

            // Feature / SEO / Speed / Media module switches
            var subSwitch = e.target.closest('.bk-module-toggle');
            if (subSwitch) {
                var subId = subSwitch.getAttribute('data-id');
                var subEnabled = subSwitch.checked;
                if (subId) {
                    rest('/module/' + encodeURIComponent(subId), {
                        method: 'POST',
                        body: JSON.stringify({ enabled: subEnabled })
                    }).then(function (res) {
                        if (res && res.success) {
                            showToast(res.message || 'ماژول بروزرسانی شد', 'success');
                        } else {
                            subSwitch.checked = !subEnabled;
                            showToast((res && res.message) || 'خطا در ویرایش ماژول', 'error');
                        }
                    });
                }
            }
        });
    }

    // Initialize on DOMReady
    document.addEventListener('DOMContentLoaded', function () {
        initEvents();

        // Initial active tab
        var c = cfg();
        var initialTab = c.activeTab || 'overview';
        setTab(initialTab);

        // Expose global methods for backward compatibility
        window.bankaiAdminInstance = {
            setTab: setTab,
            showToast: showToast
        };
    });

})();
