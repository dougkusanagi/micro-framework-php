<?php

/**
 * Web Routes
 * 
 * Define your application routes here.
 * Routes are defined as arrays with [method, path, handler]
 */

use App\Controllers\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [HomeController::class, 'about']],

    // Example route with parameter
    // ['GET', '/user/{id}', [UserController::class, 'show']],

    // Example POST route
    // ['POST', '/contact', [ContactController::class, 'store']],
];
