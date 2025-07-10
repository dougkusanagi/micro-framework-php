# Aplicação de Exemplo - Sistema de Tarefas

## 📋 Visão Geral

Esta é uma aplicação completa de exemplo criada com o GuepardoSys Micro PHP Framework. Demonstra um sistema de gerenciamento de tarefas (Todo List) com todas as funcionalidades principais do framework.

## 🎯 Funcionalidades

- ✅ Autenticação de usuários
- ✅ CRUD completo de tarefas
- ✅ Categorias de tarefas
- ✅ Status de tarefas (pendente, em progresso, concluída)
- ✅ Filtros e busca
- ✅ Dashboard com estatísticas
- ✅ API REST para mobile
- ✅ Interface responsiva

## 🗄️ Banco de Dados

### Migration: Categorias

```sql
-- database/migrations/003_create_categories_table.sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_categories_user_id ON categories(user_id);
```

### Migration: Tarefas

```sql
-- database/migrations/004_create_tasks_table.sql
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE NULL,
    completed_at TIMESTAMP NULL,
    user_id INT NOT NULL,
    category_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE INDEX idx_tasks_user_id ON tasks(user_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_category_id ON tasks(category_id);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
```

### Seeds: Dados de Exemplo

```sql
-- database/seeds/003_categories_seed.sql
INSERT INTO categories (name, color, user_id) VALUES
('Trabalho', '#dc3545', 1),
('Pessoal', '#28a745', 1),
('Estudos', '#007bff', 1),
('Casa', '#ffc107', 1);

INSERT INTO categories (name, color, user_id) VALUES
('Projetos', '#6f42c1', 2),
('Família', '#fd7e14', 2);
```

```sql
-- database/seeds/004_tasks_seed.sql
INSERT INTO tasks (title, description, status, priority, due_date, user_id, category_id) VALUES
('Finalizar relatório mensal', 'Completar análise de vendas do mês', 'pending', 'high', '2025-01-15', 1, 1),
('Reunião com cliente', 'Apresentar proposta de projeto', 'in_progress', 'high', '2025-01-12', 1, 1),
('Comprar ingredientes', 'Lista: tomate, cebola, alho', 'pending', 'medium', '2025-01-10', 1, 4),
('Estudar PHP avançado', 'Revisar conceitos de POO', 'in_progress', 'medium', '2025-01-20', 1, 3),
('Organizar escritório', 'Limpar e organizar mesa de trabalho', 'completed', 'low', '2025-01-08', 1, 4);
```

## 📂 Models

### Category Model

```php
<?php
// app/Models/Category.php

namespace App\Models;

use Src\Core\BaseModel;

class Category extends BaseModel
{
    protected $table = 'categories';
    
    protected $fillable = [
        'name', 'color', 'user_id'
    ];

    /**
     * Relacionamento com User
     */
    public function user()
    {
        return User::find($this->user_id);
    }

    /**
     * Relacionamento com Tasks
     */
    public function tasks()
    {
        return Task::where('category_id', $this->id)->get();
    }

    /**
     * Contar tarefas da categoria
     */
    public function tasksCount()
    {
        return Task::where('category_id', $this->id)->count();
    }

    /**
     * Tarefas pendentes da categoria
     */
    public function pendingTasks()
    {
        return Task::where('category_id', $this->id)
                   ->where('status', 'pending')
                   ->get();
    }

    /**
     * Categorias do usuário
     */
    public static function forUser($userId)
    {
        return self::where('user_id', $userId)
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Validar cor hexadecimal
     */
    public function setColorAttribute($value)
    {
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            $this->attributes['color'] = $value;
        } else {
            $this->attributes['color'] = '#007bff';
        }
    }
}
```

### Task Model

