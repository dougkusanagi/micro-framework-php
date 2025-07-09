@extends('layouts.app')

@section('title', 'Frontend Demo - GuepardoSys')

@section('head')
<?php echo glide_js(); ?>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-16">
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-4">
            Frontend <span class="text-primary-600">Demo</span>
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Demonstração das funcionalidades de frontend do GuepardoSys Micro PHP Framework
        </p>
    </div>

    <!-- Alpine.js Demo -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i data-lucide="zap" class="w-6 h-6 mr-2 text-primary-600"></i>
            Alpine.js Integration
        </h2>

        <div x-data="{ count: 0, message: 'Hello Alpine.js!' }" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message:</label>
                <input x-model="message" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <p class="text-lg font-semibold text-gray-900" x-text="message"></p>
                <p class="text-gray-600">Counter: <span x-text="count" class="font-bold text-primary-600"></span></p>
            </div>

            <div class="flex gap-2">
                <button @click="count++" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                    Increment
                </button>
                <button @click="count--" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    Decrement
                </button>
                <button @click="count = 0" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Tailwind CSS Demo -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i data-lucide="palette" class="w-6 h-6 mr-2 text-primary-600"></i>
            Tailwind CSS Components
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1 -->
            <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <i data-lucide="shield" class="w-8 h-8 text-primary-600"></i>
                    <span class="text-sm font-medium text-primary-700 bg-primary-200 px-2 py-1 rounded">Security</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Secure Framework</h3>
                <p class="text-gray-600">Built with security best practices from the ground up.</p>
            </div>

            <!-- Card 2 -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <i data-lucide="zap" class="w-8 h-8 text-green-600"></i>
                    <span class="text-sm font-medium text-green-700 bg-green-200 px-2 py-1 rounded">Performance</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Lightning Fast</h3>
                <p class="text-gray-600">Optimized for speed with minimal overhead.</p>
            </div>

            <!-- Card 3 -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <i data-lucide="code" class="w-8 h-8 text-purple-600"></i>
                    <span class="text-sm font-medium text-purple-700 bg-purple-200 px-2 py-1 rounded">Developer</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Developer Friendly</h3>
                <p class="text-gray-600">Clean syntax and intuitive structure.</p>
            </div>
        </div>
    </div>

    <!-- Glide.js Slider Demo -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i data-lucide="images" class="w-6 h-6 mr-2 text-primary-600"></i>
            Glide.js Image Slider
        </h2>

        <div class="glide" id="demo-slider">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    <li class="glide__slide">
                        <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg h-64 flex items-center justify-center">
                            <div class="text-center text-white">
                                <i data-lucide="image" class="w-16 h-16 mx-auto mb-4"></i>
                                <h3 class="text-2xl font-bold">Slide 1</h3>
                                <p class="text-blue-100">Beautiful image slider</p>
                            </div>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-lg h-64 flex items-center justify-center">
                            <div class="text-center text-white">
                                <i data-lucide="star" class="w-16 h-16 mx-auto mb-4"></i>
                                <h3 class="text-2xl font-bold">Slide 2</h3>
                                <p class="text-green-100">Smooth transitions</p>
                            </div>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg h-64 flex items-center justify-center">
                            <div class="text-center text-white">
                                <i data-lucide="heart" class="w-16 h-16 mx-auto mb-4"></i>
                                <h3 class="text-2xl font-bold">Slide 3</h3>
                                <p class="text-purple-100">Responsive design</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">
                    <i data-lucide="chevron-left" class="w-6 h-6"></i>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">
                    <i data-lucide="chevron-right" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="glide__bullets" data-glide-el="controls[nav]">
                <button class="glide__bullet" data-glide-dir="=0"></button>
                <button class="glide__bullet" data-glide-dir="=1"></button>
                <button class="glide__bullet" data-glide-dir="=2"></button>
            </div>
        </div>
    </div>

    <!-- JavaScript Demo -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i data-lucide="terminal" class="w-6 h-6 mr-2 text-primary-600"></i>
            JavaScript Features
        </h2>

        <div class="space-y-4">
            <button id="toast-demo" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                Show Toast Notification
            </button>

            <button id="theme-toggle" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                Toggle Theme
            </button>

            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Form Validation Demo</h3>
                <form id="demo-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <div class="error-message text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <div class="error-message text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                        Validate Form
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Demo-specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Toast demo
        document.getElementById('toast-demo').addEventListener('click', function() {
            GuepardoSys.toast.show('This is a demo toast notification!', 'success');
        });

        // Theme toggle demo
        document.getElementById('theme-toggle').addEventListener('click', function() {
            GuepardoSys.theme.toggle();
        });

        // Form validation demo
        document.getElementById('demo-form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (GuepardoSys.form.validate(this)) {
                GuepardoSys.toast.show('Form validation passed!', 'success');
            } else {
                GuepardoSys.toast.show('Please fix the errors above', 'error');
            }
        });
    });
</script>
@endsection
