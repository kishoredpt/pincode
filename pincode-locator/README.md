# India Pincode Locator

Production-ready PHP + MySQL postal portal for India PIN code lookup.

## Features
- Search by PIN code, office, district, and state.
- SEO-focused state, district, pincode, and blog pages.
- Structured schema markup (Organization, WebSite, Breadcrumb, Article, FAQ).
- Auto article generator (1000+ articles).
- Sitemap generation and AdSense readiness report.
- Secure database access with PDO prepared statements.

## Setup
1. Upload `pincode-locator` to cPanel `public_html`.
2. Create MySQL DB and import `database.sql`.
3. Configure environment vars (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) or edit `includes/db.php`.
4. Ensure Apache `mod_rewrite` is enabled.
5. Run:
   - `php tools/article-generator.php`
   - `php tools/generate-sitemap.php`
   - `php tools/adsense-check.php`

## Data import for ~155000 post offices
Use `LOAD DATA INFILE` command in `database.sql` comments and provide an India Post CSV dataset with matching columns.

## Performance
- Minified JS and compressed CSS.
- Indexed lookup fields (`pincode`, `state_id`, `district_id`).
- Lazy-loaded map iframe and images.
