<?php

namespace GuepardoSys\Core\Debug;

/**
 * ContextCollector - Collects request and environment context information
 * 
 * This class gathers information about the current request, server environment,
 * and session data while sanitizing sensitive information.
 */
class ContextCollector
{
    /**
     * Sensitive keys that should be masked in output
     */
    private const SENSITIVE_KEYS = [
        'password',
        'passwd',
        'pass',
        'pwd',
        'secret',
        'key',
        'token',
        'auth',
        'authorization',
        'api_key',
        'apikey',
        'access_token',
        'refresh_token',
        'private_key',
        'public_key',
        'salt',
        'hash',
        'signature',
        'csrf_token',
        'xsrf_token',
        '_token',
        'cookie',
        'session_id',
        'sessid'
    ];

    /**
     * Collect all context information with performance optimization
     * 
     * @return array Complete context data structure
     */
    public function collect(): array
    {
        // Performance: Use lazy loading and caching
        static $cachedContext = null;
        static $lastRequestTime = null;
        
        $currentRequestTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        
        // Performance: Cache context for the same request
        if ($cachedContext !== null && $lastRequestTime === $currentRequestTime) {
            return $cachedContext;
        }
        
        $context = [
            'request' => $this->getRequestData(),
            'server' => $this->getServerData(),
            'environment' => $this->getEnvironmentData(),
            'session' => $this->getSessionData()
        ];
        
        // Performance: Limit total context size
        $context = $this->limitContextSize($context);
        
        $cachedContext = $context;
        $lastRequestTime = $currentRequestTime;
        
        return $context;
    }

    /**
     * Limit context size to prevent memory issues
     *
     * @param array $context The context data
     * @return array Limited context data
     */
    private function limitContextSize(array $context): array
    {
        $maxTotalSize = 1024 * 1024; // 1MB limit
        $currentSize = strlen(serialize($context));
        
        if ($currentSize <= $maxTotalSize) {
            return $context;
        }
        
        // Performance: Reduce context data if too large
        $priorities = ['request', 'server', 'environment', 'session'];
        $targetSize = $maxTotalSize * 0.8; // 80% of max size
        
        foreach ($priorities as $section) {
            if (!isset($context[$section])) {
                continue;
            }
            
            $context[$section] = $this->limitSectionSize($context[$section], (int)($targetSize / count($priorities)));
            
            $currentSize = strlen(serialize($context));
            if ($currentSize <= $targetSize) {
                break;
            }
        }
        
        return $context;
    }

    /**
     * Limit individual section size
     *
     * @param array $section The section data
     * @param int $maxSize Maximum size for this section
     * @return array Limited section data
     */
    private function limitSectionSize(array $section, int $maxSize): array
    {
        $currentSize = strlen(serialize($section));
        
        if ($currentSize <= $maxSize) {
            return $section;
        }
        
        // Performance: Truncate large arrays
        $limited = [];
        $itemCount = 0;
        $maxItems = 50; // Limit number of items
        
        foreach ($section as $key => $value) {
            if ($itemCount >= $maxItems) {
                $limited['...'] = '[' . (count($section) - $itemCount) . ' more items truncated]';
                break;
            }
            
            if (is_string($value) && strlen($value) > 1000) {
                $value = substr($value, 0, 1000) . '... [truncated]';
            } elseif (is_array($value) && count($value) > 20) {
                $value = array_slice($value, 0, 20, true);
                $value['...'] = '[array truncated]';
            }
            
            $limited[$key] = $value;
            $itemCount++;
            
            // Check size after each addition
            if (strlen(serialize($limited)) > $maxSize) {
                unset($limited[$key]);
                $limited['...'] = '[section truncated due to size]';
                break;
            }
        }
        
        return $limited;
    }

    /**
     * Get HTTP request data
     * 
     * @return array Request information including method, URL, headers, and parameters
     */
    private function getRequestData(): array
    {
        $requestData = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'url' => $this->getCurrentUrl(),
            'headers' => $this->getRequestHeaders(),
            'get' => $_GET ?? [],
            'post' => $_POST ?? [],
            'files' => $_FILES ?? []
        ];

