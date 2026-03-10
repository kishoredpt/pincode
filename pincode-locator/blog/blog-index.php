<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
$meta=['title'=>'PIN Code Blog - India Pincode Locator','description'=>'Guides on Indian postal system and pincode lookup','canonical'=>siteUrl('blog/blog-index.php')];
require __DIR__ . '/../includes/header.php';
$articles=fetchAll($pdo,'SELECT title,slug,meta_description,created_at FROM articles ORDER BY created_at DESC LIMIT 100');
?>
<h1>Postal Knowledge Base</h1>
<div class="row g-3"><?php foreach($articles as $a): ?><div class="col-md-4"><article class="card h-100 p-3"><h2 class="h5"><a href="<?= e(siteUrl('blog/article.php?slug=' . $a['slug'])); ?>"><?= e($a['title']); ?></a></h2><p><?= e($a['meta_description']); ?></p><small><?= e($a['created_at']); ?></small></article></div><?php endforeach; ?></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
