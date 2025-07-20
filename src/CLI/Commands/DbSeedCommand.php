<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\SeederRunner;

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
        $this->parseArguments($args);

        $pdo = Database::getConnection();
        $seedsPath = BASE_PATH . '/database/seeds';
        $seederRunner = new SeederRunner($seedsPath, $pdo);

        echo "Running database seeds..." . PHP_EOL;
        $seederRunner->run('DatabaseSeeder');
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
