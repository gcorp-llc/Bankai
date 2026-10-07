(function () {
  'use strict';
  var root = document.getElementById('bankai-theme-admin');
  if (!root || !window.bankaiThemeAdmin) return;
  var cfg = window.bankaiThemeAdmin;
  var statusEl = document.getElementById('bta-global-status');

  function setStatus(msg, el) {
    var t = el || statusEl;
    if (t) t.textContent = msg || '';
  }

  function post(action, data) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('nonce', cfg.nonce);
    Object.keys(data || {}).forEach(function (k) {
      body.set(k, data[k]);
    });
    return fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    }).then(function (r) { return r.json(); });
  }

  /* Tabs */
  root.querySelectorAll('.bta-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      var id = tab.getAttribute('data-tab');
      root.querySelectorAll('.bta-tab').forEach(function (t) {
        t.classList.toggle('is-active', t === tab);
      });
      root.querySelectorAll('.bta-panel').forEach(function (p) {
        p.classList.toggle('is-active', p.getAttribute('data-panel') === id);
      });
    });
  });

  /* Demo filters */
  var typeFilter = 'all';
  var catFilter = 'all';
  function applyDemoFilters() {
    root.querySelectorAll('#bta-demos .bta-card').forEach(function (card) {
      var okType = typeFilter === 'all' || card.getAttribute('data-type') === typeFilter;
      var okCat = catFilter === 'all' || card.getAttribute('data-cat') === catFilter;
      card.classList.toggle('is-hidden', !(okType && okCat));
    });
  }
  root.querySelectorAll('[data-filter-group="demo-type"] .bta-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      root.querySelectorAll('[data-filter-group="demo-type"] .bta-chip').forEach(function (c) {
        c.classList.toggle('is-active', c === chip);
      });
      typeFilter = chip.getAttribute('data-filter');
      applyDemoFilters();
    });
  });
  root.querySelectorAll('[data-filter-group="demo-cat"] .bta-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      root.querySelectorAll('[data-filter-group="demo-cat"] .bta-chip').forEach(function (c) {
        c.classList.toggle('is-active', c === chip);
      });
      catFilter = chip.getAttribute('data-cat');
      applyDemoFilters();
    });
  });

  /* Import demo */
  root.querySelectorAll('[data-import-demo]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-import-demo');
      btn.disabled = true;
      setStatus(cfg.i18n.importing);
      post('bankai_theme_import_demo', { demo: id })
        .then(function (j) {
          setStatus((j && j.data && j.data.message) || (j && j.success ? cfg.i18n.done : cfg.i18n.error));
          btn.disabled = false;
        })
        .catch(function () {
          setStatus(cfg.i18n.error);
          btn.disabled = false;
        });
    });
  });

  /* Apply pattern */
  root.querySelectorAll('[data-apply-pattern]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-apply-pattern');
      var group = btn.getAttribute('data-group');
      btn.disabled = true;
      setStatus(cfg.i18n.importing);
      post('bankai_theme_apply_pattern', { pattern: id, group: group })
        .then(function (j) {
          setStatus((j && j.data && j.data.message) || cfg.i18n.done);
          btn.disabled = false;
        })
        .catch(function () {
          setStatus(cfg.i18n.error);
          btn.disabled = false;
        });
    });
  });

  /* Style cards visual active */
  root.querySelectorAll('.bta-style-card input').forEach(function (input) {
    input.addEventListener('change', function () {
      root.querySelectorAll('.bta-style-card').forEach(function (c) {
        c.classList.toggle('is-active', c.contains(input) && input.checked);
      });
    });
  });

  /* Save blog */
  var saveBlog = document.getElementById('bta-save-blog');
  if (saveBlog) {
    saveBlog.addEventListener('click', function () {
      var style = (root.querySelector('input[name="blog_card_style"]:checked') || {}).value || 'default';
      saveBlog.disabled = true;
      post('bankai_theme_save_blog_cards', {
        style: style,
        per_page: document.getElementById('blog_posts_per_page').value,
        pagination: document.getElementById('blog_pagination').value,
        title_chars: document.getElementById('blog_title_chars').value,
        excerpt_chars: document.getElementById('blog_excerpt_chars').value,
        show_author: document.getElementById('blog_show_author').checked ? '1' : '',
        show_date: document.getElementById('blog_show_date').checked ? '1' : '',
        show_cats: document.getElementById('blog_show_cats').checked ? '1' : '',
        show_excerpt: document.getElementById('blog_show_excerpt').checked ? '1' : ''
      }).then(function (j) {
        var st = document.getElementById('bta-blog-status');
        setStatus((j && j.data && j.data.message) || cfg.i18n.saved, st);
        saveBlog.disabled = false;
      }).catch(function () {
        setStatus(cfg.i18n.error, document.getElementById('bta-blog-status'));
        saveBlog.disabled = false;
      });
    });
  }
})();
