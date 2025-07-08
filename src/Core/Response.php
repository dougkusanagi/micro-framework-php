<?php

namespace GuepardoSys\Core;

/**
 * HTTP Response Handler
 */
class Response
{
    private string $content;
    private int $statusCode;
    private array $headers;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Set response content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get header
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set JSON response
     */
    public function json(array $data, int $statusCode = 200): self
    {
        $this->setContent(json_encode($data));
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        return $this;
    }

    /**
     * Set redirect response
     */
    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        return $this;
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Send content
        echo $this->content;
    }

    /**
     * Create a simple response
     */
    public static function make(string $content, int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, $headers);
    }

    /**
     * Create a JSON response (static)
     */
    public static function jsonResponse(array $data, int $statusCode = 200): self
    {
        $response = new self();
        return $response->json($data, $statusCode);
    }

    /**
     * Create a redirect response (static)
     */
    public static function redirectResponse(string $url, int $statusCode = 302): self
    {
        $response = new self();
        return $response->redirect($url, $statusCode);
    }
}
