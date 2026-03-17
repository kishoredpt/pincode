<?php
define('ALLOW_DB_OPTIONAL', true);
require_once __DIR__ . '/config/db.php';
$pageTitle = "Postal Guides and Articles | India Pincode Locator";
$metaDescription = "Read practical, human-written guides on Indian PIN codes, post office workflows, address formatting, and delivery preparation.";

$staticArticles = require "includes/static-articles.php";
$articles = [];

$dbConn = null;
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $dbConn = $conn;
}

if ($dbConn) {
    $res = $dbConn->query("SELECT title,slug,content,created_at FROM articles ORDER BY created_at DESC LIMIT 200");
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

$articles = array_values($articles);
usort($articles, static function (array $a, array $b): int {
    return strcmp($b['created_at'], $a['created_at']);
});

include "includes/header.php";
?>

<div class="container" style="padding:40px 0;">
  <h1>Postal Guides and Articles</h1>

  <p>
    This section is built for readers who want more than a raw PIN code lookup.
    Our articles explain how Indian postal data is used in practical situations such as checkout address entry,
    courier handovers, business logistics, KYC form completion, and inter-state dispatch planning.
  </p>

  <p>
    Each guide is written in an editorial format focused on clarity and field-level utility.
    We avoid thin copy and instead provide context, examples, and actionable checks that users can apply immediately.
    If you are new to postal terminology, start with beginner guides and then move to operations-focused articles.
  </p>

  <h2>Latest Articles</h2>

  <?php foreach ($articles as $article): ?>
    <?php $desc = substr(trim(strip_tags($article['content'])), 0, 180); ?>
    <div class="card">
      <h3><a href="/blog-post.php?slug=<?= urlencode($article['slug']); ?>"><?= htmlspecialchars($article['title']); ?></a></h3>
      <p><?= htmlspecialchars($desc); ?>...</p>
      <p><small>Published on <?= htmlspecialchars(date('F j, Y', strtotime($article['created_at']))); ?></small></p>
    </div>
  <?php endforeach; ?>

  <h2>How to Use These Guides</h2>
  <p>
    If your goal is to confirm a specific delivery location, first run a direct search from the homepage,
    then use articles for interpretation support.
    If your goal is policy understanding, read our trust and standards pages to learn how our content is reviewed.
  </p>

  <ul>
    <li><a href="/">Run a fresh PIN code lookup</a></li>
    <li><a href="/author.php">View editorial team profile</a></li>
    <li><a href="/editorial-policy.php">Review editorial standards</a></li>
    <li><a href="/content-guidelines.php">Read content quality rules</a></li>
    <li><a href="/data-source.php">Understand data source methodology</a></li>
  </ul>

  <h2>Editorial Coverage Approach</h2>
  <p>
    Our coverage is intentionally practical.
    We prioritize topics that reduce shipping mistakes, improve address quality, and clarify postal terminology for non-expert readers.
    Rather than publishing trend-driven filler, we focus on evergreen questions people ask repeatedly when dealing with PIN codes and post offices.
  </p>

  <h2>How Articles Are Maintained</h2>
  <p>
    Older posts are reviewed and refreshed when guidance becomes unclear or user workflows change.
    We also improve cross-linking between lookup pages and educational content so readers can move from "search" to "understand" without confusion.
    This maintenance model helps preserve quality over time and supports better trust outcomes.
  </p>


  <h2>Reader Trust Commitment</h2>
  <p>
    We publish with a long-term trust mindset.
    That means clear author attribution, accessible policy links, and article updates when user behavior shows confusion points.
    If you find an outdated section or ambiguous statement, please contact us so we can correct it quickly and keep this archive dependable.
  </p>

</div>

<?php include "includes/footer.php"; ?>
