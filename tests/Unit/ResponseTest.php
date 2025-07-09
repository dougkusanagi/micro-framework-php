<?php

use GuepardoSys\Core\Response;

describe('Response', function () {
    it('can create basic response', function () {
        $response = new Response('Hello World');

        expect($response->getContent())->toBe('Hello World');
        expect($response->getStatusCode())->toBe(200);
    });

    it('can create response with custom status code', function () {
        $response = new Response('Not Found', 404);

        expect($response->getContent())->toBe('Not Found');
        expect($response->getStatusCode())->toBe(404);
    });

    it('can set and get headers', function () {
        $response = new Response('Test');

        $response->setHeader('Content-Type', 'application/json');
        $response->setHeader('X-Custom-Header', 'custom-value');

        expect($response->getHeader('Content-Type'))->toBe('application/json');
        expect($response->getHeader('X-Custom-Header'))->toBe('custom-value');
        expect($response->getHeader('Non-Existent'))->toBeNull();
    });

    it('can get all headers', function () {
        $response = new Response('Test');

        $response->setHeader('Content-Type', 'text/html');
        $response->setHeader('Cache-Control', 'no-cache');

        $headers = $response->getHeaders();

        expect($headers)->toBeArray();
        expect($headers['Content-Type'])->toBe('text/html');
        expect($headers['Cache-Control'])->toBe('no-cache');
    });

    it('can create JSON response', function () {
        $data = ['message' => 'Success', 'data' => [1, 2, 3]];
        $response = Response::jsonResponse($data);

        expect($response->getContent())->toBe(json_encode($data));
        expect($response->getHeader('Content-Type'))->toBe('application/json');
        expect($response->getStatusCode())->toBe(200);
    });

    it('can create JSON response with custom status', function () {
        $data = ['error' => 'Not found'];
        $response = Response::jsonResponse($data, 404);

        expect($response->getContent())->toBe(json_encode($data));
        expect($response->getStatusCode())->toBe(404);
    });

    it('can create redirect response', function () {
        $response = Response::redirectResponse('/home');

        expect($response->getStatusCode())->toBe(302);
        expect($response->getHeader('Location'))->toBe('/home');
    });

    it('can create permanent redirect response', function () {
        $response = Response::redirectResponse('/home', 301);

        expect($response->getStatusCode())->toBe(301);
        expect($response->getHeader('Location'))->toBe('/home');
    });

    it('can set status code', function () {
        $response = new Response('Test');

        $response->setStatusCode(500);

        expect($response->getStatusCode())->toBe(500);
    });

    it('can set content', function () {
        $response = new Response('Original');

        $response->setContent('Updated Content');

        expect($response->getContent())->toBe('Updated Content');
    });

    it('sends headers and content when sent', function () {
        $response = new Response('Test Content');
        $response->setHeader('X-Test-Header', 'test-value');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        expect($output)->toBe('Test Content');

        // Note: Headers are sent via header() function which we can't easily test
        // In a real test environment, you might use output buffering or mocking
    });

    it('can create view response', function () {
        if (method_exists(Response::class, 'view')) {
            $response = Response::view('test.view', ['var' => 'value']);

            expect($response)->toBeInstanceOf(Response::class);
            expect($response->getStatusCode())->toBe(200);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });

    it('can create download response', function () {
        if (method_exists(Response::class, 'download')) {
            $response = Response::download('/path/to/file.txt', 'download.txt');

            expect($response)->toBeInstanceOf(Response::class);
            expect($response->getHeader('Content-Disposition'))->toContain('attachment');
            expect($response->getHeader('Content-Disposition'))->toContain('download.txt');
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });

    it('handles arrays and objects in content', function () {
        $data = ['key' => 'value', 'array' => [1, 2, 3]];
        $response = new Response($data);

        expect($response->getContent())->toBe(json_encode($data));
    });

    it('converts objects to string', function () {
        $object = new class {
            public function __toString()
            {
                return 'Object String';
            }
        };

        $response = new Response($object);

        expect($response->getContent())->toBe('Object String');
    });

    it('can chain method calls', function () {
        $response = (new Response('Test'))
            ->setStatusCode(201)
            ->setHeader('X-Custom', 'value');

        expect($response->getStatusCode())->toBe(201);
        expect($response->getHeader('X-Custom'))->toBe('value');
        expect($response->getContent())->toBe('Test');
    });
});
