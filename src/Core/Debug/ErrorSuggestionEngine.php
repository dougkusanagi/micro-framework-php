<?php

namespace GuepardoSys\Core\Debug;

use Throwable;
use PDOException;

/**
 * Provides contextual suggestions and solutions for different types of errors
 * to help developers quickly identify and fix issues
 */
class ErrorSuggestionEngine
{
    /**
     * @var array Common error patterns and their solutions
     */
    private $errorPatterns = [];

    /**
     * @var array Available routes for 404 suggestions
     */
    private $routes = [];

    /**
     * Constructor - Initialize error patterns and load routes
     */
    public function __construct()
    {
        $this->initializeErrorPatterns();
        $this->loadRoutes();
    }

    /**
     * Generate suggestions based on error type and context
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The original exception
     * @return array Array of suggestions
     */
    public function generateSuggestions(array $errorData, ?Throwable $exception = null): array
    {
        $errorType = $this->detectErrorType($errorData, $exception);
        $suggestions = [];

        switch ($errorType) {
            case 'syntax':
                $suggestions = $this->getSyntaxErrorSuggestions($errorData, $exception);
                break;
            case 'database':
                $suggestions = $this->getDatabaseErrorSuggestions($errorData, $exception);
                break;
            case '404':
                $suggestions = $this->get404ErrorSuggestions($errorData);
                break;
            case 'validation':
                $suggestions = $this->getValidationErrorSuggestions($errorData, $exception);
                break;
            case 'custom':
                $suggestions = $this->getCustomExceptionSuggestions($errorData, $exception);
                break;
            default:
                $suggestions = $this->getGeneralErrorSuggestions($errorData, $exception);
        }

        // Add pattern-based suggestions
        $patternSuggestions = $this->getPatternBasedSuggestions($errorData);
        $suggestions = array_merge($suggestions, $patternSuggestions);

        // Remove duplicates and limit suggestions
        return array_unique(array_slice($suggestions, 0, 8));
    }

    /**
     * Detect the type of error
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception
     * @return string The error type
     */
    private function detectErrorType(array $errorData, ?Throwable $exception = null): string
    {
        $errorType = $errorData['error']['type'] ?? '';
        $errorMessage = $errorData['error']['message'] ?? '';

        // Check for syntax errors (ParseError, SyntaxError)
        if ($exception && (
            $exception instanceof \ParseError ||
            get_class($exception) === 'ParseError'
        )) {
            return 'syntax';
        }

        if (strpos($errorType, 'ParseError') !== false ||
            strpos($errorType, 'SyntaxError') !== false ||
            strpos($errorMessage, 'syntax error') !== false) {
            return 'syntax';
        }

        // Check for database errors
        if ($exception instanceof PDOException) {
            return 'database';
        }

        if (strpos($errorType, 'PDO') !== false ||
            strpos($errorMessage, 'database') !== false ||
            strpos($errorMessage, 'SQL') !== false ||
            strpos($errorMessage, 'SQLSTATE') !== false) {
            return 'database';
        }

        // Check for 404 errors
        if (strpos($errorMessage, '404') !== false ||
            strpos($errorMessage, 'Not Found') !== false ||
            strpos($errorMessage, 'Route not found') !== false) {
            return '404';
        }

        // Check for validation errors
        if (strpos($errorType, 'Validation') !== false ||
            strpos($errorMessage, 'validation') !== false) {
            return 'validation';
        }

        // Check for custom exceptions based on type name patterns
        if (strpos($errorType, 'Auth') !== false ||
            strpos($errorType, 'Permission') !== false ||
            strpos($errorType, 'Authorization') !== false ||
            strpos($errorType, 'NotFound') !== false ||
            ($exception && !in_array(get_class($exception), [
                'Error', 'Exception', 'RuntimeException', 'LogicException', 'ParseError'
            ]))) {
            return 'custom';
        }

        return 'general';
    }

