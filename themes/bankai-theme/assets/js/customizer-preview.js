(function () {
  'use strict';
  if (typeof wp === 'undefined' || !wp.customize) return;

  function setVar(name, value) {
    if (value !== undefined && value !== null && value !== '') {
      document.documentElement.style.setProperty(name, value);
    }
  }

  function ensureLiveStyle() {
    var el = document.getElementById('bankai-design-live-style');
    if (!el) {
      el = document.createElement('style');
      el.id = 'bankai-design-live-style';
      document.head.appendChild(el);
    }
    return el;
  }

  function applyDesign(design) {
    if (!design || typeof design !== 'object') return;
    var c = design.colors || {};
    var r = design.radius || {};

    if (design.style) {
      var keep = [];
      (document.body.className || '').split(/\s+/).forEach(function (cls) {
        if (cls && cls.indexOf('bankai-style-') !== 0) keep.push(cls);
      });
      keep.push('bankai-style-' + design.style);
      document.body.className = keep.join(' ');
      document.documentElement.setAttribute('data-bankai-style', design.style);
    }

    if (design.scheme === 'dark' || design.scheme === 'light') {
      document.documentElement.setAttribute('data-theme', design.scheme);
    }

    // Core design tokens
    if (c.primary) setVar('--p', c.primary);
    if (c.secondary) setVar('--s', c.secondary);
    if (c.accent) setVar('--a', c.accent);
    if (c.neutral) setVar('--n', c.neutral);
    if (c.base) setVar('--b1', c.base);
    if (c.base_content || c.text) setVar('--bc', c.base_content || c.text);
    if (c.info) setVar('--in', c.info);
    if (c.success) setVar('--su', c.success);
    if (c.warning) setVar('--wa', c.warning);
    if (c.error) setVar('--er', c.error);

    if (c.primary) setVar('--bankai-primary', c.primary);
    if (c.secondary) setVar('--bankai-secondary', c.secondary);
    if (c.accent) setVar('--bankai-accent', c.accent);
    if (c.neutral) setVar('--bankai-neutral', c.neutral);
    if (c.base) setVar('--bankai-bg', c.base);
    if (c.base_content || c.text) setVar('--bankai-text', c.base_content || c.text);
    if (c.info) setVar('--bankai-info', c.info);
    if (c.success) setVar('--bankai-success', c.success);
    if (c.warning) setVar('--bankai-warning', c.warning);
    if (c.error) setVar('--bankai-error', c.error);
    if (c.primary) {
      setVar('--bankai-border', 'color-mix(in srgb, ' + c.primary + ' 18%, transparent)');
    }

    if (r.box) {
      setVar('--bankai-radius', r.box);
      setVar('--rbox', r.box);
    }
    if (r.field) {
      setVar('--bankai-radius-field', r.field);
      setVar('--rfield', r.field);
    }
    if (r.selector) {
      setVar('--bankai-radius-selector', r.selector);
    }

    // Live CSS override for elements that may have hard-coded values
    var css = '';
    if (c.base) {
      css += 'body{background:' + c.base + '!important;color:' + (c.base_content || c.text || 'inherit') + '!important;}';
    }
    if (r.box) {
      css += '.bankai-card,.bankai-post-card,.bankai-sidebar__block,.bankai-author-box,.widget,.bankai-header,.bankai-footer,.bankai-mobile-bar,.comments-area{border-radius:' + r.box + '!important;}';
    }
    if (r.field) {
      css += 'input,textarea,select,.bankai-btn,.button,button[type=submit],.wp-block-button__link{border-radius:' + r.field + '!important;}';
    }
    if (c.primary) {
      css += 'a{color:' + c.primary + ';}a:hover{color:color-mix(in srgb,' + c.primary + ' 80%,#000);}' +
        '.bankai-btn,.wp-block-button__link,button[type=submit],input[type=submit],.button{background:' + c.primary + '!important;border-color:' + c.primary + '!important;}';
    }
    ensureLiveStyle().textContent = css;

    // Sync header + mobile bar with design tokens (inherit mode)
    if (c.base) setVar('--bankai-header-bg', c.base);
    if (c.base_content) setVar('--bankai-header-color', c.base_content);
    if (c.primary) setVar('--bankai-header-link-active', c.primary);
    if (c.base_content) setVar('--bankai-header-link', c.base_content);
    var header = document.querySelector('.bankai-header, .bankai-header--builder');
    if (header) {
      // Only override inline if no custom nav skin style tag active with rules
      var skinLive = document.getElementById('bankai-nav-skin-live');
      var hasCustomSkin = skinLive && skinLive.textContent && skinLive.textContent.indexOf('background') !== -1;
      if (!hasCustomSkin) {
        if (c.base) header.style.background = c.base;
        if (c.base_content) header.style.color = c.base_content;
      }
      if (r.box) header.style.borderRadius = r.box;
    }
    var mob = document.querySelector('.bankai-mobile-bar');
    if (mob) {
      var skinLive2 = document.getElementById('bankai-nav-skin-live');
      var hasCustom2 = skinLive2 && skinLive2.textContent && skinLive2.textContent.indexOf('.bankai-mobile-bar') !== -1;
      if (!hasCustom2) {
        if (c.base) mob.style.background = c.base;
        if (c.base_content) mob.style.color = c.base_content;
      }
    }
  }

  function applySiteLayout(val) {
    var body = document.body;
    ['bankai-layout-boxed', 'bankai-layout-wide', 'bankai-layout-full', 'bankai-layout-content-sidebar', 'bankai-layout-sidebar-content'].forEach(function (c) {
      body.classList.remove(c);
    });
    var map = {
      boxed: 'bankai-layout-boxed',
      wide: 'bankai-layout-wide',
      full: 'bankai-layout-full',
      'content-sidebar': 'bankai-layout-content-sidebar',
      'sidebar-content': 'bankai-layout-sidebar-content'
    };
    if (map[val]) body.classList.add(map[val]);

    var layout = document.querySelector('.bankai-layout');
    if (layout) {
      layout.classList.remove('bankai-layout--sidebar-left', 'bankai-layout--sidebar-right', 'bankai-layout--full', 'bankai-layout--boxed', 'bankai-layout--wide');
      if (val === 'full') layout.classList.add('bankai-layout--full');
      else if (val === 'boxed') layout.classList.add('bankai-layout--boxed');
      else if (val === 'content-sidebar') layout.classList.add('bankai-layout--sidebar-right');
      else if (val === 'sidebar-content') layout.classList.add('bankai-layout--sidebar-left');
      else layout.classList.add('bankai-layout--wide');
    }

    var container = document.querySelectorAll('.bankai-container, .site-content > .bankai-container, .bankai-header .bankai-container');
    container.forEach(function (el) {
      if (val === 'full') {
        el.style.maxWidth = '100%';
        el.style.width = '100%';
      } else if (val === 'boxed') {
        el.style.maxWidth = 'var(--bankai-container, 1280px)';
        el.style.marginInline = 'auto';
      } else {
        el.style.maxWidth = '';
        el.style.width = '';
      }
    });
  }

  function applyContainerLayout(val) {
    document.body.setAttribute('data-container-layout', val || 'boxed');
    document.querySelectorAll('.bankai-container').forEach(function (el) {
      if (val === 'full') {
        el.style.maxWidth = '100%';
        el.style.paddingInline = '1rem';
      } else {
        el.style.maxWidth = 'var(--bankai-container, 1280px)';
        el.style.marginInline = 'auto';
      }
    });
  }

  function applyContainerStyle(val) {
    document.body.setAttribute('data-container-style', val || 'default');
    document.querySelectorAll('.bankai-container, .bankai-main, #primary').forEach(function (el) {
      if (val === 'framed') {
        el.style.boxShadow = 'inset 0 0 0 1px var(--bankai-border, rgba(0,0,0,.08))';
        el.style.borderRadius = 'var(--bankai-radius, 12px)';
      } else {
        el.style.boxShadow = '';
        el.style.borderRadius = '';
      }
    });
  }

  function applyTextDirection(dir) {
    if (dir !== 'rtl' && dir !== 'ltr') return;
    document.documentElement.setAttribute('dir', dir);
    document.body.setAttribute('dir', dir);
    document.documentElement.style.direction = dir;
    document.body.style.direction = dir;
  }

  function applyTopPadding(px) {
    var main = document.querySelector('.bankai-main') || document.querySelector('#primary') || document.querySelector('main');
    if (main) main.style.paddingTop = (parseInt(px, 10) || 0) + 'px';
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
    } else {
      layout.classList.add('bankai-layout--sidebar-right');
    }
  }

  wp.customize.bind('preview-ready', function () {
    if (wp.customize.preview) {
      wp.customize.preview.bind('bankai-design-live', applyDesign);
      wp.customize.preview.bind('bankai-layout-live', function (payload) {
        if (!payload) return;
        if (payload.site_layout) applySiteLayout(payload.site_layout);
        if (payload.container_layout) applyContainerLayout(payload.container_layout);
        if (payload.container_style) applyContainerStyle(payload.container_style);
      });
      wp.customize.preview.bind('bankai-dir-live', applyTextDirection);
    }

    if (wp.customize('bankai_design_json')) {
      wp.customize('bankai_design_json', function (v) {
        v.bind(function (raw) {
          try {
            applyDesign(typeof raw === 'string' ? JSON.parse(raw) : raw);
          } catch (e) { /* ignore */ }
        });
      });
    }

    if (wp.customize('site_layout')) {
      wp.customize('site_layout', function (v) {
        v.bind(applySiteLayout);
        applySiteLayout(v.get());
      });
    }
    if (wp.customize('container_layout')) {
      wp.customize('container_layout', function (v) {
        v.bind(applyContainerLayout);
        applyContainerLayout(v.get());
      });
    }
    if (wp.customize('container_style')) {
      wp.customize('container_style', function (v) {
        v.bind(applyContainerStyle);
        applyContainerStyle(v.get());
      });
    }
    if (wp.customize('container_width')) {
      wp.customize('container_width', function (v) {
        v.bind(function (w) {
          setVar('--bankai-container', (parseInt(w, 10) || 1280) + 'px');
        });
      });
    }
    if (wp.customize('text_direction')) {
      wp.customize('text_direction', function (v) {
        v.bind(applyTextDirection);
        applyTextDirection(v.get());
      });
    }

    ['font_body', 'font_heading', 'font_nav', 'font_title'].forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          if (id === 'font_body') {
            setVar('--bankai-font-body', val);
            document.body.style.fontFamily = val || '';
          }
          if (id === 'font_heading') {
            setVar('--bankai-font-heading', val);
            document.querySelectorAll('h1,h2,h3,h4,h5,h6,.bankai-single__title').forEach(function (h) {
              h.style.fontFamily = val || '';
            });
          }
          if (id === 'font_nav') {
            document.querySelectorAll('.bankai-menu,.bankai-nav,.bankai-header').forEach(function (el) {
              el.style.fontFamily = val || '';
            });
          }
          if (id === 'font_title') {
            document.querySelectorAll('.bankai-single__title,.bankai-archive-title,.bankai-card__title').forEach(function (el) {
              el.style.fontFamily = val || '';
            });
          }
        });
      });
    });

    var fontColors = {
      font_body_color: { var: '--bankai-text', sel: 'body, p, .entry-content' },
      font_h1_color: { var: '--bankai-h1-color', sel: 'h1' },
      font_h2_color: { var: '--bankai-h2-color', sel: 'h2' },
      font_h3_color: { var: '--bankai-h3-color', sel: 'h3' },
      font_h4_color: { var: '--bankai-h4-color', sel: 'h4' },
      font_nav_color: { var: '--bankai-nav-color', sel: '.bankai-menu a,.bankai-nav a,.bankai-header a' },
      font_title_color: { var: '--bankai-title-color', sel: '.bankai-single__title,.bankai-archive-title,.bankai-page__title' },
      font_button_color: { var: '--bankai-btn-color', sel: '.bankai-btn,.wp-block-button__link,button[type=submit],.button' },
      font_list_color: { var: '--bankai-list-color', sel: 'ul,ol,li' }
    };
    Object.keys(fontColors).forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          if (!val) return;
          var meta = fontColors[id];
          setVar(meta.var, val);
          try {
            document.querySelectorAll(meta.sel).forEach(function (el) { el.style.color = val; });
          } catch (e) {}
        });
      });
    });
    // When switching font_color_mode to inherit, clear inline overrides by soft refresh of vars from design
    if (wp.customize('font_color_mode')) {
      wp.customize('font_color_mode', function (v) {
        v.bind(function (mode) {
          if (mode === 'inherit') {
            document.querySelectorAll('body,h1,h2,h3,h4,p,.bankai-menu a,.bankai-nav a').forEach(function (el) {
              el.style.color = '';
            });
          }
        });
      });
    }

    ['font_body_size', 'font_h1_size', 'font_h2_size', 'font_h3_size', 'font_h4_size', 'font_nav_size', 'font_title_size'].forEach(function (id) {
      if (!wp.customize(id)) return;
      wp.customize(id, function (v) {
        v.bind(function (val) {
          var map = {
            font_body_size: '--bankai-fs',
            font_h1_size: '--bankai-h1',
            font_h2_size: '--bankai-h2',
            font_h3_size: '--bankai-h3',
            font_h4_size: '--bankai-h4',
            font_nav_size: '--bankai-nav-fs',
            font_title_size: '--bankai-title-fs'
          };
          if (map[id]) setVar(map[id], parseInt(val, 10) + 'px');
          if (id === 'font_body_size') document.body.style.fontSize = parseInt(val, 10) + 'px';
        });
      });
    });

    if (wp.customize('font_color_mode')) {
      wp.customize('font_color_mode', function (v) {
        v.bind(function (mode) {
          document.body.setAttribute('data-font-color-mode', mode || 'inherit');
          if (mode === 'inherit') {
            document.body.style.color = '';
            document.querySelectorAll('h1,h2,h3,h4,.bankai-menu a,.bankai-nav a,.bankai-single__title').forEach(function (el) {
              el.style.color = '';
            });
          }
        });
      });
    }

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
          v.bind(applySidebar);
        });
      }
    });
  });


  function applyHeaderNavSkin(cfg) {
    if (!cfg || typeof cfg !== 'object') return;
    var style = document.getElementById('bankai-nav-skin-live');
    if (!style) {
      style = document.createElement('style');
      style.id = 'bankai-nav-skin-live';
      document.head.appendChild(style);
    }
    function build(skin, scope) {
      if (!skin) return '';
      // Auto-apply if custom mode OR any color token is set
      var hasColor = !!(skin.bg || skin.color || skin.link_active || skin.sticky_bg);
      if ((skin.style_mode || 'inherit') !== 'custom' && !hasColor) return '';
      var rules = [];
      if (skin.bg) rules.push('background:' + skin.bg + ' !important');
      if (skin.color) rules.push('color:' + skin.color + ' !important');
      if (skin.font_size) rules.push('font-size:' + parseInt(skin.font_size, 10) + 'px');
      var mt = parseInt(skin.margin_top != null ? skin.margin_top : skin.margin || 0, 10);
      var mr = parseInt(skin.margin_right != null ? skin.margin_right : skin.margin || 0, 10);
      var mb = parseInt(skin.margin_bottom != null ? skin.margin_bottom : skin.margin || 0, 10);
      var ml = parseInt(skin.margin_left != null ? skin.margin_left : skin.margin || 0, 10);
      if (mt || mr || mb || ml) rules.push('margin:' + mt + 'px ' + mr + 'px ' + mb + 'px ' + ml + 'px');
      var pt = parseInt(skin.padding_top != null ? skin.padding_top : skin.padding_y || 0, 10);
      var pr = parseInt(skin.padding_right != null ? skin.padding_right : skin.padding_x || 0, 10);
      var pb = parseInt(skin.padding_bottom != null ? skin.padding_bottom : skin.padding_y || 0, 10);
      var pl = parseInt(skin.padding_left != null ? skin.padding_left : skin.padding_x || 0, 10);
      if (pt || pr || pb || pl) rules.push('padding:' + pt + 'px ' + pr + 'px ' + pb + 'px ' + pl + 'px');
      var rt = parseInt(skin.radius_top != null ? skin.radius_top : skin.radius || 0, 10);
      var rr = parseInt(skin.radius_right != null ? skin.radius_right : skin.radius || 0, 10);
      var rb = parseInt(skin.radius_bottom != null ? skin.radius_bottom : skin.radius || 0, 10);
      var rl = parseInt(skin.radius_left != null ? skin.radius_left : skin.radius || 0, 10);
      if (rt || rr || rb || rl) rules.push('border-radius:' + rt + 'px ' + rr + 'px ' + rb + 'px ' + rl + 'px');
      if (skin.border_width) rules.push('border:' + parseInt(skin.border_width, 10) + 'px ' + (skin.border_style || 'solid') + ' ' + (skin.border_color || 'transparent'));
      if (skin.shadow) rules.push('box-shadow:0 4px 20px rgba(0,0,0,.12)');
      if (skin.height) rules.push('min-height:' + parseInt(skin.height, 10) + 'px');
      var css = '';
      if (rules.length) css += scope + '{' + rules.join(';') + ';}';
      if (skin.color) css += scope + ' a,' + scope + ' .menu a{color:' + skin.color + ';}';
      if (skin.link_active) css += scope + ' .current-menu-item>a,' + scope + ' .current_page_item>a{color:' + skin.link_active + ';}';
      if (skin.sticky_on) {
        var st = [];
        if (skin.sticky_bg) {
          if (skin.sticky_blur) {
            st.push('background:color-mix(in srgb,' + skin.sticky_bg + ' 82%, transparent)');
            st.push('backdrop-filter:blur(12px)');
            st.push('-webkit-backdrop-filter:blur(12px)');
          } else {
            st.push('background:' + skin.sticky_bg);
          }
        }
        if (skin.sticky_color) st.push('color:' + skin.sticky_color);
        if (skin.sticky_anim) st.push('transition:background .28s ease,color .28s ease,box-shadow .28s ease');
        if (st.length) css += 'body.bankai-header-scrolled ' + scope + ',' + scope + '.is-scrolled{' + st.join(';') + ';}';
      }
      var as = skin.active_style || 'none';
      if (as === 'underline') {
        var ulc = skin.ul_color || skin.link_active || 'currentColor';
        css += scope + ' .current-menu-item>a,' + scope + ' .current_page_item>a{text-decoration:underline;text-decoration-style:' + (skin.ul_style || 'solid') + ';text-decoration-color:' + ulc + ';text-underline-offset:4px;}';
      } else if (as === 'box') {
        var bg = skin.box_bg || '#7c5cff';
        var op = Math.max(0, Math.min(100, parseInt(skin.box_opacity || 100, 10)));
        var pad = parseInt(skin.box_pad || 6, 10);
        var rad = parseInt(skin.box_radius || 8, 10);
        var bw = parseInt(skin.box_border_w || 0, 10);
        var bc = skin.box_border_c || 'transparent';
        css += scope + ' .current-menu-item>a,' + scope + ' .current_page_item>a{display:inline-block;padding:' + pad + 'px ' + (pad + 4) + 'px;border-radius:' + rad + 'px;background:color-mix(in srgb,' + bg + ' ' + op + '%, transparent);border:' + bw + 'px solid ' + bc + ';}';
      }
      ['top', 'main', 'bottom'].forEach(function (rid) {
        var rk = 'row_' + rid + '_bg';
        if (skin[rk]) {
          css += scope + ' .bankai-header-row[data-row="' + rid + '"],' + scope + ' .bankai-header-row--' + rid + '{background:' + skin[rk] + ';}';
        }
      });
      return css;
    }
    var css = '';
    css += build(cfg.nav_desktop || {}, '.bankai-header--builder, .bankai-header');
    css += build(cfg.nav_mobile || {}, '.bankai-mobile-bar');
    // behavior classes on header
    var header = document.querySelector('.bankai-header, .bankai-header--builder');
    if (header && cfg.behavior) {
      header.classList.remove('is-sticky', 'is-transparent', 'is-hide-on-scroll', 'is-sticky-scroll');
      if (cfg.behavior === 'sticky' || cfg.behavior === 'transparent' || cfg.behavior === 'sticky-scroll') header.classList.add('is-sticky');
      if (cfg.behavior === 'transparent') header.classList.add('is-transparent');
      if (cfg.behavior === 'hide-on-scroll') header.classList.add('is-hide-on-scroll');
      if (cfg.behavior === 'sticky-scroll') header.classList.add('is-sticky-scroll');
    }
    style.textContent = css;
    // Also mirror desktop skin into CSS variables for child elements
    var nd = cfg.nav_desktop || {};
    if (nd.bg) setVar('--bankai-header-bg', nd.bg);
    if (nd.color) {
      setVar('--bankai-header-color', nd.color);
      setVar('--bankai-header-link', nd.color);
    }
    if (nd.link_active) setVar('--bankai-header-link-active', nd.link_active);
    // Clear inline styles that may block CSS
    var headerEl = document.querySelector('.bankai-header, .bankai-header--builder');
    if (headerEl && (nd.style_mode === 'custom' || nd.bg || nd.color)) {
      if (nd.bg) headerEl.style.background = nd.bg;
      if (nd.color) headerEl.style.color = nd.color;
    }
  }

  // Bind header builder JSON
  if (wp.customize('bankai_header_builder')) {
    wp.customize('bankai_header_builder', function (val) {
      val.bind(function (v) {
        var cfg = v;
        if (typeof v === 'string') {
          try { cfg = JSON.parse(v); } catch (e) { cfg = null; }
        }
        if (cfg) applyHeaderNavSkin(cfg);
      });
    });
  }


})();
