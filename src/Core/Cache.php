<?php

namespace GuepardoSys\Core;

use Exception;

/**
 * Simple file-based cache system for GuepardoSys
 * Supports TTL, compression, and efficient file storage
 */
class Cache
{
    private string $cachePath;
    private int $defaultTtl;
    private bool $compression;

    public function __construct(?string $cachePath = null, int $defaultTtl = 3600, bool $compression = true)
    {
        $this->cachePath = $cachePath ?? STORAGE_PATH . '/cache/data';
        $this->defaultTtl = $defaultTtl;
        $this->compression = $compression;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Store data in cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $expiration = time() + $ttl;

        $data = [
            'value' => $value,
            'expires_at' => $expiration,
            'created_at' => time()
        ];

        $serialized = serialize($data);

        // Compress if enabled and data is large enough
        if ($this->compression && strlen($serialized) > 1024) {
            $serialized = gzcompress($serialized, 6);
            $isCompressed = true;
        } else {
            $isCompressed = false;
        }

        $cacheFile = $this->getCacheFile($key);

        // Add metadata header
        $content = json_encode(['compressed' => $isCompressed]) . "\n" . $serialized;

        return file_put_contents($cacheFile, $content, LOCK_EX) !== false;
    }

    /**
     * Retrieve data from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheFile = $this->getCacheFile($key);

        if (!file_exists($cacheFile)) {
            return $default;
        }

        $content = file_get_contents($cacheFile);
        if ($content === false) {
            return $default;
        }

        // Parse metadata
        $lines = explode("\n", $content, 2);
        if (count($lines) !== 2) {
            return $default;
        }

        $metadata = json_decode($lines[0], true);
        $data = $lines[1];

        // Decompress if needed
        if ($metadata['compressed'] ?? false) {
            $data = gzuncompress($data);
            if ($data === false) {
                return $default;
            }
        }

        $unserialized = unserialize($data);
        if ($unserialized === false) {
            return $default;
        }

        // Check expiration
        if ($unserialized['expires_at'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $unserialized['value'];
    }

    /**
     * Check if cache key exists and is not expired
     */
    public function has(string $key): bool
    {
        return $this->get($key, '__CACHE_MISS__') !== '__CACHE_MISS__';
    }

    /**
     * Remove item from cache
     */
    public function forget(string $key): bool
    {
        $cacheFile = $this->getCacheFile($key);

        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }

        return true;
    }

    /**
     * Get or store data using a closure
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
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
     * Clear all cache
     */
    public function flush(): bool
    {
        $files = glob($this->cachePath . '/*');
        $success = true;

        foreach ($files as $file) {
            if (is_file($file)) {
                $success = unlink($file) && $success;
            }
        }

        return $success;
    }

    /**
     * Clean expired cache entries
     */
    public function cleanExpired(): int
    {
        $files = glob($this->cachePath . '/*');
        $cleaned = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $lines = explode("\n", $content, 2);
            if (count($lines) !== 2) {
                continue;
            }

            $metadata = json_decode($lines[0], true);
            $data = $lines[1];

            // Decompress if needed
            if ($metadata['compressed'] ?? false) {
                $data = gzuncompress($data);
                if ($data === false) {
                    continue;
                }
            }

            $unserialized = unserialize($data);
            if ($unserialized === false) {
                continue;
            }

            // Remove if expired
            if ($unserialized['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Get cache statistics
     */
    public function stats(): array
    {
        $files = glob($this->cachePath . '/*');
        $totalSize = 0;
        $totalFiles = 0;
        $expiredFiles = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $totalFiles++;
            $totalSize += filesize($file);

            // Check if expired (simplified check)
            $mtime = filemtime($file);
            if ($mtime < time() - $this->defaultTtl) {
                $expiredFiles++;
            }
        }

        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'expired_files' => $expiredFiles,
            'cache_path' => $this->cachePath
        ];
    }

    /**
     * Generate cache file path
     */
    private function getCacheFile(string $key): string
    {
        $hash = hash('sha256', $key);
        return $this->cachePath . '/' . $hash . '.cache';
    }
}
