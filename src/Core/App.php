<?php

namespace GuepardoSys\Core;

use GuepardoSys\Core\Router;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;
use GuepardoSys\Core\Database;

/**
 * Main Application Class
 */
class App
{
    private Container $container;
    private Router $router;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bootstrap();
    }

    /**
     * Bootstrap the application
     */
    private function bootstrap(): void
    {
        // Register core services
        $this->registerCoreServices();

        // Initialize router
        $this->router = $this->container->resolve(Router::class);

        // Load routes
        $this->loadRoutes();
    }

    /**
     * Register core services in the container
     */
    private function registerCoreServices(): void
    {
        // Register router as singleton
        $this->container->singleton(Router::class);

        // Register database as singleton
        $this->container->singleton(Database::class);

        // Register request
        $this->container->bind(Request::class, function () {
            return Request::capture();
        });

        // Register response
        $this->container->bind(Response::class);
    }

    /**
     * Load application routes
     */
    private function loadRoutes(): void
    {
        $routesFile = BASE_PATH . '/routes/web.php';

        if (file_exists($routesFile)) {
            $routes = require $routesFile;

            if (is_array($routes)) {
                foreach ($routes as $route) {
                    if (count($route) >= 3) {
                        [$method, $path, $handler] = $route;
                        $this->router->addRoute($method, $path, $handler);
                    }
                }
            }
        }
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            $request = $this->container->resolve(Request::class);
            $response = $this->router->dispatch($request, $this->container);

            if ($response instanceof Response) {
                $response->send();
            } else {
                // Handle string response
                echo $response;
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle application exceptions
     */
    private function handleException(\Exception $e): void
    {
        // Simple error handling for now
        http_response_code(500);

        if ($_ENV['APP_DEBUG'] ?? false) {
            echo "<h1>Application Error</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }

    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
