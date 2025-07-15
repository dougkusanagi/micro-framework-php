<?php

use GuepardoSys\Core\Migration;
use GuepardoSys\Core\Database;

beforeEach(function () {
    // Set up SQLite in-memory database
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_ENV['DB_DATABASE'] = ':memory:';
    $reflection = new ReflectionClass(Database::class);
    $property = $reflection->getProperty('connection');
    $property->setAccessible(true);
    $property->setValue(null, null);

    $this->migration = new Migration();
    $this->testMigrationsDir = __DIR__ . '/../../temp_migrations';

    if (!is_dir($this->testMigrationsDir)) {
        mkdir($this->testMigrationsDir, 0755, true);
    }
});

afterEach(function () {
    // Clean up test migration files
    $testMigrationsDir = $this->testMigrationsDir ?? __DIR__ . '/../../temp_migrations';
    if (is_dir($testMigrationsDir)) {
        $files = glob($testMigrationsDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($testMigrationsDir);
    }
});

describe('Migration', function () {

    it('can create migrations table', function () {
        $this->migration->createMigrationsTable();

        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
        $result = $stmt->fetch();

        expect($result)->not->toBeFalse();
        expect($result['name'])->toBe('migrations');
    });

    it('can check if migration exists', function () {
        $this->migration->createMigrationsTable();

        // Migration should not exist initially
        expect($this->migration->migrationExists('001_test_migration'))->toBeFalse();

        // Add a migration record
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['001_test_migration', 1]);

        expect($this->migration->migrationExists('001_test_migration'))->toBeTrue();
    });

    it('can get pending migrations', function () {
        // Create test migration files
        file_put_contents($this->testMigrationsDir . '/001_create_users_table.sql', 'CREATE TABLE users (id INTEGER);');
        file_put_contents($this->testMigrationsDir . '/002_create_posts_table.sql', 'CREATE TABLE posts (id INTEGER);');

        $this->migration->createMigrationsTable();

        // Mark one migration as run
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['001_create_users_table', 1]);

        $pending = $this->migration->getPendingMigrations($this->testMigrationsDir);

        expect($pending)->toBeArray();
        expect(count($pending))->toBe(1);
        expect($pending[0])->toBe('002_create_posts_table.sql');
    });

    it('can run migration file', function () {
        $migrationContent = 'CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT);';
        $migrationFile = $this->testMigrationsDir . '/001_create_test_table.sql';
        file_put_contents($migrationFile, $migrationContent);

        $this->migration->createMigrationsTable();
        $result = $this->migration->runMigration($migrationFile, 1);

        expect($result)->toBeTrue();

        // Check if table was created
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='test_table'");
        $tableExists = $stmt->fetch();

        expect($tableExists)->not->toBeFalse();

        // Check if migration was recorded
        expect($this->migration->migrationExists('001_create_test_table'))->toBeTrue();
    });

    it('can run all pending migrations', function () {
        // Create test migration files
        $migration1 = 'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT);';
        $migration2 = 'CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT);';

        file_put_contents($this->testMigrationsDir . '/001_create_users_table.sql', $migration1);
        file_put_contents($this->testMigrationsDir . '/002_create_posts_table.sql', $migration2);

        $result = $this->migration->migrate($this->testMigrationsDir);

        expect($result)->toBeTrue();

        // Check if both tables were created
        $pdo = Database::getConnection();

        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        expect($stmt->fetch())->not->toBeFalse();

        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts'");
        expect($stmt->fetch())->not->toBeFalse();

        // Check if migrations were recorded
        expect($this->migration->migrationExists('001_create_users_table'))->toBeTrue();
        expect($this->migration->migrationExists('002_create_posts_table'))->toBeTrue();
    });

    it('can rollback migrations', function () {
        // Create migration with rollback
        $migrationContent = "
-- Up
CREATE TABLE rollback_test (id INTEGER PRIMARY KEY, name TEXT);

-- Down
DROP TABLE rollback_test;
        ";

        $migrationFile = $this->testMigrationsDir . '/001_rollback_test.sql';
        file_put_contents($migrationFile, $migrationContent);

        // Run migration
        $this->migration->migrate($this->testMigrationsDir);

        // Verify table exists
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='rollback_test'");
        expect($stmt->fetch())->not->toBeFalse();

        // Rollback
        $result = $this->migration->rollback($this->testMigrationsDir);
        expect($result)->toBeTrue();

        // Verify table no longer exists
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='rollback_test'");
        expect($stmt->fetch())->toBeFalse();
    });

    it('can get migration status', function () {
        // Create test migrations
        file_put_contents($this->testMigrationsDir . '/001_migration_one.sql', 'CREATE TABLE one (id INTEGER);');
        file_put_contents($this->testMigrationsDir . '/002_migration_two.sql', 'CREATE TABLE two (id INTEGER);');

        $this->migration->createMigrationsTable();

        // Run first migration only
        $this->migration->runMigration($this->testMigrationsDir . '/001_migration_one.sql', 1);

        $status = $this->migration->getStatus($this->testMigrationsDir);

        expect($status)->toBeArray();
        expect(count($status))->toBe(2);

        // First migration should be run
        expect($status[0]['migration'])->toBe('001_migration_one.sql');
        expect($status[0]['status'])->toBe('Ran');

        // Second migration should be pending
        expect($status[1]['migration'])->toBe('002_migration_two.sql');
        expect($status[1]['status'])->toBe('Pending');
    });

    it('can refresh migrations', function () {
        // Create test migration
        $migrationContent = "
-- Up
CREATE TABLE refresh_test (id INTEGER PRIMARY KEY);

-- Down
DROP TABLE refresh_test;
        ";

        file_put_contents($this->testMigrationsDir . '/001_refresh_test.sql', $migrationContent);

        // Run migration
        $this->migration->migrate($this->testMigrationsDir);

        // Refresh (rollback all and re-run)
        $result = $this->migration->refresh($this->testMigrationsDir);
        expect($result)->toBeTrue();

        // Table should still exist after refresh
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='refresh_test'");
        expect($stmt->fetch())->not->toBeFalse();
    });

    it('handles migration file parsing', function () {
        $migrationContent = "
-- This is a comment
-- Up
CREATE TABLE parse_test (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL
);
INSERT INTO parse_test (name) VALUES ('test');

-- Down
DROP TABLE parse_test;
        ";

        $migrationFile = $this->testMigrationsDir . '/001_parse_test.sql';
        file_put_contents($migrationFile, $migrationContent);

        // Test parsing up section
        $upStatements = $this->migration->parseUpStatements($migrationFile);
        expect($upStatements)->toBeArray();
        expect(count($upStatements))->toBeGreaterThan(0);

        // Test parsing down section
        $downStatements = $this->migration->parseDownStatements($migrationFile);
        expect($downStatements)->toBeArray();
        expect(count($downStatements))->toBeGreaterThan(0);
    });

    it('can get next batch number', function () {
        $this->migration->createMigrationsTable();

        // Should start with batch 1
        expect($this->migration->getNextBatchNumber())->toBe(1);

        // Add some migrations
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['001_test', 1]);
        $stmt->execute(['002_test', 1]);
        $stmt->execute(['003_test', 2]);

        // Next batch should be 3
        expect($this->migration->getNextBatchNumber())->toBe(3);
    });

    it('validates migration file format', function () {
        // Valid migration file
        $validContent = "-- Up\nCREATE TABLE valid (id INTEGER);\n-- Down\nDROP TABLE valid;";
        $validFile = $this->testMigrationsDir . '/001_valid.sql';
        file_put_contents($validFile, $validContent);

        expect($this->migration->validateMigrationFile($validFile))->toBeTrue();

        // Invalid migration file (missing sections)
        $invalidContent = "CREATE TABLE invalid (id INTEGER);";
        $invalidFile = $this->testMigrationsDir . '/002_invalid.sql';
        file_put_contents($invalidFile, $invalidContent);

        expect($this->migration->validateMigrationFile($invalidFile))->toBeFalse();
    });
});
