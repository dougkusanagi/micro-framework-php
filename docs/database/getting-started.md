# Database - Getting Started

GuepardoSys provides a simple yet powerful database layer that supports multiple database systems. Built on PDO, it offers an intuitive query builder and an Eloquent-inspired ORM for working with your data.

## Configuration

### Database Configuration

Configure your database connection in the `.env` file:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### Supported Databases

GuepardoSys supports the following database systems:

#### MySQL
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

#### PostgreSQL
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password
DB_CHARSET=utf8
```

#### SQLite
```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Multiple Connections

Configure multiple database connections:

```php
<?php
// config/database.php

return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],
        
        'analytics' => [
            'driver' => 'mysql',
            'host' => $_ENV['ANALYTICS_DB_HOST'] ?? '127.0.0.1',
            'database' => $_ENV['ANALYTICS_DB_DATABASE'] ?? '',
            'username' => $_ENV['ANALYTICS_DB_USERNAME'] ?? '',
            'password' => $_ENV['ANALYTICS_DB_PASSWORD'] ?? '',
        ],
    ],
];
```

## Database Setup

### Creating Your Database

#### MySQL
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE my_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user and grant permissions
CREATE USER 'my_user'@'localhost' IDENTIFIED BY 'my_password';
GRANT ALL PRIVILEGES ON my_database.* TO 'my_user'@'localhost';
FLUSH PRIVILEGES;
```

#### PostgreSQL
```bash
# Connect to PostgreSQL
sudo -u postgres psql

# Create database
CREATE DATABASE my_database;

# Create user
CREATE USER my_user WITH PASSWORD 'my_password';
GRANT ALL PRIVILEGES ON DATABASE my_database TO my_user;
```

#### SQLite
```bash
# Create SQLite database
touch database/database.sqlite

# Set permissions
chmod 664 database/database.sqlite
chmod 775 database/
```

### Testing Database Connection

Test your database connection:

```bash
# Test database connection
./guepardo db:test

# Show database information
./guepardo db:info
```

## Basic Database Usage

### Getting a Database Connection

```php
<?php

use GuepardoSys\Core\Database;

// Get default connection
$db = Database::connection();

// Get specific connection
$analyticsDb = Database::connection('analytics');

// Get PDO instance directly
$pdo = Database::connection()->getPdo();
```

### Raw Queries

Execute raw SQL queries:

```php
<?php

use GuepardoSys\Core\Database;

$db = Database::connection();

// Select query
$users = $db->select('SELECT * FROM users WHERE active = ?', [1]);

// Insert query
$db->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['John Doe', 'john@example.com']);

// Update query
$affected = $db->update('UPDATE users SET last_login = NOW() WHERE id = ?', [123]);

// Delete query
$deleted = $db->delete('DELETE FROM users WHERE active = 0');

// General statement execution
$db->statement('CREATE INDEX idx_users_email ON users(email)');
```

### Prepared Statements

Use prepared statements for security:

```php
// Prepare and execute
$stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND active = ?');
$stmt->execute(['john@example.com', 1]);
$user = $stmt->fetch();

// Named parameters
$stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND active = :active');
$stmt->execute([
    'email' => 'john@example.com',
    'active' => 1
]);
$user = $stmt->fetch();
```

## Query Builder

### Basic Query Building

GuepardoSys includes a fluent query builder:

```php
<?php

use GuepardoSys\Core\QueryBuilder;

$query = new QueryBuilder();

// Select all users
$users = $query->table('users')->get();

// Select specific columns
$users = $query->table('users')
    ->select(['id', 'name', 'email'])
    ->get();

// Where conditions
$activeUsers = $query->table('users')
    ->where('active', 1)
    ->get();

// Multiple conditions
$users = $query->table('users')
    ->where('active', 1)
    ->where('age', '>', 18)
    ->get();

// OR conditions
$users = $query->table('users')
    ->where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();
