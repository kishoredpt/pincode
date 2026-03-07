<?php

declare(strict_types=1);

class BlogController extends BaseController
{
    public function index(): void
    {
        $posts = $this->blog?->all() ?? [];
        render('pages/blog-index', [
            'appConfig' => $this->appConfig,
            'seo' => ['title' => 'Blog | ' . $this->appConfig['name'], 'description' => 'Postal guides and pincode explainers.'],
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/blog'),
            'posts' => $posts,
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Blog', 'url' => '/blog']],
        ]);
    }

    public function show(array $params): void
    {
        $post = $this->blog?->getBySlug($params['slug']);
        if (!$post) {
            http_response_code(404);
            (new PageController())->notFound();
            return;
        }

        render('pages/blog-post', [
            'appConfig' => $this->appConfig,
            'seo' => ['title' => ($post['meta_title'] ?: $post['title']), 'description' => ($post['meta_description'] ?: substr(strip_tags($post['content']), 0, 155))],
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/blog/' . $post['slug']),
            'post' => $post,
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Blog', 'url' => '/blog'], ['name' => $post['title'], 'url' => '/blog/' . $post['slug']]],
        ]);
    }
}
