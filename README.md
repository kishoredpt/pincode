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
- `DB_NAME`
- `DB_PORT` (optional, default `3306`)

### Method B: Local credentials file

1. Copy `config/db.credentials.example.php` to `config/db.credentials.php`
2. Fill your actual credentials.
3. Keep `config/db.credentials.php` out of version control (already gitignored).

Environment variables take precedence over file values.
