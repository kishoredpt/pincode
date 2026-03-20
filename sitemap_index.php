<?php
header("Content-Type: application/xml");
require_once 'config/db.php';

$base = "https://pincodelocator.co.in";
$batchSize = 10000;
$railRows = 0;
$railRes = $conn->query("SELECT COUNT(DISTINCT pincode) AS total FROM pincode_nearest_railway_station");
if ($railRes) {
    $railRows = (int) (($railRes->fetch_assoc()['total'] ?? 0));
}
$railSitemaps = max(1, (int) ceil($railRows / $batchSize));

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<sitemap>
<loc><?= $base ?>/sitemaps/states.php</loc>
</sitemap>

<sitemap>
<loc><?= $base ?>/sitemaps/districts.php</loc>
</sitemap>

<?php for ($i = 1; $i <= $railSitemaps; $i++): ?>
<sitemap>
<loc><?= $base ?>/sitemaps/railway-pages.php?page=<?= $i ?></loc>
</sitemap>
<?php endfor; ?>

<sitemap>
<loc><?= $base ?>/sitemaps/articles.php</loc>
</sitemap>

</sitemapindex>
