<?php

declare(strict_types=1);

use App\App;
use App\Core\Config;
use App\Core\Router;
use App\Core\Bootstrap;

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

// Initialize Router
$router = new Router();

// Bootstrap the application (env, defines, routes)
Bootstrap::init($router, $basePath);

// Run the application
(new App(
    $router,
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => $_SERVER['REQUEST_METHOD']],
    new Config($_ENV),
))->run();
