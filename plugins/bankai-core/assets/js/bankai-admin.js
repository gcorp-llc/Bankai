/**
 * Bankai Admin — Vanilla JS Application Core
 */
(function () {
    'use strict';

    function cfg() {
        return window.bankaiCoreData || {};
    }

    function initAdmin() {
        var app = document.getElementById('bankai-admin-app');
        if (!app) return;

        var navBtns = app.querySelectorAll('.bankai-nav-btn');
        var tabPanes = app.querySelectorAll('.bankai-tab-pane');
        var mobileOverlay = document.getElementById('bankai-mobile-overlay');
        var sidebar = document.getElementById('bankai-admin-sidebar');
        var mobileHeader = document.getElementById('bankai-sidebar-mobile-header');
        var mobileCloseBtn = document.getElementById('bankai-mobile-close-btn');
        var mobileToggleBtn = document.getElementById('bankai-mobile-toggle-btn');

        function setTab(tabId, subtabId, updateUrl) {
            if (updateUrl === undefined) updateUrl = true;

            navBtns.forEach(function (btn) {
                if (btn.getAttribute('data-tab') === tabId) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            tabPanes.forEach(function (pane) {
                if (pane.id === 'tab-' + tabId) {
                    pane.removeAttribute('hidden');
                    pane.classList.add('active');
                } else {
                    pane.setAttribute('hidden', '');
                    pane.classList.remove('active');
                }
            });

            app.setAttribute('data-active-tab', tabId);
            if (subtabId) {
                app.setAttribute('data-active-subtab', subtabId);
            } else {
                app.removeAttribute('data-active-subtab');
            }

            if (updateUrl && window.history && window.history.pushState) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tabId);
                if (subtabId) {
                    url.searchParams.set('subtab', subtabId);
                } else {
                    url.searchParams.delete('subtab');
                }
                window.history.pushState({ tab: tabId, subtab: subtabId }, '', url.toString());
            }

            closeMobileMenu();
        }

        function openMobileMenu() {
            if (sidebar) sidebar.classList.add('mobile-open');
            if (mobileOverlay) mobileOverlay.removeAttribute('hidden');
            if (mobileHeader) mobileHeader.removeAttribute('hidden');
        }

        function closeMobileMenu() {
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (mobileOverlay) mobileOverlay.setAttribute('hidden', '');
            if (mobileHeader) mobileHeader.setAttribute('hidden', '');
        }

        navBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var t = btn.getAttribute('data-tab');
                if (t) setTab(t, '');
            });
        });

        if (mobileToggleBtn) {
            mobileToggleBtn.addEventListener('click', openMobileMenu);
        }
        if (mobileCloseBtn) {
            mobileCloseBtn.addEventListener('click', closeMobileMenu);
        }
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMobileMenu);
        }

        window.addEventListener('popstate', function (e) {
            var params = new URLSearchParams(window.location.search);
            var tab = params.get('tab') || 'overview';
            var subtab = params.get('subtab') || '';
            setTab(tab, subtab, false);
        });

        // Expose helper API globally
        window.bankaiAdminApi = {
            setTab: setTab,
            showToast: function (msg, type) {
                var toast = document.getElementById('bankai-admin-toast');
                var msgEl = document.getElementById('bankai-toast-msg');
                if (!toast || !msgEl) return;
                msgEl.textContent = msg || '';
                toast.className = 'bankai-toast is-' + (type || 'success');
                toast.removeAttribute('hidden');
                setTimeout(function () {
                    toast.setAttribute('hidden', '');
                }, 3500);
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdmin);
    } else {
        initAdmin();
    }
})();
