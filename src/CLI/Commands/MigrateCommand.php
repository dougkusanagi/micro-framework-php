<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

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
        // Parse arguments
        $this->parseArguments($args);

        // Check if database exists
        if (!Migration::databaseExists()) {
            if (!$this->force) {
                echo "Database does not exist." . PHP_EOL;
                echo "Do you want to create it? (yes/no): ";

                $handle = fopen("php://stdin", "r");
                $input = trim(fgets($handle));
                fclose($handle);

                if (!in_array(strtolower($input), ['yes', 'y'])) {
                    echo "Migration cancelled." . PHP_EOL;
                    return;
                }
            }

            echo "Creating database..." . PHP_EOL;
            if (!Migration::createDatabase()) {
                echo "Failed to create database." . PHP_EOL;
                exit(1);
            }
            echo "✓ Database created successfully" . PHP_EOL;
        }

        // Run migrations
        echo "Running migrations..." . PHP_EOL;

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            $result = $migration->up();

            if (isset($result['message'])) {
                echo $result['message'] . PHP_EOL;
            } else {
                echo "✓ Migrations completed successfully!" . PHP_EOL;
                echo "  Batch: {$result['batch']}" . PHP_EOL;
                echo "  Executed: " . count($result['executed']) . " migration(s)" . PHP_EOL;

                // Show executed migrations
                foreach ($result['executed'] as $migrationFile) {
                    echo "  - {$migrationFile}" . PHP_EOL;
                }
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

                    // Show executed seeds
                    foreach ($seedResult as $seedFile) {
                        echo "  - {$seedFile}" . PHP_EOL;
                    }
                }
            }
        } catch (\Exception $e) {
            echo "✗ Error running migrations: " . $e->getMessage() . PHP_EOL;
            exit(1);
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
