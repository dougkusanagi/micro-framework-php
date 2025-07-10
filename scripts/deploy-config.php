<?php

/**
 * Deploy Configuration
 * 
 * This file contains deployment settings that can be customized
 * for different environments and deployment methods.
 */

return [
    // Build directory (where optimized files are stored)
    'build_dir' => dirname(__DIR__) . '/build',

    // Backup directory (for deployment backups)
    'backup_dir' => dirname(__DIR__) . '/backups',

    // Files and directories to exclude from deployment
    'exclude_patterns' => [
        '/\.git/',
        '/node_modules/',
        '/tests/',
        '/docs/',
        '/build/',
        '/backups/',
        '/temp_cache/',
        '/temp_test/',
        '/temp_views/',
        '/\.env\.example$/',
        '/phpunit\.xml$/',
        '/phpstan\.neon$/',
        '/phpcs\.xml$/',
        '/package\.json$/',
        '/package-lock\.json$/',
        '/bun\.lock$/',
        '/tailwind\.config\.js$/',
        '/\.gitignore$/',
        '/\.htaccess\.dev$/',
        '/composer\.lock$/',
        '/storage\/logs\/.*\.log$/',
        '/storage\/cache\/test\//',
        '/storage\/cache\/phpstan\//',
        '/storage\/cache\/phpunit\//',
        '/vendor\/.*\/tests\//',
        '/vendor\/.*\/docs\//',
    ],

    // Directories that need to be writable
    'writable_dirs' => [
        'storage',
        'storage/cache',
        'storage/cache/views',
        'storage/logs',
        'public/assets',
    ],

    // Post-deployment tasks to run
    'post_deploy_commands' => [
        'clear_cache',
        'set_permissions',
        'warmup_cache',
        'create_symlinks',
    ],

    // Deployment environments
    'environments' => [
        'production' => [
            'name' => 'Production',
            'type' => 'ftp', // or 'rsync', 'local'
            'config' => [
                'host' => 'your-server.com',
                'username' => 'your-username',
                'password' => '', // Leave empty to prompt
                'remote_path' => '/public_html',
                'port' => 21,
                'passive' => true,
            ],
            'backup' => true,
            'maintenance_mode' => true,
        ],

        'staging' => [
            'name' => 'Staging',
            'type' => 'rsync',
            'config' => [
                'destination' => 'user@staging-server.com:/var/www/staging',
                'ssh_key' => '~/.ssh/id_rsa',
            ],
            'backup' => true,
            'maintenance_mode' => false,
        ],

        'local' => [
            'name' => 'Local',
            'type' => 'local',
            'config' => [
                'path' => '/var/www/html',
            ],
            'backup' => true,
            'maintenance_mode' => false,
        ],
    ],

    // FTP specific settings
    'ftp' => [
        'timeout' => 30,
        'retry_attempts' => 3,
        'chunk_size' => 8192,
    ],

    // Rsync specific settings
    'rsync' => [
        'options' => [
            '--archive',
            '--verbose',
            '--compress',
            '--delete',
            '--human-readable',
            '--progress',
        ],
        'exclude_from_file' => true,
    ],

    // Health checks after deployment
    'health_checks' => [
        'enabled' => true,
        'endpoints' => [
            '/' => 200,
            '/health' => 200,
        ],
        'timeout' => 10,
        'max_attempts' => 3,
    ],

    // Notification settings
    'notifications' => [
        'enabled' => false,
        'channels' => [
            'email' => [
                'to' => 'admin@example.com',
                'subject' => 'Deployment Notification',
            ],
            'slack' => [
                'webhook_url' => '',
                'channel' => '#deployments',
            ],
        ],
    ],

    // Rollback settings
    'rollback' => [
        'enabled' => true,
        'keep_releases' => 5,
        'release_dir' => 'releases',
        'current_symlink' => 'current',
    ],

    // Security settings
    'security' => [
        'verify_ssl' => true,
        'check_file_permissions' => true,
        'scan_for_malware' => false,
        'allowed_file_types' => [
            'php',
            'js',
            'css',
            'html',
            'json',
            'xml',
            'png',
            'jpg',
            'jpeg',
            'gif',
            'svg',
            'ico',
            'ttf',
            'woff',
            'woff2',
            'eot',
            'pdf',
            'txt',
            'md',
        ],
    ],
];
