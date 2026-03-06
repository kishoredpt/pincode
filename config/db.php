<?php
/**
 * Database bootstrap.
 *
 * Resolution order for each key:
 * 1) Environment variables (getenv / $_ENV / $_SERVER)
 * 2) Optional local override file: config/db.credentials.php
 */

$localDbConfig = [];
$localCredentialsFile = __DIR__ . '/db.credentials.php';
if (is_file($localCredentialsFile)) {
    $localDbConfig = require $localCredentialsFile;
    if (!is_array($localDbConfig)) {
        $localDbConfig = [];
    }
}

function envValue(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }

    return $default;
}

$host = envValue('DB_HOST', (string) ($localDbConfig['host'] ?? '127.0.0.1'));
$user = envValue('DB_USER', (string) ($localDbConfig['user'] ?? ''));
$pass = envValue('DB_PASS', (string) ($localDbConfig['pass'] ?? ''));
$db   = envValue('DB_NAME', (string) ($localDbConfig['name'] ?? ''));
$port = (int) envValue('DB_PORT', (string) ($localDbConfig['port'] ?? 3306));

if ($user === '' || $db === '') {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1>';
    echo '<p>Database is not configured. Set DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT or create config/db.credentials.php from config/db.credentials.example.php.</p>';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1>';
    echo '<p>Please verify DB credentials and database availability.</p>';
    exit;
}
?>
