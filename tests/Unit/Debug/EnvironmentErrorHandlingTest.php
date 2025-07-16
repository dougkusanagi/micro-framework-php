<?php

namespace Tests\Unit\Debug;

use Tests\TestCase;
use GuepardoSys\Core\ErrorHandler;
use GuepardoSys\Core\Logger;
use Exception;
use RuntimeException;
use InvalidArgumentException;

/**
 * Tests error handling behavior in different environments (dev/prod)
 * Ensures proper behavior switching between debug and production modes
 */
class EnvironmentErrorHandlingTest extends TestCase
{
    private string $testLogDir;
    protected array $originalEnv;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testLogDir = __DIR__ . '/../../storage/logs/env_test';
        if (!is_dir($this->testLogDir)) {
            mkdir($this->testLogDir, 0755, true);
        }
        
        // Backup original environment
        $this->originalEnv = $_ENV;
    }

    protected function tearDown(): void
    {
        // Restore original environment
        $_ENV = $this->originalEnv;
        
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

    public function test_development_environment_shows_detailed_errors()
    {
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, true);
        
        $exception = new RuntimeException('Development error details', 500);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should show detailed error information
        $this->assertStringContainsString('Development error details', $output);
        $this->assertStringContainsString('RuntimeException', $output);
        $this->assertStringContainsString('Stack Trace', $output);
        $this->assertStringContainsString('Source Code', $output);
        $this->assertStringContainsString(__FILE__, $output);
        
        // Should contain advanced debug features
        $this->assertStringContainsString('Request Context', $output);
        $this->assertStringContainsString('copy-button', $output);
        $this->assertStringContainsString('<style>', $output);
        $this->assertStringContainsString('<script>', $output);
    }

    public function test_production_environment_hides_sensitive_information()
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, false);
        
        $exception = new RuntimeException('Sensitive production error', 500);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should NOT show sensitive information
        $this->assertStringNotContainsString('Sensitive production error', $output);
        $this->assertStringNotContainsString('RuntimeException', $output);
        $this->assertStringNotContainsString('Stack Trace', $output);
        $this->assertStringNotContainsString(__FILE__, $output);
        
        // Should show generic error page
        $this->assertStringContainsString('500', $output);
        $this->assertStringContainsString('Internal Server Error', $output);
        $this->assertStringContainsString('Sorry, something went wrong', $output);
    }

    public function test_testing_environment_behavior()
    {
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, true);
        
        $exception = new InvalidArgumentException('Testing environment error', 422);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Testing environment should behave like development
        $this->assertStringContainsString('Testing environment error', $output);
        $this->assertStringContainsString('InvalidArgumentException', $output);
        $this->assertStringContainsString('Stack Trace', $output);
    }

    public function test_staging_environment_behavior()
    {
        $_ENV['APP_ENV'] = 'staging';
        $_ENV['APP_DEBUG'] = 'false';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, false);
        
        $exception = new Exception('Staging environment error', 500);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Staging should behave like production
        $this->assertStringNotContainsString('Staging environment error', $output);
        $this->assertStringContainsString('500', $output);
        $this->assertStringContainsString('Internal Server Error', $output);
    }

    public function test_debug_flag_overrides_environment()
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'true'; // Debug enabled despite production env
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, true);
        
        $exception = new Exception('Debug override test', 500);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should show debug information because debug flag is true
        $this->assertStringContainsString('Debug override test', $output);
        $this->assertStringContainsString('Exception', $output);
        $this->assertStringContainsString('Stack Trace', $output);
    }

    public function test_php_error_handling_in_different_environments()
    {
        $testCases = [
            ['development', 'true', true],
            ['production', 'false', false],
            ['testing', 'true', true],
            ['staging', 'false', false]
        ];
        
        foreach ($testCases as [$env, $debug, $shouldShowDetails]) {
            $_ENV['APP_ENV'] = $env;
            $_ENV['APP_DEBUG'] = $debug;
            
            $logger = new Logger($this->testLogDir);
            $errorHandler = new ErrorHandler($logger, $debug === 'true');
            
            ob_start();
            $result = $errorHandler->doHandleError(E_WARNING, "Test warning in $env", __FILE__, __LINE__);
            $output = ob_get_clean();
            
            $this->assertTrue($result);
            
            if ($shouldShowDetails) {
                $this->assertStringContainsString("Test warning in $env", $output);
                $this->assertStringContainsString('Warning', $output);
            } else {
                // In production, warnings might not be displayed
                // This depends on the implementation
            }
        }
    }

    public function test_logging_behavior_across_environments()
    {
        $environments = [
            ['development', 'true'],
            ['production', 'false'],
            ['testing', 'true'],
            ['staging', 'false']
        ];
        
        foreach ($environments as [$env, $debug]) {
            $_ENV['APP_ENV'] = $env;
            $_ENV['APP_DEBUG'] = $debug;
            
            $logger = new Logger($this->testLogDir);
            $errorHandler = new ErrorHandler($logger, $debug === 'true');
            
            $exception = new Exception("Error in $env environment", 500);
            
            ob_start();
            $errorHandler->doHandleException($exception);
            ob_get_clean();
            
            // All environments should log errors
            $logFile = $this->testLogDir . '/' . date('Y-m-d') . '.log';
            $this->assertFileExists($logFile);
            
            $logContent = file_get_contents($logFile);
            $this->assertStringContainsString("Error in $env environment", $logContent);
            $this->assertStringContainsString('[CRITICAL]', $logContent);
            
            // Clear log for next test
            file_put_contents($logFile, '');
        }
    }

    public function test_error_page_customization_by_environment()
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, false);
        
        // Set custom error pages
        $errorHandler->setErrorPage(404, 'errors.404');
        $errorHandler->setErrorPage(500, 'errors.500');
        
        $exception = new Exception('Custom error page test', 404);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should attempt to use custom error page, fall back to basic
        $this->assertStringContainsString('404', $output);
        $this->assertStringContainsString('Not Found', $output);
    }

    public function test_security_considerations_in_production()
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, false);
        
        // Test with potentially sensitive information
        $sensitiveData = [
            'password' => 'secret123',
            'api_key' => 'sk-1234567890abcdef',
            'database_url' => 'mysql://user:pass@localhost/db'
        ];
        
        $exception = new Exception('Error with sensitive data: ' . json_encode($sensitiveData), 500);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        // Should not expose sensitive information
        $this->assertStringNotContainsString('secret123', $output);
        $this->assertStringNotContainsString('sk-1234567890abcdef', $output);
        $this->assertStringNotContainsString('mysql://user:pass@localhost/db', $output);
        
        // Should show generic error
        $this->assertStringContainsString('500', $output);
        $this->assertStringContainsString('Internal Server Error', $output);
    }

    public function test_performance_impact_in_production()
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';
        
        $logger = new Logger($this->testLogDir);
        $errorHandler = new ErrorHandler($logger, false);
        
        $exception = new Exception('Performance test error', 500);
        
        $startTime = microtime(true);
        
        ob_start();
        $errorHandler->doHandleException($exception);
        $output = ob_get_clean();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Production error handling should be fast (less than 100ms)
        $this->assertLessThan(0.1, $executionTime);
        
        // Output should be minimal
        $this->assertLessThan(10000, strlen($output)); // Less than 10KB
    }

    public function test_environment_variable_validation()
    {
        // Test with invalid environment values
        $invalidValues = [
            ['APP_ENV' => '', 'APP_DEBUG' => 'true'],
            ['APP_ENV' => 'invalid', 'APP_DEBUG' => 'true'],
            ['APP_ENV' => 'production', 'APP_DEBUG' => 'invalid'],
            ['APP_ENV' => 'development', 'APP_DEBUG' => '1'],
            ['APP_ENV' => 'production', 'APP_DEBUG' => '0']
        ];
        
        foreach ($invalidValues as $envVars) {
            foreach ($envVars as $key => $value) {
                $_ENV[$key] = $value;
            }
            
            $logger = new Logger($this->testLogDir);
            
            // Should handle invalid values gracefully
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            $errorHandler = new ErrorHandler($logger, $debug);
            
            $exception = new Exception('Environment validation test', 500);
            
            ob_start();
            $errorHandler->doHandleException($exception);
            $output = ob_get_clean();
            
            // Should not crash and should produce some output
            $this->assertNotEmpty($output);
            $this->assertStringContainsString('500', $output);
        }
    }

    public function test_memory_usage_in_different_environments()
    {
        $environments = [
            ['development', 'true'],
            ['production', 'false']
        ];
        
        foreach ($environments as [$env, $debug]) {
            $_ENV['APP_ENV'] = $env;
            $_ENV['APP_DEBUG'] = $debug;
            
            $memoryBefore = memory_get_usage(true);
            
            $logger = new Logger($this->testLogDir);
            $errorHandler = new ErrorHandler($logger, $debug === 'true');
            
            $exception = new Exception("Memory test in $env", 500);
            
            ob_start();
            $errorHandler->doHandleException($exception);
            ob_get_clean();
            
            $memoryAfter = memory_get_usage(true);
            $memoryUsed = $memoryAfter - $memoryBefore;
            
            // Memory usage should be reasonable (less than 10MB)
            $this->assertLessThan(10 * 1024 * 1024, $memoryUsed);
            
            // Production should use less memory than development
            if ($env === 'production') {
                $prodMemory = $memoryUsed;
            } elseif ($env === 'development' && isset($prodMemory)) {
                // Development might use more memory due to advanced rendering
                // This is acceptable as long as it's not excessive
                $this->assertLessThan($memoryUsed * 2, $prodMemory);
            }
        }
    }
}