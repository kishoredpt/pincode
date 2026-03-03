<?php
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
if ($host === 'www.pincodelocator.co.in') {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/blog';
    header('Location: https://pincodelocator.co.in' . $requestUri, true, 301);
    exit;
}

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    $requestPath = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (preg_match('#^blog/([a-zA-Z0-9-]+)$#', $requestPath, $matches)) {
        $slug = $matches[1];
    }
}

$legacySlugMap = [
    'what-is-pin-code-india' => 'what-is-pin-code',
    'what-is-pin-code-system-in-india' => 'what-is-pin-code',
];
if (isset($legacySlugMap[$slug])) {
    header('Location: /blog/' . $legacySlugMap[$slug], true, 301);
    exit;
}

if ($slug === '') {
    header("Location:/blog", true, 302);
    exit;
}

$article = null;
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

if (!$article) {
    if (str_starts_with($slug, 'what-is-pin-code')) {
        header('Location: /blog/what-is-pin-code', true, 301);
        exit;
    }

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

<?php include "includes/footer.php"; ?>
