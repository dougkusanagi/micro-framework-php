# Advanced Error Debugging

GuepardoSys includes a sophisticated error debugging system inspired by Laravel Ignition and Spatie's error handling tools. This system provides detailed error information during development while maintaining security in production.

## Overview

The advanced debugging system provides:

- **Beautiful error pages** with syntax highlighting
- **Interactive stack traces** with source code preview
- **Request context information** including headers, session, and environment
- **Smart error suggestions** based on error types
- **Copy-to-clipboard functionality** for easy sharing
- **Performance metrics** and memory usage
- **Security-conscious** data masking

## Configuration

### Environment Setup

Control debugging features through environment variables:

```env
# Enable/disable debug mode
APP_DEBUG=true

# Debug-specific settings
DEBUG_SHOW_SOURCE=true
DEBUG_CONTEXT_LINES=10
DEBUG_MAX_STRING_LENGTH=1000
DEBUG_HIDE_VENDOR=true
DEBUG_SHOW_PERFORMANCE=true
```

### Debug Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `APP_DEBUG` | Enable detailed error pages | `false` |
| `DEBUG_SHOW_SOURCE` | Show source code in errors | `true` |
| `DEBUG_CONTEXT_LINES` | Lines of code context | `10` |
| `DEBUG_MAX_STRING_LENGTH` | Max length for variable dumps | `1000` |
| `DEBUG_HIDE_VENDOR` | Hide vendor files from stack trace | `true` |
| `DEBUG_SHOW_PERFORMANCE` | Show performance metrics | `true` |

## Error Page Features

### Main Error Display

When an error occurs in debug mode, you'll see:

```
┌─────────────────────────────────────────────────────────────┐
│ 🚨 ErrorException                                           │
│ Undefined variable: nonExistentVariable                     │
│                                                             │
│ app/Controllers/HomeController.php:25                       │
└─────────────────────────────────────────────────────────────┘
```

### Source Code Preview

The error page shows the problematic code with syntax highlighting:

```php
// app/Controllers/HomeController.php

20  public function index(Request $request)
21  {
22      $users = User::all();
23      
24      // This line has an error
25  ❌  return $this->view('home', compact($nonExistentVariable));
26      
27      return $this->view('home', compact('users'));
28  }
```

### Interactive Stack Trace

Click on any stack frame to see:
- Full source code context
- Variable values at that point
- Function arguments
- File path and line number

## Error Types and Handling

### PHP Errors

#### Syntax Errors
```php
// Missing semicolon
$user = User::find(1)  // ❌ Parse error

// The debug page will highlight the exact location
// and suggest adding the missing semicolon
```

#### Runtime Errors
```php
// Undefined variable
echo $undefinedVariable; // ❌ Notice: Undefined variable

// Calling method on null
$user = null;
$user->getName(); // ❌ Fatal error: Call to a member function
```

#### Type Errors
```php
function processUser(User $user) {
    // Process user
}

processUser("not a user"); // ❌ TypeError: Argument must be User
```

### Database Errors

When database errors occur, the debug page shows:

```sql
-- Failed Query
SELECT * FROM users WHERE email = ? AND status = ?

-- Parameters
[0] => "user@example.com"
[1] => "active"

-- Error Message
Table 'myapp.users' doesn't exist
```

### HTTP Errors

#### 404 Not Found
Shows available routes and suggests similar routes:

```
Route not found: GET /usres

Did you mean:
• GET /users
• GET /user/{id}
• POST /users
```

#### 403 Forbidden
Shows authentication status and required permissions:

```
Access denied to: GET /admin/users

Authentication: ✅ Logged in as john@example.com
Authorization: ❌ Missing role: admin
```

### Custom Exceptions

Create informative custom exceptions:

