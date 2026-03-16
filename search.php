<?php
require_once 'config/db.php';
header('X-Robots-Tag: noindex, nofollow', true);

$q = trim($_GET['q'] ?? '');

if (!preg_match('/^[0-9]{6}$/', $q)) {
    http_response_code(422);
    die("Invalid Pincode");
}

$stmt = $conn->prepare(
    "SELECT officename, officetype, delivery, district, statename
     FROM post_offices
     WHERE pincode = ?
     ORDER BY officename"
);

$stmt->bind_param("s", $q);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<h2>No records found for " . htmlspecialchars($q) . "</h2>";
    exit;
}

$firstRow = $result->fetch_assoc();

echo "<h1>Pincode: " . htmlspecialchars($q) . "</h1>";
echo "<h2>District: " . htmlspecialchars($firstRow['district']) . " | State: " . htmlspecialchars($firstRow['statename']) . "</h2>";
echo "<hr>";

echo "<div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;'>";

echo renderCard($firstRow);

while ($row = $result->fetch_assoc()) {
    echo renderCard($row);
}

echo "</div>";

function renderCard($row): string {
    return "
    <div style='padding:15px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.05);'>
        <strong>" . htmlspecialchars($row['officename']) . "</strong><br>
        Type: " . htmlspecialchars($row['officetype']) . "<br>
        Delivery: " . htmlspecialchars($row['delivery']) . "
    </div>
    ";
}