```php
<?php
// app/Models/Task.php

namespace App\Models;

use Src\Core\BaseModel;

class Task extends BaseModel
{
    protected $table = 'tasks';
    
    protected $fillable = [
        'title', 'description', 'status', 'priority', 
        'due_date', 'user_id', 'category_id'
    ];

    /**
     * Relacionamento com User
     */
    public function user()
    {
        return User::find($this->user_id);
    }

    /**
     * Relacionamento com Category
     */
    public function category()
    {
        return Category::find($this->category_id);
    }

    /**
     * Scopes para status
     */
    public static function pending()
    {
        return self::where('status', 'pending');
    }

    public static function inProgress()
    {
        return self::where('status', 'in_progress');
    }

    public static function completed()
    {
        return self::where('status', 'completed');
    }

    /**
     * Scopes para prioridade
     */
    public static function highPriority()
    {
        return self::where('priority', 'high');
    }

    public static function overdue()
    {
        return self::where('due_date', '<', date('Y-m-d'))
                   ->where('status', '!=', 'completed');
    }

    /**
     * Tarefas do usuário
     */
    public static function forUser($userId)
    {
        return self::where('user_id', $userId);
    }

    /**
     * Buscar tarefas
     */
    public static function search($query)
    {
        return self::where('title', 'LIKE', "%{$query}%")
                   ->orWhere('description', 'LIKE', "%{$query}%");
    }

    /**
     * Marcar como concluída
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verificar se está atrasada
     */
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date < date('Y-m-d') && 
               $this->status !== 'completed';
    }

    /**
     * Accessor para cor da prioridade
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'text-green-600',
            'medium' => 'text-yellow-600',
            'high' => 'text-red-600'
        ];
        
        return $colors[$this->priority] ?? 'text-gray-600';
    }

    /**
     * Accessor para cor do status
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800'
        ];
        
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Accessor para status em português
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendente',
            'in_progress' => 'Em Progresso',
            'completed' => 'Concluída'
        ];
        
        return $labels[$this->status] ?? 'Desconhecido';
    }

    /**
     * Accessor para prioridade em português
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta'
        ];
        
        return $labels[$this->priority] ?? 'Desconhecida';
    }
}
```

## 🎮 Controllers

### DashboardController

```php
<?php
// app/Controllers/DashboardController.php

namespace App\Controllers;

use App\Models\Task;
use App\Models\Category;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Estatísticas gerais
        $stats = [
            'total' => Task::forUser($userId)->count(),
            'pending' => Task::forUser($userId)->pending()->count(),
            'in_progress' => Task::forUser($userId)->inProgress()->count(),
            'completed' => Task::forUser($userId)->completed()->count(),
            'overdue' => Task::forUser($userId)->overdue()->count()
        ];

        // Tarefas recentes
        $recentTasks = Task::forUser($userId)
                          ->orderBy('created_at', 'DESC')
                          ->limit(5)
                          ->get();

        // Tarefas em atraso
        $overdueTasks = Task::forUser($userId)
                           ->overdue()
                           ->orderBy('due_date', 'ASC')
                           ->limit(5)
                           ->get();

        // Tarefas de alta prioridade
        $highPriorityTasks = Task::forUser($userId)
                                ->highPriority()
                                ->where('status', '!=', 'completed')
                                ->orderBy('due_date', 'ASC')
                                ->limit(5)
                                ->get();

        // Estatísticas por categoria
        $categories = Category::forUser($userId);
        $categoryStats = [];
        foreach ($categories as $category) {
            $categoryStats[] = [
                'category' => $category,
                'total' => Task::where('category_id', $category->id)->count(),
                'completed' => Task::where('category_id', $category->id)
                                  ->completed()
                                  ->count()
            ];
        }

        return $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentTasks' => $recentTasks,
            'overdueTasks' => $overdueTasks,
            'highPriorityTasks' => $highPriorityTasks,
            'categoryStats' => $categoryStats
        ]);
    }

    /**
     * Estatísticas para gráficos (AJAX)
     */
    public function stats()
    {
        $userId = $_SESSION['user_id'];

        // Tarefas por status
        $statusStats = [
            'pending' => Task::forUser($userId)->pending()->count(),
            'in_progress' => Task::forUser($userId)->inProgress()->count(),
            'completed' => Task::forUser($userId)->completed()->count()
        ];

        // Tarefas por prioridade
        $priorityStats = [
            'low' => Task::forUser($userId)->where('priority', 'low')->count(),
            'medium' => Task::forUser($userId)->where('priority', 'medium')->count(),
            'high' => Task::forUser($userId)->where('priority', 'high')->count()
        ];

        // Tarefas criadas nos últimos 7 dias
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dailyStats[$date] = Task::forUser($userId)
                                    ->where('created_at', 'LIKE', $date . '%')
                                    ->count();
        }

        return $this->json([
            'status' => $statusStats,
            'priority' => $priorityStats,
            'daily' => $dailyStats
        ]);
    }
}
```

### TaskController

