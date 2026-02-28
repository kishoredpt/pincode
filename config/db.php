<?php
$host = "localhost";
$user = "u854527538_kishore";
$pass = "0044Ki05@123";
$db   = "u854527538_pincode";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(503);
    echo "<h1>Service temporarily unavailable</h1><p>Please try again shortly.</p>";
    exit;
}
?>
