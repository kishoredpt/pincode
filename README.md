# pincode

## Database configuration

If you see this error after deployment:

> Service temporarily unavailable  
> Database is not configured.

configure DB credentials using **either** method:

### Method A: Environment variables (recommended)

Set these variables in your runtime/webserver:

- `DB_HOST`
- `DB_USER`
- `DB_PASS`
- `DB_NAME` (or `DB_DATABASE`)
- `DB_PORT` (optional, default `3306`)

### Method B: Local credentials file

1. Copy `config/db.credentials.example.php` to `config/db.credentials.php`
2. Fill your actual credentials.
3. Ensure file is readable by PHP user (`644` usually works).
4. Keep `config/db.credentials.php` out of version control (already gitignored).

Environment variables take precedence over file values.

## Production troubleshooting checklist

1. Confirm file path is exact: `.../config/db.credentials.php`
2. Confirm returned value is an array with keys: `host`, `user`, `pass`, `name` (or `db`/`database`), `port`
3. Confirm no syntax issues: `php -l config/db.credentials.php`
4. Confirm permissions: `chmod 644 config/db.credentials.php`
5. Confirm PHP process can read the file (same user as webserver/PHP-FPM)
6. If env vars are present, verify they are not blank/incorrect (they override file values)
