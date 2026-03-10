<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$urls = [siteUrl('index.php')];
foreach (fetchAll($pdo, 'SELECT slug FROM states') as $s) { $urls[] = siteUrl('state/' . $s['slug'] . '-pincode'); }
foreach (fetchAll($pdo, 'SELECT slug FROM districts') as $d) { $urls[] = siteUrl('district/' . $d['slug'] . '-pincode'); }
foreach (fetchAll($pdo, 'SELECT pincode, office_name FROM post_offices LIMIT 200000') as $p) { $urls[] = siteUrl('pincode/' . $p['pincode'] . '-' . slugify($p['office_name'])); }
foreach (fetchAll($pdo, 'SELECT slug FROM articles') as $a) { $urls[] = siteUrl('blog/' . $a['slug']); }

$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;
$urlset = $xml->createElement('urlset');
$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
foreach ($urls as $u) {
    $url = $xml->createElement('url');
    $url->appendChild($xml->createElement('loc', $u));
    $url->appendChild($xml->createElement('changefreq', 'weekly'));
    $url->appendChild($xml->createElement('priority', '0.8'));
    $urlset->appendChild($url);
}
$xml->appendChild($urlset);
file_put_contents(__DIR__ . '/../sitemap.xml', $xml->saveXML());
echo "Sitemap generated with " . count($urls) . " URLs\n";
