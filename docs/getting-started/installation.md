# Installation

## System Requirements

GuepardoSys Micro PHP Framework has minimal requirements, making it perfect for shared hosting environments:

- **PHP**: 8.3 or higher (8.4 recommended)
- **Composer**: For dependency management
- **Bun**: For frontend asset compilation (optional)
- **Web Server**: Apache, Nginx, or PHP built-in server
- **Database**: MySQL 8.0+ or PostgreSQL 12+ (optional)

### Checking Your Environment

```bash
# Check PHP version
php -v

# Check required PHP extensions
php -m | grep -E "(pdo|json|mbstring|openssl)"

# Check Composer
composer --version

# Check Bun (optional)
bun --version
```

## Installation Methods

### Method 1: Git Clone (Recommended)

```bash
# Clone the repository
git clone https://github.com/your-username/guepardosys-micro-php.git my-project
cd my-project

# Install PHP dependencies
composer install

# Install frontend dependencies (optional)
bun install

# Set up environment
cp .env.example .env

# Set permissions (Linux/Mac)
chmod +x guepardo
chmod -R 775 storage/
chmod -R 775 public/assets/
```

### Method 2: Composer Create-Project

```bash
# Create new project via Composer
composer create-project guepardosys/micro-php my-project

# Navigate to project
cd my-project

# Set up environment
cp .env.example .env
```

### Method 3: Direct Download

```bash
# Download and extract
curl -L https://github.com/your-username/guepardosys-micro-php/archive/main.zip -o framework.zip
unzip framework.zip
cd guepardosys-micro-php-main

# Install dependencies
composer install
```

## Configuration

### Environment Configuration

Edit your `.env` file with your application settings:

```env
# Application Settings
APP_NAME="My Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (optional)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=my_user
DB_PASSWORD=my_password

# Cache Settings
CACHE_DRIVER=file
CACHE_TTL=3600

# Logging
LOG_LEVEL=debug
LOG_MAX_FILES=5
```

### Database Setup (Optional)

If you plan to use a database:

#### MySQL Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE my_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
./guepardo migrate

# Seed sample data (optional)
./guepardo db:seed
```

#### PostgreSQL Setup
```bash
# Create database
createdb my_database

# Update .env for PostgreSQL
DB_CONNECTION=pgsql
DB_PORT=5432

# Run migrations
./guepardo migrate
```

### Frontend Assets (Optional)

If you want to use the included Tailwind CSS and Alpine.js setup:

```bash
# Development build
bun run dev

# Production build
bun run build

# Watch mode (auto-rebuild on changes)
bun run watch
```

## Verification

### Start Development Server

```bash
# Start the built-in development server
./guepardo serve

# Custom host and port
./guepardo serve 127.0.0.1 3000
```

Visit `http://localhost:8000` to see your application running.

### Run Tests

```bash
# Run the test suite
./guepardo test

# Run code quality checks
./guepardo quality

# Check available routes
./guepardo route:list
```

## Web Server Configuration

### Apache Configuration

The framework includes a `.htaccess` file in the `public` directory. Ensure `mod_rewrite` is enabled:

```apache
# Enable mod_rewrite
sudo a2enmod rewrite

# Example VirtualHost
<VirtualHost *:80>
    ServerName myapp.local
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name myapp.local;
    root /path/to/project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Shared Hosting Setup

GuepardoSys is designed for shared hosting. Here's how to deploy:

### File Structure for Shared Hosting

```
public_html/              # Your hosting document root
├── index.php            # Copy from public/index.php
├── assets/              # Copy from public/assets/
├── .htaccess           # Copy from public/.htaccess
└── framework/          # Upload entire framework here
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── src/
    ├── vendor/
    └── ...
```

### Deployment Script

```bash
#!/bin/bash
# deploy.sh

# Upload files (adjust paths as needed)
rsync -avz --exclude='.git' --exclude='node_modules' ./ user@server:~/public_html/framework/

# Move public files to document root
ssh user@server "
cd ~/public_html
cp framework/public/index.php ./
cp -r framework/public/assets ./
cp framework/public/.htaccess ./
"

# Optimize for production
ssh user@server "cd ~/public_html/framework && ./guepardo optimize"
```

## Troubleshooting

### Common Issues

#### "Permission denied: ./guepardo"
```bash
chmod +x guepardo
```

#### "Storage directory not writable"
```bash
chmod -R 775 storage/
```

#### "Database connection failed"
```bash
# Check your .env database settings
# Verify database exists and credentials are correct
./guepardo config:show
```

#### "Assets not loading"
```bash
# Rebuild assets
bun run build

# Check permissions
chmod -R 755 public/assets/
```

#### "Views not found"
```bash
# Clear view cache
./guepardo cache:clear

# Check view file exists
ls -la app/Views/
```

### Getting Help

- Check the [FAQ](../faq.md)
- Review [Common Issues](../troubleshooting.md)
- Join our [Community Forum](https://github.com/your-username/guepardosys-micro-php/discussions)

## Next Steps

Now that you have GuepardoSys installed:

1. **[Quick Start Guide](quickstart.md)** - Build your first application
2. **[Directory Structure](structure.md)** - Understand the framework layout
3. **[Configuration](configuration.md)** - Learn about configuration options
4. **[Routing](../basics/routing.md)** - Define your application routes

---

**🎉 Congratulations! You've successfully installed GuepardoSys Micro PHP Framework.**