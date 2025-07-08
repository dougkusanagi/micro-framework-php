# Database System - GuepardoSys Micro PHP

## Overview

The database system provides a simple but powerful way to interact with databases using PDO with support for multiple database systems (MySQL, PostgreSQL).

## Configuration

Database configuration is stored in `config/database.php` and uses environment variables from `.env`:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=guepardo
DB_USERNAME=root
DB_PASSWORD=
```

## Database Setup

### MySQL

1. Create database:
```sql
CREATE DATABASE guepardo;
```

2. Run migration:
```sql
source database/migrations/001_create_users_table.sql;
```

3. Optional - Run seeds:
```sql
source database/seeds/users_seed.sql;
```

### PostgreSQL

1. Create database:
```sql
CREATE DATABASE guepardo;
```

2. Run migration:
```sql
\i database/migrations/001_create_users_table_pgsql.sql;
```

3. Optional - Run seeds:
```sql
\i database/seeds/users_seed.sql;
```

## Models

### BaseModel

All models extend the `BaseModel` class which provides:

- **Convention over Configuration**: Table names are automatically inferred from class names
- **CRUD Operations**: `find()`, `all()`, `where()`, `create()`, `update()`, `delete()`
- **Security**: Prepared statements and fillable fields
- **Mass Assignment Protection**: Only fillable fields can be mass assigned

### Creating Models

```php
<?php

namespace App\Models;

use GuepardoSys\Core\BaseModel;

class Product extends BaseModel
{
    protected array $fillable = [
        'name',
        'price',
        'description',
        'created_at',
        'updated_at'
    ];
}
```

### Model Usage Examples

#### Find Records

```php
// Find by ID
$user = User::find(1);

// Find all
$users = User::all();

// Find by condition
$users = User::where('email', 'test@example.com');
```

#### Create Records

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123'
]);
```

#### Update Records

```php
$user = User::find(1);
$user->update([
    'name' => 'John Smith',
    'email' => 'johnsmith@example.com'
]);
```

#### Delete Records

```php
$user = User::find(1);
$user->delete();
```

## Database Connection

The `Database` class handles connections:

```php
use GuepardoSys\Core\Database;

// Get default connection
$pdo = Database::getConnection();

// Get specific connection
$pdo = Database::getConnection('mysql');

// Test connection
$isConnected = Database::testConnection();
```

## User Model Example

The `User` model includes:

- Password hashing
- Email validation
- Custom finder methods
- Timestamps handling
- Data validation

```php
// Create user with hashed password
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123' // Will be automatically hashed
]);

// Find by email
$user = User::findByEmail('test@example.com');

// Verify password
if ($user->verifyPassword('password123')) {
    // Password is correct
}
```

## CRUD Controller Example

The `UsersController` demonstrates:

- Full CRUD operations
- Error handling
- Form validation
- View integration

Routes available:
- `GET /users` - List all users
- `GET /users/create` - Show create form
- `POST /users` - Store new user
- `GET /users/{id}` - Show user details
- `GET /users/{id}/edit` - Show edit form
- `POST /users/{id}` - Update user
- `GET /users/{id}/delete` - Delete user

## Security Features

- **Prepared Statements**: All queries use prepared statements
- **Mass Assignment Protection**: Only fillable fields can be assigned
- **Password Hashing**: Passwords are automatically hashed using PHP's `password_hash()`
- **XSS Protection**: Template engine automatically escapes output

## Best Practices

1. **Always use fillable fields**: Define `$fillable` array in your models
2. **Use validation**: Validate data before saving
3. **Handle exceptions**: Wrap database operations in try-catch blocks
4. **Use transactions**: For complex operations involving multiple tables
5. **Keep models lean**: Business logic should be in services, not models

## Extending BaseModel

You can add custom methods to your models:

```php
class User extends BaseModel
{
    // Custom finder
    public static function findActive(): array
    {
        $instance = new static();
        $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE active = 1");
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new static();
            $model->attributes = $row;
            $results[] = $model;
        }
        
        return $results;
    }
    
    // Custom relationship
    public function orders(): array
    {
        return Order::where('user_id', $this->id);
    }
}
```
