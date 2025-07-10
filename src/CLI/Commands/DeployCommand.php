<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Deploy Command for Production Deployment
 */
class DeployCommand extends BaseCommand
{
    /**
     * Execute the deploy command
     */
    public function execute(array $args): void
    {
        $this->info('Starting deployment process...');

        // Parse arguments
        $target = $args[0] ?? 'local';
        $options = $this->parseOptions($args);

        switch ($target) {
            case 'local':
                $this->deployLocal($options);
                break;
            case 'ftp':
                $this->deployFtp($options);
                break;
            case 'rsync':
                $this->deployRsync($options);
                break;
            default:
                $this->error("Unknown deployment target: {$target}");
                $this->showUsage();
                return;
        }

        $this->success('Deployment completed successfully!');
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return 'Deploy application to production environment';
    }

    /**
     * Deploy to local directory
     */
    private function deployLocal(array $options): void
    {
        $destination = $options['path'] ?? '/var/www/html';
        $buildDir = getcwd() . '/build';

        if (!is_dir($buildDir)) {
            $this->error('Build directory not found. Run "guepardo build" first.');
            return;
        }

        $this->info("Deploying to local directory: {$destination}");

        // Backup existing deployment if exists
        if (is_dir($destination) && !empty($options['backup'])) {
            $this->createBackup($destination);
        }

        // Copy build to destination
        $this->copyDirectory($buildDir, $destination);

        // Set permissions
        $this->setPermissions($destination);

        // Run post-deploy tasks
        $this->runPostDeployTasks($destination);

        $this->success("Local deployment completed to: {$destination}");
    }

    /**
     * Deploy via FTP
     */
    private function deployFtp(array $options): void
    {
        $this->info('Starting FTP deployment...');

        $ftpConfig = [
            'host' => $options['host'] ?? $this->prompt('FTP Host: '),
            'username' => $options['username'] ?? $this->prompt('FTP Username: '),
            'password' => $options['password'] ?? $this->promptPassword('FTP Password: '),
            'path' => $options['remote-path'] ?? '/',
            'port' => (int)($options['port'] ?? 21),
            'passive' => !empty($options['passive']),
        ];

        $buildDir = getcwd() . '/build';
        if (!is_dir($buildDir)) {
            $this->error('Build directory not found. Run "guepardo build" first.');
            return;
        }

        $this->uploadViaFtp($buildDir, $ftpConfig);
        $this->success('FTP deployment completed');
    }

    /**
     * Deploy via Rsync
     */
    private function deployRsync(array $options): void
    {
        $this->info('Starting Rsync deployment...');

        $destination = $options['destination'] ?? $this->prompt('Rsync destination (user@host:/path): ');
        $buildDir = getcwd() . '/build';

        if (!is_dir($buildDir)) {
            $this->error('Build directory not found. Run "guepardo build" first.');
            return;
        }

        $excludeFile = $this->createRsyncExcludeFile();

        $rsyncCommand = sprintf(
            'rsync -avz --delete --exclude-from=%s %s/ %s',
            escapeshellarg($excludeFile),
            escapeshellarg($buildDir),
            escapeshellarg($destination)
        );

        $this->info("Running: {$rsyncCommand}");

        exec($rsyncCommand, $output, $returnCode);

        unlink($excludeFile);

        if ($returnCode === 0) {
            $this->success('Rsync deployment completed');
        } else {
            $this->error('Rsync deployment failed');
            foreach ($output as $line) {
                $this->info($line);
            }
        }
    }

    /**
     * Create backup of existing deployment
     */
    private function createBackup(string $path): void
    {
        $backupPath = $path . '_backup_' . date('Y-m-d_H-i-s');
        $this->info("Creating backup: {$backupPath}");

        $this->copyDirectory($path, $backupPath);
        $this->success('Backup created');
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

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
     * Set proper permissions for deployment
     */
    private function setPermissions(string $path): void
    {
        $this->info('Setting permissions...');

        // Set directory permissions
        $directories = [
            $path . '/storage',
            $path . '/storage/cache',
            $path . '/storage/logs',
            $path . '/public/assets',
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                chmod($dir, 0755);
                $this->makeDirectoryWritable($dir);
            }
        }

        // Set file permissions
        $files = [
            $path . '/.env',
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                chmod($file, 0644);
            }
        }

