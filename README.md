# India Pincode Locator (Refactored)

## 1) Current Architecture Scan (Legacy Baseline)

Legacy repository contained a flat PHP structure with direct-entry files (`index.php`, `blog.php`, static policy pages), shared includes, and SQL queries embedded in page files. Routing relied on request path parsing directly in `index.php` and mixed rendering/query logic. Main dataset access was done from `post_offices` with route-driven SQL checks for state, district, and pincode.

### Issues detected
- Monolithic route + render flow in a single entry point.
- Multiple repeated DB checks and low separation of concerns.
- SEO metadata generated inline with weak canonical/systematic schema coverage.
- Limited caching and no structured deployment pipeline.

## 2) New Architecture

```
/app
/config
/routes
/controllers
/services
/templates
/blog
/sitemap
/public
/assets
/database
/deploy
/scripts
```

### MVC-lite flow
1. `index.php` boots app and router.
2. `routes/web.php` maps SEO URLs to controllers.
3. Controllers call service layer for DB work and content generation.
4. Templates render HTML with shared layout.

## 3) URL Router

Supported routes:
- `/`
- `/state/{state}`
- `/district/{district}`
- `/area/{area}`
- `/pincode/{pincode}`
- `/blog/{slug}`

Trust pages:
- `/about`, `/contact`, `/privacy-policy`, `/disclaimer`, `/terms`

Sitemaps:
- `/sitemap.xml`
- `/sitemap/{type}.xml`

## 4) Database

### Existing table compatibility
Runtime still reads existing `post_offices` directly for backward compatibility.

### Normalized schema
Migration SQL adds:
- `states`
- `districts`
- `areas`
- `pincodes`
- normalized `post_offices`
- `blog_posts`

### Indexing
Added index migration for common lookup dimensions:
- `pincode`
- `district`
- `statename`
- `officename`
- composite (`statename`, `district`, `pincode`)

## 5) Content + Internal Linking Engine

- `generateLocationContent($data)` implemented in `ContentGeneratorService`.
- Related links implemented:
  - nearby pincodes (max 10)
  - district links (max 10)
  - state links (max 10)

## 6) SEO Optimization

Implemented:
- meta title/description
- canonical URLs
- Open Graph tags
- JSON-LD: `WebSite`, `SearchAction`, `BreadcrumbList`, `FAQPage`

## 7) Sitemap Generation

Run:
```bash
php scripts/generate-sitemaps.php
```

Generates chunked files (50,000 URL max each) and sitemap index in `/sitemap`.

## 8) Performance Optimizations

- File-based query caching (`assets/cache`)
- Prepared statements for all dynamic queries
- Focused field selection and dimension indexes
- Lightweight layout and server-side render flow

## 9) Deployment

### Local Docker
```bash
docker compose up --build
```

### VPS (Ubuntu + Nginx + PHP-FPM)
```bash
./deploy/deploy_vps.sh
```

### Environment variables
- `APP_URL`
- `APP_ENV`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `CACHE_TTL`
- `CONTACT_EMAIL`

## 10) Setup

```bash
cp config/db.credentials.example.php config/db.credentials.php
# fill credentials
php scripts/migrate.php
php -S 0.0.0.0:8000 index.php
```

## 11) Database introspection

```bash
php scripts/db-introspect.php
```

This script auto-detects tables and fields from the connected MySQL database.
