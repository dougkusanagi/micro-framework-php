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

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }

    /**
     * Get authenticated user ID
     *
     * @return int|null
     */
    protected function getAuthenticatedUserId(): ?int
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get authenticated user
     *
     * @return \App\Models\User|null
     */
    protected function getAuthenticatedUser(): ?\App\Models\User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        return \App\Models\User::find($userId);
    }

    /**
     * Require authentication (redirect to login if not authenticated)
     *
     * @param string $redirectTo URL to redirect to after login
     * @return bool True if authenticated, false if redirected
     */
    protected function requireAuth(string $redirectTo = null): bool
    {
        if (!$this->isAuthenticated()) {
            if ($redirectTo) {
                $_SESSION['intended'] = $redirectTo;
            }

            // For AJAX requests, return JSON
            if (
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            ) {
                echo json_encode(['error' => 'Authentication required', 'redirect' => '/login']);
                exit;
            }

            // Regular redirect
            header('Location: /login');
            exit;
        }

        return true;
    }
}
