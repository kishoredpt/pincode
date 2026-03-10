<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';

$report = [];
$articleCount = (int) $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$report[] = ['check' => 'Minimum article count (>=30)', 'status' => $articleCount >= 30 ? 'PASS' : 'FAIL', 'detail' => (string)$articleCount];

$thinPages = (int) $pdo->query('SELECT COUNT(*) FROM articles WHERE CHAR_LENGTH(content) < 2500')->fetchColumn();
$report[] = ['check' => 'Thin pages', 'status' => $thinPages === 0 ? 'PASS' : 'WARN', 'detail' => (string)$thinPages];

$missingMeta = (int) $pdo->query('SELECT COUNT(*) FROM articles WHERE meta_title = "" OR meta_description = ""')->fetchColumn();
$report[] = ['check' => 'Missing meta tags', 'status' => $missingMeta === 0 ? 'PASS' : 'FAIL', 'detail' => (string)$missingMeta];

$sitemap = file_exists(__DIR__ . '/../sitemap.xml');
$report[] = ['check' => 'Sitemap availability', 'status' => $sitemap ? 'PASS' : 'FAIL', 'detail' => $sitemap ? 'present' : 'missing'];

$robots = file_exists(__DIR__ . '/../robots.txt');
$report[] = ['check' => 'Robots file', 'status' => $robots ? 'PASS' : 'FAIL', 'detail' => $robots ? 'present' : 'missing'];

foreach ($report as $item) {
    echo sprintf("[%s] %s => %s\n", $item['status'], $item['check'], $item['detail']);
}
