<?php

namespace GuepardoSys\Core;

use Exception;
use Throwable;
use GuepardoSys\Core\Logger;
use GuepardoSys\Core\Security\SecurityHeaders;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\ErrorRendererInterface;

/**
 * Global Error Handler
 * Handles exceptions, errors and provides custom error pages
 */
class ErrorHandler
{
    private Logger $logger;
    private bool $debug;
    private array $errorPages = [];
    private ?ErrorRendererInterface $advancedRenderer = null;

    /**
     * Static instance for backwards compatibility
     */
    private static ?ErrorHandler $instance = null;

    public function __construct(?Logger $logger = null, bool $debug = false)
    {
        $this->logger = $logger ?? new Logger();
        $this->debug = $debug;
        
        // Initialize advanced renderer when debug mode is enabled
        if ($this->debug) {
            $this->initializeAdvancedRenderer();
        }
    }

    /**
     * Initialize the advanced error renderer
     */
    private function initializeAdvancedRenderer(): void
    {
        try {
            $this->advancedRenderer = new AdvancedErrorRenderer();
        } catch (Exception $e) {
            // If advanced renderer fails to initialize, fall back to basic rendering
            $this->advancedRenderer = null;
        }
    }

    /**
     * Register error handlers (instance method)
     */
    private function doRegister(): void
    {
        set_error_handler([$this, 'doHandleError']);
        set_exception_handler([$this, 'doHandleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Handle PHP errors (instance method)
     */
    public function doHandleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Don't interfere with tests unless we're explicitly testing error handling
        if ($this->isTestEnvironment() && !$this->isErrorHandlingTest()) {
            return false;
        }

        $errorType = $this->getErrorType($severity);
        $logMessage = "PHP {$errorType}: {$message} in {$file} on line {$line}";

        $this->logger->error($logMessage, [
            'type' => $errorType,
            'file' => $file,
            'line' => $line,
            'severity' => $severity
        ]);

        if ($this->debug) {
            $this->displayError($errorType, $message, $file, $line);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions (instance method)
     */
    public function doHandleException(Throwable $exception): void
    {
        // Don't interfere with tests unless we're explicitly testing error handling
        if ($this->isTestEnvironment() && !$this->isErrorHandlingTest()) {
            throw $exception;
        }

        $this->logger->critical('Uncaught Exception: ' . $exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if (!headers_sent()) {
            http_response_code(500);
            SecurityHeaders::setAll();
        }

        if ($this->debug) {
            $this->displayException($exception);
        } else {
            $this->displayErrorPage(500);
        }

        exit(1);
    }

    /**
     * Handle fatal errors
     */
    public function handleFatalError(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logger->critical('Fatal Error: ' . $error['message'], [
                'type' => $this->getErrorType($error['type']),
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            if ($this->debug) {
                $this->displayError(
                    $this->getErrorType($error['type']),
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
            } else {
                $this->displayErrorPage(500);
            }
        }
    }

    /**
     * Set custom error page
     */
    public function setErrorPage(int $statusCode, string $viewPath): void
    {
        $this->errorPages[$statusCode] = $viewPath;
    }

    /**
     * Display error page
     */
    private function displayErrorPage(int $statusCode): void
    {
        if (isset($this->errorPages[$statusCode])) {
            try {
                $view = new \GuepardoSys\Core\View\View();
                echo $view->render($this->errorPages[$statusCode], [
                    'status_code' => $statusCode,
                    'status_text' => $this->getStatusText($statusCode)
                ]);
                return;
            } catch (Exception $e) {
                // Fall back to basic error page
            }
        }

        $this->displayBasicErrorPage($statusCode);
    }

    /**
     * Display basic error page
     */
    private function displayBasicErrorPage(int $statusCode): void
    {
        $statusText = $this->getStatusText($statusCode);

        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$statusCode} - {$statusText}</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
        .error-container { max-width: 500px; margin: 0 auto; }
        h1 { color: #dc3545; font-size: 4rem; margin: 0; }
        h2 { color: #6c757d; margin: 0 0 20px 0; }
        p { color: #868e96; line-height: 1.5; }
        .back-link { color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <h1>{$statusCode}</h1>
        <h2>{$statusText}</h2>
        <p>Sorry, something went wrong. Please try again later.</p>
        <a href=\"/\" class=\"back-link\">← Go back to homepage</a>
    </div>
</body>
</html>";
    }

    /**
     * Display debug error information
     */
    private function displayError(string $type, string $message, string $file, int $line): void
    {
        // Clear any existing output buffer to prevent mixing with HTML
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set appropriate headers
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
            SecurityHeaders::setAll();
        }

        // Use advanced renderer if available
        if ($this->advancedRenderer !== null) {
            try {
                echo $this->advancedRenderer->renderError($type, $message, $file, $line);
                exit(1);
            } catch (Exception $e) {
                // Fall back to basic rendering if advanced renderer fails
            }
        }

        // Fallback to basic error display with full page layout
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$type} - GuepardoSys Debug</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; color: #333; }
        .error-container { max-width: 1200px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .error-header { background: #dc3545; color: white; padding: 20px; }
        .error-header h1 { margin: 0; font-size: 1.5rem; }
        .error-content { padding: 20px; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .error-details { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; font-family: 'Courier New', monospace; }
        .error-details strong { color: #495057; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; padding: 8px 16px; border: 1px solid #007bff; border-radius: 4px; }
        .back-link:hover { background: #007bff; color: white; }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <div class=\"error-header\">
            <h1>🐛 {$type}</h1>
        </div>
        <div class=\"error-content\">
            <div class=\"error-message\">
                <strong>Error Message:</strong> " . htmlspecialchars($message) . "
            </div>
            <div class=\"error-details\">
                <p><strong>File:</strong> " . htmlspecialchars($file) . "</p>
                <p><strong>Line:</strong> {$line}</p>
            </div>
            <a href=\"/\" class=\"back-link\">← Go back to homepage</a>
        </div>
    </div>
</body>
</html>";
        exit(1);
    }

    /**
     * Display debug exception information
     */
    private function displayException(Throwable $exception): void
    {
        // Clear any existing output buffer to prevent mixing with HTML
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set appropriate headers
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
            SecurityHeaders::setAll();
        }

        // Use advanced renderer if available
        if ($this->advancedRenderer !== null) {
            try {
                echo $this->advancedRenderer->render($exception);
                exit(1);
            } catch (Exception $e) {
                // Fall back to basic rendering if advanced renderer fails
            }
        }

        // Fallback to basic exception display with full page layout
        $exceptionClass = get_class($exception);
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Uncaught {$exceptionClass} - GuepardoSys Debug</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; color: #333; }
        .error-container { max-width: 1200px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .error-header { background: #dc3545; color: white; padding: 20px; }
        .error-header h1 { margin: 0; font-size: 1.5rem; }
        .error-content { padding: 20px; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .error-details { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; font-family: 'Courier New', monospace; margin-bottom: 20px; }
        .error-details strong { color: #495057; }
        .stack-trace { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; overflow: hidden; }
        .stack-trace summary { background: #e9ecef; padding: 15px; cursor: pointer; font-weight: bold; color: #495057; }
        .stack-trace summary:hover { background: #dee2e6; }
        .stack-trace pre { margin: 0; padding: 15px; overflow-x: auto; font-size: 0.9rem; line-height: 1.4; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; padding: 8px 16px; border: 1px solid #007bff; border-radius: 4px; }
        .back-link:hover { background: #007bff; color: white; }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <div class=\"error-header\">
            <h1>💥 Uncaught {$exceptionClass}</h1>
        </div>
        <div class=\"error-content\">
            <div class=\"error-message\">
                <strong>Exception Message:</strong> " . htmlspecialchars($exception->getMessage()) . "
            </div>
            <div class=\"error-details\">
                <p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>
                <p><strong>Line:</strong> " . $exception->getLine() . "</p>
            </div>
            <div class=\"stack-trace\">
                <details>
                    <summary>📋 Stack Trace</summary>
                    <pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>
                </details>
            </div>
            <a href=\"/\" class=\"back-link\">← Go back to homepage</a>
        </div>
    </div>
</body>
</html>";
        exit(1);
    }

    /**
     * Get error type name
     */
    private function getErrorType(int $type): string
    {
        $types = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        // Add E_STRICT only if it's defined (deprecated in PHP 8.0+)
        // Use numeric value to avoid referencing the deprecated constant
        if (defined('E_STRICT')) {
            $types[2048] = 'Strict Standards'; // E_STRICT = 2048
        }

        return $types[$type] ?? 'Unknown Error';
    }

    /**
     * Get HTTP status text
     */
    private function getStatusText(int $statusCode): string
    {
        $statusTexts = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];

        return $statusTexts[$statusCode] ?? 'Error';
    }

    /**
     * Check if we're in a test environment
     */
    private function isTestEnvironment(): bool
    {
        // Check for PHPUnit/Pest constants
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return true;
        }
        
        // Check environment variable
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') {
            return true;
        }
        
        // Check if Pest is running
        if (class_exists('Pest\\TestSuite', false)) {
            return true;
        }
        
        // Check command line arguments
        if (isset($_SERVER['argv'])) {
            $argv = $_SERVER['argv'];
            foreach ($argv as $arg) {
                if (str_contains($arg, 'pest') || str_contains($arg, 'phpunit') || str_contains($arg, 'vendor/bin/pest')) {
                    return true;
                }
            }
        }
        
        // Check global argv
        if (isset($GLOBALS['argv'])) {
            $argv = $GLOBALS['argv'];
            foreach ($argv as $arg) {
                if (str_contains($arg, 'pest') || str_contains($arg, 'phpunit') || str_contains($arg, '--configuration')) {
                    return true;
                }
            }
        }
        
        // Check if we're in CLI mode with test-related script
        if (php_sapi_name() === 'cli' && isset($_SERVER['SCRIPT_NAME'])) {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            if (str_contains($scriptName, 'pest') || str_contains($scriptName, 'phpunit')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if we're specifically testing error handling functionality
     */
    private function isErrorHandlingTest(): bool
    {
        // Check if we're in an ErrorHandler integration test
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        
        foreach ($backtrace as $frame) {
            if (isset($frame['class']) && str_contains($frame['class'], 'ErrorHandlerIntegrationTest')) {
                return true;
            }
            if (isset($frame['file']) && str_contains($frame['file'], 'ErrorHandlerIntegrationTest')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Set debug mode
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
        
        // Initialize or clear advanced renderer based on debug mode
        if ($this->debug) {
            $this->initializeAdvancedRenderer();
        } else {
            $this->advancedRenderer = null;
        }
    }

    /**
     * Get or create static instance
     */
    private static function getInstance(): ErrorHandler
    {
        if (self::$instance === null) {
            $debug = (bool)($_ENV['APP_DEBUG'] ?? false);
            self::$instance = new self(null, $debug);
        }
        return self::$instance;
    }

    /**
     * Static register method
     */
    public static function register(): void
    {
        $instance = self::getInstance();
        
        // Don't register error handlers during tests
        if (!$instance->isTestEnvironment()) {
            $instance->doRegister();
        }
    }

    /**
     * Static handle exception method
     */
    public static function handleException(Throwable $exception): void
    {
        self::getInstance()->doHandleException($exception);
    }

    /**
     * Static handle error method
     */
    public static function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        return self::getInstance()->doHandleError($severity, $message, $file, $line);
    }

    /**
     * Static set logger method
     */
    public static function setLogger(Logger $logger): void
    {
        self::getInstance()->logger = $logger;
    }
}
