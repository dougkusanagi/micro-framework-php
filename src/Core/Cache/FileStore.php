<?php

namespace GuepardoSys\Core\Cache;

class FileStore implements Repository
{
    private string $cachePath;
    private string $prefix;
    private int $defaultTtl;

    public function __construct(?string $path = null, string $prefix = '')
    {
        $this->cachePath = $path ?? STORAGE_PATH . '/cache/data';
        $this->prefix = $prefix;
        $this->defaultTtl = (int)(env('CACHE_TTL', 3600));

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $filepath = $this->getFilePath($key);

        if (!file_exists($filepath)) {
            return $default;
        }

        $data = json_decode(file_get_contents($filepath), true);
        if (!$data || !isset($data['expires_at'], $data['value'])) {
            return $default;
        }

        if ($data['expires_at'] && time() > $data['expires_at']) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    public function many(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $expiresAt = $ttl > 0 ? time() + $ttl : null;

        $data = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time()
        ];

        return file_put_contents($this->getFilePath($key), json_encode($data), LOCK_EX) !== false;
    }

    public function putMany(array $values, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->put($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function increment(string $key, int $value = 1): int|bool
    {
        $current = (int)$this->get($key, 0);
        $new = $current + $value;
        return $this->put($key, $new) ? $new : false;
    }

    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->increment($key, -$value);
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, null);
    }

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

    public function rememberForever(string $key, \Closure $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->forever($key, $value);
        return $value;
    }

    public function forget(string $key): bool
    {
        $filepath = $this->getFilePath($key);
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true;
    }

    public function flush(): bool
    {
        $files = glob($this->cachePath . '/*');
        if ($files === false) {
            return true;
        }

        $success = true;
        foreach ($files as $file) {
            if (is_file($file) && !unlink($file)) {
                $success = false;
            }
        }
        return $success;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    private function getFilePath(string $key): string
    {
        $hashedKey = hash('sha256', $this->prefixKey($key));
        return $this->cachePath . '/' . $hashedKey . '.cache';
    }

    private function prefixKey(string $key): string
    {
        return $this->prefix ? $this->prefix . ':' . $key : $key;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    public function forgetMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->forget($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function stats(): array
    {
        $files = glob($this->cachePath . '/*') ?: [];
        $totalSize = 0;
        $valid = 0;
        $expired = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $totalSize += $size;

                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['expires_at'])) {
                    if ($data['expires_at'] && time() > $data['expires_at']) {
                        $expired++;
                    } else {
                        $valid++;
                    }
                } else {
                    $valid++;
                }
            }
        }

        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'valid_entries' => $valid,
            'expired_entries' => $expired,
            'cache_path' => $this->cachePath
        ];
    }

    public function cleanExpired(): int
    {
        $files = glob($this->cachePath . '/*') ?: [];
        $cleaned = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $data = json_decode(file_get_contents($file), true);
                if (
                    $data && isset($data['expires_at']) &&
                    $data['expires_at'] && time() > $data['expires_at']
                ) {
                    if (unlink($file)) {
                        $cleaned++;
                    }
                }
            }
        }

        return $cleaned;
    }
}
