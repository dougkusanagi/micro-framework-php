<?php

/**
 * Standalone Health Check Script
 * 
 * This script can be used to monitor application health
 * independently of the CLI framework.
 */

class HealthChecker
{
    private string $projectRoot;
    private array $config;
    private array $results = [];

    public function __construct(string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?: dirname(__DIR__);
        $this->loadConfig();
    }

    /**
     * Load health check configuration
     */
    private function loadConfig(): void
    {
        $configFile = $this->projectRoot . '/scripts/health-config.php';

        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = $this->getDefaultConfig();
        }
    }

    /**
     * Get default health check configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'checks' => [
                'system' => true,
                'database' => true,
                'cache' => true,
                'storage' => true,
                'security' => true,
                'performance' => true,
            ],
            'thresholds' => [
                'critical_score' => 60,
                'warning_score' => 80,
                'memory_limit_mb' => 128,
                'disk_space_gb' => 1,
                'response_time_ms' => 1000,
            ],
            'output_format' => 'console', // console, json, html
            'log_results' => true,
        ];
    }

    /**
     * Run all health checks
     */
    public function runAllChecks(): array
    {
        $this->results = [];

        if ($this->config['checks']['system']) {
            $this->results['system'] = $this->checkSystem();
        }

        if ($this->config['checks']['database']) {
            $this->results['database'] = $this->checkDatabase();
        }

        if ($this->config['checks']['cache']) {
            $this->results['cache'] = $this->checkCache();
        }

        if ($this->config['checks']['storage']) {
            $this->results['storage'] = $this->checkStorage();
        }

        if ($this->config['checks']['security']) {
            $this->results['security'] = $this->checkSecurity();
        }

        if ($this->config['checks']['performance']) {
            $this->results['performance'] = $this->checkPerformance();
        }

        if ($this->config['log_results']) {
            $this->logResults();
        }

        return $this->results;
    }

    /**
     * Check system requirements
     */
    private function checkSystem(): array
    {
        $checks = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $checks['php_version'] = [
            'name' => 'PHP Version',
            'status' => version_compare($phpVersion, '8.1.0', '>='),
            'value' => $phpVersion,
            'expected' => '8.1+',
            'critical' => true,
        ];

        // Memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->parseMemoryLimit($memoryLimit);
        $minMemory = $this->config['thresholds']['memory_limit_mb'] * 1024 * 1024;

        $checks['memory_limit'] = [
            'name' => 'Memory Limit',
            'status' => $memoryBytes >= $minMemory,
            'value' => $memoryLimit,
            'expected' => $this->config['thresholds']['memory_limit_mb'] . 'M+',
            'critical' => false,
        ];

        // Required extensions
        $requiredExtensions = ['pdo', 'mbstring', 'json', 'openssl', 'hash'];
        foreach ($requiredExtensions as $ext) {
            $checks["extension_{$ext}"] = [
                'name' => "Extension: {$ext}",
                'status' => extension_loaded($ext),
                'value' => extension_loaded($ext) ? 'loaded' : 'missing',
                'expected' => 'loaded',
                'critical' => true,
            ];
        }

        // Disk space
        $diskSpace = disk_free_space($this->projectRoot);
        $diskSpaceGB = $diskSpace / (1024 * 1024 * 1024);
        $minDiskSpace = $this->config['thresholds']['disk_space_gb'];

        $checks['disk_space'] = [
            'name' => 'Free Disk Space',
            'status' => $diskSpaceGB >= $minDiskSpace,
            'value' => round($diskSpaceGB, 2) . ' GB',
            'expected' => $minDiskSpace . ' GB+',
            'critical' => false,
        ];

        return $checks;
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        $checks = [];

        try {
            $configFile = $this->projectRoot . '/config/database.php';
            if (!file_exists($configFile)) {
                $checks['config'] = [
                    'name' => 'Database Config',
                    'status' => false,
                    'value' => 'missing',
                    'expected' => 'present',
                    'critical' => true,
                ];
                return $checks;
            }

            $config = require $configFile;
            $defaultConnection = $config['default'] ?? 'mysql';
            $dbConfig = $config['connections'][$defaultConnection] ?? null;

            if (!$dbConfig) {
                $checks['config'] = [
                    'name' => 'Database Config',
                    'status' => false,
                    'value' => 'invalid',
                    'expected' => 'valid',
                    'critical' => true,
                ];
                return $checks;
            }

            // Test connection
            $startTime = microtime(true);
            $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $connectionTime = (microtime(true) - $startTime) * 1000;

            $checks['connection'] = [
                'name' => 'Database Connection',
                'status' => true,
                'value' => 'connected',
                'expected' => 'connected',
                'critical' => true,
                'response_time' => round($connectionTime, 2) . 'ms',
            ];

            // Test query
            $stmt = $pdo->query('SELECT 1');
            $checks['query'] = [
                'name' => 'Database Query',
                'status' => $stmt !== false,
                'value' => $stmt !== false ? 'working' : 'failed',
                'expected' => 'working',
                'critical' => true,
            ];
        } catch (Exception $e) {
            $checks['connection'] = [
                'name' => 'Database Connection',
                'status' => false,
                'value' => 'failed',
                'expected' => 'connected',
                'critical' => true,
                'error' => $e->getMessage(),
            ];
        }

        return $checks;
    }

    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        $checks = [];

        $cacheDir = $this->projectRoot . '/storage/cache';

        // Cache directory accessibility
        $checks['directory'] = [
            'name' => 'Cache Directory',
            'status' => is_dir($cacheDir) && is_writable($cacheDir),
            'value' => (is_dir($cacheDir) && is_writable($cacheDir)) ? 'accessible' : 'not accessible',
            'expected' => 'accessible',
            'critical' => false,
        ];

        // Test cache write/read
        try {
            $testFile = $cacheDir . '/health_check_' . time();
            $testContent = 'health_check_test';

            file_put_contents($testFile, $testContent);
            $readContent = file_get_contents($testFile);
            unlink($testFile);

            $checks['write_read'] = [
                'name' => 'Cache Write/Read',
                'status' => $readContent === $testContent,
                'value' => $readContent === $testContent ? 'working' : 'failed',
                'expected' => 'working',
                'critical' => false,
            ];
        } catch (Exception $e) {
            $checks['write_read'] = [
                'name' => 'Cache Write/Read',
                'status' => false,
                'value' => 'failed',
                'expected' => 'working',
                'critical' => false,
                'error' => $e->getMessage(),
            ];
        }

        return $checks;
    }

    /**
     * Check storage permissions
     */
    private function checkStorage(): array
    {
        $checks = [];

        $storageDirs = [
            'storage/cache' => true,
            'storage/logs' => true,
            'public/assets' => false,
        ];

        foreach ($storageDirs as $dir => $critical) {
            $fullPath = $this->projectRoot . '/' . $dir;
            $writable = is_dir($fullPath) && is_writable($fullPath);

            $checks[str_replace('/', '_', $dir)] = [
                'name' => ucwords(str_replace('/', ' ', $dir)) . ' Directory',
                'status' => $writable,
                'value' => $writable ? 'writable' : 'not writable',
                'expected' => 'writable',
                'critical' => $critical,
            ];
        }

        return $checks;
    }

    /**
     * Check security settings
     */
    private function checkSecurity(): array
    {
        $checks = [];

        // Check for exposed sensitive files
        $sensitiveFiles = ['.env', 'composer.json', 'phpunit.xml'];
        $publicDir = $this->projectRoot . '/public';
        $exposedFiles = [];

        foreach ($sensitiveFiles as $file) {
            if (file_exists($publicDir . '/' . $file)) {
                $exposedFiles[] = $file;
            }
        }

        $checks['sensitive_files'] = [
            'name' => 'Sensitive Files Exposure',
            'status' => empty($exposedFiles),
            'value' => empty($exposedFiles) ? 'protected' : 'exposed: ' . implode(', ', $exposedFiles),
            'expected' => 'protected',
            'critical' => !empty($exposedFiles),
        ];

        // Check error reporting in production
        $errorReporting = ini_get('display_errors');
        $checks['error_display'] = [
            'name' => 'Error Display',
            'status' => !$errorReporting,
            'value' => $errorReporting ? 'enabled' : 'disabled',
            'expected' => 'disabled',
            'critical' => false,
        ];

        return $checks;
    }

    /**
     * Check performance metrics
     */
    private function checkPerformance(): array
    {
        $checks = [];

        // Measure application bootstrap time
        $startTime = microtime(true);

        // Simulate basic application initialization
        if (file_exists($this->projectRoot . '/bootstrap/app.php')) {
            require_once $this->projectRoot . '/bootstrap/app.php';
        }

        $bootstrapTime = (microtime(true) - $startTime) * 1000;
        $maxResponseTime = $this->config['thresholds']['response_time_ms'];

        $checks['bootstrap_time'] = [
            'name' => 'Bootstrap Time',
            'status' => $bootstrapTime <= $maxResponseTime,
            'value' => round($bootstrapTime, 2) . 'ms',
            'expected' => '<= ' . $maxResponseTime . 'ms',
            'critical' => false,
        ];

        // Check opcode cache
        $opcacheEnabled = function_exists('opcache_get_status') && opcache_get_status() !== false;
        $checks['opcache'] = [
            'name' => 'OPcache',
            'status' => $opcacheEnabled,
            'value' => $opcacheEnabled ? 'enabled' : 'disabled',
            'expected' => 'enabled',
            'critical' => false,
        ];

        return $checks;
    }

    /**
     * Calculate overall health score
     */
    public function getHealthScore(): int
    {
        if (empty($this->results)) {
            return 0;
        }

        $totalChecks = 0;
        $passedChecks = 0;
        $criticalFailures = 0;

        foreach ($this->results as $category => $checks) {
            foreach ($checks as $check) {
                $totalChecks++;
                if ($check['status']) {
                    $passedChecks++;
                } elseif ($check['critical']) {
                    $criticalFailures++;
                }
            }
        }

        if ($totalChecks === 0) {
            return 0;
        }

        $baseScore = ($passedChecks / $totalChecks) * 100;
        $penalty = $criticalFailures * 15;

        return max(0, (int)($baseScore - $penalty));
    }

    /**
     * Get health status text
     */
    public function getHealthStatus(): string
    {
        $score = $this->getHealthScore();

        if ($score >= $this->config['thresholds']['warning_score']) {
            return 'Healthy';
        } elseif ($score >= $this->config['thresholds']['critical_score']) {
            return 'Warning';
        } else {
            return 'Critical';
        }
    }

    /**
     * Output results based on configured format
     */
    public function outputResults(): void
    {
        switch ($this->config['output_format']) {
            case 'json':
                $this->outputJson();
                break;
            case 'html':
                $this->outputHtml();
                break;
            default:
                $this->outputConsole();
                break;
        }
    }

    /**
     * Output results in console format
     */
    private function outputConsole(): void
    {
        echo "Health Check Results\n";
        echo "===================\n\n";

        foreach ($this->results as $category => $checks) {
            echo ucfirst($category) . ":\n";

            foreach ($checks as $check) {
                $symbol = $check['status'] ? '✓' : ($check['critical'] ? '✗' : '⚠');
                $status = $check['status'] ? 'PASS' : 'FAIL';

                echo "  {$symbol} {$check['name']}: {$status}";

                if (isset($check['value']) && isset($check['expected'])) {
                    echo " ({$check['value']}, expected: {$check['expected']})";
                }

                if (isset($check['error'])) {
                    echo " - Error: {$check['error']}";
                }

                echo "\n";
            }
            echo "\n";
        }

        $score = $this->getHealthScore();
        $status = $this->getHealthStatus();

        echo "Overall Health: {$score}% - {$status}\n";
    }

    /**
     * Output results in JSON format
     */
    private function outputJson(): void
    {
        $output = [
            'timestamp' => date('c'),
            'score' => $this->getHealthScore(),
            'status' => $this->getHealthStatus(),
            'checks' => $this->results,
        ];

        echo json_encode($output, JSON_PRETTY_PRINT);
    }

    /**
     * Output results in HTML format
     */
    private function outputHtml(): void
    {
        $score = $this->getHealthScore();
        $status = $this->getHealthStatus();
        $statusClass = strtolower($status);

        echo "<!DOCTYPE html>\n";
        echo "<html><head><title>Health Check</title>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .health-{$statusClass} { color: " . ($status === 'Healthy' ? 'green' : ($status === 'Warning' ? 'orange' : 'red')) . "; }
            .check-pass { color: green; }
            .check-fail { color: red; }
            .check-warning { color: orange; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style></head><body>\n";

        echo "<h1>Health Check Results</h1>\n";
        echo "<p class='health-{$statusClass}'>Overall Health: {$score}% - {$status}</p>\n";

        foreach ($this->results as $category => $checks) {
            echo "<h2>" . ucfirst($category) . "</h2>\n";
            echo "<table>\n";
            echo "<tr><th>Check</th><th>Status</th><th>Value</th><th>Expected</th></tr>\n";

            foreach ($checks as $check) {
                $statusClass = $check['status'] ? 'pass' : ($check['critical'] ? 'fail' : 'warning');
                $statusText = $check['status'] ? 'PASS' : 'FAIL';

                echo "<tr>";
                echo "<td>{$check['name']}</td>";
                echo "<td class='check-{$statusClass}'>{$statusText}</td>";
                echo "<td>" . ($check['value'] ?? '-') . "</td>";
                echo "<td>" . ($check['expected'] ?? '-') . "</td>";
                echo "</tr>\n";
            }

            echo "</table>\n";
        }

        echo "</body></html>\n";
    }

    /**
     * Log health check results
     */
    private function logResults(): void
    {
        $logDir = $this->projectRoot . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/health-check-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $score = $this->getHealthScore();
        $status = $this->getHealthStatus();

        $logEntry = "[{$timestamp}] Health Check - Score: {$score}%, Status: {$status}\n";

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Set output format
     */
    public function setOutputFormat(string $format): void
    {
        $this->config['output_format'] = $format;
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

// Example usage if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $healthChecker = new HealthChecker();

    // Parse command line arguments
    $format = 'console';
    if ($argc > 1) {
        $format = $argv[1];
    }

    // Set output format
    $healthChecker->setOutputFormat($format);

    // Run checks and output results
    $healthChecker->runAllChecks();
    $healthChecker->outputResults();

    // Exit with appropriate code
    $score = $healthChecker->getHealthScore();
    exit($score >= 80 ? 0 : 1);
}
