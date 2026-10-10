/* Bankai Speed & Cache tab module */
(function () {
  function rootData() {
    var el = document.getElementById('bankai-admin-app');
    if (el && el._x_dataStack && el._x_dataStack[0]) return el._x_dataStack[0];
    return null;
  }

  document.addEventListener('bankai:init', () => {
    BankaiApp.data('bankaiSpeedTab', (savedCfg) => ({
      speedTab: 'config',
      openOpt: '',
      cfg: {},
      busySave: {},
      busyPurge: false,
      busyDb: false,
      busyRedisTest: false,
      busyRedisSave: false,
      redisLocal: { enabled: false, host: '127.0.0.1', port: 6379, password: '', db: 0, prefix: 'bankai:', timeout: 1.5 },
      speedOptions: [
        { id: 'page_caching', badge: 'CACHE', badgeClass: 'is-green', title: 'کش صفحات', desc: 'صفحات HTML را به‌صورت استاتیک ذخیره می‌کند تا TTFB کاهش یابد. می‌توانید TTL، کاربران واردشده و آدرس‌های مستثنی را تنظیم کنید.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0 1 14.25 16H1.75A1.75 1.75 0 0 1 0 14.25Zm1.75-.25a.25.25 0 0 0-.25.25v12.5c0 .138.112.25.25.25h12.5a.25.25 0 0 0 .25-.25V1.75a.25.25 0 0 0-.25-.25ZM3.5 7.75a.75.75 0 0 1 .75-.75h7a.75.75 0 0 1 0 1.5h-7a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5Z"/></svg>' },
        { id: 'asset_optimization', badge: 'ASSETS', badgeClass: 'is-blue', title: 'CSS و JS', desc: 'فشرده‌سازی و بهینه‌سازی فایل‌های CSS و JS: Minify، ترکیب، Defer/Delay و حذف CSS استفاده‌نشده برای بهبود نمره سرعت.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4.72 9.57 2.22 7.07a.75.75 0 0 1 0-1.06l2.5-2.5a.75.75 0 0 1 1.06 1.06L4.06 6.5h6.38a.75.75 0 0 1 0 1.5H4.06l1.72 1.72a.75.75 0 1 1-1.06 1.06Zm6.56-3.14 2.5 2.5a.75.75 0 0 1 0 1.06l-2.5 2.5a.75.75 0 1 1-1.06-1.06l1.72-1.72H5.56a.75.75 0 0 1 0-1.5h6.38L10.22 6.49a.75.75 0 0 1 1.06-1.06Z"/></svg>' },
        { id: 'media_lazy', badge: 'MEDIA', badgeClass: 'is-pink', title: 'LazyLoad رسانه', desc: 'بارگذاری تنبل تصاویر، iframe و پیش‌نمایش YouTube تا اولین رندر سریع‌تر شود و پهنای باند ذخیره گردد.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1.75 2.5a.25.25 0 0 0-.25.25v10.5c0 .138.112.25.25.25h.94a.75.75 0 0 1 0 1.5H1.75A1.75 1.75 0 0 1 0 13.25V2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0 1 14.25 15h-.94a.75.75 0 0 1 0-1.5h.94a.25.25 0 0 0 .25-.25V2.75a.25.25 0 0 0-.25-.25Z"/></svg>' },
        { id: 'preload', badge: 'PRELOAD', badgeClass: 'is-violet', title: 'Preload / Prefetch', desc: 'Preload فونت‌های حیاتی، DNS-Prefetch و Preconnect به دامنه‌های خارجی برای کاهش تاخیر اتصال.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M7.47 10.78a.75.75 0 0 0 1.06 0l3.75-3.75a.75.75 0 0 0-1.06-1.06L8.75 8.44V1.75a.75.75 0 0 0-1.5 0v6.69L4.78 5.97a.75.75 0 0 0-1.06 1.06l3.75 3.75ZM3.75 13a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5Z"/></svg>' },
        { id: 'browser_cache', badge: 'BROWSER', badgeClass: 'is-blue', title: 'کش مرورگر', desc: 'هدرهای Cache-Control، ETag و فشرده‌سازی Gzip/Brotli برای دارایی‌های استاتیک در مرورگر کاربر.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm4.879-2.773 4.264 2.558a.25.25 0 0 1 0 .43l-4.264 2.558A.25.25 0 0 1 6 10.558V5.442a.25.25 0 0 1 .379-.215Z"/></svg>' },
        { id: 'database_optimizer', badge: 'DB', badgeClass: 'is-amber', title: 'پاکسازی دیتابیس', desc: 'پاکسازی رونوشت‌های قدیمی، پیش‌نویس خودکار، زباله‌دان، اسپم و transientهای منقضی برای سبک‌تر شدن دیتابیس.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 0 0-1.5.14v1.53A6.5 6.5 0 1 1 1.67 8H.14A8 8 0 1 0 8 0ZM2.5 8a5.5 5.5 0 1 0 5.5-5.5V8Z"/></svg>' },
        { id: 'heartbeat', badge: 'WP', badgeClass: 'is-amber', title: 'Heartbeat API', desc: 'کنترل فاصله Heartbeat وردپرس در ادمین و فرانت برای کاهش بار CPU و درخواست‌های AJAX بی‌مورد.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="m8 14.25.345.666a.75.75 0 0 0 .61 0L9 14.25l-.345-.666a.75.75 0 0 0-.61 0Zm-4.808-1.288-.346-.666a.75.75 0 0 1 .61-1.088h.006l1.354.001 1.14-2.85a.75.75 0 0 1 1.4.01L8 12.25h4.192a.75.75 0 0 1 .61 1.088l-.345.666a.748.748 0 0 1-.61.336H3.402a.75.75 0 0 1-.61-.338Z"/></svg>' },
        { id: 'object_cache', badge: 'REDIS', badgeClass: 'is-green', title: 'Object Cache', desc: 'کش اشیاء وردپرس روی Redis برای کوئری‌های پرتکرار. اتصال و تست را از تب Redis انجام دهید.', svg: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0 1 14.25 15H1.75A1.75 1.75 0 0 1 0 13.25ZM1.75 2.5a.25.25 0 0 0-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 0 0 .25-.25V2.75a.25.25 0 0 0-.25-.25Z"/></svg>' },
      ],
      get redisSaving() { var r = rootData(); return r ? !!r.redisSaving : false; },
      get redisStatus() { var r = rootData(); return r ? r.redisStatus : { ok: false, message: '' }; },
      get speedStats() { var r = rootData(); return r ? (r.speedStats || {}) : {}; },
      toast(msg, type) {
        var r = rootData();
        if (r && typeof r.showToast === 'function') r.showToast(msg, type || 'success');
      },
      initSpeed() {
        const defaults = {
          page_caching: { enabled: true, ttl: 86400, skip_logged_in: '1', skip_query: '1', exclusions: '' },
          asset_optimization: { enabled: true, minify_css: true, minify_js: true, combine_css: false, defer_js: true, delay_js: false, remove_unused_css: false, exclude_js: '', exclude_css: '' },
          media_lazy: { enabled: true, images: true, iframes: true, youtube: true, threshold: 200 },
          preload: { enabled: true, fonts: true, links: false, dns: true, preconnect: 'https://fonts.gstatic.com' },
          browser_cache: { enabled: true, ttl: 31536000, etag: true, gzip: true },
          database_optimizer: { enabled: true, revisions: true, auto_drafts: true, trashed: true, spam: true, transients: true, revisions_limit: 5 },
          heartbeat: { enabled: true, admin_interval: 60, front_interval: 0, disable_front: true },
          object_cache: { enabled: true },
        };
        const incoming = (savedCfg && typeof savedCfg === 'object') ? savedCfg : {};
        this.cfg = {};
        Object.keys(defaults).forEach((k) => {
          this.cfg[k] = Object.assign({}, defaults[k], incoming[k] || {});
        });
        this.loadRedisSettings();
        setTimeout(() => { try { this.testRedisQuiet(); } catch(e){} }, 400);
      },
      isOptOn(id) {
        return !!(this.cfg[id] && this.cfg[id].enabled !== false);
      },
      toggleOpt(id, on) {
        if (!this.cfg[id]) this.cfg[id] = { enabled: on };
        else this.cfg[id].enabled = on;
        this.saveOpt(id);
      },
      saveOpt(id) {
        this.busySave[id] = true;
        const data = new FormData();
        data.append('action', 'bankai_save_speed_settings');
        data.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || window.bankaiAdminNonce || '');
        data.append('option_id', id);
        data.append('settings', JSON.stringify(this.cfg[id] || {}));
        data.append('speed_config', JSON.stringify(this.cfg));
        fetch((window.bankaiCoreData && window.bankaiCoreData.ajaxUrl) || window.ajaxurl || '', { method: 'POST', body: data, credentials: 'same-origin' })
          .then(r => r.json())
          .then(res => this.toast(res.success ? 'تنظیمات ذخیره شد' : ((res.data && res.data.message) || 'خطا'), res.success ? 'success' : 'error'))
          .catch(() => this.toast('خطای شبکه', 'error'))
          .finally(() => { this.busySave[id] = false; });
      },
      purgePageCache() {
        this.busyPurge = true;
        const data = new FormData();
        data.append('action', 'bankai_purge_page_cache');
        data.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || window.bankaiAdminNonce || '');
        fetch((window.bankaiCoreData && window.bankaiCoreData.ajaxUrl) || window.ajaxurl || '', { method: 'POST', body: data, credentials: 'same-origin' })
          .then(r => r.json())
          .then(res => this.toast(res.success ? 'کش پاک شد' : 'خطا', res.success ? 'success' : 'error'))
          .finally(() => { this.busyPurge = false; });
      },
      runDbOptimize() {
        this.busyDb = true;
        const data = new FormData();
        data.append('action', 'bankai_optimize_database');
        data.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || window.bankaiAdminNonce || '');
        fetch((window.bankaiCoreData && window.bankaiCoreData.ajaxUrl) || window.ajaxurl || '', { method: 'POST', body: data, credentials: 'same-origin' })
          .then(r => r.json())
          .then(res => this.toast(res.success ? 'پاکسازی انجام شد' : ((res.data && res.data.message) || 'خطا'), res.success ? 'success' : 'error'))
          .finally(() => { this.busyDb = false; });
      },
      loadRedisSettings() {
        var r = rootData();
        if (r && typeof r.loadRedisSettings === 'function') r.loadRedisSettings();
        setTimeout(() => {
          var rr = rootData();
          if (rr && rr.redisSettings) this.redisLocal = Object.assign({}, this.redisLocal, rr.redisSettings);
        }, 280);
      },
      testRedisQuiet() {
        var r = rootData();
        if (r && typeof r.testRedisConnection === 'function') {
          // silent status check
          try { r.testRedisConnection(); } catch (e) {}
        }
      },
      testRedisConnection() {
        this.busyRedisTest = true;
        var r = rootData();
        if (r) r.redisSettings = Object.assign({}, r.redisSettings || {}, this.redisLocal);
        if (r && typeof r.testRedisConnection === 'function') {
          Promise.resolve(r.testRedisConnection()).finally(() => { this.busyRedisTest = false; });
          setTimeout(() => { this.busyRedisTest = false; }, 4000);
        } else {
          this.busyRedisTest = false;
        }
      },
      saveRedisSettings() {
        this.busyRedisSave = true;
        var r = rootData();
        if (r) r.redisSettings = Object.assign({}, r.redisSettings || {}, this.redisLocal);
        if (r && typeof r.saveRedisSettings === 'function') {
          Promise.resolve(r.saveRedisSettings()).finally(() => { this.busyRedisSave = false; });
          setTimeout(() => { this.busyRedisSave = false; }, 4000);
        } else {
          this.busyRedisSave = false;
        }
      },
    }));
  });
})();
