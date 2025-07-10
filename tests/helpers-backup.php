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
                ],
            ],
            'app.env' => 'testing',
            'app.debug' => true,
        ];

        return data_get($config, $key, $default);
    }
}

if (!function_exists('create_test_user')) {
    /**
     * Create a test user array
     */
    function create_test_user(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides);
    }
}

if (!function_exists('create_test_request')) {
    /**
     * Create a test request
     */
    function create_test_request(string $method = 'GET', string $uri = '/', array $data = []): \GuepardoSys\Core\Request
    {
        $query = [];
        if ($method === 'GET') {
            $query = $data;
        }

        return new \GuepardoSys\Core\Request($method, $uri, $query, $data);
    }
}

if (!function_exists('temp_view')) {
    /**
     * Create a temporary view file for testing
     */
    function temp_view(string $name, string $content): string
    {
        $path = sys_get_temp_dir() . '/' . $name . '.guepardo.php';
        file_put_contents($path, $content);
        return $path;
    }
}

if (!function_exists('cleanup_temp_view')) {
    /**
     * Clean up temporary view file
     */
    function cleanup_temp_view(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     */
    function data_get(array $target, string $key, mixed $default = null): mixed
    {
        if (strpos($key, '.') === false) {
            return $target[$key] ?? $default;
        }

        $keys = explode('.', $key);
        $value = $target;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}

/**
 * Additional Test Helper Functions
 */

use GuepardoSys\Core\Database;
use GuepardoSys\Core\App;

/**
 * Set up test environment
 */
function setupTestEnvironment()
{
    $_ENV['APP_ENV'] = 'testing';
    $_ENV['APP_DEBUG'] = 'true';
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_ENV['DB_DATABASE'] = ':memory:';

    // Reset singletons
    Database::$instance = null;
    App::$container = null;
}

/**
 * Clean up test environment
 */
function cleanupTestEnvironment()
{
    $_GET = [];
    $_POST = [];
    $_SERVER = array_merge($_SERVER, [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'HTTP_HOST' => 'localhost'
    ]);

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Create test database with tables
 */
function createTestDatabase()
{
    $pdo = Database::getConnection();

    // Users table
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Products table
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');
}

/**
 * Create a test user
 */
function createTestUser($name = 'Test User', $email = 'test@example.com', $password = 'password')
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

    return $pdo->lastInsertId();
}

/**
 * Mock HTTP request
 */
function mockRequest($method = 'GET', $uri = '/', $data = [])
{
    $_SERVER['REQUEST_METHOD'] = strtoupper($method);
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['HTTP_HOST'] = 'localhost';

    if ($method === 'POST') {
        $_POST = $data;
    } else {
        $_GET = $data;
    }
}

/**
 * Set authenticated user in session
 */
function authenticateTestUser($userId = 1)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $userId;
    $_SESSION['authenticated'] = true;
}

/**
 * Create test directory
 */
function createTestDirectory($path)
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
    return $path;
}

/**
 * Remove test directory recursively
 */
function removeTestDirectory($dir)
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                removeTestDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
