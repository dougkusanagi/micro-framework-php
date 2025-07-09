<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\CLI\Console;

class AssetCommand
{
    public function __construct(private Console $console) {}

    public function build()
    {
        $this->console->info('🎨 Building assets for production...');

        // Build CSS
        $this->console->info('📦 Compiling CSS...');
        $output = shell_exec('cd ' . getcwd() . ' && bun run build:css 2>&1');

        if (strpos($output, 'Done') !== false) {
            $this->console->success('✅ CSS compiled successfully');
        } else {
            $this->console->error('❌ CSS compilation failed');
            $this->console->error($output);
            return;
        }

        // Build JS (placeholder for future implementation)
        $this->console->info('📦 Processing JavaScript...');
        $output = shell_exec('cd ' . getcwd() . ' && bun run build:js 2>&1');
        $this->console->success('✅ JavaScript processed');

        // Optimize assets
        $this->console->info('⚡ Optimizing assets...');
        $this->optimizeAssets();

        $this->console->success('🎉 Assets built successfully!');
    }

    public function dev()
    {
        $this->console->info('🔥 Starting development asset watcher...');
        $this->console->info('Press Ctrl+C to stop');

        // Start Tailwind CSS watcher
        $command = 'cd ' . getcwd() . ' && bun run dev:css';
        passthru($command);
    }

    public function clean()
    {
        $this->console->info('🧹 Cleaning compiled assets...');

        $cssFile = getcwd() . '/public/assets/css/output.css';
        if (file_exists($cssFile)) {
            unlink($cssFile);
            $this->console->success('✅ CSS output file removed');
        }

        $this->console->success('🎉 Assets cleaned successfully!');
    }

    private function optimizeAssets()
    {
        $publicPath = getcwd() . '/public/assets';

        // Optimize CSS
        $cssFile = $publicPath . '/css/output.css';
        if (file_exists($cssFile)) {
            $content = file_get_contents($cssFile);

            // Remove comments
            $content = preg_replace('/\/\*.*?\*\//s', '', $content);

            // Remove extra whitespace
            $content = preg_replace('/\s+/', ' ', $content);
            $content = str_replace('; ', ';', $content);
            $content = str_replace(': ', ':', $content);
            $content = str_replace('{ ', '{', $content);
            $content = str_replace(' }', '}', $content);

            file_put_contents($cssFile, trim($content));
            $this->console->success('✅ CSS optimized');
        }

        // Generate asset manifest for versioning
        $this->generateAssetManifest();
    }

    private function generateAssetManifest()
    {
        $publicPath = getcwd() . '/public/assets';
        $manifest = [];

        // Generate hashes for CSS files
        $cssFiles = glob($publicPath . '/css/*.css');
        foreach ($cssFiles as $file) {
            $relativePath = str_replace($publicPath . '/', '', $file);
            $hash = substr(md5_file($file), 0, 8);
            $manifest[$relativePath] = $relativePath . '?v=' . $hash;
        }

        // Generate hashes for JS files
        $jsFiles = glob($publicPath . '/js/*.js');
        foreach ($jsFiles as $file) {
            $relativePath = str_replace($publicPath . '/', '', $file);
            $hash = substr(md5_file($file), 0, 8);
            $manifest[$relativePath] = $relativePath . '?v=' . $hash;
        }

        // Save manifest
        $manifestFile = $publicPath . '/manifest.json';
        file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT));

        $this->console->success('✅ Asset manifest generated');
    }
}
