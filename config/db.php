<?php
/**
 * Database config resolution order:
 * 1) Environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME)
 * 2) Web server vars ($_SERVER / $_ENV) with same keys
 * 3) Optional local file: config/db.hostinger.php returning array keys host,user,pass,name
 */
function dbConfigValue(string $key, string $default = ''): string
{
    $fromEnv = getenv($key);
    if ($fromEnv !== false && trim((string)$fromEnv) !== '') {
        return trim((string)$fromEnv);
    }

    if (!empty($_SERVER[$key])) {
        return trim((string)$_SERVER[$key]);
    }

    if (!empty($_ENV[$key])) {
        return trim((string)$_ENV[$key]);
    }

    return $default;
}

$host = dbConfigValue('DB_HOST', 'localhost');
$user = dbConfigValue('DB_USER', '');
$pass = dbConfigValue('DB_PASS', '');
$db = dbConfigValue('DB_NAME', '');

$allowOptionalDb = defined('ALLOW_DB_OPTIONAL') && ALLOW_DB_OPTIONAL === true;

$hostingerFile = __DIR__ . '/db.hostinger.php';
if (($user === '' || $db === '') && is_file($hostingerFile)) {
    $hostingerConfig = require $hostingerFile;

    if (is_array($hostingerConfig)) {
        $host = trim((string)($hostingerConfig['host'] ?? $host));
        $user = trim((string)($hostingerConfig['user'] ?? $user));
        $pass = trim((string)($hostingerConfig['pass'] ?? $pass));
        $db = trim((string)($hostingerConfig['name'] ?? $db));
    }
}

if ($user === '' || $db === '') {
    if ($allowOptionalDb) {
        $conn = null;
        return;
    }

    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1><p>Database configuration is missing. Add DB_* env vars or create config/db.hostinger.php.</p>';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    if ($allowOptionalDb) {
        $conn = null;
        return;
    }

    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1><p>Please try again shortly.</p>';
    exit;
}
?>
