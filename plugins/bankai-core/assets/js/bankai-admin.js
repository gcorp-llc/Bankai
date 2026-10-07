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
            enabled: wmSaved.enabled !== false,
            type: wmSaved.type || 'text',
            position: wmSaved.position || 'bottom-right',
            opacity: wmSaved.opacity || 75,
            text: wmSaved.text || '© BANKAI MEDIA',
            image_id: wmSaved.image_id || 0,
            image_url: wmSaved.image_url || '',
            quality: wmSaved.quality || 82,
            min_dimension: wmSaved.min_dimension || 300,
            apply_upload: wmSaved.apply_upload !== false,
            apply_content: !!wmSaved.apply_content,
            lazy_load: wmSaved.lazy_load !== false,
            strip_exif: wmSaved.strip_exif !== false,
            convert_webp: wmSaved.convert_webp !== false,
            keep_original: wmSaved.keep_original !== false
        };

        var moduleActive = {};
        (st.coreModules || []).forEach(function (m) {
            moduleActive[m.key] = m.active !== false;
        });

        // Defaults if empty
        if (!Object.keys(moduleActive).length) {
            ['seo_engine', 'speed_cache', 'media_watermark', 'ai_studio', 'llms_txt', 'jalali_calendar'].forEach(function (k) {
                moduleActive[k] = true;
            });
        }

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
            compressOpts: { format: 'webp', quality: 82, maxWidth: 1600 },
            mediaLib: {
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
                imageProviders: []
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

            setTab: function (tab) {
                var map = {
                    'seo-engine': 'seo_engine',
                    'speed-cache': 'speed_cache',
                    'media-watermark': 'media_watermark',
                    'ai-studio': 'ai_studio',
                    'jalali-calendar': 'jalali_calendar'
                };
                var modKey = map[tab];
                if (modKey && this.moduleActive[modKey] === false) {
                    this.showToast(this.t('moduleDisabled'), 'error');
                    return;
                }
                var self = this;
                this.pageLoading = true;
                this.pageProgress = 40;
                setTimeout(function () {
                    self.activeTab = tab;
                    self.pageProgress = 100;
                    self.pageLoading = false;
                    self.closeMobileMenu();
                    if (tab === 'seo-engine') {
                        if (typeof self.runSeoAuditInline === 'function') self.runSeoAuditInline();
                        if (self.seoPanel === 'articles' && typeof self.loadArticles === 'function') self.loadArticles();
                    }
                }, 160);
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
            toggleCoreModule: function (key, enabled) {
                var self = this;
                return rest('/core-module/' + encodeURIComponent(key), {
                    method: 'POST',
                    body: JSON.stringify({ enabled: !!enabled })
                }).then(function (data) {
                    if (data && data.success) {
                        self.moduleActive[key] = !!enabled;
                        self.showToast(data.message || self.t('saved'), 'success');
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
                        var chip = document.getElementById('bk-core-mod-count');
                        if (chip && typeof data.active_count === 'number') {
                            chip.innerHTML = data.active_count + ' / 6 <span>فعال</span>';
                        }
                    } else {
                        self.moduleActive[key] = !enabled;
                        self.showToast((data && data.message) || self.t('error'), 'error');
                    }
                    return data;
                }).catch(function () {
                    self.moduleActive[key] = !enabled;
                    self.showToast(self.t('error'), 'error');
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
                var self = this;
                if (this.seoImport.running) return;
                this.seoImport.running = true;
                this.seoImport.progress = 0;
                this.seoImport.page = 0;
                this.seoImport.totalPages = 0;
                this.seoImport.importedCount = 0;
                this.seoImport.skippedCount = 0;
                this.seoImport.report = false;
                this.seoImport.stepText = 'شروع همگام‌سازی…';
                this.showToast('همگام‌سازی Rank Math / Yoast آغاز شد', 'info');

                function runPage(page) {
                    var c = cfg();
                    var fd = new FormData();
                    fd.append('action', 'bankai_seo_import_run');
                    fd.append('nonce', c.adminNonce || c.nonce || '');
                    fd.append('page', String(page));
                    fd.append('per_page', '15');
                    fd.append('overwrite', self.seoImport.overwrite ? '1' : '0');
                    fd.append('prefer', self.seoImport.prefer || 'auto');
                    self.seoImport.stepText = 'در حال استخراج صفحه ' + page + '…';
                    return fetch(c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST', credentials: 'same-origin', body: fd
                    }).then(function (r) { return r.json(); }).then(function (j) {
                        if (!j || !j.success) {
                            throw new Error((j && j.data && j.data.message) || 'خطا در همگام‌سازی');
                        }
                        var d = j.data || {};
                        self.seoImport.page = d.page || page;
                        self.seoImport.totalPages = d.total_pages || 0;
                        self.seoImport.importedCount += (d.imported || 0);
                        self.seoImport.skippedCount += (d.skipped || 0);
                        var tp = d.total_pages || 1;
                        self.seoImport.progress = Math.min(100, Math.round(((d.page || page) / tp) * 100));
                        self.showToast('صفحه ' + (d.page || page) + ': ' + (d.imported || 0) + ' مقاله وارد شد', 'success');
                        if (d.done || (d.page || page) >= tp) {
                            return null;
                        }
                        return runPage((d.page || page) + 1);
                    });
                }

                runPage(1).then(function () {
                    self.seoImport.running = false;
                    self.seoImport.progress = 100;
                    self.seoImport.stepText = 'همگام‌سازی کامل شد';
                    self.seoImport.report = true;
                    self.showToast('✓ همگام‌سازی تمام شد — ' + self.seoImport.importedCount + ' مقاله', 'success');
                    self.loadMissingSeo();
                }).catch(function (e) {
                    self.seoImport.running = false;
                    self.seoImport.stepText = 'خطا: ' + ((e && e.message) || 'نامشخص');
                    self.showToast((e && e.message) || 'خطا', 'error');
                });
            },
            loadMissingSeo: function () {
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


            getWatermarkPositionStyle: function () {
                var map = {
                    'top-left': { top: '12px', left: '12px', right: 'auto', bottom: 'auto' },
                    'top-center': { top: '12px', left: '50%', transform: 'translateX(-50%)', right: 'auto', bottom: 'auto' },
                    'top-right': { top: '12px', right: '12px', left: 'auto', bottom: 'auto' },
                    'center-left': { top: '50%', left: '12px', transform: 'translateY(-50%)', right: 'auto', bottom: 'auto' },
                    'center': { top: '50%', left: '50%', transform: 'translate(-50%,-50%)', right: 'auto', bottom: 'auto' },
                    'center-right': { top: '50%', right: '12px', transform: 'translateY(-50%)', left: 'auto', bottom: 'auto' },
                    'bottom-left': { bottom: '12px', left: '12px', top: 'auto', right: 'auto' },
                    'bottom-center': { bottom: '12px', left: '50%', transform: 'translateX(-50%)', top: 'auto', right: 'auto' },
                    'bottom-right': { bottom: '12px', right: '12px', top: 'auto', left: 'auto' }
                };
                var s = map[this.watermarkStudio.position] || map['bottom-right'];
                return Object.keys(s).map(function (k) { return k + ':' + s[k]; }).join(';');
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
                    image_id: w.image_id || 0,
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
