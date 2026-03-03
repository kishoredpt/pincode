<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php

$q=$conn->query("
SELECT DISTINCT district,statename
FROM post_offices
ORDER BY district
");

while($row=$q->fetch_assoc()){

$state=strtolower(str_replace(' ','-',$row['statename']));
$district=strtolower(str_replace(' ','-',$row['district']));

$url="https://pincodelocator.co.in/state/$state/$district";
?>

<url>
<loc><?= $url ?></loc>
<changefreq>weekly</changefreq>
<priority>0.8</priority>
</url>

<?php } ?>

</urlset>