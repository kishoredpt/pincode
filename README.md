# pincode

Postal directory website for India PIN code search and location discovery.

## Database setup (Hostinger compatible)

`config/db.php` now resolves credentials in this order:

1. `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` from environment variables.
2. Same keys from server-level variables (`$_SERVER` / `$_ENV`).
3. Local file `config/db.hostinger.php` (if present).

### Option A: Environment variables

```bash
export DB_HOST=localhost
export DB_USER=your_user
export DB_PASS=your_password
export DB_NAME=your_database
php -S 0.0.0.0:8000
```

### Option B: Hostinger file-based config

1. Copy `config/db.hostinger.example.php` to `config/db.hostinger.php`.
2. Fill your Hostinger MySQL credentials from hPanel → Databases.
3. Ensure `config/db.hostinger.php` is not committed (already ignored in `.gitignore`).

## SEO / crawl hygiene included

- `robots.txt` blocks internal API/search utility endpoints from crawler indexation.
- API/search endpoints emit `X-Robots-Tag: noindex, nofollow`.
- Shared header and homepage output Open Graph/Twitter metadata and `WebSite` JSON-LD.


## AdSense publisher config

`/ads.txt` is routed to `ads-txt.php` (via `.htaccess`) and supports two config sources:

1. `ADSENSE_PUBLISHER_ID` environment variable (recommended).
2. Optional file `config/adsense.php` returning `publisher_id` (copy `config/adsense.example.php`).

Example value format: `ca-pub-################`.
