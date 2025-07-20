<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\MigrationRunner;

/**
 * Migration Up Command
 * 
 * Executa migrações pendentes
 */
class MigrateUpCommand
{
    public function getDescription(): string
    {
        return 'Run pending database migrations';
    }

    public function execute(array $args): void
    {
        echo "Running migrations..." . PHP_EOL;

        $pdo = Database::getConnection();
        $migrationsPath = BASE_PATH . '/database/migrations';
        $migrationRunner = new MigrationRunner($migrationsPath, $pdo);
        $migrationRunner->migrate();
    }
}
