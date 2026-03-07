<?php

declare(strict_types=1);

class LocationController extends BaseController
{
    public function state(array $params): void
    {
        $data = $this->locations->getStatePage($params['state']);
        $this->guardNotFound($data['summary'] ?? null);

        render('pages/state', [
            'appConfig' => $this->appConfig,
            'seo' => $this->seo->forPage('state', $data, $this->appConfig),
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/state/' . $params['state']),
            'data' => $data,
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => $data['summary']['statename'], 'url' => '/state/' . $params['state']]],
        ]);
    }

    public function district(array $params): void
    {
        $data = $this->locations->getDistrictPage($params['district']);
        $this->guardNotFound($data['summary'] ?? null);

        render('pages/district', [
            'appConfig' => $this->appConfig,
            'seo' => $this->seo->forPage('district', $data, $this->appConfig),
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/district/' . $params['district']),
            'data' => $data,
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => $data['summary']['district'], 'url' => '/district/' . $params['district']]],
        ]);
    }

    public function area(array $params): void
    {
        $data = $this->locations->getAreaPage($params['area']);
        $this->guardNotFound($data['summary'] ?? null);
        render('pages/area', [
            'appConfig' => $this->appConfig,
            'seo' => ['title' => $data['summary']['officename'] . ' Area Postal Coverage', 'description' => 'Area level postal offices and nearby PIN code links.'],
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/area/' . $params['area']),
            'data' => $data,
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => $data['summary']['officename'], 'url' => '/area/' . $params['area']]],
        ]);
    }

    public function pincode(array $params): void
    {
        $data = $this->locations->getPincodePage($params['pincode']);
        $this->guardNotFound($data['summary'] ?? null);

        $nearby = $this->locations->getNearbyPincodes($data['summary']['district'], $params['pincode']);
        $districtLinks = $this->locations->getDistrictLinks($data['summary']['statename']);
        $stateLinks = $this->locations->getStateLinks($data['summary']['statename']);
        $generated = $this->content->generateLocationContent($data);

        render('pages/pincode', [
            'appConfig' => $this->appConfig,
            'seo' => $this->seo->forPage('pincode', $data, $this->appConfig),
            'canonical' => $this->seo->canonical($this->appConfig['base_url'], '/pincode/' . $params['pincode']),
            'data' => $data,
            'generated' => $generated,
            'nearby' => $nearby,
            'districtLinks' => $districtLinks,
            'stateLinks' => $stateLinks,
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => $params['pincode'], 'url' => '/pincode/' . $params['pincode']]],
        ]);
    }

    private function guardNotFound(?array $summary): void
    {
        if ($summary !== null) {
            return;
        }

        http_response_code(404);
        (new PageController())->notFound();
        exit;
    }
}
