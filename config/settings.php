<?php

declare(strict_types=1);

$environment = getenv('APP_ENV') ?: 'development';

return [
    'environment' => $environment,
    'display_errors' => $environment !== 'production',
    'timezone' => getenv('APP_TIMEZONE') ?: 'Europe/Paris',
    'database_path' => dirname(__DIR__) . '/database/bibliotheque.sqlite',
    'views_path' => dirname(__DIR__) . '/views',
];
