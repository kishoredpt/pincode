<?php

declare(strict_types=1);

require_once __DIR__ . '/engine.php';

$engine = new SeoEngine();
$pdo = $engine->pdo;
$maxUrls = 50000;
$publicPath = dirname(__DIR__);

$datasets = [
    'sitemap-pincode.xml' => "SELECT page_path, last_generated_at FROM seo_pages WHERE entity_type='pincode' ORDER BY page_path",
    'sitemap-district.xml' => "SELECT page_path, last_generated_at FROM seo_pages WHERE entity_type='district' ORDER BY page_path",
    'sitemap-state.xml' => "SELECT page_path, last_generated_at FROM seo_pages WHERE entity_type='state' ORDER BY page_path",
    'sitemap-blog.xml' => "SELECT CONCAT('/blog/', slug) AS page_path, created_at AS last_generated_at FROM blog_posts ORDER BY created_at DESC",
];

$generated = [];

$buildXml = static function (array $rows, string $baseUrl): string {
    $items = [];
    foreach ($rows as $row) {
        $items[] = '<url><loc>' . htmlspecialchars(rtrim($baseUrl, '/') . $row['page_path']) . '</loc><lastmod>' . date('c', strtotime((string) $row['last_generated_at'])) . '</lastmod></url>';
    }

    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . implode('', $items) . '</urlset>';
};

foreach ($datasets as $filename => $sql) {
    $rows = $pdo->query($sql)->fetchAll();
    $chunks = array_chunk($rows, $maxUrls);
    if ($chunks === []) {
        $chunks = [[]];
    }

    foreach ($chunks as $index => $chunk) {
        $target = $filename;
        if (count($chunks) > 1) {
            $target = str_replace('.xml', '-' . ($index + 1) . '.xml', $filename);
        }

        file_put_contents($publicPath . '/' . $target, $buildXml($chunk, $engine->baseUrl));
        $generated[] = $target;
    }
}

$indexXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
foreach ($generated as $file) {
    $indexXml .= '<sitemap><loc>' . htmlspecialchars($engine->baseUrl . '/' . $file) . '</loc><lastmod>' . date('c') . '</lastmod></sitemap>';
}
$indexXml .= '</sitemapindex>';

file_put_contents($publicPath . '/sitemap.xml', $indexXml);
echo 'Generated sitemaps: ' . implode(', ', $generated) . PHP_EOL;
