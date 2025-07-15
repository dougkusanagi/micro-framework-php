# Views

Views contain the HTML served by your application and separate your controller/application logic from your presentation logic. Views are stored in the `app/Views` directory and use PHP as the templating language with some helpful conventions.

## Creating Views

### Basic Views

Views are simple PHP files stored in `app/Views`:

```php
<!-- app/Views/welcome.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GuepardoSys') ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
</body>
</html>
```

### Returning Views from Controllers

Return views from your controllers using the `view()` method:

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('welcome', [
            'title' => 'Welcome to GuepardoSys',
            'message' => 'Your application is ready!'
        ]);
    }
}
```

## Passing Data to Views

### Using Arrays

Pass data to views using associative arrays:

```php
public function show(Request $request)
{
    $user = User::find($request->getRouteParam('id'));
    
    return $this->view('users.show', [
        'user' => $user,
        'title' => 'User Profile: ' . $user->name,
        'isOwner' => $this->getAuthenticatedUserId() === $user->id
    ]);
}
```

### Using Compact

Use PHP's `compact()` function for cleaner code:

```php
public function index(Request $request)
{
    $users = User::all();
    $totalUsers = count($users);
    $title = 'All Users';
    
    return $this->view('users.index', compact('users', 'totalUsers', 'title'));
}
```

### Sharing Data Across Views

Share data across multiple views by setting it in the controller constructor:

```php
class UserController extends BaseController
{
    protected $sharedData = [];
    
    public function __construct()
    {
        $this->sharedData = [
            'appName' => $_ENV['APP_NAME'] ?? 'GuepardoSys',
            'currentUser' => $this->getAuthenticatedUser()
        ];
    }
    
    public function index(Request $request)
    {
        $users = User::all();
        
        return $this->view('users.index', array_merge($this->sharedData, [
            'users' => $users,
            'title' => 'All Users'
        ]));
    }
}
```

## View Organization

### Directory Structure

Organize views in subdirectories for better structure:

```
app/Views/
├── layouts/
│   ├── main.php
│   ├── admin.php
│   └── auth.php
├── partials/
│   ├── header.php
│   ├── footer.php
│   ├── navigation.php
│   └── sidebar.php
├── users/
│   ├── index.php
│   ├── show.php
│   ├── create.php
│   └── edit.php
├── posts/
│   ├── index.php
│   ├── show.php
│   └── create.php
└── errors/
    ├── 404.php
    ├── 500.php
    └── generic.php
```

### Nested Views

Access nested views using dot notation:

```php
// Renders app/Views/users/profile/show.php
return $this->view('users.profile.show', $data);

// Renders app/Views/admin/dashboard/index.php
return $this->view('admin.dashboard.index', $data);
```

## Layouts and Partials

### Creating Layouts

Create reusable layouts to avoid code duplication:

```php
<!-- app/Views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GuepardoSys') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (isset($additionalHead)): ?>
        <?= $additionalHead ?>
    <?php endif; ?>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <?php if (isset($content)): ?>
            <?= $content ?>
        <?php else: ?>
            <!-- Default content area -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <?php if (isset($title)): ?>
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">
                        <?= htmlspecialchars($title) ?>
                    </h1>
                <?php endif; ?>
                
                <!-- Page content goes here -->
                <?php if (isset($pageContent)): ?>
                    <?= $pageContent ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include __DIR__ . '/../partials/footer.php'; ?>
    
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>
```

### Using Layouts

Use layouts by rendering content within them:

```php
public function show(Request $request)
{
    $user = User::find($request->getRouteParam('id'));
    
    // Render the user profile content
    ob_start();
    include APP_PATH . '/Views/users/profile-content.php';
    $profileContent = ob_get_clean();
    
    // Render with layout
    return $this->view('layouts.main', [
        'title' => 'User Profile: ' . $user->name,
        'content' => $profileContent,
        'user' => $user
    ]);
}
```

### Partials

Create reusable partial views:

```php
<!-- app/Views/partials/header.php -->
<header class="bg-white shadow-sm">
    <nav class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="/" class="text-xl font-bold text-gray-900">
                    <?= htmlspecialchars($appName ?? 'GuepardoSys') ?>
                </a>
            </div>
            
            <div class="flex items-center space-x-4">
                <?php if (isset($currentUser) && $currentUser): ?>
                    <span class="text-gray-700">
                        Hello, <?= htmlspecialchars($currentUser->name) ?>
                    </span>
                    <a href="/logout" class="text-red-600 hover:text-red-800">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="/login" class="text-blue-600 hover:text-blue-800">
                        Login
                    </a>
                    <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
