# Self-Updating SEO Engine Setup

## Folder structure

- `seo-engine/detect-new-records.php` — scans the last 24h inserts and fills `seo_generation_queue`.
- `seo-engine/page-generator.php` — dequeues pending entities, generates 500–700 word content pages, metadata, schema, and cache files.
- `seo-engine/link-builder.php` — rebuilds internal link graph (`nearby`, `district`, `state`) with 10-link caps per section.
- `seo-engine/sitemap-generator.php` — creates `sitemap.xml`, `sitemap-pincode.xml`, `sitemap-district.xml`, `sitemap-state.xml`, `sitemap-blog.xml`.
- `seo-engine/blog-generator.php` — generates 2–3 template-driven posts per week into `blog_posts`.
- `seo-engine/meta-generator.php` — metadata service (title/description/canonical).
- `templates/state-template.php`, `templates/district-template.php`, `templates/area-template.php`, `templates/pincode-template.php` — SEO page templates with breadcrumbs + JSON-LD.
- `cache/pages/` — generated page cache output.
- `admin/seo-engine` route — dashboard for SEO engine status.

## SQL schema changes

Run:

```bash
php scripts/migrate.php
```

Migration `database/003_seo_engine.sql` adds:
- `created_at` columns on `pincodes`, `post_offices`, `areas`, `districts`, `states`
- `seo_generation_queue`
- `seo_pages`
- `seo_internal_links`

## Cron configuration

Install the provided cron file:

```bash
crontab deploy/seo-engine.cron
```

Included schedules:
- Hourly: `detect-new-records.php`
- Every 2 hours: `page-generator.php`
- Every 6 hours: `link-builder.php`
- Daily: `sitemap-generator.php`
- Twice weekly: `blog-generator.php`

## Operating sequence

1. `detect-new-records.php` queues new location entities.
2. `page-generator.php` builds pages and writes cache + metadata.
3. `link-builder.php` updates internal linking table.
4. `sitemap-generator.php` publishes sitemap index + segment files.
5. `blog-generator.php` publishes informational articles.
6. Dashboard at `/admin/seo-engine` shows queue, page, and sitemap health.
