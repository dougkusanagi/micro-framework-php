# CLI Tool (Guepardo)

GuepardoSys includes a powerful command-line interface called **Guepardo** that provides over 20 commands to streamline your development workflow. From code generation to database management, the CLI tool is your companion for efficient development.

## Overview

The Guepardo CLI tool provides:

- **Code Generation** - Controllers, models, migrations, and more
- **Database Management** - Migrations, seeding, and schema operations
- **Development Server** - Built-in PHP development server
- **Quality Assurance** - Testing, code analysis, and style checking
- **Asset Management** - Frontend build and optimization
- **Production Tools** - Optimization and deployment helpers

## Getting Started

### Making Guepardo Executable

Ensure the CLI tool is executable:

```bash
chmod +x guepardo
```

### Basic Usage

```bash
# Show available commands
./guepardo

# Get help for a specific command
./guepardo help serve
./guepardo serve --help

# Run a command
./guepardo serve
```

## Available Commands

### Development Commands

#### Start Development Server
```bash
# Start server on localhost:8000
./guepardo serve

# Custom host and port
./guepardo serve 127.0.0.1 3000

# With specific PHP version
./guepardo serve --php=/usr/bin/php8.3
```

#### List Routes
```bash
# Show all routes
./guepardo route:list

# Filter by HTTP method
./guepardo route:list --method=GET
./guepardo route:list --method=POST

# Filter by pattern
./guepardo route:list --filter=users
./guepardo route:list --filter=api
```

### Code Generation Commands

#### Generate Controllers
```bash
# Basic controller
./guepardo make:controller UserController

# Controller with namespace
./guepardo make:controller Admin/UserController

# API controller
./guepardo make:controller Api/UserController --api

# Resource controller (with all CRUD methods)
./guepardo make:controller PostController --resource
```

#### Generate Models
```bash
# Basic model
./guepardo make:model User

# Model with migration
./guepardo make:model Post --migration

# Model with factory and seeder
./guepardo make:model Product --factory --seeder
```

#### Generate Migrations
```bash
# Create table migration
./guepardo make:migration create_users_table

# Add column migration
./guepardo make:migration add_email_to_users_table

# Modify table migration
./guepardo make:migration modify_users_table
```

#### Generate Other Components
```bash
# Middleware
./guepardo make:middleware AuthMiddleware

# Seeder
./guepardo make:seeder UserSeeder

# Test
./guepardo make:test UserTest

# Command
./guepardo make:command CustomCommand
```

### Database Commands

#### Migration Management
```bash
# Run pending migrations
./guepardo migrate

# Rollback last migration
./guepardo migrate:rollback

# Rollback specific number of migrations
./guepardo migrate:rollback --step=3

# Reset all migrations and re-run
./guepardo migrate:refresh

# Show migration status
./guepardo migrate:status

# Create migration files from existing database
./guepardo migrate:generate
```

#### Database Operations
```bash
# Create database
./guepardo db:create

# Drop database
./guepardo db:drop

# Show database info
./guepardo db:info

# Test database connection
./guepardo db:test

# Backup database
./guepardo db:backup

# Restore database
./guepardo db:restore backup.sql
```

#### Seeding
```bash
# Run all seeders
./guepardo db:seed

# Run specific seeder
./guepardo db:seed --class=UserSeeder

# Fresh migration with seeding
./guepardo migrate:fresh --seed
```

### Testing Commands

#### Run Tests
```bash
# Run all tests
./guepardo test

# Run specific test file
./guepardo test tests/Feature/UserTest.php

# Run tests with filter
./guepardo test --filter=UserTest

# Run tests with coverage
./guepardo test --coverage

# Run tests in parallel
./guepardo test --parallel
```

#### Code Quality
```bash
# Run PHPStan analysis
./guepardo stan

# Run with specific level
./guepardo stan --level=8

# Analyze specific directory
./guepardo stan app/Controllers

# Run PHP CodeSniffer
./guepardo cs

# Fix code style issues
./guepardo cs:fix

# Run all quality checks
./guepardo quality
```

### Asset Commands

#### Frontend Build
```bash
# Build assets for development
./guepardo build:dev

# Build assets for production
./guepardo build:prod

# Watch for changes and rebuild
./guepardo build:watch

# Clean build artifacts
./guepardo build:clean
```

