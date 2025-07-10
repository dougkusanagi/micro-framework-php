# Melhores Práticas - GuepardoSys Micro PHP Framework

## 🎯 Objetivo

Este guia apresenta as melhores práticas para desenvolver aplicações robustas, seguras e performáticas com o GuepardoSys Micro PHP Framework.

## 🏗️ Arquitetura e Organização

### Estrutura de Diretórios

```
app/
├── Controllers/
│   ├── Auth/           # Controllers de autenticação
│   ├── Admin/          # Controllers administrativos
│   └── Api/            # Controllers de API
├── Models/
├── Views/
│   ├── layouts/        # Layouts reutilizáveis
│   ├── partials/       # Componentes parciais
│   ├── auth/           # Views de autenticação
│   └── admin/          # Views administrativas
└── Middleware/         # Middlewares customizados
```

### Convenções de Nomenclatura

```php
// Controllers (PascalCase + Controller)
class UserController extends BaseController {}
class AdminUserController extends BaseController {}

// Models (PascalCase singular)
class User extends BaseModel {}
class BlogPost extends BaseModel {}

// Views (snake_case ou kebab-case)
user_profile.guepardo.php
blog-post-list.guepardo.php

// Métodos (camelCase)
public function getUserProfile() {}
public function updateUserStatus() {}

// Variáveis (camelCase)
$userProfile = [];
$blogPostList = [];
```

### Organização por Feature

```
app/
├── Features/
│   ├── Auth/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   └── Views/
│   ├── Blog/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   └── Views/
│   └── Admin/
│       ├── Controllers/
│       ├── Models/
│       └── Views/
```

## 🎮 Controllers - Melhores Práticas

### Responsabilidade Única

```php
// ❌ Evitar - Controller fazendo muitas coisas
class UserController extends BaseController
{
    public function profile($id)
    {
        // Buscar usuário
        // Validar permissões
        // Buscar posts do usuário
        // Processar dados
        // Enviar email
        // Fazer log
        // Renderizar view
    }
}

// ✅ Melhor - Responsabilidades separadas
class UserController extends BaseController
{
    public function profile($id)
    {
        $user = $this->getUserOrFail($id);
        $this->authorizeUserAccess($user);
        
        $posts = $user->recentPosts(5);
        
        return $this->view('users.profile', [
            'user' => $user,
            'posts' => $posts
        ]);
    }
    
    private function getUserOrFail($id)
    {
        $user = User::find($id);
        if (!$user) {
            throw new \Exception('Usuário não encontrado', 404);
        }
        return $user;
    }
    
    private function authorizeUserAccess($user)
    {
        // Lógica de autorização
    }
}
```

### Validação Consistente

```php
class UserController extends BaseController
{
    // ✅ Método para validação de criação
    private function validateUserCreation()
    {
        return $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'birth_date' => 'required|date'
        ]);
    }
    
    // ✅ Método para validação de atualização
    private function validateUserUpdate($userId)
    {
        return $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,id,' . $userId,
            'birth_date' => 'required|date'
        ]);
    }
    
    public function store()
    {
        $data = $this->validateUserCreation();
        $user = User::create($data);
        
        $this->flash('success', 'Usuário criado com sucesso!');
        return $this->redirect('/users/' . $user->id);
    }
}
```

### Error Handling

```php
class PostController extends BaseController
{
    public function show($id)
    {
        try {
            $post = Post::findOrFail($id);
            
            // Verificar se está publicado
            if (!$post->isPublished() && !$this->canViewDrafts()) {
                return $this->forbidden();
            }
            
            return $this->view('posts.show', compact('post'));
            
        } catch (\Exception $e) {
            $this->logError('Error loading post', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->serverError();
        }
    }
    
    private function canViewDrafts()
    {
        return isset($_SESSION['user_id']) && 
               $_SESSION['user_role'] === 'admin';
    }
}
```

## 🗄️ Models - Melhores Práticas

### Configuração Adequada

```php
class User extends BaseModel
{
    protected $table = 'users';
    
    // ✅ Definir campos preenchíveis
    protected $fillable = [
        'name', 'email', 'password', 'birth_date', 'avatar'
    ];
    
    // ✅ Ocultar campos sensíveis
    protected $hidden = [
        'password', 'remember_token'
    ];
    
    // ✅ Campos que devem ser tratados como datas
    protected $dates = [
        'birth_date', 'last_login_at'
    ];
}
```

### Scopes para Queries Complexas

