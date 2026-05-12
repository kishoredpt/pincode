<?php
$resolvedTitle = isset($pageTitle) && trim((string)$pageTitle) !== ''
    ? $pageTitle
    : 'Pincode Locator India';

$resolvedDescription = isset($metaDescription) && trim((string)$metaDescription) !== ''
    ? $metaDescription
    : 'Search Indian PIN Codes, Post Offices, States and District wise postal information across India. Updated postal database covering 1.5+ lakh post offices.';

$resolvedRobots = isset($metaRobots) && trim((string)$metaRobots) !== ''
    ? $metaRobots
    : 'index, follow';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$canonicalPath = strtok($requestUri, '?');
if ($canonicalPath === false || $canonicalPath === '') {
    $canonicalPath = '/';
}
$resolvedCanonical = 'https://pincodelocator.co.in' . $canonicalPath;
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#4f46e5">

<title><?= htmlspecialchars($resolvedTitle, ENT_QUOTES, 'UTF-8'); ?></title>

<meta name="description" content="<?= htmlspecialchars($resolvedDescription, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="robots" content="<?= htmlspecialchars($resolvedRobots, ENT_QUOTES, 'UTF-8'); ?>">
<link rel="canonical" href="<?= htmlspecialchars($resolvedCanonical, ENT_QUOTES, 'UTF-8'); ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="PincodeLocator.co.in">
<meta property="og:title" content="<?= htmlspecialchars($resolvedTitle, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:description" content="<?= htmlspecialchars($resolvedDescription, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:url" content="<?= htmlspecialchars($resolvedCanonical, ENT_QUOTES, 'UTF-8'); ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($resolvedTitle, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($resolvedDescription, ENT_QUOTES, 'UTF-8'); ?>">
<?php $adsense = include __DIR__ . '/../config/adsense.php'; ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo $adsense['publisher_id']; ?>" crossorigin="anonymous"></script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "PincodeLocator.co.in",
  "url": "https://pincodelocator.co.in/",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://pincodelocator.co.in/?route={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>

<link rel="stylesheet" href="/assets/style.css">

</head>

<body>

<header class="main-header">

<div class="container">

<div class="logo">
<a href="/">📮 PincodeLocator.co.in</a>
</div>

<nav class="nav-menu">
<a href="/">Home</a>
<a href="/blog.php">Articles</a>
<a href="/about.php">About</a>
<a href="/author.php">Author</a>
<a href="/contact.php">Contact</a>
</nav>

</div>

</header>

<div class="site-authority">

<div class="container">

<p>
India's independent postal information platform helping users
discover accurate PIN Code data, delivery offices,
and district-wise postal coverage across all Indian states.
</p>

</div>

</div>

<div class="side-ad side-ad-left" aria-label="Happy Heavens Farm Houses advertisement">
<a href="https://www.instagram.com/svcs_infradevelopers?igsh=MW10bDFxYmhjdnNzNQ==" title="Happy Heavens Farm Houses at Aler" target="_blank" rel="noopener noreferrer">
<span class="ad-badge">Sponsored</span>
<h3><span class="ad-brand">Happy Heavens</span><br><span class="ad-brand">Farm Houses</span></h3>
<p>at Aler • SVCS Infra Developers</p>
<p class="ad-highlight">Own your dream farmhouse with ultra luxury features.</p>
<div class="ad-images" aria-label="Happy Heavens Farm Houses ad gallery">
<img src="/assets/images/ads/happy-heavens-ad-1.jpg" alt="Happy Heavens Farm House advertisement artwork" loading="lazy">
<img src="/assets/images/ads/happy-heavens-ad-2.jpg" alt="Happy Heavens Farm House sample villa" loading="lazy">
</div>
</a>
</div>

<div class="side-ad side-ad-right" aria-label="Happy Heavens Farm Houses advertisement">
<a href="https://www.instagram.com/svcs_infradevelopers?igsh=MW10bDFxYmhjdnNzNQ==" title="Happy Heavens Farm Houses at Aler" target="_blank" rel="noopener noreferrer">
<span class="ad-badge">Sponsored</span>
<h3><span class="ad-brand">Happy Heavens</span><br><span class="ad-brand">Farm Houses</span></h3>
<p>at Aler • SVCS Infra Developers</p>
<p class="ad-highlight">Visit Aler farmhouse project.</p>
<div class="ad-images" aria-label="Happy Heavens Farm Houses ad gallery">
<img src="/assets/images/ads/happy-heavens-ad-1.jpg" alt="Happy Heavens Farm House advertisement artwork" loading="lazy">
<img src="/assets/images/ads/happy-heavens-ad-2.jpg" alt="Happy Heavens Farm House sample villa" loading="lazy">
</div>
</a>
</div>
