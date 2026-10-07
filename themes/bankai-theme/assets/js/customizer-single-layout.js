(function ($) {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  wp.customize.bind('ready', function () {
    document.querySelectorAll('.bsl-card').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-setting');
        if (!id || !wp.customize(id)) return;
        var cur = wp.customize(id).get();
        var on = !(cur === true || cur === '1' || cur === 1);
        wp.customize(id).set(on);
        btn.classList.toggle('is-on', on);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        var st = btn.querySelector('.bsl-card__state');
        if (st) st.textContent = on ? 'فعال' : 'خاموش';
      });
    });

    document.querySelectorAll('.bvc-card input').forEach(function (input) {
      input.addEventListener('change', function () {
        var grid = input.closest('.bvc-grid');
        if (!grid) return;
        grid.querySelectorAll('.bvc-card input').forEach(function (r) {
          if (r !== input) r.checked = false;
        });
        grid.querySelectorAll('.bvc-card').forEach(function (c) {
          c.classList.toggle('is-active', c.contains(input));
        });
      });
    });

    document.querySelectorAll('.bcg-card__picker').forEach(function (picker) {
      picker.addEventListener('input', function () {
        var sid = picker.getAttribute('data-setting');
        var val = picker.value;
        var card = picker.closest('.bcg-card');
        if (card) {
          var sw = card.querySelector('.bcg-card__swatch');
          var inp = card.querySelector('.bcg-card__input');
          if (sw) sw.style.background = val;
          if (inp) inp.value = val;
        }
        if (sid && wp.customize(sid)) {
          wp.customize(sid).set(val);
        }
      });
    });
    document.querySelectorAll('.bcg-card__input').forEach(function (inp) {
      inp.addEventListener('change', function () {
        var sid = inp.getAttribute('data-setting');
        var val = inp.value;
        if (sid && wp.customize(sid) && /^#([0-9A-Fa-f]{3}){1,2}$/.test(val)) {
          wp.customize(sid).set(val);
          var card = inp.closest('.bcg-card');
          if (card) {
            var sw = card.querySelector('.bcg-card__swatch');
            var pk = card.querySelector('.bcg-card__picker');
            if (sw) sw.style.background = val;
            if (pk) pk.value = val;
          }
        }
      });
    });

    document.querySelectorAll('.bsp-card input').forEach(function (input) {
      input.addEventListener('change', function () {
        var grid = input.closest('.bsp-grid');
        if (!grid) return;
        grid.querySelectorAll('.bsp-card input').forEach(function (r) {
          if (r !== input) r.checked = false;
        });
        grid.querySelectorAll('.bsp-card').forEach(function (c) {
          c.classList.toggle('is-active', c.contains(input));
        });
      });
    });

    document.querySelectorAll('.bts-card input').forEach(function (input) {
      input.addEventListener('change', function () {
        var wrap = input.closest('.bts');
        if (!wrap) return;
        wrap.querySelectorAll('.bts-card input').forEach(function (r) {
          if (r !== input) r.checked = false;
        });
        wrap.querySelectorAll('.bts-card').forEach(function (c) {
          c.classList.toggle('is-active', c.contains(input));
        });
        var key = wrap.getAttribute('data-offset-setting');
        if (key && wp.customize(key)) {
          wp.customize(key).set(input.value === 'flush' ? 0 : 80);
        }
      });
    });
    document.querySelectorAll('.bsm-mode input').forEach(function (input) {
      input.addEventListener('change', function () {
        var wrap = input.closest('.bsm');
        if (!wrap) return;
        wrap.querySelectorAll('.bsm-mode input').forEach(function (r) {
          if (r !== input) r.checked = false;
        });
        wrap.querySelectorAll('.bsm-mode').forEach(function (c) {
          c.classList.toggle('is-active', c.contains(input));
        });
        var custom = wrap.querySelector('.bsm-custom');
        if (custom) custom.style.display = input.value === 'custom' ? '' : 'none';
      });
    });
    document.querySelectorAll('.bsm-font, .bsm-size').forEach(function (el) {
      el.addEventListener('change', function () {
        var sid = el.getAttribute('data-setting');
        if (sid && wp.customize(sid)) {
          wp.customize(sid).set(el.type === 'number' ? parseInt(el.value, 10) || 16 : el.value);
        }
      });
    });

    /* SEO AI buttons */
    document.querySelectorAll('[data-bankai-seo-ai]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var page = btn.getAttribute('data-bankai-seo-ai');
        var status = btn.parentNode.querySelector('.bsa-ai__status');
        btn.disabled = true;
        if (status) status.textContent = 'در حال تولید…';
        var body = new URLSearchParams();
        body.set('action', 'bankai_theme_seo_ai');
        body.set('nonce', (window.bankaiThemeCustomizer && window.bankaiThemeCustomizer.nonce) || '');
        body.set('page', page);
        fetch((window.bankaiThemeCustomizer && window.bankaiThemeCustomizer.ajaxUrl) || ajaxurl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString()
        }).then(function (r) { return r.json(); }).then(function (j) {
          btn.disabled = false;
          if (j && j.success && j.data) {
            var d = j.data;
            if (d.title && wp.customize('page_' + page + '_seo_title')) wp.customize('page_' + page + '_seo_title').set(d.title);
            if (d.description && wp.customize('page_' + page + '_seo_desc')) wp.customize('page_' + page + '_seo_desc').set(d.description);
            if (d.keywords && wp.customize('page_' + page + '_seo_keywords')) wp.customize('page_' + page + '_seo_keywords').set(d.keywords);
            if (status) status.textContent = d.message || 'تولید شد — انتشار را بزنید';
          } else {
            if (status) status.textContent = (j && j.data && j.data.message) || 'خطا در تولید';
          }
        }).catch(function () {
          btn.disabled = false;
          if (status) status.textContent = 'خطای شبکه';
        });
      });
    });


    document.querySelectorAll('.bsa-input[data-setting]').forEach(function (inp) {
      inp.addEventListener('input', function () {
        var sid = inp.getAttribute('data-setting');
        if (sid && wp.customize(sid)) wp.customize(sid).set(inp.value);
        var len = inp.parentNode.querySelector('.bsa-len');
        if (len) {
          var max = inp.getAttribute('data-seo-field') === 'description' ? 160 : 60;
          len.textContent = (inp.value || '').length + '/' + max;
        }
      });
    });
    document.querySelectorAll('[data-bankai-seo-ai]').forEach(function (btn) {
      var old = btn.onclick;
      btn.addEventListener('click', function () {
        var panel = btn.closest('.bsa-panel');
        var prog = panel && panel.querySelector('.bsa-progress');
        if (prog) prog.hidden = false;
      });
    });

  });
})(jQuery);
