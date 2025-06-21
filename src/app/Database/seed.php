<?php

declare(strict_types=1);

use App\App;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Seeders\DatabaseSeeder;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

require_once __DIR__ . '/../vendor/autoload.php';
// Initialize Router
$container = Container::getInstance();
$events = new Dispatcher($container);
$request = Request::capture();

// Run the application
(new App(
    $container,
    $events,
    $request,
))->boot();

DatabaseSeeder::run();
