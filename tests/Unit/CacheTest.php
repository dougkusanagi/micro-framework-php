<?php

use GuepardoSys\Core\Cache;

describe('Cache', function () {
    beforeEach(function () {
        $this->cacheDir = __DIR__ . '/../../storage/cache/test';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        $this->cache = new Cache($this->cacheDir);
    });

    afterEach(function () {
        // Clean up test cache files
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    });

    it('can store and retrieve cache', function () {
        $key = 'test_key';
        $value = 'test_value';

        $this->cache->put($key, $value);

        expect($this->cache->get($key))->toBe($value);
    });

    it('can store and retrieve cache with ttl', function () {
        $key = 'test_ttl_key';
        $value = 'test_value';
        $ttl = 3600; // 1 hour

        $this->cache->put($key, $value, $ttl);

        expect($this->cache->get($key))->toBe($value);
    });

    it('returns null for non-existent cache', function () {
        expect($this->cache->get('non_existent_key'))->toBeNull();
    });

    it('returns default value for non-existent cache', function () {
        $default = 'default_value';

        expect($this->cache->get('non_existent_key', $default))->toBe($default);
    });

    it('can check if cache exists', function () {
        $key = 'existence_test';

        expect($this->cache->has($key))->toBeFalse();

        $this->cache->put($key, 'value');

        expect($this->cache->has($key))->toBeTrue();
    });

    it('can forget cache', function () {
        $key = 'forget_test';
        $value = 'test_value';

        $this->cache->put($key, $value);
        expect($this->cache->has($key))->toBeTrue();

        $this->cache->forget($key);
        expect($this->cache->has($key))->toBeFalse();
    });

    it('can flush all cache', function () {
        $this->cache->put('key1', 'value1');
        $this->cache->put('key2', 'value2');

        expect($this->cache->has('key1'))->toBeTrue();
        expect($this->cache->has('key2'))->toBeTrue();

        $this->cache->flush();

        expect($this->cache->has('key1'))->toBeFalse();
        expect($this->cache->has('key2'))->toBeFalse();
    });

    it('handles complex data types', function () {
        $data = [
            'string' => 'test',
            'number' => 123,
            'array' => [1, 2, 3],
            'object' => (object) ['prop' => 'value']
        ];

        $this->cache->put('complex_data', $data);
        $retrieved = $this->cache->get('complex_data');

        expect($retrieved)->toEqual($data);
    });

    it('respects ttl expiration', function () {
        $key = 'ttl_test';
        $value = 'expires_soon';

        // Set cache with 1 second TTL
        $this->cache->put($key, $value, 1);

        expect($this->cache->get($key))->toBe($value);

        // Wait for expiration
        sleep(2);

        expect($this->cache->get($key))->toBeNull();
    });

    it('can remember values with closure', function () {
        $key = 'remember_test';
        $computedValue = 'computed_value';

        $result = $this->cache->remember($key, function () use ($computedValue) {
            return $computedValue;
        }, 3600);

        expect($result)->toBe($computedValue);
        expect($this->cache->get($key))->toBe($computedValue);
    });

    it('uses cached value in remember if exists', function () {
        $key = 'remember_cached';
        $cachedValue = 'already_cached';
        $newValue = 'should_not_be_used';

        $this->cache->put($key, $cachedValue);

        $result = $this->cache->remember($key, function () use ($newValue) {
            return $newValue;
        }, 3600);

        expect($result)->toBe($cachedValue);
    });
});
