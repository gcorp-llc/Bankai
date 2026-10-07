(function ($) {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  var state = null;
  var footerState = null;
  var device = 'desktop'; // desktop | mobile
  var sheetMode = 'header'; // header | footer
  var sheet = null;
  var portal = null;

  var ICONS = {
    logo: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 14l2.5-5 2 3.5L15 9l3 5H8z"/></svg>',
    menu: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 7h16M4 12h16M4 17h12"/></svg>',
    search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="11" cy="11" r="6.5"/><path d="M16.5 16.5L21 21"/></svg>',
    social: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="7" cy="12" r="2.5"/><circle cx="17" cy="7" r="2.5"/><circle cx="17" cy="17" r="2.5"/><path d="M9.3 11l5-2.8M9.3 13l5 2.8"/></svg>',
    button: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="7" width="18" height="10" rx="5"/><path d="M8 12h8"/></svg>',
    account: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="9" r="3.5"/><path d="M5.5 19.5c1.5-3 4-4.5 6.5-4.5s5 1.5 6.5 4.5"/></svg>',
    html: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13 5l-2 14"/></svg>',
    divider: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 4v16"/></svg>',
    widget: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg>',
    copyright: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="8"/><path d="M14.5 9.5a3.5 3.5 0 1 0 0 5"/></svg>',
    cart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M6 6L5 3H2"/></svg>',
    phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M6.5 3.5l2.5 2.5-1.5 2.5a12 12 0 006.5 6.5l2.5-1.5 2.5 2.5-1 2a3 3 0 01-3.2 1.8C9 19 5 15 3.7 8.7a3 3 0 011.8-3.2l1-1z"/></svg>',
    text: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 7V5h16v2M12 5v14M9 19h6"/></svg>'
  };

  function cfg() {
    return window.bankaiHeaderBuilder || { config: {}, elements: {}, footerConfig: {}, footerElements: {} };
  }

  function elType(el) {
    return typeof el === 'string' ? el : (el && el.type) || 'html';
  }
  function elProps(el) {
    if (typeof el === 'string') return defaultProps(el);
    return Object.assign(defaultProps(el.type), el.props || {});
  }
  function defaultProps(type) {
    return {
      label: '',
      url: '',
      icon: type,
      size: 20,
      color: '',
      bg: '',
      radius: 10,
      style: 'solid',
      target: '_self',
      html: ''
    };
  }
  function makeEl(type) {
    return { type: type, props: defaultProps(type) };
  }

  function defaultHeader() {
    return {
      sticky: true,
      transparent: false,
      behavior: 'sticky',
      rows: threeRows(),
      mobile: { behavior: 'sticky', rows: mobileRows() }
    };
  }
  function threeRows() {
    return [
      { id: 'top', enabled: false, height: 36, colCount: 3, cols: emptyCols(3) },
      { id: 'main', enabled: true, height: 72, colCount: 3, cols: [
        { align: 'start', elements: [makeEl('logo')] },
        { align: 'center', elements: [makeEl('menu')] },
        { align: 'end', elements: [makeEl('search'), makeEl('search')] }
      ]},
      { id: 'bottom', enabled: false, height: 44, colCount: 3, cols: emptyCols(3) }
    ];
  }
  function mobileRows() {
    return [
      { id: 'main', enabled: true, height: 60, colCount: 2, cols: [
        { align: 'start', elements: [makeEl('logo')] },
        { align: 'end', elements: [makeEl('menu'), makeEl('search')] }
      ]}
    ];
  }
  function emptyCols(n) {
    var a = [];
    for (var i = 0; i < n; i++) {
      a.push({ align: i === 0 ? 'start' : (i === n - 1 ? 'end' : 'center'), elements: [] });
    }
    return a;
  }
  function defaultFooter() {
    return {
      rows: [
        { id: 'top', enabled: false, height: 48, colCount: 3, cols: emptyCols(3) },
        { id: 'main', enabled: true, height: 120, colCount: 3, cols: [
          { align: 'start', elements: [makeEl('widget')] },
          { align: 'center', elements: [makeEl('widget')] },
          { align: 'end', elements: [makeEl('widget')] }
        ]},
        { id: 'bottom', enabled: true, height: 48, colCount: 1, cols: [
          { align: 'center', elements: [makeEl('copyright')] }
        ]}
      ],
      mobile: { rows: [
        { id: 'main', enabled: true, height: 100, colCount: 1, cols: [{ align: 'center', elements: [makeEl('widget'), makeEl('copyright')] }] }
      ]}
    };
  }

  function normalize(cfgIn) {
    var s = JSON.parse(JSON.stringify(cfgIn || defaultHeader()));
    if (!s.behavior) s.behavior = s.transparent ? 'transparent' : (s.sticky ? 'sticky' : 'static');
    if (!s.mobile_menu) s.mobile_menu = { icon: 'hamburger', style: 'drawer-right', show_logo: true, show_search: true, logo_url: '' };
    if (!s.nav_desktop) s.nav_desktop = { bg: '', color: '', shadow: false, font: '', font_size: 0 };
    if (!s.nav_mobile) s.nav_mobile = { bg: '', color: '', shadow: false, font: '', font_size: 0 };
    if (!s.mobile) s.mobile = { behavior: s.behavior, rows: mobileRows() };
    (s.rows || []).forEach(function (row) {
      if (!row.colCount) row.colCount = (row.cols && row.cols.length) || 3;
      (row.cols || []).forEach(function (col) {
        col.elements = (col.elements || []).map(function (e) {
          return typeof e === 'string' ? makeEl(e) : e;
        });
      });
    });
    return s;
  }

  function activeRows() {
    if (sheetMode === 'footer') {
      return device === 'mobile' ? (footerState.mobile && footerState.mobile.rows) || footerState.rows : footerState.rows;
    }
    return device === 'mobile' ? (state.mobile && state.mobile.rows) || state.rows : state.rows;
  }

  function persist() { save(); }
  function save() {
    var input = document.getElementById('bhb-config-input');
    if (input && state) {
      input.value = JSON.stringify(state);
      if (wp.customize('bankai_header_builder')) {
        wp.customize('bankai_header_builder').set(input.value);
      }
      $(input).trigger('change');
    }
    var fin = document.getElementById('bfb-config-input');
    if (fin && footerState) {
      fin.value = JSON.stringify(footerState);
      if (wp.customize('bankai_footer_builder')) {
        wp.customize('bankai_footer_builder').set(fin.value);
      }
      $(fin).trigger('change');
    }
  }

  function ensurePortal() {
    if (portal) return portal;
    portal = document.createElement('div');
    portal.id = 'bhb-portal';
    portal.className = 'bhb-portal';
    document.body.appendChild(portal);
    return portal;
  }

  function closeMenus() {
    if (portal) portal.innerHTML = '';
    document.querySelectorAll('.bhb-pop').forEach(function (p) { p.remove(); });
  }

  /* ——— Sheet ——— */
  function getPreviewHost() {
    // Mount sheet inside live preview frame area (not over sidebar)
    return document.querySelector('.wp-full-overlay-main') ||
      document.getElementById('customize-preview') ||
      document.body;
  }

  
  function enableSheetDrag(sheet) {
    var panel = sheet.querySelector('.bhb-sheet__panel');
    var handle = sheet.querySelector('.bhb-sheet__handle');
    if (!panel || !handle || handle.getAttribute('data-drag-bound')) return;
    handle.setAttribute('data-drag-bound', '1');
    handle.style.cursor = 'grab';
    var startY = 0, startBottom = 0, dragging = false;
    function onDown(e) {
      dragging = true;
      handle.style.cursor = 'grabbing';
      startY = (e.touches ? e.touches[0].clientY : e.clientY);
      var cs = window.getComputedStyle(panel);
      startBottom = parseInt(cs.bottom, 10) || 0;
      e.preventDefault();
    }
    function onMove(e) {
      if (!dragging) return;
      var y = (e.touches ? e.touches[0].clientY : e.clientY);
      var dy = startY - y; // drag up increases bottom
      var next = Math.max(0, Math.min(window.innerHeight - 120, startBottom + dy));
      panel.style.bottom = next + 'px';
    }
    function onUp() {
      dragging = false;
      handle.style.cursor = 'grab';
    }
    handle.addEventListener('mousedown', onDown);
    handle.addEventListener('touchstart', onDown, { passive: false });
    window.addEventListener('mousemove', onMove);
    window.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('mouseup', onUp);
    window.addEventListener('touchend', onUp);
  }

  function ensureSheet() {
    if (sheet && document.body.contains(sheet)) return sheet;
    sheet = document.createElement('div');
    sheet.id = 'bhb-sheet';
    sheet.className = 'bhb-sheet bhb-sheet--in-preview';
    sheet.innerHTML =
      '<div class="bhb-sheet__backdrop"></div>' +
      '<div class="bhb-sheet__panel">' +
        '<div class="bhb-sheet__handle"></div>' +
        '<div class="bhb-sheet__top">' +
          '<div class="bhb-device">' +
            '<button type="button" class="bhb-device__btn is-active" data-device="desktop" title="Desktop">' +
              '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>' +
            '</button>' +
            '<button type="button" class="bhb-device__btn" data-device="mobile" title="Mobile">' +
              '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>' +
            '</button>' +
          '</div>' +
          '<strong class="bhb-sheet__title">سربرگ</strong>' +
          '<button type="button" class="bhb-sheet__close" aria-label="Close">×</button>' +
        '</div>' +
        '<div class="bhb-sheet__body"></div>' +
      '</div>';
    getPreviewHost().appendChild(sheet);
    sheet.querySelector('.bhb-sheet__backdrop').addEventListener('click', closeSheet);
    sheet.querySelector('.bhb-sheet__close').addEventListener('click', closeSheet);
    enableSheetDrag(sheet);
    sheet.querySelectorAll('[data-device]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        device = btn.getAttribute('data-device');
        document.body.classList.toggle('bhb-device-mobile', device === 'mobile');
        document.body.classList.toggle('bhb-device-desktop', device !== 'mobile');
        sheet.querySelectorAll('[data-device]').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        // Notify preview iframe of device for responsive preview width
        try {
          if (wp.customize && wp.customize.previewedDevice) {
            wp.customize.previewedDevice.set(device === 'mobile' ? 'mobile' : 'desktop');
          }
        } catch (e) {}
        renderSheetBody();
      });
    });
    return sheet;
  }

  function openSheet(mode) {
    sheetMode = mode || 'header';
    ensureSheet();
    sheet.classList.add('is-open');
    sheet.classList.toggle('is-footer', sheetMode === 'footer');
    sheet.querySelector('.bhb-sheet__title').textContent = sheetMode === 'footer' ? 'پاورقی' : 'سربرگ';
    // footer: lift above preview so live footer stays visible
    if (sheetMode === 'footer') {
      sheet.classList.add('bhb-sheet--footer');
      document.body.classList.add('bhb-editing-footer');
    } else {
      sheet.classList.remove('bhb-sheet--footer');
      document.body.classList.remove('bhb-editing-footer');
    }
    renderSheetBody();
  }

  function closeSheet() {
    if (!sheet) return;
    sheet.classList.remove('is-open');
    document.body.classList.remove('bhb-editing-footer');
    closeMenus();
  }

  function renderSheetBody() {
    var body = sheet.querySelector('.bhb-sheet__body');
    var rows = activeRows();
    var html = '';
    (rows || []).forEach(function (row, ri) {
      html += rowHtml(row, ri);
    });
    if (!rows || !rows.length) {
      html = '<p class="bhb-empty">ردیفی نیست. یک پیش‌فرض انتخاب کنید.</p>';
    }
    body.innerHTML = html;
    bindRowEvents(body);
  }


  function renderRowSettingsSidebar(ri) {
    var dock = document.getElementById('bhb-element-dock');
    if (!dock) return;
    var rows = activeRows();
    var row = rows[ri];
    if (!row) return;
    var html = '<div class="bhb-side-panel" data-row-edit="' + ri + '">';
    html += '<div class="bhb-side-panel__title">تنظیمات ردیف ' + (ri + 1) + ' · ' + (row.id || '') + '</div>';
    html += '<div class="bhb-side-colors">';
    html += colorCircle('bg', 'پس‌زمینه', row.bg || '#ffffff');
    html += colorCircle('color', 'متن', row.color || '#1a1a1a');
    html += colorCircle('link_color', 'لینک', row.link_color || '#5c6b7a');
    html += colorCircle('link_hover', 'هاور لینک', row.link_hover || '#5c6b7a');
    html += colorCircle('link_active', 'صفحه فعلی', row.link_active || '#0969DA');
    html += '</div>';
    html += '<label class="bhb-side-field"><span>ارتفاع (px)</span><input type="range" min="28" max="160" data-row-style="height" value="' + (row.height || 60) + '"><em data-h-val>' + (row.height || 60) + '</em></label>';
    html += '<div class="bhb-side-toggles">';
    html += '<label class="bhb-check-pill' + (row.border ? ' is-on' : '') + '"><input type="checkbox" data-row-style="border" ' + (row.border ? 'checked' : '') + '><span>بوردر</span></label>';
    html += '<label class="bhb-check-pill' + (row.shadow ? ' is-on' : '') + '"><input type="checkbox" data-row-style="shadow" ' + (row.shadow ? 'checked' : '') + '><span>سایه</span></label>';
    html += '</div></div>';
    dock.innerHTML = html;
    dock.hidden = false;
    bindRowStyleDock(dock, ri);
  }

  function colorCircle(key, label, value) {
    var isNone = !value || value === 'none';
    var isTr = value === 'transparent';
    var bg = isNone || isTr ? '#ffffff' : value;
    var swClass = 'bhb-color-circle__swatch' + (isNone ? ' is-none' : '') + (isTr ? ' is-transparent' : '');
    return '<label class="bhb-color-circle" title="' + label + '">' +
      '<span class="bhb-color-circle__lab">' + label + '</span>' +
      '<span class="' + swClass + '" style="background:' + bg + '"></span>' +
      '<span class="bhb-color-circle__actions">' +
        '<button type="button" class="bhb-color-none' + (isNone ? ' is-on' : '') + '" data-color-special="none" data-color-key="' + key + '">None</button>' +
        '<button type="button" class="bhb-color-none' + (isTr ? ' is-on' : '') + '" data-color-special="transparent" data-color-key="' + key + '">شفاف</button>' +
      '</span>' +
      '<input type="color" data-row-style="' + key + '" value="' + value + '">' +
      '</label>';
  }

  function bindRowStyleDock(root, ri) {
    root.querySelectorAll('[data-row-style]').forEach(function (inp) {
      var apply = function () {
        var key = inp.getAttribute('data-row-style');
        var row = activeRows()[ri];
        if (!row) return;
        if (inp.type === 'checkbox') {
          row[key] = inp.checked;
          var pill = inp.closest('.bhb-check-pill');
          if (pill) pill.classList.toggle('is-on', inp.checked);
        } else if (inp.type === 'range' || inp.type === 'number') {
          row[key] = parseInt(inp.value, 10) || 60;
          var em = root.querySelector('[data-h-val]');
          if (em) em.textContent = row[key];
        } else {
          row[key] = inp.value;
          var sw = inp.parentNode.querySelector('.bhb-color-circle__swatch');
          if (sw) sw.style.background = inp.value;
        }
        save();
      };
      inp.addEventListener('input', apply);
      inp.addEventListener('change', apply);
    });
  }

  function rowHtml(row, ri) {
    var colCount = row.colCount || (row.cols && row.cols.length) || 3;
    var cols = row.cols || emptyCols(colCount);
    var h = '<div class="bhb-row" data-row="' + ri + '">';
    h += '<div class="bhb-row__bar">';
    h += '<label class="bhb-switch"><input type="checkbox" class="bhb-switch-input" data-row-enable ' + (row.enabled ? 'checked' : '') + '><span class="bhb-switch__ui"></span><span>ردیف ' + (ri + 1) + ' · ' + (row.id || '') + '</span></label>';
    h += '<div class="bhb-colpick" data-row="' + ri + '">';
    [1, 2, 3].forEach(function (n) {
      h += '<button type="button" class="bhb-colpick__btn' + (colCount === n ? ' is-active' : '') + '" data-cols="' + n + '" title="' + n + ' ستون">';
      h += '<span class="bhb-colpick__art bhb-colpick__art--' + n + '"></span></button>';
    });
    h += '</div></div>';
    h += '<button type="button" class="bhb-row-settings-btn" data-edit-row="' + ri + '">تنظیمات ردیف</button>';
    h += '<div class="bhb-row__cols bhb-row__cols--' + colCount + '">';
    for (var ci = 0; ci < colCount; ci++) {
      var col = cols[ci] || { align: 'center', elements: [] };
      h += colHtml(col, ri, ci);
    }
    h += '</div></div>';
    return h;
  }

  function colHtml(col, ri, ci) {
    var align = col.align || 'center';
    var h = '<div class="bhb-col" data-row="' + ri + '" data-col="' + ci + '">';
    h += '<div class="bhb-align">';
    ['start', 'center', 'end'].forEach(function (a) {
      var icon = a === 'start' ? '▦▌' : (a === 'center' ? '▦' : '▐▦');
      h += '<button type="button" class="bhb-align__btn' + (align === a ? ' is-active' : '') + '" data-align="' + a + '" title="' + a + '">' +
        '<span class="bhb-align__ico bhb-align__ico--' + a + '"></span></button>';
    });
    h += '</div>';
    h += '<div class="bhb-slots">';
    (col.elements || []).forEach(function (el, ei) {
      var t = elType(el);
      h += '<button type="button" class="bhb-chip" data-el="' + ei + '" data-type="' + t + '">';
      h += '<span class="bhb-chip__ico">' + (ICONS[t] || ICONS.html) + '</span>';
      h += '<span class="bhb-chip__lab">' + t + '</span>';
      h += '<span class="bhb-chip__x" data-remove>×</span>';
      h += '</button>';
    });
    h += '<button type="button" class="bhb-add" data-add>+</button>';
    h += '</div></div>';
    return h;
  }

  function bindRowEvents(body) {
    body.querySelectorAll('[data-row-enable]').forEach(function (cb) {
      cb.addEventListener('change', function () {
        var ri = +cb.closest('[data-row]').getAttribute('data-row');
        activeRows()[ri].enabled = cb.checked;
        save();
      });
    });
    body.querySelectorAll('[data-edit-row]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        renderRowSettingsSidebar(+btn.getAttribute('data-edit-row'));
      });
    });
    body.querySelectorAll('[data-row-style]').forEach(function (inp) {
      var handler = function () {
        var wrap = inp.closest('[data-row]');
        var ri = +wrap.getAttribute('data-row');
        var key = inp.getAttribute('data-row-style');
        if (inp.type === 'checkbox') {
          activeRows()[ri][key] = inp.checked;
          inp.closest('.bhb-check-pill') && inp.closest('.bhb-check-pill').classList.toggle('is-on', inp.checked);
        } else if (inp.type === 'number') {
          activeRows()[ri][key] = parseInt(inp.value, 10) || 60;
        } else {
          activeRows()[ri][key] = inp.value;
        }
        save();
      };
      inp.addEventListener('change', handler);
      inp.addEventListener('input', handler);
    });
    body.querySelectorAll('[data-cols]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var ri = +btn.closest('[data-row]').getAttribute('data-row');
        var n = +btn.getAttribute('data-cols');
        var row = activeRows()[ri];
        row.colCount = n;
        while (row.cols.length < n) row.cols.push({ align: 'center', elements: [] });
        row.cols = row.cols.slice(0, n);
        save();
        renderSheetBody();
      });
    });
    body.querySelectorAll('[data-align]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var colEl = btn.closest('.bhb-col');
        var ri = +colEl.getAttribute('data-row');
        var ci = +colEl.getAttribute('data-col');
        activeRows()[ri].cols[ci].align = btn.getAttribute('data-align');
        colEl.querySelectorAll('[data-align]').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        save();
      });
    });
    body.querySelectorAll('.bhb-add').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        openAddMenu(btn);
      });
    });
    body.querySelectorAll('.bhb-chip').forEach(function (chip) {
      chip.addEventListener('click', function (e) {
        if (e.target.closest('[data-remove]')) {
          var colEl = chip.closest('.bhb-col');
          var ri = +colEl.getAttribute('data-row');
          var ci = +colEl.getAttribute('data-col');
          var ei = +chip.getAttribute('data-el');
          activeRows()[ri].cols[ci].elements.splice(ei, 1);
          save();
          renderSheetBody();
          return;
        }
        openElementEditor(chip);
      });
    });
  }

  function openAddMenu(anchor) {
    closeMenus();
    var colEl = anchor.closest('.bhb-col');
    var ri = +colEl.getAttribute('data-row');
    var ci = +colEl.getAttribute('data-col');
    var map = sheetMode === 'footer' ? (cfg().footerElements || {}) : (cfg().elements || {});
    if (!Object.keys(map).length) {
      map = sheetMode === 'footer'
        ? { widget: 'ویجت', social: 'سوشال', copyright: 'کپی‌رایت', menu: 'منو', html: 'HTML', divider: 'جداکننده' }
        : { logo: 'لوگو', menu: 'منو', search: 'جستجو', button: 'دکمه', social: 'سوشال', account: 'حساب', cart: 'سبد', phone: 'تلفن', text: 'متن', html: 'HTML', divider: 'جداکننده' };
    }
    // Toggle / mobile menu button only when editing mobile layout
    if (device !== 'mobile' && map.toggle) {
      // keep toggle as theme switch (desktop ok) but mark mobile_nav separate
    }
    if (device !== 'mobile' && map.mobile_toggle) {
      delete map.mobile_toggle;
    }
    var pop = document.createElement('div');
    pop.className = 'bhb-pop bhb-pop--add';
    var html = '<div class="bhb-pop__grid">';
    Object.keys(map).forEach(function (k) {
      html += '<button type="button" class="bhb-pop__item" data-type="' + k + '">' +
        '<span class="bhb-pop__ico">' + (ICONS[k] || ICONS.html) + '</span>' +
        '<span>' + map[k] + '</span></button>';
    });
    html += '</div>';
    pop.innerHTML = html;
    ensurePortal().appendChild(pop);
    positionPop(pop, anchor);

    pop.querySelectorAll('[data-type]').forEach(function (item) {
      item.addEventListener('click', function () {
        activeRows()[ri].cols[ci].elements.push(makeEl(item.getAttribute('data-type')));
        save();
        closeMenus();
        renderSheetBody();
      });
    });
  }

  function openElementEditor(chip) {
    closeMenus();
    var colEl = chip.closest('.bhb-col');
    var ri = +colEl.getAttribute('data-row');
    var ci = +colEl.getAttribute('data-col');
    var ei = +chip.getAttribute('data-el');
    var el = activeRows()[ri].cols[ci].elements[ei];
    var type = elType(el);
    var props = Object.assign({}, elProps(el));

    var labels = { logo: 'لوگو', menu: 'منو', search: 'جستجو', button: 'دکمه', social: 'سوشال', account: 'حساب', cart: 'سبد', phone: 'تلفن', text: 'متن', html: 'HTML', divider: 'جداکننده', widget: 'ویجت', copyright: 'کپی‌رایت' };
    var html = '<div class="bhb-edit"><div class="bhb-edit__title">تنظیمات · ' + (labels[type] || type) + '</div>';

    if (type === 'logo') {
      html += selectField('mode', 'نوع نمایش', props.mode || 'auto', { auto: 'خودکار (لوگوی وردپرس)', image: 'تصویر سفارشی', text: 'متن' });
      html += field('label', 'متن لوگو', props.label || '', 'text');
      html += mediaField('image', 'تصویر لوگو (آپلود)', props.image || '');
      html += field('width', 'عرض (px)', props.width || 140, 'number');
      html += field('height', 'ارتفاع (px)', props.height || 40, 'number');
    } else if (type === 'search') {
      html += selectField('box', 'نوع باکس', props.box || 'icon', { icon: 'فقط آیکون', expand: 'بازشو', full: 'فیلد کامل' });
      html += selectField('icon', 'آیکون', props.icon || 'search', iconOptions(props.icon));
      html += mediaField('icon_image', 'آیکون سفارشی (آپلود تصویر)', props.icon_image || '');
      html += field('placeholder', 'placeholder', props.placeholder || 'جستجو…', 'text');
      html += field('size', 'سایز آیکون', props.size || 20, 'number');
      html += field('color', 'رنگ', props.color || '#1A1A1A', 'color');
      html += field('bg', 'پس‌زمینه', props.bg || '#ffffff', 'color');
    } else if (type === 'button') {
      html += field('label', 'متن دکمه', props.label || 'تماس', 'text');
      html += field('url', 'لینک', props.url || '', 'url');
      html += selectField('style', 'سبک', props.style || 'solid', { solid: 'توپر', outline: 'خطی', ghost: 'شفاف' });
      html += field('color', 'رنگ متن', props.color || '#ffffff', 'color');
      html += field('bg', 'پس‌زمینه', props.bg || '#5C6B7A', 'color');
      html += field('radius', 'انحنا', props.radius || 10, 'number');
    } else if (type === 'menu') {
      html += '<p class="bhb-edit__hint">منوی «اصلی» وردپرس نمایش داده می‌شود. از نمایش > فهرست‌ها اختصاص دهید.</p>';
      html += field('color', 'رنگ لینک', props.color || '', 'color');
      html += field('active_color', 'رنگ صفحه فعلی (active)', props.active_color || '#0969DA', 'color');
    } else if (type === 'account' || type === 'cart') {
      html += field('size', 'سایز آیکون', props.size || 20, 'number');
      html += field('color', 'رنگ', props.color || '#1A1A1A', 'color');
      if (type === 'account') {
        html += '<label class="bhb-edit__check"><input type="checkbox" data-prop="show_label" ' + (props.show_label ? 'checked' : '') + '> نمایش برچسب</label>';
      }
    } else if (type === 'phone' || type === 'text') {
      html += field('label', type === 'phone' ? 'شماره تلفن' : 'متن', props.label || '', 'text');
      html += field('size', 'سایز', props.size || 16, 'number');
      html += field('color', 'رنگ', props.color || '#1A1A1A', 'color');
    } else if (type === 'html') {
      html += field('html', 'HTML', props.html || '', 'text');
    } else {
      html += field('label', 'عنوان', props.label || '', 'text');
      html += field('url', 'لینک', props.url || '', 'url');
      html += field('color', 'رنگ', props.color || '#1A1A1A', 'color');
      html += field('bg', 'پس‌زمینه', props.bg || '#ffffff', 'color');
    }

    html += '<p class="bhb-edit__live-hint">تغییرات به‌صورت زنده ذخیره می‌شوند</p></div>';

    // Prefer dock in customizer sidebar if available
    var dock = document.getElementById('bhb-element-dock');
    if (dock) {
      dock.innerHTML = html;
      dock.hidden = false;
      bindEditForm(dock, ri, ci, ei, type, props);
      return;
    }

    var pop = document.createElement('div');
    pop.className = 'bhb-pop bhb-pop--edit';
    pop.innerHTML = html;
    ensurePortal().appendChild(pop);
    positionPop(pop, chip);
    bindEditForm(pop, ri, ci, ei, type, props);
  }


  function mediaField(prop, label, value) {
    return '<div class="bhb-field bhb-field--media" data-media-prop="' + prop + '">' +
      '<span class="bhb-field__lab">' + label + '</span>' +
      '<div class="bhb-media-row">' +
        '<input type="url" data-prop="' + prop + '" value="' + (value || '').replace(/"/g, '&quot;') + '" placeholder="https://… یا آپلود" dir="ltr" />' +
        '<button type="button" class="bhb-media-btn" data-media-for="' + prop + '">آپلود</button>' +
      '</div>' +
      (value ? '<div class="bhb-media-preview"><img src="' + value.replace(/"/g, '&quot;') + '" alt="" /></div>' : '') +
    '</div>';
  }

  function bindMediaButtons(root) {
    root.querySelectorAll('[data-media-for]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var prop = btn.getAttribute('data-media-for');
        if (typeof wp === 'undefined' || !wp.media) {
          alert('کتابخانه رسانه در دسترس نیست');
          return;
        }
        var frame = wp.media({
          title: 'انتخاب تصویر / آیکون',
          button: { text: 'استفاده' },
          multiple: false,
          library: { type: 'image' }
        });
        frame.on('select', function () {
          var att = frame.state().get('selection').first().toJSON();
          var url = att.url || '';
          var input = root.querySelector('[data-prop="' + prop + '"]');
          if (input) { input.value = url; input.dispatchEvent(new Event('input', { bubbles: true })); input.dispatchEvent(new Event('change', { bubbles: true })); }
          var wrap = btn.closest('.bhb-field--media');
          if (wrap) {
            var prev = wrap.querySelector('.bhb-media-preview');
            if (!prev) {
              prev = document.createElement('div');
              prev.className = 'bhb-media-preview';
              wrap.appendChild(prev);
            }
            prev.innerHTML = url ? '<img src="' + url + '" alt="" />' : '';
          }
        });
        frame.open();
      });
    });
  }

  function bindEditForm(root, ri, ci, ei, type, props) {
    bindMediaButtons(root);
    function commitLive() {
      var p = Object.assign({}, props);
      root.querySelectorAll('[data-prop]').forEach(function (inp) {
        if (inp.type === 'checkbox') {
          p[inp.getAttribute('data-prop')] = inp.checked;
        } else if (inp.type === 'number') {
          p[inp.getAttribute('data-prop')] = parseInt(inp.value, 10) || 0;
        } else {
          p[inp.getAttribute('data-prop')] = inp.value;
        }
      });
      // update color swatches
      root.querySelectorAll('.bhb-color-circle input[type="color"]').forEach(function (inp) {
        var sw = inp.parentNode.querySelector('.bhb-color-circle__swatch');
        if (sw) sw.style.background = inp.value;
      });
      activeRows()[ri].cols[ci].elements[ei] = { type: type, props: p };
      save();
    }
    root.querySelectorAll('[data-prop]').forEach(function (inp) {
      inp.addEventListener('input', commitLive);
      inp.addEventListener('change', commitLive);
    });
  }

  function field(key, label, val, type) {
    type = type || 'text';
    val = val == null ? '' : val;
    if (type === 'color') {
      return '<label class="bhb-color-circle" title="' + label + '">' +
        '<span class="bhb-color-circle__lab">' + label + '</span>' +
        '<span class="bhb-color-circle__swatch" style="background:' + (val || '#ffffff') + '"></span>' +
        '<input type="color" data-prop="' + key + '" value="' + (val || '#ffffff') + '">' +
        '</label>';
    }
    return '<label class="bhb-edit__field"><span>' + label + '</span>' +
      '<input type="' + type + '" data-prop="' + key + '" value="' + String(val).replace(/"/g, '&quot;') + '"></label>';
  }
  function selectField(key, label, val, opts) {
    var h = '<label class="bhb-edit__field"><span>' + label + '</span><select data-prop="' + key + '">';
    Object.keys(opts).forEach(function (k) {
      h += '<option value="' + k + '"' + (val === k ? ' selected' : '') + '>' + opts[k] + '</option>';
    });
    return h + '</select></label>';
  }

  function positionPop(pop, anchor) {
    var r = anchor.getBoundingClientRect();
    var pw = 300;
    var left = Math.min(window.innerWidth - pw - 12, Math.max(12, r.left));
    var top = r.bottom + 8;
    if (top + 320 > window.innerHeight) top = Math.max(12, r.top - 320);
    pop.style.position = 'fixed';
    pop.style.left = left + 'px';
    pop.style.top = top + 'px';
    pop.style.zIndex = '1000000';
  }

  /* ——— Sidebar visuals ——— */
  
  
  
  // --- Lucide icon modal (full catalog + categories + customize) ---
  var lucideNodesCache = null;
  var lucideTagsCache = null;
  var lucideCatsCache = null;
  var iconModalTarget = null;
  var iconModalSelected = '';
  var iconModalStroke = 2;
  var iconModalSize = 24;
  var iconModalColor = 'currentColor';
  var iconModalCategory = 'all';
  var iconModalQuery = '';
  var iconModalRendered = 0;
  var ICON_PAGE = 120;

  function lucideCfg(key) {
    return (window.bankaiHeaderBuilder && window.bankaiHeaderBuilder[key]) || '';
  }

  function loadLucideData() {
    if (lucideNodesCache) return Promise.resolve(lucideNodesCache);
    var url = lucideCfg('lucideNodesUrl');
    if (!url) return Promise.resolve({});
    return fetch(url).then(function (r) { return r.json(); }).then(function (data) {
      lucideNodesCache = data || {};
      return lucideNodesCache;
    }).catch(function () { lucideNodesCache = {}; return lucideNodesCache; });
  }

  function loadLucideMeta() {
    var p1 = Promise.resolve();
    var p2 = Promise.resolve();
    if (!lucideTagsCache && lucideCfg('lucideTagsUrl')) {
      p1 = fetch(lucideCfg('lucideTagsUrl')).then(function (r) { return r.json(); }).then(function (t) { lucideTagsCache = t || {}; }).catch(function () { lucideTagsCache = {}; });
    }
    if (!lucideCatsCache && lucideCfg('lucideCategoriesUrl')) {
      p2 = fetch(lucideCfg('lucideCategoriesUrl')).then(function (r) { return r.json(); }).then(function (t) { lucideCatsCache = t || {}; }).catch(function () { lucideCatsCache = {}; });
    } else if (!lucideCatsCache) {
      lucideCatsCache = {};
    }
    return Promise.all([p1, p2]);
  }

  function nodesToSvg(nodes, size, stroke, color) {
    size = size || 22;
    stroke = stroke == null ? 2 : stroke;
    color = color || 'currentColor';
    if (!nodes || !nodes.length) return '';
    var inner = '';
    nodes.forEach(function (node) {
      if (!node || !node[0]) return;
      var tag = String(node[0]).replace(/[^a-zA-Z]/g, '');
      var attrs = node[1] || {};
      var attrStr = '';
      Object.keys(attrs).forEach(function (k) {
        attrStr += ' ' + k + '="' + String(attrs[k]).replace(/"/g, '&quot;') + '"';
      });
      inner += '<' + tag + attrStr + '/>';
    });
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="' + color + '" stroke-width="' + stroke + '" stroke-linecap="round" stroke-linejoin="round">' + inner + '</svg>';
  }

  function filteredIconNames() {
    if (!lucideNodesCache) return [];
    var names = Object.keys(lucideNodesCache);
    if (iconModalCategory && iconModalCategory !== 'all' && lucideCatsCache && lucideCatsCache[iconModalCategory]) {
      var set = {};
      lucideCatsCache[iconModalCategory].forEach(function (n) { set[n] = true; });
      names = names.filter(function (n) { return set[n]; });
    }
    var q = (iconModalQuery || '').trim().toLowerCase();
    if (q) {
      names = names.filter(function (n) {
        if (n.indexOf(q) !== -1) return true;
        if (lucideTagsCache && lucideTagsCache[n]) {
          return lucideTagsCache[n].some(function (t) { return String(t).toLowerCase().indexOf(q) !== -1; });
        }
        return false;
      });
    }
    return names.sort();
  }

  function ensureIconModal() {
    var m = document.getElementById('bhb-icon-modal');
    if (m) return m;
    m = document.createElement('div');
    m.id = 'bhb-icon-modal';
    m.className = 'bhb-icon-modal';
    m.innerHTML =
      '<div class="bhb-icon-modal__panel" role="dialog" aria-modal="true">' +
        '<div class="bhb-icon-modal__layout">' +
          '<aside class="bhb-icon-modal__sidebar">' +
            '<div class="bhb-icon-modal__brand">Lucide <span>Icons</span></div>' +
            '<div class="bhb-icon-modal__cats" id="bhb-icon-cats"></div>' +
          '</aside>' +
          '<div class="bhb-icon-modal__main">' +
            '<div class="bhb-icon-modal__toolbar">' +
              '<input type="search" class="bhb-icon-modal__search" id="bhb-icon-search" placeholder="Search all icons…" autocomplete="off" />' +
              '<button type="button" class="bhb-icon-modal__x" data-icon-close aria-label="Close">×</button>' +
            '</div>' +
            '<div class="bhb-icon-modal__meta"><span id="bhb-icon-count">0</span> icons</div>' +
            '<div class="bhb-icon-modal__grid" id="bhb-icon-grid"></div>' +
          '</div>' +
          '<aside class="bhb-icon-modal__customize" id="bhb-icon-customize">' +
            '<div class="bhb-icon-modal__cust-title">Customize</div>' +
            '<div class="bhb-icon-modal__preview-box" id="bhb-icon-live-preview"></div>' +
            '<div class="bhb-icon-modal__sel-name" id="bhb-icon-sel-name">—</div>' +
            '<label class="bhb-icon-modal__field">Color<input type="color" id="bhb-icon-color" value="#1a1a1a"></label>' +
            '<label class="bhb-icon-modal__field">Stroke width<input type="range" id="bhb-icon-stroke" min="0.5" max="3" step="0.25" value="2"><span id="bhb-icon-stroke-val">2</span></label>' +
            '<label class="bhb-icon-modal__field">Size<input type="range" id="bhb-icon-size" min="16" max="48" step="1" value="24"><span id="bhb-icon-size-val">24</span></label>' +
            '<div class="bhb-icon-modal__actions">' +
              '<button type="button" class="bhb-icon-modal__cancel" data-icon-close>انصراف</button>' +
              '<button type="button" class="bhb-icon-modal__apply" data-icon-apply>اعمال آیکون</button>' +
            '</div>' +
          '</aside>' +
        '</div>' +
      '</div>';
    document.body.appendChild(m);

    var search = m.querySelector('#bhb-icon-search');
    var t = null;
    search.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(function () {
        iconModalQuery = search.value;
        iconModalRendered = 0;
        renderIconGrid(true);
      }, 100);
    });

    m.querySelector('#bhb-icon-color').addEventListener('input', function (e) {
      iconModalColor = e.target.value;
      updateIconLivePreview();
    });
    m.querySelector('#bhb-icon-stroke').addEventListener('input', function (e) {
      iconModalStroke = parseFloat(e.target.value) || 2;
      m.querySelector('#bhb-icon-stroke-val').textContent = String(iconModalStroke);
      updateIconLivePreview();
    });
    m.querySelector('#bhb-icon-size').addEventListener('input', function (e) {
      iconModalSize = parseInt(e.target.value, 10) || 24;
      m.querySelector('#bhb-icon-size-val').textContent = String(iconModalSize);
      updateIconLivePreview();
    });

    var grid = m.querySelector('#bhb-icon-grid');
    grid.addEventListener('scroll', function () {
      if (grid.scrollTop + grid.clientHeight >= grid.scrollHeight - 80) {
        renderIconGrid(false);
      }
    });

    return m;
  }

  function updateIconLivePreview() {
    var box = document.getElementById('bhb-icon-live-preview');
    var nameEl = document.getElementById('bhb-icon-sel-name');
    if (!box || !lucideNodesCache || !iconModalSelected) return;
    box.innerHTML = nodesToSvg(lucideNodesCache[iconModalSelected], iconModalSize, iconModalStroke, iconModalColor === 'currentColor' ? '#1a1a1a' : iconModalColor);
    if (nameEl) nameEl.textContent = iconModalSelected;
  }

  function renderCategories() {
    var el = document.getElementById('bhb-icon-cats');
    if (!el) return;
    var html = '<button type="button" class="bhb-icon-cat' + (iconModalCategory === 'all' ? ' is-active' : '') + '" data-icon-cat="all">All icons</button>';
    var cats = Object.keys(lucideCatsCache || {}).sort();
    cats.forEach(function (c) {
      html += '<button type="button" class="bhb-icon-cat' + (iconModalCategory === c ? ' is-active' : '') + '" data-icon-cat="' + c + '">' + c + ' <small>' + (lucideCatsCache[c].length) + '</small></button>';
    });
    el.innerHTML = html;
    el.querySelectorAll('[data-icon-cat]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        iconModalCategory = btn.getAttribute('data-icon-cat') || 'all';
        iconModalRendered = 0;
        renderCategories();
        renderIconGrid(true);
      });
    });
  }

  function renderIconGrid(reset) {
    var modal = document.getElementById('bhb-icon-modal');
    if (!modal || !lucideNodesCache) return;
    var grid = modal.querySelector('#bhb-icon-grid');
    var countEl = modal.querySelector('#bhb-icon-count');
    var names = filteredIconNames();
    if (countEl) countEl.textContent = String(names.length);
    if (reset) {
      grid.innerHTML = '';
      iconModalRendered = 0;
    }
    var next = names.slice(iconModalRendered, iconModalRendered + ICON_PAGE);
    var frag = document.createDocumentFragment();
    next.forEach(function (id) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'bhb-icon-modal__item' + (id === iconModalSelected ? ' is-selected' : '');
      b.setAttribute('data-icon-id', id);
      b.title = id;
      b.innerHTML = '<span class="bhb-icon-modal__svg">' + nodesToSvg(lucideNodesCache[id], 22, 2, '#1a1a1a') + '</span><span class="bhb-icon-modal__name">' + id + '</span>';
      b.addEventListener('click', function () {
        iconModalSelected = id;
        grid.querySelectorAll('.bhb-icon-modal__item').forEach(function (x) { x.classList.remove('is-selected'); });
        b.classList.add('is-selected');
        updateIconLivePreview();
      });
      frag.appendChild(b);
    });
    grid.appendChild(frag);
    iconModalRendered += next.length;
  }

  function openIconModal(btn) {
    iconModalTarget = btn;
    iconModalSelected = btn.getAttribute('data-icon') || 'home';
    iconModalStroke = 2;
    iconModalSize = 24;
    iconModalColor = '#1a1a1a';
    iconModalCategory = 'all';
    iconModalQuery = '';
    iconModalRendered = 0;
    var modal = ensureIconModal();
    modal.classList.add('is-open');
    var search = modal.querySelector('#bhb-icon-search');
    search.value = '';
    modal.querySelector('#bhb-icon-grid').innerHTML = '<div class="bhb-icon-modal__loading">Loading Lucide…</div>';
    loadLucideData().then(function () {
      return loadLucideMeta();
    }).then(function () {
      renderCategories();
      renderIconGrid(true);
      updateIconLivePreview();
      search.focus();
    });
  }

  function closeIconModal() {
    var modal = document.getElementById('bhb-icon-modal');
    if (modal) modal.classList.remove('is-open');
    iconModalTarget = null;
  }

  function applyIconModal() {
    if (!iconModalTarget || !iconModalSelected) { closeIconModal(); return; }
    iconModalTarget.setAttribute('data-icon', iconModalSelected);
    var glyph = iconModalTarget.querySelector('.bhb-icon-preview__glyph');
    if (glyph && lucideNodesCache && lucideNodesCache[iconModalSelected]) {
      glyph.innerHTML = nodesToSvg(lucideNodesCache[iconModalSelected], 22, iconModalStroke, iconModalColor);
    }
    var bag = iconModalTarget.getAttribute('data-ft');
    var key = iconModalTarget.getAttribute('data-ft-key') || 'icon';
    if (bag) {
      if (typeof ensureFooterState === 'function') {
        var fs = ensureFooterState();
        if (!fs[bag]) fs[bag] = {};
        fs[bag][key] = iconModalSelected;
        fs[bag].icon_stroke = iconModalStroke;
        fs[bag].icon_size = iconModalSize;
        fs[bag].icon_color = iconModalColor === '#1a1a1a' ? '' : iconModalColor;
        saveFooterState();
      }
    }
    if (iconModalTarget.getAttribute('data-prop') === 'icon' && typeof setProp === 'function') {
      setProp('icon', iconModalSelected);
    }
    if (iconModalTarget.hasAttribute('data-mm-icon-set') || iconModalTarget.hasAttribute('data-mm-icon-pick')) {
      if (!state) state = normalize({});
      if (!state.mobile_menu) state.mobile_menu = { icon: 'menu', style: 'drawer-right' };
      state.mobile_menu.icon = iconModalSelected;
      save();
      var g = iconModalTarget.querySelector('.bhb-icon-preview__glyph');
      if (g && lucideNodesCache && lucideNodesCache[iconModalSelected]) {
        g.innerHTML = nodesToSvg(lucideNodesCache[iconModalSelected], 24, iconModalStroke, iconModalColor || '#1a1a1a');
      }
      iconModalTarget.setAttribute('data-icon', iconModalSelected);
    }
    if (iconModalTarget.hasAttribute('data-b404-icon')) {
      var b404id = iconModalTarget.getAttribute('data-b404-icon');
      if (b404id && wp.customize(b404id)) {
        wp.customize(b404id).set(iconModalSelected);
        if (wp.customize.state && wp.customize.state('saved')) wp.customize.state('saved').set(false);
      }
      var g404 = iconModalTarget.querySelector('.bhb-icon-preview__glyph');
      if (g404 && lucideNodesCache && lucideNodesCache[iconModalSelected]) {
        g404.innerHTML = nodesToSvg(lucideNodesCache[iconModalSelected], 20, iconModalStroke, iconModalColor || '#1a1a1a');
      }
      iconModalTarget.setAttribute('data-icon', iconModalSelected);
    }
    if (iconModalTarget.hasAttribute('data-bn-item-icon')) {
      var bi = parseInt(iconModalTarget.getAttribute('data-bn-item-icon'), 10);
      if (typeof ensureFooterState === 'function') {
        var fs2 = ensureFooterState();
        if (!Array.isArray(fs2.bottom_nav.items)) fs2.bottom_nav.items = [];
        if (!fs2.bottom_nav.items[bi]) fs2.bottom_nav.items[bi] = { icon: 'home', label: '', url: '' };
        fs2.bottom_nav.items[bi].icon = iconModalSelected;
        saveFooterState();
      }
    }
    closeIconModal();
  }

  document.addEventListener('click', function (e) {
    var openBtn = e.target.closest('[data-open-icon-modal]');
    if (openBtn) {
      e.preventDefault();
      openIconModal(openBtn);
      return;
    }
    if (e.target.closest('[data-icon-close]')) { closeIconModal(); return; }
    if (e.target.closest('[data-icon-apply]')) { applyIconModal(); return; }
    var modal = document.getElementById('bhb-icon-modal');
    if (modal && e.target === modal) closeIconModal();
  });


  // Footer bsl cards + bottom nav columns
  function ensureFooterState() {
    if (!window.bankaiFooterState) {
      try {
        var raw = wp.customize('bankai_footer_builder') && wp.customize('bankai_footer_builder').get();
        window.bankaiFooterState = typeof raw === 'string' ? JSON.parse(raw) : (raw || {});
      } catch (e) { window.bankaiFooterState = {}; }
    }
    if (!window.bankaiFooterState.scroll_top) window.bankaiFooterState.scroll_top = {};
    if (!window.bankaiFooterState.contact_fab) window.bankaiFooterState.contact_fab = {};
    if (!window.bankaiFooterState.bottom_nav) window.bankaiFooterState.bottom_nav = { items: [], columns: 3, item_layout: 'icon-text-below', style_mode: 'inherit' };
    if (!Array.isArray(window.bankaiFooterState.bottom_nav.items)) window.bankaiFooterState.bottom_nav.items = [];
    return window.bankaiFooterState;
  }
  function saveFooterState() {
    var fs = ensureFooterState();
    if (wp.customize('bankai_footer_builder')) {
      wp.customize('bankai_footer_builder').set(JSON.stringify(fs));
      if (wp.customize.state && wp.customize.state('saved')) wp.customize.state('saved').set(false);
    }
  }
  function padBottomNavItems(cols) {
    var fs = ensureFooterState();
    var defaults = [
      {icon:'home',label:'خانه',url:'/'},
      {icon:'search',label:'جستجو',url:'/?s='},
      {icon:'user',label:'حساب',url:'/'},
      {icon:'heart',label:'علاقه‌مندی',url:'/'},
      {icon:'settings',label:'بیشتر',url:'/'}
    ];
    while (fs.bottom_nav.items.length < cols) {
      var d = defaults[fs.bottom_nav.items.length] || {icon:'circle',label:'',url:'#'};
      fs.bottom_nav.items.push({icon:d.icon,label:d.label,url:d.url});
    }
  }
  function syncBnItemVisibility() {
    var fs = ensureFooterState();
    var cols = parseInt((fs.bottom_nav && fs.bottom_nav.columns) || 3, 10) || 3;
    document.querySelectorAll('[data-bn-item]').forEach(function (el) {
      var i = parseInt(el.getAttribute('data-bn-item'), 10);
      el.classList.toggle('is-hidden', i >= cols);
    });
  }
  document.addEventListener('click', function (e) {
    var card = e.target.closest('[data-ft-card]');
    if (card) {
      e.preventDefault();
      var key = card.getAttribute('data-ft-card');
      var fs = ensureFooterState();
      if (!fs[key]) fs[key] = {};
      fs[key].enabled = !fs[key].enabled;
      card.classList.toggle('is-on', !!fs[key].enabled);
      card.setAttribute('aria-pressed', fs[key].enabled ? 'true' : 'false');
      var stateEl = card.querySelector('.bsl-card__state');
      if (stateEl) stateEl.textContent = fs[key].enabled ? 'فعال' : 'خاموش';
      var ed = document.querySelector('[data-ft-editor="' + key + '"]');
      if (ed) ed.classList.toggle('is-open', !!fs[key].enabled);
      saveFooterState();
      return;
    }
    var colsBtn = e.target.closest('[data-bn-cols]');
    if (colsBtn) {
      e.preventDefault();
      var cols = parseInt(colsBtn.getAttribute('data-bn-cols'), 10) || 3;
      var fs = ensureFooterState();
      fs.bottom_nav.columns = cols;
      padBottomNavItems(cols);
      document.querySelectorAll('[data-bn-cols]').forEach(function (b) {
        b.classList.toggle('is-on', parseInt(b.getAttribute('data-bn-cols'), 10) === cols);
      });
      syncBnItemVisibility();
      saveFooterState();
      return;
    }
    var layoutBtn = e.target.closest('[data-bn-layout]');
    if (layoutBtn) {
      e.preventDefault();
      var layout = layoutBtn.getAttribute('data-bn-layout');
      var fs = ensureFooterState();
      fs.bottom_nav.item_layout = layout;
      document.querySelectorAll('[data-bn-layout]').forEach(function (b) {
        b.classList.toggle('is-on', b.getAttribute('data-bn-layout') === layout);
      });
      saveFooterState();
      return;
    }
  });
  document.addEventListener('input', function (e) {
    var t = e.target;
    if (t.hasAttribute('data-bn-item-label') || t.hasAttribute('data-bn-item-url')) {
      var fs = ensureFooterState();
      if (!Array.isArray(fs.bottom_nav.items)) fs.bottom_nav.items = [];
      var idx = parseInt(t.getAttribute('data-bn-item-label') || t.getAttribute('data-bn-item-url'), 10);
      if (!fs.bottom_nav.items[idx]) fs.bottom_nav.items[idx] = { icon: 'home', label: '', url: '' };
      if (t.hasAttribute('data-bn-item-label')) fs.bottom_nav.items[idx].label = t.value;
      if (t.hasAttribute('data-bn-item-url')) fs.bottom_nav.items[idx].url = t.value;
      saveFooterState();
    }
  });



  function renderFooterInlineBuilder() {
    var box = document.getElementById('bfb-inline-builder');
    if (!box) return;
    ensureFooterState();
    var fs = window.bankaiFooterState;
    if (!fs.rows || !fs.rows.length) {
      // seed default 1 row
      fs.rows = [{
        id: 'main', enabled: true, height: 80, bg: '', color: '', border: false, shadow: false,
        cols: [
          { align: 'start', elements: [{ type: 'widget', props: {} }] },
          { align: 'center', elements: [{ type: 'copyright', props: {} }] },
          { align: 'end', elements: [{ type: 'social', props: {} }] }
        ]
      }];
      saveFooterState();
    }
    var html = '';
    (fs.rows || []).forEach(function (row, ri) {
      var on = row.enabled !== false;
      html += '<div class="bfb-row-card' + (on ? ' is-on' : '') + '" data-bfb-row="' + ri + '">';
      html += '<div class="bfb-row-card__top">';
      html += '<label class="bhb-check-pill' + (on ? ' is-on' : '') + '"><input type="checkbox" data-bfb-row-en="' + ri + '"' + (on ? ' checked' : '') + '><span>ردیف ' + (ri + 1) + (row.id ? ' · ' + row.id : '') + '</span></label>';
      html += '<button type="button" class="bfb-row-card__edit" data-bfb-row-edit="' + ri + '">ویرایش</button>';
      html += '</div>';
      html += '<div class="bfb-row-card__preview" aria-hidden="true">';
      var cols = row.cols || [];
      cols.forEach(function (col) {
        var els = (col.elements || []).map(function (e) { return (e && e.type) ? e.type : ''; }).filter(Boolean);
        html += '<span class="bfb-row-card__col">' + (els.length ? els.join(' · ') : '—') + '</span>';
      });
      html += '</div></div>';
    });
    html += '<div class="bfb-row-actions">';
    html += '<button type="button" class="bhb-open-btn" data-bfb-add-row>+ افزودن ردیف</button>';
    html += '</div>';
    box.innerHTML = html;

    box.querySelectorAll('[data-bfb-row-en]').forEach(function (inp) {
      inp.addEventListener('change', function () {
        var ri = parseInt(inp.getAttribute('data-bfb-row-en'), 10);
        ensureFooterState();
        if (window.bankaiFooterState.rows[ri]) {
          window.bankaiFooterState.rows[ri].enabled = inp.checked;
          saveFooterState();
          renderFooterInlineBuilder(); enhanceColorCircles();
        }
      });
    });
    box.querySelectorAll('[data-bfb-row-edit]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var ri = parseInt(btn.getAttribute('data-bfb-row-edit'), 10);
        openFooterRowEditor(ri);
      });
    });
    var add = box.querySelector('[data-bfb-add-row]');
    if (add) add.addEventListener('click', function () {
      ensureFooterState();
      window.bankaiFooterState.rows.push({
        id: 'row' + (window.bankaiFooterState.rows.length + 1),
        enabled: true, height: 60, bg: '', color: '', border: false, shadow: false,
        cols: [
          { align: 'start', elements: [] },
          { align: 'center', elements: [] },
          { align: 'end', elements: [] }
        ]
      });
      saveFooterState();
      renderFooterInlineBuilder(); enhanceColorCircles();
    });
  }

  function openFooterRowEditor(ri) {
    var dock = document.getElementById('bfb-inline-dock');
    if (!dock) return;
    ensureFooterState();
    var row = window.bankaiFooterState.rows[ri];
    if (!row) return;
    if (!row.cols) row.cols = [];
    var types = (window.bankaiHeaderBuilder && window.bankaiHeaderBuilder.footerElements) || {
      widget: 'ویجت', social: 'سوشال', copyright: 'کپی‌رایت', menu: 'فهرست', html: 'HTML', divider: 'جداکننده', text: 'متن'
    };
    var colN = row.cols.length || 3;
    if (colN < 1) colN = 1;
    if (colN > 4) colN = 4;
    var html = '<div class="bfb-row-editor">';
    html += '<div class="bfb-row-editor__title">ویرایش ردیف ' + (ri + 1) + '</div>';
    html += '<div class="bhb-side-colors">';
    html += '<label class="bhb-color-circle"><span class="bhb-color-circle__lab">پس‌زمینه</span><span class="bhb-color-circle__swatch" style="background:' + (row.bg || '#ffffff') + '"></span><input type="color" data-bfb-row-style="bg" value="' + (row.bg || '#ffffff') + '"></label>';
    html += '<label class="bhb-color-circle"><span class="bhb-color-circle__lab">متن</span><span class="bhb-color-circle__swatch" style="background:' + (row.color || '#1a1a1a') + '"></span><input type="color" data-bfb-row-style="color" value="' + (row.color || '#1a1a1a') + '"></label>';
    html += '</div>';
    html += '<label class="bhb-side-field"><span>ارتفاع</span><input type="number" min="32" max="200" data-bfb-row-style="height" value="' + (row.height || 60) + '"></label>';
    html += '<div class="bhb-side-toggles" style="margin:8px 0">';
    html += '<label class="bhb-check-pill' + (row.border ? ' is-on' : '') + '"><input type="checkbox" data-bfb-row-style="border"' + (row.border ? ' checked' : '') + '><span>بوردر</span></label>';
    html += '<label class="bhb-check-pill' + (row.shadow ? ' is-on' : '') + '"><input type="checkbox" data-bfb-row-style="shadow"' + (row.shadow ? ' checked' : '') + '><span>سایه</span></label>';
    html += '</div>';
    html += '<span class="customize-control-title" style="margin:8px 0 6px">تعداد ستون</span>';
    html += '<div class="bsl-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:10px">';
    for (var cn = 1; cn <= 4; cn++) {
      html += '<button type="button" class="bsl-card' + (colN === cn ? ' is-on' : '') + '" data-bfb-cols="' + cn + '"><span class="bsl-card__label">' + cn + '</span></button>';
    }
    html += '</div>';
    while (row.cols.length < colN) {
      row.cols.push({ align: row.cols.length === 0 ? 'start' : (row.cols.length === colN - 1 ? 'end' : 'center'), elements: [] });
    }
    row.cols = row.cols.slice(0, colN);
    row.cols.forEach(function (col, ci) {
      html += '<div class="bfb-col-box"><div class="bfb-col-box__lab">ستون ' + (ci + 1) + '</div>';
      html += '<div class="bfb-col-els">';
      (col.elements || []).forEach(function (el, ei) {
        html += '<span class="bfb-chip">' + (types[el.type] || el.type) + ' <button type="button" data-bfb-del-el="' + ri + '-' + ci + '-' + ei + '">×</button></span>';
      });
      html += '</div>';
      html += '<select data-bfb-add-el="' + ri + '-' + ci + '" class="bhb-input-text">';
      html += '<option value="">+ افزودن المان</option>';
      Object.keys(types).forEach(function (k) {
        html += '<option value="' + k + '">' + types[k] + '</option>';
      });
      html += '</select></div>';
    });
    html += '<button type="button" class="bhb-open-btn" data-bfb-close-row style="margin-top:8px">بستن</button>';
    html += '</div>';
    dock.innerHTML = html;

    dock.querySelectorAll('[data-bfb-cols]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var n = parseInt(btn.getAttribute('data-bfb-cols'), 10) || 3;
        ensureFooterState();
        var r = window.bankaiFooterState.rows[ri];
        if (!r.cols) r.cols = [];
        while (r.cols.length < n) {
          r.cols.push({ align: 'center', elements: [] });
        }
        r.cols = r.cols.slice(0, n);
        r.colCount = n;
        saveFooterState();
        openFooterRowEditor(ri);
        renderFooterInlineBuilder(); enhanceColorCircles();
      });
    });

    dock.querySelectorAll('[data-bfb-row-style]').forEach(function (el) {
      var apply = function () {
        ensureFooterState();
        var r = window.bankaiFooterState.rows[ri];
        if (!r) return;
        var key = el.getAttribute('data-bfb-row-style');
        if (el.type === 'checkbox') {
          r[key] = el.checked;
          var pill = el.closest('.bhb-check-pill');
          if (pill) pill.classList.toggle('is-on', el.checked);
        } else if (key === 'height') {
          r.height = parseInt(el.value, 10) || 60;
        } else {
          r[key] = el.value;
          var sw = el.parentNode.querySelector('.bhb-color-circle__swatch');
          if (sw) sw.style.background = el.value;
        }
        saveFooterState();
      };
      el.addEventListener('change', apply);
      el.addEventListener('input', apply);
    });
    dock.querySelectorAll('[data-bfb-add-el]').forEach(function (sel) {
      sel.addEventListener('change', function () {
        var parts = sel.getAttribute('data-bfb-add-el').split('-');
        var rri = parseInt(parts[0], 10), cci = parseInt(parts[1], 10);
        var type = sel.value;
        if (!type) return;
        ensureFooterState();
        var col = window.bankaiFooterState.rows[rri].cols[cci];
        if (!col.elements) col.elements = [];
        col.elements.push({ type: type, props: {} });
        sel.value = '';
        saveFooterState();
        openFooterRowEditor(rri);
        renderFooterInlineBuilder(); enhanceColorCircles();
      });
    });
    dock.querySelectorAll('[data-bfb-del-el]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var parts = btn.getAttribute('data-bfb-del-el').split('-');
        var rri = parseInt(parts[0], 10), cci = parseInt(parts[1], 10), ei = parseInt(parts[2], 10);
        ensureFooterState();
        window.bankaiFooterState.rows[rri].cols[cci].elements.splice(ei, 1);
        saveFooterState();
        openFooterRowEditor(rri);
        renderFooterInlineBuilder(); enhanceColorCircles();
      });
    });
    var close = dock.querySelector('[data-bfb-close-row]');
    if (close) close.addEventListener('click', function () { dock.innerHTML = ''; });
  }



  function enhanceColorCircles(root) {
    root = root || document;
    root.querySelectorAll('.bhb-color-circle').forEach(function (lab) {
      if (lab.querySelector('.bhb-color-circle__actions')) return;
      var actions = document.createElement('span');
      actions.className = 'bhb-color-circle__actions';
      actions.innerHTML = '<button type="button" class="bhb-color-none" data-color-special="none">None</button>' +
        '<button type="button" class="bhb-color-none" data-color-special="transparent">شفاف</button>';
      lab.appendChild(actions);
    });
  }

  function bindSidebar() {
    enhanceColorCircles();


  document.querySelectorAll('[data-nav][data-nav-key="style_mode"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var device = btn.getAttribute('data-nav');
      var val = btn.getAttribute('data-value');
      if (!state.nav_desktop) state.nav_desktop = {};
      if (!state.nav_mobile) state.nav_mobile = {};
      if (device === 'desktop') {
        state.nav_desktop.style_mode = val;
        if (val === 'inherit') {
          state.nav_desktop.bg = ''; state.nav_desktop.color = ''; state.nav_desktop.font = ''; state.nav_desktop.font_size = 0; state.nav_desktop.shadow = false;
        }
      } else {
        state.nav_mobile.style_mode = val;
        if (val === 'inherit') {
          state.nav_mobile.bg = ''; state.nav_mobile.color = ''; state.nav_mobile.font = ''; state.nav_mobile.font_size = 0; state.nav_mobile.shadow = false;
        }
      }
      var wrap = btn.closest('.btg-mode-toggle');
      if (wrap) {
        wrap.querySelectorAll('.btg-mode-btn').forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
      }
      var skin = document.querySelector('[data-depends-mode="' + device + '"]');
      if (skin) skin.classList.toggle('is-open', val === 'custom');
      persist();
    });
  });


    // Footer tools (scroll top / contact / bottom nav)
    document.querySelectorAll('[data-ft][data-ft-key]').forEach(function (el) {
      var apply = function () {
        if (typeof footerState === 'undefined' || !footerState) return;
        // footerState may be in outer scope as state when footer mode - use window.bankaiFooterState
        var fs = window.bankaiFooterState;
        if (!fs && typeof state !== 'undefined' && state.rows && state.scroll_top === undefined) {
          // try reading from setting
        }
        if (!window.bankaiFooterState) {
          try {
            var raw = wp.customize('bankai_footer_builder') && wp.customize('bankai_footer_builder').get();
            window.bankaiFooterState = typeof raw === 'string' ? JSON.parse(raw) : (raw || {});
          } catch (e) { window.bankaiFooterState = {}; }
        }
        fs = window.bankaiFooterState;
        var bag = el.getAttribute('data-ft');
        var key = el.getAttribute('data-ft-key');
        if (!fs[bag]) fs[bag] = {};
        if (el.type === 'checkbox') {
          fs[bag][key] = el.checked;
          var pill = el.closest('.bhb-check-pill');
          if (pill) pill.classList.toggle('is-on', el.checked);
        } else if (el.classList.contains('btg-mode-btn') || el.hasAttribute('data-value')) {
          var val = el.getAttribute('data-value');
          if (key === 'enabled') {
            fs[bag][key] = (val === '1' || val === 'true');
            var panel = document.querySelector('[data-ft-panel="' + bag + '"]');
            if (panel) panel.classList.toggle('is-open', !!fs[bag][key]);
          } else {
            fs[bag][key] = val;
          }
          var wrap = el.closest('.btg-mode-toggle') || el.parentNode;
          wrap.querySelectorAll('.btg-mode-btn').forEach(function (b) { b.classList.remove('is-active'); });
          el.classList.add('is-active');
          if (key === 'style_mode') {
            var customSkin = document.querySelector('[data-bn-custom]');
            if (bag === 'bottom_nav' && customSkin) customSkin.classList.toggle('is-open', val === 'custom');
            var ftCustom = document.querySelector('[data-ft-custom="' + bag + '"]');
            if (ftCustom) ftCustom.classList.toggle('is-open', val === 'custom');
            if (val === 'inherit') {
              fs[bag].bg = '';
              fs[bag].color = '';
            }
          }
        } else {
          fs[bag][key] = el.value;
          var sw = el.parentNode && el.parentNode.querySelector && el.parentNode.querySelector('.bhb-color-circle__swatch');
          if (sw && el.type === 'color') sw.style.background = el.value;
        }
        if (wp.customize('bankai_footer_builder')) {
          wp.customize('bankai_footer_builder').set(JSON.stringify(fs));
          if (wp.customize.state && wp.customize.state('saved')) wp.customize.state('saved').set(false);
        }
      };
      el.addEventListener('click', function () {
        if (el.classList.contains('btg-mode-btn') || el.type === 'checkbox') apply();
      });
      el.addEventListener('input', apply);
      el.addEventListener('change', apply);
    });


    // Nav skin desktop/mobile
    document.querySelectorAll('[data-nav][data-nav-key]').forEach(function (inp) {
      var apply = function () {
        var which = inp.getAttribute('data-nav'); // desktop|mobile
        var key = inp.getAttribute('data-nav-key');
        var bag = which === 'mobile' ? (state.nav_mobile = state.nav_mobile || {}) : (state.nav_desktop = state.nav_desktop || {});
        if (inp.type === 'checkbox') {
          bag[key] = inp.checked;
          var pill = inp.closest('.bhb-check-pill');
          if (pill) pill.classList.toggle('is-on', inp.checked);
        } else if (inp.type === 'number') {
          bag[key] = parseInt(inp.value, 10) || 0;
        } else {
          bag[key] = inp.value;
          var sw = inp.parentNode.querySelector('.bhb-color-circle__swatch');
          if (sw) sw.style.background = inp.value;
        }
        save();
      };
      inp.addEventListener('input', apply);
      inp.addEventListener('change', apply);
    });
    document.querySelectorAll('[data-mm-opt]').forEach(function (inp) {
      var apply = function () {
        if (!state.mobile_menu) state.mobile_menu = {};
        var key = inp.getAttribute('data-mm-opt');
        if (inp.type === 'checkbox') {
          state.mobile_menu[key] = inp.checked;
          var pill = inp.closest('.bhb-check-pill');
          if (pill) pill.classList.toggle('is-on', inp.checked);
        } else {
          state.mobile_menu[key] = inp.value;
        }
        save();
      };
      inp.addEventListener('input', apply);
      inp.addEventListener('change', apply);
    });
    var logoPick = document.querySelector('[data-mm-logo-pick]');
    if (logoPick && typeof wp !== 'undefined' && wp.media) {
      logoPick.addEventListener('click', function () {
        var frame = wp.media({ title: 'لوگو', button: { text: 'انتخاب' }, multiple: false });
        frame.on('select', function () {
          var url = frame.state().get('selection').first().toJSON().url || '';
          var input = document.querySelector('[data-mm-opt="logo_url"]');
          if (input) {
            input.value = url;
            if (!state.mobile_menu) state.mobile_menu = {};
            state.mobile_menu.logo_url = url;
            save();
          }
        });
        frame.open();
      });
    }


    document.querySelectorAll('[data-mm-style]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!state.mobile_menu) state.mobile_menu = { icon: 'hamburger', style: 'drawer-right' };
        var st = btn.getAttribute('data-mm-style');
        if (st === 'bottom-sheet') st = 'sheet';
        state.mobile_menu.style = st;
        document.querySelectorAll('[data-mm-style]').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        save();
      });
    });
    document.querySelectorAll('[data-mm-icon]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!state.mobile_menu) state.mobile_menu = { icon: 'hamburger', style: 'drawer-right' };
        state.mobile_menu.icon = btn.getAttribute('data-mm-icon');
        document.querySelectorAll('[data-mm-icon]').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        save();
      });
    });

    // Behavior visual cards
    document.querySelectorAll('[data-bhb-behavior]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('[data-bhb-behavior]').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        state.behavior = btn.getAttribute('data-bhb-behavior');
        state.sticky = state.behavior === 'sticky' || state.behavior === 'sticky-scroll' || state.behavior === 'transparent';
        state.transparent = state.behavior === 'transparent';
        save();
      });
    });
    // Presets
    document.querySelectorAll('[data-bfb-preset]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var p = btn.getAttribute('data-bfb-preset');
        ensureFooterState();
        var fs = window.bankaiFooterState;
        if (!fs.rows) fs.rows = [];
        // reuse header threeRows structure simplified for footer
        if (p === '1row') {
          fs.rows = [
            {id:'main', enabled:true, height:80, bg:'', border:false, shadow:false, cols:[
              {align:'start', elements:[{type:'widget',props:{}}]},
              {align:'center', elements:[{type:'copyright',props:{}}]},
              {align:'end', elements:[{type:'social',props:{}}]}
            ]}
          ];
        } else if (p === '2row') {
          fs.rows = [
            {id:'main', enabled:true, height:100, bg:'', border:false, shadow:false, cols:[
              {align:'start', elements:[{type:'widget',props:{}}]},
              {align:'center', elements:[{type:'widget',props:{}}]},
              {align:'end', elements:[{type:'widget',props:{}}]}
            ]},
            {id:'bottom', enabled:true, height:48, bg:'', border:true, shadow:false, cols:[
              {align:'center', elements:[{type:'copyright',props:{}}]},
              {align:'center', elements:[]},
              {align:'end', elements:[{type:'social',props:{}}]}
            ]}
          ];
        } else {
          fs.rows = [
            {id:'top', enabled:true, height:40, bg:'', border:false, shadow:false, cols:[
              {align:'start', elements:[{type:'social',props:{}}]},
              {align:'center', elements:[]},
              {align:'end', elements:[{type:'menu',props:{}}]}
            ]},
            {id:'main', enabled:true, height:100, bg:'', border:false, shadow:false, cols:[
              {align:'start', elements:[{type:'widget',props:{}}]},
              {align:'center', elements:[{type:'widget',props:{}}]},
              {align:'end', elements:[{type:'widget',props:{}}]}
            ]},
            {id:'bottom', enabled:true, height:48, bg:'', border:true, shadow:false, cols:[
              {align:'center', elements:[{type:'copyright',props:{}}]},
              {align:'center', elements:[]},
              {align:'end', elements:[]}
            ]}
          ];
        }
        document.querySelectorAll('[data-bfb-preset]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
        saveFooterState();
        // open sheet refresh if footer mode
        if (typeof footerState !== 'undefined') footerState = fs;
        if (typeof renderSheetBody === 'function' && sheetMode === 'footer') renderSheetBody();
      });
    });
    document.querySelectorAll('[data-bhb-preset]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('[data-bhb-preset]').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        applyPreset(btn.getAttribute('data-bhb-preset'));
      });
    });
    var openH = document.getElementById('bhb-open-sheet');
    if (openH) openH.addEventListener('click', function () { openSheet('header'); });
    // Footer rows edit inline in sidebar
    renderFooterInlineBuilder(); enhanceColorCircles();
  }

  function applyPreset(id) {
    if (id === '1row') {
      state.rows = [
        { id: 'main', enabled: true, height: 72, colCount: 3, cols: [
          { align: 'start', elements: [makeEl('logo')] },
          { align: 'center', elements: [makeEl('menu')] },
          { align: 'end', elements: [makeEl('search'), makeEl('button')] }
        ]}
      ];
    } else if (id === '2row') {
      state.rows = [
        { id: 'top', enabled: true, height: 36, colCount: 2, cols: [
          { align: 'start', elements: [makeEl('phone')] },
          { align: 'end', elements: [makeEl('social')] }
        ]},
        { id: 'main', enabled: true, height: 72, colCount: 3, cols: [
          { align: 'start', elements: [makeEl('logo')] },
          { align: 'center', elements: [makeEl('menu')] },
          { align: 'end', elements: [makeEl('search'), makeEl('account')] }
        ]}
      ];
    } else {
      state.rows = threeRows();
      state.rows[0].enabled = true;
      state.rows[0].cols[0].elements = [makeEl('text')];
      state.rows[0].cols[2].elements = [makeEl('social')];
      state.rows[2].enabled = true;
      state.rows[2].cols[1].elements = [makeEl('menu')];
    }
    save();
    if (sheet && sheet.classList.contains('is-open')) renderSheetBody();
  }

  function watchSections() {
    if (!wp.customize.section) return;
    ['bankai_header', 'bankai_footer'].forEach(function (id) {
      var sec = wp.customize.section(id);
      if (!sec) return;
      sec.expanded.bind(function (open) {
        if (open) openSheet(id === 'bankai_footer' ? 'footer' : 'header');
        else closeSheet();
      });
    });
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.bhb-pop') && !e.target.closest('.bhb-add') && !e.target.closest('.bhb-chip')) {
      closeMenus();
    }
  });

  wp.customize.bind('ready', function () {
    var base = cfg().config || defaultHeader();
    try {
      var input = document.getElementById('bhb-config-input');
      if (input && input.value) {
        var parsed = JSON.parse(input.value);
        if (parsed && parsed.rows) base = parsed;
      }
    } catch (e) {}
    state = normalize(base);
    footerState = cfg().footerConfig || defaultFooter();
    try {
      var fin = document.getElementById('bfb-config-input');
      if (fin && fin.value) {
        var fp = JSON.parse(fin.value);
        if (fp && fp.rows) footerState = fp;
      }
    } catch (e2) {}
    if (!footerState.mobile) footerState.mobile = defaultFooter().mobile;

    bindSidebar();
    renderFooterInlineBuilder(); enhanceColorCircles();
    watchSections();
  });
})(jQuery);
