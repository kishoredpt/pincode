# pincode

Postal directory website for India PIN code search and location discovery.

## Local setup

Set database credentials using environment variables before running PHP:

- `DB_HOST`
- `DB_USER`
- `DB_PASS`
- `DB_NAME`

Example:

```bash
export DB_HOST=localhost
export DB_USER=your_user
export DB_PASS=your_password
export DB_NAME=your_database
php -S 0.0.0.0:8000
```

## SEO / crawl hygiene included

- `robots.txt` blocks internal API/search utility endpoints from crawler indexation.
- API/search endpoints now emit `X-Robots-Tag: noindex, nofollow`.
- Shared header and homepage output Open Graph/Twitter metadata and `WebSite` JSON-LD.
