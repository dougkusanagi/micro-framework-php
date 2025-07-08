<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Make Migration Command
 * 
 * Cria um novo arquivo de migração
 */
class MakeMigrationCommand
{
    public function getDescription(): string
    {
        return 'Create a new migration file';
    }

    public function execute(array $args): void
    {
        if (empty($args)) {
            echo "Error: Migration name is required" . PHP_EOL;
            echo "Usage: php guepardo make:migration create_table_name" . PHP_EOL;
            return;
        }

        $migrationName = $args[0];
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$migrationName}.sql";
        $filePath = BASE_PATH . "/database/migrations/{$fileName}";

        if (file_exists($filePath)) {
            echo "Error: Migration already exists: {$fileName}" . PHP_EOL;
            return;
        }

        $template = $this->getMigrationTemplate($migrationName);

        if (file_put_contents($filePath, $template)) {
            echo "✓ Migration created: {$fileName}" . PHP_EOL;
        } else {
            echo "✗ Error creating migration file" . PHP_EOL;
        }
    }

    private function getMigrationTemplate(string $migrationName): string
    {
        // Extract table name from migration name
        $tableName = $this->extractTableName($migrationName);

        if (strpos($migrationName, 'create_') === 0) {
            return $this->getCreateTableTemplate($tableName);
        } elseif (strpos($migrationName, 'add_') === 0) {
            return $this->getAddColumnTemplate($tableName);
        } elseif (strpos($migrationName, 'drop_') === 0) {
            return $this->getDropTableTemplate($tableName);
        } else {
            return $this->getGenericTemplate();
        }
    }

    private function extractTableName(string $migrationName): string
    {
        // Remove common prefixes and suffixes
        $tableName = str_replace(['create_', 'add_', 'drop_', '_table'], '', $migrationName);
        return $tableName;
    }

    private function getCreateTableTemplate(string $tableName): string
    {
        return "-- Create {$tableName} table migration
CREATE TABLE IF NOT EXISTS {$tableName} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Add your columns here
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";
    }

    private function getAddColumnTemplate(string $tableName): string
    {
        return "-- Add column to {$tableName} table migration
ALTER TABLE {$tableName}
ADD COLUMN column_name VARCHAR(255) NOT NULL;
";
    }

    private function getDropTableTemplate(string $tableName): string
    {
        return "-- Drop {$tableName} table migration
DROP TABLE IF EXISTS {$tableName};
";
    }

    private function getGenericTemplate(): string
    {
        return "-- Migration file
-- Add your SQL commands here

";
    }
}
