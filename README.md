<p align="center">
  <img src="./public/images/logo.jpg" alt="Bankai Logo" width="128" style="border-radius: 16px;" />
</p>

# Bankai Core — Modular WordPress Ecosystem & AI Studio

**Architecture:** Native PHP Server-Side Rendered (SSR) + Alpine.js v3 + HTMX + Tailwind CSS (#080C14 Cyberpunk Slate)
**Localization:** 100% Persian RTL Support with Local Vazirmatn Font Integration
**License:** GPLv2 or later  

Bankai Core is an enterprise-grade WordPress ecosystem engineered for maximum execution speed, dynamic SEO architecture, image optimization, dynamic watermarking, and multi-LLM AI capabilities.

---

## Directory Architecture & Clean-Slate Structure

```text
.
├── logo.jpg                         <- Ecosystem root logo fallback
├── includes/
│   └── class-admin-menu.php         <- Menu handler (Position 2), logo icon & asset enqueuer
├── public/                          <- Static Frontend Assets
│   ├── css/
│   │   └── bankai-admin.css         <- Tailwind CSS output, scoping (#080C14) & Vazirmatn font
│   ├── js/
│   │   ├── alpine.min.js            <- Alpine.js reactive framework
│   │   ├── htmx.min.js              <- HTMX AJAX engine
│   │   └── bankai-admin.js          <- Alpine state engine & tab router
│   ├── images/
│   │   └── logo.jpg                 <- Official menu icon & brand asset
│   └── fonts/
│       ├── Vazirmatn-Regular.ttf    <- Local Vazirmatn Regular font file
│       └── Vazirmatn-Bold.ttf       <- Local Vazirmatn Bold font file
└── views/                           <- PHP SSR Templates
    └── admin/
        ├── layout.php               <- Master container wrapper & x-show container
        ├── header.php               <- Header bar with status badges & telemetry
        ├── sidebar.php              <- Responsive navigation sidebar with icons & Persian labels
        ├── dashboard.php            <- View 1: Overview & Health
        ├── theme-kits.php           <- View 2: Theme & Starter Kits
        ├── seo-engine.php           <- View 3: Autonomous SEO Engine
        ├── speed-cache.php          <- View 4: Speed & Cache Engine
        ├── media-watermark.php      <- View 5: Media & Watermark Studio
        ├── ai-studio.php            <- View 6: AI Content Studio & LLMs
        └── settings-license.php     <- View 7: Settings, RBAC & License Hub
```

---

## Development & Local Testing Setup

1. Copy or link this repository into your WordPress installation (`wp-content/plugins/bankai-core/`).
2. Activate **Bankai Core** in WP Admin -> Plugins.
3. Access the dashboard from the 2nd position in the WP Admin sidebar ("Bankai Core").
4. Run PHP syntax validation across all views:
   ```bash
   for f in views/admin/*.php includes/*.php; do php -l "$f"; done
   ```

---

## Asset Compilation & Release Packaging

Build all workspace assets and package the production release archive:

```bash
npm run build
npm run build:zip
```
