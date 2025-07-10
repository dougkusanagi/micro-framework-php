<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Health Check Command for Monitoring
 */
class HealthCommand extends BaseCommand
{
    /**
     * Execute the health check command
     */
    public function execute(array $args): void
    {
        $this->info('Running health checks...');

        $checks = [
            'System Requirements' => $this->checkSystemRequirements(),
            'File Permissions' => $this->checkFilePermissions(),
            'Database Connection' => $this->checkDatabaseConnection(),
            'Cache System' => $this->checkCacheSystem(),
            'Log System' => $this->checkLogSystem(),
            'Security Headers' => $this->checkSecurityHeaders(),
            'Environment Configuration' => $this->checkEnvironmentConfig(),
        ];

        $this->displayResults($checks);

        $overallHealth = $this->calculateOverallHealth($checks);

        if ($overallHealth >= 90) {
            $this->success("Overall Health: {$overallHealth}% - Excellent");
        } elseif ($overallHealth >= 70) {
            $this->warning("Overall Health: {$overallHealth}% - Good");
        } else {
            $this->error("Overall Health: {$overallHealth}% - Needs Attention");
        }
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return 'Run health checks for the application';
    }

    /**
     * Check system requirements
     */
    private function checkSystemRequirements(): array
    {
        $checks = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $checks['PHP Version'] = [
            'status' => version_compare($phpVersion, '8.1.0', '>='),
            'message' => "PHP {$phpVersion} " . (version_compare($phpVersion, '8.1.0', '>=') ? '✓' : '✗ (8.1+ required)'),
            'critical' => true,
        ];

        // Required extensions
        $requiredExtensions = ['pdo', 'mbstring', 'json', 'openssl', 'hash'];
        foreach ($requiredExtensions as $ext) {
            $checks["Extension: {$ext}"] = [
                'status' => extension_loaded($ext),
                'message' => extension_loaded($ext) ? "✓ {$ext} loaded" : "✗ {$ext} missing",
                'critical' => true,
            ];
        }

        // Memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryOk = $this->parseMemoryLimit($memoryLimit) >= 128 * 1024 * 1024; // 128MB
        $checks['Memory Limit'] = [
            'status' => $memoryOk,
            'message' => $memoryOk ? "✓ {$memoryLimit}" : "✗ {$memoryLimit} (128M+ recommended)",
            'critical' => false,
        ];

        return $checks;
    }

    /**
     * Check file permissions
     */
    private function checkFilePermissions(): array
    {
        $checks = [];

        $writableDirs = [
            'storage/',
            'storage/cache/',
            'storage/logs/',
            'public/assets/',
        ];

        foreach ($writableDirs as $dir) {
            $fullPath = getcwd() . '/' . $dir;
            $writable = is_dir($fullPath) && is_writable($fullPath);

            $checks["Writable: {$dir}"] = [
                'status' => $writable,
                'message' => $writable ? "✓ {$dir} writable" : "✗ {$dir} not writable",
                'critical' => true,
            ];
        }

        // Check .env file
        $envPath = getcwd() . '/.env';
        $envExists = file_exists($envPath);
        $checks['.env file'] = [
            'status' => $envExists,
            'message' => $envExists ? '✓ .env exists' : '✗ .env missing',
            'critical' => true,
        ];

        return $checks;
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): array
    {
        $checks = [];

        try {
            require_once getcwd() . '/config/database.php';

            $config = require getcwd() . '/config/database.php';
            $defaultConnection = $config['default'] ?? 'mysql';
            $dbConfig = $config['connections'][$defaultConnection] ?? null;

            if (!$dbConfig) {
                $checks['Database Config'] = [
                    'status' => false,
                    'message' => '✗ Database configuration missing',
                    'critical' => true,
                ];
                return $checks;
            }

            // Test connection
            $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            $pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password']);

            $checks['Database Connection'] = [
                'status' => true,
                'message' => "✓ Connected to {$dbConfig['driver']}://{$dbConfig['host']}:{$dbConfig['port']}/{$dbConfig['database']}",
                'critical' => true,
            ];

            // Test basic query
            $stmt = $pdo->query('SELECT 1');
            $checks['Database Query'] = [
                'status' => $stmt !== false,
                'message' => $stmt !== false ? '✓ Database queries working' : '✗ Database queries failing',
                'critical' => true,
            ];
        } catch (\Exception $e) {
            $checks['Database Connection'] = [
                'status' => false,
                'message' => "✗ Database connection failed: " . $e->getMessage(),
                'critical' => true,
            ];
        }

        return $checks;
    }

    /**
     * Check cache system
     */
    private function checkCacheSystem(): array
    {
        $checks = [];

        $cacheDir = getcwd() . '/storage/cache';
        $viewsCacheDir = $cacheDir . '/views';

        // Cache directory exists and writable
        $checks['Cache Directory'] = [
            'status' => is_dir($cacheDir) && is_writable($cacheDir),
            'message' => (is_dir($cacheDir) && is_writable($cacheDir))
                ? '✓ Cache directory accessible'
                : '✗ Cache directory not accessible',
            'critical' => false,
        ];

        // Views cache
        $checks['Views Cache'] = [
            'status' => is_dir($viewsCacheDir) && is_writable($viewsCacheDir),
            'message' => (is_dir($viewsCacheDir) && is_writable($viewsCacheDir))
                ? '✓ Views cache directory accessible'
                : '✗ Views cache directory not accessible',
            'critical' => false,
        ];

        // Test cache write/read
        try {
            $testFile = $cacheDir . '/health_check_' . time();
            file_put_contents($testFile, 'test');
            $content = file_get_contents($testFile);
            unlink($testFile);

            $checks['Cache Write/Read'] = [
                'status' => $content === 'test',
                'message' => $content === 'test' ? '✓ Cache write/read working' : '✗ Cache write/read failed',
                'critical' => false,
            ];
        } catch (\Exception $e) {
            $checks['Cache Write/Read'] = [
                'status' => false,
                'message' => '✗ Cache write/read failed: ' . $e->getMessage(),
                'critical' => false,
            ];
        }

        return $checks;
    }