```php
class Post extends BaseModel
{
    // ✅ Scope para posts publicados
    public static function published()
    {
        return self::where('status', 'published')
                   ->where('published_at', '<=', date('Y-m-d H:i:s'));
    }
    
    // ✅ Scope para posts recentes
    public static function recent($days = 30)
    {
        return self::where('created_at', '>=', 
                          date('Y-m-d H:i:s', strtotime("-{$days} days")));
    }
    
    // ✅ Scope para busca
    public static function search($query)
    {
        return self::where('title', 'LIKE', "%{$query}%")
                   ->orWhere('content', 'LIKE', "%{$query}%");
    }
}

// Uso combinado
$posts = Post::published()
             ->recent(7)
             ->search('php')
             ->orderBy('created_at', 'DESC')
             ->limit(10)
             ->get();
```

### Relacionamentos Bem Definidos

```php
class User extends BaseModel
{
    // ✅ Relacionamento um-para-muitos
    public function posts()
    {
        return Post::where('user_id', $this->id)
                   ->orderBy('created_at', 'DESC')
                   ->get();
    }
    
    // ✅ Posts publicados apenas
    public function publishedPosts()
    {
        return Post::where('user_id', $this->id)
                   ->where('status', 'published')
                   ->orderBy('published_at', 'DESC')
                   ->get();
    }
    
    // ✅ Último post
    public function latestPost()
    {
        return Post::where('user_id', $this->id)
                   ->orderBy('created_at', 'DESC')
                   ->first();
    }
}

class Post extends BaseModel
{
    // ✅ Relacionamento muitos-para-um
    public function author()
    {
        return User::find($this->user_id);
    }
    
    // ✅ Verificar se tem autor
    public function hasAuthor()
    {
        return !empty($this->user_id) && $this->author() !== null;
    }
}
```

### Mutators e Accessors

```php
class User extends BaseModel
{
    // ✅ Mutator para hash de senha
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
        }
    }
    
    // ✅ Mutator para email em lowercase
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }
    
    // ✅ Accessor para nome completo
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    // ✅ Accessor para avatar com fallback
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return '/uploads/avatars/' . $this->avatar;
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }
}
```

### Validação no Model

```php
class User extends BaseModel
{
    // ✅ Validar antes de salvar
    public function validate($data)
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Nome é obrigatório';
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        }
        
        // Verificar email único
        if ($this->emailExists($data['email'])) {
            $errors['email'] = 'Email já cadastrado';
        }
        
        return $errors;
    }
    
    private function emailExists($email)
    {
        $existing = self::where('email', $email);
        
        // Se estamos atualizando, excluir o próprio registro
        if ($this->id) {
            $existing = $existing->where('id', '!=', $this->id);
        }
        
        return $existing->exists();
    }
}
```

## 🎨 Views - Melhores Práticas

### Layouts Bem Estruturados

```php
<!-- layouts/main.guepardo.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Meu Site')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', 'Descrição padrão')">
    <meta name="keywords" content="@yield('keywords', 'palavras, chave')">
    
    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', $title ?? 'Meu Site')">
    <meta property="og:description" content="@yield('og_description', 'Descrição padrão')">
    <meta property="og:image" content="@yield('og_image', '/assets/img/default-og.jpg')">
    
    <!-- CSS -->
    <link href="/assets/css/app.css" rel="stylesheet">
    @yield('css')
</head>
<body class="@yield('body_class', '')">
    <!-- Navigation -->
    @include('partials.navigation')
    
    <!-- Flash Messages -->
    @include('partials.flash-messages')
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('partials.footer')
    
    <!-- Scripts -->
    <script src="/assets/js/app.js"></script>
    @yield('scripts')
</body>
</html>
```

### Componentes Reutilizáveis

```php
<!-- partials/user-card.guepardo.php -->
<div class="user-card">
    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="avatar">
    <div class="user-info">
        <h3>{{ $user->name }}</h3>
        <p>{{ $user->email }}</p>
        @if($user->bio)
            <p class="bio">{{ $user->bio }}</p>
        @endif
    </div>
</div>

<!-- Uso -->
@include('partials.user-card', ['user' => $author])
```

### Formulários Consistentes

```php
<!-- partials/form-field.guepardo.php -->
<div class="form-field">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required ?? false)
            <span class="required">*</span>
        @endif
    </label>
    
    @if($type === 'textarea')
        <textarea name="{{ $name }}" id="{{ $name }}" 
                  class="form-input {{ $errors[$name] ?? '' ? 'error' : '' }}"
                  rows="{{ $rows ?? 3 }}">{{ old($name, $value ?? '') }}</textarea>
    @else
        <input type="{{ $type ?? 'text' }}" 
               name="{{ $name }}" 
               id="{{ $name }}"
               value="{{ old($name, $value ?? '') }}"
               class="form-input {{ $errors[$name] ?? '' ? 'error' : '' }}">
    @endif
    
    @if($errors[$name] ?? '')
        <span class="error-message">{{ $errors[$name] }}</span>
    @endif
</div>

<!-- Uso -->
@include('partials.form-field', [
    'name' => 'email',
    'label' => 'Email',
    'type' => 'email',
    'required' => true
])
```

