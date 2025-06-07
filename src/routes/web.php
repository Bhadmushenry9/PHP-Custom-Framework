<?php

use App\Router;
use App\Controllers\HomeController;
use App\Controllers\InvoiceController;

/** @var Router $router */
$router
    ->get('/', [HomeController::class, 'index'])

    // Invoice routes
    ->get('/invoices', [InvoiceController::class, 'index'])
    ->get('/invoices/create', [InvoiceController::class, 'create'])
    ->post('/invoices/store', [InvoiceController::class, 'store']);