### Cache Commands

#### Cache Management
```bash
# Clear all caches
./guepardo cache:clear

# Clear specific cache
./guepardo cache:clear --type=views
./guepardo cache:clear --type=config

# Cache configuration
./guepardo config:cache

# Clear configuration cache
./guepardo config:clear

# Show cache statistics
./guepardo cache:stats
```

### Production Commands

#### Optimization
```bash
# Optimize for production
./guepardo optimize

# Clear optimization
./guepardo optimize:clear

# Generate autoload files
./guepardo optimize:autoload

# Optimize configuration
./guepardo optimize:config

# Optimize routes
./guepardo optimize:routes
```

#### Maintenance
```bash
# Put application in maintenance mode
./guepardo down

# Bring application back online
./guepardo up

# Check application health
./guepardo health:check

# Show application status
./guepardo status
```

## Command Details

### Serve Command

The serve command starts a PHP development server:

```bash
./guepardo serve [host] [port] [options]
```

**Options:**
- `--host` - Server host (default: localhost)
- `--port` - Server port (default: 8000)
- `--php` - PHP binary path
- `--no-reload` - Disable auto-reload

**Examples:**
```bash
# Basic usage
./guepardo serve

# Custom host and port
./guepardo serve 0.0.0.0 8080

# Specific PHP version
./guepardo serve --php=/usr/bin/php8.3
```

### Make Commands

Code generation commands follow consistent patterns:

```bash
./guepardo make:controller ControllerName [options]
./guepardo make:model ModelName [options]
./guepardo make:migration migration_name [options]
```

**Common Options:**
- `--force` - Overwrite existing files
- `--dry-run` - Show what would be created without creating
- `--template` - Use custom template

### Migration Commands

Database migration commands:

```bash
# Run migrations
./guepardo migrate [options]

# Rollback migrations
./guepardo migrate:rollback [options]

# Migration status
./guepardo migrate:status
```

**Migration Options:**
- `--step` - Number of migrations to run/rollback
- `--force` - Force in production
- `--pretend` - Show SQL without executing

### Test Commands

Testing commands with various options:

```bash
./guepardo test [path] [options]
```

**Test Options:**
- `--filter` - Filter tests by name
- `--group` - Run specific test group
- `--coverage` - Generate coverage report
- `--parallel` - Run tests in parallel
- `--stop-on-failure` - Stop on first failure

## Configuration

### CLI Configuration

Configure CLI behavior in `config/cli.php`:

```php
<?php

return [
    'commands' => [
        // Custom commands
        'app:custom' => App\Commands\CustomCommand::class,
    ],
    
    'aliases' => [
        // Command aliases
        't' => 'test',
        's' => 'serve',
        'm' => 'migrate',
    ],
    
    'defaults' => [
        'serve' => [
            'host' => 'localhost',
            'port' => 8000,
        ],
        'test' => [
            'stop_on_failure' => false,
        ],
    ],
];
```

### Environment Variables

CLI-specific environment variables:

```env
# CLI Configuration
CLI_VERBOSITY=normal
CLI_ANSI=auto
CLI_NO_INTERACTION=false

# Development Server
DEV_SERVER_HOST=localhost
DEV_SERVER_PORT=8000

# Testing
TEST_PARALLEL=false
TEST_COVERAGE=false
```

## Custom Commands

### Creating Custom Commands

Generate a new command:

```bash
./guepardo make:command SendEmailCommand
```

This creates:

```php
<?php

namespace App\Commands;

use GuepardoSys\CLI\Commands\BaseCommand;

class SendEmailCommand extends BaseCommand
{
    protected $signature = 'email:send {recipient} {--subject=}';
    protected $description = 'Send an email to a recipient';
    
    public function handle()
    {
        $recipient = $this->argument('recipient');
        $subject = $this->option('subject', 'Default Subject');
        
        $this->info("Sending email to: {$recipient}");
        $this->info("Subject: {$subject}");
        
        // Your email sending logic here
        
        $this->success('Email sent successfully!');
    }
}
```

### Command Structure

Commands extend `BaseCommand` and implement:

