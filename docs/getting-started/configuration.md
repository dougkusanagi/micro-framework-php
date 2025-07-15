# Configuration

GuepardoSys uses environment-based configuration to keep your application flexible and secure. This guide covers all configuration options and best practices.

## Environment Configuration

### The `.env` File

The `.env` file contains environment-specific settings and should never be committed to version control. Copy `.env.example` to create your own:

```bash
cp .env.example .env
```

### Basic Configuration

```env
# Application Settings
APP_NAME="My Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600

# Session Configuration
SESSION_LIFETIME=7200
SESSION_COOKIE_NAME=guepardo_session

# Logging Configuration
LOG_LEVEL=debug
LOG_MAX_FILES=5

# Security Configuration
CSRF_TOKEN_LENGTH=32
HASH_ALGORITHM=bcrypt
```

## Application Configuration

### Environment Types

Set `APP_ENV` to control application behavior:

- **`local`** - Development environment with debugging enabled
- **`testing`** - Testing environment for automated tests
- **`staging`** - Pre-production environment for testing
- **`production`** - Production environment with optimizations

### Debug Mode

Control debugging features with `APP_DEBUG`:

```env
# Development - Show detailed error pages
APP_DEBUG=true

# Production - Show generic error pages
APP_DEBUG=false
```

When `APP_DEBUG=true`:
- Detailed error pages with stack traces
- Source code display around errors
- Request context information
- Performance metrics

When `APP_DEBUG=false`:
- Generic error pages
- Errors logged but not displayed
- Optimized performance
- Security-focused output

### Application URL

Set the base URL for your application:

```env
# Development
APP_URL=http://localhost:8000

# Production
APP_URL=https://myapp.com

# Subdirectory installation
APP_URL=https://mysite.com/myapp
```

## Database Configuration

### Connection Types

GuepardoSys supports multiple database types:

#### MySQL Configuration
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

#### PostgreSQL Configuration
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password
DB_CHARSET=utf8
```

#### SQLite Configuration
```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Multiple Database Connections

You can configure multiple database connections:

```env
# Primary database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=main_db

# Secondary database
DB_SECONDARY_CONNECTION=pgsql
DB_SECONDARY_HOST=192.168.1.100
DB_SECONDARY_DATABASE=analytics_db
```

## Cache Configuration

### Cache Drivers

Configure caching behavior:

```env
# File-based cache (default)
CACHE_DRIVER=file
CACHE_TTL=3600

# Redis cache (if available)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0

# Disable cache
CACHE_DRIVER=null
```

### Cache Settings

```env
# Cache time-to-live in seconds
CACHE_TTL=3600

# View cache (compiled templates)
VIEW_CACHE_ENABLED=true

# Configuration cache (production optimization)
CONFIG_CACHE_ENABLED=false
```

## Session Configuration

### Session Settings

```env
# Session lifetime in seconds (2 hours)
SESSION_LIFETIME=7200

# Session cookie name
SESSION_COOKIE_NAME=guepardo_session

# Session cookie domain
SESSION_COOKIE_DOMAIN=

# Session cookie path
SESSION_COOKIE_PATH=/

# HTTPS only cookies
SESSION_COOKIE_SECURE=false

# HTTP only cookies (prevent XSS)
SESSION_COOKIE_HTTPONLY=true

# SameSite cookie attribute
SESSION_COOKIE_SAMESITE=lax
```

## Logging Configuration

### Log Levels

Configure logging verbosity:

```env
# Log levels: debug, info, warning, error
LOG_LEVEL=debug

# Maximum number of log files to keep
LOG_MAX_FILES=5

# Log file size limit (MB)
LOG_MAX_SIZE=10

# Custom log file name
LOG_FILE_NAME=app.log
```

### Log Channels

```env
# Single file logging
LOG_CHANNEL=single

# Daily rotating logs
LOG_CHANNEL=daily

# Syslog integration
LOG_CHANNEL=syslog
```

## Security Configuration

### CSRF Protection

```env
# CSRF token length
CSRF_TOKEN_LENGTH=32

# CSRF token lifetime (seconds)
CSRF_TOKEN_LIFETIME=3600
```

### Password Hashing

```env
# Hashing algorithm: bcrypt, argon2i, argon2id
HASH_ALGORITHM=bcrypt

# Bcrypt cost (4-31, higher = more secure but slower)
BCRYPT_COST=12

# Argon2 memory cost (KB)
ARGON2_MEMORY=65536

# Argon2 time cost
ARGON2_TIME=4

# Argon2 threads
ARGON2_THREADS=3
```

### Security Headers

