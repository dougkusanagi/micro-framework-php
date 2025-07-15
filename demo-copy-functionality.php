<?php

/**
 * Demo specifically for testing the copy functionality
 * This creates a simple error page to test the copy buttons
 */

require_once 'vendor/autoload.php';

use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\DebugConfig;

// Configure debug settings
DebugConfig::set('DEBUG_SHOW_SOURCE', true);
DebugConfig::set('DEBUG_CONTEXT_LINES', 10);

// Create a simple test function that will appear in the stack trace
function testCopyFunction() {
    $testData = [
        'message' => 'Testing copy functionality',
        'timestamp' => date('Y-m-d H:i:s'),
        'user' => 'developer'
    ];
    
    // This line will be highlighted in the error
    throw new RuntimeException('Test error for copy functionality demo');
}

try {
    testCopyFunction();
} catch (Exception $e) {
    $renderer = new AdvancedErrorRenderer();
    
    // Add some test context
    $context = [
        'test_info' => [
            'purpose' => 'Testing copy functionality',
            'features' => ['copy code', 'copy error info', 'copy stack trace'],
            'browser_support' => 'Modern browsers with Clipboard API'
        ]
    ];
    
    $html = $renderer->render($e, $context);
    
    // Save to a specific file for copy testing
    file_put_contents('copy-test.html', $html);
    
    echo "✅ Copy functionality test page generated!\n";
    echo "📄 File: copy-test.html\n\n";
    
    echo "🧪 Test Instructions:\n";
    echo "1. Open copy-test.html in your browser\n";
    echo "2. Look for the green copy feedback notification area (top-right)\n";
    echo "3. Try clicking the 📋 copy buttons next to code blocks\n";
    echo "4. Try the 'Copy Error Info' button in the Quick Actions sidebar\n";
    echo "5. Try the 'Copy Stack Trace' button\n";
    echo "6. Each copy action should show a green notification\n";
    echo "7. Paste the copied content to verify it worked\n\n";
    
    echo "🎯 Expected Results:\n";
    echo "• Copy buttons should show green success notification\n";
    echo "• Copied code should maintain proper formatting\n";
    echo "• Error info should include type, message, file, and line\n";
    echo "• Stack trace should be properly formatted\n";
    echo "• Notifications should auto-hide after 3 seconds\n\n";
    
    echo "🔧 Troubleshooting:\n";
    echo "• If copy doesn't work, check browser console for errors\n";
    echo "• Older browsers may need manual copy (Ctrl+C)\n";
    echo "• Make sure you're using HTTPS or localhost for Clipboard API\n";
}