### SEO e Performance

```php
<!-- pages/post.guepardo.php -->
@extends('layouts.main')

@section('title', $post->title . ' - Blog')
@section('description', $post->excerpt)
@section('keywords', implode(', ', $post->tags ?? []))

@section('og_title', $post->title)
@section('og_description', $post->excerpt)
@section('og_image', $post->featured_image ?? '/assets/img/default-post.jpg')

@section('css')
    <link rel="canonical" href="{{ url('/blog/' . $post->slug) }}">
    <style>
        /* CSS específico da página */
    </style>
@endsection

@section('content')
    <article class="post">
        <header>
            <h1>{{ $post->title }}</h1>
            <div class="post-meta">
                <time datetime="{{ $post->created_at }}">
                    {{ date('d/m/Y', strtotime($post->created_at)) }}
                </time>
                <span>Por {{ $post->author()->name }}</span>
            </div>
        </header>
        
        <div class="post-content">
            {!! nl2br(e($post->content)) !!}
        </div>
    </article>
@endsection

@section('scripts')
    <script>
        // JavaScript específico da página
    </script>
@endsection
```

## 🔐 Segurança

### Validação e Sanitização

```php
class PostController extends BaseController
{
    public function store()
    {
        // ✅ Sempre validar dados de entrada
        $data = $this->validate([
            'title' => 'required|max:255',
            'content' => 'required|max:10000',
            'status' => 'required|in:draft,published',
            'tags' => 'array|max:5'
        ]);
        
        // ✅ Sanitização adicional se necessário
        $data['title'] = strip_tags($data['title']);
        $data['slug'] = $this->generateSlug($data['title']);
        
        // ✅ Dados do usuário autenticado
        $data['user_id'] = $this->getAuthenticatedUserId();
        
        $post = Post::create($data);
        
        return $this->redirect('/posts/' . $post->id);
    }
    
    private function getAuthenticatedUserId()
    {
        if (!isset($_SESSION['user_id'])) {
            throw new \Exception('Usuário não autenticado', 401);
        }
        
        return $_SESSION['user_id'];
    }
}
```

### Autorização

```php
class PostController extends BaseController
{
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        
        // ✅ Verificar autorização
        $this->authorizePostAccess($post, 'edit');
        
        return $this->view('posts.edit', compact('post'));
    }
    
    private function authorizePostAccess($post, $action)
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        // Não autenticado
        if (!$userId) {
            throw new \Exception('Acesso negado', 403);
        }
        
        // Proprietário pode tudo
        if ($post->user_id == $userId) {
            return true;
        }
        
        // Admin pode tudo
        if ($_SESSION['user_role'] === 'admin') {
            return true;
        }
        
        // Editor pode editar posts publicados
        if ($action === 'edit' && 
            $_SESSION['user_role'] === 'editor' && 
            $post->status === 'published') {
            return true;
        }
        
        throw new \Exception('Acesso negado', 403);
    }
}
```

### Proteção CSRF

```php
<!-- ✅ Sempre incluir token CSRF em formulários -->
<form method="POST" action="/posts">
    {{ csrf_token() }}
    
    <!-- campos do formulário -->
    
    <button type="submit">Salvar</button>
</form>

<!-- ✅ Para requisições AJAX -->
<script>
    const csrfToken = '{{ csrf_token() }}';
    
    fetch('/api/posts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(data)
    });
</script>
```

## 🚀 Performance

### Cache Eficiente

```php
class BlogController extends BaseController
{
    public function index()
    {
        // ✅ Cache de queries pesadas
        $cacheKey = 'blog_posts_' . date('Y-m-d-H');
        
        $posts = $this->cache($cacheKey, function() {
            return Post::published()
                       ->with('author')
                       ->orderBy('published_at', 'DESC')
                       ->limit(10)
                       ->get();
        }, 3600); // 1 hora
        
        return $this->view('blog.index', compact('posts'));
    }
    
    private function cache($key, $callback, $ttl = 3600)
    {
        $cacheFile = storage_path('cache/' . md5($key) . '.cache');
        
        if (file_exists($cacheFile) && 
            (time() - filemtime($cacheFile)) < $ttl) {
            return unserialize(file_get_contents($cacheFile));
        }
        
        $data = $callback();
        file_put_contents($cacheFile, serialize($data));
        
        return $data;
    }
}
```

