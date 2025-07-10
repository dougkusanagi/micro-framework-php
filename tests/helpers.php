<?php

// Define path constants for tests if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', BASE_PATH . '/config');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

// Load test helpers
require_once __DIR__ . '/Helpers/TestHelpers.php';

use Tests\Helpers\TestHelpers;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;
use GuepardoSys\Core\Container;

/**
 * Global test helper functions
 */

if (!function_exists('createTestRequest')) {
    function createTestRequest(string $method = 'GET', string $uri = '/', array $data = []): Request
    {
        return TestHelpers::createRequest($method, $uri, [], $data);
    }
}

if (!function_exists('createTestContainer')) {
    function createTestContainer(): Container
    {
        return TestHelpers::createContainer();
    }
}

if (!function_exists('mockAuthenticated')) {
    function mockAuthenticated(int $userId = 1): void
    {
        TestHelpers::mockAuthenticatedUser($userId);
    }
}

if (!function_exists('mockUnauthenticated')) {
    function mockUnauthenticated(): void
    {
        TestHelpers::mockUnauthenticatedUser();
    }
}

if (!function_exists('captureOutput')) {
    function captureOutput(callable $callback): string
    {
        TestHelpers::startOutputCapture();
        $callback();
        return TestHelpers::getCapturedOutput();
    }
}

if (!function_exists('setupTestDb')) {
    function setupTestDb(): void
    {
        TestHelpers::setupTestDatabase();
    }
}

if (!function_exists('cleanupTest')) {
    function cleanupTest(): void
    {
        TestHelpers::cleanupTestEnvironment();
    }
}

if (!function_exists('test_config')) {
    /**
     * Get test configuration value
     */
    function test_config(string $key, mixed $default = null): mixed
    {
        $config = [
            'database.default' => 'testing',
            'database.connections.testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
            ],
        ];

        return data_get($config, $key, $default);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array using "dot" notation
     */
    function data_get(array $target, string $key, mixed $default = null): mixed
    {
        if (strpos($key, '.') === false) {
            return $target[$key] ?? $default;
        }

        $keys = explode('.', $key);
        foreach ($keys as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } else {
                return $default;
            }
        }

        return $target;
    }
}
