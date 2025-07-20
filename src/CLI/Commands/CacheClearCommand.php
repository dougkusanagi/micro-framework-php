<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Cache\CacheFacade;

/**
 * Command to manage cache operations
 */
class CacheClearCommand extends BaseCommand
{
    public function execute(array $args): void
    {
        $this->info('Clearing application cache...');

        $cleared = 0;

        // Clear data cache
        CacheFacade::flush();
        echo '- Data cache: cleared' . PHP_EOL;

        // Clear view cache
        $viewCacheCleared = $this->clearViewCache();
        echo "- View cache: {$viewCacheCleared} files cleared" . PHP_EOL;

        // Clear temp directories (used by tests)
        $tempCacheCleared = $this->clearTempDirectories();
        echo "- Temp directories: {$tempCacheCleared} files cleared" . PHP_EOL;

        // Clean expired cache entries
        $expiredCleaned = 0; // TODO: Implement cleanExpired in CacheFacade
        echo "- Expired entries: {$expiredCleaned} cleaned" . PHP_EOL;

        $totalCleared = $cleared + $viewCacheCleared + $tempCacheCleared;
        $this->success("Cache cleared successfully! Total files: {$totalCleared}");
    }

    /**
     * Clear view cache
     */
    private function clearViewCache(): int
    {
        $cleared = 0;
        $cacheDirs = [
            STORAGE_PATH . '/cache/views',
            STORAGE_PATH . '/cache',
        ];

        foreach ($cacheDirs as $cacheDir) {
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $cleared++;
                    }
                }
            }
        }

        return $cleared;
    }

    /**
     * Clear temp directories used by tests
     */
    private function clearTempDirectories(): int
    {
        $cleared = 0;
        $tempDirs = [
            __DIR__ . '/../../../temp_cache',
            __DIR__ . '/../../../temp_views',
        ];

        foreach ($tempDirs as $tempDir) {
            if (is_dir($tempDir)) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $cleared++;
                    } elseif (is_dir($file)) {
                        $this->removeDirectory($file);
                        $cleared++;
                    }
                }
            }
        }

        return $cleared;
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->removeDirectory($path);
                } else {
                    unlink($path);
                }
            }
            rmdir($dir);
        }
    }

    public function getDescription(): string
    {
        return 'Clear application cache (views, data, and temp directories)';
    }
}
