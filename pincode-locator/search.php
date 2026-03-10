<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$q = sanitizeInput($_GET['q'] ?? '');
$meta = ['title' => 'Search Results - India Pincode Locator', 'description' => 'Search post office and pincode records.', 'canonical' => siteUrl('search.php?q=' . urlencode($q))];
require __DIR__ . '/includes/header.php';
$rows = [];
if ($q !== '') {
    $rows = fetchAll($pdo, 'SELECT po.*, d.district_name, s.state_name FROM post_offices po JOIN districts d ON po.district_id=d.district_id JOIN states s ON po.state_id=s.state_id WHERE po.pincode LIKE :q OR po.office_name LIKE :name OR d.district_name LIKE :name ORDER BY po.pincode LIMIT 100', [':q' => $q . '%', ':name' => '%' . $q . '%']);
}
?>
<h1>Search Results</h1>
<p>Results for: <strong><?= e($q); ?></strong></p>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Office</th><th>PIN</th><th>District</th><th>State</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><a href="<?= e(siteUrl('pincode/pincode.php?pincode=' . $r['pincode'] . '&office=' . slugify($r['office_name']))); ?>"><?= e($r['office_name']); ?></a></td><td><?= e($r['pincode']); ?></td><td><a href="<?= e(siteUrl('district/district.php?slug=' . slugify($r['district_name']))); ?>"><?= e($r['district_name']); ?></a></td><td><a href="<?= e(siteUrl('state/state.php?slug=' . slugify($r['state_name']))); ?>"><?= e($r['state_name']); ?></a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
