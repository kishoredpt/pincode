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

$pageTitle = $article['title'] . ' | India Pincode Locator';
$metaDescription = substr(trim(strip_tags($article['content'])), 0, 155);
$canonicalPath = '/blog/' . rawurlencode($article['slug']);

include "includes/header.php";
?>

<div class="container" style="padding:40px 0;">

<h1><?= htmlspecialchars($article['title']); ?></h1>
<p style="color:#475569;">Published: <?= htmlspecialchars(date('F j, Y', strtotime($article['created_at']))); ?> | Author: India Pincode Locator Editorial Team</p>

<div class="article">
<?= $article['content']; ?>
</div>

<section style="margin-top:30px;">
  <h2>Editorial Note</h2>
  <p>
    This guide is part of our postal knowledge series and is meant to support real-world address use, shipping preparation,
    and serviceability checks. We keep the post-article guidance concise so the main article remains the primary source of value.
  </p>

  <h2>Related Resources</h2>
  <ul>
    <li><a href="/blog.php">Browse all postal guides</a></li>
    <li><a href="/">Search PIN code and post office pages</a></li>
    <li><a href="/author.php">Meet the editorial team</a></li>
    <li><a href="/editorial-policy.php">Read our editorial policy</a></li>
  </ul>

  <p style="margin-top:18px;">
    If you spot unclear wording or a factual issue, send us the page URL and correction note.
    For transparency, this article sits alongside our <a href="/about.php">About</a>,
    <a href="/content-guidelines.php">Content Guidelines</a>, and <a href="/disclaimer.php">Disclaimer</a> pages.
  </p>
</section>

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
<p>Written by <a href="/author.php">Chowdary</a></p>
<?php include "includes/footer.php"; ?>
