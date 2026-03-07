<?php
$credentialsFile = __DIR__ . '/db.credentials.php';
$fileConfig = [];

if (is_readable($credentialsFile)) {
    $loaded = require $credentialsFile;

    if (is_array($loaded)) {
        $fileConfig = $loaded;
    }
}

$host = getenv('DB_HOST') ?: ($fileConfig['host'] ?? '127.0.0.1');
$user = getenv('DB_USER') ?: ($fileConfig['user'] ?? '');
$pass = getenv('DB_PASS') ?: ($fileConfig['pass'] ?? '');
$db   = getenv('DB_NAME') ?: ($fileConfig['name'] ?? '');
$port = (int) (getenv('DB_PORT') ?: ($fileConfig['port'] ?? 3306));


if ($host === 'localhost') {
    // Prefer TCP to avoid missing local MySQL socket issues in some hosting/runtime setups.
    $host = '127.0.0.1';
}

if ($user === '' || $db === '') {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1><p>Database is not configured.</p>';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1>';
    echo '<p>Please verify DB credentials and database availability.</p>';
    exit;
}
?>
