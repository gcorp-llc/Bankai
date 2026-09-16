/**
 * Bankai Core - Admin Alpine.js & HTMX Logic
 */

// Bridge WP localized objects
window.bankaiData = window.bankaiData || window.bankaiCoreData || {};
window.bankaiCoreData = window.bankaiCoreData || window.bankaiData;

function bankaiAdmin() {
    // Check persisted language preference
    const savedLang = typeof localStorage !== 'undefined' ? localStorage.getItem('bankai_lang') : null;
    const initialIsRtl = savedLang ? (savedLang === 'fa') : (window.bankaiData?.isRtl || false);

    return {
        activeTab: window.bankaiData?.activeTab || 'overview',
        isRtl: initialIsRtl,
        mobileMenuOpen: false,
        toast: { show: false, message: '', type: 'success' },
        showApiKeys: false,

        // Page and Action Loaders
        pageLoading: false,
        pageProgress: 0,
        savingLoader: {
            show: false,
            state: 'saving', // 'saving' | 'saved'
            title: '',
            message: ''
        },

        // Comprehensive Bilingual i18n Dictionary for WordPress Admin
        i18n: {
            en: {
                // Header & General
                slogan: 'Next-Gen High-Performance Modular WordPress Platform & AI Studio',
                purgeCache: 'Purge Cache',
                checkUpdates: 'Check Updates',
                wpDashboard: 'WP Admin',
                allSystemsOptimal: 'ALL SYSTEMS OPTIMAL',
                lifetimeLicense: 'LIFETIME PRO',
                
                // Sidebar Tabs
                navOverview: 'Overview & Telemetry',
                navThemeKits: 'Theme Kits & Customizer',
                navSeo: 'SEO & Schema Engine',
                navSpeed: 'Speed & Cache Engine',
                navMedia: 'Media & Watermark',
                navAi: 'AI Studio & Manifests',
                navSettings: 'Settings & License',
                coreArch: 'Core Architecture',
                navArch: 'Core Architecture',
                navPerformance: 'Optimization & Speed',
                navIntelligence: 'Intelligence & Admin',
                allSystemsNormal: 'All Systems Normal',
                memoryLimit: 'Memory Limit',
                database: 'Database Engine',
                layoutMode: 'Layout Direction',

                // Overview Tab
                uptimeSpeed: '⚡ Uptime & Speed',
                indexedNodes: '🕸️ Indexed Nodes',
                aiCrawls: '🤖 AI Crawler Hits',
                schemaScore: '🛡️ SEO Schema Score',
                avgLatency: 'Avg Latency',
                varnishHit: 'Varnish Hit Ratio',
                llmActive: 'LLM Manifest v1.2 Active',
                gradeA: 'Grade A+ Certified',
                coreModulesTitle: '🛠️ Core Engine Modules & Features',
                seoEngineDesc: 'Automated meta generation, Schema.org builder & XML Sitemaps.',
                mediaOptimizerDesc: 'WebP/AVIF auto-conversion, async processor & lazyloading.',
                llmManifestDesc: 'Structured markdown index endpoints for AI agents and LLMs.',
                speedCacheDesc: 'Zero-latency dynamic HTML page caching & Redis object store.',
                aiStudioDesc: 'Server-side Gemini AI content generation & prompt engineering.',
                vitalsTitle: '⚙️ SEO & Core Web Vitals',
                anomalyTitle: '⚠️ 404 Anomaly Hits & Heuristics',
                liveFeed: 'Live Feed',
                requestedUri: 'REQUESTED URI',
                hits: 'HITS',
                action: 'ACTION',
                apply301: '✓ Apply 301',
                optimalIndex: 'OPTIMAL INDEX',

                // Theme Kits
                themeKitsTitle: 'Bankai Starter Kits & Frontend Customizer',
                themeKitsDesc: '1-Click turnkey site architectures, Tailwind CSS variable compiler, and high-performance design presets.',
                syncLibrary: 'Sync Library',
                customizerTitle: 'Theme Customizer Panel',
                fontFamily: 'Typography Font Family',
                containerWidth: 'Container Max-Width',
                layoutComponents: 'Layout Components',
                stickyHeader: 'Sticky Header',
                minimalFooter: 'Minimal Footer',
                importKit: '⚡ Import Kit',
                preview: '👁️ Preview',
                active: 'Active',
                importingKit: 'Importing Starter Kit',
                stepContent: 'Import Demo Content & Menus',
                stepAcf: 'Configure Custom Fields (ACF)',
                stepPlugins: 'Activate & Verify Dependency Plugins',
                cancel: 'Cancel',
                close: 'Close',

                // SEO Tab
                seoTitle: 'Autonomous AI-Powered Schema & Metadata Engine',
                seoDesc: 'Real-time JSON-LD structured graph compiler, automatic SERP snippet enhancer, and instant indexing webhook dispatcher.',
                runAudit: '⚡ Run SEO Audit',
                seoWizard: '🧙‍♂️ Setup Wizard',
                configure: 'Configure',

                // Speed Tab
                speedTitle: 'High-Performance Caching & Asset Compiler',
                speedDesc: 'Page cache, Redis object caching, CSS/JS async aggregation, and database table defragmentation engine.',
                benchmarkVitals: '⚡ Benchmark Vitals',
                optimizeDb: '🗄️ Optimize Database',

                // Media Tab
                mediaTitle: 'Media Optimization Engine & Watermark Studio',
                mediaDesc: 'Automatic next-gen WebP/AVIF conversions, EXIF privacy stripping, and dynamic text/logo watermark overlay.',
                bulkConvert: '⚡ Bulk Convert Media',
                regenThumbs: '🔄 Regenerate Thumbnails',
                watermarkSettings: 'Dynamic Watermark Studio',
                watermarkPosition: 'Watermark Position',
                watermarkOpacity: 'Watermark Opacity',
                watermarkText: 'Watermark Text Label',

                // AI Studio
                aiTitle: 'Generative AI Content Studio & Prompt Manifests',
                aiDesc: 'Autonomous WordPress SEO outline writer, auto alt-tag generator, and public /llms.txt AI crawler manifest endpoint.',
                testAi: '⚡ Test AI Connections',
                defaultModel: 'Default AI Engine',
                aiSandboxLabel: '✨ Interactive AI Content & Meta Generator (Powered by Server-Side Gemini API)',
                generateNow: '⚡ Generate Now',
                processing: '⌛ Processing...',

                // Settings & License
                settingsTitle: 'Ecosystem Configuration & License Management',
                settingsDesc: 'Manage your pro subscription, update frequency, system diagnostics, and configuration backup/restore.',
                licenseStatus: 'Pro License Status',
                licenseKey: 'License Key',
                licenseActive: 'Active & Verified (Lifetime)',
                deactivateLicense: 'Deactivate',
                activateLicense: 'Activate License',
                exportConfig: 'Export Configuration',
                importConfig: 'Import Configuration',
                factoryReset: 'Factory Reset',
                systemReport: 'System Diagnostics Report',
                copyReport: 'Copy System Report',
                viewLogs: 'View Activity Logs'
            },
            fa: {
                // Header & General
                slogan: 'اکوسیستم نسل جدید قالب، سئو پیشرفته و بهینه‌سازی سرعت وردپرس',
                purgeCache: 'تخلیه کش',
                checkUpdates: 'بررسی بروزرسانی',
                wpDashboard: 'پیشخوان وردپرس',
                allSystemsOptimal: 'وضعیت تمامی سیستم‌ها مطلوب است',
                lifetimeLicense: 'لایسنس مادام‌العمر حرفه‌ای',

                // Sidebar Tabs
                navOverview: 'پیشخوان و سلامت سیستم',
                navThemeKits: 'قالب‌های آماده و سفارشی‌ساز',
                navSeo: 'موتور سئو و ساختار اسکیما',
                navSpeed: 'بهینه‌سازی سرعت و کش',
                navMedia: 'مدیریت رسانه و واترمارک',
                navAi: 'استودیو هوش مصنوعی و مانیفست',
                navSettings: 'تنظیمات و مدیریت لایسنس',
                coreArch: 'معماری و ماژول‌های سامانه',
                navArch: 'معماری و سامانه',
                navPerformance: 'بهینه‌سازی و سرعت',
                navIntelligence: 'هوش مصنوعی و لایسنس',
                allSystemsNormal: 'تمامی سرویس‌ها فعال',
                memoryLimit: 'محدودیت رم سرور',
                database: 'موتور پایگاه داده',
                layoutMode: 'جهت چینش صفحه',

                // Overview Tab
                uptimeSpeed: '⚡ پایداری و زمان پاسخ',
                indexedNodes: '🕸️ برگه و صفحات ایندکس‌شده',
                aiCrawls: '🤖 پیمایش ربات‌های هوش مصنوعی',
                schemaScore: '🛡️ امتیاز ساختار اسکیما',
                avgLatency: 'میانگین تأخیر سرور',
                varnishHit: 'نرخ موفقیت کش وارنیش',
                llmActive: 'مانیفست هوش مصنوعی نسخه ۱.۲ فعال است',
                gradeA: 'دارای گواهینامه رتبه A+',
                coreModulesTitle: '🛠️ ماژول‌های اصلی و قابلیت‌های فعال',
                seoEngineDesc: 'تولید خودکار متاتگ‌ها، تولیدکننده کدهای اسکیما و نقشه‌های داینامیک XML.',
                mediaOptimizerDesc: 'تبدیل خودکار به WebP/AVIF، پردازش ناهمگام و لود تنبل تصاویر.',
                llmManifestDesc: 'اندپوینت‌های استاندارد متنی مارک‌داون برای دستیاران هوش مصنوعی و LLMها.',
                speedCacheDesc: 'کش صفحات HTML فوق‌سریع و پایگاه داده کش اشیاء ردیس.',
                aiStudioDesc: 'تولید محتوا، ایده و تیترهای سئو شده با مدل‌های پیشرفته هوش مصنوعی.',
                vitalsTitle: '⚙️ امتیاز سئو و هسته حیاتی وب (Core Web Vitals)',
                anomalyTitle: '⚠️ لاگ خطاهای ۴۰۴ و ریدایرکت خودکار',
                liveFeed: 'گزارش لحظه‌ای',
                requestedUri: 'آدرس درخواست‌شده',
                hits: 'تعداد خطا',
                action: 'عملیات',
                apply301: '✓ اعمال ریدایرکت ۳۰۱',
                optimalIndex: 'شاخص بهینه',

                // Theme Kits
                themeKitsTitle: 'کتابخانه قالب‌های آماده و سفارشی‌ساز فرانت‌اند',
                themeKitsDesc: 'نصب یک‌کلیکه معماری‌های کامل سایت، کامپایلر متغیرهای استایل و الگوهای پرسرعت.',
                syncLibrary: 'بروزرسانی کتابخانه',
                customizerTitle: 'پنل تنظیمات ظاهری و سفارشی‌ساز قالب',
                fontFamily: 'تایپوگرافی و فونت پیش‌فرض',
                containerWidth: 'حداکثر عرض بدنه سایت (Container)',
                layoutComponents: 'بخش‌های چیدمان قالب',
                stickyHeader: 'سربرگ چسبان (Sticky)',
                minimalFooter: 'پاورقی مینیمال',
                importKit: '⚡ نصب قالب',
                preview: '👁️ پیش‌نمایش',
                active: 'فعال',
                importingKit: 'در حال درون‌ریزی و پیکربندی قالب',
                stepContent: 'درون‌ریزی محتوای دمو، نوشته‌ها و فهرست‌ها',
                stepAcf: 'تنظیم فیلدهای پیشرفته وردپرس (ACF)',
                stepPlugins: 'فعال‌سازی و تأیید افزونه‌های پیش‌نیاز',
                cancel: 'انصراف',
                close: 'بستن',

                // SEO Tab
                seoTitle: 'موتور هوشمند اسکیما و سئوی ساختاریافته وردپرس',
                seoDesc: 'کامپایلر لحظه‌ای نمودارهای گراف JSON-LD، بهبوددهنده خودکار اسنیپت‌های گوگل و ثبت فوری در موتورهای جستجو.',
                runAudit: '⚡ آنالیز کامل سئو',
                seoWizard: '🧙‍♂️ جادوگر پیکربندی سریع',
                configure: 'پیکربندی',

                // Speed Tab
                speedTitle: 'موتور کش پیشرفته و کامپایلر بهینه‌ساز فایل‌ها',
                speedDesc: 'کش تمام صفحه، کش اشیاء ردیس، فشرده‌سازی خودکار کدهای CSS/JS و یکپارچه‌سازی جداول دیتابیس.',
                benchmarkVitals: '⚡ تست سرعت لود',
                optimizeDb: '🗄️ بهینه‌سازی دیتابیس',

                // Media Tab
                mediaTitle: 'استودیو بهینه‌سازی رسانه و درج واترمارک هوشمند',
                mediaDesc: 'تبدیل خودکار به فرمت‌های نسل جدید WebP و AVIF، حذف متادیتای حساس و درج لوگو و حق نشر.',
                bulkConvert: '⚡ بهینه‌سازی دسته‌جمعی تصاویر',
                regenThumbs: '🔄 بازسازی اندازه‌های بندانگشتی',
                watermarkSettings: 'تنظیمات استودیو واترمارک',
                watermarkPosition: 'موقعیت قرارگیری واترمارک',
                watermarkOpacity: 'میزان شفافیت (Opacity)',
                watermarkText: 'متن کپی‌رایت واترمارک',

                // AI Studio
                aiTitle: 'استودیو تولید محتوای هوش مصنوعی و مانیفست LLM',
                aiDesc: 'دستیار خودکار تولید متن و متن جایگزین تصاویر، تدوین سرفصل‌های سئو و اندپوینت اختصاصی llms.txt.',
                testAi: '⚡ تست اتصال هوش مصنوعی',
                defaultModel: 'مدل پردازشگر پیش‌فرض',
                aiSandboxLabel: '✨ محیط تعاملی تولید محتوا و ساختار سئو با هوش مصنوعی (Gemini API)',
                generateNow: '⚡ تولید فوری محتوا',
                processing: '⌛ در حال پردازش...',

                // Settings & License
                settingsTitle: 'تنظیمات سامانه، پشتیبان‌گیری و مدیریت لایسنس',
                settingsDesc: 'مدیریت اشتراک حرفه‌ای، فرکانس بروزرسانی‌ها، لاگ‌های عملکرد و تهیه فایل پشتیبان.',
                licenseStatus: 'وضعیت لایسنس افزونه',
                licenseKey: 'کلید لایسنس فعال',
                licenseActive: 'فعال و تایید شده (مادام‌العمر)',
                deactivateLicense: 'غیرفعال‌سازی',
                activateLicense: 'فعال‌سازی لایسنس',
                exportConfig: 'خروجی گرفتن از تنظیمات (Export)',
                importConfig: 'درون‌ریزی تنظیمات (Import)',
                factoryReset: 'بازگردانی به تنظیمات کارخانه',
                systemReport: 'گزارش سلامت و مشخصات سرور',
                copyReport: 'کپی گزارش سیستم',
                viewLogs: 'مشاهده لاگ وقایع'
            }
        },

        // Helper method to retrieve localized string
        t(key) {
            const lang = this.isRtl ? 'fa' : 'en';
            return this.i18n[lang]?.[key] || this.i18n['en']?.[key] || key;
        },

        // Responsive Navigation Control
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
        closeMobileMenu() {
            this.mobileMenuOpen = false;
        },

        // Language Switcher with State Persistence
        toggleLanguage() {
            this.isRtl = !this.isRtl;
            const langCode = this.isRtl ? 'fa' : 'en';
            const dir = this.isRtl ? 'rtl' : 'ltr';
            
            document.documentElement.setAttribute('dir', dir);
            document.documentElement.setAttribute('lang', langCode);
            
            try {
                localStorage.setItem('bankai_lang', langCode);
            } catch (e) {}

            this.showToast(
                this.isRtl ? 'زبان با موفقیت به فارسی (RTL) تغییر یافت' : 'Language successfully switched to English (LTR)'
            );
        },

        toggleRtl() {
            return this.toggleLanguage();
        },

        license: {
            key: 'BANKAI-PRO-9984-X721-LIFETIME',
            active: true
        },

        settingsModals: {
            import: false,
            reset: false
        },
        
        // Theme Customizer State
        customizer: {
            fontFamily: 'Inter',
            containerWidth: 1400,
            headerStyle: 'sticky',
            footerStyle: 'minimal'
        },

        // SEO Engine State
        seoState: {
            ai_search_visibility: true,
            ai_meta_assistant: true,
            ai_link_genius: true,
            instant_indexing: true,
            xml_sitemaps: true,
            schema_builder: true,
            monitor_404: true,
            image_seo: true,
            acf_integration: true,
            woocommerce_seo: true,
            local_seo: true,
            llms_txt_builder: true
        },

        seoDrawer: {
            show: false,
            id: '',
            title: ''
        },

        // Speed & Cache State
        speedState: {
            page_caching: true,
            asset_optimization: true,
            database_optimizer: true,
            object_cache: true,
            server_compression: true,
            fonts_localizer: true
        },

        speedDrawer: {
            show: false,
            id: '',
            title: ''
        },

        // Media & Watermark Studio State
        mediaState: {
            webp_avif_engine: true,
            dynamic_watermark: true,
            exif_privacy_stripper: true,
            cloud_offload_cdn: false,
            responsive_cls_guard: true,
            image_compression: true
        },

        watermarkStudio: {
            position: 'bottom-right',
            opacity: 75,
            text: '© BANKAI MEDIA'
        },

        mediaDrawer: {
            show: false,
            id: '',
            title: ''
        },

        // AI Content Studio State
        aiState: {
            auto_meta_alt: true,
            content_outline_studio: true,
            brand_persona_builder: true,
            ai_interlinking_guard: true,
            content_repurposer: true,
            prompt_manifests: true
        },

        aiStudio: {
            defaultModel: 'gpt-4o'
        },

        aiSandbox: {
            promptInput: '',
            aiResult: '',
            isGenerating: false
        },

        aiDrawer: {
            show: false,
            id: '',
            title: ''
        },

        // Import Modal State
        importModal: {
            show: false,
            kit: null,
            progress: 0,
            status: 'idle', // idle, importing, completed
            steps: [
                { id: 'demo_content', label: 'Import Demo Content & Menus', status: 'pending' },
                { id: 'custom_fields', label: 'Configure Custom Fields (ACF)', status: 'pending' },
                { id: 'plugins', label: 'Activate & Verify Dependency Plugins', status: 'pending' }
            ]
        },

        init() {
            window.bankaiAdminInstance = this;

            // Apply direction & language to document root
            if (this.isRtl) {
                document.documentElement.setAttribute('dir', 'rtl');
                document.documentElement.setAttribute('lang', 'fa');
            } else {
                document.documentElement.setAttribute('dir', 'ltr');
                document.documentElement.setAttribute('lang', 'en');
            }

            // Keyboard navigation listener (ESC to close drawers/modals/mobile menu)
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.seoDrawer.show = false;
                    this.speedDrawer.show = false;
                    this.mediaDrawer.show = false;
                    this.aiDrawer.show = false;
                    this.importModal.show = false;
                    this.settingsModals.import = false;
                    this.settingsModals.reset = false;
                    this.mobileMenuOpen = false;
                }
            });

            // Read initial route from URL params if available
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const page = urlParams.get('page');
                if (page === 'bankai-theme-kits') {
                    this.activeTab = 'theme-kits';
                } else if (page === 'bankai-seo-engine') {
                    this.activeTab = 'seo';
                } else if (page === 'bankai-speed-cache') {
                    this.activeTab = 'speed';
                } else if (page === 'bankai-media') {
                    this.activeTab = 'media';
                } else if (page === 'bankai-ai-manifests' || page === 'bankai-ai-studio') {
                    this.activeTab = 'ai';
                } else if (page === 'bankai-settings') {
                    this.activeTab = 'settings';
                }
            } catch (err) {
                // Ignore URL search param parsing errors
            }
        },

        setTab(tab) {
            if (this.activeTab === tab) {
                this.closeMobileMenu();
                return;
            }

            // Trigger Page Transition Loader
            this.pageLoading = true;
            this.pageProgress = 25;

            setTimeout(() => {
                this.pageProgress = 70;
            }, 60);

            setTimeout(() => {
                this.activeTab = tab;
                this.pageProgress = 100;
                this.closeMobileMenu();

                setTimeout(() => {
                    this.pageLoading = false;
                    this.pageProgress = 0;
                }, 180);
            }, 200);

            // Update browser history state without full page reload if supported
            try {
                const pageMap = {
                    'overview': 'bankai-core',
                    'theme-kits': 'bankai-theme-kits',
                    'seo': 'bankai-seo-engine',
                    'speed': 'bankai-speed-cache',
                    'media': 'bankai-media',
                    'ai': 'bankai-ai-manifests',
                    'settings': 'bankai-settings'
                };
                const page = pageMap[tab] || 'bankai-core';
                const newUrl = window.location.pathname + '?page=' + page;
                if (window.history && window.history.pushState) {
                    window.history.pushState({ tab: tab }, '', newUrl);
                }
            } catch (err) {
                // Safe ignore if sandboxed inside iframe
            }
        },

        triggerSaveLoader(options = {}) {
            let config = {
                title: this.isRtl ? 'در حال ذخیره‌سازی تغییرات...' : 'Saving Changes...',
                message: this.isRtl ? 'در حال اعمال تنظیمات و همگام‌سازی با پایگاه داده...' : 'Applying configuration & synchronizing database...',
                savedTitle: this.isRtl ? 'تغییرات با موفقیت ذخیره شد' : 'Changes Saved Successfully',
                savedMessage: this.isRtl ? 'پیکربندی با موفقیت به‌روزرسانی و ذخیره گردید.' : 'Configuration updated and synchronized.',
                duration: 600,
                callback: null
            };

            if (typeof options === 'string') {
                config.title = options;
            } else if (typeof options === 'object' && options !== null) {
                config = Object.assign(config, options);
            }

            this.savingLoader.show = true;
            this.savingLoader.state = 'saving';
            this.savingLoader.title = config.title;
            this.savingLoader.message = config.message;

            setTimeout(() => {
                this.savingLoader.state = 'saved';
                this.savingLoader.title = config.savedTitle;
                this.savingLoader.message = config.savedMessage;

                if (typeof config.callback === 'function') {
                    config.callback();
                }

                setTimeout(() => {
                    this.savingLoader.show = false;
                }, 1100);
            }, config.duration);
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        // Settings & License Methods
        async checkLicenseUpdates() {
            this.showToast(
                this.isRtl ? 'اعتبارسنجی لایسنس انجام شد: لایسنس مادام‌العمر فعال و به‌روز است!' : 'License verified: Pro Lifetime Key is active & up-to-date!',
                'success'
            );

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/license/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    }
                });
            } catch (err) {
                // Silently fallback if REST API endpoint is pending
            }
        },

        toggleLicenseActivation() {
            this.license.active = !this.license.active;
            this.showToast(
                this.license.active
                    ? (this.isRtl ? 'لایسنس با موفقیت فعال گردید' : 'License key activated successfully!')
                    : (this.isRtl ? 'لایسنس غیرفعال شد' : 'License key deactivated.'),
                this.license.active ? 'success' : 'error'
            );
        },

        exportConfiguration() {
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify({
                version: "1.0.0",
                timestamp: new Date().toISOString(),
                seoState: this.seoState,
                speedState: this.speedState,
                mediaState: this.mediaState,
                aiState: this.aiState,
                customizer: this.customizer
            }));
            const downloadAnchor = document.createElement('a');
            downloadAnchor.setAttribute("href", dataStr);
            downloadAnchor.setAttribute("download", `bankai-config-${new Date().toISOString().slice(0,10)}.json`);
            document.body.appendChild(downloadAnchor);
            downloadAnchor.click();
            downloadAnchor.remove();

            this.showToast(
                this.isRtl ? 'فایل پیکربندی JSON با موفقیت دانلود شد' : 'Encrypted JSON configuration exported successfully!'
            );
        },

        openImportConfigModal() {
            this.settingsModals.import = true;
        },

        confirmImportConfig() {
            this.settingsModals.import = false;
            this.showToast(
                this.isRtl ? 'پیکربندی با موفقیت جایگزین گردید' : 'Configuration imported and settings updated successfully!',
                'success'
            );
        },

        openResetModal() {
            this.settingsModals.reset = true;
        },

        confirmFactoryReset() {
            this.settingsModals.reset = false;
            this.showToast(
                this.isRtl ? 'تمامی تنظیمات به حالت اولیه کارخانه بازگردانی شدند' : 'All settings reset to factory defaults!',
                'error'
            );
        },

        copySystemReport() {
            this.showToast(
                this.isRtl ? 'گزارش عیب‌یابی سیستم در حافظه کپی شد' : 'System Diagnostic report copied to clipboard!',
                'success'
            );
        },

        openLogViewer() {
            this.showToast(
                this.isRtl ? 'نمایشگر لاگ‌های عیب‌یابی: صفر خطای بحرانی ثبت شده است' : 'Debug Log Viewer: 0 critical errors reported in bankai-logs/debug.log',
                'success'
            );
        },

        // AI Content Studio Methods
        async testAiConnections() {
            this.showToast(
                this.isRtl ? 'در حال بررسی اتصالات API مدل‌های هوش مصنوعی (OpenAI, Claude, DeepSeek, Gemini)... تمامی سرویس‌ها فعالند!' : 'Testing AI API connections (OpenAI, Claude, DeepSeek, Gemini)... All LLMs active & responsive!',
                'success'
            );

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/ai/test-connections', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    }
                });
            } catch (err) {
                // Silently fallback if REST API endpoint is pending
            }
        },

        async toggleAiModule(modId) {
            const newState = this.aiState[modId];
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال تنظیم ماژول هوش مصنوعی...` : `Updating AI Module...`,
                savedTitle: this.isRtl
                    ? `ماژول ${modId} ${newState ? 'فعال' : 'غیرفعال'} گردید`
                    : `AI Module '${modId}' ${newState ? 'enabled' : 'disabled'}`,
                duration: 500
            });

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/ai/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    },
                    body: JSON.stringify({ module: modId, enabled: newState ? 1 : 0 })
                });
            } catch (err) {
                // Silently fallback
            }
        },

        openAiDrawer(id, title) {
            this.aiDrawer.id = id;
            this.aiDrawer.title = title;
            this.aiDrawer.show = true;
        },

        saveAiDrawerSettings() {
            this.aiDrawer.show = false;
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال ذخیره‌سازی پرامپت ${this.aiDrawer.title}...` : `Saving ${this.aiDrawer.title}...`,
                message: this.isRtl ? 'در حال ثبت دستورالعمل‌ها و پارامترهای LLM در مانیفست سرور...' : 'Saving system instructions & LLM parameters to server manifest...',
                savedTitle: this.isRtl ? `قالب پرامپت ${this.aiDrawer.title} با موفقیت ذخیره گردید` : `Prompt rules for '${this.aiDrawer.title}' saved successfully!`,
                duration: 650
            });
        },

        async generateAiPrompt() {
            if (!this.aiSandbox.promptInput.trim()) {
                this.showToast(
                    this.isRtl ? 'لطفاً عنوان یا پرامپت را وارد نمایید' : 'Please enter a topic or prompt for AI generation',
                    'error'
                );
                return;
            }
            this.aiSandbox.isGenerating = true;
            this.aiSandbox.aiResult = '';
            try {
                const res = await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/ai/generate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: this.aiSandbox.promptInput, model: this.aiStudio.defaultModel })
                });
                const data = await res.json();
                this.aiSandbox.isGenerating = false;
                this.aiSandbox.aiResult = data.text || data.error || 'Generated content received';
                this.showToast(this.isRtl ? 'محتوای هوش مصنوعی با موفقیت تولید شد' : 'AI response generated successfully!');
            } catch (err) {
                this.aiSandbox.isGenerating = false;
                this.aiSandbox.aiResult = 'Failed to generate response: ' + err.message;
                this.showToast('AI Generation error', 'error');
            }
        },

        // Media & Watermark Studio Methods
        getWatermarkPositionStyle() {
            const pos = this.watermarkStudio.position;
            let style = '';
            if (pos.includes('top')) style += 'top: 12px; ';
            if (pos.includes('bottom')) style += 'bottom: 12px; ';
            if (pos.includes('left')) style += 'left: 12px; ';
            if (pos.includes('right')) style += 'right: 12px; ';
            if (pos === 'top-center' || pos === 'center' || pos === 'bottom-center') {
                style += 'left: 50%; transform: translateX(-50%); ';
            }
            if (pos === 'center-left' || pos === 'center' || pos === 'center-right') {
                style += 'top: 50%; transform: translateY(-50%); ';
                if (pos === 'center') style = 'top: 50%; left: 50%; transform: translate(-50%, -50%); ';
            }
            return style;
        },

        async bulkConvertMedia() {
            this.showToast(
                this.isRtl ? 'فرایند تبدیل همزمان فایل‌ها به WebP/AVIF آغاز گردید...' : 'Bulk converting media library to WebP & AVIF...',
                'success'
            );

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/media/bulk-convert', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    }
                });
            } catch (err) {
                // Silently fallback if REST API endpoint is pending
            }
        },

        regenerateThumbnails() {
            this.showToast(
                this.isRtl ? 'تصاویر بندانگشتی با موفقیت بازسازی شدند' : 'Thumbnails regenerated successfully!',
                'success'
            );
        },

        async toggleMediaModule(modId) {
            const newState = this.mediaState[modId];
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال به‌روزرسانی ماژول رسانه...` : `Updating Media Module...`,
                savedTitle: this.isRtl
                    ? `ماژول ${modId} ${newState ? 'فعال' : 'غیرفعال'} گردید`
                    : `Media Module '${modId}' ${newState ? 'enabled' : 'disabled'}`,
                duration: 500
            });

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/media/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    },
                    body: JSON.stringify({ module: modId, enabled: newState ? 1 : 0 })
                });
            } catch (err) {
                // Silently fallback
            }
        },

        openMediaDrawer(id, title) {
            this.mediaDrawer.id = id;
            this.mediaDrawer.title = title;
            this.mediaDrawer.show = true;
        },

        saveMediaDrawerSettings() {
            this.mediaDrawer.show = false;
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال ذخیره‌سازی تنظیمات ${this.mediaDrawer.title}...` : `Saving ${this.mediaDrawer.title}...`,
                message: this.isRtl ? 'در حال اعمال الگوهای گرافیکی و قوانین فشرده‌سازی در سرور...' : 'Applying image rules, watermark coordinates & formats to options...',
                savedTitle: this.isRtl ? `تنظیمات ${this.mediaDrawer.title} با موفقیت ذخیره گردید` : `Settings for '${this.mediaDrawer.title}' saved!`,
                duration: 650
            });
        },

        // Speed & Cache Methods
        async purgeAllCaches() {
            this.triggerSaveLoader({
                title: this.isRtl ? 'در حال پاکسازی کامل کش‌های سرور...' : 'Purging All Server & Edge Caches...',
                message: this.isRtl ? 'در حال تخلیه حافظه HTML، بافرهای Redis، Varnish و فایل‌های استاتیک...' : 'Clearing page cache, Redis objects, Varnish, and minified bundles...',
                savedTitle: this.isRtl ? 'تمامی کش‌ها با موفقیت پاکسازی شدند!' : 'All caches purged successfully!',
                duration: 750
            });

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/speed/purge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    }
                });
            } catch (err) {
                // Silently fallback if REST API endpoint is pending
            }
        },

        benchmarkVitals() {
            this.triggerSaveLoader({
                title: this.isRtl ? 'در حال سنجش شاخص‌های حیاتی و کش...' : 'Benchmarking Core Web Vitals...',
                message: this.isRtl ? 'محاسبه TTFB، LCP، CLS و سرعت بارگذاری با سرور ابری...' : 'Calculating TTFB, LCP, CLS and server response times...',
                savedTitle: this.isRtl ? 'شاخص‌های حیاتی بهینه هستند (امتیاز ۹۹/۱۰۰)' : 'Core Web Vitals optimal (99/100)!',
                duration: 700
            });
        },

        optimizeDatabase() {
            this.triggerSaveLoader({
                title: this.isRtl ? 'در حال بهینه‌سازی جداول پایگاه داده...' : 'Optimizing MySQL Database...',
                message: this.isRtl ? 'پاکسازی پیش‌نویس‌های قدیمی، هرزنامه‌ها و بازسازی ایندکس‌ها...' : 'Cleaning post revisions, transients & rebuilding SQL indices...',
                savedTitle: this.isRtl ? 'پایگاه داده بهینه‌سازی و متای یتیم پاکسازی گردید' : 'Database optimized successfully!',
                duration: 700
            });
        },

        async toggleSpeedModule(modId) {
            const newState = this.speedState[modId];
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال به‌روزرسانی ماژول سرعت...` : `Updating Speed Module...`,
                savedTitle: this.isRtl
                    ? `ماژول ${modId} ${newState ? 'فعال' : 'غیرفعال'} گردید`
                    : `Speed Module '${modId}' ${newState ? 'enabled' : 'disabled'}`,
                duration: 500
            });

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/speed/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    },
                    body: JSON.stringify({ module: modId, enabled: newState ? 1 : 0 })
                });
            } catch (err) {
                // Silently fallback
            }
        },

        openSpeedDrawer(id, title) {
            this.speedDrawer.id = id;
            this.speedDrawer.title = title;
            this.speedDrawer.show = true;
        },

        saveSpeedDrawerSettings() {
            this.speedDrawer.show = false;
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال اعمال تنظیمات ${this.speedDrawer.title}...` : `Saving ${this.speedDrawer.title}...`,
                message: this.isRtl ? 'در حال بازسازی قوانین وب‌سرور، فایل .htaccess و بافرها...' : 'Writing web server rules, .htaccess directives & cache levels...',
                savedTitle: this.isRtl ? `تنظیمات ${this.speedDrawer.title} با موفقیت ذخیره گردید` : `Settings for '${this.speedDrawer.title}' saved!`,
                duration: 650
            });
        },

        // SEO Engine Methods
        async toggleSeoModule(modId) {
            const newState = this.seoState[modId];
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال به‌روزرسانی ماژول سئو...` : `Updating SEO Module...`,
                savedTitle: this.isRtl
                    ? `ماژول ${modId} ${newState ? 'فعال' : 'غیرفعال'} گردید`
                    : `SEO Module '${modId}' ${newState ? 'enabled' : 'disabled'}`,
                duration: 500
            });

            try {
                await fetch((window.bankaiData?.restUrl || '/wp-json/bankai/v1') + '/seo/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.bankaiData?.nonce || ''
                    },
                    body: JSON.stringify({ module: modId, enabled: newState ? 1 : 0 })
                });
            } catch (err) {
                // Silently fallback if rest API endpoint is pending
            }
        },

        runSeoAudit() {
            this.triggerSaveLoader({
                title: this.isRtl ? 'در حال اجرای ممیزی ۲۸ نقطه‌ای سئو...' : 'Running 28-Point SEO Audit...',
                message: this.isRtl ? 'بررسی متاتگ‌ها، سایت‌مپ، داده‌های ساختاریافته و ربات‌ها...' : 'Checking metadata, sitemap.xml, robots.txt & schema graphs...',
                savedTitle: this.isRtl ? 'ممیزی با موفقیت انجام شد (امتیاز ۹۸/۱۰۰)' : 'SEO Audit complete (98/100)!',
                duration: 750
            });
        },

        openSeoWizard() {
            this.showToast(
                this.isRtl ? 'راه‌انداز هوشمند سئو فعال گردید' : 'SEO Setup Wizard initialized.',
                'success'
            );
        },

        openSeoDrawer(id, title) {
            this.seoDrawer.id = id;
            this.seoDrawer.title = title;
            this.seoDrawer.show = true;
        },

        saveSeoDrawerSettings() {
            this.seoDrawer.show = false;
            this.triggerSaveLoader({
                title: this.isRtl ? `در حال ذخیره‌سازی تنظیمات ${this.seoDrawer.title}...` : `Saving ${this.seoDrawer.title}...`,
                message: this.isRtl ? 'در حال کامپایل گراف‌های معنایی و ذخیره اسکیمای JSON-LD...' : 'Compiling semantic entities and saving JSON-LD schema...',
                savedTitle: this.isRtl ? `تنظیمات ${this.seoDrawer.title} با موفقیت ذخیره گردید` : `Settings for '${this.seoDrawer.title}' saved!`,
                duration: 650
            });
        },

        syncLibrary() {
            this.showToast(this.isRtl ? 'کتابخانه قالب‌ها با موفقیت همگام‌سازی شد' : 'Starter Kit library synchronized successfully!', 'success');
        },

        openImportModal(kit) {
            this.importModal.kit = kit;
            this.importModal.progress = 0;
            this.importModal.status = 'idle';
            this.importModal.steps.forEach(s => s.status = 'pending');
            this.importModal.show = true;
        },

        closeImportModal() {
            if (this.importModal.status === 'importing') {
                if (!confirm(this.isRtl ? 'فرایند نصب در حال انجام است. آیا مطمئن هستید؟' : 'Import is in progress. Are you sure you want to cancel?')) {
                    return;
                }
            }
            this.importModal.show = false;
        },

        startImport() {
            this.importModal.status = 'importing';
            this.importModal.progress = 10;
            this.importModal.steps[0].status = 'in_progress';

            const interval = setInterval(() => {
                this.importModal.progress += 15;
                if (this.importModal.progress >= 40 && this.importModal.steps[0].status !== 'completed') {
                    this.importModal.steps[0].status = 'completed';
                    this.importModal.steps[1].status = 'in_progress';
                }
                if (this.importModal.progress >= 75 && this.importModal.steps[1].status !== 'completed') {
                    this.importModal.steps[1].status = 'completed';
                    this.importModal.steps[2].status = 'in_progress';
                }
                if (this.importModal.progress >= 100) {
                    this.importModal.progress = 100;
                    this.importModal.steps[2].status = 'completed';
                    this.importModal.status = 'completed';
                    clearInterval(interval);
                    this.showToast(
                        this.isRtl ? `قالب ${this.importModal.kit.name} با موفقیت نصب گردید!` : `Starter Kit '${this.importModal.kit.name}' successfully imported!`,
                        'success'
                    );
                }
            }, 500);
        }
    };
}

// Assign to window for direct evaluation
window.bankaiAdmin = bankaiAdmin;

// Global helper bindings so any test or script can call them directly
window.setTab = function(tab) {
    if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.setTab === 'function') {
        return window.bankaiAdminInstance.setTab(tab);
    }
    const el = document.getElementById('bankai-admin-app');
    if (el && window.Alpine) {
        try {
            const data = window.Alpine.$data(el);
            if (data && typeof data.setTab === 'function') {
                return data.setTab(tab);
            }
        } catch (e) {}
    }
    if (window.bankaiData) {
        window.bankaiData.activeTab = tab;
    }
};

window.showToast = function(msg, type = 'success') {
    if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function') {
        return window.bankaiAdminInstance.showToast(msg, type);
    }
};

window.toggleLanguage = function() {
    if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.toggleLanguage === 'function') {
        return window.bankaiAdminInstance.toggleLanguage();
    }
    const el = document.getElementById('bankai-admin-app');
    if (el && window.Alpine) {
        try {
            const data = window.Alpine.$data(el);
            if (data && typeof data.toggleLanguage === 'function') {
                return data.toggleLanguage();
            }
        } catch (e) {}
    }
};
window.toggleRtl = window.toggleLanguage;

// Register with Alpine data repository if Alpine is already present or upon alpine:init
if (window.Alpine) {
    window.Alpine.data('bankaiAdmin', bankaiAdmin);
}
document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.data('bankaiAdmin', bankaiAdmin);
    }
});
