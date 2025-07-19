# Shared Hosting Deployment

GuepardoSys is specifically designed for shared hosting environments. This guide covers everything you need to deploy your application to shared hosting providers like cPanel, Hostinger, GoDaddy, and others.

## Overview

Shared hosting deployment involves:
- Uploading files to the correct directories
- Configuring the web server document root
- Setting up environment variables
- Optimizing for production
- Managing file permissions

## Pre-Deployment Checklist

### Local Preparation

Before deploying, ensure your application is ready:

```bash
# 1. Run all tests
./guepardo test

# 2. Check code quality
./guepardo quality

# 3. Build production assets
bun run build

# 4. Optimize for production
./guepardo optimize

# 5. Clear development caches
./guepardo cache:clear
```

### Environment Configuration

Create a production `.env` file:

```env
# Production Environment
APP_NAME="My Application"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (provided by hosting provider)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Security
SESSION_COOKIE_SECURE=true
CSRF_TOKEN_LENGTH=40

# Performance
CACHE_DRIVER=file
VIEW_CACHE_ENABLED=true
CONFIG_CACHE_ENABLED=true

# Logging
LOG_LEVEL=error
LOG_MAX_FILES=3
```

## File Structure for Shared Hosting

### Typical Shared Hosting Structure

Most shared hosting providers use this structure:

```
/home/username/
├── public_html/          # Web server document root
│   ├── index.php         # Your app entry point
│   ├── assets/           # CSS, JS, images
│   ├── .htaccess         # Apache configuration
│   └── favicon.ico
├── private/              # Private files (not web accessible)
│   └── myapp/            # Your application files
│       ├── app/
│       ├── bootstrap/
│       ├── config/
│       ├── src/
│       ├── vendor/
│       ├── storage/
│       ├── .env
│       └── composer.json
└── logs/                 # Server logs (varies by provider)
```

### Recommended Structure

For better security, place your application outside the web root:

```
/home/username/
├── public_html/          # Only public files here
│   ├── index.php         # Modified entry point
│   ├── assets/           # Static assets
│   └── .htaccess         # Web server config
└── myapp/                # Application files (secure)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── src/
    ├── vendor/
    ├── storage/
    ├── database/
    ├── .env
    └── composer.json
```

## Step-by-Step Deployment

### Step 1: Upload Application Files

#### Via FTP/SFTP

```bash
# Using rsync (if available)
rsync -avz --exclude='.git' --exclude='node_modules' --exclude='tests' \
  ./ username@yourhost.com:~/myapp/

# Using SCP
scp -r . username@yourhost.com:~/myapp/

# Or use FTP client like FileZilla
# Upload all files except:
# - .git/
# - node_modules/
# - tests/
# - .env (upload separately with production values)
```

#### Via cPanel File Manager

1. **Compress your project** locally (excluding unnecessary files):
   ```bash
   tar -czf myapp.tar.gz --exclude='.git' --exclude='node_modules' --exclude='tests' .
   ```

2. **Upload the archive** through cPanel File Manager

3. **Extract in the correct location**:
   - Extract to `/home/username/myapp/` (outside public_html)
   - Or extract to `/home/username/public_html/myapp/` if required

### Step 2: Configure Public Directory

#### Create Modified Entry Point

Create `/home/username/public_html/index.php`:

```php
<?php

// Define paths for shared hosting
define('BASE_PATH', dirname(__DIR__) . '/myapp');  // Adjust path as needed
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Check if application exists
if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
    die('Application not found. Please check your file paths.');
}

// Load Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
use GuepardoSys\Core\Dotenv;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Bootstrap and run the application
try {
    $container = new GuepardoSys\Core\Container();
    $app = new GuepardoSys\Core\App($container);
    $app->run();
} catch (Exception $e) {
    // Production error handling
    if ($_ENV['APP_DEBUG'] ?? false) {
        throw $e;
    }
    
    http_response_code(500);
    echo '<!DOCTYPE html>
    <html>
    <head><title>Server Error</title></head>
    <body>
        <h1>Something went wrong</h1>
        <p>Please try again later.</p>
    </body>
    </html>';
}
```

#### Copy Static Assets

Copy assets to the public directory:

```bash
# Copy assets to public_html
cp -r myapp/public/assets public_html/
cp myapp/public/.htaccess public_html/
cp myapp/public/favicon.ico public_html/
```

