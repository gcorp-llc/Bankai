<p align="center">
  <img src="./logo.jpg" alt="Bankai Logo" width="133" style="border-radius: 16px;" />
</p>

# Bankai — High-Performance WordPress Ecosystem

**Developer:** GCORP LLC  
**Architecture:** Server-Side Rendered (SSR) Dual-Component Monorepo (Theme + Modular Core Plugin)
**License:** GPLv2 or later  

Bankai is a structured dual-component ecosystem for WordPress engineered for maximum speed, advanced SEO, optimized media management, and integrated AI capabilities.

---

## Architecture (Alpine.js + HTMX + Native PHP SSR)

To guarantee ultimate execution speed, eliminate white screen (WSOD) errors completely, and remove heavy client-side bundler overhead, the Bankai Core admin suite is built using **Native PHP Server-Side Rendering (SSR) coupled with Alpine.js and HTMX**:

1. **Bankai Theme (`themes/bankai-theme`)**
   * Ultra-lightweight base theme without jQuery dependency.
   * Dynamic CSS variables for color palette and typography management.

2. **Bankai Core Plugin (`plugins/bankai-core`)**
   * **Admin Suite Architecture:** Direct HTML view template rendering via Native PHP (removing `@wordpress/scripts` and Webpack build requirements).
   * **Alpine.js:** Lightweight reactive UI client state management, instant tab navigation, dynamic module toggles, and notification toast feedback.
   * **HTMX / REST Client:** Asynchronous REST API interactions (`/wp-json/bankai/v1`) for cache purging, sitemap synchronization, and 404 redirect rules.
   * **Cyberpunk Dark Slate Theme:** Custom Dark Navy/Slate visual design with 100% RTL (Right-to-Left) and LTR language support.

---

## Directory Structure

```text
plugins/bankai-core/
├── bankai-core.php             <- Main plugin bootstrap
├── assets/                     <- Static isolated assets
│   ├── css/
│   │   └── bankai-admin.css    <- Dark Slate / Cyberpunk & RTL styling
│   └── js/
│       ├── alpine.min.js       <- Alpine.js v3.13 library
│       └── htmx.min.js         <- HTMX v1.9 library
├── includes/                   <- PHP Classes & backend logic
│   ├── class-admin-menu.php    <- Menu registration & asset loader
│   ├── class-rest-api.php      <- REST API endpoints
│   └── modules/                <- Independent modules (SEO, Speed, Media, AI)
└── templates/                  <- Direct PHP view templates
    └── admin/
        └── admin-dashboard.php <- Main SSR dashboard template
```

---

## Development & Testing Workflow

Testing and developing Bankai Core is zero-build and immediate:

1. Place `plugins/bankai-core` in your local WordPress `wp-content/plugins/` directory and activate it.
2. Navigate to `admin.php?page=bankai-core` in WP Admin.
3. The interface renders cleanly via PHP SSR without any JS compilation steps, eliminating blank screens.

---

## Release Packaging

To build production release zip packages:

```bash
npm run build:zip
```
