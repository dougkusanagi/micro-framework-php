<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

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

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            $result = $migration->down($steps);

            if (empty($result)) {
                echo "No migrations to rollback" . PHP_EOL;
            } else {
                echo "Rollback completed successfully!" . PHP_EOL;
                echo "Rolled back: " . count($result) . " migration(s)" . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "Error rolling back migrations: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}
