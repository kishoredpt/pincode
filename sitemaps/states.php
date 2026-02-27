<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
$q=$conn->query("SELECT DISTINCT statename FROM post_offices ORDER BY statename");

while($row=$q->fetch_assoc()){

$slug=strtolower(str_replace(' ','-',$row['statename']));
$url="https://pincodelocator.co.in/state/".$slug;
?>

<url>
<loc><?= $url ?></loc>
<changefreq>weekly</changefreq>
<priority>0.9</priority>
</url>

<?php } ?>

</urlset>