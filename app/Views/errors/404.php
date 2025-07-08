@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="mb-8">
            <h1 class="text-6xl font-bold text-gray-400">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mt-4">Page Not Found</h2>
            <p class="text-gray-600 mt-2">{{ $message ?? 'The page you are looking for does not exist.' }}</p>
        </div>

        <div class="mt-6">
            <a href="/" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
