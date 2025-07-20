<?php

namespace GuepardoSys\Core;

use PDO;

class MigrationRunner
{
    private string $migrationsPath;
    private PDO $pdo;

    public function __construct(string $migrationsPath, PDO $pdo)
    {
        $this->migrationsPath = $migrationsPath;
        $this->pdo = $pdo;
    }

    public function migrate(): void
    {
        $this->createMigrationsTable();

        $ranMigrations = $this->getRanMigrations();
        $allMigrations = $this->getAllMigrations();

        $toRun = array_diff($allMigrations, $ranMigrations);

        foreach ($toRun as $migration) {
            $this->runMigration($migration);
        }
    }

    public function rollback(int $steps = 1): void
    {
        $this->createMigrationsTable();

        $ranMigrations = $this->getRanMigrations();
        $toRollback = array_slice(array_reverse($ranMigrations), 0, $steps);

        foreach ($toRollback as $migration) {
            if ($migration === '001_create_migrations_table') {
                continue;
            }
            $this->rollbackMigration($migration);
        }
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                migration VARCHAR(255) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        SQL);
    }

    private function getRanMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getAllMigrations(): array
    {
        $files = scandir($this->migrationsPath);
        $migrations = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        sort($migrations);

        return $migrations;
    }

    private function runMigration(string $migration): void
    {
        require_once $this->migrationsPath . '/' . $migration . '.php';

        $className = $this->getClassNameFromFileName($migration);
        $migrationInstance = new $className($this->pdo);
        $migrationInstance->up();

        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migration]);

        echo "✓ Migrated: {$migration}" . PHP_EOL;
    }

    private function rollbackMigration(string $migration): void
    {
        require_once $this->migrationsPath . '/' . $migration . '.php';

        $className = $this->getClassNameFromFileName($migration);
        $migrationInstance = new $className($this->pdo);
        $migrationInstance->down();

        $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);

        echo "✓ Rolled back: {$migration}" . PHP_EOL;
    }

    private function getClassNameFromFileName(string $fileName): string
    {
        $parts = explode('_', $fileName);
        $name = implode('', array_map('ucfirst', array_slice($parts, 1)));
        return $name;
    }
}