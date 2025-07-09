<?php

namespace GuepardoSys\Core;

/**
 * Logger implementation for GuepardoSys
 * Supports multiple log levels, rotation, and structured logging
 */
class Logger
{
    private string $logPath;
    private string $logLevel;
    private array $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];

    public function __construct(string $logPath = null, string $logLevel = 'info')
    {
        $this->logPath = $logPath ?? STORAGE_PATH . '/logs';
        $this->logLevel = strtolower($logLevel);

        // Create logs directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log message with specified level
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $level = strtolower($level);

        // Check if this level should be logged
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = $this->formatLogEntry($timestamp, $level, $message, $context);

        $logFile = $this->getLogFile();
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Check if log rotation is needed
        $this->rotateIfNeeded($logFile);
    }

    /**
     * Check if a level should be logged
     */
    private function shouldLog(string $level): bool
    {
        if (!isset($this->levels[$level])) {
            return false;
        }

        return $this->levels[$level] >= $this->levels[$this->logLevel];
    }

    /**
     * Format log entry
     */
    private function formatLogEntry(string $timestamp, string $level, string $message, array $context): string
    {
        $levelUpper = strtoupper($level);
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);

        return "[{$timestamp}] {$levelUpper}: {$message}{$contextStr}";
    }

    /**
     * Get current log file path
     */
    private function getLogFile(): string
    {
        $date = date('Y-m-d');
        return $this->logPath . "/app-{$date}.log";
    }

    /**
     * Rotate log file if it's too large
     */
    private function rotateIfNeeded(string $logFile): void
    {
        if (!file_exists($logFile)) {
            return;
        }

        $maxSize = 10 * 1024 * 1024; // 10MB

        if (filesize($logFile) > $maxSize) {
            $rotatedFile = $logFile . '.' . time();
            rename($logFile, $rotatedFile);

            // Compress old log
            if (function_exists('gzopen')) {
                $this->compressLog($rotatedFile);
            }
        }
    }

    /**
     * Compress log file
     */
    private function compressLog(string $logFile): void
    {
        $gzFile = $logFile . '.gz';
        $fp = fopen($logFile, 'rb');
        $gz = gzopen($gzFile, 'wb9');

        if ($fp && $gz) {
            while (!feof($fp)) {
                gzwrite($gz, fread($fp, 1024));
            }
            fclose($fp);
            gzclose($gz);
            unlink($logFile);
        }
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $daysToKeep = 30): int
    {
        $files = glob($this->logPath . '/app-*.log*');
        $cleaned = 0;
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Get log statistics
     */
    public function getStats(): array
    {
        $files = glob($this->logPath . '/app-*.log*');
        $totalSize = 0;
        $totalFiles = count($files);

        foreach ($files as $file) {
            $totalSize += filesize($file);
        }

        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'log_path' => $this->logPath,
            'current_level' => $this->logLevel
        ];
    }

    /**
     * Set log level
     */
    public function setLevel(string $level): void
    {
        $level = strtolower($level);
        if (isset($this->levels[$level])) {
            $this->logLevel = $level;
        }
    }
}
