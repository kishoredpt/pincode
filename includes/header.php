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

<title><?= htmlspecialchars($resolvedTitle, ENT_QUOTES, 'UTF-8'); ?></title>

<meta name="description" content="<?= htmlspecialchars($resolvedDescription, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="robots" content="<?= htmlspecialchars($resolvedRobots, ENT_QUOTES, 'UTF-8'); ?>">

<link rel="canonical" href="<?= htmlspecialchars($resolvedCanonical, ENT_QUOTES, 'UTF-8'); ?>">

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

<?php if (isset($lastReviewed) && trim((string) $lastReviewed) !== ''): ?>
<div class="container" style="padding-top:12px;">
<p style="font-size:14px;color:#475569;">Last reviewed: <?= htmlspecialchars($lastReviewed, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>

</div>
