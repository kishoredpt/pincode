<?php
header('Content-Type: application/json');

$conn = new mysqli(
    "localhost",
    "u854527538_kishore",
    "0044Ki05@123",
    "u854527538_pincode"
);

if ($conn->connect_error) {
    http_response_code(500);
    exit;
}

$q = $_GET['q'] ?? '';

if (!preg_match('/^[0-9]{6}$/', $q)) {
    echo json_encode(["error" => "Invalid Pincode"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT officename, officetype, delivery, district, statename, latitude, longitude
    FROM post_offices
    WHERE pincode = ?
    LIMIT 50
");

$stmt->bind_param("s", $q);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);