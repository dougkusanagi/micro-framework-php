# Controllers

Controllers are the heart of your GuepardoSys application. They handle HTTP requests, process business logic, and return responses. Controllers keep your routes clean and organize your application logic.

## Basic Controllers

### Creating Controllers

Generate a new controller using the CLI:

```bash
# Create a basic controller
./guepardo make:controller UserController

# Create a controller in a subdirectory
./guepardo make:controller Admin/UserController
```

### Basic Controller Structure

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        // List all users
        $users = User::all();
        return $this->view('users.index', compact('users'));
    }
    
    public function show(Request $request)
    {
        // Show a specific user
        $id = $request->getRouteParam('id');
        $user = User::find($id);
        
        if (!$user) {
            return $this->response('User not found', 404);
        }
        
        return $this->view('users.show', compact('user'));
    }
    
    public function create(Request $request)
    {
        // Show create form
        return $this->view('users.create');
    }
    
    public function store(Request $request)
    {
        // Create a new user
        $data = $request->all();
        
        // Validation would go here
        $user = User::create($data);
        
        return $this->redirect('/users/' . $user->id);
    }
}
```

## BaseController Features

### Available Methods

The `BaseController` class provides many helpful methods:

#### View Methods
```php
// Render a view
return $this->view('template', $data);

// Render with specific layout
return $this->view('template', $data, 'layouts.admin');
```

#### Response Methods
```php
// JSON response
return $this->json(['status' => 'success', 'data' => $data]);

// Redirect responses
return $this->redirect('/path');
return $this->redirectBack();

// HTTP status responses
return $this->response('Content', 200);
return $this->notFound(); // 404
return $this->forbidden(); // 403
return $this->serverError(); // 500
```

#### Authentication Methods
```php
// Check if user is authenticated
if ($this->isAuthenticated()) {
    // User is logged in
}

// Get authenticated user
$user = $this->getAuthenticatedUser();

// Require authentication (redirect if not authenticated)
$this->requireAuth('/intended-url');
```

## Request Handling

### Accessing Request Data

```php
public function store(Request $request)
{
    // Get all input data
    $data = $request->all();
    
    // Get specific input
    $name = $request->input('name');
    $email = $request->input('email', 'default@example.com'); // With default
    
    // Get route parameters
    $id = $request->getRouteParam('id');
    
    // Check request method
    if ($request->isPost()) {
        // Handle POST request
    }
    
    // Get request headers
    $userAgent = $request->getHeader('User-Agent');
    
    // Check for AJAX requests
    if ($request->isAjax()) {
        return $this->json(['status' => 'success']);
    }
}
```

### File Uploads

```php
public function uploadAvatar(Request $request)
{
    $file = $request->file('avatar');
    
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = BASE_PATH . '/storage/uploads/';
        $filename = uniqid() . '_' . $file['name'];
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // File uploaded successfully
            $user = $this->getAuthenticatedUser();
            $user->avatar = $filename;
            $user->save();
            
            return $this->json(['status' => 'success', 'filename' => $filename]);
        }
    }
    
    return $this->json(['status' => 'error', 'message' => 'Upload failed'], 400);
}
```

## Response Types

### HTML Responses

```php
public function show(Request $request)
{
    $user = User::find($request->getRouteParam('id'));
    
    // Simple view response
    return $this->view('users.show', compact('user'));
}
```

### JSON Responses

```php
public function apiIndex(Request $request)
{
    $users = User::all();
    
    return $this->json([
        'status' => 'success',
        'data' => $users,
        'count' => count($users)
    ]);
}

public function apiShow(Request $request)
{
    $user = User::find($request->getRouteParam('id'));
    
    if (!$user) {
        return $this->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }
    
    return $this->json([
        'status' => 'success',
        'data' => $user
    ]);
}
```

### Redirect Responses

```php
public function store(Request $request)
{
    $user = User::create($request->all());
    
    // Redirect to user profile
    return $this->redirect('/users/' . $user->id);
}

public function update(Request $request)
{
    $user = User::find($request->getRouteParam('id'));
    $user->update($request->all());
    
    // Redirect back to previous page
    return $this->redirectBack();
}
```

## Data Validation

### Basic Validation

```php
public function store(Request $request)
{
    $data = $request->all();
    $errors = [];
    
    // Manual validation
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (!empty($errors)) {
        return $this->view('users.create', [
            'errors' => $errors,
            'old' => $data
        ]);
    }
    
    // Create user
    $user = User::create($data);
    return $this->redirect('/users/' . $user->id);
}
```

### Validation Helper

Create a validation helper for reusable validation logic:

```php
<?php

namespace App\Controllers;

trait ValidatesRequests
{
    protected function validate(array $data, array $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int) $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int) $matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                }
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        return $data;
    }
}
```

Usage:

```php
class UserController extends BaseController
{
    use ValidatesRequests;
    
