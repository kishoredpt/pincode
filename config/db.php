<?php
/**
 * Database bootstrap.
 *
 * Resolution order for each key:
 * 1) Environment variables (getenv / $_ENV / $_SERVER)
 * 2) Optional local override file (first readable match):
 *    - config/db.credentials.php
 *    - project-root/db.credentials.php
 */

function envValue(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value !== false && trim((string) $value) !== '') {
        return trim((string) $value);
    }

    if (isset($_ENV[$key]) && trim((string) $_ENV[$key]) !== '') {
        return trim((string) $_ENV[$key]);
    }

    if (isset($_SERVER[$key]) && trim((string) $_SERVER[$key]) !== '') {
        return trim((string) $_SERVER[$key]);
    }

    return $default;
}

$localDbConfig = [];
$checkedCredentialPaths = [
    __DIR__ . '/db.credentials.php',
    dirname(__DIR__) . '/db.credentials.php',
];

foreach ($checkedCredentialPaths as $credentialPath) {
    if (!is_readable($credentialPath)) {
        continue;
    }

    $loadedConfig = require $credentialPath;
    if (is_array($loadedConfig)) {
        $localDbConfig = $loadedConfig;
        break;
    }
}

$host = envValue('DB_HOST', (string) ($localDbConfig['host'] ?? '127.0.0.1'));
$user = envValue('DB_USER', (string) ($localDbConfig['user'] ?? ''));
$pass = envValue('DB_PASS', (string) ($localDbConfig['pass'] ?? ''));
$db = envValue('DB_NAME', (string) ($localDbConfig['name'] ?? ($localDbConfig['db'] ?? ($localDbConfig['database'] ?? ''))));
$port = (int) envValue('DB_PORT', (string) ($localDbConfig['port'] ?? 3306));

// Common alias supported by many hosting panels.
if ($db === '') {
    $db = envValue('DB_DATABASE', $db);
}

if ($user === '' || $db === '') {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1>';
    echo '<p>Database is not configured. Set DB_HOST, DB_USER, DB_PASS, DB_NAME/DB_DATABASE, DB_PORT or create a readable db.credentials.php.</p>';
    echo '<p>Checked credential paths: <code>' . htmlspecialchars(implode(' | ', $checkedCredentialPaths), ENT_QUOTES, 'UTF-8') . '</code></p>';
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
