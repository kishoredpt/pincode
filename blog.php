<?php
require_once "config/db.php";

$pageTitle = "Blog | India Pincode Locator";
$metaDescription = "Guides and practical articles on Indian pincodes, post offices, and addressing best practices.";

$articles = require "includes/static-articles.php";

usort($articles, static function (array $a, array $b): int {
    return strcmp($b['created_at'], $a['created_at']);
});

include "includes/header.php";

echo "<div class='container'><h1>Blog</h1>";
echo "<p class='mb-8'>Read practical postal guides written for Indian users, e-commerce teams, and logistics operators.</p>";

foreach ($articles as $article) {
    $desc = substr(strip_tags($article['content']), 0, 180);
    echo "<div class='card'>";
    echo "<h3><a href='/blog-post.php?slug=" . urlencode($article['slug']) . "'>" . htmlspecialchars($article['title']) . "</a></h3>";
    echo "<p>" . htmlspecialchars($desc) . "...</p>";
    echo "</div>";
}

echo "</div>";
include "includes/footer.php";
