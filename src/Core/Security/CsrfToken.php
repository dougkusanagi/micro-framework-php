<?php

namespace GuepardoSys\Core\Security;

/**
 * CSRF Token Management
 * Provides protection against Cross-Site Request Forgery attacks
 */
class CsrfToken
{
    private const TOKEN_LENGTH = 32;

    /**
     * Generate a new CSRF token
     */
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION['_token'] = $token;

        return $token;
    }

    /**
     * Validate CSRF token
     */
    public static function validate(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($token) || !isset($_SESSION['_token'])) {
            return false;
        }

        return hash_equals($_SESSION['_token'], $token);
    }

    /**
     * Get current CSRF token
     */
    public static function get(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['_token'] ?? null;
    }

    /**
     * Regenerate CSRF token
     */
    public static function regenerate(): string
    {
        self::clear();
        return self::generate();
    }

    /**
     * Check if CSRF token exists
     */
    public static function exists(): bool
    {
        return self::get() !== null;
    }

    /**
     * Clear CSRF token
     */
    public static function clear(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['_token']);
    }

    /**
     * Generate HTML hidden input for forms
     */
    public static function field(): string
    {
        $token = self::get() ?? self::generate();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Generate meta tag for AJAX requests
     */
    public static function meta(): string
    {
        $token = self::get() ?? self::generate();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate request token
     */
    public static function validateRequest(): bool
    {
        // Check for token in POST data
        if (isset($_POST['_token'])) {
            return self::validate($_POST['_token']);
        }

        // Check for token in headers
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return self::validate($_SERVER['HTTP_X_CSRF_TOKEN']);
        }

        return false;
    }
}
