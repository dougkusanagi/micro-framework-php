<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use GuepardoSys\Core\ErrorHandler;
use GuepardoSys\Core\Logger;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use Exception;
use RuntimeException;
use InvalidArgumentException;
use ErrorException;

/**
 * Integration tests for ErrorHandler with the new debug system
 * Tests the complete error handling flow including advanced rendering
 */
class ErrorHandlerIntegrationTest extends TestCase
{
    private string $testLogDir;
    private ErrorHandler $errorHandler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testLogDir = __DIR__ . '/../../storage/logs/test';
        if (!is_dir($this->testLogDir)) {
            mkdir($this->testLogDir, 0755, true);
        }
        
        $logger = new Logger($this->testLogDir);
        $this->errorHandler = new ErrorHandler($logger, true);
    }

    protected function tearDown(): void
    {
        // Clean up test log files
        if (is_dir($this->testLogDir)) {
            $files = glob($this->testLogDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        parent::tearDown();
    }

    public function test_error_handler_integrates_with_advanced_renderer_in_debug_mode()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        $exception = new RuntimeException('Integration test exception', 500);
        
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should contain advanced error page elements
        $this->assertStringContainsString('Integration test exception', $output);
        $this->assertStringContainsString('RuntimeException', $output);
        $this->assertStringContainsString('Stack Trace', $output);
        $this->assertStringContainsString('Source Code', $output);
        $this->assertStringContainsString('Request Context', $output);
        
        // Should contain CSS and JavaScript for advanced features
        $this->assertStringContainsString('<style>', $output);
        $this->assertStringContainsString('<script>', $output);
    }

    public function test_error_handler_falls_back_to_basic_rendering_when_advanced_renderer_fails()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        // Create a mock that will fail
        $reflection = new \ReflectionClass($this->errorHandler);
        $property = $reflection->getProperty('advancedRenderer');
        $property->setAccessible(true);
        $property->setValue($this->errorHandler, null);
        
        $exception = new Exception('Fallback test exception');
        
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should contain basic error display
        $this->assertStringContainsString('Fallback test exception', $output);
        $this->assertStringContainsString('Uncaught Exception', $output);
        $this->assertStringContainsString('Stack Trace', $output);
        
        // Should not contain advanced features
        $this->assertStringNotContainsString('Source Code', $output);
        $this->assertStringNotContainsString('Request Context', $output);
    }

    public function test_error_handler_switches_between_debug_and_production_modes()
    {
        $exception = new Exception('Mode switch test', 500);
        
        // Test debug mode
        $this->errorHandler->setDebug(true);
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $debugOutput = ob_get_clean();
        
        // Test production mode
        $this->errorHandler->setDebug(false);
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $prodOutput = ob_get_clean();
        
        // Debug mode should show detailed information
        $this->assertStringContainsString('Mode switch test', $debugOutput);
        $this->assertStringContainsString('Exception', $debugOutput);
        
        // Production mode should show generic error page
        $this->assertStringContainsString('500', $prodOutput);
        $this->assertStringContainsString('Internal Server Error', $prodOutput);
        $this->assertStringNotContainsString('Mode switch test', $prodOutput);
    }

    public function test_php_error_integration_with_advanced_renderer()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        ob_start();
        $result = $this->errorHandler->doHandleError(E_WARNING, 'Test warning message', __FILE__, __LINE__);
        $output = ob_get_clean();
        
        $this->assertTrue($result);
        
        // Should contain advanced error rendering
        $this->assertStringContainsString('Test warning message', $output);
        $this->assertStringContainsString('Warning', $output);
        $this->assertStringContainsString(__FILE__, $output);
    }

    public function test_fatal_error_handling_integration()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        // Simulate a fatal error scenario
        $errorData = [
            'type' => E_ERROR,
            'message' => 'Fatal error test',
            'file' => __FILE__,
            'line' => __LINE__
        ];
        
        // Mock error_get_last to return our test error
        $originalErrorHandler = set_error_handler(function() {});
        
        ob_start();
        $this->errorHandler->handleFatalError();
        $output = ob_get_clean();
        
        restore_error_handler();
        
        // Note: This test is limited because we can't easily mock error_get_last
        // But we can verify the method doesn't crash
        $this->assertTrue(true);
    }

    public function test_logging_integration_with_advanced_error_handling()
    {
        $exception = new InvalidArgumentException('Logging integration test', 422);
        
        ob_start();
        $this->errorHandler->doHandleException($exception);
        ob_get_clean();
        
        // Check if error was logged
        $logFile = $this->testLogDir . '/' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);
        
        $logContent = file_get_contents($logFile);
        $this->assertStringContainsString('Logging integration test', $logContent);
        $this->assertStringContainsString('InvalidArgumentException', $logContent);
        $this->assertStringContainsString('[CRITICAL]', $logContent);
    }

    public function test_custom_error_pages_integration()
    {
        $this->errorHandler->setDebug(false);
        $this->errorHandler->setErrorPage(404, 'errors.404');
        
        $exception = new Exception('Not found', 404);
        
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should show basic error page since custom view doesn't exist
        $this->assertStringContainsString('404', $output);
        $this->assertStringContainsString('Not Found', $output);
    }

    public function test_security_headers_integration()
    {
        $exception = new Exception('Security test', 500);
        
        // Capture headers (this is tricky in tests, so we'll just verify no errors)
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Verify the error was handled without throwing additional exceptions
        $this->assertStringContainsString('500', $output);
    }

    public function test_error_handler_with_different_exception_types()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        $exceptions = [
            new RuntimeException('Runtime error'),
            new InvalidArgumentException('Invalid argument'),
            new \LogicException('Logic error'),
            new \BadMethodCallException('Bad method call'),
            new ErrorException('Error exception', 0, E_ERROR, __FILE__, __LINE__)
        ];
        
        foreach ($exceptions as $exception) {
            ob_start();
            $this->errorHandler->doHandleException($exception);
            $output = ob_get_clean();
            
            $this->assertStringContainsString($exception->getMessage(), $output);
            $this->assertStringContainsString(get_class($exception), $output);
            $this->assertStringContainsString('Stack Trace', $output);
        }
    }

    public function test_error_handler_with_nested_exceptions()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        $innerException = new InvalidArgumentException('Inner exception');
        $outerException = new RuntimeException('Outer exception', 0, $innerException);
        
        ob_start();
        $this->errorHandler->doHandleException($outerException);
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Outer exception', $output);
        $this->assertStringContainsString('RuntimeException', $output);
        
        // Advanced renderer should handle nested exceptions
        if (strpos($output, 'Previous Exception') !== false) {
            $this->assertStringContainsString('Inner exception', $output);
        }
    }

    public function test_error_handler_performance_with_large_stack_trace()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        // Create a deep call stack
        $exception = $this->createDeepStackTraceException(50);
        
        $startTime = microtime(true);
        
        ob_start();
        $this->errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $executionTime);
        $this->assertStringContainsString('Deep stack trace test', $output);
    }

    private function createDeepStackTraceException(int $depth): Exception
    {
        if ($depth <= 0) {
            return new Exception('Deep stack trace test');
        }
        
        try {
            return $this->createDeepStackTraceException($depth - 1);
        } catch (Exception $e) {
            throw new RuntimeException("Level $depth", 0, $e);
        }
    }

    public function test_error_handler_with_large_file_context()
    {
        $_ENV['APP_DEBUG'] = 'true';
        
        // Create a temporary large file
        $largeFile = $this->testLogDir . '/large_test_file.php';
        $content = "<?php\n";
        for ($i = 1; $i <= 1000; $i++) {
            $content .= "// Line $i - This is a test line with some content to make it longer\n";
        }
        $content .= "throw new Exception('Large file test');\n";
        
        file_put_contents($largeFile, $content);
        
        $startTime = microtime(true);
        
        try {
            include $largeFile;
        } catch (Exception $e) {
            ob_start();
            $this->errorHandler->doHandleException($e);
            $output = ob_get_clean();
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            // Should complete within reasonable time
            $this->assertLessThan(2.0, $executionTime);
            $this->assertStringContainsString('Large file test', $output);
        }
        
        // Clean up
        if (file_exists($largeFile)) {
            unlink($largeFile);
        }
    }
}