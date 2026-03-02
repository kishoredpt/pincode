<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

$limit = 10000;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php

$stmt = $conn->prepare("
SELECT slug, updated_at
FROM post_offices
LIMIT ? OFFSET ?
");

$stmt->bind_param("ii",$limit,$offset);
$stmt->execute();

$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
?>

<url>
<loc>https://pincodelocator.co.in/post-office/<?= htmlspecialchars($row['slug']) ?></loc>
<changefreq>monthly</changefreq>
<priority>0.8</priority>
</url>

<?php } ?>

</urlset>