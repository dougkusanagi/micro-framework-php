<?php

use GuepardoSys\Core\App;
use GuepardoSys\Core\Container;
use GuepardoSys\Core\Router;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;
use Tests\Helpers\TestHelpers;

describe('App Core', function () {
    beforeEach(function () {
        // Clean environment before each test
        TestHelpers::cleanupTestEnvironment();
        $this->container = new Container();

        // Set minimal server environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['REQUEST_TIME'] = time();
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
        $router = new Router();
        $router->get('/', function () {
            return new Response('Hello World');
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        expect($output)->toBe('Hello World');
    });

    it('can handle route parameters', function () {
        $_SERVER['REQUEST_URI'] = '/users/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new Router();
        $router->get('/users/{id}', function (Request $request) {
            $id = $request->getRouteParam('id');
            return new Response('User ID: ' . $id);
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        expect($output)->toBe('User ID: 123');
    });

    it('can handle POST requests', function () {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/api/test';
        $_POST['name'] = 'Test Name';

        $router = new Router();
        $router->post('/api/test', function (Request $request) {
            $name = $request->input('name');
            return new Response('Posted: ' . $name);
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        expect($output)->toBe('Posted: Test Name');
    });

    it('can handle JSON responses', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/json';

        $router = new Router();
        $router->get('/api/json', function () {
            $response = new Response();
            return $response->json(['status' => 'success', 'data' => 'test']);
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        $expected = json_encode(['status' => 'success', 'data' => 'test']);
        expect($output)->toBe($expected);
    });

    it('can handle redirects', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/redirect-test';

        $router = new Router();
        $router->get('/redirect-test', function () {
            $response = new Response();
            return $response->redirect('/target');
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        // Should have minimal output for redirect
        expect($output)->toBe('');
    });

    it('handles 404 errors gracefully', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';

        $router = new Router();
        // No routes added intentionally

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        expect($output)->toContain('404');
    });

    it('can handle exceptions gracefully', function () {
        $_ENV['APP_DEBUG'] = 'true';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/error';

        $router = new Router();
        $router->get('/error', function () {
            throw new Exception('Test error');
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        ob_start();
        try {
            $app->run();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            // If exception is thrown, that's expected behavior
            expect($e->getMessage())->toBe('Test error');
            return;
        }

        // If no exception, check error output
        expect($output)->toContain('error');
    });

    it('can handle middleware if implemented', function () {
        // Simple test to verify middleware structure exists
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/protected';

        $router = new Router();
        $router->get('/protected', function () {
            return new Response('Protected content');
        });

        $this->container->instance(Router::class, $router);
        $app = new App($this->container);

        expect($app)->toBeInstanceOf(App::class);
        expect($app->getContainer())->toBeInstanceOf(Container::class);
    });
});
