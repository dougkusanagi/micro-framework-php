<?php

describe('Cache Helpers', function () {
    beforeEach(function () {
        // Limpar cache antes de cada teste
        @array_map('unlink', glob(STORAGE_PATH . '/cache/data/*'));
    });

    it('cache() helper can get manager without parameters', function () {
        $manager = cache();
        expect($manager)->toBeInstanceOf(GuepardoSys\Core\Cache\CacheManager::class);
    });

    it('cache() helper can get value with key only', function () {
        cache('helper.test', 'test value', 300);

        $value = cache('helper.test');
        expect($value)->toBe('test value');
    });

    it('cache() helper can set value with key and value', function () {
        $result = cache('helper.set.test', 'set value', 300);
        expect($result)->toBeTrue();

        $value = cache('helper.set.test');
        expect($value)->toBe('set value');
    });

    it('cache_remember() helper works correctly', function () {
        $callCount = 0;

        $value1 = cache_remember('remember.helper', function () use (&$callCount) {
            $callCount++;
            return 'remembered value';
        }, 300);

        $value2 = cache_remember('remember.helper', function () use (&$callCount) {
            $callCount++;
            return 'remembered value';
        }, 300);

        expect($value1)->toBe('remembered value');
        expect($value2)->toBe('remembered value');
        expect($callCount)->toBe(1);
    });

    it('cache_forever() helper works correctly', function () {
        $result = cache_forever('forever.helper', 'forever value');
        expect($result)->toBeTrue();

        $value = cache('forever.helper');
        expect($value)->toBe('forever value');
    });

    it('cache_forget() helper works correctly', function () {
        cache('forget.helper', 'value to forget', 300);
        expect(cache('forget.helper'))->toBe('value to forget');

        $result = cache_forget('forget.helper');
        expect($result)->toBeTrue();
        expect(cache('forget.helper'))->toBeNull();
    });

    it('cache_flush() helper works correctly', function () {
        cache('flush1', 'value1', 300);
        cache('flush2', 'value2', 300);

        expect(cache('flush1'))->toBe('value1');
        expect(cache('flush2'))->toBe('value2');

        $result = cache_flush();
        expect($result)->toBeTrue();

        expect(cache('flush1'))->toBeNull();
        expect(cache('flush2'))->toBeNull();
    });

    it('cache_tags() helper works correctly', function () {
        $tagged = cache_tags(['helper', 'test']);
        expect($tagged)->toBeInstanceOf(GuepardoSys\Core\Cache\CacheTagged::class);

        // Test storing and retrieving
        $tagged->put('tagged.helper', 'tagged value', 300);
        $value = $tagged->get('tagged.helper');
        expect($value)->toBe('tagged value');
    });

    it('helpers integrate well with facade', function () {
        // Use helper to store
        cache_remember('integration.test', function () {
            return ['helper' => true, 'facade' => true];
        }, 300);

        // Use facade to retrieve
        $value = \GuepardoSys\Core\Cache\CacheFacade::get('integration.test');
        expect($value)->toBe(['helper' => true, 'facade' => true]);

        // Use helper to check existence
        expect(\GuepardoSys\Core\Cache\CacheFacade::has('integration.test'))->toBeTrue();
    });

    it('helpers handle null ttl correctly', function () {
        // TTL null deve usar o padrão
        $result = cache_remember('null.ttl.test', function () {
            return 'default ttl value';
        });

        expect($result)->toBe('default ttl value');

        // Verificar se está no cache
        $cached = cache('null.ttl.test');
        expect($cached)->toBe('default ttl value');
    });

    it('cache helper returns appropriate defaults', function () {
        // Chave inexistente deve retornar null
        $value = cache('non.existent.key');
        expect($value)->toBeNull();

        // Helper forget para chave inexistente deve retornar true
        $result = cache_forget('another.non.existent');
        expect($result)->toBeTrue();
    });
});
