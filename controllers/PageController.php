<?php

declare(strict_types=1);

class PageController extends BaseController
{
    public function trust(string $page): void
    {
        $allowed = ['about', 'contact', 'privacy-policy', 'disclaimer', 'terms'];
        if (!in_array($page, $allowed, true)) {
            $this->notFound();
            return;
        }

        render('pages/trust-' . $page, [
            'appConfig' => $this->appConfig,
            'seo' => ['title' => ucwords(str_replace('-', ' ', $page)) . ' | ' . $this->appConfig['name'], 'description' => 'Policy and trust information page.'],
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/' . $page),
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => ucwords(str_replace('-', ' ', $page)), 'url' => '/' . $page]],
        ]);
    }

    public function notFound(): void
    {
        render('pages/404', [
            'appConfig' => $this->appConfig,
            'seo' => ['title' => 'Page Not Found', 'description' => 'The requested page does not exist.'],
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], $_SERVER['REQUEST_URI'] ?? '/404'),
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Not Found', 'url' => '#']],
        ]);
    }
}
