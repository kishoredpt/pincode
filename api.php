<?php
header('Content-Type: application/json');
require_once 'config/db.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Service unavailable"]);
    exit;
}

$q = trim($_GET['q'] ?? '');

if (!preg_match('/^[0-9]{6}$/', $q)) {
    http_response_code(422);
    echo json_encode(["error" => "Invalid pincode format"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT officename, pincode, officetype, delivery, district, statename, latitude, longitude
     FROM post_offices
     WHERE pincode = ?
     ORDER BY officename
     LIMIT 50"
);

$stmt->bind_param('s', $q);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
