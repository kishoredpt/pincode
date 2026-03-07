<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$baseUrl = rtrim(getenv('APP_URL') ?: 'https://pincodelocator.co.in', '/');
$outDir = __DIR__ . '/../sitemap';
@mkdir($outDir, 0775, true);

$pdo = Database::connection();

$types = [
    'pincode' => ["SELECT DISTINCT pincode AS slug FROM post_offices ORDER BY pincode", '/pincode/'],
    'district' => ["SELECT DISTINCT LOWER(REPLACE(district,' ','-')) AS slug FROM post_offices ORDER BY district", '/district/'],
    'state' => ["SELECT DISTINCT LOWER(REPLACE(statename,' ','-')) AS slug FROM post_offices ORDER BY statename", '/state/'],
    'blog' => ["SELECT slug FROM blog_posts ORDER BY created_at DESC", '/blog/'],
];

$indexEntries = [];

foreach ($types as $name => [$query, $prefix]) {
    $rows = $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);
    $chunks = array_chunk($rows, 50000);
    $part = 1;

    foreach ($chunks as $chunk) {
        $filename = "sitemap-{$name}-{$part}.xml";
        $filepath = $outDir . '/' . $filename;
        $xml = new SimpleXMLElement('<urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($chunk as $slug) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($baseUrl . $prefix . $slug, ENT_QUOTES, 'UTF-8'));
        }
        $xml->asXML($filepath);
        $indexEntries[] = $filename;
        $part++;
    }
}

$index = new SimpleXMLElement('<sitemapindex/>');
$index->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
foreach ($indexEntries as $entry) {
    $sitemap = $index->addChild('sitemap');
    $sitemap->addChild('loc', htmlspecialchars($baseUrl . '/sitemap/' . $entry, ENT_QUOTES, 'UTF-8'));
}
$index->asXML($outDir . '/sitemap.xml');

echo "Generated sitemap files in {$outDir}\n";
