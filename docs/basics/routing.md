# Routing

GuepardoSys provides a simple yet powerful routing system that allows you to define URL patterns and map them to controllers or closures. Routes are the entry points to your application.

## Basic Routing

### Defining Routes

Routes are defined in the `routes/web.php` file as an array of route definitions:

```php
<?php
// routes/web.php

return [
    // [HTTP_METHOD, PATH, HANDLER]
    ['GET', '/', ['App\Controllers\HomeController', 'index']],
    ['POST', '/contact', ['App\Controllers\ContactController', 'store']],
    ['GET', '/about', function($request) {
        return 'About page content';
    }],
];
```

### Available HTTP Methods

GuepardoSys supports all standard HTTP methods:

```php
return [
    ['GET', '/users', ['App\Controllers\UserController', 'index']],
    ['POST', '/users', ['App\Controllers\UserController', 'store']],
    ['PUT', '/users/{id}', ['App\Controllers\UserController', 'update']],
    ['PATCH', '/users/{id}', ['App\Controllers\UserController', 'patch']],
    ['DELETE', '/users/{id}', ['App\Controllers\UserController', 'destroy']],
];
```

## Route Parameters

### Basic Parameters

Capture dynamic segments of the URL using curly braces:

```php
return [
    ['GET', '/users/{id}', ['App\Controllers\UserController', 'show']],
    ['GET', '/posts/{slug}', ['App\Controllers\PostController', 'show']],
    ['GET', '/categories/{category}/posts/{id}', ['App\Controllers\PostController', 'showInCategory']],
];
```

### Accessing Parameters in Controllers

Route parameters are available through the Request object:

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;

class UserController extends BaseController
{
    public function show(Request $request)
    {
        $id = $request->getRouteParam('id');
        
        // Find user by ID
        $user = User::find($id);
        
        if (!$user) {
            return $this->response('User not found', 404);
        }
        
        return $this->view('users.show', compact('user'));
    }
    
    public function showInCategory(Request $request)
    {
        $category = $request->getRouteParam('category');
        $id = $request->getRouteParam('id');
        
        // Your logic here
        return $this->view('posts.show', compact('category', 'id'));
    }
}
```

### Optional Parameters

Create optional parameters by providing default values in your controller:

```php
return [
    ['GET', '/posts/{page}', ['App\Controllers\PostController', 'index']],
    ['GET', '/posts', ['App\Controllers\PostController', 'index']], // page defaults to 1
];
```

```php
public function index(Request $request)
{
    $page = $request->getRouteParam('page', 1); // Default to page 1
    $posts = Post::paginate($page, 10);
    
    return $this->view('posts.index', compact('posts', 'page'));
}
```

## Closure Routes

### Simple Closures

For simple routes, you can use closures instead of controllers:

```php
return [
    ['GET', '/hello', function($request) {
        return 'Hello, World!';
    }],
    
    ['GET', '/time', function($request) {
        return 'Current time: ' . date('Y-m-d H:i:s');
    }],
    
    ['GET', '/json', function($request) {
        return json_encode(['message' => 'Hello from JSON']);
    }],
];
```

### Closures with Parameters

Closures can also access route parameters:

```php
return [
    ['GET', '/hello/{name}', function($request) {
        $name = $request->getRouteParam('name');
        return "Hello, {$name}!";
    }],
    
    ['GET', '/calculate/{a}/{b}', function($request) {
        $a = (int) $request->getRouteParam('a');
        $b = (int) $request->getRouteParam('b');
        return "Result: " . ($a + $b);
    }],
];
```

## RESTful Routes

### Resource Routes

Create RESTful routes for resources:

```php
return [
    // Users resource
    ['GET', '/users', ['App\Controllers\UserController', 'index']],        // List all users
    ['GET', '/users/create', ['App\Controllers\UserController', 'create']], // Show create form
    ['POST', '/users', ['App\Controllers\UserController', 'store']],        // Create new user
    ['GET', '/users/{id}', ['App\Controllers\UserController', 'show']],     // Show specific user
    ['GET', '/users/{id}/edit', ['App\Controllers\UserController', 'edit']], // Show edit form
    ['PUT', '/users/{id}', ['App\Controllers\UserController', 'update']],   // Update user
    ['DELETE', '/users/{id}', ['App\Controllers\UserController', 'destroy']], // Delete user
];
```

### API Routes

For API endpoints, create a separate routes file:

```php
<?php
// routes/api.php

