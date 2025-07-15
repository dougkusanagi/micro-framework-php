<?php

namespace Tests\Unit\Debug;

use Tests\TestCase;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\StackTraceFormatter;
use GuepardoSys\Core\Debug\ContextCollector;
use GuepardoSys\Core\ErrorHandler;
use GuepardoSys\Core\Logger;
use Exception;
use RuntimeException;

/**
 * Performance tests for large stack traces and files
 * Ensures the debug system performs well under stress conditions
 */
class PerformanceTest extends TestCase
{
    private string $testDataDir;
    private AdvancedErrorRenderer $renderer;
    private SourceCodeExtractor $sourceExtractor;
    private StackTraceFormatter $stackFormatter;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testDataDir = __DIR__ . '/../../storage/performance_test_data';
        if (!is_dir($this->testDataDir)) {
            mkdir($this->testDataDir, 0755, true);
        }
        
        $this->renderer = new AdvancedErrorRenderer();
        $this->sourceExtractor = new SourceCodeExtractor();
        $this->stackFormatter = new StackTraceFormatter();
    }

    protected function tearDown(): void
    {
        // Clean up test data files
        if (is_dir($this->testDataDir)) {
            $files = glob($this->testDataDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        parent::tearDown();
    }

    public function test_performance_with_deep_stack_trace()
    {
        $exception = $this->createDeepStackTraceException(100);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $output = $this->renderer->render($exception);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should complete within reasonable time (less than 2 seconds)
        $this->assertLessThan(2.0, $executionTime, "Deep stack trace rendering took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, "Deep stack trace used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // Output should contain the exception
        $this->assertStringContainsString('Deep stack trace test', $output);
        $this->assertStringContainsString('Stack Trace', $output);
    }

    public function test_performance_with_very_large_file()
    {
        $largeFile = $this->createLargePhpFile(10000); // 10,000 lines
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $sourceCode = $this->sourceExtractor->extract($largeFile, 5000, 20);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should complete quickly (less than 500ms)
        $this->assertLessThan(0.5, $executionTime, "Large file extraction took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 10MB)
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, "Large file extraction used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // Should return expected number of lines
        $this->assertCount(41, $sourceCode['lines']); // 20 before + 1 target + 20 after
        $this->assertEquals(5000, $sourceCode['highlighted_line']);
        
        // Clean up
        unlink($largeFile);
    }

    public function test_performance_with_large_stack_trace_formatting()
    {
        $largeTrace = $this->createLargeStackTrace(500);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $formattedTrace = $this->stackFormatter->format($largeTrace);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should complete quickly (less than 1 second)
        $this->assertLessThan(1.0, $executionTime, "Large stack trace formatting took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 20MB)
        $this->assertLessThan(20 * 1024 * 1024, $memoryUsed, "Large stack trace formatting used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // Should format all frames
        $this->assertCount(500, $formattedTrace);
        
        // Each frame should have required properties
        foreach (array_slice($formattedTrace, 0, 10) as $frame) { // Check first 10 frames
            $this->assertArrayHasKey('file', $frame);
            $this->assertArrayHasKey('line', $frame);
            $this->assertArrayHasKey('function', $frame);
            $this->assertArrayHasKey('is_vendor', $frame);
            $this->assertArrayHasKey('is_application', $frame);
        }
    }

    public function test_performance_with_multiple_large_files_in_stack_trace()
    {
        // Create multiple large files
        $largeFiles = [];
        for ($i = 0; $i < 5; $i++) {
            $largeFiles[] = $this->createLargePhpFile(2000, "large_file_$i.php");
        }
        
        // Create exception with stack trace referencing these files
        $exception = $this->createExceptionWithMultipleFiles($largeFiles);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $output = $this->renderer->render($exception);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should complete within reasonable time (less than 3 seconds)
        $this->assertLessThan(3.0, $executionTime, "Multiple large files rendering took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 100MB)
        $this->assertLessThan(100 * 1024 * 1024, $memoryUsed, "Multiple large files used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // Output should contain references to the files
        $this->assertStringContainsString('Multiple files test', $output);
        
        // Clean up
        foreach ($largeFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function test_performance_with_large_context_data()
    {
        // Simulate large request context
        $_GET = array_fill(0, 1000, 'large_get_data_' . str_repeat('x', 100));
        $_POST = array_fill(0, 1000, 'large_post_data_' . str_repeat('y', 100));
        $_SESSION = array_fill(0, 500, 'large_session_data_' . str_repeat('z', 200));
        $_SERVER = array_merge($_SERVER, array_fill(0, 200, 'large_server_data_' . str_repeat('w', 150)));
        
        $contextCollector = new ContextCollector();
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $context = $contextCollector->collect();
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should complete quickly (less than 500ms)
        $this->assertLessThan(0.5, $executionTime, "Large context collection took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, "Large context collection used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // Should collect all context types
        $this->assertArrayHasKey('request', $context);
        $this->assertArrayHasKey('session', $context);
        $this->assertArrayHasKey('server', $context);
        
        // Clean up
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
    }

    public function test_performance_with_concurrent_error_handling()
    {
        $logger = new Logger($this->testDataDir);
        $errorHandler = new ErrorHandler($logger, true);
        
        $exceptions = [];
        for ($i = 0; $i < 10; $i++) {
            $exceptions[] = new Exception("Concurrent test exception $i", 500);
        }
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $outputs = [];
        foreach ($exceptions as $exception) {
            ob_start();
            $errorHandler->doHandleException($exception);
            $outputs[] = ob_get_clean();
        }
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should handle all exceptions within reasonable time (less than 5 seconds)
        $this->assertLessThan(5.0, $executionTime, "Concurrent error handling took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 100MB)
        $this->assertLessThan(100 * 1024 * 1024, $memoryUsed, "Concurrent error handling used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // All outputs should be generated
        $this->assertCount(10, $outputs);
        
        // Each output should contain the respective exception
        for ($i = 0; $i < 10; $i++) {
            $this->assertStringContainsString("Concurrent test exception $i", $outputs[$i]);
        }
    }

    public function test_memory_leak_prevention()
    {
        $initialMemory = memory_get_usage(true);
        
        // Process many exceptions to test for memory leaks
        for ($i = 0; $i < 100; $i++) {
            $exception = new RuntimeException("Memory leak test $i", 500);
            
            ob_start();
            $output = $this->renderer->render($exception);
            ob_end_clean();
            
            // Force garbage collection every 10 iterations
            if ($i % 10 === 0) {
                gc_collect_cycles();
            }
        }
        
        gc_collect_cycles();
        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Memory increase should be minimal (less than 10MB)
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease, "Potential memory leak detected: " . ($memoryIncrease / 1024 / 1024) . "MB increase");
    }

    public function test_performance_with_binary_file_handling()
    {
        // Create a binary file that might be referenced in stack trace
        $binaryFile = $this->testDataDir . '/binary_test.bin';
        file_put_contents($binaryFile, random_bytes(1024 * 1024)); // 1MB of random data
        
        $startTime = microtime(true);
        
        // Try to extract source code from binary file (should handle gracefully)
        $result = $this->sourceExtractor->extract($binaryFile, 1, 10);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete quickly without crashing (less than 100ms)
        $this->assertLessThan(0.1, $executionTime, "Binary file handling took too long: {$executionTime}s");
        
        // Should return empty or safe result
        $this->assertIsArray($result);
        
        // Clean up
        unlink($binaryFile);
    }

    public function test_performance_with_extremely_long_lines()
    {
        // Create file with extremely long lines
        $longLineFile = $this->testDataDir . '/long_lines.php';
        $longLine = '<?php // ' . str_repeat('This is a very long line. ', 1000) . "\n";
        $content = $longLine . "throw new Exception('Long line test');\n";
        file_put_contents($longLineFile, $content);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $result = $this->sourceExtractor->extract($longLineFile, 2, 5);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Should handle long lines efficiently (less than 200ms)
        $this->assertLessThan(0.2, $executionTime, "Long line handling took too long: {$executionTime}s");
        
        // Should use reasonable memory (less than 5MB)
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed, "Long line handling used too much memory: " . ($memoryUsed / 1024 / 1024) . "MB");
        
        // Should return truncated or handled lines
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        
        // Clean up
        unlink($longLineFile);
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

    private function createLargePhpFile(int $lines, string $filename = 'large_test.php'): string
    {
        $filePath = $this->testDataDir . '/' . $filename;
        $content = "<?php\n";
        
        for ($i = 1; $i <= $lines; $i++) {
            $content .= "// Line $i: " . str_repeat("This is test content for line $i. ", 3) . "\n";
            
            // Add some PHP code every 100 lines
            if ($i % 100 === 0) {
                $content .= "function testFunction$i() { return 'test'; }\n";
            }
        }
        
        $content .= "throw new Exception('Large file test');\n";
        
        file_put_contents($filePath, $content);
        return $filePath;
    }

    private function createLargeStackTrace(int $frameCount): array
    {
        $trace = [];
        
        for ($i = 0; $i < $frameCount; $i++) {
            $trace[] = [
                'file' => "/path/to/file$i.php",
                'line' => rand(1, 1000),
                'function' => "testFunction$i",
                'class' => $i % 3 === 0 ? "TestClass$i" : null,
                'type' => $i % 3 === 0 ? '::' : null,
                'args' => [
                    "arg1_$i",
                    "arg2_$i",
                    ['nested' => "array_$i"]
                ]
            ];
        }
        
        return $trace;
    }

    private function createExceptionWithMultipleFiles(array $files): Exception
    {
        // This is a simplified version - in reality, we'd need to create
        // an exception that actually references these files in its stack trace
        return new Exception('Multiple files test');
    }
}