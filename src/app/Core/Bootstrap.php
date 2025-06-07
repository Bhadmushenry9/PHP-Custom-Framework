<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;

class Bootstrap
{
    public static function init(Router $router, string $basePath): void
    {
        self::loadEnv($basePath);
        self::loadDefines($basePath);
        self::loadRoutes($router, $basePath);
    }

    private static function loadEnv(string $basePath): void
    {
        if (file_exists($basePath . '/.env')) {
            Dotenv::createImmutable($basePath)->safeLoad();
        }
    }

    private static function loadDefines(string $basePath): void
    {
        require_once $basePath . '/includes/defines.constant.php';
        require_once $basePath . '/includes/defines.tables.php';
    }

    private static function loadRoutes(Router $router, string $basePath): void
    {
        (function () use ($router, $basePath) {
            require $basePath . '/routes/web.php';
        })();
    }
}
