<?php

declare(strict_types=1);

use App\App;
use App\Core\Router;
use Illuminate\Container\Container;

require_once __DIR__ . '/../vendor/autoload.php';
// Initialize Router
$container = Container::getInstance();
$router = new Router($container);

// Run the application
(new App(
    $container,
    $router,
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => strtolower($_SERVER['REQUEST_METHOD'])]
))->boot()->run();
