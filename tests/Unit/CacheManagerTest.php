<?php

use GuepardoSys\Core\Cache\CacheManager;
use GuepardoSys\Core\Cache\Cache;

describe('Cache Manager', function () {
    beforeEach(function () {
        // Limpar cache antes de cada teste
        @array_map('unlink', glob(STORAGE_PATH . '/cache/data/*'));
    });

    it('can get singleton instance', function () {
        $instance1 = CacheManager::getInstance();
        $instance2 = CacheManager::getInstance();

        expect($instance1)->toBe($instance2);
        expect($instance1)->toBeInstanceOf(CacheManager::class);
    });

    it('can get default store', function () {
        $manager = CacheManager::getInstance();
        $store = $manager->store();

        expect($store)->toBeInstanceOf(\GuepardoSys\Core\Cache\FileStore::class);
    });

    it('can forward calls to default store', function () {
        $manager = CacheManager::getInstance();

        $result = $manager->put('test.key', 'test value', 300);
        expect($result)->toBeTrue();

        $value = $manager->get('test.key');
        expect($value)->toBe('test value');
    });
});

describe('Cache Facade', function () {
    beforeEach(function () {
        // Limpar cache antes de cada teste
        @array_map('unlink', glob(STORAGE_PATH . '/cache/data/*'));
    });

    it('can put and get values', function () {
        $result = Cache::put('facade.test', 'facade value', 300);
        expect($result)->toBeTrue();

        $value = Cache::get('facade.test');
        expect($value)->toBe('facade value');
    });

    it('can check if key exists', function () {
        Cache::put('exists.test', 'value', 300);

        expect(Cache::has('exists.test'))->toBeTrue();
        expect(Cache::has('not.exists'))->toBeFalse();
    });

    it('can forget values', function () {
        Cache::put('forget.test', 'value', 300);
        expect(Cache::has('forget.test'))->toBeTrue();

        $result = Cache::forget('forget.test');
        expect($result)->toBeTrue();
        expect(Cache::has('forget.test'))->toBeFalse();
    });

    it('can remember values with closure', function () {
        $callCount = 0;

        $value1 = Cache::remember('remember.test', 300, function () use (&$callCount) {
            $callCount++;
            return 'computed value';
        });

        $value2 = Cache::remember('remember.test', 300, function () use (&$callCount) {
            $callCount++;
            return 'computed value';
        });

        expect($value1)->toBe('computed value');
        expect($value2)->toBe('computed value');
        expect($callCount)->toBe(1); // Closure deve ser chamada apenas uma vez
    });

    it('can store forever', function () {
        $result = Cache::forever('forever.test', 'forever value');
        expect($result)->toBeTrue();

        $value = Cache::get('forever.test');
        expect($value)->toBe('forever value');
    });

    it('can pull values (get and remove)', function () {
        Cache::put('pull.test', 'pull value', 300);

        $value = Cache::pull('pull.test');
        expect($value)->toBe('pull value');

        // Deve ter sido removido
        expect(Cache::has('pull.test'))->toBeFalse();
    });

    it('can increment values', function () {
        // Primeira chamada - valor inicial 0
        $value1 = Cache::increment('counter.test');
        expect($value1)->toBe(1);

        // Segunda chamada - incrementa
        $value2 = Cache::increment('counter.test', 5);
        expect($value2)->toBe(6);
    });

    it('can decrement values', function () {
        Cache::put('decrement.test', 10, 300);

        $value1 = Cache::decrement('decrement.test');
        expect($value1)->toBe(9);

        $value2 = Cache::decrement('decrement.test', 3);
        expect($value2)->toBe(6);
    });

    it('can flush all cache', function () {
        Cache::put('flush.test.1', 'value1', 300);
        Cache::put('flush.test.2', 'value2', 300);

        expect(Cache::has('flush.test.1'))->toBeTrue();
        expect(Cache::has('flush.test.2'))->toBeTrue();

        $result = Cache::flush();
        expect($result)->toBeTrue();

        expect(Cache::has('flush.test.1'))->toBeFalse();
        expect(Cache::has('flush.test.2'))->toBeFalse();
    });

    it('can get cache statistics', function () {
        Cache::put('stats.test.1', 'value1', 300);
        Cache::put('stats.test.2', 'value2', 300);

        $stats = Cache::stats();

        expect($stats)->toBeArray();
        expect($stats)->toHaveKey('total_files');
        expect($stats)->toHaveKey('total_size');
        expect($stats)->toHaveKey('cache_path');
        expect($stats['total_files'])->toBeGreaterThanOrEqual(2);
    });

    it('can remember forever', function () {
        $callCount = 0;

        $value = Cache::rememberForever('forever.remember', function () use (&$callCount) {
            $callCount++;
            return 'forever computed';
        });

        expect($value)->toBe('forever computed');
        expect($callCount)->toBe(1);

        // Segunda chamada não deve executar closure
        $value2 = Cache::rememberForever('forever.remember', function () use (&$callCount) {
            $callCount++;
            return 'forever computed';
        });

        expect($value2)->toBe('forever computed');
        expect($callCount)->toBe(1);
    });
});
