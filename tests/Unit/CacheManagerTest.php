<?php

use GuepardoSys\Core\Cache\CacheManager;
use GuepardoSys\Core\Cache\CacheFacade;
use GuepardoSys\Core\Cache;

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

        expect($store)->toBeInstanceOf(Cache::class);
    });

    it('can forward calls to default store', function () {
        $manager = CacheManager::getInstance();

        $result = $manager->put('test.key', 'test value', 300);
        expect($result)->toBeTrue();

        $value = $manager->get('test.key');
        expect($value)->toBe('test value');
    });

    it('can set default store', function () {
        $manager = CacheManager::getInstance();
        $manager->setDefaultStore('file');

        // Should not throw error
        $store = $manager->store();
        expect($store)->toBeInstanceOf(Cache::class);
    });
});

describe('Cache Facade', function () {
    beforeEach(function () {
        // Limpar cache antes de cada teste
        @array_map('unlink', glob(STORAGE_PATH . '/cache/data/*'));
    });

    it('can put and get values', function () {
        $result = CacheFacade::put('facade.test', 'facade value', 300);
        expect($result)->toBeTrue();

        $value = CacheFacade::get('facade.test');
        expect($value)->toBe('facade value');
    });

    it('can check if key exists', function () {
        CacheFacade::put('exists.test', 'value', 300);

        expect(CacheFacade::has('exists.test'))->toBeTrue();
        expect(CacheFacade::has('not.exists'))->toBeFalse();
    });

    it('can forget values', function () {
        CacheFacade::put('forget.test', 'value', 300);
        expect(CacheFacade::has('forget.test'))->toBeTrue();

        $result = CacheFacade::forget('forget.test');
        expect($result)->toBeTrue();
        expect(CacheFacade::has('forget.test'))->toBeFalse();
    });

    it('can remember values with closure', function () {
        $callCount = 0;

        $value1 = CacheFacade::remember('remember.test', function () use (&$callCount) {
            $callCount++;
            return 'computed value';
        }, 300);

        $value2 = CacheFacade::remember('remember.test', function () use (&$callCount) {
            $callCount++;
            return 'computed value';
        }, 300);

        expect($value1)->toBe('computed value');
        expect($value2)->toBe('computed value');
        expect($callCount)->toBe(1); // Closure deve ser chamada apenas uma vez
    });

    it('can store forever', function () {
        $result = CacheFacade::forever('forever.test', 'forever value');
        expect($result)->toBeTrue();

        $value = CacheFacade::get('forever.test');
        expect($value)->toBe('forever value');
    });

    it('can pull values (get and remove)', function () {
        CacheFacade::put('pull.test', 'pull value', 300);

        $value = CacheFacade::pull('pull.test');
        expect($value)->toBe('pull value');

        // Deve ter sido removido
        expect(CacheFacade::has('pull.test'))->toBeFalse();
    });

    it('can increment values', function () {
        // Primeira chamada - valor inicial 0
        $value1 = CacheFacade::increment('counter.test');
        expect($value1)->toBe(1);

        // Segunda chamada - incrementa
        $value2 = CacheFacade::increment('counter.test', 5);
        expect($value2)->toBe(6);
    });

    it('can decrement values', function () {
        CacheFacade::put('decrement.test', 10, 300);

        $value1 = CacheFacade::decrement('decrement.test');
        expect($value1)->toBe(9);

        $value2 = CacheFacade::decrement('decrement.test', 3);
        expect($value2)->toBe(6);
    });

    it('can flush all cache', function () {
        CacheFacade::put('flush.test.1', 'value1', 300);
        CacheFacade::put('flush.test.2', 'value2', 300);

        expect(CacheFacade::has('flush.test.1'))->toBeTrue();
        expect(CacheFacade::has('flush.test.2'))->toBeTrue();

        $result = CacheFacade::flush();
        expect($result)->toBeTrue();

        expect(CacheFacade::has('flush.test.1'))->toBeFalse();
        expect(CacheFacade::has('flush.test.2'))->toBeFalse();
    });

    it('can get cache statistics', function () {
        CacheFacade::put('stats.test.1', 'value1', 300);
        CacheFacade::put('stats.test.2', 'value2', 300);

        $stats = CacheFacade::stats();

        expect($stats)->toBeArray();
        expect($stats)->toHaveKey('total_files');
        expect($stats)->toHaveKey('total_size');
        expect($stats)->toHaveKey('cache_path');
        expect($stats['total_files'])->toBeGreaterThanOrEqual(2);
    });

    it('can remember forever', function () {
        $callCount = 0;

        $value = CacheFacade::rememberForever('forever.remember', function () use (&$callCount) {
            $callCount++;
            return 'forever computed';
        });

        expect($value)->toBe('forever computed');
        expect($callCount)->toBe(1);

        // Segunda chamada não deve executar closure
        $value2 = CacheFacade::rememberForever('forever.remember', function () use (&$callCount) {
            $callCount++;
            return 'forever computed';
        });

        expect($value2)->toBe('forever computed');
        expect($callCount)->toBe(1);
    });
});