```php
<?php

namespace App\Exceptions;

class UserNotFoundException extends \Exception
{
    public function __construct(int $userId)
    {
        parent::__construct("User with ID {$userId} not found");
        
        // Add context for debugging
        $this->context = [
            'user_id' => $userId,
            'suggestion' => 'Check if the user exists in the database',
            'query' => "SELECT * FROM users WHERE id = {$userId}"
        ];
    }
    
    public function getContext(): array
    {
        return $this->context ?? [];
    }
}
```

Usage:

```php
public function show(Request $request)
{
    $id = $request->getRouteParam('id');
    $user = User::find($id);
    
    if (!$user) {
        throw new UserNotFoundException($id);
    }
    
    return $this->view('users.show', compact('user'));
}
```

## Context Information

### Request Context

The debug page shows comprehensive request information:

```
📨 Request Information
Method: POST
URL: https://myapp.com/users/create
IP: 192.168.1.100
User Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)

📋 Headers
Accept: text/html,application/xhtml+xml
Content-Type: application/x-www-form-urlencoded
Cookie: session_id=abc123...

📝 Form Data
name: "John Doe"
email: "john@example.com"
password: "[HIDDEN]"

🔐 Session Data
user_id: 123
csrf_token: "xyz789..."
flash_messages: ["Welcome!"]
```

### Server Environment

```
🖥️ Server Information
PHP Version: 8.3.0
Memory Limit: 128M
Memory Usage: 45.2M (35.3%)
Execution Time: 0.045s

📁 File Information
Document Root: /var/www/html
Script Path: /var/www/html/public/index.php
Include Path: /var/www/html:/usr/share/php

🌍 Environment Variables
APP_ENV: local
DB_CONNECTION: mysql
CACHE_DRIVER: file
[Sensitive variables hidden]
```

## Error Suggestions

### Smart Suggestions

The system provides context-aware suggestions:

#### Undefined Variable
```
💡 Suggestions for "Undefined variable: $user"

1. Check if the variable is defined before use:
   if (isset($user)) { ... }

2. Initialize the variable:
   $user = null;

3. Check for typos in variable name:
   Did you mean: $users, $userData, $currentUser?
```

#### Database Connection Error
```
💡 Suggestions for "Connection refused"

1. Check database configuration in .env:
   DB_HOST=127.0.0.1
   DB_PORT=3306

2. Verify database server is running:
   sudo service mysql start

3. Test connection manually:
   mysql -h 127.0.0.1 -u username -p
```

#### Missing Class
```
💡 Suggestions for "Class 'App\Models\User' not found"

1. Check if the file exists:
   app/Models/User.php

2. Verify namespace and class name match:
   namespace App\Models;
   class User extends BaseModel

3. Run composer autoload dump:
   composer dump-autoload
```

## Copy Functionality

### Copy Error Details

The debug page includes copy buttons for:

- **Full error details** - Complete error information
- **Stack trace** - Just the stack trace
- **Source code** - Code around the error
- **Request data** - Request context information

### Formatted Output

Copied content is formatted for easy sharing:

```
Error: ErrorException
Message: Undefined variable: nonExistentVariable
File: app/Controllers/HomeController.php:25

Stack Trace:
#0 app/Controllers/HomeController.php(25): handleError()
#1 routes/web.php(12): App\Controllers\HomeController->index()
#2 src/Core/Router.php(45): call_user_func_array()

Request:
Method: GET
URL: http://localhost:8000/
IP: 127.0.0.1

Environment:
PHP: 8.3.0
Framework: GuepardoSys v2.0
Debug: Enabled
```

## Performance Metrics

### Execution Metrics

The debug page shows performance information:

```
⚡ Performance Metrics
Execution Time: 0.045s
Memory Usage: 45.2M / 128M (35.3%)
Peak Memory: 48.1M
Database Queries: 3 (0.012s)
Files Loaded: 127
```

### Query Analysis

Database queries are tracked and displayed:

```sql
-- Query 1 (0.005s)
SELECT * FROM users WHERE active = 1
LIMIT 10

-- Query 2 (0.003s)
SELECT COUNT(*) FROM posts WHERE user_id = ?
Parameters: [123]

-- Query 3 (0.004s)
UPDATE users SET last_login = NOW() WHERE id = ?
Parameters: [123]
```

## Security Features

### Data Masking

Sensitive information is automatically masked:

```php
// Original data
$_POST = [
    'username' => 'john',
    'password' => 'secret123',
    'api_key' => 'sk_live_abc123'
];

// Displayed in debug page
$_POST = [
    'username' => 'john',
    'password' => '[HIDDEN]',
    'api_key' => '[HIDDEN]'
];
```

### Masked Fields

The following fields are automatically masked:
- `password`, `passwd`, `pwd`
- `secret`, `key`, `token`
- `api_key`, `auth_token`
- `credit_card`, `cc_number`
- `ssn`, `social_security`

### Custom Masking

Add custom fields to mask:

```php
// In your configuration
'debug' => [
    'masked_fields' => [
        'custom_secret',
        'internal_token',
        'private_key'
    ]
]
```

## Production Mode

### Error Handling in Production

When `APP_DEBUG=false`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>500 - Server Error</title>
</head>
<body>
    <h1>Something went wrong</h1>
    <p>We're working to fix this issue. Please try again later.</p>
</body>
</html>
```

### Error Logging

Errors are still logged for debugging:

```
[2025-01-15 10:30:45] ERROR: ErrorException: Undefined variable: nonExistentVariable
File: app/Controllers/HomeController.php:25
Stack trace:
#0 app/Controllers/HomeController.php(25): handleError()
#1 routes/web.php(12): App\Controllers\HomeController->index()

Request: GET http://myapp.com/
IP: 192.168.1.100
User: john@example.com (ID: 123)
```

## Custom Error Pages

### Creating Custom Error Pages

Create custom error templates:

```php
<!-- app/Views/errors/404.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error-code { font-size: 72px; color: #e74c3c; }
        .error-message { font-size: 24px; color: #333; }
    </style>
</head>
<body>
    <div class="error-code">404</div>
    <div class="error-message">Page Not Found</div>
    <p>The page you're looking for doesn't exist.</p>
    <a href="/">Go Home</a>
</body>
</html>
```

```php
<!-- app/Views/errors/500.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Server Error</title>
</head>
<body>
    <h1>Oops! Something went wrong</h1>
    <p>We're working to fix this issue.</p>
    <?php if ($_ENV['APP_DEBUG'] ?? false): ?>
        <details>
            <summary>Error Details</summary>
            <pre><?= htmlspecialchars($error ?? 'No error details available') ?></pre>
        </details>
    <?php endif; ?>
</body>
</html>
```

## CLI Debugging Commands

### Debug Commands

Use CLI commands for debugging:

```bash
# Test error handling
./guepardo debug:test-error

# Show current debug configuration
./guepardo debug:config

# Clear error logs
./guepardo debug:clear-logs

# Analyze error patterns
./guepardo debug:analyze

# Test error page rendering
./guepardo debug:render-error "Test error message"
```

## Best Practices

### Development

1. **Always use debug mode** during development
2. **Test error scenarios** to ensure proper handling
3. **Review error logs** regularly
4. **Create meaningful error messages**
5. **Use custom exceptions** for business logic errors

### Production

1. **Disable debug mode** in production
2. **Monitor error logs** for issues
3. **Set up error alerting** for critical errors
4. **Create user-friendly error pages**
5. **Log sufficient context** for debugging

### Security

1. **Never expose sensitive data** in error messages
2. **Validate error page access** in production
3. **Sanitize error output** to prevent XSS
4. **Use HTTPS** for error reporting
5. **Implement rate limiting** for error endpoints

---

**🐛 The advanced debugging system makes development faster and more enjoyable while maintaining security in production environments.**