```php
<?php
// app/Controllers/TaskController.php

namespace App\Controllers;

use App\Models\Task;
use App\Models\Category;

class TaskController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listar tarefas com filtros
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Filtros
        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $search = $_GET['search'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 15;

        // Query base
        $query = Task::forUser($userId);

        // Aplicar filtros
        if ($status) {
            $query = $query->where('status', $status);
        }

        if ($category) {
            $query = $query->where('category_id', $category);
        }

        if ($priority) {
            $query = $query->where('priority', $priority);
        }

        if ($search) {
            $query = $query->where(function($q) use ($search) {
                return $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Paginação
        $total = $query->count();
        $tasks = $query->orderBy('due_date', 'ASC')
                      ->orderBy('priority', 'DESC')
                      ->limit($perPage)
                      ->offset(($page - 1) * $perPage)
                      ->get();

        // Adicionar dados das categorias
        foreach ($tasks as $task) {
            $task->category_data = $task->category();
        }

        // Categorias para filtro
        $categories = Category::forUser($userId);

        return $this->view('tasks.index', [
            'title' => 'Minhas Tarefas',
            'tasks' => $tasks,
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category' => $category,
                'priority' => $priority,
                'search' => $search
            ],
            'pagination' => [
                'current' => $page,
                'total' => ceil($total / $perPage),
                'hasNext' => $page < ceil($total / $perPage),
                'hasPrev' => $page > 1
            ]
        ]);
    }

    /**
     * Exibir tarefa
     */
    public function show($id)
    {
        $task = Task::find($id);

        if (!$task || $task->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $task->category_data = $task->category();

        return $this->view('tasks.show', [
            'title' => $task->title,
            'task' => $task
        ]);
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $categories = Category::forUser($_SESSION['user_id']);

        return $this->view('tasks.create', [
            'title' => 'Nova Tarefa',
            'categories' => $categories
        ]);
    }

    /**
     * Salvar nova tarefa
     */
    public function store()
    {
        $data = $this->validate([
            'title' => 'required|max:255',
            'description' => 'max:1000',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'date',
            'category_id' => 'integer'
        ]);

        $data['user_id'] = $_SESSION['user_id'];
        $data['status'] = 'pending';

        // Verificar se categoria pertence ao usuário
        if (!empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if (!$category || $category->user_id != $_SESSION['user_id']) {
                unset($data['category_id']);
            }
        }

        $task = Task::create($data);

        $this->flash('success', 'Tarefa criada com sucesso!');
        return $this->redirect('/tasks/' . $task->id);
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $task = Task::find($id);

        if (!$task || $task->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $categories = Category::forUser($_SESSION['user_id']);

        return $this->view('tasks.edit', [
            'title' => 'Editar Tarefa',
            'task' => $task,
            'categories' => $categories
        ]);
    }

    /**
     * Atualizar tarefa
     */
    public function update($id)
    {
        $task = Task::find($id);

        if (!$task || $task->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $data = $this->validate([
            'title' => 'required|max:255',
            'description' => 'max:1000',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'date',
            'category_id' => 'integer'
        ]);

        // Verificar se categoria pertence ao usuário
        if (!empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if (!$category || $category->user_id != $_SESSION['user_id']) {
                unset($data['category_id']);
            }
        }

        // Se mudou para concluída, marcar data
        if ($data['status'] === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($data['status'] !== 'completed') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        $this->flash('success', 'Tarefa atualizada com sucesso!');
        return $this->redirect('/tasks/' . $task->id);
    }

    /**
     * Excluir tarefa
     */
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task || $task->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $task->delete();

        $this->flash('success', 'Tarefa excluída com sucesso!');
        return $this->redirect('/tasks');
    }

    /**
     * Alternar status (AJAX)
     */
    public function toggleStatus($id)
    {
        $task = Task::find($id);

        if (!$task || $task->user_id != $_SESSION['user_id']) {
            return $this->json(['error' => 'Tarefa não encontrada'], 404);
        }

        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        
        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? date('Y-m-d H:i:s') : null
        ]);

        return $this->json([
            'success' => true,
            'status' => $newStatus,
            'status_label' => $task->status_label,
            'status_color' => $task->status_color
        ]);
    }
}
```

### CategoryController

