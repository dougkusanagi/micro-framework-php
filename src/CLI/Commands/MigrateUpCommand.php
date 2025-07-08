<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

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

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            $result = $migration->up();

            if (isset($result['message'])) {
                echo $result['message'] . PHP_EOL;
            } else {
                echo "Migrations completed successfully!" . PHP_EOL;
                echo "Batch: {$result['batch']}" . PHP_EOL;
                echo "Executed: " . count($result['executed']) . " migrations" . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "Error running migrations: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}
