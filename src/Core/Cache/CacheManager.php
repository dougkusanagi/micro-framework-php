<?php

namespace GuepardoSys\Core\Cache;

/**
 * Cache Manager - Interface exata do Laravel
 */
class CacheManager
{
    private static ?CacheManager $instance = null;
    private array $stores = [];
    private string $defaultStore = 'file';
    private array $config = [];

    private function __construct()
    {
        $this->config = [
            'default' => env('CACHE_DRIVER', 'file'),
            'stores' => [
                'file' => [
                    'driver' => 'file',
                    'path' => STORAGE_PATH . '/cache/data',
                    'prefix' => env('CACHE_PREFIX', 'guepardo_cache'),
                ],
            ],
            'prefix' => env('CACHE_PREFIX', 'guepardo_cache'),
        ];
    }

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
     * Get a cache store instance by name.
     */
    public function store(?string $name = null): Repository
    {
        $name = $name ?? $this->getDefaultDriver();

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->resolve($name);
        }

        return $this->stores[$name];
    }

    /**
     * Get the default cache driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config['default'];
    }

    /**
     * Set the default cache driver name.
     */
    public function setDefaultDriver(string $name): void
    {
        $this->config['default'] = $name;
    }

    /**
     * Resolve the given store.
     */
    protected function resolve(string $name): Repository
    {
        $config = $this->config['stores'][$name] ?? [];

        if (empty($config)) {
            throw new \InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        switch ($config['driver']) {
            case 'file':
                return new FileStore(
                    $config['path'] ?? null,
                    $config['prefix'] ?? ''
                );

            default:
                throw new \InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Dynamically call the default driver instance.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}
