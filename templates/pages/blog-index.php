<h1>Postal Knowledge Blog</h1>
<ul><?php foreach ($posts as $post): ?><li><a href="/blog/<?= htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?></ul>
