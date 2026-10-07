(function () {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  function setVar(name, value) {
    if (value !== undefined && value !== null && value !== '') {
      document.documentElement.style.setProperty(name, value);
    }
  }

  function applyDesign(design) {
    if (!design) return;
    // Design style → body class (CSS for all styles is always present)
    if (design.style) {
      var keep = [];
      (document.body.className || '').split(/\s+/).forEach(function (c) {
        if (c && c.indexOf('bankai-style-') !== 0) keep.push(c);
      });
      keep.push('bankai-style-' + design.style);
      document.body.className = keep.join(' ');
      document.documentElement.setAttribute('data-bankai-style', design.style);
    }
    if (!design.colors) return;
    var c = design.colors;
    var r = design.radius || {};
    // Core tokens
    setVar('--p', c.primary);
    setVar('--s', c.secondary);
    setVar('--a', c.accent);
    setVar('--n', c.neutral);
    setVar('--b1', c.base);
    setVar('--bc', c.base_content || c.text);
    setVar('--in', c.info);
    setVar('--su', c.success);
    setVar('--wa', c.warning);
    setVar('--er', c.error);
    setVar('--bankai-primary', c.primary);
    setVar('--bankai-secondary', c.secondary);
    setVar('--bankai-accent', c.accent);
    setVar('--bankai-neutral', c.neutral);
    setVar('--bankai-bg', c.base);
    setVar('--bankai-text', c.base_content || c.text);
    setVar('--bankai-info', c.info);
    setVar('--bankai-success', c.success);
    setVar('--bankai-warning', c.warning);
    setVar('--bankai-error', c.error);
    setVar('--bankai-border', 'color-mix(in srgb, ' + (c.primary || '#8B9BB4') + ' 18%, transparent)');
    if (r.box) {
      setVar('--bankai-radius', r.box);
      setVar('--rbox', r.box);
    }
    if (r.field) {
      setVar('--bankai-radius-field', r.field);
      setVar('--rfield', r.field);
    }
    // Navbar / header — inherit unless custom overrides later
    var header = document.querySelector('.bankai-header');
    if (header) {
      header.style.background = c.base || '';
      header.style.color = c.base_content || '';
      header.style.borderColor = c.primary ? 'color-mix(in srgb, ' + c.primary + ' 22%, transparent)' : '';
      header.style.borderRadius = r.box || '';
    }
    document.querySelectorAll('.bankai-menu a, .bankai-header a').forEach(function (a) {
      a.style.color = c.base_content || '';
    });
    document.querySelectorAll('.bankai-btn, .wp-block-button__link, button.bankai-comment-submit').forEach(function (btn) {
      if (c.primary) btn.style.background = c.primary;
      if (r.field) btn.style.borderRadius = r.field;
    });
    document.querySelectorAll('.bankai-post-card, .bankai-sidebar__block, .bankai-author-box').forEach(function (el) {
      if (r.box) el.style.borderRadius = r.box;
    });
    if (design.scheme === 'dark' || design.scheme === 'light') {
      document.documentElement.setAttribute('data-theme', design.scheme);
    }
    document.body.style.background = c.base || '';
    document.body.style.color = c.base_content || '';
  }

  function applyTopPadding(px) {
    var main = document.querySelector('.bankai-main') || document.querySelector('#primary') || document.querySelector('main');
    if (main) {
      main.style.paddingTop = (parseInt(px, 10) || 0) + 'px';
    }
  }

  function applySidebar(pos) {
    var layout = document.querySelector('.bankai-layout');
    if (!layout) return;
    layout.classList.remove('bankai-layout--sidebar-left', 'bankai-layout--sidebar-right', 'bankai-layout--full');
    var side = document.querySelector('.bankai-sidebar');
    if (pos === 'none') {
      layout.classList.add('bankai-layout--full');
      if (side) side.style.display = 'none';
      return;
    }
    if (side) side.style.display = '';
    if (pos === 'left' || pos === 'sidebar-content') {
      layout.classList.add('bankai-layout--sidebar-left');
    } else if (pos === 'right' || pos === 'content-sidebar' || pos === 'default') {
      layout.classList.add('bankai-layout--sidebar-right');
    }
  }

  function applyBlogStyleMode(mode) {
    document.body.setAttribute('data-blog-style', mode || 'inherit');
  }
  function applySingleStyleMode(mode) {
    document.body.setAttribute('data-single-style', mode || 'inherit');
  }

  wp.customize.bind('preview-ready', function () {
    if (wp.customize.preview) {
      wp.customize.preview.bind('bankai-design-live', applyDesign);
    }

    // Design JSON setting
    if (wp.customize('bankai_design_json')) {
      wp.customize('bankai_design_json', function (v) {
        v.bind(function (raw) {
          try {
            applyDesign(typeof raw === 'string' ? JSON.parse(raw) : raw);
          } catch (e) { /* ignore */ }
        });
      });
    }

    // Fonts live
    ['font_body', 'font_heading', 'font_nav'].forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          if (id === 'font_body') {
            setVar('--bankai-font-body', val);
            document.body.style.fontFamily = val === 'inherit' ? '' : val;
          }
          if (id === 'font_heading') {
            setVar('--bankai-font-heading', val);
            document.querySelectorAll('h1,h2,h3,h4,h5,h6,.bankai-single__title').forEach(function (h) {
              h.style.fontFamily = val === 'inherit' ? '' : val;
            });
          }
          if (id === 'font_nav') {
            document.querySelectorAll('.bankai-menu,.bankai-header').forEach(function (el) {
              el.style.fontFamily = val === 'inherit' ? '' : val;
            });
          }
        });
      });
    });

    // Font colors
    var fontColors = {
      font_body_color: '--bankai-text',
      font_heading_color: '--bankai-heading-color',
      font_link_color: '--bankai-primary',
      font_nav_color: '--bankai-nav-color'
    };
    Object.keys(fontColors).forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          setVar(fontColors[id], val);
          if (id === 'font_body_color') document.body.style.color = val;
          if (id === 'font_heading_color') {
            document.querySelectorAll('h1,h2,h3,h4,h5,h6').forEach(function (h) {
              h.style.color = val;
            });
          }
          if (id === 'font_link_color') {
            document.querySelectorAll('a').forEach(function (a) {
              if (!a.closest('.bankai-header')) a.style.color = val;
            });
          }
          if (id === 'font_nav_color') {
            document.querySelectorAll('.bankai-menu a').forEach(function (a) {
              a.style.color = val;
            });
          }
        });
      });
    });

    ['font_body_size', 'font_h1_size', 'font_h2_size'].forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          var map = {
            font_body_size: '--bankai-fs',
            font_h1_size: '--bankai-h1',
            font_h2_size: '--bankai-h2'
          };
          setVar(map[id], parseInt(val, 10) + 'px');
        });
      });
    });

    // Top spacing modes for each page
    ['home', 'blog', 'single', 'search', '404', 'login'].forEach(function (key) {
      var modeId = 'page_' + key + '_top_mode';
      var offsetId = 'page_' + key + '_top_offset';
      if (wp.customize(modeId)) {
        wp.customize(modeId, function (v) {
          v.bind(function (mode) {
            var px = mode === 'flush' ? 0 : (wp.customize(offsetId) ? parseInt(wp.customize(offsetId).get(), 10) || 80 : 80);
            applyTopPadding(px);
          });
        });
      }
      if (wp.customize(offsetId)) {
        wp.customize(offsetId, function (v) {
          v.bind(function (px) {
            var mode = wp.customize(modeId) ? wp.customize(modeId).get() : 'offset';
            applyTopPadding(mode === 'flush' ? 0 : px);
          });
        });
      }
      var sideId = 'page_' + key + '_sidebar';
      if (wp.customize(sideId)) {
        wp.customize(sideId, function (v) {
          v.bind(function (pos) {
            applySidebar(pos);
          });
        });
      }
    });

    if (wp.customize('blog_style_mode')) {
      wp.customize('blog_style_mode', function (v) {
        v.bind(applyBlogStyleMode);
      });
    }
    if (wp.customize('single_style_mode')) {
      wp.customize('single_style_mode', function (v) {
        v.bind(applySingleStyleMode);
      });
    }

    // Blog/single custom colors live
    ['blog_card_bg', 'blog_card_text', 'blog_card_muted', 'blog_card_link', 'blog_card_border'].forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          var map = {
            blog_card_bg: '--blog-card-bg',
            blog_card_text: '--blog-card-text',
            blog_card_muted: '--blog-card-muted',
            blog_card_link: '--blog-card-link',
            blog_card_border: '--blog-card-border'
          };
          setVar(map[id], val);
        });
      });
    });
    ['single_bg', 'single_text', 'single_muted', 'single_link', 'single_heading'].forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          var map = {
            single_bg: '--single-bg',
            single_text: '--single-text',
            single_muted: '--single-muted',
            single_link: '--single-link',
            single_heading: '--single-heading'
          };
          setVar(map[id], val);
        });
      });
    });
  });
})();


