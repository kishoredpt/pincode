<?php

/* ===============================
   SEO ENGINE – PHASE 4 FINAL
================================ */

$siteName = "India Pincode Locator";
$baseURL  = "https://pincodelocator.co.in";

$pageTitle = $pageTitle ?? $siteName;
$metaDescription = $metaDescription ??
"Search Indian Post Office details, PIN Codes, districts and states across India.";

$currentURL =
(isset($_SERVER['HTTPS']) ? "https://" : "http://") .
$_SERVER['HTTP_HOST'] .
$_SERVER['REQUEST_URI'];

/* CANONICAL */
$canonical = strtok($currentURL,'?');

/* PAGINATION */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$prevURL = $page>1 ? $canonical.'?page='.($page-1) : '';
$nextURL = $canonical.'?page='.($page+1);

?>
<title><?=htmlspecialchars($pageTitle)?></title>

<meta name="description" content="<?=htmlspecialchars($metaDescription)?>">
<link rel="canonical" href="<?=$canonical?>" />

<?php if($prevURL): ?>
<link rel="prev" href="<?=$prevURL?>">
<?php endif; ?>

<link rel="next" href="<?=$nextURL?>">