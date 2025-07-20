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
     * Get all migration files (.php and .sql)
     */
    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        $migrations = [];

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'sql' || $ext === 'php') {
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
        $steps = (int) $steps; // Ensure steps is always an integer for LIMIT
        $sql = "
            SELECT migration 
            FROM migrations 
            ORDER BY id DESC 
            LIMIT $steps
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Execute migration file (.php or .sql)
     */
    private function executeMigration(string $migration): void
    {
        $phpPath = $this->migrationsPath . '/' . $migration . '.php';
        $sqlPath = $this->migrationsPath . '/' . $migration . '.sql';

        if (file_exists($phpPath)) {
            require_once $phpPath;
            $className = $this->getMigrationClassName($migration);
            if (!class_exists($className)) {
                throw new Exception("Migration class not found: {$className}");
            }
            $instance = new $className();
            if (!method_exists($instance, 'up')) {
                throw new Exception("Migration class missing up() method: {$className}");
            }
            $instance->up($this->pdo);
        } elseif (file_exists($sqlPath)) {
            $sql = file_get_contents($sqlPath);
            $this->pdo->exec($sql);
        } else {
            throw new Exception("Migration file not found: {$migration}");
        }
    }

    /**
     * Rollback migration (.php or .sql)
     */
    private function rollbackMigration(string $migration): void
    {
        $phpPath = $this->migrationsPath . '/' . $migration . '.php';
        $sqlPath = $this->migrationsPath . '/' . $migration . '.sql';

        if (file_exists($phpPath)) {
            require_once $phpPath;
            $className = $this->getMigrationClassName($migration);
            if (!class_exists($className)) {
                throw new Exception("Migration class not found: {$className}");
            }
            $instance = new $className();
            if (!method_exists($instance, 'down')) {
                throw new Exception("Migration class missing down() method: {$className}");
            }
            $instance->down($this->pdo);
        } elseif (file_exists($sqlPath)) {
            // No automatic rollback for SQL migrations
            throw new Exception("Rollback not supported for SQL migration: {$migration}");
        } else {
            throw new Exception("Migration file not found: {$migration}");
        }
    }

    /**
     * Get migration class name from file name
     */
    private function getMigrationClassName(string $migration): string
    {
        // Example: 001_create_migrations_table => CreateMigrationsTable
        $parts = explode('_', $migration, 2);
        $name = isset($parts[1]) ? $parts[1] : $parts[0];
        $name = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));
        return $name;
    }

    /**
     * Execute seed file (PHP with run())
     */
    private function executeSeed(string $seedFile): void
    {
        $filePath = $this->seedsPath . '/' . $seedFile . '.php';
        if (!file_exists($filePath)) {
            throw new Exception("Seed file not found: {$filePath}");
        }
        require_once $filePath;
        $className = $this->getSeedClassName($seedFile);
        if (!class_exists($className)) {
            throw new Exception("Seed class not found: {$className}");
        }
        $instance = new $className();
        if (!method_exists($instance, 'run')) {
            throw new Exception("Seed class missing run() method: {$className}");
        }
        $instance->run($this->pdo);
    }

    /**
     * Get seed files (PHP only)
     */
    private function getSeedFiles(): array
    {
        if (!is_dir($this->seedsPath)) {
            return [];
        }
        $files = scandir($this->seedsPath);
        $seeds = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $seeds[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        sort($seeds);
        return $seeds;
    }

    /**
     * Get seed class name from file name
     */
    private function getSeedClassName(string $seedFile): string
    {
        // Example: UsersSeeder => UsersSeeder
        // Example: 001_users_seeder => UsersSeeder
        $parts = explode('_', $seedFile, 2);
        $name = isset($parts[1]) ? $parts[1] : $parts[0];
        $name = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));
        if (stripos($name, 'seeder') === false) {
            $name .= 'Seeder';
        }
        return $name;
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

class MigrationRunner
{
    protected $migrationsPath;
    protected $pdo;
    protected $table = 'migrations';

    public function __construct($migrationsPath, $pdo)
    {
        $this->migrationsPath = $migrationsPath;
        $this->pdo = $pdo;
        $this->ensureMigrationsTable();
    }

    protected function ensureMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS {$this->table} (id INTEGER PRIMARY KEY AUTOINCREMENT, migration VARCHAR(255), batch INTEGER, migrated_at DATETIME)");
    }

    public function getAppliedMigrations()
    {
        $stmt = $this->pdo->query("SELECT migration FROM {$this->table}");
        return $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
    }

    public function discoverMigrations()
    {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }

    public function migrate()
    {
        $applied = $this->getAppliedMigrations();
        $files = $this->discoverMigrations();
        $batch = $this->getNextBatchNumber();
        foreach ($files as $file) {
            $class = $this->getClassNameFromFile($file);
            if (!in_array($class, $applied)) {
                require_once $file;
                $migration = new $class();
                $migration->up();
                $this->recordMigration($class, $batch);
                echo "Migrated: $class\n";
            }
        }
    }

    public function rollback($steps = 1)
    {
        $applied = $this->getAppliedMigrationsWithBatch();
        $batches = array_unique(array_column($applied, 'batch'));
        rsort($batches);
        $batches = array_slice($batches, 0, $steps);
        foreach ($applied as $row) {
            if (in_array($row['batch'], $batches)) {
                $file = $this->migrationsPath . '/' . $row['migration'] . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    $migration = new $row['migration']();
                    $migration->down();
                    $this->removeMigration($row['migration']);
                    echo "Rolled back: {$row['migration']}\n";
                }
            }
        }
    }

    protected function getNextBatchNumber()
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM {$this->table}");
        $max = $stmt ? $stmt->fetchColumn() : 0;
        return $max + 1;
    }

    protected function getAppliedMigrationsWithBatch()
    {
        $stmt = $this->pdo->query("SELECT migration, batch FROM {$this->table} ORDER BY batch DESC, id DESC");
        return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
    }

    protected function recordMigration($class, $batch)
    {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (migration, batch, migrated_at) VALUES (?, ?, ?)");
        $stmt->execute([$class, $batch, date('Y-m-d H:i:s')]);
    }

    protected function removeMigration($class)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE migration = ?");
        $stmt->execute([$class]);
    }

    protected function getClassNameFromFile($file)
    {
        // Assumes class name matches file name (without .php)
        return pathinfo($file, PATHINFO_FILENAME);
    }
}
