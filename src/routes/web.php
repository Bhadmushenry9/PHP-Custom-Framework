<?php

use App\Router;
use App\Controller\HomeController;
use App\Controller\InvoiceController;

/** @var Router $router */
$router
    ->get('/', [HomeController::class, 'index'])

    // Invoice routes
    ->get('/invoices', [InvoiceController::class, 'index'])
    ->get('/invoices/create', [InvoiceController::class, 'create'])
    ->post('/invoices/store', [InvoiceController::class, 'store']);
