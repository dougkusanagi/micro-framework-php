<?php

use GuepardoSys\Core\Request;

test('request can be created with basic data', function () {
    $request = new Request('GET', '/test', ['param' => 'value']);

    expect($request->getMethod())->toBe('GET');
    expect($request->getUri())->toBe('/test');
    expect($request->query('param'))->toBe('value');
});

test('request handles different HTTP methods', function () {
    $getRequest = new Request('GET', '/');
    $postRequest = new Request('POST', '/');
    $putRequest = new Request('PUT', '/');
    $deleteRequest = new Request('DELETE', '/');

    expect($getRequest->getMethod())->toBe('GET');
    expect($postRequest->getMethod())->toBe('POST');
    expect($putRequest->getMethod())->toBe('PUT');
    expect($deleteRequest->getMethod())->toBe('DELETE');
});

test('request can handle POST data', function () {
    $data = ['name' => 'John', 'email' => 'john@example.com'];
    $request = new Request('POST', '/users', [], $data);

    expect($request->input('name'))->toBe('John');
    expect($request->input('email'))->toBe('john@example.com');
    expect($request->all())->toEqual($data);
});

test('request can handle query parameters', function () {
    $query = ['page' => '1', 'limit' => '10'];
    $request = new Request('GET', '/users', $query);

    expect($request->query('page'))->toBe('1');
    expect($request->query('limit'))->toBe('10');
});

test('request provides defaults for missing parameters', function () {
    $request = new Request('GET', '/');

    expect($request->query('nonexistent'))->toBeNull();
    expect($request->query('nonexistent', 'default'))->toBe('default');
});
