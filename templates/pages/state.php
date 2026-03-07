<h1><?= htmlspecialchars($data['summary']['statename'], ENT_QUOTES, 'UTF-8') ?> PIN Code Directory</h1>
<p>Districts: <?= (int) $data['summary']['district_count'] ?> | Offices: <?= (int) $data['summary']['office_count'] ?></p>
<h2>Districts</h2>
<ul><?php foreach ($data['districts'] as $district): ?><li><a href="/district/<?= htmlspecialchars($district['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($district['district'], ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?></ul>
