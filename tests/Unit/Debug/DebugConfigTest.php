<?php

use GuepardoSys\Core\Debug\DebugConfig;

beforeEach(function () {
    // Reset configuration before each test
    DebugConfig::reset();
});

afterEach(function () {
    // Clean up environment variables after each test
    unset($_ENV['DEBUG_SHOW_SOURCE']);
    unset($_ENV['DEBUG_CONTEXT_LINES']);
    unset($_ENV['DEBUG_MAX_STRING_LENGTH']);
    unset($_ENV['DEBUG_HIDE_VENDOR']);
    DebugConfig::reset();
});

it('has correct default values', function () {
    expect(DebugConfig::showSource())->toBeTrue();
    expect(DebugConfig::getContextLines())->toBe(10);
    expect(DebugConfig::getMaxStringLength())->toBe(1000);
    expect(DebugConfig::hideVendor())->toBeTrue();
});

it('handles show source configuration correctly', function () {
    // Test true values
    $_ENV['DEBUG_SHOW_SOURCE'] = 'true';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeTrue();

    $_ENV['DEBUG_SHOW_SOURCE'] = '1';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeTrue();

    $_ENV['DEBUG_SHOW_SOURCE'] = 'yes';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeTrue();

    $_ENV['DEBUG_SHOW_SOURCE'] = 'on';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeTrue();

    // Test false values
    $_ENV['DEBUG_SHOW_SOURCE'] = 'false';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeFalse();

    $_ENV['DEBUG_SHOW_SOURCE'] = '0';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeFalse();

    $_ENV['DEBUG_SHOW_SOURCE'] = 'no';
    DebugConfig::reset();
    expect(DebugConfig::showSource())->toBeFalse();
});

it('handles context lines configuration correctly', function () {
    $_ENV['DEBUG_CONTEXT_LINES'] = '5';
    DebugConfig::reset();
    expect(DebugConfig::getContextLines())->toBe(5);

    $_ENV['DEBUG_CONTEXT_LINES'] = '25';
    DebugConfig::reset();
    expect(DebugConfig::getContextLines())->toBe(25);

    // Test limits
    $_ENV['DEBUG_CONTEXT_LINES'] = '100'; // Should be limited to 50
    DebugConfig::reset();
    expect(DebugConfig::getContextLines())->toBe(50);

    $_ENV['DEBUG_CONTEXT_LINES'] = '-5'; // Should be limited to 0
    DebugConfig::reset();
    expect(DebugConfig::getContextLines())->toBe(0);
});

it('handles max string length configuration correctly', function () {
    $_ENV['DEBUG_MAX_STRING_LENGTH'] = '500';
    DebugConfig::reset();
    expect(DebugConfig::getMaxStringLength())->toBe(500);

    $_ENV['DEBUG_MAX_STRING_LENGTH'] = '2000';
    DebugConfig::reset();
    expect(DebugConfig::getMaxStringLength())->toBe(2000);

    // Test limits
    $_ENV['DEBUG_MAX_STRING_LENGTH'] = '50'; // Should be limited to 100
    DebugConfig::reset();
    expect(DebugConfig::getMaxStringLength())->toBe(100);

    $_ENV['DEBUG_MAX_STRING_LENGTH'] = '20000'; // Should be limited to 10000
    DebugConfig::reset();
    expect(DebugConfig::getMaxStringLength())->toBe(10000);
});

it('handles hide vendor configuration correctly', function () {
    // Test true values
    $_ENV['DEBUG_HIDE_VENDOR'] = 'true';
    DebugConfig::reset();
    expect(DebugConfig::hideVendor())->toBeTrue();

    $_ENV['DEBUG_HIDE_VENDOR'] = '1';
    DebugConfig::reset();
    expect(DebugConfig::hideVendor())->toBeTrue();

    // Test false values
    $_ENV['DEBUG_HIDE_VENDOR'] = 'false';
    DebugConfig::reset();
    expect(DebugConfig::hideVendor())->toBeFalse();

    $_ENV['DEBUG_HIDE_VENDOR'] = '0';
    DebugConfig::reset();
    expect(DebugConfig::hideVendor())->toBeFalse();
});

it('returns all configuration correctly', function () {
    $_ENV['DEBUG_SHOW_SOURCE'] = 'false';
    $_ENV['DEBUG_CONTEXT_LINES'] = '15';
    $_ENV['DEBUG_MAX_STRING_LENGTH'] = '800';
    $_ENV['DEBUG_HIDE_VENDOR'] = 'false';
    DebugConfig::reset();

    $config = DebugConfig::all();

    expect($config['DEBUG_SHOW_SOURCE'])->toBeFalse();
    expect($config['DEBUG_CONTEXT_LINES'])->toBe(15);
    expect($config['DEBUG_MAX_STRING_LENGTH'])->toBe(800);
    expect($config['DEBUG_HIDE_VENDOR'])->toBeFalse();
});

it('allows setting configuration values', function () {
    DebugConfig::set('DEBUG_SHOW_SOURCE', false);
    expect(DebugConfig::showSource())->toBeFalse();

    DebugConfig::set('DEBUG_CONTEXT_LINES', 20);
    expect(DebugConfig::getContextLines())->toBe(20);

    DebugConfig::set('DEBUG_MAX_STRING_LENGTH', 1500);
    expect(DebugConfig::getMaxStringLength())->toBe(1500);

    DebugConfig::set('DEBUG_HIDE_VENDOR', false);
    expect(DebugConfig::hideVendor())->toBeFalse();
});

it('handles custom defaults correctly', function () {
    $value = DebugConfig::get('NONEXISTENT_KEY', 'custom_default');
    expect($value)->toBe('custom_default');
});

it('caches configuration values', function () {
    // First call should read from environment
    $_ENV['DEBUG_CONTEXT_LINES'] = '15';
    expect(DebugConfig::getContextLines())->toBe(15);

    // Change environment variable
    $_ENV['DEBUG_CONTEXT_LINES'] = '20';
    
    // Should still return cached value
    expect(DebugConfig::getContextLines())->toBe(15);

    // Reset cache and should read new value
    DebugConfig::reset();
    expect(DebugConfig::getContextLines())->toBe(20);
});