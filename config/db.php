<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: '';

if ($user === '' || $db === '') {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1><p>Database configuration is missing.</p>';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(503);
    echo '<h1>Service temporarily unavailable</h1><p>Please try again shortly.</p>';
    exit;
}
?>
