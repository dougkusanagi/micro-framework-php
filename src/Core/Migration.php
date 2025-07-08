<?php

namespace GuepardoSys\Core;

use PDO;
use Exception;

/**
 * Migration System
 * 
 * Gerencia a execução de migrações do banco de dados
 */
class Migration
{
    private PDO $pdo;
    private string $migrationsPath;
    private string $seedsPath;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = BASE_PATH . '/database/migrations';
        $this->seedsPath = BASE_PATH . '/database/seeds';

        $this->createMigrationsTable();
    }

    /**
     * Create migrations control table
     */
    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->pdo->exec($sql);
    }

    /**
     * Run pending migrations
     */
    public function up(): array
    {
        $executed = [];
        $pendingMigrations = $this->getPendingMigrations();

        if (empty($pendingMigrations)) {
            return ['message' => 'No pending migrations'];
        }

        $batch = $this->getNextBatch();

        foreach ($pendingMigrations as $migration) {
            try {
                $this->executeMigration($migration);
                $this->recordMigration($migration, $batch);
                $executed[] = $migration;

                echo "✓ Migrated: {$migration}" . PHP_EOL;
            } catch (Exception $e) {
                echo "✗ Failed to migrate {$migration}: " . $e->getMessage() . PHP_EOL;
                break;
            }
        }

        return [
            'executed' => $executed,
            'batch' => $batch
        ];
    }

    /**
     * Rollback migrations
     */
    public function down(int $steps = 1): array
    {
        $rolledBack = [];
        $migrationsToRollback = $this->getMigrationsToRollback($steps);

        foreach ($migrationsToRollback as $migration) {
            try {
                $this->rollbackMigration($migration);
                $this->removeMigrationRecord($migration);
                $rolledBack[] = $migration;

                echo "✓ Rolled back: {$migration}" . PHP_EOL;
            } catch (Exception $e) {
                echo "✗ Failed to rollback {$migration}: " . $e->getMessage() . PHP_EOL;
                break;
            }
        }

        return $rolledBack;
    }

    /**
     * Run database seeds
     */
    public function seed(): array
    {
        $executed = [];
        $seedFiles = $this->getSeedFiles();

        foreach ($seedFiles as $seedFile) {
            try {
                $this->executeSeed($seedFile);
                $executed[] = $seedFile;

                echo "✓ Seeded: {$seedFile}" . PHP_EOL;
            } catch (Exception $e) {
                echo "✗ Failed to seed {$seedFile}: " . $e->getMessage() . PHP_EOL;
                break;
            }
        }

        return $executed;
    }

    /**
     * Get pending migrations
     */
    private function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();

        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * Get all migration files
     */
    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        $migrations = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get executed migrations
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get migrations to rollback
     */
    private function getMigrationsToRollback(int $steps): array
    {
        $stmt = $this->pdo->prepare("
            SELECT migration 
            FROM migrations 
            ORDER BY id DESC 
            LIMIT ?
        ");
        $stmt->execute([$steps]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Execute migration file
     */
    private function executeMigration(string $migration): void
    {
        $filePath = $this->migrationsPath . '/' . $migration . '.sql';

        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$filePath}");
        }

        $sql = file_get_contents($filePath);
        $this->pdo->exec($sql);
    }

    /**
     * Rollback migration (basic implementation)
     */
    private function rollbackMigration(string $migration): void
    {
        // For now, we'll implement basic rollback
        // In a more complete system, we'd have separate UP and DOWN files
        echo "Warning: Rollback not implemented for {$migration}" . PHP_EOL;
    }

    /**
     * Execute seed file
     */
    private function executeSeed(string $seedFile): void
    {
        $filePath = $this->seedsPath . '/' . $seedFile;

        if (!file_exists($filePath)) {
            throw new Exception("Seed file not found: {$filePath}");
        }

        $sql = file_get_contents($filePath);
        $this->pdo->exec($sql);
    }

    /**
     * Get seed files
     */
    private function getSeedFiles(): array
    {
        if (!is_dir($this->seedsPath)) {
            return [];
        }

        $files = scandir($this->seedsPath);
        $seeds = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $seeds[] = $file;
            }
        }

        sort($seeds);
        return $seeds;
    }

    /**
     * Record migration execution
     */
    private function recordMigration(string $migration, int $batch): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO migrations (migration, batch) 
            VALUES (?, ?)
        ");
        $stmt->execute([$migration, $batch]);
    }

    /**
     * Remove migration record
     */
    private function removeMigrationRecord(string $migration): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
    }

    /**
     * Get next batch number
     */
    private function getNextBatch(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        $maxBatch = $stmt->fetchColumn();

        return $maxBatch ? $maxBatch + 1 : 1;
    }

    /**
     * Get migration status
     */
    public function status(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();

        $status = [];
        foreach ($allMigrations as $migration) {
            $status[] = [
                'migration' => $migration,
                'status' => in_array($migration, $executedMigrations) ? 'Executed' : 'Pending'
            ];
        }

        return $status;
    }

    /**
     * Check if database exists
     */
    public static function databaseExists(): bool
    {
        try {
            $config = config('database.connections.' . config('database.default'));
            $dbName = $config['database'];

            // Create connection without database name
            $tempConfig = $config;
            unset($tempConfig['database']);

            $tempPdo = self::createConnectionFromConfig($tempConfig);

            // Check if database exists
            $stmt = $tempPdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$dbName]);

            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create database if it doesn't exist
     */
    public static function createDatabase(): bool
    {
        try {
            $config = config('database.connections.' . config('database.default'));
            $dbName = $config['database'];

            // Create connection without database name
            $tempConfig = $config;
            unset($tempConfig['database']);

            $tempPdo = self::createConnectionFromConfig($tempConfig);

            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $tempPdo->exec($sql);

            return true;
        } catch (Exception $e) {
            echo "Error creating database: " . $e->getMessage() . PHP_EOL;
            return false;
        }
    }

    /**
     * Create PDO connection from config
     */
    private static function createConnectionFromConfig(array $config): PDO
    {
        $driver = $config['driver'];
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'] ?? null;
        $username = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'] ?? 'utf8mb4';

        if ($driver === 'mysql') {
            $dsn = "mysql:host={$host};port={$port}";
            if ($database) {
                $dsn .= ";dbname={$database}";
            }
            $dsn .= ";charset={$charset}";
        } elseif ($driver === 'pgsql') {
            $dsn = "pgsql:host={$host};port={$port}";
            if ($database) {
                $dsn .= ";dbname={$database}";
            }
        } else {
            throw new Exception("Unsupported database driver: {$driver}");
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $username, $password, $options);
    }
}
