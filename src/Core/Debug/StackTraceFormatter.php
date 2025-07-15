<?php

namespace GuepardoSys\Core\Debug;

/**
 * Stack trace formatter that processes exception stack traces
 * and organizes them for better debugging experience
 */
class StackTraceFormatter
{
    /**
     * Format a complete stack trace array
     *
     * @param array $trace The raw stack trace from debug_backtrace() or Exception::getTrace()
     * @return array The formatted stack trace with additional metadata
     */
    public function format(array $trace): array
    {
        $formattedTrace = [];
        
        foreach ($trace as $index => $frame) {
            $formattedFrame = $this->formatFrame($frame, $index);
            
            // Skip vendor frames if configured to hide them
            if (\GuepardoSys\Core\Debug\DebugConfig::hideVendor() && $formattedFrame['is_vendor']) {
                continue;
            }
            
            $formattedTrace[] = $formattedFrame;
        }
        
        return $formattedTrace;
    }

    /**
     * Format a single stack trace frame
     *
     * @param array $frame The raw stack trace frame
     * @param int $index The frame index in the stack trace
     * @return array The formatted frame with additional metadata
     */
    public function formatFrame(array $frame, int $index = 0): array
    {
        // Ensure required keys exist with defaults
        $frame = array_merge([
            'file' => null,
            'line' => null,
            'function' => 'unknown',
            'class' => null,
            'type' => null,
            'args' => []
        ], $frame);

        $formattedFrame = [
            'index' => $index,
            'file' => $frame['file'],
            'line' => $frame['line'],
            'function' => $frame['function'],
            'class' => $frame['class'],
            'type' => $frame['type'],
            'args' => $this->formatArguments($frame['args']),
            'source' => null, // Will be populated by SourceCodeExtractor when needed
            'is_vendor' => $this->isVendorFrame($frame),
            'is_application' => $this->isApplicationFrame($frame),
            'short_file' => $this->getShortFilePath($frame['file']),
            'function_name' => $this->getFunctionName($frame)
        ];

        return $formattedFrame;
    }

    /**
     * Check if a frame belongs to vendor code
     *
     * @param array $frame The stack trace frame
     * @return bool True if the frame is from vendor code
     */
    private function isVendorFrame(array $frame): bool
    {
        if (!isset($frame['file']) || $frame['file'] === null) {
            return false;
        }

        $file = $frame['file'];
        
        // Check for common vendor directories
        $vendorPatterns = [
            '/vendor/',
            '/node_modules/',
            '/composer/',
            '/pear/',
            '/lib/php/',
            '/usr/share/php/',
            '/System/Library/',
            '/Library/WebServer/'
        ];

        foreach ($vendorPatterns as $pattern) {
            if (strpos($file, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a frame belongs to application code
     *
     * @param array $frame The stack trace frame
     * @return bool True if the frame is from application code
     */
    private function isApplicationFrame(array $frame): bool
    {
        if (!isset($frame['file']) || $frame['file'] === null) {
            return false;
        }

        $file = $frame['file'];
        
        // If it's not vendor code and contains application directories, it's application code
        if (!$this->isVendorFrame($frame)) {
            $appPatterns = [
                '/src/',
                '/app/',
                '/controllers/',
                '/models/',
                '/views/',
                '/routes/',
                '/config/'
            ];

            foreach ($appPatterns as $pattern) {
                if (stripos($file, $pattern) !== false) {
                    return true;
                }
            }
            
            // If file is in the current working directory and not vendor, consider it application
            $cwd = getcwd();
            if ($cwd && strpos($file, $cwd) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a shortened version of the file path for display
     *
     * @param string|null $file The full file path
     * @return string|null The shortened file path
     */
    private function getShortFilePath(?string $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $cwd = getcwd();
        if ($cwd && strpos($file, $cwd) === 0) {
            return substr($file, strlen($cwd) + 1);
        }

        // If not in current directory, show last 3 path segments
        $parts = explode('/', $file); // Use forward slash for cross-platform compatibility
        if (count($parts) > 3) {
            return '.../' . implode('/', array_slice($parts, -3));
        }

        return $file;
    }

    /**
     * Get a formatted function name including class and type
     *
     * @param array $frame The stack trace frame
     * @return string The formatted function name
     */
    private function getFunctionName(array $frame): string
    {
        $functionName = $frame['function'] ?? 'unknown';
        
        if (isset($frame['class']) && $frame['class'] !== null) {
            $type = $frame['type'] ?? '::';
            return $frame['class'] . $type . $functionName;
        }

        return $functionName;
    }

    /**
     * Format function arguments for display
     *
     * @param array $args The function arguments
     * @return array The formatted arguments
     */
    private function formatArguments(array $args): array
    {
        $formattedArgs = [];
        
        foreach ($args as $arg) {
            $formattedArgs[] = $this->formatArgument($arg);
        }
        
        return $formattedArgs;
    }

    /**
     * Format a single argument for display
     *
     * @param mixed $arg The argument to format
     * @return array The formatted argument with type and value information
     */
    private function formatArgument($arg): array
    {
        $type = gettype($arg);
        $value = null;
        $preview = null;
        $maxLength = \GuepardoSys\Core\Debug\DebugConfig::getMaxStringLength();

        switch ($type) {
            case 'string':
                $value = $arg;
                $preview = strlen($arg) > $maxLength ? substr($arg, 0, $maxLength - 3) . '...' : $arg;
                break;
                
            case 'integer':
            case 'double':
            case 'boolean':
                $value = $arg;
                $preview = var_export($arg, true);
                break;
                
            case 'array':
                $value = null; // Don't store full array to save memory
                $preview = 'array(' . count($arg) . ')';
                break;
                
            case 'object':
                $value = null; // Don't store full object to save memory
                $preview = get_class($arg);
                break;
                
            case 'resource':
                $value = null;
                $preview = 'resource(' . get_resource_type($arg) . ')';
                break;
                
            case 'NULL':
                $value = null;
                $preview = 'null';
                break;
                
            default:
                $value = null;
                $preview = $type;
        }

        return [
            'type' => $type,
            'value' => $value,
            'preview' => $preview
        ];
    }
}