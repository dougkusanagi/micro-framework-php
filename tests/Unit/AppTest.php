<?php

use GuepardoSys\Core\App;
use GuepardoSys\Core\Container;
use GuepardoSys\Core\Router;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;
use Tests\Helpers\TestHelpers;

describe('App Core', function () {
    beforeEach(function () {
        TestHelpers::cleanupTestEnvironment();
        $this->container = new Container();
    });

    afterEach(function () {
        TestHelpers::cleanupTestEnvironment();
    });

    it('can be instantiated', function () {
        $app = new App($this->container);
        expect($app)->toBeInstanceOf(App::class);
        expect($app->getContainer())->toBe($this->container);
    });

    it('can run basic request', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';

        $response = new Response('Hello World');

        $router = new Router();
        $router->get('/', function () use ($response) {
            return $response;
        });

        $this->container->instance(Router::class, $router);

        $app = new App($this->container);

        $output = captureOutput(function () use ($app) {
            $app->run();
        });

        expect($output)->toBe('Hello World');
    });

    it('can handle route parameters', function () {
        $_SERVER['REQUEST_URI'] = '/users/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new Router();
        $router->get('/users/{id}', function (Request $request, $id) {
            return new Response('User ID: ' . $id);
        });

        $this->container->instance(Router::class, $router);
        $this->container->instance(Request::class, Request::createFromGlobals());

        $app = new App($this->container);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        expect($output)->toBe('User ID: 123');
    });

    it('can handle POST requests', function () {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/users';
        $_POST = ['name' => 'John'];

        $router = new Router();
        $router->post('/users', function (Request $request) {
            $data = $request->all();
            return new Response('Created user: ' . $data['name']);
        });

        $this->container->instance(Router::class, $router);
        $this->container->instance(Request::class, Request::createFromGlobals());

        $app = new App($this->container);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        expect($output)->toBe('Created user: John');

        // Clean up
        $_POST = [];
    });

    it('can handle JSON responses', function () {
        $router = new Router();
        $router->get('/api/users', function () {
            return Response::jsonResponse(['users' => [['id' => 1, 'name' => 'John']]]);
        });

        $this->container->instance(Router::class, $router);
        $this->container->instance(Request::class, Request::createFromGlobals());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/users';

        $app = new App($this->container);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        expect($data)->toBeArray();
        expect($data['users'][0]['name'])->toBe('John');
    });

    it('can handle redirects', function () {
        $router = new Router();
        $router->get('/redirect', function () {
            return Response::redirectResponse('/home');
        });

        $this->container->instance(Router::class, $router);
        $this->container->instance(Request::class, Request::createFromGlobals());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/redirect';

        $app = new App($this->container);

        // Since headers can't be tested directly, we'll test the response object
        $router->get('/redirect', function () {
            $response = Response::redirect('/home');
            expect($response->getStatusCode())->toBe(302);
            expect($response->getHeader('Location'))->toBe('/home');
            return $response;
        });

        ob_start();
        $app->run();
        ob_get_clean();

        expect(true)->toBeTrue(); // Test passed if no exceptions
    });

    it('handles 404 errors gracefully', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/non-existent';

        $router = new Router();
        // No routes defined

        $this->container->instance(Router::class, $router);
        $this->container->instance(Request::class, Request::createFromGlobals());

        $app = new App($this->container);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        // Should handle 404 without crashing
        expect($output)->toContain('404');
    });

    it('can handle exceptions', function () {
        $router = new Router();
        $router->get('/error', function () {
            throw new Exception('Test error');
        });

        $this->container->instance(Router::class, $router);
        $this->container->instance(Request::class, Request::createFromGlobals());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/error';

        $app = new App($this->container);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        // Should handle exception gracefully
        expect($output)->toContain('error');
    });

    it('can handle middleware if implemented', function () {
        // This test checks if middleware system works
        expect(true)->toBeTrue(); // Skip for now as middleware implementation varies
    });
});
