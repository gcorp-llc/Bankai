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
      if (!setting) return false;
      setting.set(value);
      try {
        if (typeof setting._dirty !== 'undefined') setting._dirty = true;
        if (wp.customize.state && wp.customize.state('saved')) {
          wp.customize.state('saved').set(false);
        }
      } catch (e2) {}
      return true;
    } catch (e) { return false; }
  }

  function pushLiveToPreview(design) {
    try {
      if (typeof wp !== 'undefined' && wp.customize && wp.customize.previewer) {
        wp.customize.previewer.send('bankai-design-live', design);
      }
    } catch (e) { /* ignore */ }
  }

  function pushLayoutLive(payload) {
    try {
      if (typeof wp !== 'undefined' && wp.customize && wp.customize.previewer) {
        wp.customize.previewer.send('bankai-layout-live', payload);
      }
    } catch (e) {}
  }

  function pushDirLive(dir) {
    try {
      if (typeof wp !== 'undefined' && wp.customize && wp.customize.previewer) {
        wp.customize.previewer.send('bankai-dir-live', dir);
      }
    } catch (e) {}
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
      // Always push design to Customizer + live preview (demo box is optional)
      if (design && design.colors) {
        if (demo) {
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
        }
        syncDesignSetting();
      }
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
          colors: Object.assign({}, p.colors || {}),
          radius: Object.assign({}, p.radius || design.radius || {})
        };
        syncColorInputs();
        syncDesignSetting();
        pushLiveToPreview(design);
      });
    });

    $all('[data-color]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        if (!design.colors) design.colors = {};
        var key = el.getAttribute('data-color');
        design.colors[key] = el.value;
        design.preset = 'custom';
        var textEl = root.querySelector('[data-color-text="' + key + '"]');
        if (textEl) textEl.value = el.value;
        $all('.btg-preset', root).forEach(function (b) { b.classList.remove('is-active'); });
        applyDemo();
        syncDesignSetting();
        pushLiveToPreview(design);
      });
    });

    $all('[data-color-text]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        var key = el.getAttribute('data-color-text');
        var val = el.value;
        if (!/^#[0-9A-Fa-f]{6}$/.test(val)) return;
        if (!design.colors) design.colors = {};
        design.colors[key] = val;
        design.preset = 'custom';
        var color = root.querySelector('[data-color="' + key + '"]');
        if (color) color.value = val;
        applyDemo();
        syncDesignSetting();
        pushLiveToPreview(design);
      });
      el.addEventListener('change', function () {
        var key = el.getAttribute('data-color-text');
        var val = el.value;
        if (!/^#[0-9A-Fa-f]{6}$/.test(val)) return;
        if (!design.colors) design.colors = {};
        design.colors[key] = val;
        design.preset = 'custom';
        var color = root.querySelector('[data-color="' + key + '"]');
        if (color) color.value = val;
        applyDemo();
        syncDesignSetting();
        pushLiveToPreview(design);
      });
    });

    $all('[data-radius]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        if (!design.radius) design.radius = {};
        var key = el.getAttribute('data-radius');
        design.radius[key] = remFromRange(el.value);
        design.preset = 'custom';
        var lab = el.parentNode.querySelector('.btg-radius-val');
        if (lab) lab.textContent = design.radius[key];
        applyDemo();
        syncDesignSetting();
        pushLiveToPreview(design);
      });
    });

    /* Page layout cards */
    $all('.btg-layout-grid', root).forEach(function (grid) {
      var setting = grid.getAttribute('data-setting');
      $all('.btg-layout-card', grid).forEach(function (card) {
        card.addEventListener('click', function () {
          $all('.btg-layout-card', grid).forEach(function (c) { c.classList.remove('is-active'); });
          card.classList.add('is-active');
          var val = card.getAttribute('data-value');
          setMod(setting, val);
          var payload = {};
          payload[setting] = val;
          pushLayoutLive(payload);
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
          var dir = btn.getAttribute('data-value');
          setMod('text_direction', dir);
          pushDirLive(dir);
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
        // Apply style radius hints when available
        var styles = cfg.styles || {};
        var meta = styles[sid] || {};
        if (meta.radius && typeof meta.radius === 'object') {
          design.radius = Object.assign({}, design.radius || {}, meta.radius);
          $all('[data-radius]', root).forEach(function (el) {
            var key = el.getAttribute('data-radius');
            if (design.radius[key] !== undefined) {
              el.value = rangeFromRem(design.radius[key]);
              var lab = el.parentNode.querySelector('.btg-radius-val');
              if (lab) lab.textContent = design.radius[key];
            }
          });
        }
        syncDesignSetting();
        applyDemo();
        pushLiveToPreview(design);
      });
    });

    /* Font color mode inherit / custom + live demo */
    function updateTypoLive(card) {
      if (!card) return;
      var sample = card.querySelector('.btg-typo-live__sample');
      if (!sample) return;
      var sel = card.querySelector('.btg-font-select');
      var sizeInp = card.querySelector('.btg-size-input, input[type="number"][data-mod]');
      var colorInp = card.querySelector('.btg-astra-color input[type="color"], .btg-color-circle input[type="color"]');
      if (sel && sel.value) sample.style.fontFamily = sel.value;
      if (sizeInp && sizeInp.value) sample.style.fontSize = parseInt(sizeInp.value, 10) + 'px';
      if (colorInp && colorInp.value) sample.style.color = colorInp.value;
    }

    $all('.btg-mode-toggle', root).forEach(function (wrap) {
      var setting = wrap.getAttribute('data-setting') || 'font_color_mode';
      $all('.btg-mode-btn', wrap).forEach(function (btn) {
        btn.addEventListener('click', function () {
          $all('.btg-mode-btn', wrap).forEach(function (b) { b.classList.remove('is-active'); });
          btn.classList.add('is-active');
          var val = btn.getAttribute('data-value') || btn.getAttribute('data-font-mode') || '';
          setMod(setting, val);
          var cards = $('.btg-typo-cards', root) || document.getElementById('btg-typo-cards');
          if (cards) {
            cards.setAttribute('data-color-mode', val);
            cards.style.display = val === 'custom' ? '' : 'none';
          }
          if (val === 'custom') {
            $all('.btg-typo-card', root).forEach(updateTypoLive);
          }
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
        updateTypoLive(el.closest('.btg-typo-card'));
      });
    });
    $all('.btg-font-select', root).forEach(function (el) {
      el.addEventListener('change', function () {
        var id = el.getAttribute('data-mod');
        if (id) setMod(id, el.value);
        updateTypoLive(el.closest('.btg-typo-card'));
      });
    });
    $all('.btg-size-input, input[type="number"][data-mod]', root).forEach(function (el) {
      el.addEventListener('input', function () {
        var id = el.getAttribute('data-mod');
        if (id) setMod(id, el.value);
        updateTypoLive(el.closest('.btg-typo-card'));
      });
    });

    /* Init custom cards visibility */
    var initCards = $('.btg-typo-cards', root) || document.getElementById('btg-typo-cards');
    if (initCards) {
      var mode = initCards.getAttribute('data-color-mode') || 'inherit';
      initCards.style.display = mode === 'custom' ? '' : 'none';
      if (mode === 'custom') $all('.btg-typo-card', root).forEach(updateTypoLive);
    }

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


/* Font custom live demo */
(function () {
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function updateFontLiveDemo() {
    var demo = $('#btg-font-live-demo');
    if (!demo) return;
    var bodyStack = '';
    var bodySize = 16;
    var headStack = '';
    var headSize = 28;
    var bodySel = document.querySelector('[data-setting="font_body"], select[name="font_body"], [data-font-role="body"] select');
    var sizeInp = document.querySelector('[data-mod="font_body_size"], input[data-setting="font_body_size"]');
    var headSel = document.querySelector('[data-setting="font_heading"], [data-font-role="h1"] select, [data-font-role="heading"] select');
    var headSizeInp = document.querySelector('[data-mod="font_h1_size"], input[data-setting="font_h1_size"]');
    // try active font card
    var activeCard = document.querySelector('.btg-font-card.is-active');
    if (activeCard) bodyStack = activeCard.getAttribute('data-stack') || '';
    if (bodySel && bodySel.value) bodyStack = bodySel.value;
    if (sizeInp && sizeInp.value) bodySize = parseInt(sizeInp.value, 10) || 16;
    if (headSel && headSel.value) headStack = headSel.value;
    if (headSizeInp && headSizeInp.value) headSize = parseInt(headSizeInp.value, 10) || 28;
    if (!headStack) headStack = bodyStack;
    var sample = $('#btg-font-live-sample');
    var heading = $('#btg-font-live-heading');
    if (sample) {
      sample.style.fontFamily = bodyStack || 'inherit';
      sample.style.fontSize = bodySize + 'px';
    }
    if (heading) {
      heading.style.fontFamily = headStack || bodyStack || 'inherit';
      heading.style.fontSize = headSize + 'px';
    }
  }
  document.addEventListener('click', function (e) {
    var modeBtn = e.target.closest('[data-font-mode], .btg-mode-btn, .btg-font-mode-btn');
    if (modeBtn) {
      var mode = modeBtn.getAttribute('data-font-mode') || modeBtn.getAttribute('data-value') || modeBtn.getAttribute('data-mode') || '';
      var isCustom = mode === 'custom' || modeBtn.classList.contains('is-custom') || (modeBtn.textContent || '').indexOf('سفارشی') !== -1;
      var cards = document.getElementById('btg-typo-cards');
      if (cards) {
        cards.style.display = isCustom ? '' : 'none';
        cards.setAttribute('data-color-mode', isCustom ? 'custom' : 'inherit');
      }
      setTimeout(updateFontLiveDemo, 50);
    }
    if (e.target.closest('.btg-font-card')) setTimeout(updateFontLiveDemo, 30);
  });
  document.addEventListener('input', function (e) {
    if (e.target.matches('input[type="range"], select, input[type="number"]')) updateFontLiveDemo();
  });
  document.addEventListener('change', function (e) {
    if (e.target.matches('select, input')) updateFontLiveDemo();
  });

  /* Global tab switcher for طرح کلی (robust in Customizer) */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btg-tab-btn[data-btg-tab]');
    if (!btn) return;
    var root = btn.closest('.btg');
    if (!root) return;
    var id = btn.getAttribute('data-btg-tab');
    root.querySelectorAll('.btg-tab-btn').forEach(function (b) {
      b.classList.toggle('is-active', b === btn);
    });
    root.querySelectorAll('.btg-tab-panel').forEach(function (p) {
      var on = p.getAttribute('data-btg-panel') === id;
      p.classList.toggle('is-active', on);
      p.style.display = on ? 'block' : 'none';
    });
  });
})();


/* Keep color swatches in sync with inputs */
(function () {
  document.addEventListener('input', function (e) {
    var inp = e.target;
    if (!inp || inp.type !== 'color') return;
    var wrap = inp.closest('.btg-color-circle, .bhb-color-circle, .bk-swatch, .btg-color-row');
    if (!wrap) return;
    var fill = wrap.querySelector('.btg-color-swatch, .bhb-color-circle__swatch, .bk-swatch__fill');
    if (fill) fill.style.background = inp.value;
    var text = wrap.parentNode && wrap.parentNode.querySelector('[data-color-text="' + (inp.getAttribute('data-color') || '') + '"]');
    if (text) text.value = inp.value;
  }, true);
})();


/* Global: color/size from Astra picker → Customizer + live card demo */
(function () {
  'use strict';
  function setModSafe(id, value) {
    try {
      if (typeof wp === 'undefined' || !wp.customize || !wp.customize(id)) return false;
      wp.customize(id).set(value);
      if (wp.customize.state && wp.customize.state('saved')) wp.customize.state('saved').set(false);
      return true;
    } catch (e) { return false; }
  }
  function updateTypoLive(inp) {
    var card = inp.closest('.btg-typo-card');
    if (!card) return;
    var sample = card.querySelector('.btg-typo-live__sample');
    if (!sample) return;
    if (inp.type === 'color' || (inp.getAttribute('data-mod') || '').indexOf('_color') !== -1) {
      sample.style.color = inp.value;
    }
    if (inp.type === 'number' || (inp.getAttribute('data-mod') || '').indexOf('_size') !== -1) {
      sample.style.fontSize = (parseInt(inp.value, 10) || 16) + 'px';
    }
    if (inp.tagName === 'SELECT') {
      sample.style.fontFamily = inp.value;
    }
  }
  document.addEventListener('input', function (e) {
    var inp = e.target;
    if (!inp || !inp.getAttribute) return;
    var id = inp.getAttribute('data-mod');
    if (!id) return;
    setModSafe(id, inp.type === 'number' ? (parseInt(inp.value, 10) || 0) : inp.value);
    updateTypoLive(inp);
    // Keep swatch fill in sync
    var wrap = inp.closest('.btg-color-circle, .bk-swatch, .btg-color-row');
    if (wrap) {
      var fill = wrap.querySelector('.btg-color-swatch, .bk-swatch__fill');
      if (fill && inp.type === 'color') fill.style.background = inp.value;
    }
  }, true);
  document.addEventListener('change', function (e) {
    var inp = e.target;
    if (!inp || !inp.getAttribute) return;
    var id = inp.getAttribute('data-mod');
    if (!id) return;
    setModSafe(id, inp.type === 'number' ? (parseInt(inp.value, 10) || 0) : inp.value);
    updateTypoLive(inp);
  }, true);
})();
