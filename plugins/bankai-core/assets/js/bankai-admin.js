/**
 * Bankai Admin — Alpine.js root (SEO + Speed + module sync)
 */
(function () {
    'use strict';

    function cfg() {
        return window.bankaiData || window.bankaiCoreData || {};
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

    function selfHexRgba(hex, alpha) {
        hex = (hex || '#000000').replace('#','');
        if (hex.length === 3) hex = hex.split('').map(function(c){return c+c;}).join('');
        var r = parseInt(hex.slice(0,2),16)||0, g = parseInt(hex.slice(2,4),16)||0, b = parseInt(hex.slice(4,6),16)||0;
        return 'rgba('+r+','+g+','+b+','+alpha+')';
    }
    function ajax(action, data, timeoutMs) {
        var c = cfg();
        var body = new FormData();
        body.append('action', action);
        body.append('nonce', c.adminNonce || '');
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
        return fetch(c.ajaxUrl || window.ajaxurl || '', {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
            signal: controller ? controller.signal : undefined
        }).then(function (r) {
            return r.json().catch(function () {
                return { success: false, data: { message: 'Invalid JSON response' } };
            });
        }).catch(function (err) {
            var msg = (err && err.name === 'AbortError')
                ? 'Request timed out'
                : ((err && err.message) || 'Network error');
            return { success: false, data: { message: msg } };
        }).finally(function () {
            if (timer) clearTimeout(timer);
        });
    }

    var i18nFa = {
        navOverview: 'پیشخوان و سلامت',
        coreModulesTitle: 'موتورهای اصلی بنکای',
        navTheme: 'تقویم جلالی',
        navSeo: 'موتور سئو و اسکیما',
        navSpeed: 'کش و سرعت',
        navMedia: 'رسانه و واترمارک',
        navAi: 'استودیو هوش مصنوعی',
        navSettings: 'تنظیمات و لایسنس',
        allSystemsNormal: 'همه سیستم‌ها عادی',
        memoryLimit: 'حافظه',
        seoEngineTitle: 'موتور سئو و معماری اسکیما',
        seoEngineSubtitle: 'بهینه‌سازی کلیدواژه، اسکیما، ایندکس و LLM',
        speedEngineTitle: 'شتاب‌دهنده کش و سرعت',
        speedEngineSubtitle: 'کش صفحه زیر ۵۰ms، CSS بحرانی، Redis و بهینه‌سازی دیتابیس',
        runAudit: 'آنالیز کامل سئو',
        seoWizard: 'جادوگر پیکربندی سریع',
        benchmarkVitals: 'بنچمارک Core Web Vitals',
        purgeCaches: 'تخلیه تمام کش‌ها',
        purgeCache: 'تخلیه کش',
        active: 'فعال',
        disabled: 'غیرفعال',
        configure: 'پیکربندی',
        cancel: 'انصراف',
        save: 'ذخیره',
        moduleDisabled: 'این ماژول غیرفعال است — از پیشخوان فعال کنید',
        saving: 'در حال ذخیره…',
        saved: 'ذخیره شد',
        error: 'خطا',
        purging: 'در حال تخلیه کش…',
        purged: 'کش با موفقیت تخلیه شد',
        optimizing: 'در حال بهینه‌سازی دیتابیس…',
        optimized: 'دیتابیس بهینه شد',
        benchmarking: 'در حال اندازه‌گیری…',
        mediaEngineTitle: 'موتور رسانه و واترمارک',
        mediaEngineSubtitle: 'WebP، واترمارک، EXIF و Lazy Load',
        regenThumbs: 'بازتولید بندانگشتی',
        bulkConvert: 'تبدیل گروهی',
        aiStudioTitle: 'استودیو هوش مصنوعی',
        aiStudioSubtitle: 'اتصال واقعی Multi-LLM',
        testApis: 'تست اتصال',
        generateNow: 'تولید',
        generating: 'در حال تولید…',
    };

    var i18nEn = {
        navOverview: 'Dashboard & Health',
        coreModulesTitle: 'Bankai Core Engines',
        navTheme: 'Jalali Calendar',
        navSeo: 'SEO & Schema Engine',
        navSpeed: 'Speed & Cache',
        navMedia: 'Media & Watermark',
        navAi: 'AI Studio',
        navSettings: 'Settings & License',
        allSystemsNormal: 'All Systems Normal',
        memoryLimit: 'Memory Limit',
        seoEngineTitle: 'Autonomous SEO & Schema',
        seoEngineSubtitle: 'AI keywords, schemas, indexing & LLM visibility',
        speedEngineTitle: 'Autonomous Speed & Cache',
        speedEngineSubtitle: 'Sub-50ms page cache, critical CSS, Redis & DB optimize',
        runAudit: 'Run SEO Audit',
        seoWizard: 'SEO Setup Wizard',
        benchmarkVitals: 'Benchmark Core Web Vitals',
        purgeCaches: 'Purge All Caches',
        purgeCache: 'Purge Cache',
        active: 'Active',
        disabled: 'Disabled',
        configure: 'Configure',
        cancel: 'Cancel',
        save: 'Save',
        moduleDisabled: 'Module disabled — enable from Dashboard',
        saving: 'Saving…',
        saved: 'Saved',
        error: 'Error',
        purging: 'Purging caches…',
        purged: 'All caches purged',
        optimizing: 'Optimizing database…',
        optimized: 'Database optimized',
        benchmarking: 'Measuring…',
        mediaEngineTitle: 'Media & Watermark',
        mediaEngineSubtitle: 'WebP, watermark, EXIF & lazy load',
        regenThumbs: 'Regenerate Thumbnails',
        bulkConvert: 'Bulk Convert',
        aiStudioTitle: 'AI Studio',
        aiStudioSubtitle: 'Real Multi-LLM orchestration',
        testApis: 'Test APIs',
        generateNow: 'Generate',
        generating: 'Generating…',
    };

    window.bankaiAdmin = function bankaiAdmin() {
        var initial = cfg();
        var st = window.bankaiState || {};

        var seoState = {};
        (st.seoModules || []).forEach(function (m) {
            seoState[m.id] = m.enabled !== false;
        });

        var speedState = {};
        (st.speedModules || []).forEach(function (m) {
            speedState[m.id] = m.enabled !== false;
        });

        var mediaState = {};
        (st.mediaModules || []).forEach(function (m) {
            mediaState[m.id] = m.enabled !== false;
        });

        var wmSaved = st.watermarkSettings || {};
        var watermarkStudio = {
            custom_fonts: (window.bankaiWmCustomFonts || []),
            custom_font_path: '',
            enabled: wmSaved.enabled !== false,
            type: wmSaved.type || 'text',
            position: wmSaved.position || 'bottom-right',
            opacity: wmSaved.opacity || 75,
            text: wmSaved.text || '© BANKAI MEDIA',
            text_size_type: wmSaved.text_size_type || 'scale',
            text_scale_percent: wmSaved.text_scale_percent || 3.5,
            min_font_size: wmSaved.min_font_size || 12,
            max_font_size: wmSaved.max_font_size || 72,
            margin_percent: wmSaved.margin_percent != null ? wmSaved.margin_percent : 2.0,
            text_size: wmSaved.text_size || 18,
            text_color: wmSaved.text_color || '#FFFFFF',
            text_font: wmSaved.text_font || 'default',
            box_enabled: wmSaved.box_enabled !== false,
            box_padding: wmSaved.box_padding || 8,
            box_radius: wmSaved.box_radius || 6,
            box_color: wmSaved.box_color || '#000000',
            box_opacity: wmSaved.box_opacity != null ? wmSaved.box_opacity : 45,
            box_style: wmSaved.box_style || 'solid',
            text_cover: wmSaved.text_cover || 'none',
            custom_font_url: wmSaved.custom_font_url || '',
            custom_font_name: wmSaved.custom_font_name || '',
            preview_url: wmSaved.preview_url || '',
            _preview: 0,
            image_id: wmSaved.image_id || 0,
            image_url: wmSaved.image_url || '',
            image_scale: wmSaved.image_scale || 20,
            quality: wmSaved.quality || 82,
            min_dimension: wmSaved.min_dimension || 0,
            exclude_patterns: wmSaved.exclude_patterns || "logo\nbanner\nicon\nsprite\nfavicon",
            apply_upload: wmSaved.apply_upload !== false,
            apply_content: !!wmSaved.apply_content,
            lazy_load: wmSaved.lazy_load !== false,
            strip_exif: wmSaved.strip_exif !== false,
            convert_webp: wmSaved.convert_webp !== false,
            keep_original: wmSaved.keep_original !== false
        };

        var moduleActive = {
            seo_engine: true,
            ai_studio: true,
            speed_cache: true,
            media_watermark: true,
            jalali_calendar: true,
            pwa: true,
            llms_txt: true
        };
        (st.coreModules || []).forEach(function (m) {
            if (m && m.key) {
                // Respect server active flag (do not force everything ON)
                moduleActive[m.key] = m.active !== false && m.active !== 0 && m.active !== '0';
            }
        });

        return {
            activeTab: initial.activeTab || 'overview',
            isRtl: !!initial.isRtl,
            mobileMenuOpen: false,
            pageLoading: false,
            pageProgress: 0,
            savingLoader: { show: false, state: 'saving', title: '', message: '' },
            toast: { show: false, message: '', type: 'success' },
            busy: false,

            seoState: seoState,
            speedState: speedState,
            mediaState: mediaState,
            watermarkStudio: watermarkStudio,
            mediaDrawer: { show: false, id: '', title: '' },
            mediaSubTab: 'watermark',
            wmFontBusy: false,
            wmBulkModal: { show: false, running: false, progress: 0, progressText: '', items: [], title: '', alsoCompress: false },

            compressOpts: { format: 'webp', quality: 82, maxWidth: 1600 },
            mediaLib: {
                mode: 'compress',
                items: [],
                page: 1,
                hasMore: false,
                loading: false,
                busy: false,
                progress: 0,
                progressText: '',
                doneCount: 0,
                totalCount: 0,
                savedBytes: 0
            },
            moduleActive: moduleActive,
            dashVisitsLoading: false,
            cdnModal: { show: false, saving: false },
            cdnSettings: { provider: 'custom', base_url: '', path_prefix: '', access_key: '', secret_key: '', bucket: '', rewrite_urls: true },
            redisSettings: { enabled: false, host: '127.0.0.1', port: 6379, password: '', db: 0, prefix: 'bankai:' },
            redisStatus: { checked: false, ok: false, message: '' },
            redisSaving: false,

            dashVisits: { total: 0, change: 0, desktop: 0, mobile: 0, tablet: 0, error: '' },
            coreModuleCards: [
                { key: 'seo_engine', title: 'SEO Engine', title_fa: 'موتور سئو و اسکیما', desc: 'Meta, schema, sitemap, canonical', desc_fa: 'متا، اسکیما، نقشه سایت، کانونیکال و ممیزی سئو' },
                { key: 'ai_studio', title: 'AI Studio', title_fa: 'استودیو هوش مصنوعی', desc: 'Text & image AI providers', desc_fa: 'اتصال موتورهای متنی و تصویری برای تولید محتوا و سئو' },
                { key: 'speed_cache', title: 'Cache & Speed', title_fa: 'کش و سرعت', desc: 'Page cache, assets, DB optimize', desc_fa: 'کش صفحه، بهینه‌سازی فایل‌ها و پایگاه‌داده' },
                { key: 'media_watermark', title: 'Media & Watermark', title_fa: 'رسانه و واترمارک', desc: 'Compress, WebP, watermark', desc_fa: 'فشرده‌سازی، WebP، واترمارک و حذف EXIF' },
                { key: 'jalali_calendar', title: 'Jalali Calendar', title_fa: 'تقویم جلالی', desc: 'Persian dates across admin', desc_fa: 'نمایش و انتخاب تاریخ شمسی در کل وردپرس' },
                { key: 'pwa', title: 'Progressive Web App', title_fa: 'اپلیکیشن وب پیشرفته (PWA)', desc: 'Installable app, offline, manifest', desc_fa: 'نصب‌پذیر، آفلاین، مانیفست و Service Worker — تنظیمات در سئوی سایت' }
            ],

            seoDrawer: { show: false, id: '', title: '' },
            seoAudit: { show: false, running: false, score: 0, items: [] },
            seoWizard: { show: false, step: 1 },

            /* Articles list (SEO tab) */
            articles: [],
            page: 1,
            totalPages: 1,
            total: 0,
            searchQ: '',
            perPage: 25,
            orderby: 'modified',
            order: 'DESC',
            seoPanel: 'tools',
            pageAudit: {
                q: '',
                url: '',
                postId: 0,
                posts: [],
                running: false,
                result: null,
                summary: null,
                engines: [],
                score: 0,
                tab: 'seo',
                error: ''
            },
            seoImport: {
                sources: {},
                importableTotal: 0,
                overwrite: false,
                prefer: 'auto',
                running: false,
                progress: 0,
                page: 0,
                totalPages: 0,
                importedCount: 0,
                skippedCount: 0,
                stepText: '',
                report: false,
                missing: [],
                missingTotal: 0,
                missingPage: 1,
                missingPages: 1,
                selected: [],
                allSelected: false,
                aiRunning: false,
                aiProgress: 0,
                aiAbort: false,
                textProvider: '',
                imageProvider: '',
                textProviders: [],
                imageProviders: [],
                direction: 'import',
                phase: 'pick',
                selectedSource: '',
                cards: [],
                queue: [],
                queueTotal: 0,
                processed: 0,
                done: false,
                doneTitle: '',
                doneMessage: ''
            },
            wizardBusy: false,
            loading: false,
            fixedList: [],
            fixedKeywordsRaw: '',
            fixedKeywordInput: '',
            savingFixed: false,
            aiBusyId: 0,
            edit: { open: false, row: null, seo_title: '', description: '', focus_keyword: '', keywords_str: '', saving: false, ai: false },
            gaPropertyId: '',
            gaConnected: false,

            speedDrawer: { show: false, id: '', title: '', ttl: 86400, exclusions: '/cart/*\n/checkout/*\n/my-account/*' },
            speedVitals: st.speedVitals || { ttfb: '—', lcp: '—', cls: '—', fid: '—', score: 0 },
            speedStats: st.speedStats || { hit_ratio: '—', ttfb: '—', redis_latency: '—', revisions: 0 },

            integSaving: {},
            integEdit: {},

            init: function () {
                window.bankaiAdminInstance = this;
                try {
                    var t = this.activeTab || 'overview';
                    document.querySelectorAll('#bankai-admin-app .bankai-tab-pane').forEach(function (el) {
                        var on = (el.id === 'tab-' + t);
                        el.classList.toggle('is-active-tab', on);
                        if (on) el.removeAttribute('x-cloak');
                    });
                } catch (e) {}
            },

            t: function (key) {
                var map = this.isRtl ? i18nFa : i18nEn;
                return map[key] || key;
            },

            toggleMobileMenu: function () {
                this.mobileMenuOpen = !this.mobileMenuOpen;
            },

            closeMobileMenu: function () {
                this.mobileMenuOpen = false;
            },

            loadRedisSettings: function () {
                var self = this;
                var cfg = window.bankaiData || window.bankaiCoreData || {};
                var base = (cfg.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'speed/redis', { credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce || '' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        var d = j.data || j;
                        if (d && typeof d === 'object') {
                            self.redisSettings = Object.assign({}, self.redisSettings, {
                                enabled: !!d.enabled,
                                host: d.host || '127.0.0.1',
                                port: d.port || 6379,
                                password: d.password || '',
                                db: d.db || 0,
                                prefix: d.prefix || 'bankai:'
                            });
                        }
                    }).catch(function () {});
            },
            setTab: function (tab) {
                if (!tab) return;
                var self = this;
                // Ensure modules appear enabled in UI
                // keep moduleActive as-is (respect dashboard toggles)
                if (tab === 'speed-cache' && typeof this.loadRedisSettings === 'function') {
                    try { this.loadRedisSettings(); } catch (e) {}
                }
                this.pageLoading = true;
                this.pageProgress = 40;
                // Set tab immediately so content shows even if timeout fails
                this.activeTab = tab;
                try {
                    document.querySelectorAll('#bankai-admin-app .bankai-tab-pane').forEach(function (el) {
                        var id = el.id || '';
                        var on = (id === 'tab-' + tab);
                        el.classList.toggle('is-active-tab', on);
                        if (on) { el.removeAttribute('x-cloak'); }
                    });
                } catch (e) {}
                setTimeout(function () {
                    self.activeTab = tab;
                    self.pageProgress = 100;
                    self.pageLoading = false;
                    if (typeof self.closeMobileMenu === 'function') self.closeMobileMenu();
                    if (tab === 'seo-engine') {
                        try {
                            if (typeof self.runSeoAuditInline === 'function') self.runSeoAuditInline();
                            if (self.seoPanel === 'articles' && typeof self.loadArticles === 'function') self.loadArticles();
                        } catch (e) {}
                    }
                    if (tab === 'media-watermark' && typeof self.loadMediaLibrary === 'function') {
                        try { self.loadMediaLibrary(true); } catch (e) {}
                    }
                }, 80);
            },

            isModuleNavEnabled: function (tab) {
                var map = {
                    'seo-engine': 'seo_engine',
                    'speed-cache': 'speed_cache',
                    'media-watermark': 'media_watermark',
                    'ai-studio': 'ai_studio',
                    'jalali-calendar': 'jalali_calendar'
                };
                var modKey = map[tab];
                if (!modKey) return true;
                return this.moduleActive[modKey] !== false;
            },

            showToast: function (message, type, duration) {
                var t = type || 'success';
                if (t === 'warn') t = 'warning';
                var ms = duration || (t === 'error' ? 4800 : 3600);
                this.toast = { show: true, message: message || '', type: t, duration: ms };
                var self = this;
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(function () { self.toast.show = false; }, ms);
            },

            toggleLanguage: function () {
                this.isRtl = !this.isRtl;
            },

            /* ---- Core modules (dashboard switches) ---- */
            
            
            openCdnSettings: function () {
                var self = this;
                self.cdnModal.show = true;
                var cfg = window.bankaiData || window.bankaiCoreData || {};
                var base = (cfg.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'media/cdn', { credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce || '' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        var d = j.data || j;
                        if (d && typeof d === 'object') {
                            self.cdnSettings = Object.assign({}, self.cdnSettings, d);
                        }
                    }).catch(function () {});
            },
            saveCdnSettings: function () {
                var self = this;
                self.cdnModal.saving = true;
                var cfg = window.bankaiData || window.bankaiCoreData || {};
                var base = (cfg.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'media/cdn', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
                    body: JSON.stringify(self.cdnSettings)
                }).then(function (r) { return r.json(); })
                .then(function (j) {
                    self.cdnModal.saving = false;
                    if (j.success === false) {
                        self.showToast((j.message || 'خطا در ذخیره CDN'), 'error');
                    } else {
                        self.showToast(self.isRtl ? 'تنظیمات CDN ذخیره شد' : 'CDN settings saved', 'success');
                        self.cdnModal.show = false;
                    }
                }).catch(function () {
                    self.cdnModal.saving = false;
                    self.showToast('خطا در ارتباط', 'error');
                });
            },
            testRedisConnection: function () {
                var self = this;
                self.redisStatus = { checked: false, ok: false, message: self.isRtl ? 'در حال تست…' : 'Testing…' };
                var cfg = window.bankaiData || window.bankaiCoreData || {};
                var base = (cfg.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'speed/redis/test', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
                    body: JSON.stringify(self.redisSettings)
                }).then(function (r) { return r.json(); })
                .then(function (j) {
                    var d = j.data || j;
                    self.redisStatus = {
                        checked: true,
                        ok: !!(d.ok || j.success),
                        message: d.message || j.message || (d.ok ? 'OK' : 'Failed')
                    };
                }).catch(function () {
                    self.redisStatus = { checked: true, ok: false, message: 'Connection error' };
                });
            },
            saveRedisSettings: function () {
                var self = this;
                self.redisSaving = true;
                var cfg = window.bankaiData || window.bankaiCoreData || {};
                var base = (cfg.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'speed/redis', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
                    body: JSON.stringify(self.redisSettings)
                }).then(function (r) { return r.json(); })
                .then(function (j) {
                    self.redisSaving = false;
                    if (j.success === false) {
                        self.showToast(j.message || 'خطا', 'error');
                    } else {
                        self.showToast(self.isRtl ? 'تنظیمات Redis ذخیره شد' : 'Redis settings saved', 'success');
                        if (j.data && j.data.message) self.redisStatus = { checked: true, ok: !!j.data.ok, message: j.data.message };
                    }
                }).catch(function () {
                    self.redisSaving = false;
                    self.showToast('خطا در ارتباط', 'error');
                });
            },
initDashboard: function () {
                this.loadDashboardVisits();
            },
            formatDashNum: function (n) {
                n = parseInt(n, 10) || 0;
                try { return n.toLocaleString(this.isRtl ? 'fa-IR' : 'en-US'); } catch (e) { return String(n); }
            },
            loadDashboardVisits: function () {
                var self = this;
                self.dashVisitsLoading = true;
                self.dashVisits.error = '';
                var cfg = window.bankaiData || window.bankaiCoreData || {};
                var base = (cfg.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'analytics/report?days=7', {
                    credentials: 'same-origin',
                    headers: { 'X-WP-Nonce': cfg.nonce || '' }
                }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    var d = (res.j && res.j.data) ? res.j.data : (res.j || {});
                    self.dashVisits.total = d.visits || d.total || 0;
                    self.dashVisits.change = d.visits_change || d.change || 0;
                    var devices = d.devices || d.by_device || {};
                    function hitsOf(key) {
                        var v = devices[key] || devices[key.charAt(0).toUpperCase() + key.slice(1)];
                        if (v && typeof v === 'object') return parseInt(v.hits != null ? v.hits : v.h, 10) || 0;
                        return parseInt(v, 10) || 0;
                    }
                    self.dashVisits.desktop = hitsOf('desktop');
                    self.dashVisits.mobile = hitsOf('mobile');
                    self.dashVisits.tablet = hitsOf('tablet');
                    if (!res.ok) self.dashVisits.error = d.message || (self.isRtl ? 'گزارش در دسترس نیست' : 'Report unavailable');
                }).catch(function () {
                    self.dashVisits.error = self.isRtl ? 'ماژول آنالیتیکس را فعال کنید یا کمی بعد دوباره تلاش کنید' : 'Enable analytics module or try again';
                }).finally(function () { self.dashVisitsLoading = false; });
            },
toggleCoreModule: function (key, enabled) {
                var self = this;
                var prev = self.moduleActive[key];
                // Optimistic UI — instant feedback
                self.moduleActive[key] = !!enabled;
                self.moduleActive = Object.assign({}, self.moduleActive);

                var reverse = {
                    seo_engine: 'seo-engine',
                    speed_cache: 'speed-cache',
                    media_watermark: 'media-watermark',
                    ai_studio: 'ai-studio',
                    jalali_calendar: 'jalali-calendar'
                };
                if (!enabled && reverse[key] && self.activeTab === reverse[key]) {
                    self.setTab('overview');
                }

                return rest('/core-module/' + encodeURIComponent(key), {
                    method: 'POST',
                    body: JSON.stringify({ enabled: !!enabled })
                }).then(function (data) {
                    if (data && data.success) {
                        self.moduleActive[key] = !!enabled;
                        self.showToast(data.message || self.t('saved'), 'success', 2200);
                        var chip = document.getElementById('bk-core-mod-count');
                        if (chip && typeof data.active_count === 'number') {
                            chip.innerHTML = data.active_count + ' / 6 <span>فعال</span>';
                        }
                    } else {
                        self.moduleActive[key] = prev;
                        self.moduleActive = Object.assign({}, self.moduleActive);
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.moduleActive[key] = prev;
                    self.moduleActive = Object.assign({}, self.moduleActive);
                    self.showToast(self.t('error') || 'خطا', 'error');
                });
            },

            /* ---- SEO ---- */
            toggleSeoModule: function (id) {
                var self = this;
                var enabled = !!this.seoState[id];
                rest('/module/' + encodeURIComponent(id), {
                    method: 'POST',
                    body: JSON.stringify({ enabled: enabled })
                }).then(function (data) {
                    if (data && data.success) {
                        self.showToast(data.message || self.t('saved'), 'success');
                    } else {
                        self.seoState[id] = !enabled;
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.seoState[id] = !enabled;
                    self.showToast(self.t('error'), 'error');
                });
            },

            openSeoDrawer: function (id, title) {
                this.seoDrawer = { show: true, id: id, title: title || id };
            },

            saveSeoDrawerSettings: function () {
                this.seoDrawer.show = false;
                this.showToast(this.t('saved'), 'success');
            },

            runSeoAudit: function () {
                this.runSeoAuditInline();
            },

            buildClientAudit: function () {
                var si = (window.bankaiState && window.bankaiState.seoIntegrations) || {};
                var home = (window.bankaiState && window.bankaiState.homeUrl) || '/';
                return [
                    { key: 'sitemap', label: 'نقشه سایت XML', label_en: 'XML Sitemap', pass: si.sitemap_enabled !== false, link: home + 'sitemap.xml' },
                    { key: 'robots', label: 'robots.txt', label_en: 'robots.txt', pass: true, link: home + 'robots.txt' },
                    { key: 'seo_module', label: 'ماژول سئو فعال', label_en: 'SEO module on', pass: this.moduleActive.seo_engine !== false },
                    { key: 'google_ver', label: 'تأیید گوگل', label_en: 'Google verify', pass: !!si.google_site_verification, link: 'https://search.google.com/search-console' },
                    { key: 'ga4', label: 'GA4', label_en: 'GA4', pass: !!si.ga4_measurement_id },
                ];
            },

            scoreFromItems: function (items) {
                if (!items || !items.length) return 0;
                return Math.round((items.filter(function (i) { return i.pass; }).length / items.length) * 100);
            },

            openSeoWizard: function () { this.runAutoWizard(); },
            wizardNext: function () { if (this.seoWizard.step < 4) this.seoWizard.step++; },
            wizardPrev: function () { if (this.seoWizard.step > 1) this.seoWizard.step--; },
            wizardFinish: function () { this.runAutoWizard(); },
            runAutoWizard: function () {
                var self = this;
                if (this.wizardBusy) return;
                this.wizardBusy = true;
                this.seoPanel = 'tools';
                var ids = ['auto_meta', 'sitemap_pro', 'canonical_guard', 'open_graph_ai', 'local_seo_schema', 'llms_txt_builder'];
                var i = 0;
                function next() {
                    if (i >= ids.length) {
                        self.wizardBusy = false;
                        self.seoWizard = { show: false, step: 1 };
                        self.showToast(self.isRtl
                            ? '✓ راه‌اندازی کامل شد — همه ماژول‌های ضروری سئو فعال شدند'
                            : '✓ Setup complete — essential SEO modules enabled', 'success');
                        if (typeof self.runSeoAuditInline === 'function') self.runSeoAuditInline();
                        return;
                    }
                    var id = ids[i++];
                    self.seoState[id] = true;
                    rest('/module/' + encodeURIComponent(id), {
                        method: 'POST',
                        body: JSON.stringify({ enabled: true })
                    }).finally(function () {
                        setTimeout(next, 180);
                    });
                }
                next();
            },
            runSeoAuditInline: function () {
                var self = this;
                if (!this.seoAudit) this.seoAudit = { show: false, running: false, score: 0, items: [] };
                this.seoAudit.running = true;
                this.seoAudit.items = [];
                // Prefer client checklist (always available)
                this.seoAudit.items = this.buildClientAudit();
                this.seoAudit.score = this.scoreFromItems(this.seoAudit.items);
                rest('/seo/site-audit', { method: 'GET' })
                    .then(function (data) {
                        if (data && data.success && data.data) {
                            self.seoAudit.items = data.data.items || self.seoAudit.items;
                            self.seoAudit.score = data.data.score || self.scoreFromItems(self.seoAudit.items);
                        }
                    })
                    .catch(function () { /* keep client audit */ })
                    .finally(function () { self.seoAudit.running = false; });
            },

            saveIntegration: function (fields) {
                var self = this;
                var key = fields._key || 'all';
                this.integSaving[key] = true;
                var payload = {};
                Object.keys(fields).forEach(function (k) {
                    if (k !== '_key') payload[k] = fields[k];
                });
                // Prefer settings endpoint when integrations route may be missing
                return rest('/settings', {
                    method: 'POST',
                    body: JSON.stringify({ settings: payload })
                }).then(function (data) {
                    if (data && data.success !== false) {
                        self.showToast(self.t('saved'), 'success');
                        if (window.bankaiState) {
                            window.bankaiState.seoIntegrations = Object.assign(
                                window.bankaiState.seoIntegrations || {}, payload
                            );
                        }
                    } else {
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.integSaving[key] = false;
                });
            },


            /* ---- SEO Import from Rank Math / Yoast ---- */
            
            searchAuditPosts: function () {
                var self = this;
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_seo_page_audit_posts');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fd.append('q', this.pageAudit.q || '');
                fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (j && j.success) self.pageAudit.posts = j.data.items || [];
                }).catch(function () {});
            },
            runPageAudit: function () {
                var self = this;
                if (this.pageAudit.running) return;
                if (!this.pageAudit.url && !this.pageAudit.postId) {
                    this.pageAudit.error = 'لینک یا مقاله را مشخص کنید';
                    return;
                }
                this.pageAudit.running = true;
                this.pageAudit.error = '';
                this.pageAudit.result = null;
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_seo_page_audit');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                if (this.pageAudit.postId) fd.append('post_id', String(this.pageAudit.postId));
                if (this.pageAudit.url) fd.append('url', this.pageAudit.url);
                fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    self.pageAudit.running = false;
                    if (!j || !j.success) {
                        self.pageAudit.error = (j && j.data && j.data.message) || 'خطا در ممیزی';
                        return;
                    }
                    var d = j.data || {};
                    self.pageAudit.result = { seo: d.seo || [], aeo: d.aeo || [], geo: d.geo || [] };
                    self.pageAudit.summary = d.summary || {};
                    self.pageAudit.engines = d.engines || [];
                    self.pageAudit.score = d.score || 0;
                    self.pageAudit.tab = 'seo';
                    if (d.error) self.pageAudit.error = d.error;
                }).catch(function (e) {
                    self.pageAudit.running = false;
                    self.pageAudit.error = (e && e.message) || 'خطای شبکه';
                });
            },
