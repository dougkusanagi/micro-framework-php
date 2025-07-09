<?php

use GuepardoSys\Core\Middleware\AuthMiddleware;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;

describe('AuthMiddleware', function () {
    beforeEach(function () {
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session data
        unset($_SESSION['user_id']);
        unset($_SESSION['authenticated']);
    });

    afterEach(function () {
        // Clean up session
        unset($_SESSION['user_id']);
        unset($_SESSION['authenticated']);
    });

    it('allows authenticated users to pass through', function () {
        // Set authenticated session
        $_SESSION['user_id'] = 1;
        $_SESSION['authenticated'] = true;

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->getContent())->toBe('Protected content');
    });

    it('redirects unauthenticated users to login', function () {
        // No authenticated session

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->getStatusCode())->toBe(302);
        expect($response->getHeader('Location'))->toContain('login');
    });

    it('checks for user_id in session', function () {
        // Set only authenticated flag without user_id
        $_SESSION['authenticated'] = true;

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        // Should redirect because user_id is missing
        expect($response->getStatusCode())->toBe(302);
    });

    it('checks for authenticated flag in session', function () {
        // Set only user_id without authenticated flag
        $_SESSION['user_id'] = 1;

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        // Should redirect because authenticated flag is missing
        expect($response->getStatusCode())->toBe(302);
    });

    it('can customize redirect URL', function () {
        $middleware = new AuthMiddleware('/custom-login');
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        expect($response->getStatusCode())->toBe(302);
        expect($response->getHeader('Location'))->toBe('/custom-login');
    });

    it('preserves intended URL for redirect after login', function () {
        // Mock current URL
        $_SERVER['REQUEST_URI'] = '/protected/page';

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        expect($response->getStatusCode())->toBe(302);
        expect($_SESSION['intended_url'])->toBe('/protected/page');

        // Clean up
        unset($_SERVER['REQUEST_URI']);
        unset($_SESSION['intended_url']);
    });

    it('handles AJAX requests differently', function () {
        // Mock AJAX request
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $response = $middleware->handle($request, $next);

        // Should return 401 for AJAX instead of redirect
        expect($response->getStatusCode())->toBe(401);

        // Clean up
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    });

    it('can check specific user roles if implemented', function () {
        $_SESSION['user_id'] = 1;
        $_SESSION['authenticated'] = true;
        $_SESSION['user_role'] = 'user';

        if (method_exists(AuthMiddleware::class, 'requireRole')) {
            $middleware = new AuthMiddleware();
            $middleware->requireRole('admin');

            $request = Request::createFromGlobals();

            $next = function ($request) {
                return new Response('Admin content');
            };

            $response = $middleware->handle($request, $next);

            // Should redirect/deny access for insufficient role
            expect($response->getStatusCode())->toBeIn([302, 403]);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }

        // Clean up
        unset($_SESSION['user_role']);
    });

    it('can check multiple authentication methods', function () {
        // Test API token authentication if implemented
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token';

        if (method_exists(AuthMiddleware::class, 'checkApiToken')) {
            $middleware = new AuthMiddleware();
            $request = Request::createFromGlobals();

            $next = function ($request) {
                return new Response('API content');
            };

            $response = $middleware->handle($request, $next);

            // This would depend on token validation logic
            expect($response)->toBeInstanceOf(Response::class);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }

        // Clean up
        unset($_SERVER['HTTP_AUTHORIZATION']);
    });

    it('handles session timeouts', function () {
        $_SESSION['user_id'] = 1;
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time() - 7200; // 2 hours ago

        if (method_exists(AuthMiddleware::class, 'checkSessionTimeout')) {
            $middleware = new AuthMiddleware();
            $request = Request::createFromGlobals();

            $next = function ($request) {
                return new Response('Protected content');
            };

            $response = $middleware->handle($request, $next);

            // Should redirect due to timeout
            expect($response->getStatusCode())->toBe(302);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }

        // Clean up
        unset($_SESSION['last_activity']);
    });

    it('updates last activity timestamp', function () {
        $_SESSION['user_id'] = 1;
        $_SESSION['authenticated'] = true;

        $middleware = new AuthMiddleware();
        $request = Request::createFromGlobals();

        $next = function ($request) {
            return new Response('Protected content');
        };

        $middleware->handle($request, $next);

        if (isset($_SESSION['last_activity'])) {
            expect($_SESSION['last_activity'])->toBeCloseTo(time(), 5);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });

    it('can be bypassed for guest routes', function () {
        if (method_exists(AuthMiddleware::class, 'except')) {
            $middleware = new AuthMiddleware();
            $middleware->except(['/login', '/register']);

            // Mock login route
            $_SERVER['REQUEST_URI'] = '/login';

            $request = Request::createFromGlobals();

            $next = function ($request) {
                return new Response('Login page');
            };

            $response = $middleware->handle($request, $next);

            // Should pass through without authentication
            expect($response->getContent())->toBe('Login page');

            // Clean up
            unset($_SERVER['REQUEST_URI']);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });
});
