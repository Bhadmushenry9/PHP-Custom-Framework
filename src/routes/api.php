<?php

use App\Router;
use App\Controller\Api\UserController;

/** @var Router $router */

// Optional: Add an '/api' prefix manually for each route
$router
    ->get('/api/users', [UserController::class, 'index'])
    ->get('/api/users/{id}', [UserController::class, 'show'])
    ->post('/api/users', [UserController::class, 'store'])
    ->put('/api/users/{id}', [UserController::class, 'update'])
    ->delete('/api/users/{id}', [UserController::class, 'destroy']);
