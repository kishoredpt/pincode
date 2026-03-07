<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::connection();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    echo "Detected tables:\n";
    foreach ($tables as $table) {
        echo "- {$table}\n";
        $columns = $pdo->query("DESCRIBE {$table}")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "    {$column['Field']} {$column['Type']}\n";
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, "DB introspection failed: {$e->getMessage()}\n");
    exit(1);
}
