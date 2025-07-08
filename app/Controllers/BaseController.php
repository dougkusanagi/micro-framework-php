<?php

namespace App\Controllers;

use GuepardoSys\Core\Response;

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers
 */
abstract class BaseController
{
    /**
     * Render a view using PHP includes (fallback para quando template engine tem problemas)
     */
    protected function view(string $view, array $data = []): string
    {
        return $this->viewLegacy($view, $data);
    }

    /**
     * Render a view using PHP includes (legacy method)
     */
    protected function viewLegacy(string $view, array $data = []): string
    {
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found at {$viewPath}");
        }

        // Extract variables for the view
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        include $viewPath;

        // Get the content and clean the buffer
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Return a JSON response
     */
    protected function json(array $data, int $statusCode = 200): Response
    {
        return Response::jsonResponse($data, $statusCode);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirectResponse($url, $statusCode);
    }

    /**
     * Return a simple response
     */
    protected function response(string $content, int $statusCode = 200, array $headers = []): Response
    {
        return Response::make($content, $statusCode, $headers);
    }
}
