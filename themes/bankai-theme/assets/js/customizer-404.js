(function ($) {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  function setMod(id, val) {
    if (!wp.customize(id)) return;
    wp.customize(id).set(val);
    if (wp.customize.state && wp.customize.state('saved')) {
      wp.customize.state('saved').set(false);
    }
  }

  function bind404() {
    document.addEventListener('click', function (e) {
      var tog = e.target.closest('[data-b404-toggle]');
      if (tog) {
        e.preventDefault();
        var id = tog.getAttribute('data-b404-toggle');
        var on = !tog.classList.contains('is-on');
        tog.classList.toggle('is-on', on);
        tog.setAttribute('aria-pressed', on ? 'true' : 'false');
        var st = tog.querySelector('.bsl-card__state');
        if (st) st.textContent = on ? 'فعال' : 'خاموش';
        setMod(id, on);
        if (id === 'page_404_show_latest') {
          var p = document.querySelector('[data-b404-panel="latest"]');
          if (p) p.classList.toggle('is-open', on);
        }
        if (id === 'page_404_auto_redirect') {
          var p2 = document.querySelector('[data-b404-panel="redirect"]');
          if (p2) p2.classList.toggle('is-open', on);
        }
        return;
      }

      var th = e.target.closest('[data-b404-theme]');
      if (th) {
        e.preventDefault();
        var tid = th.getAttribute('data-b404-theme');
        document.querySelectorAll('[data-b404-theme]').forEach(function (b) {
          b.classList.toggle('is-active', b === th);
        });
        setMod('page_404_theme', tid);
        return;
      }

      var ch = e.target.closest('[data-b404-choice]');
      if (ch) {
        e.preventDefault();
        var sid = ch.getAttribute('data-b404-choice');
        var val = ch.getAttribute('data-value');
        var group = ch.parentNode;
        if (group) {
          group.querySelectorAll('[data-b404-choice="' + sid + '"]').forEach(function (b) {
            b.classList.toggle('is-on', b === ch);
            b.classList.toggle('is-active', b === ch);
          });
        }
        setMod(sid, val);
        if (sid === 'page_404_redirect_type') {
          var box = document.querySelector('[data-b404-redir-url]');
          if (box) box.classList.toggle('is-open', val === 'custom');
        }
        return;
      }
    });

    document.addEventListener('input', function (e) {
      var t = e.target;
      if (t && t.hasAttribute('data-b404-setting')) {
        var id = t.getAttribute('data-b404-setting');
        var val = t.type === 'number' ? parseInt(t.value, 10) || 0 : t.value;
        setMod(id, val);
      }
    });
    document.addEventListener('change', function (e) {
      var t = e.target;
      if (t && t.hasAttribute('data-b404-setting')) {
        var id = t.getAttribute('data-b404-setting');
        var val = t.type === 'number' ? parseInt(t.value, 10) || 0 : t.value;
        setMod(id, val);
      }
    });

    // Icon picker for 404 button
    document.addEventListener('click', function (e) {
      // apply handled by header-builder icon modal via data-b404-icon — also bind here
    });

    // Navigate preview to 404 when section opens
    if (wp.customize.section) {
      wp.customize.section('bankai_page_404', function (section) {
        section.expanded.bind(function (isExpanded) {
          if (!isExpanded) return;
          var url = (window.bankai404Ctrl && bankai404Ctrl.preview404) || (home_url_fallback());
          try {
            wp.customize.previewer.previewUrl.set(url);
          } catch (err) {}
        });
      });
    }
  }

  function home_url_fallback() {
    return (window.bankai404Ctrl && bankai404Ctrl.preview404) || '/bankai-preview-404-page/';
  }

  // Hook icon modal apply for b404
  var origApply = null;
  $(function () {
    bind404();
    // When icon modal applies, check data-b404-icon
    document.addEventListener('click', function (e) {
      if (!e.target.closest('[data-icon-apply]')) return;
      setTimeout(function () {
        var btn = document.querySelector('[data-b404-icon][data-icon]');
        // handled in header-builder; also sync setting
        var open = document.querySelector('[data-b404-icon]');
        if (open) {
          var icon = open.getAttribute('data-icon');
          if (icon) setMod(open.getAttribute('data-b404-icon'), icon);
        }
      }, 50);
    });
  });
})(jQuery);
