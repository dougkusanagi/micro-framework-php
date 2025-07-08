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
        if (self::$connection === null) {
            self::$connection = self::createConnection($connectionName);
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
        $connectionName = $connectionName ?? config('database.default');
        $config = config("database.connections.{$connectionName}");

        if (!$config) {
            throw new PDOException("Database connection [{$connectionName}] not configured.");
        }

        $dsn = self::buildDsn($config);
        $options = $config['options'] ?? [];

        try {
            return new PDO($dsn, $config['username'], $config['password'], $options);
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
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];

        switch ($driver) {
            case 'mysql':
                $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                if (isset($config['charset'])) {
                    $dsn .= ";charset={$config['charset']}";
                }
                break;

            case 'pgsql':
                $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
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
