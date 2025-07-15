# Quick Start Guide

This guide will help you build your first application with GuepardoSys Micro PHP Framework in just a few minutes.

## Prerequisites

Make sure you have completed the [installation](installation.md) and have the development server running:

```bash
./guepardo serve
```

Your application should be accessible at `http://localhost:8000`.

## Creating Your First Route

Routes are defined in `routes/web.php`. Let's create a simple "Hello World" route:

```php
<?php
// routes/web.php

return [
    ['GET', '/', ['App\Controllers\HomeController', 'index']],
    ['GET', '/hello', function($request) {
        return 'Hello, World!';
    }],
    ['GET', '/hello/{name}', ['App\Controllers\HomeController', 'hello']],
];
```

Visit `http://localhost:8000/hello` to see your first route in action!

## Creating Your First Controller

Let's create a controller to handle more complex logic:

```bash
# Generate a new controller
./guepardo make:controller WelcomeController
```

This creates `app/Controllers/WelcomeController.php`:

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;

class WelcomeController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('welcome', [
            'title' => 'Welcome to GuepardoSys',
            'message' => 'Your application is ready!'
        ]);
    }

    public function about(Request $request)
    {
        return $this->view('about', [
            'title' => 'About Us',
            'framework' => 'GuepardoSys Micro PHP'
        ]);
    }
}
```

## Creating Your First View

Create a view file at `app/Views/welcome.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GuepardoSys') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-8">
            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">
                GuepardoSys Framework
            </div>
            <h1 class="block mt-1 text-lg leading-tight font-medium text-black">
                <?= htmlspecialchars($title) ?>
            </h1>
            <p class="mt-2 text-gray-500">
                <?= htmlspecialchars($message) ?>
            </p>
            <div class="mt-4">
                <a href="/about" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                    Learn More
                </a>
            </div>
        </div>
    </div>
</body>
</html>
```

## Adding Routes for Your Controller

Update `routes/web.php` to use your new controller:

```php
<?php
// routes/web.php

return [
    ['GET', '/', ['App\Controllers\WelcomeController', 'index']],
    ['GET', '/about', ['App\Controllers\WelcomeController', 'about']],
    ['GET', '/hello/{name}', ['App\Controllers\HomeController', 'hello']],
];
```

## Working with Data

Let's create a simple data-driven page. First, create a model:

```bash
./guepardo make:model Post
```

This creates `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use GuepardoSys\Core\BaseModel;

class Post extends BaseModel
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'author'];
}
```

Create a controller for posts:

```bash
./guepardo make:controller PostController
```

Update `app/Controllers/PostController.php`:

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;
use App\Models\Post;

class PostController extends BaseController
{
    public function index(Request $request)
    {
        // For now, let's use static data
        $posts = [
            ['id' => 1, 'title' => 'First Post', 'content' => 'This is my first post!'],
            ['id' => 2, 'title' => 'Second Post', 'content' => 'Another great post.'],
            ['id' => 3, 'title' => 'Third Post', 'content' => 'More content here.'],
        ];

        return $this->view('posts/index', [
            'title' => 'All Posts',
            'posts' => $posts
        ]);
    }

    public function show(Request $request)
    {
        $id = $request->getRouteParam('id');
        
        // Static data for demo
        $posts = [
            1 => ['id' => 1, 'title' => 'First Post', 'content' => 'This is my first post!'],
            2 => ['id' => 2, 'title' => 'Second Post', 'content' => 'Another great post.'],
            3 => ['id' => 3, 'title' => 'Third Post', 'content' => 'More content here.'],
        ];

        $post = $posts[$id] ?? null;

        if (!$post) {
            return $this->response('Post not found', 404);
        }

        return $this->view('posts/show', [
            'title' => $post['title'],
            'post' => $post
        ]);
    }
}
```

Create the posts views directory and files:

```bash
mkdir -p app/Views/posts
```

Create `app/Views/posts/index.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8"><?= htmlspecialchars($title) ?></h1>
        
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($posts as $post): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        <a href="/posts/<?= $post['id'] ?>" class="hover:text-indigo-600">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </h2>
                    <p class="text-gray-600">
                        <?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...
                    </p>
                    <a href="/posts/<?= $post['id'] ?>" class="inline-block mt-4 text-indigo-600 hover:text-indigo-800">
                        Read more →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-8">
            <a href="/" class="text-indigo-600 hover:text-indigo-800">← Back to Home</a>
        </div>
    </div>
</body>
</html>
```

