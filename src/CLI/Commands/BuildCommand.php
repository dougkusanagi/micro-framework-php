<?php

namespace GuepardoSys\CLI\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Build Command for Production Optimization
 */
class BuildCommand extends BaseCommand
{
    /**
     * Execute the build command
     */
    public function execute(array $args): void
    {
        $this->info('Starting production build...');

        // Create build directory
        $buildDir = getcwd() . '/build';
        $this->createBuildDirectory($buildDir);

        // Copy project files excluding development files
        $this->copyProjectFiles($buildDir);

        // Optimize autoload
        $this->optimizeAutoload($buildDir);

        // Clear and optimize caches
        $this->optimizeCaches($buildDir);

        // Remove development files
        $this->removeDevelopmentFiles($buildDir);

        // Generate production config
        $this->generateProductionConfig($buildDir);

        $this->success('Production build completed successfully!');
        $this->info("Build directory: {$buildDir}");
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return 'Build optimized version for production deployment';
    }

    /**
     * Create build directory
     */
    private function createBuildDirectory(string $buildDir): void
    {
        if (is_dir($buildDir)) {
            $this->info('Cleaning existing build directory...');
            $this->removeDirectory($buildDir);
        }

        mkdir($buildDir, 0755, true);
        $this->success('Build directory created');
    }

    /**
     * Copy project files excluding development files
     */
    private function copyProjectFiles(string $buildDir): void
    {
        $this->info('Copying project files...');

        $sourceDir = getcwd();
        $excludePatterns = [
            '/\.git/',
            '/node_modules/',
            '/tests/',
            '/docs/',
            '/build/',
            '/temp_cache/',
            '/temp_test/',
            '/temp_views/',
            '/\.env\.example$/',
            '/phpunit\.xml$/',
            '/phpstan\.neon$/',
            '/phpcs\.xml$/',
            '/package\.json$/',
            '/package-lock\.json$/',
            '/bun\.lock$/',
            '/tailwind\.config\.js$/',
            '/\.gitignore$/',
            '/\.htaccess\.dev$/',
            '/composer\.lock$/',
            '/storage\/logs\//',
            '/storage\/cache\/test\//',
            '/storage\/cache\/phpstan\//',
            '/storage\/cache\/phpunit\//',
        ];

        $this->copyDirectoryExcluding($sourceDir, $buildDir, $excludePatterns);
        $this->success('Project files copied');
    }

    /**
     * Copy directory excluding patterns
     */
    private function copyDirectoryExcluding(string $source, string $dest, array $excludePatterns): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($source, '', $item->getPathname());

            // Check if file should be excluded
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (preg_match($pattern, $relativePath)) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            $destPath = $dest . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * Optimize autoload for production
     */
    private function optimizeAutoload(string $buildDir): void
    {
        $this->info('Optimizing autoload...');

        $composerPath = $buildDir . '/composer.json';
        if (file_exists($composerPath)) {
            // Run composer install with optimizations
            $commands = [
                "cd {$buildDir}",
                "composer install --no-dev --optimize-autoloader --no-interaction"
            ];

            $command = implode(' && ', $commands);
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->success('Autoload optimized');
            } else {
                $this->warning('Autoload optimization failed, continuing...');
            }
        }
    }

    /**
     * Optimize caches for production
     */
    private function optimizeCaches(string $buildDir): void
    {
        $this->info('Optimizing caches...');

        // Clear all cache directories
        $cacheDirectories = [
            $buildDir . '/storage/cache/views',
            $buildDir . '/storage/cache/test',
            $buildDir . '/storage/cache/phpstan',
            $buildDir . '/storage/cache/phpunit',
            $buildDir . '/temp_cache',
            $buildDir . '/temp_test',
            $buildDir . '/temp_views',
        ];

        foreach ($cacheDirectories as $cacheDir) {
            if (is_dir($cacheDir)) {
                $this->removeDirectory($cacheDir);
            }
        }

        // Recreate essential cache directories
        $essentialDirs = [
            $buildDir . '/storage/cache',
            $buildDir . '/storage/cache/views',
            $buildDir . '/storage/logs',
        ];

        foreach ($essentialDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $this->success('Caches optimized');
    }

    /**
     * Remove development files from build
     */
    private function removeDevelopmentFiles(string $buildDir): void
    {
        $this->info('Removing development files...');

        $devFiles = [
            $buildDir . '/phpunit.xml',
            $buildDir . '/phpstan.neon',
            $buildDir . '/phpcs.xml',
            $buildDir . '/package.json',
            $buildDir . '/package-lock.json',
            $buildDir . '/bun.lock',
            $buildDir . '/tailwind.config.js',
            $buildDir . '/.env.example',
            $buildDir . '/.gitignore',
            $buildDir . '/.htaccess.dev',
        ];

        foreach ($devFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->success('Development files removed');
    }

    /**
     * Generate production configuration
     */
    private function generateProductionConfig(string $buildDir): void
    {
        $this->info('Generating production configuration...');

        // Create production .htaccess if needed
        $htaccessPath = $buildDir . '/public/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Angular and Vue.js routes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable server signature
ServerTokens Prod
ServerSignature Off

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<FilesMatch "\.(log|sql|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
HTACCESS;

            file_put_contents($htaccessPath, $htaccessContent);
        }

        // Create production deployment info
        $deployInfo = [
            'built_at' => date('Y-m-d H:i:s'),
            'version' => $this->getVersionFromComposer($buildDir),
            'environment' => 'production',
            'optimizations' => [
                'autoload_optimized' => true,
                'caches_cleared' => true,
                'dev_files_removed' => true,
            ]
        ];

        file_put_contents(
            $buildDir . '/build-info.json',
            json_encode($deployInfo, JSON_PRETTY_PRINT)
        );

        $this->success('Production configuration generated');
    }

    /**
     * Get version from composer.json
     */
    private function getVersionFromComposer(string $buildDir): string
    {
        $composerPath = $buildDir . '/composer.json';
        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            return $composer['version'] ?? '1.0.0';
        }
        return '1.0.0';
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
