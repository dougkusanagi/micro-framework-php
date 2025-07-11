<?php

namespace GuepardoSys\Core;

/**
 * Simple Environment Variable Loader
 * 
 * Lightweight implementation for loading .env files
 */
class Dotenv
{
    private string $path;
    private string $filename;
    private array $variables = [];

    public function __construct(string $path, string $filename = '.env')
    {
        $this->path = $path;
        $this->filename = $filename;
    }

    /**
     * Create a new Dotenv instance
     */
    public static function createImmutable(string $path): self
    {
        return new self($path);
    }

    /**
     * Load environment variables from .env file
     */
    public function load(): void
    {
        $this->loadEnvFile(false);
    }

    /**
     * Load environment variables safely (without throwing exceptions)
     */
    public function safeLoad(): void
    {
        $this->loadEnvFile(true);
    }

    /**
     * Load and parse .env file
     */
    private function loadEnvFile(bool $safe = false): void
    {
        $envFile = $this->path . '/' . $this->filename;

        if (!file_exists($envFile)) {
            if ($safe) {
                return;
            }
            throw new \Exception("Environment file not found: {$envFile}");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || (strlen($line) > 0 && $line[0] === '#')) {
                continue;
            }

            $this->parseLine($line);
        }
    }

    /**
     * Parse a single line from .env file
     */
    private function parseLine(string $line): void
    {
        // Split on first = character
        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            return;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove quotes if present
        $value = $this->removeQuotes($value);

        // Set environment variable
        $this->setEnvironmentVariable($key, $value);
    }

    /**
     * Remove quotes from value
     */
    private function removeQuotes(string $value): string
    {
        $length = strlen($value);

        if ($length < 2) {
            return $value;
        }

        $firstChar = $value[0];
        $lastChar = $value[$length - 1];

        // Remove single or double quotes
        if (($firstChar === '"' && $lastChar === '"') ||
            ($firstChar === "'" && $lastChar === "'")
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Set environment variable
     */
    private function setEnvironmentVariable(string $key, string $value): void
    {
        // Don't override existing environment variables
        if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        $this->variables[$key] = $value;
    }

    /**
     * Get loaded variables
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Check if variable exists
     */
    public function hasVariable(string $key): bool
    {
        return isset($this->variables[$key]);
    }

    /**
     * Get variable value
     */
    public function getVariable(string $key, mixed $default = null): mixed
    {
        return $this->variables[$key] ?? $default;
    }

    /**
     * Load and override existing environment variables
     */
    public function overload(): void
    {
        $envFile = $this->path . '/' . $this->filename;

        if (!file_exists($envFile)) {
            throw new \Exception("Environment file not found: {$envFile}");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || (strlen($line) > 0 && $line[0] === '#')) {
                continue;
            }

            $this->parseLineOverride($line);
        }
    }

    /**
     * Parse a line and override existing variables
     */
    private function parseLineOverride(string $line): void
    {
        // Split on first = character
        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            return;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove quotes if present
        $value = $this->removeQuotes($value);

        // Override environment variable
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
        $this->variables[$key] = $value;
    }
}
