<?php

namespace GuepardoSys\Core\Cache;

/**
 * Tagged Cache Store - Interface EXATA do Laravel
 */
class TaggedCache implements Repository
{
    private CacheManager $manager;
    private array $tags;

    public function __construct(CacheManager $manager, array $tags)
    {
        $this->manager = $manager;
        $this->tags = $tags;
    }

    /**
     * Retrieve an item from the cache by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->manager->get($this->taggedKey($key), $default);
    }

    /**
     * Retrieve multiple items from the cache by key.
     */
    public function many(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Store the key in tag index
        foreach ($this->tags as $tag) {
            $this->addKeyToTag($tag, $key);
        }

        return $this->manager->put($this->taggedKey($key), $value, $ttl);
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     */
    public function putMany(array $values, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $this->put($key, $value, $ttl) && $success;
        }
        return $success;
    }

    /**
     * Increment the value of an item in the cache.
     */
    public function increment(string $key, int $value = 1): int|bool
    {
        $taggedKey = $this->taggedKey($key);
        return $this->manager->increment($taggedKey, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     */
    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->increment($key, -$value);
    }

    /**
     * Store an item in the cache indefinitely.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 86400 * 365);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public function remember(string $key, ?int $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     */
    public function rememberForever(string $key, \Closure $callback): mixed
    {
        return $this->remember($key, 86400 * 365, $callback);
    }

    /**
     * Remove an item from the cache.
     */
    public function forget(string $key): bool
    {
        return $this->manager->forget($this->taggedKey($key));
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): bool
    {
        $success = true;

        foreach ($this->tags as $tag) {
            $keys = $this->getTagKeys($tag);

            foreach ($keys as $key) {
                $taggedKey = $this->taggedKey($key);
                $success = $this->manager->forget($taggedKey) && $success;
            }

            // Clear tag index
            $this->manager->forget("tag_index:{$tag}");
        }

        return $success;
    }

    /**
     * Get the cache key prefix.
     */
    public function getPrefix(): string
    {
        return $this->manager->getPrefix();
    }

    /**
     * Determine if an item exists in the cache.
     */
    public function has(string $key): bool
    {
        return $this->manager->has($this->taggedKey($key));
    }

    /**
     * Get an item from the cache and remove it.
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        if ($value !== $default) {
            $this->forget($key);
        }
        return $value;
    }

    /**
     * Get tagged cache key
     */
    private function taggedKey(string $key): string
    {
        $tagKey = implode(':', $this->tags);
        return "tagged:{$tagKey}:{$key}";
    }

    /**
     * Add key to tag index
     */
    private function addKeyToTag(string $tag, string $key): void
    {
        $indexKey = "tag_index:{$tag}";
        $keys = $this->manager->get($indexKey, []);

        if (!in_array($key, $keys)) {
            $keys[] = $key;
            $this->manager->put($indexKey, $keys, 86400 * 7); // 1 week
        }
    }

    /**
     * Get all keys for a tag
     */
    private function getTagKeys(string $tag): array
    {
        return $this->manager->get("tag_index:{$tag}", []);
    }
}
