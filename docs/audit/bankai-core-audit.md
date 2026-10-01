# Bankai Core Plugin Architecture & Audit Report

**Generated:** Phase 0 Audit
**Target Plugin:** `plugins/bankai-core`

---

## 1. Current Architecture Map & Module Dependencies

`plugins/bankai-core` is a modular WordPress plugin structured as follows:

- **Core Plugin Entry (`bankai-core.php`):**
  - Defines constants (`BANKAI_CORE_PATH`, `BANKAI_CORE_URL`, `BANKAI_CORE_VERSION`).
  - Bootstraps main classes in `inc/`: `Bankai_Admin_Menu`, `Bankai_REST_API`, `Bankai_Dashboard_Stats`, `Bankai_Editor_SEO`, etc.
  - Loads modules in `inc/modules/`:
    - `Bankai_SEO_Engine` / `Bankai_SEO_Sitewide` / `Bankai_SEO_Integrations`
    - `Bankai_AI_Studio`
    - `Bankai_Speed_Cache`
    - `Bankai_Media_Watermark`
    - `Bankai_Jalali_Calendar`, `Bankai_LLMs_Txt`, `Bankai_Theme_Kits`, `Bankai_Settings_License`

- **Database & Options:**
  - `bankai_core_settings` storing global module options.
  - `bankai_post_seo_*` meta fields storing post SEO state.
  - `bankai_ai_settings` storing API keys and model configurations.

- **Admin UI & Assets:**
  - PHP views in `views/admin/`. Currently uses Alpine.js (`assets/js/alpine.min.js`) and HTMX (`assets/js/htmx.min.js`).
  - CSS stylesheets in `assets/css/` (`bankai-admin.css`, `editor-seo-ui.css`).
  - Gutenberg sidebar integration in `assets/js/editor-seo-gutenberg.js`.

---

## 2. Identified Security Issues, Bugs, and Technical Debt

### 2.1 Security & Encryption
- **Plaintext API Keys in Options:** API keys for AI providers (OpenAI, Gemini, OpenRouter, etc.) are currently stored as plain text or weakly encoded strings in `wp_options`.
- **Encryption Requirement Solution:** Encryption/decryption must use `sodium_crypto_secretbox` (libsodium) with AES-256-GCM fallback via OpenSSL. Encryption key must be derived from `BANKAI_ENCRYPTION_KEY` constant if defined in `wp-config.php`, or HKDF-derived using WordPress salts (`AUTH_KEY` + `SECURE_AUTH_KEY`).

### 2.2 Performance & Autoload Overhead
- **Autoload Options:** Global option `bankai_core_settings` is autoloaded on every request, including frontend requests where admin-only options are unnecessary.
- **Unused Asset Enqueues:** Alpine.js and HTMX assets are enqueued across admin pages without scope isolation.
- **Legacy UI Overhead:** Double dependencies (Alpine.js + HTMX) increase bundle overhead and create race conditions with Gutenberg React components.

### 2.3 Editor SEO & Worker Isolation
- **Main Thread SEO Calculations:** Heavy DOM analysis and Persian text normalization (zero-width non-joiner, Persian digits, stop-words) currently execute on the main thread, causing typing lag on large documents.
- **Internal Link Scanning:** Database scanning for internal links lacks server-side cached indexing, triggering expensive queries on post editing.

---

## 3. Prioritized List of Improvements (Impact vs. Effort)

| Improvement | Category | Impact | Effort | Priority |
| :--- | :--- | :--- | :--- | :--- |
| **Vanilla WP React / CSS Variables UI System** | UI/UX | High | High | P0 |
| **libsodium/OpenSSL HKDF Key Encryption Layer** | Security | High | Medium | P0 |
| **Provider Abstraction Architecture for AI Studio** | AI / Core | High | High | P0 |
| **Web Worker Real-time Persian SEO Engine** | Editor SEO | High | Medium | P0 |
| **ActionScheduler / Async Batch Image Watermarking** | Media | Medium | Medium | P1 |
| **Server-side Internal Link Indexing with Cache** | SEO Engine | High | Medium | P1 |
| **Auto-detection & Disabling Schema Conflicts** | SEO Engine | Medium | Low | P2 |

---

## 4. Phase Execution Plan & Technical Blueprint

### Phase 1: Admin Panel UI/UX Redesign
- Replace Alpine.js/HTMX UI with custom vanilla `@wordpress/element` (WordPress-native React) components styled via scoped CSS Variables (`.bankai-`).
- Build design tokens matching Dark Navy / Slate with modern purple/blue accents and full RTL support.
- Scoped CSS variables ensuring complete style isolation without conflicting with WP core styles or third-party plugins.

### Phase 2: Core Modules Review & Enhancement
- **AI Studio:**
  - Implement Provider Abstraction Interface (`complete`, `stream`, `listModels`, `validateKey`, `estimateCost`).
  - Support 8 providers in exact order: OpenRouter, Google Gemini, OpenAI, Anthropic Claude, DeepSeek, Hugging Face, Groq, Cloudflare Workers AI.
  - Implement libsodium encryption layer with HKDF key derivation from WP salts and fallback to `BANKAI_ENCRYPTION_KEY`.
  - Externalize prompt templates layer with variable placeholders and system roles.
- **SEO / Schema:**
  - Infinite auto-load article listing with custom pagination, sorting, and view counter.
  - Global fixed SEO keywords with pin badge (📌) SVG integration.
  - Auto conflict resolution with Yoast / Rank Math schema outputs.
- **Optimize & Media:**
  - Integration with `Action Scheduler` (or packaged library fallback) for async batch watermarking and WebP conversion.
  - Memory limit safety checks for Imagick/GD.

### Phase 3: Real-Time SEO Sidebar in Post Editor
- Implement Gutenberg `PluginSidebar` / `PluginDocumentSettingPanel` utilizing `@wordpress/data` (`core/editor` selectors).
- Offload heavy content analysis (Persian normalization `\u200c`, character count, density, readability) to a Web Worker with `requestIdleCallback` fallback.
- Server-side cached internal link indexing updated on `save_post`.
- Live streaming AI suggestions for Title, Meta, Alt text, and FAQ Schema.

---

## 5. Risk Assessment & Migration Strategy

1. **Option Key Backward Compatibility:** Existing settings in `bankai_core_settings` will be migrated transparently on plugin update without loss of existing user configurations.
2. **Fallback Safety for Encryption:** If encryption keys change or decay, API fields will display "Re-enter API Key" without breaking admin views or throwing fatal errors.
3. **Editor Fallback:** Classic Editor fallback mode provided via lightweight script if Gutenberg React environment is absent.
