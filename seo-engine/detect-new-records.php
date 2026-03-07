<?php

declare(strict_types=1);

require_once __DIR__ . '/engine.php';

$engine = new SeoEngine();
$pdo = $engine->pdo;

$windowCondition = "created_at >= (NOW() - INTERVAL 1 DAY)";
$counts = [];

$pincodeRows = $pdo->query("SELECT pincode, district, statename, officename FROM post_offices WHERE {$windowCondition}")->fetchAll();
foreach ($pincodeRows as $row) {
    $engine->enqueue('pincode', (string) $row['pincode']);
    $engine->enqueue('district', $engine->slugify((string) $row['district']));
    $engine->enqueue('state', $engine->slugify((string) $row['statename']));
    $engine->enqueue('area', $engine->slugify((string) $row['officename']));
}
$counts['post_offices'] = count($pincodeRows);

$tables = [
    'pincodes' => ['entity_type' => 'pincode', 'id_column' => 'pincode'],
    'areas' => ['entity_type' => 'area', 'id_column' => 'name'],
    'districts' => ['entity_type' => 'district', 'id_column' => 'name'],
    'states' => ['entity_type' => 'state', 'id_column' => 'name'],
];

foreach ($tables as $table => $map) {
    $stmt = $pdo->query("SELECT {$map['id_column']} AS entity FROM {$table} WHERE {$windowCondition}");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $id = (string) $row['entity'];
        if ($map['entity_type'] !== 'pincode') {
            $id = $engine->slugify($id);
        }
        $engine->enqueue($map['entity_type'], $id);
    }
    $counts[$table] = count($rows);
}

echo 'Queued updates: ' . json_encode($counts) . PHP_EOL;
