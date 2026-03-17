<?php
define('ALLOW_DB_OPTIONAL', true);
require_once __DIR__ . '/config/db.php';
$slug = trim($_GET['slug'] ?? '');

$legacySlugMap = [
    'what-is-pin-code-system-in-india' => 'what-is-pin-code',
];
if (isset($legacySlugMap[$slug])) {
    header('Location: /blog-post.php?slug=' . $legacySlugMap[$slug], true, 301);
    exit;
}

if ($slug === '') {
    header("Location:/blog.php", true, 302);
    exit;
}

$article = null;
$dbConn = null;
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $dbConn = $conn;
}

if ($dbConn) {
    $stmt = $dbConn->prepare("SELECT * FROM articles WHERE slug=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $article = $res->fetch_assoc();
        }
    }
}

if (!$article) {
    $staticArticles = require "includes/static-articles.php";
    foreach ($staticArticles as $static) {
        if ($static['slug'] === $slug) {
            $article = [
                'title' => $static['title'],
                'slug' => $static['slug'],
                'content' => $static['content'],
                'created_at' => $static['created_at'],
                'author_name' => 'Editorial Team',
                'source' => 'static',
            ];
            break;
        }
    }
}

if (!$article) {
    header("Location:/404.php");
    exit;
}

$pageTitle = $article['title'];
$metaDescription = substr(strip_tags($article['content']), 0, 155);

include "includes/header.php";
?>

<div class="container">

<h1><?= htmlspecialchars($article['title']); ?></h1>
<p style="color:#475569;">Published: <?= htmlspecialchars(date('F j, Y', strtotime($article['created_at']))); ?> | Author: India Pincode Locator Editorial Team</p>

<div class="article">
<?= $article['content']; ?>
</div>

</div>

<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"Article",
"headline":"<?= addslashes($article['title']); ?>",
"datePublished":"<?= $article['created_at']; ?>",
"dateModified":"<?= $article['created_at']; ?>",
"author":{
 "@type":"Organization",
 "name":"India Pincode Locator"
},
"publisher":{
 "@type":"Organization",
 "name":"India Pincode Locator",
 "logo":{
   "@type":"ImageObject",
   "url":"https://pincodelocator.co.in/logo.png"
 }
}
}
</script>
<p class="author-line">
Written by <a href="/author.php">India Pincode Locator Editorial Team</a>
</p>

<?php include "includes/footer.php"; ?>
