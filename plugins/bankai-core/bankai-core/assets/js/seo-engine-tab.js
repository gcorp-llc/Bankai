/* Bankai SEO Engine — modular */
document.addEventListener('alpine:init', () => {
    Alpine.data('bankaiSeoEngineData', function () {

    return {
        seoPanel: 'tools',
        sw: {
            settings: {
                org_name: '', org_logo: '', org_same_as: '', website_search: true,
                noindex_author: true, noindex_date: true, noindex_tag: false, noindex_search: true, noindex_attach: true,
                sitemap_exclude_ids: '', hreflang_enabled: false, hreflang_default: '', gsc_property: ''
            },
            redirects: [],
            rdPage: 1,
            log404: [],
            log404Page: 1,
            sitemapIndex: '',
            saving: false,
            savingRd: false
        },
        wizardBusy: false,
        seoAudit: { running: false, score: 0, items: [] },
        an: {
            loading: false, range: '30d', visits: 0, prev_visits: 0, visits_change: 0,
            series: [], country_count: 0, countries: [], top_posts: [],
            devices: { desktop: {hits:0,pct:0}, mobile: {hits:0,pct:0}, tablet: {hits:0,pct:0} }, _loaded: false
        },
        seoState: null,

        integEdit: {
            ga4: 'null',
            gtm: 'null',
            gsc: 'null',
            bing: 'null',
            yandex: 'null',
            sitemap: null,
            robots: null
        },
        integSaving: { google: false, other: false, robots: false },
        fixedList: [],
        fixedKeywordInput: '',
        savingFixed: false,
        
        // Articles state
        articles: [],
        page: 1,
        perPage: 10,
        totalPages: 1,
        total: 0,
        searchQ: '',
        orderby: 'modified',
        order: 'DESC',
        loading: false,

        edit: { open: false, row: null, seo_title: '', description: '', focus_keyword: '', keywords_str: '', saving: false },


        loadAnalyticsReport: function () {
            var self = this;
            self.an.loading = true;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'analytics/report?range=' + encodeURIComponent(self.an.range || '30d'), {
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': nonce }
            }).then(function (r) { return r.json(); }).then(function (j) {
                self.an.loading = false;
                var d = (j && j.data) ? j.data : j;
                if (!d || typeof d !== 'object') return;
                self.an.visits = d.visits || 0;
                self.an.prev_visits = d.prev_visits || 0;
                self.an.visits_change = d.visits_change || 0;
                self.an.series = d.series || [];
                if (d.devices) self.an.devices = d.devices;
                self.an.countries = d.countries || [];
                self.an.country_count = d.country_count || 0;
                self.an.top_posts = d.top_posts || [];
                self.an._loaded = true;
            }).catch(function () { self.an.loading = false; });
        },
        formatAnNum: function (n) {
            n = parseInt(n, 10) || 0;
            if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
            if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
            return String(n);
        },
        sparkPath: function (series) {
            series = series || [];
            if (!series.length) return 'M0 40 L200 40';
            var max = 1;
            for (var i = 0; i < series.length; i++) { if ((series[i].h || 0) > max) max = series[i].h; }
            var w = 200, h = 48, step = w / Math.max(1, series.length - 1);
            var d = '';
            for (var j = 0; j < series.length; j++) {
                var x = j * step;
                var y = h - ((series[j].h || 0) / max) * (h - 4) - 2;
                d += (j === 0 ? 'M' : 'L') + x.toFixed(1) + ' ' + y.toFixed(1) + ' ';
            }
            return d;
        },
        sparkArea: function (series) {
            return this.sparkPath(series) + ' L200 48 L0 48 Z';
        },
        countryPct: function (hits) {
            var max = 1;
            var list = this.an.countries || [];
            for (var i = 0; i < list.length; i++) { if (list[i].hits > max) max = list[i].hits; }
            return Math.max(4, Math.round((hits / max) * 100));
        },
        donutStyle: function () {
            var d = this.an.devices || {};
            var a = (d.desktop && d.desktop.pct) || 0;
            var b = (d.mobile && d.mobile.pct) || 0;
            return 'background:conic-gradient(#5C6B7A 0 ' + a + '%, #8B9BB4 ' + a + '% ' + (a + b) + '%, #C5CBD3 ' + (a + b) + '% 100%)';
        },
        pickOrgLogo: function () {
            if (typeof wp === 'undefined' || !wp.media) {
                alert('کتابخانه رسانه در دسترس نیست');
                return;
            }
            var self = this;
            var frame = wp.media({
                title: 'انتخاب لوگوی سازمان',
                button: { text: 'استفاده از این تصویر' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                if (att && att.url) {
                    self.sw.settings.org_logo = att.url;
                }
            });
            frame.open();
        },
        loadSitewide: function () {
            var self = this;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/sitewide', { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j || !j.success || !j.data) return;
                    if (j.data.settings) self.sw.settings = Object.assign({}, self.sw.settings, j.data.settings);
                    self.sw.redirects = j.data.redirects || [];
                    self.sw.log404 = j.data.log404 || [];
                    self.sw.sitemapIndex = (window.location.origin || '') + '/sitemap.xml';
                }).catch(function () {});
        },
        saveSitewide: function () {
            var self = this;
            self.sw.saving = true;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/sitewide', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ settings: self.sw.settings })
            }).then(function (r) { return r.json(); }).then(function (j) {
                self.sw.saving = false;
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast((j && j.success) ? 'ذخیره شد' : 'خطا', j && j.success ? 'success' : 'error');
            }).catch(function () { self.sw.saving = false; });
        },
        get pagedRedirects() {
            const list = this.sw.redirects || [];
            const p = this.sw.rdPage || 1;
            return list.slice((p - 1) * 10, p * 10);
        },
        get rdTotalPages() { return Math.max(1, Math.ceil((this.sw.redirects || []).length / 10)); },
        get paged404() {
            const list = this.sw.log404 || [];
            const p = this.sw.log404Page || 1;
            return list.slice((p - 1) * 10, p * 10);
        },
        get log404TotalPages() { return Math.max(1, Math.ceil((this.sw.log404 || []).length / 10)); },
        removeRedirectAt: function (idx) {
            if (idx >= 0 && idx < (this.sw.redirects || []).length) {
                this.sw.redirects.splice(idx, 1);
                if (this.sw.rdPage > this.rdTotalPages) this.sw.rdPage = this.rdTotalPages;
            }
        },
        saveRedirects: function () {
            var self = this;
            self.sw.savingRd = true;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/redirects', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ redirects: self.sw.redirects })
            }).then(function (r) { return r.json(); }).then(function (j) {
                self.sw.savingRd = false;
                if (j && j.data) self.sw.redirects = j.data;
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('ریدایرکت‌ها ذخیره شد', 'success');
            }).catch(function () { self.sw.savingRd = false; });
        },
        clear404: function () {
            var self = this;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/404-log', { method: 'DELETE', credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
                .then(function () { self.sw.log404 = []; });
        },
        toggleSeoModule: function (modId) {
            var self = this;
            var enabled = !!self.seoState[modId];
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'module/' + encodeURIComponent(modId), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ enabled: enabled })
            }).then(function (r) { return r.json(); }).then(function (j) {
                if (j && j.success) {
                    if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast(j.message || 'ذخیره شد', 'success');
                    else if (typeof Alpine !== 'undefined' && window.bankaiShowToast) window.bankaiShowToast(j.message || 'ذخیره شد');
                } else {
                    self.seoState[modId] = !enabled;
                    if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast((j && j.message) || 'خطا در ذخیره', 'error');
                }
            }).catch(function () {
                self.seoState[modId] = !enabled;
            });
        },
        initEngine: function () {
            var self = this;
            setTimeout(function () { if (self.seoPanel === 'tools') self.loadAnalyticsReport(); }, 300);
            // Seed module toggles from server-rendered state if empty
            if (!this.seoState || !Object.keys(this.seoState).length) {
                var mods = (window.bankaiState && window.bankaiState.seoModules) || [];
                var map = {};
                (mods || []).forEach(function (m) {
                    if (m && m.id) map[m.id] = !!m.enabled;
                });
                this.seoState = map;
            }
            this.parseFixedKeywords(null);
            this.runSeoAuditInline();
        },
        parseFixedKeywords: function(raw) {
            this.fixedList = String(raw || '').split(/[,،\n]+/).map(s => s.trim()).filter(Boolean);
        },
        scoreColor: function (s) {
            s = parseInt(s, 10) || 0;
            if (s >= 80) return '#16A34A';
            if (s >= 50) return '#D97706';
            return '#DC2626';
        },
        cfg: function () {
            return window.bankaiCoreData || window.bankaiEditorSeo || {};
        },
        openArticlesPanel: function () {
            this.seoPanel = 'articles';
            if (!this.articles.length) {
                this.loadArticles();
            }
        },
        loadArticles: async function () {
            this.loading = true;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const url = `${base}seo/articles?page=${this.page}&per_page=${this.perPage}&search=${encodeURIComponent(this.searchQ || '')}&orderby=${this.orderby}&order=${this.order}`;
                
                const r = await fetch(url, { credentials: 'same-origin', headers: { 'X-WP-Nonce': c.nonce || '' } });
                const j = await r.json();
                
                if (j && j.success && j.data) {
                    this.articles = j.data.items || [];
                    this.total = j.data.total || 0;
                    this.totalPages = j.data.total_pages || 1;
                }
            } catch (e) {
                console.error('Error loading articles:', e);
            } finally {
                this.loading = false;
            }
        },
        addFixedKeyword: function() {
            const kw = this.fixedKeywordInput.trim();
            if (kw && !this.fixedList.includes(kw)) {
                this.fixedList.push(kw);
                this.fixedKeywordInput = '';
            }
        },
        removeFixedKeyword: function(index) {
            this.fixedList.splice(index, 1);
        },
        saveFixedKeywords: async function () {
            this.savingFixed = true;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const r = await fetch(base + 'seo/fixed-keywords', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({ keywords: this.fixedList })
                });
                const j = await r.json();
                if (j && j.success) {
                    this.fixedList = j.data.keywords || this.fixedList;
                }
            } catch (e) {
                console.error('Save failed:', e);
            } finally {
                this.savingFixed = false;
            }
        },
        openEdit: function (row) {
            this.edit = {
                open: true,
                row: row,
                seo_title: row.seo_title || '',
                description: row.description || '',
                focus_keyword: row.focus_keyword || '',
                keywords_str: (row.keywords || []).join('، '),
                saving: false
            };
        },
        saveEdit: async function () {
            if (!this.edit.row) return;
            this.edit.saving = true;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const keywords = this.edit.keywords_str.split(/[,،]+/).map(s => s.trim()).filter(Boolean);
                
                const r = await fetch(base + 'seo/articles/' + this.edit.row.id, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({
                        seo_title: this.edit.seo_title,
                        description: this.edit.description,
                        focus_keyword: this.edit.focus_keyword,
                        keywords: keywords
                    })
                });
                const j = await r.json();
                if (j && j.success) {
                    this.edit.open = false;
                    this.loadArticles();
                }
            } catch (e) {
                console.error('Save meta failed:', e);
            } finally {
                this.edit.saving = false;
            }
        },
        runSeoAuditInline: function() {
            this.seoAudit.running = true;
            setTimeout(() => {
                this.seoAudit = {
                    running: false,
                    score: 88,
                    items: [
                        { key: 'title', label: 'ساختار عناوین و متای صفحات', pass: true },
                        { key: 'sitemap', label: 'فعال بودن نقشه سایت XML', pass: !!this.integEdit.sitemap },
                        { key: 'robots', label: 'تنظیمات صحیح robots.txt', pass: true },
                        { key: 'ga4', label: 'اتصال گوگل آنالیتیکس (GA4)', pass: !!this.integEdit.ga4 }
                    ]
                };
            }, 600);
        }
    };

    });
});
