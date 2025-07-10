# Tutorial - GuepardoSys Micro PHP Framework

## 🎯 Objetivo

Este tutorial vai te guiar na criação de uma aplicação completa usando o GuepardoSys Micro PHP, desde a instalação até uma aplicação funcional com autenticação e CRUD.

## 📚 O que você vai aprender

- Configuração inicial
- Criação de rotas e controllers
- Trabalho com views e templates
- Operações com banco de dados
- Sistema de autenticação
- Deploy em produção

## 🚀 Projeto: Sistema de Blog

Vamos criar um sistema de blog simples com:
- Listagem de posts
- Criação/edição de posts
- Sistema de usuários
- Autenticação
- Comentários

## Etapa 1: Configuração Inicial

### 1.1 Instalação
```bash
# Clone ou baixe o framework
git clone <repo-url> meu-blog
cd meu-blog

# Instale dependências
composer install
bun install

# Configure o ambiente
cp .env.example .env
chmod +x guepardo
```

### 1.2 Configuração do Banco
```env
# .env
APP_NAME="Meu Blog"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meu_blog
DB_USERNAME=root
DB_PASSWORD=
```

### 1.3 Preparação do Banco
```bash
# Criar banco
mysql -u root -p -e "CREATE DATABASE meu_blog"

# Executar migrações básicas
./guepardo migrate

# Popular usuários de exemplo
./guepardo db:seed
```

## Etapa 2: Criando o Model Post

### 2.1 Gerar Migration
```bash
./guepardo make:migration create_posts_table
```

### 2.2 Editar Migration
```sql
-- database/migrations/003_create_posts_table.sql
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    user_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_posts_status ON posts(status);
CREATE INDEX idx_posts_user_id ON posts(user_id);
```

### 2.3 Executar Migration
```bash
./guepardo migrate
```

### 2.4 Criar Model
```bash
./guepardo make:model Post
```

### 2.5 Implementar Model
```php
<?php
// app/Models/Post.php

namespace App\Models;

use Src\Core\BaseModel;

class Post extends BaseModel
{
    protected $table = 'posts';
    
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'user_id', 'status'
    ];

    /**
     * Relacionamento com User
     */
    public function user()
    {
        $userId = $this->user_id;
        return User::find($userId);
    }

    /**
     * Buscar posts publicados
     */
    public static function published()
    {
        return self::where('status', 'published');
    }

    /**
     * Buscar por slug
     */
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Gerar slug automaticamente
     */
    public function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Verificar se já existe
        $count = 1;
        $originalSlug = $slug;
        
        while (self::where('slug', $slug)->first()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    /**
     * Criar excerpt automaticamente
     */
    public function generateExcerpt($content, $length = 150)
    {
        $excerpt = strip_tags($content);
        if (strlen($excerpt) > $length) {
            $excerpt = substr($excerpt, 0, $length) . '...';
        }
        return $excerpt;
    }
}
```

## Etapa 3: Controllers e Rotas

### 3.1 Criar Controllers
```bash
./guepardo make:controller PostController
./guepardo make:controller BlogController
```

### 3.2 BlogController (Frontend)
```php
<?php
// app/Controllers/BlogController.php

namespace App\Controllers;

use App\Models\Post;
use App\Models\User;

class BlogController extends BaseController
{
    /**
     * Página inicial do blog
     */
    public function index()
    {
        $posts = Post::published()
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('blog/index', [
            'title' => 'Blog',
            'posts' => $posts
        ]);
    }

    /**
     * Exibir post individual
     */
    public function show($slug)
    {
        $post = Post::findBySlug($slug);
        
        if (!$post || $post->status !== 'published') {
            return $this->notFound();
        }

        $author = $post->user();

        return $this->view('blog/show', [
            'title' => $post->title,
            'post' => $post,
            'author' => $author
        ]);
    }

    /**
     * Posts por autor
     */
    public function author($userId)
    {
        $author = User::find($userId);
        
        if (!$author) {
            return $this->notFound();
        }

        $posts = Post::where('user_id', $userId)
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->get();

        return $this->view('blog/author', [
            'title' => 'Posts de ' . $author->name,
            'author' => $author,
            'posts' => $posts
        ]);
    }
}
```

