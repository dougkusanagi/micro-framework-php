<?php
// Capture content for layout
ob_start();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-violet-600 to-pink-500 min-h-screen flex items-center justify-center text-white relative overflow-hidden -mx-5 -mt-8">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-white/10 rounded-full opacity-50"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-white/10 rounded-full opacity-50"></div>
    <div class="text-center p-8 z-10">
        <div class="mb-6 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded-lg inline-block">
            <strong>✅ Framework Status:</strong> <?= htmlspecialchars($message) ?>
        </div>
        <h2 class="text-5xl md:text-7xl font-black mb-4 tracking-tight">Build Fast. Code Less.</h2>
        <h3 class="text-5xl md:text-7xl font-black mb-6 tracking-tight text-pink-300">Deploy Instantly.</h3>
        <p class="text-lg md:text-xl max-w-2xl mx-auto mb-4 text-gray-200">
            <?= htmlspecialchars($title ?? 'GuepardoSys Framework') ?> - Version <?= htmlspecialchars($version) ?>
        </p>
        <p class="text-lg md:text-xl max-w-2xl mx-auto mb-10 text-gray-200">
            A lightweight PHP micro framework designed for developers who value simplicity, speed, and elegant code.
        </p>
        <div class="flex justify-center space-x-4">
            <a class="bg-white text-gray-900 font-bold py-3 px-8 rounded-full hover:bg-gray-200 transition-transform transform hover:scale-105 shadow-lg" href="#quick-start">Get Started</a>
            <a class="bg-white/20 text-white font-bold py-3 px-8 rounded-full hover:bg-white/30 transition-transform transform hover:scale-105 shadow-lg" href="/about">Learn More</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 md:py-32 bg-slate-50 -mx-5">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-16">Why GuepardoSys?</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php 
            $featureIcons = ['inventory_2', 'speed', 'code', 'rocket_launch'];
            $featureTitles = ['Zero Dependencies', 'Blazing Fast', 'Developer Experience', 'Production Ready'];
            $featureColors = ['text-purple-500', 'text-pink-500', 'text-blue-500', 'text-green-500'];
            $featureDescriptions = [
                'Standalone and ready to go. No complex installations or dependency hell.',
                'Engineered for performance, perfect for high-traffic applications.',
                'Clean syntax and comprehensive documentation make development a joy.',
                'Enterprise-grade reliability in a small, efficient package.'
            ];
            
            foreach ($features as $index => $feature): 
                $iconIndex = $index % count($featureIcons);
            ?>
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2">
                    <div class="<?= $featureColors[$iconIndex] ?> mb-4">
                        <span class="material-icons text-4xl"><?= $featureIcons[$iconIndex] ?></span>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?= $featureTitles[$iconIndex] ?></h3>
                    <p class="text-gray-600"><?= htmlspecialchars($feature) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Quick Start Section -->
<section class="py-20 md:py-32 bg-white -mx-5" id="quick-start">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center">
            <h2 class="text-4xl font-bold mb-4">🚀 Quick Start Guide</h2>
            <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">Get your first GuepardoSys application up and running in minutes.</p>
        </div>
        <div class="bg-gray-900 rounded-xl shadow-2xl overflow-hidden max-w-4xl mx-auto">
            <div class="p-4 bg-gray-800 flex items-center space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            </div>
            <div class="p-8">
                <pre><code class="language-bash text-gray-300">
<span class="text-green-400">$</span> composer create-project guepardosys/guepardosys my-app
<span class="text-green-400">$</span> cd my-app
<span class="text-green-400">$</span> php -S localhost:8000 -t public
                </code></pre>
                <div class="mt-8 pt-6 border-t border-gray-700">
                    <h4 class="text-lg font-semibold text-white mb-4">What's next?</h4>
                    <ul class="list-disc list-inside text-gray-400 space-y-2">
                        <li>Create new controllers in <code class="bg-gray-700 text-pink-400 px-2 py-1 rounded">app/Controllers/</code></li>
                        <li>Add new routes in <code class="bg-gray-700 text-pink-400 px-2 py-1 rounded">routes/web.php</code></li>
                        <li>Create views in <code class="bg-gray-700 text-pink-400 px-2 py-1 rounded">app/Views/</code></li>
                        <li>Add models in <code class="bg-gray-700 text-pink-400 px-2 py-1 rounded">app/Models/</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section -->
<section class="py-20 md:py-32 bg-slate-50 -mx-5">
    <div class="max-w-6xl mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-4">Join the Community</h2>
        <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">Connect with other developers, ask questions, and contribute to the framework.</p>
        <div class="flex justify-center items-center space-x-6">
            <a class="text-gray-500 hover:text-purple-500 transition-colors" href="#">
                <span class="material-icons text-5xl">forum</span>
                <span class="block mt-2 text-lg font-semibold">Forums</span>
            </a>
            <a class="text-gray-500 hover:text-blue-500 transition-colors" href="#">
                <span class="material-icons text-5xl">description</span>
                <span class="block mt-2 text-lg font-semibold">Documentation</span>
            </a>
            <a class="text-gray-500 hover:text-gray-700 transition-colors" href="#">
                <span class="material-icons text-5xl">code</span>
                <span class="block mt-2 text-lg font-semibold">GitHub</span>
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include APP_PATH . '/Views/layouts/main.php';
?>
