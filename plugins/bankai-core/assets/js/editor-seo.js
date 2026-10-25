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

        openGroups: { basic: true, advanced: false, title: false, content: false, aeo: true, geo: true },
        schemaType: 'Article',
        faqItems: [{ q: '', a: '' }],
        howToSteps: [{ name: '', text: '' }],

        // Links state
        linkData: { internal: [], external: [], counts: { internal: 0, external: 0 } },
        pendingAiLinks: [],
        linkApplyBusy: false,
        linksSubTab: 'active', // 'active' | 'pending'
        linkTypeTab: 'internal', // 'internal' | 'external'
        linkSourceMode: null, // 'ai' | 'manual' | null — mutually exclusive panels
        ctaText: '',
        ctaUrl: '',
        ctaAlign: 'right',
        ctaBold: true,
        ctaPlacements: [],
        showBulkAltPanel: false,
        bulkAltText: '',
        linkAnchor: '',
        postResults: [],
        extAnchor: '',
        extUrl: '',
        extNofollow: false,

        linkBusy: false,
        optBusy: false,
        // Enhanced image compression with before/after preview and uniform sizing
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
        aiModalTitle: 'دستیار AI سئو',
        modalSubTab: 'geo',
        aiImgType: 'cover',
        aiImgStyle: 'photorealistic',
        aiImgStep: 1,          // 1=select, 2=progress, 3=result
        aiImgProgress: 0,
        aiImgStepText: '',
        aiImgResultUrl: '',
        aiImgResultId: 0,
        aiImgResultHtml: '',
        aiOptions: { style: 'seo', count: '5' },
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
        aiPhase: 'setup', // setup | running | review
        aiRunChecklist: [],

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

        get subScores() {
            if (this.analysis && this.analysis.sub_scores) {
                return this.analysis.sub_scores;
            }
            const checks = (this.analysis && this.analysis.checks) || [];
            let seoPass = 0, seoTot = 0;
            let aeoPass = 0, aeoTot = 0;
            let geoPass = 0, geoTot = 0;

            checks.forEach(c => {
                if (c.status === 'not_applicable') return;
                const grp = c.group || 'basic';
                const key = c.key || '';
                const isPass = c.passed || c.status === 'pass';

                if (grp === 'basic' || grp === 'title' || key.includes('kw') || key.includes('desc') || key.includes('url') || key.includes('title')) {
                    seoTot++;
                    if (isPass) seoPass++;
                }
                if (grp === 'content' || key.includes('list') || key.includes('heading') || key.includes('para') || key.includes('sentence') || key.includes('faq')) {
                    aeoTot++;
                    if (isPass) aeoPass++;
                }
                if (grp === 'advanced' || key.includes('media') || key.includes('unique') || key.includes('density') || key.includes('length')) {
                    geoTot++;
                    if (isPass) geoPass++;
                }
            });

            const mainScore = this.analysis ? (this.analysis.score || 0) : 0;
            const seoScore = seoTot > 0 ? Math.round((seoPass / seoTot) * 100) : mainScore;
            const aeoScore = aeoTot > 0 ? Math.round((aeoPass / aeoTot) * 100) : Math.max(0, mainScore - 5);
            const geoScore = geoTot > 0 ? Math.round((geoPass / geoTot) * 100) : Math.round(mainScore * 0.9);

            return {
                seo: Math.min(100, Math.max(0, seoScore)),
                aeo: Math.min(100, Math.max(0, aeoScore)),
                geo: Math.min(100, Math.max(0, geoScore))
            };
        },

        subScoreOffset(val) {
            const c = 2 * Math.PI * 12;
            const s = Math.min(100, Math.max(0, Number(val) || 0));
            return c - (c * s / 100);
        },

        subScoreColor(val) {
            const s = Number(val) || 0;
            if (s < 25) return '#ef4444';
            if (s < 40) return '#f97316';
            if (s < 55) return '#f59e0b';
            if (s < 65) return '#eab308';
            if (s < 75) return '#3b82f6';
            if (s < 85) return '#06b6d4';
            if (s < 95) return '#10b981';
            return '#22c55e';
        },

        checkStatusClass(check) {
            const st = (check && check.status) ? check.status : (check && check.passed ? 'pass' : 'fail');
            if (st === 'pass') return 'is-pass';
            if (st === 'not_applicable') return 'is-na';
            if (st === 'unknown') return 'is-unknown';
            if (st === 'warning') return 'is-warn';
            return 'is-fail';
        },
        checkStatusLabel(check) {
            const st = (check && check.status) ? check.status : (check && check.passed ? 'pass' : 'fail');
            const map = {
                pass: 'تایید',
                fail: 'رد',
                warning: 'هشدار',
                not_applicable: 'نامرتبط',
                unknown: 'نامشخص'
            };
            return map[st] || (check && check.passed ? 'تایید' : 'هشدار');
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
            return this.allChecksList().filter(c => (c.status === 'fail' || c.status === 'warning' || (!c.passed && c.status !== 'not_applicable' && c.status !== 'unknown')));
        },
        passedChecksList() {
            return this.allChecksList().filter(c => c.passed || c.status === 'pass');
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
                return this.failedChecksList();
            }
            if (this.checklistFilter === 'passed') {
                return this.passedChecksList();
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
                const faqHtml = (res && (res.faq_html || res.content || res.text || res.html)) || '';
                if (faqHtml) {
                    this.insertContentIntoEditor(faqHtml, 'append');
                    this.showToast('بخش سوالات متداول با موفقیت به مقاله اضافه شد.', 'success');
                    await this.analyze(false);
                } else {
                    this.showToast((res && res.message) || 'خطا در تولید بخش FAQ', 'error');
                }
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در ارتباط با هوش مصنوعی', 'error');
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
                const summaryHtml = (res && (res.summary_html || res.content || res.text || res.html)) || '';
                if (summaryHtml) {
                    this.insertContentIntoEditor(summaryHtml, 'prepend');
                    this.showToast('خلاصه نکات کلیدی با موفقیت به مقاله اضافه شد.', 'success');
                    await this.analyze(false);
                } else {
                    this.showToast((res && res.message) || 'خطا در تولید خلاصه نکات کلیدی', 'error');
                }
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در ارتباط با هوش مصنوعی', 'error');
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
            this.postResults = [];
            this.linkSourceMode = 'ai';
            this.showToast('در حال دریافت پیشنهادهای لینک داخلی...', 'info');
            try {
                const ctx = this.getEditorContext();
                const res = await this.callSeoAi('smart_internal_links', ctx);
                const list = (res && (res.smartLinks || res.links || res.suggestions)) || [];
                const arr = Array.isArray(list) ? list : [];
                if (arr.length) {
                    const kws = (this.keywordList || []).slice();
                    if (this.seo && this.seo.focus_keyword) kws.unshift(this.seo.focus_keyword);
                    this.autoLinkSuggestions = arr.map((s, i) => {
                        let anchor = s.suggested_anchor || s.keyword || s.anchor || '';
                        if (!anchor && kws.length) anchor = kws[i % kws.length];
                        if (!anchor) anchor = this.pickRandomAnchor() || s.target_title || s.title || '';
                        return Object.assign({}, s, {
                            suggested_anchor: String(anchor).trim(),
                            keyword: String(anchor).trim(),
                            target_url: s.target_url || s.permalink || s.url || '',
                            target_title: s.target_title || s.title || '',
                            selected: false
                        });
                    });
                    this.showToast(this.autoLinkSuggestions.length + ' پیشنهاد لینک بر اساس کلیدواژه دریافت شد', 'success');
                } else {
                    this.autoLinkSuggestions = [];
                    this.showToast('پیشنهاد جدیدی یافت نشد.', 'info');
                }
            } catch (e) {
                this.showToast('خطا در بارگیری لینک‌ها', 'error');
            } finally {
                this.linkBusy = false;
            }
        },
        startAiLinkMode() {
            this.linkSourceMode = 'ai';
            this.postResults = [];
            this.loadSmartLinkSuggestions();
        },
        startManualLinkSearch() {
            this.linkSourceMode = 'manual';
            this.autoLinkSuggestions = [];
            return this.runInternalSearch();
        },
        allAiSelected() {
            const list = this.autoLinkSuggestions || [];
            return list.length > 0 && list.every((s) => s.selected);
        },
        toggleAllAi(on) {
            (this.autoLinkSuggestions || []).forEach((s) => { s.selected = !!on; });
        },
        allManualSelected() {
            const list = this.postResults || [];
            return list.length > 0 && list.every((p) => p.accepted);
        },
        toggleAllManual(on) {
            (this.postResults || []).forEach((p) => {
                p.accepted = !!on;
                p.rejected = !on;
            });
        },
        confirmSelectedAiLinks() {
            const selected = (this.autoLinkSuggestions || []).filter((s) => s.selected);
            if (!selected.length) {
                this.showToast('حداقل یک مورد را انتخاب کنید', 'warn');
                return;
            }
            let n = 0;
            selected.forEach((s) => {
                const before = n;
                this.insertSuggestedLink(s);
                n++;
            });
            this.showToast(n + ' لینک اعمال شد', 'success');
            this.autoLinkSuggestions = (this.autoLinkSuggestions || []).filter((s) => !s.selected);
            if (!this.autoLinkSuggestions.length) this.linkSourceMode = null;
            this.loadLinks();
        },
        confirmSelectedManualLinks() {
            const selected = (this.postResults || []).filter((p) => p.accepted && !p.rejected);
            if (!selected.length) {
                this.showToast('حداقل یک مورد را انتخاب کنید', 'warn');
                return;
            }
            let n = 0;
            selected.forEach((p, i) => {
                const kws = (this.keywordList || []).slice();
                if (this.seo && this.seo.focus_keyword && !kws.includes(this.seo.focus_keyword)) kws.unshift(this.seo.focus_keyword);
                const anchor = (this.linkAnchor && this.linkAnchor.trim())
                    || (kws.length ? kws[i % kws.length] : '')
                    || this.pickRandomAnchor()
                    || p.title;
                if (this.insertLinkIntoEditor(String(anchor).trim(), p.permalink, p.title, false, true)) n++;
            });
            if (n) {
                this.showToast(n + ' لینک اعمال شد', 'success');
                this.postResults = (this.postResults || []).filter((p) => !p.accepted);
                if (!this.postResults.length) this.linkSourceMode = null;
                this.loadLinks();
                this.analyze(false);
            } else {
                this.showToast('درج لینک ممکن نشد', 'error');
            }
        },
        pickRandomAnchor() {
            const pool = [];
            if (this.seo && this.seo.focus_keyword) pool.push(String(this.seo.focus_keyword).trim());
            (this.keywordList || []).forEach((k) => {
                const s = String(k || '').trim();
                if (s && !pool.includes(s)) pool.push(s);
            });
            if (this.linkAnchor && String(this.linkAnchor).trim()) {
                const a = String(this.linkAnchor).trim();
                if (!pool.includes(a)) pool.push(a);
            }
            if (!pool.length) return '';
            return pool[Math.floor(Math.random() * pool.length)];
        },
        insertSuggestedLink(s) {
            if (!s) return;
            const anchor = s.suggested_anchor || s.keyword || this.pickRandomAnchor() || s.target_title || s.title || '';
            const url = s.permalink || s.target_url || '';
            if (!anchor || !url) {
                this.showToast('انکر یا آدرس نامعتبر است', 'error');
                return;
            }
            const ok = this.insertLinkIntoEditor(anchor, url, s.target_title || s.title || '', false, true);
            if (ok) {
                this.showToast('لینک «' + anchor + '» اضافه شد', 'success');
                this.loadLinks();
                this.analyze(false);
            } else {
                // fallback append
                const linkTag = '<a href="' + url + '" target="_blank" rel="noopener"><strong>' + anchor + '</strong></a>';
                if (typeof this.insertContentIntoEditor === 'function') {
                    this.insertContentIntoEditor(linkTag, 'append');
                    this.showToast('لینک «' + anchor + '» اضافه شد', 'success');
                    this.loadLinks();
                } else {
                    this.showToast('درج لینک ممکن نشد', 'error');
                }
            }
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
            this.aiModalOpen = false;
            this.aiOpen = false;
            this.aiTask = '';
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
                    if (data.sub_scores && typeof data.sub_scores === 'object') {
                        this.analysis.sub_scores = data.sub_scores;
                    }
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
        addExternalLink() {
            return this.insertExternalLink();
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
            // Open modal UI only — do not lock editor without explicit task
            this.aiOpen = true;
            this.aiModalOpen = true;
            this.aiReviewOpen = false;
            this.aiPhase = 'setup';
            this.aiBusy = false;
            this.aiLoading = false;
            this.aiProgress = 0;
            this.aiStepText = '';
            if (!this.aiImageProvider) {
                const list = (window.bankaiEditorSeo && bankaiEditorSeo.imageProviders) || [];
                this.aiImageProvider = (window.bankaiEditorSeo && bankaiEditorSeo.defaultImageProvider) || (list[0] && list[0].id) || '';
            }
            if (!this.aiProvider) {
                const list = (window.bankaiEditorSeo && bankaiEditorSeo.aiProviders) || [];
                this.aiProvider = (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || (list[0] && list[0].id) || '';
            }
            this.resetAiChecklist();
            if (action) {
                this.selectAiTask(action);
            } else {
                this.aiTask = '';
                this.aiModalTitle = 'دستیار AI سئو';
            }
        },

        closeAiModal() {
            if (this.aiBusy || this.aiLoading) return;
            this.aiModalOpen = false;
            this.aiOpen = false;
            this.aiTask = '';
            this.aiStepText = '';
            this.aiProgress = 0;
            this.aiPhase = 'setup';
            this.aiRunChecklist = [];
        },

        selectAiTask(task) {
            const titles = {
                meta: 'تولید عنوان و توضیحات متا',
                title: 'تولید عنوان سئو',
                description: 'تولید توضیحات متا',
                keywords: 'استخراج کلمات کلیدی',
                links: 'لینک‌سازی داخلی هوشمند',
                images: 'تولید Alt تصاویر',
                faq: 'تولید FAQ برای AEO',
                takeaways: 'نکات کلیدی GEO',
                rewrite: 'بازنویسی مقاله با AI',
                auto_all: 'بهینه‌سازی یک‌پارچه مقاله',
                cover: 'تولید کاور مقاله با پرامپت واقع‌گرایانه',
                schema: 'تولید اسکیما',
                aeo: 'بهبود AEO با هوش مصنوعی',
                geo: 'بهبود GEO با هوش مصنوعی',
                faq: 'بهبود AEO با هوش مصنوعی',
                takeaways: 'بهبود GEO با هوش مصنوعی'
            };
            this.aiTask = task || '';
            this.aiModalTitle = titles[task] || 'دستیار AI سئو';
            this.aiOpen = true;
            this.aiModalOpen = true;
            this.aiPhase = 'setup';
            this.aiProgress = 0;
            this.aiStepText = '';
            this.aiReviewOpen = false;
            this.resetAiChecklist();
            this.aiRunChecklist = this.plannedChecklist().map((x) => Object.assign({}, x, { status: 'pending' }));
        },


        async fixAeoWithAi() {
            // Only failed items to avoid meta regeneration duplication
            this.aiFailedItemsOnly = true;
            this.activeTab = 'aeo_geo';
            this.selectAiTask('faq');
            this.aiModalTitle = 'بهبود AEO با هوش مصنوعی';
            this.aiOpen = true;
            this.aiModalOpen = true;
            this.aiPhase = 'setup';
        },

        async fixGeoWithAi() {
            this.activeTab = 'aeo_geo';
            this.selectAiTask('takeaways');
            this.aiModalTitle = 'بهبود GEO با هوش مصنوعی';
            this.aiOpen = true;
            this.aiModalOpen = true;
            this.aiPhase = 'setup';
        },


        aiPhaseIndex() {
            return { setup: 0, running: 1, review: 2 }[this.aiPhase] || 0;
        },
        aiPhaseLabel() {
            return ({ setup: 'مرحله ۱ — تنظیم و انتخاب AI', running: 'مرحله ۲ — اجرای فرایند', review: 'مرحله ۳ — بازبینی و تایید' })[this.aiPhase] || '';
        },
        aiTaskBlurb() {
            const map = {
                auto_all: 'تمام گام‌های سئو، کلیدواژه، لینک، تصویر و اسکیما به‌صورت یک‌پارچه اجرا می‌شود.',
                faq: 'ساختار پاسخ‌محور و FAQ برای Featured Snippet و موتورهای پاسخ‌گو ساخته می‌شود.',
                aeo: 'ساختار پاسخ‌محور و FAQ برای Featured Snippet و موتورهای پاسخ‌گو ساخته می‌شود.',
                takeaways: 'چکیده و نکات کلیدی برای نقل‌قول توسط ChatGPT و موتورهای مولد تولید می‌شود.',
                geo: 'چکیده و نکات کلیدی برای نقل‌قول توسط ChatGPT و موتورهای مولد تولید می‌شود.',
                meta: 'عنوان سئو و متا دیسکریپشن استاندارد و جذاب تولید می‌شود.',
                keywords: 'کلمه کلیدی اصلی و فرعی استخراج و با ثابت‌های سایت ادغام می‌شود.',
                links: 'پیشنهاد لینک داخلی هوشمند بر اساس محتوا و کلیدواژه‌ها.',
                images: 'متن جایگزین (Alt) تصاویر مقاله تولید می‌شود.',
                rewrite: 'بازنویسی هوشمند محتوا با حفظ ساختار و سئو.',
                schema: 'داده ساختاریافته JSON-LD تولید می‌شود.'
            };
            return map[this.aiTask] || 'دستیار هوش مصنوعی سئوی بنکای آماده اجراست.';
        },
        plannedChecklist() {
            const t = this.aiTask || '';
            if (t === 'auto_all') {
                return [
                    { id: 'analyze', label: 'تحلیل محتوا و ساختار مقاله' },
                    { id: 'meta', label: 'تولید عنوان سئو و متا دیسکریپشن' },
                    { id: 'keywords', label: 'استخراج کلمات کلیدی' },
                    { id: 'links', label: 'پیشنهاد لینک داخلی' },
                    { id: 'images', label: 'سئوی تصاویر و Alt' },
                    { id: 'optimize_images', label: 'فشرده‌سازی تصاویر' },
                    { id: 'schema', label: 'تولید اسکیما' },
                    { id: 'terms', label: 'دسته و برچسب' }
                ];
            }
            if (t === 'faq' || t === 'aeo') {
                return [
                    { id: 'analyze', label: 'تحلیل آمادگی پاسخ‌محور (AEO)' },
                    { id: 'faq_gen', label: 'تولید سوالات متداول ساختاریافته' },
                    { id: 'faq_schema', label: 'آماده‌سازی اسکیما FAQ' },
                    { id: 'review', label: 'آماده‌سازی پیش‌نمایش برای تایید' }
                ];
            }
            if (t === 'takeaways' || t === 'geo') {
                return [
                    { id: 'analyze', label: 'تحلیل آمادگی موتورهای مولد (GEO)' },
                    { id: 'geo_sum', label: 'تولید خلاصه نکات کلیدی' },
                    { id: 'geo_meta', label: 'بهینه‌سازی متا برای نقل‌قول AI' },
                    { id: 'review', label: 'آماده‌سازی پیش‌نمایش برای تایید' }
                ];
            }
            if (t === 'meta' || t === 'title' || t === 'description') {
                return [
                    { id: 'analyze', label: 'تحلیل عنوان و توضیحات فعلی' },
                    { id: 'meta', label: 'تولید عنوان و متا با AI' },
                    { id: 'review', label: 'آماده‌سازی برای تایید' }
                ];
            }
            if (t === 'keywords') {
                return [
                    { id: 'analyze', label: 'تحلیل محتوا' },
                    { id: 'keywords', label: 'استخراج و ادغام کلیدواژه‌ها' },
                    { id: 'review', label: 'آماده‌سازی برای تایید' }
                ];
            }
            if (t === 'links') {
                return [
                    { id: 'analyze', label: 'تحلیل لینک‌های موجود' },
                    { id: 'links', label: 'رتبه‌بندی پیشنهادهای داخلی' },
                    { id: 'review', label: 'آماده‌سازی برای تایید' }
                ];
            }
            if (t === 'images') {
                return [
                    { id: 'analyze', label: 'اسکن تصاویر بدون Alt' },
                    { id: 'images', label: 'تولید Alt با AI' },
                    { id: 'review', label: 'آماده‌سازی برای تایید' }
                ];
            }
            if (t === 'rewrite') {
                return [
                    { id: 'analyze', label: 'تحلیل ساختار مقاله' },
                    { id: 'rewrite', label: 'بازنویسی هوشمند' },
                    { id: 'review', label: 'آماده‌سازی برای تایید' }
                ];
            }
            return (this.aiChecklist || []).map((x) => ({ id: x.id, label: x.label }));
        },
        activeRunChecklist() {
            return (this.aiRunChecklist && this.aiRunChecklist.length)
                ? this.aiRunChecklist
                : this.plannedChecklist().map((x) => Object.assign({}, x, { status: 'pending' }));
        },
        aiStatusFa(st) {
            return ({ pending: 'در صف', loading: 'در حال اجرا', success: 'انجام شد', done: 'انجام شد', error: 'خطا' })[st] || 'در صف';
        },
        setRunCheck(id, status) {
            if (!this.aiRunChecklist) this.aiRunChecklist = [];
            const item = this.aiRunChecklist.find((i) => i.id === id);
            if (item) item.status = status;
            if (typeof this.setChecklistStatus === 'function') {
                try { this.setChecklistStatus(id, status === 'success' ? 'success' : (status === 'loading' ? 'loading' : status)); } catch (e) {}
            }
        },
        async startAiWorkflow() {
            // Fix: ensure data population and only active AI
            if (!this.aiProvider) {
                this.aiProvider = (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || 'gemini';
            }
            this.aiPhase = 'running';
            this.aiProgress = 0;
            this.aiStepText = 'شروع فرایند…';
            this.aiRunChecklist = this.plannedChecklist().map((x) => Object.assign({}, x, { status: 'pending' }));
            this.aiBusy = true;
            this.aiLoading = true;
            try {
                await this.runAiGeneration();
            } finally {
                // Force review with data if available
                if (this.aiDraft && (this.aiDraft.title || this.aiDraft.description || this.aiDraft.rewrite)) {
                    this.aiPhase = 'review';
                } else if (!this.aiBusy) {
                    this.aiPhase = 'review';
                }
            }
        },
        async rewriteAiWorkflow() {
            this.aiPhase = 'setup';
            this.aiProgress = 0;
            this.aiStepText = '';
            this.aiReviewOpen = false;
            this.aiDraft = Object.assign({}, this.aiDraft, {
                title: '', description: '', rewrite: '', faqText: '', faqHtml: '', geoText: '', takeaways: ''
            });
            // user clicks start again
        },
                scatterAndInsertSmartLinks(links) {
            if (!Array.isArray(links) || !links.length) return;
            let content = this.getEditorRawContent() || '';
            if (!content.trim()) return;

            const parts = content.split(/(?=<p\b|<div\b|<h[2-6]\b|\n\n)/i);
            if (!parts || !parts.length) return;

            const bodyIndices = [];
            parts.forEach((p, idx) => {
                if (idx > 0 && p.length > 20) {
                    bodyIndices.push(idx);
                }
            });

            if (!bodyIndices.length) {
                parts.forEach((_, idx) => bodyIndices.push(idx));
            }

            const step = Math.max(1, Math.floor(bodyIndices.length / links.length));

            links.forEach((link, lIdx) => {
                const targetIdx = bodyIndices[Math.min(lIdx * step, bodyIndices.length - 1)];
                const keyword = link.keyword || link.anchor || link.title || '';
                const url = link.url || link.permalink || '';

                if (keyword && url && parts[targetIdx]) {
                    if (!parts[targetIdx].includes(url)) {
                        const regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'i');
                        if (regex.test(parts[targetIdx])) {
                            parts[targetIdx] = parts[targetIdx].replace(regex, '<a href="' + url + '" title="' + keyword + '">$1</a>');
                        } else {
                            parts[targetIdx] += ' <a href="' + url + '" title="' + keyword + '">' + keyword + '</a>';
                        }
                    }
                }
            });

            const newContent = parts.join('');
            if (newContent !== content && typeof this.setEditorRawContent === 'function') {
                this.setEditorRawContent(newContent);
            }
        },

        applyAiReview() {
            try {
                if (this.aiDraft.title) this.seo.seo_title = this.aiDraft.title;
                if (this.aiDraft.description) this.seo.description = this.aiDraft.description;
                if (this.aiDraft.focus_keyword) {
                    this.seo.focus_keyword = this.aiDraft.focus_keyword;
                    if (!this.keywordList.includes(this.aiDraft.focus_keyword)) {
                        this.keywordList.unshift(this.aiDraft.focus_keyword);
                    }
                }
                if (Array.isArray(this.aiDraft.keywords) && this.aiDraft.keywords.length) {
                    this.aiDraft.keywords.forEach((k) => {
                        if (k && !this.keywordList.includes(k)) this.keywordList.push(k);
                    });
                    this.pushKeywordsToSeo && this.pushKeywordsToSeo();
                }
                if (this.aiDraft.rewrite && typeof this.setEditorRawContent === 'function') {
                    this.setEditorRawContent(this.aiDraft.rewrite);
                }
                if (this.aiDraft.faqHtml || this.aiDraft.faqText) {
                    const html = this.aiDraft.faqHtml || this.aiDraft.faqText;
                    if (typeof this.insertContentIntoEditor === 'function') {
                        this.insertContentIntoEditor(html, 'append');
                    } else if (typeof this.setEditorRawContent === 'function') {
                        const cur = this.getEditorRawContent() || '';
                        this.setEditorRawContent(cur + '\n' + html);
                    }
                }
                if (this.aiDraft.geoHtml) {
                    if (typeof this.insertContentIntoEditor === 'function') {
                        this.insertContentIntoEditor(this.aiDraft.geoHtml, 'prepend');
                    }
                } else if (this.aiDraft.geoText || this.aiDraft.takeaways) {
                    const geo = this.aiDraft.geoText || this.aiDraft.takeaways;
                    if (typeof this.insertContentIntoEditor === 'function') {
                        this.insertContentIntoEditor('<div class="bankai-summary"><h2>خلاصه نکات کلیدی</h2><p>' + geo + '</p></div>', 'prepend');
                    }
                }
                // Insert & scatter smart internal links in content body
                if (Array.isArray(this.aiDraft.smartLinks) && this.aiDraft.smartLinks.length) {
                    this.scatterAndInsertSmartLinks(this.aiDraft.smartLinks);
                }

                if (Array.isArray(this.aiDraft.altTexts)) {
                    this.aiDraft.altTexts.forEach((a) => {
                        if (a && (a.suggestedAlt || a.alt) && typeof this.updateImageAlt === 'function') {
                            this.updateImageAlt(a, a.suggestedAlt || a.alt);
                        }
                    });
                }
                if (typeof this.onFieldChange === 'function') this.onFieldChange();
                if (typeof this.analyze === 'function') this.analyze(false);
                this.showToast('تغییرات AI اعمال شد', 'success');
                this.closeAiModal();
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در اعمال', 'error');
            }
        },

        async runAiGeneration() {
            let task = this.aiTask || this.aiActiveAction || '';
            if (!task) {
                task = 'meta';
                this.selectAiTask('meta');
            }
            if (this.aiPhase === 'setup') this.aiPhase = 'running';
            if (!this.aiRunChecklist || !this.aiRunChecklist.length) {
                this.aiRunChecklist = this.plannedChecklist().map((x) => Object.assign({}, x, { status: 'pending' }));
            }
            // Map UI tasks to existing pipeline actions
            const map = {
                title: 'meta',
                description: 'meta',
                takeaways: 'takeaways',
                geo: 'takeaways',
                faq: 'faq',
                aeo: 'faq'
            };
            const action = map[task] || task;
            this.aiBusy = true;
            this.aiLoading = true;
            try {
                if (action === 'faq' || action === 'aeo') {
                    await this.runAeoWorkflow();
                } else if (action === 'takeaways' || action === 'geo') {
                    await this.runGeoWorkflow();
                } else if (typeof this.runAiAction === 'function') {
                    await this.runAiAction(action);
                }
                this.aiPhase = 'review';
                this.aiReviewOpen = true;
            } catch (e) {
                console.warn('[Bankai SEO] AI generation error', e);
                this.showToast((e && e.message) || 'خطا در تولید AI', 'error');
                this.aiPhase = 'setup';
                this.aiStepText = (e && e.message) || 'خطا — دوباره تلاش کنید';
            } finally {
                this.aiBusy = false;
                this.aiLoading = false;
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
            if (this.aiRunChecklist && this.aiRunChecklist.length) {
                const r = this.aiRunChecklist.find((i) => i.id === id);
                if (r) r.status = status;
            }
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
            body.append('provider', this.aiProvider || (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || ctx.provider || '');
            body.append('seo_title', ctx.seo_title || (this.seo && this.seo.seo_title) || '');
            body.append('description', ctx.description || (this.seo && this.seo.description) || '');

            Object.keys(extraData).forEach((k) => {
                if (k === '_timeoutMs') return;
                body.append(k, typeof extraData[k] === 'object' ? JSON.stringify(extraData[k]) : extraData[k]);
            });

            // Heavy tasks need longer client timeout (default was 60s → abort noise)
            const heavy = ['rewrite', 'smart_internal_links', 'auto_all', 'aeo_faq', 'geo_summary', 'faq'].includes(String(task));
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
            const style = this.aiImgStyle || 'photorealistic';

            const styleGuide = {
                photorealistic: 'Ultra-realistic photography, natural skin tones if people appear, real-world lighting, shallow depth of field, shot on 35mm lens, high dynamic range, no CGI look, authentic textures.',
                editorial: 'Editorial magazine photography, clean composition, soft natural light, professional documentary feel, muted color grade.',
                cinematic: 'Cinematic still frame, anamorphic bokeh, dramatic but natural lighting, film grain subtle, wide emotional scene.',
                illustration: 'High-end digital illustration, clean shapes, soft gradients, modern editorial illustration, not cartoonish.'
            }[style] || style;

            const common = [
                'This image is for a WordPress article. Match the article topic accurately.',
                'Article title: ' + (title || 'Untitled'),
                kw ? ('Focus topic / keyword: ' + kw) : '',
                'Visual style: ' + styleGuide,
                'Composition: clear subject, balanced framing, suitable for web hero or in-article placement.',
                'STRICT: no text, no letters, no watermark, no logo, no UI, no borders, no collages.'
            ].filter(Boolean);

            if (purpose === 'cover') {
                const story = firstTwo || this.getPlainContent().slice(0, 400);
                return [
                    'Create a photorealistic editorial COVER image for a blog article (16:9 feel).',
                    'Story context: ' + (story || title || 'general topic'),
                    ...common,
                    'Prefer real environments and objects related to the topic; avoid abstract blobs.'
                ].join('\n');
            }

            const sectionText = (sectionCtx && (sectionCtx.context || sectionCtx.text || '')) || '';
            return [
                'Create a photorealistic INLINE illustration photo for an article section.',
                'Surrounding content: ' + (sectionText || firstTwo || title || 'article topic'),
                ...common,
                'Make the scene look photographed in real life, not AI-plastic.'
            ].join('\n');
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
            if (options.style) {
                body.append('style', options.style);
            } else if (this.aiImgStyle) {
                body.append('style', this.aiImgStyle);
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


        async runAeoWorkflow() {
            const ctx = this.getEditorContext();
            if (!ctx.title && !ctx.content && !ctx.rawHtml) {
                throw new Error('محتوای مقاله خالی است — ابتدا متن را ذخیره یا بنویسید');
            }
            this.setRunCheck('analyze', 'loading');
            this.aiProgress = 8;
            this.aiStepText = 'تحلیل آمادگی پاسخ‌محور (AEO)…';
            await new Promise((r) => setTimeout(r, 250));
            this.setRunCheck('analyze', 'success');
            this.aiProgress = 20;

            this.setRunCheck('faq_gen', 'loading');
            this.aiStepText = 'ارسال پرامپت FAQ به هوش مصنوعی…';
            let faqHtml = '';
            let faqText = '';
            try {
                const res = await this.callSeoAi('aeo_faq', ctx, { _timeoutMs: 180000 });
                faqHtml = (res && (res.faq_html || res.content || res.html || res.text)) || '';
                // plain text fallback for review textarea
                if (faqHtml) {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = faqHtml;
                    faqText = (tmp.innerText || tmp.textContent || '').trim();
                }
            } catch (e) {
                this.setRunCheck('faq_gen', 'error');
                this.aiStepText = (e && e.message) || 'خطا در تولید FAQ';
                throw e;
            }
            if (!faqHtml) {
                this.setRunCheck('faq_gen', 'error');
                throw new Error('پاسخ خالی از هوش مصنوعی برای FAQ');
            }
            this.aiDraft.faqHtml = faqHtml;
            this.aiDraft.faqText = faqText;
            this.setRunCheck('faq_gen', 'success');
            this.aiProgress = 65;

            this.setRunCheck('faq_schema', 'loading');
            this.aiStepText = 'آماده‌سازی اسکیما FAQ…';
            try {
                this.schemaType = 'FAQPage';
                // extract Q/A pairs into faqItems if possible
                const qs = [];
                try {
                    const doc = new DOMParser().parseFromString(faqHtml, 'text/html');
                    const hs = doc.querySelectorAll('h3');
                    hs.forEach((h) => {
                        const q = (h.textContent || '').trim();
                        let a = '';
                        let n = h.nextElementSibling;
                        while (n && n.tagName && n.tagName.toLowerCase() !== 'h3') {
                            if (n.tagName.toLowerCase() === 'p') a += (n.textContent || '') + ' ';
                            n = n.nextElementSibling;
                        }
                        if (q) qs.push({ q, a: a.trim() });
                    });
                } catch (eParse) {}
                if (qs.length) this.faqItems = qs;
                if (typeof this.onSchemaTypeChange === 'function') this.onSchemaTypeChange();
                this.setRunCheck('faq_schema', 'success');
            } catch (e) {
                this.setRunCheck('faq_schema', 'error');
            }
            this.aiProgress = 88;

            this.setRunCheck('review', 'loading');
            this.aiStepText = 'آماده‌سازی پیش‌نمایش…';
            this.setRunCheck('review', 'success');
            this.aiProgress = 100;
            this.aiStepText = 'AEO آماده بازبینی است';
            this.aiReviewOpen = true;
            this.aiPhase = 'review';
            this.showToast('FAQ با AI تولید شد — بازبینی کنید', 'success');
        },

        async runGeoWorkflow() {
            const ctx = this.getEditorContext();
            if (!ctx.title && !ctx.content && !ctx.rawHtml) {
                throw new Error('محتوای مقاله خالی است — ابتدا متن را ذخیره یا بنویسید');
            }
            this.setRunCheck('analyze', 'loading');
            this.aiProgress = 8;
            this.aiStepText = 'تحلیل آمادگی موتورهای مولد (GEO)…';
            await new Promise((r) => setTimeout(r, 250));
            this.setRunCheck('analyze', 'success');
            this.aiProgress = 18;

            this.setRunCheck('geo_sum', 'loading');
            this.aiStepText = 'ارسال پرامپت خلاصه به هوش مصنوعی…';
            let summaryHtml = '';
            let geoText = '';
            try {
                const res = await this.callSeoAi('geo_summary', ctx, { _timeoutMs: 180000 });
                summaryHtml = (res && (res.summary_html || res.content || res.html || res.text)) || '';
                if (summaryHtml) {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = summaryHtml;
                    geoText = (tmp.innerText || tmp.textContent || '').trim();
                }
            } catch (e) {
                this.setRunCheck('geo_sum', 'error');
                this.aiStepText = (e && e.message) || 'خطا در تولید خلاصه GEO';
                throw e;
            }
            if (!summaryHtml) {
                this.setRunCheck('geo_sum', 'error');
                throw new Error('پاسخ خالی از هوش مصنوعی برای GEO');
            }
            this.aiDraft.geoText = geoText;
            this.aiDraft.takeaways = geoText;
            this.aiDraft.faqHtml = this.aiDraft.faqHtml || ''; // keep separate
            // store html for apply
            this.aiDraft.geoHtml = summaryHtml;
            this.setRunCheck('geo_sum', 'success');
            this.aiProgress = 55;

            this.setRunCheck('geo_meta', 'loading');
            this.aiStepText = 'بهینه‌سازی متا برای نقل‌قول AI…';
            try {
                const metaRes = await this.callSeoAi('meta', ctx);
                if (metaRes) {
                    this.aiDraft.title = metaRes.seo_title || metaRes.title || this.aiDraft.title || '';
                    this.aiDraft.description = metaRes.description || metaRes.meta_description || this.aiDraft.description || '';
                }
                this.setRunCheck('geo_meta', 'success');
            } catch (e) {
                this.setRunCheck('geo_meta', 'error');
            }
            this.aiProgress = 88;

            this.setRunCheck('review', 'loading');
            this.aiStepText = 'آماده‌سازی پیش‌نمایش…';
            this.setRunCheck('review', 'success');
            this.aiProgress = 100;
            this.aiStepText = 'GEO آماده بازبینی است';
            this.aiReviewOpen = true;
            this.aiPhase = 'review';
            this.showToast('خلاصه GEO با AI تولید شد — بازبینی کنید', 'success');
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
                    this.aiPhase = 'review';
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
            // Live real-time re-analysis & score refresh
            this.analyze(false).then(() => {
                if (typeof this.emitScore === 'function') {
                    this.emitScore(this.analysis.score || 0);
                }
            });
            // Refresh media list so new Alts appear in cards
            if (this.activeTab === 'media' || (this.postImages && this.postImages.length)) {
                try { this.scanPostImages(); } catch (e) {}
            }
        },



        checkPillar(check) {
            if (!check) return 'seo';
            if (check.pillar) return check.pillar;
            const g = (check.group || '').toLowerCase();
            if (g.indexOf('aeo') >= 0) return 'aeo';
            if (g.indexOf('geo') >= 0) return 'geo';
            const key = String(check.key || check.id || '').toLowerCase();
            if (key.indexOf('aeo') >= 0 || key.indexOf('faq') >= 0) return 'aeo';
            if (key.indexOf('geo') >= 0 || key.indexOf('schema') >= 0) return 'geo';
            return 'seo';
        },

        toggleGroup(gkey) {
            this.openGroups[gkey] = !this.openGroups[gkey];
        },

        seoGroupKeys() {
            return ['basic', 'title', 'content', 'advanced'];
        },

        groupPassed(group) {
            if (!group || !group.items || !group.items.length) return true;
            return group.items.every((c) => c.passed || c.status === 'pass');
        },

        groupPassedCount(group) {
            if (!group || !group.items) return 0;
            return group.items.filter((c) => c.passed || c.status === 'pass').length;
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


        
        insertKeyTakeawaysBox() {
            const title = this.seo.seo_title || this.seo.title || 'مقاله';
            const kw = this.seo.focus_keyword || 'موضوع اصلی';
            const boxHtml = `<div class="bankai-key-takeaways" style="background:#f8fafc;border-right:4px solid #0969da;padding:12px 16px;border-radius:8px;margin-bottom:16px;">` +
                `<strong style="display:block;font-size:13px;color:#0f172a;margin-bottom:6px;">📌 نکات کلیدی و خلاصه (Key Takeaways):</strong>` +
                `<ul style="margin:8px 0 0;padding-inline-start:20px;font-size:12px;color:#334155;">` +
                `<li>بررسی جامع ${title} و پاسخ به پرسش‌های اصلی کاربران.</li>` +
                `<li>نکات عملی و کاربردی درباره ${kw}.</li>` +
                `</ul></div>\n\n`;

            let html = this.getEditorRawContent() || '';
            this.setEditorRawContent(boxHtml + html);
            this.showToast('باکس نکات کلیدی در ابتدای متن درج شد', 'success');
            this.analyze(false);
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
            this.linkSourceMode = 'manual';
            this.autoLinkSuggestions = [];
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

        scrollToLinkInContent(link) {
            if (!link || !link.href) return;
            const href = String(link.href);
            try {
                // Gutenberg
                if (window.wp && wp.data && wp.data.select('core/block-editor')) {
                    const blocks = wp.data.select('core/block-editor').getBlocks();
                    const find = (list) => {
                        for (const b of list || []) {
                            const html = (b.attributes && (b.attributes.content || b.attributes.url || '')) || '';
                            if (String(html).indexOf(href) >= 0 || (b.attributes && b.attributes.url === href)) {
                                wp.data.dispatch('core/block-editor').selectBlock(b.clientId);
                                return true;
                            }
                            if (b.innerBlocks && find(b.innerBlocks)) return true;
                        }
                        return false;
                    };
                    if (find(blocks)) {
                        this.showToast('لینک در ویرایشگر مشخص شد', 'info');
                        return;
                    }
                }
                // Classic / iframe fallback
                const frames = document.querySelectorAll('iframe');
                for (const f of frames) {
                    try {
                        const doc = f.contentDocument;
                        if (!doc) continue;
                        const a = doc.querySelector('a[href="' + href.replace(/"/g, '\\"') + '"]');
                        if (a) {
                            a.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            a.style.outline = '2px solid #3b82f6';
                            setTimeout(() => { a.style.outline = ''; }, 2000);
                            this.showToast('لینک در محتوا مشخص شد', 'info');
                            return;
                        }
                    } catch (e) {}
                }
                this.showToast('محل لینک در ویرایشگر پیدا نشد', 'warn');
            } catch (e) {
                this.showToast('خطا در یافتن لینک', 'error');
            }
        },

        insertCtaBlocks() {
            const text = String(this.ctaText || '').trim();
            let url = String(this.ctaUrl || '').trim();
            if (!text || !url) {
                this.showToast('متن و لینک CTA را وارد کنید', 'warn');
                return;
            }
            if (/^(javascript:|data:|vbscript:)/i.test(url)) {
                this.showToast('آدرس نامعتبر است', 'error');
                return;
            }
            if (!/^(https?:\/\/|\/|#)/i.test(url) && !url.startsWith('//')) {
                this.showToast('آدرس باید http/https باشد', 'error');
                return;
            }
            let html = this.getEditorRawContent();
            if (!html) {
                this.showToast('محتوای مقاله خالی است', 'error');
                return;
            }
            try {
                const doc = new DOMParser().parseFromString('<div id="bk-root">' + html + '</div>', 'text/html');
                const root = doc.getElementById('bk-root') || doc.body;
                const paras = Array.from(root.querySelectorAll('p'));
                if (paras.length < 2) {
                    this.showToast('حداقل ۲ پاراگراف برای درج CTA لازم است', 'warn');
                    return;
                }
                const step = 3;
                const placed = [];
                const align = this.ctaAlign || 'right';
                const bold = !!this.ctaBold;
                for (let i = step - 1; i < paras.length; i += step) {
                    const p = paras[i];
                    if (!p || !p.parentNode) continue;
                    const wrap = doc.createElement('p');
                    wrap.setAttribute('class', 'bk-cta-block');
                    wrap.setAttribute('data-bk-cta', '1');
                    wrap.style.textAlign = align === 'left' ? 'left' : (align === 'center' ? 'center' : 'right');
                    const a = doc.createElement('a');
                    a.setAttribute('href', url);
                    a.setAttribute('target', '_blank');
                    a.setAttribute('rel', 'noopener');
                    a.setAttribute('class', 'bk-cta-link');
                    if (bold) {
                        const strong = doc.createElement('strong');
                        strong.textContent = text;
                        a.appendChild(strong);
                    } else {
                        a.textContent = text;
                    }
                    wrap.appendChild(a);
                    if (p.nextSibling) {
                        p.parentNode.insertBefore(wrap, p.nextSibling);
                    } else {
                        p.parentNode.appendChild(wrap);
                    }
                    const id = 'cta_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6);
                    wrap.setAttribute('data-bk-cta-id', id);
                    placed.push({
                        id,
                        text,
                        url,
                        afterParagraph: i + 1,
                        align,
                        bold,
                        selected: false
                    });
                }
                this.setEditorRawContent(root.innerHTML);
                this.ctaPlacements = (this.ctaPlacements || []).concat(placed);
                this.showToast(placed.length + ' CTA در مقاله درج شد', 'success');
                this.loadLinks();
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در درج CTA', 'error');
            }
        },

        removeCtaPlacement(c) {
            if (!c || !c.id) return;
            let html = this.getEditorRawContent();
            if (!html) return;
            try {
                const doc = new DOMParser().parseFromString('<div id="bk-root">' + html + '</div>', 'text/html');
                const root = doc.getElementById('bk-root') || doc.body;
                const el = root.querySelector('[data-bk-cta-id="' + c.id + '"]');
                if (el && el.parentNode) el.parentNode.removeChild(el);
                this.setEditorRawContent(root.innerHTML);
                this.ctaPlacements = (this.ctaPlacements || []).filter((x) => x.id !== c.id);
                this.showToast('CTA حذف شد', 'success');
                this.loadLinks();
            } catch (e) {
                this.showToast('خطا در حذف CTA', 'error');
            }
        },

        removeSelectedCtas() {
            const selected = (this.ctaPlacements || []).filter((c) => c.selected);
            if (!selected.length) return;
            selected.forEach((c) => this.removeCtaPlacement(c));
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
                const href = targetHref;
                const internal = (this.linkData.internal || []).filter((x) => x && x.href !== href);
                const external = (this.linkData.external || []).filter((x) => x && x.href !== href);
                this.linkData = {
                    internal,
                    external,
                    counts: { internal: internal.length, external: external.length }
                };
                this.showToast('لینک از متن حذف شد', 'success');
                setTimeout(() => { if (typeof this.loadLinks === 'function') this.loadLinks(); }, 400);
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
                this.postImages = (this.postImages || []).map((img) => this.normalizeImageRow(img));
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

        async startAiImageFlow() {
            this.aiImgStep = 2;
            this.aiImgProgress = 5;
            this.aiImgStepText = 'آماده‌سازی پرامپت و ترجمه به انگلیسی…';
            this.aiImgResultUrl = '';
            this.aiImgResultId = 0;
            this.aiImgResultHtml = '';
            this.aiBusy = true;
            this.optBusy = true;
            try {
                const type = this.aiImgType || 'cover';
                const style = this.aiImgStyle || 'photorealistic';
                const styleLabels = {
                    photorealistic: 'photorealistic high detail',
                    minimalist: 'minimalist modern corporate',
                    vector: 'vector illustration',
                    render_3d: '3D digital render',
                    cinematic: 'cinematic studio lighting',
                    infographic: 'infographic educational'
                };
                const ctx = this.getEditorContext();
                const rawPrompt = [
                    ctx.title || 'Article image',
                    ctx.focus_keyword ? ('Keyword: ' + ctx.focus_keyword) : '',
                    'Style: ' + (styleLabels[style] || style)
                ].filter(Boolean).join(' — ');

                this.aiImgProgress = 25;
                this.aiImgStepText = 'ترجمه محتوا و ساخت پرامپت انگلیسی…';

                // callImageAi will hit server which now translates Persian → English
                this.aiImgProgress = 45;
                this.aiImgStepText = 'ارسال به موتور تصویر و تولید…';
                const imgResult = await this.callImageAi(type, {
                    prompt: rawPrompt,
                    section: null,
                    style: style
                });

                this.aiImgProgress = 90;
                this.aiImgStepText = 'ذخیره در کتابخانه رسانه…';

                if (!imgResult || (!imgResult.url && !imgResult.src && !imgResult.attachment_id)) {
                    throw new Error('تصویر توسط هوش مصنوعی تولید نشد');
                }
                this.aiImgResultUrl = imgResult.url || imgResult.src || '';
                this.aiImgResultId = imgResult.attachment_id || 0;
                this.aiImgResultHtml = imgResult.html || '';
                this.aiImgProgress = 100;
                this.aiImgStepText = 'آماده شد';
                this.aiImgStep = 3;
                this.showToast('تصویر با موفقیت تولید شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در تولید تصویر', 'error');
                this.aiImgStep = 1;
            } finally {
                this.aiBusy = false;
                this.optBusy = false;
            }
        },

        async confirmAiImage() {
            if (!this.aiImgResultUrl) return;
            try {
                if (this.aiImgType === 'cover' && this.aiImgResultId) {
                    // already set as thumbnail on server when purpose=cover
                    this.showToast('کاور تایید و به عنوان تصویر شاخص ست شد', 'success');
                } else if (this.aiImgResultHtml) {
                    this.insertContentIntoEditor(this.aiImgResultHtml, 'append');
                    this.showToast('تصویر درون محتوا درج شد', 'success');
                }
                await this.scanPostImages();
                this.aiImgStep = 1;
                this.aiImgResultUrl = '';
                this.aiImgResultId = 0;
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در تایید', 'error');
            }
        },

        async compressLastAiImage() {
            if (!this.aiImgResultId) return;
            this.showToast('فشرده‌سازی تصویر…', 'info');
            try {
                this.optBusy = true;
                // reuse existing optimize pipeline if available
                if (typeof this.runOptimizeImages === 'function') {
                    await this.runOptimizeImages();
                }
                this.showToast('فشرده‌سازی انجام شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در فشرده‌سازی', 'error');
            } finally {
                this.optBusy = false;
            }
        },

        async watermarkLastAiImage() {
            if (!this.aiImgResultId) return;
            this.showToast('اعمال واترمارک…', 'info');
            try {
                this.optBusy = true;
                if (typeof this.watermarkSingleImage === 'function') {
                    await this.watermarkSingleImage({ attachment_id: this.aiImgResultId, src: this.aiImgResultUrl });
                }
                this.showToast('واترمارک اعمال شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در واترمارک', 'error');
            } finally {
                this.optBusy = false;
            }
        },

        async generateAiArticleImage() {
            // legacy entry → route to new 3-step flow
            return this.startAiImageFlow();
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

                calcSavingsPercent(img) {
            if (!img) return null;
            const b = Number(img.bytes_before || 0);
            const a = Number(img.bytes_after || 0);
            if (b > 0 && a > 0 && b !== a) {
                return Math.round(((b - a) / b) * 100);
            }
            return null;
        },

        autoInitSchemaPreview() {
            // Automatically reset/rebuild and refresh JSON-LD schema preview on tab open
            this.rebuildSchema();
            if (typeof this.syncSchemaFromForms === 'function') {
                this.syncSchemaFromForms(true);
            }
        },

        isSchemaValid() {
            if (!this.seo || !this.seo.schema || !this.seo.schema.trim()) return false;
            try {
                JSON.parse(this.seo.schema);
                return true;
            } catch (e) {
                return false;
            }
        },

        normalizeImageRow(img) {
            if (!img) return {};
            return {
                id: img.id || 'img_' + Math.random().toString(36).substr(2, 9),
                src: img.src || img.original_src || '',
                original_src: img.original_src || img.src || '',
                alt: img.alt || img.new_alt || '',
                new_alt: img.new_alt || img.alt || '',
                format: img.format || '',
                size: img.size || 0,
                watermarked: !!img.watermarked,
                attachment_id: img.attachment_id || 0,
                is_cover: !!img.is_cover,
                converted: !!img.converted,
                resized: !!img.resized
            };
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
                // Ensure postImages is updated first before compression
                await this.scanPostImages();
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
                this.postImages = this.mergeCoverImage(this.postImages).map((img) => this.normalizeImageRow(img));
                this.showToast(data.message || 'بهینه‌سازی انجام شد', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا', 'error');
            } finally {
                this.optBusy = false;
            }
        },

        applyBulkAltText() {
            const alt = String(this.bulkAltText || '').trim();
            if (!alt) {
                this.showToast('متن Alt را وارد کنید', 'warn');
                return;
            }
            const list = this.postImages || [];
            list.forEach((img) => {
                img.new_alt = alt;
                img.alt = alt;
                this.updateImageAlt(img, alt);
            });
            this.showToast('Alt برای ' + list.length + ' تصویر اعمال شد', 'success');
            this.showBulkAltPanel = false;
        },

        async generateAllAltsWithAi() {
            const list = this.postImages || [];
            if (!list.length) return;
            this.aiBusy = true;
            let n = 0;
            try {
                for (const img of list) {
                    try {
                        await this.generateSingleImageAlt(img);
                        n++;
                    } catch (e) {}
                }
                this.showToast(n + ' Alt با AI تولید شد', 'success');
                this.showBulkAltPanel = false;
            } finally {
                this.aiBusy = false;
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

        async removeAllWatermarks() {
            const list = (this.postImages || []).filter((img) => img && (img.watermarked || img.attachment_id || img.src));
            if (!list.length) {
                this.showToast('تصویری برای حذف واترمارک یافت نشد', 'info');
                return;
            }
            this.optBusy = true;
            try {
                for (const img of list) {
                    await this.removeImageWatermark(img);
                }
                this.showToast('واترمارک‌ها حذف شدند', 'success');
            } catch (e) {
                this.showToast((e && e.message) || 'خطا در حذف واترمارک', 'error');
            } finally {
                this.optBusy = false;
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
