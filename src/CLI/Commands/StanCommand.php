<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Stan Command - Run PHPStan static analysis
 */
class StanCommand
{
    public function execute(array $args): int
    {
        return self::handle($args);
    }

    public static function handle(array $args): int
    {
        $options = self::parseOptions($args);

        echo "🔍 Running PHPStan static analysis...\n\n";

        $level = $options['level'] ?? 'max';
        $format = $options['format'] ?? 'table';

        $command = "vendor/bin/phpstan analyse --level={$level} --memory-limit=1G --error-format={$format}";

        // Add configuration file if it exists
        if (file_exists('phpstan.neon')) {
            $command .= ' --configuration=phpstan.neon';
        }

        // Add specific paths if provided
        if (!empty($options['paths'])) {
            $command .= ' ' . implode(' ', $options['paths']);
        }

        // Execute the command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        // Display output
        foreach ($output as $line) {
            echo $line . "\n";
        }

        if ($returnCode === 0) {
            echo "\n✅ No errors found by PHPStan!\n";
        } else {
            echo "\n❌ PHPStan found issues.\n";
        }

        return $returnCode;
    }

    private static function parseOptions(array $args): array
    {
        $options = [];
        $paths = [];

        for ($i = 1; $i < count($args); $i++) {
            $arg = $args[$i];

            if (str_starts_with($arg, '--level=')) {
                $options['level'] = substr($arg, 8);
            } elseif (str_starts_with($arg, '--format=')) {
                $options['format'] = substr($arg, 9);
            } elseif ($arg === '--level') {
                $options['level'] = $args[++$i] ?? 'max';
            } elseif ($arg === '--format') {
                $options['format'] = $args[++$i] ?? 'table';
            } elseif (!str_starts_with($arg, '--')) {
                $paths[] = $arg;
            }
        }

        if (!empty($paths)) {
            $options['paths'] = $paths;
        }

        return $options;
    }

    public static function help(): string
    {
        return "Run PHPStan static analysis

Usage:
  ./guepardo stan [options] [paths]

Options:
  --level=VALUE   Analysis level (0-8 or max)
  --format=VALUE  Output format (table, checkstyle, json, etc.)

Examples:
  ./guepardo stan
  ./guepardo stan --level=8
  ./guepardo stan --format=json
  ./guepardo stan src app
";
    }
}
