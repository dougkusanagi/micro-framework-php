<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Test Command - Run tests with Pest
 */
class TestCommand
{
    public function execute(array $args): int
    {
        return self::handle($args);
    }

    public function getDescription(): string
    {
        return 'Run tests with Pest';
    }

    public static function handle(array $args): int
    {
        $options = self::parseOptions($args);

        echo "🧪 Running tests...\n\n";

        // Use proper path for Windows/Unix
        $pestPath = DIRECTORY_SEPARATOR === '\\' ? 'vendor\\bin\\pest.bat' : 'vendor/bin/pest';
        if (!file_exists($pestPath)) {
            $pestPath = DIRECTORY_SEPARATOR === '\\' ? 'vendor\\bin\\pest' : 'vendor/bin/pest';
        }
        
        $command = $pestPath;

        // Add coverage option
        if ($options['coverage'] ?? false) {
            $command .= ' --coverage';
            if ($options['coverage-clover'] ?? false) {
                $command .= ' --coverage-clover=coverage.xml';
            }
        }

        // Add filter option
        if (!empty($options['filter'])) {
            $command .= ' --filter="' . $options['filter'] . '"';
        }

        // Add testsuite option
        if (!empty($options['testsuite'])) {
            $command .= ' --testsuite=' . $options['testsuite'];
        }

        // Add parallel option
        if ($options['parallel'] ?? false) {
            $command .= ' --parallel';
        }

        // Add verbose option
        if ($options['verbose'] ?? false) {
            $command .= ' --verbose';
        }

        // Add stop on failure option
        if ($options['stop-on-failure'] ?? false) {
            $command .= ' --stop-on-failure';
        }

        // Execute the command using passthru for real-time output
        echo "Running: $command\n\n";
        passthru($command, $returnCode);

        if ($returnCode === 0) {
            echo "\n✅ All tests passed!\n";
        } else {
            echo "\n❌ Some tests failed.\n";
        }

        return $returnCode;
    }

    private static function parseOptions(array $args): array
    {
        $options = [];

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            switch ($arg) {
                case '--coverage':
                    $options['coverage'] = true;
                    break;
                case '--coverage-clover':
                    $options['coverage'] = true;
                    $options['coverage-clover'] = true;
                    break;
                case '--parallel':
                    $options['parallel'] = true;
                    break;
                case '--verbose':
                case '-v':
                    $options['verbose'] = true;
                    break;
                case '--stop-on-failure':
                    $options['stop-on-failure'] = true;
                    break;
                case '--filter':
                    $options['filter'] = $args[++$i] ?? '';
                    break;
                case '--testsuite':
                    $options['testsuite'] = $args[++$i] ?? '';
                    break;
            }
        }

        return $options;
    }

    public static function help(): string
    {
        return "Run tests with Pest

Usage:
  php guepardo test [options]

Options:
  --coverage           Generate code coverage report
  --coverage-clover    Generate code coverage with clover XML output
  --parallel           Run tests in parallel
  --verbose, -v        Verbose output
  --stop-on-failure    Stop execution on first failure
  --filter=VALUE       Filter tests by name
  --testsuite=VALUE    Run specific test suite (Unit|Feature)

Examples:
  php guepardo test
  php guepardo test --coverage
  php guepardo test --coverage-clover
  php guepardo test --filter=UserTest
  php guepardo test --testsuite=Unit
  php guepardo test --verbose --stop-on-failure
";
    }
}
