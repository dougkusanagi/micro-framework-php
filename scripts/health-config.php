<?php

/**
 * Health Check Configuration
 * 
 * Configuration for health monitoring and checks
 */

return [
    // Which checks to run
    'checks' => [
        'system' => true,           // System requirements and resources
        'database' => true,         // Database connectivity
        'cache' => true,            // Cache system functionality
        'storage' => true,          // File system permissions
        'security' => true,         // Security configuration
        'performance' => true,      // Performance metrics
    ],

    // Health score thresholds
    'thresholds' => [
        'critical_score' => 60,     // Below this is critical
        'warning_score' => 80,      // Below this is warning
        'memory_limit_mb' => 128,   // Minimum memory limit in MB
        'disk_space_gb' => 1,       // Minimum free disk space in GB
        'response_time_ms' => 1000, // Maximum acceptable response time
    ],

    // Output configuration
    'output_format' => 'console',   // console, json, html
    'log_results' => true,          // Log results to file
    'email_alerts' => false,        // Send email alerts on failures
    'slack_webhook' => '',          // Slack webhook URL for notifications

    // Monitoring intervals (for cron jobs)
    'monitoring' => [
        'enabled' => false,
        'interval' => '*/5 * * * *',    // Every 5 minutes
        'critical_only' => false,       // Only alert on critical issues
        'max_alerts_per_hour' => 3,     // Rate limiting for alerts
    ],

    // System-specific checks
    'system_checks' => [
        'php_version' => [
            'min_version' => '8.1.0',
            'critical' => true,
        ],
        'required_extensions' => [
            'pdo' => true,
            'mbstring' => true,
            'json' => true,
            'openssl' => true,
            'hash' => true,
            'curl' => false,        // Optional but recommended
            'gd' => false,          // Optional for image processing
            'zip' => false,         // Optional for file operations
        ],
        'memory_limit' => [
            'min_mb' => 128,
            'recommended_mb' => 256,
            'critical' => false,
        ],
        'execution_time' => [
            'max_seconds' => 30,
            'critical' => false,
        ],
    ],

    // Database checks
    'database_checks' => [
        'connection_timeout' => 5,      // Seconds
        'query_timeout' => 10,          // Seconds
        'test_queries' => [
            'basic' => 'SELECT 1',
            'tables' => 'SHOW TABLES',  // MySQL/MariaDB
        ],
        'check_migrations' => true,     // Verify migrations are up to date
    ],

    // Cache checks
    'cache_checks' => [
        'test_write_size' => 1024,      // Bytes to test write
        'directories' => [
            'storage/cache' => ['writable' => true, 'critical' => true],
            'storage/cache/views' => ['writable' => true, 'critical' => false],
            'storage/logs' => ['writable' => true, 'critical' => true],
        ],
    ],

    // Security checks
    'security_checks' => [
        'sensitive_files' => [
            '.env',
            'composer.json',
            'composer.lock',
            'phpunit.xml',
            'phpstan.neon',
            'phpcs.xml',
        ],
        'check_permissions' => true,
        'check_error_display' => true,
        'check_debug_mode' => true,
    ],

    // Performance checks
    'performance_checks' => [
        'bootstrap_time_ms' => 500,     // Max acceptable bootstrap time
        'opcache' => [
            'check_enabled' => true,
            'critical' => false,
        ],
        'memory_usage' => [
            'max_mb' => 64,             // Max memory usage for basic request
            'critical' => false,
        ],
    ],

    // Alert configuration
    'alerts' => [
        'email' => [
            'enabled' => false,
            'smtp' => [
                'host' => 'smtp.example.com',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
            ],
            'from' => 'noreply@example.com',
            'to' => ['admin@example.com'],
            'subject' => 'Health Check Alert - {{status}}',
        ],
        'slack' => [
            'enabled' => false,
            'webhook_url' => '',
            'channel' => '#alerts',
            'username' => 'HealthBot',
            'icon_emoji' => ':warning:',
        ],
    ],

    // Custom health endpoints
    'endpoints' => [
        'basic' => [
            'url' => '/',
            'expected_status' => 200,
            'timeout' => 10,
            'critical' => true,
        ],
        'api' => [
            'url' => '/api/health',
            'expected_status' => 200,
            'timeout' => 5,
            'critical' => false,
        ],
    ],

    // Maintenance mode detection
    'maintenance' => [
        'check_file' => 'storage/framework/down',
        'ignore_during_maintenance' => [
            'performance',
            'endpoints',
        ],
    ],

    // Historical data
    'history' => [
        'keep_days' => 30,              // Days to keep historical data
        'log_file' => 'storage/logs/health-history.log',
        'track_trends' => true,         // Track performance trends
    ],

    // Custom checks (callable functions)
    'custom_checks' => [
        // Example custom check
        // 'queue_health' => function() {
        //     // Custom queue health check logic
        //     return [
        //         'name' => 'Queue Health',
        //         'status' => true,
        //         'value' => 'operational',
        //         'expected' => 'operational',
        //         'critical' => false,
        //     ];
        // },
    ],
];
