<?php

namespace GuepardoSys\Core;

/**
 * HTTP Request Handler
 */
class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $data;
    private array $files;
    private array $headers;

    public function __construct(
        string $method,
        string $uri,
        array $query = [],
        array $data = [],
        array $files = [],
        array $headers = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $this->normalizeUri($uri);
        $this->query = $query;
        $this->data = $data;
        $this->files = $files;
        $this->headers = $headers;
    }

    /**
     * Create a request from PHP globals
     */
    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Parse query string
        $query = $_GET ?? [];

        // Get request data
        $data = [];
        if ($method === 'POST') {
            $data = $_POST ?? [];
        } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            // Parse input for other methods
            parse_str(file_get_contents('php://input'), $data);
        }

        $files = $_FILES ?? [];
        $headers = self::getHeaders();

        return new self($method, $uri, $query, $data, $files, $headers);
    }

    /**
     * Get all headers
     */
    private static function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($header)] = $value;
            }
        }

        return $headers;
    }

    /**
     * Normalize URI by removing query string and trailing slashes
     */
    private function normalizeUri(string $uri): string
    {
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove trailing slash except for root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Get request method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get query parameter
     */
    public function query(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Get request data
     */
    public function input(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Get all request data (query + input)
     */
    public function all(): array
    {
        return array_merge($this->query, $this->data);
    }

    /**
     * Check if request has input
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]) || isset($this->query[$key]);
    }

    /**
     * Get file upload
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get header
     */
    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('x-requested-with', '')) === 'xmlhttprequest';
    }

    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}
