# Sistema de Cache - GuepardoSys Micro PHP

## 📖 Visão Geral

O sistema de cache do GuepardoSys foi inspirado no Laravel e oferece uma interface simples e poderosa para armazenar dados temporariamente, melhorando significativamente a performance da aplicação.

## 🚀 Características

- **Interface Laravel-like**: Sintaxe familiar para desenvolvedores Laravel
- **Cache sob demanda**: Só cria cache quando necessário
- **Sistema de Tags**: Organize e invalide caches relacionados
- **Helpers globais**: Funções simples para uso rápido
- **TTL flexível**: Controle preciso do tempo de vida
- **Compressão automática**: Para dados grandes
- **Thread-safe**: Usando file locking

## 📚 Formas de Uso

### 1. Helper Functions (Mais Simples)

```php
// Buscar do cache
$value = cache('user.preferences.1');

// Armazenar no cache
cache('user.preferences.1', $preferences, 3600); // 1 hora

// Cache remember (mais comum)
$users = cache_remember('users.all', function() {
    return User::all()->toArray();
}, 600); // 10 minutos

// Cache forever
cache_forever('system.config', $config);

// Esquecer cache
cache_forget('user.preferences.1');

// Limpar todo cache
cache_flush();
```

### 2. Cache Facade (Mais Recursos)

```php
use GuepardoSys\Core\Cache\CacheFacade;

// Operações básicas
CacheFacade::put('key', $value, 3600);
$value = CacheFacade::get('key', $default);
$exists = CacheFacade::has('key');
CacheFacade::forget('key');

// Remember pattern
$data = CacheFacade::remember('expensive.query', function() {
    // Operação custosa aqui
    return Database::query('SELECT * FROM heavy_table');
}, 1800);

// Cache forever
CacheFacade::forever('rarely.changes', $data);

// Pull (get e remove)
$value = CacheFacade::pull('one.time.token');

// Contadores
$views = CacheFacade::increment('page.views');
$remaining = CacheFacade::decrement('api.quota.123');

// Estatísticas
$stats = CacheFacade::stats();
```

### 3. Cache com Tags (Invalidação em Grupo)

```php
// Cache com tags
cache_tags(['users', 'profiles'])->put('user.1.profile', $profile, 3600);
cache_tags(['users', 'preferences'])->put('user.1.settings', $settings, 3600);

// Remember com tags
$userData = cache_tags(['users', 'dashboard'])->remember('user.1.dashboard', function() {
    return [
        'profile' => User::find(1),
        'stats' => UserStats::for(1),
        'recent_activity' => Activity::recent(1)
    ];
}, 1800);

// Invalidar todo cache de usuários
cache_tags(['users'])->flush();

// Cache com múltiplas tags
cache_tags(['users', 'admin', 'reports'])->put('admin.report.1', $report, 7200);
```

## 🎯 Casos de Uso Práticos

### Controllers

```php
class ProductController extends BaseController
{
    public function index()
    {
        $products = cache_remember('products.featured', function() {
            return Product::where('featured', true)
                         ->with('category', 'images')
                         ->orderBy('priority')
                         ->get()
                         ->toArray();
        }, 3600); // Cache por 1 hora

        return $this->view('products.index', compact('products'));
    }

    public function show($id)
    {
        $product = cache_tags(['products'])->remember("product.{$id}", function() use ($id) {
            return Product::with('category', 'images', 'reviews')
                         ->findOrFail($id)
                         ->toArray();
        }, 1800); // Cache por 30 minutos

        return $this->view('products.show', compact('product'));
    }

    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        // Invalidar cache relacionado
        cache_tags(['products'])->flush();
        cache_forget('products.featured');

        return redirect("/products/{$id}");
    }
}
```

### Models

```php
class User extends BaseModel
{
    public function getProfile()
    {
        return cache_remember("user.{$this->id}.profile", function() {
            return [
                'basic_info' => $this->toArray(),
                'preferences' => $this->preferences(),
                'stats' => $this->calculateStats(),
                'social_links' => $this->socialLinks()
            ];
        }, 1800);
    }

    public function clearCache()
    {
        cache_tags(['users'])->flush();
        cache_forget("user.{$this->id}.profile");
    }

    public static function getPopular()
    {
        return cache_remember('users.popular', function() {
            return static::where('reputation', '>', 1000)
                         ->orderBy('reputation', 'desc')
                         ->limit(10)
                         ->get()
                         ->toArray();
        }, 7200); // Cache por 2 horas
    }
}
```

### Services/Utilities