        return $this->sanitizeSensitiveData($requestData);
    }

    /**
     * Get server environment data
     * 
     * @return array Relevant server information
     */
    private function getServerData(): array
    {
        $relevantKeys = [
            'SERVER_SOFTWARE',
            'SERVER_NAME',
            'SERVER_PORT',
            'DOCUMENT_ROOT',
            'SCRIPT_NAME',
            'SCRIPT_FILENAME',
            'REQUEST_TIME',
            'REQUEST_TIME_FLOAT',
            'REMOTE_ADDR',
            'REMOTE_HOST',
            'REMOTE_PORT',
            'HTTP_HOST',
            'HTTP_USER_AGENT',
            'HTTP_ACCEPT',
            'HTTP_ACCEPT_LANGUAGE',
            'HTTP_ACCEPT_ENCODING',
            'HTTP_CONNECTION',
            'HTTPS',
            'SERVER_PROTOCOL',
            'GATEWAY_INTERFACE',
            'PHP_SELF'
        ];

        $serverData = [];
        foreach ($relevantKeys as $key) {
            if (isset($_SERVER[$key])) {
                $serverData[$key] = $_SERVER[$key];
            }
        }

        return $this->sanitizeSensitiveData($serverData);
    }

    /**
     * Get environment variables (filtered for relevant ones)
     * 
     * @return array Environment variables
     */
    private function getEnvironmentData(): array
    {
        $envData = [];
        
        // Get common environment variables that are useful for debugging
        $relevantEnvKeys = [
            'APP_ENV',
            'APP_DEBUG',
            'APP_NAME',
            'PHP_VERSION',
            'PATH_INFO',
            'QUERY_STRING'
        ];

        foreach ($relevantEnvKeys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $envData[$key] = $value;
            }
        }

        // Add PHP version and other runtime info
        $envData['PHP_VERSION'] = PHP_VERSION;
        $envData['PHP_OS'] = PHP_OS;
        $envData['PHP_SAPI'] = PHP_SAPI;

        return $this->sanitizeSensitiveData($envData);
    }

    /**
     * Get session data if session is active
     * 
     * @return array Session data
     */
    private function getSessionData(): array
    {
        // Check if $_SESSION is available and not empty
        if (isset($_SESSION) && !empty($_SESSION)) {
            return $this->sanitizeSensitiveData($_SESSION);
        }

        return [];
    }

    /**
     * Get current URL from server variables with security validation
     * 
     * @return string Current request URL
     */
    private function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        
        // Security: Validate and sanitize host
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $host = $this->sanitizeHost($host);
        
        // Security: Sanitize URI to prevent XSS
        $uri = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'] ?? '';
        $uri = $this->sanitizeUri($uri);
        
        return $protocol . $host . $uri;
    }

    /**
     * Sanitize host to prevent header injection
     *
     * @param string $host The host to sanitize
     * @return string Sanitized host
     */
    private function sanitizeHost(string $host): string
    {
        // Remove any characters that shouldn't be in a hostname
        $host = preg_replace('/[^a-zA-Z0-9\-\.\:]/', '', $host);
        
        // Limit length to prevent DoS
        if (strlen($host) > 255) {
            $host = substr($host, 0, 255);
        }
        
        return $host;
    }

    /**
     * Sanitize URI to prevent XSS and information disclosure
     *
     * @param string $uri The URI to sanitize
     * @return string Sanitized URI
     */
    private function sanitizeUri(string $uri): string
    {
        // Security: Remove sensitive query parameters
        $sensitiveParams = ['password', 'token', 'key', 'secret', 'auth'];
        
        $parts = parse_url($uri);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            
            foreach ($queryParams as $param => $value) {
                if ($this->isSensitiveKey($param)) {
                    $queryParams[$param] = '[HIDDEN]';
                }
            }
            
            $parts['query'] = http_build_query($queryParams);
            $uri = $parts['path'] ?? '';
            if (!empty($parts['query'])) {
                $uri .= '?' . $parts['query'];
            }
        }
        
        // Limit URI length
        if (strlen($uri) > 2000) {
            $uri = substr($uri, 0, 2000) . '... [truncated]';
        }
        
        return htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get request headers from server variables
     * 
     * @return array HTTP headers
     */
    private function getRequestHeaders(): array
    {
        $headers = [];
        
        // Use getallheaders() if available (Apache)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback: extract headers from $_SERVER
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerName = str_replace('_', '-', substr($key, 5));
                    $headerName = ucwords(strtolower($headerName), '-');
                    $headers[$headerName] = $value;
                }
            }
        }

        return $this->sanitizeSensitiveData($headers);
    }

    /**
     * Sanitize sensitive data by masking values
     * 
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    private function sanitizeSensitiveData(array $data): array
    {
        $sanitized = [];
        $maxLength = \GuepardoSys\Core\Debug\DebugConfig::getMaxStringLength();
        
        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $sanitized[$key] = $this->maskValue($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeSensitiveData($value);
            } elseif (is_string($value) && strlen($value) > $maxLength) {
                // Truncate long strings
                $sanitized[$key] = substr($value, 0, $maxLength - 3) . '...';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Check if a key is considered sensitive
     * 
     * @param string $key Key to check
     * @return bool True if key is sensitive
     */
    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);
        
        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (strpos($key, $sensitiveKey) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask a sensitive value
     * 
     * @param mixed $value Value to mask
     * @return string Masked value
     */
    private function maskValue($value): string
    {
        if (is_string($value)) {
            $length = strlen($value);
            if ($length <= 4) {
                return str_repeat('*', $length);
            }
            // Show first 2 and last 2 characters, mask the middle
            return substr($value, 0, 2) . str_repeat('*', max(1, $length - 4)) . substr($value, -2);
        }

        return '[HIDDEN]';
    }
}