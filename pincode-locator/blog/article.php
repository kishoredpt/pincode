<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seo.php';
$slug=sanitizeInput($_GET['slug'] ?? '');
$article=fetchOne($pdo,'SELECT * FROM articles WHERE slug=:slug',[':slug'=>$slug]);
if(!$article){http_response_code(404);exit('Article not found');}
$meta=['title'=>$article['meta_title'],'description'=>$article['meta_description'],'canonical'=>siteUrl('blog/' . $article['slug'])];
require __DIR__ . '/../includes/header.php';
$related=fetchAll($pdo,'SELECT office_name,pincode FROM post_offices ORDER BY RAND() LIMIT 5');
?>
<article>
<h1><?= e($article['title']); ?></h1>
<div><?= $article['content']; ?></div>
<h2>Related pincode resources</h2><ul><?php foreach($related as $r): ?><li><a href="<?= e(siteUrl('pincode/' . $r['pincode'] . '-' . slugify($r['office_name']))); ?>"><?= e($r['office_name'] . ' - ' . $r['pincode']); ?></a></li><?php endforeach; ?></ul>
</article>
<script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'Article','headline'=>$article['title'],'description'=>$article['meta_description'],'datePublished'=>$article['created_at'],'mainEntityOfPage'=>siteUrl('blog/' . $article['slug'])], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT); ?></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
