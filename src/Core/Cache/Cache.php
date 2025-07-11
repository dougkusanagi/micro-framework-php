<?php

namespace GuepardoSys\Core\Cache;

/**
 * Cache Facade - Interface EXATA do Laravel Cache
 */
class Cache
{
    /**
     * Get the cache manager instance.
     */
    private static function manager(): CacheManager
    {
        return CacheManager::getInstance();
    }

    /**
     * Get a cache store instance by name.
     */
    public static function store(?string $name = null): Repository
    {
        return self::manager()->store($name);
    }

    /**
     * Retrieve an item from the cache by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::manager()->get($key, $default);
    }

    /**
     * Retrieve multiple items from the cache by key.
     */
    public static function many(array $keys): array
    {
        return self::manager()->many($keys);
    }

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public static function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        return self::manager()->put($key, $value, $ttl);
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     */
    public static function putMany(array $values, ?int $ttl = null): bool
    {
        return self::manager()->putMany($values, $ttl);
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     */
    public static function add(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!self::has($key)) {
            return self::put($key, $value, $ttl);
        }
        return false;
    }

    /**
     * Increment the value of an item in the cache.
     */
    public static function increment(string $key, int $value = 1): int|bool
    {
        return self::manager()->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     */
    public static function decrement(string $key, int $value = 1): int|bool
    {
        return self::manager()->decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     */
    public static function forever(string $key, mixed $value): bool
    {
        return self::manager()->forever($key, $value);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(string $key, ?int $ttl, \Closure $callback): mixed
    {
        return self::manager()->remember($key, $ttl, $callback);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     */
    public static function rememberForever(string $key, \Closure $callback): mixed
    {
        return self::manager()->rememberForever($key, $callback);
    }

    /**
     * Remove an item from the cache.
     */
    public static function forget(string $key): bool
    {
        return self::manager()->forget($key);
    }

    /**
     * Remove multiple items from the cache.
     */
    public static function forgetMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            $success = self::forget($key) && $success;
        }
        return $success;
    }

    /**
     * Remove all items from the cache.
     */
    public static function flush(): bool
    {
        return self::manager()->flush();
    }

    /**
     * Determine if an item exists in the cache.
     */
    public static function has(string $key): bool
    {
        return self::manager()->has($key);
    }

    /**
     * Determine if an item doesn't exist in the cache.
     */
    public static function missing(string $key): bool
    {
        return !self::has($key);
    }

    /**
     * Get an item from the cache and remove it.
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        return self::manager()->pull($key, $default);
    }

    /**
     * Get the cache key prefix.
     */
    public static function getPrefix(): string
    {
        return self::manager()->getPrefix();
    }

    /**
     * Begin executing a new tags operation if the store supports it.
     */
    public static function tags(array $names): TaggedCache
    {
        return new TaggedCache(self::manager(), $names);
    }

    /**
     * Get cache statistics (extension method).
     */
    public static function stats(): array
    {
        return self::manager()->stats();
    }

    /**
     * Clean expired cache entries (extension method).
     */
    public static function cleanExpired(): int
    {
        return self::manager()->cleanExpired();
    }
}
