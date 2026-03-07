<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$files = [
    __DIR__ . '/../database/001_normalize_schema.sql',
    __DIR__ . '/../database/002_add_indexes.sql',
];

try {
    $pdo = Database::connection();
    foreach ($files as $file) {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException("Cannot read migration file {$file}");
        }
        $pdo->exec($sql);
        echo "Applied: {$file}\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Migration failed: {$e->getMessage()}\n");
    exit(1);
}
