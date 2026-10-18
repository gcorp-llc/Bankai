# Bankai SEO × Real AI v1

## چه کار می‌کند
دکمه‌های AI در پنل سئوی مقاله دیگر دمو نیستند. درخواست واقعی به `bankai_ai_seo_task` می‌رود.

### انتخاب موتور
- در مودال AI: انتخاب «موتور AI» از لیست کلیدهای ذخیره‌شده
- پیش‌فرض از استودیو هوش مصنوعی

### Fallback
اگر موتور انتخابی خطا بدهد یا کلید نداشته باشد، به‌ترتیب Gemini → OpenAI → Anthropic → DeepSeek → OpenRouter امتحان می‌شود.

### وظایف
- meta_title / meta_description
- focus_keyword / keywords
- rewrite / outline / alt_text / faq_schema

## فایل‌ها
- `inc/modules/class-ai-studio.php` — رمزنگاری کلید + fallback + پرامپت‌های دقیق
- `inc/class-editor-seo.php` — localize با aiProviders
- `assets/js/editor-seo.js` — generateAI واقعی
- `views/tab-seo-engine.php` — انتخابگر موتور در مودال

## نصب
کپی روی `wp-content/plugins/bankai-core/` و Activate.
ابتدا در استودیو AI حداقل یک کلید ذخیره و تست کنید.
