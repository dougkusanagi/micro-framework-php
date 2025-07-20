# Migration & Seeding System (Plain PHP)

This micro-framework supports Laravel-style migrations and seeders using only plain PHP (no external libraries).

## Migrations

- Place migration files in `database/migrations/`.
- Supported formats:
  - **PHP class**: e.g., `20230708_create_users_table.php`
  - **SQL file**: e.g., `20230708_create_users_table.sql`
- For PHP migrations, the file must define a class named after the file (e.g., `CreateUsersTable`) with `up(PDO $pdo)` and `down(PDO $pdo)` methods.

**Example: `database/migrations/20230708_create_users_table.php`**
```php
<?php
class CreateUsersTable {
    public function up(PDO $pdo) {
        $pdo->exec("CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), email VARCHAR(100), password VARCHAR(100))");
    }
    public function down(PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
}
```

- For SQL migrations, only `up` is supported (no rollback).

## Seeders

- Place seeder files in `database/seeds/`.
- Each seeder is a PHP file with a class named after the file (e.g., `UsersSeeder`) and a `run(PDO $pdo)` method.

**Example: `database/seeds/UsersSeeder.php`**
```php
<?php
class UsersSeeder {
    public function run(PDO $pdo) {
        $pdo->exec("INSERT INTO users (name, email, password) VALUES ('Alice', 'alice@example.com', 'password1'), ('Bob', 'bob@example.com', 'password2')");
    }
}
```

## Running Migrations and Seeds

Use the CLI commands:

- Run all migrations:
  ```sh
  php guepardo migrate
  ```
- Run all migrations and then seeds:
  ```sh
  php guepardo migrate --seed
  ```
- Run only seeds:
  ```sh
  php guepardo migrate:seed
  ```
- Rollback last migration batch:
  ```sh
  php guepardo migrate:rollback
  ```

## Notes
- Migration and seeder classes must match the file name (CamelCase, no extension).
- Only `.php` and `.sql` files are supported for migrations.
- Only `.php` files are supported for seeders.
- No external dependencies are required—everything is plain PHP. 