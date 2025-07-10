<?php

namespace Tests\Helpers;

use GuepardoSys\Core\Request;
use GuepardoSys\Core\Container;
use GuepardoSys\Core\Router;

/**
 * Test helpers for creating mock objects and test data
 */
class TestHelpers
{
    /**
     * Create a mock request
     */
    public static function createRequest(
        string $method = 'GET',
        string $uri = '/',
        array $query = [],
        array $data = [],
        array $headers = []
    ): Request {
        // Set server variables for request
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_HOST'] = 'localhost';

        // Set query parameters
        $_GET = $query;

        // Set POST data
        if ($method === 'POST') {
            $_POST = $data;
        }

        // Set headers
        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        return Request::createFromGlobals();
    }

    /**
     * Create a fresh container
     */
    public static function createContainer(): Container
    {
        return new Container();
    }

    /**
     * Create a router with test routes
     */
    public static function createRouter(array $routes = []): Router
    {
        $router = new Router();

        foreach ($routes as $route) {
            if (count($route) >= 3) {
                [$method, $path, $handler] = $route;
                $router->addRoute($method, $path, $handler);
            }
        }

        return $router;
    }

    /**
     * Create test database configuration
     */
    public static function setupTestDatabase(): void
    {
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['DB_HOST'] = '';
        $_ENV['DB_PORT'] = '';
        $_ENV['DB_USERNAME'] = '';
        $_ENV['DB_PASSWORD'] = '';
    }

    /**
     * Clean up test environment
     */
    public static function cleanupTestEnvironment(): void
    {
        // Clear session data
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }

        // Clear superglobals
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_COOKIE = [];
        $_SESSION = [];

        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * Start output buffering for response testing
     */
    public static function startOutputCapture(): void
    {
        if (!ob_get_level()) {
            ob_start();
        }
    }

    /**
     * Get captured output and clean buffer
     */
    public static function getCapturedOutput(): string
    {
        $output = '';
        if (ob_get_level()) {
            $output = ob_get_clean();
        }
        return $output;
    }

    /**
     * Create a mock file upload
     */
    public static function createFileUpload(
        string $name = 'test.txt',
        string $content = 'test content',
        string $mimeType = 'text/plain'
    ): array {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($tmpFile, $content);

        return [
            'name' => $name,
            'type' => $mimeType,
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($content)
        ];
    }

    /**
     * Set test headers for request
     */
    public static function setTestHeaders(array $headers): void
    {
        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }
    }

    /**
     * Mock authentication state
     */
    public static function mockAuthenticatedUser(int $userId = 1): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = $userId;
    }

    /**
     * Mock unauthenticated state
     */
    public static function mockUnauthenticatedUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['authenticated'] = false;
        unset($_SESSION['user_id']);
    }
}
