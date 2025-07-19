<?php

namespace GuepardoSys\Core\Debug;

use Throwable;
use PDOException;

/**
 * Handles different types of errors and provides specific information
 * for each error type to enhance debugging experience
 */
class ErrorTypeHandler
{
    /**
     * @var array Available routes for 404 error handling
     */
    private $routes = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadRoutes();
    }

    /**
     * Detect the error type and enhance error data accordingly
     *
     * @param array $errorData The basic error data
     * @param Throwable|null $exception The original exception if available
     * @return array Enhanced error data with type-specific information
     */
    public function enhanceErrorData(array $errorData, ?Throwable $exception = null): array
    {
        $errorType = $this->detectErrorType($errorData, $exception);
        
        switch ($errorType) {
            case 'database':
                return $this->handleDatabaseError($errorData, $exception);
            case '404':
                return $this->handle404Error($errorData);
            case 'validation':
                return $this->handleValidationError($errorData, $exception);
            case 'syntax':
                return $this->handleSyntaxError($errorData);
            default:
                return $errorData;
        }
    }

    /**
     * Detect the type of error based on error data and exception
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception if available
     * @return string The detected error type
     */
    private function detectErrorType(array $errorData, ?Throwable $exception = null): string
    {
        // Check for database errors
        if ($exception instanceof PDOException || 
            (isset($errorData['error']['type']) && strpos($errorData['error']['type'], 'PDO') !== false)) {
            return 'database';
        }

        // Check for 404 errors
        if (isset($errorData['error']['message']) && 
            (strpos($errorData['error']['message'], '404') !== false ||
             strpos($errorData['error']['message'], 'Not Found') !== false ||
             strpos($errorData['error']['message'], 'Route not found') !== false)) {
            return '404';
        }

        // Check for validation errors
        if (isset($errorData['error']['type']) && 
            (strpos($errorData['error']['type'], 'Validation') !== false ||
             strpos($errorData['error']['message'], 'validation') !== false)) {
            return 'validation';
        }

        // Check for syntax errors
        if (isset($errorData['error']['type']) && 
            (strpos($errorData['error']['type'], 'ParseError') !== false ||
             strpos($errorData['error']['type'], 'SyntaxError') !== false ||
             strpos($errorData['error']['message'], 'syntax error') !== false)) {
            return 'syntax';
        }

        return 'general';
    }

    /**
     * Handle database errors by extracting SQL query information
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The database exception
     * @return array Enhanced error data with database-specific information
     */
    private function handleDatabaseError(array $errorData, ?Throwable $exception = null): array
    {
        $errorData['error_type'] = 'database';
        $errorData['database_info'] = [];

        if ($exception instanceof PDOException) {
            // Extract SQL query from error message if available
            $message = $exception->getMessage();
            
            // Try to extract SQL query from common PDO error patterns
            if (preg_match('/\(SQL: (.+?)\)/', $message, $matches)) {
                $errorData['database_info']['sql_query'] = $matches[1];
            }
            
            // Extract the main error description
            if (preg_match('/SQLSTATE\[[^\]]+\]: (.+?)(?:\s+\(SQL:|$)/', $message, $matches)) {
                $errorData['database_info']['sql_error'] = $matches[1];
            }

            // Extract SQLSTATE code
            if (preg_match('/SQLSTATE\[([^\]]+)\]/', $message, $matches)) {
                $errorData['database_info']['sqlstate'] = $matches[1];
                $errorData['database_info']['error_description'] = $this->getSQLStateDescription($matches[1]);
            }

            // Get connection info (without sensitive data)
            $errorData['database_info']['connection'] = [
                'driver' => $this->getDatabaseDriver(),
                'host' => $this->getDatabaseHost(),
                'database' => $this->getDatabaseName()
            ];
        }

        // Add suggestions for common database errors
        $errorData['suggestions'] = $this->getDatabaseErrorSuggestions($errorData);

        return $errorData;
    }

    /**
     * Handle 404 errors by showing available routes
     *
     * @param array $errorData The error data
     * @return array Enhanced error data with available routes
     */
    private function handle404Error(array $errorData): array
    {
        $errorData['error_type'] = '404';
        $errorData['routes_info'] = [
            'available_routes' => $this->getFormattedRoutes(),
            'similar_routes' => $this->findSimilarRoutes($errorData['error']['message'] ?? '')
        ];

        // Add suggestions for 404 errors
        $errorData['suggestions'] = [
            'Check if the URL is spelled correctly',
            'Verify that the route is defined in routes/web.php',
            'Make sure the HTTP method matches the route definition',
            'Check if the route requires authentication'
        ];

        return $errorData;
    }

    /**
     * Handle validation errors by highlighting problematic fields
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The validation exception
     * @return array Enhanced error data with validation-specific information
     */
    private function handleValidationError(array $errorData, ?Throwable $exception = null): array
    {
        $errorData['error_type'] = 'validation';
        $errorData['validation_info'] = [];

        // Try to extract validation details from the exception or error message
        $message = $errorData['error']['message'];
        
        // Parse validation errors if they follow a specific format
        if (preg_match_all('/Field \'([^\']+)\' (.+?)(?:\.|$)/', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $errorData['validation_info']['failed_fields'][] = [
                    'field' => $match[1],
                    'error' => $match[2]
                ];
            }
        }

        // Add suggestions for validation errors
        $errorData['suggestions'] = [
            'Check the validation rules for the failing fields',
            'Verify that required fields are being provided',
            'Ensure data types match the expected format',
            'Check for proper input sanitization'
        ];

        return $errorData;
    }

    /**
     * Handle syntax errors by highlighting the problematic line
     *
     * @param array $errorData The error data
     * @return array Enhanced error data with syntax-specific information
     */
    private function handleSyntaxError(array $errorData): array
    {
        $errorData['error_type'] = 'syntax';
        $errorData['syntax_info'] = [];

        $message = $errorData['error']['message'];
        
        // Extract syntax error details
        if (preg_match('/syntax error, (.+?) in (.+?) on line (\d+)/', $message, $matches)) {
            $errorData['syntax_info'] = [
                'description' => $matches[1],
                'file' => $matches[2],
                'line' => (int)$matches[3]
            ];
        }

        // Add suggestions for syntax errors
        $errorData['suggestions'] = [
            'Check for missing semicolons, brackets, or quotes',
            'Verify proper PHP syntax and structure',
            'Look for unclosed strings or comments',
            'Check for proper variable declarations'
        ];

        return $errorData;
    }

    /**
     * Load available routes from the routes file
     */
    private function loadRoutes(): void
    {
        $routesFile = __DIR__ . '/../../../routes/web.php';
        
        if (file_exists($routesFile)) {
            $this->routes = include $routesFile;
        }
    }

    /**
     * Get formatted routes for display
     *
     * @return array Formatted routes information
     */
    private function getFormattedRoutes(): array
    {
        $formatted = [];
        
        foreach ($this->routes as $route) {
            if (is_array($route) && count($route) >= 3) {
                $formatted[] = [
                    'method' => $route[0],
                    'path' => $route[1],
                    'handler' => is_array($route[2]) ? 
                        $route[2][0] . '::' . $route[2][1] : 
                        (string)$route[2]
                ];
            }
        }
        
        return $formatted;
    }

    /**
     * Find routes similar to the requested URL
     *
     * @param string $requestedUrl The requested URL from error message
     * @return array Similar routes
     */
    private function findSimilarRoutes(string $requestedUrl): array
    {
        $similar = [];
        
        // Extract URL from error message if possible
        if (preg_match('/\/[^\s]*/', $requestedUrl, $matches)) {
            $url = $matches[0];
            
            foreach ($this->routes as $route) {
                if (is_array($route) && count($route) >= 2) {
                    $routePath = $route[1];
                    
                    // Simple similarity check
                    if (levenshtein($url, $routePath) <= 3 || 
                        strpos($routePath, $url) !== false || 
                        strpos($url, $routePath) !== false) {
                        $similar[] = [
                            'method' => $route[0],
                            'path' => $routePath
                        ];
                    }
                }
            }
        }
        
        return array_slice($similar, 0, 5); // Limit to 5 suggestions
    }

    /**
     * Get database driver from configuration
     *
     * @return string Database driver
     */
    private function getDatabaseDriver(): string
    {
        $config = $this->getDatabaseConfig();
        return $config['default'] ?? 'unknown';
    }

    /**
     * Get database host from configuration
     *
     * @return string Database host
     */
    private function getDatabaseHost(): string
    {
        $config = $this->getDatabaseConfig();
        $connection = $config['connections'][$config['default']] ?? [];
        return $connection['host'] ?? 'unknown';
    }

    /**
     * Get database name from configuration
     *
     * @return string Database name
     */
    private function getDatabaseName(): string
    {
        $config = $this->getDatabaseConfig();
        $connection = $config['connections'][$config['default']] ?? [];
        return $connection['database'] ?? 'unknown';
    }

    /**
     * Get database configuration
     *
     * @return array Database configuration
     */
    private function getDatabaseConfig(): array
    {
        $configFile = __DIR__ . '/../../../config/database.php';
        
        if (file_exists($configFile)) {
            return include $configFile;
        }
        
        return [];
    }

    /**
     * Get SQLSTATE error description
     *
     * @param string $sqlstate The SQLSTATE code
     * @return string Error description
     */
    private function getSQLStateDescription(string $sqlstate): string
    {
        $descriptions = [
            '23000' => 'Integrity constraint violation',
            '42000' => 'Syntax error or access violation',
            '42S02' => 'Base table or view not found',
            '42S22' => 'Column not found',
            '08006' => 'Connection failure',
            '28000' => 'Invalid authorization specification',
            '22001' => 'String data, right truncated',
            '23505' => 'Unique violation'
        ];

        return $descriptions[$sqlstate] ?? 'Unknown database error';
    }

    /**
     * Get suggestions for database errors
     *
     * @param array $errorData The error data
     * @return array Suggestions
     */
    private function getDatabaseErrorSuggestions(array $errorData): array
    {
        $suggestions = [];
        $sqlstate = $errorData['database_info']['sqlstate'] ?? '';

        switch ($sqlstate) {
            case '23000':
            case '23505':
                $suggestions[] = 'Check for duplicate values in unique columns';
                $suggestions[] = 'Verify foreign key constraints';
                break;
            case '42S02':
                $suggestions[] = 'Verify that the table exists in the database';
                $suggestions[] = 'Check if migrations have been run';
                break;
            case '42S22':
                $suggestions[] = 'Check if the column name is spelled correctly';
                $suggestions[] = 'Verify that the column exists in the table';
                break;
            case '08006':
            case '28000':
                $suggestions[] = 'Check database connection settings';
                $suggestions[] = 'Verify database credentials';
                break;
            default:
                $suggestions[] = 'Check the SQL query syntax';
                $suggestions[] = 'Verify database connection';
                $suggestions[] = 'Check database permissions';
        }

        return $suggestions;
    }
}