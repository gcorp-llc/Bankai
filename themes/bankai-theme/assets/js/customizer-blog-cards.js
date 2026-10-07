(function ($) {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  function syncAccent() {
    var accent = '#8B9BB4';
    try {
      if (wp.customize('bankai_design_json')) {
        var raw = wp.customize('bankai_design_json').get();
        if (raw) {
          var d = typeof raw === 'string' ? JSON.parse(raw) : raw;
          if (d && d.colors && d.colors.primary) accent = d.colors.primary;
          else if (d && d.p) accent = d.p;
        }
      }
    } catch (e) {}
    var soft = accent;
    document.querySelectorAll('.bbc').forEach(function (el) {
      el.style.setProperty('--bbc-accent', accent);
      el.style.setProperty('--bbc-accent-soft', soft);
    });
  }

  wp.customize.bind('ready', function () {
    document.querySelectorAll('.bbc-card input').forEach(function (input) {
      input.addEventListener('change', function () {
        var card = input.closest('.bbc-card');
        var grid = input.closest('.bbc-grid');
        if (grid) {
          grid.querySelectorAll('.bbc-card').forEach(function (c) {
            c.classList.toggle('is-active', c === card);
          });
        }
      });
    });
    syncAccent();
    if (wp.customize('bankai_design_json')) {
      wp.customize('bankai_design_json', function (s) {
        s.bind(syncAccent);
      });
    }
  });
})(jQuery);
