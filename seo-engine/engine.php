<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class SeoEngine
{
    public PDO $pdo;
    public string $baseUrl;
    public string $rootPath;

    public function __construct()
    {
        $this->pdo = Database::connection();
        $appConfig = require __DIR__ . '/../config/app.php';
        $this->baseUrl = rtrim((string) ($appConfig['base_url'] ?? 'http://localhost:8000'), '/');
        $this->rootPath = dirname(__DIR__);
    }

    public function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    public function enqueue(string $entityType, string $entityId): void
    {
        $sql = "INSERT INTO seo_generation_queue (entity_type, entity_id, status, created_at) VALUES (:type, :id, 'pending', NOW())
                ON DUPLICATE KEY UPDATE status='pending', created_at=NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['type' => $entityType, 'id' => $entityId]);
    }

    public function cachePath(string $pagePath): string
    {
        $file = trim(str_replace('/', '_', $pagePath), '_') ?: 'home';
        return $this->rootPath . '/cache/pages/' . $file . '.html';
    }

    public function writeCache(string $pagePath, string $html): void
    {
        $cachePath = $this->cachePath($pagePath);
        $directory = dirname($cachePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        file_put_contents($cachePath, $html);
    }

    public function invalidateCache(string $entityType, string $entityId): void
    {
        $guesses = [
            "/{$entityType}/{$entityId}",
            '/' . $entityType . '/' . $this->slugify($entityId),
        ];

        foreach (array_unique($guesses) as $guess) {
            $cachePath = $this->cachePath($guess);
            if (is_file($cachePath)) {
                unlink($cachePath);
            }
        }
    }
}
