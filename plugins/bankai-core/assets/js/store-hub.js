/**
 * Bankai Store Hub
 */
(function () {
  'use strict';

  function nonce() {
    // Must match check_ajax_referer('bankai_admin_nonce')
    var d = window.bankaiCoreData || window.bankaiData || {};
    return d.adminNonce || d.admin_nonce || d.nonce || '';
  }
  function ajaxUrl() {
    var d = window.bankaiCoreData || window.bankaiData || {};
    return d.ajaxUrl || d.ajax_url || window.ajaxurl || '/wp-admin/admin-ajax.php';
  }
  function toast(msg, type) {
    if (typeof window.bankaiShowToast === 'function') {
      window.bankaiShowToast(msg, type || 'success');
      return;
    }
    // Alpine root may expose showToast via $root — fallback console
    try {
      var root = document.querySelector('[x-data]');
      if (root && root._x_dataStack) {
        for (var i = 0; i < root._x_dataStack.length; i++) {
          if (typeof root._x_dataStack[i].showToast === 'function') {
            root._x_dataStack[i].showToast(msg, type || 'success');
            return;
          }
        }
      }
    } catch (e) {}
    if (window.console) console.log('[Bankai Store]', type || 'info', msg);
  }
  function post(action, data) {
    var fd = new FormData();
    fd.append('action', action);
    fd.append('nonce', nonce());
    Object.keys(data || {}).forEach(function (k) {
      var v = data[k];
      if (typeof v === 'object') fd.append(k, JSON.stringify(v));
      else fd.append(k, v == null ? '' : String(v));
    });
    return fetch(ajaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  window.bankaiStoreHub = function () {
    return {
      loading: false,
      coverBusy: 0,
      report: { open: false, loading: false, data: null },
      statsLoading: false,
      statsError: '',
      productsError: '',
      items: [],
      stats: { series: [], order_series: [] },
      period: 30,
      search: '',
      stock: 'all',
      status: 'all',
      type: 'all',
      on_sale: 'all',
      category: 0,
      seo_filter: 'all',
      price_min: '',
      price_max: '',
      orderby: 'date',
      page: 1,
      pages: 1,
      total: 0,
      categories: [],
      wizard: {
        id: 0,
        running: false,
        modal: false,
        applying: false,
        genImage: false,
        currentLabel: '',
        bag: {},
        steps: [],
        product: null,
        conflict: { message: '', current: null, proposed: null }
      },
      wizardFields: [],
      create: {
        open: false, name: '', short_description: '', description: '',
        price: '', sale_price: '', sku: '', stock_status: 'instock',
        status: 'draft', saving: false, aiBusy: false, coverPrompt: ''
      },

      init: function () {
        this.loadStats();
        this.loadProducts();
        this.loadCategories();
      },

      setPeriod: function (p) {
        this.period = p;
        this.loadStats();
      },

      loadCategories: function () {
        var self = this;
        post('bankai_store_categories', {}).then(function (res) {
          if (res && res.success && res.data) self.categories = res.data.items || [];
        }).catch(function () {});
      },

      formatMoney: function (n) {
        if (n === null || n === undefined || n === '') return '—';
        n = Number(n);
        if (isNaN(n)) return '—';
        var c = (this.stats && this.stats.currency) || '';
        if ((!c || c.indexOf('&') !== -1) && this.report && this.report.data && this.report.data.currency) {
          c = this.report.data.currency;
        }
        // Decode HTML entities like &#xfdfc; → ﷼
        if (c && c.indexOf('&') !== -1) {
          var ta = document.createElement('textarea');
          ta.innerHTML = c;
          c = ta.value;
        }
        try {
          return n.toLocaleString('fa-IR') + (c ? ' ' + c : '');
        } catch (e) {
          return n + (c ? ' ' + c : '');
        }
      },

      stripTags: function (html) {
        if (!html) return '';
        var d = document.createElement('div');
        d.innerHTML = String(html);
        return (d.textContent || d.innerText || '').trim();
      },

      isHtmlField: function (field) {
        if (!field) return false;
        var k = field.key || '';
        return k === 'faq_html' || k === 'geo_html' || k === 'description' || k === 'schema_json'
          || (field.value && /<[a-z][\s\S]*>/i.test(String(field.value)));
      },

      sanitizePreview: function (html) {
        if (!html) return '<span style="color:#94a3b8">—</span>';
        // Allow basic tags only
        var allowed = /<(\/?)(p|br|ul|ol|li|strong|b|em|i|h[1-6]|span|div|a)(\s[^>]*)?>/gi;
        var s = String(html);
        // Escape then re-enable allowed via text - simpler: strip scripts
        s = s.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '');
        s = s.replace(/on\w+="[^"]*"/gi, '');
        return s;
      },

      barHeight: function (total) {
        var series = this.stats.series || [];
        var max = 1;
        series.forEach(function (p) { if (Number(p.total) > max) max = Number(p.total); });
        return Math.max(4, Math.round((Number(total || 0) / max) * 100));
      },

      scoreTone: function (s) {
        s = parseInt(s, 10) || 0;
        if (s >= 80) return 'is-good';
        if (s >= 50) return 'is-mid';
        return 'is-bad';
      },
      scoreOffset: function (s) {
        s = Math.min(100, Math.max(0, parseInt(s, 10) || 0));
        return 87.96 - (87.96 * s / 100);
      },

      stepLabel: function (key) {
        return ({
          meta: 'متا تگ‌ها', schema: 'اسکیما', aeo: 'AEO / FAQ',
          geo: 'GEO', links: 'لینک داخلی', images: 'تصاویر', cover: 'کاور'
        })[key] || key;
      },
      prettyDraft: function (val) {
        if (val == null) return '—';
        if (typeof val === 'string') return val;
        try { return JSON.stringify(val, null, 2); } catch (e) { return String(val); }
      },

      loadStats: function () {
        var self = this;
        self.statsLoading = true;
        self.statsError = '';
        post('bankai_store_stats', { period: self.period }).then(function (res) {
          self.statsLoading = false;
          if (res && res.success && res.data) self.stats = res.data;
          else self.statsError = (res && res.data && res.data.message) || 'خطا در دریافت آمار';
        }).catch(function () {
          self.statsLoading = false;
          self.statsError = 'خطای شبکه در آمار';
        });
      },

      loadProducts: function () {
        var self = this;
        self.loading = true;
        self.productsError = '';
        post('bankai_store_products', {
          search: self.search,
          stock: self.stock,
          status: self.status,
          type: self.type,
          on_sale: self.on_sale,
          category: self.category || 0,
          seo_filter: self.seo_filter,
          price_min: self.price_min,
          price_max: self.price_max,
          orderby: self.orderby,
          page: self.page,
          per_page: 12
        }).then(function (res) {
          self.loading = false;
          if (res && res.success && res.data) {
            self.items = res.data.items || [];
            self.pages = res.data.pages || 1;
            self.page = res.data.page || 1;
            self.total = res.data.total || 0;
          } else {
            self.productsError = (res && res.data && res.data.message) || 'خطا در دریافت محصولات';
            toast(self.productsError, 'error');
          }
        }).catch(function () {
          self.loading = false;
          self.productsError = 'خطای شبکه';
          toast('خطا در بارگذاری محصولات', 'error');
        });
      },

      resetFilters: function () {
        this.search = '';
        this.stock = 'all';
        this.status = 'all';
        this.type = 'all';
        this.on_sale = 'all';
        this.category = 0;
        this.seo_filter = 'all';
        this.price_min = '';
        this.price_max = '';
        this.orderby = 'date';
        this.page = 1;
        this.loadProducts();
      },

      startWizard: function (item) {
        var self = this;
        if (self.wizard.running) return;
        self.wizard.id = item.id;
        self.wizard.product = item;
        self.wizard.running = true;
        self.wizard.modal = false;
        self.wizard.bag = {};
        self.wizard.conflict = { message: '', current: null, proposed: null };
        self.wizardFields = [];
        var steps = [
          { id: 'meta', label: 'تولید متا تگ‌ها', status: 'pending' },
          { id: 'content', label: 'توضیحات محصول', status: 'pending' },
          { id: 'taxonomies', label: 'دسته، برچسب و کلمات کلیدی', status: 'pending' },
          { id: 'schema', label: 'اسکیمای محصول', status: 'pending' },
          { id: 'aeo', label: 'AEO / FAQ', status: 'pending' },
          { id: 'geo', label: 'GEO', status: 'pending' },
          { id: 'links', label: 'لینک داخلی', status: 'pending' },
          { id: 'images', label: 'Alt و فشرده‌سازی تصویر', status: 'pending' }
        ];
        if (self.wizard.genImage) {
          steps.push({ id: 'cover', label: 'تولید تصویر کاور', status: 'pending' });
        }
        self.wizard.steps = steps;

        (async function () {
          for (var i = 0; i < steps.length; i++) {
            steps[i].status = 'run';
            self.wizard.currentLabel = steps[i].label + '…';
            self.wizard.steps = steps.slice();
            try {
              var res = await post('bankai_store_wizard_step', {
                product_id: item.id,
                step: steps[i].id,
                gen_image: self.wizard.genImage ? 1 : 0
              });
              if (res && res.success && res.data) {
                self.wizard.bag[steps[i].id] = res.data.draft || {};
                steps[i].status = 'done';
                self.wizard.currentLabel = steps[i].label + ' ✓';
              } else {
                steps[i].status = 'error';
                self.wizard.currentLabel = steps[i].label + ' — خطا';
              }
            } catch (e) {
              steps[i].status = 'error';
              self.wizard.currentLabel = steps[i].label + ' — خطا';
            }
            self.wizard.steps = steps.slice();
            await new Promise(function (r) { setTimeout(r, 220); });
          }
          self.wizard.running = false;
          self.buildWizardFields();
          self.wizard.modal = true;
        })();
      },

      buildWizardFields: function () {
        var bag = this.wizard.bag || {};
        var cur = (this.wizard.product && this.wizard.product.fields) || {};
        var fields = [];
        var meta = bag.meta || {};
        if (meta.seo_title || meta.description || meta.focus_keyword) {
          if (meta.seo_title) {
            fields.push({ key: 'seo_title', step: 'meta', label: 'عنوان SEO', value: meta.seo_title || '', current: cur.seo_title || '', selected: true, skipped: false, note: '' });
          }
          if (meta.description) {
            fields.push({ key: 'seo_description', step: 'meta', label: 'Meta Description', value: meta.description || '', current: cur.seo_description || '', selected: true, skipped: false, note: '' });
          }
          if (meta.focus_keyword) {
            fields.push({ key: 'focus_keyword', step: 'meta', label: 'کلمه کلیدی اصلی', value: meta.focus_keyword || '', current: cur.focus_keyword || '', selected: true, skipped: false, note: '' });
          }
        }
        var content = bag.content || {};
        if (content.short_description) {
          fields.push({ key: 'short_description', step: 'content', label: 'توضیح کوتاه', value: content.short_description || '', current: cur.short_description || '', selected: true, skipped: false, note: '' });
        }
        if (content.description) {
          fields.push({ key: 'description', step: 'content', label: 'توضیحات کامل', value: content.description || '', current: cur.description || '', selected: true, skipped: false, note: '' });
        }
        if (content.slug) {
          fields.push({ key: 'slug', step: 'content', label: 'Slug پیشنهادی', value: content.slug || '', current: '', selected: false, skipped: false, note: 'اختیاری — در صورت تأیید روی محصول اعمال می‌شود' });
        }
        var tax = bag.taxonomies || {};
        if (tax.categories) {
          fields.push({ key: 'categories', step: 'taxonomies', label: 'دسته‌بندی‌ها', value: tax.categories || '', current: tax.current_categories || cur.categories || '', selected: true, skipped: false, note: '' });
        }
        if (tax.tags) {
          fields.push({ key: 'tags', step: 'taxonomies', label: 'برچسب‌ها', value: tax.tags || '', current: tax.current_tags || cur.tags || '', selected: true, skipped: false, note: '' });
        }
        if (tax.keywords) {
          fields.push({ key: 'keywords', step: 'taxonomies', label: 'کلمات کلیدی مرتبط', value: tax.keywords || '', current: '', selected: true, skipped: false, note: 'علاوه بر کلمه کلیدی اصلی' });
        }
        var schema = bag.schema || {};
        if (schema.schema_json) {
          fields.push({ key: 'schema_json', step: 'schema', label: 'Schema Markup (JSON-LD)', value: schema.schema_json || '', current: cur.schema_json || '', selected: true, skipped: false, note: '' });
        }
        var aeo = bag.aeo || {};
        if (aeo.faq_html) {
          fields.push({ key: 'faq_html', step: 'aeo', label: 'پرسش‌های متداول (AEO)', value: aeo.faq_html || '', current: cur.faq_html || '', selected: true, skipped: false, note: '' });
        } else if (aeo.faq) {
          fields.push({ key: 'faq_html', step: 'aeo', label: 'پرسش‌های متداول (AEO)', value: JSON.stringify(aeo.faq, null, 2), current: cur.faq_html || '', selected: true, skipped: false, note: '' });
        }
        var geo = bag.geo || {};
        if (geo.geo_html) {
          fields.push({ key: 'geo_html', step: 'geo', label: 'خلاصه GEO', value: geo.geo_html || '', current: cur.geo_html || '', selected: true, skipped: false, note: '' });
        } else if (geo.geo_summary) {
          fields.push({ key: 'geo_html', step: 'geo', label: 'خلاصه GEO', value: geo.geo_summary || '', current: cur.geo_html || '', selected: true, skipped: false, note: '' });
        }
        var links = bag.links || {};
        if (links.internal_links && links.internal_links.length) {
          var linkTxt = links.internal_links.map(function (l) { return (l.title || '') + ' → ' + (l.url || ''); }).join('\n');
          fields.push({ key: 'internal_links', step: 'links', label: 'لینک‌های داخلی پیشنهادی', value: linkTxt, current: '', selected: true, skipped: false, note: 'این لینک‌ها فقط به‌عنوان پیشنهاد ذخیره می‌شوند' });
        }
        var images = bag.images || {};
        if (images.alt) {
          var dimNote = '';
          if (images.analysis) {
            dimNote = 'ابعاد: ' + (images.analysis.width || '?') + '×' + (images.analysis.height || '?') +
              ' · ' + (images.analysis.kb || 0) + ' KB';
          }
          fields.push({ key: 'image_alt', step: 'images', label: 'متن جایگزین تصویر (Alt)', value: images.alt || '', current: cur.image_alt || images.current_alt || '', selected: true, skipped: false, note: dimNote || images.note || '' });
          if (images.compress_available && images.compress) {
            fields.push({ key: 'image_compress', step: 'images', label: 'فشرده‌سازی تصویر (WebP)', value: '1', current: '', selected: true, skipped: false, note: 'پس از تأیید با ماژول رسانه اجرا می‌شود · ' + (images.note || '') });
          } else if (images.compress_available) {
            fields.push({ key: 'image_compress', step: 'images', label: 'فشرده‌سازی تصویر (اختیاری)', value: '1', current: '', selected: false, skipped: false, note: 'ابعاد/حجم در محدوده مناسب است — در صورت تمایل فعال کنید' });
          }
        } else if (images.note) {
          fields.push({ key: 'image_alt', step: 'images', label: 'تصاویر', value: '', current: cur.image_alt || '', selected: false, skipped: true, note: images.note });
        }
        var cover = bag.cover || {};
        if (cover.skipped) {
          fields.push({ key: 'cover', step: 'cover', label: 'تصویر کاور', value: '', current: '', selected: false, skipped: true, note: cover.note || 'رد شد' });
        } else if (cover.attachment_id || cover.url) {
          fields.push({ key: 'cover_image', step: 'cover', label: 'تصویر کاور تولیدشده', value: String(cover.attachment_id || ''), current: '', selected: true, skipped: false, note: cover.note || 'پس از تأیید به‌عنوان تصویر شاخص ذخیره می‌شود', preview: cover.url || '' });
        } else if (cover.prompt) {
          fields.push({ key: 'cover_prompt', step: 'cover', label: 'پرامپت تصویر کاور', value: cover.prompt || '', current: '', selected: false, skipped: false, note: cover.note || 'موتور تصویر پیکربندی نشده — فقط پرامپت ذخیره می‌شود' });
        }
        this.wizardFields = fields;
      },

      wizardDoneCount: function () {
        return (this.wizard.steps || []).filter(function (s) { return s.status === 'done'; }).length;
      },
      wizardErrorCount: function () {
        return (this.wizard.steps || []).filter(function (s) { return s.status === 'error'; }).length;
      },
      selectAllFields: function (val) {
        (this.wizardFields || []).forEach(function (f) {
          if (!f.skipped) f.selected = !!val;
        });
      },
      hasSelectedFields: function () {
        return (this.wizardFields || []).some(function (f) { return f.selected && !f.skipped; });
      },
      selectedFieldCount: function () {
        return (this.wizardFields || []).filter(function (f) { return f.selected && !f.skipped; }).length;
      },

      rerunWizard: function () {
        this.wizard.modal = false;
        this.wizardFields = [];
        if (this.wizard.product) this.startWizard(this.wizard.product);
      },


      openReport: function (item) {
        var self = this;
        self.report = { open: true, loading: true, data: null };
        post('bankai_store_product_report', { product_id: item.id }).then(function (res) {
          self.report.loading = false;
          if (res && res.success && res.data) {
            self.report.data = res.data;
          } else {
            toast((res && res.data && res.data.message) || 'خطا در گزارش', 'error');
            self.report.open = false;
          }
        }).catch(function () {
          self.report.loading = false;
          self.report.open = false;
          toast('خطای شبکه', 'error');
        });
      },

      reportBarH: function (b) {
        var series = (this.report.data && this.report.data.series) || [];
        var max = 1;
        series.forEach(function (p) { if (Number(p.qty) > max) max = Number(p.qty); });
        return Math.max(4, Math.round((Number(b.qty || 0) / max) * 100));
      },

      genCover: function (item) {
        var self = this;
        if (!item || !item.id) return;
        self.coverBusy = item.id;
        post('bankai_store_gen_cover', { product_id: item.id, apply: 1 }).then(function (res) {
          self.coverBusy = 0;
          if (res && res.success) {
            toast('کاور ایجاد و روی محصول تنظیم شد', 'success');
            if (res.data && res.data.product) {
              // Update item in list
              self.items = (self.items || []).map(function (it) {
                return it.id === item.id ? Object.assign({}, it, res.data.product) : it;
              });
            } else {
              self.loadProducts();
            }
          } else {
            var msg = (res && res.data && res.data.message) || 'خطا در تولید کاور';
            toast(msg, 'error');
          }
        }).catch(function () {
          self.coverBusy = 0;
          toast('خطای شبکه', 'error');
        });
      },

      buildProposedPayload: function () {
        var proposed = {};
        var bagImages = (this.wizard.bag && this.wizard.bag.images) || {};
        var attachId = bagImages.attachment_id || 0;
        (this.wizardFields || []).forEach(function (f) {
          if (!f.selected || f.skipped) return;
          if (!proposed[f.step]) proposed[f.step] = {};
          if (f.key === 'seo_title') proposed.meta = proposed.meta || {}, proposed.meta.seo_title = f.value;
          else if (f.key === 'seo_description') proposed.meta = proposed.meta || {}, proposed.meta.description = f.value;
          else if (f.key === 'focus_keyword') proposed.meta = proposed.meta || {}, proposed.meta.focus_keyword = f.value;
          else if (f.key === 'short_description') proposed.content = proposed.content || {}, proposed.content.short_description = f.value;
          else if (f.key === 'description') proposed.content = proposed.content || {}, proposed.content.description = f.value;
          else if (f.key === 'slug') proposed.content = proposed.content || {}, proposed.content.slug = f.value;
          else if (f.key === 'categories') proposed.taxonomies = proposed.taxonomies || {}, proposed.taxonomies.categories = f.value;
          else if (f.key === 'tags') proposed.taxonomies = proposed.taxonomies || {}, proposed.taxonomies.tags = f.value;
          else if (f.key === 'keywords') proposed.taxonomies = proposed.taxonomies || {}, proposed.taxonomies.keywords = f.value;
          else if (f.key === 'schema_json') proposed.schema = proposed.schema || {}, proposed.schema.schema_json = f.value;
          else if (f.key === 'faq_html') proposed.aeo = proposed.aeo || {}, proposed.aeo.faq_html = f.value;
          else if (f.key === 'geo_html') proposed.geo = proposed.geo || {}, proposed.geo.geo_html = f.value;
          else if (f.key === 'internal_links') proposed.links = proposed.links || {}, proposed.links.raw = f.value;
          else if (f.key === 'image_alt') {
            proposed.images = proposed.images || {};
            proposed.images.alt = f.value;
            if (attachId) proposed.images.attachment_id = attachId;
          }
          else if (f.key === 'image_compress') {
            proposed.images = proposed.images || {};
            proposed.images.do_compress = 1;
            if (attachId) proposed.images.attachment_id = attachId;
          }
          else if (f.key === 'cover_image' && f.value) proposed.cover = proposed.cover || {}, proposed.cover.attachment_id = parseInt(f.value, 10) || 0;
        });
        return proposed;
      },

      applyWizard: function (force) {
        var self = this;
        if (!self.wizard.id || !self.hasSelectedFields()) return;
        self.wizard.applying = true;
        self.wizard.conflict = { message: '', current: null, proposed: null };

        var payload = {
          product_id: self.wizard.id,
          proposed: self.buildProposedPayload()
        };
        if (force) payload.force = 1;

        post('bankai_store_wizard_apply', payload).then(function (res) {
          self.wizard.applying = false;
          if (res && res.success) {
            toast('تغییرات انتخاب‌شده اعمال شد', 'success');
            self.wizard.modal = false;
            self.wizard.conflict = { message: '', current: null, proposed: null };
            self.wizardFields = [];
            if (res.data && res.data.product) {
              var idx = self.items.findIndex(function (x) { return x.id === res.data.product.id; });
              if (idx >= 0) self.items.splice(idx, 1, res.data.product);
            } else {
              self.loadProducts();
            }
          } else if (res && res.data && res.data.code === 'conflict') {
            self.wizard.conflict = res.data;
            toast(res.data.message || 'تعارض نسخه محصول', 'error');
          } else {
            toast((res && res.data && res.data.message) || 'خطا در اعمال', 'error');
          }
        }).catch(function () {
          self.wizard.applying = false;
          toast('خطای شبکه', 'error');
        });
      },

      forceApplyWizard: function () {
        this.applyWizard(true);
      },

      genCoverPrompt: function () {
        var t = (this.create.name || '').trim();
        if (!t) return;
        this.create.coverPrompt = 'Professional ecommerce product photography of "' + t + '", studio lighting, clean white background, catalog style, no text, no watermark';
        toast('پرامپت تصویر کاور آماده شد', 'success');
      },

      openCreate: function () {
        this.create = {
          open: true, name: '', short_description: '', description: '',
          price: '', sale_price: '', sku: '', stock_status: 'instock',
          status: 'draft', product_type: 'simple',
          manage_stock: false, stock_qty: '', tags: '', category: 0,
          saving: false, aiBusy: false, coverPrompt: '',
          lastEditUrl: '', lastId: 0
        };
      },

      aiComplete: function () {
        var self = this;
        if (!self.create.name) return;
        self.create.aiBusy = true;
        post('bankai_store_ai_complete', { title: self.create.name }).then(function (res) {
          self.create.aiBusy = false;
          if (res && res.success && res.data && res.data.draft) {
            var d = res.data.draft;
            // Only fill empty fields; never invent price/SKU into filled values
            if (d.name && !self.create.name) self.create.name = d.name;
            else if (d.name) self.create.name = d.name; // title refinement ok
            if (d.short_description && !self.create.short_description) self.create.short_description = d.short_description;
            if (d.description && !self.create.description) self.create.description = d.description;
            // Price and SKU: never auto-fill from AI guess
            self.create._aiSeo = {
              seo_title: d.seo_title || '',
              description_meta: d.description_meta || '',
              focus_keyword: d.focus_keyword || '',
              faq: d.faq || null,
              geo_summary: d.geo_summary || ''
            };
            toast('فیلدهای متنی تکمیل شد — قیمت و SKU را خودتان وارد کنید', 'success');
          } else {
            toast('تکمیل AI ناموفق', 'error');
          }
        }).catch(function () {
          self.create.aiBusy = false;
          toast('خطای شبکه', 'error');
        });
      },

      saveProduct: function () {
        var self = this;
        if (!self.create.name || !String(self.create.name).trim()) {
          toast('عنوان محصول الزامی است', 'error');
          return;
        }
        self.create.saving = true;
        var payload = {
          name: self.create.name,
          short_description: self.create.short_description,
          description: self.create.description,
          price: self.create.price,
          sale_price: self.create.sale_price,
          sku: self.create.sku,
          stock_status: self.create.stock_status,
          status: self.create.status,
          product_type: self.create.product_type || 'simple',
          manage_stock: self.create.manage_stock ? 1 : 0,
          stock_qty: self.create.stock_qty,
          tags: self.create.tags || '',
          categories: self.create.category && self.create.category !== '0' ? JSON.stringify([parseInt(self.create.category, 10)]) : '[]'
        };
        var ai = self.create._aiSeo || {};
        if (ai.seo_title) payload.seo_title = ai.seo_title;
        if (ai.description_meta) payload.seo_description = ai.description_meta;
        if (ai.focus_keyword) payload.focus_keyword = ai.focus_keyword;
        if (ai.geo_summary) payload.geo_summary = ai.geo_summary;
        if (ai.faq) payload.faq_json = JSON.stringify(ai.faq);
        post('bankai_store_create_product', payload).then(function (res) {
          self.create.saving = false;
          if (res && res.success) {
            toast('محصول ثبت شد', 'success');
            if (res.data && res.data.product) {
              self.create.lastId = res.data.product.id || 0;
              self.create.lastEditUrl = res.data.product.edit_url || '';
            }
            self.create.open = false;
            self.loadProducts();
            self.loadStats();
            if (self.create.lastEditUrl) {
              // Soft hint via toast — user can open WC editor for variations etc.
              setTimeout(function () {
                toast('برای تنظیمات پیشرفته: ویرایش در ووکامرس', 'success');
              }, 600);
            }
          } else {
            toast((res && res.data && res.data.message) || 'خطا در ثبت', 'error');
          }
        }).catch(function () {
          self.create.saving = false;
          toast('خطای شبکه', 'error');
        });
      }
    };
  };
})();
