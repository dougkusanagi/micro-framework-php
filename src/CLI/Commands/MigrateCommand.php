<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;
use GuepardoSys\Core\SeederRunner;
use PDOException;
use PDO;

/**
 * Migrate Command
 * 
 * Comando principal de migrações (similar ao artisan migrate)
 */
class MigrateCommand
{
    private bool $force = false;
    private bool $seed = false;

    public function getDescription(): string
    {
        return 'Run database migrations';
    }

    public function execute(array $args): void
    {
        $this->parseArguments($args);

        try {
            $pdo = Database::getConnection();
            $migrationsPath = BASE_PATH . '/database/migrations';
            $seedsPath = BASE_PATH . '/database/seeds';

            $migration = new Migration($pdo);
            $seederRunner = new SeederRunner($seedsPath, $pdo);

            echo "Running migrations..." . PHP_EOL;
            $migration->up();

            if ($this->seed) {
                echo PHP_EOL . "Running seeds..." . PHP_EOL;
                $seederRunner->run('DatabaseSeeder');
            }
        } catch (PDOException $e) {
            // Check if the error is about unknown database
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $dbName = $this->extractDatabaseName($e->getMessage());
                echo "Database '{$dbName}' does not exist." . PHP_EOL;
                echo "Would you like to create it? (y/n): ";
                $answer = trim(fgets(STDIN));
                
                if (strtolower($answer) === 'y') {
                    $this->createDatabase($dbName);
                    echo "Database '{$dbName}' created successfully." . PHP_EOL;
                    echo "Retrying migrations..." . PHP_EOL;
                    $this->execute($args); // Retry the migration
                } else {
                    echo "Migration aborted." . PHP_EOL;
                }
            } else {
                // For other database errors, just show the message
                echo "Error: " . $e->getMessage() . PHP_EOL;
            }
        }
    }

    /**
     * Extract database name from error message
     */
    private function extractDatabaseName(string $errorMessage): string
    {
        // Default database name from config
        $config = Database::getConfig();
        $defaultConnection = $config['default'];
        $dbName = $config['connections'][$defaultConnection]['database'] ?? 'guepardo';
        
        // Try to extract from error message
        if (preg_match("/Unknown database '([^']+)'/", $errorMessage, $matches)) {
            $dbName = $matches[1];
        }
        
        return $dbName;
    }

    /**
     * Create database
     */
    private function createDatabase(string $dbName): void
    {
        $config = Database::getConfig();
        $defaultConnection = $config['default'];
        $connectionConfig = $config['connections'][$defaultConnection];
        
        // Create a PDO connection without specifying a database
        $dsn = "";
        switch ($connectionConfig['driver']) {
            case 'mysql':
                $dsn = "mysql:host={$connectionConfig['host']};port={$connectionConfig['port']}";
                break;
            case 'pgsql':
                $dsn = "pgsql:host={$connectionConfig['host']};port={$connectionConfig['port']}";
                break;
        }
        
        $pdo = new PDO(
            $dsn, 
            $connectionConfig['username'], 
            $connectionConfig['password'], 
            $connectionConfig['options'] ?? []
        );
        
        // Create the database
        $pdo->exec("CREATE DATABASE `{$dbName}`");
    }

    /**
     * Parse command arguments
     */
    private function parseArguments(array $args): void
    {
        foreach ($args as $arg) {
            switch ($arg) {
                case '--force':
                    $this->force = true;
                    break;
                case '--seed':
                    $this->seed = true;
                    break;
                case '--help':
                    $this->showHelp();
                    exit(0);
                    break;
            }
        }
    }

    /**
     * Show help information
     */
    private function showHelp(): void
    {
        echo "Usage: php guepardo migrate [options]" . PHP_EOL;
        echo PHP_EOL;
        echo "Options:" . PHP_EOL;
        echo "  --force    Force the operation to run when in production" . PHP_EOL;
        echo "  --seed     Run database seeds after migrations" . PHP_EOL;
        echo "  --help     Show this help message" . PHP_EOL;
        echo PHP_EOL;
        echo "Examples:" . PHP_EOL;
        echo "  php guepardo migrate" . PHP_EOL;
        echo "  php guepardo migrate --seed" . PHP_EOL;
        echo "  php guepardo migrate --force --seed" . PHP_EOL;
    }
}
