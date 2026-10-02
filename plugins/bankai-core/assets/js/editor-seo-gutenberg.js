/**
 * Bankai Gutenberg SEO Sidebar Integration (Vanilla JS)
 */
(function (wp) {
    'use strict';

    if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components) {
        return;
    }

    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var useEffect = wp.element.useEffect;
    var useRef = wp.element.useRef;

    function BankaiSeoSidebar() {
        var hostRef = useRef(null);

        useEffect(function () {
            if (!hostRef.current) return;
            var cfg = window.bankaiGutenbergSeo || {};
            if (cfg.panelHtml) {
                hostRef.current.innerHTML = cfg.panelHtml;
                // Re-trigger event listeners on dynamically injected HTML if needed
                var event = new CustomEvent('bankai-editor-seo-rendered');
                document.dispatchEvent(event);
            }
        }, []);

        return createElement(
            Fragment,
            null,
            createElement(
                PluginSidebarMoreMenuItem,
                {
                    target: 'bankai-seo-sidebar-panel',
                    icon: 'chart-bar',
                },
                'سئوی بنکای'
            ),
            createElement(
                PluginSidebar,
                {
                    name: 'bankai-seo-sidebar-panel',
                    title: 'سئوی بنکای',
                    icon: 'chart-bar',
                },
                createElement('div', {
                    ref: hostRef,
                    className: 'bankai-gutenberg-panel-wrap',
                })
            )
        );
    }

    registerPlugin('bankai-seo-sidebar', {
        render: BankaiSeoSidebar,
        icon: 'chart-bar',
    });
})(window.wp);