Create `app/Views/posts/show.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <article class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                <?= htmlspecialchars($post['title']) ?>
            </h1>
            <div class="prose max-w-none">
                <p class="text-gray-700 leading-relaxed">
                    <?= htmlspecialchars($post['content']) ?>
                </p>
            </div>
        </article>
        
        <div class="mt-8 flex space-x-4">
            <a href="/posts" class="text-indigo-600 hover:text-indigo-800">← All Posts</a>
            <a href="/" class="text-indigo-600 hover:text-indigo-800">Home</a>
        </div>
    </div>
</body>
</html>
```

Add the new routes to `routes/web.php`:

```php
<?php
// routes/web.php

return [
    ['GET', '/', ['App\Controllers\WelcomeController', 'index']],
    ['GET', '/about', ['App\Controllers\WelcomeController', 'about']],
    ['GET', '/posts', ['App\Controllers\PostController', 'index']],
    ['GET', '/posts/{id}', ['App\Controllers\PostController', 'show']],
    ['GET', '/hello/{name}', ['App\Controllers\HomeController', 'hello']],
];
```

## Adding Navigation

Update your welcome view to include navigation to the posts:

```php
<!-- In app/Views/welcome.php, add this to the content div -->
<div class="mt-4 space-x-2">
    <a href="/about" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
        Learn More
    </a>
    <a href="/posts" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
        View Posts
    </a>
</div>
```

## Testing Your Application

Run the built-in tests to make sure everything is working:

```bash
# Run all tests
./guepardo test

# Check code quality
./guepardo quality

# List all routes
./guepardo route:list
```

## Working with Forms

Let's add a contact form. Create a contact controller:

```bash
./guepardo make:controller ContactController
```

Update `app/Controllers/ContactController.php`:

```php
<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;

class ContactController extends BaseController
{
    public function show(Request $request)
    {
        return $this->view('contact', [
            'title' => 'Contact Us'
        ]);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        
        // Simple validation
        $errors = [];
        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        }
        if (empty($data['message'])) {
            $errors[] = 'Message is required';
        }

        if (!empty($errors)) {
            return $this->view('contact', [
                'title' => 'Contact Us',
                'errors' => $errors,
                'old' => $data
            ]);
        }

        // Process the form (save to database, send email, etc.)
        // For now, just show a success message
        return $this->view('contact-success', [
            'title' => 'Thank You!',
            'name' => $data['name']
        ]);
    }
}
```

Create `app/Views/contact.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6"><?= htmlspecialchars($title) ?></h1>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="/contact" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="message" name="message" rows="4" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-8">
            <a href="/" class="text-indigo-600 hover:text-indigo-800">← Back to Home</a>
        </div>
    </div>
</body>
</html>
```

Create `app/Views/contact-success.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-8 text-center">
            <div class="text-green-500 text-6xl mb-4">✓</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($title) ?></h1>
            <p class="text-gray-600 mb-6">
                Thank you, <?= htmlspecialchars($name) ?>! Your message has been sent successfully.
            </p>
            <a href="/" class="bg-indigo-500 text-white px-6 py-2 rounded hover:bg-indigo-600">
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
```

Add the contact routes:

```php
<?php
// routes/web.php

return [
    ['GET', '/', ['App\Controllers\WelcomeController', 'index']],
    ['GET', '/about', ['App\Controllers\WelcomeController', 'about']],
    ['GET', '/posts', ['App\Controllers\PostController', 'index']],
    ['GET', '/posts/{id}', ['App\Controllers\PostController', 'show']],
    ['GET', '/contact', ['App\Controllers\ContactController', 'show']],
    ['POST', '/contact', ['App\Controllers\ContactController', 'submit']],
    ['GET', '/hello/{name}', ['App\Controllers\HomeController', 'hello']],
];
```

## Next Steps

Congratulations! You've built a complete application with:

- ✅ Multiple routes and controllers
- ✅ Views with data binding
- ✅ Form handling and validation
- ✅ Navigation between pages
- ✅ Error handling

### What to Learn Next

1. **[Database Integration](../database/getting-started.md)** - Connect to a real database
2. **[Authentication](../security/authentication.md)** - Add user login/registration
3. **[Middleware](../basics/middleware.md)** - Add request filtering
4. **[Advanced Routing](../basics/routing.md)** - Learn about route groups and middleware
5. **[Testing](../testing/getting-started.md)** - Write tests for your application

### Useful CLI Commands

```bash
# Generate new components
./guepardo make:controller UserController
./guepardo make:model User
./guepardo make:migration create_users_table

# Database operations
./guepardo migrate
./guepardo db:seed

# Development tools
./guepardo serve
./guepardo route:list
./guepardo test
./guepardo quality
```

---

**🎉 You've successfully built your first GuepardoSys application! Keep exploring the documentation to learn more advanced features.**