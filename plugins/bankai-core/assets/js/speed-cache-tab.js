/**
 * Bankai Speed & Cache - Pure Vanilla JS Controller
 * Handles Performance Audit, Fix All, Redis, Database Optimization, and Settings.
 */
document.addEventListener('DOMContentLoaded', function () {
  const root = document.getElementById('tab-speed-cache');
  if (!root) return;

  const getNonce = () => (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || window.bankaiAdminNonce || '';
  const getAjaxUrl = () => (window.bankaiCoreData && window.bankaiCoreData.ajaxUrl) || window.ajaxurl || '';
  const getRestUrl = (path) => {
    const base = (window.bankaiCoreData && window.bankaiCoreData.restUrl) || '/wp-json/';
    return base.replace(/\/$/, '') + path;
  };

  const showToast = (msg, type = 'success', duration) => {
    if (typeof window.bankaiShowToast === 'function') {
      window.bankaiShowToast(msg, type, duration);
    } else if (typeof window.showToast === 'function' && window.showToast !== showToast) {
      window.showToast(msg, type, duration);
    }
  };

  /* ========== Professional Operation Progress Controller ========== */
  const opBox = () => root.querySelector('#bk-speed-op-progress');
  const opTitle = () => root.querySelector('#bk-op-title');
  const opPct = () => root.querySelector('#bk-op-pct');
  const opFill = () => root.querySelector('#bk-op-fill');
  const opMsg = () => root.querySelector('#bk-op-msg');
  const opSteps = () => root.querySelector('#bk-op-steps');

  function showOpProgress(title, steps) {
    const box = opBox();
    if (!box) return;
    box.style.display = 'block';
    if (opTitle()) opTitle().textContent = title || 'در حال اجرا…';
    if (opPct()) opPct().textContent = '0%';
    if (opFill()) opFill().style.width = '0%';
    if (opMsg()) opMsg().textContent = 'آماده‌سازی…';
    if (opSteps() && Array.isArray(steps)) {
      opSteps().innerHTML = steps.map((s, i) =>
        `<span class="bk-stage-row" data-idx="${i}" style="display:inline-flex;padding:4px 10px;margin:0;font-size:11px">
          <span class="bk-stage-icon" style="width:18px;height:18px;font-size:10px">${i + 1}</span>
          <span>${s}</span>
        </span>`
      ).join('');
    }
  }
  function updateOpProgress(pct, msg, stepIdx) {
    if (opPct()) opPct().textContent = Math.round(pct) + '%';
    if (opFill()) opFill().style.width = Math.min(100, pct) + '%';
    if (opMsg() && msg) opMsg().textContent = msg;
    if (typeof stepIdx === 'number' && opSteps()) {
      opSteps().querySelectorAll('.bk-stage-row').forEach((el, i) => {
        el.classList.remove('is-running', 'is-done');
        if (i < stepIdx) el.classList.add('is-done');
        else if (i === stepIdx) el.classList.add('is-running');
      });
    }
  }
  function hideOpProgress(delay) {
    setTimeout(() => {
      const box = opBox();
      if (box) box.style.display = 'none';
    }, delay || 1200);
  }

  function setStageStatus(stageId, status, label) {
    const row = root.querySelector('#bk-fix-all-stages [data-stage="' + stageId + '"]');
    if (!row) return;
    row.classList.remove('is-running', 'is-done', 'is-error');
    if (status === 'running') row.classList.add('is-running');
    if (status === 'done') row.classList.add('is-done');
    if (status === 'error') row.classList.add('is-error');
    const st = row.querySelector('.bk-wpr-stage__st, .bk-stage-status');
    if (st) st.textContent = label || (status === 'done' ? '✓' : status === 'running' ? '…' : '—');
  }

  // State
  let speedConfig = Object.assign({
    page_caching: { enabled: true, ttl: 86400, exclusions: '' },
    asset_optimization: { enabled: true, minify_css: true, minify_js: true, defer_js: true, delay_js: true },
    cwv_lighthouse: { enabled: true, font_display: true, cls_images: true, async_css: true },
    media_lazy: { enabled: true },
    preload: { enabled: true },
    heartbeat: { enabled: true },
    browser_cache: { enabled: true, ttl: 31536000 }
  }, window.bankaiSpeedInitialCfg || {});

  /* Form Hydration from Saved Config */
  function hydrateFormFields() {
    // Page Cache
    const pcEn = root.querySelector('#cfg-page-cache-enable');
    const pcTtl = root.querySelector('#cfg-page-cache-ttl');
    const pcExcl = root.querySelector('#cfg-page-cache-excl');
    if (pcEn) pcEn.checked = !!(speedConfig.page_caching && speedConfig.page_caching.enabled !== false);
    if (pcTtl && speedConfig.page_caching && speedConfig.page_caching.ttl) pcTtl.value = speedConfig.page_caching.ttl;
    if (pcExcl && speedConfig.page_caching && speedConfig.page_caching.exclusions) pcExcl.value = speedConfig.page_caching.exclusions;

    // Assets
    const astEn = root.querySelector('#cfg-assets-enable');
    const minCss = root.querySelector('#cfg-asset-minify-css');
    const minJs = root.querySelector('#cfg-asset-minify-js');
    const defJs = root.querySelector('#cfg-asset-defer-js');
    const delJs = root.querySelector('#cfg-asset-delay-js');
    if (astEn) astEn.checked = !!(speedConfig.asset_optimization && speedConfig.asset_optimization.enabled !== false);
    if (minCss) minCss.checked = !!(speedConfig.asset_optimization && speedConfig.asset_optimization.minify_css !== false);
    if (minJs) minJs.checked = !!(speedConfig.asset_optimization && speedConfig.asset_optimization.minify_js !== false);
    if (defJs) defJs.checked = !!(speedConfig.asset_optimization && speedConfig.asset_optimization.defer_js !== false);
    if (delJs) delJs.checked = !!(speedConfig.asset_optimization && speedConfig.asset_optimization.delay_js !== false);

    // CWV, Media, Preload, Heartbeat, Browser Cache
    const cwvEn = root.querySelector('#cfg-cwv-enable');
    if (cwvEn) cwvEn.checked = !!(speedConfig.cwv_lighthouse && speedConfig.cwv_lighthouse.enabled !== false);

    const mediaEn = root.querySelector('#cfg-media-enable');
    if (mediaEn) mediaEn.checked = !!(speedConfig.media_lazy && speedConfig.media_lazy.enabled !== false);

    const preloadEn = root.querySelector('#cfg-preload-enable');
    if (preloadEn) preloadEn.checked = !!(speedConfig.preload && speedConfig.preload.enabled !== false);

    const hbEn = root.querySelector('#cfg-heartbeat-enable');
    if (hbEn) hbEn.checked = !!(speedConfig.heartbeat && speedConfig.heartbeat.enabled !== false);

    const bcEn = root.querySelector('#cfg-browser-enable');
    if (bcEn) bcEn.checked = !!(speedConfig.browser_cache && speedConfig.browser_cache.enabled !== false);

    const fontsLocal = root.querySelector('#cfg-fonts-local');
    if (fontsLocal) fontsLocal.checked = !!(speedConfig.fonts_localizer && speedConfig.fonts_localizer.enabled !== false);
    const fontsSwap = root.querySelector('#cfg-fonts-display-swap');
    if (fontsSwap) fontsSwap.checked = !(speedConfig.fonts_localizer && speedConfig.fonts_localizer.display_swap === false);
    const fontsPre = root.querySelector('#cfg-fonts-preload-critical');
    if (fontsPre) fontsPre.checked = !(speedConfig.fonts_localizer && speedConfig.fonts_localizer.preload_critical === false);
  }

  hydrateFormFields();

  /* Section Navigation (left sidebar — WP Rocket style) */
  const navItems = root.querySelectorAll('.bk-wpr-nav__item');
  const sections = root.querySelectorAll('.bk-wpr-section');

  navItems.forEach((btn) => {
    btn.addEventListener('click', function () {
      const sec = this.getAttribute('data-section');
      navItems.forEach((b) => b.classList.remove('is-active'));
      sections.forEach((s) => s.classList.remove('is-active'));
      this.classList.add('is-active');
      const target = root.querySelector('.bk-wpr-section[data-section="' + sec + '"]');
      if (target) target.classList.add('is-active');
      if (sec === 'cdn' && typeof loadRedisSettings === 'function') loadRedisSettings();
    });
  });

  /* Device Toggle for Audit */
  let activeStrategy = 'mobile';
  const deviceBtns = root.querySelectorAll('.bk-wpr-device-btn, .bk-audit-device-btn');
  deviceBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      deviceBtns.forEach((b) => b.classList.remove('is-active'));
      this.classList.add('is-active');
      activeStrategy = this.getAttribute('data-strategy') || 'mobile';
    });
  });

  /* Run Performance Test */
  const runAuditBtn = root.querySelector('#bk-run-audit-btn');
  const auditUrlInput = root.querySelector('#bk-audit-url');
  const resultsContainer = root.querySelector('#bk-audit-results-container');

  if (runAuditBtn) {
    runAuditBtn.addEventListener('click', function () {
      const url = auditUrlInput.value.trim();
      if (!url) {
        showToast('لطفاً آدرس سایت را وارد کنید.', 'error');
        return;
      }

      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      const fd = new FormData();
      fd.append('action', 'bankai_run_performance_test');
      fd.append('nonce', getNonce());
      fd.append('url', url);
      fd.append('strategy', activeStrategy);

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          if (res.success && res.data && res.data.result) {
            renderAuditResult(res.data.result);
            showToast('تست عملکرد با موفقیت انجام شد.', 'success');
          } else {
            showToast((res.data && res.data.message) || 'خطا در اجرای تست', 'error');
          }
        })
        .catch(() => showToast('خطای ارتباط شبکه', 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  function renderAuditResult(result) {
    if (!resultsContainer) return;
    resultsContainer.style.display = 'block';

    const scores = result.scores || {};
    const vitals = result.vitals || {};

    setGauge('gauge-perf', scores.performance || 0);
    setGauge('gauge-access', scores.accessibility || 0);
    setGauge('gauge-bp', scores.best_practices || 0);
    setGauge('gauge-seo', scores.seo || 0);

    setVital('vital-fcp', 'status-fcp', vitals.fcp || '—');
    setVital('vital-lcp', 'status-lcp', vitals.lcp || '—');
    setVital('vital-tbt', 'status-tbt', vitals.tbt || '—');
    setVital('vital-cls', 'status-cls', vitals.cls || '—');
    setVital('vital-si', 'status-si', vitals.si || '—');

    const oppsList = root.querySelector('#bk-opportunities-list');
    if (oppsList) {
      oppsList.innerHTML = '';
      const items = [...(result.opportunities || []), ...(result.diagnostics || [])];
      if (items.length === 0) {
        oppsList.innerHTML = '<div style="font-size:13px;color:#1a7f37">✓ هیچ مشکل یا فرصت بهینه‌سازی بحرانی یافت نشد.</div>';
      } else {
        items.forEach((item) => {
          const div = document.createElement('div');
          div.className = 'bk-issue-item';
          div.innerHTML = `
            <div class="bk-issue-item__main">
              <span class="bk-issue-item__title">${item.title}</span>
              <span class="bk-issue-item__desc">${item.description}</span>
              <div class="bk-issue-item__meta">
                <span class="bk-issue-badge ${item.fixable ? 'is-fixable' : 'is-manual'}">
                  ${item.fixable ? 'قابل‌رفع خودکار با Fix All' : 'نیازمند بررسی دستی'}
                </span>
                ${item.displayValue ? `<span style="font-size:12px;font-weight:600;color:#636c76">${item.displayValue}</span>` : ''}
              </div>
            </div>
          `;
          oppsList.appendChild(div);
        });
      }
    }
  }

  function setGauge(id, val) {
    const el = root.querySelector('#' + id);
    if (!el) return;
    el.textContent = val;
    el.className = 'bk-gauge-circle ' + (val >= 90 ? 'score-green' : val >= 50 ? 'score-orange' : 'score-red');
  }

  function setVital(valId, statusId, valStr) {
    const vEl = root.querySelector('#' + valId);
    const sEl = root.querySelector('#' + statusId);
    if (vEl) vEl.textContent = valStr;
    if (sEl) {
      sEl.textContent = 'اندازه‌گیری‌شده';
      sEl.className = 'bk-vital-card__status is-good';
    }
  }

  /* Fix All Issues */
  const fixAllBtn = root.querySelector('#bk-fix-all-btn');
  const fixAllLog = root.querySelector('#bk-fix-all-log');
  const rollbackBtn = root.querySelector('#bk-rollback-btn');
  let currentSnapshotId = '';

  if (fixAllBtn) {
    fixAllBtn.addEventListener('click', function () {
      const url = auditUrlInput ? auditUrlInput.value.trim() : '';
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      const stagesEl = root.querySelector('#bk-fix-all-stages');
      if (stagesEl) stagesEl.style.display = 'block';
      ['snapshot', 'diagnose', 'apply', 'validate', 'warmup'].forEach((s) => setStageStatus(s, '', '—'));

      showOpProgress('رفع هوشمند چندمرحله‌ای', [
        'Snapshot ایمنی', 'تشخیص گلوگاه', 'اعمال بهینه‌سازی', 'اعتبارسنجی', 'پیش‌گرم کش'
      ]);
      setStageStatus('snapshot', 'running', 'در حال اجرا…');
      updateOpProgress(10, 'ایجاد Snapshot از تنظیمات فعلی…', 0);

      const fd = new FormData();
      fd.append('action', 'bankai_fix_performance_issues');
      fd.append('nonce', getNonce());
      fd.append('url', url);

      // Simulate staged UX while server works
      setTimeout(() => { setStageStatus('snapshot', 'done', '✓'); setStageStatus('diagnose', 'running', '…'); updateOpProgress(30, 'تشخیص گلوگاه‌های عملکرد…', 1); }, 600);
      setTimeout(() => { setStageStatus('diagnose', 'done', '✓'); setStageStatus('apply', 'running', '…'); updateOpProgress(55, 'اعمال بهینه‌سازی‌های اولویت‌دار…', 2); }, 1400);

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          setStageStatus('apply', 'done', '✓');
          setStageStatus('validate', 'running', '…');
          updateOpProgress(80, 'اعتبارسنجی نتایج…', 3);
          if (res.success && res.data) {
            currentSnapshotId = res.data.snapshot_id || '';
            setStageStatus('validate', 'done', '✓');
            setStageStatus('warmup', 'done', '✓');
            updateOpProgress(100, 'تمام مراحل با موفقیت انجام شد', 4);
            showToast(res.data.message || 'اصلاحات انجام شد.', 'success');

            if (fixAllLog) {
              fixAllLog.style.display = 'block';
              const actions = res.data.fixed_actions || [];
              fixAllLog.innerHTML = '<strong>اقدامات خودکار انجام شده:</strong><ul style="margin:6px 0 0;padding-right:18px">' +
                actions.map((a) => `<li>✓ ${a}</li>`).join('') + '</ul>';
            }
            if (rollbackBtn && currentSnapshotId) {
              rollbackBtn.style.display = 'inline-block';
            }
            if (res.data.after) {
              renderAuditResult(res.data.after);
            }
          } else {
            setStageStatus('apply', 'error', '✗');
            setStageStatus('validate', 'error', '✗');
            updateOpProgress(100, (res.data && res.data.message) || 'خطا', 2);
            showToast((res.data && res.data.message) || 'خطا در اجرای Fix All', 'error');
          }
        })
        .catch(() => {
          showToast('خطای ارتباط شبکه', 'error');
          updateOpProgress(100, 'خطای شبکه', 0);
        })
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
          hideOpProgress(2500);
        });
    });
  }

  if (rollbackBtn) {
    rollbackBtn.addEventListener('click', function () {
      if (!currentSnapshotId) return;
      if (!confirm('آیا از بازگردانی تنظیمات به حالت قبل از Fix All اطمینان دارید؟')) return;

      const fd = new FormData();
      fd.append('action', 'bankai_rollback_speed_snapshot');
      fd.append('nonce', getNonce());
      fd.append('snapshot_id', currentSnapshotId);

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          if (res.success) {
            showToast(res.data.message || 'بازگردانی انجام شد.', 'success');
            rollbackBtn.style.display = 'none';
            if (fixAllLog) fixAllLog.style.display = 'none';
          } else {
            showToast((res.data && res.data.message) || 'خطا در بازگردانی', 'error');
          }
        });
    });
  }

  /* Purge & Rebuild Assets */
  const purgeBtn = root.querySelector('#bk-speed-purge-btn');
  if (purgeBtn) {
    purgeBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;
      showOpProgress('تخلیه کش چندلایه', ['پاک‌سازی HTML', 'پاک‌سازی دارایی‌ها', 'هماهنگی CDN']);
      updateOpProgress(25, 'در حال پاک‌سازی کش صفحات…', 0);

      const fd = new FormData();
      fd.append('action', 'bankai_purge_speed_cache');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          updateOpProgress(100, res.success ? 'کش‌ها با موفقیت پاک شدند' : 'خطا در پاکسازی', 2);
          showToast(res.success ? 'کش‌ها با موفقیت پاک شدند.' : 'خطا در پاکسازی', res.success ? 'success' : 'error');
        })
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
          hideOpProgress(1800);
        });
    });
  }

  const rebuildBtn = root.querySelector('#bk-speed-rebuild-btn');
  if (rebuildBtn) {
    rebuildBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;
      showOpProgress('بازسازی دارایی‌های بهینه‌شده', ['Minify CSS', 'Minify JS', 'اعمال قوانین بارگذاری']);
      updateOpProgress(20, 'شروع بازسازی…', 0);

      const fd = new FormData();
      fd.append('action', 'bankai_rebuild_assets');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          updateOpProgress(100, res.success ? 'فایل‌های بهینه‌شده بازسازی شدند' : 'خطا', 2);
          showToast(res.success ? 'فایل‌های بهینه‌شده بازسازی شدند.' : 'خطا', res.success ? 'success' : 'error');
        })
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
          hideOpProgress(1800);
        });
    });
  }

  /* Preload */
  const preloadBtn = root.querySelector('#bk-speed-preload-btn');
  if (preloadBtn) {
    preloadBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;
      showOpProgress('پیش‌گرم هوشمند کش', ['استخراج URLها', 'اولویت‌بندی صفحات', 'تولید کش با نرخ کنترل‌شده']);
      updateOpProgress(15, 'استخراج نقشه سایت و صفحات مهم…', 0);

      const fd = new FormData();
      fd.append('action', 'bankai_preload_page_cache');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          updateOpProgress(100, res.success ? (res.data && res.data.message) || 'پیش‌گرم انجام شد' : 'خطا', 2);
          showToast(res.success ? (res.data && res.data.message) || 'پیش‌گرم انجام شد' : 'خطا', res.success ? 'success' : 'error');
        })
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  /* Save Full Config */
  const saveConfigBtn = root.querySelector('#bk-save-speed-cfg-btn, #bk-save-config-btn');
  if (saveConfigBtn) {
    saveConfigBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      const q = (sel) => root.querySelector(sel);
      const checked = (sel) => !!(q(sel) && q(sel).checked);

      speedConfig.page_caching = Object.assign({}, speedConfig.page_caching, {
        enabled: checked('#cfg-page-cache-enable'),
        ttl: parseInt((q('#cfg-page-cache-ttl') && q('#cfg-page-cache-ttl').value) || '86400', 10),
        exclusions: (q('#cfg-page-cache-excl') && q('#cfg-page-cache-excl').value) || '',
      });

      speedConfig.asset_optimization = Object.assign({}, speedConfig.asset_optimization, {
        enabled: checked('#cfg-assets-enable'),
        minify_css: checked('#cfg-asset-minify-css'),
        minify_js: checked('#cfg-asset-minify-js'),
        defer_js: checked('#cfg-asset-defer-js'),
        delay_js: checked('#cfg-asset-delay-js'),
      });

      speedConfig.cwv_lighthouse = Object.assign({}, speedConfig.cwv_lighthouse, {
        enabled: checked('#cfg-cwv-enable'),
        font_display: true,
        cls_images: true,
        async_css: true,
      });

      speedConfig.media_lazy = Object.assign({}, speedConfig.media_lazy, {
        enabled: checked('#cfg-media-enable'),
        images: true,
        iframes: true,
        youtube: true,
      });

      speedConfig.preload = Object.assign({}, speedConfig.preload, {
        enabled: checked('#cfg-preload-enable'),
        fonts: true,
        dns: true,
      });

      speedConfig.heartbeat = Object.assign({}, speedConfig.heartbeat, {
        enabled: checked('#cfg-heartbeat-enable'),
      });

      speedConfig.browser_cache = Object.assign({}, speedConfig.browser_cache, {
        enabled: checked('#cfg-browser-enable'),
        ttl: 31536000,
      });

      speedConfig.fonts_localizer = Object.assign({}, speedConfig.fonts_localizer, {
        enabled: checked('#cfg-fonts-local'),
        display_swap: checked('#cfg-fonts-display-swap'),
        preload_critical: checked('#cfg-fonts-preload-critical'),
        self_host: true,
      });

      const fd = new FormData();
      fd.append('action', 'bankai_save_speed_settings');
      fd.append('nonce', getNonce());
      fd.append('speed_config', JSON.stringify(speedConfig));

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => showToast(res.success ? 'تنظیمات کش ذخیره شدند.' : 'خطا', res.success ? 'success' : 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  /* Redis Management */
  const testRedisBtn = root.querySelector('#bk-test-redis-btn');
  const saveRedisBtn = root.querySelector('#bk-save-redis-btn');
  const redisStatusMsg = root.querySelector('#redis-status-msg');

  function getRedisPayload() {
    return {
      enabled: root.querySelector('#redis-enabled') ? root.querySelector('#redis-enabled').checked : false,
      host: root.querySelector('#redis-host') ? root.querySelector('#redis-host').value.trim() : '127.0.0.1',
      port: parseInt(root.querySelector('#redis-port') ? root.querySelector('#redis-port').value : '6379', 10),
      password: root.querySelector('#redis-password') ? root.querySelector('#redis-password').value : '',
      db: parseInt(root.querySelector('#redis-db') ? root.querySelector('#redis-db').value : '0', 10),
    };
  }

  function loadRedisSettings() {
    fetch(getRestUrl('/bankai/v1/speed/redis'), {
      headers: { 'X-WP-Nonce': getNonce() },
      credentials: 'same-origin'
    })
      .then((res) => res.json())
      .then((data) => {
        if (data) {
          if (root.querySelector('#redis-enabled')) root.querySelector('#redis-enabled').checked = !!data.enabled;
          if (root.querySelector('#redis-host')) root.querySelector('#redis-host').value = data.host || '127.0.0.1';
          if (root.querySelector('#redis-port')) root.querySelector('#redis-port').value = data.port || 6379;
          if (root.querySelector('#redis-password') && data.password && data.password !== '********') root.querySelector('#redis-password').value = data.password;
          if (root.querySelector('#redis-db')) root.querySelector('#redis-db').value = data.db || 0;
        }
      })
      .catch(() => {});
  }

  if (testRedisBtn) {
    testRedisBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      fetch(getRestUrl('/bankai/v1/speed/redis/test'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': getNonce(),
        },
        body: JSON.stringify(getRedisPayload()),
        credentials: 'same-origin',
      })
        .then((res) => res.json())
        .then((res) => {
          if (redisStatusMsg) {
            redisStatusMsg.textContent = res.message || '';
            redisStatusMsg.style.color = res.ok ? '#1a7f37' : '#cf222e';
          }
          if (typeof window.bankaiSetRedisMeter === 'function') {
            window.bankaiSetRedisMeter(res.ok ? 100 : 15, res.ok ? 'متصل' : 'ناموفق');
          }
          showToast(res.message || 'بررسی انجام شد.', res.ok ? 'success' : 'error');
        })
        .catch(() => showToast('خطای ارتباط با سرور', 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  if (saveRedisBtn) {
    saveRedisBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      fetch(getRestUrl('/bankai/v1/speed/redis'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': getNonce(),
        },
        body: JSON.stringify(getRedisPayload()),
        credentials: 'same-origin',
      })
        .then((res) => res.json())
        .then((res) => {
          if (res.data && redisStatusMsg) {
            redisStatusMsg.textContent = res.data.message || '';
            redisStatusMsg.style.color = res.data.ok ? '#1a7f37' : '#cf222e';
          }
          showToast('تنظیمات Redis ذخیره شد.', 'success');
        })
        .catch(() => showToast('خطای ارتباط با سرور', 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  /* PSI Key */
  const savePsiKeyBtn = root.querySelector('#bk-save-psi-key-btn');
  const psiKeyInput = root.querySelector('#bk-psi-key-input');
  if (savePsiKeyBtn && psiKeyInput) {
    savePsiKeyBtn.addEventListener('click', function () {
      const key = psiKeyInput.value.trim();
      const fd = new FormData();
      fd.append('action', 'bankai_save_speed_settings');
      fd.append('nonce', getNonce());
      fd.append('psi_api_key', key);

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => showToast(res.success ? 'کلید API ذخیره شد.' : 'خطا', res.success ? 'success' : 'error'));
    });
  }

  /* Database Optimizer with progress */
  const runDbOptBtn = root.querySelector('#bk-run-db-opt-btn');
  const dbOptResult = root.querySelector('#db-opt-result');
  const dbProgress = root.querySelector('#bk-db-progress');
  const dbFill = root.querySelector('#bk-db-progress-fill');
  const dbLabel = root.querySelector('#bk-db-progress-label');
  const dbSteps = root.querySelector('#bk-db-steps');
  if (runDbOptBtn) {
    runDbOptBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;
      if (dbProgress) dbProgress.style.display = 'block';
      if (dbOptResult) dbOptResult.style.display = 'none';

      const stepKeys = ['revisions', 'auto_drafts', 'trash', 'spam', 'transients', 'optimize'];
      let si = 0;
      if (dbSteps) {
        dbSteps.querySelectorAll('li').forEach(function (li) {
          li.classList.remove('is-run', 'is-done', 'is-err');
          const st = li.querySelector('.bk-db-st');
          if (st) st.textContent = '…';
        });
      }
      const tick = setInterval(function () {
        if (si < stepKeys.length && dbSteps) {
          const li = dbSteps.querySelector('[data-step="' + stepKeys[si] + '"]');
          if (li) {
            li.classList.add('is-run');
            const st = li.querySelector('.bk-db-st');
            if (st) st.textContent = '◉';
          }
          if (si > 0) {
            const prev = dbSteps.querySelector('[data-step="' + stepKeys[si - 1] + '"]');
            if (prev) {
              prev.classList.remove('is-run');
              prev.classList.add('is-done');
              const st = prev.querySelector('.bk-db-st');
              if (st) st.textContent = '✓';
            }
          }
          if (dbFill) dbFill.style.width = Math.round(((si + 1) / stepKeys.length) * 90) + '%';
          if (dbLabel) dbLabel.textContent = 'در حال اجرا: ' + (li ? li.textContent.replace(/^[^\s]+\s/, '') : '…');
          si++;
        }
      }, 420);

      const fd = new FormData();
      fd.append('action', 'bankai_optimize_database');
      fd.append('nonce', getNonce());
      root.querySelectorAll('#bk-db-chips input[data-db-opt]').forEach(function (cb) {
        if (cb.checked) fd.append('opts[]', cb.getAttribute('data-db-opt'));
      });

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          clearInterval(tick);
          if (dbSteps) {
            dbSteps.querySelectorAll('li').forEach(function (li) {
              li.classList.remove('is-run');
              li.classList.add('is-done');
              const st = li.querySelector('.bk-db-st');
              if (st) st.textContent = '✓';
            });
          }
          if (dbFill) dbFill.style.width = '100%';
          if (dbLabel) dbLabel.textContent = 'پاکسازی کامل شد';
          if (res.success && res.data) {
            showToast('پاکسازی پایگاه داده با موفقیت انجام شد.', 'success');
            if (dbOptResult) {
              dbOptResult.style.display = 'block';
              const d = res.data;
              dbOptResult.innerHTML = '<div class="bk-db-report__title">گزارش پاکسازی</div>' +
                '<div class="bk-db-report__row"><span>رونوشت‌ها</span><strong>' + (d.deleted_revisions || 0) + '</strong></div>' +
                '<div class="bk-db-report__row"><span>پیش‌نویس خودکار</span><strong>' + (d.deleted_drafts || 0) + '</strong></div>' +
                '<div class="bk-db-report__row"><span>زباله‌دان</span><strong>' + (d.deleted_trash || 0) + '</strong></div>' +
                '<div class="bk-db-report__row"><span>دیدگاه اسپم</span><strong>' + (d.deleted_spam || 0) + '</strong></div>' +
                '<div class="bk-db-report__row"><span>Transient</span><strong>' + (d.deleted_transients || d.cleaned_transients || 0) + '</strong></div>' +'<div class="bk-db-report__row"><span>متای یتیم</span><strong>' + (d.deleted_orphan_meta || 0) + '</strong></div>';
            }
          } else {
            showToast((res && res.data && res.data.message) || 'خطا در بهینه‌سازی', 'error');
            if (dbLabel) dbLabel.textContent = 'خطا در اجرا';
          }
        })
        .catch(function () {
          clearInterval(tick);
          showToast('خطای شبکه', 'error');
        })
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  /* History & Report Export */
  const historyList = root.querySelector('#bk-history-list');
  function loadHistory() {
    if (!historyList) return;
    const fd = new FormData();
    fd.append('action', 'bankai_get_audit_history');
    fd.append('nonce', getNonce());

    fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
      .then((res) => res.json())
      .then((res) => {
        if (res.success && res.data && res.data.history) {
          historyList.innerHTML = '';
          const list = res.data.history;
          if (list.length === 0) {
            historyList.innerHTML = '<div style="font-size:13px;color:#636c76">هیچ تست قبلی یافت نشد.</div>';
            return;
          }
          list.forEach((item) => {
            const div = document.createElement('div');
            div.style.cssText = 'padding:12px;background:#f6f8fa;border:1px solid #d0d7de;border-radius:8px;font-size:12px;display:flex;justify-content:space-between;align-items:center';
            div.innerHTML = `
              <div>
                <strong style="font-size:13px;color:#1f2328">${item.url} (${item.strategy})</strong>
                <div style="color:#636c76;margin-top:2px">${item.timestamp} · موتور: ${item.engine}</div>
              </div>
              <div style="font-size:16px;font-weight:700;color:${(item.scores && item.scores.performance >= 90) ? '#1a7f37' : '#9a6700'}">
                ${(item.scores && item.scores.performance) || '—'} / 100
              </div>
            `;
            historyList.appendChild(div);
          });
        }
      });
  }

  const exportReportBtn = root.querySelector('#bk-export-report-btn');
  if (exportReportBtn) {
    exportReportBtn.addEventListener('click', function () {
      const fd = new FormData();
      fd.append('action', 'bankai_export_performance_report');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          if (res.success && res.data) {
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(res.data, null, 2));
            const downloadAnchor = document.createElement('a');
            downloadAnchor.setAttribute("href", dataStr);
            downloadAnchor.setAttribute("download", "bankai_performance_report.json");
            document.body.appendChild(downloadAnchor);
            downloadAnchor.click();
            downloadAnchor.remove();
            showToast('گزارش JSON دانلود شد.', 'success');
          } else {
            showToast('گزارشی برای خروجی گرفتن یافت نشد.', 'error');
          }
        });
    });
  }


  /* ── Boost presets (Safe / Balanced / Aggressive) ───────── */
  const boostPresets = {
    safe: {
      page_caching: { enabled: true },
      asset_optimization: { enabled: true, minify_css: true, minify_js: false, defer_js: true, delay_js: false },
      cwv_lighthouse: { enabled: true },
      media_lazy: { enabled: true },
      preload: { enabled: true },
      heartbeat: { enabled: false },
      browser_cache: { enabled: true }
    },
    balanced: {
      page_caching: { enabled: true },
      asset_optimization: { enabled: true, minify_css: true, minify_js: true, defer_js: true, delay_js: true },
      cwv_lighthouse: { enabled: true },
      media_lazy: { enabled: true },
      preload: { enabled: true },
      heartbeat: { enabled: true },
      browser_cache: { enabled: true }
    },
    aggressive: {
      page_caching: { enabled: true },
      asset_optimization: { enabled: true, minify_css: true, minify_js: true, defer_js: true, delay_js: true },
      cwv_lighthouse: { enabled: true },
      media_lazy: { enabled: true },
      preload: { enabled: true },
      heartbeat: { enabled: true },
      browser_cache: { enabled: true }
    }
  };
  let activeBoost = 'balanced';

  root.querySelectorAll('.bk-boost-chip[data-mode]').forEach((chip) => {
    chip.addEventListener('click', function () {
      const mode = this.getAttribute('data-mode');
      if (mode === 'apply') return;
      activeBoost = mode;
      root.querySelectorAll('.bk-boost-chip[data-mode]').forEach((c) => {
        if (c.getAttribute('data-mode') !== 'apply') c.classList.toggle('is-active', c === this);
      });
    });
  });

  const boostApply = root.querySelector('#bk-boost-apply');
  if (boostApply) {
    boostApply.addEventListener('click', function () {
      const preset = boostPresets[activeBoost] || boostPresets.balanced;
      speedConfig = Object.assign({}, speedConfig, {
        page_caching: Object.assign({}, speedConfig.page_caching || {}, preset.page_caching || {}),
        asset_optimization: Object.assign({}, speedConfig.asset_optimization || {}, preset.asset_optimization || {}),
        cwv_lighthouse: Object.assign({}, speedConfig.cwv_lighthouse || {}, preset.cwv_lighthouse || {}),
        media_lazy: Object.assign({}, speedConfig.media_lazy || {}, preset.media_lazy || {}),
        preload: Object.assign({}, speedConfig.preload || {}, preset.preload || {}),
        heartbeat: Object.assign({}, speedConfig.heartbeat || {}, preset.heartbeat || {}),
        browser_cache: Object.assign({}, speedConfig.browser_cache || {}, preset.browser_cache || {})
      });
      if (typeof hydrateFormFields === 'function') hydrateFormFields();
      // Reflect toggles
      const map = {
        'cfg-page-cache-enable': speedConfig.page_caching && speedConfig.page_caching.enabled !== false,
        'cfg-assets-enable': speedConfig.asset_optimization && speedConfig.asset_optimization.enabled !== false,
        'cfg-cwv-enable': speedConfig.cwv_lighthouse && speedConfig.cwv_lighthouse.enabled !== false,
        'cfg-media-enable': speedConfig.media_lazy && speedConfig.media_lazy.enabled !== false,
        'cfg-preload-enable': speedConfig.preload && speedConfig.preload.enabled !== false,
        'cfg-heartbeat-enable': speedConfig.heartbeat && speedConfig.heartbeat.enabled !== false,
        'cfg-browser-enable': speedConfig.browser_cache && speedConfig.browser_cache.enabled !== false,
        'cfg-asset-minify-css': !!(speedConfig.asset_optimization && speedConfig.asset_optimization.minify_css),
        'cfg-asset-minify-js': !!(speedConfig.asset_optimization && speedConfig.asset_optimization.minify_js),
        'cfg-asset-defer-js': !!(speedConfig.asset_optimization && speedConfig.asset_optimization.defer_js),
        'cfg-asset-delay-js': !!(speedConfig.asset_optimization && speedConfig.asset_optimization.delay_js)
      };
      Object.keys(map).forEach((id) => {
        const el = root.querySelector('#' + id);
        if (el) el.checked = !!map[id];
      });
      root.querySelectorAll('.bk-mod-card').forEach((card) => {
        const input = card.querySelector('input[type="checkbox"]');
        if (input && input.id && input.id.indexOf('cfg-') === 0 && input.id.indexOf('asset-') === -1) {
          card.classList.toggle('is-on', input.checked);
        }
      });
      showToast('حالت «' + activeBoost + '» روی ماژول‌ها اعمال شد — ذخیره را بزنید.', 'success');
    });
  }

  // Toggle card is-on state
  root.querySelectorAll('.bk-mod-card .bk-toggle input, .bk-mod-card > .bk-mod-card__head input[type="checkbox"]').forEach((inp) => {
    inp.addEventListener('change', function () {
      const card = this.closest('.bk-mod-card');
      if (card) card.classList.toggle('is-on', this.checked);
    });
  });

  // DB chips visual (old + new WPR classes)
  root.querySelectorAll('#bk-db-chips .bk-db-chip, #bk-db-chips .bk-wpr-chip').forEach((chip) => {
    const input = chip.querySelector('input');
    if (!input) return;
    chip.classList.toggle('is-checked', input.checked);
    input.addEventListener('change', function () {
      chip.classList.toggle('is-checked', input.checked);
    });
  });

  // Side panel: Preload mirrors main preload button
  const preloadSide = root.querySelector('#bk-speed-preload-btn-side');
  const preloadMain = root.querySelector('#bk-speed-preload-btn');
  if (preloadSide && preloadMain) {
    preloadSide.addEventListener('click', function () { preloadMain.click(); });
  }

  // Clear & Preload combined action
  const purgePreloadBtn = root.querySelector('#bk-speed-purge-preload-btn');
  if (purgePreloadBtn) {
    purgePreloadBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;
      showOpProgress('پاک‌سازی و پیش‌بارگذاری', ['تخلیه کش', 'استخراج URL', 'تولید کش']);
      updateOpProgress(20, 'تخلیه کش…', 0);

      const fd = new FormData();
      fd.append('action', 'bankai_purge_speed_cache');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          if (!res.success) throw new Error('purge failed');
          updateOpProgress(50, 'شروع پیش‌بارگذاری…', 1);
          const fd2 = new FormData();
          fd2.append('action', 'bankai_preload_page_cache');
          fd2.append('nonce', getNonce());
          return fetch(getAjaxUrl(), { method: 'POST', body: fd2, credentials: 'same-origin' });
        })
        .then((res) => res.json())
        .then((res) => {
          updateOpProgress(100, res.success ? 'انجام شد' : 'خطا', 2);
          showToast(res.success ? ((res.data && res.data.message) || 'Clear & Preload انجام شد') : 'خطا', res.success ? 'success' : 'error');
        })
        .catch(() => showToast('خطا در Clear & Preload', 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
          hideOpProgress(1800);
        });
    });
  }


  // Localize fonts button
  const fontsBtn = root.querySelector('#bk-fonts-localize-btn');
  if (fontsBtn) {
    fontsBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;
      showOpProgress('محلی‌سازی فونت‌ها', ['شناسایی فونت‌ها', 'دانلود WOFF2', 'تولید CSS محلی']);
      updateOpProgress(20, 'شناسایی فونت‌های خارجی…', 0);

      const fd = new FormData();
      fd.append('action', 'bankai_localize_fonts');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          updateOpProgress(100, res.success ? 'انجام شد' : 'خطا', 2);
          const box = root.querySelector('#bk-fonts-status');
          if (box) {
            box.style.display = 'block';
            if (res.success && res.data) {
              box.textContent = (res.data.message || 'موفق') + (res.data.css_url ? ' — ' + res.data.css_url : '');
            } else {
              box.textContent = (res.data && res.data.message) || 'خطا در محلی‌سازی فونت';
            }
          }
          showToast(res.success ? ((res.data && res.data.message) || 'فونت‌ها محلی شدند') : 'خطا', res.success ? 'success' : 'error');
        })
        .catch(() => showToast('خطای شبکه', 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
          hideOpProgress(2000);
        });
    });
  }

  // Redis meter helper
  window.bankaiSetRedisMeter = function (pct, label) {
    const bar = root.querySelector('#bk-redis-meter-bar');
    const lab = root.querySelector('#bk-redis-meter-label');
    if (bar) bar.style.width = Math.max(0, Math.min(100, pct || 0)) + '%';
    if (lab) lab.textContent = label || (pct + '%');
  };

});
