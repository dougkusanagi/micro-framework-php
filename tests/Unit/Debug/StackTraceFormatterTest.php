<?php

use GuepardoSys\Core\Debug\StackTraceFormatter;

beforeEach(function () {
    $this->formatter = new StackTraceFormatter();
});

describe('format()', function () {
        it('formats a complete stack trace array', function () {
            $trace = [
                [
                    'file' => '/app/src/Controller/HomeController.php',
                    'line' => 25,
                    'function' => 'index',
                    'class' => 'HomeController',
                    'type' => '->',
                    'args' => ['test', 123]
                ],
                [
                    'file' => '/vendor/framework/Router.php',
                    'line' => 150,
                    'function' => 'dispatch',
                    'class' => 'Router',
                    'type' => '::',
                    'args' => []
                ]
            ];

            $result = $this->formatter->format($trace);

            expect($result)->toHaveCount(2);
            expect($result[0]['index'])->toBe(0);
            expect($result[1]['index'])->toBe(1);
            expect($result[0]['is_application'])->toBe(true);
            expect($result[1]['is_vendor'])->toBe(true);
        });

        it('handles empty stack trace', function () {
            $result = $this->formatter->format([]);
            expect($result)->toBe([]);
        });
    });

    describe('formatFrame()', function () {
        it('formats a basic stack trace frame', function () {
            $frame = [
                'file' => '/app/src/Controller/HomeController.php',
                'line' => 25,
                'function' => 'index',
                'class' => 'HomeController',
                'type' => '->',
                'args' => []
            ];

            $result = $this->formatter->formatFrame($frame, 0);

            expect($result['index'])->toBe(0);
            expect($result['file'])->toBe('/app/src/Controller/HomeController.php');
            expect($result['line'])->toBe(25);
            expect($result['function'])->toBe('index');
            expect($result['class'])->toBe('HomeController');
            expect($result['type'])->toBe('->');
            expect($result['function_name'])->toBe('HomeController->index');
            expect($result['is_application'])->toBe(true);
            expect($result['is_vendor'])->toBe(false);
        });

        it('handles frame with missing keys', function () {
            $frame = [
                'function' => 'test'
            ];

            $result = $this->formatter->formatFrame($frame);

            expect($result['file'])->toBe(null);
            expect($result['line'])->toBe(null);
            expect($result['function'])->toBe('test');
            expect($result['class'])->toBe(null);
            expect($result['type'])->toBe(null);
            expect($result['args'])->toBe([]);
            expect($result['function_name'])->toBe('test');
        });

        it('formats function name with class and type', function () {
            $frame = [
                'function' => 'method',
                'class' => 'TestClass',
                'type' => '::'
            ];

            $result = $this->formatter->formatFrame($frame);
            expect($result['function_name'])->toBe('TestClass::method');
        });

        it('formats function name without class', function () {
            $frame = [
                'function' => 'globalFunction'
            ];

            $result = $this->formatter->formatFrame($frame);
            expect($result['function_name'])->toBe('globalFunction');
        });
    });

    describe('vendor frame detection', function () {
        it('identifies vendor frames correctly', function () {
            $vendorFrames = [
                ['file' => '/vendor/package/src/Class.php'],
                ['file' => '/node_modules/package/index.js'],
                ['file' => '/usr/share/php/PEAR/Class.php'],
                ['file' => '/System/Library/Frameworks/PHP.framework/Class.php']
            ];

            foreach ($vendorFrames as $frame) {
                $result = $this->formatter->formatFrame($frame);
                expect($result['is_vendor'])->toBe(true);
                expect($result['is_application'])->toBe(false);
            }
        });

        it('does not identify application frames as vendor', function () {
            $appFrames = [
                ['file' => '/app/src/Controller/HomeController.php'],
                ['file' => '/project/app/Models/User.php'],
                ['file' => getcwd() . '/src/Core/Router.php']
            ];

            foreach ($appFrames as $frame) {
                $result = $this->formatter->formatFrame($frame);
                expect($result['is_vendor'])->toBe(false);
            }
        });
    });

    describe('application frame detection', function () {
        it('identifies application frames correctly', function () {
            $appFrames = [
                ['file' => '/app/src/Controller/HomeController.php'],
                ['file' => '/project/app/Models/User.php'],
                ['file' => '/src/Core/Router.php'],
                ['file' => getcwd() . '/custom/path/Class.php']
            ];

            foreach ($appFrames as $frame) {
                $result = $this->formatter->formatFrame($frame);
                expect($result['is_application'])->toBe(true);
            }
        });

        it('does not identify vendor frames as application', function () {
            $vendorFrames = [
                ['file' => '/vendor/package/src/Class.php'],
                ['file' => '/usr/share/php/Class.php']
            ];

            foreach ($vendorFrames as $frame) {
                $result = $this->formatter->formatFrame($frame);
                expect($result['is_application'])->toBe(false);
            }
        });
    });

    describe('short file path generation', function () {
        it('shortens paths relative to current working directory', function () {
            $cwd = getcwd();
            $frame = ['file' => $cwd . '/src/Controller/HomeController.php'];

            $result = $this->formatter->formatFrame($frame);
            expect($result['short_file'])->toBe('src/Controller/HomeController.php');
        });

        it('shortens long absolute paths', function () {
            $frame = ['file' => '/very/long/path/to/some/deep/directory/file.php'];

            $result = $this->formatter->formatFrame($frame);
            expect($result['short_file'])->toBe('.../deep/directory/file.php');
        });

        it('keeps short paths unchanged', function () {
            $frame = ['file' => '/short/path.php'];

            $result = $this->formatter->formatFrame($frame);
            expect($result['short_file'])->toBe('/short/path.php');
        });

        it('handles null file paths', function () {
            $frame = ['file' => null];

            $result = $this->formatter->formatFrame($frame);
            expect($result['short_file'])->toBe(null);
        });
    });

    describe('argument formatting', function () {
        it('formats string arguments', function () {
            $frame = [
                'function' => 'test',
                'args' => ['hello world', 'a very long string that should be truncated because it exceeds the limit']
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['args'][0]['type'])->toBe('string');
            expect($result['args'][0]['value'])->toBe('hello world');
            expect($result['args'][0]['preview'])->toBe('hello world');
            
            expect($result['args'][1]['type'])->toBe('string');
            expect($result['args'][1]['preview'])->toContain('...');
        });

        it('formats numeric arguments', function () {
            $frame = [
                'function' => 'test',
                'args' => [123, 45.67, true, false]
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['args'][0]['type'])->toBe('integer');
            expect($result['args'][0]['value'])->toBe(123);
            expect($result['args'][0]['preview'])->toBe('123');
            
            expect($result['args'][1]['type'])->toBe('double');
            expect($result['args'][1]['value'])->toBe(45.67);
            
            expect($result['args'][2]['type'])->toBe('boolean');
            expect($result['args'][2]['value'])->toBe(true);
            expect($result['args'][2]['preview'])->toBe('true');
            
            expect($result['args'][3]['type'])->toBe('boolean');
            expect($result['args'][3]['value'])->toBe(false);
            expect($result['args'][3]['preview'])->toBe('false');
        });

        it('formats array arguments', function () {
            $frame = [
                'function' => 'test',
                'args' => [['a', 'b', 'c'], []]
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['args'][0]['type'])->toBe('array');
            expect($result['args'][0]['value'])->toBe(null);
            expect($result['args'][0]['preview'])->toBe('array(3)');
            
            expect($result['args'][1]['type'])->toBe('array');
            expect($result['args'][1]['preview'])->toBe('array(0)');
        });

        it('formats object arguments', function () {
            $obj = new stdClass();
            $frame = [
                'function' => 'test',
                'args' => [$obj]
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['args'][0]['type'])->toBe('object');
            expect($result['args'][0]['value'])->toBe(null);
            expect($result['args'][0]['preview'])->toBe('stdClass');
        });

        it('formats null arguments', function () {
            $frame = [
                'function' => 'test',
                'args' => [null]
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['args'][0]['type'])->toBe('NULL');
            expect($result['args'][0]['value'])->toBe(null);
            expect($result['args'][0]['preview'])->toBe('null');
        });

        it('handles empty arguments array', function () {
            $frame = [
                'function' => 'test',
                'args' => []
            ];

            $result = $this->formatter->formatFrame($frame);
            expect($result['args'])->toBe([]);
        });
    });

    describe('edge cases', function () {
        it('handles frames without file information', function () {
            $frame = [
                'function' => 'eval',
                'args' => []
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['file'])->toBe(null);
            expect($result['line'])->toBe(null);
            expect($result['is_vendor'])->toBe(false);
            expect($result['is_application'])->toBe(false);
            expect($result['short_file'])->toBe(null);
        });

        it('handles internal PHP functions', function () {
            $frame = [
                'function' => 'array_map',
                'args' => []
            ];

            $result = $this->formatter->formatFrame($frame);
            
            expect($result['function'])->toBe('array_map');
            expect($result['function_name'])->toBe('array_map');
            expect($result['is_vendor'])->toBe(false);
            expect($result['is_application'])->toBe(false);
        });
    });