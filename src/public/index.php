<?php

declare(strict_types=1);

use App\App;
use App\Core\Container;
use App\Core\Router;
use App\Core\Bootstrap;
use App\Seeders\DatabaseSeeder;

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

// Initialize Router
$container = Container::getInstance();
$router = new Router($container);

// Bootstrap the application (env, defines, routes)
new Bootstrap($basePath, $router, $container);

// Run the application
(new App(
    $router,
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => strtolower($_SERVER['REQUEST_METHOD'])],
))->run();