        $this->success('Permissions set');
    }

    /**
     * Make directory writable recursively
     */
    private function makeDirectoryWritable(string $dir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
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
     * Upload files via FTP
     */
    private function uploadViaFtp(string $localDir, array $config): void
    {
        if (!extension_loaded('ftp')) {
            $this->error('FTP extension not available');
            return;
        }

        $connection = ftp_connect($config['host'], $config['port']);
        if (!$connection) {
            $this->error('Could not connect to FTP server');
            return;
        }

        if (!ftp_login($connection, $config['username'], $config['password'])) {
            $this->error('FTP login failed');
            ftp_close($connection);
            return;
        }

        if ($config['passive']) {
            ftp_pasv($connection, true);
        }

        // Change to remote directory
        if (!ftp_chdir($connection, $config['path'])) {
            $this->error("Could not change to remote directory: {$config['path']}");
            ftp_close($connection);
            return;
        }

        $this->uploadDirectoryFtp($connection, $localDir, '');
        ftp_close($connection);
    }

    /**
     * Upload directory via FTP recursively
     */
    private function uploadDirectoryFtp($connection, string $localDir, string $remotePath): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($localDir . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $remoteFile = $remotePath . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($item->isDir()) {
                // Create directory if it doesn't exist
                if (!@ftp_chdir($connection, $remoteFile)) {
                    ftp_mkdir($connection, $remoteFile);
                    $this->info("Created directory: {$remoteFile}");
                }
                ftp_chdir($connection, '/');
                ftp_chdir($connection, $remotePath ?: '/');
            } else {
                // Upload file
                if (ftp_put($connection, $remoteFile, $item->getPathname(), FTP_BINARY)) {
                    $this->info("Uploaded: {$remoteFile}");
                } else {
                    $this->warning("Failed to upload: {$remoteFile}");
                }
            }
        }
    }

    /**
     * Create rsync exclude file
     */
    private function createRsyncExcludeFile(): string
    {
        $excludeFile = sys_get_temp_dir() . '/guepardo_rsync_exclude';
        $excludePatterns = [
            '.env',
            'storage/logs/*',
            'storage/cache/*',
            'node_modules/',
            '.git/',
            '*.log',
            'temp_*/',
        ];

        file_put_contents($excludeFile, implode("\n", $excludePatterns));
        return $excludeFile;
    }

    /**
     * Run post-deployment tasks
     */
    private function runPostDeployTasks(string $deployPath): void
    {
        $this->info('Running post-deployment tasks...');

        // Clear caches
        $cacheDirs = [
            $deployPath . '/storage/cache/views',
            $deployPath . '/storage/cache/test',
        ];

        foreach ($cacheDirs as $cacheDir) {
            if (is_dir($cacheDir)) {
                $this->clearDirectory($cacheDir);
            }
        }

        // Create necessary directories
        $requiredDirs = [
            $deployPath . '/storage/logs',
            $deployPath . '/storage/cache',
            $deployPath . '/storage/cache/views',
        ];

        foreach ($requiredDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $this->success('Post-deployment tasks completed');
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
     * Parse command options
     */
    private function parseOptions(array $args): array
    {
        $options = [];

        for ($i = 1; $i < count($args); $i++) {
            $arg = $args[$i];

            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = $parts[1] ?? true;
                $options[$key] = $value;
            }
        }

        return $options;
    }

    /**
     * Prompt for password (hidden input)
     */
    private function promptPassword(string $message): string
    {
        echo $message;

        // Hide input on Unix systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $password = shell_exec('read -s -p "" password && echo $password');
            echo "\n";
            return trim($password);
        }

        // Fallback for Windows or if shell_exec is disabled
        return trim(fgets(STDIN));
    }

    /**
     * Prompt for user input
     */
    private function prompt(string $message): string
    {
        echo $message;
        return trim(fgets(STDIN));
    }

    /**
     * Show command usage
     */
    private function showUsage(): void
    {
        $this->info('Usage:');
        $this->info('  guepardo deploy [target] [options]');
        $this->info('');
        $this->info('Targets:');
        $this->info('  local   Deploy to local directory');
        $this->info('  ftp     Deploy via FTP');
        $this->info('  rsync   Deploy via Rsync');
        $this->info('');
        $this->info('Options:');
        $this->info('  --path=PATH            Local deployment path');
        $this->info('  --host=HOST            FTP/SSH host');
        $this->info('  --username=USER        FTP/SSH username');
        $this->info('  --password=PASS        FTP password');
        $this->info('  --port=PORT            FTP/SSH port');
        $this->info('  --remote-path=PATH     Remote path');
        $this->info('  --destination=DEST     Rsync destination');
        $this->info('  --backup               Create backup before deploy');
        $this->info('  --passive              Use passive FTP mode');
    }
}