    /**
     * Get suggestions for syntax errors
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception
     * @return array Suggestions
     */
    private function getSyntaxErrorSuggestions(array $errorData, ?Throwable $exception = null): array
    {
        $suggestions = [];
        $message = $errorData['error']['message'] ?? '';

        // Common syntax error patterns
        if (strpos($message, 'unexpected') !== false) {
            if (strpos($message, "unexpected '}'") !== false) {
                $suggestions[] = 'Check for missing opening brace { before this line';
                $suggestions[] = 'Verify that all control structures have proper opening braces';
            } elseif (strpos($message, "unexpected '{'") !== false) {
                $suggestions[] = 'Check for missing semicolon ; before the opening brace';
                $suggestions[] = 'Verify proper syntax for function or class declarations';
            } elseif (strpos($message, "unexpected ';'") !== false) {
                $suggestions[] = 'Check for extra semicolon in control structures';
                $suggestions[] = 'Verify that the previous line has proper syntax';
            }
        }

        if (strpos($message, 'expecting') !== false) {
            if (strpos($message, "expecting ')'") !== false) {
                $suggestions[] = 'Check for missing closing parenthesis )';
                $suggestions[] = 'Count opening and closing parentheses to ensure they match';
            } elseif (strpos($message, "expecting ';'") !== false) {
                $suggestions[] = 'Add missing semicolon ; at the end of the statement';
                $suggestions[] = 'Check if the previous line needs a semicolon';
            }
        }

        // General syntax suggestions
        $suggestions[] = 'Use a code editor with syntax highlighting to spot errors';
        $suggestions[] = 'Check for proper indentation and code structure';
        $suggestions[] = 'Verify that all strings are properly quoted';

        return $suggestions;
    }

    /**
     * Get suggestions for database errors
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception
     * @return array Suggestions
     */
    private function getDatabaseErrorSuggestions(array $errorData, ?Throwable $exception = null): array
    {
        $suggestions = [];
        $message = $errorData['error']['message'] ?? '';

        // Extract SQLSTATE if available
        $sqlstate = '';
        if (preg_match('/SQLSTATE\[([^\]]+)\]/', $message, $matches)) {
            $sqlstate = $matches[1];
        }

        switch ($sqlstate) {
            case '23000':
            case '23505':
                $suggestions[] = 'Check for duplicate values in unique or primary key columns';
                $suggestions[] = 'Verify foreign key constraints are not violated';
                $suggestions[] = 'Use INSERT IGNORE or ON DUPLICATE KEY UPDATE for MySQL';
                break;

            case '42S02':
                $suggestions[] = 'Verify that the table exists in the database';
                $suggestions[] = 'Run database migrations to create missing tables';
                $suggestions[] = 'Check table name spelling and case sensitivity';
                break;

            case '42S22':
                $suggestions[] = 'Check if the column name is spelled correctly';
                $suggestions[] = 'Verify that the column exists in the table schema';
                $suggestions[] = 'Run migrations to add missing columns';
                break;

            case '08006':
            case '28000':
                $suggestions[] = 'Check database connection settings in config/database.php';
                $suggestions[] = 'Verify database credentials (username, password)';
                $suggestions[] = 'Ensure database server is running and accessible';
                break;

            case '22001':
                $suggestions[] = 'Data is too long for the column - increase column size';
                $suggestions[] = 'Truncate or validate input data before insertion';
                break;

            default:
                // General database error suggestions
                if (strpos($message, 'Connection refused') !== false) {
                    $suggestions[] = 'Database server may be down - check if it\'s running';
                    $suggestions[] = 'Verify database host and port configuration';
                }

                if (strpos($message, 'Access denied') !== false) {
                    $suggestions[] = 'Check database username and password';
                    $suggestions[] = 'Verify user has proper permissions for the database';
                }

                $suggestions[] = 'Check the SQL query syntax for errors';
                $suggestions[] = 'Verify database connection configuration';
                $suggestions[] = 'Check database server logs for more details';
        }

        return $suggestions;
    }

    /**
     * Get suggestions for 404 errors
     *
     * @param array $errorData The error data
     * @return array Suggestions
     */
    private function get404ErrorSuggestions(array $errorData): array
    {
        $suggestions = [];
        $message = $errorData['error']['message'] ?? '';

        // Extract requested URL if possible
        $requestedUrl = '';
        if (preg_match('/\/[^\s]*/', $message, $matches)) {
            $requestedUrl = $matches[0];
        }

        $suggestions[] = 'Check if the URL is spelled correctly';
        $suggestions[] = 'Verify that the route is defined in routes/web.php';
        $suggestions[] = 'Make sure the HTTP method (GET, POST, etc.) matches the route';

        // Suggest similar routes if available
        if ($requestedUrl && !empty($this->routes)) {
            $similarRoutes = $this->findSimilarRoutes($requestedUrl);
            if (!empty($similarRoutes)) {
                $suggestions[] = 'Did you mean one of these routes: ' . 
                    implode(', ', array_column($similarRoutes, 'path'));
            }
        }

        $suggestions[] = 'Check if the route requires authentication or middleware';
        $suggestions[] = 'Verify that the controller and method exist';

        return $suggestions;
    }

