<h1><?= htmlspecialchars($data['summary']['officename'], ENT_QUOTES, 'UTF-8') ?> Area</h1>
<ul><?php foreach ($data['offices'] as $office): ?><li><a href="/pincode/<?= htmlspecialchars($office['pincode'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($office['officename'], ENT_QUOTES, 'UTF-8') ?></a> (<?= htmlspecialchars($office['district'], ENT_QUOTES, 'UTF-8') ?>)</li><?php endforeach; ?></ul>
