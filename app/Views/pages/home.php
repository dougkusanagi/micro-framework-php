<?php
// Capture content for layout
ob_start();
?>

<div class="status">
    <strong>✅ Framework Status:</strong> <?= htmlspecialchars($message) ?>
</div>

<div class="hero">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p>Version <?= htmlspecialchars($version) ?> - Built for shared hosting environments</p>
    <a href="/about" class="btn">Learn More</a>
</div>

<div class="features">
    <?php foreach ($features as $feature): ?>
        <div class="feature">
            <h3>✨ Feature</h3>
            <p><?= htmlspecialchars($feature) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div style="margin-top: 3rem; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h2>🚀 Quick Start</h2>
    <p>Your GuepardoSys framework is up and running! Here's what you can do next:</p>
    <ul style="margin: 1rem 0; padding-left: 2rem;">
        <li>Create new controllers in <code>app/Controllers/</code></li>
        <li>Add new routes in <code>routes/web.php</code></li>
        <li>Create views in <code>app/Views/</code></li>
        <li>Add models in <code>app/Models/</code></li>
    </ul>
</div>

<?php
$content = ob_get_clean();
include APP_PATH . '/Views/layouts/main.php';
?>
