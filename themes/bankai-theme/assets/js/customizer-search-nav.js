(function ($) {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;
  wp.customize.bind('ready', function () {
    function bindSection(id, urlBuilder) {
      if (!wp.customize.section) return;
      wp.customize.section(id, function (section) {
        section.expanded.bind(function (isExpanded) {
          if (!isExpanded) return;
          try {
            var url = typeof urlBuilder === 'function' ? urlBuilder() : urlBuilder;
            wp.customize.previewer.previewUrl.set(url);
          } catch (e) {}
        });
      });
    }
    var home = (window.bankaiSearchNav && bankaiSearchNav.homeUrl) || '/';
    bindSection('bankai_page_search', function () {
      return home + (home.indexOf('?') >= 0 ? '&' : '?') + 's=';
    });
    bindSection('bankai_page_blog', function () {
      return (window.bankaiSearchNav && bankaiSearchNav.blogUrl) || home;
    });
  });
})(jQuery);
