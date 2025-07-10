<?php

/**
 * Web Routes
 * 
 * Define your application routes here.
 * Routes are defined as arrays with [method, path, handler]
 */

use App\Controllers\HomeController;
use App\Controllers\UsersController;
use App\Controllers\SimpleAuthController;
use App\Controllers\CacheExamplesController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [HomeController::class, 'about']],
    ['GET', '/teste', [HomeController::class, 'teste']],
    ['GET', '/frontend-demo', [HomeController::class, 'frontendDemo']],

    // Authentication routes - using SimpleAuthController temporarily
    ['GET', '/login', [SimpleAuthController::class, 'showLogin']],
    ['POST', '/login', [SimpleAuthController::class, 'login']],
    ['GET', '/register', [SimpleAuthController::class, 'showRegister']],
    ['POST', '/register', [SimpleAuthController::class, 'register']],
    ['GET', '/logout', [SimpleAuthController::class, 'logout']],
    ['GET', '/dashboard', [SimpleAuthController::class, 'dashboard']],

    // Users CRUD routes
    ['GET', '/users', [UsersController::class, 'index']],
    ['GET', '/users/create', [UsersController::class, 'create']],
    ['POST', '/users', [UsersController::class, 'store']],
    ['GET', '/users/{id}', [UsersController::class, 'show']],
    ['GET', '/users/{id}/edit', [UsersController::class, 'edit']],
    ['POST', '/users/{id}', [UsersController::class, 'update']],
    ['GET', '/users/{id}/delete', [UsersController::class, 'delete']],

    // Cache Examples routes
    ['GET', '/cache-examples/basic', [CacheExamplesController::class, 'basicCache']],
    ['GET', '/cache-examples/tagged', [CacheExamplesController::class, 'taggedCache']],
    ['GET', '/cache-examples/database', [CacheExamplesController::class, 'databaseCache']],
    ['GET', '/cache-examples/incremental', [CacheExamplesController::class, 'incrementalCache']],
    ['GET', '/cache-examples/forever', [CacheExamplesController::class, 'foreverCache']],
    ['GET', '/cache-examples/clear', [CacheExamplesController::class, 'clearCache']],

    // Example routes
    // ['GET', '/user/{id}', [UserController::class, 'show']],
    // ['POST', '/contact', [ContactController::class, 'store']],
];
];
