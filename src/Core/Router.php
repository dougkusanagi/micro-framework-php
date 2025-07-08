<?php

namespace GuepardoSys\Core;

use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;
use GuepardoSys\Core\Container;

/**
 * Simple Router Implementation
 */
class Router
{
    private array $routes = [];

    /**
     * Add a route
     */
    public function addRoute(string $method, string $path, array|callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->compilePattern($path)
        ];
    }

    /**
     * Compile route pattern to regex
     */
    private function compilePattern(string $path): string
    {
        // Escape special regex characters except for parameter placeholders
        $pattern = preg_quote($path, '#');

        // Replace parameter placeholders with regex patterns
        $pattern = preg_replace('/\\\\{([^}]+)\\\\}/', '(?P<$1>[^/]+)', $pattern);

        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch(Request $request, Container $container): mixed
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Extract parameters
                $parameters = $this->extractParameters($matches);

                // Handle the request
                return $this->handleRoute($route['handler'], $parameters, $container);
            }
        }

        // No route found
        return $this->handleNotFound();
    }

    /**
     * Extract parameters from regex matches
     */
    private function extractParameters(array $matches): array
    {
        $parameters = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Handle route execution
     */
    private function handleRoute(array|callable $handler, array $parameters, Container $container): mixed
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $parameters);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller {$controllerClass} not found.");
            }

            // Resolve controller through container
            $controller = $container->resolve($controllerClass);

            if (!method_exists($controller, $method)) {
                throw new \Exception("Method {$method} not found in controller {$controllerClass}.");
            }

            // Call controller method with parameters
            return call_user_func_array([$controller, $method], $parameters);
        }

        throw new \Exception("Invalid route handler.");
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): Response
    {
        $response = new Response();
        $response->setStatusCode(404);
        $response->setContent($this->getNotFoundContent());
        return $response;
    }

    /**
     * Get 404 content
     */
    private function getNotFoundContent(): string
    {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #333; }
                p { color: #666; }
            </style>
        </head>
        <body>
            <h1>404 - Page Not Found</h1>
            <p>The requested page could not be found.</p>
        </body>
        </html>';
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