initSeoImport: function () {
                var self = this;
                if (!this.seoImport) return;
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_seo_import_detect');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (j && j.success && j.data) {
                        self.seoImport.sources = j.data.sources || {};
                        self.seoImport.importableTotal = j.data.importable_total || 0;
                    }
                }).catch(function () {});
                // Load AI providers from localized state or REST
                this.loadSeoAiProviders();
                if (!(this.seoImport.missing && this.seoImport.missing.length)) {
                    this.loadMissingSeo();
                }
            },
            loadSeoAiProviders: function () {
                var self = this;
                var st = (window.bankaiState || this.$data || {});
                // Alpine state often has providers from get_initial_state_data
                var text = st.providers || st.aiProviders || this.providers || [];
                var image = st.imageProviders || this.imageProviders || [];
                var c = cfg();
                if ((!text || !text.length) && c.providers) text = c.providers;
                if ((!image || !image.length) && c.imageProviders) image = c.imageProviders;

                function norm(list) {
                    var statusish = ['کلید تنظیم شده', 'بدون کلید API', 'بدون کلید', 'ready', 'missing_key'];
                    var idNames = {
                        openrouter: 'OpenRouter', gemini: 'Google Gemini', openai: 'OpenAI',
                        anthropic: 'Anthropic Claude', deepseek: 'DeepSeek', huggingface: 'Hugging Face',
                        groq: 'Groq', cloudflare: 'Cloudflare Workers AI'
                    };
                    return (list || []).map(function (p) {
                        if (typeof p === 'string') return { id: p, name: p, ready: true };
                        var ready = !!(p.ready || p.has_key || p.hasKey || p.configured
                            || p.status === 'ready' || p.status === 'ok' || p.masked_key
                            || (p.key && String(p.key).length > 0));
                        var name = p.name || '';
                        if (!name || statusish.indexOf(name) !== -1) {
                            name = (p.label && statusish.indexOf(p.label) === -1) ? p.label : (p.id || p.key || '');
                        }
                        if (!name || statusish.indexOf(name) !== -1) {
                            name = idNames[p.id] || p.id || '';
                        }
                        return {
                            id: p.id || p.key || '',
                            name: name,
                            ready: ready
                        };
                    }).filter(function (p) { return p.id; });
                }
                // Alpine root often has providers directly
                if ((!text || !text.length) && Array.isArray(self.providers)) text = self.providers;
                if ((!image || !image.length) && Array.isArray(self.imageProviders)) image = self.imageProviders;
                self.seoImport.textProviders = norm(text);
                self.seoImport.imageProviders = norm(image);

                // Live from AI Studio if available on page
                if (typeof Bankai_AI_Studio === 'undefined' && self.seoImport.textProviders.length === 0) {
                    // try reading DOM data attributes later — ignore
                }
                if (!self.seoImport.textProvider && self.seoImport.textProviders.length) {
                    var readyT = self.seoImport.textProviders.find(function (p) { return p.ready; });
                    self.seoImport.textProvider = (readyT || self.seoImport.textProviders[0]).id;
                }
                if (!self.seoImport.imageProvider && self.seoImport.imageProviders.length) {
                    var readyI = self.seoImport.imageProviders.find(function (p) { return p.ready; });
                    self.seoImport.imageProvider = (readyI || self.seoImport.imageProviders[0]).id;
                }
            },
            startSeoImport: function () {
                // legacy entry → start with auto preferred source
                var src = 'rankmath';
                if (this.seoImport.sources && this.seoImport.sources.rankmath && this.seoImport.sources.rankmath.active) {
                    src = 'rankmath';
                } else if (this.seoImport.sources && this.seoImport.sources.yoast && this.seoImport.sources.yoast.active) {
                    src = 'yoast';
                }
                this.selectSeoSource(src);
            },

            selectSeoSource: function (source) {
                var self = this;
                if (!source) return;
                if (this.seoImport.running) return;
                this.seoImport.selectedSource = source;
                this.seoImport.prefer = source;
                this.seoImport.phase = 'list';
                this.seoImport.cards = [];
                this.seoImport.queue = [];
                this.seoImport.queueTotal = 0;
                this.seoImport.processed = 0;
                this.seoImport.importedCount = 0;
                this.seoImport.skippedCount = 0;
                this.seoImport.progress = 0;
                this.seoImport.done = false;
                this.seoImport.running = true;
                this.seoImport.stepText = this.seoImport.direction === 'export'
                    ? 'در حال فهرست مقالات دارای سئوی Bankai…'
                    : ('در حال فهرست مقالات ' + (source === 'yoast' ? 'Yoast' : 'Rank Math') + '…');

                var c = cfg();
                var allItems = [];
                var page = 1;
                var totalPages = 1;
                var action = this.seoImport.direction === 'export' ? 'bankai_seo_export_list' : 'bankai_seo_sync_list';

                function fetchPage() {
                    var fd = new FormData();
                    fd.append('action', action);
                    fd.append('nonce', c.adminNonce || c.nonce || '');
                    fd.append('page', String(page));
                    fd.append('per_page', '20');
                    if (self.seoImport.direction === 'export') {
                        fd.append('target', source);
                    } else {
                        fd.append('source', source);
                    }
                    return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST', credentials: 'same-origin', body: fd
                    }).then(function (r) { return r.json(); }).then(function (j) {
                        if (!j || !j.success || !j.data) throw new Error((j && j.data && j.data.message) || 'list failed');
                        totalPages = j.data.total_pages || 1;
                        var items = j.data.items || [];
                        items.forEach(function (it) {
                            it.status = 'pending';
                            it.statusLabel = 'در صف';
                            it.progress = 0;
                            it.checklist = [];
                            allItems.push(it);
                        });
                        self.seoImport.cards = allItems.slice();
                        self.seoImport.queueTotal = j.data.total || allItems.length;
                        self.seoImport.progress = Math.min(30, Math.round((page / Math.max(1, totalPages)) * 30));
                        self.seoImport.stepText = 'لیست مقالات: صفحه ' + page + ' از ' + totalPages;
                        if (page < totalPages) {
                            page++;
                            return fetchPage();
                        }
                        return allItems;
                    });
                }

                fetchPage().then(function (items) {
                    if (!items.length) {
                        self.seoImport.running = false;
                        self.seoImport.phase = 'done';
                        self.seoImport.done = true;
                        self.seoImport.progress = 100;
                        self.seoImport.doneTitle = 'مقاله‌ای یافت نشد';
                        self.seoImport.doneMessage = self.seoImport.direction === 'export'
                            ? 'هیچ مقاله‌ای با متای سئوی Bankai پیدا نشد.'
                            : 'هیچ مقاله‌ای با متای ' + (source === 'yoast' ? 'Yoast' : 'Rank Math') + ' پیدا نشد.';
                        self.seoImport.stepText = 'پایان';
                        return;
                    }
                    self.seoImport.queue = items.map(function (it) { return it.id; });
                    self.seoImport.phase = 'run';
                    self.seoImport.stepText = 'شروع پردازش مقالات…';
                    return self.processSeoSyncQueue();
                }).catch(function (e) {
                    self.seoImport.running = false;
                    self.seoImport.stepText = 'خطا: ' + ((e && e.message) || 'نامشخص');
                    if (self.showToast) self.showToast(self.seoImport.stepText, 'error');
                });
            },

            processSeoSyncQueue: function () {
                var self = this;
                var c = cfg();
                var queue = (this.seoImport.queue || []).slice();
                var total = queue.length || 1;
                var idx = 0;
                var isExport = this.seoImport.direction === 'export';
                var source = this.seoImport.selectedSource || 'rankmath';

                function next() {
                    if (idx >= queue.length) {
                        self.seoImport.running = false;
                        self.seoImport.phase = 'done';
                        self.seoImport.done = true;
                        self.seoImport.progress = 100;
                        self.seoImport.stepText = 'همگام‌سازی کامل شد';
                        self.seoImport.doneTitle = isExport ? 'خروجی با موفقیت انجام شد' : 'ورود اطلاعات با موفقیت انجام شد';
                        self.seoImport.doneMessage = (self.seoImport.importedCount || 0) + ' مقاله پردازش شد، ' + (self.seoImport.skippedCount || 0) + ' مورد رد یا بدون تغییر.';
                        if (self.showToast) self.showToast(self.seoImport.doneTitle, 'success');
                        return Promise.resolve();
                    }
                    var postId = queue[idx];
                    var card = (self.seoImport.cards || []).find(function (x) { return x.id === postId; });
                    if (card) {
                        card.status = 'running';
                        card.statusLabel = 'در حال استخراج…';
                        card.progress = 15;
                    }
                    self.seoImport.stepText = (isExport ? 'ارسال مقاله ' : 'استخراج مقاله ') + (idx + 1) + ' از ' + queue.length;

                    var fd = new FormData();
                    fd.append('action', isExport ? 'bankai_seo_export_process_one' : 'bankai_seo_sync_process_one');
                    fd.append('nonce', c.adminNonce || c.nonce || '');
                    fd.append('post_id', String(postId));
                    fd.append('overwrite', self.seoImport.overwrite ? '1' : '0');
                    if (isExport) fd.append('target', source);
                    else fd.append('source', source);

                    return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST', credentials: 'same-origin', body: fd
                    }).then(function (r) { return r.json(); }).then(function (j) {
                        var ok = j && j.success && j.data;
                        var data = ok ? j.data : {};
                        if (card) {
                            card.progress = 100;
                            card.checklist = data.checklist || [];
                            if (ok && (data.imported || data.exported)) {
                                card.status = 'done';
                                card.statusLabel = isExport ? 'ارسال شد' : 'وارد شد';
                                self.seoImport.importedCount = (self.seoImport.importedCount || 0) + 1;
                            } else {
                                card.status = 'skip';
                                card.statusLabel = 'بدون تغییر';
                                self.seoImport.skippedCount = (self.seoImport.skippedCount || 0) + 1;
                            }
                        } else if (ok && (data.imported || data.exported)) {
                            self.seoImport.importedCount = (self.seoImport.importedCount || 0) + 1;
                        } else {
                            self.seoImport.skippedCount = (self.seoImport.skippedCount || 0) + 1;
                        }
                        idx++;
                        self.seoImport.processed = idx;
                        self.seoImport.progress = Math.round(30 + (idx / total) * 70);
                        return next();
                    }).catch(function () {
                        if (card) {
                            card.status = 'error';
                            card.statusLabel = 'خطا';
                            card.progress = 100;
                        }
                        self.seoImport.skippedCount = (self.seoImport.skippedCount || 0) + 1;
                        idx++;
                        self.seoImport.processed = idx;
                        self.seoImport.progress = Math.round(30 + (idx / total) * 70);
                        return next();
                    });
                }

                return next();
            },

            loadMissingSeoAfterImport: function () {
                var self = this;
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_seo_import_missing');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fd.append('page', String(this.seoImport.missingPage || 1));
                fd.append('per_page', '20');
                fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (j && j.success && j.data) {
                        self.seoImport.missing = (j.data.items || []).map(function (row) {
                            row.aiStatus = '';
                            return row;
                        });
                        self.seoImport.missingTotal = j.data.total || 0;
                        self.seoImport.missingPages = j.data.total_pages || 1;
                        self.seoImport.missingPage = j.data.page || 1;
                        self.seoImport.selected = [];
                        self.seoImport.allSelected = false;
                        self.seoImport.report = true;
                    }
                }).catch(function () {});
            },

            selectedMissingCount: function () {
                return (this.seoImport.selected || []).length;
            },
            toggleSelectAllMissing: function () {
                if (this.seoImport.allSelected) {
                    this.seoImport.selected = [];
                    this.seoImport.allSelected = false;
                } else {
                    this.seoImport.selected = (this.seoImport.missing || []).map(function (r) { return r.id; });
                    this.seoImport.allSelected = true;
                }
            },

            defaultSeoChecklist: function () {
                return [
                    { id: 'analyze', label: 'تحلیل محتوا و ساختار مقاله', status: 'pending' },
                    { id: 'meta', label: 'تولید خودکار عنوان سئو و متا دیسکریپشن', status: 'pending' },
                    { id: 'keywords', label: 'استخراج کلمات کلیدی + ادغام ثابت‌ها', status: 'pending' },
                    { id: 'links', label: 'لینک‌سازی هوشمند داخلی با مقالات مرتبط', status: 'pending' },
                    { id: 'images', label: 'سئوی تصاویر و تولید Alt Text خودکار', status: 'pending' },
                    { id: 'optimize', label: 'بهینه‌سازی و فشرده‌سازی تصاویر مقاله', status: 'pending' },
                    { id: 'schema', label: 'تولید اسکیما JSON-LD مقاله', status: 'pending' },
                    { id: 'terms', label: 'تولید دسته و برچسب خودکار', status: 'pending' }
                ];
            },
            aiPhaseLabel: function (phase) {
                var map = {
                    pending: 'در صف',
                    running: 'در حال اجرا',
                    review: 'آماده تأیید',
                    done: 'ذخیره شد',
                    error: 'خطا',
                    cancelled: 'لغو شد'
                };
                return map[phase] || phase || '';
            },
            approvedReadyCount: function () {
                return (this.seoImport.missing || []).filter(function (r) {
                    return r.aiPhase === 'review' && r.draft;
                }).length;
            },
            setRowCheck: function (row, id, status) {
                if (!row.checklist) return;
                var item = row.checklist.find(function (c) { return c.id === id; });
                if (item) item.status = status;
            },
            callSeoAiTask: function (task, payload) {
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_ai_seo_task');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fd.append('task', task);
                fd.append('provider', this.seoImport.textProvider || '');
                Object.keys(payload || {}).forEach(function (k) {
                    var v = payload[k];
                    if (v === undefined || v === null) return;
                    if (typeof v === 'object') fd.append(k, JSON.stringify(v));
                    else fd.append(k, String(v));
                });
                return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (!j || !j.success) {
                        throw new Error((j && j.data && j.data.message) || ('خطا در ' + task));
                    }
                    return j.data || {};
                });
            },
            fetchPostContext: function (postId) {
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_seo_ai_post_context');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fd.append('post_id', String(postId));
                return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'خطا در دریافت محتوا');
                    return j.data || {};
                });
            },
            runSeoPipelineForRow: async function (row) {
                var self = this;
                row.aiPhase = 'running';
                row.aiProgress = 0;
                row.aiError = '';
                row.checklist = this.defaultSeoChecklist();
                row.draft = { seo_title: '', description: '', focus_keyword: '', keywords: [], schema: '', coverUrl: '', coverId: 0, content: '' };
                row.saving = false;

                var steps = [
                    { id: 'analyze', pct: 8, text: 'گام ۱/۱۰: تحلیل محتوا…' },
                    { id: 'meta', pct: 18, text: 'گام ۲/۱۰: تولید عنوان و متا…' },
                    { id: 'keywords', pct: 28, text: 'گام ۳/۱۰: کلیدواژه‌ها…' },
                    { id: 'links', pct: 40, text: 'گام ۴/۱۰: لینک‌سازی داخلی…' },
                    { id: 'images', pct: 52, text: 'گام ۵/۱۰: Alt تصاویر…' },
                    { id: 'optimize', pct: 70, text: 'گام ۶/۸: بهینه‌سازی تصاویر…' },
                    { id: 'schema', pct: 85, text: 'گام ۷/۸: اسکیما…' },
                    { id: 'terms', pct: 97, text: 'گام ۸/۸: دسته و برچسب…' }
                ];

                var ctx = await this.fetchPostContext(row.id);
                row.draft.content = ctx.content || '';

                for (var s = 0; s < steps.length; s++) {
                    if (self.seoImport.aiAbort) {
                        row.aiPhase = 'cancelled';
                        row.aiStepText = 'متوقف شد';
                        return;
                    }
                    var step = steps[s];
                    row.aiStepText = step.text;
                    row.aiProgress = step.pct;
                    self.setRowCheck(row, step.id, 'loading');

                    try {
                        if (step.id === 'analyze') {
                            // light local analysis marker
                            await new Promise(function (r) { setTimeout(r, 200); });
                            self.setRowCheck(row, 'analyze', 'success');
                        } else if (step.id === 'meta') {
                            var meta = await self.callSeoAiTask('meta_title', {
                                title: ctx.title || row.title,
                                content: ctx.content || '',
                                focus_keyword: ctx.focus_keyword || '',
                                locale: 'fa_IR'
                            });
                            row.draft.seo_title = meta.seo_title || meta.title || '';
                            row.draft.description = meta.description || meta.meta_description || '';
                            if (meta.focus_keyword) row.draft.focus_keyword = meta.focus_keyword;
                            self.setRowCheck(row, 'meta', 'success');
                        } else if (step.id === 'keywords') {
                            var kw = await self.callSeoAiTask('keywords', {
                                title: ctx.title || row.title,
                                content: ctx.content || '',
                                focus_keyword: row.draft.focus_keyword || ctx.focus_keyword || '',
                                locale: 'fa_IR'
                            });
                            var list = kw.keywords || kw.list || [];
                            if (!Array.isArray(list)) list = [];
                            // merge fixed site keywords
                            var fixed = self.parseKw(self.fixedKeywordsRaw || '');
                            list = list.concat(fixed);
                            list = list.filter(function (v, i, a) { return v && a.indexOf(v) === i; });
                            row.draft.keywords = list;
                            if (!row.draft.focus_keyword && list[0]) row.draft.focus_keyword = list[0];
                            self.setRowCheck(row, 'keywords', 'success');
                        } else if (step.id === 'links') {
                            try {
                                var links = await self.callSeoAiTask('smart_internal_links', {
                                    title: ctx.title || row.title,
                                    content: ctx.content || '',
                                    focus_keyword: row.draft.focus_keyword || '',
                                    articles: JSON.stringify(ctx.related || []),
                                    locale: 'fa_IR'
                                });
                                row.draft.links = links.links || links.suggestions || [];
                                self.setRowCheck(row, 'links', 'success');
                            } catch (eL) {
                                self.setRowCheck(row, 'links', 'error');
                            }
                        } else if (step.id === 'images') {
                            var imgs = ctx.images || [];
                            row.draft.altTexts = [];
                            for (var ii = 0; ii < Math.min(imgs.length, 6); ii++) {
                                var img = imgs[ii];
                                if (img.alt && String(img.alt).trim()) continue;
                                try {
                                    var altRes = await self.callSeoAiTask('image_alt_text', {
                                        title: ctx.title || row.title,
                                        content: ctx.content || '',
                                        image_url: img.src || '',
                                        image_context: (ctx.title || '') + ' ' + (row.draft.focus_keyword || ''),
                                        locale: 'fa_IR'
                                    });
                                    row.draft.altTexts.push({ src: img.src, alt: altRes.alt_text || altRes.text || '' });
                                } catch (eA) { /* skip */ }
                            }
                            self.setRowCheck(row, 'images', 'success');
                        } else if (step.id === 'optimize') {
                            try {
                                var c = cfg();
                                var fd = new FormData();
                                fd.append('action', 'bankai_optimize_post_images');
                                fd.append('nonce', c.adminNonce || c.nonce || '');
                                fd.append('post_id', String(row.id));
                                await fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                                    method: 'POST', credentials: 'same-origin', body: fd
                                });
                                self.setRowCheck(row, 'optimize', 'success');
                            } catch (eO) {
                                self.setRowCheck(row, 'optimize', 'error');
                            }
                        } else if (step.id === 'schema') {
                            var schema = {
                                '@context': 'https://schema.org',
                                '@type': 'Article',
                                headline: row.draft.seo_title || ctx.title || row.title,
                                description: row.draft.description || '',
                                mainEntityOfPage: ctx.permalink || '',
                                keywords: (row.draft.keywords || []).join(', ')
                            };
                            row.draft.schema = JSON.stringify(schema, null, 2);
                            self.setRowCheck(row, 'schema', 'success');
                        } else if (step.id === 'cover') {
                            try {
                                var c2 = cfg();
                                var fd2 = new FormData();
                                fd2.append('action', 'bankai_ai_image_generate');
                                fd2.append('nonce', c2.adminNonce || c2.nonce || '');
                                fd2.append('provider', self.seoImport.imageProvider || '');
                                fd2.append('purpose', 'cover');
                                fd2.append('post_id', String(row.id));
                                fd2.append('prompt', 'Cover image for article: ' + (row.draft.seo_title || row.title) + '. Keyword: ' + (row.draft.focus_keyword || ''));
                                var r2 = await fetch(c2.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                                    method: 'POST', credentials: 'same-origin', body: fd2
                                });
                                var j2 = await r2.json();
                                if (j2 && j2.success && j2.data) {
                                    row.draft.coverUrl = j2.data.url || '';
                                    row.draft.coverId = j2.data.attachment_id || 0;
                                    self.setRowCheck(row, 'cover', 'success');
                                } else {
                                    self.setRowCheck(row, 'cover', 'error');
                                }
                            } catch (eC) {
                                self.setRowCheck(row, 'cover', 'error');
                            }
                        } else if (step.id === 'inline_img') {
                            try {
                                var c3 = cfg();
                                var fd3 = new FormData();
                                fd3.append('action', 'bankai_ai_image_generate');
                                fd3.append('nonce', c3.adminNonce || c3.nonce || '');
                                fd3.append('provider', self.seoImport.imageProvider || '');
                                fd3.append('purpose', 'inline');
                                fd3.append('post_id', String(row.id));
                                fd3.append('prompt', 'In-article illustration for: ' + (row.draft.seo_title || row.title));
                                var r3 = await fetch(c3.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                                    method: 'POST', credentials: 'same-origin', body: fd3
                                });
                                var j3 = await r3.json();
                                if (j3 && j3.success) {
                                    self.setRowCheck(row, 'inline_img', 'success');
                                } else {
                                    self.setRowCheck(row, 'inline_img', 'error');
                                }
                            } catch (eI) {
                                self.setRowCheck(row, 'inline_img', 'error');
                            }
                        } else if (step.id === 'terms') {
                            try {
                                var c4 = cfg();
                                var fd4 = new FormData();
                                fd4.append('action', 'bankai_ai_assign_terms');
                                fd4.append('nonce', c4.adminNonce || c4.nonce || '');
                                fd4.append('post_id', String(row.id));
                                fd4.append('focus', row.draft.focus_keyword || '');
                                fd4.append('keywords', JSON.stringify(row.draft.keywords || []));
                                await fetch(c4.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                                    method: 'POST', credentials: 'same-origin', body: fd4
                                });
                                self.setRowCheck(row, 'terms', 'success');
                            } catch (eT) {
                                self.setRowCheck(row, 'terms', 'error');
                            }
                        }
                    } catch (err) {
                        self.setRowCheck(row, step.id, 'error');
                        // meta/keywords are critical
                        if (step.id === 'meta' || step.id === 'keywords') {
                            row.aiPhase = 'error';
                            row.aiError = (err && err.message) || 'خطا در مراحل اصلی سئو';
                            throw err;
                        }
                    }
                }

                row.aiProgress = 100;
                row.aiStepText = 'آماده تأیید و ذخیره';
                row.aiPhase = 'review';
            },
            runSeoForSelectedMissing: async function () {
                var self = this;
                var ids = (this.seoImport.selected || []).slice();
                if (!ids.length) {
                    this.showToast('هیچ مقاله‌ای انتخاب نشده', 'error');
                    return;
                }
                if (!this.seoImport.textProvider) {
                    this.showToast('ابتدا موتور AI متنی را انتخاب کنید', 'error');
                    return;
                }
                this.seoImport.aiRunning = true;
                this.seoImport.aiAbort = false;
                this.seoImport.aiProgress = 0;
                var done = 0;
                var failed = 0;

                for (var i = 0; i < ids.length; i++) {
                    if (this.seoImport.aiAbort) break;
                    var id = ids[i];
                    var row = (this.seoImport.missing || []).find(function (r) { return String(r.id) === String(id); });
                    if (!row) continue;
                    try {
                        await this.runSeoPipelineForRow(row);
                        if (row.aiPhase === 'review') done++;
                        else if (row.aiPhase === 'error') failed++;
                    } catch (e) {
                        failed++;
                        row.aiPhase = 'error';
                        row.aiError = (e && e.message) || 'خطا';
                    }
                    this.seoImport.aiProgress = Math.round(((i + 1) / ids.length) * 100);
                }

                this.seoImport.aiRunning = false;
                if (this.seoImport.aiAbort) {
                    this.showToast('فرایند متوقف شد — موفق: ' + done + ' / خطا: ' + failed, 'error');
                } else {
                    this.showToast('پایان صف — آماده تأیید: ' + done + ' · خطا: ' + failed, done ? 'success' : 'error');
                }
            },
            approveArticleDraft: function (row) {
                var self = this;
                if (!row || !row.draft) return;
                row.saving = true;
                var c = cfg();
                var fd = new FormData();
                fd.append('action', 'bankai_seo_ai_apply_draft');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fd.append('post_id', String(row.id));
                fd.append('seo_title', row.draft.seo_title || '');
                fd.append('description', row.draft.description || '');
                fd.append('focus_keyword', row.draft.focus_keyword || '');
                fd.append('keywords', JSON.stringify(row.draft.keywords || []));
                fd.append('schema', row.draft.schema || '');
                if (row.draft.coverId) fd.append('cover_id', String(row.draft.coverId));
                fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body: fd
                }).then(function (r) { return r.json(); }).then(function (j) {
                    row.saving = false;
                    if (j && j.success) {
                        row.aiPhase = 'done';
                        row.score = (j.data && j.data.score) || row.score || 0;
                        self.showToast('ذخیره شد: ' + (row.title || ''), 'success');
                    } else {
                        self.showToast((j && j.data && j.data.message) || 'خطا در ذخیره', 'error');
                    }
                }).catch(function () {
                    row.saving = false;
                    self.showToast('خطا در ذخیره', 'error');
                });
            },
            cancelArticleDraft: function (row) {
                if (!row) return;
                row.aiPhase = 'cancelled';
                row.draft = null;
                row.checklist = [];
                row.aiProgress = 0;
                row.aiStepText = '';
            },
            rerunArticleSeo: async function (row) {
                if (!row || this.seoImport.aiRunning) return;
                this.seoImport.aiRunning = true;
                this.seoImport.aiAbort = false;
                try {
                    await this.runSeoPipelineForRow(row);
                } catch (e) {
                    row.aiPhase = 'error';
                    row.aiError = (e && e.message) || 'خطا';
                }
                this.seoImport.aiRunning = false;
            },
            approveAllCompleted: function () {
                var self = this;
                var rows = (this.seoImport.missing || []).filter(function (r) {
                    return r.aiPhase === 'review' && r.draft;
                });
                if (!rows.length) {
                    this.showToast('مقالهٔ آماده‌ای برای تأیید نیست', 'error');
                    return;
                }
                var i = 0;
                function next() {
                    if (i >= rows.length) {
                        self.showToast('تأیید گروهی تمام شد (' + rows.length + ' مقاله)', 'success');
                        return;
                    }
                    var row = rows[i++];
                    self.approveArticleDraft(row);
                    setTimeout(next, 400);
                }
                next();
            },



            /* ---- SEO Articles list ---- */
            initSeoArticles: function () {
                this.fixedList = this.parseKw(this.fixedKeywordsRaw || '');
                this.loadArticles();
            },
            parseKw: function (raw) {
                return String(raw || '').split(/[,،\n]+/).map(function (s) { return s.trim(); }).filter(Boolean);
            },
            scoreColor: function (s) {
                s = parseInt(s, 10) || 0;
                if (s >= 80) return '#1A7F37';
                if (s >= 50) return '#D97706';
                return '#CF222E';
            },
            loadArticles: function () {
                var self = this;
                this.loading = true;
                var c = cfg();
                var base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                var url = base + 'seo/articles?page=' + this.page
                    + '&per_page=' + (this.perPage || 25)
                    + '&search=' + encodeURIComponent(this.searchQ || '')
                    + '&orderby=' + encodeURIComponent(this.orderby || 'modified')
                    + '&order=' + encodeURIComponent(this.order || 'DESC');
                fetch(url, { credentials: 'same-origin', headers: { 'X-WP-Nonce': c.nonce || '' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        var d = (j && j.data) ? j.data : {};
                        self.articles = d.items || [];
                        self.total = d.total || 0;
                        self.totalPages = d.total_pages || 1;
                    })
                    .catch(function (e) {
                        self.showToast((e && e.message) || self.t('error'), 'error');
                    })
                    .finally(function () { self.loading = false; });
            },
            openArticlesPanel: function () {
                this.seoPanel = 'articles';
                this.page = 1;
                this.loadArticles();
                setTimeout(function () {
                    var el = document.getElementById('bk-seo-articles-section');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 50);
            },
            openAnalyticsPanel: function () {
                this.seoPanel = 'analytics';
            },
            openToolsPanel: function () {
                this.seoPanel = 'tools';
            },
            saveFixedKeywords: function () {
                var self = this;
                this.savingFixed = true;
                var c = cfg();
                var base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                fetch(base + 'seo/fixed-keywords', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({ raw: this.fixedKeywordsRaw })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (j.success) {
                            self.fixedList = (j.data && j.data.keywords) || self.parseKw(self.fixedKeywordsRaw);
                            self.showToast(self.isRtl ? 'کلمات ثابت ذخیره شد' : 'Saved', 'success');
                        } else {
                            throw new Error((j && j.message) || 'error');
                        }
                    })
                    .catch(function (e) {
                        self.showToast((e && e.message) || self.t('error'), 'error');
                    })
                    .finally(function () { self.savingFixed = false; });
            },
            addFixedKeyword: function () {
                var kw = String(this.fixedKeywordInput || '').trim();
                if (!kw) return;
                var list = this.fixedList ? this.fixedList.slice() : [];
                var lower = kw.toLowerCase();
                if (list.some(function (x) { return String(x).toLowerCase() === lower; })) {
                    this.fixedKeywordInput = '';
                    return;
                }
                list.push(kw);
                this.fixedList = list;
                this.fixedKeywordsRaw = list.join('، ');
                this.fixedKeywordInput = '';
                this.saveFixedKeywords();
            },
            removeFixedKeyword: function (idx) {
                var list = (this.fixedList || []).slice();
                if (idx < 0 || idx >= list.length) return;
                list.splice(idx, 1);
                this.fixedList = list;
                this.fixedKeywordsRaw = list.join('، ');
                this.saveFixedKeywords();
            },
            openEdit: function (row) {
                this.edit = {
                    open: true,
                    row: row,
                    seo_title: row.seo_title || '',
                    description: row.description || '',
                    focus_keyword: row.focus_keyword || '',
                    keywords_str: (row.keywords || []).join('، '),
                    saving: false,
                    ai: false
                };
            },
            saveEdit: function () {
                var self = this;
                if (!this.edit.row) return;
                this.edit.saving = true;
                var c = cfg();
                var base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                var kws = this.parseKw(this.edit.keywords_str);
                fetch(base + 'seo/' + this.edit.row.id, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({
                        seo_title: this.edit.seo_title,
                        description: this.edit.description,
                        focus_keyword: this.edit.focus_keyword,
                        keywords: kws
                    })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (!j.success) throw new Error((j && j.message) || 'error');
                        self.showToast(self.t('saved'), 'success');
                        self.edit.open = false;
                        self.loadArticles();
                    })
                    .catch(function (e) {
                        self.showToast((e && e.message) || self.t('error'), 'error');
                    })
                    .finally(function () { self.edit.saving = false; });
            },
            ajaxAiSeo: function (task, ctx) {
                var c = cfg();
                var body = new FormData();
                body.append('action', 'bankai_ai_seo_task');
                body.append('nonce', c.adminNonce || c.nonce || '');
                body.append('task', task);
                body.append('title', (ctx && ctx.title) || '');
                body.append('content', (ctx && ctx.content) || '');
                body.append('focus_keyword', (ctx && ctx.focus_keyword) || '');
                body.append('locale', c.locale || 'fa_IR');
                body.append('provider', c.aiDefaultProvider || '');
                return fetch(c.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: body
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (!j.success) throw new Error((j.data && j.data.message) || j.message || 'AI error');
                    return j.data || j;
                });
            },
            runAiMeta: function (row) {
                var self = this;
                this.aiBusyId = row.id;
                var ctx = { title: row.title, content: '', focus_keyword: row.focus_keyword || '' };
                Promise.all([
                    this.ajaxAiSeo('meta_title', ctx),
                    this.ajaxAiSeo('meta_description', ctx),
                    this.ajaxAiSeo('keywords', ctx)
                ]).then(function (results) {
                    var t = results[0], d = results[1], k = results[2];
                    var c = cfg();
                    var base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                    var keywords = (k.keywords || []).concat(self.fixedList || []);
                    return fetch(base + 'seo/' + row.id, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                        body: JSON.stringify({
                            seo_title: t.seo_title || t.text || '',
                            description: d.description || d.text || '',
                            keywords: keywords
                        })
                    });
                }).then(function () {
                    self.showToast(self.isRtl ? 'متای AI ذخیره شد' : 'AI meta saved', 'success');
                    self.loadArticles();
                }).catch(function (e) {
                    self.showToast((e && e.message) || 'AI error', 'error');
                }).finally(function () {
                    self.aiBusyId = 0;
                });
            },
            runAiForEdit: function () {
                var self = this;
                if (!this.edit.row) return;
                this.edit.ai = true;
                var ctx = { title: this.edit.row.title, focus_keyword: this.edit.focus_keyword };
                Promise.all([
                    this.ajaxAiSeo('meta_title', ctx),
                    this.ajaxAiSeo('meta_description', ctx),
                    this.ajaxAiSeo('keywords', ctx)
                ]).then(function (results) {
                    self.edit.seo_title = results[0].seo_title || results[0].text || self.edit.seo_title;
                    self.edit.description = results[1].description || results[1].text || self.edit.description;
                    var kws = (results[2].keywords || []).concat(self.fixedList || []);
                    self.edit.keywords_str = kws.join('، ');
                    self.showToast(self.isRtl ? 'پیشنهاد AI آماده است' : 'AI ready', 'success');
                }).catch(function (e) {
                    self.showToast((e && e.message) || 'AI error', 'error');
                }).finally(function () {
                    self.edit.ai = false;
                });
            },

            /* ---- Speed & Cache ---- */
            toggleSpeedModule: function (id) {
                var self = this;
                var enabled = !!this.speedState[id];
                rest('/module/' + encodeURIComponent(id), {
                    method: 'POST',
                    body: JSON.stringify({ enabled: enabled })
                }).then(function (data) {
                    if (data && data.success) {
                        self.showToast(data.message || self.t('saved'), 'success');
                    } else {
                        self.speedState[id] = !enabled;
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.speedState[id] = !enabled;
                    self.showToast(self.t('error'), 'error');
                });
            },

            openSpeedDrawer: function (id, title) {
                var saved = (window.bankaiState && window.bankaiState.speedSettings) || {};
                this.speedDrawer = {
                    show: true,
                    id: id,
                    title: title || id,
                    ttl: saved.cache_ttl || 86400,
                    exclusions: (saved.cache_exclusions || '/cart/*\n/checkout/*\n/my-account/*')
                };
            },

            saveSpeedDrawerSettings: function () {
                var self = this;
                var d = this.speedDrawer;
                var payload = {
                    cache_ttl: parseInt(d.ttl, 10) || 86400,
                    cache_exclusions: d.exclusions || ''
                };
                // Also store under settings API
                rest('/settings', {
                    method: 'POST',
                    body: JSON.stringify({ settings: payload })
                }).then(function (data) {
                    if (data && data.success !== false) {
                        if (window.bankaiState) {
                            window.bankaiState.speedSettings = Object.assign(
                                window.bankaiState.speedSettings || {}, payload
                            );
                        }
                        self.speedDrawer.show = false;
                        self.showToast(self.t('saved'), 'success');
                    } else {
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    // Fallback AJAX
                    ajax('bankai_save_speed_settings', payload).then(function (res) {
                        if (res && res.success) {
                            self.speedDrawer.show = false;
                            self.showToast(self.t('saved'), 'success');
                        } else {
                            self.showToast(self.t('error'), 'error');
                        }
                    }).catch(function () {
                        self.showToast(self.t('error'), 'error');
                    });
                });
            },

            purgeAllCaches: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                this.showToast(this.t('purging'), 'success');
                ajax('bankai_purge_speed_cache', {}).then(function (res) {
                    var d = res.data || res;
                    if (res.success || d.success !== false) {
                        self.showToast((d && d.message) || self.t('purged'), 'success');
                        ajax('bankai_speed_stats', {}).then(function (st) {
                            var s = (st && st.data && st.data.stats) ? st.data.stats : null;
                            if (s && self.speedStats) Object.assign(self.speedStats, s);
                        }).catch(function () {});
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.busy = false;
                });
            },

            optimizeDatabase: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                this.showToast(this.t('optimizing'), 'success');
                ajax('bankai_optimize_database', {}).then(function (res) {
                    var d = res.data || res;
                    if (res.success) {
                        var msg = (d && d.message) || self.t('optimized');
                        if (d && typeof d.revisions_left === 'number') {
                            self.speedStats.revisions = d.revisions_left;
                        } else if (d && typeof d.deleted_revisions === 'number') {
                            self.speedStats.revisions = Math.max(0, (self.speedStats.revisions || 0) - d.deleted_revisions);
                        }
                        if (d && typeof d.deleted_revisions === 'number') {
                            msg += ' (' + d.deleted_revisions + ' revisions)';
                        }
                        self.showToast(msg, 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.busy = false;
                });
            },

            benchmarkVitals: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                this.showToast(this.t('benchmarking'), 'success');
                ajax('bankai_benchmark_vitals', {}).then(function (res) {
                    var d = res.data || res;
                    if (res.success && d.vitals) {
                        self.speedVitals = d.vitals;
                        if (d.vitals.ttfb) self.speedStats.ttfb = d.vitals.ttfb;
                        self.showToast((d.message) || (self.isRtl ? 'بنچمارک کامل شد' : 'Benchmark done'), 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.busy = false;
                });
            },

            /* ---- Media & Watermark ---- */
            initMediaStudio: function () {
                if (!this.mediaSubTab) this.mediaSubTab = 'watermark';
                if (!this.compressOpts) {
                    this.compressOpts = { format: 'webp', quality: 82, maxWidth: 1600 };
                }
                if (!this.mediaLib) {
                    this.mediaLib = {
                        items: [], page: 1, hasMore: false, loading: false, busy: false,
                        progress: 0, progressText: '', doneCount: 0, totalCount: 0, savedBytes: 0
                    };
                }
            },

            formatMediaBytes: function (n) {
                n = Number(n) || 0;
                if (n < 1024) return n + ' B';
                if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
                return (n / 1048576).toFixed(2) + ' MB';
            },

            loadMediaLibrary: function (reset) {
                var self = this;
                if (self.mediaLib.loading) return;
                if (reset) {
                    self.mediaLib.page = 1;
                    self.mediaLib.items = [];
                    self.mediaLib.hasMore = false;
                }
                self.mediaLib.loading = true;
                ajax('bankai_list_media_library', {
                    page: self.mediaLib.page,
                    per_page: 18
                }).then(function (res) {
                    var d = (res && res.data) || res || {};
                    var items = (d.items || []).map(function (it) {
                        return Object.assign({ working: false, optimized: false, bytes_after: 0 }, it);
                    });
                    if (reset) self.mediaLib.items = items;
                    else self.mediaLib.items = self.mediaLib.items.concat(items);
                    self.mediaLib.hasMore = !!d.has_more;
                    if (d.has_more) self.mediaLib.page = (self.mediaLib.page || 1) + 1;
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.mediaLib.loading = false;
                });
            },

            onMediaGridScroll: function (e) {
                var el = e.target;
                if (!el || this.mediaLib.loading || !this.mediaLib.hasMore) return;
                if (el.scrollTop + el.clientHeight >= el.scrollHeight - 80) {
                    this.loadMediaLibrary(false);
                }
            },

            compressOneMedia: function (img) {
                var self = this;
                if (!img || img.working || self.mediaLib.busy) return;
                img.working = true;
                ajax('bankai_compress_attachment', {
                    id: img.id,
                    format: self.compressOpts.format,
                    quality: self.compressOpts.quality,
                    max_width: self.compressOpts.maxWidth || 0
                }).then(function (res) {
                    var d = (res && res.data) || res || {};
                    if (res.success) {
                        img.bytes_after = d.bytes_after || img.bytes;
                        img.format = d.format || img.format;
                        img.thumb = d.thumb || img.thumb;
                        img.url = d.url || img.url;
                        img.optimized = true;
                        self.showToast(d.message || self.t('saved'), 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    img.working = false;
                });
            },

            compressAllMedia: async function () {
                var self = this;
                if (self.mediaLib.busy) return;
                // Ensure we have items
                if (!self.mediaLib.items.length) {
                    await new Promise(function (resolve) {
                        self.loadMediaLibrary(true);
                        var t = setInterval(function () {
                            if (!self.mediaLib.loading) { clearInterval(t); resolve(); }
                        }, 120);
                    });
                }
                // Load remaining pages first (up to reasonable cap)
                var guard = 0;
                while (self.mediaLib.hasMore && guard < 20) {
                    guard++;
                    await new Promise(function (resolve) {
                        self.loadMediaLibrary(false);
                        var t = setInterval(function () {
                            if (!self.mediaLib.loading) { clearInterval(t); resolve(); }
                        }, 120);
                    });
                }
                var list = self.mediaLib.items.slice();
                if (!list.length) {
                    self.showToast(self.isRtl ? 'تصویری نیست' : 'No images', 'info');
                    return;
                }
                self.mediaLib.busy = true;
                self.mediaLib.progress = 0;
                self.mediaLib.doneCount = 0;
                self.mediaLib.totalCount = list.length;
                self.mediaLib.savedBytes = 0;
                self.mediaLib.progressText = self.isRtl ? 'شروع فشرده‌سازی گروهی…' : 'Starting bulk compress…';

                for (var i = 0; i < list.length; i++) {
                    var img = list[i];
                    img.working = true;
                    self.mediaLib.progressText = (self.isRtl ? 'در حال فشرده‌سازی: ' : 'Compressing: ') + (img.title || img.id);
                    try {
                        var res = await ajax('bankai_compress_attachment', {
                            id: img.id,
                            format: self.compressOpts.format,
                            quality: self.compressOpts.quality,
                            max_width: self.compressOpts.maxWidth || 0
                        });
                        var d = (res && res.data) || res || {};
                        if (res.success) {
                            img.bytes_after = d.bytes_after || img.bytes;
                            img.format = d.format || img.format;
                            img.thumb = d.thumb || img.thumb;
                            img.url = d.url || img.url;
                            img.optimized = true;
                            self.mediaLib.savedBytes += (d.saved || Math.max(0, (img.bytes || 0) - (d.bytes_after || 0)));
                        }
                    } catch (e) {}
                    img.working = false;
                    self.mediaLib.doneCount = i + 1;
                    self.mediaLib.progress = Math.round(((i + 1) / list.length) * 100);
                }
                self.mediaLib.busy = false;
                self.mediaLib.progressText = self.isRtl ? 'فشرده‌سازی گروهی تمام شد' : 'Bulk compress finished';
                self.showToast(self.mediaLib.progressText, 'success');
            },


            
            
            wmFontList: function () {
                var list = [
                    { id: 'default', name: 'پیش‌فرض', sample: 'Aa آب', style: "font-family: Vazirmatn, Tahoma, sans-serif" },
                    { id: 'vazirmatn-bold', name: 'وزیر Bold', sample: 'Aa آب', style: "font-family: Vazirmatn, Tahoma, sans-serif;font-weight:700" },
                    { id: 'tahoma', name: 'Tahoma', sample: 'Aa آب', style: "font-family: Tahoma, sans-serif" },
                    { id: 'arial', name: 'Arial', sample: 'Aa', style: "font-family: Arial, sans-serif" },
                    { id: 'dejavu', name: 'DejaVu', sample: 'Aa آب', style: "font-family: DejaVu Sans, Tahoma, sans-serif" }
                ];
                var w = this.watermarkStudio || {};
                var customs = Array.isArray(w.custom_fonts) ? w.custom_fonts : [];
                customs.forEach(function (cf) {
                    if (!cf) return;
                    var id = cf.id || ('cf_' + (cf.name || 'x'));
                    list.push({
                        id: id,
                        name: cf.name || id,
                        sample: 'Aa آب',
                        style: 'font-family: "' + id + '", Tahoma, sans-serif',
                        url: cf.url || ''
                    });
                });
                if (w.custom_font_name && !customs.some(function (c) { return c && c.name === w.custom_font_name; })) {
                    list.push({
                        id: 'custom',
                        name: w.custom_font_name,
                        sample: 'Aa آب',
                        style: "font-family: 'custom-wm', Tahoma, sans-serif",
                        url: w.custom_font_url || ''
                    });
                }
                return list;
            },
            wmColorSwatches: function () {
                return ['#FFFFFF', '#F8FAFC', '#E2E8F0', '#94A3B8', '#0F172A', '#000000',
                    '#EF4444', '#F97316', '#EAB308', '#22C55E', '#3B82F6', '#8B5CF6',
                    'rgba(255,255,255,0.7)', 'rgba(0,0,0,0.55)', 'rgba(124,58,237,0.65)', 'transparent'];
            },
            wmBgSwatches: function () {
                return ['#000000', '#0F172A', '#1E293B', '#7C3AED', '#FFFFFF',
                    'rgba(0,0,0,0.55)', 'rgba(0,0,0,0.35)', 'rgba(124,58,237,0.5)', 'rgba(255,255,255,0.35)'];
            },
            selectWmFont: function (f) {
                if (!f || !this.watermarkStudio) return;
                this.watermarkStudio.text_font = f.id;
                if ((f.id === 'custom' || f.id === 'custom_font') && this.watermarkStudio.custom_font_url) {
                    this.ensureWmFontFace();
                }
            },
            ensureWmFontFace: function () {
                var w = this.watermarkStudio || {};
                if (!w.custom_font_url) return;
                var fam = 'BankaiWmCustom';
                var id = 'bk-wm-font-face-custom';
                var existing = document.getElementById(id);
                if (existing) existing.remove();
                var style = document.createElement('style');
                style.id = id;
                var url = w.custom_font_url;
                var format = 'woff2';
                if (/\.woff2/i.test(url)) format = 'woff2';
                else if (/\.woff/i.test(url)) format = 'woff';
                else if (/\.otf/i.test(url)) format = 'opentype';
                else if (/\.ttf/i.test(url)) format = 'truetype';
                style.textContent = "@font-face{font-family:'" + fam + "';src:url('" + url + "') format('" + format + "');font-display:swap;font-weight:100 900;font-style:normal;}";
                document.head.appendChild(style);
                // force repaint of badge
                if (this.watermarkStudio) {
                    this.watermarkStudio._fontTick = (this.watermarkStudio._fontTick || 0) + 1;
                }
            },
            startBulkCompress: function (selectedOnly) {
                var self = this;
                var items = (this.mediaLib && this.mediaLib.items) || [];
                var targets = selectedOnly ? items.filter(function (i) { return i.selected; }) : items.slice();
                if (!targets.length) {
                    this.showToast(this.isRtl ? 'تصویری برای فشرده‌سازی نیست' : 'No images', 'warning');
                    return;
                }
                this.mediaLib.busy = true;
                this.mediaLib.selectedOnly = !!selectedOnly;
                this.mediaLib.mode = 'compress';
                this.mediaLib.progress = 0;
                this.mediaLib.progressText = this.isRtl ? 'شروع فشرده‌سازی…' : 'Starting…';
                var i = 0, ok = 0, fail = 0;
                function next() {
                    if (i >= targets.length) {
                        self.mediaLib.busy = false;
                        self.mediaLib.progress = 100;
                        self.mediaLib.progressText = self.isRtl
                            ? (ok + ' موفق · ' + fail + ' ناموفق')
                            : (ok + ' ok · ' + fail + ' fail');
                        self.showToast(self.mediaLib.progressText, fail ? 'warning' : 'success');
                        setTimeout(function () { self.mediaLib.progress = 0; }, 1500);
                        if (self.loadMediaLibrary) self.loadMediaLibrary(true);
                        return;
                    }
                    var img = targets[i];
                    self.mediaLib.progress = Math.round((i / targets.length) * 100);
                    self.mediaLib.progressText = (i + 1) + ' / ' + targets.length + ' — ' + (img.title || img.id);
                    ajax('bankai_compress_attachment', {
                        id: img.id,
                        format: (self.compressOpts && self.compressOpts.format) || 'webp',
                        quality: (self.compressOpts && self.compressOpts.quality) || 82,
                        max_width: (self.compressOpts && self.compressOpts.maxWidth) || 1600
                    }, 60000).then(function (res) {
                        if (res && res.success) ok++; else fail++;
                    }).catch(function () { fail++; }).finally(function () {
                        i++;
                        setTimeout(next, 80);
                    });
                }
                next();
            },
            selectedMediaCount: function () {
                return ((this.mediaLib && this.mediaLib.items) || []).filter(function (i) { return i.selected; }).length;
            },
            openWmBulkApply: function () {
                var self = this;
                this.wmBulkModal = {
                    show: true, running: false, progress: 0,
                    progressText: this.isRtl ? 'در حال بارگذاری تصاویر…' : 'Loading images…',
                    items: [], title: this.isRtl ? 'اعمال گروهی واترمارک' : 'Bulk watermark',
                    alsoCompress: !!(this.wmBulkModal && this.wmBulkModal.alsoCompress),
                    selectAll: false
                };
                function mapItems(list) {
                    return (list || []).filter(function (it) { return it && it.id; }).map(function (it) {
                        return {
                            id: it.id,
                            title: it.title || ('#' + it.id),
                            thumb: it.thumb || it.url,
                            url: it.url,
                            selected: true,
                            status: 'idle',
                            statusText: self.isRtl ? 'آماده' : 'Ready'
                        };
                    });
                }
                function done(items) {
                    self.wmBulkModal.items = items;
                    self.wmBulkModal.selectAll = items.length > 0;
                    self.wmBulkModal.progressText = items.length
                        ? (self.isRtl ? (items.length + ' تصویر — انتخاب کنید و شروع کنید') : (items.length + ' images — select & start'))
                        : (self.isRtl ? 'تصویری یافت نشد' : 'No images');
                }
                var cached = ((this.mediaLib && this.mediaLib.items) || []);
                if (cached.length) {
                    done(mapItems(cached));
                    return;
                }
                this.mediaLib = this.mediaLib || {};
                this.mediaLib.loading = true;
                ajax('bankai_list_media_library', { page: 1, per_page: 80 }).then(function (res) {
                    var d = (res && res.data) ? res.data : (res || {});
                    var items = d.items || d.media || [];
                    self.mediaLib.items = items;
                    self.mediaLib.loading = false;
                    done(mapItems(items));
                }).catch(function () {
                    self.mediaLib.loading = false;
                    self.wmBulkModal.progressText = self.t('error');
                });
            },
            toggleWmBulkSelectAll: function () {
                var all = !!(this.wmBulkModal && this.wmBulkModal.selectAll);
                (this.wmBulkModal.items || []).forEach(function (r) { r.selected = all; });
            },
            startWmBulkSelected: function () {
                var self = this;
                var items = (this.wmBulkModal.items || []).filter(function (r) { return r.selected; });
                if (!items.length) {
                    this.showToast(this.isRtl ? 'حداقل یک تصویر انتخاب کنید' : 'Select at least one image', 'warning');
                    return;
                }
                var w = this.watermarkStudio || {};
                var payload = {
                    enabled: '1',
                    type: w.type || 'text',
                    position: w.position || 'bottom-right',
                    opacity: w.opacity != null ? w.opacity : 75,
                    text: w.text || '',
                    text_size_type: w.text_size_type || 'scale',
                    text_scale_percent: w.text_scale_percent || 3.5,
                    min_font_size: w.min_font_size || 12,
                    max_font_size: w.max_font_size || 72,
                    margin_percent: w.margin_percent != null ? w.margin_percent : 2.0,
                    text_size: w.text_size || 18,
                    text_color: w.text_color || '#FFFFFF',
                    text_font: w.text_font || 'default',
                    box_enabled: w.box_enabled ? '1' : '0',
                    box_padding: w.box_padding || 8,
                    box_radius: w.box_radius || 6,
                    box_color: w.box_color || '#000000',
                    box_opacity: w.box_opacity != null ? w.box_opacity : 45,
                    box_style: w.box_style || 'solid',
                    text_cover: w.text_cover || 'none',
                    custom_font_url: w.custom_font_url || '',
                    custom_font_name: w.custom_font_name || '',
                    custom_font_path: w.custom_font_path || '',
                    image_id: w.image_id || 0,
                    image_scale: w.image_scale || 20,
                    min_dimension: 0
                };
                this.wmBulkModal.running = true;
                this.wmBulkModal.progressText = this.isRtl ? 'ذخیره تنظیمات…' : 'Saving settings…';
                ajax('bankai_save_watermark_settings', payload).then(function () {
                    self.wmBulkModal.items = items.map(function (r) {
                        return Object.assign({}, r, { status: 'pending', statusText: self.isRtl ? 'در صف' : 'Queued' });
                    });
                    self.runWmBulkQueue(true);
                }).catch(function () {
                    self.wmBulkModal.items = items.map(function (r) {
                        return Object.assign({}, r, { status: 'pending', statusText: self.isRtl ? 'در صف' : 'Queued' });
                    });
                    self.runWmBulkQueue(true);
                });
            },

            runWmBulkQueue: function (apply) {
                var self = this;
                var items = (this.wmBulkModal.items || []).slice();
                if (!items.length) return;
                this.wmBulkModal.running = true;
                var alsoCompress = !!(this.wmBulkModal.alsoCompress);
                var i = 0, ok = 0, fail = 0;
                function processOne() {
                    if (i >= items.length) {
                        self.wmBulkModal.running = false;
                        self.wmBulkModal.progress = 100;
                        self.wmBulkModal.progressText = self.isRtl
                            ? ('تمام شد: ' + ok + ' موفق · ' + fail + ' ناموفق')
                            : ('Done: ' + ok + ' ok · ' + fail + ' fail');
                        self.showToast(self.wmBulkModal.progressText, fail ? 'warning' : 'success');
                        return;
                    }
                    var row = items[i];
                    row.status = 'work';
                    row.statusText = self.isRtl
                        ? (alsoCompress ? 'واترمارک + فشرده…' : 'در حال واترمارک…')
                        : (alsoCompress ? 'WM + compress…' : 'Applying…');
                    self.wmBulkModal.items = items.slice();
                    self.wmBulkModal.progress = Math.round((i / items.length) * 100);
                    self.wmBulkModal.progressText = (i + 1) + ' / ' + items.length + ' — ' + (row.title || '');

                    var payload = {
                        ids: JSON.stringify([row.id]),
                        apply: apply ? '1' : '0',
                        replace: '1',
                        also_compress: alsoCompress ? '1' : '0'
                    };
                    // single-id endpoint with extended timeout via fetch
                    var c = (typeof cfg === 'function') ? cfg() : (window.bankaiData || window.bankaiCoreData || {});
                    var body = new FormData();
                    body.append('action', 'bankai_bulk_watermark_ids');
                    try {
                        var w = self.watermarkStudio || {};
                        body.append('wm_settings', JSON.stringify({
                            enabled: true,
                            type: w.type || 'text',
                            position: w.position || 'bottom-right',
                            opacity: w.opacity != null ? w.opacity : 75,
                            text: w.text || '',
                            text_size: w.text_size || 18,
                            text_color: w.text_color || '#FFFFFF',
                            text_font: w.text_font || 'default',
                            box_enabled: !!w.box_enabled,
                            box_padding: w.box_padding || 8,
                            box_radius: w.box_radius || 6,
                            box_color: w.box_color || '#000000',
                            box_opacity: w.box_opacity != null ? w.box_opacity : 45,
                            box_style: w.box_style || 'solid',
                            text_cover: w.text_cover || 'none',
                            custom_font_url: w.custom_font_url || '',
                            custom_font_name: w.custom_font_name || '',
                            custom_font_path: w.custom_font_path || '',
                            image_id: w.image_id || 0,
                            image_scale: w.image_scale || 20,
                            min_dimension: 0
                        }));
                    } catch (e) {}
                    body.append('nonce', c.adminNonce || c.nonce || '');
                    Object.keys(payload).forEach(function (k) { body.append(k, payload[k]); });

                    var controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                    var timer = setTimeout(function () { if (controller) controller.abort(); }, 90000);

                    fetch(c.ajaxUrl || window.ajaxurl || '', {
                        method: 'POST',
                        body: body,
                        credentials: 'same-origin',
                        signal: controller ? controller.signal : undefined
                    }).then(function (r) {
                        return r.text().then(function (txt) {
                            try { return JSON.parse(txt); }
                            catch (e) { return { success: false, data: { message: txt.slice(0, 120) } }; }
                        });
                    }).then(function (res) {
                        clearTimeout(timer);
                        if (res && res.success) {
                            row.status = 'done';
                            row.statusText = self.isRtl ? 'انجام شد' : 'Done';
                            ok++;
                        } else {
                            row.status = 'fail';
                            row.statusText = (res && res.data && res.data.message) || 'Error';
                            fail++;
                        }
                    }).catch(function (err) {
                        clearTimeout(timer);
                        row.status = 'fail';
                        row.statusText = (err && err.name === 'AbortError')
                            ? (self.isRtl ? 'تایم‌اوت' : 'Timeout')
                            : 'Error';
                        fail++;
                    }).finally(function () {
                        self.wmBulkModal.items = items.slice();
                        i++;
                        setTimeout(processOne, 200);
                    });
                }
                processOne();
            },

            wmPreviewSamples: function () {
                return [
                    (typeof bankaiAdmin !== 'undefined' && bankaiAdmin.pluginUrl ? bankaiAdmin.pluginUrl + 'assets/img/wm-preview.jpg' : ''),
                    'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#312e81"/><stop offset="1" stop-color="#7c3aed"/></linearGradient></defs><rect width="100%" height="100%" fill="url(#g)"/><text x="50%" y="48%" fill="white" font-size="42" text-anchor="middle" font-family="sans-serif">Bankai Preview</text><text x="50%" y="58%" fill="rgba(255,255,255,.7)" font-size="20" text-anchor="middle">نمونه پیش‌نمایش واترمارک</text></svg>')
                ];
            },
            wmPreviewSrc: function () {
                var w = this.watermarkStudio || {};
                if (w.preview_url) return w.preview_url;
                var samples = this.wmPreviewSamples();
                var i = (w._preview || 0) % samples.length;
                return samples[i] || samples[1];
            },
            onWmPreviewError: function (e) {
                try {
                    if (e && e.target) e.target.src = this.wmPreviewSamples()[1];
                } catch (err) {}
            },
            cycleWmPreview: function () {
                if (!this.watermarkStudio) return;
                this.watermarkStudio.preview_url = '';
                this.watermarkStudio._preview = (this.watermarkStudio._preview || 0) + 1;
            },
            pickWmPreviewImage: function () {
                var self = this;
                if (typeof wp === 'undefined' || !wp.media) {
                    self.showToast('کتابخانه رسانه در دسترس نیست', 'error');
                    return;
                }
                var frame = wp.media({ title: 'تصویر پیش‌نمایش واترمارک', multiple: false, library: { type: 'image' } });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    self.watermarkStudio.preview_url = (att.sizes && att.sizes.large ? att.sizes.large.url : att.url) || att.url;
                });
                frame.open();
            },
            uploadWmFont: function (ev) {
                var self = this;
                var file = ev.target && ev.target.files && ev.target.files[0];
                if (!file) return;
                var c = (typeof cfg === 'function') ? cfg() : (window.bankaiData || window.bankaiCoreData || {});
                var fd = new FormData();
                fd.append('action', 'bankai_upload_wm_font');
                fd.append('nonce', c.adminNonce || c.nonce || '');
                fd.append('font', file);
                self.wmFontBusy = true;
                fetch(c.ajaxUrl || window.ajaxurl || '', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.text().then(function (txt) {
                            try { return JSON.parse(txt); }
                            catch (e) { return { success: false, data: { message: txt.slice(0, 180) || 'Invalid response' } }; }
                        });
                    })
                    .then(function (res) {
                        if (res && res.success && res.data) {
                            if (!self.watermarkStudio) self.watermarkStudio = {};
                            self.watermarkStudio.custom_font_url = res.data.url || '';
                            self.watermarkStudio.custom_font_name = res.data.name || file.name;
                            self.watermarkStudio.custom_font_path = res.data.path || '';
                            self.watermarkStudio._fontTick = Date.now();
                            var fonts = Array.isArray(self.watermarkStudio.custom_fonts) ? self.watermarkStudio.custom_fonts.slice() : [];
                            var entry = {
                                id: 'cf_' + String(res.data.name || file.name).replace(/[^a-zA-Z0-9._-]/g, '_'),
                                name: res.data.name || file.name,
                                url: res.data.url || '',
                                path: res.data.path || ''
                            };
                            fonts = fonts.filter(function (f) { return f && f.name !== entry.name; });
                            fonts.push(entry);
                            self.watermarkStudio.custom_fonts = fonts;
                            self.watermarkStudio.text_font = entry.id;
                            try {
                                if (entry.url) {
                                    var st = document.getElementById('bankai-wm-font-face') || document.createElement('style');
                                    st.id = 'bankai-wm-font-face';
                                    st.textContent = (st.textContent || '') + '@font-face{font-family:"' + entry.id + '";src:url("' + entry.url + '");font-display:swap;}';
                                    document.head.appendChild(st);
                                }
                            } catch (e) {}
                            if (typeof self.ensureWmFontFace === 'function') self.ensureWmFontFace();
                            self.showToast((res.data.message) || (self.isRtl ? 'فونت آپلود شد' : 'Font uploaded'), 'success');
                        } else {
                            var msg = (res && res.data && res.data.message) ? res.data.message : self.t('error');
                            self.showToast(msg, 'error');
                        }
                    })
                    .catch(function (err) {
                        self.showToast((err && err.message) || self.t('error'), 'error');
                    })
                    .finally(function () {
                        self.wmFontBusy = false;
                        if (ev.target) ev.target.value = '';
                    });
            },
            wmBadgeStyle: function () {
                var w = this.watermarkStudio || {};
                var style = { opacity: ((w.opacity || 75) / 100) };
                var cover = w.text_cover || 'none';
                if (cover === 'width') { style.left = '0'; style.right = '0'; style.width = '100%'; style.maxWidth = '100%'; style.transform = 'none'; }
                if (cover === 'height') { style.top = '0'; style.bottom = '0'; style.height = '100%'; style.transform = 'none'; }
                if (cover === 'banner') {
                    style.left = '0'; style.right = '0'; style.bottom = '0'; style.top = 'auto';
                    style.width = '100%'; style.transform = 'none';
                    style.background = 'linear-gradient(to top, rgba(0,0,0,0.7), transparent)';
                    style.borderRadius = '0';
                }
                if (w.box_enabled && w.box_style === 'solid' && cover !== 'banner') {
                    var hex = w.box_color || '#000000';
                    var op = (w.box_opacity != null ? w.box_opacity : 45) / 100;
                    style.background = selfHexRgba(hex, op);
                    style.borderRadius = (w.box_radius || 6) + 'px';
                    style.padding = (w.box_padding || 8) + 'px';
                }
                if (w.box_style === 'gradient' && cover !== 'banner') {
                    var hex2 = w.box_color || '#000000';
                    style.background = 'linear-gradient(to top, ' + selfHexRgba(hex2, 0.75) + ', transparent)';
                    style.padding = (w.box_padding || 8) + 'px';
                }
                var fontMap = {
                    'default': 'Vazirmatn, Tahoma, sans-serif',
                    'vazirmatn-bold': 'Vazirmatn, Tahoma, sans-serif',
                    'tahoma': 'Tahoma, sans-serif',
                    'arial': 'Arial, sans-serif',
                    'georgia': 'Georgia, serif',
                    'roboto': 'Roboto, sans-serif',
                    'inter': 'Inter, sans-serif',
                    'custom': (w.custom_font_name ? ("'" + w.custom_font_name + "'") : 'Vazirmatn') + ', sans-serif'
                };
                style.fontFamily = fontMap[w.text_font] || fontMap.default;
                if (w.text_font === 'vazirmatn-bold') style.fontWeight = '800';
                if (w.text_color && w.text_color !== 'transparent') style.color = w.text_color;
                if (w.text_cover === 'banner') {
                    style.left = '0'; style.right = '0'; style.bottom = '0'; style.top = 'auto';
                    style.width = '100%'; style.maxWidth = 'none'; style.margin = '0';
                    style.borderRadius = '0'; style.transform = 'none';
                }
                return style;
            },

            setWmPosition: function (pos) {
                var map = {
                    'top-left':'top-left','top-center':'top-center','top-right':'top-right',
                    'center-left':'center-left','middle-left':'center-left','mid-left':'center-left',
                    'center':'center',
                    'center-right':'center-right','middle-right':'center-right','mid-right':'center-right',
                    'bottom-left':'bottom-left','bottom-center':'bottom-center','btm-center':'bottom-center',
                    'bottom-right':'bottom-right','btm-right':'bottom-right'
                };
                if (!this.watermarkStudio) return;
                this.watermarkStudio.position = map[pos] || pos;
            },
            getWatermarkPositionStyle: function () {
                var map = {
                    'top-left': { top: '12px', left: '12px', right: 'auto', bottom: 'auto', transform: 'none' },
                    'top-center': { top: '12px', left: '50%', right: 'auto', bottom: 'auto', transform: 'translateX(-50%)' },
                    'top-right': { top: '12px', right: '12px', left: 'auto', bottom: 'auto', transform: 'none' },
                    'center-left': { top: '50%', left: '12px', right: 'auto', bottom: 'auto', transform: 'translateY(-50%)' },
                    'middle-left': { top: '50%', left: '12px', right: 'auto', bottom: 'auto', transform: 'translateY(-50%)' },
                    'center': { top: '50%', left: '50%', right: 'auto', bottom: 'auto', transform: 'translate(-50%,-50%)' },
                    'center-right': { top: '50%', right: '12px', left: 'auto', bottom: 'auto', transform: 'translateY(-50%)' },
                    'middle-right': { top: '50%', right: '12px', left: 'auto', bottom: 'auto', transform: 'translateY(-50%)' },
                    'bottom-left': { bottom: '12px', left: '12px', top: 'auto', right: 'auto', transform: 'none' },
                    'bottom-center': { bottom: '12px', left: '50%', top: 'auto', right: 'auto', transform: 'translateX(-50%)' },
                    'bottom-right': { bottom: '12px', right: '12px', top: 'auto', left: 'auto', transform: 'none' }
                };
                var pos = this.watermarkStudio && this.watermarkStudio.position;
                return map[pos] || map['bottom-right'];
            },
            wmTextBadgeStyle: function () {
                var w = this.watermarkStudio || {};
                var tick = w._fontTick || 0;
                var fam = 'Vazirmatn, Tahoma, sans-serif';
                if (w.text_font === 'custom' && w.custom_font_url) {
                    fam = "'BankaiWmCustom', Vazirmatn, Tahoma, sans-serif";
                    this.ensureWmFontFace();
                } else if (w.text_font === 'vazirmatn' || w.text_font === 'vazirmatn-bold') {
                    fam = "Vazirmatn, Tahoma, sans-serif";
                } else if (w.text_font === 'tahoma') {
                    fam = "Tahoma, sans-serif";
                } else if (w.text_font === 'arial') {
                    fam = "Arial, sans-serif";
                }
                var boxOp = (w.box_opacity != null ? w.box_opacity : 45) / 100;
                var bg = 'transparent';
                if (w.box_enabled) {
                    var hex = w.box_color || '#000000';
                    var r = parseInt(hex.slice(1,3),16)||0, g=parseInt(hex.slice(3,5),16)||0, b=parseInt(hex.slice(5,7),16)||0;
                    bg = 'rgba('+r+','+g+','+b+','+boxOp+')';
                }

                // Calculate font size according to text_size_type (scale vs fixed)
                var fontSizePx = 18;
                if ((w.text_size_type || 'scale') === 'scale') {
                    var pct = parseFloat(w.text_scale_percent || 3.5);
                    // Mockup canvas image simulated width = 1200px
                    var calc = Math.round(1200 * (pct / 100));
                    var minF = parseInt(w.min_font_size || 12, 10);
                    var maxF = parseInt(w.max_font_size || 72, 10);
                    fontSizePx = Math.max(minF, Math.min(maxF, calc));
                    // Scale down for UI mockup container (~380px actual width)
                    fontSizePx = Math.max(10, Math.round(fontSizePx * 0.45));
                } else {
                    fontSizePx = Math.max(10, Math.min(100, parseInt(w.text_size || 18, 10)));
                }

                return {
                    fontFamily: fam,
                    fontSize: fontSizePx + 'px',
                    color: w.text_color || '#fff',
                    background: bg,
                    padding: w.box_enabled ? ((w.box_padding || 8) + 'px') : '0',
                    borderRadius: (w.box_radius || 6) + 'px',
                    fontWeight: (w.text_font === 'vazirmatn-bold' ? '800' : '700'),
                    direction: 'rtl',
                    unicodeBidi: 'plaintext',
                    textAlign: 'center',
                    whiteSpace: 'nowrap',
                    backdropFilter: w.box_enabled ? 'blur(6px)' : 'none',
                    opacity: (w.opacity != null ? (w.opacity/100) : 0.9)
                };
            },
            wmPreviewStyle: function () {
                var pos = this.getWatermarkPositionStyle();
                var o = (this.watermarkStudio && this.watermarkStudio.opacity != null) ? this.watermarkStudio.opacity : 75;
                return Object.assign({}, pos, {
                    opacity: String(Number(o) / 100),
                    position: 'absolute',
                    zIndex: '3',
                    pointerEvents: 'none'
                });
            },

            isCdnModuleOn: function () {
                var s = this.mediaState || {};
                var keys = Object.keys(s);
                for (var i = 0; i < keys.length; i++) {
                    var k = keys[i];
                    if (/cdn|cloud|r2|bunny|s3|arvan|abr|liara|pars/i.test(k) && s[k]) return true;
                }
                // also show if base_url already configured
                if (this.cdnSettings && this.cdnSettings.base_url) return true;
                return false;
            },
            toggleMediaModule: function (id) {
                var self = this;
                var enabled = !!this.mediaState[id];
                rest('/module/' + encodeURIComponent(id), {
                    method: 'POST',
                    body: JSON.stringify({ enabled: enabled })
                }).then(function (data) {
                    if (data && data.success) {
                        self.showToast(data.message || self.t('saved'), 'success');
                    } else {
                        self.mediaState[id] = !enabled;
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.mediaState[id] = !enabled;
                    self.showToast(self.t('error'), 'error');
                });
            },

            openMediaDrawer: function (id, title) {
                this.mediaDrawer = { show: true, id: id, title: title || id };
            },

            saveMediaDrawerSettings: function () {
                this.mediaDrawer.show = false;
                this.saveWatermarkStudio();
            },

            pickWatermarkImage: function () {
                var self = this;
                if (typeof wp === 'undefined' || !wp.media) {
                    self.showToast(self.isRtl ? 'کتابخانه رسانه در دسترس نیست' : 'Media library unavailable', 'error');
                    return;
                }
                var frame = wp.media({
                    title: self.isRtl ? 'انتخاب لوگوی واترمارک' : 'Select watermark logo',
                    button: { text: self.isRtl ? 'انتخاب' : 'Use this' },
                    multiple: false
                });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    self.watermarkStudio.image_id = att.id;
                    self.watermarkStudio.image_url = (att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url) || att.url;
                    self.watermarkStudio.type = 'image';
                });
                frame.open();
            },

            
            toggleSelectAllMedia: function () {
                var items = (this.mediaLib && this.mediaLib.items) || [];
                if (!items.length) return;
                var all = items.every(function (i) { return i.selected; });
                items.forEach(function (i) { i.selected = !all; });
                this.mediaLib.items = items.slice();
            },
            selectedMediaIds: function () {
                return ((this.mediaLib && this.mediaLib.items) || []).filter(function (i) { return i.selected; }).map(function (i) { return i.id; });
            },
            runBulkWatermark: function (apply) {
                var self = this;
                var ids = this.selectedMediaIds();
                if (!ids.length) {
                    this.showToast(this.isRtl ? 'حداقل یک تصویر را انتخاب کنید.' : 'Select at least one image.', 'warning');
                    return;
                }
                if (apply && this.watermarkStudio && !this.watermarkStudio.enabled) {
                    this.showToast(this.isRtl ? 'ابتدا سرویس واترمارک را فعال و ذخیره کنید.' : 'Enable and save watermark service first.', 'warning');
                    return;
                }
                this.mediaLib.busy = true;
                this.mediaLib.mode = 'wm';
                this.mediaLib.progress = 5;
                this.mediaLib.progressText = apply
                    ? (this.isRtl ? 'اعمال واترمارک…' : 'Applying watermark…')
                    : (this.isRtl ? 'حذف واترمارک…' : 'Removing watermark…');
                ajax('bankai_bulk_watermark_ids', {
                    ids: ids,
                    apply: apply ? '1' : '0'
                }).then(function (res) {
                    var d = res.data || res;
                    if (res.success) {
                        self.showToast((d && d.message) || self.t('saved'), 'success');
                        self.mediaLib.progress = 100;
                        if (self.loadMediaLibrary) self.loadMediaLibrary(true);
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.mediaLib.busy = false;
                    setTimeout(function () { self.mediaLib.progress = 0; }, 1200);
                });
            },
            watermarkSingle: function (img, apply) {
                if (!img || !img.id) return;
                var self = this;
                img.working = true;
                ajax('bankai_bulk_watermark_ids', {
                    ids: [img.id],
                    apply: apply ? '1' : '0'
                }).then(function (res) {
                    var d = res.data || res;
                    if (res.success) {
                        img.done = true;
                        self.showToast((d && d.message) || self.t('saved'), 'success');
                        if (self.loadMediaLibrary) self.loadMediaLibrary(false);
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).finally(function () { img.working = false; });
            },
openBulkWatermark: function () {
                this.mediaSubTab = 'compress';
                if (this.loadMediaLibrary) this.loadMediaLibrary(true);
                this.showToast(this.isRtl
                    ? 'تصاویر را انتخاب کنید و «اعمال واترمارک روی انتخاب» را بزنید.'
                    : 'Select images then apply watermark.',
                    'info');
            },
            saveWatermarkStudio: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                var w = this.watermarkStudio;
                ajax('bankai_save_watermark_settings', {
                    enabled: w.enabled ? '1' : '0',
                    type: w.type,
                    position: w.position,
                    opacity: w.opacity,
                    text: w.text,
                    text_size: w.text_size || 18,
                    text_color: w.text_color || '#FFFFFF',
                    text_font: w.text_font || 'default',
                    box_enabled: w.box_enabled ? '1' : '0',
                    box_padding: w.box_padding || 8,
                    box_radius: w.box_radius || 6,
                    box_color: w.box_color || '#000000',
                    box_opacity: w.box_opacity != null ? w.box_opacity : 45,
                    box_style: w.box_style || 'solid',
                    text_cover: w.text_cover || 'none',
                    custom_font_url: w.custom_font_url || '',
                    custom_font_name: w.custom_font_name || '',
                    preview_url: w.preview_url || '',
                    image_id: w.image_id || 0,
                    image_scale: w.image_scale || 20,
                    exclude_patterns: w.exclude_patterns || '',
                    quality: w.quality,
                    min_dimension: w.min_dimension,
                    apply_upload: w.apply_upload ? '1' : '0',
                    apply_content: w.apply_content ? '1' : '0',
                    lazy_load: w.lazy_load ? '1' : '0',
                    strip_exif: w.strip_exif ? '1' : '0',
                    convert_webp: w.convert_webp ? '1' : '0',
                    keep_original: w.keep_original ? '1' : '0'
                }).then(function (res) {
                    var d = res.data || res;
                    if (res.success) {
                        self.showToast((d && d.message) || self.t('saved'), 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.busy = false;
                });
            },

            bulkConvertMedia: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                this.showToast(this.isRtl ? 'شروع تبدیل گروهی…' : 'Bulk convert started…', 'success');
                ajax('bankai_bulk_convert_media', {}).then(function (res) {
                    var d = res.data || res;
                    if (res.success) {
                        self.showToast((d && d.message) || self.t('saved'), 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.busy = false;
                });
            },

            regenerateThumbnails: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                this.showToast(this.isRtl ? 'بازتولید بندانگشتی…' : 'Regenerating…', 'success');
                ajax('bankai_regenerate_thumbs', {}).then(function (res) {
                    var d = res.data || res;
                    if (res.success) {
                        self.showToast((d && d.message) || self.t('saved'), 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.busy = false;
                });
            },

            /* ---- AI Studio ---- */
            toggleAiModule: function (id) {
                var self = this;
                var enabled = !!this.aiState[id];
                rest('/module/' + encodeURIComponent(id), {
                    method: 'POST',
                    body: JSON.stringify({ enabled: enabled })
                }).then(function (data) {
                    if (data && data.success) self.showToast(data.message || self.t('saved'), 'success');
                    else {
                        self.aiState[id] = !enabled;
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.aiState[id] = !enabled;
                    self.showToast(self.t('error'), 'error');
                });
            },

            openAiDrawer: function (id, title) {
                this.aiDrawer = { show: true, id: id, title: title || id };
            },

            saveAiDrawerSettings: function () {
                this.aiDrawer.show = false;
                this.showToast(this.t('saved'), 'success');
            },

            saveAiProvider: function (id, key, model) {
                var self = this;
                var payload = {
                    model_id: id,
                    model_value: model || '',
                    default_provider: this.aiStudio.defaultProvider || id
                };
                var raw = (key || '').trim();
                if (raw && raw.indexOf('\u2022') === -1 && raw.indexOf('•') === -1 && raw !== '__unchanged__') {
                    payload[id] = raw;
                } else {
                    payload[id] = '__unchanged__';
                }
                return ajax('bankai_save_ai_keys', payload, 15000).then(function (res) {
                    var d = (res && res.data) || res || {};
                    if (res && res.success) {
                        self.showToast(d.message || self.t('saved'), 'success');
                        var has = false;
                        var masked = '';
                        (d.providers || []).forEach(function (p) {
                            if (p.id === id) {
                                has = !!p.has_key;
                                masked = p.masked_key || '';
                            }
                        });
                        if (!has && raw && raw.indexOf('•') === -1 && raw !== '__unchanged__') has = true;
                        return {
                            status: has ? 'ready' : 'missing_key',
                            label: has ? 'Ready' : 'No API Key',
                            masked_key: masked
                        };
                    }
                    self.showToast((d && d.message) || self.t('error'), 'error');
                    return null;
                }).catch(function (e) {
                    self.showToast((e && e.message) || self.t('error'), 'error');
                    return null;
                });
            },

            clearAiProviderKey: function (id) {
                var self = this;
                var payload = {};
                payload[id] = '';
                payload['clear_' + id] = '1';
                return ajax('bankai_save_ai_keys', payload, 12000).then(function (res) {
                    if (res && res.success) {
                        self.showToast(self.isRtl ? 'کلید حذف شد' : 'Key cleared', 'success');
                        return { status: 'missing_key', label: 'No API Key', masked_key: '' };
                    }
                    self.showToast(self.t('error'), 'error');
                    return null;
                });
            },

            saveDefaultProvider: function () {
                var self = this;
                ajax('bankai_save_ai_models', {
                    default_provider: this.aiStudio.defaultProvider || 'gemini'
                }, 12000).then(function (res) {
                    if (res && res.success) {
                        self.showToast(self.isRtl ? 'ارائه‌دهنده پیش‌فرض ذخیره شد' : 'Default provider saved', 'success');
                    } else {
                        self.showToast(self.t('error'), 'error');
                    }
                });
            },

            testAiProvider: function (id) {
                var self = this;
                return ajax('bankai_test_ai_connections', { provider: id || '' }, 18000).then(function (res) {
                    var d = (res && res.data) || {};
                    var list = d.providers || [];
                    var row = null;
                    for (var i = 0; i < list.length; i++) {
                        if (list[i].id === id) { row = list[i]; break; }
                    }
                    row = row || list[0] || null;
                    if (row) {
                        var msg = row.label + (row.detail ? ' — ' + row.detail : '');
                        self.showToast(msg, row.ok ? 'success' : 'error');
                        return row;
                    }
                    self.showToast((d.message) || self.t('error'), 'error');
                    return null;
                }).catch(function () {
                    self.showToast(self.isRtl ? 'تست ناموفق یا قطع ارتباط' : 'Test failed', 'error');
                    return null;
                });
            },

            testAiConnections: function () {
                var self = this;
                if (this.busy) return;
                this.busy = true;
                this.showToast(this.isRtl ? 'در حال بررسی اتصالات…' : 'Testing connections…', 'success');
                ajax('bankai_test_ai_connections', {}, 25000).then(function (res) {
                    var list = (res && res.data && res.data.providers) || [];
                    var ok = list.filter(function (p) { return p.ok; }).length;
                    self.showToast(
                        (self.isRtl ? 'متصل: ' : 'Connected: ') + ok + ' / ' + list.length,
                        ok > 0 ? 'success' : 'error'
                    );
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () { self.busy = false; });
            },

            generateAiPrompt: function () {
                var self = this;
                var prompt = (this.aiSandbox.promptInput || '').trim();
                if (!prompt) {
                    this.showToast(this.isRtl ? 'پرامپت خالی است' : 'Empty prompt', 'error');
                    return;
                }
                this.aiSandbox.isGenerating = true;
                this.aiSandbox.aiResult = '';
                this.showToast(this.isRtl ? 'در حال تولید…' : 'Generating…', 'success');
                ajax('bankai_generate_ai_prompt', {
                    prompt_input: prompt,
                    provider: this.aiStudio.defaultProvider || '',
                    default_model: ''
                }, 60000).then(function (res) {
                    var d = (res && res.data) || res || {};
                    if (res && res.success && d.result) {
                        self.aiSandbox.aiResult = d.result;
                        self.showToast(self.isRtl ? 'پاسخ دریافت شد' : 'Response ready', 'success');
                    } else {
                        self.showToast((d && d.message) || self.t('error'), 'error');
                    }
                }).catch(function () {
                    self.showToast(self.t('error'), 'error');
                }).finally(function () {
                    self.aiSandbox.isGenerating = false;
                });
            },

            runSeoAiTask: function (task, ctx) {
                var self = this;
                ctx = ctx || {};
                return ajax('bankai_ai_seo_task', {
                    task: task,
                    title: ctx.title || '',
                    content: ctx.content || '',
                    focus_keyword: ctx.focus_keyword || '',
                    locale: ctx.locale || (self.isRtl ? 'fa_IR' : 'en_US'),
                    provider: self.aiStudio.defaultProvider || ''
                }).then(function (res) {
                    if (res && res.success) return res.data || res;
                    throw new Error((res && res.data && res.data.message) || (res && res.message) || 'AI error');
                });
            },

        };
    };
})();