### 3.3 PostController (Admin)
```php
<?php
// app/Controllers/PostController.php

namespace App\Controllers;

use App\Models\Post;

class PostController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listar posts do usuário
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $posts = Post::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();

        return $this->view('posts/index', [
            'title' => 'Meus Posts',
            'posts' => $posts
        ]);
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return $this->view('posts/create', [
            'title' => 'Novo Post'
        ]);
    }

    /**
     * Salvar novo post
     */
    public function store()
    {
        $data = $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ]);

        $post = new Post();
        $data['user_id'] = $_SESSION['user_id'];
        $data['slug'] = $post->generateSlug($data['title']);
        
        if (empty($data['excerpt'])) {
            $data['excerpt'] = $post->generateExcerpt($data['content']);
        }

        $post = Post::create($data);

        $this->flash('success', 'Post criado com sucesso!');
        return $this->redirect('/dashboard/posts');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $post = Post::find($id);
        
        if (!$post || $post->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        return $this->view('posts/edit', [
            'title' => 'Editar Post',
            'post' => $post
        ]);
    }

    /**
     * Atualizar post
     */
    public function update($id)
    {
        $post = Post::find($id);
        
        if (!$post || $post->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $data = $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ]);

        // Atualizar slug se título mudou
        if ($data['title'] !== $post->title) {
            $data['slug'] = $post->generateSlug($data['title']);
        }

        if (empty($data['excerpt'])) {
            $data['excerpt'] = $post->generateExcerpt($data['content']);
        }

        $post->update($data);

        $this->flash('success', 'Post atualizado com sucesso!');
        return $this->redirect('/dashboard/posts');
    }

    /**
     * Excluir post
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        
        if (!$post || $post->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $post->delete();

        $this->flash('success', 'Post excluído com sucesso!');
        return $this->redirect('/dashboard/posts');
    }
}
```

### 3.4 Configurar Rotas
```php
<?php
// routes/web.php

// Blog público
$router->get('/', 'BlogController@index');
$router->get('/blog', 'BlogController@index');
$router->get('/blog/{slug}', 'BlogController@show');
$router->get('/author/{id}', 'BlogController@author');

// Autenticação (já existente)
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Dashboard (protegido)
$router->get('/dashboard', 'HomeController@dashboard');

// Gestão de posts (protegido)
$router->get('/dashboard/posts', 'PostController@index');
$router->get('/dashboard/posts/create', 'PostController@create');
$router->post('/dashboard/posts', 'PostController@store');
$router->get('/dashboard/posts/{id}/edit', 'PostController@edit');
$router->post('/dashboard/posts/{id}', 'PostController@update');
$router->post('/dashboard/posts/{id}/delete', 'PostController@destroy');
```

## Etapa 4: Views e Templates

### 4.1 Layout Principal
```php
<!-- app/Views/layouts/main.guepardo.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Meu Blog' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-gray-800">Meu Blog</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/blog" class="text-gray-600 hover:text-gray-900">Blog</a>
                    @if(isset($_SESSION['user_id']))
                        <a href="/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <form method="POST" action="/logout" class="inline">
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Sair</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="/register" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Registrar</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(isset($_SESSION['flash_success']))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ $_SESSION['flash_success'] }}
            </div>
            {{ unset($_SESSION['flash_success']) }}
        @endif

        @if(isset($_SESSION['flash_error']))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $_SESSION['flash_error'] }}
            </div>
            {{ unset($_SESSION['flash_error']) }}
        @endif

        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-8 px-4 text-center">
            <p>&copy; 2025 Meu Blog. Feito com GuepardoSys Micro PHP.</p>
        </div>
    </footer>
</body>
</html>
```

### 4.2 Página Inicial do Blog
```php
<!-- app/Views/blog/index.guepardo.php -->
@extends('layouts.main')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Blog</h1>
    
    @if(empty($posts))
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg">Nenhum post publicado ainda.</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <a href="/blog/{{ $post->slug }}" class="hover:text-blue-600">
                                {{ $post->title }}
                            </a>
                        </h2>
                        
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <span>Por {{ $post->user()->name ?? 'Autor' }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ date('d/m/Y', strtotime($post->created_at)) }}</span>
                        </div>
                        
                        <p class="text-gray-700 mb-4">{{ $post->excerpt }}</p>
                        
                        <a href="/blog/{{ $post->slug }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            Ler mais →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
```

### 4.3 Post Individual
```php
<!-- app/Views/blog/show.guepardo.php -->
@extends('layouts.main')

@section('content')
<div class="max-w-4xl mx-auto">
    <article class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>
            
            <div class="flex items-center text-sm text-gray-500 mb-8">
                <span>Por 
                    <a href="/author/{{ $author->id }}" class="text-blue-600 hover:text-blue-800">
                        {{ $author->name }}
                    </a>
                </span>
                <span class="mx-2">•</span>
                <span>{{ date('d/m/Y H:i', strtotime($post->created_at)) }}</span>
            </div>
            
            <div class="prose prose-lg max-w-none">
                {!! nl2br(e($post->content)) !!}
            </div>
        </div>
    </article>
    
    <div class="mt-8 text-center">
        <a href="/blog" class="text-blue-600 hover:text-blue-800 font-medium">
            ← Voltar ao blog
        </a>
    </div>
</div>
@endsection
```

