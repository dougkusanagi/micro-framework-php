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
        <h1 class="text-5xl md:text-7xl font-black mb-6 tracking-tight">Blog GuepardoSys</h1>
        <p class="text-xl md:text-2xl max-w-3xl mx-auto text-gray-200">
            Artigos, tutoriais e novidades sobre o framework PHP mais rápido e elegante
        </p>
    </div>
</section>

<!-- Blog Posts Section -->
<section class="py-20 md:py-32 bg-white dark:bg-slate-800">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Featured Post -->
            <article class="lg:col-span-2 bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="p-8">
                    <div class="flex items-center mb-4">
                        <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm font-medium">Destaque</span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-4">15 de Janeiro, 2025</span>
                    </div>
                    <h2 class="text-2xl font-bold mb-4 dark:text-white">Introdução ao GuepardoSys: O Framework PHP que Você Estava Esperando</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                        Descubra como o GuepardoSys está revolucionando o desenvolvimento PHP com sua abordagem minimalista, 
                        performance excepcional e sintaxe elegante. Neste artigo, exploramos os fundamentos do framework e 
                        como ele pode acelerar seu desenvolvimento.
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                <span class="material-icons text-white text-sm">person</span>
                            </div>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Equipe GuepardoSys</span>
                        </div>
                        <a href="#" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium">
                            Ler mais →
                        </a>
                    </div>
                </div>
            </article>

            <!-- Regular Posts -->
            <article class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-3">
                        <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full text-xs font-medium">Tutorial</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-3">10 de Janeiro, 2025</span>
                    </div>
                    <h3 class="text-lg font-bold mb-3 dark:text-white">Criando sua Primeira API com GuepardoSys</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Aprenda a construir APIs RESTful rapidamente usando o sistema de rotas e controllers do GuepardoSys.
                    </p>
                    <a href="#" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                        Ler mais →
                    </a>
                </div>
            </article>

            <article class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-3">
                        <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded-full text-xs font-medium">Performance</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-3">8 de Janeiro, 2025</span>
                    </div>
                    <h3 class="text-lg font-bold mb-3 dark:text-white">Otimizando Performance com Cache Inteligente</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Explore o sistema de cache avançado do GuepardoSys e como ele pode melhorar drasticamente a performance.
                    </p>
                    <a href="#" class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 text-sm font-medium">
                        Ler mais →
                    </a>
                </div>
            </article>

            <article class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-3">
                        <span class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-1 rounded-full text-xs font-medium">Dicas</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-3">5 de Janeiro, 2025</span>
                    </div>
                    <h3 class="text-lg font-bold mb-3 dark:text-white">Melhores Práticas para Estrutura de Projetos</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Organize seu projeto GuepardoSys seguindo as melhores práticas de arquitetura e organização de código.
                    </p>
                    <a href="#" class="text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 text-sm font-medium">
                        Ler mais →
                    </a>
                </div>
            </article>

            <article class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-3">
                        <span class="bg-pink-100 dark:bg-pink-900 text-pink-800 dark:text-pink-200 px-2 py-1 rounded-full text-xs font-medium">Novidades</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-3">3 de Janeiro, 2025</span>
                    </div>
                    <h3 class="text-lg font-bold mb-3 dark:text-white">Novos Recursos na Versão 2.0</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Conheça as novas funcionalidades e melhorias que chegaram com a versão 2.0 do GuepardoSys.
                    </p>
                    <a href="#" class="text-pink-600 dark:text-pink-400 hover:text-pink-700 dark:hover:text-pink-300 text-sm font-medium">
                        Ler mais →
                    </a>
                </div>
            </article>

            <article class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-3">
                        <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-2 py-1 rounded-full text-xs font-medium">Comunidade</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-3">1 de Janeiro, 2025</span>
                    </div>
                    <h3 class="text-lg font-bold mb-3 dark:text-white">Contribuindo para o GuepardoSys</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Saiba como você pode contribuir para o desenvolvimento do framework e fazer parte da comunidade.
                    </p>
                    <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-medium">
                        Ler mais →
                    </a>
                </div>
            </article>

        </div>

        <!-- Newsletter Section -->
        <div class="mt-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 md:p-12 text-white">
            <div class="text-center max-w-2xl mx-auto">
                <h3 class="text-3xl font-bold mb-4">Fique por Dentro</h3>
                <p class="text-lg mb-8 text-purple-100">
                    Receba as últimas novidades, tutoriais e dicas sobre o GuepardoSys diretamente no seu email.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                    <input type="email" placeholder="Seu email" class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                    <button class="bg-white text-purple-600 font-bold px-6 py-3 rounded-lg hover:bg-gray-100 transition-colors">
                        Inscrever-se
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include APP_PATH . '/Views/layouts/main.php';
?> 