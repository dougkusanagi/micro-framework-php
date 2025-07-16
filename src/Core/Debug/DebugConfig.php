<?php

namespace GuepardoSys\Core\Debug;

/**
 * Debug configuration manager
 * Handles all debug-related configuration options from environment variables
 */
class DebugConfig
{
    /**
     * Default configuration values
     */
    private const DEFAULTS = [
        'DEBUG_SHOW_SOURCE' => true,
        'DEBUG_CONTEXT_LINES' => 10,
        'DEBUG_MAX_STRING_LENGTH' => 1000,
        'DEBUG_HIDE_VENDOR' => true,
    ];

    /**
     * Cached configuration values
     */
    private static array $config = [];

    /**
     * Get a debug configuration value
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if not set
     * @return mixed The configuration value
     */
    public static function get(string $key, $default = null)
    {
        // Use provided default or system default
        if ($default === null && isset(self::DEFAULTS[$key])) {
            $default = self::DEFAULTS[$key];
        }

        // Return cached value if available
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        // Get value from environment
        $value = $_ENV[$key] ?? $default;

        // Convert string values to appropriate types
        $value = self::convertValue($key, $value);

        // Cache the value
        self::$config[$key] = $value;

        return $value;
    }

    /**
     * Check if source code display is enabled
     *
     * @return bool
     */
    public static function showSource(): bool
    {
        return (bool) self::get('DEBUG_SHOW_SOURCE');
    }

    /**
     * Get the number of context lines to show around errors
     *
     * @return int
     */
    public static function getContextLines(): int
    {
        return (int) self::get('DEBUG_CONTEXT_LINES');
    }

    /**
     * Get the maximum string length for output limiting
     *
     * @return int
     */
    public static function getMaxStringLength(): int
    {
        return (int) self::get('DEBUG_MAX_STRING_LENGTH');
    }

    /**
     * Check if vendor frames should be hidden in stack traces
     *
     * @return bool
     */
    public static function hideVendor(): bool
    {
        return (bool) self::get('DEBUG_HIDE_VENDOR');
    }

    /**
     * Get all debug configuration as an array
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            'DEBUG_SHOW_SOURCE' => self::showSource(),
            'DEBUG_CONTEXT_LINES' => self::getContextLines(),
            'DEBUG_MAX_STRING_LENGTH' => self::getMaxStringLength(),
            'DEBUG_HIDE_VENDOR' => self::hideVendor(),
        ];
    }

    /**
     * Convert environment variable values to appropriate types
     *
     * @param string $key The configuration key
     * @param mixed $value The raw value from environment
     * @return mixed The converted value
     */
    private static function convertValue(string $key, $value)
    {
        // Handle boolean values
        if (in_array($key, ['DEBUG_SHOW_SOURCE', 'DEBUG_HIDE_VENDOR'])) {
            if (is_string($value)) {
                return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
            }
            return (bool) $value;
        }

        // Handle integer values
        if (in_array($key, ['DEBUG_CONTEXT_LINES', 'DEBUG_MAX_STRING_LENGTH'])) {
            $intValue = (int) $value;
            
            // Apply reasonable limits
            if ($key === 'DEBUG_CONTEXT_LINES') {
                return max(0, min(50, $intValue)); // Limit between 0 and 50
            }
            
            if ($key === 'DEBUG_MAX_STRING_LENGTH') {
                return max(100, min(10000, $intValue)); // Limit between 100 and 10000
            }
            
            return $intValue;
        }

        return $value;
    }

    /**
     * Reset cached configuration (useful for testing)
     */
    public static function reset(): void
    {
        self::$config = [];
    }

    /**
     * Set a configuration value (useful for testing)
     *
     * @param string $key The configuration key
     * @param mixed $value The value to set
     */
    public static function set(string $key, $value): void
    {
        self::$config[$key] = self::convertValue($key, $value);
    }
}