    /**
     * Get suggestions for validation errors
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception
     * @return array Suggestions
     */
    private function getValidationErrorSuggestions(array $errorData, ?Throwable $exception = null): array
    {
        $suggestions = [];
        $message = $errorData['error']['message'] ?? '';

        if (strpos($message, 'required') !== false) {
            $suggestions[] = 'Ensure all required fields are provided in the request';
            $suggestions[] = 'Check form inputs and API request body';
        }

        if (strpos($message, 'email') !== false) {
            $suggestions[] = 'Verify email format is valid (user@domain.com)';
            $suggestions[] = 'Check for proper email validation rules';
        }

        if (strpos($message, 'numeric') !== false || strpos($message, 'integer') !== false) {
            $suggestions[] = 'Ensure numeric fields contain only numbers';
            $suggestions[] = 'Check data type conversion and validation';
        }

        if (strpos($message, 'length') !== false || strpos($message, 'min') !== false || strpos($message, 'max') !== false) {
            $suggestions[] = 'Check field length requirements and limits';
            $suggestions[] = 'Verify input data meets size constraints';
        }

        $suggestions[] = 'Review validation rules in your controller or model';
        $suggestions[] = 'Check client-side validation to prevent invalid submissions';

        return $suggestions;
    }

    /**
     * Get suggestions for custom exceptions
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception
     * @return array Suggestions
     */
    private function getCustomExceptionSuggestions(array $errorData, ?Throwable $exception = null): array
    {
        $suggestions = [];
        $exceptionClass = $errorData['error']['type'] ?? '';

        // Provide suggestions based on common custom exception patterns
        if (strpos($exceptionClass, 'Auth') !== false) {
            $suggestions[] = 'Check user authentication status';
            $suggestions[] = 'Verify login credentials and session';
            $suggestions[] = 'Ensure proper authentication middleware is applied';
        }

        if (strpos($exceptionClass, 'Permission') !== false || strpos($exceptionClass, 'Authorization') !== false) {
            $suggestions[] = 'Check user permissions and roles';
            $suggestions[] = 'Verify authorization policies';
            $suggestions[] = 'Ensure user has required access level';
        }

        if (strpos($exceptionClass, 'NotFound') !== false) {
            $suggestions[] = 'Verify that the requested resource exists';
            $suggestions[] = 'Check resource ID or identifier';
            $suggestions[] = 'Ensure proper error handling for missing resources';
        }

        $suggestions[] = 'Check the custom exception documentation';
        $suggestions[] = 'Review the code that throws this exception';

        return $suggestions;
    }

