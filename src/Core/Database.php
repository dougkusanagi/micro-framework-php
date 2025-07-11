<?php

namespace GuepardoSys\Core;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 */
class Database
{
    private static ?PDO $connection = null;
    public static ?PDO $instance = null; // For testing compatibility
    private static array $config = [];

    /**
     * Get database connection
     *
     * @param string|null $connectionName
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(?string $connectionName = null): PDO
    {
        if (self::$connection === null || self::$instance === null) {
            self::$connection = self::createConnection($connectionName);
            self::$instance = self::$connection; // For compatibility
        }

        return self::$connection;
    }

    /**
     * Create new database connection
     *
     * @param string|null $connectionName
     * @return PDO
     * @throws PDOException
     */
    private static function createConnection(?string $connectionName = null): PDO
    {
        // Check for environment variables (for testing)
        if (isset($_ENV['DB_CONNECTION'])) {
            $config = [
                'driver' => $_ENV['DB_CONNECTION'],
                'database' => $_ENV['DB_DATABASE'] ?? ':memory:',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'username' => $_ENV['DB_USERNAME'] ?? '',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            ];
        } else {
            // Use config function if available
            $connectionName = $connectionName ?? (function_exists('config') ? config('database.default') : 'mysql');
            $config = function_exists('config') ? config("database.connections.{$connectionName}") : null;

            if (!$config) {
                throw new PDOException("Database connection [{$connectionName}] not configured.");
            }
        }

        $dsn = self::buildDsn($config);
        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            return new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', $options);
        } catch (PDOException $e) {
            throw new PDOException("Could not connect to database: " . $e->getMessage());
        }
    }

    /**
     * Build DSN string from configuration
     *
     * @param array $config
     * @return string
     */
    private static function buildDsn(array $config): string
    {
        $driver = $config['driver'];
        $database = $config['database'];

        switch ($driver) {
            case 'mysql':
                $host = $config['host'];
                $port = $config['port'];
                $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                if (isset($config['charset'])) {
                    $dsn .= ";charset={$config['charset']}";
                }
                break;

            case 'pgsql':
                $host = $config['host'];
                $port = $config['port'];
                $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
                break;

            case 'sqlite':
                $dsn = "sqlite:{$database}";
                break;

            default:
                throw new PDOException("Unsupported database driver: {$driver}");
        }

        return $dsn;
    }

    /**
     * Close database connection
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
        self::$instance = null;
    }

    /**
     * Get database configuration
     */
    public static function getConfig(): array
    {
        if (isset($_ENV['DB_CONNECTION'])) {
            return [
                'default' => $_ENV['DB_CONNECTION'],
                'connections' => [
                    $_ENV['DB_CONNECTION'] => [
                        'driver' => $_ENV['DB_CONNECTION'],
                        'database' => $_ENV['DB_DATABASE'] ?? ':memory:',
                        'host' => $_ENV['DB_HOST'] ?? 'localhost',
                        'port' => $_ENV['DB_PORT'] ?? '3306',
                        'username' => $_ENV['DB_USERNAME'] ?? '',
                        'password' => $_ENV['DB_PASSWORD'] ?? '',
                        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
                    ]
                ]
            ];
        }

        return [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:'
                ]
            ]
        ];
    }

    /**
     * Create database if not exists
     */
    public static function createDatabaseIfNotExists(): bool
    {
        // For SQLite in memory, database is always created
        // For other databases, this would check and create if needed
        return true;
    }

    /**
     * Test database connection
     *
     * @param string|null $connectionName
     * @return bool
     */
    public static function testConnection(?string $connectionName = null): bool
    {
        try {
            $connection = self::createConnection($connectionName);
            return $connection->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
