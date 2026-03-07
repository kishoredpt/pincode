# Legacy Architecture Audit

## Project scan summary
- Entry points were primarily file-based (`index.php`, `blog.php`, `blog-post.php`, policy pages).
- Routing was done by parsing query params and request path inside `index.php`.
- Templates were mixed inline with business/data logic.
- Data source centered on `post_offices` in MySQL.

## Routing logic detected
- home + slug route inference from `route` query string.
- special handling for `/blog` and `/blog/{slug}`.
- pincode/state/district detection via sequential SQL checks.

## Query patterns
- state and district lookup used `SELECT DISTINCT ... WHERE LOWER(column)=LOWER(?)`.
- pincode pages retrieved all matching `post_offices` rows.
- no dedicated repository/service layer; SQL lived in page files.

## Performance issues detected
- repeated route-path parsing and repeated SQL checks per request.
- no query-result caching.
- no route-level cache or page-cache strategy.
- monolithic file loads affecting maintainability and performance tuning.

## SEO issues detected
- meta and canonical generation not consistently centralized.
- schema markup coverage partial and coupled to route logic.
- inconsistent URL structure between `.php` pages and clean URLs.
- sitemap generation not fully chunked by 50k URL limits.
