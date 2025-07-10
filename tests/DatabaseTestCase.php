<?php

namespace Tests;

use GuepardoSys\Core\Database;
use PDO;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDatabase();
    }

    protected function setupTestDatabase(): void
    {
        // Configure test database
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        // Clear any existing database instance
        $this->clearDatabaseSingleton();

        // Get fresh connection
        $this->pdo = Database::getConnection();

        // Create basic test tables
        $this->createTestTables();
    }

    protected function clearDatabaseSingleton(): void
    {
        $reflection = new \ReflectionClass(Database::class);
        if ($reflection->hasProperty('connection')) {
            $property = $reflection->getProperty('connection');
            $property->setAccessible(true);
            $property->setValue(null, null);
        }
    }

    protected function createTestTables(): void
    {
        // Users table for auth tests
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Products table for CRUD tests
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Migrations table for migration tests
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL,
                batch INTEGER NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function seedTestData(): void
    {
        // Seed users
        $this->pdo->exec("
            INSERT INTO users (name, email, password) VALUES 
            ('John Doe', 'john@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "'),
            ('Jane Smith', 'jane@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "')
        ");

        // Seed products
        $this->pdo->exec("
            INSERT INTO products (name, price, description) VALUES 
            ('Product 1', 19.99, 'Description 1'),
            ('Product 2', 29.99, 'Description 2')
        ");
    }

    protected function clearTestData(): void
    {
        $this->pdo->exec("DELETE FROM users");
        $this->pdo->exec("DELETE FROM products");
        $this->pdo->exec("DELETE FROM migrations");
    }

    protected function tearDown(): void
    {
        // Clear test data
        $this->clearTestData();

        parent::tearDown();
    }

    /**
     * Assert that a table exists
     */
    protected function assertTableExists(string $tableName): void
    {
        $stmt = $this->pdo->prepare("
            SELECT name FROM sqlite_master 
            WHERE type='table' AND name=?
        ");
        $stmt->execute([$tableName]);

        $this->assertNotFalse(
            $stmt->fetch(),
            "Table '{$tableName}' does not exist"
        );
    }

    /**
     * Assert that a table has specific columns
     */
    protected function assertTableHasColumns(string $tableName, array $columns): void
    {
        $stmt = $this->pdo->prepare("PRAGMA table_info({$tableName})");
        $stmt->execute();
        $tableInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $existingColumns = array_column($tableInfo, 'name');

        foreach ($columns as $column) {
            $this->assertContains(
                $column,
                $existingColumns,
                "Column '{$column}' does not exist in table '{$tableName}'"
            );
        }
    }

    /**
     * Get row count for a table
     */
    protected function getRowCount(string $tableName): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$tableName}");
        return (int) $stmt->fetchColumn();
    }
}
