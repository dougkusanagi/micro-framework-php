<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected array $originalServer = [];
    protected array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Backup original state
        $this->originalServer = $_SERVER;
        $this->originalEnv = $_ENV;

        // Reset session state
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }

        // Initialize clean state for testing
        $this->initializeCleanState();
    }

    protected function initializeCleanState(): void
    {
        // Set default test environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        // Clear any existing singleton instances
        $this->clearSingletons();

        // Set minimal server variables
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['REQUEST_TIME'] = time();
    }

    protected function clearSingletons(): void
    {
        // Reset Database singleton
        if (class_exists(\GuepardoSys\Core\Database::class)) {
            $reflection = new \ReflectionClass(\GuepardoSys\Core\Database::class);
            if ($reflection->hasProperty('connection')) {
                $property = $reflection->getProperty('connection');
                $property->setAccessible(true);
                $property->setValue(null, null);
            }
        }
    }

    protected function tearDown(): void
    {
        // Restore original state
        $_SERVER = $this->originalServer;
        $_ENV = $this->originalEnv;

        // Clean up session
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }

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
