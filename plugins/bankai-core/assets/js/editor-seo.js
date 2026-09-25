document.addEventListener('alpine:init', () => {

    Alpine.data('bankaiSeoSidebar', (postId) => ({

        postId: postId || (window.bankaiEditorSeo && bankaiEditorSeo.postId) || 0,

        activeTab: 'seo',
        loading: false,
        saving: false,
        serpMobile: false,

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
            robots: { index: true, follow: true },
            og_title: '',
            og_description: '',
            og_image: '',
            x_title: '',
            x_description: '',
            x_image: '',
            schema: '',
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

        // Links state
        linkData: { internal: [], external: [], counts: { internal: 0, external: 0 } },
        pendingAiLinks: [],
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
            quality: 82,
            defaultAlt: ''
        },
        internalReport: { show: false, message: '', type: 'info' },
        externalReport: { show: false, message: '', type: 'info' },
        internalMatches: [],
        externalMatches: [],
        selectedInternalPost: null,
        contentMatchesInternal: [],
        contentMatchesExternal: [],

        // AI Modal & Automation State
        aiOpen: false,
        aiProvider: (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || '',
        aiLoading: false,
        aiProgress: 0,
        aiStepText: '',
        aiActiveAction: '', // 'rewrite', 'meta', 'links', 'images', 'auto_all'
        aiChecklist: [
            { id: 'analyze', label: 'تحلیل محتوا و ساختار مقاله', status: 'pending', icon: 'analytics' },
            { id: 'meta', label: 'تولید خودکار عنوان سئو و متا دیسکریپشن', status: 'pending', icon: 'title' },
            { id: 'keywords', label: 'استخراج کلمات کلیدی + ادغام ثابت‌ها', status: 'pending', icon: 'key' },
            { id: 'links', label: 'لینک‌سازی هوشمند داخلی با مقالات مرتبط', status: 'pending', icon: 'link' },
            { id: 'images', label: 'سئوی تصاویر و تولید Alt Text خودکار', status: 'pending', icon: 'image' }
        ],
        aiDraft: {
            title: '',
            description: '',
            focus_keyword: '',
            keywords: [],
            rewrite: '',
            altTexts: [],   // [ { id, src, currentAlt, suggestedAlt, context } ]
            smartLinks: []  // [ { keyword, target_post_id, target_title, target_url, reason } ]
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
            if (data.robots && typeof data.robots === 'object') {
                this.seo.robots = {
                    index: data.robots.index !== false && data.robots.index !== '0' && data.robots.index !== 0,
                    follow: data.robots.follow !== false && data.robots.follow !== '0' && data.robots.follow !== 0,
                };
            }
            // Keywords
            let list = [];
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
                        this.seo.score = data.score || 0;
                    }
                    if (data.groups && typeof data.groups === 'object') {
                        this.analysis.groups = data.groups;
                    }
                }
            } catch (e) {
                console.warn('[Bankai SEO] analyze error', e);
            }
        },

                async loadLinks() {
            if (!this.postId) return;
            try {
                const res = await fetch(this.restBase() + 'seo/links/' + this.postId, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: this.restHeaders(false)
                });
                const json = await res.json().catch(() => ({}));
                const data = (json && json.success && json.data) ? json.data
                    : (json && json.data) ? json.data
                    : (json && (json.internal || json.external)) ? json
                    : null;
                if (data) {
                    this.linkData = {
                        internal: Array.isArray(data.internal) ? data.internal : [],
                        external: Array.isArray(data.external) ? data.external : [],
                        counts: data.counts || {
                            internal: Array.isArray(data.internal) ? data.internal.length : 0,
                            external: Array.isArray(data.external) ? data.external.length : 0,
                        }
                    };
                }
            } catch (e) {
                console.warn('[Bankai SEO] load links error', e);
            }
        },



        async smartSuggestInternalLinks() {
            this.linkBusy = true;
            try {
                const cfg = window.bankaiEditorSeo || {};
                const ctx = this.getEditorContext();
                const res = await fetch(this.restBase() + 'seo/suggest-links', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({
                        post_id: this.postId,
                        keywords: this.keywordList,
                        content: ctx.content
                    })
                });
                const json = await res.json();
                if (json.success && json.data && Array.isArray(json.data.suggestions)) {
                    json.data.suggestions.forEach((s) => {
                        const exists = this.pendingAiLinks.some((p) => p.target_url === s.permalink);
                        if (!exists) {
                            this.pendingAiLinks.push({
                                id: 'sugg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                                keyword: s.suggested_anchor || s.focus_keyword || s.title,
                                target_post_id: s.id,
                                target_title: s.title,
                                target_url: s.permalink,
                                reason: s.in_content ? 'کلمه کلیدی در متن مقاله یافت شد' : 'مقاله مرتبط سئوشده',
                                status: 'pending'
                            });
                        }
                    });
                    this.linksSubTab = 'pending';
                    this.showToast(json.data.suggestions.length + ' پیشنهاد لینک هوشمند اضافه شد', 'success');
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

        acceptPendingLink(link) {
            if (!link) return;
            this.insertLinkIntoEditor(link.keyword, link.target_url, link.target_title);
            this.pendingAiLinks = this.pendingAiLinks.filter((p) => p.id !== link.id);
            this.showToast('لینک پیشنهادی پذیرفته و در متن درج شد', 'success');
            this.loadLinks();
        },

        rejectPendingLink(link) {
            if (!link) return;
            this.pendingAiLinks = this.pendingAiLinks.filter((p) => p.id !== link.id);
            this.showToast('پیشنهاد رد شد', 'info');
        },

        approveAllPendingLinks() {
            if (!this.pendingAiLinks.length) return;
            const count = this.pendingAiLinks.length;
            this.pendingAiLinks.forEach((link) => {
                this.insertLinkIntoEditor(link.keyword, link.target_url, link.target_title);
            });
            this.pendingAiLinks = [];
            this.showToast(count + ' لینک پیشنهادی پذیرفته و در متن مقاله درج شدند', 'success');
            this.loadLinks();
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

        insertLinkIntoEditor(keyword, url, title = '', nofollow = false, bold = true) {
            let html = this.getEditorRawContent();
            if (!html || !keyword || !url) return false;
            const rel = nofollow ? ' rel="nofollow noopener"' : ' rel="noopener"';
            const esc = String(keyword).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const re = new RegExp('(?<!<a[^>]*>)(' + esc + ')(?![^<]*</a>)', 'i');
            if (!re.test(html)) {
                return false;
            }
            const inner = bold ? '<strong>$1</strong>' : '$1';
            const titleAttr = String(title || keyword).replace(/"/g, '&quot;');
            html = html.replace(re, '<a href="' + url + '" title="' + titleAttr + '"' + rel + '>' + inner + '</a>');
            this.setEditorRawContent(html);
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
                provider: this.aiProvider || (window.bankaiEditorSeo && bankaiEditorSeo.defaultAiProvider) || ''
            };
        },

        async callSeoAi(task, ctx, extraData = {}) {
            const cfg = window.bankaiEditorSeo || {};
            const body = new FormData();
            body.append('action', 'bankai_ai_seo_task');
            body.append('nonce', cfg.adminNonce || cfg.nonce || '');
            body.append('task', task);
            body.append('title', ctx.title || '');
            body.append('content', ctx.content || '');
            body.append('focus_keyword', ctx.focus_keyword || '');
            body.append('locale', ctx.locale || 'fa_IR');
            body.append('provider', ctx.provider || '');

            Object.keys(extraData).forEach((k) => {
                body.append(k, typeof extraData[k] === 'object' ? JSON.stringify(extraData[k]) : extraData[k]);
            });

            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timer = controller ? setTimeout(() => { try { controller.abort(); } catch (e) {} }, 60000) : null;

            try {
                const r = await fetch(cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: body,
                    signal: controller ? controller.signal : undefined
                });
                const json = await r.json();
                if (timer) clearTimeout(timer);
                if (!json || !json.success) {
                    const msg = (json && json.data && json.data.message) || (json && json.message) || 'خطا در هوش مصنوعی';
                    throw new Error(msg);
                }
                return json.data || json;
            } catch (e) {
                if (timer) clearTimeout(timer);
                throw e;
            }
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
                    this.aiProgress = 20;
                    this.aiStepText = 'در حال تولید عنوان سئو و متا دیسکریپشن...';
                    this.setChecklistStatus('meta', 'loading');

                    const [tRes, dRes] = await Promise.all([
                        this.callSeoAi('meta_title', ctx),
                        this.callSeoAi('meta_description', ctx)
                    ]);
                    this.aiDraft.title = tRes.seo_title || tRes.text || '';
                    this.aiDraft.description = dRes.description || dRes.text || '';
                    this.setChecklistStatus('meta', 'success');

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
                    this.aiProgress = 40;
                    this.aiStepText = 'در حال تحلیل مقاله و یافتن بهترین مقالات مرتبط سایت...';
                    this.setChecklistStatus('links', 'loading');

                    let siteArts = [];
                    try {
                        const cfg = window.bankaiEditorSeo || {};
                        const searchRes = await fetch(this.restBase() + 'seo/search-posts', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
                            body: JSON.stringify({ s: ctx.focus_keyword || ctx.title || 'a', post_id: this.postId })
                        });
                        const sJson = await searchRes.json();
                        if (sJson.success && sJson.data) {
                            siteArts = (sJson.data.posts || sJson.data || []).map((p) => ({
                                id: p.id,
                                title: p.title,
                                permalink: p.permalink
                            }));
                        }
                    } catch (e) {}

                    const linkRes = await this.callSeoAi('smart_internal_links', ctx, { articles: siteArts });
                    const newLinks = linkRes.links || [];
                    this.aiDraft.smartLinks = newLinks;

                    newLinks.forEach((l) => {
                        const exists = this.pendingAiLinks.some((p) => p.target_url === l.target_url);
                        if (!exists) {
                            this.pendingAiLinks.push({
                                id: 'ai_link_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                                keyword: l.keyword,
                                target_post_id: l.target_post_id,
                                target_title: l.target_title,
                                target_url: l.target_url,
                                reason: l.reason,
                                status: 'pending'
                            });
                        }
                    });

                    this.aiProgress = 100;
                    this.setChecklistStatus('links', 'success');
                    this.aiReviewOpen = true;
                    this.showToast(newLinks.length + ' لینک پیشنهادی به سایدباکس اضافه شد', 'success');

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

                } else if (actionKey === 'auto_all') {
                    this.resetAiChecklist();

                    // Step 1: Content Analysis
                    this.aiProgress = 15;
                    this.aiStepText = 'گام ۱/۴: تحلیل کامل محتوا و ساختار مقاله...';
                    this.setChecklistStatus('analyze', 'loading');
                    await new Promise((r) => setTimeout(r, 400));
                    this.setChecklistStatus('analyze', 'success');

                    // Step 2: Auto Meta & Keywords
                    this.aiProgress = 35;
                    this.aiStepText = 'گام ۲/۴: تولید عنوان سئو، متادیسکریپشن و کلیدواژه‌ها...';
                    this.setChecklistStatus('meta', 'loading');
                    this.setChecklistStatus('keywords', 'loading');

                    const [tRes, dRes] = await Promise.all([
                        this.callSeoAi('meta_title', ctx),
                        this.callSeoAi('meta_description', ctx)
                    ]);
                    this.aiDraft.title = tRes.seo_title || tRes.text || '';
                    this.aiDraft.description = dRes.description || dRes.text || '';

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

                    // Step 3: Smart Internal Links
                    this.aiProgress = 65;
                    this.aiStepText = 'گام ۳/۴: بررسی و تولید پیشنهادهای لینک هوشمند...';
                    this.setChecklistStatus('links', 'loading');

                    let siteArts = [];
                    try {
                        const cfg = window.bankaiEditorSeo || {};
                        const searchRes = await fetch(this.restBase() + 'seo/search-posts', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
                            body: JSON.stringify({ s: fk || ctx.title || 'a', post_id: this.postId })
                        });
                        const sJson = await searchRes.json();
                        if (sJson.success && sJson.data) {
                            siteArts = (sJson.data.posts || sJson.data || []).map((p) => ({
                                id: p.id,
                                title: p.title,
                                permalink: p.permalink
                            }));
                        }
                    } catch (e) {}

                    const linkRes = await this.callSeoAi('smart_internal_links', ctx, { articles: siteArts });
                    const newLinks = linkRes.links || [];
                    this.aiDraft.smartLinks = newLinks;
                    newLinks.forEach((l) => {
                        const exists = this.pendingAiLinks.some((p) => p.target_url === l.target_url);
                        if (!exists) {
                            this.pendingAiLinks.push({
                                id: 'ai_link_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                                keyword: l.keyword,
                                target_post_id: l.target_post_id,
                                target_title: l.target_title,
                                target_url: l.target_url,
                                reason: l.reason,
                                status: 'pending'
                            });
                        }
                    });
                    this.setChecklistStatus('links', 'success');

                    // Step 4: Images & Alt Text
                    this.aiProgress = 85;
                    this.aiStepText = 'گام ۴/۴: اسکن و تولید متن جایگزین برای تصاویر...';
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

                    this.aiProgress = 100;
                    this.aiStepText = 'عملیات یک‌پارچه با موفقیت انجام شد!';
                    this.aiReviewOpen = true;
                    this.showToast('تمام بخش‌های سئو با هوش مصنوعی تحلیل و آماده تایید شد', 'success');
                }

            } catch (e) {
                const msg = (e && e.message) ? e.message : 'خطا در اجرای فرآیند هوش مصنوعی';
                this.showToast(msg, 'error');
                this.aiStepText = 'خطا: ' + msg;
                if (this.aiActiveAction) {
                    this.setChecklistStatus(this.aiActiveAction, 'error');
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
                    }
                });
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
            const type = this.schemaType || 'Article';
            const title = this.seo.seo_title || this.seo.title || '';
            const desc = this.seo.description || '';
            const url = this.seo.permalink || '';
            const schema = {
                '@context': 'https://schema.org',
                '@type': type,
                'headline': title,
                'description': desc,
                'url': url
            };
            if (type === 'FAQPage') {
                schema.mainEntity = [];
            }
            if (type === 'HowTo') {
                schema.step = [];
            }
            this.seo.schema = JSON.stringify(schema, null, 2);
            this.onFieldChange();
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
                this.postImages = local;
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
                    this.postImages = json.data.images;
                }
            } catch (e) {
                console.warn(e);
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
                    id: 'rep_' + i,
                    src: row.src || row.original_src,
                    alt: row.new_alt || row.alt || '',
                    format: row.format_after || row.format_before,
                    size: row.size_after || row.size_before,
                    watermarked: !!row.watermarked,
                    attachment_id: row.attachment_id || 0,
                    converted: row.converted,
                    resized: row.resized,
                    actions: row.actions
                }));
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

        showToast(message, type = 'info') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3200);
            if (window.bankaiAdmin && typeof window.bankaiAdmin.showToast === 'function') {
                window.bankaiAdmin.showToast(message, type);
            }
        }
    }));
});
