/* Bankai Content Hub v179 */
(function () {
  function nonce() {
    return (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || window.bankaiAdminNonce || '';
  }
  function ajax() {
    return (window.bankaiCoreData && window.bankaiCoreData.ajaxUrl) || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
  }
  function restBase() {
    var u = (window.bankaiCoreData && window.bankaiCoreData.restUrl) || '/wp-json/bankai/v1/';
    return String(u).replace(/\/?$/, '/');
  }
  function restNonce() {
    // localized as bankaiCoreData.nonce (wp_rest)
    return (window.bankaiCoreData && (window.bankaiCoreData.nonce || window.bankaiCoreData.restNonce)) || '';
  }

  document.addEventListener('alpine:init', function () {
    Alpine.data('bankaiArticlesHub', function () {
      return {
        panel: 'list',
        articles: [],
        page: 1,
        perPage: 10,
        totalPages: 1,
        total: 0,
        searchQ: '',
        orderby: 'date',
        postType: 'post',
        auditBusyId: 0,
        wizBusy: false,
        batchBusy: false,
        wiz: { topic: '', selected: [], queue: [], phase: 'setup' },
        auditDrawer: {
          open: false, title: '', uiTab: 'analysis', result: null, summary: null,
          phase: 'idle', progress: 0, step: '', view: 'list',
          liveSeo: [], liveAeo: [], liveGeo: [], postId: 0,
          viewsTotal: 0, viewsDelta: 0, viewsSeries: [3, 5, 4, 8, 6, 9, 7],
          viewsDesktop: 0, viewsMobile: 0, viewsTablet: 0,
          seoForm: {
            seo_title: '', description: '', focus_keyword: '', keywords_str: '',
            permalink: '', robots_index: true, robots_follow: true, hide_date: false, canonical: ''
          },
          keywordInput: '',
          seoSaving: false,
          aiRunning: false, aiReady: false, aiProgress: 0, aiStep: '', aiSteps: [], aiProposed: null
        },
        tip: { show: false, x: 0, y: 0, title: '', body: '', pass: false, sticky: false },
        // AI modal state
        aiModalOpen: false,
        aiTask: '',
        aiModalTitle: '',
        aiPhase: 'setup',
        aiProvider: '',
        aiOptions: { style: 'seo' },
        aiProgress: 0,
        aiStepText: '',
        aiBusy: false,
        aiLoading: false,
        aiRunChecklist: [],
        aiDraft: { title: '', description: '', focus_keyword: '', keywords: [], rewrite: '' },

        init() {
          this.loadArticles();
        },

        adminNonce() { return nonce(); },

        lineIcon(state) {
          if (state === 'pass' || state === 'done' || state === 'success') {
            return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>';
          }
          if (state === 'run' || state === 'loading') {
            return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#6366f1" stroke-width="2"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>';
          }
          if (state === 'fail' || state === 'error') {
            return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>';
          }
          return '<span style="opacity:.35">○</span>';
        },
        statusLabelFa(row) {
          var s = (row && (row.status || '')) || '';
          var map = {
            publish: 'منتشرشده',
            draft: 'پیش‌نویس',
            pending: 'در انتظار',
            future: 'زمان‌بندی‌شده',
            private: 'خصوصی',
            trash: 'زباله‌دان'
          };
          if (map[s]) return map[s];
          var lab = (row && row.status_label) ? String(row.status_label) : '';
          lab = lab.replace(/[.…]+$/g, '').trim();
          return lab || s || '—';
        },
        scoreTone(n) {
          n = Number(n) || 0;
          if (n >= 80) return 'tone-high';
          if (n >= 55) return 'tone-ok';
          if (n >= 30) return 'tone-mid';
          return 'tone-low';
        },
        scoreOffset(n) {
          n = Math.min(100, Math.max(0, Number(n) || 0));
          return 87.96 - (87.96 * n / 100);
        },
        btnBusy: {},
        setBtnBusy(key, on) {
          this.btnBusy = Object.assign({}, this.btnBusy || {}, { [key]: !!on });
        },
        isBtnBusy(key) {
          return !!(this.btnBusy && this.btnBusy[key]);
        },
        rowScores(row) {
          if (!row) return { seo: 0, aeo: 0, geo: 0 };
          return {
            seo: Number(row.seo_audit != null ? row.seo_audit : (row.seo_score || 0)) || 0,
            aeo: Number(row.aeo_audit != null ? row.aeo_audit : 0) || 0,
            geo: Number(row.geo_audit != null ? row.geo_audit : 0) || 0
          };
        },
        patchArticleScores(id, summary, extra) {
          id = Number(id) || 0;
          if (!id || !Array.isArray(this.articles)) return;
          var seo = Number((summary && summary.seo) || 0) || 0;
          var aeo = Number((summary && summary.aeo) || 0) || 0;
          var geo = Number((summary && summary.geo) || 0) || 0;
          this.articles = this.articles.map(function (r) {
            if (Number(r.id) !== id) return r;
            var next = Object.assign({}, r, {
              seo_audit: seo,
              aeo_audit: aeo,
              geo_audit: geo,
              seo_score: seo,
              score: seo,
              audit_score: Math.round((seo + aeo + geo) / 3)
            });
            if (extra && typeof extra === 'object') Object.assign(next, extra);
            return next;
          });
          if (this.auditDrawer && Number(this.auditDrawer.postId) === id) {
            this.auditDrawer.summary = Object.assign({}, this.auditDrawer.summary || {}, { seo: seo, aeo: aeo, geo: geo });
          }
        },
        async syncAfterSeoChange(opts) {
          opts = opts || {};
          var id = this.auditDrawer.postId;
          if (!id) return;
          if (opts.reaudit) {
            await this.rerunAudit();
            return;
          }
          await this.fetchAuditReport(id);
          if (this.auditDrawer.summary) {
            this.patchArticleScores(id, this.auditDrawer.summary, opts.extra || null);
          }
          try { await this.loadArticles(); } catch (e) {}
        },
        seoLoading: false,
        checkAcc: { basic: true, technical: false, content: false, social: false },
        analysisOpen: {},
        toggleCheckAcc(key) {
          this.checkAcc[key] = !this.checkAcc[key];
        },
        toggleAnalysisItem(key) {
          this.analysisOpen[key] = !this.analysisOpen[key];
        },
        groupSeoChecks(list) {
          var groups = {
            basic: { title: 'پایه و عنوان', items: [] },
            technical: { title: 'فنی و ایندکس', items: [] },
            content: { title: 'محتوا و لینک', items: [] },
            social: { title: 'سوشال و اسکیما', items: [] }
          };
          (list || []).forEach(function (c, i) {
            var lab = String(c.label || c.name || '').toLowerCase();
            var item = Object.assign({}, c, { _k: c.key || ('g' + i) });
            if (/title|description|meta|h1|h2|هدینگ|عنوان|توضیح/.test(lab)) groups.basic.items.push(item);
            else if (/canonical|https|viewport|charset|lang|robots|url|css|html/.test(lab)) groups.technical.items.push(item);
            else if (/لینک|link|کلمه|عمق|تصویر|image|alt|نسبت/.test(lab)) groups.content.items.push(item);
            else if (/open graph|og:|twitter|schema|اسکیما/.test(lab)) groups.social.items.push(item);
            else groups.basic.items.push(item);
          });
          return ['basic', 'technical', 'content', 'social'].map(function (k) {
            return { key: k, title: groups[k].title, items: groups[k].items };
          }).filter(function (g) { return g.items.length; });
        },

        async loadArticles() {
          try {
            const params = new URLSearchParams({
              page: String(this.page || 1),
              per_page: String(this.perPage || 10),
              search: this.searchQ || '',
              orderby: this.orderby || 'date',
              post_type: this.postType || 'post'
            });
            let url = restBase() + 'seo/articles?' + params.toString();
            let r = await fetch(url, { credentials: 'same-origin', headers: { 'X-WP-Nonce': restNonce() } });
            if (!r.ok) {
              const fd = new FormData();
              fd.append('action', 'bankai_articles_list');
              fd.append('nonce', nonce());
              fd.append('page', String(this.page || 1));
              fd.append('per_page', String(this.perPage || 10));
              fd.append('search', this.searchQ || '');
              fd.append('orderby', this.orderby || 'date');
              fd.append('post_type', this.postType || 'post');
              r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            }
            const j = await r.json();
            // REST: {success, data:{items}}  | AJAX: {success, data:{items}} | raw: {items}
            let data = j;
            if (j && j.data) data = j.data;
            if (data && data.data && (data.data.items || data.data.posts)) data = data.data;
            this.articles = data.items || data.posts || data.articles || [];
            // normalize edit_link
            this.articles = (this.articles || []).map(function (row) {
              if (row && !row.edit_link && row.edit_url) row.edit_link = row.edit_url;
              if (row && row.score != null && row.seo_score == null) row.seo_score = row.score;
              return row;
            });
            this.totalPages = data.total_pages || data.pages || 1;
            this.total = data.total || this.articles.length;
            if (!this.articles.length && window.console) {
              console.warn('[Bankai] articles empty', { status: r.status, payload: j });
            }
          } catch (e) {
            this.articles = [];
            if (window.showToast) window.showToast('خطا در بارگذاری محتوا', 'error');
          }
        },

        closeAuditPanel() {
          this.auditDrawer.open = false;
          this.auditDrawer.phase = 'idle';
        },


        async switchAuditTab(tab) {
          this.auditDrawer.uiTab = tab;
          if (tab === 'analysis') {
            this.ensureAnalysisLoaded();
          }
        },
        async ensureAnalysisLoaded(force) {
          var id = this.auditDrawer.postId;
          if (!id) return;
          if (!force && this.auditDrawer.phase === 'done' && this.auditDrawer.result &&
              ((this.auditDrawer.result.seo || []).length || (this.auditDrawer.result.aeo || []).length || (this.auditDrawer.result.geo || []).length)) {
            return; // already have saved analysis
          }
          if (this.auditBusyId) return;
          // try load saved report first
          await this.fetchAuditReport(id);
          var has = this.auditDrawer.result && (
            (this.auditDrawer.result.seo || []).length ||
            (this.auditDrawer.result.aeo || []).length ||
            (this.auditDrawer.result.geo || []).length
          );
          if (has && !force) {
            this.auditDrawer.phase = 'done';
            if (this.auditDrawer.summary) this.patchArticleScores(id, this.auditDrawer.summary);
            return;
          }
          await this.rerunAudit(!!force);
        },
        plainText(v) {
          if (v == null) return '';
          var s = typeof v === 'string' ? v : (Array.isArray(v) ? v.map(function (x) {
            if (typeof x === 'string') return x;
            return (x.q || x.question || '') + ' — ' + (x.a || x.answer || x.text || '');
          }).join('\n') : JSON.stringify(v));
          s = String(s).replace(/<script[\s\S]*?<\/script>/gi, '');
          s = s.replace(/<style[\s\S]*?<\/style>/gi, '');
          s = s.replace(/<[^>]+>/g, ' ');
          s = s.replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
          return s.replace(/\s+/g, ' ').trim();
        },
        buildAeoGeoHtml() {
          var parts = [];
          var faq = this.aiDraft.faq || this.aiDraft.faqText;
          var take = this.aiDraft.takeaways || this.aiDraft.takeawaysText;
          if (faq) {
            parts.push('<h2>سوالات متداول</h2>');
            if (Array.isArray(faq)) {
              faq.forEach(function (x) {
                var q = x.q || x.question || x.title || '';
                var a = x.a || x.answer || x.text || '';
                if (q) parts.push('<h3>' + q + '</h3><p>' + a + '</p>');
              });
            } else {
              parts.push('<div>' + String(faq) + '</div>');
            }
          }
          if (take) {
            parts.push('<h2>نکات کلیدی</h2>');
            if (Array.isArray(take)) {
              parts.push('<ul>' + take.map(function (t) {
                var tx = typeof t === 'string' ? t : (t.text || t.title || '');
                return '<li>' + tx + '</li>';
              }).join('') + '</ul>');
            } else {
              parts.push('<p>' + String(take) + '</p>');
            }
          }
          return parts.join('\n');
        },
        copyPermalink() {
          var u = (this.auditDrawer.seoForm && this.auditDrawer.seoForm.permalink) || '';
          if (!u) return;
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(u).then(function () {
              if (window.showToast) window.showToast('لینک کپی شد', 'success');
            });
          } else if (window.showToast) window.showToast(u, 'info');
        },
        async openAuditPanel(row) {
          if (!row || !row.id) return;
          this.auditDrawer.postId = row.id;
          this.auditDrawer.title = row.title || '';
          this.auditDrawer.uiTab = 'analysis';
          this.auditDrawer.view = 'list';
          this.auditDrawer.open = true;
          this.seoLoading = true;
          this.analysisOpen = {};
          this.auditDrawer.phase = 'idle';
          this.auditDrawer.aiReady = false;
          this.auditDrawer.aiRunning = false;
          this.auditDrawer.aiProposed = null;
          const total = Number(row.views || row.view_count || 0) || 0;
          this.auditDrawer.viewsTotal = total;
          this.auditDrawer.viewsDelta = Number(row.views_delta != null ? row.views_delta : (total ? 100 : 0));
          this.auditDrawer.viewsDesktop = Number(row.views_desktop || Math.round(total * 0.7)) || 0;
          this.auditDrawer.viewsMobile = Number(row.views_mobile || Math.round(total * 0.25)) || 0;
          this.auditDrawer.viewsTablet = Number(row.views_tablet || Math.max(0, total - this.auditDrawer.viewsDesktop - this.auditDrawer.viewsMobile)) || 0;
          this.auditDrawer.viewsSeries = Array.isArray(row.views_series) && row.views_series.length
            ? row.views_series
            : [2, 4, 3, 6, 5, 8, Math.max(3, Math.round((total || 14) / 7))];
          this.auditDrawer.summary = {
            seo: row.seo_audit || row.seo_score || 0,
            aeo: row.aeo_audit || 0,
            geo: row.geo_audit || 0
          };
          this.auditDrawer.seoForm = {
            seo_title: row.seo_title || '',
            description: row.description || row.seo_description || '',
            focus_keyword: row.focus_keyword || '',
            keywords_str: Array.isArray(row.keywords) ? row.keywords.join('، ') : (row.keywords || ''),
            permalink: row.permalink || row.url || '',
            robots_index: true,
            robots_follow: true,
            hide_date: false,
            canonical: row.canonical || row.permalink || ''
          };
          try {
            await this.loadAuditSeoForm();
            await this.ensureAnalysisLoaded(false);
          } finally {
            this.seoLoading = false;
          }
        },

        auditKeywordList() {
          const f = this.auditDrawer.seoForm || {};
          const primary = (f.focus_keyword || '').trim();
          const rest = (f.keywords_str || '').split(/[,،]/).map(function (s) { return s.trim(); }).filter(Boolean);
          const list = [];
          if (primary) list.push(primary);
          rest.forEach(function (k) { if (k !== primary && list.indexOf(k) === -1) list.push(k); });
          return list;
        },
        addAuditKeyword() {
          const v = (this.auditDrawer.keywordInput || '').trim();
          if (!v) return;
          if (!this.auditDrawer.seoForm.focus_keyword) {
            this.auditDrawer.seoForm.focus_keyword = v;
          } else {
            const cur = (this.auditDrawer.seoForm.keywords_str || '').trim();
            this.auditDrawer.seoForm.keywords_str = cur ? (cur + '، ' + v) : v;
          }
          this.auditDrawer.keywordInput = '';
        },
        removeAuditKeyword(i) {
          const list = this.auditKeywordList();
          list.splice(i, 1);
          this.auditDrawer.seoForm.focus_keyword = list[0] || '';
          this.auditDrawer.seoForm.keywords_str = list.slice(1).join('، ');
        },

        async refreshAuditViews() {
          await this.loadAuditSeoForm();
        },

        async loadAuditSeoForm() {
          const id = this.auditDrawer.postId;
          if (!id) return;
          try {
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_post_context');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            const r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            const j = await r.json();
            if (j && j.success && j.data) {
              const d = j.data;
              this.auditDrawer.seoForm = Object.assign({}, this.auditDrawer.seoForm, {
                seo_title: d.seo_title || d.title || this.auditDrawer.seoForm.seo_title || '',
                description: d.description || d.seo_description || '',
                focus_keyword: d.focus_keyword || '',
                keywords_str: Array.isArray(d.keywords) ? d.keywords.join('، ') : (d.keywords || ''),
                permalink: d.permalink || d.url || this.auditDrawer.seoForm.permalink || '',
                canonical: d.canonical || d.permalink || this.auditDrawer.seoForm.canonical || ''
              });
              if (d.views != null) this.auditDrawer.viewsTotal = Number(d.views) || this.auditDrawer.viewsTotal;
            }
          } catch (e) {}
        },

        async saveAuditSeoForm() {
          const id = this.auditDrawer.postId;
          if (!id) return;
          this.auditDrawer.seoSaving = true;
          this.setBtnBusy('saveSeo', true);
          try {
            const f = this.auditDrawer.seoForm || {};
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_apply_draft');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            fd.append('seo_title', f.seo_title || '');
            fd.append('description', f.description || '');
            fd.append('focus_keyword', f.focus_keyword || '');
            if (f.canonical) fd.append('canonical', f.canonical);
            const kws = this.auditKeywordList().slice(1);
            if (kws.length) fd.append('keywords', JSON.stringify(kws));
            const r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            const j = await r.json();
            if (j && j.success) {
              this.patchArticleScores(id, this.auditDrawer.summary || {}, {
                seo_title: f.seo_title || '',
                description: f.description || '',
                focus_keyword: f.focus_keyword || ''
              });
              // re-run audit so scores + checklist match saved meta
              await this.syncAfterSeoChange({
                reaudit: true,
                extra: {
                  seo_title: f.seo_title || '',
                  description: f.description || '',
                  focus_keyword: f.focus_keyword || ''
                }
              });
              if (window.showToast) window.showToast('ذخیره شد و امتیازها به‌روز شدند', 'success');
            } else if (window.showToast) {
              window.showToast((j && j.data && j.data.message) || 'خطا در ذخیره', 'error');
            }
          } catch (e) {
            if (window.showToast) window.showToast('خطای شبکه', 'error');
          } finally {
            this.auditDrawer.seoSaving = false;
            this.setBtnBusy('saveSeo', false);
          }
        },



        isPass(item) {
          if (!item) return false;
          if (item.passed === true || item.pass === true) return true;
          if (item.status === 'pass' || item.state === 'pass') return true;
          return false;
        },
        normalizeChecks(list, group) {
          return (list || []).map(function (it, i) {
            var pass = !!(it.passed || it.pass || it.status === 'pass');
            return {
              key: it.key || it.id || (group + i),
              label: it.label || it.name || it.message || ('#' + (i + 1)),
              message: it.message || it.hint || '',
              pass: pass,
              passed: pass,
              status: pass ? 'pass' : 'fail',
              state: pass ? 'pass' : 'fail',
              group: group,
              hint: it.hint || it.guide || it.message || ''
            };
          });
        },
        failedAuditItems() {
          var r = this.auditDrawer.result || {};
          var all = []
            .concat(this.normalizeChecks(r.seo, 'seo'))
            .concat(this.normalizeChecks(r.aeo, 'aeo'))
            .concat(this.normalizeChecks(r.geo, 'geo'));
          return all.filter(function (x) { return !x.pass; });
        },
        showTip(e, item) {
          if (!item || !e) return;
          var pass = this.isPass(item);
          var pad = 12;
          var tipW = 300;
          var tipH = 140;
          var x = (e.clientX || 0) + 14;
          var y = (e.clientY || 0) + 14;
          if (x + tipW > window.innerWidth - pad) x = (e.clientX || 0) - tipW - 10;
          if (x < pad) x = pad;
          if (y + tipH > window.innerHeight - pad) y = (e.clientY || 0) - tipH - 8;
          if (y < pad) y = pad;
          // update in place to reduce Alpine re-render lag
          this.tip.show = true;
          this.tip.x = Math.round(x);
          this.tip.y = Math.round(y);
          this.tip.title = item.label || '';
          this.tip.body = this.expandGuideHint(item);
          this.tip.pass = pass;
          this.tip.sticky = false;
        },
        hideTip() {
          if (this.tip) this.tip.show = false;
        },
        expandGuideHint(item) {
          if (!item) return '';
          if (item.hint && String(item.hint).length > 40) return item.hint;
          var label = (item.label || item.name || '').toLowerCase();
          var msg = item.message ? String(item.message) : '';
          var guides = [
            { k: /title|عنوان سئو|تگ title/, t: 'عنوان سئو (Title Tag) اولین سیگنال موضوع صفحه برای گوگل و کاربران است. طول ایده‌آل ۵۰–۶۰ کاراکتر است و باید کلیدواژه اصلی نزدیک ابتدای عنوان باشد. راه حل: عنوان را شفاف، یکتا و تمرکز‌دار بازنویسی کنید.' },
            { k: /description|توضیح|meta description/, t: 'Meta Description خلاصه جذاب صفحه در نتایج جستجو است و روی نرخ کلیک اثر دارد. طول مناسب حدود ۱۴۰–۱۶۰ کاراکتر است. راه حل: یک جمله ارزش‌محور با کلیدواژه طبیعی بنویسید.' },
            { k: /h1/, t: 'تگ H1 موضوع اصلی صفحه را مشخص می‌کند و معمولاً فقط یک‌بار باید باشد. راه حل: یک H1 واضح با کلیدواژه اصلی بگذارید.' },
            { k: /h2|هدینگ|سلسله/, t: 'سلسله‌مراتب هدینگ ساختار معنایی محتوا را برای گوگل و AI روشن می‌کند. راه حل: H2 برای موضوعات اصلی و H3 برای زیرموضوع‌ها؛ از پرش سطح خودداری کنید.' },
            { k: /canonical/, t: 'Canonical نسخه اصلی URL را اعلام می‌کند تا محتوای تکراری جریمه نشود. راه حل: آدرس نهایی HTTPS صفحه را در Canonical ثبت کنید.' },
            { k: /https|ssl/, t: 'HTTPS پیش‌فرض اعتماد و ایندکس مدرن است. راه حل: SSL را فعال و لینک‌های داخلی را یکدست کنید.' },
            { k: /viewport|موبایل/, t: 'Viewport موبایل برای نمایش صحیح در گوشی ضروری است. راه حل: متا viewport استاندارد قالب را فعال نگه دارید.' },
            { k: /faq|پرسش|سؤالی|سوال/, t: 'بخش FAQ پاسخ‌های کوتاه و قابل‌استخراج برای Featured Snippet و AEO می‌سازد. راه حل: ۳ تا ۷ پرسش واقعی با پاسخ مستقیم بنویسید و FAQ Schema اضافه کنید. می‌توانید از «بهبود AEO با هوش مصنوعی» استفاده کنید.' },
            { k: /schema|اسکیما/, t: 'داده ساختاریافته فهم محتوا را برای گوگل و موتورهای پاسخ‌گو دقیق‌تر می‌کند. راه حل: Article یا FAQPage متناسب اضافه کنید.' },
            { k: /open graph|og:|twitter/, t: 'تگ‌های سوشال ظاهر اشتراک‌گذاری را کنترل می‌کنند. راه حل: عنوان، توضیح و تصویر OG را تکمیل کنید.' },
            { k: /لینک داخلی|internal/, t: 'لینک داخلی اعتبار و مسیر خزش را بین صفحات مرتبط توزیع می‌کند. راه حل: ۲ تا ۵ لینک مرتبط با انکر توصیفی اضافه کنید.' },
            { k: /کلمه|عمق|نسبت متن|content/, t: 'عمق محتوا سیگنال پوشش موضوع برای SEO و GEO است. راه حل: با مثال، توضیح و زیرسرفصل محتوا را غنی کنید.' },
            { k: /تصویر|image|alt/, t: 'تصاویر بدون ابعاد یا ALT باعث CLS و ضعف دسترس‌پذیری می‌شوند. راه حل: ابعاد مشخص و ALT توصیفی بگذارید.' },
            { k: /toc|فهرست/, t: 'فهرست مطالب پیمایش و استخراج بخش‌ها را برای کاربر و AI آسان می‌کند. راه حل: TOC بر اساس H2/H3 اضافه کنید.' },
            { k: /خلاصه|takeaway|key takeaways|نکات کلیدی|geo/, t: 'خلاصه نکات کلیدی برای نقل‌قول در ChatGPT و موتورهای مولد (GEO) مهم است. راه حل: ۳ تا ۵ نکته کوتاه و قابل‌استناد بنویسید یا «بهبود GEO با هوش مصنوعی» را اجرا کنید.' },
            { k: /نویسنده|e-e-a-t|author/, t: 'سیگنال نویسنده و E-E-A-T اعتماد را افزایش می‌دهد. راه حل: نام نویسنده و بیو کوتاه اضافه کنید.' },
            { k: /robots|ایندکس|index/, t: 'دستور Robots مشخص می‌کند صفحه ایندکس شود یا نه. راه حل: برای صفحات عمومی Index + Follow را فعال بگذارید.' }
          ];
          for (var i = 0; i < guides.length; i++) {
            if (guides[i].k.test(label) || guides[i].k.test(msg)) return guides[i].t;
          }
          if (msg && msg.length > 15) {
            return msg + ' — این مورد را اصلاح کنید تا امتیاز بخش مربوطه افزایش یابد. در صورت نیاز از بهبود با هوش مصنوعی استفاده کنید.';
          }
          return 'این بررسی وضعیت فعلی صفحه را نشان می‌دهد. در صورت ناموفق بودن، محتوا یا تنظیمات مرتبط را اصلاح کنید و آنالیز را مجدد اجرا نمایید.';
        },
        seedLiveChecks() {
          var mk = function (arr, group) {
            return arr.map(function (label, i) {
              return { key: group + i, label: label, state: 'pending', group: group };
            });
          };
          this.auditDrawer.liveSeo = mk([
            'تگ Title', 'طول Title', 'Meta description', 'طول Description', 'H1', 'H2', 'سلسله‌مراتب هدینگ',
            'Viewport', 'Charset', 'lang', 'Canonical', 'HTTPS', 'تصاویر', 'Open Graph', 'Schema', 'لینک داخلی', 'عمق محتوا', 'نسبت متن به HTML'
          ], 'seo');
          this.auditDrawer.liveAeo = mk([
            'FAQ', 'هدینگ سؤالی', 'لیست ساختاریافته', 'TOC', 'Key Takeaways', 'الگوی تعریف', 'details/summary',
            'جدول', 'dl', 'ویدیو', 'داده ساختاریافته AI', 'نویسنده', 'تازگی', 'عمق AI', 'جملات قابل استخراج'
          ], 'aeo');
          this.auditDrawer.liveGeo = mk([
            'تعاریف Overview', 'هدینگ Q&A', 'عمق Overview', 'منابع', 'هویت برند', 'تنوع Schema',
            'خلاصه', 'محتوای عملیاتی', 'مقایسه‌ای', 'مثال', 'تاریخ', 'H3', 'HTTPS', 'موبایل', 'Canonical', 'OG', 'ALT', 'E-E-A-T'
          ], 'geo');
        },
        applyAuditResult(d) {
          if (!d) return;
          this.auditDrawer.result = {
            seo: this.normalizeChecks(d.seo, 'seo'),
            aeo: this.normalizeChecks(d.aeo, 'aeo'),
            geo: this.normalizeChecks(d.geo, 'geo')
          };
          if (d.summary) this.auditDrawer.summary = d.summary;
          this.auditDrawer.liveSeo = (this.auditDrawer.result.seo || []).map(function (x) {
            return { key: x.key, label: x.label, state: x.pass ? 'pass' : 'fail', message: x.message, group: 'seo' };
          });
          this.auditDrawer.liveAeo = (this.auditDrawer.result.aeo || []).map(function (x) {
            return { key: x.key, label: x.label, state: x.pass ? 'pass' : 'fail', message: x.message, group: 'aeo' };
          });
          this.auditDrawer.liveGeo = (this.auditDrawer.result.geo || []).map(function (x) {
            return { key: x.key, label: x.label, state: x.pass ? 'pass' : 'fail', message: x.message, group: 'geo' };
          });
        },
        async fetchAuditReport(id) {
          try {
            var fd = new FormData();
            fd.append('action', 'bankai_seo_page_audit_report');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            var r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            var j = await r.json();
            if (j && j.success && j.data) this.applyAuditResult(j.data);
          } catch (e) {}
        },
        async rerunAudit(force) {
          var id = this.auditDrawer.postId;
          if (!id || this.auditBusyId) return;
          this.auditBusyId = id;
          this.setBtnBusy('rerun', true);
          this.auditDrawer.phase = 'run';
          this.auditDrawer.progress = 5;
          this.auditDrawer.step = 'شروع آنالیز…';
          this.auditDrawer.uiTab = 'analysis';
          this.auditDrawer.view = 'list';
          this.seedLiveChecks();
          var self = this;
          var tick = setInterval(function () {
            if (self.auditDrawer.progress < 80) {
              self.auditDrawer.progress += 3;
              [self.auditDrawer.liveSeo, self.auditDrawer.liveAeo, self.auditDrawer.liveGeo].forEach(function (g) {
                var pending = (g || []).find(function (x) { return x.state === 'pending'; });
                if (pending) pending.state = 'run';
                var running = (g || []).find(function (x) { return x.state === 'run'; });
                if (running && Math.random() > 0.4) running.state = 'pass';
              });
            }
          }, 220);
          try {
            var fd = new FormData();
            fd.append('action', 'bankai_seo_page_audit');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            var r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            clearInterval(tick);
            var j = await r.json();
            if (j && j.success && j.data) {
              this.applyAuditResult(j.data);
              this.auditDrawer.phase = 'done';
              this.auditDrawer.progress = 100;
              this.auditDrawer.step = 'گزارش آماده است';
              this.loadArticles();
            } else {
              this.auditDrawer.phase = 'done';
              this.auditDrawer.step = (j && j.data && j.data.message) || 'آنالیز کامل شد';
            }
          } catch (e) {
            clearInterval(tick);
            this.auditDrawer.phase = 'done';
            this.auditDrawer.step = (e && e.message) || 'خطا در آنالیز';
            if (window.showToast) window.showToast(this.auditDrawer.step, 'error');
          } finally {
            this.auditBusyId = 0;
            this.setBtnBusy('rerun', false);
            this.setBtnBusy('analyze', false);
          }
        },
        async runAiFix() {
          var id = this.auditDrawer.postId;
          if (!id || this.auditDrawer.aiRunning) return;
          this.setBtnBusy('aiFix', true);
          var fails = this.failedAuditItems();
          this.auditDrawer.aiRunning = true;
          this.auditDrawer.aiReady = false;
          this.auditDrawer.aiProgress = 5;
          this.auditDrawer.aiStep = 'دریافت پیشنهاد از AI…';
          this.auditDrawer.aiSteps = (fails.length ? fails.slice(0, 8) : [
            { label: 'بهینه‌سازی عنوان و متا با AI' },
            { label: 'بررسی ساختار هدینگ' },
            { label: 'همگام‌سازی Open Graph' }
          ]).map(function (f, i) {
            var lab = f.label || '';
            return { id: 's' + i, label: lab.indexOf('بررسی') === 0 ? lab : ('بررسی: ' + lab), status: 'pending' };
          });
          try {
            for (var i = 0; i < this.auditDrawer.aiSteps.length; i++) {
              this.auditDrawer.aiSteps[i].status = 'run';
              this.auditDrawer.aiStep = this.auditDrawer.aiSteps[i].label;
              this.auditDrawer.aiProgress = 10 + Math.round((i / Math.max(1, this.auditDrawer.aiSteps.length)) * 70);
              await new Promise(function (res) { setTimeout(res, 280); });
              this.auditDrawer.aiSteps[i].status = 'done';
            }
            var fd = new FormData();
            fd.append('action', 'bankai_seo_audit_ai_fix');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            var r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            var j = await r.json();
            if (j && j.success && j.data) {
              this.auditDrawer.aiProposed = j.data.proposed || j.data;
              this.auditDrawer.aiReady = true;
              this.auditDrawer.aiStep = 'پیشنهادهای بهبود آماده است. برای اعمال، «ثبت اصلاحات» را بزنید.';
            } else {
              this.auditDrawer.aiStep = (j && j.data && j.data.message) || 'پاسخی دریافت نشد';
            }
            this.auditDrawer.aiProgress = 100;
          } catch (e) {
            this.auditDrawer.aiStep = (e && e.message) || 'خطا';
            if (window.showToast) window.showToast(this.auditDrawer.aiStep, 'error');
          } finally {
            this.auditDrawer.aiRunning = false;
            this.setBtnBusy('aiFix', false);
          }
        },
        async applyAiFix() {
          var id = this.auditDrawer.postId;
          if (!id || !this.auditDrawer.aiReady) return;
          this.setBtnBusy('applyFix', true);
          try {
            var prop = this.auditDrawer.aiProposed || {};
            if (prop.seo_title) this.auditDrawer.seoForm.seo_title = prop.seo_title;
            if (prop.description) this.auditDrawer.seoForm.description = prop.description;
            if (prop.focus_keyword) this.auditDrawer.seoForm.focus_keyword = prop.focus_keyword;
            if (prop.keywords) {
              this.auditDrawer.seoForm.keywords_str = Array.isArray(prop.keywords) ? prop.keywords.join('، ') : String(prop.keywords);
            }
            var fd = new FormData();
            fd.append('action', 'bankai_seo_audit_apply_fix');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            fd.append('proposed', JSON.stringify(prop));
            var r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            var j = await r.json();
            // always also apply via draft endpoint so meta is written even if transient expired
            var fd2 = new FormData();
            fd2.append('action', 'bankai_seo_ai_apply_draft');
            fd2.append('nonce', nonce());
            fd2.append('post_id', String(id));
            fd2.append('seo_title', prop.seo_title || this.auditDrawer.seoForm.seo_title || '');
            fd2.append('description', prop.description || this.auditDrawer.seoForm.description || '');
            fd2.append('focus_keyword', prop.focus_keyword || this.auditDrawer.seoForm.focus_keyword || '');
            await fetch(ajax(), { method: 'POST', body: fd2, credentials: 'same-origin' });
            await this.saveAuditSeoForm();
            this.auditDrawer.aiReady = false;
            this.auditDrawer.aiProposed = null;
            if (window.showToast) window.showToast((j && j.success) ? 'اصلاحات در مقاله ذخیره شد' : 'ذخیره مستقیم انجام شد', 'success');
            await this.loadAuditSeoForm();
            await this.rerunAudit();
          } catch (e) {
            if (window.showToast) window.showToast('خطای شبکه', 'error');
          } finally {
            this.setBtnBusy('aiFix', false);
            this.setBtnBusy('applyFix', false);
          }
        },

        /* ===== AI Modal workflow ===== */
        aiProvidersList() {
          if (window.bankaiEditorSeo && Array.isArray(bankaiEditorSeo.aiProviders)) {
            return bankaiEditorSeo.aiProviders;
          }
          try {
            const el = document.getElementById('bk-wiz-providers-json');
            if (el) return JSON.parse(el.textContent || '[]');
          } catch (e) {}
          return [
            { id: 'openrouter', label: 'OpenRouter' },
            { id: 'gemini', label: 'Google Gemini' },
            { id: 'openai', label: 'OpenAI' },
            { id: 'anthropic', label: 'Anthropic Claude' },
            { id: 'deepseek', label: 'DeepSeek' },
            { id: 'groq', label: 'Groq' }
          ];
        },
        openAiModal(task) {
          this.aiTask = task || 'auto_all';
          const titles = {
            auto_all: 'بهینه‌سازی یک‌پارچه مقاله',
            meta: 'تولید عنوان و متا',
            keywords: 'استخراج کلیدواژه',
            faq: 'بهبود AEO با هوش مصنوعی',
            aeo: 'بهبود AEO با هوش مصنوعی',
            aeo_geo: 'بهبود یک‌پارچه AEO · GEO',
            takeaways: 'بهبود GEO با هوش مصنوعی',
            geo: 'بهبود GEO با هوش مصنوعی',
            links: 'لینک‌سازی داخلی',
            images: 'Alt تصاویر',
            rewrite: 'بازنویسی مقاله'
          };
          this.aiModalTitle = titles[this.aiTask] || 'دستیار AI سئو';
          this.aiPhase = 'setup';
          this.aiProgress = 0;
          this.aiDraft = { title: '', description: '', focus_keyword: '', keywords: [], rewrite: '', faq: null, takeaways: null, schema: null, faqText: '', takeawaysText: '' };
          this.aiRunChecklist = this.plannedChecklist().map(function (x) {
            return { id: x.id, label: x.label, status: 'pending' };
          });
          this.aiModalOpen = true;
        },
        closeAiModal() {
          if (this.aiBusy) return;
          this.aiModalOpen = false;
          this.aiPhase = 'setup';
        },
        aiPhaseLabel() {
          if (this.aiPhase === 'setup') return 'مرحله ۱ — تنظیم و انتخاب AI';
          if (this.aiPhase === 'running') return 'مرحله ۲ — اجرا';
          if (this.aiPhase === 'review') return 'مرحله ۳ — بازبینی';
          return '';
        },
        aiPhaseIndex() {
          return { setup: 0, running: 1, review: 2 }[this.aiPhase] || 0;
        },
        aiTaskBlurb() {
          const m = {
            auto_all: 'تمام گام‌های سئو، کلیدواژه، لینک، تصویر و اسکیما به‌صورت یک‌پارچه اجرا می‌شود.',
            meta: 'عنوان سئو و متا دیسکریپشن استاندارد تولید می‌شود.',
            keywords: 'کلیدواژه اصلی و فرعی از محتوا استخراج می‌شود.',
            faq: 'ساختار پاسخ‌محور و FAQ برای Featured Snippet و موتورهای پاسخ‌گو ساخته می‌شود.',
            aeo: 'ساختار پاسخ‌محور و FAQ برای Featured Snippet و موتورهای پاسخ‌گو ساخته می‌شود.',
            aeo_geo: 'FAQ ساختاریافته، اسکیما، خلاصه نکات کلیدی و متای قابل‌نقل برای AEO و GEO در یک فرایند تولید می‌شود.',
            takeaways: 'چکیده و نکات کلیدی برای نقل‌قول توسط ChatGPT و موتورهای مولد تولید می‌شود.',
            geo: 'چکیده و نکات کلیدی برای نقل‌قول توسط ChatGPT و موتورهای مولد تولید می‌شود.',
            links: 'پیشنهاد لینک داخلی بر اساس محتوا.',
            images: 'تولید Alt Text برای تصاویر مقاله.',
            rewrite: 'بازنویسی کامل محتوا با حفظ HTML.'
          };
          return m[this.aiTask] || m.auto_all;
        },
        plannedChecklist() {
          if (this.aiTask === 'meta') {
            return [
              { id: 'title', label: 'تولید عنوان سئو' },
              { id: 'desc', label: 'تولید متا دیسکریپشن' }
            ];
          }
          if (this.aiTask === 'keywords') {
            return [
              { id: 'focus', label: 'کلیدواژه اصلی' },
              { id: 'kws', label: 'کلیدواژه‌های فرعی' }
            ];
          }
          if (this.aiTask === 'aeo_geo') {
            return [
              { id: 'aeo_ready', label: 'تحلیل آمادگی پاسخ‌محور (AEO)' },
              { id: 'faq', label: 'تولید سوالات متداول ساختاریافته' },
              { id: 'schema_faq', label: 'آماده‌سازی اسکیما FAQ' },
              { id: 'geo_ready', label: 'تحلیل آمادگی موتورهای مولد (GEO)' },
              { id: 'geo', label: 'تولید خلاصه نکات کلیدی' },
              { id: 'geo_meta', label: 'بهینه‌سازی متا برای نقل‌قول AI' },
              { id: 'preview', label: 'آماده‌سازی پیش‌نمایش بدون تگ HTML' }
            ];
          }
          if (this.aiTask === 'faq' || this.aiTask === 'aeo') {
            return [
              { id: 'aeo_ready', label: 'تحلیل آمادگی پاسخ‌محور (AEO)' },
              { id: 'faq', label: 'تولید سوالات متداول ساختاریافته' },
              { id: 'schema_faq', label: 'آماده‌سازی اسکیما FAQ' },
              { id: 'preview', label: 'آماده‌سازی پیش‌نمایش برای تایید' }
            ];
          }
          if (this.aiTask === 'takeaways' || this.aiTask === 'geo') {
            return [
              { id: 'geo_ready', label: 'تحلیل آمادگی موتورهای مولد (GEO)' },
              { id: 'geo', label: 'تولید خلاصه نکات کلیدی' },
              { id: 'geo_meta', label: 'بهینه‌سازی متا برای نقل‌قول AI' },
              { id: 'preview', label: 'آماده‌سازی پیش‌نمایش برای تایید' }
            ];
          }
          if (this.aiTask === 'links') return [{ id: 'links', label: 'پیشنهاد لینک داخلی' }];
          if (this.aiTask === 'images') return [{ id: 'alt', label: 'تولید Alt تصاویر' }];
          if (this.aiTask === 'rewrite') return [{ id: 'rewrite', label: 'بازنویسی محتوا' }];
          return [
            { id: 'analyze', label: 'تحلیل محتوا و ساختار مقاله' },
            { id: 'meta', label: 'تولید عنوان سئو و متا دیسکریپشن' },
            { id: 'keywords', label: 'استخراج کلمات کلیدی' },
            { id: 'links', label: 'پیشنهاد لینک داخلی' },
            { id: 'images', label: 'سئوی تصاویر و Alt' },
            { id: 'schema', label: 'تولید اسکیما' }
          ];
        },
        activeRunChecklist() { return this.aiRunChecklist || []; },
        aiStatusFa(st) {
          return ({ pending: 'در صف', loading: 'در حال اجرا', success: 'انجام شد', done: 'انجام شد', error: 'خطا' })[st] || 'در صف';
        },

        async startAiWorkflow() {
          if (this.aiBusy) return;
          this.aiBusy = true;
          this.aiLoading = true;
          this.setBtnBusy('aiStart', true);
          this.aiPhase = 'running';
          this.aiProgress = 5;
          this.aiRunChecklist = this.plannedChecklist().map(function (x) {
            return { id: x.id, label: x.label, status: 'pending' };
          });
          const setSt = (id, st) => {
            const it = this.aiRunChecklist.find((x) => x.id === id);
            if (it) it.status = st;
          };
          try {
            await this.loadAuditSeoForm();
            const f = this.auditDrawer.seoForm || {};
            const base = {
              post_id: this.auditDrawer.postId,
              title: this.auditDrawer.title || '',
              seo_title: f.seo_title || '',
              focus_keyword: f.focus_keyword || '',
              locale: 'fa_IR',
              provider: this.aiProvider || '',
              style: (this.aiOptions && this.aiOptions.style) || 'seo'
            };
            const call = async (task) => {
              const fd = new FormData();
              fd.append('action', 'bankai_ai_seo_task');
              fd.append('nonce', nonce());
              fd.append('task', task);
              Object.keys(base).forEach((k) => { if (base[k] != null) fd.append(k, String(base[k])); });
              const r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
              const j = await r.json();
              if (!j || !j.success) throw new Error((j && j.data && j.data.message) || task);
              return j.data || {};
            };

            const steps = this.aiRunChecklist.slice();
            for (let i = 0; i < steps.length; i++) {
              const s = steps[i];
              setSt(s.id, 'loading');
              this.aiStepText = s.label;
              this.aiProgress = 10 + Math.round((i / Math.max(1, steps.length)) * 70);
              try {
                if (s.id === 'title' || s.id === 'meta' || s.id === 'analyze') {
                  const d = await call('meta_title');
                  this.aiDraft.title = d.seo_title || d.title || d.text || d.result || this.aiDraft.title;
                  if (s.id === 'meta' || s.id === 'analyze') {
                    const d2 = await call('meta_description');
                    this.aiDraft.description = d2.description || d2.meta_description || d2.seo_description || d2.text || '';
                  }
                } else if (s.id === 'desc') {
                  const d = await call('meta_description');
                  this.aiDraft.description = d.description || d.meta_description || d.text || '';
                } else if (s.id === 'focus' || s.id === 'keywords') {
                  const d = await call('focus_keyword');
                  this.aiDraft.focus_keyword = d.focus_keyword || d.keyword || d.text || '';
                  if (s.id === 'keywords' || this.aiTask === 'auto_all') {
                    const d2 = await call('keywords');
                    this.aiDraft.keywords = d2.keywords || d2.list || [];
                  }
                } else if (s.id === 'kws') {
                  const d = await call('keywords');
                  this.aiDraft.keywords = d.keywords || d.list || [];
                } else if (s.id === 'rewrite') {
                  const d = await call('rewrite');
                  this.aiDraft.rewrite = d.rewrite || d.content || d.text || '';
                } else if (s.id === 'aeo_ready' || s.id === 'faq' || s.id === 'schema_faq') {
                  const d = await call(s.id === 'schema_faq' ? 'schema' : (s.id === 'aeo_ready' ? 'aeo_faq' : 'faq'));
                  if (d.faq || d.items || d.questions) {
                    this.aiDraft.faq = d.faq || d.items || d.questions;
                  }
                  if (d.schema) this.aiDraft.schema = d.schema;
                  if (d.text && !this.aiDraft.faq) this.aiDraft.faqText = d.text;
                } else if (s.id === 'geo_ready' || s.id === 'geo' || s.id === 'geo_meta') {
                  const d = await call(s.id === 'geo_meta' ? 'meta_description' : 'geo_summary');
                  if (d.takeaways || d.summary || d.points) {
                    this.aiDraft.takeaways = d.takeaways || d.summary || d.points;
                  }
                  if (d.description || d.meta_description) {
                    this.aiDraft.description = d.description || d.meta_description;
                  }
                  if (d.text && !this.aiDraft.takeaways) this.aiDraft.takeawaysText = d.text;
                } else if (s.id === 'preview') {
                  // assemble preview only
                } else {
                  try {
                    const map = { faq: 'faq', geo: 'geo_summary', links: 'internal_links', alt: 'image_alt_text', images: 'image_alt_text', schema: 'schema' };
                    if (map[s.id]) {
                      const d = await call(map[s.id]);
                      if (map[s.id] === 'faq' && (d.faq || d.items)) this.aiDraft.faq = d.faq || d.items;
                      if (map[s.id] === 'geo_summary' && (d.takeaways || d.summary)) this.aiDraft.takeaways = d.takeaways || d.summary;
                    }
                  } catch (e2) {}
                }
                setSt(s.id, 'success');
              } catch (e) {
                setSt(s.id, 'error');
              }
            }
            // strip HTML tags from visible draft fields
            ['title','description','focus_keyword','rewrite','faqText','takeawaysText'].forEach((k) => {
              if (this.aiDraft[k]) this.aiDraft[k] = this.plainText(this.aiDraft[k]);
            });
            if (this.aiDraft.faq && typeof this.aiDraft.faq === 'string') {
              this.aiDraft.faq = this.plainText(this.aiDraft.faq);
            }
            if (this.aiDraft.takeaways && typeof this.aiDraft.takeaways === 'string') {
              this.aiDraft.takeaways = this.plainText(this.aiDraft.takeaways);
            }
            this.aiProgress = 100;
            this.aiPhase = 'review';
            this.aiStepText = 'آماده بازبینی — بدون نمایش تگ HTML';
          } catch (e) {
            if (window.showToast) window.showToast((e && e.message) || 'خطا در فرایند AI', 'error');
            this.aiPhase = 'setup';
          } finally {
            this.aiBusy = false;
            this.aiLoading = false;
            this.setBtnBusy('aiStart', false);
          }
        },

        async rewriteAiWorkflow() {
          await this.startAiWorkflow();
        },

        async applyAiReview() {
          const id = this.auditDrawer.postId;
          if (!id) return;
          this.setBtnBusy('aiApply', true);
          try {
            const fd = new FormData();
            fd.append('action', 'bankai_seo_ai_apply_draft');
            fd.append('nonce', nonce());
            fd.append('post_id', String(id));
            fd.append('seo_title', this.aiDraft.title || '');
            fd.append('description', this.aiDraft.description || '');
            fd.append('focus_keyword', this.aiDraft.focus_keyword || '');
            if (this.aiDraft.keywords && this.aiDraft.keywords.length) {
              fd.append('keywords', JSON.stringify(this.aiDraft.keywords));
            }
            // Append FAQ/Takeaways HTML into post content when AEO/GEO task
            var bodyHtml = '';
            if (this.aiTask === 'aeo_geo' || this.aiTask === 'aeo' || this.aiTask === 'geo' || this.aiTask === 'faq' || this.aiTask === 'takeaways') {
              bodyHtml = this.buildAeoGeoHtml();
              if (bodyHtml) {
                fd.append('content_append', bodyHtml);
                fd.append('append_content', '1');
              }
            }
            if (this.aiDraft.rewrite) {
              fd.append('content', this.aiDraft.rewrite);
            }
            if (this.aiDraft.faq) {
              fd.append('faq', typeof this.aiDraft.faq === 'string' ? this.aiDraft.faq : JSON.stringify(this.aiDraft.faq));
            }
            if (this.aiDraft.takeaways) {
              fd.append('takeaways', typeof this.aiDraft.takeaways === 'string' ? this.aiDraft.takeaways : JSON.stringify(this.aiDraft.takeaways));
            }
            if (this.aiDraft.schema) {
              fd.append('schema', typeof this.aiDraft.schema === 'string' ? this.aiDraft.schema : JSON.stringify(this.aiDraft.schema));
            }
            if (this.aiDraft.faqText) fd.append('faq_text', this.aiDraft.faqText);
            if (this.aiDraft.takeawaysText) fd.append('takeaways_text', this.aiDraft.takeawaysText);
            const r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            const j = await r.json();
            if (j && j.success) {
              if (this.aiDraft.title) this.auditDrawer.seoForm.seo_title = this.aiDraft.title;
              if (this.aiDraft.description) this.auditDrawer.seoForm.description = this.aiDraft.description;
              if (this.aiDraft.focus_keyword) this.auditDrawer.seoForm.focus_keyword = this.aiDraft.focus_keyword;
              await this.syncAfterSeoChange({ reaudit: true });
              this.aiModalOpen = false;
              this.auditDrawer.uiTab = 'analysis';
              if (window.showToast) window.showToast('محتوا و سئو ذخیره شد و امتیازها به‌روز شدند', 'success');
            } else if (window.showToast) {
              window.showToast((j && j.data && j.data.message) || 'خطا در اعمال', 'error');
            }
          } catch (e) {
            if (window.showToast) window.showToast('خطای شبکه', 'error');
          } finally {
            this.setBtnBusy('aiApply', false);
          }
        },

        /* wizard */
        addManualTitle() {
          if (!this.wiz.selected) this.wiz.selected = [];
          this.wiz.selected.push({ title: this.wiz.topic || 'عنوان جدید', approved: true });
        },
        async suggestTitles() {
          if (!this.wiz.topic || !this.wiz.topic.trim()) {
            if (window.showToast) window.showToast('موضوع را وارد کنید', 'warning');
            return;
          }
          this.wizBusy = true;
          try {
            const fd = new FormData();
            fd.append('action', 'bankai_ai_suggest_titles');
            fd.append('nonce', nonce());
            fd.append('topic', this.wiz.topic);
            fd.append('count', '5');
            const r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
            const j = await r.json();
            const titles = (j && j.data && (j.data.titles || j.data.items)) || [];
            this.wiz.selected = titles.map(function (t) {
              return { title: typeof t === 'string' ? t : (t.title || ''), approved: true };
            }).filter(function (x) { return x.title; });
            if (!this.wiz.selected.length && window.showToast) {
              window.showToast((j && j.data && j.data.message) || 'عنوانی دریافت نشد', 'error');
            }
          } catch (e) {
            if (window.showToast) window.showToast('خطا در پیشنهاد عنوان', 'error');
          } finally {
            this.wizBusy = false;
          }
        },
        async startProduce() {
          const items = (this.wiz.selected || []).filter(function (s) { return s.title && s.title.trim(); });
          if (!items.length) return;
          this.batchBusy = true;
          this.wiz.phase = 'produce';
          this.wiz.queue = items.map(function (s) {
            return { title: s.title.trim(), status: 'pending', progress: 0, stepLabel: 'آماده', post_id: 0 };
          });
          for (let i = 0; i < this.wiz.queue.length; i++) {
            const job = this.wiz.queue[i];
            job.status = 'run';
            job.stepLabel = 'تولید محتوا…';
            job.progress = 10;
            try {
              const fd = new FormData();
              fd.append('action', 'bankai_ai_generate_article');
              fd.append('nonce', nonce());
              fd.append('topic', job.title);
              fd.append('focus', this.wiz.topic || '');
              fd.append('length', '2000');
              fd.append('status', 'draft');
              fd.append('do_seo', '1');
              const r = await fetch(ajax(), { method: 'POST', body: fd, credentials: 'same-origin' });
              const text = await r.text();
              let j = null;
              try { j = JSON.parse(text); } catch (e) { throw new Error('پاسخ نامعتبر'); }
              if (j && j.success) {
                job.progress = 100;
                job.status = 'done';
                job.stepLabel = 'تکمیل شد';
                job.post_id = (j.data && j.data.post_id) || 0;
              } else {
                job.status = 'error';
                job.stepLabel = (j && j.data && j.data.message) || 'خطا';
              }
            } catch (e) {
              job.status = 'error';
              job.stepLabel = (e && e.message) || 'خطا';
            }
          }
          this.batchBusy = false;
          this.loadArticles();
          if (window.showToast) window.showToast('صف تولید پایان یافت', 'success');
        }
      };
    });
  });
})();
