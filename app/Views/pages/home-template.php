@extends('layouts.app')

@section('title', 'Home - GuepardoSys')

@section('content')
<div class="hero">
    <h2>Bem-vindo ao {{ $appName ?? 'GuepardoSys' }}</h2>
    <p>Um micro-framework PHP leve, rápido e eficiente para hospedagem compartilhada.</p>

    @if($showFeatures ?? true)
    <div class="features">
        <div class="feature">
            <h3>🚀 Performance</h3>
            <p>TTFB inferior a 50ms em produção</p>
        </div>
        <div class="feature">
            <h3>📁 Poucos Arquivos</h3>
            <p>Menos de 200 arquivos no core</p>
        </div>
        <div class="feature">
            <h3>🎯 Simplicidade</h3>
            <p>Curva de aprendizado suave</p>
        </div>
    </div>
    @endif
</div>

<div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
    <h3>Funcionalidades Implementadas:</h3>
    <ul>
        @foreach($features as $feature)
        <li>{{ $feature }}</li>
        @endforeach
    </ul>
</div>
@endsection
