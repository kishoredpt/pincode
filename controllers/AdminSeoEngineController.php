<?php

declare(strict_types=1);

class AdminSeoEngineController extends BaseController
{
    public function index(): void
    {
        $pdo = Database::connection();

        $stats = [
            'generated_pages' => (int) $pdo->query('SELECT COUNT(*) FROM seo_pages')->fetchColumn(),
            'pending_queue' => (int) $pdo->query("SELECT COUNT(*) FROM seo_generation_queue WHERE status='pending'")->fetchColumn(),
            'failed_queue' => (int) $pdo->query("SELECT COUNT(*) FROM seo_generation_queue WHERE status='failed'")->fetchColumn(),
        ];

        $latestPages = $pdo->query('SELECT page_path, entity_type, last_generated_at FROM seo_pages ORDER BY last_generated_at DESC LIMIT 20')->fetchAll();

        $sitemaps = [];
        foreach (['sitemap.xml', 'sitemap-pincode.xml', 'sitemap-district.xml', 'sitemap-state.xml', 'sitemap-blog.xml'] as $file) {
            $path = __DIR__ . '/../' . $file;
            $sitemaps[] = [
                'file' => $file,
                'exists' => is_file($path),
                'updated_at' => is_file($path) ? date('Y-m-d H:i:s', (int) filemtime($path)) : null,
            ];
        }

        render('pages/admin/seo-engine', [
            'title' => 'SEO Engine Dashboard',
            'description' => 'Monitoring dashboard for automated SEO engine',
            'stats' => $stats,
            'latestPages' => $latestPages,
            'sitemaps' => $sitemaps,
        ]);
    }
}
