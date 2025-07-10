<?php

/**
 * Standalone Deploy Script
 * 
 * This script can be run independently without the CLI framework
 * for simple deployments or server maintenance.
 */

class SimpleDeployer
{
    private string $projectRoot;
    private array $config;

    public function __construct(string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?: dirname(__DIR__);
        $this->loadConfig();
    }

    /**
     * Load deployment configuration
     */
    private function loadConfig(): void
    {
        $configFile = $this->projectRoot . '/scripts/deploy-config.php';

        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = $this->getDefaultConfig();
        }
    }

    /**
     * Get default deployment configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'build_dir' => $this->projectRoot . '/build',
            'backup_dir' => $this->projectRoot . '/backups',
            'exclude_patterns' => [
                '/\.git/',
                '/node_modules/',
                '/tests/',
                '/docs/',
                '/build/',
                '/temp_*/',
                '/\.env\.example$/',
                '/composer\.lock$/',
                '/storage\/logs\//',
                '/storage\/cache\/test\//',
            ],
            'writable_dirs' => [
                'storage/cache',
                'storage/logs',
                'public/assets',
            ],
            'post_deploy_commands' => [
                'clear_cache',
                'set_permissions',
                'warmup_cache',
            ],
        ];
    }

    /**
     * Deploy to local directory
     */
    public function deployLocal(string $destination, array $options = []): bool
    {
        $this->log('Starting local deployment...');

        try {
            // Validate build directory
            if (!is_dir($this->config['build_dir'])) {
                throw new Exception('Build directory not found. Run build first.');
            }

            // Create backup if requested
            if ($options['backup'] ?? false) {
                $this->createBackup($destination);
            }

            // Deploy files
            $this->deployFiles($destination);

            // Set permissions
            $this->setPermissions($destination);

            // Run post-deploy tasks
            $this->runPostDeployTasks($destination);

            $this->log('Local deployment completed successfully!');
            return true;
        } catch (Exception $e) {
            $this->log('Deployment failed: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Deploy via FTP
     */
    public function deployFtp(array $ftpConfig): bool
    {
        $this->log('Starting FTP deployment...');

        if (!extension_loaded('ftp')) {
            $this->log('FTP extension not available', 'ERROR');
            return false;
        }

        try {
            $connection = ftp_connect($ftpConfig['host'], $ftpConfig['port'] ?? 21);
            if (!$connection) {
                throw new Exception('Could not connect to FTP server');
            }

            if (!ftp_login($connection, $ftpConfig['username'], $ftpConfig['password'])) {
                throw new Exception('FTP login failed');
            }

            if ($ftpConfig['passive'] ?? false) {
                ftp_pasv($connection, true);
            }

            $this->uploadDirectoryFtp($connection, $this->config['build_dir'], $ftpConfig['remote_path'] ?? '/');

            ftp_close($connection);

            $this->log('FTP deployment completed successfully!');
            return true;
        } catch (Exception $e) {
            if (isset($connection)) {
                ftp_close($connection);
            }
            $this->log('FTP deployment failed: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Create backup of existing deployment
     */
    private function createBackup(string $deployPath): void
    {
        if (!is_dir($deployPath)) {
            return;
        }

        $backupDir = $this->config['backup_dir'];
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupPath = $backupDir . '/backup_' . date('Y-m-d_H-i-s');
        $this->log("Creating backup: {$backupPath}");

        $this->copyDirectory($deployPath, $backupPath);

        // Keep only last 5 backups
        $this->cleanupOldBackups($backupDir, 5);
    }

    /**
     * Deploy files from build directory
     */
    private function deployFiles(string $destination): void
    {
        $this->log('Deploying files...');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $this->copyDirectory($this->config['build_dir'], $destination);
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory(string $source, string $dest): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * Upload directory via FTP
     */
    private function uploadDirectoryFtp($connection, string $localDir, string $remotePath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($localDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($localDir . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $remoteFile = rtrim($remotePath, '/') . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($item->isDir()) {
                if (!@ftp_chdir($connection, $remoteFile)) {
                    ftp_mkdir($connection, $remoteFile);
                    $this->log("Created directory: {$remoteFile}");
                }
                // Reset to base path
                ftp_chdir($connection, $remotePath);
            } else {
                if (ftp_put($connection, $remoteFile, $item->getPathname(), FTP_BINARY)) {
                    $this->log("Uploaded: {$remoteFile}");
                } else {
                    $this->log("Failed to upload: {$remoteFile}", 'WARNING');
                }
            }
        }
    }

    /**
     * Set proper permissions
     */
    private function setPermissions(string $deployPath): void
    {
        $this->log('Setting permissions...');

        foreach ($this->config['writable_dirs'] as $dir) {
            $fullPath = $deployPath . '/' . $dir;
            if (is_dir($fullPath)) {
                chmod($fullPath, 0755);
                $this->makeDirectoryWritable($fullPath);
            }
        }
    }

    /**
     * Make directory writable recursively
     */
    private function makeDirectoryWritable(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                chmod($item->getPathname(), 0755);
            } else {
                chmod($item->getPathname(), 0644);
            }
        }
    }

    /**
     * Run post-deployment tasks
     */
    private function runPostDeployTasks(string $deployPath): void
    {
        $this->log('Running post-deployment tasks...');

        foreach ($this->config['post_deploy_commands'] as $task) {
            switch ($task) {
                case 'clear_cache':
                    $this->clearCache($deployPath);
                    break;
                case 'set_permissions':
                    $this->setPermissions($deployPath);
                    break;
                case 'warmup_cache':
                    $this->warmupCache($deployPath);
                    break;
            }
        }
    }

    /**
     * Clear application caches
     */
    private function clearCache(string $deployPath): void
    {
        $cacheDirs = [
            $deployPath . '/storage/cache/views',
            $deployPath . '/storage/cache/test',
        ];

        foreach ($cacheDirs as $cacheDir) {
            if (is_dir($cacheDir)) {
                $this->clearDirectory($cacheDir);
            }
        }
    }

    /**
     * Warmup application cache
     */
    private function warmupCache(string $deployPath): void
    {
        // Create necessary cache directories
        $requiredDirs = [
            $deployPath . '/storage/cache',
            $deployPath . '/storage/cache/views',
            $deployPath . '/storage/logs',
        ];

        foreach ($requiredDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Clear directory contents
     */
    private function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Clean up old backups
     */
    private function cleanupOldBackups(string $backupDir, int $keepCount): void
    {
        $backups = glob($backupDir . '/backup_*');

        if (count($backups) <= $keepCount) {
            return;
        }

        // Sort by modification time (oldest first)
        usort($backups, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Remove oldest backups
        $toRemove = array_slice($backups, 0, count($backups) - $keepCount);

        foreach ($toRemove as $backup) {
            $this->removeDirectory($backup);
            $this->log("Removed old backup: " . basename($backup));
        }
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

    /**
     * Log message with timestamp
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    }
}

// Example usage if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $deployer = new SimpleDeployer();

    // Example: Local deployment
    if ($argc > 1 && $argv[1] === 'local') {
        $destination = $argv[2] ?? '/var/www/html';
        $options = ['backup' => true];
        $deployer->deployLocal($destination, $options);
    }

    // Example: FTP deployment
    elseif ($argc > 1 && $argv[1] === 'ftp') {
        $ftpConfig = [
            'host' => $argv[2] ?? 'ftp.example.com',
            'username' => $argv[3] ?? 'username',
            'password' => $argv[4] ?? 'password',
            'remote_path' => $argv[5] ?? '/public_html',
            'passive' => true,
        ];
        $deployer->deployFtp($ftpConfig);
    } else {
        echo "Usage:\n";
        echo "  php deploy.php local [destination_path]\n";
        echo "  php deploy.php ftp [host] [username] [password] [remote_path]\n";
    }
}
