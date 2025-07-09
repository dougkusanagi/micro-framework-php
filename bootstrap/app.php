<?php

/**
 * GuepardoSys Application Bootstrap
 * 
 * This file is responsible for:
 * - Loading environment variables
 * - Setting up the dependency container
 * - Initializing the application
 */

use GuepardoSys\Core\App;
use GuepardoSys\Core\Container;
use GuepardoSys\Core\Dotenv;

// Load environment variables
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// Load asset helpers
require_once __DIR__ . '/../src/Core/assets.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create dependency container
$container = new Container();

// Create and return application instance
$app = new App($container);

return $app;
