(function () {
    'use strict';

    function bankaiSeoSidebarFactory(postId) {
        return ({

        postId: postId || (window.bankaiEditorSeo && bankaiEditorSeo.postId) || 0,

        activeTab: 'seo',
        loading: false,
        saving: false,
        serpMobile: false,
        serpDate: '',
        serpAuthor: '',
        autoLinkSuggestions: [],

        seo: {
            id: 0,
            title: '',
            permalink: '',
            slug: '',
            content: '',
            seo_title: '',
            description: '',
            focus_keyword: '',
            keywords: [],
            canonical: '',
            robots: { index: true, follow: true, noarchive: false, nosnippet: false, noimageindex: false, max_snippet: -1, max_image_preview: 'large', hide_date: false },
            og_title: '',
            og_description: '',
            og_image: '',
            x_title: '',
            x_description: '',
            x_image: '',
            schema: '',
            redirect: '',
            score: 0
        },

        keywordList: [],
        kwDraft: '',
        fixedKeywordsList: [],

        analysis: {
            score: 0,
            passed: 0,
            total: 0,
            checks: [],
            groups: {},
            stats: {}
        },

        openGroups: { basic: true, advanced: true, title: false, content: false },
        schemaType: 'Article',
        faqItems: [{ q: '', a: '' }],
        howToSteps: [{ name: '', text: '' }],

        // Links state
        linkData: { internal: [], external: [], counts: { internal: 0, external: 0 } },
        pendingAiLinks: [],
        linkApplyBusy: false,
        linksSubTab: 'active', // 'active' | 'pending'
        linkAnchor: '',
        postResults: [],
        extAnchor: '',
        extUrl: '',
        extNofollow: false,

        linkBusy: false,
        optBusy: false,
        postImages: [],
        optReport: [],
        optOptions: {
            convertWebp: true,
            resize: true,
            watermark: false,
            fillAlt: true,
            maxWidth: 1600,
            maxHeight: 0,
            quality: 82,
            defaultAlt: ''
        },

        // Call To Action — خودکار هر N پاراگراف یا درج تکی
        cta: {
            url: '',
            label: 'همین حالا اقدام کنید',
            code: '',
            every: 3
        },
        internalReport: { show: false, message: '', type: 'info' },
        externalReport: { show: false, message: '', type: 'info' },
        internalMatches: [],
        externalMatches: [],
        selectedInternalPost: null,
        contentMatchesInternal: [],
        contentMatchesExternal: [],

        checklistFilter: 'all',
        aiBusy: false,

        // AI Modal & Automation State
        aiOpen: false,
        aiModalOpen: false,
        aiTask: '',
        aiOptions: {},
        aiProvider: (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || '',
        aiImageProvider: (window.bankaiEditorSeo && bankaiEditorSeo.defaultImageProvider) || '',
        aiLoading: false,
        legacySyncBusy: false,
        legacySyncDone: false,
        aiProgress: 0,
        aiStepText: '',
        aiActiveAction: '', // 'rewrite', 'meta', 'links', 'images', 'auto_all'
        metaGenerationStatus: '', // success | error
        metaValidationStatus: '', // passed | needs_review | failed
        metaValidationMessage: '',
        metaFailedChecks: [],
        // Phase D — display only (from central settings)
        centralTextProviderName: '',
        centralImageProviderName: '',
        centralTextProviderReady: false,
        centralImageProviderReady: false,
        aiChecklist: [
            { id: 'analyze', label: 'تحلیل محتوا و ساختار مقاله', status: 'pending', icon: 'analytics' },
            { id: 'meta', label: 'تولید خودکار عنوان سئو و متا دیسکریپشن', status: 'pending', icon: 'title' },
            { id: 'keywords', label: 'استخراج کلمات کلیدی + ادغام ثابت‌ها', status: 'pending', icon: 'key' },
            { id: 'links', label: 'لینک‌سازی هوشمند داخلی با مقالات مرتبط', status: 'pending', icon: 'link' },
            { id: 'images', label: 'سئوی تصاویر و تولید Alt Text خودکار', status: 'pending', icon: 'image' },
            { id: 'optimize_images', label: 'بهینه‌سازی و فشرده‌سازی تصاویر مقاله', status: 'pending', icon: 'image' },
            { id: 'schema', label: 'تولید اسکیما JSON-LD مقاله', status: 'pending', icon: 'code' },
            { id: 'cover', label: 'تولید کاور مقاله با هوش مصنوعی', status: 'pending', icon: 'image' },
            { id: 'inline_img', label: 'تولید تصویر محتوا با هوش مصنوعی', status: 'pending', icon: 'image' },
            { id: 'terms', label: 'تولید دسته و برچسب خودکار', status: 'pending', icon: 'tag' }
        ],
        aiDraft: {
            title: '',
            description: '',
            focus_keyword: '',
            keywords: [],
            rewrite: '',
            altTexts: [],   // [ { id, src, currentAlt, suggestedAlt, context } ]
            smartLinks: [],  // [ { keyword, target_post_id, target_title, target_url, reason } ]
            coverUrl: '',
            coverId: 0,
            inlineUrl: '',
            inlineHtml: '',
            inlineId: 0
        },
        aiReviewOpen: false,
        aiReviewSelection: {
            applyMeta: true,
            applyKeywords: true,
            applyLinks: true,
            applyImages: true,
            applyRewrite: false
        },

        toast: { show: false, message: '', type: 'info' },
        _saveTimer: null,
        _analyzeTimer: null,

        get aiProvidersList() {
            if (window.bankaiEditorSeo && Array.isArray(bankaiEditorSeo.aiProviders) && bankaiEditorSeo.aiProviders.length) {
                return bankaiEditorSeo.aiProviders;
            }
            if (window.bankaiState && Array.isArray(bankaiState.providers) && bankaiState.providers.length) {
                return bankaiState.providers;
            }
            return [
                { id: 'gemini', name: 'Google Gemini Pro / Flash' },
                { id: 'openai', name: 'OpenAI GPT-4o / GPT-4' },
                { id: 'deepseek', name: 'DeepSeek Chat' },
                { id: 'groq', name: 'Groq Llama 3' }
            ];
        },
        get imageProvidersList() {
            if (window.bankaiEditorSeo && Array.isArray(bankaiEditorSeo.imageProviders) && bankaiEditorSeo.imageProviders.length) {
                return bankaiEditorSeo.imageProviders;
            }
            return [];
        },


        get isRtl() {
            return (window.bankaiEditorSeo && bankaiEditorSeo.isRtl) || document.documentElement.dir === 'rtl' || true;
        },

        // ---- Score circle math ----
        get scoreCircumference() {
            return 2 * Math.PI * 14;
        },

        get scoreOffset() {
            const c = this.scoreCircumference;
            const s = Math.min(100, Math.max(0, this.analysis.score || 0));
            return c - (c * s / 100);
        },

        checkStatusClass(check) {
            const st = (check && check.status) ? check.status : (check && check.passed ? 'pass' : 'fail');
            if (st === 'pass') return 'is-ok';
            if (st === 'not_applicable') return 'is-na';
            if (st === 'unknown') return 'is-unknown';
            if (st === 'warning') return 'is-warn';
            return 'is-warn';
        },
        checkStatusLabel(check) {
            const st = (check && check.status) ? check.status : (check && check.passed ? 'pass' : 'fail');
            const map = {
                pass: 'تایید',
                fail: 'رد',
                warning: 'توجه',
                not_applicable: 'نامرتبط',
                unknown: 'نامشخص'
            };
            return map[st] || (check && check.passed ? 'تایید' : 'توجه');
        },

        allChecksList() {
            const list = [];
            if (this.analysis && this.analysis.groups) {
                Object.values(this.analysis.groups).forEach(g => {
                    if (g && Array.isArray(g.items)) {
                        g.items.forEach(i => list.push(i));
                    }
                });
            }
            return list;
        },
        filterChecksByPillar(pillar) {
            return this.allChecksList().filter(c => c.group === pillar);
        },
        failedChecksList() {
            return this.allChecksList().filter(c => !c.passed);
        },
        passedChecksList() {
            return this.allChecksList().filter(c => c.passed);
        },
        filteredChecklistItems() {
            const all = this.allChecksList();
            if (this.checklistFilter === 'seo') {
                return all.filter(c => !c.group || c.group === 'basic' || c.group === 'advanced' || c.group === 'title' || c.group === 'content' || c.group === 'seo');
            }
            if (this.checklistFilter === 'aeo') {
                return all.filter(c => c.group === 'aeo');
            }
            if (this.checklistFilter === 'geo') {
                return all.filter(c => c.group === 'geo');
            }
            if (this.checklistFilter === 'failed') {
                return all.filter(c => !c.passed);
            }
            if (this.checklistFilter === 'passed') {
                return all.filter(c => c.passed);
            }
            return all;
        },
        async generateAeoFaq() {
            if (this.aiLoading || this.aiBusy) return;
            this.aiLoading = true;
            this.aiBusy = true;
            this.showToast('در حال تولید بخش سوالات متداول (AEO FAQ)...', 'info');
            try {
                const ctx = this.getEditorContext();
                const res = await this.callSeoAi('aeo_faq', ctx);
                if (res && res.success && (res.data?.faq_html || res.data?.content || res.data?.text)) {
                    const faqHtml = res.data.faq_html || res.data.content || res.data.text;
                    this.insertContentIntoEditor(faqHtml, 'append');
                    this.showToast('بخش سوالات متداول با موفقیت به مقاله اضافه شد.', 'success');
                    await this.analyze(false);
                } else {
                    this.showToast((res && res.data && res.data.message) || 'خطا در تولید بخش FAQ', 'error');
                }
            } catch (e) {
                this.showToast('خطا در ارتباط با هوش مصنوعی', 'error');
            } finally {
                this.aiLoading = false;
                this.aiBusy = false;
            }
        },
        async generateGeoSummary() {
            if (this.aiLoading || this.aiBusy) return;
            this.aiLoading = true;
            this.aiBusy = true;
            this.showToast('در حال تولید خلاصه نکات کلیدی (GEO Summary)...', 'info');
            try {
                const ctx = this.getEditorContext();
                const res = await this.callSeoAi('geo_summary', ctx);
                if (res && res.success && (res.data?.summary_html || res.data?.content || res.data?.text)) {
                    const summaryHtml = res.data.summary_html || res.data.content || res.data.text;
                    this.insertContentIntoEditor(summaryHtml, 'prepend');
                    this.showToast('خلاصه نکات کلیدی با موفقیت به مقاله اضافه شد.', 'success');
                    await this.analyze(false);
                } else {
                    this.showToast((res && res.data && res.data.message) || 'خطا در تولید خلاصه نکات کلیدی', 'error');
                }
            } catch (e) {
                this.showToast('خطا در ارتباط با هوش مصنوعی', 'error');
            } finally {
                this.aiLoading = false;
                this.aiBusy = false;
            }
        },
        insertContentIntoEditor(html, position = 'append') {
            if (!html) return;
            if (window.wp && wp.data && wp.data.dispatch && wp.data.select) {
                try {
                    const ed = wp.data.select('core/editor');
                    const disp = wp.data.dispatch('core/editor');
                    if (ed && disp && typeof ed.getEditedPostContent === 'function') {
                        const current = ed.getEditedPostContent() || '';
                        let updated = current;
                        if (position === 'prepend') {
                            updated = html + "\n\n" + current;
                        } else {
                            updated = current + "\n\n" + html;
                        }
                        if (wp.blocks && typeof wp.blocks.parse === 'function' && typeof disp.resetBlocks === 'function') {
                            disp.resetBlocks(wp.blocks.parse(updated));
                            return;
                        }
                    }
                } catch (e) {}
            }
            const el = document.getElementById('content');
            if (el) {
                if (position === 'prepend') {
                    el.value = html + "\n\n" + el.value;
                } else {
                    el.value = el.value + "\n\n" + html;
                }
            }
        },
        async loadSmartLinkSuggestions() {
            if (this.linkBusy) return;
            this.linkBusy = true;
            this.showToast('در حال دریافت پیشنهادهای لینک داخلی...', 'info');
            try {
                const ctx = this.getEditorContext();
                const res = await this.callSeoAi('smart_internal_links', ctx);
                if (res && res.success && Array.isArray(res.data?.smartLinks || res.data?.links)) {
                    this.autoLinkSuggestions = res.data.smartLinks || res.data.links;
                    this.showToast('پیشنهادهای لینک داخلی دریافت شد.', 'success');
                } else {
                    this.showToast('پیشنهاد جدیدی یافت نشد.', 'info');
                }
            } catch (e) {
                this.showToast('خطا در بارگیری لینک‌ها', 'error');
            } finally {
                this.linkBusy = false;
            }
        },
        insertSuggestedLink(s) {
            if (!s) return;
            const anchor = s.suggested_anchor || s.keyword || '';
            const url = s.permalink || s.target_url || '#';
            if (!anchor || !url) return;
            const linkTag = `<a href="${url}" target="_blank" rel="noopener"><strong>${anchor}</strong></a>`;
            this.insertContentIntoEditor(linkTag, 'append');
            this.showToast(`لینک «${anchor}» اضافه شد.`, 'success');
            this.analyze(false);
        },
        async generateSingleImageAlt(img) {
            if (!img || this.aiBusy) return;
            this.aiBusy = true;
            this.showToast('در حال تولید Alt تصویر با AI...', 'info');
            try {
                const ctx = this.getEditorContext();
                const res = await this.callSeoAi('alt_text', ctx, { img_url: img.src || img.original_src || '' });
                if (res && res.success && (res.data?.alt_text || res.data?.text)) {
                    const alt = res.data.alt_text || res.data.text;
                    img.new_alt = alt;
                    img.alt = alt;
                    this.updateImageAlt(img, alt);
                    this.showToast('متن جایگزین تصویر ثبت شد.', 'success');
                }
            } catch (e) {
                this.showToast('خطا در تولید Alt', 'error');
            } finally {
                this.aiBusy = false;
            }
        },

        get scoreColor() {

            const s = this.analysis.score || 0;
            if (s < 10) return '#B8BCC2';
            if (s < 20) return '#E8D3A2';
            if (s < 40) return '#A6122D';
            if (s < 60) return '#E0A030';
            if (s < 80) return '#93C572';
            return '#1E7F5C';
        },

        get titlePx() {
            const t = this.seo.seo_title || this.seo.title || '';
            return Math.round(t.length * 8.5);
        },

        get titlePxOk() {
            return this.titlePx > 0 && this.titlePx <= 600;
        },

        get seoTitleClass() {
            const n = (this.seo.seo_title || '').length;
            if (n >= 30 && n <= 60) return 'is-ok';
            if (n > 60) return 'is-warn';
            return '';
        },

        get descClass() {
            const n = (this.seo.description || '').length;
            if (n >= 120 && n <= 160) return 'is-ok';
            if (n > 160) return 'is-warn';
            return '';
        },

        get titleBarPct() {
            return Math.min(100, ((this.seo.seo_title || '').length / 60) * 100);
        },

        get titleBarColor() {
            const n = (this.seo.seo_title || '').length;
            if (n === 0) return '#CBD5E1';
            if (n >= 30 && n <= 60) return '#10B981';
            if (n > 60) return '#EF4444';
            return '#F59E0B';
        },

        get descBarPct() {
            return Math.min(100, ((this.seo.description || '').length / 160) * 100);
        },

        get descBarColor() {
            const n = (this.seo.description || '').length;
            if (n === 0) return '#CBD5E1';
            if (n >= 120 && n <= 160) return '#10B981';
            if (n > 160) return '#EF4444';
            return '#F59E0B';
        },

        init() {
            this.initFixedKeywords();
            this.initCentralProviders();
            this.loadSeo();

            document.addEventListener('bankai-ai-result', (e) => {
                if (!e || !e.detail) return;
                const { task, data } = e.detail;
                if (task === 'meta_title' && data.seo_title) this.seo.seo_title = data.seo_title;
                if (task === 'meta_description' && data.description) this.seo.description = data.description;
                this.onFieldChange();
            });

            document.addEventListener('bankai-ai-keywords', (e) => {
                if (!e || !Array.isArray(e.detail)) return;
                e.detail.forEach((k) => {
                    if (k && !this.keywordList.includes(k)) this.keywordList.push(k);
                });
                this.pushKeywordsToSeo();
                this.onFieldChange();
            });
        },

        initFixedKeywords() {
            const raw = (window.bankaiEditorSeo && (bankaiEditorSeo.fixedKeywords || bankaiEditorSeo.seoFixedKeywords)) || [];
            if (Array.isArray(raw)) {
                this.fixedKeywordsList = raw.map((s) => String(s).trim()).filter(Boolean);
            } else if (typeof raw === 'string' && raw) {
                this.fixedKeywordsList = raw.split(/[,\u060C\r\n]+/).map((s) => s.trim()).filter(Boolean);
            }
        },

        isFixedKeyword(kw) {
            if (!kw || !this.fixedKeywordsList.length) return false;
            const target = String(kw).trim().toLowerCase();
            return this.fixedKeywordsList.some((f) => String(f).trim().toLowerCase() === target);
        },

        initCentralProviders() {
            const cfg = window.bankaiEditorSeo || {};
            const textId = cfg.defaultAiProvider || this.aiProvider || '';
            const imageId = cfg.defaultImageProvider || this.aiImageProvider || '';
            this.aiProvider = textId;
            this.aiImageProvider = imageId;
            this.centralTextProviderName = cfg.defaultAiProviderName || textId || 'تنظیم‌نشده';
            this.centralImageProviderName = cfg.defaultImageProviderName || imageId || 'تنظیم‌نشده';
            const list = Array.isArray(cfg.aiProviders) ? cfg.aiProviders : [];
            const imgList = Array.isArray(cfg.imageProviders) ? cfg.imageProviders : [];
            const textEntry = list.find((p) => p && p.id === textId);
            const imgEntry = imgList.find((p) => p && p.id === imageId);
            this.centralTextProviderReady = !!(textEntry && (textEntry.ready || textEntry.has_key));
            this.centralImageProviderReady = !!(imgEntry && (imgEntry.ready || imgEntry.has_key));
            // If list empty but default set, still show name
            if (!list.length && textId) {
                this.centralTextProviderName = cfg.defaultAiProviderName || textId;
            }
        },

        restBase() {
            const cfg = window.bankaiEditorSeo || {};
            let base = (cfg.restUrl || '/wp-json/bankai/v1/').toString();
            if (!base.endsWith('/')) base += '/';
            return base;
        },

        restHeaders(json = false) {
            const cfg = window.bankaiEditorSeo || {};
            const h = { 'X-WP-Nonce': cfg.nonce || '' };
            if (json) {
                h['Content-Type'] = 'application/json';
                h['Accept'] = 'application/json';
            }
            return h;
        },

        normalizeSeoPayload(data) {
            if (!data || typeof data !== 'object') return;
            // Merge known fields without wiping reactive defaults incorrectly
            const keys = [
                'id','title','permalink','slug','content','seo_title','description',
                'focus_keyword','canonical','og_title','og_description','og_image',
                'x_title','x_description','x_image','schema','score'
            ];
            keys.forEach((k) => {
                if (data[k] !== undefined && data[k] !== null) {
                    this.seo[k] = data[k];
                }
            });
            // Prefer pretty permalink for canonical (never sticky ?p=ID)
            const perma = (data.permalink || this.seo.permalink || '').toString();
            let can = (this.seo.canonical || '').toString();
            if (!can || /\?p=\d+/.test(can) || (perma && /\?p=\d+/.test(can))) {
                if (perma && !/\?p=\d+/.test(perma)) {
                    this.seo.canonical = perma;
                }
            }
            if (data.robots && typeof data.robots === 'object') {
                this.seo.robots = {
                    index: data.robots.index !== false && data.robots.index !== '0' && data.robots.index !== 0,
                    follow: data.robots.follow !== false && data.robots.follow !== '0' && data.robots.follow !== 0,
                };
            }
            // Keywords
            let list = [];
            if (data.redirect !== undefined) this.seo.redirect = data.redirect || '';
            if (data.robots && typeof data.robots === 'object') {
                this.seo.robots = Object.assign({}, this.seo.robots, data.robots);
            }
            const cfg = window.bankaiEditorSeo || {};
            this.serpDate = cfg.postDate || '';
            this.serpAuthor = cfg.postAuthor || '';

            if (Array.isArray(data.keywords)) {
                list = data.keywords.map((k) => String(k).trim()).filter(Boolean);
            } else if (typeof data.keywords === 'string' && data.keywords) {
                try {
                    const parsed = JSON.parse(data.keywords);
                    if (Array.isArray(parsed)) {
                        list = parsed.map((k) => String(k).trim()).filter(Boolean);
                    } else {
                        list = data.keywords.split(/[,\u060C\n]+/).map((s) => s.trim()).filter(Boolean);
                    }
                } catch (e) {
                    list = data.keywords.split(/[,\u060C\n]+/).map((s) => s.trim()).filter(Boolean);
                }
            }
            if (data.focus_keyword && !list.includes(data.focus_keyword)) {
                list.unshift(String(data.focus_keyword).trim());
            }
            this.fixedKeywordsList.forEach((fkw) => {
                if (fkw && !list.includes(fkw)) list.push(fkw);
            });
            this.keywordList = list;
            this.pushKeywordsToSeo();
            if (!this.seo.focus_keyword && list.length) {
                this.seo.focus_keyword = list[0];
            }
        },

        async loadSeo() {
            if (!this.postId) {
                this.analyze(false);
                return;
            }
            this.loading = true;
            try {
                const url = this.restBase() + 'seo/' + this.postId;
                const res = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: this.restHeaders(false)
                });
                if (!res.ok) {
                    console.warn('[Bankai SEO] load HTTP', res.status, url);
                }
                const json = await res.json().catch(() => ({}));
                const data = (json && json.success && json.data) ? json.data
                    : (json && json.data) ? json.data
                    : (json && json.id) ? json
                    : null;
                if (data) {
                    this.normalizeSeoPayload(data);
                } else {
                    console.warn('[Bankai SEO] empty payload', json);
                }
            } catch (e) {
                console.warn('[Bankai SEO] load error', e);
            } finally {
                this.loading = false;
                this.analyze(false);
                this.loadLinks();
            }
        },

        async saveSeo() {
            if (!this.postId) return;
            this.saving = true;
            try {
                const cfg = window.bankaiEditorSeo || {};
                this.pushKeywordsToSeo();
                const res = await fetch(this.restBase() + 'seo/' + this.postId, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify(this.seo)
                });
                const json = await res.json();
                if (json.success) {
                    this.showToast('تنظیمات سئو ذخیره شد', 'success');
                    const sug = (json.data && json.data.link_suggestions) || json.link_suggestions || [];
                    if (Array.isArray(sug) && sug.length) {
                        this.autoLinkSuggestions = sug;
                    }
                    if (json.analysis) this.analysis = json.analysis;
                } else {
                    this.showToast(json.message || 'خطا در ذخیره‌سازی', 'error');
                }
            } catch (e) {
                this.showToast('خطا در ارتباط با سرور', 'error');
            } finally {
                this.saving = false;
            }
        },

        onFieldChange() {
            if (this._saveTimer) clearTimeout(this._saveTimer);
            if (this._analyzeTimer) clearTimeout(this._analyzeTimer);

            this._saveTimer = setTimeout(() => this.saveSeo(), 1200);
            this._analyzeTimer = setTimeout(() => this.analyze(false), 500);
        },

        pushKeywordsToSeo() {
            this.seo.keywords = this.keywordList.slice();
        },

        addKeywordFromInput() {
            const v = (this.kwDraft || '').trim();
            if (!v) return;
            const parts = v.split(/[,\u060C]+/).map((s) => s.trim()).filter(Boolean);
            parts.forEach((p) => {
                if (!this.keywordList.includes(p)) this.keywordList.push(p);
            });
            this.kwDraft = '';
            this.pushKeywordsToSeo();
            this.onFieldChange();
        },

        removeKeyword(idx) {
            if (idx >= 0 && idx < this.keywordList.length) {
                this.keywordList.splice(idx, 1);
                this.pushKeywordsToSeo();
                this.onFieldChange();
            }
        },

        emitScore(score) {
            try {
                const s = Number(score) || 0;
                this.seo.score = s;
                this.analysis.score = s;
                window.dispatchEvent(new CustomEvent('bankai-seo-score', {
                    detail: { score: s, postId: this.postId || 0 }
                }));
            } catch (e) {}
        },

        async analyze(forceSave = false) {
            try {
                const cfg = window.bankaiEditorSeo || {};
                const ctx = this.getEditorContext();
                const body = Object.assign({}, this.seo, {
                    content: ctx.rawHtml || this.seo.content || ctx.content || '',
                    title: ctx.title || this.seo.title,
                    focus_keyword: this.seo.focus_keyword || ctx.focus_keyword || ''
                });
                const res = await fetch(this.restBase() + 'seo/analyze/' + (this.postId || 0), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify(body)
                });
                const json = await res.json();
                const data = (json && json.success && json.data) ? json.data
                    : (json && json.data) ? json.data
                    : (json && typeof json.score !== 'undefined') ? json
                    : null;
                if (data) {
                    this.analysis = Object.assign({}, this.analysis, data);
                    if (typeof data.score !== 'undefined') {
                        this.emitScore(data.score || 0);
                    }
                    if (data.groups && typeof data.groups === 'object') {
                        this.analysis.groups = data.groups;
                    }
                }
            } catch (e) {
                console.warn('[Bankai SEO] analyze error', e);
            }
        },

        extractLinksFromHtml(html) {
            const internal = [];
            const external = [];
            if (!html) return { internal, external };
            try {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const homeHost = (location.hostname || '').replace(/^www\./i, '').toLowerCase();
                doc.querySelectorAll('a[href]').forEach((a) => {
                    const href = (a.getAttribute('href') || '').trim();
                    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('javascript:') || href.startsWith('tel:')) return;
                    const text = (a.textContent || '').trim();
                    const nofollow = ((a.getAttribute('rel') || '').toLowerCase().indexOf('nofollow') !== -1);
                    const item = { href, text, nofollow };
                    let isInternal = false;
                    if (href.startsWith('/') && !href.startsWith('//')) isInternal = true;
                    else {
                        try {
                            const u = new URL(href, location.origin);
                            const h = (u.hostname || '').replace(/^www\./i, '').toLowerCase();
                            isInternal = h === homeHost;
                        } catch (e) { isInternal = false; }
                    }
                    if (isInternal) internal.push(item); else external.push(item);
                });
            } catch (e) {}
            return { internal, external };
        },

        async loadLinks() {
            const ctx = this.getEditorContext ? this.getEditorContext() : {};
            const live = this.extractLinksFromHtml(ctx.rawHtml || ctx.content || this.seo.content || '');
            let data = null;
            if (this.postId) {
                try {
                    const res = await fetch(this.restBase() + 'seo/links/' + this.postId, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: this.restHeaders(false)
                    });
                    const json = await res.json().catch(() => ({}));
                    data = (json && json.success && json.data) ? json.data
                        : (json && json.data) ? json.data
                        : (json && (json.internal || json.external)) ? json
                        : null;
                } catch (e) {
                    console.warn('[Bankai SEO] load links error', e);
                }
            }
            const internal = (data && Array.isArray(data.internal) && data.internal.length) ? data.internal : live.internal;
            const external = (data && Array.isArray(data.external) && data.external.length)
                ? data.external
                : (live.external.length ? live.external : (data && data.external) || []);
            // Prefer union when both exist
            const merge = (a, b) => {
                const seen = {};
                const out = [];
                [...(a||[]), ...(b||[])].forEach((it) => {
                    const k = ((it.href||'') + '|' + (it.text||'')).toLowerCase();
                    if (seen[k]) return;
                    seen[k] = true;
                    out.push(it);
                });
                return out;
            };
            const mi = merge(data && data.internal, live.internal);
            const me = merge(data && data.external, live.external);
            this.linkData = {
                internal: mi,
                external: me,
                counts: { internal: mi.length, external: me.length }
            };
            if (this.linkStats) {
                this.linkStats.internal = mi.length;
                this.linkStats.external = me.length;
            }
        },



        async smartSuggestInternalLinks() {
            this.linkBusy = true;
            try {
                const ctx = this.getEditorContext();
                const kws = Array.isArray(this.keywordList) ? this.keywordList.slice() : [];
                if (this.seo && this.seo.focus_keyword && !kws.includes(this.seo.focus_keyword)) {
                    kws.unshift(this.seo.focus_keyword);
                }
                const res = await fetch(this.restBase() + 'seo/suggest-links', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({
                        post_id: this.postId,
                        keywords: kws,
                        content: ctx.rawHtml || ctx.content || '',
                        title: ctx.title || '',
                        limit: 8
                    })
                });
                const json = await res.json();
                const payload = (json && json.data) ? json.data : json;
                const suggestions = (payload && Array.isArray(payload.suggestions)) ? payload.suggestions : [];
                let added = 0;
                suggestions.forEach((s) => {
                    const url = s.target_url || s.permalink || '';
                    if (!url) return;
                    const exists = this.pendingAiLinks.some((p) => p.target_url === url || p.target_post_id === (s.target_post_id || s.id));
                    if (exists) return;
                    this.pendingAiLinks.push({
                        id: 'sugg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                        keyword: s.suggested_anchor || s.keyword || s.focus_keyword || s.title,
                        target_post_id: s.target_post_id || s.id || 0,
                        target_title: s.target_title || s.title || '',
                        target_url: url,
                        reason: s.reason || (Array.isArray(s.reasons) ? s.reasons.join(' ') : ''),
                        reasons: Array.isArray(s.reasons) ? s.reasons : [],
                        score: typeof s.score === 'number' ? s.score : 0,
                        factors: s.factors || {},
                        insertion_confidence: s.insertion_confidence || (s.anchor_in_content ? 'high' : 'low'),
                        link_status: s.link_status || 'new',
                        requires_approval: true,
                        status: 'pending'
                    });
                    added++;
                });
                this.linksSubTab = 'pending';
                if (added) {
                    this.showToast(added + ' پیشنهاد لینک رتبه‌بندی‌شده اضافه شد (نیازمند تأیید)', 'success');
                } else {
                    this.showToast('پیشنهاد مرتبطی با آستانه کیفیت یافت نشد', 'info');
                }
            } catch (e) {
                this.showToast('خطا در دریافت پیشنهادهای لینک', 'error');
            } finally {
                this.linkBusy = false;
            }
        },

        insertInternalLink(p) {
            if (!p || !p.permalink) return;
            const kw = this.linkAnchor || p.focus_keyword || p.title;
            this.insertLinkIntoEditor(kw, p.permalink, p.title);
            this.showToast('لینک داخلی در متن درج شد', 'success');
            this.loadLinks();
        },

        insertExternalLink() {
            if (!this.extUrl) {
                this.showToast('لطفا URL را وارد کنید', 'error');
                return;
            }
            this.insertLinkIntoEditor(this.extAnchor || 'لینک خارجی', this.extUrl, '', this.extNofollow);
            this.showToast('لینک خارجی درج شد', 'success');
            this.extAnchor = '';
            this.extUrl = '';
            this.loadLinks();
        },

        async acceptPendingLink(link) {
            if (!link || this.linkApplyBusy) return;
            if (link.status === 'applied') return;
            this.linkApplyBusy = true;
            try {
                const href = String(link.target_url || '').trim();
                const needle = String(link.keyword || link.suggested_anchor || '').trim();
                if (!href || /^(javascript:|data:|vbscript:)/i.test(href)) {
                    this.showToast('آدرس مقصد نامعتبر است', 'error');
                    return;
                }
                if (!needle) {
                    this.showToast('عبارت لنگر خالی است', 'error');
                    return;
                }
                // Live content — never use stale snapshot from suggestion time
                const raw = this.getEditorRawContent() || '';
                if (!raw) {
                    this.showToast('محتوای ویرایشگر در دسترس نیست', 'error');
                    return;
                }
                // Duplicate destination (normalized path — trailing slash / host variants)
                if (this.contentAlreadyLinksTo(raw, href, link.target_post_id || 0)) {
                    this.showToast('این مقصد از قبل در مقاله لینک شده است', 'info');
                    this.pendingAiLinks = this.pendingAiLinks.filter((p) => p.id !== link.id);
                    return;
                }
                // Anchor must still exist in current content (author may have edited)
                if (raw.toLowerCase().indexOf(needle.toLowerCase()) === -1) {
                    this.showToast('عبارت لنگر در محتوای فعلی یافت نشد؛ احتمالاً متن تغییر کرده است. پیشنهاد را رد یا انکر را ویرایش کنید.', 'warning');
                    return;
                }
                // Low confidence: still allow only if user clicked confirm AND anchor exists
                const ok = this.insertLinkIntoEditor(needle, href, link.target_title || '', false, true);
                if (!ok) {
                    this.showToast('درج لینک ناموفق بود (محل مناسب یافت نشد یا داخل لینک موجود است)', 'error');
                    return;
                }
                link.status = 'applied';
                this.pendingAiLinks = this.pendingAiLinks.filter((p) => p.id !== link.id);
                this.showToast('لینک با موفقیت درج شد', 'success');
                if (typeof this.loadLinks === 'function') this.loadLinks();
                // Analyze is separate from insert success
                if (typeof this.analyze === 'function') {
                    try {
                        await this.analyze(false);
                    } catch (eA) {
                        this.showToast('لینک درج شد، اما به‌روزرسانی تحلیل سئو ناموفق بود', 'warning');
                    }
                }
            } finally {
                this.linkApplyBusy = false;
            }
        },

        rejectPendingLink(link) {
            if (!link) return;
            this.pendingAiLinks = this.pendingAiLinks.filter((p) => p.id !== link.id);
            this.showToast('پیشنهاد رد شد', 'info');
        },

        async approveAllPendingLinks() {
            if (!this.pendingAiLinks.length || this.linkApplyBusy) return;
            // Only high-confidence anchors auto-batch; low confidence stays for manual review
            const batch = this.pendingAiLinks.filter((l) => l.insertion_confidence !== 'low');
            const skipped = this.pendingAiLinks.length - batch.length;
            let applied = 0;
            for (const link of batch.slice()) {
                const before = this.pendingAiLinks.length;
                await this.acceptPendingLink(link);
                if (this.pendingAiLinks.length < before) applied++;
            }
            if (skipped > 0) {
                this.showToast(applied + ' لینک درج شد؛ ' + skipped + ' مورد با اطمینان پایین نیاز به بررسی دستی دارند', 'info');
            } else {
                this.showToast(applied + ' لینک درج شد', applied ? 'success' : 'info');
            }
            if (typeof this.loadLinks === 'function') this.loadLinks();
        },

        getEditorRawContent() {
            try {
                if (window.wp && wp.data && wp.data.select) {
                    const ed = wp.data.select('core/editor');
                    if (ed && ed.getEditedPostContent) {
                        return ed.getEditedPostContent() || '';
                    }
                }
            } catch (e) {}
            const ta = document.getElementById('content');
            return ta ? ta.value : '';
        },

        setEditorRawContent(html) {
            if (html == null) return;
            try {
                if (window.wp && wp.data && wp.data.dispatch) {
                    const ed = wp.data.dispatch('core/editor');
                    if (ed && ed.resetBlocks && window.wp.blocks && wp.blocks.parse) {
                        ed.resetBlocks(wp.blocks.parse(html));
                        return;
                    }
                }
            } catch (e) {}
            const ta = document.getElementById('content');
            if (ta) {
                ta.value = html;
                ta.dispatchEvent(new Event('input', { bubbles: true }));
                ta.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },

        normalizeHrefForCompare(href) {
            try {
                const base = (typeof window !== 'undefined' && window.location && window.location.origin)
                    ? window.location.origin
                    : 'https://example.invalid';
                const u = new URL(String(href || ''), base);
                let path = (u.pathname || '/').replace(/\/+$/, '') || '/';
                return (path + (u.search || '')).toLowerCase();
            } catch (e) {
                return String(href || '').replace(/\/+$/, '').toLowerCase();
            }
        },

        contentAlreadyLinksTo(html, href, targetPostId) {
            if (!html) return false;
            let doc;
            try {
                doc = new DOMParser().parseFromString(String(html), 'text/html');
            } catch (e) {
                return false;
            }
            const targetNorm = this.normalizeHrefForCompare(href);
            const anchors = doc.querySelectorAll('a[href]');
            for (let i = 0; i < anchors.length; i++) {
                const aHref = anchors[i].getAttribute('href') || '';
                if (!aHref || aHref.charAt(0) === '#') continue;
                if (this.normalizeHrefForCompare(aHref) === targetNorm) return true;
            }
            if (targetPostId) {
                const idStr = String(targetPostId);
                if (html.indexOf('post_id=' + idStr) !== -1 || html.indexOf('data-post-id="' + idStr + '"') !== -1) {
                    return true;
                }
            }
            return false;
        },

        /**
         * Insert link on first free text match inside editor HTML (not inside existing <a>).
         * Internal: follow + target=_blank + rel=noopener
         * External: optional nofollow + target=_blank
         * Bold wrap when bold=true.
         */
        insertLinkIntoEditor(keyword, url, title = '', nofollow = false, bold = true) {
            const needle = String(keyword || '').trim();
            let href = String(url || '').trim();
            if (!needle || !href) return false;
            // Security: only http(s) or site-relative paths
            if (/^(javascript:|data:|vbscript:)/i.test(href)) return false;
            if (!/^(https?:\/\/|\/|#)/i.test(href)) {
                // allow protocol-relative
                if (!href.startsWith('//')) return false;
            }
            // Avoid linking entire document replacement — operate on live editor HTML only

            let html = this.getEditorRawContent();
            if (!html) return false;

            let doc;
            try {
                doc = new DOMParser().parseFromString('<div id="bk-root">' + html + '</div>', 'text/html');
            } catch (e) {
                return false;
            }
            const root = doc.getElementById('bk-root') || doc.body;
            if (!root) return false;

            const needleLower = needle.toLowerCase();
            let done = false;

            const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
                acceptNode(node) {
                    if (!node.nodeValue || !node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                    let p = node.parentElement;
                    while (p) {
                        const tag = (p.tagName || '').toLowerCase();
                        if (tag === 'a' || tag === 'script' || tag === 'style') return NodeFilter.FILTER_REJECT;
                        p = p.parentElement;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            });

            const nodes = [];
            while (walker.nextNode()) nodes.push(walker.currentNode);

            for (let i = 0; i < nodes.length; i++) {
                const node = nodes[i];
                const text = node.nodeValue;
                const idx = text.toLowerCase().indexOf(needleLower);
                if (idx === -1) continue;

                const before = text.slice(0, idx);
                const match = text.slice(idx, idx + needle.length);
                const after = text.slice(idx + needle.length);

                const a = doc.createElement('a');
                a.setAttribute('href', href);
                a.setAttribute('target', '_blank');
                const relParts = ['noopener'];
                if (nofollow) relParts.unshift('nofollow');
                a.setAttribute('rel', relParts.join(' '));
                if (title) a.setAttribute('title', String(title));

                if (bold) {
                    const strong = doc.createElement('strong');
                    strong.textContent = match;
                    a.appendChild(strong);
                } else {
                    a.textContent = match;
                }

                const parent = node.parentNode;
                if (!parent) continue;
                if (before) parent.insertBefore(doc.createTextNode(before), node);
                parent.insertBefore(a, node);
                if (after) parent.insertBefore(doc.createTextNode(after), node);
                parent.removeChild(node);
                done = true;
                break;
            }

            if (!done) return false;

            const out = root.innerHTML;
            this.setEditorRawContent(out);
            return true;
        },

        scanMissingImageAlts() {
            const content = this.getEditorRawContent();
            if (!content) return [];
            const doc = new DOMParser().parseFromString(content, 'text/html');
            const imgs = doc.querySelectorAll('img');
            const missing = [];
            imgs.forEach((img, idx) => {
                const alt = (img.getAttribute('alt') || '').trim();
                const src = img.getAttribute('src') || '';
                if (!alt && src) {
                    const parentText = img.parentElement ? img.parentElement.textContent.trim().slice(0, 150) : '';
                    missing.push({
                        id: 'img_' + idx + '_' + Date.now(),
                        src: src,
                        currentAlt: '',
                        suggestedAlt: '',
                        context: parentText
                    });
                }
            });
            return missing;
        },

        updateImageAltInEditor(src, newAlt) {
            if (!src || !newAlt) return;
            let html = this.getEditorRawContent();
            if (!html) return;
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const imgs = doc.querySelectorAll('img');
            let updated = false;
            imgs.forEach((img) => {
                if (img.getAttribute('src') === src) {
                    img.setAttribute('alt', newAlt);
                    updated = true;
                }
            });
            if (updated && doc.body) {
                this.setEditorRawContent(doc.body.innerHTML);
            }
        },

        // ---- AI Modal & Automation Workflow ----
        openAiModal(action = '') {
            this.aiOpen = true;
            if (!this.aiImageProvider) {
                const list = (window.bankaiEditorSeo && bankaiEditorSeo.imageProviders) || [];
                this.aiImageProvider = (window.bankaiEditorSeo && bankaiEditorSeo.defaultImageProvider) || (list[0] && list[0].id) || '';
            }
            if (!this.aiProvider) {
                const list = (window.bankaiEditorSeo && bankaiEditorSeo.aiProviders) || [];
                this.aiProvider = (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || (list[0] && list[0].id) || '';
            }
            this.aiReviewOpen = false;
            this.resetAiChecklist();
            if (action) {
                this.runAiAction(action);
            }
        },

        resetAiChecklist() {
            this.aiProgress = 0;
            this.aiStepText = '';
            this.aiActiveAction = '';
            this.aiChecklist.forEach((item) => {
                item.status = 'pending';
            });
        },

        setChecklistStatus(id, status) {
            const item = this.aiChecklist.find((i) => i.id === id);
            if (item) item.status = status;
        },

        getEditorContext() {
            let title = this.seo.title || this.seo.seo_title || '';
            let rawHtml = '';
            try {
                if (window.wp && wp.data && wp.data.select) {
                    const ed = wp.data.select('core/editor');
                    if (ed) {
                        if (!title && ed.getEditedPostAttribute) {
                            title = ed.getEditedPostAttribute('title') || title;
                        }
                        if (ed.getEditedPostContent) {
                            rawHtml = ed.getEditedPostContent() || '';
                        }
                    }
                }
            } catch (e) {}
            if (!rawHtml) {
                const ta = document.getElementById('content');
                if (ta) rawHtml = ta.value || '';
            }
            const plain = String(rawHtml).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 8000);
            return {
                title: title,
                content: plain,
                rawHtml: rawHtml,
                focus_keyword: this.seo.focus_keyword || (this.keywordList && this.keywordList[0]) || '',
                locale: (window.bankaiEditorSeo && bankaiEditorSeo.locale) || document.documentElement.lang || 'fa_IR',
                // Phase D: never send a client-chosen provider override
                provider: (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || ''
            };
        },

        async callSeoAi(task, ctx, extraData = {}) {
            const cfg = window.bankaiEditorSeo || {};
            const body = new FormData();
            body.append('action', 'bankai_ai_seo_task');
            body.append('nonce', cfg.adminNonce || cfg.nonce || '');
            body.append('task', task);
            body.append('post_id', String(this.postId || ctx.post_id || 0));
            body.append('title', ctx.title || '');
            body.append('content', ctx.content || ctx.rawHtml || '');
            body.append('focus_keyword', ctx.focus_keyword || '');
            body.append('locale', ctx.locale || 'fa_IR');
            body.append('provider', (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || ctx.provider || '');
            body.append('seo_title', ctx.seo_title || (this.seo && this.seo.seo_title) || '');
            body.append('description', ctx.description || (this.seo && this.seo.description) || '');

            Object.keys(extraData).forEach((k) => {
                if (k === '_timeoutMs') return;
                body.append(k, typeof extraData[k] === 'object' ? JSON.stringify(extraData[k]) : extraData[k]);
            });

            // Heavy tasks need longer client timeout (default was 60s → abort noise)
            const heavy = ['rewrite', 'smart_internal_links', 'auto_all'].includes(String(task));
            const timeoutMs = parseInt(extraData._timeoutMs, 10)
                || (heavy ? 240000 : 180000); // 4 min / 3 min

            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            let timedOut = false;
            const timer = controller
                ? setTimeout(() => {
                    timedOut = true;
                    try { controller.abort(); } catch (e) {}
                }, timeoutMs)
                : null;

            try {
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: body,
                    signal: controller ? controller.signal : undefined
                });
                if (timer) clearTimeout(timer);
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status + ' — پاسخ سرور نامعتبر');
                }
                const json = await r.json().catch(() => null);
                if (!json || !json.success) {
                    const msg = (json && json.data && json.data.message) || (json && json.message) || 'خطا در هوش مصنوعی';
                    throw new Error(msg);
                }
                return json.data || json;
            } catch (e) {
                if (timer) clearTimeout(timer);
                const name = (e && e.name) || '';
                const msg = (e && e.message) || '';
                if (name === 'AbortError' || /aborted/i.test(msg) || timedOut) {
                    throw new Error(
                        timedOut
                            ? 'زمان پاسخ موتور AI به پایان رسید. اتصال/کلید را بررسی کنید یا دوباره تلاش کنید.'
                            : 'درخواست لغو شد. اگر عمدی نبوده، صفحه را تازه کنید و دوباره اجرا کنید.'
                    );
                }
                throw e;
            }
        },


        /** متن ساده محتوا برای تحلیل پاراگراف‌ها */
        getPlainContent() {
            const ctx = this.getEditorContext();
            let html = ctx.rawHtml || ctx.content || this.seo.content || '';
            const div = document.createElement('div');
            div.innerHTML = html;
            return (div.innerText || div.textContent || '').replace(/\s+/g, ' ').trim();
        },

        /** پاراگراف‌های معنادار محتوا */
        extractParagraphs() {
            const ctx = this.getEditorContext();
            let html = ctx.rawHtml || ctx.content || this.seo.content || '';
            const div = document.createElement('div');
            div.innerHTML = html;
            const nodes = div.querySelectorAll('p');
            let paras = [];
            if (nodes && nodes.length) {
                nodes.forEach(p => {
                    const t = (p.innerText || p.textContent || '').replace(/\s+/g, ' ').trim();
                    if (t.length >= 40) paras.push(t);
                });
            }
            if (!paras.length) {
                const plain = this.getPlainContent();
                paras = plain.split(/(?<=[.!?؟۔])\s+/).map(s => s.trim()).filter(s => s.length >= 40);
            }
            if (!paras.length) {
                const plain = this.getPlainContent();
                if (plain) paras = [plain.slice(0, 500)];
            }
            return paras;
        },

        wordCount(text) {
            const t = (text || '').trim();
            if (!t) return 0;
            // فارسی/انگلیسی
            const parts = t.split(/\s+/).filter(Boolean);
            return parts.length;
        },

        /**
         * تقسیم محتوا: >2000 کلمه → ۳ بخش، وگرنه ۲ بخش
         * هر بخش متن اطراف نقطه درج را برای پرامپت برمی‌گرداند
         */
        planContentImageSections() {
            const paras = this.extractParagraphs();
            const plain = this.getPlainContent();
            const words = this.wordCount(plain);
            const parts = words > 2000 ? 3 : 2;
            if (!paras.length) {
                return [{ index: 0, context: plain.slice(0, 400), label: 'بخش ۱' }];
            }
            const sections = [];
            for (let i = 1; i <= parts; i++) {
                const ratio = i / (parts + 1);
                let idx = Math.max(0, Math.min(paras.length - 1, Math.floor(paras.length * ratio)));
                // جلوگیری از ایندکس تکراری
                while (sections.some(s => s.index === idx) && idx < paras.length - 1) idx++;
                const before = paras[Math.max(0, idx - 1)] || '';
                const at = paras[idx] || '';
                const after = paras[Math.min(paras.length - 1, idx + 1)] || '';
                const context = [before, at, after].filter(Boolean).join(' ').slice(0, 600);
                sections.push({
                    index: idx,
                    context,
                    label: 'بخش ' + i + '/' + parts,
                    words
                });
            }
            return sections;
        },

        buildImagePrompt(purpose, sectionCtx) {
            const ctx = this.getEditorContext();
            const title = (ctx.title || this.seo.title || this.seo.seo_title || '').trim();
            const kw = (ctx.focus_keyword || (this.keywordList && this.keywordList[0]) || this.seo.focus_keyword || '').trim();
            const paras = this.extractParagraphs();
            const firstTwo = paras.slice(0, 2).join(' ').slice(0, 500);

            if (purpose === 'cover') {
                const story = firstTwo || this.getPlainContent().slice(0, 400);
                return [
                    'Create a professional editorial blog cover image that represents this article.',
                    'Article title: ' + (title || 'Untitled'),
                    kw ? ('Main keyword: ' + kw) : '',
                    'Article introduction (first paragraphs): ' + (story || title || 'general topic'),
                    'Style: cinematic lighting, high detail, photorealistic or refined illustration, 16:9 composition.',
                    'Strict rules: no text, no letters, no watermark, no logo, no UI chrome.'
                ].filter(Boolean).join('\n');
            }

            // inline / section image
            const sectionText = (sectionCtx && sectionCtx.context) ? sectionCtx.context : firstTwo;
            return [
                'Create a high-quality illustrative photo for an article body section.',
                'Article title: ' + (title || 'Untitled'),
                kw ? ('Keyword: ' + kw) : '',
                'This image must match the following surrounding paragraphs (before/after context):',
                sectionText || title || 'article topic',
                'Style: realistic, clean composition, related to the paragraph topic, suitable mid-article illustration.',
                'Strict rules: no text overlay, no watermark, no logo.'
            ].filter(Boolean).join('\n');
        },

        async callImageAi(purpose, options) {
            options = options || {};
            const cfg = window.bankaiEditorSeo || {};
            const body = new FormData();
            body.append('action', 'bankai_ai_image_generate');
            body.append('nonce', cfg.adminNonce || cfg.nonce || '');
            body.append('purpose', purpose === 'cover' ? 'cover' : 'inline');
            body.append('post_id', String(this.postId || 0));
            const prompt = options.prompt || this.buildImagePrompt(purpose, options.section || null);
            body.append('prompt', prompt);
            if (options.section && options.section.context) {
                body.append('section_context', options.section.context);
            }
            const ctx = this.getEditorContext();
            body.append('title', ctx.title || this.seo.title || '');
            body.append('focus_keyword', ctx.focus_keyword || this.seo.focus_keyword || '');
            // Phase D: only central default image provider (server also enforces)
            const imgProv = (window.bankaiEditorSeo && bankaiEditorSeo.defaultImageProvider) || '';
            body.append('provider', imgProv);
            if (!imgProv) {
                throw new Error('موتور تصویر پیش‌فرض در تنظیمات مرکزی تنظیم نشده است. از استودیو AI → تب تصویر پیکربندی کنید.');
            }
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timer = controller ? setTimeout(() => { try { controller.abort(); } catch (e) {} }, 180000) : null;
            try {
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body,
                    signal: controller ? controller.signal : undefined
                });
                if (timer) clearTimeout(timer);
                const json = await r.json();
                if (!json || !json.success) {
                    throw new Error((json && json.data && json.data.message) || 'خطا در تولید تصویر');
                }
                return json.data || {};
            } catch (e) {
                if (timer) clearTimeout(timer);
                throw e;
            }
        },

        applyFeaturedMedia(attachmentId) {
            const id = parseInt(attachmentId, 10) || 0;
            if (!id) return;
            try {
                if (window.wp && wp.data && wp.data.dispatch) {
                    const d = wp.data.dispatch('core/editor');
                    if (d && d.editPost) d.editPost({ featured_media: id });
                }
            } catch (e) {}
        },

        insertInlineHtml(html) {
            if (!html) return;
            try {
                if (window.wp && wp.data && wp.data.dispatch) {
                    const block = wp.blocks && wp.blocks.createBlock
                        ? wp.blocks.createBlock('core/html', { content: html })
                        : null;
                    if (block && wp.data.dispatch('core/block-editor')) {
                        wp.data.dispatch('core/block-editor').insertBlocks(block);
                        return;
                    }
                }
            } catch (e) {}
            try {
                if (window.tinymce && tinymce.activeEditor) {
                    tinymce.activeEditor.execCommand('mceInsertContent', false, html);
                }
            } catch (e2) {}
        },

        /**
         * درج تصویر در موقعیت نسبی بخش (از انتها به ابتدا تا ایندکس‌ها به‌هم نریزد در حالت rebuild)
         * برای گوتنبرگ: بلوک را در نسبت پیشرفت محتوا درج می‌کند
         */
        insertInlineHtmlAtSection(html, section, sectionIdx, totalSections) {
            if (!html) return;
            try {
                if (window.wp && wp.data && wp.data.select && wp.data.dispatch) {
                    const be = wp.data.select('core/block-editor');
                    const blocks = be && be.getBlocks ? be.getBlocks() : [];
                    if (blocks && blocks.length && wp.blocks && wp.blocks.createBlock) {
                        const ratio = (sectionIdx + 1) / (totalSections + 1);
                        let insertAt = Math.max(1, Math.min(blocks.length, Math.floor(blocks.length * ratio)));
                        const block = wp.blocks.createBlock('core/html', { content: html });
                        wp.data.dispatch('core/block-editor').insertBlocks(block, insertAt);
                        return;
                    }
                }
            } catch (e) {}
            // Classic: rebuild HTML with image after target paragraph
            try {
                const ta = document.getElementById('content');
                let htmlContent = '';
                if (window.tinymce && tinymce.get('content')) {
                    htmlContent = tinymce.get('content').getContent();
                } else if (ta) {
                    htmlContent = ta.value;
                }
                if (!htmlContent) {
                    this.insertInlineHtml(html);
                    return;
                }
                const div = document.createElement('div');
                div.innerHTML = htmlContent;
                const ps = div.querySelectorAll('p');
                const target = section && typeof section.index === 'number' ? section.index : 0;
                if (ps.length && ps[Math.min(target, ps.length - 1)]) {
                    ps[Math.min(target, ps.length - 1)].insertAdjacentHTML('afterend', html);
                } else {
                    div.insertAdjacentHTML('beforeend', html);
                }
                const out = div.innerHTML;
                if (window.tinymce && tinymce.get('content')) {
                    tinymce.get('content').setContent(out);
                } else if (ta) {
                    ta.value = out;
                }
            } catch (e2) {
                this.insertInlineHtml(html);
            }
        },

        buildCtaHtml() {
            const url = (this.cta.url || '').trim();
            const label = (this.cta.label || 'ادامه مطلب / اقدام').trim();
            const code = (this.cta.code || '').trim();
            if (code) return code;
            if (!url) return '';
            return '<div class="bankai-cta wp-block-group" style="margin:1.5em 0;padding:1em 1.25em;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;text-align:center;">'
                + '<a class="bankai-cta-link" href="' + url.replace(/"/g, '&quot;') + '" style="display:inline-block;padding:10px 18px;border-radius:999px;background:#2563eb;color:#fff;text-decoration:none;font-weight:600;">'
                + label.replace(/</g, '&lt;') + '</a></div>';
        },

        /** درج یک CTA در موقعیت مکان‌نما / انتهای محتوا */
        insertSingleCta() {
            const html = this.buildCtaHtml();
            if (!html) {
                this.showToast('لینک یا کد CTA را تنظیم کنید', 'error');
                return 0;
            }
            this.insertInlineHtml(html);
            return 1;
        },

        /**
         * درج خودکار CTA هر N پاراگراف (2 یا 3)
         * @param {boolean} replaceExisting اگر true، CTAهای قبلی bankai حذف می‌شوند
         */
        applyCtaToContent(replaceExisting) {
            const html = this.buildCtaHtml();
            if (!html) return 0;
            const every = Math.max(2, Math.min(5, parseInt(this.cta.every, 10) || 3));
            let count = 0;
            try {
                if (window.wp && wp.data && wp.data.select && wp.data.dispatch && wp.blocks) {
                    const be = wp.data.select('core/block-editor');
                    let blocks = be.getBlocks ? be.getBlocks().slice() : [];
                    if (replaceExisting) {
                        blocks = blocks.filter(b => !(b.attributes && String(b.attributes.content || '').indexOf('bankai-cta') !== -1));
                    }
                    const newBlocks = [];
                    let pCount = 0;
                    blocks.forEach(b => {
                        newBlocks.push(b);
                        const isP = b.name === 'core/paragraph' || (b.name === 'core/html' && /<p[\s>]/i.test(b.attributes && b.attributes.content || ''));
                        if (isP) {
                            pCount++;
                            if (pCount % every === 0) {
                                newBlocks.push(wp.blocks.createBlock('core/html', { content: html }));
                                count++;
                            }
                        }
                    });
                    if (count === 0 && blocks.length) {
                        // حداقل یکی وسط محتوا
                        const mid = Math.floor(blocks.length / 2);
                        newBlocks.splice(mid, 0, wp.blocks.createBlock('core/html', { content: html }));
                        count = 1;
                        wp.data.dispatch('core/block-editor').resetBlocks(newBlocks);
                        return count;
                    }
                    if (count > 0) {
                        wp.data.dispatch('core/block-editor').resetBlocks(newBlocks);
                    }
                    return count;
                }
            } catch (e) { console.warn(e); }

            // Classic editor
            try {
                const ta = document.getElementById('content');
                let content = '';
                if (window.tinymce && tinymce.get('content')) content = tinymce.get('content').getContent();
                else if (ta) content = ta.value;
                if (!content) return 0;
                if (replaceExisting) {
                    content = content.replace(/<div class="bankai-cta[\s\S]*?<\/div>/gi, '');
                }
                const div = document.createElement('div');
                div.innerHTML = content;
                const ps = Array.from(div.querySelectorAll('p'));
                let inserted = 0;
                // از انتها درج کن تا ایندکس‌ها خراب نشود
                for (let i = ps.length - 1; i >= 0; i--) {
                    if ((i + 1) % every === 0) {
                        ps[i].insertAdjacentHTML('afterend', html);
                        inserted++;
                    }
                }
                if (!inserted && ps.length) {
                    ps[Math.floor(ps.length / 2)].insertAdjacentHTML('afterend', html);
                    inserted = 1;
                }
                const out = div.innerHTML;
                if (window.tinymce && tinymce.get('content')) tinymce.get('content').setContent(out);
                else if (ta) ta.value = out;
                count = inserted;
            } catch (e2) {}
            return count;
        },

        async runAiAction(actionKey) {
            this.aiLoading = true;
            this.aiActiveAction = actionKey;
            this.aiReviewOpen = false;
            const ctx = this.getEditorContext();

            try {
                if (actionKey === 'rewrite') {
                    this.aiProgress = 30;
                    this.aiStepText = 'در حال بازنویسی هوشمند محتوا با حفظ ساختار...';
                    this.setChecklistStatus('analyze', 'loading');
                    const res = await this.callSeoAi('rewrite', ctx);
                    this.aiDraft.rewrite = res.rewrite || res.text || '';
                    this.aiProgress = 100;
                    this.setChecklistStatus('analyze', 'success');
                    this.aiReviewSelection.applyRewrite = true;
                    this.aiReviewOpen = true;
                    this.showToast('بازنویسی هوشمند مقاله آماده پیش‌نمایش است', 'success');

                } else if (actionKey === 'meta') {
                    this.aiProgress = 15;
                    this.aiStepText = 'تحلیل وضعیت فعلی و تولید عنوان/متا...';
                    this.setChecklistStatus('meta', 'loading');
                    this.metaGenerationStatus = '';
                    this.metaValidationStatus = '';
                    this.metaValidationMessage = '';
                    this.metaFailedChecks = [];

                    // Phase C: single pipeline (generate → validate → repair ≤2)
                    const metaRes = await this.callSeoAi('meta', Object.assign({}, ctx, {
                        seo_title: (this.seo && this.seo.seo_title) || '',
                        description: (this.seo && this.seo.description) || ''
                    }));

                    this.metaGenerationStatus = metaRes.generation_status || (metaRes.seo_title || metaRes.description ? 'success' : 'error');
                    this.metaValidationStatus = metaRes.validation_status || (metaRes.validated ? 'passed' : (metaRes.needs_review ? 'needs_review' : ''));
                    this.metaValidationMessage = metaRes.message || '';
                    this.metaFailedChecks = Array.isArray(metaRes.failed_checks) ? metaRes.failed_checks : [];

                    if (metaRes.seo_title || metaRes.title) {
                        this.aiDraft.title = metaRes.seo_title || metaRes.title || '';
                    }
                    if (metaRes.description || metaRes.meta_description) {
                        this.aiDraft.description = metaRes.description || metaRes.meta_description || '';
                    }

                    // AI step status = generation only (not SEO pass)
                    if (this.metaGenerationStatus === 'error') {
                        this.setChecklistStatus('meta', 'error');
                        this.showToast(this.metaValidationMessage || 'خطای تولید عنوان/متا', 'error');
                    } else {
                        this.setChecklistStatus('meta', 'success');
                        if (this.metaValidationStatus === 'passed') {
                            this.showToast('تولید و اعتبارسنجی سئو موفق بود', 'success');
                        } else if (this.metaValidationStatus === 'needs_review') {
                            this.showToast('خروجی تولید شد — نیازمند بازبینی معیارهای سئو', 'warning');
                        } else {
                            this.showToast('تولید انجام شد', 'info');
                        }
                    }

                    // Apply draft to live fields then re-analyze deterministically
                    if (this.aiDraft.title) this.seo.seo_title = this.aiDraft.title;
                    if (this.aiDraft.description) this.seo.description = this.aiDraft.description;
                    if (typeof this.onFieldChange === 'function') this.onFieldChange();
                    if (typeof this.analyze === 'function') {
                        try { await this.analyze(false); } catch (eAn) {}
                    }

                    this.aiProgress = 60;
                    this.aiStepText = 'در حال استخراج کلمات کلیدی و ادغام کلیدواژه‌های ثابت...';
                    this.setChecklistStatus('keywords', 'loading');

                    let fk = ctx.focus_keyword;
                    if (!fk) {
                        const fkRes = await this.callSeoAi('focus_keyword', ctx);
                        fk = fkRes.focus_keyword || fkRes.text || '';
                        if (fk) this.aiDraft.focus_keyword = fk;
                    }
                    const kwRes = await this.callSeoAi('keywords', Object.assign({}, ctx, { focus_keyword: fk }));
                    this.aiDraft.keywords = (kwRes.keywords && kwRes.keywords.length) ? kwRes.keywords : [];
                    this.setChecklistStatus('keywords', 'success');

                    this.aiProgress = 100;
                    this.aiReviewOpen = true;
                    this.showToast('متا و کلمات کلیدی پیشنهادی آماده تایید است', 'success');

                } else if (actionKey === 'links') {
                    this.aiProgress = 30;
                    this.aiStepText = 'رتبه‌بندی مقالات مرتبط بر اساس محتوا و کلمات کلیدی...';
                    this.setChecklistStatus('links', 'loading');
                    await this.smartSuggestInternalLinks();
                    this.aiProgress = 100;
                    this.aiStepText = 'پیشنهادهای رتبه‌بندی‌شده آماده تأیید هستند.';
                    this.setChecklistStatus('links', this.pendingAiLinks.length ? 'success' : 'error');
                    this.aiReviewOpen = true;
                    this.showToast(
                        this.pendingAiLinks.length
                            ? (this.pendingAiLinks.length + ' پیشنهاد لینک — فقط پس از تأیید درج می‌شود')
                            : 'پیشنهاد مرتبطی یافت نشد',
                        this.pendingAiLinks.length ? 'success' : 'info'
                    );

                } else if (actionKey === 'images') {
                    this.aiProgress = 30;
                    this.aiStepText = 'در حال اسکن تصاویر بدون متن جایگزین (Alt Text)...';
                    this.setChecklistStatus('images', 'loading');

                    const missingImgs = this.scanMissingImageAlts();
                    if (!missingImgs.length) {
                        this.aiProgress = 100;
                        this.setChecklistStatus('images', 'success');
                        this.showToast('تمام تصاویر مقاله دارای متن جایگزین (Alt) هستند', 'info');
                        return;
                    }

                    const generatedAlts = [];
                    for (let i = 0; i < missingImgs.length; i++) {
                        const img = missingImgs[i];
                        this.aiProgress = 30 + Math.round(((i + 1) / missingImgs.length) * 60);
                        this.aiStepText = `تولید Alt برای تصویر ${i + 1} از ${missingImgs.length}...`;

                        const altRes = await this.callSeoAi('image_alt_text', ctx, {
                            image_url: img.src,
                            image_context: img.context
                        });
                        img.suggestedAlt = altRes.alt_text || altRes.text || '';
                        generatedAlts.push(img);
                    }

                    this.aiDraft.altTexts = generatedAlts;
                    this.aiProgress = 100;
                    this.setChecklistStatus('images', 'success');
                    this.aiReviewOpen = true;
                    this.showToast(generatedAlts.length + ' متن جایگزین برای تصاویر تولید شد', 'success');

                
                } else if (actionKey === 'terms') {
                    this.aiProgress = 30;
                    this.aiStepText = 'در حال پیشنهاد دسته و برچسب بر اساس کلمات کلیدی...';
                    this.setChecklistStatus('terms', 'loading');
                    const kw = (this.keywordList && this.keywordList.length) ? this.keywordList : [];
                    const focus = this.seo.focus_keyword || (kw[0] || '');
                    let list = kw;
                    try {
                        const res = await this.callSeoAi('keywords', Object.assign({}, ctx, { focus_keyword: focus }));
                        if (res.keywords && res.keywords.length) list = res.keywords;
                    } catch (eKw) { /* use existing keywords */ }
                    this.aiProgress = 70;
                    const cfg = window.bankaiEditorSeo || {};
                    const body = new FormData();
                    body.append('action', 'bankai_ai_assign_terms');
                    body.append('nonce', cfg.adminNonce || cfg.nonce || '');
                    body.append('post_id', String(this.postId || 0));
                    body.append('focus', focus);
                    body.append('keywords', JSON.stringify(list));
                    const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST', credentials: 'same-origin', body
                    });
                    const json = await r.json();
                    if (json && json.success) {
                        this.setChecklistStatus('terms', 'success');
                        this.aiProgress = 100;
                        try {
                            const data = json.data || {};
                            const catIds = data.category_ids || [];
                            const tagIds = data.tag_ids || [];
                            if (window.wp && wp.data && wp.data.dispatch) {
                                const d = wp.data.dispatch('core/editor');
                                if (d && d.editPost) {
                                    const patch = {};
                                    if (catIds.length) patch.categories = catIds;
                                    if (tagIds.length) patch.tags = tagIds;
                                    if (Object.keys(patch).length) d.editPost(patch);
                                }
                            }
                        } catch (eSync) {}
                        const msg = (json.data && json.data.message) || 'دسته و برچسب ذخیره شد';
                        this.showToast(msg, 'success');
                    } else {
                        throw new Error((json && json.data && json.data.message) || 'خطا در ذخیره دسته/برچسب');
                    }

                } else if (actionKey === 'cover_image') {
                    this.aiProgress = 20;
                    this.aiStepText = 'تحلیل عنوان و دو پاراگراف اول برای کاور...';
                    this.setChecklistStatus('cover', 'loading');
                    const img = await this.callImageAi('cover');
                    this.aiDraft.coverUrl = img.url || '';
                    this.aiDraft.coverId = img.attachment_id || 0;
                    if (img.attachment_id) this.applyFeaturedMedia(img.attachment_id);
                    this.aiProgress = 100;
                    this.setChecklistStatus('cover', 'success');
                    this.aiReviewOpen = true;
                    this.showToast(img.message || 'کاور بر اساس عنوان و مقدمه مقاله ساخته شد', 'success');

                } else if (actionKey === 'content_image') {
                    this.aiProgress = 10;
                    this.aiStepText = 'تقسیم محتوا و برنامه‌ریزی تصاویر درون‌متنی...';
                    this.setChecklistStatus('inline_img', 'loading');
                    const sections = this.planContentImageSections();
                    const generated = [];
                    for (let i = 0; i < sections.length; i++) {
                        const sec = sections[i];
                        this.aiProgress = 15 + Math.round(((i + 1) / sections.length) * 75);
                        this.aiStepText = 'تولید تصویر ' + sec.label + ' (هماهنگ با پاراگراف اطراف)...';
                        try {
                            const img = await this.callImageAi('inline', { section: sec });
                            if (img && img.html) {
                                this.insertInlineHtmlAtSection(img.html, sec, i, sections.length);
                                generated.push(img);
                            }
                        } catch (eImg) {
                            console.warn('content image section failed', eImg);
                        }
                    }
                    if (generated.length) {
                        this.aiDraft.inlineUrl = generated[0].url || '';
                        this.aiDraft.inlineHtml = generated.map(g => g.html || '').join('\n');
                        this.aiDraft.inlineId = generated[0].attachment_id || 0;
                    }
                    this.aiProgress = 100;
                    this.setChecklistStatus('inline_img', generated.length ? 'success' : 'error');
                    this.aiReviewOpen = true;
                    this.showToast(generated.length
                        ? (generated.length + ' تصویر متناسب با بخش‌های محتوا درج شد')
                        : 'تولید تصویر محتوا ناموفق بود', generated.length ? 'success' : 'error');

                } else if (actionKey === 'cta_auto') {
                    this.aiProgress = 40;
                    this.aiStepText = 'درج خودکار کال‌تو‌اکشن در محتوا...';
                    const n = this.applyCtaToContent(false);
                    this.aiProgress = 100;
                    this.showToast(n > 0 ? (n + ' کال‌تو‌اکشن در محتوا درج شد') : 'CTA تنظیم نشده یا پاراگراف کافی نیست', n > 0 ? 'success' : 'info');

                } else if (actionKey === 'cta_once') {
                    this.insertSingleCta();
                    this.showToast('یک کال‌تو‌اکشن در موقعیت فعلی درج شد', 'success');

                } else if (actionKey === 'auto_all') {
                    this.resetAiChecklist();

                    // Step 1: Content Analysis
                    this.aiProgress = 15;
                    this.aiStepText = 'گام ۱/۵: تحلیل کامل محتوا و ساختار مقاله...';
                    this.setChecklistStatus('analyze', 'loading');
                    await new Promise((r) => setTimeout(r, 400));
                    this.setChecklistStatus('analyze', 'success');

                    // Step 2: Auto Meta & Keywords
                    this.aiProgress = 35;
                    this.aiStepText = 'گام ۲/۵: تولید عنوان سئو، متادیسکریپشن و کلیدواژه‌ها...';
                    this.setChecklistStatus('meta', 'loading');
                    this.setChecklistStatus('keywords', 'loading');

                    const metaRes = await this.callSeoAi('meta', Object.assign({}, ctx, {
                        seo_title: (this.seo && this.seo.seo_title) || '',
                        description: (this.seo && this.seo.description) || ''
                    }));
                    this.metaGenerationStatus = metaRes.generation_status || '';
                    this.metaValidationStatus = metaRes.validation_status || '';
                    this.metaValidationMessage = metaRes.message || '';
                    this.metaFailedChecks = Array.isArray(metaRes.failed_checks) ? metaRes.failed_checks : [];
                    this.aiDraft.title = metaRes.seo_title || metaRes.title || '';
                    this.aiDraft.description = metaRes.description || metaRes.meta_description || '';
                    if (this.aiDraft.title) this.seo.seo_title = this.aiDraft.title;
                    if (this.aiDraft.description) this.seo.description = this.aiDraft.description;

                    let fk = ctx.focus_keyword;
                    if (!fk) {
                        const fkRes = await this.callSeoAi('focus_keyword', ctx);
                        fk = fkRes.focus_keyword || fkRes.text || '';
                        if (fk) this.aiDraft.focus_keyword = fk;
                    }
                    const kwRes = await this.callSeoAi('keywords', Object.assign({}, ctx, { focus_keyword: fk }));
                    this.aiDraft.keywords = (kwRes.keywords && kwRes.keywords.length) ? kwRes.keywords : [];

                    this.setChecklistStatus('meta', 'success');
                    this.setChecklistStatus('keywords', 'success');

                    // Step 3: Ranked internal links (Phase E — no client bulk dump to AI)
                    this.aiProgress = 65;
                    this.aiStepText = 'گام ۳: رتبه‌بندی لینک‌های داخلی بر اساس محتوا...';
                    this.setChecklistStatus('links', 'loading');
                    await this.smartSuggestInternalLinks();
                    this.aiDraft.smartLinks = (this.pendingAiLinks || []).map((l) => ({
                        keyword: l.keyword,
                        target_post_id: l.target_post_id,
                        target_title: l.target_title,
                        target_url: l.target_url,
                        reason: l.reason,
                        score: l.score
                    }));
                    this.setChecklistStatus('links', this.pendingAiLinks.length ? 'success' : 'error');

                    // Step 4: Images & Alt Text
                    this.aiProgress = 85;
                    this.aiStepText = 'گام ۴/۵: اسکن و تولید متن جایگزین برای تصاویر...';
                    this.setChecklistStatus('images', 'loading');

                    const missingImgs = this.scanMissingImageAlts();
                    if (missingImgs.length) {
                        const generatedAlts = [];
                        for (let i = 0; i < missingImgs.length; i++) {
                            const img = missingImgs[i];
                            const altRes = await this.callSeoAi('image_alt_text', ctx, {
                                image_url: img.src,
                                image_context: img.context
                            });
                            img.suggestedAlt = altRes.alt_text || altRes.text || '';
                            generatedAlts.push(img);
                        }
                        this.aiDraft.altTexts = generatedAlts;
                    }
                    this.setChecklistStatus('images', 'success');

                    // Step 5: Optimize images (compress)
                    this.aiProgress = 88;
                    this.aiStepText = 'گام ۵/۷: بهینه‌سازی و فشرده‌سازی تصاویر...';
                    this.setChecklistStatus('optimize_images', 'loading');
                    try {
                        if (typeof this.optimizeAllImages === 'function') {
                            await this.optimizeAllImages({ silent: true });
                        } else if (typeof this.runImageOptimize === 'function') {
                            await this.runImageOptimize();
                        } else {
                            // Direct AJAX fallback
                            const cfgO = window.bankaiEditorSeo || {};
                            const bodyO = new FormData();
                            bodyO.append('action', 'bankai_optimize_post_images');
                            bodyO.append('nonce', cfgO.adminNonce || cfgO.nonce || '');
                            bodyO.append('post_id', String(this.postId || 0));
                            await fetch(cfgO.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                                method: 'POST', credentials: 'same-origin', body: bodyO
                            });
                        }
                        this.setChecklistStatus('optimize_images', 'success');
                    } catch (eOpt) {
                        this.setChecklistStatus('optimize_images', 'error');
                    }

                    // Step 6: Schema
                    this.aiProgress = 93;
                    this.aiStepText = 'گام ۶/۷: تولید اسکیما JSON-LD...';
                    this.setChecklistStatus('schema', 'loading');
                    try {
                        if (typeof this.buildSchema === 'function') {
                            this.buildSchema();
                        } else if (typeof this.generateSchema === 'function') {
                            this.generateSchema();
                        }
                        this.setChecklistStatus('schema', 'success');
                    } catch (eSch) {
                        this.setChecklistStatus('schema', 'error');
                    }

                    // Step 7: Categories & Tags
                    this.aiProgress = 97;
                    this.aiStepText = 'گام ۷/۷: تولید دسته و برچسب خودکار...';
                    this.setChecklistStatus('terms', 'loading');
                    try {
                        const kwList = (this.aiDraft.keywords && this.aiDraft.keywords.length)
                            ? this.aiDraft.keywords
                            : (this.keywordList || []);
                        const focusKw = this.aiDraft.focus_keyword || this.seo.focus_keyword || (kwList[0] || '');
                        const cfgT = window.bankaiEditorSeo || {};
                        const bodyT = new FormData();
                        bodyT.append('action', 'bankai_ai_assign_terms');
                        bodyT.append('nonce', cfgT.adminNonce || cfgT.nonce || '');
                        bodyT.append('post_id', String(this.postId || 0));
                        bodyT.append('focus', focusKw);
                        bodyT.append('keywords', JSON.stringify(kwList));
                        const rT = await fetch(cfgT.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                            method: 'POST', credentials: 'same-origin', body: bodyT
                        });
                        const jT = await rT.json().catch(() => ({}));
                        if (jT && jT.success) {
                            this.setChecklistStatus('terms', 'success');
                            // Sync to block editor category/tag panels
                            try {
                                const data = jT.data || {};
                                const catIds = data.category_ids || [];
                                const tagIds = data.tag_ids || [];
                                if (window.wp && wp.data && wp.data.dispatch) {
                                    const d = wp.data.dispatch('core/editor');
                                    if (d && d.editPost) {
                                        const patch = {};
                                        if (catIds.length) patch.categories = catIds;
                                        if (tagIds.length) patch.tags = tagIds;
                                        if (Object.keys(patch).length) d.editPost(patch);
                                    }
                                }
                            } catch (eSync) { /* ignore UI sync errors */ }
                        } else {
                            this.setChecklistStatus('terms', 'error');
                        }
                    } catch (eTerms) {
                        this.setChecklistStatus('terms', 'error');
                    }

                    this.aiProgress = 100;
                    this.aiStepText = 'عملیات یک‌پارچه با موفقیت انجام شد!';
                    this.aiReviewOpen = true;
                    this.showToast('تمام بخش‌های سئو با هوش مصنوعی تحلیل و آماده تایید شد', 'success');
                }

            } catch (e) {
                let msg = (e && e.message) ? e.message : 'خطا در اجرای فرآیند هوش مصنوعی';
                if ((e && e.name === 'AbortError') || /aborted without reason/i.test(msg) || /The user aborted/i.test(msg)) {
                    msg = 'زمان پاسخ موتور AI به پایان رسید یا درخواست قطع شد. کلید API، مدل و اتصال اینترنت را بررسی کنید و دوباره تلاش کنید.';
                }
                this.showToast(msg, 'error');
                this.aiStepText = 'خطا: ' + msg;
                if (this.aiActiveAction) {
                    this.setChecklistStatus(this.aiActiveAction === 'auto_all' ? 'analyze' : this.aiActiveAction, 'error');
                }
            } finally {
                this.aiLoading = false;
            }
        },

        applyApprovedAiChanges() {
            const sel = this.aiReviewSelection;
            let appliedCount = 0;

            if (sel.applyMeta && (this.aiDraft.title || this.aiDraft.description)) {
                if (this.aiDraft.title) this.seo.seo_title = this.aiDraft.title;
                if (this.aiDraft.description) this.seo.description = this.aiDraft.description;
                if (this.aiDraft.focus_keyword) this.seo.focus_keyword = this.aiDraft.focus_keyword;
                appliedCount++;
            }

            if (sel.applyKeywords && this.aiDraft.keywords && this.aiDraft.keywords.length) {
                this.aiDraft.keywords.forEach((k) => {
                    if (k && !this.keywordList.includes(k)) {
                        this.keywordList.push(k);
                    }
                });
                this.pushKeywordsToSeo();
                appliedCount++;
            }

            if (sel.applyRewrite && this.aiDraft.rewrite) {
                this.setEditorRawContent(this.aiDraft.rewrite);
                appliedCount++;
            }

            if (sel.applyImages && this.aiDraft.altTexts && this.aiDraft.altTexts.length) {
                this.aiDraft.altTexts.forEach((item) => {
                    if (item.src && item.suggestedAlt) {
                        this.updateImageAltInEditor(item.src, item.suggestedAlt);
                        // Reflect Alt in media tab cards
                        const norm = (u) => String(u || '').split('?')[0].replace(/\/+$/, '');
                        (this.postImages || []).forEach((img) => {
                            if (norm(img.src) === norm(item.src) || norm(img.original_src) === norm(item.src)) {
                                img.alt = item.suggestedAlt;
                                img.new_alt = item.suggestedAlt;
                            }
                        });
                        // If list empty or image missing, push entry
                        if (!this.postImages) this.postImages = [];
                        const exists = this.postImages.some((img) => norm(img.src) === norm(item.src) || norm(img.original_src) === norm(item.src));
                        if (!exists) {
                            this.postImages.push({
                                id: 'ai_' + this.postImages.length,
                                src: item.src,
                                original_src: item.src,
                                alt: item.suggestedAlt,
                                new_alt: item.suggestedAlt,
                                format: '',
                                size: 0,
                                watermarked: false
                            });
                        }
                    }
                });
                // Refresh list so UI binds
                this.postImages = this.postImages.slice();
                appliedCount++;
            }

            if (sel.applyLinks && this.aiDraft.smartLinks && this.aiDraft.smartLinks.length) {
                this.approveAllPendingLinks();
                appliedCount++;
            }

            this.onFieldChange();
            this.aiReviewOpen = false;
            this.aiOpen = false;
            this.showToast('تغییرات تاییدشده سئو با موفقیت اعمال شدند', 'success');
            // Refresh media list so new Alts appear in cards
            if (this.activeTab === 'media' || (this.postImages && this.postImages.length)) {
                try { this.scanPostImages(); } catch (e) {}
            }
        },


        toggleGroup(gkey) {
            this.openGroups[gkey] = !this.openGroups[gkey];
        },

        groupPassed(group) {
            if (!group || !group.items || !group.items.length) return true;
            return group.items.every((c) => c.passed);
        },

        groupPassedCount(group) {
            if (!group || !group.items) return 0;
            return group.items.filter((c) => c.passed).length;
        },

        editKeyword(i) {
            if (i < 0 || i >= this.keywordList.length) return;
            const current = this.keywordList[i];
            const next = window.prompt('ویرایش کلیدواژه', current);
            if (next === null) return;
            const v = String(next).trim();
            if (!v) {
                this.removeKeyword(i);
                return;
            }
            this.keywordList[i] = v;
            if (i === 0) this.seo.focus_keyword = v;
            this.pushKeywordsToSeo();
            this.onFieldChange();
        },

        rebuildSchema() {
            this.syncSchemaFromForms(true);
        },

        onSchemaTypeChange() {
            if (this.schemaType === 'FAQPage' && (!this.faqItems || !this.faqItems.length)) {
                this.faqItems = [{ q: '', a: '' }];
            }
            if (this.schemaType === 'HowTo' && (!this.howToSteps || !this.howToSteps.length)) {
                this.howToSteps = [{ name: '', text: '' }];
            }
            this.syncSchemaFromForms(true);
        },

        syncSchemaFromForms(force) {
            const type = this.schemaType || 'Article';
            const title = this.seo.seo_title || this.seo.title || '';
            const desc = this.seo.description || '';
            const url = this.seo.permalink || '';
            const schema = {
                '@context': 'https://schema.org',
                '@type': type,
                headline: title,
                name: title,
                description: desc,
                url: url
            };
            if (type === 'FAQPage') {
                const ents = (this.faqItems || [])
                    .filter((i) => (i.q || '').trim())
                    .map((i) => ({
                        '@type': 'Question',
                        name: i.q,
                        acceptedAnswer: { '@type': 'Answer', text: i.a || '' }
                    }));
                schema.mainEntity = ents;
            }
            if (type === 'HowTo') {
                schema.step = (this.howToSteps || [])
                    .filter((s) => (s.name || s.text || '').trim())
                    .map((s, idx) => ({
                        '@type': 'HowToStep',
                        position: idx + 1,
                        name: s.name || ('مرحله ' + (idx + 1)),
                        text: s.text || ''
                    }));
            }
            this.seo.schema = JSON.stringify(schema, null, 2);
            if (force !== false) this.onFieldChange();
        },

        pickMedia(field) {
            if (!window.wp || !wp.media) {
                this.showToast('کتابخانه رسانه در دسترس نیست', 'error');
                return;
            }
            const frame = wp.media({
                title: 'انتخاب تصویر',
                button: { text: 'استفاده از این تصویر' },
                multiple: false,
                library: { type: 'image' }
            });
            const self = this;
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                const url = (att && (att.url || (att.sizes && att.sizes.full && att.sizes.full.url))) || '';
                if (!url) return;
                if (field === 'og_image') self.seo.og_image = url;
                else if (field === 'x_image') self.seo.x_image = url;
                self.onFieldChange();
            });
            frame.open();
        },


        
        autoSplitLongParagraphs() {
            let html = this.getEditorRawContent();
            if (!html) {
                this.showToast('محتوایی برای اصلاح نیست', 'warn');
                return;
            }
            try {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const blocks = doc.querySelectorAll('p, div[class*="wp-block-paragraph"]');
                let changed = 0;
                blocks.forEach((el) => {
                    const text = (el.textContent || '').trim();
                    const words = text.split(/\s+/).filter(Boolean);
                    if (words.length <= 120) return;
                    // Split on sentence endings (. ! ? …) while keeping delimiter
                    const parts = text.split(/(?<=[.!?…۔])\s+/u).map((s) => s.trim()).filter(Boolean);
                    if (parts.length < 2) return;
                    const frag = doc.createDocumentFragment();
                    parts.forEach((part) => {
                        const p = doc.createElement('p');
                        p.textContent = part;
                        frag.appendChild(p);
                    });
                    el.replaceWith(frag);
                    changed++;
                });
                if (!changed) {
                    // Fallback: whole body text split for classic content without p tags
                    const body = doc.body;
                    if (body && (body.textContent || '').split(/\s+/).length > 120) {
                        const parts = (body.textContent || '').split(/(?<=[.!?…۔])\s+/u).map((s) => s.trim()).filter(Boolean);
                        if (parts.length > 1) {
                            body.innerHTML = parts.map((p) => '<p>' + p.replace(/</g, '&lt;') + '</p>').join('');
                            changed = parts.length;
                        }
                    }
                }
                if (!changed) {
                    this.showToast('پاراگراف بلندی برای تقسیم یافت نشد', 'info');
                    return;
                }
                this.setEditorRawContent(doc.body.innerHTML);
                this.showToast(changed + ' پاراگراف بلند کوتاه شد', 'success');
                this.analyze(false);
            } catch (e) {
                this.showToast('خطا در کوتاه‌سازی پاراگراف', 'error');
            }
        },


        findMatchesInContent(needle) {
            const term = String(needle || '').trim();
            if (term.length < 2) return [];
            const html = this.getEditorRawContent() || '';
            const plain = html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
            const matches = [];
            const lower = plain.toLowerCase();
            const t = term.toLowerCase();
            let from = 0;
            while (from < lower.length && matches.length < 15) {
                const idx = lower.indexOf(t, from);
                if (idx === -1) break;
                const start = Math.max(0, idx - 28);
                const end = Math.min(plain.length, idx + term.length + 28);
                const snippet = (start > 0 ? '…' : '') + plain.slice(start, end).trim() + (end < plain.length ? '…' : '');
                matches.push({ text: term, index: idx, snippet: snippet, selected: true, targetId: '' });
                from = idx + Math.max(1, term.length);
            }
            return matches;
        },

        acceptedInternalPosts() {
            return (this.postResults || []).filter((p) => p.accepted && !p.rejected);
        },

        acceptAllInternalPosts() {
            (this.postResults || []).forEach((p) => { p.accepted = true; p.rejected = false; });
        },

        loadInternalContentMatches() {
            const term = (this.linkAnchor || this.seo.focus_keyword || '').trim();
            this.contentMatchesInternal = this.findMatchesInContent(term);
            const posts = this.acceptedInternalPosts();
            this.contentMatchesInternal.forEach((m, i) => {
                if (posts.length) {
                    m.targetId = String(posts[i % posts.length].id);
                    m.selected = true;
                }
            });
            if (!this.contentMatchesInternal.length) {
                this.showToast('عبارتی در متن برای لینک یافت نشد', 'warn');
            }
        },

        applyInternalRandom() {
            const posts = this.acceptedInternalPosts();
            if (!posts.length) {
                this.showToast('ابتدا مقالات را تایید کنید', 'warn');
                return;
            }
            const term = (this.linkAnchor || '').trim();
            if (!term) {
                this.showToast('کلیدواژه خالی است', 'warn');
                return;
            }
            if (!this.contentMatchesInternal.length) {
                this.loadInternalContentMatches();
            }
            const pool = this.contentMatchesInternal.filter((m) => m.selected);
            const list = pool.length ? pool : this.contentMatchesInternal;
            let applied = 0;
            list.forEach((m) => {
                const p = posts[Math.floor(Math.random() * posts.length)];
                if (!p) return;
                if (this.insertLinkIntoEditor(m.text || term, p.permalink, p.title, false, true)) {
                    applied++;
                }
            });
            if (applied) {
                this.showToast(applied + ' لینک تصادفی اعمال شد', 'success');
                this.loadLinks();
                this.analyze(false);
            } else {
                this.showToast('امکان درج لینک در متن نبود', 'error');
            }
        },

        findExternalMatches() {
            const term = (this.extAnchor || '').trim();
            if (term.length < 2) {
                this.showToast('عبارت جستجو را وارد کنید', 'warn');
                return;
            }
            this.contentMatchesExternal = this.findMatchesInContent(term);
            if (!this.contentMatchesExternal.length) {
                this.showToast('عبارتی در متن مقاله یافت نشد', 'warn');
            }
        },

        confirmExternalLinking() {
            const url = (this.extUrl || '').trim();
            if (!url) {
                this.showToast('آدرس مقصد را وارد کنید', 'error');
                return;
            }
            const selected = (this.contentMatchesExternal || []).filter((m) => m.selected);
            if (!selected.length) {
                this.showToast('حداقل یک تطابق را انتخاب کنید', 'warn');
                return;
            }
            let applied = 0;
            selected.forEach((m) => {
                if (this.insertLinkIntoEditor(m.text || this.extAnchor, url, '', !!this.extNofollow, true)) {
                    applied++;
                }
            });
            if (applied) {
                this.contentMatchesExternal = [];
                this.showToast(applied + ' لینک خارجی در متن اعمال شد', 'success');
                this.loadLinks();
                this.analyze(false);
            } else {
                this.showToast('عبارت در متن یافت نشد — لینک به انتهای مقاله اضافه نمی‌شود', 'error');
            }
        },

        async runInternalSearch() {
            const term = (this.linkAnchor || this.seo.focus_keyword || '').trim();
            if (!term) {
                this.showToast('کلیدواژه را وارد کنید', 'warn');
                return;
            }
            this.linkBusy = true;
            this.contentMatchesInternal = [];
            this.selectedInternalPost = null;
            try {
                const res = await fetch(this.restBase() + 'seo/search-posts', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({ s: term, post_id: this.postId })
                });
                const json = await res.json();
                let posts = [];
                if (json.success && json.data) {
                    posts = json.data.posts || json.data || [];
                }
                try {
                    const res2 = await fetch(this.restBase() + 'seo/suggest-links', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: this.restHeaders(true),
                        body: JSON.stringify({
                            post_id: this.postId,
                            keywords: [term].concat(this.keywordList || []),
                            content: ((this.getEditorContext().rawHtml) || '').slice(0, 4000)
                        })
                    });
                    const j2 = await res2.json();
                    if (j2.success && j2.data && Array.isArray(j2.data.suggestions)) {
                        j2.data.suggestions.forEach((s) => {
                            if (!posts.some((p) => p.id === s.id)) {
                                posts.push({
                                    id: s.id,
                                    title: s.title,
                                    permalink: s.permalink,
                                    match_reason: s.in_content ? 'کلیدواژه در محتوا' : 'مرتبط با سئو'
                                });
                            }
                        });
                    }
                } catch (e2) {}

                this.postResults = posts.map((p) => Object.assign({}, p, {
                    accepted: false,
                    rejected: false,
                    match_reason: p.match_reason || ((p.title && String(p.title).toLowerCase().includes(term.toLowerCase())) ? 'تطابق عنوان' : 'جستجوی سایت')
                }));
                if (!this.postResults.length) {
                    this.showToast('مقاله مرتبطی یافت نشد', 'info');
                } else {
                    this.showToast(this.postResults.length + ' مقاله مرتبط — تایید کنید سپس محل لینک را مشخص کنید', 'success');
                }
            } catch (e) {
                this.showToast('خطا در جستجوی مقالات', 'error');
            } finally {
                this.linkBusy = false;
            }
        },

        confirmInternalLinking() {
            const selected = (this.contentMatchesInternal || []).filter((m) => m.selected && m.targetId);
            if (!selected.length) {
                this.showToast('محل لینک و مقاله مقصد را مشخص کنید', 'warn');
                return;
            }
            let applied = 0;
            selected.forEach((m) => {
                const p = this.acceptedInternalPosts().find((x) => String(x.id) === String(m.targetId))
                    || (this.postResults || []).find((x) => String(x.id) === String(m.targetId));
                if (!p || !p.permalink) return;
                if (this.insertLinkIntoEditor(m.text || this.linkAnchor, p.permalink, p.title, false, true)) {
                    applied++;
                }
            });
            if (applied) {
                this.contentMatchesInternal = [];
                this.showToast(applied + ' لینک داخلی با متن برجسته اعمال شد', 'success');
                this.loadLinks();
                this.analyze(false);
            } else {
                this.showToast('درج لینک در متن ممکن نشد', 'error');
            }
        },


        formatBytes(n) {
            n = Number(n) || 0;
            if (n < 1024) return n + ' B';
            if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
            return (n / 1048576).toFixed(2) + ' MB';
        },

        removeLinkFromContent(link) {
            if (!link || !link.href) return;
            let html = this.getEditorRawContent();
            if (!html) return;
            const targetHref = String(link.href);
            try {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const anchors = doc.querySelectorAll('a[href]');
                let removed = 0;
                anchors.forEach((a) => {
                    const h = a.getAttribute('href') || '';
                    if (h === targetHref) {
                        const textNode = doc.createTextNode(a.textContent || '');
                        if (a.parentNode) {
                            a.parentNode.replaceChild(textNode, a);
                            removed++;
                        }
                    }
                });
                if (!removed) {
                    this.showToast('لینک در ویرایشگر یافت نشد', 'warn');
                    return;
                }
                const next = doc.body ? doc.body.innerHTML : html;
                this.setEditorRawContent(next);
                this.showToast('لینک از متن حذف شد', 'success');
                setTimeout(() => this.loadLinks(), 300);
            } catch (e) {
                this.showToast('خطا در حذف لینک', 'error');
            }
        },

        async scanPostImages() {
            this.optBusy = true;
            try {
                const cfg = window.bankaiEditorSeo || {};
                const content = this.getEditorRawContent() || this.seo.content || '';
                // Client-side scan first for instant UI
                const local = this.scanImagesLocal(content);
                this.postImages = this.mergeCoverImage(local);
                this.optReport = [];

                const body = new FormData();
                body.append('action', 'bankai_scan_post_images');
                body.append('nonce', cfg.adminNonce || cfg.nonce || '');
                body.append('post_id', this.postId || 0);
                body.append('content', content);
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body
                });
                const json = await r.json();
                if (json && json.success && json.data && Array.isArray(json.data.images)) {
                    this.postImages = this.mergeCoverImage(json.data.images || []);
                }
            } catch (e) {
                console.warn(e);
            } finally {
                this.optBusy = false;
            }
        },


        
        async syncFromLegacySeo() {
            if (this.legacySyncBusy) return;
            this.legacySyncBusy = true;
            try {
                const cfg = window.bankaiEditorSeo || {};
                const body = new FormData();
                body.append('action', 'bankai_seo_sync_post_from_plugins');
                body.append('nonce', cfg.adminNonce || cfg.nonce || '');
                body.append('post_id', String(this.postId || 0));
                body.append('overwrite', '1');
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body
                });
                const j = await r.json();
                if (!j || !j.success) {
                    throw new Error((j && j.data && j.data.message) || 'داده‌ای از Rank Math/Yoast یافت نشد');
                }
                const seo = (j.data && j.data.seo) ? j.data.seo : {};
                if (seo.seo_title) this.seo.seo_title = seo.seo_title;
                if (seo.description) this.seo.description = seo.description;
                if (seo.focus_keyword) this.seo.focus_keyword = seo.focus_keyword;
                if (seo.canonical) this.seo.canonical = seo.canonical;
                if (seo.og_title) this.seo.og_title = seo.og_title;
                if (seo.og_description) this.seo.og_description = seo.og_description;
                if (seo.og_image) this.seo.og_image = seo.og_image;
                if (seo.keywords) {
                    try {
                        const kw = typeof seo.keywords === 'string' ? JSON.parse(seo.keywords) : seo.keywords;
                        if (Array.isArray(kw)) this.keywordList = kw;
                    } catch (e) {}
                }
                if (typeof this.refreshAnalysis === 'function') this.refreshAnalysis();
                else if (typeof this.recalcScore === 'function') this.recalcScore();
                const src = (j.data && j.data.source) || '';
                this.legacySyncDone = true;
                this.showToast('سئو از ' + (src || 'پلاگین‌های قبلی') + ' وارد شد (' + ((j.data.fields || []).length) + ' فیلد)', 'success');
            } catch (e) {
                this.legacySyncDone = false;
                this.showToast((e && e.message) || 'خطا در همگام‌سازی', 'error');
            } finally {
                this.legacySyncBusy = false;
            }
        },

        mergeCoverImage(list) {
            const arr = Array.isArray(list) ? list.slice() : [];
            try {
                let coverUrl = '';
                let coverId = 0;
                const cfg = window.bankaiEditorSeo || {};
                if (cfg.featuredUrl) {
                    coverUrl = String(cfg.featuredUrl);
                    coverId = parseInt(cfg.featuredId || 0, 10) || 0;
                }
                if (!coverUrl && window.wp && wp.data && wp.data.select) {
                    const ed = wp.data.select('core/editor');
                    if (ed && ed.getEditedPostAttribute) {
                        coverId = ed.getEditedPostAttribute('featured_media') || 0;
                    }
                    if (coverId && wp.data.select('core')) {
                        const media = wp.data.select('core').getMedia(coverId);
                        if (media && media.source_url) coverUrl = media.source_url;
                        else if (media && media.media_details && media.media_details.sizes) {
                            const s = media.media_details.sizes;
                            coverUrl = (s.large && s.large.source_url) || (s.full && s.full.source_url) || coverUrl;
                        }
                    }
                }
                if (!coverUrl) {
                    const selectors = [
                        '#postimagediv img',
                        '.editor-post-featured-image img',
                        '.editor-post-featured-image__container img',
                        '.components-responsive-wrapper img',
                        'img.attachment-post-thumbnail'
                    ];
                    for (const sel of selectors) {
                        const thumb = document.querySelector(sel);
                        if (thumb && thumb.src && !thumb.src.includes('data:')) {
                            coverUrl = thumb.src;
                            break;
                        }
                    }
                }
                // Classic editor featured id hidden field
                if (!coverId) {
                    const hid = document.getElementById('_thumbnail_id');
                    if (hid && hid.value && parseInt(hid.value, 10) > 0) {
                        coverId = parseInt(hid.value, 10);
                    }
                }
                if (coverUrl) {
                    const normalize = (u) => String(u || '').split('?')[0];
                    let coverAlt = '';
                    try {
                        if (coverId && window.wp && wp.data && wp.data.select('core')) {
                            const media = wp.data.select('core').getMedia(coverId);
                            if (media) {
                                coverAlt = (media.alt_text || media.alt || '') + '';
                                if (!coverUrl && media.source_url) coverUrl = media.source_url;
                            }
                        }
                    } catch (eAlt) {}
                    const exists = arr.some((i) => normalize(i.src) === normalize(coverUrl) || normalize(i.original_src) === normalize(coverUrl) || i.is_cover);
                    if (!exists) {
                        arr.unshift({
                            id: 'cover_' + (coverId || '0'),
                            src: coverUrl,
                            original_src: coverUrl,
                            alt: coverAlt,
                            new_alt: coverAlt,
                            format: (coverUrl.split('.').pop() || '').split(/\#|\?/)[0].toLowerCase(),
                            size: 0,
                            is_cover: true,
                            attachment_id: coverId || 0,
                            watermarked: false
                        });
                    } else {
                        arr.forEach((i) => {
                            if (normalize(i.src) === normalize(coverUrl) || normalize(i.original_src) === normalize(coverUrl)) {
                                i.is_cover = true;
                            }
                        });
                    }
                }
            } catch (e) { console.warn('[Bankai] cover merge', e); }
            return arr;
        },

        async generateSingleAlt(img) {
            if (!img) return;
            try {
                this.aiLoading = true;
                const ctx = this.getEditorContext();
                const res = await this.callSeoAi('image_alt_text', ctx, {
                    img_url: img.src || img.original_src || '',
                    img_context: (ctx.title || '') + ' ' + (ctx.focus_keyword || '')
                });
                const alt = res.alt_text || res.text || '';
                if (alt) {
                    img.suggestedAlt = alt;
                    this.showToast('Alt پیشنهاد شد — تأیید کنید', 'success');
                } else {
                    this.showToast('Alt تولید نشد', 'error');
                }
            } catch (e) {
                this.showToast((e && e.message) || 'خطا', 'error');
            } finally {
                this.aiLoading = false;
            }
        },

        async watermarkSingleImage(img) {
            if (!img) return;
            try {
                this.optBusy = true;
                this.optOptions.watermark = true;
                await this.runOptimizeImages();
                this.showToast('واترمارک اعمال شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا', 'error');
            } finally {
                this.optBusy = false;
            }
        },

        scanImagesLocal(content) {
            if (!content) return [];
            try {
                const doc = new DOMParser().parseFromString(content, 'text/html');
                const imgs = [...doc.querySelectorAll('img')];
                return imgs.map((img, i) => ({
                    id: 'local_' + i,
                    src: img.getAttribute('src') || '',
                    alt: img.getAttribute('alt') || '',
                    has_alt: !!(img.getAttribute('alt') || '').trim(),
                    format: ((img.getAttribute('src') || '').split('.').pop() || '').split('?')[0],
                    size: 0,
                    watermarked: false,
                    attachment_id: 0
                })).filter((x) => x.src);
            } catch (e) {
                return [];
            }
        },

        async runOptimizeImages() {
            this.optBusy = true;
            this.optReport = [];
            try {
                const cfg = window.bankaiEditorSeo || {};
                const content = this.getEditorRawContent() || this.seo.content || '';
                const body = new FormData();
                body.append('action', 'bankai_optimize_post_images');
                body.append('nonce', cfg.adminNonce || cfg.nonce || '');
                body.append('post_id', this.postId || 0);
                body.append('content', content);
                body.append('convert_webp', this.optOptions.convertWebp ? '1' : '0');
                body.append('resize', this.optOptions.resize ? '1' : '0');
                body.append('watermark', this.optOptions.watermark ? '1' : '0');
                body.append('fill_alt', this.optOptions.fillAlt ? '1' : '0');
                body.append('max_width', String(this.optOptions.maxWidth || 1600));
                body.append('max_height', String(this.optOptions.maxHeight || 0));
                body.append('quality', String(this.optOptions.quality || 82));
                body.append('default_alt', this.optOptions.defaultAlt || '');

                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body
                });
                const json = await r.json();
                if (!json || !json.success) {
                    throw new Error((json && json.data && json.data.message) || 'خطا در بهینه‌سازی');
                }
                const data = json.data || {};
                this.optReport = data.report || [];
                if (data.content) {
                    this.setEditorRawContent(data.content);
                }
                this.postImages = this.optReport.map((row, i) => ({
                    id: row.is_cover ? ('cover_' + (row.attachment_id || i)) : ('rep_' + i),
                    src: row.src || row.original_src,
                    original_src: row.original_src || row.src,
                    alt: row.new_alt || row.alt || '',
                    new_alt: row.new_alt || row.alt || '',
                    format: row.format_after || row.format_before,
                    size: this.formatBytes(row.size_after || row.size_before || 0),
                    format_before: row.format_before || '',
                    format_after: row.format_after || row.format_before || '',
                    size_before: this.formatBytes(row.size_before || 0),
                    size_after: this.formatBytes(row.size_after || 0),
                    watermarked: !!row.watermarked,
                    attachment_id: row.attachment_id || 0,
                    is_cover: !!row.is_cover,
                    converted: row.converted,
                    resized: row.resized,
                    actions: row.actions
                }));
                // Keep featured/cover in list even if server omitted it
                this.postImages = this.mergeCoverImage(this.postImages);
                this.showToast(data.message || 'بهینه‌سازی انجام شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا', 'error');
            } finally {
                this.optBusy = false;
            }
        },

        async updateImageAlt(img, alt) {
            if (!img) return;
            alt = String(alt || '').trim();
            img.alt = alt;
            img.new_alt = alt;
            // Update in editor content
            this.updateImageAltInEditor(img.src || img.original_src, alt);
            try {
                const cfg = window.bankaiEditorSeo || {};
                const body = new FormData();
                body.append('action', 'bankai_update_image_alt');
                body.append('nonce', cfg.adminNonce || cfg.nonce || '');
                body.append('src', img.src || img.original_src || '');
                body.append('alt', alt);
                body.append('content', this.getEditorRawContent() || '');
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body
                });
                const json = await r.json();
                if (json && json.success && json.data && json.data.content) {
                    this.setEditorRawContent(json.data.content);
                }
                this.showToast('Alt ذخیره شد', 'success');
            } catch (e) {
                this.showToast('خطا در ذخیره Alt', 'error');
            }
        },

        async removeImageWatermark(img) {
            if (!img) return;
            try {
                const cfg = window.bankaiEditorSeo || {};
                const body = new FormData();
                body.append('action', 'bankai_remove_image_watermark');
                body.append('nonce', cfg.adminNonce || cfg.nonce || '');
                body.append('src', img.src || '');
                body.append('attachment_id', img.attachment_id || 0);
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST', credentials: 'same-origin', body
                });
                const json = await r.json();
                if (!json || !json.success) {
                    throw new Error((json && json.data && json.data.message) || 'حذف ناموفق');
                }
                img.watermarked = false;
                if (json.data && json.data.src) img.src = json.data.src;
                this.showToast(json.data.message || 'واترمارک حذف شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا', 'error');
            }
        },

        showToast(message, type = 'info', duration = 0) {
            let t = type || 'info';
            if (t === 'warn') t = 'warning';
            const ms = duration || (t === 'error' ? 4800 : 3600);
            this.toast = { show: true, message: message || '', type: t, duration: ms };
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => { this.toast.show = false; }, ms);
        }
        });
    }

    function registerBankaiSeoAlpine() {
        if (!window.Alpine || typeof Alpine.data !== 'function') return;
        if (window.__bankaiSeoAlpineRegistered) return;
        Alpine.data('bankaiSeoSidebar', bankaiSeoSidebarFactory);
        window.__bankaiSeoAlpineRegistered = true;
    }

    document.addEventListener('alpine:init', registerBankaiSeoAlpine);
    // If Alpine already booted (script order race), register immediately
    if (window.Alpine && window.Alpine.version) {
        registerBankaiSeoAlpine();
    }
    // Late fallback for Gutenberg inject
    window.bankaiRegisterSeoAlpine = registerBankaiSeoAlpine;
})();
