# pincode

## Database configuration

If you see this error after deployment:

> Service temporarily unavailable  
> Database is not configured.

configure DB credentials using **either** method:

### Method A: Environment variables

Set these variables in your runtime/webserver:

- `DB_HOST`
- `DB_USER`
- `DB_PASS`
- `DB_NAME` (or `DB_DATABASE`)
- `DB_PORT` (optional, default `3306`)

### Method B: Local credentials file (recommended for Hostinger shared hosting)

1. In File Manager, create `config/db.credentials.php`.
2. Paste:

```php
<?php
return [
  'host' => 'localhost',
  'user' => 'your_db_user',
  'pass' => 'your_db_password',
  'name' => 'your_db_name',
  'port' => 3306,
];
```

3. Save with permission `0644`.
4. Make sure values exactly match hPanel → Databases → MySQL Databases.

Environment values override file values only when env values are non-empty.

## Hostinger validation without SSH

If your hosting plan has no terminal access:

1. Upload `db-check.php` to project root (already included in repo).
2. Open: `https://your-domain/db-check.php`
3. Confirm:
   - Credentials file found = Yes
   - Keys include `host,user,pass,name,port`
4. If Env(DB_USER/DB_NAME/DB_DATABASE) shows `set`, verify those values are correct in hosting config.
5. Delete `db-check.php` after fix.

## Common break causes

- File saved in wrong path (`db.credentials.php` must be inside `config/` for primary lookup).
- Wrong key name (`name` expected; aliases `db`, `database`, `dbname` are also supported).
- File not readable by PHP user.
- DB user/database mismatch with current Hostinger account prefix.
