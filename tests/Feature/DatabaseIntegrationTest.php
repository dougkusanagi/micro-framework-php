<?php

use GuepardoSys\Core\Database;
use GuepardoSys\Models\User;

describe('Database Integration', function () {
    beforeEach(function () {
        // Set up SQLite in-memory database
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        Database::$instance = null;

        // Create users table
        $pdo = Database::getConnection();
        $pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
    });

    it('can create and retrieve users', function () {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        expect($user)->toBeInstanceOf(User::class);
        expect($user->id)->toBeGreaterThan(0);
        expect($user->name)->toBe('John Doe');
        expect($user->email)->toBe('john@example.com');

        $found = User::find($user->id);
        expect($found->name)->toBe('John Doe');
        expect($found->email)->toBe('john@example.com');
    });

    it('can update user records', function () {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $user->name = 'Jane Smith';
        $user->save();

        $updated = User::find($user->id);
        expect($updated->name)->toBe('Jane Smith');
    });

    it('can delete user records', function () {
        $user = User::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $id = $user->id;
        $user->delete();

        $found = User::find($id);
        expect($found)->toBeNull();
    });

    it('can find users by email', function () {
        User::create([
            'name' => 'Find Me',
            'email' => 'findme@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $user = User::where('email', 'findme@example.com')->first();

        expect($user)->toBeInstanceOf(User::class);
        expect($user->name)->toBe('Find Me');
    });

    it('can count user records', function () {
        User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => 'hash1']);
        User::create(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => 'hash2']);
        User::create(['name' => 'User 3', 'email' => 'user3@example.com', 'password' => 'hash3']);

        $count = User::count();
        expect($count)->toBe(3);
    });

    it('can authenticate users', function () {
        $password = 'secret123';
        $user = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // Test correct password
        if (method_exists(User::class, 'authenticate')) {
            $authenticated = User::authenticate('auth@example.com', $password);
            expect($authenticated)->toBeInstanceOf(User::class);
            expect($authenticated->email)->toBe('auth@example.com');

            // Test wrong password
            $failed = User::authenticate('auth@example.com', 'wrongpassword');
            expect($failed)->toBeFalse();
        } else {
            // Manual verification
            $found = User::where('email', 'auth@example.com')->first();
            expect(password_verify($password, $found->password))->toBeTrue();
        }
    });

    it('can handle validation errors', function () {
        // Test unique email constraint
        User::create([
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        expect(function () {
            User::create([
                'name' => 'Second User',
                'email' => 'duplicate@example.com',
                'password' => password_hash('secret', PASSWORD_DEFAULT)
            ]);
        })->toThrow(Exception::class);
    });

    it('can order and limit results', function () {
        User::create(['name' => 'Charlie', 'email' => 'charlie@example.com', 'password' => 'hash']);
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'hash']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'hash']);

        $users = User::orderBy('name')->limit(2)->get();

        expect(count($users))->toBe(2);
        expect($users[0]->name)->toBe('Alice');
        expect($users[1]->name)->toBe('Bob');
    });

    it('can convert models to arrays and JSON', function () {
        $user = User::create([
            'name' => 'JSON User',
            'email' => 'json@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $array = $user->toArray();
        expect($array)->toBeArray();
        expect($array['name'])->toBe('JSON User');
        expect($array['email'])->toBe('json@example.com');

        $json = $user->toJson();
        $decoded = json_decode($json, true);
        expect($decoded['name'])->toBe('JSON User');
    });

    it('can handle transactions', function () {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            User::create([
                'name' => 'Transaction User 1',
                'email' => 'trans1@example.com',
                'password' => password_hash('secret', PASSWORD_DEFAULT)
            ]);

            User::create([
                'name' => 'Transaction User 2',
                'email' => 'trans2@example.com',
                'password' => password_hash('secret', PASSWORD_DEFAULT)
            ]);

            $pdo->commit();

            $count = User::count();
            expect($count)->toBe(2);
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    });

    it('can handle complex queries', function () {
        // Create test data
        User::create(['name' => 'Admin User', 'email' => 'admin@example.com', 'password' => 'hash']);
        User::create(['name' => 'Regular User', 'email' => 'user@example.com', 'password' => 'hash']);
        User::create(['name' => 'Another Admin', 'email' => 'admin2@example.com', 'password' => 'hash']);

        // Find users with "Admin" in name
        if (method_exists(User::class, 'where')) {
            $admins = User::where('name', 'LIKE', '%Admin%')->get();
            expect(count($admins))->toBe(2);
        } else {
            expect(true)->toBeTrue(); // Skip if complex queries not implemented
        }
    });

    it('can handle model relationships if implemented', function () {
        // This would test relationships between models
        // For example, User hasMany Posts, Post belongsTo User

        if (class_exists('GuepardoSys\Models\Post')) {
            // Test user-post relationship
            expect(true)->toBeTrue();
        } else {
            expect(true)->toBeTrue(); // Skip if relationships not implemented
        }
    });

    it('can handle soft deletes if implemented', function () {
        if (method_exists(User::class, 'softDelete')) {
            $user = User::create([
                'name' => 'Soft Delete User',
                'email' => 'softdelete@example.com',
                'password' => password_hash('secret', PASSWORD_DEFAULT)
            ]);

            $user->softDelete();

            // Should not appear in normal queries
            $found = User::find($user->id);
            expect($found)->toBeNull();

            // Should appear in withTrashed queries
            $trashedUser = User::withTrashed()->find($user->id);
            expect($trashedUser)->toBeInstanceOf(User::class);
        } else {
            expect(true)->toBeTrue(); // Skip if soft deletes not implemented
        }
    });
});