return [
    ['GET', '/api/users', ['App\Controllers\Api\UserController', 'index']],
    ['POST', '/api/users', ['App\Controllers\Api\UserController', 'store']],
    ['GET', '/api/users/{id}', ['App\Controllers\Api\UserController', 'show']],
    ['PUT', '/api/users/{id}', ['App\Controllers\Api\UserController', 'update']],
    ['DELETE', '/api/users/{id}', ['App\Controllers\Api\UserController', 'destroy']],
];
```

## Route Groups

### Organizing Routes

While GuepardoSys doesn't have built-in route groups, you can organize routes logically:

```php
return [
    // Public routes
    ['GET', '/', ['App\Controllers\HomeController', 'index']],
    ['GET', '/about', ['App\Controllers\HomeController', 'about']],
    ['GET', '/contact', ['App\Controllers\ContactController', 'show']],
    ['POST', '/contact', ['App\Controllers\ContactController', 'store']],
    
    // User authentication routes
    ['GET', '/login', ['App\Controllers\AuthController', 'showLogin']],
    ['POST', '/login', ['App\Controllers\AuthController', 'login']],
    ['POST', '/logout', ['App\Controllers\AuthController', 'logout']],
    ['GET', '/register', ['App\Controllers\AuthController', 'showRegister']],
    ['POST', '/register', ['App\Controllers\AuthController', 'register']],
    
    // Protected user routes
    ['GET', '/dashboard', ['App\Controllers\DashboardController', 'index']],
    ['GET', '/profile', ['App\Controllers\ProfileController', 'show']],
    ['PUT', '/profile', ['App\Controllers\ProfileController', 'update']],
    
    // Admin routes
    ['GET', '/admin', ['App\Controllers\AdminController', 'index']],
    ['GET', '/admin/users', ['App\Controllers\AdminController', 'users']],
    ['GET', '/admin/settings', ['App\Controllers\AdminController', 'settings']],
];
```

## Route Patterns

### Common Patterns

Here are some common routing patterns:

#### Blog Routes
```php
return [
    ['GET', '/', ['App\Controllers\BlogController', 'index']],
    ['GET', '/posts', ['App\Controllers\PostController', 'index']],
    ['GET', '/posts/{slug}', ['App\Controllers\PostController', 'show']],
    ['GET', '/categories/{category}', ['App\Controllers\CategoryController', 'show']],
    ['GET', '/tags/{tag}', ['App\Controllers\TagController', 'show']],
    ['GET', '/archive/{year}/{month}', ['App\Controllers\ArchiveController', 'show']],
];
```

#### E-commerce Routes
```php
return [
    ['GET', '/products', ['App\Controllers\ProductController', 'index']],
    ['GET', '/products/{id}', ['App\Controllers\ProductController', 'show']],
    ['GET', '/categories/{category}', ['App\Controllers\CategoryController', 'products']],
    ['GET', '/cart', ['App\Controllers\CartController', 'show']],
    ['POST', '/cart/add/{product}', ['App\Controllers\CartController', 'add']],
    ['DELETE', '/cart/remove/{item}', ['App\Controllers\CartController', 'remove']],
    ['GET', '/checkout', ['App\Controllers\CheckoutController', 'show']],
    ['POST', '/checkout', ['App\Controllers\CheckoutController', 'process']],
];
```

#### User Management Routes
```php
return [
    ['GET', '/users', ['App\Controllers\UserController', 'index']],
    ['GET', '/users/{id}', ['App\Controllers\UserController', 'profile']],
    ['GET', '/users/{id}/posts', ['App\Controllers\UserController', 'posts']],
    ['GET', '/users/{id}/followers', ['App\Controllers\UserController', 'followers']],
    ['GET', '/users/{id}/following', ['App\Controllers\UserController', 'following']],
    ['POST', '/users/{id}/follow', ['App\Controllers\FollowController', 'follow']],
    ['DELETE', '/users/{id}/unfollow', ['App\Controllers\FollowController', 'unfollow']],
];
```

## Route Helpers

### URL Generation

Create helper functions for generating URLs:

```php
<?php
// src/Core/helpers.php

