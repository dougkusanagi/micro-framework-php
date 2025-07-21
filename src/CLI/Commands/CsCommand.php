<?php

namespace GuepardoSys\CLI\Commands;

/**
 * CS Command - Run PHP_CodeSniffer code style analysis
 */
class CsCommand
{
    public function execute(array $args): int
    {
        return self::handle($args);
    }

    public static function getDescription(): string
    {
        return "Run PHP_CodeSniffer for code style analysis.";
    }

    public static function handle(array $args): int
    {
        $options = self::parseOptions($args);

        if ($options['fix'] ?? false) {
            echo "🔧 Running PHP Code Beautifier and Fixer...\n\n";
            return self::runFixer($options);
        } else {
            echo "📋 Running PHP_CodeSniffer...\n\n";
            return self::runSniffer($options);
        }
    }

    private static function runSniffer(array $options): int
    {
        $command = 'vendor/bin/phpcs';

        // Add configuration file if it exists
        if (file_exists('phpcs.xml')) {
            $command .= ' --standard=phpcs.xml';
        } else {
            $command .= ' --standard=PSR12';
        }

        // Add report format
        $format = $options['format'] ?? 'full';
        $command .= " --report={$format}";

        // Add colors if available
        if (!($options['no-colors'] ?? false)) {
            $command .= ' --colors';
        }

        // Add specific paths if provided
        if (!empty($options['paths'])) {
            $command .= ' ' . implode(' ', $options['paths']);
        } else {
            $command .= ' app src';
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
            echo "\n✅ No coding standard violations found!\n";
        } else {
            echo "\n❌ Coding standard violations found. Run with --fix to auto-fix.\n";
        }

        return $returnCode;
    }

    private static function runFixer(array $options): int
    {
        $command = 'vendor/bin/phpcbf';

        // Add configuration file if it exists
        if (file_exists('phpcs.xml')) {
            $command .= ' --standard=phpcs.xml';
        } else {
            $command .= ' --standard=PSR12';
        }

        // Add specific paths if provided
        if (!empty($options['paths'])) {
            $command .= ' ' . implode(' ', $options['paths']);
        } else {
            $command .= ' app src';
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
            echo "\n✅ All fixable issues have been corrected!\n";
        } else {
            echo "\n⚠️  Some issues could not be automatically fixed.\n";
        }

        return $returnCode;
    }

    private static function parseOptions(array $args): array
    {
        $options = [];
        $paths = [];

        for ($i = 1; $i < count($args); $i++) {
            $arg = $args[$i];

            switch ($arg) {
                case '--fix':
                    $options['fix'] = true;
                    break;
                case '--no-colors':
                    $options['no-colors'] = true;
                    break;
                default:
                    if (str_starts_with($arg, '--format=')) {
                        $options['format'] = substr($arg, 9);
                    } elseif ($arg === '--format') {
                        $options['format'] = $args[++$i] ?? 'full';
                    } elseif (!str_starts_with($arg, '--')) {
                        $paths[] = $arg;
                    }
                    break;
            }
        }

        if (!empty($paths)) {
            $options['paths'] = $paths;
        }

        return $options;
    }

    public static function help(): string
    {
        return "Run PHP_CodeSniffer code style analysis

Usage:
  ./guepardo cs [options] [paths]

Options:
  --fix           Run phpcbf to automatically fix issues
  --format=VALUE  Report format (full, summary, json, etc.)
  --no-colors     Disable colored output

Examples:
  ./guepardo cs
  ./guepardo cs --fix
  ./guepardo cs --format=summary
  ./guepardo cs src app
";
    }
}
