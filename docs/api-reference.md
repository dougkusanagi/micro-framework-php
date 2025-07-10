# Referência da API - GuepardoSys Micro PHP Framework

## 📋 Visão Geral

Esta documentação apresenta todas as classes, métodos e funcionalidades disponíveis no GuepardoSys Micro PHP Framework.

## 🏗️ Core Classes

### App Class
Classe principal da aplicação que gerencia o ciclo de vida da request.

```php
use Src\Core\App;

$app = new App();
$app->run(); // Executa a aplicação
```

### Router Class
Sistema de roteamento com suporte a parâmetros e middleware.

#### Métodos Principais

```php
use Src\Core\Router;

$router = new Router();

// Definir rotas
$router->get('/path', 'Controller@method');
$router->post('/path', 'Controller@method');
$router->put('/path', 'Controller@method');
$router->delete('/path', 'Controller@method');

// Rotas com parâmetros
$router->get('/users/{id}', 'UserController@show');
$router->get('/posts/{slug}', 'PostController@show');

// Rotas com middleware
$router->get('/dashboard', 'HomeController@dashboard')->middleware('auth');

// Grupos de rotas
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', 'AdminController@dashboard');
    $router->get('/users', 'AdminController@users');
});
```

#### Métodos Disponíveis

| Método | Descrição | Exemplo |
|--------|-----------|---------|
| `get($path, $handler)` | Rota GET | `$router->get('/', 'HomeController@index')` |
| `post($path, $handler)` | Rota POST | `$router->post('/login', 'AuthController@login')` |
| `put($path, $handler)` | Rota PUT | `$router->put('/users/{id}', 'UserController@update')` |
| `delete($path, $handler)` | Rota DELETE | `$router->delete('/posts/{id}', 'PostController@destroy')` |
| `middleware($name)` | Aplicar middleware | `$router->get('/admin', 'AdminController@index')->middleware('auth')` |
| `group($options, $callback)` | Grupo de rotas | Ver exemplo acima |

## 🎮 Controllers

### BaseController Class
Classe base para todos os controllers com métodos auxiliares.

```php
use App\Controllers\BaseController;

class MyController extends BaseController
{
    public function index()
    {
        // Métodos disponíveis
    }
}
```

#### Métodos de View

```php
// Renderizar view
return $this->view('template', $data);

// Renderizar view com layout
return $this->view('template', $data, 'layouts.admin');

// JSON response
return $this->json(['status' => 'success']);

// Redirect
return $this->redirect('/path');
return $this->redirectBack();
```

#### Métodos de Validação

```php
// Validação de dados
$data = $this->validate([
    'name' => 'required|max:255',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed'
]);

// Regras disponíveis
$rules = [
    'field' => 'required',           // Campo obrigatório
    'field' => 'email',              // Email válido
    'field' => 'min:5',              // Mínimo 5 caracteres
    'field' => 'max:255',            // Máximo 255 caracteres
    'field' => 'numeric',            // Apenas números
    'field' => 'integer',            // Apenas inteiros
    'field' => 'in:value1,value2',   // Valores específicos
    'field' => 'unique:table',       // Único na tabela
    'field' => 'confirmed'           // Confirmação (field_confirmation)
];
```

#### Métodos de Session/Flash

```php
// Flash messages
$this->flash('success', 'Operação realizada com sucesso!');
$this->flash('error', 'Erro ao processar solicitação.');
$this->flash('warning', 'Atenção!');
$this->flash('info', 'Informação importante.');

// Session
$this->session('key', 'value');  // Set
$value = $this->session('key');  // Get
```

#### Métodos de Response

```php
// Responses HTTP
return $this->notFound();           // 404
return $this->forbidden();          // 403
return $this->serverError();        // 500
return $this->response($data, 200); // Custom status
```

#### Middleware

```php
class AdminController extends BaseController
{
    public function __construct()
    {
        // Aplicar middleware a todo o controller
        $this->middleware('auth');
        $this->middleware('admin');
    }
    
    public function index()
    {
        // Apenas usuários autenticados e admins podem acessar
    }
}
```

## 🗄️ Models

### BaseModel Class
Classe base para todos os models com operações CRUD e query builder.

```php
use Src\Core\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}
```

#### Propriedades Configuráveis

