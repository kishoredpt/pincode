<?php
header("Content-Type: application/xml; charset=utf-8");

include "config/db.php";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php

$query = "
SELECT officename,pincode
FROM post_offices
LIMIT 50000
";

$result = $conn->query($query);

while($row = $result->fetch_assoc()) {

$slug = strtolower(
    str_replace(
        [' ','.'],
        ['-',''],
        $row['officename']
    )
)."-post-office-pincode-".$row['pincode'];

?>

<url>
<loc>https://pincodelocator.co.in/guide/<?php echo $slug; ?></loc>
<changefreq>weekly</changefreq>
<priority>0.8</priority>
</url>

<?php } ?>

</urlset>