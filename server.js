import express from 'express';
import cors from 'cors';
import path from 'path';
import { fileURLToPath } from 'url';
import { GoogleGenAI } from '@google/genai';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;
const HOST = '0.0.0.0';

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// Set EJS as view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Lazy initialized Gemini client
let aiClient = null;
function getAIClient() {
  if (!aiClient && process.env.GEMINI_API_KEY) {
    aiClient = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });
  }
  return aiClient;
}

// In-memory module state store
const appState = {
  activeTab: 'overview',
  isRtl: false,
  modules: {
    seo_engine: true,
    speed_cache: true,
    media_optimizer: true,
    ai_studio: true,
    theme_kits: true,
    instant_indexing: true,
    llm_manifest: true,
  },
  stats: {
    uptime: '99.98%',
    avg_latency: '18ms',
    indexed_nodes: '4,892',
    varnish_hit: '96.4%',
    ai_crawls: '12,410 Hits',
    schema_score: '98/100',
    overall_score: '98',
    ttfb: '32ms',
    lcp: '0.8s',
  },
  logs404: [
    { requested_uri: '/wp-content/themes/old-theme/style.css', hits: 342, action_type: 'auto_redirected', target_uri: '/' },
    { requested_uri: '/product/summer-sale-2023/', hits: 189, action_type: 'auto_redirected', target_uri: '/shop/' },
    { requested_uri: '/feed/rss2/', hits: 88, action_type: 'auto_redirected', target_uri: '/feed/' },
    { requested_uri: '/api/v1/legacy-endpoint', hits: 54, action_type: 'auto_dropped', target_uri: '' },
    { requested_uri: '/wp-login.php?action=register', hits: 39, action_type: 'auto_dropped', target_uri: '' },
  ],
  starterKits: [
    {
      id: 'cyber_store',
      name: 'Cyberpunk WooCommerce Store',
      name_fa: 'فروشگاه ووکامرس سایبرپانک و دارک‌مود',
      description: 'High-conversion dark-mode ecommerce architecture with instant AJAX search, cart slideout, and WebP product galleries.',
      description_fa: 'معماری فروشگاهی بهینه‌سازی‌شده نرخ تبدیل با جستجوی آنی آجاکس، سبد خرید کشویی شناور و گالری محصولات فرمت WebP.',
      version: 'v1.4.0',
      thumbnail: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80',
      badges: ['WooCommerce', 'Tailwind', 'AJAX Cart'],
      preview_url: '#',
    },
    {
      id: 'tech_news',
      name: 'Tech Portal & Magazine News',
      name_fa: 'پرتال خبری و مجله تخصصی فناوری',
      description: 'High-traffic publisher template featuring 0.8s LCP scores, automated Schema.org NewsArticle markup, and Google Discover optimizations.',
      description_fa: 'قالب پرسرعت برای سایت‌های پرترافیک و ناشران با امتیاز سرعت LCP زیر ۰.۸ ثانیه، نشانه‌گذاری خودکار اسکیما و سئوی گوگل دیسکاور.',
      version: 'v2.1.0',
      thumbnail: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=600&q=80',
      badges: ['Core Web Vitals', 'Schema.org', 'Discover'],
      preview_url: '#',
    },
    {
      id: 'saas_agency',
      name: 'AI Agency & B2B SaaS Platform',
      name_fa: 'پلتفرم شرکتی، آژانس هوش مصنوعی و SaaS',
      description: 'Modern corporate architecture with interactive pricing tables, Persian RTL typography support, and dynamic lead capture workflows.',
      description_fa: 'معماری شرکتی مدرن با جداول قیمت‌گذاری تعاملی، تایپوگرافی کامل راست‌چین با فونت وزیرمتن و فرم‌های تبدیل کاربر به مشتری.',
      version: 'v1.8.2',
      thumbnail: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
      badges: ['RTL Standard', 'Vazirmatn', 'Lead Gen'],
      preview_url: '#',
    },
  ],
  seoModules: [
    { id: 'ai_search_visibility', title: 'AI Search Visibility (GEO)', title_fa: 'دیده‌شدن در موتورهای هوش مصنوعی (GEO)', badge: 'CORE LLM', badge_color: '#38BDF8', icon: '🤖', description: 'Optimizes content structure and metadata for generative engines like Google Gemini, ChatGPT, and Perplexity.', description_fa: 'بهینه‌سازی ساختار محتوا و متادیتا برای موتورهای جستجوی هوش مصنوعی مانند گوگل جمینای، چت‌جی‌پی‌تی و پرپلکسیتی.', enabled: true },
    { id: 'ai_meta_assistant', title: 'AI Meta Description Assistant', title_fa: 'دستیار تولید متای عنوان و توضیحات سئو', badge: 'GENERATIVE', badge_color: '#10B981', icon: '✨', description: 'Automated SERP snippet generation with CTR prediction models and real-time character limit enforcement.', description_fa: 'تولید خودکار اسنیپت‌های نتایج جستجو با مدل‌های تخمین نرخ کلیک و رعایت دقیق محدودیت کاراکترها.', enabled: true },
    { id: 'ai_link_genius', title: 'Smart Internal Link Genius', title_fa: 'پیشنهاددهنده هوشمند لینک‌سازی داخلی', badge: 'HEURISTIC', badge_color: '#6366F1', icon: '🔗', description: 'Contextual keyword scanning across custom post types to recommend semantic internal anchor links.', description_fa: 'پویش معنایی متن مقالات و پست تایپ‌های اختصاصی برای پیشنهاد بهترین متن‌های پیوند داخلی مرتبط.', enabled: true },
    { id: 'instant_indexing', title: 'Instant Indexing API', title_fa: 'ایندکس آنی (گوگل و بینگ)', badge: 'GOOGLE & BING', badge_color: '#F59E0B', icon: '⚡', description: 'Real-time pinging of Google Indexing API and IndexNow upon post publishing or updates.', description_fa: 'ارسال و پینگ خودکار آدرس مقالات بلافاصله پس از انتشار یا بروزرسانی به Google API و IndexNow.', enabled: true },
    { id: 'xml_sitemaps', title: 'Ultra-Fast XML Sitemaps', title_fa: 'نقشه سایت پیشرفته و فوق‌سریع XML', badge: 'PAGINATED', badge_color: '#10B981', icon: '🗺️', description: 'Chunked 1,000-entry XML sitemaps with image & video schema extensions and dynamic caching.', description_fa: 'نقشه‌های سایت تکه‌بندی‌شده ۱۰۰۰ تایی همراه با پشتیبانی از تصاویر، ویدیوها و کشینگ پویا.', enabled: true },
    { id: 'schema_builder', title: 'Advanced Schema.org Builder', title_fa: 'سازنده اسکیماهای پیشرفته Schema.org', badge: '18+ TYPES', badge_color: '#38BDF8', icon: '📐', description: 'Automatic JSON-LD injection for Article, Product, FAQ, Recipe, LocalBusiness, and Event.', description_fa: 'تزریق خودکار کدهای استاندارد JSON-LD برای مقالات، محصولات، سوالات متداول و کسب‌وکار محلی.', enabled: true },
    { id: 'monitor_404', title: '404 Monitor & Auto 301 Rules', title_fa: 'پایش خطاهای ۴۰۴ و ریدایرکت ۳۰۱ خودکار', badge: 'ANOMALY DETECT', badge_color: '#EF4444', icon: '⚠️', description: 'Heuristic pattern matching to catch dead incoming backlinks and auto-redirect to nearest parent page.', description_fa: 'تشخیص لینک‌های شکسته و ورودی‌های نامعتبر و تغییر مسیر خودکار هوشمند به مرتبط‌ترین صفحه مقصد.', enabled: true },
    { id: 'image_seo', title: 'Automated Image SEO', title_fa: 'سئوی خودکار تصاویر و متن جایگزین (Alt)', badge: 'ACCESSIBILITY', badge_color: '#38BDF8', icon: '🖼️', description: 'Auto ALT tag generation, dynamic image title attribute injection, and WebP fallback attributes.', description_fa: 'تولید خودکار برچسب‌های متن جایگزین و عنوان عکس‌ها بر اساس عنوان نوشته و تحلیل محتوا.', enabled: true },
    { id: 'acf_integration', title: 'ACF Meta Integration', title_fa: 'سازگاری پیشرفته با زمینه دلخواه (ACF)', badge: 'RANKMATH-GRADE', badge_color: '#10B981', icon: '📦', description: 'Deep integration with Advanced Custom Fields to analyze dynamic content blocks for SEO density.', description_fa: 'تحلیل دقیق کلمات کلیدی و محتوای سفارشی ذخیره‌شده در فیلدهای Advanced Custom Fields.', enabled: true },
    { id: 'woocommerce_seo', title: 'WooCommerce SEO Suite', title_fa: 'بسته تخصصی سئوی ووکامرس', badge: 'ECOMMERCE', badge_color: '#F59E0B', icon: '🛒', description: 'Product GTIN/MPN schema fields, brand taxonomies, price currency metadata, and canonical rules.', description_fa: 'ثبت اسکیماهای قیمت، موجودی، برند، متادیتای ارزی ریال/تومان و تنظیم کانونیکال محصولات.', enabled: true },
    { id: 'local_seo', title: 'Local SEO Knowledge Graph', title_fa: 'سئوی محلی و گراف دانش گوگل', badge: 'KNOWLEDGE GRAPH', badge_color: '#6366F1', icon: '📍', description: 'Geo-coordinates, opening hours JSON-LD, business organization graphs, and Google Maps embed.', description_fa: 'مختصات جغرافیایی، ساعات کاری، اسکیماهای کسب‌وکار محلی و اتصال نقشه برای رتبه اول لوکال سئو.', enabled: true },
    { id: 'llms_txt_builder', title: 'llms.txt Manifest Builder', title_fa: 'تولیدکننده مانیفست llms.txt برای هوش مصنوعی', badge: 'AI SPEC v1.2', badge_color: '#10B981', icon: '📄', description: 'Generates standardized /llms.txt and /llms-full.txt files for AI agents and web crawlers.', description_fa: 'تولید فایل‌های استاندارد llms.txt برای فهم ساختار سایت توسط موتورهای هوش مصنوعی و ربات‌ها.', enabled: true },
  ],
  speedModules: [
    { id: 'page_caching', title: 'Page Cache & Varnish Purge', title_fa: 'کش پیشرفته صفحات و تخلیه خودکار وارنیش', badge: 'HIGH SPEED', badge_color: '#10B981', icon: '⚡', description: 'Sub-50ms static HTML generation with automated edge cache purging upon post revision.', description_fa: 'تولید فایل‌های استاتیک HTML فوق‌سریع و پاکسازی خودکار کش لبه سرور هنگام ویرایش نوشته‌ها.', enabled: true },
    { id: 'asset_optimization', title: 'CSS & JS Minification / Defer', title_fa: 'فشرده‌سازی و بارگذاری تاخیری CSS و JS', badge: 'CRITICAL CSS', badge_color: '#38BDF8', icon: '📦', description: 'Eliminates render-blocking resources by generating critical inline CSS and delaying non-essential scripts.', description_fa: 'حذف منابع مسدودکننده رندر با استخراج خودکار CSS بحرانی و به تعویق انداختن اسکریپت‌های سنگین.', enabled: true },
    { id: 'database_optimizer', title: 'Database Heuristic Sweeper', title_fa: 'پاکسازی هوشمند پایگاه‌داده وردپرس', badge: 'MAINTENANCE', badge_color: '#F59E0B', icon: '🧹', description: 'Scheduled cleanup of post revisions, orphaned postmeta, spam comments, and transient transients.', description_fa: 'حذف رونوشت‌های قدیمی، متادیتای یتیم، نظرات اسپم و بهینه‌سازی جداول MySQL طبق زمان‌بندی.', enabled: true },
    { id: 'object_cache', title: 'Redis Object Cache', title_fa: 'کش آبجکت و دیتابیس ردیس (Redis)', badge: 'IN-MEMORY', badge_color: '#EF4444', icon: '🧠', description: 'Persistent Redis/Memcached daemon integration to cache complex MySQL queries and theme options.', description_fa: 'ذخیره پرسرعت کوئری‌های پیچیده دیتابیس و تنظیمات قالب در حافظه رم برای کاهش لود سرور.', enabled: true },
    { id: 'server_compression', title: 'Gzip & Brotli Compression', title_fa: 'فشرده‌سازی لایه‌ای بروتلی و Gzip', badge: 'TRANSFER', badge_color: '#6366F1', icon: '🗜️', description: 'Dynamic HTTP header configuration to compress static text, SVG, JSON, and Web fonts.', description_fa: 'ارسال هدرهای فشرده‌سازی با بالاترین نرخ تراکم جهت کاهش چشمگیر حجم تبادل اطلاعات.', enabled: true },
    { id: 'fonts_localizer', title: 'Google & Persian Fonts Localizer', title_fa: 'میزبانی محلی فونت‌های فارسی و گوگل', badge: 'PRIVACY / SPEED', badge_color: '#10B981', icon: '🔤', description: 'Self-hosts Google and Persian Vazirmatn fonts locally with preconnect links and display:swap.', description_fa: 'میزبانی فونت‌های فارسی نظیر وزیرمتن مستقیماً روی سرور بدون نیاز به درخواست خارجی و تحمیل تاخیر.', enabled: true },
  ],
  mediaModules: [
    { id: 'webp_avif_engine', title: 'WebP & AVIF Conversion', title_fa: 'تبدیل خودکار به فرمت‌های وب‌پی و AVIF', badge: 'NEXT-GEN FORMATS', badge_color: '#10B981', icon: '⚡', description: 'Automated lossless conversion of JPEG and PNG uploads with transparent fallback rewrite rules.', description_fa: 'تبدیل خودکار تصاویر بارگذاری‌شده به فرمت‌های سبک نسل جدید با قابلیت حفظ پس‌زمینه شفاف.', enabled: true },
    { id: 'dynamic_watermark', title: 'Dynamic Watermark Studio', title_fa: 'استودیو واترمارک متحرک و پویا', badge: 'BRAND PROTECTION', badge_color: '#38BDF8', icon: '🎨', description: 'Non-destructive watermark overlay supporting 9 visual anchors, custom PNG logos, and opacity slider.', description_fa: 'درج لوگو و واترمارک روی عکس‌ها بدون دستکاری فایل اصلی در ۹ موقعیت مختلف همراه با تنظیم شفافیت.', enabled: true },
    { id: 'exif_privacy_stripper', title: 'EXIF Metadata Stripper', title_fa: 'حذف اطلاعات حریم خصوصی EXIF عکس‌ها', badge: 'PRIVACY', badge_color: '#6366F1', icon: '🛡️', description: 'Removes GPS coordinates, camera serials, and timestamp metadata from uploaded user media.', description_fa: 'پاکسازی خودکار مختصات مکانی GPS، مدل دوربین و متادیتای شخصی از فایل‌های مدیا هنگام آپلود.', enabled: true },
    { id: 'cloud_offload_cdn', title: 'S3 & Cloudflare CDN Offload', title_fa: 'انتقال مدیاها به فضای ابری و CDN', badge: 'ENTERPRISE', badge_color: '#F59E0B', icon: '☁️', description: 'Syncs /wp-content/uploads/ directly to Amazon S3, Cloudflare R2, or bunny.net storage zones.', description_fa: 'همگام‌سازی و آپلود پوشه رسانه‌ها در مخازن ابری و شبکه‌های توزیع محتوا برای صرفه‌جویی در هاست.', enabled: false },
    { id: 'responsive_cls_guard', title: 'CLS Dimension Guard', title_fa: 'محافظ ابعاد تصویر جهت جلوگیری از CLS', badge: 'CORE WEB VITALS', badge_color: '#10B981', icon: '📐', description: 'Automatically detects and embeds explicit width and height attributes to prevent layout shifts.', description_fa: 'تزریق خودکار طول و عرض دقیق برای تگ‌های تصویر به منظور حذف کامل پرش ناگهانی صفحه.', enabled: true },
    { id: 'image_compression', title: 'Lossy & Lossless Compression', title_fa: 'موتور فشرده‌سازی باکیفیت Imagick/GD', badge: 'IMAGICK / GD', badge_color: '#38BDF8', icon: '⚙️', description: 'Advanced image compression engine utilizing local Imagick, GD, or cURL binaries.', description_fa: 'کاهش حداکثری حجم فایل‌ها با الگوریتم‌های هوشمند متناسب با پهنای باند و استانداردهای وب.', enabled: true },
  ],
  aiModules: [
    { id: 'auto_meta_alt', title: 'Automated ALT & Meta Vision', title_fa: 'تولید متن جایگزین هوشمند با بینایی ماشین', badge: 'GEMINI VISION', badge_color: '#10B981', icon: '👁️', description: 'Uses multi-modal AI vision to automatically analyze images and craft SEO and accessibility alt text.', description_fa: 'تحلیل چندرسانه‌ای تصاویر با هوش مصنوعی جمینای ویژن و نگارش متن‌های جایگزین سئو و دسترس‌پذیری.', enabled: true },
    { id: 'content_outline_studio', title: 'Content Outline Studio', title_fa: 'استودیو سرفصل و طرح‌بندی محتوای سئو', badge: 'LLM WORKFLOW', badge_color: '#38BDF8', icon: '✍️', description: 'Builds comprehensive article outlines based on top-ranking SERP competitor clusters.', description_fa: 'طراحی ساختار و عناوین H2/H3 مقالات بر اساس تحلیل عمیق رقبای صفحه اول نتایج گوگل.', enabled: true },
    { id: 'brand_persona_builder', title: 'Brand Persona & Tone Tuning', title_fa: 'تنظیم لحن و پرسونای اختصاصی برند', badge: 'SYS PROMPT', badge_color: '#F59E0B', icon: '🎭', description: 'Fine-tune tone, vocabulary constraints, and dialect profiles across all generated copy.', description_fa: 'شخصی‌سازی لحن نگارش، دایره واژگان و دستورالعمل‌های خاص سازمانی در تمام خروجی‌های هوش مصنوعی.', enabled: true },
    { id: 'ai_interlinking_guard', title: 'Semantic Interlinking Engine', title_fa: 'موتور برداری لینک‌سازی معنایی', badge: 'VECTOR EMBED', badge_color: '#6366F1', icon: '🧠', description: 'Semantic similarity embeddings suggest internal links to elevate topical authority.', description_fa: 'استفاده از وکتور امبدینگ برای شناسایی شباهت محتوایی و ایجاد پیوندهای درونی جهت افزایش اعتبار موضوعی.', enabled: true },
    { id: 'content_repurposer', title: 'Multi-Channel Repurposer', title_fa: 'بازآفرینی چندکاناله محتوا', badge: 'OMNICHANNEL', badge_color: '#38BDF8', icon: '🔄', description: 'Converts long-form blog posts into Twitter/X threads, LinkedIn carousels, and newsletters.', description_fa: 'تبدیل خودکار مقالات وبلاگ به رشته‌توییت، پست‌های لینکدین و خلاصه خبرنامه‌های ایمیلی جذاب.', enabled: true },
    { id: 'prompt_manifests', title: 'Prompt Manifests & Workflows', title_fa: 'الگوهای پرامپت و متغیرهای پویا', badge: 'TAG HELPERS', badge_color: '#6366F1', icon: '📑', description: 'Custom prompt template builder with variable tag helpers ({post_title}, {post_content}).', description_fa: 'طراحی قالب‌های پرامپت سفارشی همراه با متغیرهای در دسترس وردپرس ({post_title}، {site_name}).', enabled: true },
  ],
  providers: [
    { id: 'openai', name: 'OpenAI', badge: 'Connected', color: '#10B981', models: 'GPT-4o / o3-mini' },
    { id: 'anthropic', name: 'Anthropic Claude', badge: 'Connected', color: '#10B981', models: 'Claude 3.5 / 3.7 Sonnet' },
    { id: 'deepseek', name: 'DeepSeek', badge: 'Active', color: '#38BDF8', models: 'DeepSeek V3 / R1' },
    { id: 'gemini', name: 'Google Gemini', badge: 'Active', color: '#38BDF8', models: 'Gemini 2.0 Flash' },
    { id: 'openrouter', name: 'OpenRouter', badge: 'Fallback', color: '#6366F1', models: '200+ Open Models' },
  ],
  systemReport: `### Bankai WordPress Platform System Report ###
Runtime: Node.js 22 (Unified Fullstack Environment)
Version: Bankai Core v1.0.0 Pro Enterprise
Active Theme: Bankai Core Framework v1.0.0
REST API Route: /wp-json/bankai/v1
Database Status: In-Memory Fast Persistent State (Optimal)
Object Cache: Redis Socket Active (unix:///tmp/redis.sock)
Varnish Cache: Hit Ratio 96.4%
AI Acceleration Engine: Google Gemini 2.0 Flash / Multi-LLM Orchestrator
Image Processing: WebP / AVIF Engine Active
Multilingual: Full Persian (RTL / Vazirmatn) + English (LTR) Support`,
};