```php
class MyModel extends BaseModel
{
    protected $table = 'my_table';           // Nome da tabela
    protected $primaryKey = 'id';            // Chave primária
    protected $fillable = ['field1', 'field2']; // Campos preenchíveis
    protected $hidden = ['password'];        // Campos ocultos em JSON
    protected $timestamps = true;           // Usar created_at/updated_at
}
```

#### Métodos CRUD

```php
// Create
$user = User::create([
    'name' => 'João',
    'email' => 'joao@email.com'
]);

// Read
$user = User::find(1);                    // Por ID
$users = User::all();                     // Todos
$user = User::first();                    // Primeiro
$users = User::limit(10)->get();          // Com limite

// Update
$user = User::find(1);
$user->update(['name' => 'João Silva']);

// Delete
$user = User::find(1);
$user->delete();

// Or
User::destroy(1);     // Por ID
User::destroy([1,2,3]); // Múltiplos IDs
```

#### Query Builder

```php
// Condições WHERE
User::where('email', 'joao@email.com')->first();
User::where('age', '>', 18)->get();
User::where('name', 'LIKE', '%João%')->get();

// Múltiplas condições
User::where('active', 1)
    ->where('age', '>', 18)
    ->where('city', 'São Paulo')
    ->get();

// OR conditions
User::where('name', 'João')
    ->orWhere('name', 'Maria')
    ->get();

// Ordenação
User::orderBy('name', 'ASC')->get();
User::orderBy('created_at', 'DESC')->get();

// Limite e offset
User::limit(10)->get();
User::limit(10)->offset(20)->get();

// Contagem
$count = User::count();
$count = User::where('active', 1)->count();

// Agregações
$avg = User::avg('age');
$max = User::max('age');
$min = User::min('age');
$sum = User::sum('points');

// Existência
$exists = User::where('email', 'test@email.com')->exists();

// Raw queries
User::raw('SELECT * FROM users WHERE age > ?', [18]);
```

#### Relacionamentos Simples

```php
class Post extends BaseModel
{
    // Relacionamento com User
    public function user()
    {
        return User::find($this->user_id);
    }
    
    // Relacionamento reverso em User
    public function posts()
    {
        return Post::where('user_id', $this->id)->get();
    }
}

// Uso
$post = Post::find(1);
$author = $post->user();

$user = User::find(1);
$posts = $user->posts();
```

#### Mutators e Accessors

```php
class User extends BaseModel
{
    // Mutator - executa ao salvar
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    // Accessor - executa ao acessar
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}

// Uso
$user = new User();
$user->password = '123456'; // Automaticamente hasheado
echo $user->full_name;      // Combina first_name + last_name
```

#### Scopes

```php
class User extends BaseModel
{
    // Scope para usuários ativos
    public static function active()
    {
        return self::where('active', 1);
    }
    
    // Scope para usuários por cidade
    public static function fromCity($city)
    {
        return self::where('city', $city);
    }
}

// Uso
$activeUsers = User::active()->get();
$spUsers = User::fromCity('São Paulo')->get();
$activeSp = User::active()->fromCity('São Paulo')->get();
```

## 🎨 Views e Templates

### View Engine
Sistema de templates inspirado no Blade com compilação e cache.

#### Sintaxe Básica

```php
<!-- Variáveis -->
{{ $variavel }}                    <!-- Escaped -->
{!! $html !!}                     <!-- Unescaped HTML -->

<!-- Condicionais -->
@if($condition)
    Conteúdo se verdadeiro
@elseif($other)
    Conteúdo alternativo
@else
    Conteúdo padrão
@endif

<!-- Loops -->
@foreach($items as $item)
    <p>{{ $item->name }}</p>
@endforeach

@for($i = 0; $i < 10; $i++)
    <p>Número: {{ $i }}</p>
@endfor

<!-- Verificação de existência -->
@if(isset($user))
    Olá, {{ $user->name }}!
@endif

@if(!empty($posts))
    <!-- Lista de posts -->
@endif
```

#### Layouts e Herança

```php
<!-- layouts/main.guepardo.php -->
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Site' }}</title>
</head>
<body>
    <nav>@yield('nav')</nav>
    <main>@yield('content')</main>
    <footer>@yield('footer')</footer>
</body>
</html>

<!-- pages/home.guepardo.php -->
@extends('layouts.main')

@section('nav')
    <a href="/">Home</a>
@endsection

@section('content')
    <h1>Bem-vindo!</h1>
@endsection

@section('footer')
    <p>&copy; 2025</p>
@endsection
```

