<?php
declare(strict_types=1);

use App\App;
use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Routing\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$container = Container::getInstance();
$events = new Dispatcher($container);
$router = new Router($events, $container);
$request = Request::capture();

$app = new App($container, $events, $request, $router);
$app->boot();

$response = $router->dispatch($request);

$response->send();
