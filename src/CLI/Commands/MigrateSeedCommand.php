<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Database;
use GuepardoSys\Core\Migration;

/**
 * Migration Seed Command
 * 
 * Executa seeds do banco de dados
 */
class MigrateSeedCommand
{
    public function getDescription(): string
    {
        return 'Run database seeds';
    }

    public function execute(array $args): void
    {
        echo "Running database seeds..." . PHP_EOL;

        try {
            $pdo = Database::getConnection();
            $migration = new Migration($pdo);

            $result = $migration->seed();

            if (empty($result)) {
                echo "No seed files found" . PHP_EOL;
            } else {
                echo "Seeds completed successfully!" . PHP_EOL;
                echo "Executed: " . count($result) . " seed file(s)" . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "Error running seeds: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}
