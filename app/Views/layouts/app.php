<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GuepardoSys Micro PHP')</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    </footer>
</body>

</html>
