<?php
$host = "localhost";
$user = "u854527538_kishore";
$pass = "0044Ki05@123";
$db   = "u854527538_pincode";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>