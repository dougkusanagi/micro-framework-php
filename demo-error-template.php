<?php

/**
 * Comprehensive demo of the new error template features
 */

require_once 'vendor/autoload.php';

use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\DebugConfig;

// Enable all debug features
DebugConfig::set('DEBUG_SHOW_SOURCE', true);
DebugConfig::set('DEBUG_CONTEXT_LINES', 15);

echo "=== Advanced Error Template Demo ===\n\n";

// Set up realistic context data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/users/create?debug=1&token=secret123';
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

$_POST = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'super_secret_password_123',
    'api_key' => 'sk-1234567890abcdef1234567890abcdef',
    'credit_card' => '4111-1111-1111-1111',
    'phone' => '+1-555-123-4567',
    'address' => '123 Main St, City, State',
    'preferences' => [
        'theme' => 'dark',
        'notifications' => true,
        'privacy_level' => 'high'
    ]
];

$_GET = [
    'page' => '1',
    'limit' => '10',
    'search' => 'user query',
    'sort' => 'created_at',
    'debug' => '1',
    'token' => 'debug_token_123',
    'api_secret' => 'hidden_api_secret'
];

// Create a complex stack trace
function deepFunction1($data) {
    return deepFunction2($data);
}

function deepFunction2($data) {
    return deepFunction3($data);
}

function deepFunction3($data) {
    // Simulate some processing
    $processedData = array_merge($data, ['processed' => true]);
    
    // This will cause an error
    throw new InvalidArgumentException(
        'Invalid user data provided. The email format is incorrect and the password does not meet security requirements. ' .
        'Please ensure the email follows the format user@domain.com and the password contains at least 8 characters with uppercase, lowercase, numbers, and special characters.'
    );
}

function validateUserData($userData) {
    // Add some context
    $validationRules = [
        'username' => 'required|min:3|max:50',
        'email' => 'required|email',
        'password' => 'required|min:8|complex'
    ];
    
    return deepFunction1($userData);
}

function createUser($userData) {
    // Simulate user creation process
    $userData['id'] = rand(1000, 9999);
    $userData['created_at'] = date('Y-m-d H:i:s');
    
    return validateUserData($userData);
}

try {
    // Trigger the error
    $result = createUser($_POST);
    
} catch (Exception $e) {
    // Create the renderer
    $renderer = new AdvancedErrorRenderer();
    
    // Add comprehensive context
    $context = [
        'request_id' => uniqid('req_'),
        'user_session' => [
            'user_id' => 12345,
            'role' => 'admin',
            'permissions' => ['read', 'write', 'delete'],
            'last_activity' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ],
        'application_state' => [
            'version' => '2.1.0',
            'environment' => 'development',
            'debug_mode' => true,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ],
        'database_info' => [
            'connection_status' => 'connected',
            'active_queries' => 3,
            'last_query' => 'SELECT * FROM users WHERE email = ?',
            'query_time' => '0.045ms'
        ]
    ];
    
    // Render the beautiful error page
    $html = $renderer->render($e, $context);
    
    // Save to file for viewing
    file_put_contents('error-demo.html', $html);
    
    echo "✅ Error page generated successfully!\n";
    echo "📄 Saved as: error-demo.html\n";
    echo "🌐 Open in browser to see the beautiful design\n\n";
    
    echo "🎨 Features showcased:\n";
    echo "   • Modern responsive design with dark/light theme support\n";
    echo "   • Interactive collapsible sections\n";
    echo "   • Syntax-highlighted code with copy functionality\n";
    echo "   • Advanced search with keyboard shortcuts\n";
    echo "   • Sensitive data masking for security\n";
    echo "   • Comprehensive context information\n";
    echo "   • Stack trace with source code preview\n";
    echo "   • Quick actions sidebar\n";
    echo "   • Performance optimizations\n";
    echo "   • Accessibility features\n\n";
    
    echo "🔍 Try these features in the browser:\n";
    echo "   • Click section headers to expand/collapse\n";
    echo "   • Use Ctrl+F to search in code\n";
    echo "   • Click copy buttons to copy code snippets\n";
    echo "   • Toggle theme with the 🌓 button\n";
    echo "   • Use 'Expand All' / 'Collapse All' buttons\n";
    echo "   • Try the quick action buttons in the sidebar\n\n";
    
    // Also output a small preview
    echo "📋 Error Summary:\n";
    echo "   Type: " . get_class($e) . "\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . basename($e->getFile()) . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}