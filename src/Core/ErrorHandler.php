<?php

namespace GuepardoSys\Core;

use Exception;
use Throwable;
use GuepardoSys\Core\Logger;
use GuepardoSys\Core\Security\SecurityHeaders;

/**
 * Global Error Handler
 * Handles exceptions, errors and provides custom error pages
 */
class ErrorHandler
{
    private Logger $logger;
    private bool $debug;
    private array $errorPages = [];

    /**
     * Static instance for backwards compatibility
     */
    private static ?ErrorHandler $instance = null;

    public function __construct(?Logger $logger = null, bool $debug = false)
    {
        $this->logger = $logger ?? new Logger();
        $this->debug = $debug;
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
        echo "<div style=\"background: #fff; border-left: 4px solid #dc3545; padding: 20px; margin: 10px; font-family: monospace;\">
            <h3 style=\"color: #dc3545; margin: 0 0 10px 0;\">{$type}</h3>
            <p style=\"margin: 0 0 10px 0;\"><strong>Message:</strong> {$message}</p>
            <p style=\"margin: 0 0 10px 0;\"><strong>File:</strong> {$file}</p>
            <p style=\"margin: 0;\"><strong>Line:</strong> {$line}</p>
        </div>";
    }

    /**
     * Display debug exception information
     */
    private function displayException(Throwable $exception): void
    {
        echo "<div style=\"background: #fff; border-left: 4px solid #dc3545; padding: 20px; margin: 10px; font-family: monospace;\">
            <h3 style=\"color: #dc3545; margin: 0 0 10px 0;\">Uncaught " . get_class($exception) . "</h3>
            <p style=\"margin: 0 0 10px 0;\"><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>
            <p style=\"margin: 0 0 10px 0;\"><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>
            <p style=\"margin: 0 0 10px 0;\"><strong>Line:</strong> " . $exception->getLine() . "</p>
            <details style=\"margin-top: 10px;\">
                <summary style=\"cursor: pointer; color: #007bff;\">Stack Trace</summary>
                <pre style=\"background: #f8f9fa; padding: 10px; margin: 10px 0; overflow-x: auto;\">" . htmlspecialchars($exception->getTraceAsString()) . "</pre>
            </details>
        </div>";
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
     * Set debug mode
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Get or create static instance
     */
    private static function getInstance(): ErrorHandler
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Static register method
     */
    public static function register(): void
    {
        self::getInstance()->doRegister();
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