```php
<?php
// app/Controllers/CategoryController.php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Task;

class CategoryController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listar categorias
     */
    public function index()
    {
        $categories = Category::forUser($_SESSION['user_id']);

        // Adicionar contagem de tarefas
        foreach ($categories as $category) {
            $category->tasks_count = $category->tasksCount();
            $category->pending_count = Task::where('category_id', $category->id)
                                          ->pending()
                                          ->count();
        }

        return $this->view('categories.index', [
            'title' => 'Categorias',
            'categories' => $categories
        ]);
    }

    /**
     * Exibir categoria e suas tarefas
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category || $category->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $tasks = Task::where('category_id', $id)
                    ->orderBy('due_date', 'ASC')
                    ->orderBy('priority', 'DESC')
                    ->get();

        $stats = [
            'total' => count($tasks),
            'pending' => Task::where('category_id', $id)->pending()->count(),
            'in_progress' => Task::where('category_id', $id)->inProgress()->count(),
            'completed' => Task::where('category_id', $id)->completed()->count()
        ];

        return $this->view('categories.show', [
            'title' => $category->name,
            'category' => $category,
            'tasks' => $tasks,
            'stats' => $stats
        ]);
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return $this->view('categories.create', [
            'title' => 'Nova Categoria'
        ]);
    }

    /**
     * Salvar categoria
     */
    public function store()
    {
        $data = $this->validate([
            'name' => 'required|max:100',
            'color' => 'required|regex:/^#[a-f0-9]{6}$/i'
        ]);

        $data['user_id'] = $_SESSION['user_id'];

        Category::create($data);

        $this->flash('success', 'Categoria criada com sucesso!');
        return $this->redirect('/categories');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $category = Category::find($id);

        if (!$category || $category->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        return $this->view('categories.edit', [
            'title' => 'Editar Categoria',
            'category' => $category
        ]);
    }

    /**
     * Atualizar categoria
     */
    public function update($id)
    {
        $category = Category::find($id);

        if (!$category || $category->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        $data = $this->validate([
            'name' => 'required|max:100',
            'color' => 'required|regex:/^#[a-f0-9]{6}$/i'
        ]);

        $category->update($data);

        $this->flash('success', 'Categoria atualizada com sucesso!');
        return $this->redirect('/categories');
    }

    /**
     * Excluir categoria
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category || $category->user_id != $_SESSION['user_id']) {
            return $this->notFound();
        }

        // Verificar se tem tarefas
        $tasksCount = $category->tasksCount();
        
        if ($tasksCount > 0) {
            $this->flash('error', 'Não é possível excluir categoria com tarefas. Mova as tarefas primeiro.');
            return $this->redirect('/categories');
        }

        $category->delete();

        $this->flash('success', 'Categoria excluída com sucesso!');
        return $this->redirect('/categories');
    }
}
```

## 🎨 Views Principais

### Dashboard

```php
<!-- app/Views/dashboard/index.guepardo.php -->
@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Resumo das suas tarefas</p>
    </div>

    <!-- Estatísticas Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Pendentes</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Em Progresso</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Concluídas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Em Atraso</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Tarefas Recentes -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tarefas Recentes</h3>
            </div>
            <div class="p-6">
                @if(empty($recentTasks))
                    <p class="text-gray-500 text-center py-4">Nenhuma tarefa encontrada</p>
                @else
                    <div class="space-y-4">
                        @foreach($recentTasks as $task)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $task->title }}</h4>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <span class="px-2 py-1 rounded text-xs {{ $task->status_color }}">
                                            {{ $task->status_label }}
                                        </span>
                                        <span class="{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                                        @if($task->due_date)
                                            <span>• {{ date('d/m/Y', strtotime($task->due_date)) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="/tasks/{{ $task->id }}" class="text-blue-600 hover:text-blue-800">
                                    Ver
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="mt-4 text-center">
                    <a href="/tasks" class="text-blue-600 hover:text-blue-800">Ver todas as tarefas →</a>
                </div>
            </div>
        </div>

        <!-- Tarefas em Atraso -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tarefas em Atraso</h3>
            </div>
            <div class="p-6">
                @if(empty($overdueTasks))
                    <p class="text-gray-500 text-center py-4">Nenhuma tarefa em atraso 🎉</p>
                @else
                    <div class="space-y-4">
                        @foreach($overdueTasks as $task)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $task->title }}</h4>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <span class="text-red-600">
                                            Venceu em {{ date('d/m/Y', strtotime($task->due_date)) }}
                                        </span>
                                        <span class="px-2 py-1 rounded text-xs {{ $task->priority_color }}">
                                            {{ $task->priority_label }}
                                        </span>
                                    </div>
                                </div>
                                <a href="/tasks/{{ $task->id }}" class="text-red-600 hover:text-red-800">
                                    Ver
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Estatísticas por Categoria -->
    @if(!empty($categoryStats))
        <div class="mt-8 bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Estatísticas por Categoria</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($categoryStats as $stat)
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded" style="background-color: {{ $stat['category']->color }}"></div>
                                    <span class="ml-2 font-medium">{{ $stat['category']->name }}</span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $stat['total'] }} tarefas</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full" 
                                     style="background-color: {{ $stat['category']->color }}; width: {{ $stat['total'] > 0 ? ($stat['completed'] / $stat['total']) * 100 : 0 }}%"></div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $stat['completed'] }} de {{ $stat['total'] }} concluídas
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
// Gráficos opcionais com Chart.js
</script>
@endsection
```

