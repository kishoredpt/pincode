<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

try {
    $router = new Router();
    require __DIR__ . '/routes/web.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    http_response_code(503);
    error_log($exception->getMessage());
    echo '<h1>Service temporarily unavailable</h1><p>Please check database configuration.</p>';
}