```env
# Enable security headers
SECURITY_HEADERS_ENABLED=true

# Content Security Policy
CSP_ENABLED=true
CSP_POLICY="default-src 'self'; script-src 'self' 'unsafe-inline'"

# HTTP Strict Transport Security
HSTS_ENABLED=true
HSTS_MAX_AGE=31536000
```

## Mail Configuration

### SMTP Configuration

```env
# Mail driver: smtp, sendmail, log
MAIL_DRIVER=smtp

# SMTP settings
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# From address
MAIL_FROM_ADDRESS=noreply@myapp.com
MAIL_FROM_NAME="My Application"
```

## File Storage Configuration

### Storage Settings

```env
# Default storage disk
STORAGE_DISK=local

# Local storage path
STORAGE_LOCAL_PATH=storage/uploads

# Maximum upload size (MB)
UPLOAD_MAX_SIZE=10

# Allowed file types
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
```

## API Configuration

### API Settings

```env
# API rate limiting
API_RATE_LIMIT=60
API_RATE_LIMIT_WINDOW=60

# API authentication
API_AUTH_ENABLED=true
API_KEY_LENGTH=32

# CORS settings
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE
CORS_ALLOWED_HEADERS=Content-Type,Authorization
```

## Performance Configuration

### Optimization Settings

```env
# Enable output compression
COMPRESSION_ENABLED=true

# Enable asset minification
ASSET_MINIFICATION=true

# Enable view caching
VIEW_CACHE_ENABLED=true

# Enable route caching
ROUTE_CACHE_ENABLED=false

# Enable configuration caching
CONFIG_CACHE_ENABLED=false
```

## Configuration Files

### Custom Configuration Files

Create custom configuration files in the `config/` directory:

```php
<?php
// config/services.php

return [
    'stripe' => [
        'key' => $_ENV['STRIPE_KEY'] ?? '',
        'secret' => $_ENV['STRIPE_SECRET'] ?? '',
    ],
    
    'mailgun' => [
        'domain' => $_ENV['MAILGUN_DOMAIN'] ?? '',
        'secret' => $_ENV['MAILGUN_SECRET'] ?? '',
    ],
];
```

### Accessing Configuration

Access configuration values in your application:

```php
// Get configuration value
$dbHost = config('database.connections.mysql.host');

// Get with default value
$cacheDriver = config('cache.driver', 'file');

// Get environment variable
$appName = env('APP_NAME', 'GuepardoSys');

// Check if configuration exists
if (config_exists('services.stripe.key')) {
    // Stripe is configured
}
```

## Environment-Specific Configuration

### Development Environment

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
CACHE_DRIVER=null
VIEW_CACHE_ENABLED=false
```

### Testing Environment

```env
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=null
LOG_LEVEL=error
```

### Production Environment

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
CACHE_DRIVER=file
VIEW_CACHE_ENABLED=true
CONFIG_CACHE_ENABLED=true
COMPRESSION_ENABLED=true
```

## Configuration Caching

### Enable Configuration Caching

For production environments, cache your configuration:

```bash
# Cache configuration
./guepardo config:cache

# Clear configuration cache
./guepardo config:clear

# Show current configuration
./guepardo config:show
```

### Cached Configuration

When configuration is cached:
- All `.env` values are compiled into a single file
- Configuration loading is significantly faster
- Changes to `.env` require cache clearing

## Security Best Practices

### Environment File Security

1. **Never commit `.env` to version control**
2. **Use strong, unique passwords**
3. **Rotate secrets regularly**
4. **Limit environment variable access**
5. **Use different secrets per environment**

### Production Security

```env
# Production security settings
APP_DEBUG=false
LOG_LEVEL=error
SECURITY_HEADERS_ENABLED=true
SESSION_COOKIE_SECURE=true
CSRF_TOKEN_LENGTH=40
```

## Troubleshooting

### Common Configuration Issues

#### Database Connection Failed
```bash
# Check database configuration
./guepardo config:show database

# Test database connection
./guepardo db:test
```

#### Cache Issues
```bash
# Clear all caches
./guepardo cache:clear

# Rebuild caches
./guepardo optimize
```

#### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### Configuration Validation

```bash
# Validate configuration
./guepardo config:validate

# Check environment requirements
./guepardo env:check
```

## CLI Configuration Commands

### Available Commands

```bash
# Show all configuration
./guepardo config:show

# Show specific configuration
./guepardo config:show database

# Cache configuration
./guepardo config:cache

# Clear configuration cache
./guepardo config:clear

# Validate configuration
./guepardo config:validate

# Check environment
./guepardo env:check
```

---

**⚙️ Proper configuration is essential for a secure and performant GuepardoSys application. Always use environment-specific settings and keep sensitive data secure.**