#### Includes

```php
<!-- Incluir arquivo -->
@include('partials.header')

<!-- Incluir com dados -->
@include('partials.user-card', ['user' => $user])

<!-- Incluir condicionalmente -->
@if($showSidebar)
    @include('partials.sidebar')
@endif
```

#### Métodos do Controller

```php
class HomeController extends BaseController
{
    public function index()
    {
        // View simples
        return $this->view('home');
        
        // View com dados
        return $this->view('home', [
            'title' => 'Página Inicial',
            'users' => User::all()
        ]);
        
        // View com layout específico
        return $this->view('admin.dashboard', $data, 'layouts.admin');
    }
}
```

## 🛠️ CLI Tool (guepardo)

### Comandos de Desenvolvimento

```bash
# Servidor de desenvolvimento
./guepardo serve                    # localhost:8000
./guepardo serve 127.0.0.1 3000    # custom host/port

# Listagem de rotas
./guepardo route:list               # Todas as rotas
./guepardo route:list --method=GET  # Apenas GET routes
```

### Geração de Código

```bash
# Controllers
./guepardo make:controller UserController
./guepardo make:controller Admin/UserController    # Com namespace

# Models
./guepardo make:model User
./guepardo make:model Category

# Migrations
./guepardo make:migration create_users_table
./guepardo make:migration add_column_to_users_table
```

### Comandos de Banco de Dados

```bash
# Migrações
./guepardo migrate                  # Executar pendentes
./guepardo migrate:rollback         # Desfazer última
./guepardo migrate:rollback --step=3 # Desfazer 3 últimas
./guepardo migrate:refresh          # Resetar e executar todas
./guepardo migrate:status           # Status das migrações

# Seeds
./guepardo db:seed                  # Executar todos
./guepardo db:seed --class=UserSeeder # Específico

# Database
./guepardo db:create                # Criar banco
./guepardo db:drop                  # Remover banco
```

### Comandos de Qualidade

```bash
# Testes
./guepardo test                     # Todos os testes
./guepardo test --filter=UserTest   # Teste específico

# Análise estática
./guepardo stan                     # PHPStan
./guepardo stan --level=8           # Nível específico

# Code style
./guepardo cs                       # Verificar
./guepardo cs:fix                   # Corrigir automaticamente

# Qualidade geral
./guepardo quality                  # Stan + CS + Tests
```

### Comandos de Produção

```bash
# Otimização
./guepardo optimize                 # Otimizar para produção
./guepardo cache:clear              # Limpar caches
./guepardo config:cache             # Cache de configurações

# Build
./guepardo build                    # Build para produção
./guepardo build:clean              # Limpar build anterior
```

## 🔐 Autenticação e Segurança

### Sistema de Autenticação

```php
// AuthController methods
class AuthController extends BaseController
{
    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && password_verify($credentials['password'], $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            return $this->redirect('/dashboard');
        }
        
        return $this->redirectBack()->with('error', 'Credenciais inválidas');
    }
    
    public function logout()
    {
        session_destroy();
        return $this->redirect('/');
    }
}
```

### Middleware de Autenticação

```php
// app/Middleware/AuthMiddleware.php
class AuthMiddleware
{
    public function handle($request, $next)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        return $next($request);
    }
}

// Registro do middleware
$router->get('/dashboard', 'HomeController@dashboard')->middleware('auth');
```

### Proteção CSRF

```php
<!-- Em formulários -->
<form method="POST" action="/submit">
    {{ csrf_token() }}
    <!-- campos do form -->
</form>

// Verificação automática em POST/PUT/DELETE
// O framework verifica automaticamente em rotas protegidas
```

### Validação e Sanitização

```php
// Regras de validação
$data = $this->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
    'name' => 'required|max:255',
    'age' => 'integer|min:18|max:120'
]);

// Sanitização automática
// Todos os dados são automaticamente sanitizados contra XSS
```

## 📁 Sistema de Arquivos e Cache

### Cache de Views

```php
// Cache automático de templates compilados
// Localização: storage/cache/views/

// Limpar cache
./guepardo cache:clear

// Verificar cache
ls -la storage/cache/views/
```

### Cache de Configuração

```php
// Cache de configurações para produção
./guepardo config:cache

// Arquivo gerado: storage/cache/config.php
```

### Logs

