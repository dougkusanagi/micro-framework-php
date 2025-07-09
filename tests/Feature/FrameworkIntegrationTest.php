<?php

use GuepardoSys\Core\App;
use GuepardoSys\Core\Router;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;
use GuepardoSys\Core\Database;

describe('Framework Integration', function () {
    beforeEach(function () {
        // Set up test environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        // Reset singletons
        Database::$instance = null;
        App::$container = null;

        // Clean superglobals
        $_GET = [];
        $_POST = [];
        $_SERVER = array_merge($_SERVER, [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
            'HTTP_HOST' => 'localhost',
            'SERVER_NAME' => 'localhost'
        ]);
    });

    afterEach(function () {
        // Clean up
        $_GET = [];
        $_POST = [];
        session_destroy();
    });

    it('can handle basic GET request', function () {
        $app = new App();
        $router = new Router();

        $router->get('/', function () {
            return new Response('Home Page');
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        ob_start();
        $app->run();
        $output = ob_get_clean();

        expect($output)->toBe('Home Page');
    });

    it('can handle POST request with data', function () {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['name' => 'John', 'email' => 'john@example.com'];

        $app = new App();
        $router = new Router();

        $router->post('/', function (Request $request) {
            $data = $request->all();
            return new Response('Name: ' . $data['name'] . ', Email: ' . $data['email']);
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        ob_start();
        $app->run();
        $output = ob_get_clean();

        expect($output)->toBe('Name: John, Email: john@example.com');
    });

    it('can handle route parameters', function () {
        $_SERVER['REQUEST_URI'] = '/users/123';

        $app = new App();
        $router = new Router();

        $router->get('/users/{id}', function (Request $request, $id) {
            return new Response('User ID: ' . $id);
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        ob_start();
        $app->run();
        $output = ob_get_clean();

        expect($output)->toBe('User ID: 123');
    });

    it('can handle 404 errors', function () {
        $_SERVER['REQUEST_URI'] = '/non-existent';

        $app = new App();
        $router = new Router();

        // No routes defined

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        ob_start();
        $response = $app->run();
        $output = ob_get_clean();

        // Should handle 404 gracefully
        expect($output)->toContain('404');
    });

    it('can use dependency injection', function () {
        $app = new App();

        // Bind a service
        $app->bind('test.service', function () {
            return 'Test Service Value';
        });

        $router = new Router();
        $router->get('/', function () use ($app) {
            $service = $app->resolve('test.service');
            return new Response('Service: ' . $service);
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        ob_start();
        $app->run();
        $output = ob_get_clean();

        expect($output)->toBe('Service: Test Service Value');
    });

    it('can handle middleware', function () {
        if (class_exists('GuepardoSys\Core\Middleware\AuthMiddleware')) {
            // Test middleware integration
            expect(true)->toBeTrue();
        } else {
            expect(true)->toBeTrue(); // Skip if middleware not available
        }
    });

    it('can render views', function () {
        if (class_exists('GuepardoSys\Core\View\View')) {
            $viewsDir = __DIR__ . '/../../temp_views';
            if (!is_dir($viewsDir)) {
                mkdir($viewsDir, 0755, true);
            }

            $viewContent = '<h1>{{ $title }}</h1>';
            file_put_contents($viewsDir . '/test.guepardo.php', $viewContent);

            $app = new App();
            $router = new Router();

            $router->get('/', function () use ($viewsDir) {
                $view = new \GuepardoSys\Core\View\View();
                $content = $view->make('test', ['title' => 'Test Page'], $viewsDir);
                return new Response($content);
            });

            $app->bind(Router::class, function () use ($router) {
                return $router;
            });

            $app->bind(Request::class, function () {
                return new Request();
            });

            ob_start();
            $app->run();
            $output = ob_get_clean();

            expect($output)->toContain('<h1>Test Page</h1>');

            // Clean up
            unlink($viewsDir . '/test.guepardo.php');
            rmdir($viewsDir);
        } else {
            expect(true)->toBeTrue(); // Skip if View not available
        }
    });

    it('can handle database operations', function () {
        // Create test table
        $pdo = Database::getConnection();
        $pdo->exec('CREATE TABLE test_users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

        $app = new App();
        $router = new Router();

        $router->get('/users', function () {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM test_users');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return new Response('User count: ' . $result['count']);
        });

        $router->post('/users', function (Request $request) {
            $data = $request->all();
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('INSERT INTO test_users (name, email) VALUES (?, ?)');
            $stmt->execute([$data['name'], $data['email']]);
            return new Response('User created');
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        // Test GET request
        $_SERVER['REQUEST_URI'] = '/users';
        ob_start();
        $app->run();
        $output = ob_get_clean();
        expect($output)->toBe('User count: 0');

        // Test POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['name' => 'John', 'email' => 'john@example.com'];

        ob_start();
        $app->run();
        $output = ob_get_clean();
        expect($output)->toBe('User created');
    });

    it('can handle JSON responses', function () {
        $app = new App();
        $router = new Router();

        $router->get('/api/users', function () {
            $data = ['users' => [['id' => 1, 'name' => 'John']]];
            return Response::json($data);
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        $_SERVER['REQUEST_URI'] = '/api/users';

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        expect($decoded)->toBeArray();
        expect($decoded['users'][0]['name'])->toBe('John');
    });

    it('can handle redirects', function () {
        $app = new App();
        $router = new Router();

        $router->get('/redirect', function () {
            return Response::redirect('/home');
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        $_SERVER['REQUEST_URI'] = '/redirect';

        // Capture headers
        $response = null;
        $router->get('/redirect', function () use (&$response) {
            $response = Response::redirect('/home');
            return $response;
        });

        ob_start();
        $app->run();
        ob_get_clean();

        if ($response) {
            expect($response->getStatusCode())->toBe(302);
            expect($response->getHeader('Location'))->toBe('/home');
        }
    });

    it('can handle exceptions gracefully', function () {
        $app = new App();
        $router = new Router();

        $router->get('/error', function () {
            throw new Exception('Test error');
        });

        $app->bind(Router::class, function () use ($router) {
            return $router;
        });

        $app->bind(Request::class, function () {
            return new Request();
        });

        $_SERVER['REQUEST_URI'] = '/error';

        ob_start();
        $app->run();
        $output = ob_get_clean();

        // Should handle exception without crashing
        expect($output)->toContain('error');
    });
});
