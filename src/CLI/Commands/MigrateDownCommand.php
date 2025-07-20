<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\MigrationRunner;

/**
 * Migration Down Command
 * 
 * Faz rollback de migrações
 */
class MigrateDownCommand
{
    public function getDescription(): string
    {
        return 'Rollback database migrations';
    }

    public function execute(array $args): void
    {
        $steps = isset($args[0]) ? (int)$args[0] : 1;

        echo "Rolling back {$steps} migration(s)..." . PHP_EOL;

        $pdo = Database::getConnection();
        $migrationsPath = BASE_PATH . '/database/migrations';
        $migrationRunner = new MigrationRunner($migrationsPath, $pdo);
        $migrationRunner->rollback($steps);
    }
}
