<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Command to optimize application for production
 */
class OptimizeCommand extends BaseCommand
{
    public function execute(array $args): void
    {
        $this->info('Optimizing application for production...');

        // Clear all caches first
        $this->clearCaches();

        // Optimize composer autoloader
        $this->optimizeAutoloader();

        // Precompile views
        $this->precompileViews();

        // Generate configuration cache
        $this->cacheConfiguration();

        $this->success('Application optimized for production!');
    }

    public function getDescription(): string
    {
        return 'Optimize application for production (cache, autoloader, views)';
    }

    private function clearCaches(): void
    {
        $this->info('Clearing caches...');

        $cacheCommand = new CacheClearCommand();
        $cacheCommand->execute([]);
    }

    private function optimizeAutoloader(): void
    {
        $this->info('Optimizing Composer autoloader...');

        $commands = [
            'composer install --no-dev --optimize-autoloader --no-scripts',
            'composer dump-autoload --optimize --no-dev'
        ];

        foreach ($commands as $command) {
            $output = [];
            $return = 0;
            exec($command . ' 2>&1', $output, $return);

            if ($return !== 0) {
                $this->warning("Warning: Command failed: {$command}");
                echo implode(PHP_EOL, $output) . PHP_EOL;
            }
        }
    }

    private function precompileViews(): void
    {
        $this->info('Precompiling views...');

        $viewsPath = APP_PATH . '/Views';
        if (!is_dir($viewsPath)) {
            return;
        }

        $viewEngine = new \GuepardoSys\Core\View\View();
        $compiled = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($viewsPath . '/', '', $file->getPathname());
                $viewName = str_replace(['/', '.php'], ['.', ''], $relativePath);

                try {
                    $viewEngine->render($viewName, []);
                    $compiled++;
                } catch (\Exception $e) {
                    // Skip views that can't be compiled without data
                    continue;
                }
            }
        }

        echo "- Compiled {$compiled} view templates" . PHP_EOL;
    }

    private function cacheConfiguration(): void
    {
        $this->info('Caching configuration...');

        $configPath = BASE_PATH . '/config';
        $cachePath = STORAGE_PATH . '/cache/config.php';

        if (!is_dir($configPath)) {
            return;
        }

        $config = [];
        $files = glob($configPath . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }

        $cacheContent = '<?php return ' . var_export($config, true) . ';';
        file_put_contents($cachePath, $cacheContent);

        echo "- Configuration cached to {$cachePath}" . PHP_EOL;
    }
}
