<?php
header("Content-Type: application/xml");

$base="https://pincodelocator.co.in";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<sitemap>
<loc><?=$base?>/sitemaps/states.php</loc>
</sitemap>

<sitemap>
<loc><?=$base?>/sitemaps/districts.php</loc>
</sitemap>

<sitemap>
<loc><?=$base?>/sitemaps/postoffices.php</loc>
</sitemap>

<sitemap>
<loc><?=$base?>/sitemaps/articles.php</loc>
</sitemap>

</sitemapindex>