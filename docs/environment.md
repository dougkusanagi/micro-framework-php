# Environment Variables - GuepardoSys Micro PHP

## Overview

GuepardoSys Micro PHP includes a custom, lightweight environment variable loader that provides a simple interface for managing environment variables. This implementation is completely self-contained and requires no external dependencies.

## Features

- **Zero Dependencies**: No external libraries required
- **Simple Interface**: Clean and intuitive API for environment variables
- **Automatic Type Casting**: Converts string values to appropriate types
- **Safe Loading**: Gracefully handles missing .env files
- **Environment Protection**: Doesn't override existing environment variables

## Usage

### Loading Environment Variables

```php
use GuepardoSys\Core\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // Won't throw exceptions if .env doesn't exist

// Or load with exceptions
$dotenv->load(); // Will throw exception if .env file has issues
```

### Accessing Environment Variables

```php
// Get environment variable with default
$dbHost = env('DB_HOST', 'localhost');

// Get environment variable without default
$dbPassword = env('DB_PASSWORD');

// Type casting examples
$debug = env('APP_DEBUG', false);  // 'true' -> true, 'false' -> false
$port = env('APP_PORT', 8000);     // String numbers remain strings
$name = env('APP_NAME', 'MyApp');  // Regular string values
```

## Environment File Format

The `.env` file supports the same format as the original library:

```bash
# Comments start with #
APP_NAME="GuepardoSys Micro PHP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=guepardo
DB_USERNAME=root
DB_PASSWORD=

# Quoted values
SPECIAL_VALUE="Value with spaces"
SINGLE_QUOTED='Another value'

# Boolean values
FEATURE_ENABLED=true
FEATURE_DISABLED=false
NULLABLE_VALUE=null
```

## Type Casting

The `env()` function automatically converts string values:

| .env Value | PHP Value | Type |
|------------|-----------|------|
| `true` | `true` | boolean |
| `false` | `false` | boolean |
| `null` | `null` | null |
| `"string"` | `string` | string |
| `123` | `"123"` | string |

## Implementation Details

### Class Structure

```php
namespace GuepardoSys\Core;

class Dotenv
{
    public static function createImmutable(string $path): self
    public function load(): void
    public function safeLoad(): void
    public function getVariables(): array
    public function hasVariable(string $key): bool
    public function getVariable(string $key, mixed $default = null): mixed
}
```

### Features

1. **Quote Handling**: Automatically removes single and double quotes
2. **Comment Support**: Lines starting with `#` are ignored
3. **Empty Line Handling**: Empty lines are skipped
4. **Variable Precedence**: Existing environment variables take precedence
5. **Multiple Formats**: Supports both `KEY=value` and `KEY="value"` formats

## Security

- **No Override**: Won't override existing environment variables
- **Safe Defaults**: Uses safe defaults when values are missing
- **Input Validation**: Properly handles malformed .env files

## Error Handling

### Safe Loading

```php
$dotenv->safeLoad(); // Never throws exceptions
```

### Regular Loading

```php
try {
    $dotenv->load();
} catch (Exception $e) {
    // Handle .env file errors
}
```

## Performance

- **Lightweight**: ~150 lines of code vs 3000+ in original library
- **No Dependencies**: Reduces vendor directory size
- **Fast Loading**: Direct file parsing without complex features
- **Memory Efficient**: Minimal memory footprint

## Configuration Examples

### Development Environment

```bash
APP_NAME="My App - Development"
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=myapp_dev
```

### Production Environment

```bash
APP_NAME="My App"
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=prod-server.com
DB_DATABASE=myapp_prod
```

## Limitations

Intentionally simplified compared to the original library:

- **No Variable Expansion**: `${VAR}` syntax not supported
- **No Validation**: No built-in validation rules
- **No Immutability**: Variables can be changed after loading
- **No Caching**: Re-parses file on each load

These limitations keep the implementation simple and focused on the core functionality needed by most applications.

## Testing

```php
// Test environment loading
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Test variable access
assert(env('DB_HOST') === 'localhost');
assert(env('APP_DEBUG') === true);
assert(env('NON_EXISTENT', 'default') === 'default');
```

## Benefits

1. **Reduced Dependencies**: One less external dependency
2. **Faster Installation**: Smaller vendor directory
3. **Better Control**: Full control over environment loading
4. **Shared Hosting Friendly**: No additional requirements
5. **Maintainable**: Simple code that's easy to understand and modify
