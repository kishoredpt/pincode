<?php
/**
 * Database bootstrap for both VPS and shared hosting (Hostinger/cPanel).
 *
 * Resolution order:
 * 1) Readable local credentials file (first readable match)
 * 2) Non-empty environment variables (override file values)
 */

if (!function_exists('pincodeEnvValue')) {
    function pincodeEnvValue(string $key, ?string $default = null): ?string
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
}

$localDbConfig = [];
$checkedCredentialPaths = [
    __DIR__ . '/db.credentials.php',
    dirname(__DIR__) . '/db.credentials.php',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/config/db.credentials.php',
    dirname((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')) . '/config/db.credentials.php',
];

foreach ($checkedCredentialPaths as $credentialPath) {
    if ($credentialPath === '' || !is_readable($credentialPath)) {
        continue;
    }

    $loadedConfig = require $credentialPath;
    if (is_array($loadedConfig)) {
        $localDbConfig = $loadedConfig;
        break;
    }
}

$host = (string) ($localDbConfig['host'] ?? $localDbConfig['hostname'] ?? '127.0.0.1');
$user = (string) ($localDbConfig['user'] ?? $localDbConfig['username'] ?? '');
$pass = (string) ($localDbConfig['pass'] ?? $localDbConfig['password'] ?? '');
$db = (string) ($localDbConfig['name'] ?? $localDbConfig['db'] ?? $localDbConfig['database'] ?? $localDbConfig['dbname'] ?? '');
$port = (int) ($localDbConfig['port'] ?? 3306);

// Environment overrides only when they are non-empty.
$host = pincodeEnvValue('DB_HOST', $host);
$user = pincodeEnvValue('DB_USER', $user);
$pass = pincodeEnvValue('DB_PASS', $pass);
$db = pincodeEnvValue('DB_NAME', $db);
$db = pincodeEnvValue('DB_DATABASE', $db);
$port = (int) pincodeEnvValue('DB_PORT', (string) $port);

if ($user === '' || $db === '') {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1>';
    echo '<p>Database is not configured.</p>';
    echo '<p>Database is not configured. Add <code>config/db.credentials.php</code> with keys <code>host,user,pass,name,port</code>.</p>';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1>';
    echo '<p>Database connection failed. Verify credentials in hPanel → Databases → MySQL Databases.</p>';
    exit;
}
?>
