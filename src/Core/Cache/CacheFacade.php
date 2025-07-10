<?php

namespace GuepardoSys\Core\Cache;

/**
 * Cache Facade - Interface estática para o sistema de cache
 * Similar ao Cache::get(), Cache::put() do Laravel
 */
class CacheFacade
{
    /**
     * Get cache manager instance
     */
    private static function manager(): CacheManager
    {
        return CacheManager::getInstance();
    }

    /**
     * Get value from cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::manager()->get($key, $default);
    }

    /**
     * Put value in cache
     */
    public static function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        return self::manager()->put($key, $value, $ttl);
    }

    /**
     * Check if cache key exists
     */
    public static function has(string $key): bool
    {
        return self::manager()->has($key);
    }

    /**
     * Remove from cache
     */
    public static function forget(string $key): bool
    {
        return self::manager()->forget($key);
    }

    /**
     * Get or store using closure (similar ao Laravel remember)
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return self::manager()->remember($key, $callback, $ttl);
    }

    /**
     * Store forever (with very long TTL)
     */
    public static function forever(string $key, mixed $value): bool
    {
        return self::manager()->put($key, $value, 86400 * 365); // 1 year
    }

    /**
     * Get and remove from cache
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::manager()->get($key, $default);
        if ($value !== $default) {
            self::manager()->forget($key);
        }
        return $value;
    }

    /**
     * Increment cache value
     */
    public static function increment(string $key, int $value = 1): int
    {
        $current = (int)self::manager()->get($key, 0);
        $new = $current + $value;
        self::manager()->put($key, $new);
        return $new;
    }

    /**
     * Decrement cache value
     */
    public static function decrement(string $key, int $value = 1): int
    {
        return self::increment($key, -$value);
    }

    /**
     * Clear all cache
     */
    public static function flush(): bool
    {
        return self::manager()->flush();
    }

    /**
     * Get cache statistics
     */
    public static function stats(): array
    {
        return self::manager()->stats();
    }

    /**
     * Get specific store
     */
    public static function store(?string $name = null): CacheManager
    {
        return self::manager()->store($name);
    }

    /**
     * Clean expired entries
     */
    public static function cleanExpired(): int
    {
        return self::manager()->cleanExpired();
    }

    /**
     * Cache for a specific duration using a closure
     */
    public static function rememberForever(string $key, callable $callback): mixed
    {
        return self::remember($key, $callback, 86400 * 365);
    }

    /**
     * Cache a computed value with tags (simplified version)
     */
    public static function tags(array $tags): CacheTagged
    {
        return new CacheTagged(self::manager(), $tags);
    }
}
