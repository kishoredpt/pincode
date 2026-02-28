<?php
/* ===============================
   SEO ENGINE – PHASE UPGRADE
================================ */

$siteName = "India Pincode Locator";
$baseURL  = "https://pincodelocator.co.in";

$pageTitle = $pageTitle ?? $siteName;
$metaDescription = $metaDescription ??
"Search Indian Post Office details, PIN Codes, districts and states across India.";

$metaRobots = $metaRobots ?? 'index,follow';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$host = $_SERVER['HTTP_HOST'] ?? 'pincodelocator.co.in';
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$scheme = $https ? 'https://' : 'http://';

$currentURL = $scheme . $host . $requestUri;
$canonical = strtok($currentURL, '?');

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$totalPages = isset($totalPages) ? (int) $totalPages : null;
$prevURL = $page > 1 ? $canonical . '?page=' . ($page - 1) : '';
$nextURL = '';

if ($totalPages !== null) {
    if ($page < $totalPages) {
        $nextURL = $canonical . '?page=' . ($page + 1);
    }
} elseif ($page >= 1) {
    $nextURL = $canonical . '?page=' . ($page + 1);
}
?>
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta name="robots" content="<?= htmlspecialchars($metaRobots) ?>">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>" />

<?php if ($prevURL): ?>
<link rel="prev" href="<?= htmlspecialchars($prevURL) ?>">
<?php endif; ?>

<?php if ($nextURL): ?>
<link rel="next" href="<?= htmlspecialchars($nextURL) ?>">
<?php endif; ?>
