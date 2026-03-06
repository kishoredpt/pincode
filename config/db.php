<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: '';
$port = (int) (getenv('DB_PORT') ?: 3306);

if ($user === '' || $db === '') {
    http_response_code(503);
    echo "<h1>Service temporarily unavailable</h1><p>Database is not configured.</p>";
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(503);
    echo "<h1>Service temporarily unavailable</h1><p>Please try again shortly.</p>";
    exit;
}
?>
