<?php

namespace GuepardoSys\Core\Security;

/**
 * CSRF Token Management
 * Provides protection against Cross-Site Request Forgery attacks
 */
class CsrfToken
{
    private const TOKEN_LENGTH = 32;
    private const SESSION_KEY = '_csrf_tokens';
    private const MAX_TOKENS = 10;

    /**
     * Generate a new CSRF token
     */
    public static function generate(string $action = 'default'): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        // Initialize tokens array if not exists
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // Store token with timestamp
        $_SESSION[self::SESSION_KEY][$action] = [
            'token' => $token,
            'time' => time()
        ];

        // Clean old tokens to prevent session bloat
        self::cleanOldTokens();

        return $token;
    }

    /**
     * Verify CSRF token
     */
    public static function verify(string $token, string $action = 'default'): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY][$action])) {
            return false;
        }

        $storedData = $_SESSION[self::SESSION_KEY][$action];
        $storedToken = $storedData['token'];
        $tokenTime = $storedData['time'];

        // Check if token is expired (1 hour expiry)
        if (time() - $tokenTime > 3600) {
            unset($_SESSION[self::SESSION_KEY][$action]);
            return false;
        }

        // Use hash_equals for timing-safe comparison
        $isValid = hash_equals($storedToken, $token);

        // Remove token after use (one-time use)
        if ($isValid) {
            unset($_SESSION[self::SESSION_KEY][$action]);
        }

        return $isValid;
    }

    /**
     * Get CSRF token for an action
     */
    public static function get(string $action = 'default'): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY][$action])) {
            return null;
        }

        return $_SESSION[self::SESSION_KEY][$action]['token'];
    }

    /**
     * Generate HTML hidden input for forms
     */
    public static function field(string $action = 'default'): string
    {
        $token = self::generate($action);
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Generate meta tag for AJAX requests
     */
    public static function metaTag(string $action = 'default'): string
    {
        $token = self::generate($action);
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Clean old tokens to prevent session bloat
     */
    private static function cleanOldTokens(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $tokens = $_SESSION[self::SESSION_KEY];
        $currentTime = time();

        // Remove expired tokens
        foreach ($tokens as $action => $data) {
            if ($currentTime - $data['time'] > 3600) {
                unset($_SESSION[self::SESSION_KEY][$action]);
            }
        }

        // Limit number of tokens
        if (count($_SESSION[self::SESSION_KEY]) > self::MAX_TOKENS) {
            $tokens = $_SESSION[self::SESSION_KEY];

            // Sort by time and keep only the newest
            uasort($tokens, function ($a, $b) {
                return $b['time'] - $a['time'];
            });

            $_SESSION[self::SESSION_KEY] = array_slice($tokens, 0, self::MAX_TOKENS, true);
        }
    }

    /**
     * Verify token from request
     */
    public static function verifyRequest(array $request, string $action = 'default'): bool
    {
        $token = $request['_token'] ?? '';

        if (empty($token)) {
            return false;
        }

        return self::verify($token, $action);
    }

    /**
     * Validate a CSRF token (alias for verify)
     */
    public static function validate(string $token, string $action = 'default'): bool
    {
        return self::verify($token, $action);
    }

    /**
     * Regenerate CSRF token
     */
    public static function regenerate(string $action = 'default'): string
    {
        self::clear($action);
        return self::generate($action);
    }

    /**
     * Check if CSRF token exists
     */
    public static function exists(string $action = 'default'): bool
    {
        return self::get($action) !== null;
    }

    /**
     * Clear CSRF token
     */
    public static function clear(string $action = 'default'): void
    {
        unset($_SESSION['_token'][$action]);
    }

    /**
     * Create meta tag (alias for metaTag)
     */
    public static function meta(string $action = 'default'): string
    {
        return self::metaTag($action);
    }

    /**
     * Validate request token (alias for verifyRequest)
     */
    public static function validateRequest(array $request = null, string $action = 'default'): bool
    {
        $request = $request ?? $_REQUEST;

        // Check for token in POST data
        if (isset($request['_token'])) {
            return self::validate($request['_token'], $action);
        }

        // Check for token in headers
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return self::validate($_SERVER['HTTP_X_CSRF_TOKEN'], $action);
        }

        return false;
    }
}