function route($name, $params = [])
{
    // Simple URL generation
    $url = $name;
    
    foreach ($params as $key => $value) {
        $url = str_replace('{' . $key . '}', $value, $url);
    }
    
    return $_ENV['APP_URL'] . $url;
}

function url($path)
{
    return $_ENV['APP_URL'] . '/' . ltrim($path, '/');
}
```

Usage in views:

```php
<!-- Generate URLs -->
<a href="<?= route('/users/{id}', ['id' => $user->id]) ?>">View User</a>
<a href="<?= url('/about') ?>">About Us</a>

<!-- Form actions -->
<form action="<?= route('/users/{id}', ['id' => $user->id]) ?>" method="POST">
    <!-- form fields -->
</form>
```

## Route Caching

### Performance Optimization

For production environments, you can implement route caching:

```php
<?php
// src/Core/RouteCache.php

class RouteCache
{
    private static $cacheFile = BASE_PATH . '/storage/cache/routes.php';
    
    public static function cache(array $routes)
    {
        $content = '<?php return ' . var_export($routes, true) . ';';
        file_put_contents(self::$cacheFile, $content);
    }
    
    public static function load()
    {
        if (file_exists(self::$cacheFile)) {
            return require self::$cacheFile;
        }
        
        return null;
    }
    
    public static function clear()
    {
        if (file_exists(self::$cacheFile)) {
            unlink(self::$cacheFile);
        }
    }
}
```

## Route Testing

### Testing Routes

Test your routes using the testing framework:

```php
<?php
// tests/Feature/RouteTest.php

test('home page loads successfully', function () {
    $response = $this->get('/');
    expect($response->getStatusCode())->toBe(200);
});

test('user profile page loads', function () {
    $response = $this->get('/users/1');
    expect($response->getStatusCode())->toBe(200);
});

test('non-existent route returns 404', function () {
    $response = $this->get('/non-existent-page');
    expect($response->getStatusCode())->toBe(404);
});

test('post creation requires authentication', function () {
    $response = $this->post('/posts', ['title' => 'Test Post']);
    expect($response->getStatusCode())->toBe(302); // Redirect to login
});
```

## CLI Route Commands

### Route Management

Use CLI commands to manage routes:

```bash
# List all routes
./guepardo route:list

# List routes for specific method
./guepardo route:list --method=GET

# List routes matching pattern
./guepardo route:list --filter=users

# Cache routes for production
./guepardo route:cache

# Clear route cache
./guepardo route:clear
```

## Advanced Routing

### Custom Route Patterns

For more complex routing needs, you can extend the router:

```php
<?php
// src/Core/AdvancedRouter.php

class AdvancedRouter extends Router
{
    public function addRouteWithConstraints(string $method, string $path, $handler, array $constraints = [])
    {
        // Add route with parameter constraints
        // e.g., ['id' => '\d+'] to ensure ID is numeric
    }
    
    public function addSubdomainRoute(string $subdomain, string $method, string $path, $handler)
    {
        // Handle subdomain routing
        // e.g., api.example.com, admin.example.com
    }
}
```

### Route Model Binding

Automatically inject models based on route parameters:

```php
<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function show(Request $request)
    {
        $id = $request->getRouteParam('id');
        
        // Automatic model binding
        $user = User::findOrFail($id); // Throws 404 if not found
        
        return $this->view('users.show', compact('user'));
    }
}
```

## Best Practices

### Route Organization

1. **Group related routes together**
2. **Use consistent naming conventions**
3. **Keep routes file organized and commented**
4. **Use RESTful conventions when appropriate**
5. **Separate API routes from web routes**

### Performance Tips

1. **Cache routes in production**
2. **Use specific HTTP methods**
3. **Avoid overly complex route patterns**
4. **Consider route order for performance**

### Security Considerations

1. **Validate route parameters**
2. **Use HTTPS for sensitive routes**
3. **Implement proper authentication**
4. **Sanitize user input**

---

**🛣️ Routing is the foundation of your GuepardoSys application. Well-designed routes make your application intuitive and maintainable.**