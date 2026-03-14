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
    "SELECT po.officename, po.pincode, po.officetype, po.delivery, po.district, po.statename,
            po.latitude, po.longitude,
            rs.station_name AS nearest_station_name,
            rs.station_code AS nearest_station_code,
            pnr.distance_km AS nearest_station_distance_km
     FROM post_offices po
     LEFT JOIN pincode_nearest_railway_station pnr ON pnr.pincode = po.pincode
     LEFT JOIN railway_stations rs ON rs.id = pnr.station_id
     WHERE po.pincode = ?
     ORDER BY po.officename
     LIMIT 50"
);

if (!$stmt) {
    $stmt = $conn->prepare(
        "SELECT officename, pincode, officetype, delivery, district, statename, latitude, longitude
         FROM post_offices
         WHERE pincode = ?
         ORDER BY officename
         LIMIT 50"
    );
}

$stmt->bind_param('s', $q);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$hasNearestContext = false;
foreach ($data as $row) {
    if (!empty($row['nearest_station_name']) && !empty($row['nearest_station_code'])) {
        $hasNearestContext = true;
        break;
    }
}

if (!$hasNearestContext && count($data) > 0) {
    $fallbackLat = null;
    $fallbackLon = null;

    foreach ($data as $row) {
        $candidateLat = strtolower(trim((string)($row['latitude'] ?? '')));
        $candidateLon = strtolower(trim((string)($row['longitude'] ?? '')));

        if (
            $candidateLat !== ''
            && $candidateLon !== ''
            && $candidateLat !== 'nan'
            && $candidateLon !== 'nan'
            && is_numeric($candidateLat)
            && is_numeric($candidateLon)
        ) {
            $fallbackLat = (float) $candidateLat;
            $fallbackLon = (float) $candidateLon;
            break;
        }
    }

    if (is_numeric($fallbackLat) && is_numeric($fallbackLon)) {
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

        if ($stmtNearest) {
            $stmtNearest->bind_param('ddd', $fallbackLat, $fallbackLon, $fallbackLat);
            $stmtNearest->execute();
            $nearestRes = $stmtNearest->get_result();

            if ($nearestRes && $nearestRes->num_rows > 0) {
                $nearest = $nearestRes->fetch_assoc();
                foreach ($data as &$row) {
                    $row['nearest_station_name'] = $nearest['station_name'] ?? null;
                    $row['nearest_station_code'] = $nearest['station_code'] ?? null;
                    $row['nearest_station_distance_km'] = $nearest['distance_km'] ?? null;
                }
                unset($row);
            }
        }
    }
}


echo json_encode($data, JSON_UNESCAPED_UNICODE);
