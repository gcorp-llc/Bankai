(function () {
  'use strict';

  var PRESETS = ['#000000', '#1a0f38', '#f5e6ff', '#ffffff', '#f8fafc', '#334155', '#0f172a', '#db2777', '#ec4899', '#7c5cff', '#6366f1', '#22c55e', '#ef4444', '#f59e0b'];

  function hexToRgb(hex) {
    hex = String(hex || '').replace('#', '');
    if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    if (hex.length !== 6) return { r: 124, g: 92, b: 255 };
    return {
      r: parseInt(hex.slice(0, 2), 16) || 0,
      g: parseInt(hex.slice(2, 4), 16) || 0,
      b: parseInt(hex.slice(4, 6), 16) || 0
    };
  }
  function rgbToHex(r, g, b) {
    return '#' + [r, g, b].map(function (v) {
      var s = Math.max(0, Math.min(255, Math.round(v))).toString(16);
      return s.length === 1 ? '0' + s : s;
    }).join('');
  }
  function rgbToHsv(r, g, b) {
    r /= 255; g /= 255; b /= 255;
    var max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min;
    var h = 0, s = max === 0 ? 0 : d / max, v = max;
    if (d) {
      if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
      else if (max === g) h = ((b - r) / d + 2) / 6;
      else h = ((r - g) / d + 4) / 6;
    }
    return { h: h * 360, s: s, v: v };
  }
  function hsvToRgb(h, s, v) {
    h = ((h % 360) + 360) % 360 / 60;
    var i = Math.floor(h), f = h - i;
    var p = v * (1 - s), q = v * (1 - f * s), t = v * (1 - (1 - f) * s);
    var r, g, b;
    switch (i % 6) {
      case 0: r = v; g = t; b = p; break;
      case 1: r = q; g = v; b = p; break;
      case 2: r = p; g = v; b = t; break;
      case 3: r = p; g = q; b = v; break;
      case 4: r = t; g = p; b = v; break;
      default: r = v; g = p; b = q;
    }
    return { r: r * 255, g: g * 255, b: b * 255 };
  }

  var active = null;

  function ensurePicker() {
    var el = document.getElementById('bk-cpicker');
    if (el) return el;
    el = document.createElement('div');
    el.id = 'bk-cpicker';
    el.className = 'bk-cpicker';
    el.setAttribute('hidden', 'hidden');
    el.innerHTML =
      '<div class="bk-cpicker__presets"></div>' +
      '<div class="bk-cpicker__sat" dir="ltr"><div class="bk-cpicker__sat-white"></div><div class="bk-cpicker__sat-black"></div><div class="bk-cpicker__sat-thumb"></div></div>' +
      '<div class="bk-cpicker__hue" dir="ltr"><div class="bk-cpicker__slider-thumb" data-thumb="hue"></div></div>' +
      '<div class="bk-cpicker__alpha" dir="ltr"><div class="bk-cpicker__alpha-grad"></div><div class="bk-cpicker__slider-thumb" data-thumb="alpha"></div></div>' +
      '<div class="bk-cpicker__row">' +
        '<div class="bk-cpicker__hex"><span class="bk-cpicker__hex-hash">#</span><input type="text" maxlength="8" spellcheck="false"></div>' +
        '<select class="bk-cpicker__fmt"><option>Hex</option></select>' +
      '</div>' +
      '<button type="button" class="bk-cpicker__clear">پاک کردن</button>';
    document.body.appendChild(el);
    var presets = el.querySelector('.bk-cpicker__presets');
    PRESETS.forEach(function (c) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'bk-cpicker__preset';
      b.style.background = c;
      b.setAttribute('data-c', c);
      presets.appendChild(b);
    });
    bindPicker(el);
    return el;
  }

  function applyColor() {
    if (!active) return;
    var rgb = hsvToRgb(active.hsv.h, active.hsv.s, active.hsv.v);
    var hex = rgbToHex(rgb.r, rgb.g, rgb.b);
    if (active.input) {
      try { active.input.value = hex; } catch (e) {}
      // Trigger both input and change so theme-generator / header-builder save
      ['input', 'change'].forEach(function (ev) {
        try {
          active.input.dispatchEvent(new Event(ev, { bubbles: true }));
        } catch (err) {}
      });
    }
    if (active.fill) {
      active.fill.style.background = hex;
    }
    // Also update sibling swatch spans (btg-color-swatch)
    if (active.wrap) {
      var sw = active.wrap.querySelector('.btg-color-swatch, .bhb-color-circle__swatch, .bk-swatch__fill');
      if (sw) sw.style.background = hex;
      active.wrap.classList.remove('is-empty');
    }
    var hexInp = active.el.querySelector('.bk-cpicker__hex input');
    if (hexInp && document.activeElement !== hexInp) hexInp.value = hex.replace('#', '');
  }

  function paintPicker() {
    if (!active) return;
    var el = active.el;
    var hsv = active.hsv;
    var pure = hsvToRgb(hsv.h, 1, 1);
    var sat = el.querySelector('.bk-cpicker__sat');
    sat.style.background = 'rgb(' + Math.round(pure.r) + ',' + Math.round(pure.g) + ',' + Math.round(pure.b) + ')';
    var thumb = el.querySelector('.bk-cpicker__sat-thumb');
    thumb.style.left = (hsv.s * 100) + '%';
    thumb.style.top = ((1 - hsv.v) * 100) + '%';
    el.querySelector('[data-thumb="hue"]').style.left = (hsv.h / 360 * 100) + '%';
    el.querySelector('[data-thumb="alpha"]').style.left = (active.alpha * 100) + '%';
    var rgb = hsvToRgb(hsv.h, hsv.s, hsv.v);
    var hex = rgbToHex(rgb.r, rgb.g, rgb.b);
    el.querySelector('.bk-cpicker__alpha-grad').style.background =
      'linear-gradient(to right, transparent, ' + hex + ')';
  }

  function bindPicker(el) {
    var sat = el.querySelector('.bk-cpicker__sat');
    var hue = el.querySelector('.bk-cpicker__hue');
    var alpha = el.querySelector('.bk-cpicker__alpha');
    var hexInp = el.querySelector('.bk-cpicker__hex input');

    function drag(area, cb) {
      function move(e) {
        var r = area.getBoundingClientRect();
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        var x = Math.max(0, Math.min(1, (clientX - r.left) / r.width));
        var y = Math.max(0, Math.min(1, (clientY - r.top) / r.height));
        cb(x, y);
        paintPicker();
        applyColor();
      }
      function start(e) {
        e.preventDefault();
        move(e);
        function up() {
          document.removeEventListener('mousemove', move);
          document.removeEventListener('mouseup', up);
          document.removeEventListener('touchmove', move);
          document.removeEventListener('touchend', up);
        }
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', up);
        document.addEventListener('touchmove', move, { passive: false });
        document.addEventListener('touchend', up);
      }
      area.addEventListener('mousedown', start);
      area.addEventListener('touchstart', start, { passive: false });
    }
    drag(sat, function (x, y) {
      if (!active) return;
      active.hsv.s = x;
      active.hsv.v = 1 - y;
    });
    drag(hue, function (x) {
      if (!active) return;
      active.hsv.h = x * 360;
    });
    drag(alpha, function (x) {
      if (!active) return;
      active.alpha = x;
    });

    el.querySelector('.bk-cpicker__presets').addEventListener('click', function (e) {
      var b = e.target.closest('[data-c]');
      if (!b || !active) return;
      var rgb = hexToRgb(b.getAttribute('data-c'));
      active.hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
      active.alpha = 1;
      paintPicker();
      applyColor();
    });

    hexInp.addEventListener('change', function () {
      if (!active) return;
      var v = hexInp.value.replace('#', '');
      if (!/^[0-9a-fA-F]{6}$/.test(v)) return;
      var rgb = hexToRgb(v);
      active.hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
      paintPicker();
      applyColor();
    });

    el.querySelector('.bk-cpicker__clear').addEventListener('click', function () {
      if (!active) return;
      if (active.input) {
        var def = active.input.getAttribute('data-default') || '';
        active.input.value = def || '#000000';
        ['input', 'change'].forEach(function (ev) {
          active.input.dispatchEvent(new Event(ev, { bubbles: true }));
        });
      }
      if (active.fill) active.fill.style.background = 'transparent';
      if (active.wrap) active.wrap.classList.add('is-empty');
      closePicker();
    });

    document.addEventListener('mousedown', function (e) {
      if (!active) return;
      if (el.contains(e.target)) return;
      if (e.target.closest('.bk-swatch, .btg-color-circle, .bhb-color-circle, .btg-color-swatch')) return;
      closePicker();
    });
  }

  function findColorInput(node) {
    if (!node) return null;
    if (node.matches && node.matches('input[type="color"]')) return node;
    return node.querySelector && (
      node.querySelector('input[type="color"]') ||
      node.querySelector('input[data-color]') ||
      node.querySelector('input[data-mod]') ||
      node.querySelector('input[data-nav-key]')
    );
  }

  function findFill(node, input) {
    if (!node) return null;
    return node.querySelector('.bk-swatch__fill, .btg-color-swatch, .bhb-color-circle__swatch') ||
      (input && input.parentNode && input.parentNode.querySelector('.bk-swatch__fill, .btg-color-swatch'));
  }

  function openPicker(wrap, input, fill) {
    var el = ensurePicker();
    var val = (input && input.value) || '#7c5cff';
    if (!val || val === 'none' || val === 'transparent') val = '#7c5cff';
    var rgb = hexToRgb(val);
    active = {
      input: input,
      fill: fill,
      wrap: wrap,
      hsv: rgbToHsv(rgb.r, rgb.g, rgb.b),
      alpha: 1,
      el: el
    };
    paintPicker();
    el.removeAttribute('hidden');
    el.style.display = 'block';
    el.style.position = 'fixed';
    el.style.zIndex = '1000001';
    var rect = (wrap || input).getBoundingClientRect();
    var top = rect.bottom + 8;
    var left = Math.min(window.innerWidth - 280, Math.max(8, rect.left - 100));
    if (top + 360 > window.innerHeight) top = Math.max(8, rect.top - 360);
    el.style.top = top + 'px';
    el.style.left = left + 'px';
    if (wrap) wrap.classList.remove('is-empty');
  }

  function closePicker() {
    var el = document.getElementById('bk-cpicker');
    if (el) {
      el.setAttribute('hidden', 'hidden');
      el.style.display = 'none';
    }
    active = null;
  }

  function onColorTarget(e) {
    var t = e.target;
    // Prefer structured swatch / circle
    var wrap = t.closest('.bk-swatch, .btg-color-circle, .bhb-color-circle');
    if (!wrap && t.classList && t.classList.contains('btg-color-swatch')) {
      wrap = t.closest('label') || t.parentNode;
    }
    // Also: design tab row with input[type=color][data-color]
    if (!wrap) {
      var row = t.closest('.btg-color-row');
      if (row) wrap = row;
    }
    if (!wrap) return;
    // Don't open when clicking text fields
    if (t.matches && t.matches('input[type="text"], input[type="number"], select, textarea')) return;

    e.preventDefault();
    e.stopPropagation();
    var input = findColorInput(wrap);
    if (!input) return;
    var fill = findFill(wrap, input);
    openPicker(wrap, input, fill);
  }

  // Capture phase so we beat native color picker
  document.addEventListener('click', onColorTarget, true);
  document.addEventListener('mousedown', function (e) {
    var wrap = e.target.closest('.bk-swatch, .btg-color-circle, .bhb-color-circle');
    if (!wrap) return;
    // Block native color dialog
    if (e.target.matches && e.target.matches('input[type="color"]')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  // Spacing link toggle
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.bk-space__link');
    if (!btn) return;
    btn.classList.toggle('is-linked');
    var linked = btn.classList.contains('is-linked');
    btn.textContent = linked ? '⛓' : '☍';
    var box = btn.closest('.bk-space');
    if (!box) return;
    box.setAttribute('data-linked', linked ? '1' : '0');
  });

  document.addEventListener('input', function (e) {
    var cell = e.target.closest('.bk-space__cell input');
    if (!cell) return;
    var box = cell.closest('.bk-space');
    if (!box || box.getAttribute('data-linked') !== '1') return;
    var val = cell.value;
    box.querySelectorAll('.bk-space__cell input').forEach(function (inp) {
      if (inp !== cell) {
        inp.value = val;
        inp.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });

  // Reset buttons
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.bk-reset');
    if (!btn) return;
    var row = btn.closest('.bk-crow');
    if (!row) return;
    row.querySelectorAll('.bk-swatch input, input[type="color"]').forEach(function (inp) {
      var def = inp.getAttribute('data-default') || '';
      inp.value = def || '#000000';
      inp.dispatchEvent(new Event('input', { bubbles: true }));
      inp.dispatchEvent(new Event('change', { bubbles: true }));
      var fill = inp.parentNode.querySelector('.bk-swatch__fill, .btg-color-swatch, .bhb-color-circle__swatch');
      if (fill) fill.style.background = def || 'transparent';
    });
  });

  window.BankaiAstraUI = { openPicker: openPicker, closePicker: closePicker };
})();
