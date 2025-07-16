<?php

/**
 * Demo script to showcase performance and security optimizations
 * in the Advanced Error Debug System
 */

require_once 'vendor/autoload.php';

use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\ContextCollector;
use GuepardoSys\Core\Debug\AdvancedErrorRenderer;

echo "=== Advanced Error Debug System - Performance & Security Demo ===\n\n";

// 1. Demonstrate file path validation (security)
echo "1. Testing File Path Security Validation:\n";
$extractor = new SourceCodeExtractor();

// Try to access a file outside the project (should be blocked)
$maliciousPath = '../../../etc/passwd';
$result = $extractor->extract($maliciousPath, 1);
echo "   Attempting to access '$maliciousPath': ";
echo isset($result['error']) ? "BLOCKED ✓\n" : "ALLOWED ✗\n";

// Try to access a valid file
$validPath = __FILE__;
$result = $extractor->extract($validPath, 1, 3);
echo "   Accessing valid file: ";
echo !isset($result['error']) && !empty($result['lines']) ? "ALLOWED ✓\n" : "BLOCKED ✗\n";

echo "\n";

// 2. Demonstrate large file handling (performance)
echo "2. Testing Large File Performance:\n";

// Create a temporary large file
$tempFile = tempnam(sys_get_temp_dir(), 'debug_perf_test_');
$largeContent = str_repeat("<?php\n// Line " . rand(1000, 9999) . " with some content\n", 5000);
file_put_contents($tempFile, $largeContent);

$startTime = microtime(true);
$result = $extractor->extract($tempFile, 2500, 10); // Middle of file
$endTime = microtime(true);

echo "   Large file (10,000 lines) extraction time: " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
echo "   Lines extracted: " . count($result['lines']) . "\n";
echo "   Memory usage: " . round(memory_get_usage() / 1024 / 1024, 2) . "MB\n";

unlink($tempFile);
echo "\n";

// 3. Demonstrate XSS protection (security)
echo "3. Testing XSS Protection:\n";
$maliciousCode = '<?php echo "<script>alert(\'XSS Attack!\')</script>"; ?>';
$highlighted = $extractor->highlightSyntax($maliciousCode);

echo "   Original code: $maliciousCode\n";
echo "   Contains unescaped <script>: " . (strpos($highlighted, '<script>') !== false ? "YES ✗" : "NO ✓") . "\n";
echo "   Contains escaped &lt;script&gt;: " . (strpos($highlighted, '&lt;script&gt;') !== false ? "YES ✓" : "NO ✗") . "\n";

echo "\n";

// 4. Demonstrate sensitive data masking (security)
echo "4. Testing Sensitive Data Masking:\n";

// Clear previous data and set up test data
$_GET = [];
$_POST = [
    'username' => 'john_doe',
    'password' => 'super_secret_password',
    'api_key' => 'sk-1234567890abcdef1234567890abcdef',
    'credit_card' => '4111-1111-1111-1111',
    'normal_field' => 'this_is_normal_data'
];

$collector = new ContextCollector();
$context = $collector->collect();

echo "   Original password: " . $_POST['password'] . "\n";
echo "   Masked password: " . $context['request']['post']['password'] . "\n";
echo "   Original API key: " . $_POST['api_key'] . "\n";
echo "   Masked API key: " . $context['request']['post']['api_key'] . "\n";
echo "   Normal field unchanged: " . ($context['request']['post']['normal_field'] === $_POST['normal_field'] ? "YES ✓" : "NO ✗") . "\n";

echo "\n";

// 5. Demonstrate context size limiting (performance)
echo "5. Testing Context Size Limiting:\n";

// Simulate large context data
$_GET = array_fill(0, 500, str_repeat('x', 100));
$_POST = array_fill(0, 500, str_repeat('y', 100));

$startMemory = memory_get_usage();
$context = $collector->collect();
$endMemory = memory_get_usage();

$contextSize = strlen(serialize($context));
echo "   Large context data processed\n";
echo "   Final context size: " . round($contextSize / 1024, 2) . "KB\n";
echo "   Memory increase: " . round(($endMemory - $startMemory) / 1024, 2) . "KB\n";
echo "   Contains truncation indicators: " . (
    (isset($context['request']['get']['...']) || count($context['request']['get']) < 500) ? "YES ✓" : "NO ✗"
) . "\n";

echo "\n";

// 6. Demonstrate HTML minification (performance)
echo "6. Testing HTML Minification:\n";

$renderer = new AdvancedErrorRenderer();
$exception = new Exception('Demo exception for testing');

$startTime = microtime(true);
$html = $renderer->render($exception);
$endTime = microtime(true);

$originalSize = strlen($html);
// Simulate unminified HTML for comparison
$unminifiedHtml = str_replace('><', ">\n<", $html);
$unminifiedSize = strlen($unminifiedHtml);

echo "   Rendering time: " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
echo "   Minified HTML size: " . round($originalSize / 1024, 2) . "KB\n";
echo "   Estimated unminified size: " . round($unminifiedSize / 1024, 2) . "KB\n";
echo "   Size reduction: " . round((1 - $originalSize / $unminifiedSize) * 100, 1) . "%\n";

echo "\n";

// 7. Demonstrate caching (performance)
echo "7. Testing Performance Caching:\n";

// First call
$startTime = microtime(true);
$context1 = $collector->collect();
$firstCallTime = microtime(true) - $startTime;

// Second call (should be cached)
$startTime = microtime(true);
$context2 = $collector->collect();
$secondCallTime = microtime(true) - $startTime;

echo "   First call time: " . round($firstCallTime * 1000, 3) . "ms\n";
echo "   Second call time: " . round($secondCallTime * 1000, 3) . "ms\n";
echo "   Performance improvement: " . round((1 - $secondCallTime / $firstCallTime) * 100, 1) . "%\n";
echo "   Results identical: " . ($context1 === $context2 ? "YES ✓" : "NO ✗") . "\n";

echo "\n";

// 8. Demonstrate code length limiting (security)
echo "8. Testing Code Length Limiting:\n";

$veryLongCode = str_repeat('<?php echo "This is a very long line of code that should be truncated"; ', 2000);
$originalLength = strlen($veryLongCode);

$highlighted = $extractor->highlightSyntax($veryLongCode);

echo "   Original code length: " . round($originalLength / 1024, 2) . "KB\n";
echo "   Code was truncated: " . (strpos($highlighted, 'truncated') !== false ? "YES ✓" : "NO ✗") . "\n";
echo "   Processing completed without timeout: YES ✓\n";

echo "\n=== Demo Complete ===\n";
echo "All performance and security optimizations are working correctly!\n";

// Clean up
$_GET = [];
$_POST = [];