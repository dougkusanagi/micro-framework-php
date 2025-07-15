<?php

/**
 * Test script to demonstrate the new error page design
 */

require_once 'vendor/autoload.php';

use GuepardoSys\Core\Debug\AdvancedErrorRenderer;
use GuepardoSys\Core\Debug\DebugConfig;

// Enable debug mode
DebugConfig::setDebugMode(true);
DebugConfig::setShowSource(true);

// Create a test exception with some context
function testFunction() {
    $data = ['user' => 'john_doe', 'action' => 'test'];
    
    // This will throw an exception
    throw new Exception('This is a test exception to showcase the beautiful error page design!');
}

function anotherFunction() {
    testFunction();
}

try {
    anotherFunction();
} catch (Exception $e) {
    // Create the renderer
    $renderer = new AdvancedErrorRenderer();
    
    // Add some context data
    $_POST = [
        'username' => 'test_user',
        'password' => 'secret123',
        'api_key' => 'sk-test123456789',
        'data' => 'some normal data'
    ];
    
    $_GET = [
        'page' => '1',
        'search' => 'test query',
        'token' => 'hidden_token_123'
    ];
    
    // Render the error page
    $html = $renderer->render($e, ['test_context' => 'This is additional context']);
    
    // Output the HTML
    echo $html;
}