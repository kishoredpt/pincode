<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
$urls = [];
$q = $conn->query("SELECT slug FROM articles");

if ($q) {
    while ($row = $q->fetch_assoc()) {
        $slug = $row['slug'];
        $url = "https://pincodelocator.co.in/blog-post.php?slug=" . urlencode($slug);
        $urls[$url] = true;
    }
}

$staticArticles = require "../includes/static-articles.php";
foreach ($staticArticles as $static) {
    $url = "https://pincodelocator.co.in/blog-post.php?slug=" . urlencode($static['slug']);
    $urls[$url] = true;
}

foreach (array_keys($urls) as $url):
?>
<url>
<loc><?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.6</priority>
</url>
<?php endforeach; ?>

</urlset>
