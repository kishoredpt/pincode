<?php
header("Content-Type: application/json");
header("X-Robots-Tag: noindex, nofollow", true);
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
        "SELECT po.officename,po.pincode,po.district,po.statename,po.latitude,po.longitude,po.delivery,po.officetype,po.divisionname,
                rs.station_name AS nearest_station_name,
                rs.station_code AS nearest_station_code,
                pnr.distance_km AS nearest_station_distance_km
         FROM post_offices po
         LEFT JOIN pincode_nearest_railway_station pnr ON pnr.pincode = po.pincode
         LEFT JOIN railway_stations rs ON rs.id = pnr.station_id
         WHERE po.statename=? AND po.district=? AND po.officename=?
         ORDER BY pnr.distance_km ASC
         LIMIT 50"
    );
    $stmt->bind_param("sss", $state, $district, $office);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    if (!empty($data)) {
        foreach ($data as &$row) {
            $hasMappedStation = !empty($row['nearest_station_name']) && !empty($row['nearest_station_code']);
            $hasCoordinates = is_numeric($row['latitude'] ?? null) && is_numeric($row['longitude'] ?? null);

            if ($hasMappedStation || !$hasCoordinates) {
                continue;
            }

            $lat = (float) $row['latitude'];
            $lon = (float) $row['longitude'];

            $stmtNearest = $conn->prepare(
                "SELECT station_name, station_code,
                        ROUND(6371 * ACOS(
                            COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) +
                            SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                        ), 1) AS distance_km
                 FROM railway_stations
                 WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                 ORDER BY distance_km ASC
                 LIMIT 1"
            );

            if (!$stmtNearest) {
                continue;
            }

            $stmtNearest->bind_param("ddd", $lat, $lon, $lat);
            $stmtNearest->execute();
            $nearestRes = $stmtNearest->get_result();

            if ($nearestRes && $nearestRes->num_rows > 0) {
                $nearest = $nearestRes->fetch_assoc();
                $row['nearest_station_name'] = $nearest['station_name'] ?? null;
                $row['nearest_station_code'] = $nearest['station_code'] ?? null;
                $row['nearest_station_distance_km'] = $nearest['distance_km'] ?? null;
            }
        }
        unset($row);
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
