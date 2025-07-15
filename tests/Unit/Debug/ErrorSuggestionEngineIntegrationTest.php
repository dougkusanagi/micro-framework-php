<?php

use PHPUnit\Framework\TestCase;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\ErrorSuggestionEngine;

class ErrorSuggestionEngineIntegrationTest extends TestCase
{
    private AdvancedErrorRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new AdvancedErrorRenderer();
    }

    public function testAdvancedErrorRendererIncludesSuggestions()
    {
        // Create a test exception
        $exception = new Exception('Test error message');
        
        // Render the error
        $html = $this->renderer->render($exception);
        
        // Check that the HTML contains suggestions
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
        
        // The HTML should contain some general suggestions since this is a basic Exception
        $this->assertStringContainsString('Check application logs', $html);
    }

    public function testSyntaxErrorSuggestionsInRenderer()
    {
        // Create a ParseError to simulate syntax error
        $parseError = new ParseError('syntax error, unexpected \'}\'');
        
        // Render the error
        $html = $this->renderer->render($parseError);
        
        // Check that syntax-specific suggestions are included
        $this->assertStringContainsString('Check for missing opening brace', $html);
        $this->assertStringContainsString('Verify that all control structures', $html);
    }

    public function testDatabaseErrorSuggestionsInRenderer()
    {
        // Create a PDOException to simulate database error
        $dbError = new PDOException('SQLSTATE[23000]: Integrity constraint violation');
        
        // Render the error
        $html = $this->renderer->render($dbError);
        
        // Check that database-specific suggestions are included
        $this->assertStringContainsString('Check for duplicate values', $html);
        $this->assertStringContainsString('Verify foreign key constraints', $html);
    }

    public function testCustomSuggestionEngineCanBeSet()
    {
        // Create a mock suggestion engine
        $mockEngine = $this->createMock(ErrorSuggestionEngine::class);
        $mockEngine->expects($this->once())
                   ->method('generateSuggestions')
                   ->willReturn(['Custom suggestion 1', 'Custom suggestion 2']);
        
        // Set the custom engine
        $this->renderer->setSuggestionEngine($mockEngine);
        
        // Create a test exception
        $exception = new Exception('Test error');
        
        // Render the error
        $html = $this->renderer->render($exception);
        
        // Check that custom suggestions are included
        $this->assertStringContainsString('Custom suggestion 1', $html);
        $this->assertStringContainsString('Custom suggestion 2', $html);
    }

    public function testRenderErrorMethodIncludesSuggestions()
    {
        // Use the renderError method for PHP errors
        $html = $this->renderer->renderError(
            'Error',
            'Undefined variable: $username',
            '/path/to/file.php',
            10
        );
        
        // Check that variable-specific suggestions are included
        $this->assertStringContainsString('Check if the variable is properly declared', $html);
        $this->assertStringContainsString('Verify variable scope', $html);
    }

    public function testMemoryLimitErrorSuggestions()
    {
        // Create an error with memory limit message
        $memoryError = new Error('Fatal error: Allowed memory size of 134217728 bytes exhausted');
        
        // Render the error
        $html = $this->renderer->render($memoryError);
        
        // Check that memory-specific suggestions are included
        $this->assertStringContainsString('Increase PHP memory_limit', $html);
        $this->assertStringContainsString('Optimize code to use less memory', $html);
        $this->assertStringContainsString('Process data in smaller chunks', $html);
    }

    public function testSuggestionsAreLimitedInOutput()
    {
        // Create an error that would generate many suggestions
        $complexError = new Error('Multiple patterns: memory limit, execution time, undefined variable, class not found');
        
        // Render the error
        $html = $this->renderer->render($complexError);
        
        // Count suggestion items in HTML (look for list items or similar patterns)
        $suggestionCount = substr_count($html, '<li class="suggestion-item">');
        
        // Should be limited to reasonable number (8 or less as per ErrorSuggestionEngine)
        $this->assertLessThanOrEqual(8, $suggestionCount);
    }

    public function testNoSuggestionsWhenEngineIsNull()
    {
        // Set suggestion engine to null
        $this->renderer->setSuggestionEngine(null);
        
        // Create a test exception
        $exception = new Exception('Test error');
        
        // Render the error
        $html = $this->renderer->render($exception);
        
        // Should still render without errors
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
        
        // The test passes if no exception is thrown during rendering
        $this->assertTrue(true);
    }

    public function testSuggestionsWithEmptyErrorData()
    {
        // Test with minimal error data
        $html = $this->renderer->renderError('', '', '', 0);
        
        // Should still render without errors
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }
}