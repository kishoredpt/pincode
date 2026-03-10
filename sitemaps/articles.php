<?php
header("Content-Type: application/xml; charset=UTF-8");

$articles = require __DIR__ . "/../includes/static-articles.php";

$base = "https://pincodelocator.co.in";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php if (!empty($articles) && is_array($articles)): ?>
    <?php foreach ($articles as $article): ?>
        <url>
            <loc><?= $base ?>/blog-post.php?slug=<?= htmlspecialchars($article['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>
<?php endif; ?>

</urlset>
