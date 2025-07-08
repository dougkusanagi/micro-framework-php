<?php

/**
 * GuepardoSys Micro PHP Framework
 * Entry point for all requests
 */

// Define start time for performance tracking
define('GUEPARDO_START', microtime(true));

// Check if we're running PHP 8.0 or higher
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die('GuepardoSys requires PHP 8.0 or higher. Current version: ' . PHP_VERSION);
}

// Define path constants
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PUBLIC_PATH', __DIR__);

// Load Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Bootstrap the application
$app = require_once BASE_PATH . '/bootstrap/app.php';

// Run the application
$app->run();
