(function () {
  if (typeof wp === 'undefined' || !wp.customize) return;

  function scoreOf(t, d, k) {
    var s = 0;
    var tl = (t || '').length;
    var dl = (d || '').length;
    var kc = (k || '').split(',').map(function (x) { return x.trim(); }).filter(Boolean);
    if (tl >= 30 && tl <= 60) s += 30; else if (tl > 10) s += 15;
    if (dl >= 120 && dl <= 160) s += 35; else if (dl > 40) s += 18;
    if (kc.length >= 3) s += 25; else if (kc.length) s += 12;
    if (tl && dl) s += 10;
    return Math.min(100, s);
  }

  function get(id) {
    try { return wp.customize(id) ? wp.customize(id).get() : ''; } catch (e) { return ''; }
  }
  function set(id, v) {
    try {
      if (wp.customize(id)) {
        wp.customize(id).set(v);
        if (wp.customize.state && wp.customize.state('saved')) wp.customize.state('saved').set(false);
      }
    } catch (e) {}
  }

  function settingIds(page) {
    // Prefer *_seo_desc (primary), fall back to *_seo_description
    var descId = wp.customize('page_' + page + '_seo_desc') ? 'page_' + page + '_seo_desc' : 'page_' + page + '_seo_description';
    return {
      title: 'page_' + page + '_seo_title',
      desc: descId,
      kw: 'page_' + page + '_seo_keywords'
    };
  }

  function isSeoControl(el, page) {
    if (!el || !el.id) return false;
    var id = el.id;
    return (
      id.indexOf('page_' + page + '_seo_') !== -1 ||
      id.indexOf('customize-control-page_' + page + '_seo_') !== -1
    );
  }

  function markAndToggle(content, page, tab) {
    var controls = content.querySelectorAll('.customize-control');
    controls.forEach(function (el) {
      if (el.classList.contains('bk-page-seo-root-wrap')) return;
      var seo = isSeoControl(el, page) || !!el.querySelector('.bsa-panel');
      if (seo) {
        el.classList.add('bk-is-seo-control');
        el.style.display = tab === 'seo' ? '' : 'none';
      } else {
        el.classList.remove('bk-is-seo-control');
        el.style.display = tab === 'design' ? '' : 'none';
      }
    });
    // Also hide duplicate plain SEO fields always when panel is used — they live inside panel
    content.querySelectorAll('.bk-is-seo-control').forEach(function (el) {
      // Keep Bankai_Customize_Seo_Ai_Control panel visible only in seo tab
      if (el.querySelector('.bsa-panel')) {
        el.style.display = tab === 'seo' ? '' : 'none';
      } else {
        // Hide raw text/textarea SEO controls — panel replaces them
        el.style.display = 'none';
      }
    });
  }

  function mount(root, page, content) {
    if (!root || root.dataset.ready) return;
    root.dataset.ready = '1';
    root.classList.add('bk-page-seo-root-wrap');

    var ids = settingIds(page);
    var hasAi = !!(window.bankaiThemeCustomizer && window.bankaiThemeCustomizer.hasAi);

    root.innerHTML =
      '<div class="bk-page-seo-tabs" role="tablist">' +
        '<button type="button" class="is-active" data-tab="design">سفارشی‌سازی</button>' +
        '<button type="button" data-tab="seo">سئوی صفحه</button>' +
      '</div>' +
      '<div class="bk-page-seo-panel" data-panel="seo" style="display:none"></div>';

    var panel = root.querySelector('[data-panel="seo"]');
    var currentTab = 'design';

    function renderSeo() {
      var t = get(ids.title), d = get(ids.desc), k = get(ids.kw);
      var sc = scoreOf(t, d, k);
      var kws = (k || '').split(',').map(function (x) { return x.trim(); }).filter(Boolean);
      panel.innerHTML =
        '<div class="bk-seo-score-wrap">' +
          '<div class="bk-seo-ring" style="--p:' + sc + '%"><span>' + sc + '</span></div>' +
          '<div><strong style="color:#fff">امتیاز سئو</strong>' +
          '<div style="font-size:12px;color:rgba(245,243,255,.6);margin-top:4px">عنوان، متا و کلمات کلیدی</div></div>' +
        '</div>' +
        '<button type="button" class="bk-seo-ai-btn" ' + (hasAi ? '' : 'disabled ') + '>' +
          (hasAi ? 'تولید سئو با هوش مصنوعی' : 'AI غیرفعال — Bankai Core را فعال کنید') +
        '</button>' +
        '<div class="bk-seo-progress" hidden>' +
          '<div class="bk-seo-progress__track"><div class="bk-seo-progress__bar"></div></div>' +
          '<span class="bk-seo-progress__label">آماده‌سازی…</span>' +
        '</div>' +
        '<p class="bk-seo-ai-status" style="font-size:12px;color:rgba(245,243,255,.7);margin:8px 0 12px" aria-live="polite"></p>' +
        '<label style="display:block;margin:0 0 6px;font-size:12px;font-weight:700;color:rgba(245,243,255,.85)">عنوان سئو</label>' +
        '<input type="text" class="bk-seo-title" value="' + String(t).replace(/"/g, '&quot;') + '" style="width:100%;margin-bottom:10px" maxlength="70" />' +
        '<label style="display:block;margin:0 0 6px;font-size:12px;font-weight:700;color:rgba(245,243,255,.85)">متا دیسکریپشن</label>' +
        '<textarea class="bk-seo-desc" rows="3" style="width:100%;margin-bottom:10px" maxlength="180">' + String(d).replace(/</g, '&lt;') + '</textarea>' +
        '<label style="display:block;margin:0 0 6px;font-size:12px;font-weight:700;color:rgba(245,243,255,.85)">کلمات کلیدی</label>' +
        '<div style="display:flex;gap:6px;margin-bottom:8px">' +
          '<input type="text" class="bk-seo-kw-input" placeholder="کلمه کلیدی جدید" style="flex:1" />' +
          '<button type="button" class="button bk-seo-kw-add">افزودن</button>' +
        '</div>' +
        '<div class="bk-seo-kw-list">' +
          kws.map(function (w, i) {
            return '<span class="bk-seo-kw-chip">' + w + '<button type="button" data-i="' + i + '">&times;</button></span>';
          }).join('') +
        '</div>';

      panel.querySelector('.bk-seo-title').addEventListener('input', function (e) {
        set(ids.title, e.target.value); renderSeo();
      });
      panel.querySelector('.bk-seo-desc').addEventListener('input', function (e) {
        set(ids.desc, e.target.value); renderSeo();
      });
      panel.querySelector('.bk-seo-kw-add').addEventListener('click', function () {
        var inp = panel.querySelector('.bk-seo-kw-input');
        var v = (inp.value || '').trim();
        if (!v) return;
        kws.push(v);
        set(ids.kw, kws.join(', '));
        inp.value = '';
        renderSeo();
      });
      panel.querySelectorAll('.bk-seo-kw-chip button').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var i = parseInt(btn.getAttribute('data-i'), 10);
          kws.splice(i, 1);
          set(ids.kw, kws.join(', '));
          renderSeo();
        });
      });

      var aiBtn = panel.querySelector('.bk-seo-ai-btn');
      var prog = panel.querySelector('.bk-seo-progress');
      var bar = panel.querySelector('.bk-seo-progress__bar');
      var lab = panel.querySelector('.bk-seo-progress__label');
      var st = panel.querySelector('.bk-seo-ai-status');

      if (aiBtn && hasAi) {
        aiBtn.addEventListener('click', function () {
          if (aiBtn.disabled) return;
          aiBtn.disabled = true;
          prog.hidden = false;
          bar.style.width = '12%';
          lab.textContent = 'اتصال به موتور هوش مصنوعی…';
          st.textContent = '';

          var steps = [
            { p: 28, t: 'تحلیل صفحه و کلمات کلیدی…' },
            { p: 52, t: 'تولید عنوان سئو…' },
            { p: 72, t: 'تولید متا دیسکریپشن…' },
            { p: 88, t: 'استخراج کلمات کلیدی…' }
          ];
          var si = 0;
          var tick = setInterval(function () {
            if (si < steps.length) {
              bar.style.width = steps[si].p + '%';
              lab.textContent = steps[si].t;
              si += 1;
            }
          }, 450);

          var body = new FormData();
          body.set('action', 'bankai_theme_seo_ai');
          body.set('nonce', (window.bankaiThemeCustomizer && window.bankaiThemeCustomizer.nonce) || '');
          body.set('page', page);
          body.set('title', get(ids.title) || document.title || '');
          body.set('context', page);

          fetch((window.bankaiThemeCustomizer && window.bankaiThemeCustomizer.ajaxUrl) || (window.ajaxurl || '/wp-admin/admin-ajax.php'), {
            method: 'POST',
            credentials: 'same-origin',
            body: body
          }).then(function (r) { return r.json(); }).then(function (json) {
            clearInterval(tick);
            bar.style.width = '100%';
            lab.textContent = 'تکمیل شد';
            if (json && json.success && json.data) {
              if (json.data.title) set(ids.title, json.data.title);
              if (json.data.description || json.data.desc) set(ids.desc, json.data.description || json.data.desc);
              if (json.data.keywords) set(ids.kw, json.data.keywords);
              st.textContent = 'متاها با موفقیت تولید و ذخیره موقت شدند — برای اعمال، انتشار را بزنید.';
              st.style.color = '#86efac';
              renderSeo();
            } else {
              st.textContent = (json && json.data && json.data.message) ? json.data.message : 'خطا در تولید سئو';
              st.style.color = '#fca5a5';
            }
          }).catch(function () {
            clearInterval(tick);
            st.textContent = 'خطای شبکه در ارتباط با AI';
            st.style.color = '#fca5a5';
          }).finally(function () {
            aiBtn.disabled = false;
            setTimeout(function () { prog.hidden = true; bar.style.width = '0%'; }, 1200);
          });
        });
      }
    }

    function switchTab(tab) {
      currentTab = tab;
      root.querySelectorAll('.bk-page-seo-tabs button').forEach(function (b) {
        b.classList.toggle('is-active', b.getAttribute('data-tab') === tab);
      });
      panel.style.display = tab === 'seo' ? 'block' : 'none';
      markAndToggle(content, page, tab);
      if (tab === 'seo') renderSeo();
    }

    root.querySelectorAll('.bk-page-seo-tabs button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        switchTab(btn.getAttribute('data-tab'));
      });
    });

    // Default: design tab
    switchTab('design');
  }

  function ensureNonce() {
    // If Seo_Ai control not loaded yet, provide minimal localize fallback
    if (!window.bankaiThemeCustomizer) {
      window.bankaiThemeCustomizer = {
        ajaxUrl: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),
        nonce: '',
        hasAi: true
      };
    }
  }

  wp.customize.bind('ready', function () {
    ensureNonce();
    ['home', 'blog', 'single', 'search', '404'].forEach(function (p) {
      var secId = 'bankai_page_' + (p === '404' ? '404' : p);
      if (!wp.customize.section) return;
      wp.customize.section(secId, function (section) {
        section.expanded.bind(function (open) {
          if (!open) return;
          setTimeout(function () {
            var content = document.querySelector('#sub-accordion-section-' + secId);
            if (!content) return;
            var existing = content.querySelector('.bk-page-seo-root');
            if (existing) {
              mount(existing, p, content);
              return;
            }
            var box = document.createElement('div');
            box.className = 'bk-page-seo-root';
            box.setAttribute('data-page', p);
            // Place after section header (title/back), never before it
            var after = content.querySelector('.customize-section-description-container, .customize-section-title, li.customize-section-description-container');
            if (after && after.nextSibling) {
              content.insertBefore(box, after.nextSibling);
            } else if (after) {
              after.insertAdjacentElement('afterend', box);
            } else {
              content.insertBefore(box, content.firstChild);
            }
            mount(box, p, content);
          }, 120);
        });
      });
    });
  });
})();
