<?php

/**
 * Web Routes
 * 
 * Define your application routes here.
 * Routes are defined as arrays with [method, path, handler]
 */

use App\Controllers\HomeController;
use App\Controllers\UsersController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [HomeController::class, 'about']],
    ['GET', '/teste', [HomeController::class, 'teste']],

    // Users CRUD routes
    ['GET', '/users', [UsersController::class, 'index']],
    ['GET', '/users/create', [UsersController::class, 'create']],
    ['POST', '/users', [UsersController::class, 'store']],
    ['GET', '/users/{id}', [UsersController::class, 'show']],
    ['GET', '/users/{id}/edit', [UsersController::class, 'edit']],
    ['POST', '/users/{id}', [UsersController::class, 'update']],
    ['GET', '/users/{id}/delete', [UsersController::class, 'delete']],

    // Example route with parameter
    // ['GET', '/user/{id}', [UserController::class, 'show']],

    // Example POST route
    // ['POST', '/contact', [ContactController::class, 'store']],
];
