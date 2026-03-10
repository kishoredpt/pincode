<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
$pincode = sanitizeInput($_GET['pincode'] ?? '');
$officeSlug = sanitizeInput($_GET['office'] ?? '');
$row = fetchOne($pdo, 'SELECT po.*, d.district_name,d.slug AS district_slug, s.state_name,s.slug AS state_slug FROM post_offices po JOIN districts d ON po.district_id=d.district_id JOIN states s ON po.state_id=s.state_id WHERE po.pincode=:pin AND (:office = "" OR po.office_name LIKE :officeLike) LIMIT 1', [':pin'=>$pincode, ':office'=>$officeSlug, ':officeLike'=>str_replace('-', ' ', $officeSlug) . '%']);
if (!$row) { http_response_code(404); exit('Pincode page not found'); }
$meta = ['title' => $row['office_name'] . ' - ' . $row['pincode'] . ' Post Office Details', 'description' => 'Get complete postal details for ' . $row['office_name'] . ' post office.', 'canonical' => siteUrl('pincode/' . $row['pincode'] . '-' . slugify($row['office_name']))];
require __DIR__ . '/../includes/header.php';
$nearby = fetchAll($pdo, 'SELECT office_name,pincode FROM post_offices WHERE district_id=:did AND pincode<>:pin LIMIT 10', [':did'=>$row['district_id'], ':pin'=>$row['pincode']]);
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= e(siteUrl()); ?>">Home</a></li><li class="breadcrumb-item"><a href="<?= e(siteUrl('state/' . $row['state_slug'] . '-pincode')); ?>"><?= e($row['state_name']); ?></a></li><li class="breadcrumb-item"><a href="<?= e(siteUrl('district/' . $row['district_slug'] . '-pincode')); ?>"><?= e($row['district_name']); ?></a></li><li class="breadcrumb-item active"><?= e($row['pincode']); ?></li></ol></nav>
<h1><?= e($row['office_name']); ?>, <?= e($row['pincode']); ?></h1>
<table class="table table-striped"><tr><th>Office Name</th><td><?= e($row['office_name']); ?></td></tr><tr><th>PIN Code</th><td><?= e($row['pincode']); ?></td></tr><tr><th>Office Type</th><td><?= e($row['office_type']); ?></td></tr><tr><th>Delivery Status</th><td><?= e($row['delivery_status']); ?></td></tr><tr><th>District</th><td><?= e($row['district_name']); ?></td></tr><tr><th>State</th><td><?= e($row['state_name']); ?></td></tr><tr><th>Division</th><td><?= e($row['division']); ?></td></tr><tr><th>Region</th><td><?= e($row['region']); ?></td></tr><tr><th>Circle</th><td><?= e($row['circle']); ?></td></tr></table>
<iframe class="w-100 rounded border" style="height:320px" loading="lazy" src="https://maps.google.com/maps?q=<?= e((string) $row['latitude']); ?>,<?= e((string) $row['longitude']); ?>&z=14&output=embed"></iframe>
<h2 class="mt-4">About this pincode</h2><p><?= e(introText($row['pincode'] . ' pincode area', 220)); ?></p>
<h2>Areas served by this post office</h2><p>This office serves nearby neighborhoods, commercial zones, and residential blocks mapped under this delivery beat.</p>
<h2>Postal services available</h2><p>Mail booking, parcel delivery, speed post, money transfer, and identity related support are typically available.</p>
<h2>Nearby pincodes list</h2><ul><?php foreach($nearby as $n): ?><li><a href="<?= e(siteUrl('pincode/' . $n['pincode'] . '-' . slugify($n['office_name']))); ?>"><?= e($n['office_name'] . ' - ' . $n['pincode']); ?></a></li><?php endforeach; ?></ul>
<h2>Frequently asked questions</h2><details><summary>Is this pincode urban or rural?</summary>Check office type and delivery status in the details table above.</details>
<?php require __DIR__ . '/../includes/footer.php'; ?>
