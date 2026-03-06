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
if ($found) {
    try {
        $data = require $found;
        if (is_array($data)) {
            $keys = array_keys($data);
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

echo '<hr><p>After verification, delete this file: <code>db-check.php</code>.</p>';
