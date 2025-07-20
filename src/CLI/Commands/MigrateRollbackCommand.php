<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\MigrationRunner;

/**
 * Migrate Rollback Command
 * 
 * Comando para rollback de migrações (similar ao artisan migrate:rollback)
 */
class MigrateRollbackCommand
{
    private bool $force = false;
    private int $step = 1;

    public function getDescription(): string
    {
        return 'Rollback database migrations';
    }

    public function execute(array $args): void
    {
        // Parse arguments
        $this->parseArguments($args);

        // Confirmation for rollback
        if (!$this->force) {
            echo "Are you sure you want to rollback {$this->step} migration(s)? (yes/no): ";

            $handle = fopen("php://stdin", "r");
            $input = trim(fgets($handle));
            fclose($handle);

            if (!in_array(strtolower($input), ['yes', 'y'])) {
                echo "Rollback cancelled." . PHP_EOL;
                return;
            }
        }

        echo "Rolling back {$this->step} migration(s)..." . PHP_EOL;

        try {
            $pdo = Database::getConnection();
            $migrationsPath = BASE_PATH . '/database/migrations';
            $migrationRunner = new MigrationRunner($migrationsPath, $pdo);
            $migrationRunner->rollback($this->step);
        } catch (\Exception $e) {
            echo "✗ Error rolling back migrations: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    /**
     * Parse command arguments
     */
    private function parseArguments(array $args): void
    {
        foreach ($args as $arg) {
            if ($arg === '--force') {
                $this->force = true;
            } elseif (strpos($arg, '--step=') === 0) {
                $this->step = (int) substr($arg, 7);
            } elseif ($arg === '--help') {
                $this->showHelp();
                exit(0);
            }
        }
    }

    /**
     * Show help information
     */
    private function showHelp(): void
    {
        echo "Usage: php guepardo migrate:rollback [options]" . PHP_EOL;
        echo PHP_EOL;
        echo "Options:" . PHP_EOL;
        echo "  --force       Force the operation to run when in production" . PHP_EOL;
        echo "  --step=N      Number of migrations to rollback (default: 1)" . PHP_EOL;
        echo "  --help        Show this help message" . PHP_EOL;
        echo PHP_EOL;
        echo "Examples:" . PHP_EOL;
        echo "  php guepardo migrate:rollback" . PHP_EOL;
        echo "  php guepardo migrate:rollback --step=3" . PHP_EOL;
        echo "  php guepardo migrate:rollback --force --step=2" . PHP_EOL;
    }
}
