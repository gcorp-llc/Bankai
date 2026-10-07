(function () {
  'use strict';

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }
  function $all(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function remFromRange(v) {
    var n = parseInt(v, 10);
    if (n === 0) return '0';
    var rem = n / 16;
    return (Number.isInteger(rem) ? rem : rem.toFixed(2).replace(/0+$/, '').replace(/\.$/, '')) + 'rem';
  }

  function rangeFromRem(rem) {
    if (!rem || rem === '0') return 0;
    if (String(rem).indexOf('px') !== -1) return parseInt(rem, 10) || 0;
    return Math.round(parseFloat(rem) * 16);
  }

  function setMod(id, value) {
    try {
      if (typeof wp === 'undefined' || !wp.customize) return false;
      var setting = wp.customize(id);
      if (!setting) {
        // Create ephemeral binding if missing (should be registered in PHP)
        return false;
      }
      setting.set(value);
      // Force Customizer "Publish" to enable
      if (typeof setting._dirty !== 'undefined') {
        setting._dirty = true;
      }
      if (wp.customize.state && wp.customize.state('saved')) {
        wp.customize.state('saved').set(false);
      }
      return true;
    } catch (e) { /* ignore */ }
    return false;
  }

  function pushLiveToPreview(design) {
    try {
      if (typeof wp !== 'undefined' && wp.customize && wp.customize.previewer) {
        wp.customize.previewer.send('bankai-design-live', design);
      }
    } catch (e) { /* ignore */ }
  }

  function bindGenerator(root) {
    if (!root || root.getAttribute('data-btg-bound')) return;
    root.setAttribute('data-btg-bound', '1');

    var cfg = window.bankaiThemeGen || {};
    var design = JSON.parse(JSON.stringify(cfg.design || {}));
    if (!design.colors) design.colors = {};
    if (!design.style) design.style = 'premium-minimalism';
    if (!design.radius) design.radius = {};
    var presets = cfg.presets || {};
    var fonts = cfg.fonts || {};
    var demo = $('.btg-demo', root);

    function syncDesignSetting() {
      setMod('bankai_design_json', JSON.stringify(design));
      pushLiveToPreview(design);
    }

    function applyDemo() {
      if (!demo || !design.colors) return;
      var c = design.colors;
      var r = design.radius || {};
      var map = {
        '--p': c.primary, '--s': c.secondary, '--a': c.accent, '--n': c.neutral,
        '--b1': c.base, '--bc': c.base_content, '--in': c.info, '--su': c.success,
        '--wa': c.warning, '--er': c.error,
        '--rbox': r.box || '1rem', '--rfield': r.field || '.5rem'
      };
      Object.keys(map).forEach(function (k) {
        if (map[k]) demo.style.setProperty(k, map[k]);
      });
      if (c.base) demo.style.background = c.base;
      if (c.base_content) demo.style.color = c.base_content;
      syncDesignSetting();
    }

    function syncColorInputs() {
      $all('[data-color]', root).forEach(function (el) {
        var key = el.getAttribute('data-color');
        if (design.colors[key]) el.value = design.colors[key];
      });
      $all('[data-color-text]', root).forEach(function (el) {
        var key = el.getAttribute('data-color-text');
        if (design.colors[key]) el.value = design.colors[key];
      });
      $all('[data-radius]', root).forEach(function (el) {
        var key = el.getAttribute('data-radius');
        if (design.radius[key] !== undefined) {
          el.value = rangeFromRem(design.radius[key]);
          var lab = el.parentNode.querySelector('.btg-radius-val');
          if (lab) lab.textContent = design.radius[key];
        }
      });
      $all('.btg-preset', root).forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-preset') === design.preset);
      });
      applyDemo();
    }

    /* Tabs */
    $all('.btg-tab-btn', root).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-btg-tab');
        $all('.btg-tab-btn', root).forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        $all('.btg-tab-panel', root).forEach(function (p) {
          p.classList.toggle('is-active', p.getAttribute('data-btg-panel') === id);
        });
      });
    });

    /* Presets */
    $all('.btg-preset', root).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-preset');
        var p = presets[id];
        if (!p) return;
        design = {
          preset: id,
          style: design.style || 'premium-minimalism',
          scheme: p.scheme || 'light',
          colors: Object.assign({}, p.colors),
          radius: Object.assign({}, p.radius)
        };
        syncColorInputs();
      });
    });

    $all('[data-color]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        var key = el.getAttribute('data-color');
        design.colors[key] = el.value;
        design.preset = 'custom';
        var text = root.querySelector('[data-color-text="' + key + '"]');
        if (text) text.value = el.value;
        $all('.btg-preset', root).forEach(function (b) { b.classList.remove('is-active'); });
        applyDemo();
      });
    });

    $all('[data-color-text]', root).forEach(function (el) {
      el.addEventListener('change', function () {
        var key = el.getAttribute('data-color-text');
        var val = el.value;
        if (!/^#[0-9A-Fa-f]{6}$/.test(val)) return;
        design.colors[key] = val;
        design.preset = 'custom';
        var color = root.querySelector('[data-color="' + key + '"]');
        if (color) color.value = val;
        applyDemo();
      });
    });

    $all('[data-radius]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        var key = el.getAttribute('data-radius');
        design.radius[key] = remFromRange(el.value);
        design.preset = 'custom';
        var lab = el.parentNode.querySelector('.btg-radius-val');
        if (lab) lab.textContent = design.radius[key];
        applyDemo();
      });
    });

    /* Page layout cards */
    $all('.btg-layout-grid', root).forEach(function (grid) {
      var setting = grid.getAttribute('data-setting');
      $all('.btg-layout-card', grid).forEach(function (card) {
        card.addEventListener('click', function () {
          $all('.btg-layout-card', grid).forEach(function (c) { c.classList.remove('is-active'); });
          card.classList.add('is-active');
          setMod(setting, card.getAttribute('data-value'));
        });
      });
    });

    /* Direction */
    var dirWrap = $('.btg-dir-toggle', root);
    if (dirWrap) {
      $all('.btg-dir-btn', dirWrap).forEach(function (btn) {
        btn.addEventListener('click', function () {
          $all('.btg-dir-btn', dirWrap).forEach(function (b) { b.classList.remove('is-active'); });
          btn.classList.add('is-active');
          setMod('text_direction', btn.getAttribute('data-value'));
        });
      });
    }

    /* Font presets */
    $all('.btg-font-card', root).forEach(function (card) {
      card.addEventListener('click', function () {
        $all('.btg-font-card', root).forEach(function (c) { c.classList.remove('is-active'); });
        card.classList.add('is-active');
        var id = card.getAttribute('data-value');
        var stack = card.getAttribute('data-stack') || '';
        setMod('font_preset', id);
        setMod('font_body', stack);
        setMod('font_heading', stack);
      });
    });

    $all('[data-mod]', root).forEach(function (el) {
      var id = el.getAttribute('data-mod');
      function push() {
        var val = el.value;
        if (el.type === 'number') val = parseInt(val, 10) || 0;
        setMod(id, val);
      }
      el.addEventListener('change', push);
      el.addEventListener('input', push);
    });


    /* Font upload */
    var uploadBtn = $('#btg-font-upload', root);
    var fileInput = $('#btg-font-file', root);
    var labelInput = $('#btg-font-label', root);
    var fontStatus = $('#btg-font-status', root);
    var fontGrid = $('#btg-font-grid', root);

    if (uploadBtn && fileInput) {
      uploadBtn.addEventListener('click', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file) {
          if (fontStatus) fontStatus.textContent = 'فایل فونت را انتخاب کنید';
          return;
        }
        uploadBtn.disabled = true;
        if (fontStatus) fontStatus.textContent = (cfg.i18n && cfg.i18n.uploading) || '…';
        var fd = new FormData();
        fd.append('action', 'bankai_theme_upload_font');
        fd.append('nonce', cfg.nonce);
        fd.append('font_file', file);
        fd.append('font_label', labelInput ? labelInput.value : '');
        fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (j) {
            uploadBtn.disabled = false;
            if (j && j.success && j.data && j.data.font) {
              if (fontStatus) fontStatus.textContent = (cfg.i18n && cfg.i18n.upload_ok) || 'OK';
              var f = j.data.font;
              if (fontGrid) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btg-font-card';
                btn.setAttribute('data-value', f.id);
                btn.setAttribute('data-stack', f.stack);
                btn.innerHTML = '<span class="btg-font-aa" style="font-family:' + f.stack + '">Aa</span><span class="btg-font-name"></span>';
                btn.querySelector('.btg-font-name').textContent = f.label;
                fontGrid.appendChild(btn);
                btn.addEventListener('click', function () {
                  $all('.btg-font-card', root).forEach(function (c) { c.classList.remove('is-active'); });
                  btn.classList.add('is-active');
                  setMod('font_preset', f.id);
                  setMod('font_body', f.stack);
                  setMod('font_heading', f.stack);
                });
              }
              if (j.data.fonts) {
                setMod('bankai_custom_fonts', j.data.fonts);
              }
              fileInput.value = '';
              if (labelInput) labelInput.value = '';
            } else {
              if (fontStatus) fontStatus.textContent = (j && j.data && j.data.message) || (cfg.i18n && cfg.i18n.upload_fail) || 'Error';
            }
          })
          .catch(function () {
            uploadBtn.disabled = false;
            if (fontStatus) fontStatus.textContent = (cfg.i18n && cfg.i18n.upload_fail) || 'Error';
          });
      });
    }


    /* Delete custom font */
    root.addEventListener('click', function (ev) {
      var del = ev.target.closest('[data-del-font]');
      if (!del) return;
      var id = del.getAttribute('data-del-font');
      var fd = new FormData();
      fd.append('action', 'bankai_theme_delete_font');
      fd.append('nonce', cfg.nonce);
      fd.append('font_id', id);
      fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (j && j.success) {
            var wrap = del.closest('.btg-font-wrap');
            if (wrap) wrap.remove();
            if (j.data && j.data.fonts) setMod('bankai_custom_fonts', j.data.fonts);
          }
        });
    });


    
    
    /* Design style cards */
    $all('.btg-style-card', root).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var sid = btn.getAttribute('data-style');
        if (!sid) return;
        design.style = sid;
        $all('.btg-style-card', root).forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        // Apply style radius hints if available from cfg
        var styles = cfg.styles || {};
        syncDesignSetting();
        applyDemo();
      });
    });

    /* Font color mode inherit / custom */
    $all('.btg-mode-toggle', root).forEach(function (wrap) {
      var setting = wrap.getAttribute('data-setting') || 'font_color_mode';
      $all('.btg-mode-btn', wrap).forEach(function (btn) {
        btn.addEventListener('click', function () {
          $all('.btg-mode-btn', wrap).forEach(function (b) { b.classList.remove('is-active'); });
          btn.classList.add('is-active');
          var val = btn.getAttribute('data-value');
          setMod(setting, val);
          var cards = $('.btg-typo-cards', root);
          if (cards) cards.setAttribute('data-color-mode', val);
          pushLiveToPreview(design);
        });
      });
    });

    /* Color circle swatch live */
    $all('.btg-color-circle input[type="color"]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        var sw = el.parentNode.querySelector('.btg-color-swatch');
        if (sw) sw.style.background = el.value;
        var id = el.getAttribute('data-mod');
        if (id) setMod(id, el.value);
      });
    });
    $all('.btg-font-select', root).forEach(function (el) {
      el.addEventListener('change', function () {
        var id = el.getAttribute('data-mod');
        if (id) setMod(id, el.value);
      });
    });

    syncColorInputs();
  }

  function initAll() {
    $all('.btg').forEach(bindGenerator);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  if (typeof wp !== 'undefined' && wp.customize) {
    wp.customize.bind('ready', function () {
      setTimeout(initAll, 250);
    });
  }
})();
