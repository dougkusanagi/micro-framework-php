<?php

use GuepardoSys\Core\Logger;

describe('Logger', function () {
    beforeEach(function () {
        $this->logDir = __DIR__ . '/../../storage/logs/test';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        $this->logger = new Logger($this->logDir);
    });

    afterEach(function () {
        // Clean up test log files
        if (is_dir($this->logDir)) {
            $files = glob($this->logDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    });

    it('can log debug messages', function () {
        $message = 'Debug test message';
        $this->logger->debug($message);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        expect(file_exists($logFile))->toBeTrue();

        $content = file_get_contents($logFile);
        expect($content)->toContain('[DEBUG]');
        expect($content)->toContain($message);
    });

    it('can log info messages', function () {
        $message = 'Info test message';
        $this->logger->info($message);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('[INFO]');
        expect($content)->toContain($message);
    });

    it('can log warning messages', function () {
        $message = 'Warning test message';
        $this->logger->warning($message);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('[WARNING]');
        expect($content)->toContain($message);
    });

    it('can log error messages', function () {
        $message = 'Error test message';
        $this->logger->error($message);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('[ERROR]');
        expect($content)->toContain($message);
    });

    it('can log critical messages', function () {
        $message = 'Critical test message';
        $this->logger->critical($message);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('[CRITICAL]');
        expect($content)->toContain($message);
    });

    it('includes timestamp in log entries', function () {
        $this->logger->info('Timestamp test');

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        // Check for timestamp pattern (YYYY-MM-DD HH:MM:SS)
        expect($content)->toMatch('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/');
    });

    it('can log with context data', function () {
        $message = 'Context test';
        $context = ['user_id' => 123, 'action' => 'login'];

        $this->logger->info($message, $context);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain($message);
        expect($content)->toContain('user_id');
        expect($content)->toContain('123');
        expect($content)->toContain('action');
        expect($content)->toContain('login');
    });

    it('creates log files with proper naming', function () {
        $this->logger->info('File naming test');

        $expectedFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        expect(file_exists($expectedFile))->toBeTrue();
    });

    it('appends to existing log files', function () {
        $this->logger->info('First message');
        $this->logger->info('Second message');

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('First message');
        expect($content)->toContain('Second message');
        expect(substr_count($content, '[INFO]'))->toBe(2);
    });

    it('handles different log levels', function () {
        $this->logger->debug('Debug level');
        $this->logger->info('Info level');
        $this->logger->warning('Warning level');
        $this->logger->error('Error level');
        $this->logger->critical('Critical level');

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('[DEBUG]');
        expect($content)->toContain('[INFO]');
        expect($content)->toContain('[WARNING]');
        expect($content)->toContain('[ERROR]');
        expect($content)->toContain('[CRITICAL]');
    });

    it('can log exceptions', function () {
        $exception = new Exception('Test exception', 500);
        $this->logger->error('Exception occurred', ['exception' => $exception]);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('Exception occurred');
        expect($content)->toContain('Test exception');
        expect($content)->toContain('500');
    });

    it('formats context arrays properly', function () {
        $context = [
            'nested' => [
                'key' => 'value',
                'array' => [1, 2, 3]
            ],
            'simple' => 'string'
        ];

        $this->logger->info('Array context test', $context);

        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        expect($content)->toContain('nested');
        expect($content)->toContain('simple');
        expect($content)->toContain('string');
    });
});
