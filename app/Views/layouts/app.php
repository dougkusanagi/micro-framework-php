<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GuepardoSys Micro PHP')</title>

    <!-- Google Fonts -->
    <?php echo google_fonts('Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600'); ?>

    <!-- Tailwind CSS -->
    <?php echo css('output.css'); ?>

    <!-- Alpine.js -->
    <?php echo alpine_js(); ?>

    <!-- Lucide Icons -->
    <?php echo lucide_icons(); ?>

    @yield('head')
</head>

<body>
    <header>
        <div class="container">
            <h1>{{ $appName ?? 'GuepardoSys' }}</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="/" class="{{ $currentRoute === 'home' ? 'active' : '' }}">Home</a></li>
                <li><a href="/about" class="{{ $currentRoute === 'about' ? 'active' : '' }}">About</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} GuepardoSys Micro PHP. Todos os direitos reservados.</p>
        </div>
    </footer> <!-- Scripts -->
    <?php echo js('app.js'); ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>

    @yield('scripts')
</body>

</html>
