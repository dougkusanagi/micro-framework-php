<?php

namespace GuepardoSys\Core\Cache;

use GuepardoSys\Core\Cache\CacheManager;

/**
 * Cache Facade
 * 
 * Provides a simple static interface to the cache system
 */
class CacheFacade
{
    private static ?CacheManager $manager = null;

    /**
     * Get the cache manager instance
     */
    private static function getManager(): CacheManager
    {
        if (self::$manager === null) {
            self::$manager = CacheManager::getInstance();
        }

        return self::$manager;
    }

    /**
     * Store an item in the cache
     */
    public static function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return self::getManager()->put($key, $value, $ttl);
    }

    /**
     * Get an item from the cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getManager()->get($key, $default);
    }

    /**
     * Store an item in the cache if it doesn't exist, or return existing value
     */
    public static function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::put($key, $value, $ttl);

        return $value;
    }

    /**
     * Store an item in the cache forever
     */
    public static function forever(string $key, mixed $value): bool
    {
        return self::getManager()->forever($key, $value);
    }

    /**
     * Remove an item from the cache
     */
    public static function forget(string $key): bool
    {
        return self::getManager()->forget($key);
    }

    /**
     * Clear all cache
     */
    public static function flush(): bool
    {
        return self::getManager()->flush();
    }

    /**
     * Check if an item exists in the cache
     */
    public static function has(string $key): bool
    {
        return self::getManager()->has($key);
    }

    /**
     * Increment a value in the cache
     */
    public static function increment(string $key, int $value = 1): mixed
    {
        return self::getManager()->increment($key, $value);
    }

    /**
     * Decrement a value in the cache
     */
    public static function decrement(string $key, int $value = 1): mixed
    {
        return self::getManager()->decrement($key, $value);
    }

    /**
     * Get cache with tags
     */
    public static function tags(array $tags): TaggedCache
    {
        return self::getManager()->tags($tags);
    }
}
