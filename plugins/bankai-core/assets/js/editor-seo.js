/**
 * Bankai Editor SEO — Vanilla JS Tab Switcher & Analysis Controller
 */
(function () {
    'use strict';

    function initEditorSeo() {
        var root = document.getElementById('bankai-seo-sidebar');
        if (!root) return;

        // Tabs
        var tabBtns = root.querySelectorAll('#bk-editor-tabs-nav .bk-tab-btn, #bk-editor-tabs-nav .bk-tab-ai');
        var panes = root.querySelectorAll('.bk-tab-pane');

        function switchTab(targetTab) {
            tabBtns.forEach(function (btn) {
                var isTarget = btn.getAttribute('data-tab') === targetTab;
                if (isTarget) {
                    btn.classList.add('is-active');
                } else {
                    btn.classList.remove('is-active');
                }
            });

            panes.forEach(function (pane) {
                if (pane.id === 'bk-tab-' + targetTab) {
                    pane.removeAttribute('hidden');
                    pane.classList.add('is-active');
                } else {
                    pane.setAttribute('hidden', '');
                    pane.classList.remove('is-active');
                }
            });

            if (targetTab === 'ai') {
                var modal = document.getElementById('bk-ai-modal-backdrop');
                if (modal) modal.removeAttribute('hidden');
            }
        }

        tabBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var t = btn.getAttribute('data-tab');
                if (t) switchTab(t);
            });
        });

        // Modal Close
        var modalClose = document.getElementById('bk-ai-modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', function () {
                var modal = document.getElementById('bk-ai-modal-backdrop');
                if (modal) modal.setAttribute('hidden', '');
            });
        }

        // Live Counters
        var titleInput = document.getElementById('bk-seo-title-input');
        var titleCounter = document.getElementById('bk-seo-title-counter');
        var titleBar = document.getElementById('bk-title-bar');

        if (titleInput && titleCounter) {
            var updateTitle = function () {
                var len = titleInput.value.length;
                titleCounter.textContent = len + '/60';
                if (titleBar) {
                    var pct = Math.min(100, Math.round((len / 60) * 100));
                    titleBar.style.width = pct + '%';
                    titleBar.style.background = len > 60 ? '#EF4444' : '#10B981';
                }
            };
            titleInput.addEventListener('input', updateTitle);
            updateTitle();
        }

        var descInput = document.getElementById('bk-seo-desc-input');
        var descCounter = document.getElementById('bk-seo-desc-counter');
        var descBar = document.getElementById('bk-desc-bar');

        if (descInput && descCounter) {
            var updateDesc = function () {
                var len = descInput.value.length;
                descCounter.textContent = len + '/160';
                if (descBar) {
                    var pct = Math.min(100, Math.round((len / 160) * 100));
                    descBar.style.width = pct + '%';
                    descBar.style.background = len > 160 ? '#EF4444' : '#10B981';
                }
            };
            descInput.addEventListener('input', updateDesc);
            updateDesc();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditorSeo);
    } else {
        initEditorSeo();
    }
})();
