<?php
// Capture content for layout
ob_start();
?>

<!-- Hero Section -->
<section class="gradient-bg dark:dark-gradient-bg py-20 md:py-32 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-white/10 rounded-full opacity-50"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-white/10 rounded-full opacity-50"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-5xl md:text-7xl font-black mb-6 tracking-tight">Sobre o GuepardoSys</h1>
        <p class="text-xl md:text-2xl max-w-3xl mx-auto text-gray-200">
            Um framework PHP micro e leve projetado para desenvolvedores que valorizam simplicidade, velocidade e código elegante.
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="py-20 md:py-32 bg-white dark:bg-slate-800">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Mission -->
            <div class="bg-slate-50 dark:bg-slate-900 p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 dark:text-white flex items-center">
                    <span class="mr-3">🎯</span>
                    Nossa Missão
                </h2>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                    O GuepardoSys foi criado para preencher a lacuna entre frameworks full-stack complexos e scripts PHP básicos.
                    Nosso foco é fornecer exatamente a quantidade certa de estrutura e funcionalidade necessária para aplicações web
                    pequenas e médias rodando em ambientes de hospedagem compartilhada.
                </p>
            </div>

            <!-- Why Choose -->
            <div class="bg-slate-50 dark:bg-slate-900 p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 dark:text-white flex items-center">
                    <span class="mr-3">⚡</span>
                    Por que Escolher o GuepardoSys?
                </h2>
                <ul class="space-y-3 text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <strong class="text-gray-900 dark:text-white">Contagem Mínima de Arquivos:</strong> Menos de 200 arquivos para o framework completo
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <strong class="text-gray-900 dark:text-white">Performance Rápida:</strong> TTFB abaixo de 50ms em ambientes otimizados
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <strong class="text-gray-900 dark:text-white">Amigável à Hospedagem Compartilhada:</strong> Funciona perfeitamente em planos limitados
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <strong class="text-gray-900 dark:text-white">Fácil de Aprender:</strong> Arquitetura simples e limpa que é fácil de entender
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <strong class="text-gray-900 dark:text-white">PHP Moderno:</strong> Construído para PHP 8.0+ com práticas modernas
                    </li>
                </ul>
            </div>
        </div>

        <!-- Technical Stack -->
        <div class="mt-12 bg-slate-50 dark:bg-slate-900 p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold mb-8 dark:text-white flex items-center">
                <span class="mr-3">🛠</span>
                Stack Técnica
            </h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h4 class="text-xl font-bold mb-4 dark:text-white">Backend</h4>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                        <li>PHP 8.0+</li>
                        <li>PSR-4 Autoloading</li>
                        <li>PDO Database</li>
                        <li>Custom Router</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4 dark:text-white">Frontend</h4>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                        <li>Tailwind CSS</li>
                        <li>Alpine.js</li>
                        <li>Bun Package Manager</li>
                        <li>CDN Optimized</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4 dark:text-white">Desenvolvimento</h4>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                        <li>PestPHP Testing</li>
                        <li>PHPStan Analysis</li>
                        <li>CLI Tools</li>
                        <li>Hot Reload</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <?php if (isset($specs) && is_array($specs)): ?>
        <div class="mt-12 grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($specs as $spec): ?>
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold mb-2 dark:text-white"><?= htmlspecialchars($spec['title']) ?></h3>
                <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($spec['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Performance Stats -->
        <?php if (isset($phpVersion) || isset($frameworkVersion) || isset($loadTime)): ?>
        <div class="mt-12 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white">
            <h3 class="text-2xl font-bold mb-6">Estatísticas do Sistema</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <?php if (isset($phpVersion)): ?>
                <div class="text-center">
                    <div class="text-3xl font-bold mb-2">PHP</div>
                    <div class="text-lg"><?= htmlspecialchars($phpVersion) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($frameworkVersion)): ?>
                <div class="text-center">
                    <div class="text-3xl font-bold mb-2">Framework</div>
                    <div class="text-lg"><?= htmlspecialchars($frameworkVersion) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($loadTime)): ?>
                <div class="text-center">
                    <div class="text-3xl font-bold mb-2">Tempo de Carregamento</div>
                    <div class="text-lg"><?= number_format($loadTime * 1000, 2) ?>ms</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include APP_PATH . '/Views/layouts/main.php';
?>
