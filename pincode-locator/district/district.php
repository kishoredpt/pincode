<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seo.php';
$slug = sanitizeInput($_GET['slug'] ?? '');
$district = fetchOne($pdo, 'SELECT d.*, s.state_name, s.slug AS state_slug FROM districts d JOIN states s ON d.state_id=s.state_id WHERE d.slug=:slug', [':slug' => $slug]);
if (!$district) { http_response_code(404); exit('District not found'); }
$meta = ['title' => $district['district_name'] . ' PIN Code and Post Office List', 'description' => 'Find all post offices in ' . $district['district_name'], 'canonical' => siteUrl('district/' . $district['slug'] . '-pincode')];
$postOffices = fetchAll($pdo, 'SELECT office_name,pincode,office_type,delivery_status FROM post_offices WHERE district_id=:did ORDER BY office_name LIMIT 500', [':did'=>$district['district_id']]);
$nearby = fetchAll($pdo, 'SELECT district_name, slug FROM districts WHERE state_id=:sid AND district_id<>:did ORDER BY district_name LIMIT 10', [':sid'=>$district['state_id'], ':did'=>$district['district_id']]);
$faqs = [['q'=>'What services are available?', 'a'=>'Speed Post, Registered Post, Parcels, Money Orders, and retail services vary by office type.']];
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($district['district_name']); ?> District Pincode List</h1>
<p><?= e(introText($district['district_name'] . ' district', 260)); ?></p>
<p><a href="<?= e(siteUrl('state/' . $district['state_slug'] . '-pincode')); ?>">Back to <?= e($district['state_name']); ?> state page</a></p>
<h2>Post Offices</h2>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Office</th><th>PIN</th><th>Type</th><th>Delivery</th></tr></thead><tbody><?php foreach($postOffices as $po): ?><tr><td><a href="<?= e(siteUrl('pincode/' . $po['pincode'] . '-' . slugify($po['office_name']))); ?>"><?= e($po['office_name']); ?></a></td><td><?= e($po['pincode']); ?></td><td><?= e($po['office_type']); ?></td><td><?= e($po['delivery_status']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<h2>Postal services info</h2><p>District post offices connect residents to banking, philately, and document logistics through branch and head office coordination.</p>
<h2>Nearby districts</h2><ul><?php foreach($nearby as $n): ?><li><a href="<?= e(siteUrl('district/' . $n['slug'] . '-pincode')); ?>"><?= e($n['district_name']); ?></a></li><?php endforeach; ?></ul>
<h2>FAQ</h2><?php foreach($faqs as $f): ?><details><summary><?= e($f['q']); ?></summary><?= e($f['a']); ?></details><?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
