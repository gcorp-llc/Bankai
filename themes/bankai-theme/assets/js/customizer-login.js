(function ($) {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  function setMod(id, val) {
    var s = wp.customize(id);
    if (s) s.set(val);
  }
  function getMod(id, def) {
    var s = wp.customize(id);
    return s ? s.get() : def;
  }

  $(document).on('click', '[data-blogin-setting][data-value]', function (e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('blogin-setting');
    var val = String(btn.attr('data-value'));
    setMod(id, val);
    // toggle active in sibling group
    var group = btn.closest('.blogin-layouts, .btg-style-grid, .btg-presets');
    if (group.length) {
      group.find('[data-blogin-setting="' + id + '"]').removeClass('is-active');
    } else {
      btn.parent().find('[data-blogin-setting="' + id + '"]').removeClass('is-active');
    }
    btn.addClass('is-active');
  });

  $(document).on('input change', 'input[data-blogin-setting]:not([data-value]), textarea[data-blogin-setting]', function () {
    setMod($(this).data('blogin-setting'), $(this).val());
  });

  $(document).on('click', '[data-blogin-toggle]', function (e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('blogin-toggle');
    var next = !getMod(id, false);
    setMod(id, next);
    btn.toggleClass('is-on', next);
    btn.find('.bsl-card__state').text(next ? 'فعال' : 'خاموش');
  });

  $(document).on('click', '[data-blogin-media]', function (e) {
    e.preventDefault();
    var id = $(this).data('blogin-media');
    var frame = wp.media({
      title: (window.bankaiLoginCtrl && bankaiLoginCtrl.i18n.select) || 'Select',
      button: { text: 'OK' },
      multiple: false
    });
    frame.on('select', function () {
      var att = frame.state().get('selection').first().toJSON();
      setMod(id, att.id);
      var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
      $('[data-blogin-preview="' + id + '"]').html('<img src="' + url + '" alt="">');
    });
    frame.open();
  });

  $(document).on('click', '[data-blogin-media-clear]', function (e) {
    e.preventDefault();
    var id = $(this).data('blogin-media-clear');
    setMod(id, 0);
    $('[data-blogin-preview="' + id + '"]').html('<span class="blogin-media__ph">—</span>');
  });

  function goPreview(view) {
    var base = window.bankaiLoginCtrl || {};
    var url = view === 'register'
      ? (base.registerPreview || '/?bankai_login_preview=1&view=register')
      : (base.loginPreview || '/?bankai_login_preview=1');
    try {
      wp.customize.previewer.previewUrl.set(url);
    } catch (err) {}
  }

  $(document).on('click', '[data-blogin-preview-view]', function (e) {
    e.preventDefault();
    var view = $(this).data('blogin-preview-view');
    $('[data-blogin-preview-view]').removeClass('is-active');
    $(this).addClass('is-active');
    goPreview(view);
  });

  wp.customize.bind('ready', function () {
    if (!wp.customize.section) return;
    wp.customize.section('bankai_page_login', function (section) {
      section.expanded.bind(function (isExpanded) {
        if (!isExpanded) return;
        var active = $('[data-blogin-preview-view].is-active').data('blogin-preview-view') || 'login';
        goPreview(active);
      });
    });
  });
})(jQuery);
