<?php
header("Content-Type: application/xml; charset=utf-8");

$base = "https://pincodelocator.co.in";
$articles = require __DIR__ . "/../includes/static-articles.php";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($articles as $article): ?>
<url>
<loc><?= htmlspecialchars($base . '/blog-post.php?slug=' . urlencode($article['slug']), ENT_QUOTES, 'UTF-8') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.6</priority>
</url>
<?php endforeach; ?>
</urlset>
