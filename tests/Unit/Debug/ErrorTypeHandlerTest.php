<?php

use PHPUnit\Framework\TestCase;
use GuepardoSys\Core\Debug\ErrorTypeHandler;
use PDOException;

class ErrorTypeHandlerTest extends TestCase
{
    private ErrorTypeHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ErrorTypeHandler();
    }

    public function testDatabaseErrorDetection()
    {
        // Test with PDOException
        $exception = new PDOException('SQLSTATE[42S02]: Base table or view not found: 1146 Table \'test.users\' doesn\'t exist');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertEquals('database', $result['error_type']);
        $this->assertArrayHasKey('database_info', $result);
        $this->assertArrayHasKey('sqlstate', $result['database_info']);
        $this->assertEquals('42S02', $result['database_info']['sqlstate']);
        $this->assertArrayHasKey('error_description', $result['database_info']);
        $this->assertEquals('Base table or view not found', $result['database_info']['error_description']);
    }

    public function testDatabaseErrorWithSQLQuery()
    {
        $exception = new PDOException('SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax (SQL: SELECT * FROM users WHERE id = ?)');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertEquals('database', $result['error_type']);
        $this->assertArrayHasKey('sql_query', $result['database_info']);
        $this->assertEquals('SELECT * FROM users WHERE id = ?', $result['database_info']['sql_query']);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertNotEmpty($result['suggestions']);
    }

    public function test404ErrorDetection()
    {
        $errorData = [
            'error' => [
                'type' => 'NotFoundException',
                'message' => 'Route not found: GET /nonexistent-page',
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData);

        $this->assertEquals('404', $result['error_type']);
        $this->assertArrayHasKey('routes_info', $result);
        $this->assertArrayHasKey('available_routes', $result['routes_info']);
        $this->assertArrayHasKey('similar_routes', $result['routes_info']);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertContains('Check if the URL is spelled correctly', $result['suggestions']);
    }

    public function testValidationErrorDetection()
    {
        $errorData = [
            'error' => [
                'type' => 'ValidationException',
                'message' => 'Field \'email\' is required. Field \'password\' must be at least 8 characters.',
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData);

        $this->assertEquals('validation', $result['error_type']);
        $this->assertArrayHasKey('validation_info', $result);
        $this->assertArrayHasKey('failed_fields', $result['validation_info']);
        
        $failedFields = $result['validation_info']['failed_fields'];
        $this->assertCount(2, $failedFields);
        $this->assertEquals('email', $failedFields[0]['field']);
        $this->assertEquals('is required', $failedFields[0]['error']);
        $this->assertEquals('password', $failedFields[1]['field']);
        $this->assertEquals('must be at least 8 characters', $failedFields[1]['error']);
    }

    public function testSyntaxErrorDetection()
    {
        $errorData = [
            'error' => [
                'type' => 'ParseError',
                'message' => 'syntax error, unexpected \'}\' in /path/to/file.php on line 25',
                'file' => '/path/to/file.php',
                'line' => 25
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData);

        $this->assertEquals('syntax', $result['error_type']);
        $this->assertArrayHasKey('syntax_info', $result);
        $this->assertArrayHasKey('description', $result['syntax_info']);
        $this->assertEquals('unexpected \'}\'', $result['syntax_info']['description']);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertContains('Check for missing semicolons, brackets, or quotes', $result['suggestions']);
    }

    public function testGeneralErrorType()
    {
        $errorData = [
            'error' => [
                'type' => 'RuntimeException',
                'message' => 'Something went wrong',
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData);

        // Should not have error_type set for general errors
        $this->assertArrayNotHasKey('error_type', $result);
    }

    public function testDatabaseConnectionInfo()
    {
        // Create a mock database config file for testing
        $configDir = __DIR__ . '/../../../config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }
        
        $configFile = $configDir . '/database.php';
        $originalConfig = null;
        if (file_exists($configFile)) {
            $originalConfig = file_get_contents($configFile);
        }
        
        // Write test config
        file_put_contents($configFile, '<?php return [
            "default" => "mysql",
            "connections" => [
                "mysql" => [
                    "driver" => "mysql",
                    "host" => "localhost",
                    "database" => "test_db"
                ]
            ]
        ];');

        $exception = new PDOException('SQLSTATE[08006]: Connection failure');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertArrayHasKey('connection', $result['database_info']);
        $this->assertEquals('mysql', $result['database_info']['connection']['driver']);
        $this->assertEquals('localhost', $result['database_info']['connection']['host']);
        $this->assertEquals('test_db', $result['database_info']['connection']['database']);

        // Restore original config
        if ($originalConfig !== null) {
            file_put_contents($configFile, $originalConfig);
        } else {
            unlink($configFile);
        }
    }

    public function testSimilarRoutesDetection()
    {
        $errorData = [
            'error' => [
                'type' => 'NotFoundException',
                'message' => 'Route not found: GET /user',
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData);

        $this->assertEquals('404', $result['error_type']);
        $this->assertArrayHasKey('similar_routes', $result['routes_info']);
        
        // Should find similar routes like /users
        $similarRoutes = $result['routes_info']['similar_routes'];
        $this->assertNotEmpty($similarRoutes);
        
        // Check if /users route is in similar routes
        $foundSimilar = false;
        foreach ($similarRoutes as $route) {
            if ($route['path'] === '/users') {
                $foundSimilar = true;
                break;
            }
        }
        $this->assertTrue($foundSimilar, 'Should find /users as similar to /user');
    }

    public function testDatabaseErrorSuggestions()
    {
        $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertArrayHasKey('suggestions', $result);
        $suggestions = $result['suggestions'];
        $this->assertContains('Check for duplicate values in unique columns', $suggestions);
        $this->assertContains('Verify foreign key constraints', $suggestions);
    }

    public function testTableNotFoundSuggestions()
    {
        $exception = new PDOException('SQLSTATE[42S02]: Base table or view not found');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertArrayHasKey('suggestions', $result);
        $suggestions = $result['suggestions'];
        $this->assertContains('Verify that the table exists in the database', $suggestions);
        $this->assertContains('Check if migrations have been run', $suggestions);
    }

    public function testColumnNotFoundSuggestions()
    {
        $exception = new PDOException('SQLSTATE[42S22]: Column not found');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertArrayHasKey('suggestions', $result);
        $suggestions = $result['suggestions'];
        $this->assertContains('Check if the column name is spelled correctly', $suggestions);
        $this->assertContains('Verify that the column exists in the table', $suggestions);
    }

    public function testConnectionFailureSuggestions()
    {
        $exception = new PDOException('SQLSTATE[08006]: Connection failure');
        $errorData = [
            'error' => [
                'type' => 'PDOException',
                'message' => $exception->getMessage(),
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData, $exception);

        $this->assertArrayHasKey('suggestions', $result);
        $suggestions = $result['suggestions'];
        $this->assertContains('Check database connection settings', $suggestions);
        $this->assertContains('Verify database credentials', $suggestions);
    }

    public function testAvailableRoutesFormatting()
    {
        $errorData = [
            'error' => [
                'type' => 'NotFoundException',
                'message' => 'Route not found: GET /nonexistent',
                'file' => '/path/to/file.php',
                'line' => 42
            ]
        ];

        $result = $this->handler->enhanceErrorData($errorData);

        $this->assertArrayHasKey('available_routes', $result['routes_info']);
        $routes = $result['routes_info']['available_routes'];
        
        $this->assertNotEmpty($routes);
        
        // Check that routes have proper structure
        foreach ($routes as $route) {
            $this->assertArrayHasKey('method', $route);
            $this->assertArrayHasKey('path', $route);
            $this->assertArrayHasKey('handler', $route);
            $this->assertNotEmpty($route['method']);
            $this->assertNotEmpty($route['path']);
            $this->assertNotEmpty($route['handler']);
        }
    }

    public function testSQLStateDescriptions()
    {
        $testCases = [
            '23000' => 'Integrity constraint violation',
            '42000' => 'Syntax error or access violation',
            '42S02' => 'Base table or view not found',
            '42S22' => 'Column not found',
            '08006' => 'Connection failure',
            '28000' => 'Invalid authorization specification',
            '22001' => 'String data, right truncated',
            '23505' => 'Unique violation',
            '99999' => 'Unknown database error' // Test unknown code
        ];

        foreach ($testCases as $sqlstate => $expectedDescription) {
            $exception = new PDOException("SQLSTATE[{$sqlstate}]: Test error");
            $errorData = [
                'error' => [
                    'type' => 'PDOException',
                    'message' => $exception->getMessage(),
                    'file' => '/path/to/file.php',
                    'line' => 42
                ]
            ];

            $result = $this->handler->enhanceErrorData($errorData, $exception);
            
            $this->assertEquals($expectedDescription, $result['database_info']['error_description']);
        }
    }
}