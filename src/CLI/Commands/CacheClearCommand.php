<?php

namespace GuepardoSys\CLI\Commands;

use GuepardoSys\Core\Cache;
use GuepardoSys\Core\View\View;

/**
 * Command to manage cache operations
 */
class CacheClearCommand extends BaseCommand
{
    public function execute(array $args): void
    {
        $this->info('Clearing application cache...');

        $cleared = 0;

        // Clear view cache
        $viewCache = new View();
        $viewCleared = $viewCache->clearCache();
        $cleared += $viewCleared;
        echo "- View cache: {$viewCleared} files cleared" . PHP_EOL;

        // Clear data cache
        $dataCache = new Cache();
        $dataCache->flush();
        echo '- Data cache: cleared' . PHP_EOL;

        // Clean expired cache entries
        $expiredCleaned = $dataCache->cleanExpired();
        echo "- Expired entries: {$expiredCleaned} cleaned" . PHP_EOL;

        $this->success("Cache cleared successfully! Total files: {$cleared}");
    }

    public function getDescription(): string
    {
        return 'Clear application cache (views and data)';
    }
}
