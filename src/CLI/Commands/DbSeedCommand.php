<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

/**
 * Database Seed Command
 * 
 * Comando para executar seeds (similar ao artisan db:seed)
 */
class DbSeedCommand
{
    private bool $force = false;

    public function getDescription(): string
    {
        return 'Run database seeds';
    }

    public function execute(array $args): void
    {
        // Parse arguments
        $this->parseArguments($args);

        // Confirmation for seeding
        if (!$this->force) {
            echo "This will run database seeds." . PHP_EOL;
            echo "Are you sure you want to continue? (yes/no): ";

            $handle = fopen("php://stdin", "r");
            $input = trim(fgets($handle));
            fclose($handle);

            if (!in_array(strtolower($input), ['yes', 'y'])) {
                echo "Seeding cancelled." . PHP_EOL;
                return;
            }
        }

        echo "Running database seeds..." . PHP_EOL;

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            $result = $migration->seed();

            if (empty($result)) {
                echo "No seed files found" . PHP_EOL;
            } else {
                echo "✓ Seeds completed successfully!" . PHP_EOL;
                echo "  Executed: " . count($result) . " seed file(s)" . PHP_EOL;

                // Show executed seeds
                foreach ($result as $seedFile) {
                    echo "  - {$seedFile}" . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo "✗ Error running seeds: " . $e->getMessage() . PHP_EOL;
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
        echo "Usage: php guepardo db:seed [options]" . PHP_EOL;
        echo PHP_EOL;
        echo "Options:" . PHP_EOL;
        echo "  --force    Force the operation to run when in production" . PHP_EOL;
        echo "  --help     Show this help message" . PHP_EOL;
        echo PHP_EOL;
        echo "Examples:" . PHP_EOL;
        echo "  php guepardo db:seed" . PHP_EOL;
        echo "  php guepardo db:seed --force" . PHP_EOL;
    }
}