    /**
     * Get general error suggestions
     *
     * @param array $errorData The error data
     * @param Throwable|null $exception The exception
     * @return array Suggestions
     */
    private function getGeneralErrorSuggestions(array $errorData, ?Throwable $exception = null): array
    {
        $suggestions = [];
        $message = $errorData['error']['message'] ?? '';

        // Common error patterns (case-insensitive)
        $messageLower = strtolower($message);
        
        if (strpos($messageLower, 'undefined') !== false) {
            if (strpos($messageLower, 'variable') !== false) {
                $suggestions[] = 'Check if the variable is properly declared and initialized';
                $suggestions[] = 'Verify variable scope and availability';
            }
            if (strpos($messageLower, 'function') !== false) {
                $suggestions[] = 'Check if the function is properly defined';
                $suggestions[] = 'Verify function name spelling and case';
                $suggestions[] = 'Ensure required files are included';
            }
        }

        if (strpos($message, 'Call to undefined method') !== false) {
            $suggestions[] = 'Check if the method exists in the class';
            $suggestions[] = 'Verify method name spelling and case';
            $suggestions[] = 'Ensure the object is properly instantiated';
        }

        if (strpos($message, 'Class') !== false && strpos($message, 'not found') !== false) {
            $suggestions[] = 'Check if the class file is properly included';
            $suggestions[] = 'Verify namespace and use statements';
            $suggestions[] = 'Ensure autoloading is working correctly';
        }

        if (strpos($message, 'Permission denied') !== false) {
            $suggestions[] = 'Check file and directory permissions';
            $suggestions[] = 'Ensure web server has proper access rights';
        }

        // Memory and execution time errors
        if (strpos($message, 'memory') !== false && strpos($message, 'exhausted') !== false) {
            $suggestions[] = 'Increase PHP memory_limit in php.ini';
            $suggestions[] = 'Optimize code to use less memory';
            $suggestions[] = 'Process data in smaller chunks';
        }

        if (strpos($message, 'execution time') !== false && strpos($message, 'exceeded') !== false) {
            $suggestions[] = 'Increase max_execution_time in php.ini';
            $suggestions[] = 'Optimize slow database queries';
            $suggestions[] = 'Use background job processing for long tasks';
        }

        // Only add general suggestions if no specific ones were found
        if (empty($suggestions)) {
            $suggestions[] = 'Check application logs for more details';
            $suggestions[] = 'Review recent code changes that might have caused this error';
        }

        return $suggestions;
    }

    /**
     * Get pattern-based suggestions using predefined error patterns
     *
     * @param array $errorData The error data
     * @return array Suggestions
     */
    private function getPatternBasedSuggestions(array $errorData): array
    {
        $suggestions = [];
        $message = strtolower($errorData['error']['message'] ?? '');

        foreach ($this->errorPatterns as $pattern => $patternSuggestions) {
            if (strpos($message, strtolower($pattern)) !== false) {
                $suggestions = array_merge($suggestions, $patternSuggestions);
            }
        }

        return $suggestions;
    }

    /**
     * Initialize common error patterns and their solutions
     */
    private function initializeErrorPatterns(): void
    {
        $this->errorPatterns = [
            'memory limit' => [
                'Increase PHP memory_limit in php.ini',
                'Optimize code to use less memory',
                'Process data in smaller chunks'
            ],
            'maximum execution time' => [
                'Increase max_execution_time in php.ini',
                'Optimize slow database queries',
                'Use background job processing for long tasks'
            ],
            'file not found' => [
                'Check if the file path is correct',
                'Verify file permissions',
                'Ensure the file exists in the expected location'
            ],
            'connection refused' => [
                'Check if the service is running',
                'Verify host and port configuration',
                'Check firewall settings'
            ],
            'timeout' => [
                'Increase timeout settings',
                'Check network connectivity',
                'Optimize slow operations'
            ],
            'curl error' => [
                'Check network connectivity',
                'Verify SSL certificates',
                'Check API endpoint availability'
            ]
        ];
    }

    /**
     * Load available routes for 404 suggestions
     */
    private function loadRoutes(): void
    {
        $routesFile = __DIR__ . '/../../../routes/web.php';
        
        if (file_exists($routesFile)) {
            $routes = include $routesFile;
            if (is_array($routes)) {
                $this->routes = $routes;
            }
        }
    }

    /**
     * Find routes similar to the requested URL
     *
     * @param string $requestedUrl The requested URL
     * @return array Similar routes
     */
    private function findSimilarRoutes(string $requestedUrl): array
    {
        $similar = [];
        
        foreach ($this->routes as $route) {
            if (is_array($route) && count($route) >= 2) {
                $routePath = $route[1];
                
                // Calculate similarity using levenshtein distance
                $distance = levenshtein($requestedUrl, $routePath);
                
                // Consider routes with small edit distance or partial matches
                if ($distance <= 3 || 
                    strpos($routePath, $requestedUrl) !== false || 
                    strpos($requestedUrl, $routePath) !== false) {
                    $similar[] = [
                        'method' => $route[0],
                        'path' => $routePath,
                        'distance' => $distance
                    ];
                }
            }
        }
        
        // Sort by similarity (lower distance = more similar)
        usort($similar, function($a, $b) {
            return $a['distance'] - $b['distance'];
        });
        
        return array_slice($similar, 0, 3); // Return top 3 similar routes
    }
}