### Queries Otimizadas

```php
// ❌ Evitar N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author()->name; // Query para cada post!
}

// ✅ Usar eager loading quando possível
class Post extends BaseModel
{
    public static function withAuthor()
    {
        $posts = self::all();
        $userIds = array_unique(array_column($posts, 'user_id'));
        $users = User::whereIn('id', $userIds)->get();
        
        // Mapear usuários para posts
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->id] = $user;
        }
        
        foreach ($posts as $post) {
            $post->author = $userMap[$post->user_id] ?? null;
        }
        
        return $posts;
    }
}
```

### Paginação

```php
class PostController extends BaseController
{
    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // ✅ Buscar com limite
        $posts = Post::published()
                     ->orderBy('created_at', 'DESC')
                     ->limit($perPage)
                     ->offset($offset)
                     ->get();
        
        // ✅ Contar total para paginação
        $totalPosts = Post::published()->count();
        $totalPages = ceil($totalPosts / $perPage);
        
        return $this->view('posts.index', [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasNextPage' => $page < $totalPages,
            'hasPrevPage' => $page > 1
        ]);
    }
}
```

## 🧪 Testes

### Estrutura de Testes

```php
// tests/Feature/PostTest.php
class PostTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Preparar dados de teste
        $this->createTestUser();
        $this->createTestPosts();
    }
    
    public function test_user_can_create_post()
    {
        $this->actingAs($this->testUser);
        
        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'status' => 'published'
        ];
        
        $response = $this->post('/posts', $postData);
        
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $this->testUser->id
        ]);
        
        $response->assertRedirect('/posts');
        $response->assertSessionHas('success');
    }
    
    public function test_guest_cannot_create_post()
    {
        $postData = [
            'title' => 'Test Post',
            'content' => 'Content'
        ];
        
        $response = $this->post('/posts', $postData);
        $response->assertRedirect('/login');
    }
}
```

### Testes Unitários

```php
// tests/Unit/UserTest.php
class UserTest extends TestCase
{
    public function test_user_password_is_hashed()
    {
        $user = new User();
        $user->password = 'plain-password';
        
        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(password_verify('plain-password', $user->password));
    }
    
    public function test_user_full_name_accessor()
    {
        $user = new User();
        $user->first_name = 'João';
        $user->last_name = 'Silva';
        
        $this->assertEquals('João Silva', $user->full_name);
    }
    
    public function test_user_can_have_posts()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Content',
            'user_id' => $user->id
        ]);
        
        $this->assertCount(1, $user->posts());
        $this->assertEquals($post->id, $user->posts()[0]->id);
    }
}
```

## 📊 Monitoramento e Logs

### Logging Estruturado

```php
class PostController extends BaseController
{
    public function store()
    {
        try {
            $data = $this->validate([...]);
            
            // ✅ Log de início da operação
            $this->log('info', 'Creating new post', [
                'user_id' => $_SESSION['user_id'],
                'title' => $data['title']
            ]);
            
            $post = Post::create($data);
            
            // ✅ Log de sucesso
            $this->log('info', 'Post created successfully', [
                'post_id' => $post->id,
                'user_id' => $_SESSION['user_id']
            ]);
            
            return $this->redirect('/posts/' . $post->id);
            
        } catch (\Exception $e) {
            // ✅ Log de erro
            $this->log('error', 'Failed to create post', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->redirectBack()->with('error', 'Erro ao criar post');
        }
    }
    
    private function log($level, $message, $context = [])
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        $logFile = storage_path('logs/' . date('Y-m-d') . '.log');
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);
    }
}
```

### Health Checks

```php
// scripts/health-check.php
class HealthCheck
{
    public function run()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'memory' => $this->checkMemory()
        ];
        
        $allPassing = !in_array(false, array_values($checks));
        
        return [
            'status' => $allPassing ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkDatabase()
    {
        try {
            $pdo = new PDO(/* config */);
            $pdo->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function checkStorage()
    {
        $storageDir = __DIR__ . '/../storage';
        return is_writable($storageDir . '/cache') && 
               is_writable($storageDir . '/logs');
    }
    
    private function checkCache()
    {
        $cacheFile = storage_path('cache/health-check-test');
        $testData = 'health-check-' . time();
        
        file_put_contents($cacheFile, $testData);
        $read = file_get_contents($cacheFile);
        unlink($cacheFile);
        
        return $read === $testData;
    }
    
    private function checkMemory()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
        
        return ($memoryUsage / $memoryLimit) < 0.8; // < 80%
    }
}
```

## 🔧 Deploy e Produção

