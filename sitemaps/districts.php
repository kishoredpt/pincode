<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
$q=$conn->query("SELECT DISTINCT district FROM post_offices ORDER BY district");

while($row=$q->fetch_assoc()){
$slug=strtolower(preg_replace('/[^a-z0-9]+/','-',trim($row['district'])));
$slug=trim($slug,'-');
$url="https://pincodelocator.co.in/{$slug}-pincode";
?>
<url>
<loc><?= htmlspecialchars($url) ?></loc>
<changefreq>weekly</changefreq>
<priority>0.8</priority>
</url>
<?php } ?>
</urlset>
