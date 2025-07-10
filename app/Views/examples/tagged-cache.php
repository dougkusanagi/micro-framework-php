@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $title }}</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-purple-600">
            <i class="fas fa-tags"></i> Cache com Tags
        </h2>

        <p class="text-gray-600 mb-4">
            Tags permitem agrupar entradas de cache relacionadas para invalidação em conjunto.
            Útil quando você quer limpar todo cache relacionado a um usuário ou categoria.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="font-semibold mb-3 text-blue-800">
                    👤 Dados do Usuário
                    <span class="text-xs bg-blue-200 px-2 py-1 rounded ml-2">Tags: users, profiles</span>
                </h3>
                <div class="space-y-2 text-sm">
                    <div><strong>ID:</strong> {{ $userData['id'] }}</div>
                    <div><strong>Nome:</strong> {{ $userData['name'] }}</div>
                    <div><strong>Email:</strong> {{ $userData['email'] }}</div>
                    <div><strong>Último login:</strong> {{ $userData['last_login'] }}</div>
                    <div><strong>Tema:</strong> {{ $userData['preferences']['theme'] }}</div>
                    <div><strong>Idioma:</strong> {{ $userData['preferences']['language'] }}</div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="font-semibold mb-3 text-green-800">
                    📊 Estatísticas
                    <span class="text-xs bg-green-200 px-2 py-1 rounded ml-2">Tags: users, statistics</span>
                </h3>
                <div class="space-y-2 text-sm">
                    <div><strong>Total posts:</strong> {{ $userStats['total_posts'] }}</div>
                    <div><strong>Total comentários:</strong> {{ $userStats['total_comments'] }}</div>
                    <div><strong>Reputação:</strong> {{ $userStats['reputation'] }}</div>
                    <div><strong>Membro desde:</strong> {{ $userStats['joined_date'] }}</div>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mt-6">
            <h4 class="font-semibold text-purple-800">Vantagens das Tags:</h4>
            <ul class="text-purple-700 mt-2 space-y-1">
                <li>• Invalidação granular: limpe apenas cache relacionado</li>
                <li>• Organização: agrupe caches por funcionalidade</li>
                <li>• Performance: evite limpar todo o cache desnecessariamente</li>
                <li>• Flexibilidade: uma entrada pode ter múltiplas tags</li>
            </ul>
        </div>

        <div class="mt-6">
            <h4 class="font-semibold mb-2">Código exemplo:</h4>
            <pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto"><code>// Cache com tags
$userData = cache_tags(['users', 'profiles'])
    ->remember('user.1.profile', function() {
        return User::find(1)->toArray();
    }, 1800);

// Limpar todo cache de usuários
cache_tags(['users'])->flush();</code></pre>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="/cache-examples/database" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Próximo: Cache de BD
            </a>
            <a href="/cache-examples/clear?action=users" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Limpar Cache de Usuários
            </a>
        </div>
    </div>
</div>
@endsection
