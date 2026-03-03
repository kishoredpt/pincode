<?php
include("config/db.php");
$res=$conn->query("SELECT title,slug,meta_description FROM blog_posts WHERE status='published' ORDER BY id DESC");
include("includes/header.php");
echo "<div class='container'><h1>Blog</h1>";
while($r=$res->fetch_assoc()){
echo "<div class='card'>";
echo "<h3><a href='/blog/".$r['slug']."/'>".$r['title']."</a></h3>";
echo "<p>".$r['meta_description']."</p>";
echo "</div>";
}
echo "</div>";
include("includes/footer.php");