/**
 * Bankai Theme front JS — mobile bar, shared nav modes, search, header scroll
 */
(function () {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function setExpanded(btn, open) {
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var mmToggle = qs('.bankai-mobile-nav-toggle');
    var mmPanel = document.getElementById('bankai-mobile-nav');
        var mmBackdrop = mmPanel && mmPanel.querySelector('.bankai-mobile-nav__backdrop');
    var searchToggle = qs('.bankai-mobile-search-toggle');
    var searchPanel = document.getElementById('bankai-mobile-search');

    function openMobileNav(open) {
      if (!mmPanel || !mmToggle) return;
      if (open) {
        mmPanel.hidden = false;
        // force reflow for animation
        void mmPanel.offsetWidth;
        mmPanel.classList.add('is-open');
        setExpanded(mmToggle, true);
        mmToggle.classList.add('is-active');
        document.body.classList.add('bankai-mm-open');
        var burger = mmToggle.querySelector('.bankai-burger');
        if (burger) burger.classList.add('is-open');
        // focus first link for a11y
        var first = mmPanel.querySelector('.bankai-mobile-menu a');
        if (first) setTimeout(function () { first.focus(); }, 320);
      } else {
        mmPanel.classList.remove('is-open');
        setExpanded(mmToggle, false);
        mmToggle.classList.remove('is-active');
        document.body.classList.remove('bankai-mm-open');
        var burger2 = mmToggle.querySelector('.bankai-burger');
        if (burger2) burger2.classList.remove('is-open');
        setTimeout(function () {
          if (!mmPanel.classList.contains('is-open')) mmPanel.hidden = true;
        }, 340);
        mmToggle.focus();
      }
    }

    if (mmToggle && mmPanel) {
      mmToggle.addEventListener('click', function () {
        openMobileNav(!mmPanel.classList.contains('is-open'));
      });
      if (mmBackdrop) mmBackdrop.addEventListener('click', function () { openMobileNav(false); });
      var panelEl = mmPanel.querySelector('.bankai-mobile-nav__panel');
      if (panelEl) panelEl.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    if (searchToggle && searchPanel) {
      searchToggle.addEventListener('click', function () {
        var open = searchPanel.hasAttribute('hidden');
        if (open) {
          searchPanel.hidden = false;
          searchPanel.classList.add('is-open');
          setExpanded(searchToggle, true);
          var input = qs('.bankai-mobile-search__input', searchPanel);
          if (input) setTimeout(function () { input.focus(); }, 50);
        } else {
          searchPanel.hidden = true;
          searchPanel.classList.remove('is-open');
          setExpanded(searchToggle, false);
        }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (mmPanel && mmPanel.classList.contains('is-open')) openMobileNav(false);
        if (searchPanel && !searchPanel.hasAttribute('hidden')) {
          searchPanel.hidden = true;
          searchPanel.classList.remove('is-open');
          setExpanded(searchToggle, false);
        }
      }
    });

    // Optional: swipe down to close bottom sheet
    if (mmPanel && mmPanel.classList.contains('bankai-mobile-nav--sheet')) {
      var startY = 0;
      var panel = mmPanel.querySelector('.bankai-mobile-nav__panel');
      if (panel) {
        panel.addEventListener('touchstart', function (e) {
          startY = e.touches[0].clientY;
        }, { passive: true });
        panel.addEventListener('touchend', function (e) {
          var dy = e.changedTouches[0].clientY - startY;
          if (dy > 80) openMobileNav(false);
        }, { passive: true });
      }
    }
  });
})();

/* Header scroll behaviors */
(function () {
  var header = document.querySelector('.bankai-header');
  if (!header) return;
  var lastY = 0;
  window.addEventListener('scroll', function () {
    var y = window.scrollY || 0;
    if (header.classList.contains('is-hide-on-scroll')) {
      if (y > lastY && y > 80) header.classList.add('is-hidden');
      else header.classList.remove('is-hidden');
    }
    if (header.classList.contains('is-sticky-scroll')) {
      if (y > 80) header.classList.add('is-stuck');
      else header.classList.remove('is-stuck');
    }
    if (header.classList.contains('is-transparent')) {
      header.classList.toggle('is-scrolled', y > 20);
      document.body.classList.toggle('bankai-header-scrolled', y > 20);
    }
    lastY = y;
  }, { passive: true });
})();


/* Scroll to top + FABs */
(function () {
  var btn = document.querySelector('.bankai-scroll-top');
  if (btn) {
    var onScroll = function () {
      var y = window.scrollY || 0;
      if (y > 280) {
        btn.hidden = false;
        btn.classList.add('is-visible');
      } else {
        btn.classList.remove('is-visible');
        setTimeout(function () {
          if (!btn.classList.contains('is-visible')) btn.hidden = true;
        }, 250);
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }


  /* ——— Infinite scroll (blog + search) ——— */
  function bankaiInfinite() {
    var grid = document.querySelector('.bankai-posts-grid[data-pagination="infinite"]');
    if (!grid) return;
    var max = parseInt(grid.getAttribute('data-max') || '1', 10) || 1;
    var page = parseInt(grid.getAttribute('data-page') || '1', 10) || 1;
    var context = grid.getAttribute('data-context') || 'blog';
    var search = grid.getAttribute('data-search') || '';
    var loading = false;
    var status = document.querySelector('[data-infinite-status]');
    var endEl = document.querySelector('[data-infinite-end]');
    var ajaxUrl = (window.bankaiTheme && bankaiTheme.ajaxUrl) || '/wp-admin/admin-ajax.php';

    if (page >= max) {
      if (endEl) endEl.hidden = false;
      return;
    }

    function loadNext() {
      if (loading || page >= max) return;
      loading = true;
      if (status) status.hidden = false;
      var next = page + 1;
      var url = ajaxUrl + '?action=bankai_load_posts&page=' + next + '&context=' + encodeURIComponent(context);
      if (context === 'search' && search) {
        url += '&s=' + encodeURIComponent(search);
      }
      fetch(url, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data || !data.success || !data.data) {
            loading = false;
            if (status) status.hidden = true;
            return;
          }
          var html = data.data.html || '';
          if (html) {
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            while (tmp.firstChild) {
              grid.appendChild(tmp.firstChild);
            }
          }
          page = data.data.page || next;
          grid.setAttribute('data-page', String(page));
          max = data.data.max || max;
          grid.setAttribute('data-max', String(max));
          loading = false;
          if (status) status.hidden = true;
          if (!data.data.has_more || page >= max) {
            if (endEl) endEl.hidden = false;
          }
        })
        .catch(function () {
          loading = false;
          if (status) status.hidden = true;
        });
    }

    var sentinel = document.createElement('div');
    sentinel.className = 'bankai-infinite-sentinel';
    sentinel.setAttribute('aria-hidden', 'true');
    grid.parentNode.insertBefore(sentinel, grid.nextSibling);

    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) loadNext();
        });
      }, { rootMargin: '200px 0px' });
      io.observe(sentinel);
    } else {
      window.addEventListener('scroll', function () {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 400) {
          loadNext();
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    bankaiInfinite();
  });
})();