```php
class MyCommand extends BaseCommand
{
    // Command signature (name and parameters)
    protected $signature = 'my:command {arg} {--option=}';
    
    // Command description
    protected $description = 'Description of what this command does';
    
    // Command execution
    public function handle()
    {
        // Command logic here
    }
}
```

### Command Arguments and Options

```php
// Arguments (required)
protected $signature = 'user:create {name} {email}';

// Optional arguments
protected $signature = 'user:create {name} {email?}';

// Arguments with defaults
protected $signature = 'user:create {name=John} {email=john@example.com}';

// Options (flags)
protected $signature = 'user:create {name} {--force} {--dry-run}';

// Options with values
protected $signature = 'user:create {name} {--role=user} {--age=}';
```

### Accessing Arguments and Options

```php
public function handle()
{
    // Get arguments
    $name = $this->argument('name');
    $email = $this->argument('email');
    
    // Get options
    $force = $this->option('force'); // boolean
    $role = $this->option('role', 'user'); // with default
    
    // Get all arguments/options
    $allArgs = $this->arguments();
    $allOptions = $this->options();
}
```

### Output Methods

```php
public function handle()
{
    // Basic output
    $this->line('Regular text');
    $this->info('Info message');
    $this->success('Success message');
    $this->warning('Warning message');
    $this->error('Error message');
    
    // Formatted output
    $this->table(['Name', 'Email'], [
        ['John', 'john@example.com'],
        ['Jane', 'jane@example.com'],
    ]);
    
    // Progress bar
    $bar = $this->progressBar(100);
    for ($i = 0; $i < 100; $i++) {
        $bar->advance();
        usleep(50000); // 50ms delay
    }
    $bar->finish();
    
    // Ask for input
    $name = $this->ask('What is your name?');
    $password = $this->secret('Enter password:');
    $confirmed = $this->confirm('Are you sure?');
    $choice = $this->choice('Select option:', ['yes', 'no'], 'yes');
}
```

## Command Scheduling

### Scheduled Commands

While GuepardoSys doesn't include a built-in scheduler, you can use cron:

```bash
# Edit crontab
crontab -e

# Add scheduled commands
# Run every minute
* * * * * cd /path/to/project && ./guepardo schedule:run

# Run daily at midnight
0 0 * * * cd /path/to/project && ./guepardo daily:cleanup

# Run weekly on Sunday
0 0 * * 0 cd /path/to/project && ./guepardo weekly:report
```

### Creating Scheduled Commands

```php
<?php

namespace App\Commands;

class DailyCleanupCommand extends BaseCommand
{
    protected $signature = 'daily:cleanup';
    protected $description = 'Clean up temporary files and logs';
    
    public function handle()
    {
        $this->info('Starting daily cleanup...');
        
        // Clean temporary files
        $this->cleanTempFiles();
        
        // Rotate logs
        $this->rotateLogs();
        
        // Clear old cache
        $this->clearOldCache();
        
        $this->success('Daily cleanup completed!');
    }
    
    private function cleanTempFiles()
    {
        $tempDir = BASE_PATH . '/storage/temp';
        $files = glob($tempDir . '/*');
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-1 day')) {
                unlink($file);
            }
        }
        
        $this->info('Cleaned temporary files');
    }
}
```

## Best Practices

### Command Development

1. **Use descriptive names** - Make command purposes clear
2. **Provide helpful descriptions** - Explain what commands do
3. **Handle errors gracefully** - Provide meaningful error messages
4. **Use appropriate output methods** - Info, success, warning, error
5. **Validate input** - Check arguments and options
6. **Provide feedback** - Show progress for long-running commands

### Performance

1. **Use progress bars** for long operations
2. **Batch database operations** when possible
3. **Implement timeouts** for external operations
4. **Use memory-efficient approaches** for large datasets
5. **Cache expensive operations** when appropriate

### Security

1. **Validate all input** from arguments and options
2. **Sanitize file paths** to prevent directory traversal
3. **Use secure defaults** for sensitive operations
4. **Log command execution** for audit trails
5. **Implement proper permissions** for file operations

---

**⚡ The Guepardo CLI tool accelerates development by automating common tasks and providing powerful utilities for managing your application.**