### Lista de Tarefas

```php
<!-- app/Views/tasks/index.guepardo.php -->
@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Minhas Tarefas</h1>
            <p class="text-gray-600">Gerencie suas tarefas</p>
        </div>
        <a href="/tasks/create" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Nova Tarefa
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="/tasks" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Busca -->
            <div>
                <input type="text" name="search" placeholder="Buscar tarefas..." 
                       value="{{ $filters['search'] }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status -->
            <div>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os status</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="in_progress" {{ $filters['status'] === 'in_progress' ? 'selected' : '' }}>Em Progresso</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Concluída</option>
                </select>
            </div>

            <!-- Categoria -->
            <div>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $filters['category'] == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Prioridade -->
            <div>
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as prioridades</option>
                    <option value="low" {{ $filters['priority'] === 'low' ? 'selected' : '' }}>Baixa</option>
                    <option value="medium" {{ $filters['priority'] === 'medium' ? 'selected' : '' }}>Média</option>
                    <option value="high" {{ $filters['priority'] === 'high' ? 'selected' : '' }}>Alta</option>
                </select>
            </div>

            <!-- Botões -->
            <div class="md:col-span-4 flex space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Filtrar
                </button>
                <a href="/tasks" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Tarefas -->
    @if(empty($tasks))
        <div class="bg-white p-12 rounded-lg shadow-md text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma tarefa encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova tarefa</p>
            <div class="mt-6">
                <a href="/tasks/create" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Nova Tarefa
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @foreach($tasks as $task)
                <div class="border-b border-gray-200 p-6 hover:bg-gray-50 {{ $task->isOverdue() ? 'bg-red-50 border-red-200' : '' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <!-- Checkbox para marcar como concluída -->
                                <button onclick="toggleTask({{ $task->id }})" 
                                        class="flex-shrink-0 w-5 h-5 rounded border-2 {{ $task->status === 'completed' ? 'bg-green-500 border-green-500' : 'border-gray-300' }} flex items-center justify-center">
                                    @if($task->status === 'completed')
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                        </svg>
                                    @endif
                                </button>

                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900 {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                                        {{ $task->title }}
                                    </h3>
                                    
                                    @if($task->description)
                                        <p class="text-gray-600 mt-1">{{ substr($task->description, 0, 100) }}{{ strlen($task->description) > 100 ? '...' : '' }}</p>
                                    @endif

                                    <div class="flex items-center space-x-4 mt-2">
                                        <!-- Status -->
                                        <span class="px-2 py-1 rounded text-xs {{ $task->status_color }}">
                                            {{ $task->status_label }}
                                        </span>

                                        <!-- Prioridade -->
                                        <span class="text-sm {{ $task->priority_color }}">
                                            {{ $task->priority_label }}
                                        </span>

                                        <!-- Categoria -->
                                        @if($task->category_data)
                                            <span class="flex items-center text-sm text-gray-600">
                                                <div class="w-3 h-3 rounded mr-1" style="background-color: {{ $task->category_data->color }}"></div>
                                                {{ $task->category_data->name }}
                                            </span>
                                        @endif

                                        <!-- Data de vencimento -->
                                        @if($task->due_date)
                                            <span class="text-sm {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                                {{ $task->isOverdue() ? 'Venceu em' : 'Vence em' }} {{ date('d/m/Y', strtotime($task->due_date)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="flex items-center space-x-2">
                            <a href="/tasks/{{ $task->id }}" 
                               class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded">
                                Ver
                            </a>
                            <a href="/tasks/{{ $task->id }}/edit" 
                               class="text-gray-600 hover:text-gray-800 px-3 py-1 rounded">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        @if($pagination['total'] > 1)
            <div class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    @if($pagination['hasPrev'])
                        <a href="?page={{ $pagination['current'] - 1 }}&{{ http_build_query($filters) }}" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Anterior
                        </a>
                    @endif

                    <span class="px-3 py-2 bg-blue-600 text-white rounded-md">
                        {{ $pagination['current'] }} de {{ $pagination['total'] }}
                    </span>

                    @if($pagination['hasNext'])
                        <a href="?page={{ $pagination['current'] + 1 }}&{{ http_build_query($filters) }}" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Próxima
                        </a>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

@section('scripts')
<script>
async function toggleTask(taskId) {
    try {
        const response = await fetch(`/tasks/${taskId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (response.ok) {
            location.reload();
        } else {
            alert('Erro ao atualizar tarefa');
        }
    } catch (error) {
        alert('Erro ao atualizar tarefa');
    }
}
</script>
@endsection
```

## 🚀 Rotas

```php
<?php
// routes/web.php

