<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Base Command Class
 */
abstract class BaseCommand
{
    /**
     * Execute the command
     */
    abstract public function execute(array $args): void;

    /**
     * Get command description
     */
    abstract public function getDescription(): string;

    /**
     * Show success message
     */
    protected function success(string $message): void
    {
        echo "\033[32m✓ {$message}\033[0m" . PHP_EOL;
    }

    /**
     * Show error message
     */
    protected function error(string $message): void
    {
        echo "\033[31m✗ {$message}\033[0m" . PHP_EOL;
    }

    /**
     * Show info message
     */
    protected function info(string $message): void
    {
        echo "\033[34mℹ {$message}\033[0m" . PHP_EOL;
    }

    /**
     * Show warning message
     */
    protected function warning(string $message): void
    {
        echo "\033[33m⚠ {$message}\033[0m" . PHP_EOL;
    }

    /**
     * Ask for user input
     */
    protected function ask(string $question, string $default = ''): string
    {
        echo "\033[33m{$question}" . ($default ? " [{$default}]" : '') . ":\033[0m ";
        $input = trim(fgets(STDIN));
        return $input ?: $default;
    }

    /**
     * Create directory if it doesn't exist
     */
    protected function createDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Get stub content
     */
    protected function getStub(string $stubName): string
    {
        $stubPath = BASE_PATH . '/stubs/' . $stubName . '.stub';

        if (!file_exists($stubPath)) {
            throw new \Exception("Stub file not found: {$stubPath}");
        }

        return file_get_contents($stubPath);
    }
    /**
     * Replace variables in stub content
     */
    protected function replaceStubVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }
}
