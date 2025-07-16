# Directory Structure

GuepardoSys follows a clean, organized directory structure that promotes separation of concerns and maintainability. This guide explains each directory and its purpose.

## Root Directory

```
my-project/
├── app/                    # Application code
├── bootstrap/              # Application bootstrapping
├── config/                 # Configuration files
├── database/               # Database migrations and seeds
├── docs/                   # Documentation
├── public/                 # Web server document root
├── routes/                 # Route definitions
├── src/                    # Framework core code
├── storage/                # Application storage (cache, logs)
├── stubs/                  # Code generation templates
├── tests/                  # Test files
├── vendor/                 # Composer dependencies
├── .env                    # Environment configuration
├── .env.example            # Environment template
├── composer.json           # PHP dependencies
├── guepardo               # CLI tool
├── package.json           # Frontend dependencies
└── README.md              # Project documentation
```

## Application Directory (`app/`)

The `app` directory contains the core code of your application:

```
app/
├── Controllers/           # HTTP controllers
│   ├── BaseController.php # Base controller class
│   └── HomeController.php # Example controller
├── Models/               # Data models
│   └── User.php         # Example model
├── Views/               # View templates
│   ├── layouts/         # Layout templates
│   ├── partials/        # Reusable view components
│   └── pages/           # Page templates
└── Middleware/          # Custom middleware (optional)
    └── AuthMiddleware.php
```

### Controllers

Controllers handle HTTP requests and return responses. They should be thin and delegate business logic to models or services.

```php
<?php
// app/Controllers/UserController.php

namespace App\Controllers;

use GuepardoSys\Core\Request;
use App\Models\User;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $users = User::all();
        return $this->view('users.index', compact('users'));
    }
}
```

### Models

Models represent your data and business logic. They extend the `BaseModel` class which provides ORM functionality.

```php
<?php
// app/Models/User.php

namespace App\Models;

use GuepardoSys\Core\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}
```

### Views

Views contain your HTML templates. They use PHP syntax with some helper functions for common tasks.

```php
<!-- app/Views/users/index.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
</head>
<body>
    <h1>Users</h1>
    <?php foreach ($users as $user): ?>
        <p><?= htmlspecialchars($user->name) ?></p>
    <?php endforeach; ?>
</body>
</html>
```

## Bootstrap Directory (`bootstrap/`)

Contains the application bootstrap file that initializes the framework:

```
bootstrap/
└── app.php              # Application initialization
```

This file sets up the dependency injection container, registers services, and prepares the application for handling requests.

## Configuration Directory (`config/`)

Contains configuration files for different aspects of your application:

```
config/
├── app.php              # Application configuration
├── database.php         # Database configuration
├── cache.php           # Cache configuration
└── logging.php         # Logging configuration
```

Example configuration file:

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
        ],
    ],
];
```

## Database Directory (`database/`)

Contains database-related files:

```
database/
├── migrations/          # Database schema migrations
│   ├── 001_create_users_table.sql
│   └── 002_create_posts_table.sql
└── seeds/              # Database seeders
    ├── UserSeeder.php
    └── PostSeeder.php
```

### Migrations

Migrations are SQL files that define your database schema:

```sql
-- database/migrations/001_create_users_table.sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Seeders

Seeders populate your database with test data:

```php
<?php
// database/seeds/UserSeeder.php

namespace Database\Seeds;

use App\Models\User;

class UserSeeder
{
    public function run()
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT)
        ]);
    }
}
```

## Public Directory (`public/`)

The web server document root. Only files that should be directly accessible via HTTP should be here:

```
public/
├── index.php           # Application entry point
├── .htaccess          # Apache rewrite rules
├── assets/            # Compiled assets
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── images/            # Static images
└── favicon.ico        # Site favicon
```

### Entry Point

The `index.php` file is the single entry point for all HTTP requests:

```php
<?php
// public/index.php

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = GuepardoSys\Core\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Bootstrap and run the application
$container = new GuepardoSys\Core\Container();
$app = new GuepardoSys\Core\App($container);
$app->run();
```

## Routes Directory (`routes/`)

Contains route definitions:

```
routes/
├── web.php             # Web routes
├── api.php             # API routes (optional)
└── console.php         # Console routes (optional)
```

Routes are defined as arrays:

```php
<?php
// routes/web.php

return [
    ['GET', '/', ['App\Controllers\HomeController', 'index']],
    ['GET', '/users', ['App\Controllers\UserController', 'index']],
    ['POST', '/users', ['App\Controllers\UserController', 'store']],
    ['GET', '/users/{id}', ['App\Controllers\UserController', 'show']],
];
```

## Source Directory (`src/`)

Contains the framework core code:

```
src/
├── CLI/                # Command-line interface
│   ├── Commands/       # CLI commands
│   └── Console.php     # Console application
├── Core/               # Core framework classes
│   ├── App.php         # Main application class
│   ├── Router.php      # HTTP router
│   ├── Request.php     # HTTP request
│   ├── Response.php    # HTTP response
│   ├── BaseModel.php   # Base model class
│   ├── Container.php   # Dependency injection
│   ├── Database.php    # Database connection
│   └── Debug/          # Advanced debugging system
└── helpers.php         # Global helper functions
```

## Storage Directory (`storage/`)

Contains application-generated files:

```
storage/
├── cache/              # Application cache
│   ├── views/          # Compiled view cache
│   └── config/         # Configuration cache
├── logs/               # Application logs
│   ├── error.log       # Error logs
│   └── access.log      # Access logs
└── uploads/            # File uploads (if applicable)
```

### Cache

The cache directory stores compiled templates and configuration:

- `views/` - Compiled view templates for faster rendering
- `config/` - Cached configuration for production optimization

### Logs

Application logs are stored here with automatic rotation:

- `error.log` - PHP errors and exceptions
- `access.log` - HTTP request logs (development only)

## Stubs Directory (`stubs/`)

Contains templates for code generation:

```
stubs/
├── controller.stub     # Controller template
├── model.stub         # Model template
├── migration.stub     # Migration template
└── test.stub          # Test template
```

These templates are used by the CLI tool when generating new files:

```php
<?php
// stubs/controller.stub

namespace App\Controllers;

use GuepardoSys\Core\Request;

class {{ControllerName}} extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('{{viewPath}}.index');
    }
}
```

## Tests Directory (`tests/`)

Contains your application tests:

```
tests/
├── Feature/            # Feature/integration tests
│   ├── HomeTest.php
│   └── UserTest.php
├── Unit/               # Unit tests
│   ├── Models/
│   └── Controllers/
├── Pest.php           # Pest configuration
└── TestCase.php       # Base test case
```

## Configuration Files

### Environment Configuration (`.env`)

Contains environment-specific configuration:

```env
# Application
APP_NAME="My Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password

# Cache
CACHE_DRIVER=file
CACHE_TTL=3600
```

### Composer Configuration (`composer.json`)

Defines PHP dependencies and autoloading:

```json
{
    "name": "my-company/my-project",
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "GuepardoSys\\": "src/"
        }
    },
    "require": {
        "php": "^8.3"
    }
}
```

### Package Configuration (`package.json`)

Defines frontend dependencies and build scripts:

```json
{
    "scripts": {
        "dev": "bun run build --watch",
        "build": "bun run build-css && bun run build-js",
        "build-css": "tailwindcss -i ./resources/css/app.css -o ./public/assets/css/app.css",
        "build-js": "bun build ./resources/js/app.js --outdir ./public/assets/js"
    },
    "devDependencies": {
        "tailwindcss": "^3.4.0"
    }
}
```

## Best Practices

### Naming Conventions

- **Controllers**: PascalCase with "Controller" suffix (`UserController`)
- **Models**: PascalCase, singular (`User`, `BlogPost`)
- **Views**: snake_case directories and files (`users/show.php`)
- **Database tables**: snake_case, plural (`users`, `blog_posts`)
- **Routes**: kebab-case (`/blog-posts`, `/user-profile`)

### File Organization

- Keep controllers thin - move business logic to models or services
- Use subdirectories in views to organize by feature (`users/`, `posts/`)
- Group related functionality in the same directory
- Use descriptive names for files and classes

### Security

- Never put sensitive files in the `public/` directory
- Keep the `.env` file out of version control
- Use the `storage/` directory for file uploads
- Validate and sanitize all user input

---

**📁 Understanding the directory structure helps you navigate and organize your GuepardoSys application effectively.**