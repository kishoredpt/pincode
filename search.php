<?php
$conn = new mysqli(
    "localhost",
    "u854527538_kishore",
    "0044Ki05@123",
    "u854527538_pincode"
);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$q = $_GET['q'] ?? '';

if (!preg_match('/^[0-9]{6}$/', $q)) {
    die("Invalid Pincode");
}

$stmt = $conn->prepare("
    SELECT officename, officetype, delivery, district, statename 
    FROM post_offices 
    WHERE pincode = ?
");

$stmt->bind_param("s", $q);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<h2>No records found for $q</h2>";
    exit;
}

$firstRow = $result->fetch_assoc();

echo "<h1>Pincode: $q</h1>";
echo "<h2>District: {$firstRow['district']} | State: {$firstRow['statename']}</h2>";
echo "<hr>";

echo "<div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;'>";

echo renderCard($firstRow);

while ($row = $result->fetch_assoc()) {
    echo renderCard($row);
}

echo "</div>";

function renderCard($row) {
    return "
    <div style='padding:15px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.05);'>
        <strong>{$row['officename']}</strong><br>
        Type: {$row['officetype']}<br>
        Delivery: {$row['delivery']}
    </div>
    ";
}
?>