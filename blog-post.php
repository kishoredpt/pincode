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
  <h2>Why This Article Matters</h2>
  <p>
    Our blog posts are written to help users apply postal concepts in real tasks.
    Whether you are preparing a shipping label, verifying a serviceable address, or reviewing location data for operations,
    the objective is to reduce errors before dispatch.
    We recommend combining article guidance with page-level PIN code lookup whenever an address is time-sensitive.
  </p>

  <h2>Continue Reading</h2>
  <ul>
    <li><a href="/blog.php">Browse all postal guides</a></li>
    <li><a href="/">Search PIN code and post office pages</a></li>
    <li><a href="/author.php">Meet the editorial team</a></li>
    <li><a href="/editorial-policy.php">Read our editorial policy</a></li>
  </ul>
</section>


<section style="margin-top:30px;">
  <h2>Editorial Quality Note</h2>
  <p>
    This article is part of our human-reviewed postal knowledge series.
    We aim to explain concepts clearly and avoid generic filler.
    If you find a section that needs correction or expansion, contact our team so we can improve it for future readers.
  </p>

  <h2>Trust and Policy References</h2>
  <p>
    For transparency, this article is connected to our public trust documentation.
    Please review the <a href="/about.php">About</a>, <a href="/author.php">Author</a>,
    <a href="/editorial-policy.php">Editorial Policy</a>, <a href="/content-guidelines.php">Content Guidelines</a>,
    and <a href="/disclaimer.php">Disclaimer</a> pages for full methodology and limitations.
  </p>
</section>


<section style="margin-top:30px;">
  <h2>Apply the Guidance Step by Step</h2>
  <p>
    Start by identifying your exact task: personal shipment, ecommerce dispatch, form submission, or serviceability check.
    Next, use the ideas in this article with a live PIN code lookup result from our main directory.
    Finally, confirm critical details through official channels if consequences of mismatch are high.
    This layered workflow reduces mistakes and improves confidence in address quality.
  </p>
</section>


<section style="margin-top:30px;">
  <h2>Need Help or Have a Correction?</h2>
  <p>
    If you notice unclear wording or a factual issue, write to our editorial team with the page URL and specific correction note.
    User feedback is part of our quality loop and helps keep articles accurate, practical, and easy to follow.
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

<?php include "includes/footer.php"; ?>
