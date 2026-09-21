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

        analysis: {
            score: 0,
            passed: 0,
            total: 0,
            checks: [],
            groups: {},
            stats: {}
        },

        openGroups: { basic: true, advanced: true, title: false, content: false },

        linkData: { internal: [], external: [], counts: { internal: 0, external: 0 } },
        linkAnchor: '',
        postResults: [],
        extAnchor: '',
        extUrl: '',
        extNofollow: false,

        linkBusy: false,
        internalReport: { show: false, message: '', type: 'info' },
        externalReport: { show: false, message: '', type: 'info' },
        internalMatches: [],
        externalMatches: [],
        selectedInternalPost: null,
        postSearchMeta: { scanned: 0, found: 0 },

        schemaType: 'Article',

        aiOpen: false,
        aiTab: 'title',
        aiLoading: false,
        aiDraft: { title: '', description: '', keywords: '', rewrite: '' },

        toast: { show: false, message: '', type: 'info' },
        _saveTimer: null,
        _analyzeTimer: null,

        // ---- Score circle math (fast) ----
        get scoreCircumference() {
            return 2 * Math.PI * 14; // ~88
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
            // Rough px estimate ~ title length * 8.5
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
            if (n >= 30 && n <= 60) return '#1f883d';
            if (n > 60) return '#cf222e';
            return '#d0d7de';
        },

        get descBarPct() {
            return Math.min(100, ((this.seo.description || '').length / 160) * 100);
        },

        get descBarColor() {
            const n = (this.seo.description || '').length;
            if (n >= 120 && n <= 160) return '#1f883d';
            if (n > 160) return '#cf222e';
            return '#d0d7de';
        },

        // ---- Init ----
        async init() {
            await this.load();
        },

        async load() {
            if (!this.postId) return;
            this.loading = true;
            try {
                const res = await wp.apiFetch({ path: `/bankai/v1/seo/${this.postId}` });
                if (res && res.success && res.data) {
                    this.seo = { ...this.seo, ...res.data };
                    this.syncKeywordList();
                    if (res.data.schema) {
                        try {
                            const parsed = typeof res.data.schema === 'string'
                                ? JSON.parse(res.data.schema)
                                : res.data.schema;
                            if (parsed && parsed['@type']) {
                                this.schemaType = parsed['@type'];
                            }
                        } catch (e) { /* ignore */ }
                    }
                }
                await this.analyze(false);
            } catch (err) {
                this.showToast(err.message || 'خطا در بارگذاری SEO', 'error');
            } finally {
                this.loading = false;
            }
        },

        syncKeywordList() {
            const list = [];
            if (this.seo.focus_keyword) {
                list.push(this.seo.focus_keyword);
            }
            if (Array.isArray(this.seo.keywords)) {
                this.seo.keywords.forEach((k) => {
                    if (k && !list.includes(k)) list.push(k);
                });
            }
            this.keywordList = list;
        },

        pushKeywordsToSeo() {
            this.seo.focus_keyword = this.keywordList[0] || '';
            this.seo.keywords = this.keywordList.slice(1);
        },

        addKeywordFromInput() {
            const v = (this.kwDraft || '').trim();
            if (!v) return;
            if (!this.keywordList.includes(v)) {
                this.keywordList.push(v);
                this.pushKeywordsToSeo();
                this.onFieldChange();
            }
            this.kwDraft = '';
        },

        removeKeyword(i) {
            this.keywordList.splice(i, 1);
            this.pushKeywordsToSeo();
            this.onFieldChange();
        },

        editKeyword(i) {
            const current = this.keywordList[i];
            const next = window.prompt('ویرایش کلیدواژه', current);
            if (next !== null && next.trim()) {
                this.keywordList[i] = next.trim();
                this.pushKeywordsToSeo();
                this.onFieldChange();
            }
        },

        // ---- Analyze / Save ----
        onFieldChange() {
            clearTimeout(this._analyzeTimer);
            this._analyzeTimer = setTimeout(() => this.analyze(false), 200);
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this.save(true), 700);
        },

        async analyze(showToast) {
            if (!this.postId) return;
            this.loading = true;
            try {
                const body = {
                    content: this.getEditorHtml ? this.getEditorHtml() : (this.seo.content || ''),
                    seo_title: this.seo.seo_title,
                    description: this.seo.description,
                    focus_keyword: this.seo.focus_keyword || (this.keywordList[0] || '')
                };
                const res = await wp.apiFetch({
                    path: `/bankai/v1/seo/analyze/${this.postId}`,
                    method: 'POST',
                    data: body
                });
                if (res && res.success && res.data) {
                    this.analysis = res.data;
                    if (window.bankaiGutenbergSeo) {
                        window.bankaiGutenbergSeo.score = res.data.score;
                    }
                    window.dispatchEvent(new CustomEvent('bankai-seo-score', {
                        detail: { score: res.data.score, color: this.scoreColor }
                    }));
                }
                if (showToast) {
                    this.showToast('تحلیل به‌روز شد', 'success');
                }
            } catch (err) {
                console.error('Bankai analyze', err);
            } finally {
                this.loading = false;
            }
        },

        async save(silent) {
            if (!this.postId || this.saving) return;
            this.saving = true;
            this.pushKeywordsToSeo();
            try {
                const payload = {
                    seo_title: this.seo.seo_title,
                    description: this.seo.description,
                    focus_keyword: this.seo.focus_keyword,
                    keywords: this.seo.keywords,
                    canonical: this.seo.canonical,
                    robots: this.seo.robots,
                    og_title: this.seo.og_title,
                    og_description: this.seo.og_description,
                    og_image: this.seo.og_image,
                    x_title: this.seo.x_title,
                    x_description: this.seo.x_description,
                    x_image: this.seo.x_image,
                    schema: this.seo.schema
                };
                const res = await wp.apiFetch({
                    path: `/bankai/v1/seo/${this.postId}`,
                    method: 'POST',
                    data: payload
                });
                if (res && res.success) {
                    if (res.data) {
                        this.seo = { ...this.seo, ...res.data };
                    }
                    if (res.analysis) {
                        this.analysis = res.analysis;
                    }
                    if (!silent) {
                        this.showToast('ذخیره شد', 'success');
                    }
                }
            } catch (err) {
                this.showToast(err.message || 'خطا در ذخیره', 'error');
            } finally {
                this.saving = false;
            }
        },

        // ---- Groups ----
        toggleGroup(key) {
            this.openGroups[key] = !this.openGroups[key];
        },

        groupPassed(group) {
            if (!group || !group.items) return true;
            return group.items.every((c) => c.passed);
        },

        groupPassedCount(group) {
            if (!group || !group.items) return 0;
            return group.items.filter((c) => c.passed).length;
        },

        // ---- Links ----
        async loadLinks() {
            if (!this.postId) return;
            try {
                const res = await wp.apiFetch({ path: `/bankai/v1/seo/links/${this.postId}` });
                if (res && res.success) {
                    this.linkData = res.data;
                }
            } catch (e) {
                console.error(e);
            }
        },

        getEditorHtml() {
            try {
                if (window.wp && wp.data && wp.data.select) {
                    const blocks = wp.data.select('core/block-editor')?.getBlocks?.();
                    if (blocks && window.wp.blocks?.serialize) {
                        return wp.blocks.serialize(blocks);
                    }
                }
            } catch (e) { /* ignore */ }
            if (window.tinymce && tinymce.activeEditor) {
                return tinymce.activeEditor.getContent({ format: 'html' }) || '';
            }
            const ta = document.getElementById('content');
            if (ta) return ta.value || '';
            return this.seo.content || '';
        },

        setEditorHtml(html) {
            try {
                if (window.wp && wp.data && wp.blocks && wp.data.dispatch) {
                    const blocks = wp.blocks.parse(html);
                    wp.data.dispatch('core/block-editor').resetBlocks(blocks);
                    return true;
                }
            } catch (e) {
                console.warn('setEditorHtml blocks', e);
            }
            if (window.tinymce && tinymce.activeEditor) {
                tinymce.activeEditor.setContent(html);
                return true;
            }
            const ta = document.getElementById('content');
            if (ta) {
                ta.value = html;
                return true;
            }
            return false;
        },

        /**
         * Find plain-text occurrences that are NOT already inside an <a>.
         */
        findUnlinkedMatches(html, phrase) {
            if (!phrase || phrase.length < 2) return [];
            const matches = [];
            const lowerPhrase = phrase.toLowerCase();
            // Work on a simplified scan: strip script/style, track if inside <a>
            let i = 0;
            const src = html;
            let inTag = false;
            let inAnchor = false;
            let textBuf = '';
            let textStartInHtml = -1;
            const map = []; // map plain index -> html index

            const flushText = () => {
                textBuf = '';
                textStartInHtml = -1;
            };

            // Build plain text with map, skipping tags and existing anchors' content still counts but mark inAnchor
            let plain = '';
            let htmlIdx = 0;
            while (htmlIdx < src.length) {
                if (src[htmlIdx] === '<') {
                    const close = src.indexOf('>', htmlIdx);
                    if (close === -1) break;
                    const tag = src.slice(htmlIdx, close + 1);
                    const tagLower = tag.toLowerCase();
                    if (/^<a\b/.test(tagLower)) inAnchor = true;
                    if (/^<\/a\s*>/.test(tagLower)) inAnchor = false;
                    htmlIdx = close + 1;
                    continue;
                }
                // text char
                if (!inAnchor) {
                    map[plain.length] = htmlIdx;
                    plain += src[htmlIdx];
                }
                htmlIdx++;
            }

            const plainLower = plain.toLowerCase();
            let pos = 0;
            while (true) {
                const found = plainLower.indexOf(lowerPhrase, pos);
                if (found === -1) break;
                const htmlPos = map[found];
                if (htmlPos === undefined) {
                    pos = found + 1;
                    continue;
                }
                const before = plain.slice(Math.max(0, found - 28), found);
                const hit = plain.slice(found, found + phrase.length);
                const after = plain.slice(found + phrase.length, found + phrase.length + 28);
                const contextHtml = this.escapeHtml(before)
                    + '<mark>' + this.escapeHtml(hit) + '</mark>'
                    + this.escapeHtml(after);
                matches.push({
                    index: found,
                    htmlIndex: htmlPos,
                    length: phrase.length,
                    phrase: phrase,
                    contextHtml: contextHtml,
                    selected: true
                });
                pos = found + phrase.length;
            }
            return matches;
        },

        escapeHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        /**
         * Replace selected matches from end to start so indices stay valid.
         */
        wrapMatchesInHtml(html, matches, href, relAttr, bold) {
            const selected = matches
                .filter((m) => m.selected)
                .slice()
                .sort((a, b) => b.htmlIndex - a.htmlIndex);

            let out = html;
            const phrase = matches[0]?.phrase || '';
            for (const m of selected) {
                // Safer: replace nth plain occurrence in unlinked text by scanning again near htmlIndex
                const open = bold
                    ? `<a href="${href}"${relAttr}><strong>`
                    : `<a href="${href}"${relAttr}>`;
                const close = bold ? `</strong></a>` : `</a>`;

                // Find exact phrase at htmlIndex region - may need to skip tags inside phrase (rare)
                const slice = out.slice(m.htmlIndex, m.htmlIndex + m.length + 40);
                // If plain contiguous
                if (out.slice(m.htmlIndex, m.htmlIndex + m.length) === phrase
                    || out.slice(m.htmlIndex, m.htmlIndex + m.length).toLowerCase() === phrase.toLowerCase()) {
                    const actual = out.slice(m.htmlIndex, m.htmlIndex + m.length);
                    out = out.slice(0, m.htmlIndex) + open + actual + close + out.slice(m.htmlIndex + m.length);
                } else {
                    // fallback case-insensitive search from htmlIndex
                    const region = out.slice(m.htmlIndex);
                    const re = new RegExp(this.escapeRegExp(phrase), 'i');
                    const mm = region.match(re);
                    if (mm && mm.index !== undefined && mm.index < 80) {
                        const at = m.htmlIndex + mm.index;
                        const actual = out.slice(at, at + mm[0].length);
                        out = out.slice(0, at) + open + actual + close + out.slice(at + mm[0].length);
                    }
                }
            }
            return out;
        },

        escapeRegExp(s) {
            return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },

        async runInternalSearch() {
            const q = (this.linkAnchor || this.seo.focus_keyword || '').trim();
            if (q.length < 2) {
                this.internalReport = {
                    show: true,
                    type: 'warn',
                    message: 'حداقل ۲ کاراکتر برای جستجو وارد کنید.'
                };
                return;
            }
            this.linkBusy = true;
            this.internalMatches = [];
            this.selectedInternalPost = null;
            this.postResults = [];
            try {
                const res = await wp.apiFetch({
                    path: `/bankai/v1/seo/search-posts?q=${encodeURIComponent(q)}`
                });
                const list = (res && res.success && res.data) ? res.data.filter((p) => p.id !== this.postId) : [];
                this.postResults = list;
                this.postSearchMeta = { scanned: list.length, found: list.length };
                this.internalReport = {
                    show: true,
                    type: list.length ? 'success' : 'warn',
                    message: list.length
                        ? `${list.length} مقاله مرتبط پیدا شد. یک مقاله را انتخاب کنید تا محل‌های «${q}» در متن این نوشته پیدا شود.`
                        : `هیچ مقالهٔ منتشرشده‌ای برای «${q}» پیدا نشد.`
                };
            } catch (e) {
                this.internalReport = {
                    show: true,
                    type: 'error',
                    message: e.message || 'خطا در جستجوی مقالات'
                };
            } finally {
                this.linkBusy = false;
            }
        },

        // keep alias
        async searchPosts() {
            return this.runInternalSearch();
        },

        scanInternalMatches(post) {
            const phrase = (this.linkAnchor || this.seo.focus_keyword || post.title || '').trim();
            if (!phrase) {
                this.internalReport = { show: true, type: 'warn', message: 'انکر تکست خالی است.' };
                return;
            }
            this.selectedInternalPost = post;
            const html = this.getEditorHtml();
            const matches = this.findUnlinkedMatches(html, phrase);
            this.internalMatches = matches;
            this.internalReport = {
                show: true,
                type: matches.length ? 'success' : 'warn',
                message: matches.length
                    ? `${matches.length} مورد از «${phrase}» در متن (خارج از لینک‌های موجود) پیدا شد. موارد را تأیید یا لغو کنید.`
                    : `عبارت «${phrase}» در متن پیدا نشد یا از قبل لینک شده است.`
            };
            if (!matches.length) {
                this.showToast(this.internalReport.message, 'info');
            }
        },

        applyInternalLinks() {
            if (!this.selectedInternalPost) return;
            const selected = this.internalMatches.filter((m) => m.selected);
            if (!selected.length) {
                this.showToast('هیچ موردی انتخاب نشده', 'error');
                return;
            }
            const html = this.getEditorHtml();
            const href = this.selectedInternalPost.permalink;
            const next = this.wrapMatchesInHtml(html, this.internalMatches, href, '', true);
            if (this.setEditorHtml(next)) {
                this.showToast(`${selected.length} لینک داخلی اعمال شد`, 'success');
                this.internalMatches = [];
                this.internalReport = {
                    show: true,
                    type: 'success',
                    message: `${selected.length} لینک داخلی به «${this.selectedInternalPost.title}» اضافه شد.`
                };
                this.onFieldChange();
                setTimeout(() => this.loadLinks(), 400);
            } else {
                this.showToast('نتوانستیم ادیتور را به‌روز کنیم', 'error');
            }
        },

        scanExternalMatches() {
            const phrase = (this.extAnchor || '').trim();
            const url = (this.extUrl || '').trim();
            if (!phrase || phrase.length < 2) {
                this.externalReport = { show: true, type: 'warn', message: 'عبارت انکر را وارد کنید.' };
                return;
            }
            if (!url || !/^https?:\/\//i.test(url)) {
                this.externalReport = { show: true, type: 'warn', message: 'یک URL معتبر با http(s) وارد کنید.' };
                return;
            }
            const html = this.getEditorHtml();
            const matches = this.findUnlinkedMatches(html, phrase);
            this.externalMatches = matches;
            this.externalReport = {
                show: true,
                type: matches.length ? 'success' : 'warn',
                message: matches.length
                    ? `${matches.length} مورد از «${phrase}» در متن پیدا شد. انتخاب کنید کدام‌ها به ${url} لینک شوند.`
                    : `عبارت «${phrase}» در محتوای فعلی پیدا نشد. لینکی به انتها اضافه نمی‌شود — فقط متن موجود قابل تبدیل است.`
            };
            this.showToast(this.externalReport.message, matches.length ? 'success' : 'info');
        },

        applyExternalLinks() {
            const selected = this.externalMatches.filter((m) => m.selected);
            if (!selected.length) {
                this.showToast('هیچ موردی انتخاب نشده', 'error');
                return;
            }
            const url = (this.extUrl || '').trim();
            const rel = this.extNofollow
                ? ' rel="nofollow noopener" target="_blank"'
                : ' rel="noopener" target="_blank"';
            const html = this.getEditorHtml();
            const next = this.wrapMatchesInHtml(html, this.externalMatches, url, rel, true);
            if (this.setEditorHtml(next)) {
                this.showToast(`${selected.length} لینک خارجی اعمال شد`, 'success');
                this.externalReport = {
                    show: true,
                    type: 'success',
                    message: `${selected.length} مورد به لینک خارجی تبدیل شد.`
                };
                this.externalMatches = [];
                this.onFieldChange();
                setTimeout(() => this.loadLinks(), 400);
            } else {
                this.showToast('به‌روزرسانی ادیتور ناموفق بود', 'error');
            }
        },

        // deprecated append-style insert kept as no-op safety
        insertInternalLink(post) {
            this.scanInternalMatches(post);
        },

        insertExternalLink() {
            this.scanExternalMatches();
        },

        insertIntoEditor(html, fallbackText) {
            // legacy — prefer wrapMatchesInHtml
            try {
                if (window.wp && wp.data && wp.data.dispatch) {
                    const { insertBlocks } = wp.data.dispatch('core/block-editor');
                    const { createBlock } = wp.blocks;
                    if (insertBlocks && createBlock) {
                        insertBlocks(createBlock('core/paragraph', { content: html }));
                        return;
                    }
                }
            } catch (e) { /* fall through */ }
            if (window.tinymce && tinymce.activeEditor) {
                tinymce.activeEditor.execCommand('mceInsertContent', false, html);
            }
        },

        // ---- Schema ----
        rebuildSchema() {
            const type = this.schemaType || 'Article';
            const obj = {
                '@context': 'https://schema.org',
                '@type': type,
                headline: this.seo.seo_title || this.seo.title,
                description: this.seo.description,
                url: this.seo.permalink
            };
            this.seo.schema = JSON.stringify(obj, null, 2);
            this.onFieldChange();
        },

        // ---- AI Modal ----
        openAiModal(tab) {
            this.aiTab = tab || 'title';
            this.aiOpen = true;
            this.activeTab = 'seo';
        },

        async generateAI(kind) {
            this.aiLoading = true;
            try {
                // Placeholder until AI Studio is wired.
                // Expected endpoint: POST /bankai/v1/ai/seo-generate
                await new Promise((r) => setTimeout(r, 600));

                if (kind === 'title') {
                    const kw = this.seo.focus_keyword || 'موضوع';
                    this.aiDraft.title = `${kw} | راهنمای کامل و کاربردی`;
                    this.aiDraft.description = `در این مطلب درباره ${kw} به‌صورت جامع صحبت می‌کنیم و نکات عملی برای بهبود نتیجه ارائه می‌دهیم.`;
                } else if (kind === 'keywords') {
                    const base = this.seo.focus_keyword || this.seo.title || 'موضوع';
                    this.aiDraft.keywords = [base, base + ' چیست', 'بهترین ' + base, base + ' ۲۰۲۵'].join('، ');
                } else if (kind === 'rewrite') {
                    this.aiDraft.rewrite = 'بازنویسی هوشمند به‌زودی از طریق Bankai AI Studio فعال می‌شود. محتوای فعلی حفظ شده است.';
                }

                this.showToast('پیشنهاد AI آماده شد (دمو)', 'info');
            } catch (e) {
                this.showToast('خطا در تولید AI', 'error');
            } finally {
                this.aiLoading = false;
            }
        },

        applyAiDraft(kind) {
            if (kind === 'title') {
                if (this.aiDraft.title) this.seo.seo_title = this.aiDraft.title;
                if (this.aiDraft.description) this.seo.description = this.aiDraft.description;
            } else if (kind === 'keywords' && this.aiDraft.keywords) {
                const parts = this.aiDraft.keywords.split(/[،,]/).map((s) => s.trim()).filter(Boolean);
                parts.forEach((p) => {
                    if (!this.keywordList.includes(p)) this.keywordList.push(p);
                });
                this.pushKeywordsToSeo();
            }
            this.onFieldChange();
            this.showToast('اعمال شد', 'success');
        },

        showToast(message, type = 'info') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 2800);
            if (window.bankaiAdmin && typeof window.bankaiAdmin.showToast === 'function') {
                window.bankaiAdmin.showToast(message, type);
            }
        }
    }));
});
