<?php
require_once("config/db.php");

$pageTitle = "Blog | India Pincode Locator";
$metaDescription = "Guides and practical articles on Indian pincodes, post offices, and addressing best practices.";

$res=$conn->query("SELECT title,slug,content,created_at FROM articles ORDER BY created_at DESC LIMIT 200");
include("includes/header.php");

echo "<div class='container'><h1>Blog</h1>";

while($r=$res->fetch_assoc()){
$desc = substr(strip_tags($r['content']), 0, 160);
echo "<div class='card'>";
echo "<h3><a href='/blog-post.php?slug=".urlencode($r['slug'])."'>".htmlspecialchars($r['title'])."</a></h3>";
echo "<p>".htmlspecialchars($desc)."...</p>";
echo "</div>";
}

echo "</div>";
include("includes/footer.php");
