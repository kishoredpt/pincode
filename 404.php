<?php
http_response_code(404);

$pageTitle = "404 - Page Not Found | India Pincode Locator";
$metaDescription = "The requested page could not be found. Use pincode search, state pages, or blog guides to continue browsing.";
$metaRobots = "noindex,follow";

include 'includes/header.php';
?>

<div class="container" style="padding:50px 0;max-width:900px;">
  <h1>404 - Page Not Found</h1>
  <p>The page you requested is unavailable or moved.</p>

  <div class="card">
    <h2>Try these sections</h2>
    <ul>
      <li><a href="/">Homepage and pincode search</a></li>
      <li><a href="/blog.php">Postal guides and articles</a></li>
      <li><a href="/about.php">About and trust information</a></li>
      <li><a href="/sitemap_index.php">XML sitemap index</a></li>
    </ul>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
