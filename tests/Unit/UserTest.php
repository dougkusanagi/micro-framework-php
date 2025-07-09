<?php

use App\Models\User;

beforeEach(function () {
    // Clear any existing session data
    refreshApplication();
});

test('user model can be instantiated', function () {
    $user = new User();
    expect($user)->toBeInstanceOf(User::class);
});

test('user model has correct table name', function () {
    $user = new User();
    expect($user->getTable())->toBe('users');
});

test('user model can validate email format', function () {
    expect('test@example.com')->toBeValidEmail();
    expect('invalid-email')->not()->toBeValidEmail();
});

test('user data can be sanitized', function () {
    $userData = [
        'name' => '  John Doe  ',
        'email' => ' JOHN@EXAMPLE.COM ',
        'age' => '25'
    ];

    $sanitized = array_map('trim', $userData);
    $sanitized['email'] = strtolower($sanitized['email']);

    expect($sanitized['name'])->toBe('John Doe');
    expect($sanitized['email'])->toBe('john@example.com');
    expect($sanitized['age'])->toBe('25');
});
