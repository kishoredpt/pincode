<?php

declare(strict_types=1);

$router->add('GET', '/', fn () => (new HomeController())->index());
$router->add('GET', '/state/{state}', fn ($params) => (new LocationController())->state($params));
$router->add('GET', '/district/{district}', fn ($params) => (new LocationController())->district($params));
$router->add('GET', '/area/{area}', fn ($params) => (new LocationController())->area($params));
$router->add('GET', '/pincode/{pincode}', fn ($params) => (new LocationController())->pincode($params));
$router->add('GET', '/blog', fn () => (new BlogController())->index());
$router->add('GET', '/blog/{slug}', fn ($params) => (new BlogController())->show($params));
$router->add('GET', '/sitemap.xml', fn () => (new SitemapController())->index());
$router->add('GET', '/sitemap/{type}.xml', fn ($params) => (new SitemapController())->segment($params));

foreach (['about', 'contact', 'privacy-policy', 'disclaimer', 'terms'] as $trustPage) {
    $router->add('GET', '/' . $trustPage, fn () => (new PageController())->trust($trustPage));
}
