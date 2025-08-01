<?php

/**
 * Get environment variable value
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

    // Handle boolean values
    if (is_string($value)) {
        $lower = strtolower($value);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }
    }

    return $value;
}

/**
 * Get configuration value
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function config(string $key, mixed $default = null): mixed
{
    static $config = [];

    // Parse key like 'database.default'
    $parts = explode('.', $key);
    $file = array_shift($parts);

    // Load config file if not already loaded
    if (!isset($config[$file])) {
        $configFile = BASE_PATH . '/config/' . $file . '.php';
        if (file_exists($configFile)) {
            $config[$file] = require $configFile;
        } else {
            $config[$file] = [];
        }
    }

    // Navigate through the config array
    $value = $config[$file];
    foreach ($parts as $part) {
        if (is_array($value) && array_key_exists($part, $value)) {
            $value = $value[$part];
        } else {
            return $default;
        }
    }

    return $value;
}

/**
 * Get logger instance
 */
if (!function_exists('logger')) {
    function logger(?string $level = null, ?string $message = null, array $context = []): mixed
    {
        static $logger = null;

        if ($logger === null) {
            $logger = new \GuepardoSys\Core\Logger();
        }

        // If no arguments, return logger instance
        if ($level === null) {
            return $logger;
        }

        // If level and message provided, log it
        if ($message !== null) {
            $logger->log($level, $message, $context);
            return null;
        }

        // If only level provided, assume it's a message with 'info' level
        $logger->info($level, $context);
        return null;
    }
}

/**
 * Generate CSRF token
 */
if (!function_exists('csrf_token')) {
    function csrf_token(string $action = 'default'): string
    {
        return \GuepardoSys\Core\Security\CsrfToken::generate($action);
    }
}

/**
 * Generate CSRF field for forms
 */
if (!function_exists('csrf_field')) {
    function csrf_field(string $action = 'default'): string
    {
        return \GuepardoSys\Core\Security\CsrfToken::field($action);
    }
}

/**
 * Verify CSRF token
 */
if (!function_exists('csrf_verify')) {
    function csrf_verify(string $token, string $action = 'default'): bool
    {
        return \GuepardoSys\Core\Security\CsrfToken::verify($token, $action);
    }
}

/**
 * Validate data
 */
if (!function_exists('validate')) {
    function validate(array $data, array $rules): \GuepardoSys\Core\Security\Validator
    {
        return \GuepardoSys\Core\Security\Validator::make($data, $rules);
    }
}

/**
 * Sanitize data
 */
if (!function_exists('sanitize')) {
    function sanitize(array $data): array
    {
        return \GuepardoSys\Core\Security\Validator::sanitize_array($data);
    }
}

/**
 * Cache helpers - Interface EXATA do Laravel
 */

/**
 * Get value from cache or cache manager instance.
 */
if (!function_exists('cache')) {
    function cache(?string $key = null, mixed $value = null, ?int $ttl = null): mixed
    {
        $cache = \GuepardoSys\Core\Cache\Cache::class;

        // Se não passou parâmetros, retorna o manager
        if ($key === null) {
            return \GuepardoSys\Core\Cache\CacheManager::getInstance();
        }

        // Se passou apenas a chave, busca o valor
        if ($value === null && $ttl === null) {
            return $cache::get($key);
        }

        // Se passou chave e valor, armazena
        return $cache::put($key, $value, $ttl);
    }
}

/**
 * Get an item from the cache, or execute the given Closure and store the result.
 * Interface EXATA do Laravel: remember($key, $ttl, $callback)
 */
if (!function_exists('cache_remember')) {
    function cache_remember(string $key, ?int $ttl, \Closure $callback): mixed
    {
        return \GuepardoSys\Core\Cache\Cache::remember($key, $ttl, $callback);
    }
}

/**
 * Store an item in the cache indefinitely.
 */
if (!function_exists('cache_forever')) {
    function cache_forever(string $key, mixed $value): bool
    {
        return \GuepardoSys\Core\Cache\Cache::forever($key, $value);
    }
}

/**
 * Remove an item from the cache.
 */
if (!function_exists('cache_forget')) {
    function cache_forget(string $key): bool
    {
        return \GuepardoSys\Core\Cache\Cache::forget($key);
    }
}

/**
 * Remove all items from the cache.
 */
if (!function_exists('cache_flush')) {
    function cache_flush(): bool
    {
        return \GuepardoSys\Core\Cache\Cache::flush();
    }
}

/**
 * Begin executing a new tags operation.
 */
if (!function_exists('cache_tags')) {
    function cache_tags(array $tags): \GuepardoSys\Core\Cache\TaggedCache
    {
        return \GuepardoSys\Core\Cache\Cache::tags($tags);
    }
}

/**
 * Hash the given value against the bcrypt algorithm.
 *
 * @param  string  $value
 * @param  array  $options
 * @return string
 */
if (!function_exists('bcrypt')) {
    function bcrypt(string $value, array $options = []): string
    {
        return \GuepardoSys\Core\Security\Hash::make($value, $options);
    }
}

// Include View helpers
require_once __DIR__ . '/View/helpers.php';
