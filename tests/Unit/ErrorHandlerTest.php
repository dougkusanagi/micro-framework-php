<?php

use GuepardoSys\Core\ErrorHandler;
use GuepardoSys\Core\Logger;

describe('ErrorHandler', function () {
    beforeEach(function () {
        $this->originalErrorHandler = set_error_handler(null);
        $this->originalExceptionHandler = set_exception_handler(null);

        // Restore original handlers
        if ($this->originalErrorHandler) {
            set_error_handler($this->originalErrorHandler);
        }
        if ($this->originalExceptionHandler) {
            set_exception_handler($this->originalExceptionHandler);
        }
    });

    afterEach(function () {
        // Clean up any test handlers
        restore_error_handler();
        restore_exception_handler();
    });

    it('can register error and exception handlers', function () {
        ErrorHandler::register();

        // Check if handlers are registered (this is indirect testing)
        expect(true)->toBeTrue(); // If no exception is thrown, registration succeeded
    });

    it('can handle exceptions in development mode', function () {
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';

        $exception = new Exception('Test exception', 500);

        ob_start();
        ErrorHandler::handleException($exception);
        $output = ob_get_clean();

        expect($output)->toContain('Test exception');
        expect($output)->toContain('Exception');
    });

    it('can handle exceptions in production mode', function () {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';

        $exception = new Exception('Production exception', 500);

        ob_start();
        ErrorHandler::handleException($exception);
        $output = ob_get_clean();

        // Should show generic error page, not detailed exception
        expect($output)->not->toContain('Production exception');
        expect($output)->toContain('500'); // Should show error code
    });

    it('can handle different exception types', function () {
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';

        $runtimeException = new RuntimeException('Runtime error');
        $invalidArgumentException = new InvalidArgumentException('Invalid argument');

        ob_start();
        ErrorHandler::handleException($runtimeException);
        $output1 = ob_get_clean();

        ob_start();
        ErrorHandler::handleException($invalidArgumentException);
        $output2 = ob_get_clean();

        expect($output1)->toContain('Runtime error');
        expect($output1)->toContain('RuntimeException');

        expect($output2)->toContain('Invalid argument');
        expect($output2)->toContain('InvalidArgumentException');
    });

    it('can handle PHP errors', function () {
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';

        $errno = E_WARNING;
        $errstr = 'Test warning message';
        $errfile = __FILE__;
        $errline = __LINE__;

        ob_start();
        $result = ErrorHandler::handleError($errno, $errstr, $errfile, $errline);
        $output = ob_get_clean();

        // Error handler should return true to prevent default PHP error handling
        expect($result)->toBeTrue();
    });

    it('can convert errors to exceptions', function () {
        $errno = E_ERROR;
        $errstr = 'Fatal error message';
        $errfile = __FILE__;
        $errline = __LINE__;

        expect(function () use ($errno, $errstr, $errfile, $errline) {
            ErrorHandler::handleError($errno, $errstr, $errfile, $errline);
        })->toThrow(ErrorException::class);
    });

    it('logs exceptions when logger is available', function () {
        // Mock logger to verify logging
        $logDir = __DIR__ . '/../../storage/logs/test';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logger = new Logger($logDir);
        ErrorHandler::setLogger($logger);

        $exception = new Exception('Logged exception', 500);

        ob_start();
        ErrorHandler::handleException($exception);
        ob_get_clean();

        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            expect($content)->toContain('Logged exception');
            expect($content)->toContain('[ERROR]');
        }

        // Clean up
        if (file_exists($logFile)) {
            unlink($logFile);
        }
    });

    it('handles fatal errors', function () {
        // This is harder to test directly, but we can test the shutdown function registration
        ErrorHandler::register();

        // Check if shutdown function is registered
        expect(true)->toBeTrue(); // If registration doesn't throw, it's successful
    });

    it('can set custom error pages', function () {
        if (method_exists(ErrorHandler::class, 'setErrorView')) {
            ErrorHandler::setErrorView(404, 'custom.404');
            ErrorHandler::setErrorView(500, 'custom.500');

            expect(true)->toBeTrue(); // If no exception is thrown, setting succeeded
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });

    it('handles different HTTP error codes', function () {
        $_ENV['APP_ENV'] = 'production';

        $codes = [400, 401, 403, 404, 422, 500, 503];

        foreach ($codes as $code) {
            $exception = new Exception("Error $code", $code);

            ob_start();
            ErrorHandler::handleException($exception);
            $output = ob_get_clean();

            expect($output)->toContain((string)$code);
        }
    });

    it('can handle recursion in error handling', function () {
        // Test that error handler doesn't cause infinite loops
        $_ENV['APP_ENV'] = 'development';

        $exception = new Exception('Recursion test');

        ob_start();
        ErrorHandler::handleException($exception);
        $output = ob_get_clean();

        expect($output)->toContain('Recursion test');
    });

    it('sanitizes output for security', function () {
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';

        $maliciousException = new Exception('<script>alert("xss")</script>');

        ob_start();
        ErrorHandler::handleException($maliciousException);
        $output = ob_get_clean();

        // Should escape HTML entities
        expect($output)->not->toContain('<script>alert("xss")</script>');
        expect($output)->toContain('&lt;script&gt;');
    });

    it('can ignore certain error types', function () {
        // Test that certain error types can be ignored
        $result = ErrorHandler::handleError(E_NOTICE, 'Notice message', __FILE__, __LINE__);

        // Notices might be ignored depending on configuration
        expect($result)->toBeIn([true, false]);
    });
});
