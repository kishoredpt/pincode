<?php /** @var array $pageData */ ?>
<article>
  <nav aria-label="breadcrumb"><?= $pageData['breadcrumb_html'] ?></nav>
  <h1><?= htmlspecialchars($pageData['heading']) ?></h1>
  <p><?= nl2br(htmlspecialchars($pageData['content'])) ?></p>
  <section><h2>More pincodes in district</h2><ul><?php foreach ($pageData['district_links'] as $link): ?><li><a href="<?= $link['path'] ?>"><?= htmlspecialchars($link['label']) ?></a></li><?php endforeach; ?></ul></section>
  <section><h2>More pincodes in state</h2><ul><?php foreach ($pageData['state_links'] as $link): ?><li><a href="<?= $link['path'] ?>"><?= htmlspecialchars($link['label']) ?></a></li><?php endforeach; ?></ul></section>
  <script type="application/ld+json"><?= $pageData['schema_json'] ?></script>
</article>
