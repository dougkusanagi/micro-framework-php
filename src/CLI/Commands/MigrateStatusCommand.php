<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

/**
 * Migration Status Command
 * 
 * Mostra o status das migrações
 */
class MigrateStatusCommand
{
    public function getDescription(): string
    {
        return 'Show migration status';
    }

    public function execute(array $args): void
    {
        echo "Migration Status:" . PHP_EOL;
        echo str_repeat('-', 50) . PHP_EOL;

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            $status = $migration->status();

            if (empty($status)) {
                echo "No migrations found" . PHP_EOL;
                return;
            }

            foreach ($status as $migration) {
                $statusColor = $migration['status'] === 'Executed' ? '32' : '33'; // green : yellow
                echo sprintf(
                    "  %-40s \033[%sm%s\033[0m",
                    $migration['migration'],
                    $statusColor,
                    $migration['status']
                ) . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "Error getting migration status: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}