```

### Advanced Queries

```php
// LIKE queries
$users = $query->table('users')
    ->where('name', 'LIKE', '%john%')
    ->get();

// IN queries
$users = $query->table('users')
    ->whereIn('role', ['admin', 'moderator', 'editor'])
    ->get();

// BETWEEN queries
$users = $query->table('users')
    ->whereBetween('age', [18, 65])
    ->get();

// NULL checks
$users = $query->table('users')
    ->whereNull('deleted_at')
    ->get();

$deletedUsers = $query->table('users')
    ->whereNotNull('deleted_at')
    ->get();
```

### Ordering and Limiting

```php
// Order by
$users = $query->table('users')
    ->orderBy('name', 'ASC')
    ->get();

// Multiple order by
$users = $query->table('users')
    ->orderBy('role', 'ASC')
    ->orderBy('name', 'ASC')
    ->get();

// Limit and offset
$users = $query->table('users')
    ->limit(10)
    ->offset(20)
    ->get();

// Pagination helper
$users = $query->table('users')
    ->paginate(1, 10); // page 1, 10 per page
```

### Joins

```php
// Inner join
$users = $query->table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->select(['users.*', 'profiles.bio'])
    ->get();

// Left join
$users = $query->table('users')
    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->get();

// Multiple joins
$posts = $query->table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->select(['posts.*', 'users.name as author', 'categories.name as category'])
    ->get();
```

### Aggregates

```php
// Count
$userCount = $query->table('users')->count();
$activeUserCount = $query->table('users')->where('active', 1)->count();

// Sum
$totalSales = $query->table('orders')->sum('total');

// Average
$averageAge = $query->table('users')->avg('age');

// Min/Max
$youngestAge = $query->table('users')->min('age');
$oldestAge = $query->table('users')->max('age');
```

### Insert, Update, Delete

```php
// Insert single record
$userId = $query->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('password', PASSWORD_DEFAULT),
    'created_at' => date('Y-m-d H:i:s')
]);

// Insert multiple records
$query->table('users')->insertMultiple([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com']
]);

// Update records
$affected = $query->table('users')
    ->where('id', 123)
    ->update([
        'last_login' => date('Y-m-d H:i:s'),
        'login_count' => $query->raw('login_count + 1')
    ]);

// Delete records
$deleted = $query->table('users')
    ->where('active', 0)
    ->where('last_login', '<', date('Y-m-d', strtotime('-1 year')))
    ->delete();
```

## Transactions

### Basic Transactions

```php
<?php

use GuepardoSys\Core\Database;

$db = Database::connection();

try {
    $db->beginTransaction();
    
    // Insert user
    $userId = $db->insert('INSERT INTO users (name, email) VALUES (?, ?)', 
        ['John Doe', 'john@example.com']);
    
    // Insert profile
    $db->insert('INSERT INTO profiles (user_id, bio) VALUES (?, ?)', 
        [$userId, 'Software developer']);
    
    // Insert role
    $db->insert('INSERT INTO user_roles (user_id, role) VALUES (?, ?)', 
        [$userId, 'user']);
    
    $db->commit();
    
    echo "User created successfully!";
    
} catch (Exception $e) {
    $db->rollback();
    echo "Error creating user: " . $e->getMessage();
}
```

### Transaction Helper

```php
$result = $db->transaction(function($db) {
    // All database operations here are wrapped in a transaction
    $userId = $db->insert('INSERT INTO users (name, email) VALUES (?, ?)', 
        ['John Doe', 'john@example.com']);
    
    $db->insert('INSERT INTO profiles (user_id, bio) VALUES (?, ?)', 
        [$userId, 'Software developer']);
    
    return $userId;
});
```

## Database Utilities

### Schema Information

```php
// Get table columns
$columns = $db->getColumns('users');

// Check if table exists
if ($db->tableExists('users')) {
    // Table exists
}

