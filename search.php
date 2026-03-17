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
$pageTitle = 'Search Results for PIN ' . $q . ' | India Pincode Locator';
$metaDescription = 'Quick lookup results for PIN code ' . $q . ' in ' . $firstRow['district'] . ', ' . $firstRow['statename'] . ', including mapped post offices and delivery types.';

function renderCard(array $row): string {
    return "<div class='card'>
<b>" . htmlspecialchars($row['officename']) . "</b><br>
" . htmlspecialchars($row['officetype']) . "<br>
Delivery: " . htmlspecialchars($row['delivery']) . "<br>
District: " . htmlspecialchars($row['district']) . "<br>
State: " . htmlspecialchars($row['statename']) . "
</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<?php
echo "<h1>Pincode: " . htmlspecialchars($q) . "</h1>";
echo "<h2>District: " . htmlspecialchars($firstRow['district']) . " | State: " . htmlspecialchars($firstRow['statename']) . "</h2>";
echo "<p>This quick lookup view shows post offices mapped to the selected PIN code. For detailed context and policy pages, use the main site navigation.</p>";
echo "<hr>";

echo "<div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;'>";

echo renderCard($firstRow);

while ($row = $result->fetch_assoc()) {
    echo renderCard($row);
}

echo "</div>";
?>
</body>
</html>
