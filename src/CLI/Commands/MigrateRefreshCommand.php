<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

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
        // Parse arguments
        $this->parseArguments($args);

        // Confirmation for refresh
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

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            // Get all executed migrations count
            $executedCount = $this->getExecutedMigrationsCount($pdo);

            if ($executedCount > 0) {
                echo "Rolling back all migrations..." . PHP_EOL;

                // Rollback all migrations
                $rollbackResult = $migration->down($executedCount);

                if (!empty($rollbackResult)) {
                    echo "✓ Rolled back " . count($rollbackResult) . " migration(s)" . PHP_EOL;
                } else {
                    echo "No migrations to rollback" . PHP_EOL;
                }
            }

            // Run migrations again
            echo "Running migrations..." . PHP_EOL;
            $result = $migration->up();

            if (isset($result['message'])) {
                echo $result['message'] . PHP_EOL;
            } else {
                echo "✓ Migrations completed successfully!" . PHP_EOL;
                echo "  Batch: {$result['batch']}" . PHP_EOL;
                echo "  Executed: " . count($result['executed']) . " migration(s)" . PHP_EOL;
            }

            // Run seeds if requested
            if ($this->seed) {
                echo PHP_EOL . "Running seeds..." . PHP_EOL;
                $seedResult = $migration->seed();

                if (empty($seedResult)) {
                    echo "No seed files found" . PHP_EOL;
                } else {
                    echo "✓ Seeds completed successfully!" . PHP_EOL;
                    echo "  Executed: " . count($seedResult) . " seed file(s)" . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo "✗ Error refreshing migrations: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    /**
     * Get count of executed migrations
     */
    private function getExecutedMigrationsCount(\PDO $pdo): int
    {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM migrations");
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
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