// Get table list
$tables = $db->getTables();

// Get database size
$size = $db->getDatabaseSize();
```

### Database Maintenance

```php
// Optimize table
$db->optimize('users');

// Analyze table
$db->analyze('users');

// Check table
$db->check('users');

// Repair table (MySQL only)
$db->repair('users');
```

## CLI Database Commands

### Database Management

```bash
# Create database
./guepardo db:create

# Drop database
./guepardo db:drop

# Show database information
./guepardo db:info

# Test database connection
./guepardo db:test

# Backup database
./guepardo db:backup

# Restore database
./guepardo db:restore backup.sql
```

### Query Execution

```bash
# Execute SQL file
./guepardo db:execute schema.sql

# Run interactive SQL shell
./guepardo db:shell

# Export data
./guepardo db:export --table=users --format=csv

# Import data
./guepardo db:import users.csv --table=users
```

## Performance Optimization

### Connection Pooling

```php
// Configure connection pooling
'connections' => [
    'mysql' => [
        // ... other config
        'options' => [
            PDO::ATTR_PERSISTENT => true, // Enable persistent connections
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
        ],
    ],
],
```

### Query Optimization

```php
// Use indexes effectively
$users = $query->table('users')
    ->where('email', 'john@example.com') // Ensure email column is indexed
    ->first();

// Limit selected columns
$users = $query->table('users')
    ->select(['id', 'name', 'email']) // Don't select unnecessary columns
    ->get();

// Use pagination for large datasets
$users = $query->table('users')
    ->paginate($page, 50); // Limit results per page
```

### Query Caching

```php
// Simple query result caching
$cacheKey = 'users_active_' . md5(serialize($conditions));
$users = cache($cacheKey, function() use ($query, $conditions) {
    return $query->table('users')
        ->where($conditions)
        ->get();
}, 3600); // Cache for 1 hour
```

## Security Best Practices

### SQL Injection Prevention

```php
// ✅ Good - Use parameter binding
$users = $db->select('SELECT * FROM users WHERE email = ?', [$email]);

// ❌ Bad - Direct string concatenation
$users = $db->select("SELECT * FROM users WHERE email = '$email'");

// ✅ Good - Use query builder
$users = $query->table('users')->where('email', $email)->get();
```

### Data Validation

```php
// Validate input before database operations
function createUser($data) {
    // Validate required fields
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Valid email is required');
    }
    
    // Sanitize data
    $data['name'] = trim($data['name']);
    $data['email'] = strtolower(trim($data['email']));
    
    // Hash password
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    return $query->table('users')->insert($data);
}
```

### Access Control

```php
// Implement row-level security
class UserRepository {
    public function getUserPosts($userId, $currentUserId) {
        $query = $this->query->table('posts')
            ->where('user_id', $userId);
        
        // Users can only see their own draft posts
        if ($userId !== $currentUserId) {
            $query->where('status', 'published');
        }
        
        return $query->get();
    }
}
```

## Troubleshooting

### Common Issues

#### Connection Refused
```bash
# Check if database server is running
sudo service mysql status
sudo service postgresql status

# Check connection parameters
./guepardo db:test
```

#### Access Denied
```bash
# Verify credentials in .env file
# Check user permissions in database
SHOW GRANTS FOR 'username'@'localhost';
```

#### Table Doesn't Exist
```bash
# Run migrations
./guepardo migrate

# Check migration status
./guepardo migrate:status
```

### Debug Queries

```php
// Enable query logging
$db->enableQueryLog();

// Execute queries
$users = $query->table('users')->where('active', 1)->get();

// Get executed queries
$queries = $db->getQueryLog();
foreach ($queries as $query) {
    echo $query['sql'] . "\n";
    print_r($query['bindings']);
    echo "Time: " . $query['time'] . "ms\n\n";
}
```

---

**🗄️ The database layer provides a solid foundation for your application's data needs while maintaining simplicity and performance.**