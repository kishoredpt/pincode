<?php

declare(strict_types=1);

class BaseController
{
    protected array $appConfig;
    protected CacheService $cache;
    protected LocationService $locations;
    protected SeoService $seo;
    protected ContentGeneratorService $content;
    protected ?BlogService $blog = null;

    public function __construct()
    {
        $this->appConfig = require __DIR__ . '/../config/app.php';
        $this->cache = new CacheService(__DIR__ . '/../assets/cache', $this->appConfig['cache_ttl']);
        $pdo = Database::connection();
        $this->locations = new LocationService($pdo, $this->cache);
        $this->seo = new SeoService();
        $this->content = new ContentGeneratorService();
        $this->blog = new BlogService($pdo);
    }
}
