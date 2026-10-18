# Bankai SEO – Fixes & Improvements

## مشکلات شناسایی‌شده و رفع‌شده

### ۱. ناسازگاری معماری Editor
- **قبل:** `class-editor-seo.php` برای Gutenberg (`enqueue_block_editor_assets` + وابستگی‌های React) نوشته شده بود، اما `editor-seo.js` کاملاً Alpine.js بود و Alpine اصلاً لود نمی‌شد.
- **بعد:** Alpine از CDN به‌صورت defer لود می‌شود، اسکریپت روی `post.php` / `post-new.php` هم enqueue می‌شود، و nonce برای `wp.apiFetch` تنظیم می‌گردد.

### ۲. ثبت دوباره‌ی Meta با نوع متناقض
- **قبل:** `class-seo-engine.php` و `class-post-seo-meta.php` هر دو meta را ثبت می‌کردند؛ `robots` یک‌جا `array` و جای دیگر `string` بود.
- **بعد:** تنها منبع حقیقت `Bankai_SEO_Engine` است. `Bankai_Post_SEO_Meta` فقط shim سازگاری است. `robots` به‌صورت object با schema REST ثبت می‌شود.

### ۳. خروجی فرانت‌اند ناقص
- **قبل:** تگ `<title>` کنترل نمی‌شد، `meta description` نبود، robots فقط در حالت noindex چاپ می‌شد، تصویر Twitter نبود، schema بدون author/mainEntity بود.
- **بعد:**
  - فیلتر `pre_get_document_title`
  - `meta name="description"`
  - robots کامل (index/noindex + follow/nofollow)
  - OG type / locale / image fallback از thumbnail
  - Twitter image
  - Schema با author و mainEntityOfPage

### ۴. باگ‌های JS / Alpine
- قالب‌های `x-if` داخل `material-symbols` برای آیکون‌ها کار نمی‌کردند → با `x-text` جایگزین شد.
- `scoreStroke` از عدد ثابت ۳۱۴ استفاده می‌کرد → محاسبه دقیق `2πr`.
- فیلدهای X/Twitter در UI نبودند → اضافه شدند.
- Schema در save ارسال نمی‌شد → اضافه شد.
- خطای load بدون پیام مناسب → toast + fallback به Gutenberg notices.
- Debounce و state بهتر شد.

### ۵. تحلیل SEO
- بررسی طول عنوان (۳۰–۶۰) اضافه شد.
- پیام‌های i18n و جزئیات بیشتر برای تصاویر/لینک/هدینگ.
- ذخیره خودکار امتیاز در meta `_bankai_seo_score`.

### ۶. طراحی CSS
- متغیر `--seo-purple` تعریف شد (قبلاً استفاده می‌شد ولی وجود نداشت).
- شمارنده کاراکتر با کلاس `is-good` / `is-warning`.
- کارت‌های متریک، status اسکیما، divider، scrollbar ظریف.
- انیمیشن pulse برای Live Sync.
- بهبود فاصله‌ها، focus ring، accessibility پایه (role/tab).

### ۷. امنیت و پایداری
- `permission_check` و sanitize یکدست.
- `JSON_HEX_TAG` برای جلوگیری از XSS در schema.
- `esc_url` / `esc_attr` در تمام خروجی‌های meta.
- جلوگیری از double-submit در save.

---

## فایل‌های تحویلی

```
bankai-seo-fixed/
├── inc/
│   ├── class-editor-seo.php          ← جایگزین کامل
│   ├── meta/class-post-seo-meta.php  ← shim
│   └── modules/class-seo-engine.php  ← جایگزین کامل
├── assets/
│   ├── css/editor-seo.css
│   └── js/editor-seo.js
├── views/tab-seo-engine.php
├── functions-snippet.php             ← اختیاری (سخت‌سازی helpers)
└── CHANGELOG-FIXES.md
```

## نحوه جایگزینی

1. فایل‌های `inc/modules/class-seo-engine.php`، `inc/class-editor-seo.php`، `inc/meta/class-post-seo-meta.php` را جایگزین کنید.
2. `assets/css/editor-seo.css` و `assets/js/editor-seo.js` را جایگزین کنید.
3. ویوی `views/tab-seo-engine.php` (یا مسیر فعلی که این view را include می‌کند) را جایگزین کنید.
4. Cache را پاک کنید و یک پست را در ادیتور باز کنید.

> نکته: اگر sidebar هنوز در Gutenberg نمایش داده نمی‌شود، باید جایی (مثلاً در `Bankai_Admin_Menu` یا یک meta box) با `bankai_render_view('tab-seo-engine.php')` آن را رندر کنید، یا یک PluginSidebar React جداگانه اضافه شود. نسخه فعلی Alpine برای صفحه ویرایش پست (classic و block) آماده است.

---

## Update – Meta box + Gutenberg button (v1.1.1)

### علت دیده نشدن دکمه
فقط assets لود می‌شد؛ **هیچ meta box یا PluginSidebar** ثبت نشده بود.

### اصلاحات
- `class-editor-seo.php`: ثبت meta box در ستون side با اولویت high برای همه post typeهای public
- `editor-seo-gutenberg.js`: دکمه و آیکون زیبا در Gutenberg (PluginSidebar + More menu)
- `editor-seo-ui.css`: هدر گرادیان meta box + استایل دکمه Gutenberg
- `functions.php`: نسخه سخت‌شده helpers
- `tab-seo-engine.php`: پشتیبانی از `$post_id` پاس‌داده‌شده از `bankai_render_view`

### مسیر جایگزینی در پلاگین
```
bankai-core/
├── functions.php                          ← جایگزین
├── inc/class-editor-seo.php               ← جایگزین
├── inc/meta/class-post-seo-meta.php       ← جایگزین
├── inc/modules/class-seo-engine.php       ← جایگزین
├── views/tab-seo-engine.php               ← جایگزین
└── assets/
    ├── css/editor-seo.css                 ← جایگزین
    ├── css/editor-seo-ui.css              ← جدید
    ├── js/editor-seo.js                   ← جایگزین
    └── js/editor-seo-gutenberg.js         ← جدید
```

`bankai-core.php` نیاز به تغییر ندارد.
