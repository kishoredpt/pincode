<?php

declare(strict_types=1);

class BlogService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, title, slug, meta_title, meta_description, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 100');
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM blog_posts WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
