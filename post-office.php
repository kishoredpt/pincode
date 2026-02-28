<?php
require_once "config/db.php";
include "includes/header.php";
?>

<div class="container">

<h1>Postal Knowledge Hub</h1>

<p>
Learn about Indian PIN Codes, postal systems,
delivery networks, and address standards used
across India.
</p>

<?php
$res=$conn->query("
SELECT title,slug,created_at
FROM blog_posts
ORDER BY id DESC
LIMIT 20
");

while($row=$res->fetch_assoc()){
?>

<div class="blog-card">
<a href="/article/<?php echo $row['slug']; ?>">
<h3><?php echo $row['title']; ?></h3>
</a>
<p><?php echo date("d M Y",strtotime($row['created_at'])); ?></p>
</div>

<?php } ?>

</div>

<?php include "includes/footer.php"; ?>