    /**
     * Check log system
     */
    private function checkLogSystem(): array
    {
        $checks = [];

        $logsDir = getcwd() . '/storage/logs';

        // Logs directory
        $checks['Logs Directory'] = [
            'status' => is_dir($logsDir) && is_writable($logsDir),
            'message' => (is_dir($logsDir) && is_writable($logsDir))
                ? '✓ Logs directory accessible'
                : '✗ Logs directory not accessible',
            'critical' => false,
        ];

        // Test log write
        try {
            $testLogFile = $logsDir . '/health_check_' . date('Y-m-d') . '.log';
            $testContent = '[' . date('Y-m-d H:i:s') . '] HEALTH_CHECK: Test log entry' . PHP_EOL;
            file_put_contents($testLogFile, $testContent, FILE_APPEND | LOCK_EX);

            $checks['Log Write'] = [
                'status' => file_exists($testLogFile),
                'message' => file_exists($testLogFile) ? '✓ Log writing working' : '✗ Log writing failed',
                'critical' => false,
            ];
        } catch (\Exception $e) {
            $checks['Log Write'] = [
                'status' => false,
                'message' => '✗ Log writing failed: ' . $e->getMessage(),
                'critical' => false,
            ];
        }

        return $checks;
    }

    /**
     * Check security headers (if running via web)
     */
    private function checkSecurityHeaders(): array
    {
        $checks = [];

        // Check if .htaccess exists for Apache
        $htaccessPath = getcwd() . '/public/.htaccess';
        $checks['.htaccess Security'] = [
            'status' => file_exists($htaccessPath),
            'message' => file_exists($htaccessPath)
                ? '✓ .htaccess security file exists'
                : '⚠ .htaccess security file missing (recommended for Apache)',
            'critical' => false,
        ];

        // Check for sensitive files exposure
        $sensitiveFiles = [
            '.env',
            'composer.json',
            'composer.lock',
            'phpunit.xml',
        ];

        $publicDir = getcwd() . '/public';
        $exposedFiles = [];

        foreach ($sensitiveFiles as $file) {
            if (file_exists($publicDir . '/' . $file)) {
                $exposedFiles[] = $file;
            }
        }

        $checks['Sensitive Files'] = [
            'status' => empty($exposedFiles),
            'message' => empty($exposedFiles)
                ? '✓ No sensitive files in public directory'
                : '✗ Sensitive files exposed: ' . implode(', ', $exposedFiles),
            'critical' => !empty($exposedFiles),
        ];

        return $checks;
    }

    /**
     * Check environment configuration
     */
    private function checkEnvironmentConfig(): array
    {
        $checks = [];

        $envPath = getcwd() . '/.env';

        if (!file_exists($envPath)) {
            $checks['Environment File'] = [
                'status' => false,
                'message' => '✗ .env file missing',
                'critical' => true,
            ];
            return $checks;
        }

        // Load .env
        $envContent = file_get_contents($envPath);
        $envVars = [];

        foreach (explode("\n", $envContent) as $line) {
            $line = trim($line);
            if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value, '"\'');
            }
        }

        // Check required environment variables
        $requiredVars = [
            'APP_ENV' => 'Application environment',
            'DB_HOST' => 'Database host',
            'DB_DATABASE' => 'Database name',
            'DB_USERNAME' => 'Database username',
        ];

        foreach ($requiredVars as $var => $description) {
            $checks[$description] = [
                'status' => !empty($envVars[$var]),
                'message' => !empty($envVars[$var])
                    ? "✓ {$var} configured"
                    : "✗ {$var} missing or empty",
                'critical' => in_array($var, ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME']),
            ];
        }

        // Check if in production mode
        $appEnv = $envVars['APP_ENV'] ?? 'production';
        $checks['Environment Mode'] = [
            'status' => true,
            'message' => "ℹ Running in {$appEnv} mode",
            'critical' => false,
        ];

        return $checks;
    }

    /**
     * Display health check results
     */
    private function displayResults(array $checkGroups): void
    {
        foreach ($checkGroups as $groupName => $checks) {
            $this->info("\n{$groupName}:");

            foreach ($checks as $checkName => $result) {
                $symbol = $result['status'] ? '✓' : ($result['critical'] ? '✗' : '⚠');
                $color = $result['status'] ? 'success' : ($result['critical'] ? 'error' : 'warning');

                $this->{$color}("  {$symbol} {$result['message']}");
            }
        }
    }

    /**
     * Calculate overall health percentage
     */
    private function calculateOverallHealth(array $checkGroups): int
    {
        $totalChecks = 0;
        $passedChecks = 0;
        $criticalFailures = 0;

        foreach ($checkGroups as $checks) {
            foreach ($checks as $result) {
                $totalChecks++;
                if ($result['status']) {
                    $passedChecks++;
                } elseif ($result['critical']) {
                    $criticalFailures++;
                }
            }
        }

        if ($totalChecks === 0) {
            return 0;
        }

        $baseScore = ($passedChecks / $totalChecks) * 100;

        // Penalize critical failures more heavily
        $penalty = $criticalFailures * 15;

        return max(0, (int)($baseScore - $penalty));
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
