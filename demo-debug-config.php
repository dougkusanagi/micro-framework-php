<?php

require_once 'vendor/autoload.php';

use GuepardoSys\Core\Debug\DebugConfig;
use GuepardoSys\Core\Debug\SourceCodeExtractor;
use GuepardoSys\Core\Debug\ContextCollector;
use GuepardoSys\Core\Debug\StackTraceFormatter;

echo "=== Debug Configuration Demo ===\n\n";

// Reset configuration to start fresh
DebugConfig::reset();

echo "1. Default Configuration Values:\n";
echo "   DEBUG_SHOW_SOURCE: " . (DebugConfig::showSource() ? 'true' : 'false') . "\n";
echo "   DEBUG_CONTEXT_LINES: " . DebugConfig::getContextLines() . "\n";
echo "   DEBUG_MAX_STRING_LENGTH: " . DebugConfig::getMaxStringLength() . "\n";
echo "   DEBUG_HIDE_VENDOR: " . (DebugConfig::hideVendor() ? 'true' : 'false') . "\n\n";

echo "2. Testing Configuration Changes:\n";

// Test DEBUG_SHOW_SOURCE
echo "   Setting DEBUG_SHOW_SOURCE to false...\n";
DebugConfig::set('DEBUG_SHOW_SOURCE', false);
$extractor = new SourceCodeExtractor();
$result = $extractor->extract(__FILE__, 10);
echo "   Source extraction result: " . ($result['error'] ? $result['error'] : 'Success') . "\n";

// Reset and test with true
DebugConfig::set('DEBUG_SHOW_SOURCE', true);
$result = $extractor->extract(__FILE__, 10);
echo "   Source extraction with DEBUG_SHOW_SOURCE=true: " . ($result['error'] ? $result['error'] : 'Success') . "\n\n";

// Test DEBUG_CONTEXT_LINES
echo "   Setting DEBUG_CONTEXT_LINES to 5...\n";
DebugConfig::set('DEBUG_CONTEXT_LINES', 5);
$result = $extractor->extract(__FILE__, 20);
echo "   Lines extracted: " . count($result['lines']) . " (should be around 11: 5 before + 1 current + 5 after)\n\n";

// Test DEBUG_MAX_STRING_LENGTH
echo "   Testing DEBUG_MAX_STRING_LENGTH with ContextCollector...\n";
DebugConfig::set('DEBUG_MAX_STRING_LENGTH', 200);
$longString = str_repeat('test', 60); // 240 characters
$_GET['test_param'] = $longString;

$collector = new ContextCollector();
$context = $collector->collect();
$processedString = $context['request']['get']['test_param'];

echo "   Original length: " . strlen($longString) . "\n";
echo "   Processed length: " . strlen($processedString) . "\n";
echo "   Ends with '...': " . (substr($processedString, -3) === '...' ? 'true' : 'false') . "\n\n";

// Test DEBUG_HIDE_VENDOR
echo "   Testing DEBUG_HIDE_VENDOR with StackTraceFormatter...\n";
$trace = debug_backtrace();

// Add a fake vendor frame
$trace[] = [
    'file' => '/vendor/some-package/src/SomeClass.php',
    'line' => 42,
    'function' => 'someFunction',
    'class' => 'SomeVendorClass',
    'type' => '::',
    'args' => []
];

$formatter = new StackTraceFormatter();

DebugConfig::set('DEBUG_HIDE_VENDOR', true);
$formattedWithHidden = $formatter->format($trace);

DebugConfig::set('DEBUG_HIDE_VENDOR', false);
$formattedWithoutHidden = $formatter->format($trace);

echo "   Frames with DEBUG_HIDE_VENDOR=true: " . count($formattedWithHidden) . "\n";
echo "   Frames with DEBUG_HIDE_VENDOR=false: " . count($formattedWithoutHidden) . "\n";
echo "   Vendor frames hidden: " . (count($formattedWithHidden) < count($formattedWithoutHidden) ? 'true' : 'false') . "\n\n";

echo "3. All Configuration Values:\n";
$allConfig = DebugConfig::all();
foreach ($allConfig as $key => $value) {
    $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
    echo "   $key: $displayValue\n";
}

echo "\n=== Demo Complete ===\n";

// Clean up
unset($_GET['test_param']);