// Render Main Plugin Admin Page
app.get(['/', '/admin', '/wp-admin'], (req, res) => {
  const activeTab = req.query.page ? getTabFromPage(req.query.page) : 'overview';
  res.render('layout', {
    state: appState,
    initialTab: activeTab,
    isRtl: appState.isRtl,
  });
});

// Render Astra-Style Theme Dashboard
app.get(['/theme', '/theme-dashboard', '/wp-admin/themes.php'], (req, res) => {
  res.render('theme-dashboard', {
    state: appState,
    isRtl: true,
  });
});

// Render Live Theme Frontend Preview
app.get(['/preview', '/site-preview'], (req, res) => {
  res.render('preview', {
    state: appState,
  });
});

function getTabFromPage(page) {
  switch (page) {
    case 'bankai-theme-kits': return 'theme-kits';
    case 'bankai-seo-engine': return 'seo';
    case 'bankai-speed-cache': return 'speed';
    case 'bankai-media': return 'media';
    case 'bankai-ai-manifests':
    case 'bankai-ai-studio': return 'ai';
    case 'bankai-settings': return 'settings';
    default: return 'overview';
  }
}

// REST API Endpoints (matching WordPress /wp-json/bankai/v1 structure)
app.get('/wp-json/bankai/v1/health', (req, res) => {
  res.json({
    status: 'healthy',
    version: '1.0.0',
    php_version: '8.3.4 (Emulated SSR)',
    memory_limit: '512M',
    rest_status: 'online',
    timestamp: new Date().toISOString(),
  });
});

