<h1>PIN Code <?= htmlspecialchars($data['summary']['pincode'], ENT_QUOTES, 'UTF-8') ?></h1>
<p><?= htmlspecialchars($data['summary']['district'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($data['summary']['statename'], ENT_QUOTES, 'UTF-8') ?></p>
<div><?= $generated ?></div>
<h2>Post Offices</h2>
<ul>
<?php foreach ($data['offices'] as $office): ?>
  <li><?= htmlspecialchars($office['officename'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($office['delivery'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
</ul>
<h3>Nearby PIN Codes</h3>
<ul><?php foreach ($nearby as $row): ?><li><a href="/pincode/<?= htmlspecialchars($row['pincode'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['pincode'], ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?></ul>
<h3>Other Districts</h3>
<ul><?php foreach ($districtLinks as $row): ?><li><a href="/district/<?= htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['district'], ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?></ul>
<h3>Other States</h3>
<ul><?php foreach ($stateLinks as $row): ?><li><a href="/state/<?= htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['statename'], ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?></ul>
<script type="application/ld+json">
<?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>[['@type'=>'Question','name'=>'What area does PIN code '.$data['summary']['pincode'].' cover?','acceptedAnswer'=>['@type'=>'Answer','text'=>'It covers locations in '.$data['summary']['district'].', '.$data['summary']['statename'].'.']],['@type'=>'Question','name'=>'How many post offices use this pin code?','acceptedAnswer'=>['@type'=>'Answer','text'=>'There are '.count($data['offices']).' listed offices for this pincode.']]]], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
</script>
