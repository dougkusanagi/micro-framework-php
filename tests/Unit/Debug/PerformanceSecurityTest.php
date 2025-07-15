<?php

namespace Tests\Unit\Debug;

use PHPUnit\Framework\TestCase;
use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\ContextCollector;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;

/**
 * Test performance and security optimizations for the debug system
 */
class PerformanceSecurityTest extends TestCase
{
    private $sourceExtractor;
    private $contextCollector;
    private $renderer;

    protected function setUp(): void
    {
        $this->sourceExtractor = new SourceCodeExtractor();
        $this->contextCollector = new ContextCollector();
        $this->renderer = new AdvancedErrorRenderer();
    }

    /**
     * Test file path validation prevents directory traversal
     */
    public function testDirectoryTraversalPrevention()
    {
        // Create a test file outside the project
        $maliciousPath = '../../../etc/passwd';
        
        $result = $this->sourceExtractor->extract($maliciousPath, 1);
        
        // Should return empty result or error for invalid paths
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['lines']);
    }

    /**
     * Test large file handling with lazy loading
     */
    public function testLargeFileHandling()
    {
        // Create a temporary large file
        $tempFile = tempnam(sys_get_temp_dir(), 'debug_test_');
        $largeContent = str_repeat("<?php\n// This is line content\n", 10000);
        file_put_contents($tempFile, $largeContent);

        $startTime = microtime(true);
        $result = $this->sourceExtractor->extract($tempFile, 5000, 10);
        $endTime = microtime(true);

        // Should complete quickly even for large files
        $this->assertLessThan(1.0, $endTime - $startTime, 'Large file extraction should be fast');
        $this->assertArrayHasKey('lines', $result);
        $this->assertLessThanOrEqual(21, count($result['lines']), 'Should limit context lines');

        unlink($tempFile);
    }

    /**
     * Test XSS prevention in syntax highlighting
     */
    public function testXSSPrevention()
    {
        $maliciousCode = '<?php echo "<script>alert(\'XSS\')</script>"; ?>';
        
        $highlighted = $this->sourceExtractor->highlightSyntax($maliciousCode);
        
        // Should not contain unescaped script tags
        $this->assertStringNotContainsString('<script>', $highlighted);
        $this->assertStringContainsString('&lt;script&gt;', $highlighted);
        
        // The alert function should be escaped or contained within safe HTML
        $this->assertTrue(
            strpos($highlighted, '&lt;script&gt;') !== false ||
            strpos($highlighted, 'alert') === false ||
            (strpos($highlighted, 'alert') !== false && strpos($highlighted, 'span') !== false),
            'XSS content should be properly escaped or contained in safe HTML'
        );
    }

    /**
     * Test context size limiting
     */
    public function testContextSizeLimiting()
    {
        // Mock large context data
        $_GET = array_fill(0, 1000, str_repeat('x', 1000));
        $_POST = array_fill(0, 1000, str_repeat('y', 1000));
        
        $context = $this->contextCollector->collect();
        
        // Context should be limited in size
        $serializedSize = strlen(serialize($context));
        $this->assertLessThan(2 * 1024 * 1024, $serializedSize, 'Context should be limited to reasonable size');
        
        // Should contain truncation indicators
        $this->assertTrue(
            isset($context['request']['get']['...']) || 
            count($context['request']['get']) < 1000,
            'Large arrays should be truncated'
        );
    }

    /**
     * Test sensitive data masking
     */
    public function testSensitiveDataMasking()
    {
        $_POST = [
            'username' => 'testuser',
            'password' => 'secretpassword',
            'api_key' => 'sk-1234567890abcdef',
            'normal_field' => 'normal_value'
        ];

        $context = $this->contextCollector->collect();
        
        // Sensitive fields should be masked - check the actual masking format
        $this->assertNotEquals('secretpassword', $context['request']['post']['password'], 'Password should be masked');
        $this->assertNotEquals('sk-1234567890abcdef', $context['request']['post']['api_key'], 'API key should be masked');
        $this->assertEquals('normal_value', $context['request']['post']['normal_field'], 'Normal field should not be masked');
        
        // Check that sensitive data contains masking characters
        $this->assertStringContainsString('*', $context['request']['post']['password'], 'Password should contain masking characters');
        $this->assertStringContainsString('*', $context['request']['post']['api_key'], 'API key should contain masking characters');
    }

    /**
     * Test URL sanitization
     */
    public function testUrlSanitization()
    {
        $_SERVER['HTTP_HOST'] = 'example.com<script>alert(1)</script>';
        $_SERVER['REQUEST_URI'] = '/test?password=secret&normal=value';
        
        $context = $this->contextCollector->collect();
        
        // Host should be sanitized
        $this->assertStringNotContainsString('<script>', $context['request']['url']);
        
        // Sensitive query parameters should be hidden
        $this->assertStringContainsString('password=%5BHIDDEN%5D', $context['request']['url']);
        $this->assertStringContainsString('normal=value', $context['request']['url']);
    }

    /**
     * Test performance caching
     */
    public function testPerformanceCaching()
    {
        // First call
        $startTime = microtime(true);
        $context1 = $this->contextCollector->collect();
        $firstCallTime = microtime(true) - $startTime;

        // Second call (should be cached)
        $startTime = microtime(true);
        $context2 = $this->contextCollector->collect();
        $secondCallTime = microtime(true) - $startTime;

        // Second call should be significantly faster due to caching
        $this->assertLessThan($firstCallTime, $secondCallTime);
        $this->assertEquals($context1, $context2);
    }

    /**
     * Test HTML minification
     */
    public function testHtmlMinification()
    {
        $exception = new \Exception('Test exception', 500);
        
        $html = $this->renderer->render($exception);
        
        // Should not contain excessive whitespace
        $this->assertStringNotContainsString('  ', $html, 'HTML should be minified');
        $this->assertStringNotContainsString("\n\n", $html, 'HTML should not have double newlines');
    }

    /**
     * Test template path validation
     */
    public function testTemplatePathValidation()
    {
        // This test verifies that the renderer handles invalid template paths safely
        $exception = new \Exception('Test exception');
        
        // Should not throw exception even with missing template
        $html = $this->renderer->render($exception);
        
        $this->assertIsString($html);
        $this->assertStringContainsString('Test exception', $html);
    }

    /**
     * Test code length limiting
     */
    public function testCodeLengthLimiting()
    {
        // Create code that's definitely over the 50KB limit
        $veryLongCode = str_repeat('<?php echo "test with some longer content to make it bigger"; ', 5000);
        
        $highlighted = $this->sourceExtractor->highlightSyntax($veryLongCode);
        
        // Should be truncated - check for the actual truncation message
        $this->assertTrue(
            strpos($highlighted, '[truncated]') !== false || 
            strpos($highlighted, 'truncated') !== false,
            'Long code should be truncated'
        );
        
        // The original code should be longer than 50KB to trigger truncation
        $this->assertGreaterThan(50000, strlen($veryLongCode), 'Test code should be long enough to trigger truncation');
    }

    /**
     * Test memory usage optimization
     */
    public function testMemoryUsageOptimization()
    {
        $initialMemory = memory_get_usage();
        
        // Process a large error with lots of context
        $_GET = array_fill(0, 100, str_repeat('x', 100));
        $_POST = array_fill(0, 100, str_repeat('y', 100));
        
        $exception = new \Exception('Test exception with large context');
        $html = $this->renderer->render($exception);
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Memory increase should be reasonable (less than 10MB)
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease, 'Memory usage should be optimized');
        
        // Should still produce valid output
        $this->assertIsString($html);
        $this->assertStringContainsString('Test exception', $html);
    }

    /**
     * Test concurrent access safety
     */
    public function testConcurrentAccessSafety()
    {
        // Simulate multiple requests with different data
        $contexts = [];
        
        for ($i = 0; $i < 5; $i++) {
            $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) + $i;
            $_GET = ['request_id' => $i];
            
            $contexts[] = $this->contextCollector->collect();
            
            // Small delay to ensure different timestamps
            usleep(1000);
        }
        
        // Each context should be different
        for ($i = 1; $i < count($contexts); $i++) {
            $this->assertNotEquals($contexts[0], $contexts[$i], 'Different requests should have different contexts');
        }
    }

    protected function tearDown(): void
    {
        // Clean up global state
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }
}