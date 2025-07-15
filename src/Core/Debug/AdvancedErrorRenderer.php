<?php

namespace GuepardoSys\Core\Debug;

use Throwable;

/**
 * Advanced error renderer that provides detailed error information
 * for debugging purposes when APP_DEBUG is enabled
 */
class AdvancedErrorRenderer implements ErrorRendererInterface
{
    /**
     * @var SourceCodeExtractor|null
     */
    private $sourceExtractor;

    /**
     * @var ContextCollector|null
     */
    private $contextCollector;

    /**
     * @var StackTraceFormatter|null
     */
    private $stackFormatter;

    /**
     * @var ErrorTypeHandler|null
     */
    private $errorTypeHandler;

    /**
     * @var ErrorSuggestionEngine|null
     */
    private $suggestionEngine;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize available dependencies
        $this->sourceExtractor = new SourceCodeExtractor();
        $this->contextCollector = new ContextCollector();
        $this->stackFormatter = new StackTraceFormatter();
        $this->errorTypeHandler = new ErrorTypeHandler();
        $this->suggestionEngine = new ErrorSuggestionEngine();
    }

    /**
     * Render a detailed error page for an exception
     *
     * @param Throwable $exception The exception to render
     * @param array $context Additional context information
     * @return string The rendered HTML output
     */
    public function render(Throwable $exception, array $context = []): string
    {
        // Format the stack trace using the StackTraceFormatter
        $formattedTrace = $this->stackFormatter ? 
            $this->stackFormatter->format($exception->getTrace()) : 
            $exception->getTrace();

        // Prepare error data structure
        $errorData = [
            'error' => [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode()
            ],
            'stack_trace' => $formattedTrace,
            'context' => $context,
            'exception' => $exception
        ];

        return $this->generateHTML($errorData);
    }

    /**
     * Render a detailed error page for a PHP error
     *
     * @param string $type The error type
     * @param string $message The error message
     * @param string $file The file where the error occurred
     * @param int $line The line number where the error occurred
     * @return string The rendered HTML output
     */
    public function renderError(string $type, string $message, string $file, int $line): string
    {
        // Get the stack trace and format it
        $rawTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $formattedTrace = $this->stackFormatter ? 
            $this->stackFormatter->format($rawTrace) : 
            $rawTrace;

        // Prepare error data structure for PHP errors
        $errorData = [
            'error' => [
                'type' => $type,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'code' => 0
            ],
            'stack_trace' => $formattedTrace,
            'context' => []
        ];

        return $this->generateHTML($errorData);
    }

    /**
     * Generate the HTML output for the error page with performance optimization
     *
     * @param array $data The error data to display
     * @return string The generated HTML
     */
    private function generateHTML(array $data): string
    {
        // Enhance data with source code and context information
        $enhancedData = $this->enhanceErrorData($data);
        
        // Security: Sanitize all output data
        $enhancedData = $this->sanitizeErrorData($enhancedData);
        
        // Load and render the error template
        $templatePath = __DIR__ . '/Templates/error-page.html.php';
        
        // Security: Validate template path
        if (!$this->isValidTemplatePath($templatePath)) {
            return $this->generateBasicHTML($enhancedData);
        }
        
        if (file_exists($templatePath)) {
            // Performance: Use output buffering with compression if available
            if (function_exists('ob_gzhandler') && !headers_sent()) {
                ob_start('ob_gzhandler');
            } else {
                ob_start();
            }
            
            try {
                // Extract data for template
                $data = $enhancedData;
                
                // Include the template
                include $templatePath;
                
                // Get the rendered content
                $html = ob_get_clean();
                
                // Performance: Minify HTML output
                return $this->minifyHTML($html);
                
            } catch (\Exception $e) {
                ob_end_clean();
                return $this->generateBasicHTML($enhancedData);
            }
        }
        
        // Fallback to basic HTML if template is not found
        return $this->generateBasicHTML($enhancedData);
    }

    /**
     * Validate template path to prevent directory traversal
     *
     * @param string $templatePath The template path to validate
     * @return bool True if path is valid and safe
     */
    private function isValidTemplatePath(string $templatePath): bool
    {
        // Security: Prevent directory traversal
        if (strpos($templatePath, '..') !== false) {
            return false;
        }

        // Security: Ensure template is within allowed directory
        $realPath = realpath($templatePath);
        if ($realPath === false) {
            return false;
        }

        $allowedDir = realpath(__DIR__ . '/Templates');
        if ($allowedDir === false || strpos($realPath, $allowedDir) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize error data to prevent XSS attacks
     *
     * @param array $data The error data to sanitize
     * @return array Sanitized error data
     */
    private function sanitizeErrorData(array $data): array
    {
        return $this->recursiveSanitize($data);
    }

    /**
     * Recursively sanitize array data
     *
     * @param mixed $data The data to sanitize
     * @return mixed Sanitized data
     */
    private function recursiveSanitize($data)
    {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitizedKey = is_string($key) ? htmlspecialchars($key, ENT_QUOTES, 'UTF-8') : $key;
                $sanitized[$sanitizedKey] = $this->recursiveSanitize($value);
            }
            return $sanitized;
        } elseif (is_string($data)) {
            // Don't double-sanitize already processed syntax highlighting
            if (strpos($data, '<span class="php-') !== false) {
                return $data; // Already processed by SourceCodeExtractor
            }
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        } elseif (is_object($data)) {
            // Convert objects to safe string representation
            if (method_exists($data, '__toString')) {
                return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
            }
            return '[Object: ' . htmlspecialchars(get_class($data), ENT_QUOTES, 'UTF-8') . ']';
        }
        
        return $data;
    }

    /**
     * Minify HTML output for better performance
     *
     * @param string $html The HTML to minify
     * @return string Minified HTML
     */
    private function minifyHTML(string $html): string
    {
        // Performance: Basic HTML minification
        // Remove comments (but preserve conditional comments)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        
        // Remove extra whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Remove leading/trailing whitespace from lines
        $html = preg_replace('/^\s+|\s+$/m', '', $html);
        
        // Compress multiple spaces into single space
        $html = preg_replace('/\s{2,}/', ' ', $html);
        
        return trim($html);
    }

    /**
     * Enhance error data with additional information
     *
     * @param array $data The basic error data
     * @return array Enhanced error data
     */
    private function enhanceErrorData(array $data): array
    {
        // Add source code information if available and enabled
        if ($this->sourceExtractor && !empty($data['error']['file']) && !empty($data['error']['line']) && \GuepardoSys\Core\Debug\DebugConfig::showSource()) {
            $data['source'] = $this->sourceExtractor->extract(
                $data['error']['file'],
                $data['error']['line']
            );
        }

        // Add context information if available
        if ($this->contextCollector) {
            $data['context'] = array_merge(
                $data['context'] ?? [],
                $this->contextCollector->collect()
            );
        }

        // Enhance stack trace with source code for each frame
        if ($this->sourceExtractor && !empty($data['stack_trace']) && \GuepardoSys\Core\Debug\DebugConfig::showSource()) {
            foreach ($data['stack_trace'] as &$frame) {
                if (!empty($frame['file']) && !empty($frame['line']) && file_exists($frame['file'])) {
                    $frame['source'] = $this->sourceExtractor->extract(
                        $frame['file'],
                        $frame['line'],
                        5 // Use smaller context for stack trace frames
                    );
                }
            }
        }

        // Enhance with error type specific information
        if ($this->errorTypeHandler) {
            $exception = $data['exception'] ?? null;
            $data = $this->errorTypeHandler->enhanceErrorData($data, $exception);
        }

        // Generate contextual suggestions
        if ($this->suggestionEngine) {
            $exception = $data['exception'] ?? null;
            $data['suggestions'] = $this->suggestionEngine->generateSuggestions($data, $exception);
        }

        // Remove the exception from data to avoid serialization issues in template
        unset($data['exception']);

        return $data;
    }

    /**
     * Generate basic HTML fallback
     *
     * @param array $data The error data to display
     * @return string The generated HTML
     */
    private function generateBasicHTML(array $data): string
    {
        $html = '<!DOCTYPE html>';
        $html .= '<html lang="en">';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>Error - ' . htmlspecialchars($data['error']['type']) . '</title>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>Error: ' . htmlspecialchars($data['error']['type']) . '</h1>';
        $html .= '<p><strong>Message:</strong> ' . htmlspecialchars($data['error']['message']) . '</p>';
        $html .= '<p><strong>File:</strong> ' . htmlspecialchars($data['error']['file']) . '</p>';
        $html .= '<p><strong>Line:</strong> ' . htmlspecialchars((string)$data['error']['line']) . '</p>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    /**
     * Set the source code extractor dependency
     *
     * @param SourceCodeExtractor $extractor
     */
    public function setSourceExtractor($extractor): void
    {
        $this->sourceExtractor = $extractor;
    }

    /**
     * Set the context collector dependency
     *
     * @param ContextCollector $collector
     */
    public function setContextCollector($collector): void
    {
        $this->contextCollector = $collector;
    }

    /**
     * Set the stack trace formatter dependency
     *
     * @param StackTraceFormatter $formatter
     */
    public function setStackFormatter($formatter): void
    {
        $this->stackFormatter = $formatter;
    }

    /**
     * Set the error type handler dependency
     *
     * @param ErrorTypeHandler $handler
     */
    public function setErrorTypeHandler($handler): void
    {
        $this->errorTypeHandler = $handler;
    }

    /**
     * Set the error suggestion engine dependency
     *
     * @param ErrorSuggestionEngine $engine
     */
    public function setSuggestionEngine($engine): void
    {
        $this->suggestionEngine = $engine;
    }
}