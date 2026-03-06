<?php
header('Content-Type: text/html; charset=UTF-8');

$paths = [
    __DIR__ . '/config/db.credentials.php',
    __DIR__ . '/db.credentials.php',
];

$found = null;
foreach ($paths as $path) {
    if (is_readable($path)) {
        $found = $path;
        break;
    }
}

$keys = [];
$parseError = null;
$data = [];
if ($found) {
    try {
        $loaded = require $found;
        if (is_array($loaded)) {
            $data = $loaded;
            $keys = array_keys($loaded);
        }
    } catch (Throwable $e) {
        $parseError = $e->getMessage();
    }
}

echo '<h1>DB Check</h1>';
echo '<p>Credentials file found: <b>' . ($found ? htmlspecialchars($found, ENT_QUOTES, 'UTF-8') : 'No') . '</b></p>';
echo '<p>Keys detected: <b>' . htmlspecialchars(implode(', ', $keys), ENT_QUOTES, 'UTF-8') . '</b></p>';
if ($parseError) {
    echo '<p>Parse/load error: <b>' . htmlspecialchars($parseError, ENT_QUOTES, 'UTF-8') . '</b></p>';
}

echo '<p>Env(DB_USER): <b>' . (getenv('DB_USER') !== false ? 'set' : 'not set') . '</b></p>';
echo '<p>Env(DB_NAME): <b>' . (getenv('DB_NAME') !== false ? 'set' : 'not set') . '</b></p>';
echo '<p>Env(DB_DATABASE): <b>' . (getenv('DB_DATABASE') !== false ? 'set' : 'not set') . '</b></p>';

echo '<h2>Connection test</h2>';
if (!function_exists('mysqli_init')) {
    echo '<p>MySQLi extension: <b>Not available</b></p>';
} else {
    $host = (string) ($data['host'] ?? $data['hostname'] ?? '127.0.0.1');
    $user = (string) ($data['user'] ?? $data['username'] ?? '');
    $pass = (string) ($data['pass'] ?? $data['password'] ?? '');
    $db = (string) ($data['name'] ?? $data['db'] ?? $data['database'] ?? $data['dbname'] ?? '');
    $port = (int) ($data['port'] ?? 3306);

    if ($user === '' || $db === '') {
        echo '<p>Connection status: <b>SKIPPED</b> (missing user/db keys).</p>';
    } else {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($host, $user, $pass, $db, $port);
        if ($conn->connect_error) {
            echo '<p>Connection status: <b>FAILED</b></p>';
            echo '<p>MySQL error: <code>(' . (int) $conn->connect_errno . ') ' . htmlspecialchars((string) $conn->connect_error, ENT_QUOTES, 'UTF-8') . '</code></p>';
            echo '<p>Likely fix on Hostinger: reset DB user password in hPanel and update <code>config/db.credentials.php</code>, and confirm that DB user is assigned to the DB.</p>';
        } else {
            echo '<p>Connection status: <b>SUCCESS</b></p>';
            $conn->close();
        }
    }
}

echo '<hr><p>After verification, delete this file: <code>db-check.php</code>.</p>';