```php
// Sistema de logs automático
// Localização: storage/logs/

// Níveis disponíveis: debug, info, warning, error
// Rotação automática (mantém últimos 5 arquivos)

// Logs de erro automáticos
// Logs de acesso em desenvolvimento
```

## 📊 Performance e Otimização

### Métricas Padrão

- **TTFB**: < 30ms em produção
- **Arquivos**: 171 arquivos total
- **Memória**: Baixíssimo consumo
- **Compatibilidade**: 100% hospedagem compartilhada

### Otimizações Automáticas

```bash
# Comando de otimização
./guepardo optimize

# Aplica:
# - Cache de views
# - Cache de configurações  
# - Autoload otimizado
# - Compressão de assets
# - Headers de cache
```

### Configurações de Performance

```php
// .env para produção
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=file
CACHE_TTL=3600

// Headers automáticos de cache
// Compressão gzip quando disponível
// Otimização de queries automática
```

## 🔧 Configuração e Ambiente

### Arquivo .env

```env
# Aplicação
APP_NAME="Minha App"
APP_ENV=local|production
APP_DEBUG=true|false
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql|pgsql
DB_HOST=127.0.0.1
DB_PORT=3306|5432
DB_DATABASE=database_name
DB_USERNAME=username
DB_PASSWORD=password

# Cache
CACHE_DRIVER=file
CACHE_TTL=3600

# Logs
LOG_LEVEL=debug|info|warning|error
LOG_MAX_FILES=5

# Segurança
SESSION_LIFETIME=7200
CSRF_TOKEN_LENGTH=32
```

### Configuração de Database

```php
// config/database.php
return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8',
        ]
    ]
];
```

## 🧪 Testes

### Configuração PestPHP

```php
// tests/Pest.php
uses(\Tests\TestCase::class)->in('Feature', 'Unit');

// tests/TestCase.php
class TestCase extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup para testes
    }
}
```

### Exemplos de Testes

```php
// tests/Feature/HomeTest.php
test('homepage loads successfully', function () {
    $response = $this->get('/');
    expect($response->getStatusCode())->toBe(200);
});

// tests/Unit/UserTest.php
test('user can be created', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});
```

### Executar Testes

```bash
./guepardo test                     # Todos
./guepardo test --filter=UserTest   # Específicos
./guepardo test --coverage          # Com cobertura
```

## 📈 Monitoramento e Debug

### Error Handling

```php
// Tratamento automático de erros
// Logs automáticos em storage/logs/error.log
// Páginas de erro personalizadas em app/Views/errors/

// 404.guepardo.php
// 500.guepardo.php
// erro.guepardo.php
```

### Debug em Desenvolvimento

```php
// APP_DEBUG=true mostra:
// - Stack traces detalhados
// - Queries executadas
// - Tempo de carregamento
// - Uso de memória

// dd() helper para debug
dd($variable); // Dump and die

// dump() helper
dump($variable); // Dump without stopping
```

### Health Checks

```php
// scripts/health-check.php
// Verificações automáticas:
// - Conectividade de banco
// - Permissões de diretórios
// - Extensões PHP necessárias
// - Status de cache

./guepardo health:check
```

---

## 📚 Exemplos Práticos

### CRUD Completo

```php
// Model
class Product extends BaseModel
{
    protected $fillable = ['name', 'price', 'description'];
}

// Controller
class ProductController extends BaseController
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        return $this->view('products.index', compact('products'));
    }
    
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) return $this->notFound();
        return $this->view('products.show', compact('product'));
    }
    
    public function store()
    {
        $data = $this->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required'
        ]);
        
        Product::create($data);
        return $this->redirect('/products')->with('success', 'Produto criado!');
    }
}

// Routes
$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products', 'ProductController@store');
```

### API REST

```php
class ApiController extends BaseController
{
    public function users()
    {
        $users = User::all();
        return $this->json([
            'status' => 'success',
            'data' => $users
        ]);
    }
    
    public function createUser()
    {
        $data = $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users'
        ]);
        
        $user = User::create($data);
        
        return $this->json([
            'status' => 'success',
            'data' => $user
        ], 201);
    }
}
```

---

**📖 Esta é a referência completa da API do GuepardoSys Micro PHP Framework. Para exemplos práticos, consulte o [Tutorial](tutorial.md) e os [Guias de Melhores Práticas](best-practices.md).**
