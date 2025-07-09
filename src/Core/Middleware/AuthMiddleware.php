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
    /**
     * Handle the request
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next)
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            // Store intended URL
            $_SESSION['intended'] = $request->getUri();

            // For AJAX requests, return JSON
            if ($request->isAjax()) {
                return Response::jsonResponse(['error' => 'Authentication required', 'redirect' => '/login'], 401);
            }

            // Regular redirect
            return Response::redirectResponse('/login');
        }

        return $next($request);
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
}
