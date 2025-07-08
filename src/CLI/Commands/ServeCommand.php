<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Serve Command - Start development server
 */
class ServeCommand extends BaseCommand
{
    public function getDescription(): string
    {
        return 'Start the development server';
    }

    public function execute(array $args): void
    {
        $host = $args[0] ?? 'localhost';
        $port = $args[1] ?? '8000';

        $this->info("Starting development server...");
        $this->info("Server running at http://{$host}:{$port}");
        $this->info("Press Ctrl+C to stop the server");

        // Change to public directory
        $publicPath = BASE_PATH . '/public';

        if (!is_dir($publicPath)) {
            $this->error("Public directory not found: {$publicPath}");
            return;
        }

        // Start PHP built-in server
        $command = "php -S {$host}:{$port} -t {$publicPath}";

        $this->info("Running: {$command}");
        echo PHP_EOL;

        // Execute the server command
        passthru($command);
    }
}
