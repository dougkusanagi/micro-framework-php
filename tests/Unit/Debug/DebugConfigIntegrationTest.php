<?php

use GuepardoSys\Core\Debug\DebugConfig;
use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\StackTraceFormatter;
use GuepardoSys\Core\Debug\ContextCollector;

beforeEach(function () {
    DebugConfig::reset();
});

afterEach(function () {
    unset($_ENV['DEBUG_SHOW_SOURCE']);
    unset($_ENV['DEBUG_CONTEXT_LINES']);
    unset($_ENV['DEBUG_MAX_STRING_LENGTH']);
    unset($_ENV['DEBUG_HIDE_VENDOR']);
    DebugConfig::reset();
});

it('respects DEBUG_SHOW_SOURCE in SourceCodeExtractor', function () {
    $extractor = new SourceCodeExtractor();
    
    // When DEBUG_SHOW_SOURCE is true (default)
    $result = $extractor->extract(__FILE__, 1);
    expect($result['error'])->toBeNull();
    expect($result['lines'])->not->toBeEmpty();
    
    // When DEBUG_SHOW_SOURCE is false
    DebugConfig::set('DEBUG_SHOW_SOURCE', false);
    $result = $extractor->extract(__FILE__, 1);
    expect($result['error'])->toBe('Source code display is disabled');
    expect($result['lines'])->toBeEmpty();
});

it('respects DEBUG_CONTEXT_LINES in SourceCodeExtractor', function () {
    $extractor = new SourceCodeExtractor();
    
    // Set custom context lines
    DebugConfig::set('DEBUG_CONTEXT_LINES', 3);
    $result = $extractor->extract(__FILE__, 10);
    
    // Should have 7 lines total (3 before + 1 current + 3 after)
    expect(count($result['lines']))->toBeLessThanOrEqual(7);
    expect($result['start_line'])->toBe(7); // 10 - 3
});

it('respects DEBUG_HIDE_VENDOR in StackTraceFormatter', function () {
    $formatter = new StackTraceFormatter();
    
    // Create a mock trace with vendor and application frames
    $trace = [
        [
            'file' => '/vendor/some/package/file.php',
            'line' => 10,
            'function' => 'vendorFunction',
            'class' => 'VendorClass',
            'type' => '::',
            'args' => []
        ],
        [
            'file' => '/app/src/MyClass.php',
            'line' => 20,
            'function' => 'myFunction',
            'class' => 'MyClass',
            'type' => '->',
            'args' => []
        ]
    ];
    
    // When DEBUG_HIDE_VENDOR is true (default)
    $formatted = $formatter->format($trace);
    expect(count($formatted))->toBe(1); // Only application frame
    expect($formatted[0]['file'])->toContain('MyClass.php');
    
    // When DEBUG_HIDE_VENDOR is false
    DebugConfig::set('DEBUG_HIDE_VENDOR', false);
    $formatted = $formatter->format($trace);
    expect(count($formatted))->toBe(2); // Both frames
});

it('respects DEBUG_MAX_STRING_LENGTH in StackTraceFormatter', function () {
    $formatter = new StackTraceFormatter();
    
    // Set a reasonable max string length (remember minimum is 100)
    DebugConfig::set('DEBUG_MAX_STRING_LENGTH', 150);
    
    $longString = str_repeat('a', 200); // Longer than max length
    $trace = [
        [
            'file' => __FILE__,
            'line' => 10,
            'function' => 'testFunction',
            'args' => [$longString]
        ]
    ];
    
    $formatted = $formatter->format($trace);
    $argPreview = $formatted[0]['args'][0]['preview'];
    
    // Should be truncated to max length
    expect(strlen($argPreview))->toBe(150); // Exactly max length (147 chars + '...')
    expect($argPreview)->toEndWith('...');
});

it('respects DEBUG_MAX_STRING_LENGTH in ContextCollector', function () {
    $collector = new ContextCollector();
    
    // Set a reasonable max string length (remember minimum is 100)
    DebugConfig::set('DEBUG_MAX_STRING_LENGTH', 150);
    
    // Mock a long string in $_GET
    $longValue = str_repeat('test', 50); // 200 characters
    $_GET['long_param'] = $longValue;
    
    $context = $collector->collect();
    $getValue = $context['request']['get']['long_param'];
    
    // Should be truncated to max length
    expect(strlen($getValue))->toBe(150); // Exactly max length (147 chars + '...')
    expect($getValue)->toEndWith('...');
    
    // Clean up
    unset($_GET['long_param']);
});