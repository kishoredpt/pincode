<section>
    <h1>SEO Engine Dashboard</h1>
    <div style="display:grid;grid-template-columns:repeat(3,minmax(160px,1fr));gap:12px;">
        <article><h3>Generated Pages</h3><p><?= (int) $stats['generated_pages'] ?></p></article>
        <article><h3>Pending Queue</h3><p><?= (int) $stats['pending_queue'] ?></p></article>
        <article><h3>Failed Queue</h3><p><?= (int) $stats['failed_queue'] ?></p></article>
    </div>

    <h2>Latest Generated Pages</h2>
    <table>
        <thead><tr><th>Path</th><th>Type</th><th>Generated At</th></tr></thead>
        <tbody>
            <?php foreach ($latestPages as $page): ?>
                <tr>
                    <td><?= htmlspecialchars($page['page_path']) ?></td>
                    <td><?= htmlspecialchars($page['entity_type']) ?></td>
                    <td><?= htmlspecialchars($page['last_generated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Sitemap Status</h2>
    <ul>
        <?php foreach ($sitemaps as $sitemap): ?>
            <li><?= htmlspecialchars($sitemap['file']) ?> — <?= $sitemap['exists'] ? 'available (' . $sitemap['updated_at'] . ')' : 'missing' ?></li>
        <?php endforeach; ?>
    </ul>
</section>