    public function store(Request $request)
    {
        try {
            $data = $this->validate($request->all(), [
                'name' => 'required|min:2|max:255',
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);
            
            $user = User::create($data);
            return $this->redirect('/users/' . $user->id);
            
        } catch (ValidationException $e) {
            return $this->view('users.create', [
                'errors' => $e->getErrors(),
                'old' => $request->all()
            ]);
        }
    }
}
```

## Resource Controllers

### RESTful Controllers

Create controllers that follow RESTful conventions:

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;
use App\Models\Post;

class PostController extends BaseController
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $posts = Post::orderBy('created_at', 'DESC')->get();
        return $this->view('posts.index', compact('posts'));
    }
    
    /**
     * Show the form for creating a new post
     */
    public function create(Request $request)
    {
        $this->requireAuth();
        return $this->view('posts.create');
    }
    
    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        $this->requireAuth();
        
        $data = $this->validate($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ]);
        
        $data['user_id'] = $this->getAuthenticatedUserId();
        $post = Post::create($data);
        
        return $this->redirect('/posts/' . $post->id);
    }
    
    /**
     * Display the specified post
     */
    public function show(Request $request)
    {
        $id = $request->getRouteParam('id');
        $post = Post::find($id);
        
        if (!$post) {
            return $this->notFound();
        }
        
        return $this->view('posts.show', compact('post'));
    }
    
    /**
     * Show the form for editing the post
     */
    public function edit(Request $request)
    {
        $this->requireAuth();
        
        $id = $request->getRouteParam('id');
        $post = Post::find($id);
        
        if (!$post) {
            return $this->notFound();
        }
        
        // Check if user owns the post
        if ($post->user_id !== $this->getAuthenticatedUserId()) {
            return $this->forbidden();
        }
        
        return $this->view('posts.edit', compact('post'));
    }
    
    /**
     * Update the specified post
     */
    public function update(Request $request)
    {
        $this->requireAuth();
        
        $id = $request->getRouteParam('id');
        $post = Post::find($id);
        
        if (!$post || $post->user_id !== $this->getAuthenticatedUserId()) {
            return $this->forbidden();
        }
        
        $data = $this->validate($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ]);
        
        $post->update($data);
        
        return $this->redirect('/posts/' . $post->id);
    }
    
    /**
     * Remove the specified post
     */
    public function destroy(Request $request)
    {
        $this->requireAuth();
        
        $id = $request->getRouteParam('id');
        $post = Post::find($id);
        
        if (!$post || $post->user_id !== $this->getAuthenticatedUserId()) {
            return $this->forbidden();
        }
        
        $post->delete();
        
        return $this->redirect('/posts');
    }
}
```

## API Controllers

### API-Specific Controllers

Create controllers specifically for API endpoints:

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use GuepardoSys\Core\Request;
use App\Models\User;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $limit;
        
        $users = User::limit($limit)->offset($offset)->get();
        $total = User::count();
        
        return $this->json([
            'status' => 'success',
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    public function show(Request $request)
    {
        $id = $request->getRouteParam('id');
        $user = User::find($id);
        
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        
        return $this->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
    
    public function store(Request $request)
    {
        try {
            $data = $this->validate($request->all(), [
                'name' => 'required|min:2|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8'
            ]);
            
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $user = User::create($data);
            
            return $this->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
            
        } catch (ValidationException $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->getErrors()
            ], 422);
        }
    }
    
    public function update(Request $request)
    {
        $id = $request->getRouteParam('id');
        $user = User::find($id);
        
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        
        try {
            $data = $this->validate($request->all(), [
                'name' => 'min:2|max:255',
                'email' => 'email'
            ]);
            
            $user->update($data);
            
            return $this->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user
            ]);
            
        } catch (ValidationException $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->getErrors()
            ], 422);
        }
    }
    
    public function destroy(Request $request)
    {
        $id = $request->getRouteParam('id');
        $user = User::find($id);
        
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        
        $user->delete();
        
        return $this->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }
}
```

## Controller Organization

### Subdirectories

Organize controllers in subdirectories:

```
app/Controllers/
├── Admin/
│   ├── DashboardController.php
│   ├── UserController.php
│   └── SettingsController.php
├── Api/
│   ├── AuthController.php
│   ├── UserController.php
│   └── PostController.php
├── Auth/
│   ├── LoginController.php
│   ├── RegisterController.php
│   └── PasswordController.php
└── BaseController.php
```

### Namespacing

Use proper namespaces for organized controllers:

```php
<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use GuepardoSys\Core\Request;

class DashboardController extends BaseController
{
    public function __construct()
    {
        // Require admin authentication for all methods
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    public function index(Request $request)
    {
        $stats = [
            'users' => User::count(),
            'posts' => Post::count(),
            'comments' => Comment::count()
        ];
        
        return $this->view('admin.dashboard', compact('stats'));
    }
}
```

## Testing Controllers

### Controller Tests

Test your controllers using the testing framework:

```php
<?php
// tests/Feature/UserControllerTest.php

test('user index page loads', function () {
    $response = $this->get('/users');
    expect($response->getStatusCode())->toBe(200);
});

test('user can be created', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123'
    ];
    
    $response = $this->post('/users', $userData);
    expect($response->getStatusCode())->toBe(302); // Redirect after creation
});

test('user creation requires valid data', function () {
    $response = $this->post('/users', []);
    expect($response->getStatusCode())->toBe(422); // Validation error
});
```

## Best Practices

### Controller Guidelines

1. **Keep controllers thin** - Move business logic to models or services
2. **Single responsibility** - Each method should have one clear purpose
3. **Consistent naming** - Use RESTful conventions when appropriate
4. **Proper error handling** - Always handle edge cases
5. **Input validation** - Validate all user input
6. **Authentication checks** - Protect sensitive actions

### Performance Tips

1. **Eager load relationships** - Avoid N+1 query problems
2. **Use pagination** - Don't load all records at once
3. **Cache expensive operations** - Cache database queries when appropriate
4. **Optimize database queries** - Use indexes and efficient queries

### Security Considerations

1. **Validate input** - Never trust user input
2. **Authorize actions** - Check user permissions
3. **Sanitize output** - Prevent XSS attacks
4. **Use HTTPS** - For sensitive operations
5. **Rate limiting** - Prevent abuse of API endpoints

---

**🎮 Controllers are the coordinators of your application. Well-designed controllers make your application maintainable and secure.**