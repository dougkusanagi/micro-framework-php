<?php

use PHPUnit\Framework\TestCase;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use PDOException;

class ErrorTypeIntegrationTest extends TestCase
{
    private AdvancedErrorRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new AdvancedErrorRenderer();
    }

    public function testDatabaseErrorRendering()
    {
        $exception = new PDOException('SQLSTATE[42S02]: Base table or view not found: 1146 Table \'test.users\' doesn\'t exist (SQL: SELECT * FROM users WHERE id = ?)');
        
        $html = $this->renderer->render($exception);
        
        $this->assertStringContainsString('Database Error Details', $html);
        $this->assertStringContainsString('SQL Query:', $html);
        $this->assertStringContainsString('SELECT * FROM users WHERE id = ?', $html);
        $this->assertStringContainsString('SQLSTATE:', $html);
        $this->assertStringContainsString('42S02', $html);
        $this->assertStringContainsString('Base table or view not found', $html);
    }

    public function test404ErrorRendering()
    {
        // Create a mock exception that looks like a 404 error
        $exception = new Exception('Route not found: GET /nonexistent-page');
        
        $html = $this->renderer->render($exception);
        
        $this->assertStringContainsString('Available Routes', $html);
        $this->assertStringContainsString('GET', $html);
        $this->assertStringContainsString('POST', $html);
        $this->assertStringContainsString('HomeController', $html);
    }

    public function testValidationErrorRendering()
    {
        $exception = new Exception('Field \'email\' is required. Field \'password\' must be at least 8 characters.');
        
        $html = $this->renderer->render($exception);
        
        $this->assertStringContainsString('Validation Error Details', $html);
        $this->assertStringContainsString('Failed Fields:', $html);
        $this->assertStringContainsString('email', $html);
        $this->assertStringContainsString('password', $html);
    }

    public function testSyntaxErrorRendering()
    {
        $exception = new ParseError('syntax error, unexpected \'}\' in /path/to/file.php on line 25');
        
        $html = $this->renderer->render($exception);
        
        $this->assertStringContainsString('Syntax Error Details', $html);
        $this->assertStringContainsString('Error Description:', $html);
        $this->assertStringContainsString('unexpected \'}\'', $html);
    }

    public function testGeneralErrorDoesNotShowSpecificSections()
    {
        $exception = new RuntimeException('Something went wrong');
        
        $html = $this->renderer->render($exception);
        
        $this->assertStringNotContainsString('Database Error Details', $html);
        $this->assertStringNotContainsString('Available Routes', $html);
        $this->assertStringNotContainsString('Validation Error Details', $html);
        $this->assertStringNotContainsString('Syntax Error Details', $html);
    }

    public function testSuggestionsAreIncluded()
    {
        $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation');
        
        $html = $this->renderer->render($exception);
        
        $this->assertStringContainsString('Suggestions', $html);
        $this->assertStringContainsString('Check for duplicate values', $html);
    }
}