<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
$q=$conn->query("SELECT slug FROM articles");

while($row=$q->fetch_assoc()){
?>

<url>
<loc>
https://pincodelocator.co.in/article/<?= $row['slug'] ?>
</loc>
<changefreq>monthly</changefreq>
<priority>0.6</priority>
</url>

<?php } ?>

</urlset>