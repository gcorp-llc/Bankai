document.addEventListener('alpine:init', () => {
    Alpine.data('bankaiArticlesHub', () => ({
        panel: 'list',
        ms: {
            items: [], total: 0, page: 1, pages: 1, selected: [], allSelected: false,
            textProvider: '', textProviders: [],
            aiRunning: false, aiProgress: 0, aiAbort: false, loaded: false
        },
        articles: [],
        auditBusyId: 0,
        auditDrawer: { open: false, title: '', score: 0, tab: 'seo', result: null, summary: null, phase: 'idle', progress: 0, step: '', checklist: [], view: 'list', liveSeo: [], liveAeo: [], liveGeo: [], postId: 0, aiRunning: false, aiReady: false, aiProgress: 0, aiStep: '', aiSteps: [], aiProposed: null },
        tip: { show: false, x: 0, y: 0, title: '', body: '', pass: false, sticky: false },
        loading: false,
        page: 1,
        perPage: 10,
        totalPages: 1,
        total: 0,
        searchQ: '',
        orderby: 'date',
        order: 'DESC',
        queue: (window.bankaiArticlesQueue || []),
        gen: {
            topic: '', focus: '', length: '3000', status: 'draft',
            schedule_date: '', schedule_time: '10:00', hour12: 10, minute: 0, ampm: 'AM',
            jyear: 1404, jmonth: 7, jday: 6, notes: '', do_seo: true
        },
        genBusy: false, schedOpen: false, wsSchedOpen: false, tzLabel: 'UTC+3:30', _schedPosBound: null,
        jMonths: ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'],
        genMessage: '',
        genProgress: 0,
        genStepLabel: '',
        wizStep: 1,
        wizBusy: false,
        batchBusy: false,
        wizItemSchedOpen: false,
        wizSchedIdx: -1,
        wizSchedDraft: { jyear: 1404, jmonth: 7, jday: 1, hour12: 10, minute: 0, ampm: 'AM' },
        wizProviders: [],
        wiz: {
            topic: '', count: 10, category: '', provider: '',
            defaultLength: '3000', defaultNotes: '', status: 'draft', gapHours: 24,
            suggestions: [], selected: [], queue: [],
            phase: 'setup', globalProgress: 0, globalLabel: '', paused: false, pauseMessage: '', checkingConn: false
        },
        ws: {
            open: false, tab: 'seo', id: 0, title: '', status: 'draft',
            content: '', seo_title: '', description: '', focus_keyword: '', keywords_str: '',
            keywordList: [], kwDraft: '',
            canonical: '', permalink: '', cover: '', cover_id: 0, score: 0,
            robots_index: true, robots_follow: true,
            og_title: '', og_description: '', og_image: '',
            x_title: '', x_description: '', x_image: '',
            schema: '', schemaType: 'Article', faqItems: [{q:'',a:''}],
            analysis: { score: 0, passed: 0, total: 0, groups: {}, checks: [] },
            saving: false,
            jyear: 1404, jmonth: 7, jday: 6, hour12: 10, minute: 0, ampm: 'AM', date_iso: '', aiBusy: false, aiMsg: '', aiTask: '', aiProgress: 0, aiProgressLabel: '', rewriteStyle: 'seo',
            linkQuery: '', linkResults: [], linkList: [], linkData: { internal: [], external: [], counts: { internal: 0, external: 0 } },
            postResults: [], contentMatchesInternal: [], contentMatchesExternal: [], linksSubTab: 'builder', linkAnchor: '', linkBusy: false, linkSearchLabel: 'جستجو', linkStatusMsg: '', linkApplyBusy: false, extFindBusy: false, extApplyBusy: false,
            linkStats: { internal: 0, external: 0 },
            extAnchor: '', extUrl: '', extNofollow: true,
            postImages: [], optBusy: false, optProgress: 0, optMaxW: 1600, optQuality: 82, optWebp: true, optAlt: ''
        },
        get scoreCirc() { return 2 * Math.PI * 14; },
        get scoreOff() {
            const s = Math.min(100, Math.max(0, this.ws.score || 0));
            return this.scoreCirc - (this.scoreCirc * s / 100);
        },
        init() {
            try { this.initScheduleFromNow(); } catch (e) {}
            try { this.initWizFromQuery(); } catch (e) {}
            try { this.bindMissingSeoEvents(); } catch (e) {}
            this._schedPosRaf = 0;
            this._schedPosBound = () => {
                if (this._schedPosRaf) return;
                this._schedPosRaf = requestAnimationFrame(() => {
                    this._schedPosRaf = 0;
                    if (this.schedOpen) this.positionSchedulePop();
                    if (this.wsSchedOpen) this.positionWsSchedulePop();
                });
            };
            window.addEventListener('scroll', this._schedPosBound, { capture: true, passive: true });
            window.addEventListener('resize', this._schedPosBound, { passive: true });
            this.loadArticles();
        },
        scoreColor(s) {
            s = parseInt(s, 10) || 0;
            if (s >= 80) return '#0f7b3a';
            if (s >= 50) return '#d97706';
            return '#c42b1c';
        },
        scoreBg(s) {
            s = parseInt(s, 10) || 0;
            if (s >= 80) return '#e8f5e9';
            if (s >= 50) return '#fff7ed';
            return '#fef2f2';
        },
        restBase() {
            return (window.bankaiCoreData && bankaiCoreData.restUrl)
                ? bankaiCoreData.restUrl
                : '/wp-json/bankai/v1/';
        },
        restHeaders(json) {
            const h = {
                'X-WP-Nonce': (window.bankaiCoreData && bankaiCoreData.nonce)
                    || (window.wpApiSettings && wpApiSettings.nonce)
                    || ''
            };
            if (json) h['Content-Type'] = 'application/json';
            return h;
        },
        adminNonce() {
            return (window.bankaiCoreData && bankaiCoreData.adminNonce)
                || window.bankaiAdminNonce
                || '';
        },
        
        statusIcon(status) {
            if (status === 'publish') return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>';
            if (status === 'future') return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>';
        },
        
        
        ringOffset(v) {
            const s = Math.min(100, Math.max(0, Number(v) || 0));
            return 87.96 - (87.96 * s / 100);
        },
        ringLabel(v) {
            if (v == null || v === '') return '—';
            return (Number(v) || 0) + '%';
        },
        showTip(e, item) {
            if (!item) return;
            if (this._tipTimer) clearTimeout(this._tipTimer);
            const pass = item.pass === true || item.state === 'pass';
            const el = e && e.currentTarget;
            const panel = el ? el.closest('.bk-audit-panel') : document.querySelector('#tab-articles .bk-audit-panel');
            const body = this.expandGuideHint(item) || (pass
                ? 'این مورد با موفقیت پاس شده است و نیازی به اقدام ندارد.'
                : 'این مورد نیاز به بررسی و اصلاح دارد. از «راهنمای رفع خطا» یا «بهبود با هوش مصنوعی» استفاده کنید.');
            this._tipTimer = setTimeout(() => {
                if (!el || !panel) {
                    this.tip = { show: true, x: 12, y: 12, title: item.label || '', body: body, pass: pass, sticky: false };
                    return;
                }
                const pr = panel.getBoundingClientRect();
                const ir = el.getBoundingClientRect();
                const tipW = 260;
                const tipH = 110;
                // prefer corners relative to item: TR, TL, BR, BL
                const candidates = [
                    { x: ir.right - pr.left + 8, y: ir.top - pr.top }, // right of item
                    { x: ir.left - pr.left - tipW - 8, y: ir.top - pr.top }, // left
                    { x: ir.right - pr.left + 8, y: ir.bottom - pr.top - tipH }, // bottom-right
                    { x: ir.left - pr.left - tipW - 8, y: ir.bottom - pr.top - tipH }, // bottom-left
                    { x: ir.left - pr.left, y: ir.top - pr.top - tipH - 8 }, // above
                    { x: ir.left - pr.left, y: ir.bottom - pr.top + 8 }, // below
                ];
                let best = candidates[0];
                const maxX = Math.max(0, panel.clientWidth - tipW - 8);
                const maxY = Math.max(0, panel.clientHeight - tipH - 8);
                for (const c of candidates) {
                    const x = Math.min(maxX, Math.max(8, c.x));
                    const y = Math.min(maxY, Math.max(8, c.y));
                    // prefer fully inside
                    if (c.x >= 8 && c.x <= maxX && c.y >= 8 && c.y <= maxY) {
                        best = { x, y };
                        break;
                    }
                    best = { x, y };
                }
                this.tip = {
                    show: true,
                    x: best.x,
                    y: best.y,
                    title: item.label || '',
                    body: body,
                    pass: pass,
                    sticky: false
                };
            }, 1000);
        },
        hideTip() {
            if (this._tipTimer) { clearTimeout(this._tipTimer); this._tipTimer = null; }
            this.tip.show = false;
            this.tip.sticky = false;
        },
        expandGuideHint(item) {
            if (!item) return '';
            const key = String(item.key || '').toLowerCase();
            const label = String(item.label || '').toLowerCase();
            const blob = key + ' ' + label;
            const guides = [
                { re: /title.?length|طول.?عنوان|عنوان.?سئو|seo.?title|meta.?title/, text: 'نقش: عنوان سئو در نتایج گوگل و تب مرورگر دیده می‌شود و اولین سیگنال موضوع صفحه است. راه حل: عنوان را ۵۰ تا ۶۰ کاراکتر نگه دارید، کلمه کلیدی اصلی را نزدیک ابتدا بگذارید و از کلی‌گویی یا تکرار بی‌مورد پرهیز کنید. عمل: در سایدبار سئو فیلد «عنوان سئو» را ویرایش و ذخیره کنید.' },
                { re: /desc|توضیح|metadesc|meta.?desc/, text: 'نقش: توضیحات متا خلاصه ارزش صفحه را در نتایج جستجو نشان می‌دهد و نرخ کلیک را بالا می‌برد. راه حل: متنی ۱۲۰ تا ۱۵۵ کاراکتری بنویسید که مشکل مخاطب، مزیت محتوا و دعوت به اقدام را پوشش دهد. عمل: فیلد توضیحات متا را در سئوی مقاله تکمیل یا کوتاه کنید.' },
                { re: /focus|کلید.?واژه.?اصلی|primary.?keyword/, text: 'نقش: کلمه کلیدی اصلی محور بهینه‌سازی عنوان، متا و بدنه است. راه حل: یک عبارت دقیق و جستجوشونده انتخاب کنید و در عنوان، مقدمه و حداقل یک زیرعنوان بیاورید. عمل: فیلد کلمه کلیدی اصلی را تنظیم و در متن طبیعی به‌کار ببرید.' },
                { re: /secondary|کلید.?واژه.?فرعی/, text: 'نقش: کلیدواژه‌های فرعی پوشش موضوعی را کامل می‌کنند و از تک‌کلمه‌ای شدن محتوا جلوگیری می‌کنند. راه حل: ۲ تا ۴ عبارت مرتبط اضافه کنید و هر کدام را حداقل یک‌بار در بدنه یا زیرعنوان بیاورید. عمل: در باکس کلیدواژه‌ها موارد فرعی را ثبت کنید.' },
                { re: /heading|هدینگ|hierarchy|h1|h2|h3/, text: 'نقش: سلسله‌مراتب هدینگ ساختار معنایی را برای کاربر و موتور جستجو مشخص می‌کند. راه حل: یک H1، سپس H2های موضوعی و در صورت نیاز H3 زیر هر بخش — از پرش سطح (مثلاً H1 به H3) خودداری کنید. عمل: در ویرایشگر هدینگ‌ها را بازچینش کنید.' },
                { re: /image|تصویر|alt|webp|legacy.?format/, text: 'نقش: تصاویر با Alt و فرمت مناسب دسترسی‌پذیری و سرعت را بهبود می‌دهند و سیگنال موضوع بصری می‌دهند. راه حل: برای هر تصویر Alt توصیفی بنویسید و در صورت امکان به WebP با حجم منطقی تبدیل کنید. عمل: از تب تصاویر سئو اسکن کنید، Alt را پر و بهینه‌سازی گروهی را اجرا کنید.' },
                { re: /internal.?link|لینک.?داخلی/, text: 'نقش: لینک داخلی اعتبار را بین صفحات پخش می‌کند و مسیر خزیدن را روشن می‌سازد. راه حل: ۲ تا ۵ لینک به مقالات مرتبط با انکر متن توصیفی (نه «اینجا کلیک کنید») اضافه کنید. عمل: از ابزار لینک داخلی سئو یا ویرایشگر، لینک‌های موضوعی درج کنید.' },
                { re: /external|لینک.?خارجی|outbound/, text: 'نقش: ارجاع به منابع معتبر اعتماد محتوا را برای کاربر و AI افزایش می‌دهد. راه حل: در صورت نیاز یک یا دو منبع معتبر با انکر شفاف لینک دهید. عمل: در بخش منابع یا بدنه، لینک خارجی مرتبط اضافه کنید.' },
                { re: /faq|پرسش|question|aeo/, text: 'نقش: بخش سوالات متداول استخراج پاسخ برای موتورهای پاسخ‌گو (AEO) را آسان می‌کند. راه حل: ۳ تا ۵ پرسش واقعی مخاطب با پاسخ کوتاه و مستقیم بنویسید. عمل: بلوک FAQ در محتوا اضافه کنید یا از بهبود AI برای درج FAQ استفاده کنید.' },
                { re: /schema|اسکیما|json.?ld/, text: 'نقش: اسکیما نوع محتوا را برای گوگل و سیستم‌های AI ساخت‌یافته می‌کند. راه حل: نوع Article/BlogPosting و در صورت FAQ بودن FAQPage را فعال نگه دارید. عمل: در تب اسکیمای سئو نوع مناسب را انتخاب و ذخیره کنید.' },
                { re: /og:|open.?graph|twitter|سوشال/, text: 'نقش: Open Graph و Twitter Card پیش‌نمایش اشتراک‌گذاری در شبکه‌های اجتماعی را کنترل می‌کنند. راه حل: عنوان، توضیح و تصویر OG را پر کنید (ترجیحاً هم‌راستا با متای سئو). عمل: تب سوشال سئو را کامل کنید.' },
                { re: /canonical|کنونیکال/, text: 'نقش: کنونیکال نسخه اصلی URL را مشخص می‌کند تا محتوای تکراری ایندکس نشود. راه حل: روی URL نهایی و ترجیحی صفحه تنظیم شود. عمل: فیلد کنونیکال را در سئو بررسی یا خالی بگذارید تا خودکار باشد.' },
                { re: /robots|noindex|index/, text: 'نقش: دستور robots مشخص می‌کند صفحه ایندکس شود یا نه. راه حل: برای مقالات عمومی ایندکس مجاز باشد؛ آرشیوهای بی‌ارزش را noindex کنید. عمل: تنظیمات robots پست و سئوی سایت را مرور کنید.' },
                { re: /word.?count|کلمه|depth|عمق|خوانایی|sentence|جمله/, text: 'نقش: عمق و خوانایی محتوا سیگنال کیفیت برای رتبه‌بندی و تحلیل AI است. راه حل: جملات بلند را بشکنید، پاراگراف‌ها را کوتاه کنید و پوشش موضوع را کامل کنید. عمل: متن را بازنویسی و بخش‌های ناقص را گسترش دهید.' },
                { re: /mobile|viewport|https|امن|ssl/, text: 'نقش: موبایل‌فرندلی و HTTPS پیش‌نیاز اعتماد کاربر و سیستم‌های AI است. راه حل: تم واکنش‌گرا و گواهی SSL فعال باشد. عمل: در سطح سایت (هاست/تم) بررسی کنید؛ معمولاً در سطح تک‌پست قابل رفع نیست.' },
                { re: /author|نویسنده|tldr|خلاصه|entity|برند/, text: 'نقش: نویسنده، خلاصه و موجودیت برند به اعتبار و استخراج پاسخ کمک می‌کنند. راه حل: بایو نویسنده، نام برند یکدست و در صورت امکان خلاصه ابتدای مطلب را اضافه کنید. عمل: پروفایل نویسنده و مقدمه مقاله را تکمیل کنید.' },
            ];
            for (const g of guides) {
                if (g.re.test(blob)) return g.text;
            }
            // Prefer server hint if meaningful
            const base = (item.hint || '').trim();
            if (base && base.length > 20 && !/بررسی کنید؛ پس از اصلاح/.test(base)) {
                return base + ' عمل: مورد را در همان بخش مرتبط سئو یا محتوا اصلاح کنید و دوباره آنالیز بگیرید.';
            }
            return 'نقش: این معیار یکی از سیگنال‌های کیفیت صفحه برای جستجو یا موتورهای پاسخ‌گو است. راه حل: برچسب مورد را بخوانید و همان بخش (عنوان، محتوا، تصویر یا فنی) را هدف بگیرید. عمل: اصلاح مشخص همان آیتم را انجام دهید، ذخیره کنید و آنالیز مجدد بزنید.';
        },
        async runAiFix() {
            const id = this.auditDrawer.postId;
            if (!id || this.auditDrawer.aiRunning) return;
            const failed = this.failedAuditItems();
            if (!failed.length) {
                this.toastMsg('مورد ناموفقی برای بهبود نیست', 'info');
                return;
            }
            this.auditDrawer.aiRunning = true;
            this.auditDrawer.aiReady = false;
            this.auditDrawer.aiProgress = 10;
            this.auditDrawer.aiStep = 'آماده‌سازی پیشنهادهای AI…';
            this.auditDrawer.aiSteps = failed.slice(0, 8).map((f, i) => ({
                id: f.key || ('f' + i),
                label: 'بررسی: ' + (f.label || f.key),
                status: i === 0 ? 'run' : 'pending'
            }));
            this.toastMsg('بهبود با هوش مصنوعی شروع شد', 'info');
            try {
                // animate steps
                for (let i = 0; i < this.auditDrawer.aiSteps.length; i++) {
                    this.auditDrawer.aiSteps[i].status = 'run';
                    this.auditDrawer.aiProgress = 15 + Math.round((i / Math.max(1, this.auditDrawer.aiSteps.length)) * 50);
                    this.auditDrawer.aiStep = this.auditDrawer.aiSteps[i].label;
                    await new Promise((r) => setTimeout(r, 180));
                }
                const fd = new FormData();
                fd.append('action', 'bankai_seo_audit_ai_fix');
                fd.append('nonce', this.msNonce ? this.msNonce() : (window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || ''));
                fd.append('post_id', String(id));
                fd.append('failed', JSON.stringify(failed.map((f) => ({ key: f.key, label: f.label, group: f.group }))));
                if (this.ms && this.ms.textProvider) fd.append('provider', this.ms.textProvider);
                const ajax = (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
                this.auditDrawer.aiProgress = 70;
                this.auditDrawer.aiStep = 'دریافت پیشنهاد از AI…';
                const r = await fetch(ajax, { method: 'POST', credentials: 'same-origin', body: fd });
                const j = await r.json();
                if (j && j.success && j.data) {
                    (this.auditDrawer.aiSteps || []).forEach((s) => { s.status = 'done'; });
                    if (j.data.steps && j.data.steps.length) {
                        this.auditDrawer.aiSteps = j.data.steps;
                    }
                    this.auditDrawer.aiProposed = j.data.proposed || null;
                    this.auditDrawer.aiReady = true;
                    this.auditDrawer.aiProgress = 100;
                    this.auditDrawer.aiStep = j.data.message || 'پیشنهادها آماده است';
                    this.toastMsg(j.data.message || 'پیشنهادهای AI آماده شد', 'success');
                } else {
                    this.toastMsg((j && j.data && j.data.message) || 'خطا در بهبود AI', 'error');
                }
            } catch (e) {
                console.error(e);
                this.toastMsg('خطا در ارتباط با AI', 'error');
            } finally {
                this.auditDrawer.aiRunning = false;
            }
        },
        async applyAiFix() {
            const id = this.auditDrawer.postId;
            if (!id || !this.auditDrawer.aiReady) return;
            this.auditBusyId = id;
            this.toastMsg('در حال ثبت اصلاحات…', 'info');
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_seo_audit_apply_fix');
                fd.append('nonce', this.msNonce ? this.msNonce() : (window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || ''));
                fd.append('post_id', String(id));
                const ajax = (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
                const r = await fetch(ajax, { method: 'POST', credentials: 'same-origin', body: fd });
                const j = await r.json();
                if (j && j.success) {
                    this.toastMsg('اصلاحات ثبت شد — در حال آنالیز مجدد…', 'success');
                    this.auditDrawer.aiReady = false;
                    this.auditDrawer.aiProposed = null;
                    await this.rerunAudit();
                } else {
                    this.toastMsg((j && j.data && j.data.message) || 'خطا در ثبت اصلاحات', 'error');
                }
            } catch (e) {
                this.toastMsg('خطا در ثبت اصلاحات', 'error');
            } finally {
                this.auditBusyId = 0;
            }
        },

        lineIcon(state) {
            if (state === 'pass') return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>';
            if (state === 'fail') return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>';
            if (state === 'run') return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" stroke-dasharray="4 3"/></svg>';
            return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>';
        },
        toastMsg(msg, type) {
            const inst = window.bankaiAdminInstance;
            if (inst && typeof inst.showToast === 'function') {
                inst.showToast(msg, type || 'info');
            } else if (typeof this.showToast === 'function') {
                this.showToast(msg, type || 'info');
            }
        },
        closeAuditPanel() {
            this.auditDrawer.open = false;
            this.auditBusyId = 0;
        },
        failedAuditItems() {
            const r = this.auditDrawer.result || {};
            const tag = (arr, g) => (arr || []).filter((i) => !i.pass).map((i) => Object.assign({}, i, { group: g }));
            return [].concat(tag(r.seo, 'seo'), tag(r.aeo, 'aeo'), tag(r.geo, 'geo'));
        },
        seedLiveChecks() {
            // Placeholder labels shown while running (before server response)
            const mk = (keys) => keys.map((k) => ({ key: k, label: k, state: 'pending', hint: '' }));
            this.auditDrawer.liveSeo = mk(['عنوان و متا', 'هدینگ‌ها', 'تصاویر و ALT', 'لینک‌ها', 'اسکیما و OG', 'فنی صفحه']);
            this.auditDrawer.liveAeo = mk(['FAQ و پرسش', 'ساختار لیست', 'تعاریف', 'عمق محتوا', 'تازگی', 'نویسنده']);
            this.auditDrawer.liveGeo = mk(['Overview readiness', 'منابع و استناد', 'برند', 'خلاصه', 'Schema', 'موبایل/HTTPS']);
        },
        applyLiveFromResult(d) {
            const map = (arr) => (arr || []).map((i) => ({
                key: i.key,
                label: i.label,
                state: i.pass ? 'pass' : 'fail',
                hint: i.hint || ''
            }));
            this.auditDrawer.liveSeo = map(d.seo);
            this.auditDrawer.liveAeo = map(d.aeo);
            this.auditDrawer.liveGeo = map(d.geo);
        },
        async openAuditPanel(row, forceRerun) {
            if (!row || !row.id) return;
            const hasScores = (row.seo_audit != null && row.seo_audit !== '') || (row.aeo_audit != null && row.aeo_audit !== '') || (row.audit_score > 0);
            this.auditDrawer.postId = row.id;
            this.auditDrawer.title = row.title;
            this.auditDrawer.view = 'list';
            this.auditDrawer.open = true;

            if (!forceRerun && hasScores) {
                // Load previous report if available
                this.auditDrawer.phase = 'done';
                this.auditDrawer.progress = 100;
                this.auditDrawer.step = 'گزارش قبلی';
                this.auditDrawer.summary = {
                    seo: row.seo_audit || 0,
                    aeo: row.aeo_audit || 0,
                    geo: row.geo_audit || 0,
                    seo_issues: 0, aeo_issues: 0, geo_issues: 0
                };
                this.auditDrawer.score = row.audit_score || 0;
                try {
                    const fd = new FormData();
                    fd.append('action', 'bankai_seo_page_audit_report');
                    fd.append('nonce', this.msNonce ? this.msNonce() : (window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || ''));
                    fd.append('post_id', String(row.id));
                    const ajax = (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
                    const r = await fetch(ajax, { method: 'POST', credentials: 'same-origin', body: fd });
                    const j = await r.json();
                    if (j && j.success && j.data && j.data.has_report) {
                        const d = j.data;
                        this.auditDrawer.result = { seo: d.seo || [], aeo: d.aeo || [], geo: d.geo || [] };
                        this.auditDrawer.summary = d.summary || this.auditDrawer.summary;
                        this.auditDrawer.score = d.score || this.auditDrawer.score;
                        this.applyLiveFromResult(d);
                        this.toastMsg('گزارش قبلی نمایش داده شد', 'info');
                        return;
                    }
                } catch (e) {}
                // No full report — still show scores, allow re-run
                this.auditDrawer.result = { seo: [], aeo: [], geo: [] };
                this.toastMsg('امتیاز قبلی موجود است — برای جزئیات آنالیز مجدد بزنید', 'info');
                return;
            }
            await this.executeAudit(row);
        },
        async runRowAudit(row) {
            await this.openAuditPanel(row, false);
        },
        async rerunAudit() {
            const id = this.auditDrawer.postId;
            const row = (this.articles || []).find((a) => a.id === id) || { id: id, title: this.auditDrawer.title };
            await this.openAuditPanel(row, true);
        },
        async executeAudit(row) {
            if (!row || !row.id || this.auditBusyId) return;
            this.auditBusyId = row.id;
            this.seedLiveChecks();
            this.auditDrawer.phase = 'run';
            this.auditDrawer.progress = 8;
            this.auditDrawer.step = 'شروع آنالیز…';
            this.auditDrawer.result = null;
            this.toastMsg('آنالیز SEO / AEO / GEO شروع شد', 'info');

            // Animate pending → run in waves
            const markRun = (listName, idx) => {
                const list = this.auditDrawer[listName] || [];
                if (list[idx]) list[idx].state = 'run';
            };
            try {
                for (let i = 0; i < 3; i++) {
                    markRun('liveSeo', i); markRun('liveAeo', i); markRun('liveGeo', i);
                }
                this.auditDrawer.progress = 25;
                this.auditDrawer.step = 'خواندن محتوا و متا…';

                const fd = new FormData();
                fd.append('action', 'bankai_seo_page_audit');
                fd.append('nonce', this.msNonce ? this.msNonce() : (window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || ''));
                fd.append('post_id', String(row.id));
                if (row.permalink) fd.append('url', row.permalink);
                const ajax = (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
                this.auditDrawer.progress = 45;
                this.auditDrawer.step = 'اجرای چک‌لیست‌ها…';
                for (let i = 3; i < 6; i++) {
                    markRun('liveSeo', i); markRun('liveAeo', i); markRun('liveGeo', i);
                }

                const r = await fetch(ajax, { method: 'POST', credentials: 'same-origin', body: fd });
                this.auditDrawer.progress = 80;
                this.auditDrawer.step = 'جمع‌بندی گزارش…';
                const j = await r.json();
                if (j && j.success && j.data) {
                    const d = j.data;
                    const seoS = (d.summary && d.summary.seo) || 0;
                    const aeoS = (d.summary && d.summary.aeo) || 0;
                    const geoS = (d.summary && d.summary.geo) || 0;
                    const totS = d.score || 0;
                    row.seo_audit = seoS;
                    row.aeo_audit = aeoS;
                    row.geo_audit = geoS;
                    row.audit_score = totS;
                    // Live update table row in articles list
                    const idx = (this.articles || []).findIndex((a) => a.id === row.id);
                    if (idx >= 0) {
                        this.articles[idx] = Object.assign({}, this.articles[idx], {
                            seo_audit: seoS,
                            aeo_audit: aeoS,
                            geo_audit: geoS,
                            audit_score: totS
                        });
                        this.articles = this.articles.slice();
                    }
                    this.auditDrawer.result = { seo: d.seo || [], aeo: d.aeo || [], geo: d.geo || [] };
                    this.auditDrawer.summary = d.summary || {};
                    this.auditDrawer.score = d.score || 0;
                    this.applyLiveFromResult(d);
                    this.auditDrawer.progress = 100;
                    this.auditDrawer.phase = 'done';
                    this.auditDrawer.step = 'گزارش کامل شد';
                    this.toastMsg('آنالیز کامل شد — امتیاز کل ' + (d.score || 0) + '%', 'success');
                } else {
                    this.auditDrawer.phase = 'done';
                    this.toastMsg((j && j.data && j.data.message) || 'خطا در آنالیز', 'error');
                }
            } catch (e) {
                console.error(e);
                this.auditDrawer.phase = 'done';
                this.toastMsg('خطا یا تایم‌اوت — دوباره تلاش کنید', 'error');
            } finally {
                this.auditBusyId = 0;
            }
        },
        auditPct(v) {
            if (v == null || v === '') return '—';
            return (Number(v) || 0) + '%';
        },
        async loadArticles() {
            this.loading = true;
            try {
                const q = new URLSearchParams({
                    page: String(this.page || 1),
                    per_page: String(this.perPage || 10),
                    orderby: this.orderby || 'date',
                    order: this.order || 'DESC',
                    search: this.searchQ || ''
                });
                let items = [];
                let total = 0;
                // Primary: REST
                try {
                    const r = await fetch(this.restBase() + 'seo/articles?' + q.toString(), {
                        credentials: 'same-origin', headers: this.restHeaders(false)
                    });
                    const j = await r.json();
                    if (r.ok) {
                        items = (j && j.data && j.data.items) ? j.data.items : (j.items || []);
                        total = (j && j.data && j.data.total != null) ? j.data.total : (j.total || (items && items.length) || 0);
                    } else {
                        console.warn('articles REST', r.status, j);
                    }
                } catch (err) {
                    console.warn('articles REST fail', err);
                }
                // Fallback: admin-ajax
                if (!items || !items.length) {
                    const fd = new FormData();
                    fd.append('action', 'bankai_list_articles');
                    fd.append('nonce', this.adminNonce());
                    fd.append('page', String(this.page || 1));
                    fd.append('per_page', String(this.perPage || 10));
                    fd.append('orderby', this.orderby || 'date');
                    fd.append('order', this.order || 'DESC');
                    fd.append('search', this.searchQ || '');
                    const ajaxUrl = (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
                    const r2 = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd });
                    const j2 = await r2.json();
                    const payload = (j2 && j2.data) ? j2.data : j2;
                    if (j2 && j2.success && payload) {
                        items = payload.items || [];
                        total = payload.total != null ? payload.total : items.length;
                    }
                }
                this.articles = Array.isArray(items) ? items : [];
                this.total = Number(total) || this.articles.length;
                this.totalPages = Math.max(1, Math.ceil(this.total / Number(this.perPage || 10)));
            } catch (e) {
                console.error(e);
                this.articles = [];
                this.total = 0;
                this.totalPages = 1;
            } finally {
                this.loading = false;
            }
        },
        /* ---- Jalali schedule (UI جلالی → خروجی میلادی برای WP) ---- */
        g2j(gy, gm, gd) {
            if (window.BankaiJalaliDatepicker && BankaiJalaliDatepicker.g2j) {
                return BankaiJalaliDatepicker.g2j(gy, gm, gd);
            }
            const g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
            const gy2 = (gm > 2) ? (gy + 1) : gy;
            let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
                + Math.floor((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
            let jy = -1595 + (33 * Math.floor(days / 12053));
            days %= 12053;
            jy += 4 * Math.floor(days / 1461);
            days %= 1461;
            if (days > 365) { jy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
            let jm, jd;
            if (days < 186) { jm = 1 + Math.floor(days / 31); jd = 1 + (days % 31); }
            else { jm = 7 + Math.floor((days - 186) / 30); jd = 1 + ((days - 186) % 30); }
            return [jy, jm, jd];
        },
        j2g(jy, jm, jd) {
            if (window.BankaiJalaliDatepicker && BankaiJalaliDatepicker.j2g) {
                return BankaiJalaliDatepicker.j2g(jy, jm, jd);
            }
            jy += 1595;
            let days = -355668 + (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor(((jy % 33) + 3) / 4) + jd
                + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30 + 186));
            let gy = 400 * Math.floor(days / 146097);
            days %= 146097;
            if (days > 36524) {
                gy += 100 * Math.floor(--days / 36524);
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * Math.floor(days / 1461);
            days %= 1461;
            if (days > 365) { gy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
            let gd = days + 1;
            const sal_a = [0,31,((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,31,30,31,30,31,31,30,31,30,31];
            let gm = 1;
            for (; gm <= 12 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
            return [gy, gm, gd];
        },
        jDaysInMonth(jy, jm) {
            if (jm <= 6) return 31;
            if (jm <= 11) return 30;
            const a = jy - (jy > 0 ? 474 : 473);
            const b = a % 2820 + 474;
            return (((b + 38) * 682) % 2816) < 682 ? 30 : 29;
        },
        pad2(n) { return (n < 10 ? '0' : '') + n; },
        initScheduleFromNow() {
            const now = new Date();
            let h = now.getHours();
            this.gen.ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12; if (h === 0) h = 12;
            this.gen.hour12 = h;
            this.gen.minute = now.getMinutes();
            const j = this.g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
            this.gen.jyear = j[0]; this.gen.jmonth = j[1]; this.gen.jday = j[2];
            try {
                const off = -now.getTimezoneOffset() / 60;
                const sign = off >= 0 ? '+' : '-';
                const ah = Math.floor(Math.abs(off));
                const am = Math.round((Math.abs(off) - ah) * 60);
                this.tzLabel = 'UTC' + sign + ah + (am ? ':' + this.pad2(am) : '');
            } catch (e) {}
            this.syncScheduleFromUi();
        },
        toggleSchedulePop() {
            if (!this.schedOpen && (!this.gen.schedule_date || !this.gen.jyear)) {
                this.initScheduleFromNow();
            }
            this.schedOpen = !this.schedOpen;
            if (this.schedOpen) {
                this.$nextTick(() => this.positionSchedulePop());
            }
        },
        positionSchedulePop() {
            const btn = this.$refs.schedToggle;
            const pop = this.$refs.schedPop;
            if (!btn || !pop) return;
            const r = btn.getBoundingClientRect();
            const gap = 8;
            const pw = Math.min(340, window.innerWidth - 16);
            pop.style.width = pw + 'px';
            pop.style.visibility = 'hidden';
            pop.style.display = 'block';
            const ph = pop.offsetHeight || 420;
            let left = r.right - pw;
            if (left < 8) left = 8;
            if (left + pw > window.innerWidth - 8) left = Math.max(8, window.innerWidth - pw - 8);
            let top = r.bottom + gap;
            if (top + ph > window.innerHeight - 8) {
                top = r.top - ph - gap;
                if (top < 8) top = 8;
            }
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
            pop.style.right = 'auto';
            pop.style.visibility = '';
        },
        setScheduleNow() {
            this.initScheduleFromNow();
        },
        wsScheduleLabel() {
            const jy = this.ws.jyear, jm = this.ws.jmonth, jd = this.ws.jday;
            const mname = this.jMonths[(jm || 1) - 1] || '';
            const h = this.wsHour24();
            const mi = this.pad2(this.ws.minute || 0);
            if (!jy) return 'تاریخ انتشار';
            return mname + ' ' + jd + '، ' + jy + ' — ' + this.pad2(h) + ':' + mi;
        },
        wsHour24() {
            let h = Number(this.ws.hour12) || 12;
            if (h < 1) h = 1; if (h > 12) h = 12;
            if (this.ws.ampm === 'AM') return h === 12 ? 0 : h;
            return h === 12 ? 12 : h + 12;
        },
        toggleWsSchedulePop() {
            this.wsSchedOpen = !this.wsSchedOpen;
            if (this.wsSchedOpen) {
                if (!this.ws.jyear) this.setWsScheduleFromDate(new Date());
                this.$nextTick(() => this.positionWsSchedulePop());
            }
        },
        positionWsSchedulePop() {
            const btn = this.$refs.wsSchedToggle;
            const pop = this.$refs.wsSchedPop;
            if (!btn || !pop) return;
            const r = btn.getBoundingClientRect();
            const gap = 8;
            const pw = Math.min(340, window.innerWidth - 16);
            pop.style.width = pw + 'px';
            pop.style.visibility = 'hidden';
            pop.style.display = 'block';
            const ph = pop.offsetHeight || 420;
            let left = r.right - pw;
            if (left < 8) left = 8;
            if (left + pw > window.innerWidth - 8) left = Math.max(8, window.innerWidth - pw - 8);
            let top = r.bottom + gap;
            if (top + ph > window.innerHeight - 8) {
                top = r.top - ph - gap;
                if (top < 8) top = 8;
            }
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
            pop.style.right = 'auto';
            pop.style.visibility = '';
        },
        setWsScheduleNow() {
            this.setWsScheduleFromDate(new Date());
        },
        setWsScheduleFromDate(dt) {
            if (!(dt instanceof Date) || isNaN(dt.getTime())) dt = new Date();
            const j = this.g2j(dt.getFullYear(), dt.getMonth() + 1, dt.getDate());
            this.ws.jyear = j[0]; this.ws.jmonth = j[1]; this.ws.jday = j[2];
            let h = dt.getHours();
            this.ws.ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12; if (h === 0) h = 12;
            this.ws.hour12 = h;
            this.ws.minute = dt.getMinutes();
            this.syncWsScheduleFromUi();
        },
        syncWsScheduleFromUi() {
            const jy = Number(this.ws.jyear) || 1400;
            const jm = Number(this.ws.jmonth) || 1;
            let jd = Number(this.ws.jday) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            if (jd > dim) { jd = dim; this.ws.jday = dim; }
            const g = this.j2g(jy, jm, jd);
            const h = this.wsHour24();
            const m = Math.min(59, Math.max(0, Number(this.ws.minute) || 0));
            this.ws.minute = m;
            this.ws.date_iso = g[0] + '-' + this.pad2(g[1]) + '-' + this.pad2(g[2]) + 'T' + this.pad2(h) + ':' + this.pad2(m) + ':00';
        },
        onWsJalaliFieldsChange() { this.syncWsScheduleFromUi(); },
        shiftWsJMonth(delta) {
            let m = Number(this.ws.jmonth) + delta;
            let y = Number(this.ws.jyear);
            if (m > 12) { m = 1; y++; }
            if (m < 1) { m = 12; y--; }
            this.ws.jmonth = m; this.ws.jyear = y;
            this.syncWsScheduleFromUi();
        },
        pickWsJDay(cell) {
            if (!cell || cell.muted || !cell.d) return;
            this.ws.jyear = cell.jy; this.ws.jmonth = cell.jm; this.ws.jday = cell.d;
            this.syncWsScheduleFromUi();
        },
        wsCalCells() {
            const jy = Number(this.ws.jyear) || 1400;
            const jm = Number(this.ws.jmonth) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            const gFirst = this.j2g(jy, jm, 1);
            const first = new Date(gFirst[0], gFirst[1] - 1, gFirst[2]);
            const startDow = (first.getDay() + 1) % 7;
            const today = new Date();
            const tj = this.g2j(today.getFullYear(), today.getMonth() + 1, today.getDate());
            const cells = [];
            for (let i = 0; i < startDow; i++) cells.push({ muted: true, d: 0 });
            for (let d = 1; d <= dim; d++) {
                cells.push({
                    d, jy, jm,
                    sel: Number(this.ws.jday) === d,
                    today: tj[0] === jy && tj[1] === jm && tj[2] === d,
                    muted: false
                });
            }
            return cells;
        },
        hour24() {
            let h = Number(this.gen.hour12) || 12;
            if (h < 1) h = 1; if (h > 12) h = 12;
            if (this.gen.ampm === 'AM') return h === 12 ? 0 : h;
            return h === 12 ? 12 : h + 12;
        },
        syncScheduleFromUi() {
            const jy = Number(this.gen.jyear) || 1400;
            const jm = Number(this.gen.jmonth) || 1;
            let jd = Number(this.gen.jday) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            if (jd > dim) { jd = dim; this.gen.jday = dim; }
            const g = this.j2g(jy, jm, jd);
            this.gen.schedule_date = g[0] + '-' + this.pad2(g[1]) + '-' + this.pad2(g[2]);
            const h = this.hour24();
            const m = Math.min(59, Math.max(0, Number(this.gen.minute) || 0));
            this.gen.minute = m;
            this.gen.schedule_time = this.pad2(h) + ':' + this.pad2(m);
        },
        onJalaliFieldsChange() {
            this.syncScheduleFromUi();
        },
        shiftJMonth(delta) {
            let m = Number(this.gen.jmonth) + delta;
            let y = Number(this.gen.jyear);
            if (m > 12) { m = 1; y++; }
            if (m < 1) { m = 12; y--; }
            this.gen.jmonth = m; this.gen.jyear = y;
            this.syncScheduleFromUi();
        },
        pickJDay(cell) {
            if (!cell || cell.muted || !cell.d) return;
            this.gen.jyear = cell.jy; this.gen.jmonth = cell.jm; this.gen.jday = cell.d;
            this.syncScheduleFromUi();
        },
        jCalendarCells() {
            const jy = Number(this.gen.jyear) || 1400;
            const jm = Number(this.gen.jmonth) || 1;
            const dim = this.jDaysInMonth(jy, jm);
            const gFirst = this.j2g(jy, jm, 1);
            const first = new Date(gFirst[0], gFirst[1] - 1, gFirst[2]);
            const startDow = (first.getDay() + 1) % 7; // شنبه = 0
            const now = new Date();
            const todayJ = this.g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
            const cells = [];
            for (let i = 0; i < startDow; i++) cells.push({ key: 'm'+i, d: 0, muted: true, sel: false, today: false });
            for (let d = 1; d <= dim; d++) {
                cells.push({
                    key: 'd'+d, d, jy, jm, muted: false,
                    sel: d === Number(this.gen.jday),
                    today: todayJ[0] === jy && todayJ[1] === jm && todayJ[2] === d
                });
            }
            return cells;
        },
        scheduleLabel() {
            if (!this.gen.schedule_date) return 'انتخاب زمان انتشار…';
            const mName = this.jMonths[(Number(this.gen.jmonth) || 1) - 1] || '';
            const h = Number(this.gen.hour12) || 12;
            const min = this.pad2(Number(this.gen.minute) || 0);
            const ap = this.gen.ampm === 'PM' ? 'ب.ظ' : 'ق.ظ';
            return (this.tzLabel ? this.tzLabel + ' ' : '') + this.gen.jday + ' ' + mName + ' ' + this.gen.jyear + ' — ' + this.pad2(h) + ':' + min + ' ' + ap;
        },
        scheduleIso() {
            if (!this.gen.schedule_date) return '';
            // Always Gregorian for WordPress: Y-m-d H:i:s
            return this.gen.schedule_date + ' ' + (this.gen.schedule_time || '12:00') + ':00';
        },


        bindMissingSeoEvents() {
            window.addEventListener('bankai-open-missing-seo', () => {
                this.panel = 'missing-seo';
                this.initMissingSeo();
            });
            const params = new URLSearchParams(window.location.search);
            if ((params.get('subtab') || '') === 'missing-seo') {
                this.panel = 'missing-seo';
                this.initMissingSeo();
            }
        },
        initMissingSeo() {
            this.loadMissingSeoProviders();
            this.loadMissingSeoList();
        },
        loadMissingSeoProviders() {
            const st = window.bankaiState || {};
            const norm = (list) => (list || []).map(p => {
                if (typeof p === 'string') return { id: p, name: p, ready: true };
                const statusish = ['کلید تنظیم شده', 'بدون کلید API', 'بدون کلید', 'ready', 'missing_key'];
                let name = p.name || '';
                if (!name || statusish.includes(name)) {
                    name = (p.label && !statusish.includes(p.label)) ? p.label : (p.id || p.key || '');
                }
                // map common ids to readable names
                const idNames = {
                    openrouter: 'OpenRouter', gemini: 'Google Gemini', openai: 'OpenAI',
                    anthropic: 'Anthropic Claude', deepseek: 'DeepSeek', huggingface: 'Hugging Face',
                    groq: 'Groq', cloudflare: 'Cloudflare Workers AI'
                };
                if (!name || statusish.includes(name)) name = idNames[p.id] || p.id || '';
                return {
                    id: p.id || p.key || '',
                    name: name,
                    ready: !!(p.ready || p.has_key || p.hasKey || p.configured || p.status === 'ready' || p.masked_key)
                };
            }).filter(p => p.id);
            // فقط موتورهای متنی فعال (دارای کلید)
            this.ms.textProviders = norm(st.providers || []).filter(p => p.ready);
            if (!this.ms.textProvider && this.ms.textProviders.length) {
                this.ms.textProvider = this.ms.textProviders[0].id;
            } else if (this.ms.textProvider && !this.ms.textProviders.some(p => p.id === this.ms.textProvider)) {
                this.ms.textProvider = this.ms.textProviders[0] ? this.ms.textProviders[0].id : '';
            }
        },
        msCfg() {
            return window.bankaiData || window.bankaiCoreData || {};
        },
        msAjax() {
            const c = this.msCfg();
            return c.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php';
        },
        msNonce() {
            const c = this.msCfg();
            return c.adminNonce || c.nonce || '';
        },
        msSelectedCount() { return (this.ms.selected || []).length; },
        msReadyCount() { return (this.ms.items || []).filter(r => r.aiPhase === 'review' && r.draft).length; },
        msPhaseLabel(phase) {
            return ({ pending:'در صف', running:'در حال اجرا', review:'آماده تأیید', done:'ذخیره شد', error:'خطا', cancelled:'لغو شد' })[phase] || phase || '';
        },
        msToggleAll() {
            if (this.ms.allSelected) {
                this.ms.selected = [];
                this.ms.allSelected = false;
            } else {
                this.ms.selected = (this.ms.items || []).map(r => r.id);
                this.ms.allSelected = true;
            }
        },
        async loadMissingSeoList() {
            const fd = new FormData();
            fd.append('action', 'bankai_seo_import_missing');
            fd.append('nonce', this.msNonce());
            fd.append('page', String(this.ms.page || 1));
            fd.append('per_page', '10');
            try {
                const r = await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
                const j = await r.json();
                if (j && j.success && j.data) {
                    this.ms.items = (j.data.items || []).map(row => Object.assign({
                        aiPhase: '', aiProgress: 0, aiStepText: '', checklist: [], draft: null, saving: false, aiError: ''
                    }, row));
                    this.ms.total = j.data.total || 0;
                    this.ms.pages = j.data.total_pages || j.data.pages || 1;
                    this.ms.page = j.data.page || 1;
                }
                this.ms.loaded = true;
            } catch (e) {
                this.ms.loaded = true;
                if (window.showToast) window.showToast('خطا در بارگذاری موارد بدون سئو', 'error');
            }
        },
        msChecklist() {
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
        msSetCheck(row, id, status) {
            const item = (row.checklist || []).find(c => c.id === id);
            if (item) item.status = status;
        },
        async msCallTask(task, payload) {
            const fd = new FormData();
            fd.append('action', 'bankai_ai_seo_task');
            fd.append('nonce', this.msNonce());
            fd.append('task', task);
            fd.append('provider', this.ms.textProvider || '');
            Object.keys(payload || {}).forEach(k => {
                const v = payload[k];
                if (v == null) return;
                fd.append(k, typeof v === 'object' ? JSON.stringify(v) : String(v));
            });
            const r = await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
            const j = await r.json();
            if (!j || !j.success) throw new Error((j && j.data && j.data.message) || ('خطا در ' + task));
            return j.data || {};
        },
        async msContext(postId) {
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_post_context');
            fd.append('nonce', this.msNonce());
            fd.append('post_id', String(postId));
            const r = await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
            const j = await r.json();
            if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'خطا در دریافت محتوا');
            return j.data || {};
        },
        async msRunRow(row) {
            row.aiPhase = 'running';
            row.aiProgress = 0;
            row.aiError = '';
            row.checklist = this.msChecklist();
            row.draft = { seo_title: '', description: '', focus_keyword: '', keywords: [], schema: '' };
            row.saving = false;
            const steps = [
                { id: 'analyze', pct: 8, text: 'گام ۱/۸: تحلیل محتوا…' },
                { id: 'meta', pct: 18, text: 'گام ۲/۸: تولید عنوان و متا…' },
                { id: 'keywords', pct: 28, text: 'گام ۳/۸: کلیدواژه‌ها…' },
                { id: 'links', pct: 40, text: 'گام ۴/۸: لینک‌سازی داخلی…' },
                { id: 'images', pct: 52, text: 'گام ۵/۸: Alt تصاویر…' },
                { id: 'optimize', pct: 70, text: 'گام ۶/۸: بهینه‌سازی تصاویر…' },
                { id: 'schema', pct: 85, text: 'گام ۷/۸: اسکیما…' },
                { id: 'terms', pct: 97, text: 'گام ۸/۸: دسته و برچسب…' }
            ];
            const ctx = await this.msContext(row.id);
            for (const step of steps) {
                if (this.ms.aiAbort) { row.aiPhase = 'cancelled'; row.aiStepText = 'متوقف شد'; return; }
                row.aiStepText = step.text;
                row.aiProgress = step.pct;
                this.msSetCheck(row, step.id, 'loading');
                try {
                    if (step.id === 'analyze') {
                        await new Promise(r => setTimeout(r, 150));
                        this.msSetCheck(row, 'analyze', 'success');
                    } else if (step.id === 'meta') {
                        const basePayload = {
                            title: ctx.title || row.title || '',
                            content: ctx.content || '',
                            focus_keyword: ctx.focus_keyword || '',
                            locale: 'fa_IR'
                        };
                        // همان taskهای معتبر ادیتور: meta_title + meta_description
                        const [tRes, dRes] = await Promise.all([
                            this.msCallTask('meta_title', basePayload),
                            this.msCallTask('meta_description', basePayload)
                        ]);
                        row.draft.seo_title = (tRes.seo_title || tRes.title || tRes.text || '').toString().trim();
                        row.draft.description = (dRes.description || dRes.meta_description || dRes.text || '').toString().trim();
                        if (!row.draft.focus_keyword && (ctx.focus_keyword || '')) {
                            row.draft.focus_keyword = ctx.focus_keyword;
                        }
                        if (!row.draft.seo_title && !row.draft.description) {
                            throw new Error('عنوان یا متا خالی برگشت');
                        }
                        this.msSetCheck(row, 'meta', 'success');
                    } else if (step.id === 'keywords') {
                        const kw = await this.msCallTask('keywords', {
                            title: ctx.title || row.title || '',
                            content: ctx.content || '',
                            focus_keyword: row.draft.focus_keyword || ctx.focus_keyword || '',
                            locale: 'fa_IR'
                        });
                        let list = kw.keywords || kw.list || [];
                        if (typeof list === 'string') {
                            try { list = JSON.parse(list); } catch (e) { list = list.split(/[,،\n]+/); }
                        }
                        if (!Array.isArray(list)) list = [];
                        list = list.map(v => String(v || '').trim()).filter(Boolean);
                        row.draft.keywords = list.filter((v, i, a) => a.indexOf(v) === i);
                        if (!row.draft.focus_keyword && list[0]) row.draft.focus_keyword = list[0];
                        this.msSetCheck(row, 'keywords', 'success');
                    } else if (step.id === 'links') {
                        try {
                            const articles = ctx.related || ctx.articles || [];
                            await this.msCallTask('smart_internal_links', {
                                title: ctx.title || row.title || '',
                                content: ctx.content || '',
                                focus_keyword: row.draft.focus_keyword || '',
                                articles_data: typeof articles === 'string' ? articles : JSON.stringify(articles),
                                locale: 'fa_IR'
                            });
                            this.msSetCheck(row, 'links', 'success');
                        } catch (e) { this.msSetCheck(row, 'links', 'error'); }
                    } else if (step.id === 'images') {
                        const imgs = ctx.images || [];
                        for (let i = 0; i < Math.min(imgs.length, 5); i++) {
                            const img = imgs[i];
                            if (img.alt && String(img.alt).trim()) continue;
                            try {
                                await this.msCallTask('image_alt_text', {
                                    title: ctx.title || row.title || '',
                                    content: ctx.content || '',
                                    image_url: img.src || img.url || '',
                                    image_context: (ctx.title || '') + ' ' + (row.draft.focus_keyword || ''),
                                    locale: 'fa_IR'
                                });
                            } catch (e) {}
                        }
                        this.msSetCheck(row, 'images', 'success');
                    } else if (step.id === 'optimize') {
                        try {
                            const fd = new FormData();
                            fd.append('action', 'bankai_optimize_post_images');
                            fd.append('nonce', this.msNonce());
                            fd.append('post_id', String(row.id));
                            await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
                            this.msSetCheck(row, 'optimize', 'success');
                        } catch (e) { this.msSetCheck(row, 'optimize', 'error'); }
                    } else if (step.id === 'schema') {
                        row.draft.schema = JSON.stringify({
                            '@context': 'https://schema.org', '@type': 'Article',
                            headline: row.draft.seo_title || ctx.title || row.title,
                            description: row.draft.description || '',
                            mainEntityOfPage: ctx.permalink || '',
                            keywords: (row.draft.keywords || []).join(', ')
                        }, null, 2);
                        this.msSetCheck(row, 'schema', 'success');
                    } else if (step.id === 'terms') {
                        try {
                            const fd = new FormData();
                            fd.append('action', 'bankai_ai_assign_terms');
                            fd.append('nonce', this.msNonce());
                            fd.append('post_id', String(row.id));
                            fd.append('focus', row.draft.focus_keyword || '');
                            fd.append('keywords', JSON.stringify(row.draft.keywords || []));
                            await fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd });
                            this.msSetCheck(row, 'terms', 'success');
                        } catch (e) { this.msSetCheck(row, 'terms', 'error'); }
                    }
                } catch (err) {
                    this.msSetCheck(row, step.id, 'error');
                    if (step.id === 'meta' || step.id === 'keywords') {
                        row.aiPhase = 'error';
                        row.aiError = (err && err.message) || 'خطا';
                        throw err;
                    }
                }
            }
            row.aiProgress = 100;
            row.aiStepText = 'آماده تأیید و ذخیره';
            row.aiPhase = 'review';
        },
        async runMissingSeoBatch() {
            const ids = (this.ms.selected || []).slice();
            if (!ids.length) { if (window.showToast) window.showToast('هیچ مقاله‌ای انتخاب نشده', 'error'); return; }
            if (!this.ms.textProvider) { if (window.showToast) window.showToast('موتور AI متنی را انتخاب کنید', 'error'); return; }
            this.ms.aiRunning = true;
            this.ms.aiAbort = false;
            this.ms.aiProgress = 0;
            let done = 0, failed = 0;
            for (let i = 0; i < ids.length; i++) {
                if (this.ms.aiAbort) break;
                const row = (this.ms.items || []).find(r => String(r.id) === String(ids[i]));
                if (!row) continue;
                try {
                    await this.msRunRow(row);
                    if (row.aiPhase === 'review') done++;
                    else if (row.aiPhase === 'error') failed++;
                } catch (e) {
                    failed++;
                    row.aiPhase = 'error';
                    row.aiError = (e && e.message) || 'خطا';
                }
                this.ms.aiProgress = Math.round(((i + 1) / ids.length) * 100);
            }
            this.ms.aiRunning = false;
            if (window.showToast) {
                window.showToast(this.ms.aiAbort ? ('متوقف شد — موفق: ' + done) : ('پایان صف — آماده تأیید: ' + done + ' · خطا: ' + failed), done ? 'success' : 'error');
            }
        },
        msApprove(row) {
            if (!row || !row.draft) return;
            row.saving = true;
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_apply_draft');
            fd.append('nonce', this.msNonce());
            fd.append('post_id', String(row.id));
            fd.append('seo_title', row.draft.seo_title || '');
            fd.append('description', row.draft.description || '');
            fd.append('focus_keyword', row.draft.focus_keyword || '');
            fd.append('keywords', JSON.stringify(row.draft.keywords || []));
            fd.append('schema', row.draft.schema || '');
            fetch(this.msAjax(), { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(j => {
                    row.saving = false;
                    if (j && j.success) {
                        row.aiPhase = 'done';
                        row.score = (j.data && j.data.score) || row.score || 0;
                        if (window.showToast) window.showToast('ذخیره شد: ' + (row.title || ''), 'success');
                    } else if (window.showToast) window.showToast((j && j.data && j.data.message) || 'خطا در ذخیره', 'error');
                })
                .catch(() => { row.saving = false; if (window.showToast) window.showToast('خطا در ذخیره', 'error'); });
        },
        msCancel(row) {
            if (!row) return;
            row.aiPhase = 'cancelled';
            row.draft = null;
            row.checklist = [];
            row.aiProgress = 0;
            row.aiStepText = '';
        },
        async msRerun(row) {
            if (!row || this.ms.aiRunning) return;
            this.ms.aiRunning = true;
            this.ms.aiAbort = false;
            try { await this.msRunRow(row); } catch (e) { row.aiPhase = 'error'; row.aiError = (e && e.message) || 'خطا'; }
            this.ms.aiRunning = false;
        },
        msApproveAll() {
            const rows = (this.ms.items || []).filter(r => r.aiPhase === 'review' && r.draft);
            rows.forEach((row, i) => setTimeout(() => this.msApprove(row), i * 350));
            if (window.showToast) window.showToast('تأیید گروهی برای ' + rows.length + ' مقاله', 'success');
        },

        initWizFromQuery() {
            const params = new URLSearchParams(window.location.search);
            const sub = params.get('subtab') || '';
            if (sub === 'generate' || sub === 'ai-generate') {
                this.panel = 'generate';
                this.wizStep = 1;
            }
            // Providers from localized or fetch status via existing options
            const cfg = window.bankaiCoreData || {};
            if (Array.isArray(cfg.providers) && cfg.providers.length) {
                this.wizProviders = cfg.providers;
            } else if (Array.isArray(this.providers) && this.providers.length) {
                this.wizProviders = this.providers;
            }
            // PHP-injected fallback
            const el = document.getElementById('bk-wiz-providers-json');
            if (el && el.textContent) {
                try { this.wizProviders = JSON.parse(el.textContent); } catch (e) {}
            }
            if (!this.wiz.provider && this.wizProviders.length) {
                const ready = this.wizProviders.find(p => p.has_key) || this.wizProviders[0];
                this.wiz.provider = ready.id || '';
            }
        },
        jobStatusLabel(st) {
            return ({ pending:'در صف', run:'در حال اجرا', done:'انجام شد', error:'خطا' })[st] || st;
        },
        async suggestTitles() {
            if (!this.wiz.topic.trim()) { alert('موضوع را وارد کنید'); return; }
            this.wizBusy = true;
            this.wiz.globalProgress = 10;
            this.wiz.globalLabel = 'دریافت پیشنهاد عنوان…';
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_ai_suggest_titles');
                fd.append('nonce', window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || '');
                fd.append('topic', this.wiz.topic);
                fd.append('count', String(this.wiz.count || 10));
                fd.append('category', this.wiz.category || '');
                fd.append('provider', this.wiz.provider || '');
                const r = await fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' });
                const j = await r.json();
                if (j.success && j.data && j.data.titles) {
                    this.wiz.suggestions = j.data.titles;
                    this.wiz.selected = j.data.titles.map((title) => ({
                        title,
                        length: this.wiz.defaultLength || '3000',
                        notes: this.wiz.defaultNotes || '',
                        approved: true,
                        pubStatus: 'draft',
                        schedule_at: '',
                        jyear: null, jmonth: null, jday: null,
                        hour12: 10, minute: 0, ampm: 'AM'
                    }));
                    this.wiz.phase = 'titles';
                } else {
                    alert((j.data && j.data.message) || 'پیشنهادی دریافت نشد');
                }
            } catch (e) {
                alert('خطا در ارتباط');
            } finally {
                this.wizBusy = false;
                this.wiz.globalProgress = 0;
            }
        },
        addManualTitle() {
            const t = prompt('عنوان مقاله را وارد کنید');
            if (!t || !t.trim()) return;
            this.wiz.selected.push({
                title: t.trim(),
                length: this.wiz.defaultLength || '3000',
                notes: this.wiz.defaultNotes || '',
                approved: true,
                pubStatus: 'draft',
                schedule_at: '',
                jyear: null, jmonth: null, jday: null,
                hour12: 10, minute: 0, ampm: 'AM'
            });
            this.wiz.phase = 'titles';
        },
        toggleSuggestion(title, on) {
            if (on) {
                if (!this.wiz.selected.some(s => s.title === title)) {
                    this.wiz.selected.push({
                        title,
                        length: this.wiz.defaultLength || '3000',
                        notes: this.wiz.defaultNotes || ''
                    });
                }
            } else {
                this.wiz.selected = this.wiz.selected.filter(s => s.title !== title);
            }
        },
        prepareQueue() {
            const gap = Math.max(1, parseInt(this.wiz.gapHours || 24, 10));
            const base = Date.now() + gap * 3600 * 1000;
            this.wiz.queue = this.wiz.selected.map((s, i) => {
                let schedule_at = '';
                if (this.wiz.status === 'future') {
                    const d = new Date(base + i * gap * 3600 * 1000);
                    schedule_at = d.toISOString().slice(0, 19).replace('T', ' ');
                }
                return {
                    title: s.title,
                    focus: this.wiz.topic || '',
                    length: s.length || this.wiz.defaultLength || '3000',
                    notes: (s.notes || this.wiz.defaultNotes || ''),
                    status: 'pending',
                    progress: 0,
                    stepLabel: 'آماده',
                    message: '',
                    post_id: 0,
                    schedule_at,
                    pubStatus: this.wiz.status || 'draft',
                    provider: this.wiz.provider || ''
                };
            });
        },

        startProductionFromCards() {
            const approved = this.wiz.selected.filter(s => s.approved && s.title && s.title.trim());
            if (!approved.length) { alert('حداقل یک عنوان را تأیید کنید'); return; }
            this.wiz.queue = approved.map((s) => {
                let schedule_at = s.schedule_at || '';
                if (s.pubStatus === 'future' && !schedule_at && s.jyear) {
                    schedule_at = this.jalaliDraftToGregorianString(s) || '';
                }
                return {
                    title: s.title.trim(),
                    focus: this.wiz.topic || '',
                    length: s.length || this.wiz.defaultLength || '3000',
                    notes: s.notes || this.wiz.defaultNotes || '',
                    status: 'pending',
                    progress: 0,
                    stepLabel: 'آماده',
                    message: '',
                    post_id: 0,
                    schedule_at,
                    pubStatus: s.pubStatus || 'draft',
                    provider: this.wiz.provider || ''
                };
            });
            this.wiz.phase = 'produce';
            this.runBatchQueue();
        },
        async runBatchQueue() {
            if (this.batchBusy || !this.wiz.queue.length) return;
            this.batchBusy = true;
            for (let i = 0; i < this.wiz.queue.length; i++) {
                const job = this.wiz.queue[i];
                if (job.status === 'done') continue;
                job.status = 'run';
                job.progress = 8;
                job.stepLabel = 'گام ۱/۶: تولید محتوا…';
                job.message = '';
                this.wiz.globalLabel = 'تولید: ' + job.title;
                this.wiz.globalProgress = Math.round((i / this.wiz.queue.length) * 100);
                try {
                    const fd = new FormData();
                    fd.append('action', 'bankai_ai_generate_article');
                    fd.append('nonce', window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || '');
                    fd.append('topic', job.title);
                    fd.append('focus', job.focus || this.wiz.topic || '');
                    fd.append('length', String(job.length || 3000));
                    fd.append('notes', job.notes || '');
                    fd.append('status', job.pubStatus || 'draft');
                    fd.append('do_seo', '1');
                    fd.append('provider', job.provider || this.wiz.provider || '');
                    if (job.schedule_at) fd.append('schedule_at', job.schedule_at);
                    // Fake progressive UI while waiting
                    const tick = setInterval(() => {
                        if (job.progress < 88) {
                            job.progress += 2;
                            if (job.progress < 20) job.stepLabel = 'گام ۱/۶: تولید محتوا…';
                            else if (job.progress < 35) job.stepLabel = 'گام ۲/۶: تولید عنوان سئو، متادیسکریپشن و کلیدواژه‌ها…';
                            else if (job.progress < 50) job.stepLabel = 'گام ۳/۶: استخراج کلمات کلیدی + ادغام ثابت‌ها…';
                            else if (job.progress < 65) job.stepLabel = 'گام ۴/۶: لینک‌سازی هوشمند داخلی…';
                            else if (job.progress < 80) job.stepLabel = 'گام ۵/۶: سئوی تصاویر و Alt…';
                            else job.stepLabel = 'گام ۶/۶: دسته و برچسب خودکار…';
                        }
                    }, 500);
                    const r = await fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' });
                    clearInterval(tick);
                    const j = await r.json();
                    if (j.success) {
                        job.progress = 100;
                        job.status = 'done';
                        job.stepLabel = 'تکمیل شد';
                        job.post_id = (j.data && j.data.post_id) || 0;
                        job.message = (j.data && j.data.message) || 'موفق';
                        if (j.data && j.data.queue) this.queue = j.data.queue;
                        this.wiz.globalProgress = Math.round(((i + 1) / this.wiz.queue.length) * 100);
                    } else {
                        job.status = 'error';
                        job.stepLabel = 'خطا';
                        job.message = (j.data && j.data.message) || 'ناموفق';
                        this.wiz.paused = true;
                        this.wiz.pauseMessage = 'خطا در «' + job.title + '»: ' + job.message;
                        this.batchBusy = false;
                        this.wiz.globalLabel = 'متوقف شد — ' + job.message;
                        return;
                    }
                } catch (e) {
                    job.status = 'error';
                    job.message = (e && e.message) || 'خطای شبکه';
                    this.wiz.paused = true;
                    this.wiz.pauseMessage = 'خطا در «' + job.title + '»: ' + job.message;
                    this.batchBusy = false;
                    this.wiz.globalLabel = 'متوقف شد — ' + job.message;
                    return;
                }
            }
            this.batchBusy = false;
            this.wiz.paused = false;
            this.wiz.globalProgress = 100;
            const doneCount = (this.wiz.queue || []).filter(j => j.status === 'done').length;
            const errCount = (this.wiz.queue || []).filter(j => j.status === 'error').length;
            this.wiz.globalLabel = 'صف تولید تکمیل شد';
            this.loadArticles && this.loadArticles();
            // بازگشت به فرم اصلی
            this.wiz.phase = 'setup';
            this.wiz.selected = [];
            this.wiz.suggestions = [];
            this.wiz.queue = [];
            if (window.showToast) {
                window.showToast(
                    doneCount
                        ? (doneCount + ' مقاله با موفقیت تولید شد' + (errCount ? (' · ' + errCount + ' خطا') : ''))
                        : 'هیچ مقاله‌ای تولید نشد',
                    doneCount ? 'success' : 'error'
                );
            }
        },

        async continueBatchAfterCheck() {
            if (this.batchBusy || this.wiz.checkingConn) return;
            this.wiz.checkingConn = true;
            this.wiz.globalLabel = 'بررسی اتصال موتور AI…';
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_ai_test_connection');
                fd.append('nonce', window.bankaiAdminNonce || (window.bankaiCoreData && bankaiCoreData.adminNonce) || '');
                fd.append('provider', this.wiz.provider || '');
                const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                    method: 'POST', body: fd, credentials: 'same-origin'
                });
                const j = await r.json().catch(() => ({}));
                if (!j || !j.success) {
                    const msg = (j && j.data && j.data.message) || 'اتصال برقرار نیست. کلید API و موتور را بررسی کنید.';
                    this.wiz.pauseMessage = msg;
                    alert(msg);
                    return;
                }
                // Reset errored job to pending so it retries
                this.wiz.queue.forEach((job) => {
                    if (job.status === 'error') {
                        job.status = 'pending';
                        job.progress = 0;
                        job.stepLabel = 'آماده برای تلاش مجدد';
                        job.message = '';
                    }
                });
                this.wiz.paused = false;
                this.wiz.pauseMessage = '';
                await this.runBatchQueue();
            } catch (e) {
                const msg = 'بررسی اتصال ناموفق بود: ' + ((e && e.message) || 'شبکه');
                this.wiz.pauseMessage = msg;
                alert(msg);
            } finally {
                this.wiz.checkingConn = false;
            }
        },

        async generateArticle() {
            if (!this.gen.topic.trim()) { this.genMessage = 'موضوع را وارد کنید'; return; }
            this.genBusy = true;
            this.genMessage = '';
            this.genProgress = 5;
            this.genStepLabel = 'آماده‌سازی…';
            const tick = setInterval(() => {
                if (this.genProgress < 35) {
                    this.genProgress += 2;
                    this.genStepLabel = 'تولید محتوا توسط AI…';
                }
            }, 800);
            try {
                const fd = new FormData();
                fd.append('action', 'bankai_ai_generate_article');
                fd.append('nonce', this.adminNonce());
                fd.append('topic', this.gen.topic);
                fd.append('focus', this.gen.focus);
                fd.append('length', this.gen.length);
                fd.append('status', this.gen.status);
                fd.append('schedule_at', this.scheduleIso());
                fd.append('notes', this.gen.notes || '');
                if (this.gen.do_seo) fd.append('do_seo', '1');
                const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                    method: 'POST', body: fd, credentials: 'same-origin'
                });
                clearInterval(tick);
                this.genProgress = this.gen.do_seo ? 55 : 85;
                this.genStepLabel = this.gen.do_seo ? 'تکمیل سئو…' : 'ذخیره…';
                const j = await r.json();
                if (j.success) {
                    this.genProgress = 100;
                    this.genStepLabel = 'تمام شد';
                    this.genMessage = (j.data && j.data.message) || 'مقاله ساخته شد';
                    if (j.data && j.data.queue) this.queue = j.data.queue;
                    const pid = j.data && j.data.post_id;
                    if (pid) {
                        /* openWorkspace removed — use WP editor */
                    } else if (this.gen.status === 'future') {
                        this.panel = 'schedule';
                    } else {
                        this.loadArticles();
                    }
                } else {
                    this.genMessage = (j.data && j.data.message) || j.message || 'خطا';
                    this.genProgress = 0;
                }
            } catch (e) {
                clearInterval(tick);
                this.genMessage = 'خطای ارتباط';
                this.genProgress = 0;
            } finally {
                this.genBusy = false;
            }
        },
        async removeQueueItem(idx) {
            const fd = new FormData();
            fd.append('action', 'bankai_ai_remove_queue_item');
            fd.append('nonce', this.adminNonce());
            fd.append('index', String(idx));
            const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                method: 'POST', body: fd, credentials: 'same-origin'
            });
            const j = await r.json();
            if (j.success && j.data && j.data.queue) this.queue = j.data.queue;
            else this.queue.splice(idx, 1);
        },
        editorId: 'bk_art_editor',
        editorReady: true,
        editorLoading: false,
        htmlMode: false,
        editorAdvOpen: false,
        editorPrefs: { pastePlain: false, autoDir: true, fontSize: '15px' },
        getEditorContent() {
            if (this.htmlMode) {
                const ta = document.getElementById('bk_art_editor_html');
                return ta ? ta.value : (this.ws.content || '');
            }
            const el = document.getElementById(this.editorId);
            return el ? el.innerHTML : (this.ws.content || '');
        },
        setEditorContent(html) {
            const val = html || '';
            this.ws.content = val;
            const el = document.getElementById(this.editorId);
            if (el) el.innerHTML = val;
            const ta = document.getElementById('bk_art_editor_html');
            if (ta) ta.value = val;
            this.editorReady = true;
        },
        onEditorInput() {
            this.ws.content = this.getEditorContent();
        },
        destroyEditor() {
            this.htmlMode = false;
            const el = document.getElementById(this.editorId);
            const ta = document.getElementById('bk_art_editor_html');
            if (el) el.style.display = '';
            if (ta) ta.style.display = 'none';
        },
        initRichEditor() {
            const self = this;
            const el = document.getElementById(this.editorId);
            if (!el) return;
            this.setEditorContent(this.ws.content || '');
            this.editorReady = true;
            this.applyEditorPrefs();
            const wrap = el.closest('.bk-lite-editor') || el.parentElement;
            const bar = wrap && wrap.querySelector('.bk-lite-toolbar');
            if (bar && !bar.dataset.bound) {
                bar.dataset.bound = '1';
                bar.addEventListener('click', function (ev) {
                    const btn = ev.target.closest('button');
                    if (!btn) return;
                    ev.preventDefault();
                    if (btn.dataset.mode === 'html') { self.toggleHtmlMode(); return; }
                    if (self.htmlMode) return;
                    const cmd = btn.dataset.cmd;
                    if (cmd === 'openAdv') { self.editorAdvOpen = !self.editorAdvOpen; return; }
                    if (cmd === 'openMediaPanel') {
                        self.ws.tab = 'media';
                        self.scanWsImages();
                        return;
                    }
                    if (cmd === 'insertGallery') { self.insertGalleryFromMedia(); return; }
                    if (cmd === 'insertVideo') { self.insertVideoEmbed(); return; }
                    if (cmd === 'insertImage') { self.insertImageFromMedia(); return; }
                    if (cmd === 'createLink') {
                        el.focus();
                        const url = window.prompt('آدرس لینک:', 'https://');
                        if (url) document.execCommand('createLink', false, url);
                        self.onEditorInput();
                        return;
                    }
                    if (cmd === 'formatBlock') return;
                    el.focus();
                    if (cmd) document.execCommand(cmd, false, null);
                    self.onEditorInput();
                });
                bar.addEventListener('change', function (ev) {
                    const t = ev.target;
                    if (t.matches('select[data-cmd="formatBlock"]') && t.value) {
                        el.focus();
                        document.execCommand('formatBlock', false, t.value);
                        t.value = '';
                        self.onEditorInput();
                    }
                    if (t.matches('input[type="color"][data-cmd]')) {
                        el.focus();
                        const c = t.dataset.cmd;
                        if (c === 'hiliteColor') {
                            try { document.execCommand('hiliteColor', false, t.value); }
                            catch (e) { document.execCommand('backColor', false, t.value); }
                        } else {
                            document.execCommand(c, false, t.value);
                        }
                        self.onEditorInput();
                    }
                });
            }
            if (!el.dataset.bound) {
                el.dataset.bound = '1';
                el.addEventListener('input', function () { self.onEditorInput(); });
                el.addEventListener('blur', function () { self.onEditorInput(); });
                el.addEventListener('paste', function (ev) {
                    if (!self.editorPrefs.pastePlain) return;
                    ev.preventDefault();
                    const text = (ev.clipboardData || window.clipboardData).getData('text/plain');
                    document.execCommand('insertText', false, text);
                    self.onEditorInput();
                });
            }
        },
        toggleHtmlMode() {
            const el = document.getElementById(this.editorId);
            const ta = document.getElementById('bk_art_editor_html');
            if (!el || !ta) return;
            if (!this.htmlMode) {
                ta.value = el.innerHTML;
                el.style.display = 'none';
                ta.style.display = 'block';
                this.htmlMode = true;
            } else {
                el.innerHTML = ta.value;
                this.ws.content = ta.value;
                ta.style.display = 'none';
                el.style.display = '';
                this.htmlMode = false;
            }
        },
        insertImageFromMedia() {
            const self = this;
            if (!window.wp || !wp.media) {
                const url = window.prompt('آدرس تصویر:', 'https://');
                if (!url) return;
                document.execCommand('insertHTML', false, '<p><img src="' + url + '" alt=""></p>');
                self.onEditorInput();
                return;
            }
            const frame = wp.media({ title: 'درج تصویر', button: { text: 'درج' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                const src = att.url || '';
                const alt = (att.alt || att.title || '').replace(/"/g, '&quot;');
                const el = document.getElementById(self.editorId);
                if (el) el.focus();
                document.execCommand('insertHTML', false, '<p><img src="' + src + '" alt="' + alt + '"></p>');
                self.onEditorInput();
                self.scanWsImages();
            });
            frame.open();
        },
        syncEditorFromModel() {
            this.setEditorContent(this.ws.content || '');
        },
        openWorkspace(row, tab) {
            const id = row && (row.id || row.post_id);
            if (id) window.location.href = this.wpEditUrl(id);
        },
        wpEditUrl(id) {
            if (!id) return '#';
            const base = (window.bankaiCoreData && bankaiCoreData.adminUrl)
                || (typeof ajaxurl !== 'undefined' ? ajaxurl.replace(/admin-ajax\.php.*/, '') : '/wp-admin/');
            return base + 'post.php?post=' + id + '&action=edit';
        },
        cancelWizardProcess() {
            this.batchBusy = false;
            this.wizBusy = false;
            this.wiz.paused = false;
            this.wiz.pauseMessage = '';
            this.wiz.checkingConn = false;
            this.wiz.queue = [];
            this.wiz.selected = [];
            this.wiz.suggestions = [];
            this.wiz.globalProgress = 0;
            this.wiz.globalLabel = '';
            this.wiz.phase = 'setup';
            this.panel = 'generate';
        },
        openWizItemSchedule(idx) {
            this.wizSchedIdx = idx;
            const item = this.wiz.selected[idx];
            if (!item) return;
            // seed draft from item or now
            if (item.jyear) {
                this.wizSchedDraft = {
                    jyear: item.jyear, jmonth: item.jmonth, jday: item.jday,
                    hour12: item.hour12 || 10, minute: item.minute || 0, ampm: item.ampm || 'AM'
                };
            } else {
                try { this.initScheduleFromNow(); } catch (e) {}
                this.wizSchedDraft = {
                    jyear: this.gen.jyear, jmonth: this.gen.jmonth, jday: this.gen.jday,
                    hour12: this.gen.hour12, minute: this.gen.minute, ampm: this.gen.ampm
                };
            }
            this.wizItemSchedOpen = true;
            this.$nextTick(() => {
                try {
                    const pop = this.$refs.wizItemSchedPop;
                    if (!pop) return;
                    // center-ish fixed
                    pop.style.position = 'fixed';
                    pop.style.top = '20vh';
                    pop.style.left = '50%';
                    pop.style.transform = 'translateX(-50%)';
                } catch (e) {}
            });
        },
        setWizItemScheduleNow() {
            try { this.initScheduleFromNow(); } catch (e) {}
            this.wizSchedDraft = {
                jyear: this.gen.jyear, jmonth: this.gen.jmonth, jday: this.gen.jday,
                hour12: this.gen.hour12, minute: this.gen.minute, ampm: this.gen.ampm
            };
        },
        applyWizItemSchedule() {
            const idx = this.wizSchedIdx;
            if (idx < 0 || !this.wiz.selected[idx]) return;
            const d = this.wizSchedDraft;
            const item = this.wiz.selected[idx];
            item.jyear = d.jyear; item.jmonth = d.jmonth; item.jday = d.jday;
            item.hour12 = d.hour12; item.minute = d.minute; item.ampm = d.ampm;
            // Convert Jalali → Gregorian schedule_at for WP
            item.schedule_at = this.jalaliDraftToGregorianString(d);
            this.wizItemSchedOpen = false;
        },
        jalaliDraftToGregorianString(d) {
            // Prefer existing project helpers if present
            if (typeof this.jalaliToGregorian === 'function') {
                try {
                    const g = this.jalaliToGregorian(d.jyear, d.jmonth, d.jday);
                    let h = parseInt(d.hour12, 10) || 12;
                    const min = Math.max(0, Math.min(59, parseInt(d.minute, 10) || 0));
                    if (d.ampm === 'PM' && h < 12) h += 12;
                    if (d.ampm === 'AM' && h === 12) h = 0;
                    const pad = (n) => String(n).padStart(2, '0');
                    return g.y + '-' + pad(g.m) + '-' + pad(g.d) + ' ' + pad(h) + ':' + pad(min) + ':00';
                } catch (e) {}
            }
            // Fallback: BankaiJdp or simple approximation via known gen sync
            if (typeof this.j2g === 'function') {
                const g = this.j2g(d.jyear, d.jmonth, d.jday);
                let h = parseInt(d.hour12, 10) || 12;
                const min = Math.max(0, Math.min(59, parseInt(d.minute, 10) || 0));
                if (d.ampm === 'PM' && h < 12) h += 12;
                if (d.ampm === 'AM' && h === 12) h = 0;
                const pad = (n) => String(n).padStart(2, '0');
                return g[0] + '-' + pad(g[1]) + '-' + pad(g[2]) + ' ' + pad(h) + ':' + pad(min) + ':00';
            }
            // Last resort: use gen.schedule fields if synced
            return '';
        },
        wizItemScheduleLabel(item) {
            if (!item) return 'انتخاب تاریخ';
            if (item.jyear && item.jmonth && item.jday) {
                const mname = this.jMonths[(item.jmonth || 1) - 1] || '';
                let h = item.hour12 || 10;
                const min = String(item.minute || 0).padStart(2, '0');
                const ap = item.ampm === 'PM' ? 'ب.ظ' : 'ق.ظ';
                return item.jday + ' ' + mname + ' ' + item.jyear + ' — ' + h + ':' + min + ' ' + ap;
            }
            if (item.schedule_at) return item.schedule_at;
            return 'انتخاب تاریخ جلالی';
        },

        closeWorkspace() { this.ws.open = false; },
        async saveWorkspace() {
            if (!this.ws.id) return;
            this.onEditorInput();
            this.ws.saving = true;
            try {
                const keywords = (this.ws.keywordList && this.ws.keywordList.length)
                    ? this.ws.keywordList.slice()
                    : String(this.ws.keywords_str || '').split(/[,،]+/).map(s => s.trim()).filter(Boolean);
                await fetch(this.restBase() + 'seo/' + this.ws.id, {
                    method: 'POST', credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({
                        seo_title: this.ws.seo_title,
                        description: this.ws.description,
                        focus_keyword: this.ws.focus_keyword,
                        keywords: keywords,
                        canonical: this.ws.canonical,
                        robots: { index: !!this.ws.robots_index, follow: !!this.ws.robots_follow },
                        schema: this.ws.schema,
                        og_title: this.ws.og_title,
                        og_description: this.ws.og_description,
                        og_image: this.ws.og_image,
                        x_title: this.ws.x_title,
                        x_description: this.ws.x_description,
                        x_image: this.ws.x_image
                    })
                });
                this.syncWsScheduleFromUi();
                const postBody = {
                    title: this.ws.title,
                    content: this.ws.content,
                    status: this.ws.status || 'draft'
                };
                if (this.ws.date_iso) {
                    postBody.date = this.ws.date_iso;
                }
                if (this.ws.cover_id) postBody.featured_media = this.ws.cover_id;
                if (!this.ws.cover) postBody.featured_media = 0;
                await fetch('/wp-json/wp/v2/posts/' + this.ws.id, {
                    method: 'POST', credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify(postBody)
                });
                await this.refreshScore();
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('ذخیره شد', 'success');
            } catch (e) {
                console.error(e);
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('خطا در ذخیره', 'error');
            } finally {
                this.ws.saving = false;
            }
        },
        async refreshScore() {
            if (!this.ws.id) return;
            this.onEditorInput();
            try {
                const r = await fetch(this.restBase() + 'seo/analyze/' + this.ws.id, {
                    method: 'POST', credentials: 'same-origin',
                    headers: this.restHeaders(true),
                    body: JSON.stringify({
                        content: this.ws.content,
                        seo_title: this.ws.seo_title,
                        description: this.ws.description,
                        focus_keyword: this.ws.focus_keyword
                    })
                });
                const j = await r.json();
                const an = j.analysis || (j.data && j.data.analysis) || j.data || j;
                if (an && an.score != null) this.ws.score = an.score;
                if (an && (an.groups || an.checks)) {
                    this.ws.analysis = {
                        score: an.score || 0,
                        passed: an.passed || 0,
                        total: an.total || 0,
                        groups: an.groups || {},
                        checks: an.checks || []
                    };
                }
            } catch (e) {}
        },
        addKeyword() {
            const parts = String(this.ws.kwDraft || '').split(/[,،]+/).map(s => s.trim()).filter(Boolean);
            parts.forEach(p => {
                if (!this.ws.keywordList.includes(p)) this.ws.keywordList.push(p);
            });
            this.ws.kwDraft = '';
            if (!this.ws.focus_keyword && this.ws.keywordList.length) this.ws.focus_keyword = this.ws.keywordList[0];
        },
        removeKeyword(i) {
            if (i >= 0 && i < this.ws.keywordList.length) this.ws.keywordList.splice(i, 1);
        },
        rebuildWsSchema() {
            const type = this.ws.schemaType || 'Article';
            const schema = {
                '@context': 'https://schema.org',
                '@type': type,
                headline: this.ws.seo_title || this.ws.title || '',
                description: this.ws.description || '',
                url: this.ws.canonical || ''
            };
            if (type === 'FAQPage') {
                schema.mainEntity = (this.ws.faqItems || []).filter(i => (i.q||'').trim()).map(i => ({
                    '@type': 'Question', name: i.q, acceptedAnswer: { '@type': 'Answer', text: i.a || '' }
                }));
            }
            this.ws.schema = JSON.stringify(schema, null, 2);
        },

        wsToast(msg, type) {
            if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast(msg, type || 'info');
            else if (window.bankaiEditorSeo && typeof bankaiEditorSeo.showToast === 'function') {}
            this.ws.aiMsg = msg;
        },
        afterWsLinkApplied(msg) {
            this.wsToast(msg || 'لینک ثبت شد', 'success');
            this.loadWsLinks();
            this.refreshScore();
        },
        formatBytes(n) {
            n = Number(n) || 0;
            if (n < 1024) return n + ' B';
            if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
            return (n / 1048576).toFixed(2) + ' MB';
        },
        findMatchesInContent(term) {
            term = String(term || '').trim();
            if (!term) return [];
            this.onEditorInput();
            const plain = String(this.ws.content || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
            const matches = [];
            const low = plain.toLowerCase();
            const t = term.toLowerCase();
            let from = 0;
            while (matches.length < 40) {
                const idx = low.indexOf(t, from);
                if (idx === -1) break;
                const start = Math.max(0, idx - 28);
                const end = Math.min(plain.length, idx + term.length + 28);
                const snippet = (start > 0 ? '…' : '') + plain.slice(start, end).trim() + (end < plain.length ? '…' : '');
                matches.push({ text: term, index: idx, snippet, selected: true, targetId: '' });
                from = idx + Math.max(1, term.length);
            }
            return matches;
        },
        insertLinkIntoEditor(keyword, url, title, nofollow, bold) {
            if (!url) return false;
            const kw = keyword || title || url;
            const rel = nofollow ? ' rel="nofollow noopener"' : ' rel="noopener"';
            const target = nofollow || /^https?:\/\//i.test(url) && url.indexOf(location.origin) !== 0 ? ' target="_blank"' : '';
            const inner = bold !== false ? ('<strong>' + kw + '</strong>') : kw;
            // Try replace first occurrence of plain keyword in content
            this.onEditorInput();
            let html = this.ws.content || '';
            const plain = kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const re = new RegExp('(?![^<]*>)(' + plain + ')', 'i');
            if (re.test(html.replace(/<a[\s\S]*?<\/a>/gi, ''))) {
                // only replace outside existing anchors - simple approach: replace first text occurrence
                let done = false;
                html = html.replace(new RegExp('(<a[\\s\\S]*?<\\/a>)|(' + plain + ')', 'i'), function (m, a, t) {
                    if (a) return a;
                    if (!done && t) {
                        done = true;
                        return '<a href="' + url + '"' + target + rel + '>' + inner + '</a>';
                    }
                    return m;
                });
                if (done) {
                    this.setEditorContent(html);
                    this.loadWsLinks();
                    return true;
                }
            }
            this.insertHtmlAtCursor('<a href="' + url + '"' + target + rel + '>' + inner + '</a>');
            this.loadWsLinks();
            return true;
        },
        async loadWsLinks() {
            if (!this.ws.id) {
                // local scan
                this.scanLinksLocal();
                return;
            }
            try {
                const r = await fetch(this.restBase() + 'seo/links/' + this.ws.id, {
                    credentials: 'same-origin', headers: this.restHeaders(false)
                });
                const j = await r.json();
                const data = (j && j.data) ? j.data : j;
                if (data && (data.internal || data.external)) {
                    this.ws.linkData = {
                        internal: Array.isArray(data.internal) ? data.internal : [],
                        external: Array.isArray(data.external) ? data.external : [],
                        counts: {
                            internal: Array.isArray(data.internal) ? data.internal.length : 0,
                            external: Array.isArray(data.external) ? data.external.length : 0
                        }
                    };
                    this.ws.linkStats = this.ws.linkData.counts;
                    return;
                }
            } catch (e) {}
            this.scanLinksLocal();
        },
        scanLinksLocal() {
            this.onEditorInput();
            const html = this.ws.content || '';
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const internal = [], external = [];
            const origin = location.origin || '';
            doc.querySelectorAll('a[href]').forEach((a) => {
                const href = a.getAttribute('href') || '';
                const item = { href, text: (a.textContent || '').trim(), anchor: (a.textContent || '').trim() };
                if (href.startsWith('/') || href.indexOf(origin) === 0 || href.indexOf('#') === 0) internal.push(item);
                else if (/^https?:\/\//i.test(href)) external.push(item);
            });
            this.ws.linkData = { internal, external, counts: { internal: internal.length, external: external.length } };
            this.ws.linkStats = this.ws.linkData.counts;
        },
        async runInternalSearch() {
            const term = (this.ws.linkAnchor || this.ws.focus_keyword || this.ws.linkQuery || '').trim();
            if (!term) {
                this.wsToast('کلیدواژه را وارد کنید', 'warn');
                return;
            }
            this.ws.linkAnchor = term;
            this.ws.linkBusy = true;
            this.ws.linkSearchLabel = 'در حال جستجو…';
            this.ws.linkStatusMsg = 'در حال جستجوی مقالات مرتبط…';
            this.ws.postResults = [];
            try {
                const r = await fetch(this.restBase() + 'seo/search-posts?s=' + encodeURIComponent(term) + '&post_id=' + (this.ws.id || 0), {
                    credentials: 'same-origin', headers: this.restHeaders(false)
                });
                let posts = [];
                if (r.ok) {
                    const j = await r.json();
                    posts = (j.data && (j.data.posts || j.data)) || j.posts || [];
                    if (!Array.isArray(posts)) posts = [];
                }
                if (!posts.length) {
                    const r2 = await fetch('/wp-json/wp/v2/posts?search=' + encodeURIComponent(term) + '&per_page=10', {
                        credentials: 'same-origin', headers: this.restHeaders(false)
                    });
                    const arr = await r2.json();
                    posts = (Array.isArray(arr) ? arr : []).map(p => ({
                        id: p.id,
                        title: p.title && p.title.rendered ? p.title.rendered.replace(/<[^>]+>/g, '') : '',
                        permalink: p.link
                    }));
                }
                try {
                    const r3 = await fetch(this.restBase() + 'seo/suggest-links', {
                        method: 'POST', credentials: 'same-origin', headers: this.restHeaders(true),
                        body: JSON.stringify({
                            post_id: this.ws.id,
                            keywords: [term].concat(this.ws.keywordList || []),
                            content: (this.ws.content || '').slice(0, 4000)
                        })
                    });
                    const j3 = await r3.json();
                    if (j3.success && j3.data && Array.isArray(j3.data.suggestions)) {
                        j3.data.suggestions.forEach(s => {
                            if (!posts.some(p => p.id === s.id)) {
                                posts.push({ id: s.id, title: s.title, permalink: s.permalink, match_reason: s.in_content ? 'کلیدواژه در محتوا' : 'مرتبط با سئو' });
                            }
                        });
                    }
                } catch (e2) {}
                this.ws.postResults = posts.filter(p => p.id !== this.ws.id).map(p => Object.assign({}, p, {
                    accepted: false, rejected: false,
                    match_reason: p.match_reason || ((p.title && String(p.title).toLowerCase().includes(term.toLowerCase())) ? 'تطابق عنوان' : 'جستجوی سایت')
                }));
                if (this.ws.postResults.length) {
                    this.ws.linkSearchLabel = this.ws.postResults.length + ' یافت شد';
                    this.ws.linkStatusMsg = this.ws.postResults.length + ' مقاله یافت شد — عنوان را تایید کنید';
                    this.wsToast(this.ws.postResults.length + ' مقاله یافت شد', 'success');
                } else {
                    this.ws.linkSearchLabel = 'یافت نشد';
                    this.ws.linkStatusMsg = 'مقاله مرتبطی پیدا نشد';
                    this.wsToast('مقاله مرتبطی یافت نشد', 'info');
                }
            } catch (e) {
                this.ws.postResults = [];
                this.ws.linkStatusMsg = 'خطا در جستجو';
                this.wsToast('خطا در جستجوی مقالات', 'error');
            } finally {
                this.ws.linkBusy = false;
                setTimeout(() => { this.ws.linkSearchLabel = 'جستجو'; }, 2000);
            }
        },
        acceptedInternalPosts() {
            return (this.ws.postResults || []).filter(p => p.accepted && !p.rejected);
        },
        acceptAllInternalPosts() {
            (this.ws.postResults || []).forEach(p => { p.accepted = true; p.rejected = false; });
        },
        loadInternalContentMatches() {
            const term = (this.ws.linkAnchor || this.ws.focus_keyword || '').trim();
            if (!term) {
                this.wsToast('کلیدواژه خالی است', 'warn');
                return;
            }
            if (!this.acceptedInternalPosts().length) {
                this.wsToast('ابتدا حداقل یک مقاله را تایید کنید', 'warn');
                return;
            }
            this.ws.contentMatchesInternal = this.findMatchesInContent(term);
            const posts = this.acceptedInternalPosts();
            this.ws.contentMatchesInternal.forEach((m, i) => {
                if (posts.length) {
                    m.targetId = String(posts[i % posts.length].id);
                    m.selected = true;
                }
            });
            if (this.ws.contentMatchesInternal.length) {
                this.ws.linkStatusMsg = this.ws.contentMatchesInternal.length + ' محل در متن یافت شد';
                this.wsToast(this.ws.contentMatchesInternal.length + ' تطابق در متن', 'success');
            } else {
                this.ws.linkStatusMsg = 'عبارتی در متن برای لینک یافت نشد';
                this.wsToast('عبارتی در متن یافت نشد', 'warn');
            }
        },
        confirmInternalLinking() {
            const selected = (this.ws.contentMatchesInternal || []).filter(m => m.selected && m.targetId);
            const posts = this.acceptedInternalPosts();
            if (!selected.length) {
                this.wsToast('ابتدا تطابق متن و مقاله هدف را انتخاب کنید', 'warn');
                return;
            }
            this.ws.linkApplyBusy = true;
            this.ws.linkStatusMsg = 'در حال ثبت لینک داخلی…';
            let applied = 0;
            selected.forEach(m => {
                const p = posts.find(x => String(x.id) === String(m.targetId)) || posts[0];
                if (!p || !p.permalink) return;
                if (this.insertLinkIntoEditor(m.text || this.ws.linkAnchor, p.permalink, p.title, false, true)) applied++;
            });
            this.ws.linkApplyBusy = false;
            if (applied) {
                this.ws.contentMatchesInternal = [];
                this.afterWsLinkApplied(applied + ' لینک داخلی ثبت شد');
            } else {
                this.wsToast('ثبت لینک داخلی ناموفق بود', 'error');
            }
        },
        applyInternalRandom() {
            const posts = this.acceptedInternalPosts();
            if (!posts.length) {
                this.wsToast('ابتدا مقالات را تایید کنید', 'warn');
                return;
            }
            if (!this.ws.contentMatchesInternal.length) this.loadInternalContentMatches();
            let applied = 0;
            (this.ws.contentMatchesInternal || []).filter(m => m.selected).forEach(m => {
                const p = posts[Math.floor(Math.random() * posts.length)];
                if (p && this.insertLinkIntoEditor(m.text || this.ws.linkAnchor, p.permalink, p.title, false, true)) applied++;
            });
            if (applied) {
                this.ws.contentMatchesInternal = [];
                this.afterWsLinkApplied(applied + ' لینک داخلی (رندوم) ثبت شد');
            } else {
                this.wsToast('لینک رندوم اعمال نشد', 'warn');
            }
        },
        loadExternalContentMatches() {
            const term = (this.ws.extAnchor || '').trim();
            if (!term) {
                this.wsToast('عبارت انکر را وارد کنید', 'warn');
                return;
            }
            this.ws.extFindBusy = true;
            this.ws.linkStatusMsg = 'جستجو در متن مقاله…';
            this.ws.contentMatchesExternal = this.findMatchesInContent(term);
            this.ws.extFindBusy = false;
            if (this.ws.contentMatchesExternal.length) {
                this.ws.linkStatusMsg = this.ws.contentMatchesExternal.length + ' تطابق در متن یافت شد';
                this.wsToast(this.ws.contentMatchesExternal.length + ' عبارت در متن یافت شد', 'success');
            } else {
                this.ws.linkStatusMsg = 'عبارتی در متن یافت نشد — می‌توانید مستقیم ثبت کنید';
                this.wsToast('عبارتی در متن یافت نشد', 'info');
            }
        },
        confirmExternalLinking() {
            if (!this.ws.extUrl) {
                this.wsToast('آدرس URL را وارد کنید', 'warn');
                return;
            }
            this.ws.extApplyBusy = true;
            this.ws.linkStatusMsg = 'در حال ثبت لینک خارجی…';
            const selected = (this.ws.contentMatchesExternal || []).filter(m => m.selected);
            let applied = 0;
            if (selected.length) {
                selected.forEach(m => {
                    if (this.insertLinkIntoEditor(m.text || this.ws.extAnchor, this.ws.extUrl, '', !!this.ws.extNofollow, true)) applied++;
                });
            } else {
                if (this.insertLinkIntoEditor(this.ws.extAnchor || this.ws.extUrl, this.ws.extUrl, '', !!this.ws.extNofollow, true)) applied++;
            }
            this.ws.extApplyBusy = false;
            if (applied) {
                this.ws.contentMatchesExternal = [];
                this.afterWsLinkApplied(applied + ' لینک خارجی ثبت شد');
            } else {
                this.wsToast('ثبت لینک خارجی ناموفق بود', 'error');
                this.ws.linkStatusMsg = 'ثبت ناموفق';
            }
        },
        removeLinkFromContent(link) {
            if (!link || !link.href) return;
            this.onEditorInput();
            let html = this.ws.content || '';
            try {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                let removed = 0;
                doc.querySelectorAll('a[href]').forEach(a => {
                    if ((a.getAttribute('href') || '') === link.href) {
                        const textNode = doc.createTextNode(a.textContent || '');
                        if (a.parentNode) { a.parentNode.replaceChild(textNode, a); removed++; }
                    }
                });
                if (removed) {
                    this.setEditorContent(doc.body ? doc.body.innerHTML : html);
                    this.afterWsLinkApplied('لینک حذف شد');
                } else {
                    this.wsToast('لینک در متن یافت نشد', 'warn');
                }
            } catch (e) {}
        },
        async aiAltForImages() {
            if (!(this.ws.postImages || []).length) this.scanWsImages();
            for (const img of (this.ws.postImages || [])) {
                if (img.has_alt && (img.alt || '').trim()) continue;
                this.ws.aiBusy = true;
                this.ws.aiProgressLabel = 'تولید Alt…';
                try {
                    const fd = new FormData();
                    fd.append('action', 'bankai_ai_seo_task');
                    fd.append('nonce', this.adminNonce());
                    fd.append('task', 'image_alt_text');
                    fd.append('title', this.ws.title || '');
                    fd.append('content', (this.ws.content || '').slice(0, 2000));
                    fd.append('focus_keyword', this.ws.focus_keyword || '');
                    fd.append('locale', 'fa_IR');
                    fd.append('image_url', img.src || '');
                    const r = await fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', { method: 'POST', body: fd, credentials: 'same-origin' });
                    const j = await r.json();
                    const alt = (j.data && (j.data.alt || j.data.text || j.data.image_alt)) || '';
                    if (alt) this.updateWsImgAlt(img, String(alt).trim());
                } catch (e) {}
            }
            this.ws.aiBusy = false;
        },


        applyEditorPrefs() {
            const el = document.getElementById(this.editorId);
            if (!el) return;
            el.style.fontSize = this.editorPrefs.fontSize || '15px';
            if (this.editorPrefs.autoDir) el.setAttribute('dir', 'rtl');
        },
        insertGalleryFromMedia() {
            const self = this;
            if (!window.wp || !wp.media) {
                alert('کتابخانه رسانه در دسترس نیست');
                return;
            }
            const frame = wp.media({ title: 'انتخاب چند تصویر', button: { text: 'درج گالری' }, multiple: true, library: { type: 'image' } });
            frame.on('select', function () {
                const sel = frame.state().get('selection');
                let html = '<div class="bk-gallery" style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin:12px 0">';
                sel.each(function (att) {
                    const j = att.toJSON();
                    const src = j.url || '';
                    const alt = (j.alt || j.title || '').replace(/"/g, '&quot;');
                    html += '<img src="' + src + '" alt="' + alt + '" style="width:100%;border-radius:8px">';
                });
                html += '</div>';
                const el = document.getElementById(self.editorId);
                if (el) el.focus();
                document.execCommand('insertHTML', false, html);
                self.onEditorInput();
                self.scanWsImages();
            });
            frame.open();
        },
        insertVideoEmbed() {
            const url = window.prompt('لینک ویدیو (YouTube / مستقیم):', 'https://');
            if (!url) return;
            let embed = '';
            const yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]{6,})/);
            if (yt) {
                embed = '<p><iframe width="100%" height="360" src="https://www.youtube.com/embed/' + yt[1] + '" frameborder="0" allowfullscreen loading="lazy"></iframe></p>';
            } else if (/\.(mp4|webm)(\?|$)/i.test(url)) {
                embed = '<p><video controls style="max-width:100%" src="' + url + '"></video></p>';
            } else {
                embed = '<p><a href="' + url + '" target="_blank" rel="noopener">' + url + '</a></p>';
            }
            const el = document.getElementById(this.editorId);
            if (el) el.focus();
            document.execCommand('insertHTML', false, embed);
            this.onEditorInput();
        },
        setAsCover(img) {
            if (!img || !img.src) return;
            this.ws.cover = img.src;
            if (img.attachment_id) this.ws.cover_id = img.attachment_id;
            this.ws.tab = 'cover';
            if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('به‌عنوان کاور تنظیم شد', 'success');
        },
        replaceImageInContent(img) {
            const self = this;
            if (!window.wp || !wp.media) return;
            const frame = wp.media({ title: 'جایگزینی تصویر', button: { text: 'جایگزین کن' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                const newSrc = att.url || '';
                if (!newSrc || !img.src) return;
                let html = self.getEditorContent();
                html = html.split(img.src).join(newSrc);
                self.setEditorContent(html);
                self.scanWsImages();
            });
            frame.open();
        },
        removeImageFromContent(img) {
            if (!img || !img.src) return;
            if (!confirm('این تصویر از محتوای مقاله حذف شود؟')) return;
            const el = document.getElementById(this.editorId);
            if (!el) return;
            el.querySelectorAll('img').forEach(function (node) {
                if ((node.getAttribute('src') || '') === img.src) {
                    const p = node.parentElement;
                    node.remove();
                    if (p && p.tagName === 'P' && !p.textContent.trim() && !p.querySelector('img')) p.remove();
                }
            });
            this.onEditorInput();
            this.scanWsImages();
        },
        previewMedia(img) {
            if (img && img.src) window.open(img.src, '_blank', 'noopener');
        },

        pickCover() {
            this.pickMediaField('cover');
        },
        pickMediaField(field) {
            if (!window.wp || !wp.media) {
                alert('کتابخانه رسانه در دسترس نیست');
                return;
            }
            const frame = wp.media({ title: 'انتخاب تصویر', button: { text: 'انتخاب' }, multiple: false, library: { type: 'image' } });
            const self = this;
            frame.on('select', function () {
                const att = frame.state().get('selection').first().toJSON();
                if (field === 'cover') {
                    self.ws.cover = att.url;
                    self.ws.cover_id = att.id;
                } else if (field === 'og_image') {
                    self.ws.og_image = att.url;
                } else if (field === 'x_image') {
                    self.ws.x_image = att.url;
                }
            });
            frame.open();
        }
    }));
});
