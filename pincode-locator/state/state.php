<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seo.php';
$slug = sanitizeInput($_GET['slug'] ?? '');
$state = fetchOne($pdo, 'SELECT * FROM states WHERE slug = :slug', [':slug' => $slug]);
if (!$state) { http_response_code(404); exit('State not found'); }
$meta = ['title' => $state['state_name'] . ' PIN Code List by District', 'description' => 'Browse all districts and post offices in ' . $state['state_name'], 'canonical' => siteUrl('state/' . $state['slug'] . '-pincode')];
$districts = fetchAll($pdo, 'SELECT district_name, slug FROM districts WHERE state_id = :sid ORDER BY district_name', [':sid' => $state['state_id']]);
$popular = fetchAll($pdo, 'SELECT office_name,pincode FROM post_offices WHERE state_id = :sid ORDER BY pincode LIMIT 12', [':sid' => $state['state_id']]);
$faqs = [['q' => 'How many districts are listed?', 'a' => 'All mapped districts with available postal data are listed.'], ['q' => 'Can I search post offices directly?', 'a' => 'Yes, use the homepage search to jump to a pincode page.']];
require __DIR__ . '/../includes/header.php';
?>
<script type="application/ld+json"><?= breadcrumbSchema([['name'=>'Home','url'=>siteUrl()],['name'=>$state['state_name'],'url'=>siteUrl('state/' . $state['slug'] . '-pincode')]]); ?></script>
<script type="application/ld+json"><?= faqSchema($faqs); ?></script>
<h1><?= e($state['state_name']); ?> Pincode Directory</h1>
<p><?= e(introText($state['state_name'], 320)); ?></p>
<h2>Districts in <?= e($state['state_name']); ?></h2><div class="row g-2"><?php foreach($districts as $d): ?><div class="col-md-3"><a class="card p-2" href="<?= e(siteUrl('district/' . $d['slug'] . '-pincode')); ?>"><?= e($d['district_name']); ?></a></div><?php endforeach; ?></div>
<h2 class="mt-4">Popular Pincodes</h2><ul><?php foreach($popular as $p): ?><li><a href="<?= e(siteUrl('pincode/' . $p['pincode'] . '-' . slugify($p['office_name']))); ?>"><?= e($p['office_name'] . ' - ' . $p['pincode']); ?></a></li><?php endforeach; ?></ul>
<h2>Postal divisions</h2><p>State-level sorting operations run through divisions, regions, and circles for efficient delivery routing and monitoring.</p>
<h2>FAQ</h2><?php foreach($faqs as $f): ?><details><summary><?= e($f['q']); ?></summary><?= e($f['a']); ?></details><?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
