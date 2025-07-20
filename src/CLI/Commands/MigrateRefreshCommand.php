<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\MigrationRunner;
use GuepardoSys\Core\SeederRunner;

/**
 * Migrate Refresh Command
 * 
 * Comando para refresh de migrações (similar ao artisan migrate:refresh)
 */
class MigrateRefreshCommand
{
    private bool $force = false;
    private bool $seed = false;

    public function getDescription(): string
    {
        return 'Rollback all migrations and run them again';
    }

    public function execute(array $args): void
    {
        $this->parseArguments($args);

        if (!$this->force) {
            echo "This will rollback ALL migrations and run them again." . PHP_EOL;
            echo "Are you sure you want to continue? (yes/no): ";

            $handle = fopen("php://stdin", "r");
            $input = trim(fgets($handle));
            fclose($handle);

            if (!in_array(strtolower($input), ['yes', 'y'])) {
                echo "Refresh cancelled." . PHP_EOL;
                return;
            }
        }

        $pdo = Database::getConnection();
        $migrationsPath = BASE_PATH . '/database/migrations';
        $seedsPath = BASE_PATH . '/database/seeds';
        $migrationRunner = new MigrationRunner($migrationsPath, $pdo);
        $seederRunner = new SeederRunner($seedsPath, $pdo);

        // Rollback all migrations
        echo "Rolling back all migrations..." . PHP_EOL;
        // We'll rollback a large number to ensure all are rolled back
        $migrationRunner->rollback(9999);

        // Run migrations again
        echo "Running migrations..." . PHP_EOL;
        $migrationRunner->migrate();

        if ($this->seed) {
            echo PHP_EOL . "Running seeds..." . PHP_EOL;
            $seederRunner->run();
        }
    }

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

    private function showHelp(): void
    {
        echo "Usage: php guepardo migrate:refresh [options]" . PHP_EOL;
        echo PHP_EOL;
        echo "Options:" . PHP_EOL;
        echo "  --force    Force the operation to run when in production" . PHP_EOL;
        echo "  --seed     Run database seeds after migrations" . PHP_EOL;
        echo "  --help     Show this help message" . PHP_EOL;
        echo PHP_EOL;
        echo "Examples:" . PHP_EOL;
        echo "  php guepardo migrate:refresh" . PHP_EOL;
        echo "  php guepardo migrate:refresh --seed" . PHP_EOL;
        echo "  php guepardo migrate:refresh --force --seed" . PHP_EOL;
    }
}
