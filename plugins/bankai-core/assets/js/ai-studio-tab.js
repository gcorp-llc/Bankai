/* AI Studio tab module */
(function () {
  if (!window.bankaiAdminNonce) {
    window.bankaiAdminNonce = (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || '';
  }
  if (typeof ajaxurl === 'undefined') {
    window.ajaxurl = (window.bankaiCoreData && window.bankaiCoreData.ajaxUrl) || '/wp-admin/admin-ajax.php';
  }
})();
document.addEventListener('alpine:init', () => {
  Alpine.data('bankaiAiStudioRoot', () => ({
    busy: false,
    checkingConnections: false,
    addModal: false,
    aiMode: 'text',
    imageDefault: '',
    imgPurpose: 'cover',
    imgProvider: '',
    imgPostId: '',
    imgPrompt: '',
    imgBusy: false,
    imgResultUrl: '',
    imgHtml: '',
    imgMessage: '',
    defaultProvider: '',
    defaultProviderName: '',
    providerNames: '',
    hasKeyMap: {},
    imageHasKeyMap: {},
    statusMap: {},
    imageStatusMap: {},
    activeTextProviders: [],
    activeImageProviders: [],
    toast: { visible: false, message: '' },
    customForm: {
      id: '', name: '', type: 'openai_compat', endpoint: '', models: '',
      default_model: '', auth_header: 'Authorization', auth_prefix: 'Bearer ',
      extra_headers: '', api_key: '', tagline: 'ایجنت سفارشی', curl: ''
    },
    initPage() {
      this.checkingConnections = true;
      const data = new FormData();
      data.append('action', 'bankai_test_ai_connections');
      data.append('nonce', window.bankaiAdminNonce);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res && res.success && res.data) {
            const providers = res.data.providers || [];
            const imgProviders = res.data.image_providers || [];
            const activeText = [];
            const activeImg = [];

            providers.forEach(p => {
              this.statusMap[p.id] = { status: p.status, label: p.label };
              const isOk = p.status === 'ready' || p.ok || p.status !== 'missing_key';
              this.hasKeyMap[p.id] = isOk;
              if (isOk) {
                activeText.push({ id: p.id, name: p.name || p.id, model: p.model || '' });
              }
            });

            imgProviders.forEach(ip => {
              this.imageStatusMap[ip.id] = { status: ip.status, label: ip.label };
              const isOk = ip.status === 'ready' || ip.ok || ip.status !== 'missing_key';
              this.imageHasKeyMap[ip.id] = isOk;
              if (isOk) {
                activeImg.push({ id: ip.id, name: ip.name || ip.id, model: ip.model || '' });
              }
            });

            this.activeTextProviders = activeText;
            this.activeImageProviders = activeImg;

            if (activeText.length && (!this.defaultProvider || !this.hasKeyMap[this.defaultProvider])) {
              this.defaultProvider = activeText[0].id;
              this.updateDefaultProvider();
            }
            if (activeImg.length && (!this.imageDefault || !this.imageHasKeyMap[this.imageDefault])) {
              this.imageDefault = activeImg[0].id;
              this.setImageDefault();
            }
            if (!this.imgProvider && activeImg.length) {
              this.imgProvider = activeImg[0].id;
            }
          }
        })
        .catch(() => {})
        .finally(() => {
          this.checkingConnections = false;
        });
    },
    showToast(msg, type) {
      var message = String(msg || '');
      var t = type || 'info';
      if (!type) {
        if (/خطا|ناموفق|حذف نشد|مجاز نیست|network|fail|error/i.test(message)) t = 'error';
        else if (/حذف|هشدار|توجه/i.test(message)) t = 'warning';
        else if (/موفق|ذخیره|اضافه|متصل|آماده|ok|کپی|تغییر کرد|استخراج/i.test(message)) t = 'success';
        else t = 'info';
      }
      if (typeof window.showToast === 'function') {
        window.showToast(message, t);
        return;
      }
      if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function') {
        window.bankaiAdminInstance.showToast(message, t);
        return;
      }
      // fallback local (rarely used)
      this.toast.message = message;
      this.toast.visible = true;
      setTimeout(() => { this.toast.visible = false; }, 3200);
    },
    openAddModal() {
      this.resetCustomForm();
      this.addModal = true;
    },
    resetCustomForm() {
      this.customForm = {
        id: '', name: '', type: 'openai_compat', endpoint: '', models: '',
        default_model: '', auth_header: 'Authorization', auth_prefix: 'Bearer ',
        extra_headers: '', api_key: '', tagline: 'ایجنت سفارشی', curl: ''
      };
    },
    parseCurl() {
      const c = this.customForm.curl || '';
      const urlM = c.match(/curl\s+(?:-X\s+POST\s+)?["']([^"']+)["']/i) || c.match(/curl\s+-X\s+POST\s+(\S+)/i);
      if (urlM) this.customForm.endpoint = urlM[1];
      const dataM = c.match(/--data(?:-raw)?\s+['"]([\s\S]*?)['"]\s*$/m) || c.match(/--data(?:-raw)?\s+'([\s\S]*?)'/);
      let jsonStr = dataM ? dataM[1] : '';
      if (!jsonStr) {
        const brace = c.match(/\{[\s\S]*"model"[\s\S]*\}/);
        if (brace) jsonStr = brace[0];
      }
      try {
        const j = JSON.parse(jsonStr.replace(/\\'/g, "'"));
        if (j.model) {
          this.customForm.default_model = j.model;
          if (!this.customForm.models) this.customForm.models = j.model;
        }
      } catch (e) {}
      const authM = c.match(/-H\s+["']Authorization:\s*([^"']+)["']/i);
      if (authM) {
        const v = authM[1].trim();
        if (v.toLowerCase().startsWith('bearer ')) {
          this.customForm.auth_prefix = 'Bearer ';
          this.customForm.api_key = v.slice(7).trim();
        } else {
          this.customForm.api_key = v;
        }
      }
      if (!this.customForm.name && this.customForm.default_model) {
        this.customForm.name = this.customForm.default_model.split('/').pop();
      }
      this.showToast('از curl استخراج شد');
    },
    testAllConnections() {
      this.busy = true;
      const data = new FormData();
      data.append('action', 'bankai_test_ai_connections');
      data.append('nonce', window.bankaiAdminNonce);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          this.showToast(res.success ? 'تست انجام شد' : ((res.data && res.data.message) || 'خطا'));
          if (res.success) setTimeout(() => location.reload(), 600);
        })
        .catch(() => this.showToast('خطا در ارتباط'))
        .finally(() => { this.busy = false; });
    },
    updateDefaultProvider() {
      const names = this.providerNames || {};
      const id = String(this.defaultProvider || '').trim();
      if (!id) { this.showToast('ارائه‌دهنده انتخاب نشده', 'error'); return; }
      this.defaultProviderName = names[id] || id;
      const data = new FormData();
      data.append('action', 'bankai_set_default_provider');
      const nonce = window.bankaiAdminNonce
        || (window.bankaiCoreData && window.bankaiCoreData.adminNonce)
        || (window.bankaiData && window.bankaiData.adminNonce)
        || '';
      data.append('nonce', nonce);
      data.append('provider', id);
      fetch(ajaxurl || (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || '/wp-admin/admin-ajax.php', {
        method: 'POST', body: data, credentials: 'same-origin'
      })
        .then(r => r.json())
        .then(res => {
          if (res && res.success) {
            this.showToast('موتور پیش‌فرض ذخیره شد: ' + this.defaultProviderName, 'success');
          } else {
            const msg = (res && res.data && res.data.message) ? res.data.message : 'ذخیره نشد';
            this.showToast(msg, 'error');
          }
        })
        .catch(() => this.showToast('خطا در ارتباط با سرور', 'error'));
    },
    saveCustomAgent() {
      const f = this.customForm;
      if (!f.name || !f.name.trim()) { this.showToast('نام الزامی است'); return; }
      if (!f.endpoint || !f.endpoint.trim()) { this.showToast('Endpoint الزامی است'); return; }
      this.busy = true;
      const data = new FormData();
      data.append('action', 'bankai_save_custom_provider');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('id', f.id || '');
      data.append('name', f.name);
      data.append('type', 'openai_compat');
      data.append('endpoint', f.endpoint || '');
      data.append('models', f.models || f.default_model || '');
      data.append('default_model', f.default_model || '');
      data.append('auth_header', f.auth_header || 'Authorization');
      data.append('auth_prefix', f.auth_prefix || 'Bearer ');
      data.append('api_key', f.api_key || '');
      data.append('tagline', f.tagline || 'ایجنت سفارشی');
      data.append('curl_snippet', f.curl || '');
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.showToast(res.data?.message || 'ذخیره شد');
            setTimeout(() => location.reload(), 700);
          } else this.showToast((res.data && res.data.message) || 'خطا');
        })
        .catch(() => this.showToast('خطا در ارتباط'))
        .finally(() => { this.busy = false; });
    },

    setImageDefault() {
      const id = String(this.imageDefault || '').trim();
      if (!id) { this.showToast('ارائه‌دهنده تصویری انتخاب نشده', 'error'); return; }
      const data = new FormData();
      data.append('action', 'bankai_set_default_image_provider');
      const nonce = window.bankaiAdminNonce
        || (window.bankaiCoreData && window.bankaiCoreData.adminNonce)
        || (window.bankaiData && window.bankaiData.adminNonce)
        || '';
      data.append('nonce', nonce);
      data.append('provider', id);
      fetch(ajaxurl || (window.bankaiCoreData && bankaiCoreData.ajaxUrl) || '/wp-admin/admin-ajax.php', {
        method: 'POST', body: data, credentials: 'same-origin'
      })
        .then(r => r.json())
        .then(res => this.showToast(res.success ? 'پیش‌فرض تصویری ذخیره شد' : ((res.data && res.data.message) || 'خطا'), res.success ? 'success' : 'error'))
        .catch(() => this.showToast('خطا در ارتباط', 'error'));
    },
    async generateImage() {
      if (!this.imgPrompt.trim()) return;
      this.imgBusy = true;
      this.imgMessage = 'در حال تولید…';
      this.imgResultUrl = '';
      this.imgHtml = '';
      try {
        const data = new FormData();
        data.append('action', 'bankai_ai_image_generate');
        data.append('nonce', window.bankaiAdminNonce);
        data.append('provider', this.imgProvider || this.imageDefault);
        data.append('prompt', this.imgPrompt);
        data.append('purpose', this.imgPurpose || 'cover');
        data.append('post_id', this.imgPostId || '0');
        const r = await fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' });
        const j = await r.json();
        if (j.success && j.data) {
          this.imgResultUrl = j.data.url || '';
          this.imgHtml = j.data.html || '';
          this.imgMessage = j.data.message || 'موفق';
          this.showToast(this.imgMessage);
        } else {
          this.imgMessage = (j.data && j.data.message) || 'خطا در تولید';
          this.showToast(this.imgMessage);
        }
      } catch (e) {
        this.imgMessage = (e && e.message) || 'خطای شبکه';
        this.showToast(this.imgMessage);
      } finally {
        this.imgBusy = false;
      }
    },
    copyImgHtml() {
      if (!this.imgHtml) return;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(this.imgHtml).then(() => this.showToast('HTML کپی شد'));
      } else {
        this.showToast('کپی پشتیبانی نمی‌شود');
      }
    },
    toggleModule(modId, state) {
      const data = new FormData();
      data.append('action', 'bankai_toggle_ai_module');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('module_id', modId);
      data.append('active', state ? '1' : '0');
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => { if (res.success) this.showToast('وضعیت ماژول تغییر کرد'); });
    }
  }));


  Alpine.data('bankaiImageProviderCard', (config) => ({
    id: config.id,
    key: config.key || '',
    keyDirty: false,
    showKey: false,
    model: config.model || '',
    models: Array.isArray(config.models) ? config.models.slice() : [],
    hasKey: !!config.hasKey,
    masked: config.masked || config.key || '',
    status: config.status || 'missing_key',
    label: config.label || 'بدون کلید API',
    busy: false,
    saving: false,
    testing: false,
    editModels: false,
    newModel: '',
    openModelsEditor() { this.editModels = !this.editModels; },
    notify(msg, type) {
      this.bankaiNotify(msg, type);
    },

    bankaiNotify(msg, type) {
      var message = String(msg || '');
      var t = type || 'info';
      if (!type) {
        if (/خطا|ناموفق|حذف نشد|مجاز نیست|network|fail|error/i.test(message)) t = 'error';
        else if (/حذف|هشدار|توجه/i.test(message)) t = 'warning';
        else if (/موفق|ذخیره|اضافه|متصل|آماده|ok/i.test(message)) t = 'success';
        else t = 'info';
      }
      if (typeof window.showToast === 'function') {
        window.showToast(message, t);
        return;
      }
      if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function') {
        window.bankaiAdminInstance.showToast(message, t);
      }
    },

    saveModelOnly() {
      const data = new FormData();
      data.append('action', 'bankai_save_image_ai_key');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('api_key', '');
      data.append('model', this.model || '');
      fetch(ajaxurl, { method: 'POST', body: data });
    },
    addModel() {
      const m = (this.newModel || '').trim();
      if (!m) return;
      this.busy = true;
      const data = new FormData();
      data.append('action', 'bankai_add_image_model');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', m);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            if (!this.models.includes(m)) this.models.push(m);
            this.model = m;
            this.newModel = '';
            this.notify('مدل اضافه شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        })
        .finally(() => { this.busy = false; });
    },
    removeModel(m) {
      if (!m || !confirm('حذف مدل «' + m + '»؟')) return;
      const data = new FormData();
      data.append('action', 'bankai_remove_image_model');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', m);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.models = this.models.filter(x => x !== m);
            if (this.model === m) this.model = this.models[0] || '';
            this.notify('مدل حذف شد');
          }
        });
    },
    save() {
      this.saving = true;
      const data = new FormData();
      data.append('action', 'bankai_save_image_ai_key');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('api_key', (this.keyDirty && this.key && this.key.indexOf('•') === -1) ? this.key : '');
      data.append('model', this.model || '');
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.hasKey = true;
            this.status = 'ready';
            this.label = 'آماده';
            this.masked = (res.data && res.data.masked) || this.masked;
            if (this.keyDirty) { this.key = this.masked; this.keyDirty = false; }
            this.notify((res.data && res.data.message) || 'ذخیره شد');
          } else this.notify((res.data && res.data.message) || 'خطا در ذخیره');
        })
        .catch(() => this.notify('خطای شبکه'))
        .finally(() => { this.saving = false; });
    },
    clearKey() {
      if (!confirm('کلید حذف شود؟')) return;
      this.busy = true;
      const data = new FormData();
      data.append('action', 'bankai_clear_image_ai_key');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.hasKey = false;
            this.masked = '';
            this.key = '';
            this.keyDirty = false;
            this.status = 'missing_key';
            this.label = 'بدون کلید API';
            this.notify('کلید حذف شد');
          }
        })
        .finally(() => { this.busy = false; });
    },
    test() {
      this.testing = true;
      const data = new FormData();
      data.append('action', 'bankai_test_image_ai');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          const msg = res.success ? ((res.data && res.data.message) || 'اتصال موفق') : ((res.data && res.data.message) || 'ناموفق');
          this.notify(msg);
          if (res.success) { this.status = 'ready'; this.label = 'آماده'; this.hasKey = true; }
        })
        .catch(() => this.notify('خطای شبکه'))
        .finally(() => { this.testing = false; });
    }
  }));

  Alpine.data('bankaiProviderCard', (config) => ({
    id: config.id,
    key: config.key || '',
    keyDirty: false,
    model: config.model || '',
    status: config.status,
    label: config.label,
    models: Array.isArray(config.models) ? config.models.slice() : [],
    isCustom: !!config.isCustom,
    showKey: false,
    saving: false,
    testing: false,
    editModels: false,
    newModel: '',
    busyModel: false,
    openModelsEditor() { this.editModels = !this.editModels; },
    addModel() {
      const m = (this.newModel || '').trim();
      if (!m) return;
      this.busyModel = true;
      const data = new FormData();
      data.append('action', 'bankai_add_provider_model');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', m);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            if (!this.models.includes(m)) this.models.push(m);
            this.newModel = '';
            this.notify('مدل اضافه شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        })
        .finally(() => { this.busyModel = false; });
    },
    removeModel(m) {
      if (!m || !confirm('حذف مدل «' + m + '»؟')) return;
      const data = new FormData();
      data.append('action', 'bankai_remove_provider_model');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', m);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.models = this.models.filter(x => x !== m);
            if (this.model === m && this.models.length) this.model = this.models[0];
            this.notify('مدل حذف شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        });
    },
    save() {
      this.saving = true;
      const data = new FormData();
      data.append('action', 'bankai_save_ai_keys');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('key', this.keyDirty ? this.key : '__unchanged__');
      data.append('model', this.model);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.status = res.data.status;
            this.label = res.data.label;
            if (res.data.masked_key) this.key = res.data.masked_key;
            this.keyDirty = false;
            this.notify('ذخیره شد');
          } else this.notify((res.data && res.data.message) || 'خطا');
        })
        .catch(() => this.notify('خطا'))
        .finally(() => { this.saving = false; });
    },
    clearKey() {
      if (!confirm('حذف کلید؟')) return;
      const data = new FormData();
      data.append('action', 'bankai_clear_ai_key');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            this.key = ''; this.keyDirty = false;
            this.status = res.data.status; this.label = res.data.label;
          }
        });
    },
    test() {
      this.testing = true;
      const data = new FormData();
      // تست فقط با مدل فعال همین کارت
      data.append('action', 'bankai_ai_test_provider');
      data.append('nonce', window.bankaiAdminNonce);
      data.append('provider', this.id);
      data.append('model', this.model || '');
      fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            const m = (res.data && res.data.model) ? res.data.model : (this.model || '');
            this.status = 'ready';
            this.label = 'متصل ✓';
            this.notify('موفق با مدل فعال: ' + m + (res.data && res.data.preview ? (' — ' + res.data.preview) : ''), 'success');
          } else {
            this.status = 'error';
            this.label = 'خطا';
            this.notify((res.data && res.data.message) || 'ناموفق', 'error');
          }
        })
        .catch(() => this.notify('خطای شبکه', 'error'))
        .finally(() => { this.testing = false; });
    },
    notify(msg, type) {
      var message = String(msg || '');
      var t = type || 'info';
      if (!type) {
        if (/خطا|ناموفق|حذف نشد|مجاز نیست|network|fail|error/i.test(message)) t = 'error';
        else if (/حذف|هشدار|توجه/i.test(message)) t = 'warning';
        else if (/موفق|ذخیره|اضافه|متصل|آماده|ok/i.test(message)) t = 'success';
        else t = 'info';
      }
      if (typeof window.showToast === 'function') {
        window.showToast(message, t);
        return;
      }
      if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function') {
        window.bankaiAdminInstance.showToast(message, t);
      }
    }
  }));
});
document.addEventListener('bk-delete-provider', (e) => {
  const id = e.detail && e.detail.id;
  if (!id || !confirm('حذف این ایجنت؟')) return;
  const data = new FormData();
  data.append('action', 'bankai_delete_custom_provider');
  data.append('nonce', window.bankaiAdminNonce);
  data.append('id', id);
  fetch(ajaxurl, { method: 'POST', body: data })
    .then(r => r.json())
    .then(res => { if (res.success) { if (window.showToast) window.showToast('ایجنت حذف شد', 'success'); location.reload(); } else if (window.showToast) window.showToast((res.data && res.data.message) || 'خطا', 'error'); });
});
