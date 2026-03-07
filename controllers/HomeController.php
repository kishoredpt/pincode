<?php

declare(strict_types=1);

class HomeController extends BaseController
{
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $results = $query !== '' ? $this->locations->search($query) : [];
        $states = $this->locations->getStates();

        render('pages/home', [
            'appConfig' => $this->appConfig,
            'seo' => $this->seo->forPage('home', [], $this->appConfig),
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/'),
            'query' => $query,
            'results' => $results,
            'states' => array_slice($states, 0, 40),
            'breadcrumbs' => [['name' => 'Home', 'url' => '/']],
        ]);
    }
}
