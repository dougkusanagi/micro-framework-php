<?php

use GuepardoSys\Core\Dotenv;

describe('Dotenv', function () {
    beforeEach(function () {
        $this->testDir = __DIR__ . '/../../temp_test';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }

        // Backup current environment
        $this->originalEnv = $_ENV;
    });

    afterEach(function () {
        // Restore original environment
        $_ENV = $this->originalEnv;

        // Clean up test files
        if (is_dir($this->testDir)) {
            $files = glob($this->testDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->testDir);
        }
    });

    it('can load environment variables from .env file', function () {
        $envContent = "TEST_VAR=test_value\nANOTHER_VAR=another_value\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['TEST_VAR'])->toBe('test_value');
        expect($_ENV['ANOTHER_VAR'])->toBe('another_value');
    });

    it('can safely load without throwing exception if file not found', function () {
        $dotenv = new Dotenv($this->testDir);

        expect(function () use ($dotenv) {
            $dotenv->safeLoad();
        })->not->toThrow(Exception::class);
    });

    it('throws exception when loading non-existent file', function () {
        $dotenv = new Dotenv($this->testDir);

        expect(function () use ($dotenv) {
            $dotenv->load();
        })->toThrow(Exception::class);
    });

    it('can handle quoted values', function () {
        $envContent = 'QUOTED_VAR="quoted value"' . "\n" . "SINGLE_QUOTED='single quoted'\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['QUOTED_VAR'])->toBe('quoted value');
        expect($_ENV['SINGLE_QUOTED'])->toBe('single quoted');
    });

    it('can handle empty values', function () {
        $envContent = "EMPTY_VAR=\nANOTHER_EMPTY=\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['EMPTY_VAR'])->toBe('');
        expect($_ENV['ANOTHER_EMPTY'])->toBe('');
    });

    it('ignores comments and empty lines', function () {
        $envContent = "# This is a comment\nTEST_VAR=value\n\n# Another comment\nOTHER_VAR=other\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['TEST_VAR'])->toBe('value');
        expect($_ENV['OTHER_VAR'])->toBe('other');
    });

    it('handles values with equals signs', function () {
        $envContent = "URL=http://example.com?param=value&other=test\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['URL'])->toBe('http://example.com?param=value&other=test');
    });

    it('trims whitespace from keys and values', function () {
        $envContent = "  TRIMMED_KEY  =  trimmed_value  \n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['TRIMMED_KEY'])->toBe('trimmed_value');
    });

    it('can load from custom filename', function () {
        $envContent = "CUSTOM_VAR=custom_value\n";
        file_put_contents($this->testDir . '/.env.custom', $envContent);

        $dotenv = new Dotenv($this->testDir, '.env.custom');
        $dotenv->load();

        expect($_ENV['CUSTOM_VAR'])->toBe('custom_value');
    });

    it('does not override existing environment variables', function () {
        $_ENV['EXISTING_VAR'] = 'original_value';

        $envContent = "EXISTING_VAR=new_value\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->load();

        expect($_ENV['EXISTING_VAR'])->toBe('original_value');
    });

    it('can override existing variables when forced', function () {
        $_ENV['OVERRIDE_VAR'] = 'original_value';

        $envContent = "OVERRIDE_VAR=new_value\n";
        file_put_contents($this->testDir . '/.env', $envContent);

        $dotenv = new Dotenv($this->testDir);
        $dotenv->overload();

        expect($_ENV['OVERRIDE_VAR'])->toBe('new_value');
    });
});
