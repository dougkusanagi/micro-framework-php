<?php

use GuepardoSys\Core\Database;

describe('Database', function () {
    beforeEach(function () {
        // Set test database configuration
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        // Reset database instance
        $reflection = new ReflectionClass(Database::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue(null, null);
    });

    it('can create database connection', function () {
        $pdo = Database::getConnection();

        expect($pdo)->toBeInstanceOf(PDO::class);
    });

    it('returns same instance on multiple calls (singleton)', function () {
        $pdo1 = Database::getConnection();
        $pdo2 = Database::getConnection();

        expect($pdo1)->toBe($pdo2);
    });

    it('can execute queries', function () {
        $pdo = Database::getConnection();

        // Create test table
        $pdo->exec('CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)');

        // Insert test data
        $stmt = $pdo->prepare('INSERT INTO test_table (name) VALUES (?)');
        $result = $stmt->execute(['Test Name']);

        expect($result)->toBeTrue();
    });

    it('can select data', function () {
        $pdo = Database::getConnection();

        // Create and populate test table
        $pdo->exec('CREATE TABLE test_select (id INTEGER PRIMARY KEY, name TEXT)');
        $pdo->exec("INSERT INTO test_select (name) VALUES ('John'), ('Jane')");

        // Select data
        $stmt = $pdo->query('SELECT * FROM test_select');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        expect(count($results))->toBe(2);
        expect($results[0]['name'])->toBe('John');
        expect($results[1]['name'])->toBe('Jane');
    });

    it('handles prepared statements correctly', function () {
        $pdo = Database::getConnection();

        $pdo->exec('CREATE TABLE test_prepared (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

        $stmt = $pdo->prepare('INSERT INTO test_prepared (name, email) VALUES (?, ?)');
        $stmt->execute(['John Doe', 'john@example.com']);

        $stmt = $pdo->prepare('SELECT * FROM test_prepared WHERE name = ?');
        $stmt->execute(['John Doe']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        expect($result['name'])->toBe('John Doe');
        expect($result['email'])->toBe('john@example.com');
    });

    it('can handle transactions', function () {
        $pdo = Database::getConnection();

        $pdo->exec('CREATE TABLE test_transaction (id INTEGER PRIMARY KEY, name TEXT)');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO test_transaction (name) VALUES (?)');
        $stmt->execute(['Transaction Test']);

        $pdo->commit();

        $stmt = $pdo->query('SELECT COUNT(*) as count FROM test_transaction');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        expect($result['count'])->toBe(1);
    });

    it('can rollback transactions', function () {
        $pdo = Database::getConnection();

        $pdo->exec('CREATE TABLE test_rollback (id INTEGER PRIMARY KEY, name TEXT)');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO test_rollback (name) VALUES (?)');
        $stmt->execute(['Rollback Test']);

        $pdo->rollback();

        $stmt = $pdo->query('SELECT COUNT(*) as count FROM test_rollback');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        expect($result['count'])->toBe(0);
    });

    it('throws exception for invalid configuration', function () {
        $_ENV['DB_CONNECTION'] = 'invalid_driver';
        Database::$instance = null;

        expect(function () {
            Database::getConnection();
        })->toThrow(Exception::class);
    });

    it('can get database configuration', function () {
        $config = Database::getConfig();

        expect($config)->toBeArray();
        expect($config['default'])->toBe('sqlite');
    });

    it('can create database if not exists', function () {
        // This test is mainly for MySQL/PostgreSQL
        // For SQLite in memory, database is always created
        expect(Database::createDatabaseIfNotExists())->toBeTrue();
    });
});
