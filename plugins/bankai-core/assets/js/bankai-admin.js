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
        navTheme: 'کیت‌های قالب',
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
        navTheme: 'Theme Kits',
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
            ['seo_engine', 'speed_cache', 'media_watermark', 'ai_studio', 'llms_txt', 'theme_kits'].forEach(function (k) {
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
                    'theme-kits': 'theme_kits'
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
                    'theme-kits': 'theme_kits'
                };
                var modKey = map[tab];
                if (!modKey) return true;
                return this.moduleActive[modKey] !== false;
            },

            showToast: function (message, type) {
                this.toast = { show: true, message: message || '', type: type || 'success' };
                var self = this;
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(function () { self.toast.show = false; }, 3200);
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
                            theme_kits: 'theme-kits'
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
                        // refresh soft stats
                        if (self.speedStats) {
                            self.speedStats.hit_ratio = self.speedStats.hit_ratio || '—';
                        }
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
                        if (d && typeof d.deleted_revisions === 'number') {
                            msg += ' (' + d.deleted_revisions + ' revisions)';
                            self.speedStats.revisions = Math.max(0, (self.speedStats.revisions || 0) - d.deleted_revisions);
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
