<?php

declare(strict_types=1);

class SitemapController extends BaseController
{
    public function index(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $base = $this->appConfig['base_url'];
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach (['pincode', 'district', 'state', 'blog'] as $type) {
            echo '<sitemap><loc>' . htmlspecialchars($base . '/sitemap/' . $type . '.xml', ENT_QUOTES, 'UTF-8') . '</loc></sitemap>';
        }
        echo '</sitemapindex>';
    }

    public function segment(array $params): void
    {
        $type = $params['type'];
        header('Content-Type: application/xml; charset=UTF-8');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        if (in_array($type, ['pincode', 'district', 'state'], true)) {
            $rows = $this->locations->getSitemapRows($type, 50000, 0);
            foreach ($rows as $row) {
                $loc = $this->appConfig['base_url'] . '/' . $type . '/' . $row['slug'];
                echo '<url><loc>' . htmlspecialchars($loc, ENT_QUOTES, 'UTF-8') . '</loc></url>';
            }
        }

        if ($type === 'blog') {
            $posts = $this->blog?->all() ?? [];
            foreach ($posts as $post) {
                echo '<url><loc>' . htmlspecialchars($this->appConfig['base_url'] . '/blog/' . $post['slug'], ENT_QUOTES, 'UTF-8') . '</loc></url>';
            }
        }

        echo '</urlset>';
    }
}
