<?php
$seo = $seo ?? ['title' => $appConfig['name'], 'description' => ''];
$canonical = $canonical ?? $appConfig['base_url'];
$breadcrumbs = $breadcrumbs ?? [['name' => 'Home', 'url' => '/']];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8') ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="/assets/style.css">
  <script type="application/ld+json">
  <?= json_encode(['@context'=>'https://schema.org','@type'=>'WebSite','name'=>$appConfig['name'],'url'=>$appConfig['base_url'],'potentialAction'=>['@type'=>'SearchAction','target'=>$appConfig['base_url'].'/?q={search_term_string}','query-input'=>'required name=search_term_string']], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
  </script>
  <script type="application/ld+json">
  <?= json_encode(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>array_map(fn($c,$i)=>['@type'=>'ListItem','position'=>$i+1,'name'=>$c['name'],'item'=>str_starts_with($c['url'],'http')?$c['url']:rtrim($appConfig['base_url'],'/').$c['url']],$breadcrumbs,array_keys($breadcrumbs))], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
  </script>
</head>
<body>
<header class="site-header">
  <div class="container">
    <a href="/" class="brand">India Pincode Locator</a>
    <nav>
      <a href="/">Home</a><a href="/blog">Blog</a><a href="/about">About</a><a href="/contact">Contact</a>
    </nav>
  </div>
</header>
<main class="container layout">
  <section class="content"><?php require $templateFile; ?></section>
  <aside class="sidebar">
    <div class="ad-slot">Ad Slot (disabled)</div>
  </aside>
</main>
<footer class="site-footer"><div class="container">© <?= date('Y') ?> <?= htmlspecialchars($appConfig['name'], ENT_QUOTES, 'UTF-8') ?></div></footer>
</body>
</html>
