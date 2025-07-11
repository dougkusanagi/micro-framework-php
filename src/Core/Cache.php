<?php

namespace GuepardoSys\Core;

use GuepardoSys\Core\Cache\CacheFacade;

/**
 * Legacy Cache class for backward compatibility
 */
class Cache
{
    /**
     * Get default cache store
     */
    public static function store(): CacheFacade
    {
        return new CacheFacade();
    }

    /**
     * Put an item in the cache
     */
    public static function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return CacheFacade::put($key, $value, $ttl);
    }

    /**
     * Get an item from the cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return CacheFacade::get($key, $default);
    }

    /**
     * Remember an item in cache
     */
    public static function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return CacheFacade::remember($key, $callback, $ttl);
    }

    /**
     * Remove an item from cache
     */
    public static function forget(string $key): bool
    {
        return CacheFacade::forget($key);
    }

    /**
     * Clear all cache
     */
    public static function flush(): bool
    {
        return CacheFacade::flush();
    }

    /**
     * Check if key exists
     */
    public static function has(string $key): bool
    {
        return CacheFacade::has($key);
    }
}
