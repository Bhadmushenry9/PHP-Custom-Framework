<?php

use App\Controller\HomeController;
use App\Controller\UserController;
use App\Controller\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

// Invoice routes
Route::get('/invoices', [InvoiceController::class, 'index']);
Route::get('/invoices/create', [InvoiceController::class, 'create']);
Route::post('/invoices/store', [InvoiceController::class, 'store']);

// User routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/create', [UserController::class, 'create']);
Route::post('/users/store', [UserController::class, 'store'])->middleware('web');
