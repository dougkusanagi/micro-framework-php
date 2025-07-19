<?php

use GuepardoSys\Core\Debug\ContextCollector;

beforeEach(function () {
    $this->contextCollector = new ContextCollector();
    
    // Backup original superglobals
    $this->originalServer = $_SERVER;
    $this->originalGet = $_GET;
    $this->originalPost = $_POST;
    $this->originalFiles = $_FILES;
    $this->originalSession = $_SESSION ?? [];
});

afterEach(function () {
    // Restore original superglobals
    $_SERVER = $this->originalServer;
    $_GET = $this->originalGet;
    $_POST = $this->originalPost;
    $_FILES = $this->originalFiles;
    $_SESSION = $this->originalSession;
});

it('collects complete context structure', function () {
    $context = $this->contextCollector->collect();
    
    expect($context)->toBeArray()
        ->toHaveKey('request')
        ->toHaveKey('server')
        ->toHaveKey('environment')
        ->toHaveKey('session');
});

it('collects request data correctly', function () {
    // Setup test request data
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_HOST'] = 'example.com';
    $_SERVER['REQUEST_URI'] = '/test/path';
    $_SERVER['HTTPS'] = 'on';
    $_GET = ['param1' => 'value1', 'param2' => 'value2'];
    $_POST = ['field1' => 'data1', 'password' => 'secret123'];
    $_FILES = ['upload' => ['name' => 'test.txt', 'size' => 1024]];

    $context = $this->contextCollector->collect();
    $request = $context['request'];

    expect($request['method'])->toBe('POST');
    expect($request['url'])->toBe('https://example.com/test/path');
    expect($request['get'])->toBe(['param1' => 'value1', 'param2' => 'value2']);
    expect($request['files'])->toBe(['upload' => ['name' => 'test.txt', 'size' => 1024]]);
    
    // Check that password is masked
    expect($request['post'])->toHaveKey('password');
    expect($request['post']['password'])->not->toBe('secret123');
    expect($request['post']['password'])->toContain('*');
});

it('generates HTTPS URLs correctly', function () {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['HTTP_HOST'] = 'secure.example.com';
    $_SERVER['REQUEST_URI'] = '/secure/path';

    $context = $this->contextCollector->collect();
    
    expect($context['request']['url'])->toBe('https://secure.example.com/secure/path');
});

it('generates HTTP URLs correctly', function () {
    unset($_SERVER['HTTPS']);
    $_SERVER['HTTP_HOST'] = 'example.com';
    $_SERVER['REQUEST_URI'] = '/path';

    $context = $this->contextCollector->collect();
    
    expect($context['request']['url'])->toBe('http://example.com/path');
});

it('collects server data correctly', function () {
    $_SERVER['SERVER_SOFTWARE'] = 'Apache/2.4.41';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['PHP_SELF'] = '/index.php';
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test Browser';

    $context = $this->contextCollector->collect();
    $server = $context['server'];

    expect($server['SERVER_SOFTWARE'])->toBe('Apache/2.4.41');
    expect($server['SERVER_NAME'])->toBe('localhost');
    expect($server['SERVER_PORT'])->toBe('80');
    expect($server['PHP_SELF'])->toBe('/index.php');
    expect($server['HTTP_USER_AGENT'])->toBe('Mozilla/5.0 Test Browser');
});

it('collects environment data correctly', function () {
    $context = $this->contextCollector->collect();
    $environment = $context['environment'];

    // These should always be present
    expect($environment)->toHaveKey('PHP_VERSION');
    expect($environment)->toHaveKey('PHP_OS');
    expect($environment)->toHaveKey('PHP_SAPI');
    
    expect($environment['PHP_VERSION'])->toBe(PHP_VERSION);
    expect($environment['PHP_OS'])->toBe(PHP_OS);
    expect($environment['PHP_SAPI'])->toBe(PHP_SAPI);
});

it('collects session data when active', function () {
    // Mock session being active
    $_SESSION = [
        'user_id' => 123,
        'username' => 'testuser',
        'api_token' => 'secret_token_123',
        'preferences' => ['theme' => 'dark']
    ];

    $context = $this->contextCollector->collect();
    $session = $context['session'];

    expect($session['user_id'])->toBe(123);
    expect($session['username'])->toBe('testuser');
    expect($session['preferences'])->toBe(['theme' => 'dark']);
    
    // Check that api_token is masked
    expect($session)->toHaveKey('api_token');
    expect($session['api_token'])->not->toBe('secret_token_123');
    expect($session['api_token'])->toContain('*');
});

it('handles inactive session correctly', function () {
    $_SESSION = [];
    
    $context = $this->contextCollector->collect();
    
    expect($context['session'])->toBe([]);
});