```

Include partials in your views:

```php
<!-- app/Views/dashboard.php -->
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1>Dashboard</h1>
    <!-- Dashboard content -->
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
```

## Template Helpers

### Security Helpers

Always escape output to prevent XSS attacks:

```php
<!-- Safe output -->
<h1><?= htmlspecialchars($title) ?></h1>
<p><?= htmlspecialchars($description) ?></p>

<!-- For trusted HTML content -->
<div class="content">
    <?= $trustedHtmlContent ?>
</div>

<!-- URL encoding -->
<a href="<?= urlencode($url) ?>">Link</a>
```

### Custom Helper Functions

Create helper functions for common view tasks:

```php
<?php
// src/Core/View/helpers.php

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function old($key, $default = '')
{
    return $_SESSION['old_input'][$key] ?? $default;
}

function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="_token" value="' . $_SESSION['csrf_token'] . '">';
}

function asset($path)
{
    $baseUrl = $_ENV['APP_URL'] ?? '';
    return $baseUrl . '/assets/' . ltrim($path, '/');
}

function route($path, $params = [])
{
    $url = $path;
    foreach ($params as $key => $value) {
        $url = str_replace('{' . $key . '}', $value, $url);
    }
    return ($_ENV['APP_URL'] ?? '') . $url;
}
```

Usage in views:

```php
<!-- Escaped output -->
<h1><?= escape($title) ?></h1>

<!-- Form with CSRF token -->
<form method="POST" action="/users">
    <?= csrf_token() ?>
    <input type="text" name="name" value="<?= escape(old('name')) ?>">
</form>

<!-- Asset URLs -->
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script src="<?= asset('js/app.js') ?>"></script>