### Step 3: Configure Web Server

#### Apache Configuration (.htaccess)

Create `/home/username/public_html/.htaccess`:

```apache
# Enable URL rewriting
RewriteEngine On

# Handle Angular/React routes (if using SPA)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Prevent directory browsing
Options -Indexes
```

### Step 4: Set File Permissions

Set appropriate permissions for shared hosting:

```bash
# Application files (read-only)
find myapp/ -type f -exec chmod 644 {} \;
find myapp/ -type d -exec chmod 755 {} \;

# Storage directory (writable)
chmod -R 775 myapp/storage/
chmod -R 775 myapp/database/

# Configuration files (secure)
chmod 600 myapp/.env

# Public files
chmod -R 644 public_html/assets/
chmod 644 public_html/index.php
chmod 644 public_html/.htaccess
```

### Step 5: Database Setup

#### Create Database via cPanel

1. **Access MySQL Databases** in cPanel
2. **Create a new database**
3. **Create a database user**
4. **Assign user to database** with all privileges
5. **Note the connection details**

#### Run Migrations

If you have SSH access:

```bash
cd ~/myapp
php guepardo migrate
php guepardo db:seed  # If you have seeders
```

If no SSH access, create a temporary migration script:

```php
<?php
// migrate.php (temporary file)

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

require_once 'vendor/autoload.php';

use GuepardoSys\Core\Dotenv;
use GuepardoSys\Core\Database;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

try {
    $db = Database::connection();
    
    // Run your migration SQL here
    $migrations = [
        "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        // Add more migration SQL as needed
    ];
    
    foreach ($migrations as $sql) {
        $db->statement($sql);
        echo "Executed: " . substr($sql, 0, 50) . "...\n";
    }
    
    echo "Migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

// Delete this file after running
unlink(__FILE__);
?>
```

Access `https://yourdomain.com/migrate.php` once, then delete the file.

## Provider-Specific Instructions

### cPanel Hosting

#### File Manager Method

1. **Upload compressed application**
2. **Extract outside public_html**
3. **Copy public files to public_html**
4. **Set up database via MySQL Databases**
5. **Configure .env file**

#### Subdomain Setup

For subdomain deployment (e.g., app.yourdomain.com):

1. **Create subdomain** in cPanel
2. **Point subdomain to application directory**
3. **Upload files to subdomain directory**
4. **Configure accordingly**

### Hostinger

```bash
# Typical Hostinger structure
/domains/yourdomain.com/
├── public_html/          # Web root
└── private_html/         # Private files (use this for app)
```

### GoDaddy

```bash
# GoDaddy structure
/home/username/
├── public_html/          # Web root
└── app/                  # Place application here
```

### SiteGround

```bash
# SiteGround structure
/home/customer/www/yourdomain.com/
├── public_html/          # Web root
└── private/              # Private files
```

## Optimization for Production

### Enable Production Optimizations

Create an optimization script:

```php
<?php
// optimize.php (run once after deployment)

define('BASE_PATH', __DIR__);
require_once 'vendor/autoload.php';

use GuepardoSys\Core\Dotenv;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Cache configuration
$config = [];
$configFiles = glob(BASE_PATH . '/config/*.php');
foreach ($configFiles as $file) {
    $key = basename($file, '.php');
    $config[$key] = require $file;
}

file_put_contents(
    BASE_PATH . '/storage/cache/config.php',
    '<?php return ' . var_export($config, true) . ';'
);

// Cache routes
$routes = require BASE_PATH . '/routes/web.php';
file_put_contents(
    BASE_PATH . '/storage/cache/routes.php',
    '<?php return ' . var_export($routes, true) . ';'
);

// Generate optimized autoloader
exec('cd ' . BASE_PATH . ' && composer dump-autoload --optimize --classmap-authoritative');

echo "Production optimization completed!\n";

// Delete this file
unlink(__FILE__);
?>
```

### Performance Tweaks

#### Enable OPcache

Add to `.htaccess` if supported:

```apache
# Enable OPcache
<IfModule mod_php.c>
    php_value opcache.enable 1
    php_value opcache.memory_consumption 128
    php_value opcache.max_accelerated_files 4000
    php_value opcache.revalidate_freq 60
</IfModule>
```

#### Optimize PHP Settings

```apache
# PHP optimizations
<IfModule mod_php.c>
    php_value memory_limit 128M
    php_value max_execution_time 30
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>
```

