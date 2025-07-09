<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the application for testing
        $this->initializeApplication();
    }

    protected function initializeApplication(): void
    {
        // Load environment variables for testing
        $envPath = dirname(__DIR__);
        $dotenv = new \GuepardoSys\Core\Dotenv($envPath);
        $dotenv->safeLoad();

        // Initialize the database connection if needed
        if (class_exists(\GuepardoSys\Core\Database::class)) {
            try {
                \GuepardoSys\Core\Database::getConnection();
            } catch (\Exception $e) {
                // Database connection might not be available in tests
            }
        }
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        parent::tearDown();
    }

    /**
     * Helper to create a mock request
     */
    protected function createRequest(string $method = 'GET', string $uri = '/', array $data = []): \GuepardoSys\Core\Request
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';

        $query = [];
        if ($method === 'POST') {
            $_POST = $data;
        } else {
            $_GET = $data;
            $query = $data;
        }

        return new \GuepardoSys\Core\Request($method, $uri, $query, $data);
    }

    /**
     * Helper to simulate authenticated user
     */
    protected function actingAs(array $user): self
    {
        session_start();
        $_SESSION['user_id'] = $user['id'] ?? 1;
        $_SESSION['user'] = $user;

        return $this;
    }

    /**
     * Helper to clear session
     */
    protected function clearSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }
}