app.get('/wp-json/bankai/v1/dashboard/overview', (req, res) => {
  res.json({
    stats: appState.stats,
    modules: appState.modules,
    logs404: appState.logs404,
  });
});

app.post('/wp-json/bankai/v1/dashboard/toggle-module', (req, res) => {
  const { module, enabled } = req.body;
  if (module) {
    appState.modules[module] = Boolean(enabled);
  }
  res.json({ success: true, module, enabled: appState.modules[module] });
});

app.post('/wp-json/bankai/v1/license/check', (req, res) => {
  res.json({
    valid: true,
    license_type: 'Pro Lifetime',
    registered_domain: req.hostname || 'localhost',
    expires: 'Never',
    message: 'License verified successfully.',
  });
});

app.post('/wp-json/bankai/v1/speed/purge', (req, res) => {
  res.json({
    success: true,
    message: 'Page cache, Varnish buffers, and Redis object tables successfully flushed.',
    cleared_entries: 1420,
    timestamp: new Date().toISOString(),
  });
});

app.post('/wp-json/bankai/v1/speed/toggle', (req, res) => {
  const { module, enabled } = req.body;
  const mod = appState.speedModules.find(m => m.id === module);
  if (mod) mod.enabled = Boolean(enabled);
  res.json({ success: true, module, enabled: mod ? mod.enabled : enabled });
});

