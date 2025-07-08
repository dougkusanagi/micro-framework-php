@extends('layouts.app')

@section('title', 'Sobre - GuepardoSys')

@section('content')
<div class="hero">
    <h2>Sobre o GuepardoSys</h2>
    <p>Framework PHP desenvolvido para maximizar performance em ambientes de hospedagem compartilhada.</p>
</div>

<div style="margin-top: 2rem;">
    <h3>Características Técnicas</h3>

    @if($specs ?? null)
    <div class="features">
        @foreach($specs as $spec)
        <div class="feature">
            <h4>{{ $spec['title'] }}</h4>
            <p>{{ $spec['description'] }}</p>
        </div>
        @endforeach
    </div>
    @else
    <p>Carregando especificações...</p>
    @endif
</div>

<div style="margin-top: 2rem; padding: 1rem; background: #e8f4f8; border-radius: 8px;">
    <h3>Versão do Sistema</h3>
    <p><strong>PHP:</strong> {{ $phpVersion }}</p>
    <p><strong>Framework:</strong> {{ $frameworkVersion }}</p>
    <p><strong>Ambiente:</strong> {{ $environment }}</p>
</div>
@endsection
