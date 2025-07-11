<?php

namespace GuepardoSys\Core\Middleware;

use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;

/**
 * Authentication Middleware
 * 
 * Protects routes that require authentication
 */
class AuthMiddleware
{
    private string $redirectUrl;

    public function __construct(string $redirectUrl = '/login')
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Handle the request
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next)
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            // Store intended URL
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';

            // For AJAX requests, return 401
            if ($this->isAjaxRequest()) {
                return new Response('Unauthorized', 401);
            }

            // Regular redirect
            return Response::redirectResponse($this->redirectUrl);
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        return $next($request);
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) &&
            (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']));
    }

    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    protected function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
