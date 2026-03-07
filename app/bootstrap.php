<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Router.php';

spl_autoload_register(function (string $class): void {
    $directories = [
        __DIR__ . '/../controllers/',
        __DIR__ . '/../services/',
        __DIR__ . '/',
    ];

    foreach ($directories as $directory) {
        $path = $directory . $class . '.php';
        if (is_readable($path)) {
            require_once $path;
            return;
        }
    }
});

$appConfig = require __DIR__ . '/../config/app.php';

function render(string $template, array $data = []): void
{
    extract($data);
    $templateFile = __DIR__ . '/../templates/' . $template . '.php';
    require __DIR__ . '/../templates/layouts/main.php';
}
