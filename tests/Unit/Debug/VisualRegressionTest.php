<?php

namespace Tests\Unit\Debug;

use Tests\TestCase;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\ContextCollector;
use GuepardoSys\Core\Debug\StackTraceFormatter;
use Exception;
use RuntimeException;
use InvalidArgumentException;

/**
 * Visual regression tests for template rendering
 * Tests the HTML output structure and content consistency
 */
class VisualRegressionTest extends TestCase
{
    private AdvancedErrorRenderer $renderer;
    private string $testOutputDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->renderer = new AdvancedErrorRenderer();
        $this->testOutputDir = __DIR__ . '/../../storage/visual_test_output';
        
        if (!is_dir($this->testOutputDir)) {
            mkdir($this->testOutputDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test output files
        if (is_dir($this->testOutputDir)) {
            $files = glob($this->testOutputDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        parent::tearDown();
    }

    public function test_basic_exception_template_structure()
    {
        $exception = new Exception('Basic template test', 500);
        $output = $this->renderer->render($exception);
        
        // Save output for visual inspection
        file_put_contents($this->testOutputDir . '/basic_exception.html', $output);
        
        // Test HTML structure
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('<html', $output);
        $this->assertStringContainsString('<head>', $output);
        $this->assertStringContainsString('<body>', $output);
        $this->assertStringContainsString('</html>', $output);
        
        // Test required sections
        $this->assertStringContainsString('class="error-header"', $output);
        $this->assertStringContainsString('class="container"', $output);
        $this->assertStringContainsString('class="section"', $output);
        $this->assertStringContainsString('class="code-content"', $output);
        $this->assertStringContainsString('class="context-item"', $output);
        
        // Test navigation elements
        $this->assertStringContainsString('class="section-header"', $output);
        $this->assertStringContainsString('onclick="toggleSection', $output);
        
        // Test copy functionality
        $this->assertStringContainsString('📋 Copy', $output);
        $this->assertStringContainsString('onclick="copyCode', $output);
    }

    public function test_error_template_structure()
    {
        $output = $this->renderer->renderError('Fatal Error', 'Test fatal error', __FILE__, __LINE__);
        
        // Save output for visual inspection
        file_put_contents($this->testOutputDir . '/fatal_error.html', $output);
        
        // Test HTML structure
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('Fatal Error', $output);
        $this->assertStringContainsString('Test fatal error', $output);
        $this->assertStringContainsString(__FILE__, $output);
        
        // Test error-specific elements
        $this->assertStringContainsString('class="error-type"', $output);
        $this->assertStringContainsString('class="error-message"', $output);
        $this->assertStringContainsString('class="error-location"', $output);
    }

    public function test_template_css_consistency()
    {
        $exception = new RuntimeException('CSS consistency test');
        $output = $this->renderer->render($exception);
        
        // Test CSS is properly embedded
        $this->assertStringContainsString('<style>', $output);
        $this->assertStringContainsString('</style>', $output);
        
        // Test key CSS classes are defined
        $cssContent = $this->extractCssFromOutput($output);
        
        $requiredClasses = [
            '.error-header',
            '.container',
            '.section',
            '.section-header',
            '.section-content',
            '.code-block',
            '.code-content',
            '.stack-frame',
            '.context-item',
            '.btn',
            '.line-number'
        ];
        
        foreach ($requiredClasses as $class) {
            $this->assertStringContainsString($class, $cssContent, "CSS class $class not found");
        }
        
        // Test responsive design elements
        $this->assertStringContainsString('@media', $cssContent);
        $this->assertStringContainsString('max-width', $cssContent);
    }

    public function test_template_javascript_functionality()
    {
        $exception = new InvalidArgumentException('JavaScript test');
        $output = $this->renderer->render($exception);
        
        // Test JavaScript is properly embedded
        $this->assertStringContainsString('<script>', $output);
        $this->assertStringContainsString('</script>', $output);
        
        $jsContent = $this->extractJsFromOutput($output);
        
        // Test key JavaScript functions are present
        $requiredFunctions = [
            'switchTab',
            'copyToClipboard',
            'toggleSection',
            'highlightLine',
            'searchCode'
        ];
        
        foreach ($requiredFunctions as $function) {
            $this->assertStringContainsString($function, $jsContent, "JavaScript function $function not found");
        }
        
        // Test event listeners
        $this->assertStringContainsString('addEventListener', $jsContent);
        $this->assertStringContainsString('DOMContentLoaded', $jsContent);
    }

    public function test_template_accessibility_features()
    {
        $exception = new Exception('Accessibility test');
        $output = $this->renderer->render($exception);
        
        // Test ARIA attributes
        $this->assertStringContainsString('aria-', $output);
        $this->assertStringContainsString('role=', $output);
        
        // Test semantic HTML elements
        $this->assertStringContainsString('<main', $output);
        $this->assertStringContainsString('<section', $output);
        $this->assertStringContainsString('<nav', $output);
        
        // Test keyboard navigation support
        $this->assertStringContainsString('tabindex=', $output);
        $this->assertStringContainsString('aria-expanded=', $output);
        
        // Test alt text for any images/icons
        if (strpos($output, '<img') !== false) {
            $this->assertStringContainsString('alt=', $output);
        }
    }

    public function test_template_responsive_design()
    {
        $exception = new Exception('Responsive design test');
        $output = $this->renderer->render($exception);
        
        // Test viewport meta tag
        $this->assertStringContainsString('name="viewport"', $output);
        $this->assertStringContainsString('width=device-width', $output);
        
        // Test responsive CSS
        $cssContent = $this->extractCssFromOutput($output);
        
        // Test media queries for different screen sizes
        $this->assertStringContainsString('@media (max-width:', $cssContent);
        $this->assertStringContainsString('@media (min-width:', $cssContent);
        
        // Test flexible layout properties
        $this->assertStringContainsString('flex', $cssContent);
        $this->assertStringContainsString('grid', $cssContent);
        $this->assertStringContainsString('%', $cssContent); // Percentage widths
    }

    public function test_template_dark_light_theme_support()
    {
        $exception = new Exception('Theme test');
        $output = $this->renderer->render($exception);
        
        $cssContent = $this->extractCssFromOutput($output);
        
        // Test CSS custom properties for theming
        $this->assertStringContainsString('--', $cssContent); // CSS variables
        
        // Test prefers-color-scheme media query
        $this->assertStringContainsString('prefers-color-scheme', $cssContent);
        
        // Test theme-specific color definitions
        $this->assertStringContainsString('dark', $cssContent);
        $this->assertStringContainsString('light', $cssContent);
    }

    public function test_template_syntax_highlighting_consistency()
    {
        // Create an exception with PHP code context
        $testFile = $this->createTestPhpFile();
        
        try {
            include $testFile;
        } catch (Exception $e) {
            $output = $this->renderer->render($e);
            
            // Save output for visual inspection
            file_put_contents($this->testOutputDir . '/syntax_highlighting.html', $output);
            
            // Test syntax highlighting CSS classes
            $this->assertStringContainsString('class="php-keyword"', $output);
            $this->assertStringContainsString('class="php-string"', $output);
            $this->assertStringContainsString('class="php-comment"', $output);
            $this->assertStringContainsString('class="php-variable"', $output);
            
            // Test line numbers
            $this->assertStringContainsString('class="line-number"', $output);
            $this->assertStringContainsString('data-line=', $output);
        }
        
        // Clean up
        if (file_exists($testFile)) {
            unlink($testFile);
        }
    }

    public function test_template_collapsible_sections()
    {
        $exception = new Exception('Collapsible sections test');
        $output = $this->renderer->render($exception);
        
        // Test collapsible section attributes
        $this->assertStringContainsString('data-collapsible', $output);
        $this->assertStringContainsString('aria-expanded', $output);
        $this->assertStringContainsString('aria-controls', $output);
        
        // Test toggle buttons
        $this->assertStringContainsString('toggle-button', $output);
        $this->assertStringContainsString('collapse-icon', $output);
        
        // Test section states
        $this->assertStringContainsString('collapsed', $output);
        $this->assertStringContainsString('expanded', $output);
    }

    public function test_template_copy_functionality_ui()
    {
        $exception = new Exception('Copy functionality test');
        $output = $this->renderer->render($exception);
        
        // Test copy buttons
        $this->assertStringContainsString('copy-button', $output);
        $this->assertStringContainsString('data-copy-target', $output);
        
        // Test copy feedback elements
        $this->assertStringContainsString('copy-feedback', $output);
        $this->assertStringContainsString('copy-success', $output);
        
        // Test copyable content areas
        $this->assertStringContainsString('copyable-content', $output);
        $this->assertStringContainsString('data-copyable', $output);
    }

    public function test_template_performance_with_large_content()
    {
        // Create an exception with large stack trace
        $exception = $this->createLargeStackTraceException();
        
        $startTime = microtime(true);
        $output = $this->renderer->render($exception);
        $endTime = microtime(true);
        
        $renderTime = $endTime - $startTime;
        
        // Should render within reasonable time (less than 500ms)
        $this->assertLessThan(0.5, $renderTime);
        
        // Output should still contain all required elements
        $this->assertStringContainsString('class="error-page"', $output);
        $this->assertStringContainsString('class="stack-trace"', $output);
        
        // Test output size is reasonable (less than 1MB)
        $this->assertLessThan(1024 * 1024, strlen($output));
    }

    public function test_template_xss_prevention()
    {
        $maliciousMessage = '<script>alert("XSS")</script><img src="x" onerror="alert(1)">';
        $exception = new Exception($maliciousMessage);
        
        $output = $this->renderer->render($exception);
        
        // Should not contain unescaped script tags
        $this->assertStringNotContainsString('<script>alert("XSS")</script>', $output);
        
        // Should contain escaped versions (the system is working correctly by escaping)
        $this->assertStringContainsString('&lt;script&gt;', $output);
        $this->assertStringContainsString('&quot;', $output);
        
        // The malicious content should be safely escaped
        $this->assertStringContainsString('alert(&quot;XSS&quot;)', $output);
    }

    private function extractCssFromOutput(string $output): string
    {
        preg_match('/<style[^>]*>(.*?)<\/style>/s', $output, $matches);
        return $matches[1] ?? '';
    }

    private function extractJsFromOutput(string $output): string
    {
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        return $matches[1] ?? '';
    }

    private function createTestPhpFile(): string
    {
        $testFile = $this->testOutputDir . '/test_syntax.php';
        $content = '<?php
// This is a test comment
$variable = "test string";
function testFunction($param) {
    if ($param === "test") {
        throw new Exception("Test exception for syntax highlighting");
    }
    return $param;
}

testFunction("test");
';
        file_put_contents($testFile, $content);
        return $testFile;
    }

    private function createLargeStackTraceException(): Exception
    {
        $exception = new Exception('Large stack trace test');
        
        // Create a deep call stack to simulate large stack trace
        for ($i = 0; $i < 100; $i++) {
            try {
                if ($i === 99) {
                    throw $exception;
                }
            } catch (Exception $e) {
                $exception = new RuntimeException("Level $i", 0, $e);
            }
        }
        
        return $exception;
    }
}