<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Quality Command - Run all quality checks
 */
class QualityCommand
{
    public function execute(array $args): int
    {
        return self::handle($args);
    }

    public static function handle(array $args): int
    {
        echo "🎯 Running comprehensive quality checks...\n\n";

        $results = [];

        // Run tests
        echo "1️⃣ Running tests...\n";
        $results['tests'] = TestCommand::handle(['test']);

        echo "\n" . str_repeat("=", 50) . "\n\n";

        // Run PHPStan
        echo "2️⃣ Running PHPStan static analysis...\n";
        $results['stan'] = StanCommand::handle(['stan']);

        echo "\n" . str_repeat("=", 50) . "\n\n";

        // Run CodeSniffer
        echo "3️⃣ Running PHP_CodeSniffer...\n";
        $results['cs'] = CsCommand::handle(['cs']);

        echo "\n" . str_repeat("=", 50) . "\n\n";

        // Summary
        echo "📊 Quality Check Summary:\n";
        echo "------------------------\n";

        $totalScore = 0;
        $maxScore = 300; // 3 checks * 100 points each

        foreach ($results as $check => $result) {
            $status = $result === 0 ? '✅ PASS' : '❌ FAIL';
            $score = $result === 0 ? 100 : 0;
            $totalScore += $score;

            echo sprintf("%-15s: %s (%d/100)\n", ucfirst($check), $status, $score);
        }

        $percentage = round(($totalScore / $maxScore) * 100);
        echo "\nOverall Score: {$totalScore}/{$maxScore} ({$percentage}%)\n";

        if ($percentage >= 100) {
            echo "\n🎉 Excellent! Your code passes all quality checks!\n";
            return 0;
        } elseif ($percentage >= 80) {
            echo "\n👍 Good! Minor issues found.\n";
            return 1;
        } else {
            echo "\n⚠️  Needs improvement. Please address the issues above.\n";
            return 1;
        }
    }

    public static function help(): string
    {
        return "Run comprehensive quality checks

This command runs:
1. Tests (Pest)
2. Static analysis (PHPStan)
3. Code style checks (PHP_CodeSniffer)

Usage:
  ./guepardo quality

The command will provide a summary score based on all checks.
";
    }
}
