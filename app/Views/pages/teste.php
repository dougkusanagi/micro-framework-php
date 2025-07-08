@extends('layouts.app')

@section('title', 'Teste do Template Engine')

@section('content')
<div class="hero">
    <h2>Teste do Template Engine</h2>
    <p>Se você está vendo esta página, o sistema de templates está funcionando!</p>
</div>

<div style="margin-top: 2rem;">
    <h3>Variáveis:</h3>
    <p><strong>Nome:</strong> {{ $nome }}</p>
    <p><strong>Versão:</strong> {{ $versao }}</p>

    <h3>Condicional:</h3>
    @if($mostrarLista)
    <h4>Lista de itens:</h4>
    <ul>
        @foreach($itens as $item)
        <li>{{ $item }}</li>
        @endforeach
    </ul>
    @else
    <p>Lista não disponível</p>
    @endif
</div>
@endsection
