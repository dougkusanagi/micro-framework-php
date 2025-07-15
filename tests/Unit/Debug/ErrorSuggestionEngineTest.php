<?php

use PHPUnit\Framework\TestCase;
use GuepardoSys\Core\Debug\ErrorSuggestionEngine;

class ErrorSuggestionEngineTest extends TestCase
{
    private ErrorSuggestionEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new ErrorSuggestionEngine();
    }

    public function testGenerateSuggestionsForSyntaxError()
    {
        $errorData = [
            'error' => [
                'type' => 'ParseError',
                'message' => 'syntax error, unexpected \'}\' in /path/to/file.php on line 10'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('Check for missing opening brace { before this line', $suggestions);
        $this->assertContains('Verify that all control structures have proper opening braces', $suggestions);
    }

    public function testGenerateSuggestionsForDatabaseError()
    {
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry'
            ]
        ];

        $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation');

        $suggestions = $this->engine->generateSuggestions($errorData, $exception);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('Check for duplicate values in unique or primary key columns', $suggestions);
        $this->assertContains('Verify foreign key constraints are not violated', $suggestions);
    }

    public function testGenerateSuggestionsFor404Error()
    {
        $errorData = [
            'error' => [
                'message' => 'Route not found: /api/users/123'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('Check if the URL is spelled correctly', $suggestions);
        $this->assertContains('Verify that the route is defined in routes/web.php', $suggestions);
    }

    public function testGenerateSuggestionsForValidationError()
    {
        $errorData = [
            'error' => [
                'type' => 'ValidationException',
                'message' => 'The email field is required and must be a valid email address'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('Ensure all required fields are provided in the request', $suggestions);
        $this->assertContains('Verify email format is valid (user@domain.com)', $suggestions);
    }

    public function testGenerateSuggestionsForCustomException()
    {
        $errorData = [
            'error' => [
                'type' => 'AuthenticationException',
                'message' => 'User not authenticated'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('Check user authentication status', $suggestions);
        $this->assertContains('Verify login credentials and session', $suggestions);
    }

    public function testGenerateSuggestionsForGeneralError()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Call to undefined method User::getName()'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('Check if the method exists in the class', $suggestions);
        $this->assertContains('Verify method name spelling and case', $suggestions);
    }

    public function testSyntaxErrorWithMissingSemicolon()
    {
        $errorData = [
            'error' => [
                'type' => 'ParseError',
                'message' => 'syntax error, expecting \';\' in /path/to/file.php on line 15'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Add missing semicolon ; at the end of the statement', $suggestions);
        $this->assertContains('Check if the previous line needs a semicolon', $suggestions);
    }

    public function testDatabaseConnectionError()
    {
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => 'SQLSTATE[08006]: Connection failure: could not connect to server'
            ]
        ];

        $exception = new PDOException('SQLSTATE[08006]: Connection failure');

        $suggestions = $this->engine->generateSuggestions($errorData, $exception);

        $this->assertContains('Check database connection settings in config/database.php', $suggestions);
        $this->assertContains('Verify database credentials (username, password)', $suggestions);
        $this->assertContains('Ensure database server is running and accessible', $suggestions);
    }

    public function testTableNotFoundError()
    {
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => 'SQLSTATE[42S02]: Base table or view not found: 1146 Table \'mydb.users\' doesn\'t exist'
            ]
        ];

        $exception = new PDOException('SQLSTATE[42S02]: Base table or view not found');

        $suggestions = $this->engine->generateSuggestions($errorData, $exception);

        $this->assertContains('Verify that the table exists in the database', $suggestions);
        $this->assertContains('Run database migrations to create missing tables', $suggestions);
        $this->assertContains('Check table name spelling and case sensitivity', $suggestions);
    }

    public function testValidationErrorWithNumericField()
    {
        $errorData = [
            'error' => [
                'type' => 'ValidationException',
                'message' => 'The age field must be numeric and greater than 0'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Ensure numeric fields contain only numbers', $suggestions);
        $this->assertContains('Check data type conversion and validation', $suggestions);
    }

    public function testAuthorizationException()
    {
        $errorData = [
            'error' => [
                'type' => 'AuthorizationException',
                'message' => 'User does not have permission to access this resource'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Check user permissions and roles', $suggestions);
        $this->assertContains('Verify authorization policies', $suggestions);
        $this->assertContains('Ensure user has required access level', $suggestions);
    }

    public function testUndefinedVariableError()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Undefined variable: $username'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Check if the variable is properly declared and initialized', $suggestions);
        $this->assertContains('Verify variable scope and availability', $suggestions);
    }

    public function testClassNotFoundError()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Class \'App\\Models\\User\' not found'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Check if the class file is properly included', $suggestions);
        $this->assertContains('Verify namespace and use statements', $suggestions);
        $this->assertContains('Ensure autoloading is working correctly', $suggestions);
    }

    public function testMemoryLimitError()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Fatal error: Allowed memory size of 134217728 bytes exhausted'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Increase PHP memory_limit in php.ini', $suggestions);
        $this->assertContains('Optimize code to use less memory', $suggestions);
        $this->assertContains('Process data in smaller chunks', $suggestions);
    }

    public function testMaximumExecutionTimeError()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Fatal error: Maximum execution time of 30 seconds exceeded'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertContains('Increase max_execution_time in php.ini', $suggestions);
        $this->assertContains('Optimize slow database queries', $suggestions);
        $this->assertContains('Use background job processing for long tasks', $suggestions);
    }

    public function testSuggestionsAreLimited()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Multiple error patterns: memory limit exceeded, maximum execution time, file not found, connection refused'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertLessThanOrEqual(8, count($suggestions), 'Suggestions should be limited to 8 items');
    }

    public function testSuggestionsAreUnique()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Duplicate suggestion test'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        $this->assertEquals(count($suggestions), count(array_unique($suggestions)), 'All suggestions should be unique');
    }

    public function testEmptyErrorDataHandling()
    {
        $errorData = [];

        $suggestions = $this->engine->generateSuggestions($errorData);

        $this->assertIsArray($suggestions);
        // Should still provide some general suggestions
        $this->assertNotEmpty($suggestions);
    }

    public function testNullExceptionHandling()
    {
        $errorData = [
            'error' => [
                'type' => 'Error',
                'message' => 'Some error message'
            ]
        ];

        $suggestions = $this->engine->generateSuggestions($errorData, null);

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
    }
}