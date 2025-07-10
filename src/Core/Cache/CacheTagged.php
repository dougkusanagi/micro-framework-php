<?php

namespace GuepardoSys\Core\Cache;

/**
 * Cache with Tags - Similar ao Cache::tags() do Laravel
 */
class CacheTagged
{
    private CacheManager $cache;
    private array $tags;

    public function __construct(CacheManager $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
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
     * Get from tagged cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->taggedKey($key), $default);
    }

    /**
     * Put in tagged cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Store the key in tag index
        foreach ($this->tags as $tag) {
            $this->addKeyToTag($tag, $key);
        }

        return $this->cache->put($this->taggedKey($key), $value, $ttl);
    }

    /**
     * Remember with tags
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $taggedKey = $this->taggedKey($key);

        return $this->cache->remember($taggedKey, function () use ($key, $callback) {
            // Add to tag index when creating
            foreach ($this->tags as $tag) {
                $this->addKeyToTag($tag, $key);
            }
            return $callback();
        }, $ttl);
    }

    /**
     * Flush all cache entries with these tags
     */
    public function flush(): bool
    {
        $success = true;

        foreach ($this->tags as $tag) {
            $keys = $this->getTagKeys($tag);

            foreach ($keys as $key) {
                $taggedKey = $this->taggedKey($key);
                $success = $this->cache->forget($taggedKey) && $success;
            }

            // Clear tag index
            $this->cache->forget("tag_index:{$tag}");
        }

        return $success;
    }

    /**
     * Add key to tag index
     */
    private function addKeyToTag(string $tag, string $key): void
    {
        $indexKey = "tag_index:{$tag}";
        $keys = $this->cache->get($indexKey, []);

        if (!in_array($key, $keys)) {
            $keys[] = $key;
            $this->cache->put($indexKey, $keys, 86400 * 7); // 1 week
        }
    }

    /**
     * Get all keys for a tag
     */
    private function getTagKeys(string $tag): array
    {
        return $this->cache->get("tag_index:{$tag}", []);
    }
}
