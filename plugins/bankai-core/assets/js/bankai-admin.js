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
            moduleActive: moduleActive,

            seoDrawer: { show: false, id: '', title: '' },
            seoAudit: { show: false, running: false, score: 0, items: [] },
            seoWizard: { show: false, step: 1 },

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
                var self = this;
                this.seoAudit.show = true;
                this.seoAudit.running = true;
                this.seoAudit.items = [];
                rest('/seo/site-audit', { method: 'GET' })
                    .then(function (data) {
                        if (data && data.success && data.data) {
                            self.seoAudit.items = data.data.items || [];
                            self.seoAudit.score = data.data.score || 0;
                        } else {
                            self.seoAudit.items = self.buildClientAudit();
                            self.seoAudit.score = self.scoreFromItems(self.seoAudit.items);
                        }
                    })
                    .catch(function () {
                        self.seoAudit.items = self.buildClientAudit();
                        self.seoAudit.score = self.scoreFromItems(self.seoAudit.items);
                    })
                    .finally(function () { self.seoAudit.running = false; });
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

            openSeoWizard: function () { this.seoWizard = { show: true, step: 1 }; },
            wizardNext: function () { if (this.seoWizard.step < 4) this.seoWizard.step++; },
            wizardPrev: function () { if (this.seoWizard.step > 1) this.seoWizard.step--; },
            wizardFinish: function () {
                var self = this;
                ['auto_meta', 'sitemap_pro', 'canonical_guard', 'open_graph_ai'].forEach(function (id) {
                    self.seoState[id] = true;
                    rest('/module/' + encodeURIComponent(id), { method: 'POST', body: JSON.stringify({ enabled: true }) });
                });
                this.seoWizard.show = false;
                this.showToast(this.isRtl ? 'پیکربندی پایه اعمال شد' : 'Base config applied', 'success');
            },

            saveIntegration: function (fields) {
                var self = this;
                var key = fields._key || 'all';
                this.integSaving[key] = true;
                var payload = {};
                Object.keys(fields).forEach(function (k) {
                    if (k !== '_key') payload[k] = fields[k];
                });
                return rest('/seo/integrations', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }).then(function (data) {
                    if (data && data.success) {
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
                // no-op; state already hydrated
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