```php
class ApiService
{
    public function getExternalData($endpoint)
    {
        $cacheKey = "api.{$endpoint}." . md5($params);
        
        return cache_remember($cacheKey, function() use ($endpoint) {
            // Chamada custosa para API externa
            $response = file_get_contents("https://api.example.com/{$endpoint}");
            return json_decode($response, true);
        }, 600); // Cache por 10 minutos
    }

    public function getUserQuota($userId)
    {
        $quotaKey = "user.{$userId}.api.quota";
        
        // Se não existe, criar quota inicial
        if (!CacheFacade::has($quotaKey)) {
            CacheFacade::put($quotaKey, 1000, 86400); // 1000 requests/dia
        }

        return CacheFacade::get($quotaKey);
    }

    public function consumeQuota($userId, $amount = 1)
    {
        $quotaKey = "user.{$userId}.api.quota";
        return CacheFacade::decrement($quotaKey, $amount);
    }
}
```

## ⚡ Performance e Boas Práticas

### Chaves Descritivas

```php
// ❌ Ruim
cache('u1', $user);

// ✅ Bom
cache('user.profile.1', $user);
cache('dashboard.widgets.user.1', $widgets);
cache('reports.monthly.2024.07', $report);
```

### TTL Apropriado

```php
// Dados que mudam frequentemente - TTL baixo
cache_remember('live.stock.prices', $callback, 60); // 1 minuto

// Dados semi-estáticos - TTL médio
cache_remember('user.preferences.1', $callback, 3600); // 1 hora

// Dados raramente alterados - TTL alto
cache_remember('system.configuration', $callback, 86400); // 1 dia

// Dados quase estáticos - forever
cache_forever('country.list', $countries);
```

### Tags Estratégicas

```php
// Por entidade
cache_tags(['users'])->put('user.1.profile', $profile);

// Por funcionalidade
cache_tags(['dashboard'])->put('widgets.user.1', $widgets);

// Por responsabilidade
cache_tags(['reports', 'admin'])->put('admin.monthly.report', $report);

// Múltiplas tags para flexibilidade
cache_tags(['users', 'profiles', 'public'])->put('user.1.public', $public);
```

### Invalidação Inteligente

```php
class UserController extends BaseController
{
    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());

        // Invalidação específica
        cache_forget("user.{$id}.profile");
        cache_tags(['users'])->flush(); // Todo cache de usuários
        
        // Invalidar cache relacionado
        if ($request->has('email')) {
            cache_forget('users.email.index');
        }
    }
}
```

## 🛡️ Limpeza e Manutenção

```php
// Limpar cache expirado (agendar via cron)
CacheFacade::cleanExpired();

// Estatísticas para monitoramento
$stats = CacheFacade::stats();
/*
[
    'total_files' => 150,
    'total_size' => 2048576,
    'expired_files' => 12,
    'cache_path' => '/path/to/cache'
]
*/

// Flush completo (cuidado em produção!)
CacheFacade::flush();
```

## 🔧 Configuração

### Variáveis de Ambiente

```env
# .env
CACHE_TTL=3600              # TTL padrão em segundos
CACHE_COMPRESSION=true      # Habilitar compressão
```

### Configuração no Bootstrap

```php
// bootstrap/app.php
define('STORAGE_PATH', BASE_PATH . '/storage');

// O cache será criado automaticamente em: /storage/cache/data/
```

## 📈 Monitoramento

```php
// Controller para dashboard de cache
class CacheController extends BaseController
{
    public function dashboard()
    {
        $stats = CacheFacade::stats();
        $cleanedExpired = CacheFacade::cleanExpired();
        
        return $this->view('admin.cache', [
            'stats' => $stats,
            'cleaned' => $cleanedExpired
        ]);
    }

    public function clear($type = 'expired')
    {
        switch ($type) {
            case 'all':
                CacheFacade::flush();
                break;
            case 'users':
                cache_tags(['users'])->flush();
                break;
            case 'expired':
            default:
                CacheFacade::cleanExpired();
                break;
        }

        return redirect('/admin/cache');
    }
}
```

## 🚦 Testando Cache

```php
// tests/Feature/CacheTest.php
it('caches expensive operations', function () {
    $start = microtime(true);
    
    $result1 = cache_remember('expensive.test', function() {
        usleep(100000); // 100ms
        return 'expensive result';
    }, 300);
    
    $time1 = microtime(true) - $start;
    
    $start = microtime(true);
    $result2 = cache('expensive.test');
    $time2 = microtime(true) - $start;
    
    expect($result1)->toBe($result2);
    expect($time2)->toBeLessThan($time1 / 10); // Cache deve ser 10x mais rápido
});
```

Este sistema de cache oferece flexibilidade e performance similar ao Laravel, mas otimizado para o micro-framework GuepardoSys!