### Configuração de Produção

```env
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://meusite.com

# Cache mais agressivo
CACHE_TTL=7200

# Logs menos verbosos
LOG_LEVEL=warning
LOG_MAX_FILES=10
```

### Script de Deploy

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Iniciando deploy..."

# Backup da versão atual
echo "📦 Criando backup..."
tar -czf backup-$(date +%Y%m%d-%H%M).tar.gz public_html/

# Fazer upload dos arquivos
echo "📤 Fazendo upload..."
rsync -avz --exclude='.git' --exclude='node_modules' --exclude='tests' ./ usuario@servidor:~/public_html/

# Comandos no servidor
echo "⚙️ Configurando no servidor..."
ssh usuario@servidor "
cd ~/public_html

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Otimizar para produção
./guepardo optimize

# Compilar assets
bun run build

# Aplicar migrações
./guepardo migrate --force

# Configurar permissões
chmod 755 guepardo
chmod -R 775 storage/

echo '✅ Deploy concluído!'
"
```

### Monitoramento em Produção

```php
// scripts/monitor.php
class ProductionMonitor
{
    public function checkAndAlert()
    {
        $checks = [
            'disk_space' => $this->checkDiskSpace(),
            'error_rate' => $this->checkErrorRate(),
            'response_time' => $this->checkResponseTime(),
            'database' => $this->checkDatabase()
        ];
        
        foreach ($checks as $check => $result) {
            if (!$result['healthy']) {
                $this->sendAlert($check, $result);
            }
        }
    }
    
    private function checkErrorRate()
    {
        $logFile = storage_path('logs/' . date('Y-m-d') . '.log');
        
        if (!file_exists($logFile)) {
            return ['healthy' => true];
        }
        
        $lines = file($logFile);
        $errors = array_filter($lines, function($line) {
            return strpos($line, '"level":"error"') !== false;
        });
        
        $errorRate = count($errors) / count($lines);
        
        return [
            'healthy' => $errorRate < 0.05, // < 5%
            'error_rate' => $errorRate,
            'total_errors' => count($errors)
        ];
    }
    
    private function sendAlert($check, $result)
    {
        // Enviar email ou webhook
        mail(
            'admin@meusite.com',
            "⚠️ Alerta: {$check}",
            "Problema detectado: " . json_encode($result)
        );
    }
}
```

## 📈 Otimização Contínua

### Análise de Performance

```php
// Middleware de profiling
class PerformanceMiddleware
{
    public function handle($request, $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $this->logPerformance([
            'url' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'execution_time' => round(($endTime - $startTime) * 1000, 2), // ms
            'memory_usage' => round(($endMemory - $startMemory) / 1024, 2), // KB
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) // MB
        ]);
        
        return $response;
    }
}
```

### Code Quality

```bash
# Scripts de qualidade automática
#!/bin/bash
# quality.sh

echo "🔍 Verificando qualidade do código..."

# PHPStan
./guepardo stan --level=8
if [ $? -ne 0 ]; then
    echo "❌ PHPStan falhou"
    exit 1
fi

# PHPCS
./guepardo cs
if [ $? -ne 0 ]; then
    echo "❌ Code style falhou"
    exit 1
fi

# Testes
./guepardo test
if [ $? -ne 0 ]; then
    echo "❌ Testes falharam"
    exit 1
fi

echo "✅ Qualidade verificada!"
```

---

## 📚 Checklist de Melhores Práticas

### ✅ Desenvolvimento
- [ ] Seguir convenções de nomenclatura
- [ ] Implementar validação em todos os formulários
- [ ] Usar scopes nos models para queries complexas
- [ ] Implementar autorização adequada
- [ ] Adicionar logs estruturados
- [ ] Escrever testes para funcionalidades críticas

### ✅ Segurança
- [ ] Sempre validar e sanitizar inputs
- [ ] Usar tokens CSRF em formulários
- [ ] Implementar autorização em controllers
- [ ] Usar prepared statements (automático no framework)
- [ ] Configurar headers de segurança
- [ ] Hash de senhas seguro (automático)

### ✅ Performance
- [ ] Implementar cache para queries pesadas
- [ ] Otimizar consultas ao banco
- [ ] Usar paginação em listagens
- [ ] Comprimir assets para produção
- [ ] Configurar headers de cache

### ✅ Manutenção
- [ ] Documentar código complexo
- [ ] Implementar health checks
- [ ] Configurar monitoramento
- [ ] Criar scripts de deploy
- [ ] Implementar backup automático

---

**🏆 Seguindo essas práticas, você terá uma aplicação robusta, segura e performática com o GuepardoSys Micro PHP Framework!**
