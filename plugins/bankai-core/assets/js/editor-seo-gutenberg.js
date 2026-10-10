(function () {
    'use strict';

    if (!window.wp || !wp.plugins || !wp.editPost || !wp.element) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { createElement: el, useState, useEffect, useRef } = wp.element;

    const cfg = window.bankaiGutenbergSeo || {
        score: 0,
        color: '#B8BCC2',
        label: 'سئو',
        panelHtml: '',
        postId: 0
    };

    function scoreColor(s) {
        if (s < 10) return '#B8BCC2';
        if (s < 20) return '#E8D3A2';
        if (s < 40) return '#A6122D';
        if (s < 60) return '#E0A030';
        if (s < 80) return '#93C572';
        return '#1E7F5C';
    }

    function ScoreCircleIcon({ score, size }) {
        const s = Math.min(100, Math.max(0, Number(score) || 0));
        const color = scoreColor(s);
        const r = size * 0.38;
        const c = 2 * Math.PI * r;
        const offset = c - (c * s / 100);
        const cx = size / 2;

        return el(
            'span',
            {
                className: 'bankai-seo-score-icon',
                style: {
                    width: size,
                    height: size,
                    position: 'relative',
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderRadius: '50%',
                    flexShrink: 0
                }
            },
            el(
                'svg',
                {
                    width: size,
                    height: size,
                    viewBox: '0 0 ' + size + ' ' + size,
                    style: { position: 'absolute', inset: 0 }
                },
                el('circle', {
                    cx: cx, cy: cx, r: r,
                    fill: 'none', stroke: '#eaeef2', strokeWidth: Math.max(2, size * 0.08)
                }),
                el('circle', {
                    cx: cx, cy: cx, r: r,
                    fill: 'none', stroke: color, strokeWidth: Math.max(2, size * 0.08),
                    strokeDasharray: String(c),
                    strokeDashoffset: String(offset),
                    strokeLinecap: 'round',
                    transform: 'rotate(-90 ' + cx + ' ' + cx + ')'
                })
            ),
            el('span', {
                style: {
                    position: 'relative',
                    fontSize: Math.max(7, Math.round(size * 0.30)),
                    fontWeight: 800,
                    fontFamily: 'ui-monospace, monospace',
                    color: color,
                    lineHeight: 1
                }
            }, String(s))
        );
    }

    function preparePanelHtml(html, postId) {
        if (!html || typeof html !== 'string') {
            return '';
        }
        let out = html;
        out = out.replace(
            /id="bankai-seo-sidebar"/,
            'id="bankai-seo-sidebar-gb"'
        );
        if (postId > 0) {
            out = out.replace(
                /bankaiSeoSidebar\(\s*\d+\s*\)/,
                'bankaiSeoSidebar(' + postId + ')'
            );
        }
        return out;
    }

    function FullSeoPanel() {
        const hostRef = useRef(null);
        const [score, setScore] = useState(cfg.score || 0);
        const [ready, setReady] = useState(false);
        const [error, setError] = useState('');

        useEffect(() => {
            const onScore = (e) => {
                if (e.detail && typeof e.detail.score === 'number') {
                    setScore(e.detail.score);
                    cfg.score = e.detail.score;
                }
            };
            window.addEventListener('bankai-seo-score', onScore);
            return () => window.removeEventListener('bankai-seo-score', onScore);
        }, []);

        useEffect(() => {
            const node = hostRef.current;
            if (!node || node.dataset.bankaiMounted === '1') {
                return;
            }

            let cancelled = false;

            function mount() {
                if (cancelled || !hostRef.current) return;

                const html = preparePanelHtml(cfg.panelHtml, cfg.postId || 0);

                if (!html) {
                    setError('پنل سئو در دسترس نیست. صفحه را رفرش کنید.');
                    setReady(true);
                    return;
                }

                try {
                    document.querySelectorAll('.postbox#bankai-seo-sidebar, #bankai-seo-sidebar.postbox, .postbox[id*="bankai-seo"]').forEach(function (box) {
                        box.style.display = 'none';
                    });
                } catch (e) { /* ignore */ }

                hostRef.current.innerHTML = html;
                hostRef.current.dataset.bankaiMounted = '1';

                const tryAlpine = (attempt) => {
                    if (cancelled) return;
                    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                        try {
                            window.Alpine.initTree(hostRef.current);
                            setReady(true);
                            return;
                        } catch (err) {
                            console.warn('Bankai Alpine.initTree', err);
                        }
                    }
                    if (attempt < 40) {
                        setTimeout(function () { tryAlpine(attempt + 1); }, 100);
                    } else {
                        setReady(true);
                    }
                };

                setTimeout(function () { tryAlpine(0); }, 50);
            }

            setReady(false);
            setTimeout(mount, 120);

            return function () {
                cancelled = true;
            };
        }, []);

        return el(
            'div',
            {
                className: 'bankai-gutenberg-seo-shell',
                style: {
                    position: 'relative',
                    minHeight: '320px',
                    direction: 'rtl',
                    background: '#f6f8fa'
                }
            },
            !ready && el(
                'div',
                {
                    className: 'bankai-seo-loader',
                    style: {
                        position: 'absolute',
                        inset: 0,
                        zIndex: 20,
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: '12px',
                        background: 'rgba(246, 248, 250, 0.72)',
                        backdropFilter: 'blur(8px)',
                        WebkitBackdropFilter: 'blur(8px)'
                    }
                },
                el('div', {
                    className: 'bankai-seo-spinner',
                    style: {
                        width: '36px',
                        height: '36px',
                        border: '3px solid #d0d7de',
                        borderTopColor: '#0969da',
                        borderRadius: '50%',
                        animation: 'bankai-spin 0.7s linear infinite'
                    }
                }),
                el('span', {
                    style: {
                        fontSize: '12px',
                        fontWeight: 600,
                        color: '#656d76',
                        fontFamily: 'Vazirmatn, sans-serif'
                    }
                }, 'در حال بارگذاری سوئیت سئو…')
            ),
            error && el(
                'div',
                {
                    style: {
                        padding: '16px',
                        fontSize: '12px',
                        color: '#cf222e',
                        fontFamily: 'Vazirmatn, sans-serif',
                        direction: 'rtl'
                    }
                },
                error
            ),
            el('div', {
                ref: hostRef,
                className: 'bankai-gutenberg-seo-host',
                style: {
                    margin: 0,
                    padding: 0,
                    opacity: ready ? 1 : 0.35,
                    transition: 'opacity 0.25s ease',
                    pointerEvents: ready ? 'auto' : 'none'
                }
            })
        );
    }

    function PluginIcon() {
        const [score, setScore] = useState(cfg.score || 0);

        useEffect(() => {
            const onScore = (e) => {
                if (e.detail && typeof e.detail.score === 'number') {
                    setScore(e.detail.score);
                }
            };
            window.addEventListener('bankai-seo-score', onScore);
            return () => window.removeEventListener('bankai-seo-score', onScore);
        }, []);

        return el(ScoreCircleIcon, { score: score, size: 24 });
    }

    function MoreMenuItem() {
        const [score, setScore] = useState(cfg.score || 0);

        useEffect(() => {
            const onScore = (e) => {
                if (e.detail && typeof e.detail.score === 'number') {
                    setScore(e.detail.score);
                }
            };
            window.addEventListener('bankai-seo-score', onScore);
            return () => window.removeEventListener('bankai-seo-score', onScore);
        }, []);

        return el(
            PluginSidebarMoreMenuItem,
            { target: 'bankai-seo-sidebar-panel' },
            el(
                'span',
                {
                    style: {
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '8px',
                        direction: 'rtl'
                    }
                },
                el(ScoreCircleIcon, { score: score, size: 22 }),
                el('span', { style: { fontWeight: 600 } }, cfg.label || 'سئو')
            )
        );
    }

    registerPlugin('bankai-seo', {
        icon: el(PluginIcon),
        render: function () {
            return el(
                'div',
                null,
                el(MoreMenuItem),
                el(
                    PluginSidebar,
                    {
                        name: 'bankai-seo-sidebar-panel',
                        title: 'سئوی بنکای',
                        icon: el(PluginIcon),
                        className: 'bankai-plugin-sidebar'
                    },
                    el(FullSeoPanel)
                )
            );
        }
    });

    const style = document.createElement('style');
    style.textContent = [
        '@keyframes bankai-spin { to { transform: rotate(360deg); } }',
        '.bankai-plugin-sidebar .components-panel__body { padding: 0 !important; }',
        '.interface-complementary-area.bankai-plugin-sidebar .components-panel { background: #f6f8fa; }',
        '.bankai-gutenberg-seo-host .bankai-seo-root,',
        '.bankai-gutenberg-seo-host #bankai-seo-sidebar-gb { width: 100%; min-height: 100%; }',
        '.bankai-seo-score-icon { border-radius: 50% !important; }',
        '#bankai-seo-sidebar-gb .bk-header { border-bottom: none; }',
        '#bankai-seo-sidebar-gb .bk-header-main {',
        '  border-bottom: 1px dotted #d0d7de;',
        '  padding-bottom: 10px;',
        '  margin-bottom: 0;',
        '}',
        '#bankai-seo-sidebar-gb .bk-tabs { border-top: none; margin-top: 0; }',
        'body.block-editor-page .postbox#bankai-seo-sidebar,',
        'body.block-editor-page #bankai-seo-sidebar.postbox { display: none !important; }'
    ].join('\n');
    document.head.appendChild(style);
})();
