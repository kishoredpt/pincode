<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/seo.php';
$meta = $meta ?? [
    'title' => 'India Pincode Locator',
    'description' => 'Search all Indian post offices and PIN codes.',
    'canonical' => siteUrl(),
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <?= seoTags($meta); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(siteUrl('assets/css/style.css')); ?>" rel="stylesheet">
    <script type="application/ld+json"><?= organizationSchema(); ?></script>
    <script type="application/ld+json"><?= websiteSchema(); ?></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= e(siteUrl()); ?>">India Pincode Locator</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
        <div id="menu" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= e(siteUrl()); ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(siteUrl('blog/blog-index.php')); ?>">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(siteUrl('legal/about.php')); ?>">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(siteUrl('legal/contact.php')); ?>">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
