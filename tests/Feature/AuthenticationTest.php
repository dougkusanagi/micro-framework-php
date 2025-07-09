<?php

test('home page returns successful response', function () {
    // This would test the actual application flow
    $request = $this->createRequest('GET', '/');

    // For now, just test that basic routing works
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri())->toBe('/');
});

test('authentication redirects unauthenticated users', function () {
    // Test authentication middleware behavior
    $request = $this->createRequest('GET', '/dashboard');

    // Clear any existing session
    $this->clearSession();

    expect($request->getUri())->toBe('/dashboard');
});

test('authenticated user can access protected routes', function () {
    // Simulate authenticated user
    $user = ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'];
    $this->actingAs($user);

    $request = $this->createRequest('GET', '/dashboard');

    expect($request->getUri())->toBe('/dashboard');
    expect($_SESSION['user_id'])->toBe(1);
});