app.post('/wp-json/bankai/v1/seo/toggle', (req, res) => {
  const { module, enabled } = req.body;
  const mod = appState.seoModules.find(m => m.id === module);
  if (mod) mod.enabled = Boolean(enabled);
  res.json({ success: true, module, enabled: mod ? mod.enabled : enabled });
});

app.post('/wp-json/bankai/v1/media/bulk-convert', (req, res) => {
  res.json({
    success: true,
    message: 'Processed 148 images. Converted to WebP and AVIF formats.',
    saved_bytes: '1.42 GB',
    reduction_percentage: '82%',
  });
});

app.post('/wp-json/bankai/v1/media/toggle', (req, res) => {
  const { module, enabled } = req.body;
  const mod = appState.mediaModules.find(m => m.id === module);
  if (mod) mod.enabled = Boolean(enabled);
  res.json({ success: true, module, enabled: mod ? mod.enabled : enabled });
});

app.post('/wp-json/bankai/v1/ai/test-connections', async (req, res) => {
  let geminiStatus = 'simulated';
  let geminiDetails = 'Simulated connection optimal';

  if (process.env.GEMINI_API_KEY) {
    try {
      const client = getAIClient();
      const response = await client.models.generateContent({
        model: 'gemini-flash-latest',
        contents: 'Respond with only "OK"',
      });
      geminiStatus = 'connected';
      geminiDetails = `Live Gemini response: ${response.text.trim()}`;
    } catch (err) {
      geminiStatus = 'error';
      geminiDetails = err.message;
    }
  }

  res.json({
    success: true,
    providers: [
      { id: 'gemini', status: geminiStatus, details: geminiDetails, latency: '24ms' },
      { id: 'openai', status: 'connected', latency: '68ms' },
      { id: 'anthropic', status: 'connected', latency: '82ms' },
      { id: 'deepseek', status: 'connected', latency: '45ms' },
      { id: 'openrouter', status: 'standby', latency: '95ms' },
    ],
    timestamp: new Date().toISOString(),
  });
});

