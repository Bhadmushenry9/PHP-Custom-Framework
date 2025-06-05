<?php

use App\App;
use App\Config;

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../includes/bootstrap.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$router = new \App\Routes();

$router
    ->get('/', [\App\Controllers\HomeController::class, 'index'])
    ->get('/invoices', [\App\Controllers\InvoiceController::class, 'index'])
    ->get('/invoices/create', [\App\Controllers\InvoiceController::class, 'create'])
    ->post('/invoices/store', [\App\Controllers\InvoiceController::class, 'store']);

(new App(
    $router, 
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => $_SERVER['REQUEST_METHOD']], 
    new Config($_ENV)
))->run();