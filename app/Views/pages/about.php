<?php
// Capture content for layout
ob_start();
?>

<div class="hero">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($description) ?></p>
</div>

<div style="margin-top: 3rem;">
    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2>🎯 Our Mission</h2>
        <p>
            GuepardoSys was created to fill the gap between complex full-stack frameworks and basic PHP scripts.
            We focus on providing just the right amount of structure and functionality needed for small to
            medium-sized web applications running on shared hosting environments.
        </p>
    </div>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2>⚡ Why Choose GuepardoSys?</h2>
        <ul style="margin: 1rem 0; padding-left: 2rem; line-height: 1.8;">
            <li><strong>Minimal File Count:</strong> Under 200 files for the complete framework</li>
            <li><strong>Fast Performance:</strong> TTFB under 50ms in optimized environments</li>
            <li><strong>Shared Hosting Friendly:</strong> Works perfectly on limited hosting plans</li>
            <li><strong>Easy to Learn:</strong> Simple, clean architecture that's easy to understand</li>
            <li><strong>Modern PHP:</strong> Built for PHP 8.0+ with modern practices</li>
        </ul>
    </div>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2>🛠 Technical Stack</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div>
                <h4>Backend</h4>
                <ul style="padding-left: 1rem; margin-top: 0.5rem;">
                    <li>PHP 8.0+</li>
                    <li>PSR-4 Autoloading</li>
                    <li>PDO Database</li>
                    <li>Custom Router</li>
                </ul>
            </div>
            <div>
                <h4>Frontend</h4>
                <ul style="padding-left: 1rem; margin-top: 0.5rem;">
                    <li>Tailwind CSS</li>
                    <li>Alpine.js</li>
                    <li>Bun Package Manager</li>
                    <li>CDN Optimized</li>
                </ul>
            </div>
            <div>
                <h4>Development</h4>
                <ul style="padding-left: 1rem; margin-top: 0.5rem;">
                    <li>PestPHP Testing</li>
                    <li>PHPStan Analysis</li>
                    <li>CLI Tools</li>
                    <li>Hot Reload</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include APP_PATH . '/Views/layouts/main.php';
?>