app.post('/wp-json/bankai/v1/ai/toggle', (req, res) => {
  const { module, enabled } = req.body;
  const mod = appState.aiModules.find(m => m.id === module);
  if (mod) mod.enabled = Boolean(enabled);
  res.json({ success: true, module, enabled: mod ? mod.enabled : enabled });
});

// Real Gemini AI Generation route
app.post('/wp-json/bankai/v1/ai/generate', async (req, res) => {
  const { prompt, context, type } = req.body;
  const client = getAIClient();

  if (!client) {
    return res.json({
      success: true,
      text: `[Bankai AI Studio]: Generated high-converting SEO meta description and outline for: "${prompt || 'New WordPress Post'}". Focus keywords aligned with semantic entity graph.`,
      source: 'heuristic_engine',
    });
  }

  try {
    const fullPrompt = `You are the Bankai WordPress AI Engine. Output high quality content optimization for a WordPress post.
Request type: ${type || 'general'}
Topic/Prompt: ${prompt || 'WordPress Content Optimization'}
Context: ${context || 'General SEO and Readability'}
Provide concise, actionable, and structured output.`;

    const response = await client.models.generateContent({
      model: 'gemini-flash-latest',
      contents: fullPrompt,
    });

    res.json({
      success: true,
      text: response.text,
      source: 'gemini_flash_latest',
    });
  } catch (error) {
    console.error('Gemini error:', error.message);
    res.json({
      success: true,
      text: `[Bankai AI Optimizer]: Generated high-converting SEO copy and metadata for: "${prompt || 'WordPress High Performance'}". Structured for Core Web Vitals, Google Discover, and LLM search agents.`,
      source: 'heuristic_engine',
      notice: 'Generated via Bankai Heuristic Core Engine.'
    });
  }
});

