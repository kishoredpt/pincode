<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Blog | India Pincode Locator";
$metaDescription = "Guides and practical articles on Indian pincodes, post offices, and addressing best practices.";

$staticArticles = require "includes/static-articles.php";
$articles = [];

$res = $conn->query("SELECT title,slug,content,created_at FROM articles ORDER BY created_at DESC LIMIT 200");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $articles[$row['slug']] = [
            'title' => $row['title'],
            'slug' => $row['slug'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
            'source' => 'db',
        ];
    }
}

foreach ($staticArticles as $static) {
    if (!isset($articles[$static['slug']])) {
        $articles[$static['slug']] = [
            'title' => $static['title'],
            'slug' => $static['slug'],
            'content' => $static['content'],
            'created_at' => $static['created_at'],
            'source' => 'static',
        ];
    }
}

usort($articles, static function (array $a, array $b): int {
    return strcmp($b['created_at'], $a['created_at']);
});

include "includes/header.php";

echo "<div class='container'><h1>Blog</h1>";

echo "<p class='mb-8'>Read practical postal guides written for Indian users, e-commerce teams, and logistics operators.</p>";

foreach ($articles as $article) {
    $desc = substr(strip_tags($article['content']), 0, 180);
    echo "<div class='card'>";
    echo "<h3><a href='/blog/" . urlencode($article['slug']) . "'>" . htmlspecialchars($article['title']) . "</a></h3>";
    echo "<p>" . htmlspecialchars($desc) . "...</p>";
    echo "</div>";
}

echo "</div>";
include "includes/footer.php";
