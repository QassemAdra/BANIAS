qassem-platform/
├─ apps/
│  ├─ site/                      # الواجهة العامة (Astro)
│  │  ├─ src/
│  │  │  ├─ pages/               # routes: /, /evidence, /explore, /articles, /submit
│  │  │  ├─ layouts/             # MainLayout, AdminLayout
│  │  │  ├─ components/          # UI components (Buttons, Cards, Forms)
│  │  │  ├─ sections/            # Home sections (Hero, Stats, LatestEvidence, MapPreview...)
│  │  │  ├─ features/            # evidence, articles, explore (feature-based modules)
│  │  │  ├─ styles/              # tokens.css, rtl.css, components.css
│  │  │  ├─ lib/                 # helpers: i18n, fetch, format dates, validation
│  │  │  └─ assets/              # demo images + icons
│  │  ├─ public/
│  │  │  └─ data/                # generated indexes: evidence-index.ar.json...
│  │  └─ astro.config.mjs
│  │
│  ├─ admin/                     # لوحة التحكم (Astro/React SPA) - WordPress-like
│     ├─ src/
│     │  ├─ pages/               # /admin routes
│     │  ├─ components/          # Sidebar, Topbar, Tables, Editor panels
│     │  ├─ features/            # evidence editor, inbox, appearance, settings
│     │  ├─ styles/
│     │  └─ lib/                 # auth, api client, form validation
│     └─ astro.config.mjs
│
├─ packages/
│  ├─ content-schema/            # schema validation for MD/JSON (shared)
│  ├─ ui-kit/                    # UI kit tokens + shared components (optional)
│  └─ shared/                    # shared utils (slugify, date, rtl helpers)
│
├─ workers/
│  ├─ api/                       # Cloudflare Worker (auth + R2 + GitHub PR)
│  │  ├─ routes/
│  │  │  ├─ admin.login.ts
│  │  │  ├─ admin.presignUpload.ts
│  │  │  ├─ admin.createPR.ts
│  │  │  ├─ submit.jsonOnly.ts   # /submit بدون رفع ملفات
│  │  │  └─ admin.pending.ts
│  │  ├─ lib/                    # jwt, cors, github, r2, validators
│  │  └─ index.ts
│  └─ wrangler.toml
│
├─ content/                      # محتوى الموقع (مصدر الحقيقة) = ملفات
│  ├─ evidence/
│  ├─ articles/
│  ├─ events/
│  ├─ locations/
│  └─ settings/
│     ├─ theme.ar.json
│     └─ menus.ar.json
│
├─ scripts/                      # generate indexes + validate content
│  ├─ build-indexes.mjs
│  ├─ validate-content.mjs
│  └─ demo-seed.mjs
│
├─ docs/
│  ├─ ARCHITECTURE.md
│  ├─ DEPLOY_CLOUDFLARE.md
│  ├─ ADMIN_WP_LIKE.md
│  ├─ APPEARANCE.md
│  ├─ R2_UPLOADS.md
│  └─ SUBMIT_NO_UPLOAD.md
│
├─ .github/
│  └─ workflows/                 # CI + build + lint
│
└─ README.md