it('sanitizes sensitive data correctly', function () {
    $_POST = [
        'username' => 'testuser',
        'password' => 'mypassword123',
        'email' => 'test@example.com',
        'api_key' => 'sk_test_1234567890',
        'secret_token' => 'very_secret_token',
        'csrf_token' => 'csrf_abc123',
        'normal_field' => 'normal_value'
    ];

    $context = $this->contextCollector->collect();
    $post = $context['request']['post'];

    // Normal fields should not be masked
    expect($post['username'])->toBe('testuser');
    expect($post['email'])->toBe('test@example.com');
    expect($post['normal_field'])->toBe('normal_value');

    // Sensitive fields should be masked
    expect($post['password'])->not->toBe('mypassword123');
    expect($post['api_key'])->not->toBe('sk_test_1234567890');
    expect($post['secret_token'])->not->toBe('very_secret_token');
    expect($post['csrf_token'])->not->toBe('csrf_abc123');

    // All masked values should contain asterisks
    expect($post['password'])->toContain('*');
    expect($post['api_key'])->toContain('*');
    expect($post['secret_token'])->toContain('*');
    expect($post['csrf_token'])->toContain('*');
});

it('detects sensitive keys correctly', function () {
    $testData = [
        'PASSWORD' => 'should_be_masked',
        'user_password' => 'should_be_masked',
        'Password' => 'should_be_masked',
        'API_KEY' => 'should_be_masked',
        'apikey' => 'should_be_masked',
        'access_token' => 'should_be_masked',
        'Authorization' => 'should_be_masked',
        'username' => 'should_not_be_masked',
        'email' => 'should_not_be_masked',
        'data' => 'should_not_be_masked'
    ];

    $_POST = $testData;
    $context = $this->contextCollector->collect();
    $post = $context['request']['post'];

    // Sensitive keys should be masked
    expect($post['PASSWORD'])->not->toBe('should_be_masked');
    expect($post['user_password'])->not->toBe('should_be_masked');
    expect($post['Password'])->not->toBe('should_be_masked');
    expect($post['API_KEY'])->not->toBe('should_be_masked');
    expect($post['apikey'])->not->toBe('should_be_masked');
    expect($post['access_token'])->not->toBe('should_be_masked');
    expect($post['Authorization'])->not->toBe('should_be_masked');

    // Non-sensitive keys should not be masked
    expect($post['username'])->toBe('should_not_be_masked');
    expect($post['email'])->toBe('should_not_be_masked');
    expect($post['data'])->toBe('should_not_be_masked');
});

it('masks values with different lengths correctly', function () {
    $testCases = [
        'a' => '*',
        'ab' => '**',
        'abc' => '***',
        'abcd' => '****',
        'abcde' => 'ab*de',
        'password123' => 'pa*******23',
        'very_long_secret_key_12345' => 've**********************45'
    ];

    foreach ($testCases as $input => $expected) {
        $_POST = ['secret' => $input];
        $context = $this->contextCollector->collect();
        $masked = $context['request']['post']['secret'];
        
        expect($masked)->toBe($expected);
    }
});

it('sanitizes nested arrays correctly', function () {
    $_POST = [
        'user' => [
            'name' => 'John Doe',
            'password' => 'secret123',
            'profile' => [
                'email' => 'john@example.com',
                'api_key' => 'key_12345'
            ]
        ]
    ];

    $context = $this->contextCollector->collect();
    $post = $context['request']['post'];

    expect($post['user']['name'])->toBe('John Doe');
    expect($post['user']['profile']['email'])->toBe('john@example.com');
    
    // Nested sensitive data should be masked
    expect($post['user']['password'])->not->toBe('secret123');
    expect($post['user']['profile']['api_key'])->not->toBe('key_12345');
    expect($post['user']['password'])->toContain('*');
    expect($post['user']['profile']['api_key'])->toContain('*');
});

it('collects request headers correctly', function () {
    $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml';
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test Browser';
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer secret_token';

    $context = $this->contextCollector->collect();
    $headers = $context['request']['headers'];

    expect($headers['Accept'])->toBe('text/html,application/xhtml+xml');
    expect($headers['Accept-Language'])->toBe('en-US,en;q=0.9');
    expect($headers['User-Agent'])->toBe('Mozilla/5.0 Test Browser');
    
    // Authorization header should be masked
    expect($headers)->toHaveKey('Authorization');
    expect($headers['Authorization'])->not->toBe('Bearer secret_token');
    expect($headers['Authorization'])->toContain('*');
});

it('masks non-string values correctly', function () {
    $_POST = [
        'number_secret' => 12345,
        'array_secret' => ['key' => 'value'],
        'bool_secret' => true,
        'null_secret' => null
    ];

    // Make these keys sensitive by using 'secret' in the name
    $context = $this->contextCollector->collect();
    $post = $context['request']['post'];

    // Non-string values should be masked as '[HIDDEN]'
    expect($post['number_secret'])->toBe('[HIDDEN]');
    expect($post['array_secret'])->toBe('[HIDDEN]');
    expect($post['bool_secret'])->toBe('[HIDDEN]');
    expect($post['null_secret'])->toBe('[HIDDEN]');
});

it('handles empty superglobals correctly', function () {
    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_SESSION = [];
    $_SERVER = array_merge($_SERVER, [
        'REQUEST_METHOD' => 'GET',
        'HTTP_HOST' => 'localhost',
        'REQUEST_URI' => '/'
    ]);

    $context = $this->contextCollector->collect();

    expect($context['request']['get'])->toBe([]);
    expect($context['request']['post'])->toBe([]);
    expect($context['request']['files'])->toBe([]);
    expect($context['session'])->toBe([]);
    expect($context['request']['method'])->toBe('GET');
    expect($context['request']['url'])->toBe('http://localhost/');
});