### 4.4 Dashboard - Lista de Posts
```php
<!-- app/Views/posts/index.guepardo.php -->
@extends('layouts.main')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Meus Posts</h1>
        <a href="/dashboard/posts/create" 
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Novo Post
        </a>
    </div>
    
    @if(empty($posts))
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg mb-4">Você ainda não criou nenhum post.</p>
            <a href="/dashboard/posts/create" 
               class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                Criar Primeiro Post
            </a>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Título
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Criado em
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($posts as $post)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $post->title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $post->excerpt }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($post->status === 'published')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Publicado
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Rascunho
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ date('d/m/Y', strtotime($post->created_at)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="/dashboard/posts/{{ $post->id }}/edit" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    Editar
                                </a>
                                @if($post->status === 'published')
                                    <a href="/blog/{{ $post->slug }}" 
                                       class="text-green-600 hover:text-green-900 mr-3" target="_blank">
                                        Ver
                                    </a>
                                @endif
                                <form method="POST" action="/dashboard/posts/{{ $post->id }}/delete" 
                                      class="inline" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir este post?')">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
```

### 4.5 Formulário de Criação/Edição
```php
<!-- app/Views/posts/create.guepardo.php -->
@extends('layouts.main')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $title }}</h1>
    
    <form method="POST" action="/dashboard/posts" class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Título
            </label>
            <input type="text" name="title" id="title" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="{{ old('title') }}">
        </div>
        
        <div class="mb-6">
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                Conteúdo
            </label>
            <textarea name="content" id="content" rows="15" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('content') }}</textarea>
        </div>
        
        <div class="mb-6">
            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                Resumo (opcional)
            </label>
            <textarea name="excerpt" id="excerpt" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Deixe em branco para gerar automaticamente">{{ old('excerpt') }}</textarea>
        </div>
        
        <div class="mb-6">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>
            <select name="status" id="status" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>
                    Rascunho
                </option>
                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>
                    Publicado
                </option>
            </select>
        </div>
        
        <div class="flex justify-between items-center">
            <a href="/dashboard/posts" class="text-gray-600 hover:text-gray-800">
                ← Voltar
            </a>
            <button type="submit" 
                    class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                Salvar Post
            </button>
        </div>
    </form>
</div>
@endsection
```

## Etapa 5: Testando a Aplicação

### 5.1 Compilar Assets
```bash
# Desenvolvimento
bun run dev

# Produção
bun run build
```

### 5.2 Iniciar Servidor
```bash
./guepardo serve
```

### 5.3 Testar Funcionalidades

1. **Registro/Login**:
   - Acesse `http://localhost:8000/register`
   - Crie uma conta
   - Faça login

2. **Criar Posts**:
   - Acesse `/dashboard/posts`
   - Clique em "Novo Post"
   - Crie alguns posts

3. **Visualizar Blog**:
   - Acesse `/blog`
   - Clique nos posts para ver detalhes

### 5.4 Executar Testes
```bash
./guepardo test
./guepardo quality
```

## Etapa 6: Deploy em Produção

### 6.1 Otimizar para Produção
```bash
# Otimizações
./guepardo optimize

# Build de assets
bun run build

# Configurar .env para produção
APP_ENV=production
APP_DEBUG=false
```

### 6.2 Upload para Servidor
```bash
# Hospedar em shared hosting
rsync -avz --exclude='.git' --exclude='node_modules' ./ usuario@servidor:~/public_html/
```

### 6.3 Configuração Final
```bash
# No servidor
chmod 755 guepardo
chmod -R 775 storage/
./guepardo cache:clear
./guepardo migrate
```

## 🎯 Próximos Passos

### Funcionalidades Avançadas
- Sistema de comentários
- Tags e categorias
- Upload de imagens
- Editor WYSIWYG
- Sistema de busca
- Feed RSS
- SEO otimizado

### Melhorias de Performance
- Cache de queries
- Paginação de posts
- Lazy loading de imagens
- CDN para assets

### Recursos Administrativos
- Painel de administração
- Moderação de comentários
- Analytics básico
- Backup automático

## 📚 Recursos Adicionais

- **[API Reference](api-reference.md)** - Documentação completa
- **[Best Practices](best-practices.md)** - Melhores práticas
- **[Security Guide](security.md)** - Guia de segurança
- **[Performance Tips](performance.md)** - Dicas de performance

---

**🎉 Parabéns! Você criou um sistema de blog completo com GuepardoSys Micro PHP!**

O framework oferece todos os recursos necessários para criar aplicações web modernas e performáticas. Continue explorando a documentação para descobrir mais funcionalidades avançadas.