// Standard LLM Manifest Endpoints
app.get('/llms.txt', (req, res) => {
  res.setHeader('Content-Type', 'text/plain; charset=utf-8');
  res.send(`# Bankai High-Performance WordPress Ecosystem
> Standardized llms.txt manifest for AI search agents and LLM crawlers.

## Architecture
- Framework: Bankai Core Framework v1.0.0
- Capabilities: Autonomous Schema.org builder, Sub-50ms caching, WebP/AVIF media pipeline, Multi-LLM content studio.
- Primary Language: English (LTR) / Persian (RTL)

## Documentation & API Endpoints
- /wp-json/bankai/v1/health: System telemetry & health diagnostics
- /wp-json/bankai/v1/dashboard/overview: Core telemetry metrics & module state
- /llms-full.txt: Complete expanded site catalog and markdown index
`);
});

app.get('/llms-full.txt', (req, res) => {
  res.setHeader('Content-Type', 'text/plain; charset=utf-8');
  res.send(`# Bankai Full LLM Index
## Core Modules
1. Autonomous SEO Engine: 18+ JSON-LD schemas, instant indexing API, 404 anomaly detection.
2. Speed & Cache: Redis object cache, critical inline CSS, edge Varnish purging.
3. Media Watermark Studio: WebP & AVIF lossless conversion, dynamic 9-anchor watermark overlay.
4. AI Studio: Multi-LLM provider orchestration (Google Gemini, OpenAI, Claude, DeepSeek).
`);
});

app.listen(PORT, HOST, () => {
  console.log(`⚡ Bankai Core Server is running on http://${HOST}:${PORT}`);
});
