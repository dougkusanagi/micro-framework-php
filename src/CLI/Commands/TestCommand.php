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

    public static function handle(array $args): int
    {
        $options = self::parseOptions($args);

        echo "🧪 Running tests...\n\n";

        $command = 'vendor/bin/pest';

        // Add coverage option
        if ($options['coverage'] ?? false) {
            $command .= ' --coverage';
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

        // Execute the command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        // Display output
        foreach ($output as $line) {
            echo $line . "\n";
        }

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

        for ($i = 1; $i < count($args); $i++) {
            $arg = $args[$i];

            switch ($arg) {
                case '--coverage':
                    $options['coverage'] = true;
                    break;
                case '--parallel':
                    $options['parallel'] = true;
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
  ./guepardo test [options]

Options:
  --coverage      Generate code coverage report
  --parallel      Run tests in parallel
  --filter=VALUE  Filter tests by name
  --testsuite=VALUE Run specific test suite (Unit|Feature)

Examples:
  ./guepardo test
  ./guepardo test --coverage
  ./guepardo test --filter=UserTest
  ./guepardo test --testsuite=Unit
";
    }
}
