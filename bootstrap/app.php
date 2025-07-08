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
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

// Create dependency container
$container = new Container();

// Create and return application instance
$app = new App($container);

return $app;
