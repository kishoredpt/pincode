<?php
header("Content-Type: application/xml");
require_once __DIR__ . '/../config/db.php';

$base = "https://pincodelocator.co.in";
$page = max(1, (int)($_GET['page'] ?? 1));
$batchSize = 10000;
$offset = ($page - 1) * $batchSize;

$totalRows = 0;
$totalRes = $conn->query("SELECT COUNT(DISTINCT pincode) AS total FROM pincode_nearest_railway_station");
if ($totalRes) {
    $totalRows = (int)($totalRes->fetch_assoc()['total'] ?? 0);
}

$stmt = $conn->prepare("\n    SELECT DISTINCT pincode\n    FROM pincode_nearest_railway_station\n    ORDER BY pincode\n    LIMIT ? OFFSET ?\n");
$stmt->bind_param("ii", $batchSize, $offset);
$stmt->execute();
$res = $stmt->get_result();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php while ($res && $row = $res->fetch_assoc()): ?>
    <url>
        <loc><?= $base ?>/nearest-railway-station-<?= htmlspecialchars($row['pincode']) ?></loc>
    </url>
<?php endwhile; ?>
</urlset>
