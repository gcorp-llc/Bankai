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

  const showToast = (msg, type = 'success') => {
    if (typeof window.bankaiShowToast === 'function') {
      window.bankaiShowToast(msg, type);
    } else {
      alert(msg);
    }
  };

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
  }

  hydrateFormFields();

  /* Subtab Navigation */
  const tabBtns = root.querySelectorAll('.bk-speed-tab-btn');
  const tabPanes = root.querySelectorAll('.bk-speed-tab-pane');

  tabBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      const sub = this.getAttribute('data-subtab');
      tabBtns.forEach((b) => b.classList.remove('is-active'));
      tabPanes.forEach((p) => p.classList.remove('is-active'));

      this.classList.add('is-active');
      const targetPane = root.querySelector('#bk-pane-' + sub);
      if (targetPane) targetPane.classList.add('is-active');

      if (sub === 'history') loadHistory();
      if (sub === 'redis') loadRedisSettings();
    });
  });

  /* Device Toggle for Audit */
  let activeStrategy = 'mobile';
  const deviceBtns = root.querySelectorAll('.bk-audit-device-btn');
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

      const fd = new FormData();
      fd.append('action', 'bankai_fix_performance_issues');
      fd.append('nonce', getNonce());
      fd.append('url', url);

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          if (res.success && res.data) {
            currentSnapshotId = res.data.snapshot_id || '';
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
            showToast((res.data && res.data.message) || 'خطا در اجرای Fix All', 'error');
          }
        })
        .catch(() => showToast('خطای ارتباط شبکه', 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
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

      const fd = new FormData();
      fd.append('action', 'bankai_purge_speed_cache');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => showToast(res.success ? 'کش‌ها با موفقیت پاک شدند.' : 'خطا در پاکسازی', res.success ? 'success' : 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  const rebuildBtn = root.querySelector('#bk-speed-rebuild-btn');
  if (rebuildBtn) {
    rebuildBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      const fd = new FormData();
      fd.append('action', 'bankai_rebuild_assets');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => showToast(res.success ? 'فایل‌های بهینه‌شده بازسازی شدند.' : 'خطا', res.success ? 'success' : 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
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

      const fd = new FormData();
      fd.append('action', 'bankai_preload_page_cache');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => showToast(res.success ? res.data.message : 'خطا', res.success ? 'success' : 'error'))
        .finally(() => {
          if (spin) spin.style.display = 'none';
          this.disabled = false;
        });
    });
  }

  /* Save Full Config */
  const saveConfigBtn = root.querySelector('#bk-save-config-btn');
  if (saveConfigBtn) {
    saveConfigBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      // Update local speedConfig preserving unknown keys
      speedConfig.page_caching = Object.assign({}, speedConfig.page_caching, {
        enabled: root.querySelector('#cfg-page-cache-enable').checked,
        ttl: parseInt(root.querySelector('#cfg-page-cache-ttl').value || '86400', 10),
        exclusions: root.querySelector('#cfg-page-cache-excl').value,
      });

      speedConfig.asset_optimization = Object.assign({}, speedConfig.asset_optimization, {
        enabled: root.querySelector('#cfg-assets-enable').checked,
        minify_css: root.querySelector('#cfg-asset-minify-css').checked,
        minify_js: root.querySelector('#cfg-asset-minify-js').checked,
        defer_js: root.querySelector('#cfg-asset-defer-js').checked,
        delay_js: root.querySelector('#cfg-asset-delay-js').checked,
      });

      speedConfig.cwv_lighthouse = Object.assign({}, speedConfig.cwv_lighthouse, {
        enabled: root.querySelector('#cfg-cwv-enable').checked,
        font_display: true,
        cls_images: true,
        async_css: true,
      });

      speedConfig.media_lazy = Object.assign({}, speedConfig.media_lazy, {
        enabled: root.querySelector('#cfg-media-enable').checked,
        images: true,
        iframes: true,
        youtube: true,
      });

      speedConfig.preload = Object.assign({}, speedConfig.preload, {
        enabled: root.querySelector('#cfg-preload-enable').checked,
        fonts: true,
        dns: true,
      });

      speedConfig.heartbeat = Object.assign({}, speedConfig.heartbeat, {
        enabled: root.querySelector('#cfg-heartbeat-enable').checked,
      });

      speedConfig.browser_cache = Object.assign({}, speedConfig.browser_cache, {
        enabled: root.querySelector('#cfg-browser-enable').checked,
        ttl: 31536000,
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

  /* Database Optimizer */
  const runDbOptBtn = root.querySelector('#bk-run-db-opt-btn');
  const dbOptResult = root.querySelector('#db-opt-result');
  if (runDbOptBtn) {
    runDbOptBtn.addEventListener('click', function () {
      const spin = this.querySelector('.bk-spin');
      if (spin) spin.style.display = 'inline-block';
      this.disabled = true;

      const fd = new FormData();
      fd.append('action', 'bankai_optimize_database');
      fd.append('nonce', getNonce());

      fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then((res) => res.json())
        .then((res) => {
          if (res.success && res.data) {
            showToast('پاکسازی پایگاه داده با موفقیت انجام شد.', 'success');
            if (dbOptResult) {
              dbOptResult.style.display = 'block';
              dbOptResult.innerHTML = `
                ✓ رونوشت‌های پاک‌شده: ${res.data.deleted_revisions || 0}<br>
                ✓ پیش‌نویس‌های پاک‌شده: ${res.data.deleted_drafts || 0}<br>
                ✓ زباله‌دان پاک‌شده: ${res.data.deleted_trash || 0}<br>
                ✓ دیدگاه‌های اسپم پاک‌شده: ${res.data.deleted_spam || 0}
              `;
            }
          } else {
            showToast('خطا در بهینه‌سازی', 'error');
          }
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
});
