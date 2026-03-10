<?php
header("Content-Type: application/json");
require_once "config/db.php";

$type = $_GET['type'] ?? '';
$allowedTypes = ['states', 'districts', 'offices', 'search', 'district-postoffices'];

if (!in_array($type, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request type']);
    exit;
}

function cleanText(string $value): string {
    $value = trim($value);
    if (strlen($value) > 100) {
        return '';
    }
    return $value;
}

if ($type === "states") {
    $stmt = $conn->prepare("SELECT DISTINCT statename FROM post_offices ORDER BY statename");
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['statename'])) {
            $data[] = $row['statename'];
        }
    }

    echo json_encode($data);
    exit;
}

$state = cleanText($_GET['state'] ?? '');
if ($state === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing state']);
    exit;
}

if ($type === "districts") {
    $stmt = $conn->prepare("SELECT DISTINCT district FROM post_offices WHERE statename=? ORDER BY district");
    $stmt->bind_param("s", $state);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['district'];
    }

    echo json_encode($data);
    exit;
}

$district = cleanText($_GET['district'] ?? '');
if ($district === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing district']);
    exit;
}

if ($type === "offices") {
    $stmt = $conn->prepare("SELECT DISTINCT officename FROM post_offices WHERE statename=? AND district=? ORDER BY officename LIMIT 1000");
    $stmt->bind_param("ss", $state, $district);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['officename'];
    }

    echo json_encode($data);
    exit;
}

if ($type === "search") {
    $office = cleanText($_GET['office'] ?? '');
    if ($office === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Missing office']);
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT officename,pincode,district,statename,latitude,longitude,delivery,officetype
         FROM post_offices
         WHERE statename=? AND district=? AND officename=?
         LIMIT 50"
    );
    $stmt->bind_param("sss", $state, $district, $office);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

if ($type === "district-postoffices") {
    $stmt = $conn->prepare(
        "SELECT officename,pincode,statename,district
         FROM post_offices
         WHERE statename=? AND district=?
         ORDER BY officename
         LIMIT 1000"
    );
    $stmt->bind_param("ss", $state, $district);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}
