<?php

namespace GuepardoSys\Core\Cache;

use GuepardoSys\Core\Cache;

/**
 * Cache Manager - Singleton para gerenciar instâncias de cache
 * Similar ao Cache Manager do Laravel
 */
class CacheManager
{
    private static ?CacheManager $instance = null;
    private array $stores = [];
    private string $defaultStore = 'file';

    private function __construct() {}

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get cache store
     */
    public function store(?string $name = null): Cache
    {
        $name = $name ?? $this->defaultStore;

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->createStore($name);
        }

        return $this->stores[$name];
    }

    /**
     * Create cache store
     */
    private function createStore(string $name): Cache
    {
        switch ($name) {
            case 'file':
            default:
                $cachePath = STORAGE_PATH . '/cache/data';
                $defaultTtl = (int)(env('CACHE_TTL', 3600));
                $compression = (bool)(env('CACHE_COMPRESSION', true));

                return new Cache($cachePath, $defaultTtl, $compression);
        }
    }

    /**
     * Set default store
     */
    public function setDefaultStore(string $store): void
    {
        $this->defaultStore = $store;
    }

    /**
     * Forward calls to default store
     */
    public function __call(string $method, array $arguments)
    {
        return $this->store()->{$method}(...$arguments);
    }
}