## Monitoring and Maintenance

### Health Check Script

Create a health check endpoint:

```php
<?php
// health.php

define('BASE_PATH', dirname(__DIR__) . '/myapp');
require_once BASE_PATH . '/vendor/autoload.php';

use GuepardoSys\Core\Dotenv;
use GuepardoSys\Core\Database;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

$checks = [
    'app' => false,
    'database' => false,
    'storage' => false,
];

// Check application
try {
    $checks['app'] = file_exists(BASE_PATH . '/vendor/autoload.php');
} catch (Exception $e) {
    // App check failed
}

// Check database
try {
    $db = Database::connection();
    $db->select('SELECT 1');
    $checks['database'] = true;
} catch (Exception $e) {
    // Database check failed
}

// Check storage
$checks['storage'] = is_writable(BASE_PATH . '/storage');

$allHealthy = array_reduce($checks, function($carry, $check) {
    return $carry && $check;
}, true);

http_response_code($allHealthy ? 200 : 500);
header('Content-Type: application/json');

echo json_encode([
    'status' => $allHealthy ? 'healthy' : 'unhealthy',
    'checks' => $checks,
    'timestamp' => date('c')
]);
?>
```

### Log Monitoring

Set up log rotation:

```php
<?php
// log-rotate.php (run via cron if available)

$logDir = __DIR__ . '/storage/logs';
$maxFiles = 5;
$maxSize = 10 * 1024 * 1024; // 10MB

$logFiles = glob($logDir . '/*.log');

foreach ($logFiles as $logFile) {
    if (filesize($logFile) > $maxSize) {
        // Rotate log
        $rotated = $logFile . '.' . date('Y-m-d-H-i-s');
        rename($logFile, $rotated);
        
        // Clean old rotated logs
        $pattern = $logFile . '.*';
        $rotatedFiles = glob($pattern);
        
        if (count($rotatedFiles) > $maxFiles) {
            usort($rotatedFiles, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $toDelete = array_slice($rotatedFiles, 0, -$maxFiles);
            foreach ($toDelete as $file) {
                unlink($file);
            }
        }
    }
}
?>
```

## Troubleshooting

### Common Issues

#### "Internal Server Error"

1. **Check error logs** (usually in cPanel Error Logs)
2. **Verify file permissions**
3. **Check .htaccess syntax**
4. **Ensure PHP version compatibility**

#### "Database Connection Failed"

1. **Verify database credentials**
2. **Check database server hostname**
3. **Ensure database exists**
4. **Test connection manually**

#### "File Not Found" Errors

1. **Check file paths in index.php**
2. **Verify file upload completed**
3. **Check case sensitivity**
4. **Ensure all dependencies uploaded**

#### Performance Issues

1. **Enable caching**
2. **Optimize database queries**
3. **Compress assets**
4. **Use CDN for static files**

### Debug Mode for Deployment

Temporarily enable debug mode to diagnose issues:

```env
# Temporarily set for debugging
APP_DEBUG=true
LOG_LEVEL=debug
```

**Remember to disable debug mode in production!**

### Backup Strategy

#### Automated Backups

Create a backup script:

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/username/backups"
APP_DIR="/home/username/myapp"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup files
tar -czf $BACKUP_DIR/app_$DATE.tar.gz -C $APP_DIR .

# Backup database
mysqldump -u username -p password database_name > $BACKUP_DIR/db_$DATE.sql

# Keep only last 7 backups
find $BACKUP_DIR -name "app_*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "db_*.sql" -mtime +7 -delete
```

## Security Considerations

### File Security

1. **Keep application files outside web root**
2. **Set restrictive file permissions**
3. **Protect sensitive files with .htaccess**
4. **Use HTTPS for all traffic**
5. **Regular security updates**

### Environment Security

```env
# Production security settings
APP_DEBUG=false
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_HTTPONLY=true
SESSION_COOKIE_SAMESITE=strict
CSRF_TOKEN_LENGTH=40
```

### Access Control

```apache
# Restrict access to admin areas
<Directory "/home/username/public_html/admin">
    AuthType Basic
    AuthName "Admin Area"
    AuthUserFile /home/username/.htpasswd
    Require valid-user
</Directory>
```

---

**🚀 Shared hosting deployment requires careful attention to file structure and security, but GuepardoSys makes it straightforward with its lightweight architecture.**