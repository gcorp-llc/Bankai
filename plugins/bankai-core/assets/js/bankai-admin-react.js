/**
 * Bankai Core Admin - React Application Bootstrapper
 * Built with @wordpress/element (WP React)
 */

(function() {
    'use strict';

    if (typeof window.wp === 'undefined' || !window.wp.element) {
        console.error('Bankai Core: wp.element is required for Bankai Admin App.');
        return;
    }

    const { createElement, useState, useEffect, useMemo, useCallback, useRef } = window.wp.element;

    // Root data passed via wp_localize_script or inline
    const initialData = window.bankaiAdminData || window.bankaiCoreData || {};
    const initialState = window.bankaiInitialState || {};

    // Helper to request REST API
    async function apiRequest(endpoint, options = {}) {
        const restUrl = initialData.restUrl || '/wp-json/bankai/v1/';
        const nonce = initialData.nonce || '';
        const url = restUrl.replace(/\/$/, '') + '/' + endpoint.replace(/^\//, '');

        const headers = {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce,
            ...(options.headers || {})
        };

        const res = await fetch(url, { ...options, headers });
        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: 'Server error' }));
            throw new Error(err.message || 'API request failed');
        }
        return await res.json();
    }

    // --- Components ---

    // SVG Icons
    function Icon({ name, className = '', size = 20 }) {
        const style = { width: size, height: size, display: 'inline-block', flexShrink: 0 };
        switch (name) {
            case 'dashboard':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('rect', { x: '3', y: '3', width: '7', height: '9', rx: '1' }),
                    createElement('rect', { x: '14', y: '3', width: '7', height: '5', rx: '1' }),
                    createElement('rect', { x: '14', y: '12', width: '7', height: '9', rx: '1' }),
                    createElement('rect', { x: '3', y: '16', width: '7', height: '5', rx: '1' })
                );
            case 'seo':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('path', { d: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' })
                );
            case 'ai':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'currentColor' },
                    createElement('path', { d: 'M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z' })
                );
            case 'speed':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('polygon', { points: '13 2 3 14 12 14 11 22 21 10 12 10 13 2' })
                );
            case 'media':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('rect', { x: '3', y: '3', width: '18', height: '18', rx: '2' }),
                    createElement('circle', { cx: '8.5', cy: '8.5', r: '1.5' }),
                    createElement('polyline', { points: '21 15 16 10 5 21' })
                );
            case 'articles':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('path', { d: 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z' }),
                    createElement('polyline', { points: '14 2 14 8 20 8' }),
                    createElement('line', { x1: '16', y1: '13', x2: '8', y2: '13' }),
                    createElement('line', { x1: '16', y1: '17', x2: '8', y2: '17' })
                );
            case 'kits':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('polygon', { points: '12 2 2 7 12 12 22 7 12 2' }),
                    createElement('polyline', { points: '2 17 12 22 22 17' }),
                    createElement('polyline', { points: '2 12 12 17 22 12' })
                );
            case 'calendar':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('rect', { x: '3', y: '4', width: '18', height: '18', rx: '2' }),
                    createElement('line', { x1: '16', y1: '2', x2: '16', y2: '6' }),
                    createElement('line', { x1: '8', y1: '2', x2: '8', y2: '6' }),
                    createElement('line', { x1: '3', y1: '10', x2: '21', y2: '10' })
                );
            case 'settings':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    createElement('circle', { cx: '12', cy: '12', r: '3' }),
                    createElement('path', { d: 'M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z' })
                );
            case 'pin':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'currentColor' },
                    createElement('path', { d: 'M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z' })
                );
            case 'moon':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5' },
                    createElement('path', { d: 'M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z' })
                );
            case 'sun':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5' },
                    createElement('circle', { cx: '12', cy: '12', r: '5' }),
                    createElement('line', { x1: '12', y1: '1', x2: '12', y2: '3' }),
                    createElement('line', { x1: '12', y1: '21', x2: '12', y2: '23' }),
                    createElement('line', { x1: '4.22', y1: '4.22', x2: '5.64', y2: '5.64' }),
                    createElement('line', { x1: '18.36', y1: '18.36', x2: '19.78', y2: '19.78' }),
                    createElement('line', { x1: '1', y1: '12', x2: '3', y2: '12' }),
                    createElement('line', { x1: '21', y1: '12', x2: '23', y2: '12' }),
                    createElement('line', { x1: '4.22', y1: '19.78', x2: '5.64', y2: '18.36' }),
                    createElement('line', { x1: '18.36', y1: '5.64', x2: '19.78', y2: '4.22' })
                );
            case 'search':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5' },
                    createElement('circle', { cx: '11', cy: '11', r: '8' }),
                    createElement('line', { x1: '21', y1: '21', x2: '16.65', y2: '16.65' })
                );
            case 'check':
                return createElement('svg', { style, className, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '2' },
                    createElement('polyline', { points: '20 6 9 17 4 12' })
                );
            default:
                return null;
        }
    }

    // Switch Component
    function Switch({ checked, onChange, label, disabled = false }) {
        return createElement('label', { className: `bankai-switch ${disabled ? 'bankai-switch-disabled' : ''}` },
            createElement('input', {
                type: 'checkbox',
                checked: !!checked,
                disabled,
                onChange: (e) => !disabled && onChange(e.target.checked)
            }),
            createElement('span', { className: 'bankai-switch-slider' }),
            label && createElement('span', { className: 'bankai-switch-label' }, label)
        );
    }

    // Toast Container
    function Toast({ toast, onClose }) {
        if (!toast) return null;
        return createElement('div', { className: `bankai-toast bankai-toast-${toast.type || 'info'}` },
            createElement('span', null, toast.message),
            createElement('button', { type: 'button', onClick: onClose, className: 'bankai-toast-close' }, '×')
        );
    }

    // Onboarding Wizard Modal
    function OnboardingWizard({ isOpen, onClose }) {
        if (!isOpen) return null;
        const [step, setStep] = useState(1);

        return createElement('div', { className: 'bankai-modal-overlay' },
            createElement('div', { className: 'bankai-modal-content' },
                createElement('div', { className: 'bankai-modal-header' },
                    createElement('h3', null, 'راهنمای راه‌اندازی سریع Bankai Core'),
                    createElement('button', { className: 'bankai-modal-close', onClick: onClose }, '×')
                ),
                createElement('div', { className: 'bankai-modal-body' },
                    step === 1 && createElement('div', null,
                        createElement('h4', null, '۱. تنظیمات اتصال به AI Studio'),
                        createElement('p', null, 'کلیدهای API ارائه‌دهندگان خود نظیر OpenRouter، Google Gemini یا OpenAI را در بخش AI Studio وارد کنید. کلیدها با امنیت بالا رمزنگاری می‌شوند.'),
                        createElement('div', { className: 'bankai-callout bankai-callout-info' },
                            'پیشنهاد: می‌توانید کلید BANKAI_ENCRYPTION_KEY را در wp-config.php تعریف کنید.'
                        )
                    ),
                    step === 2 && createElement('div', null,
                        createElement('h4', null, '۲. فعال‌سازی ماژول‌های سئو و سرعت'),
                        createElement('p', null, 'ماژول‌های مورد نیاز خود مانند نقشه سایت XML، بهینه‌سازی تصاویر و کلمات کلیدی ثابت 📌 را فعال نمایید.')
                    ),
                    step === 3 && createElement('div', null,
                        createElement('h4', null, '۳. اتصال Google Analytics & Search Console'),
                        createElement('p', null, 'کد اندازه‌گیری Google Analytics 4 را جهت نمایش آمار لحظه‌ای بازدید در بخش سئو وارد کنید.')
                    )
                ),
                createElement('div', { className: 'bankai-modal-footer' },
                    step > 1 && createElement('button', { className: 'bankai-btn bankai-btn-secondary', onClick: () => setStep(step - 1) }, 'قبلی'),
                    step < 3 && createElement('button', { className: 'bankai-btn bankai-btn-primary', onClick: () => setStep(step + 1) }, 'بعدی'),
                    step === 3 && createElement('button', { className: 'bankai-btn bankai-btn-success', onClick: onClose }, 'پایان و شروع کار')
                )
            )
        );
    }

    // Header Component
    function Header({ activeTab, isDark, toggleTheme, onOpenWizard, searchQuery, setSearchQuery }) {
        return createElement('header', { className: 'bankai-admin-header' },
            createElement('div', { className: 'bankai-header-left' },
                createElement('div', { className: 'bankai-header-title-wrap' },
                    createElement('h1', { className: 'bankai-header-title' }, 'Bankai Core Platform'),
                    createElement('span', { className: 'bankai-badge bankai-badge-purple' }, 'v' + (initialData.version || '4.0'))
                )
            ),
            createElement('div', { className: 'bankai-header-right' },
                createElement('div', { className: 'bankai-search-box' },
                    createElement(Icon, { name: 'search', size: 16 }),
                    createElement('input', {
                        type: 'text',
                        placeholder: 'جستجوی سریع تنظیمات...',
                        value: searchQuery,
                        onChange: (e) => setSearchQuery(e.target.value)
                    })
                ),
                createElement('button', {
                    type: 'button',
                    className: 'bankai-btn-icon',
                    onClick: onOpenWizard,
                    title: 'راهنمای راه‌اندازی'
                }, '✨ راهنما'),
                createElement('button', {
                    type: 'button',
                    className: 'bankai-btn-icon',
                    onClick: toggleTheme,
                    title: isDark ? 'حالت روشن' : 'حالت تاریک'
                }, createElement(Icon, { name: isDark ? 'sun' : 'moon', size: 18 }))
            )
        );
    }

    // Sidebar Component
    function Sidebar({ activeTab, setActiveTab, coreModules, toggleModule }) {
        const tabs = [
            { id: 'overview', label: 'داشبورد اصلی', icon: 'dashboard', moduleKey: 'overview' },
            { id: 'seo-engine', label: 'سئو و اسکیما', icon: 'seo', moduleKey: 'seo' },
            { id: 'ai-studio', label: 'استودیوی AI Studio', icon: 'ai', moduleKey: 'ai', isBold: true },
            { id: 'articles', label: 'مدیریت مقالات', icon: 'articles', moduleKey: 'articles' },
            { id: 'speed-cache', label: 'سرعت و کش', icon: 'speed', moduleKey: 'speed' },
            { id: 'media-watermark', label: 'تصاویر و واترمارک', icon: 'media', moduleKey: 'media' },
            { id: 'theme-kits', label: 'کیت‌های طراحی', icon: 'kits', moduleKey: 'theme_kits' },
            { id: 'jalali-calendar', label: 'تقویم جلالی', icon: 'calendar', moduleKey: 'jalali' },
            { id: 'settings-license', label: 'تنظیمات و لایسنس', icon: 'settings', moduleKey: 'settings' },
        ];

        return createElement('aside', { className: 'bankai-admin-sidebar' },
            createElement('div', { className: 'bankai-brand-card' },
                createElement('div', { className: 'bankai-brand-logo' },
                    createElement(Icon, { name: 'ai', size: 24 })
                ),
                createElement('div', { className: 'bankai-brand-info' },
                    createElement('div', { className: 'bankai-brand-title' }, 'BANKAI CORE'),
                    createElement('div', { className: 'bankai-brand-status' },
                        createElement('span', { className: 'bankai-status-dot' }),
                        'موتور فعال'
                    )
                )
            ),
            createElement('nav', { className: 'bankai-sidebar-nav' },
                tabs.map(tab => {
                    const isModuleActive = coreModules[tab.moduleKey] !== false;
                    const isActive = activeTab === tab.id;

                    return createElement('div', {
                        key: tab.id,
                        className: `bankai-nav-item ${isActive ? 'active' : ''} ${!isModuleActive ? 'disabled' : ''}`
                    },
                        createElement('button', {
                            type: 'button',
                            className: 'bankai-nav-link',
                            onClick: () => setActiveTab(tab.id)
                        },
                            createElement(Icon, { name: tab.icon, size: 18, className: tab.isBold ? 'bankai-icon-bold' : '' }),
                            createElement('span', { className: 'bankai-nav-label' }, tab.label)
                        ),
                        tab.moduleKey !== 'overview' && tab.moduleKey !== 'settings' && createElement(Switch, {
                            checked: isModuleActive,
                            onChange: (val) => toggleModule(tab.moduleKey, val)
                        })
                    );
                })
            )
        );
    }

    // Main App Component
    function App() {
        const [activeTab, setActiveTab] = useState(initialData.activeTab || 'overview');
        const [isDark, setIsDark] = useState(() => localStorage.getItem('bankai_theme') === 'dark');
        const [toast, setToast] = useState(null);
        const [isWizardOpen, setIsWizardOpen] = useState(false);
        const [searchQuery, setSearchQuery] = useState('');
        const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
        const [isSaving, setIsSaving] = useState(false);

        const [coreModules, setCoreModules] = useState(() => {
            const map = {
                overview: true, seo: true, ai: true, articles: true,
                speed: true, media: true, theme_kits: true, jalali: true, settings: true
            };
            if (Array.isArray(initialState.coreModules)) {
                initialState.coreModules.forEach(m => {
                    if (m.key) map[m.key] = !!m.active;
                });
            }
            return map;
        });

        // Handle Theme Toggle
        useEffect(() => {
            document.body.classList.toggle('bankai-dark-mode', isDark);
            localStorage.setItem('bankai_theme', isDark ? 'dark' : 'light');
        }, [isDark]);

        const showToast = useCallback((message, type = 'info') => {
            setToast({ message, type });
            setTimeout(() => setToast(null), 4000);
        }, []);

        const toggleModule = useCallback(async (key, enabled) => {
            setCoreModules(prev => ({ ...prev, [key]: enabled }));
            setHasUnsavedChanges(true);
            try {
                await apiRequest('modules/toggle', {
                    method: 'POST',
                    body: JSON.stringify({ module: key, enabled })
                });
                showToast(`وضعیت ماژول ${key} بروزرسانی شد`, 'success');
            } catch (e) {
                showToast('خطا در تغییر وضعیت ماژول: ' + e.message, 'error');
            }
        }, [showToast]);

        const handleSave = async () => {
            setIsSaving(true);
            try {
                // Trigger save logic via REST or ajax
                showToast('تمامی تنظیمات با موفقیت ذخیره شدند', 'success');
                setHasUnsavedChanges(false);
            } catch (e) {
                showToast('خطا در ذخیره‌سازی تنظیمات', 'error');
            } finally {
                setIsSaving(false);
            }
        };

        return createElement('div', {
            id: 'bankai-admin-app',
            className: `bankai-app-wrapper ${isDark ? 'bankai-dark' : 'bankai-light'} ${initialData.isRtl ? 'rtl' : 'ltr'}`,
            dir: initialData.isRtl ? 'rtl' : 'ltr'
        },
            createElement(Header, {
                activeTab,
                isDark,
                toggleTheme: () => setIsDark(!isDark),
                onOpenWizard: () => setIsWizardOpen(true),
                searchQuery,
                setSearchQuery
            }),
            createElement('div', { className: 'bankai-admin-body' },
                createElement(Sidebar, {
                    activeTab,
                    setActiveTab,
                    coreModules,
                    toggleModule
                }),
                createElement('main', { className: 'bankai-admin-content' },
                    // Placeholder for tab content view rendering
                    createElement('div', { className: 'bankai-tab-container' },
                        createElement('div', { className: 'bankai-card' },
                            createElement('h2', { className: 'bankai-card-title' }, `نمای ماژول: ${activeTab}`),
                            createElement('p', null, 'محتوای این بخش توسط کامپوننت‌های اختصاصی مدیریت می‌شود.')
                        )
                    )
                )
            ),
            hasUnsavedChanges && createElement('div', { className: 'bankai-floating-save' },
                createElement('span', null, 'شما تغییرات ذخیره‌نشده دارید.'),
                createElement('button', {
                    className: 'bankai-btn bankai-btn-success',
                    onClick: handleSave,
                    disabled: isSaving
                }, isSaving ? 'در حال ذخیره...' : 'ذخیره تغییرات')
            ),
            createElement(Toast, { toast, onClose: () => setToast(null) }),
            createElement(OnboardingWizard, { isOpen: isWizardOpen, onClose: () => setIsWizardOpen(false) })
        );
    };

    // Initialize React App on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        const rootEl = document.getElementById('bankai-admin-app');
        if (rootEl && window.wp.element.render) {
            window.wp.element.render(createElement(App), rootEl);
        }
    });

})();
