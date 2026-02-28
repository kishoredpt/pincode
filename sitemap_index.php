<?php
header("Content-Type: application/xml");
require_once 'config/db.php';

$base = "https://pincodelocator.co.in";
$batchSize = 10000;
$totalRows = 0;

$res = $conn->query("SELECT COUNT(*) AS total FROM post_offices");
if ($res) {
    $row = $res->fetch_assoc();
    $totalRows = (int) ($row['total'] ?? 0);
}

$totalPostofficeSitemaps = max(1, (int) ceil($totalRows / $batchSize));

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<sitemap>
<loc><?= $base ?>/sitemaps/states.php</loc>
</sitemap>

<sitemap>
<loc><?= $base ?>/sitemaps/districts.php</loc>
</sitemap>

<?php for ($i = 1; $i <= $totalPostofficeSitemaps; $i++): ?>
<sitemap>
<loc><?= $base ?>/sitemaps/postoffices.php?page=<?= $i ?></loc>
</sitemap>
<?php endfor; ?>

<sitemap>
<loc><?= $base ?>/sitemaps/articles.php</loc>
</sitemap>

</sitemapindex>
