<?php

use GuepardoSys\Core\Router;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Container;

test('router can register and match routes', function () {
    $router = new Router();
    $container = new Container();

    $router->addRoute('GET', '/', function () {
        return 'home';
    });

    $router->addRoute('POST', '/users', function () {
        return 'create user';
    });

    $request = new Request('GET', '/');
    $result = $router->dispatch($request, $container);
    expect($result)->toBe('home');

    $postRequest = new Request('POST', '/users');
    $result = $router->dispatch($postRequest, $container);
    expect($result)->toBe('create user');
});

test('router can handle route parameters', function () {
    $router = new Router();
    $container = new Container();

    $router->addRoute('GET', '/user/{id}', function ($id) {
        return "User: $id";
    });

    $request = new Request('GET', '/user/123');
    $result = $router->dispatch($request, $container);
    expect($result)->toBe('User: 123');
});

test('router can handle multiple parameters', function () {
    $router = new Router();
    $container = new Container();

    $router->addRoute('GET', '/user/{id}/post/{postId}', function ($id, $postId) {
        return "User: $id, Post: $postId";
    });

    $request = new Request('GET', '/user/123/post/456');
    $result = $router->dispatch($request, $container);
    expect($result)->toBe('User: 123, Post: 456');
});
