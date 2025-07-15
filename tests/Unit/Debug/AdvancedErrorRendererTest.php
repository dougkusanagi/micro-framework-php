<?php

use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\ContextCollector;
use GuepardoSys\Core\Debug\StackTraceFormatter;

describe('AdvancedErrorRenderer', function () {
    beforeEach(function () {
        $this->renderer = new AdvancedErrorRenderer();
    });

    it('renders exception with modern UI template', function () {
        $exception = new Exception('Test error message', 500);
        
        $html = $this->renderer->render($exception);
        
        // Check that the HTML contains modern UI elements
        expect($html)->toContain('<!DOCTYPE html>');
        expect($html)->toContain('error-header');
        expect($html)->toContain('Test error message');
        expect($html)->toContain('Exception');
        expect($html)->toContain('section');
        expect($html)->toContain('Stack Trace');
    });

    it('renders PHP error with modern UI template', function () {
        $html = $this->renderer->renderError('Fatal Error', 'Test PHP error', __FILE__, __LINE__);
        
        // Check that the HTML contains modern UI elements
        expect($html)->toContain('<!DOCTYPE html>');
        expect($html)->toContain('error-header');
        expect($html)->toContain('Test PHP error');
        expect($html)->toContain('Fatal Error');
        expect($html)->toContain('section');
    });

    it('includes responsive CSS styling', function () {
        $exception = new Exception('Test error');
        $html = $this->renderer->render($exception);
        
        // Check for responsive design elements
        expect($html)->toContain('viewport');
        expect($html)->toContain('@media');
        expect($html)->toContain('max-width');
        expect($html)->toContain('grid-template-columns');
    });

    it('includes dark/light theme support', function () {
        $exception = new Exception('Test error');
        $html = $this->renderer->render($exception);
        
        // Check for theme support
        expect($html)->toContain(':root');
        expect($html)->toContain('--bg-primary');
        expect($html)->toContain('prefers-color-scheme: dark');
        expect($html)->toContain('--text-primary');
    });

    it('includes syntax highlighting CSS classes', function () {
        $exception = new Exception('Test error');
        $html = $this->renderer->render($exception);
        
        // Check for syntax highlighting classes
        expect($html)->toContain('php-keyword');
        expect($html)->toContain('php-string');
        expect($html)->toContain('php-comment');
        expect($html)->toContain('php-variable');
        expect($html)->toContain('php-function');
    });

    it('includes JavaScript functionality for navigation', function () {
        $exception = new Exception('Test error');
        $html = $this->renderer->render($exception);
        
        // Check for JavaScript functions
        expect($html)->toContain('toggleSection');
        expect($html)->toContain('toggleStackFrame');
        expect($html)->toContain('copyToClipboard');
        expect($html)->toContain('copyFullError');
    });

    it('includes copy functionality', function () {
        $exception = new Exception('Test error');
        $html = $this->renderer->render($exception);
        
        // Check for copy features
        expect($html)->toContain('copyCode');
        expect($html)->toContain('copyStackTrace');
        expect($html)->toContain('📋 Copy');
        expect($html)->toContain('navigator.clipboard');
    });

    it('includes collapsible sections', function () {
        $exception = new Exception('Test error');
        $html = $this->renderer->render($exception);
        
        // Check for collapsible functionality
        expect($html)->toContain('section-header');
        expect($html)->toContain('section-toggle');
        expect($html)->toContain('collapsed');
        expect($html)->toContain('onclick="toggleSection');
    });

    it('falls back to basic HTML when template is missing', function () {
        // Create a renderer and simulate missing template
        $renderer = new AdvancedErrorRenderer();
        
        // Use reflection to test the fallback method
        $reflection = new ReflectionClass($renderer);
        $method = $reflection->getMethod('generateBasicHTML');
        $method->setAccessible(true);
        
        $data = [
            'error' => [
                'type' => 'TestError',
                'message' => 'Test message',
                'file' => __FILE__,
                'line' => __LINE__
            ]
        ];
        
        $html = $method->invoke($renderer, $data);
        
        expect($html)->toContain('<!DOCTYPE html>');
        expect($html)->toContain('TestError');
        expect($html)->toContain('Test message');
    });

    it('enhances error data with source code and context', function () {
        $renderer = new AdvancedErrorRenderer();
        
        // Use reflection to test the enhance method
        $reflection = new ReflectionClass($renderer);
        $method = $reflection->getMethod('enhanceErrorData');
        $method->setAccessible(true);
        
        $data = [
            'error' => [
                'type' => 'TestError',
                'message' => 'Test message',
                'file' => __FILE__,
                'line' => 1
            ],
            'stack_trace' => []
        ];
        
        $enhanced = $method->invoke($renderer, $data);
        
        expect($enhanced)->toBeArray();
        expect($enhanced)->toHaveKey('error');
        expect($enhanced['error'])->toEqual($data['error']);
    });
});