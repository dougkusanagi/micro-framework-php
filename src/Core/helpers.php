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
