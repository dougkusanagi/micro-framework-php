<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GuepardoSys') ?></title>
    <link rel="stylesheet" href="/assets/css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
</head>

<body class="font-sans antialiased bg-slate-50 text-gray-900 leading-relaxed">
    <header class="bg-white shadow-sm py-4">
        <div class="max-w-6xl mx-auto px-5">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600 hover:text-blue-700 transition-colors">GuepardoSys</a>
                <nav>
                    <ul class="flex space-x-8">
                        <li><a href="/" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">Home</a></li>
                        <li><a href="/about" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">About</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="min-h-screen">
        <?= $content ?? '' ?>
    </main>

    <footer class="bg-gray-900 text-white text-center py-4">
        <div class="max-w-6xl mx-auto px-5">
            <p>&copy; <?= date('Y') ?> GuepardoSys - Lightweight PHP Framework</p>
        </div>
    </footer>
</body>

</html>