// Página inicial
$router->get('/', 'HomeController@index');

// Autenticação
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Dashboard (protegido)
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/stats', 'DashboardController@stats');

// Tarefas (protegido)
$router->get('/tasks', 'TaskController@index');
$router->get('/tasks/create', 'TaskController@create');
$router->post('/tasks', 'TaskController@store');
$router->get('/tasks/{id}', 'TaskController@show');
$router->get('/tasks/{id}/edit', 'TaskController@edit');
$router->post('/tasks/{id}', 'TaskController@update');
$router->post('/tasks/{id}/delete', 'TaskController@destroy');
$router->post('/tasks/{id}/toggle', 'TaskController@toggleStatus'); // AJAX

// Categorias (protegido)
$router->get('/categories', 'CategoryController@index');
$router->get('/categories/create', 'CategoryController@create');
$router->post('/categories', 'CategoryController@store');
$router->get('/categories/{id}', 'CategoryController@show');
$router->get('/categories/{id}/edit', 'CategoryController@edit');
$router->post('/categories/{id}', 'CategoryController@update');
$router->post('/categories/{id}/delete', 'CategoryController@destroy');

// API REST (futuro)
$router->get('/api/tasks', 'Api\TaskController@index');
$router->post('/api/tasks', 'Api\TaskController@store');
$router->get('/api/tasks/{id}', 'Api\TaskController@show');
$router->put('/api/tasks/{id}', 'Api\TaskController@update');
$router->delete('/api/tasks/{id}', 'Api\TaskController@destroy');
```

## 🧪 Testes

```php
// tests/Feature/TaskTest.php
test('user can create task', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'password123'
    ]);

    $_SESSION['user_id'] = $user->id;

    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test Description',
        'priority' => 'high',
        'due_date' => '2025-01-15'
    ];

    $response = $this->post('/tasks', $taskData);

    expect(Task::where('title', 'Test Task')->first())->not->toBeNull();
});

test('user cannot view other user tasks', function () {
    $user1 = User::create(['name' => 'User 1', 'email' => 'user1@test.com', 'password' => 'password']);
    $user2 = User::create(['name' => 'User 2', 'email' => 'user2@test.com', 'password' => 'password']);

    $task = Task::create([
        'title' => 'Private Task',
        'user_id' => $user1->id,
        'priority' => 'medium'
    ]);

    $_SESSION['user_id'] = $user2->id;

    $response = $this->get('/tasks/' . $task->id);
    expect($response->getStatusCode())->toBe(404);
});
```

## 🚀 Executando a Aplicação

### 1. Instalação
```bash
# Migrações
./guepardo migrate

# Seeds
./guepardo db:seed

# Assets
bun run build
```

### 2. Execução
```bash
# Servidor de desenvolvimento
./guepardo serve

# Acessar http://localhost:8000
```

### 3. Uso
1. Registrar usuário em `/register`
2. Fazer login em `/login`
3. Acessar dashboard em `/dashboard`
4. Criar categorias em `/categories`
5. Gerenciar tarefas em `/tasks`

---

**🎯 Esta aplicação demonstra todos os recursos principais do GuepardoSys Micro PHP Framework de forma prática e real!**
