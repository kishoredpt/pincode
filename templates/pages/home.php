<h1>Find PIN Code, State, District, and Post Office</h1>
<form method="get" action="/">
  <input type="search" name="q" value="<?= htmlspecialchars($query ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by pincode or location">
  <button type="submit">Search</button>
</form>
<?php if (!empty($results)): ?>
  <h2>Search Results</h2>
  <ul>
  <?php foreach ($results as $row): ?>
    <li><a href="/pincode/<?= htmlspecialchars($row['pincode'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(($row['officename'] ?? 'PIN') . ' - ' . $row['pincode'], ENT_QUOTES, 'UTF-8') ?></a></li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>
<h2>States</h2>
<ul>
<?php foreach ($states as $state): ?>
  <li><a href="/state/<?= htmlspecialchars($state['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($state['name'], ENT_QUOTES, 'UTF-8') ?></a></li>
<?php endforeach; ?>
</ul>
