<?php

namespace GuepardoSys\Core\Cache;

/**
 * Cache Repository Interface - Exatamente como no Laravel
 */
interface Repository
{
    /**
     * Retrieve an item from the cache by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Retrieve multiple items from the cache by key.
     */
    public function many(array $keys): array;

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Store multiple items in the cache for a given number of seconds.
     */
    public function putMany(array $values, ?int $ttl = null): bool;

    /**
     * Increment the value of an item in the cache.
     */
    public function increment(string $key, int $value = 1): int|bool;

    /**
     * Decrement the value of an item in the cache.
     */
    public function decrement(string $key, int $value = 1): int|bool;

    /**
     * Store an item in the cache indefinitely.
     */
    public function forever(string $key, mixed $value): bool;

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public function remember(string $key, ?int $ttl, \Closure $callback): mixed;

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     */
    public function rememberForever(string $key, \Closure $callback): mixed;

    /**
     * Remove an item from the cache.
     */
    public function forget(string $key): bool;

    /**
     * Remove all items from the cache.
     */
    public function flush(): bool;

    /**
     * Get the cache key prefix.
     */
    public function getPrefix(): string;
}