<!-- Route URLs -->
<a href="<?= route('/users/{id}', ['id' => $user->id]) ?>">View User</a>
```

## Forms and Input

### Form Handling

Create forms with proper CSRF protection:

```php
<!-- app/Views/users/create.php -->
<form method="POST" action="/users" class="space-y-4">
    <?= csrf_token() ?>
    
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" id="name" name="name" 
               value="<?= escape(old('name')) ?>"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        <?php if (isset($errors['name'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= escape($errors['name']) ?></p>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email" 
               value="<?= escape(old('email')) ?>"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        <?php if (isset($errors['email'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= escape($errors['email']) ?></p>
        <?php endif; ?>
    </div>
    
    <div>
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create User
        </button>
    </div>
</form>
```

### Error Display

Display validation errors consistently:

```php
<!-- app/Views/partials/errors.php -->
<?php if (isset($errors) && !empty($errors)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <h4 class="font-bold">Please fix the following errors:</h4>
        <ul class="mt-2 list-disc list-inside">
            <?php foreach ($errors as $field => $error): ?>
                <li><?= escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
```

### Flash Messages

Display flash messages:

```php
<!-- app/Views/partials/flash.php -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= escape($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= escape($_SESSION['flash_error']) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_warning'])): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
        <?= escape($_SESSION['flash_warning']) ?>
    </div>
    <?php unset($_SESSION['flash_warning']); ?>
<?php endif; ?>
```

## Conditional Rendering

### Authentication States

Show different content based on authentication:

```php
<?php if (isset($currentUser) && $currentUser): ?>
    <!-- Authenticated user content -->
    <div class="user-menu">
        <span>Welcome, <?= escape($currentUser->name) ?>!</span>
        <a href="/dashboard">Dashboard</a>
        <a href="/profile">Profile</a>
        <a href="/logout">Logout</a>
    </div>
<?php else: ?>
    <!-- Guest user content -->
    <div class="auth-links">
        <a href="/login">Login</a>
        <a href="/register">Register</a>
    </div>
<?php endif; ?>
```

### Permission-Based Rendering

Show content based on user permissions:

```php
<?php if (isset($currentUser) && $currentUser->hasRole('admin')): ?>
    <div class="admin-panel">
        <h3>Admin Panel</h3>
        <a href="/admin/users">Manage Users</a>
        <a href="/admin/settings">Settings</a>
    </div>
<?php endif; ?>

<?php if (isset($currentUser) && ($currentUser->id === $post->user_id || $currentUser->hasRole('admin'))): ?>
    <div class="post-actions">
        <a href="/posts/<?= $post->id ?>/edit">Edit</a>
        <a href="/posts/<?= $post->id ?>/delete" onclick="return confirm('Are you sure?')">Delete</a>
    </div>
<?php endif; ?>
```

## Loops and Iteration

### Displaying Lists

Display collections of data:

```php
<!-- app/Views/users/index.php -->
<div class="users-list">
    <?php if (!empty($users)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($users as $user): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <?= escape($user->name) ?>
                    </h3>
                    <p class="text-gray-600"><?= escape($user->email) ?></p>
                    <div class="mt-4">
                        <a href="/users/<?= $user->id ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            View Profile
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">No users found.</p>
            <a href="/users/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded">
                Create First User
            </a>
        </div>
    <?php endif; ?>
</div>
```

### Table Display

Display data in tables:

```php
<!-- app/Views/users/table.php -->
<div class="overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= escape($user->name) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">
                            <?= escape($user->email) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            <?= escape($user->role) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/users/<?= $user->id ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            View
                        </a>
                        <a href="/users/<?= $user->id ?>/edit" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Edit
                        </a>
                        <a href="/users/<?= $user->id ?>/delete" 
                           class="text-red-600 hover:text-red-900"
                           onclick="return confirm('Are you sure?')">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

## View Caching

### Manual Caching

Implement view caching for better performance:

```php
<?php

class ViewCache
{
    private static $cacheDir = BASE_PATH . '/storage/cache/views/';
    
    public static function get($key)
    {
        $file = self::$cacheDir . md5($key) . '.php';
        
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return file_get_contents($file);
        }
        
        return null;
    }
    
    public static function put($key, $content)
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        
        $file = self::$cacheDir . md5($key) . '.php';
        file_put_contents($file, $content);
    }
    
    public static function clear()
    {
        $files = glob(self::$cacheDir . '*.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
```

Usage in controllers:

```php
public function index(Request $request)
{
    $cacheKey = 'users_index_' . md5(serialize($request->all()));
    $cached = ViewCache::get($cacheKey);
    
    if ($cached) {
        return $cached;
    }
    
    $users = User::all();
    $content = $this->view('users.index', compact('users'));
    
    ViewCache::put($cacheKey, $content);
    
    return $content;
}
```

## Best Practices

### Security

1. **Always escape output** - Use `htmlspecialchars()` or helper functions
2. **Validate CSRF tokens** - Include CSRF protection in forms
3. **Sanitize user input** - Never trust user-provided data
4. **Use HTTPS** - For sensitive data transmission
5. **Implement Content Security Policy** - Prevent XSS attacks

### Performance

1. **Minimize database queries** - Use eager loading when possible
2. **Cache expensive operations** - Cache rendered views when appropriate
3. **Optimize images** - Use appropriate image formats and sizes
4. **Minimize HTTP requests** - Combine CSS and JavaScript files
5. **Use CDNs** - For static assets and libraries

### Maintainability

1. **Keep views simple** - Move complex logic to controllers or models
2. **Use consistent naming** - Follow naming conventions
3. **Organize views logically** - Use subdirectories for related views
4. **Create reusable partials** - Avoid code duplication
5. **Document complex views** - Add comments for complex logic

### Accessibility

1. **Use semantic HTML** - Proper heading structure and landmarks
2. **Include alt text** - For images and media
3. **Ensure keyboard navigation** - All interactive elements should be accessible
4. **Use proper form labels** - Associate labels with form controls
5. **Test with screen readers** - Ensure compatibility with assistive technologies

---

**🎨 Views are the presentation layer of your application. Well-structured views make your application user-friendly and maintainable.**