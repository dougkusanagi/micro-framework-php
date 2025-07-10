@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $title }}</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4 text-blue-600">
            <i class="fas fa-memory"></i> Cache Helper Function
        </h2>

        <p class="text-gray-600 mb-4">
            Este exemplo mostra como usar o cache através da função helper <code>cache_remember()</code>.
            Os dados são calculados apenas uma vez e reutilizados nas próximas requisições.
        </p>

        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h3 class="font-semibold mb-2">Dados Cacheados:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-3 rounded border">
                    <strong>Resultado:</strong> {{ $data['result'] }}
                </div>
                <div class="bg-white p-3 rounded border">
                    <strong>Calculado em:</strong> {{ $data['calculated_at'] }}
                </div>
                <div class="bg-white p-3 rounded border">
                    <strong>Tempo processamento:</strong> {{ $data['processing_time'] }}
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <h4 class="font-semibold text-blue-800">Como funciona:</h4>
            <ul class="text-blue-700 mt-2 space-y-1">
                <li>• Primeira requisição: Dados são calculados e salvos no cache</li>
                <li>• Próximas requisições: Dados são retornados diretamente do cache</li>
                <li>• Cache expira em 5 minutos, depois é recalculado</li>
            </ul>
        </div>

        <div class="mt-6">
            <h4 class="font-semibold mb-2">Código exemplo:</h4>
            <pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto"><code>$data = cache_remember('expensive.calculation', function() {
    // Operação custosa aqui
    return $result;
}, 300); // Cache por 5 minutos</code></pre>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="/cache-examples/tagged" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Próximo: Cache com Tags
            </a>
            <a href="/cache-examples/clear?action=info" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Gerenciar Cache
            </a>
        </div>
    </div>
</div>
@endsection
