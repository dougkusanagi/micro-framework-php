<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GuepardoSys') ?></title>
    <link rel="stylesheet" href="/assets/css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
        }

        .dark body {
            background-color: #0F172A;
            color: #F8FAFC;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #6D28D9 0%, #EC4899 100%);
        }

        .dark .dark-gradient-bg {
            background: linear-gradient(135deg, #1E3A8A 0%, #4C1D95 100%);
        }
    </style>
</head>

<body class="font-sans antialiased bg-slate-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 leading-relaxed">
    <header class="bg-white dark:bg-slate-800 shadow-sm py-4">
        <div class="max-w-6xl mx-auto px-5">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">GuepardoSys</a>
                <nav>
                    <ul class="flex space-x-8">
                        <li><a href="/" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">Início</a></li>
                        <li><a href="/blog" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">Blog</a></li>
                        <li><a href="/about" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">Sobre</a></li>
                        <li><a href="https://github.com/dougkusanagi/micro-framework-php" target="_blank" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors font-medium flex items-center">
                            <span class="material-icons text-sm mr-1">code</span>
                            GitHub
                        </a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="min-h-screen">
        <?= $content ?? '' ?>
    </main>

    <footer class="bg-gray-900 dark:bg-black text-white text-center py-8">
        <div class="max-w-6xl mx-auto px-5">
            <p>&copy; <?= date('Y') ?> GuepardoSys - Framework PHP Leve</p>
        </div>
    </footer>

    <script>
        // Simple dark mode detection
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>

</html>
