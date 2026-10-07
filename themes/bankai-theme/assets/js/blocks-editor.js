(function (wp) {
  'use strict';
  if (!wp || !wp.blocks) return;

  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var RangeControl = wp.components.RangeControl;
  var SelectControl = wp.components.SelectControl;
  var ServerSideRender = wp.serverSideRender
    ? wp.serverSideRender
    : (wp.components && wp.components.ServerSideRender);

  function field(label, value, onChange, multiline) {
    if (multiline) {
      return el(TextareaControl, { label: label, value: value || '', onChange: onChange });
    }
    return el(TextControl, { label: label, value: value || '', onChange: onChange });
  }

  function wrapEdit(name, attrsFields) {
    return function (props) {
      var attrs = props.attributes;
      var set = props.setAttributes;
      var controls = attrsFields.map(function (f) {
        if (f.type === 'range') {
          return el(RangeControl, {
            key: f.key,
            label: f.label,
            value: attrs[f.key] || f.min || 1,
            onChange: function (v) { var o = {}; o[f.key] = v; set(o); },
            min: f.min || 1,
            max: f.max || 12
          });
        }
        if (f.type === 'select') {
          return el(SelectControl, {
            key: f.key,
            label: f.label,
            value: attrs[f.key] || f.options[0].value,
            options: f.options,
            onChange: function (v) { var o = {}; o[f.key] = v; set(o); }
          });
        }
        return el('div', { key: f.key }, field(f.label, attrs[f.key], function (v) {
          var o = {}; o[f.key] = v; set(o);
        }, f.multiline));
      });

      return el('div', { className: 'bankai-block-editor' },
        el(InspectorControls, {},
          el(PanelBody, { title: 'تنظیمات', initialOpen: true }, controls)
        ),
        ServerSideRender
          ? el(ServerSideRender, { block: name, attributes: attrs })
          : el('div', { className: 'bankai-block-placeholder' }, 'Bankai Block: ' + name)
      );
    };
  }

  registerBlockType('bankai/hero', {
    apiVersion: 2,
    title: 'هیرو بنکای',
    category: 'bankai',
    icon: 'cover-image',
    attributes: {
      title: { type: 'string', default: 'قالب فوق‌سریع Bankai' },
      subtitle: { type: 'string', default: 'سبک، مدرن و بهینه برای Core Web Vitals' },
      ctaText: { type: 'string', default: 'شروع کنید' },
      ctaUrl: { type: 'string', default: '#' },
      align: { type: 'string', default: 'center' }
    },
    edit: wrapEdit('bankai/hero', [
      { key: 'title', label: 'عنوان' },
      { key: 'subtitle', label: 'زیرعنوان', multiline: true },
      { key: 'ctaText', label: 'متن دکمه' },
      { key: 'ctaUrl', label: 'لینک دکمه' },
      { key: 'align', label: 'تراز', type: 'select', options: [
        { label: 'وسط', value: 'center' },
        { label: 'چپ', value: 'left' },
        { label: 'راست', value: 'right' }
      ]}
    ]),
    save: function () { return null; }
  });

  registerBlockType('bankai/features', {
    apiVersion: 2,
    title: 'ویژگی‌ها (۳ ستون)',
    category: 'bankai',
    icon: 'screenoptions',
    attributes: {
      f1Title: { type: 'string', default: 'سرعت' },
      f1Text: { type: 'string', default: 'بدون jQuery و با CSS حداقلی.' },
      f2Title: { type: 'string', default: 'سئو' },
      f2Text: { type: 'string', default: 'سازگار با پلاگین Bankai Core.' },
      f3Title: { type: 'string', default: 'RTL' },
      f3Text: { type: 'string', default: 'پشتیبانی کامل فارسی و راست‌چین.' }
    },
    edit: wrapEdit('bankai/features', [
      { key: 'f1Title', label: 'عنوان ۱' }, { key: 'f1Text', label: 'متن ۱', multiline: true },
      { key: 'f2Title', label: 'عنوان ۲' }, { key: 'f2Text', label: 'متن ۲', multiline: true },
      { key: 'f3Title', label: 'عنوان ۳' }, { key: 'f3Text', label: 'متن ۳', multiline: true }
    ]),
    save: function () { return null; }
  });

  registerBlockType('bankai/cta', {
    apiVersion: 2,
    title: 'فراخوان اقدام (CTA)',
    category: 'bankai',
    icon: 'megaphone',
    attributes: {
      title: { type: 'string', default: 'آماده شروع هستید؟' },
      text: { type: 'string', default: 'همین حالا سایت خود را با Bankai بسازید.' },
      btnText: { type: 'string', default: 'تماس با ما' },
      btnUrl: { type: 'string', default: '#' }
    },
    edit: wrapEdit('bankai/cta', [
      { key: 'title', label: 'عنوان' },
      { key: 'text', label: 'متن', multiline: true },
      { key: 'btnText', label: 'متن دکمه' },
      { key: 'btnUrl', label: 'لینک' }
    ]),
    save: function () { return null; }
  });

  registerBlockType('bankai/posts', {
    apiVersion: 2,
    title: 'شبکه نوشته‌ها',
    category: 'bankai',
    icon: 'grid-view',
    attributes: {
      count: { type: 'number', default: 3 },
      category: { type: 'string', default: '' }
    },
    edit: wrapEdit('bankai/posts', [
      { key: 'count', label: 'تعداد', type: 'range', min: 1, max: 12 },
      { key: 'category', label: 'نامک دسته (اختیاری)' }
    ]),
    save: function () { return null; }
  });
})(window.wp);
