<?php

namespace GuepardoSys\Core\Security;

/**
 * Security Headers Manager
 * Helps set security-related HTTP headers
 */
class SecurityHeaders
{
    /**
     * Set all recommended security headers
     */
    public static function setAll(): void
    {
        self::setXFrameOptions();
        self::setXContentTypeOptions();
        self::setXSSProtection();
        self::setReferrerPolicy();
        self::setContentSecurityPolicy();
        self::setStrictTransportSecurity();
        self::setPermissionsPolicy();
    }

    /**
     * Set X-Frame-Options header to prevent clickjacking
     */
    public static function setXFrameOptions(string $value = 'DENY'): void
    {
        header("X-Frame-Options: {$value}");
    }

    /**
     * Set X-Content-Type-Options header to prevent MIME sniffing
     */
    public static function setXContentTypeOptions(): void
    {
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Set X-XSS-Protection header (legacy but still useful)
     */
    public static function setXSSProtection(): void
    {
        header('X-XSS-Protection: 1; mode=block');
    }

    /**
     * Set Referrer-Policy header
     */
    public static function setReferrerPolicy(string $policy = 'strict-origin-when-cross-origin'): void
    {
        header("Referrer-Policy: {$policy}");
    }

    /**
     * Set Content Security Policy header
     */
    public static function setContentSecurityPolicy(array $directives = []): void
    {
        $defaultDirectives = [
            "default-src" => "'self'",
            "script-src" => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com",
            "style-src" => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src" => "'self' https://fonts.gstatic.com",
            "img-src" => "'self' data: https:",
            "connect-src" => "'self'",
            "frame-ancestors" => "'none'"
        ];

        $directives = array_merge($defaultDirectives, $directives);

        $cspValue = [];
        foreach ($directives as $directive => $value) {
            $cspValue[] = "{$directive} {$value}";
        }

        header('Content-Security-Policy: ' . implode('; ', $cspValue));
    }

    /**
     * Set Strict-Transport-Security header (HTTPS only)
     */
    public static function setStrictTransportSecurity(int $maxAge = 31536000, bool $includeSubDomains = true): void
    {
        if (self::isHttps()) {
            $value = "max-age={$maxAge}";
            if ($includeSubDomains) {
                $value .= '; includeSubDomains';
            }
            header("Strict-Transport-Security: {$value}");
        }
    }

    /**
     * Set Permissions-Policy header
     */
    public static function setPermissionsPolicy(array $policies = []): void
    {
        $defaultPolicies = [
            'geolocation' => '()',
            'microphone' => '()',
            'camera' => '()',
            'payment' => '()',
            'usb' => '()',
            'magnetometer' => '()',
            'accelerometer' => '()',
            'gyroscope' => '()'
        ];

        $policies = array_merge($defaultPolicies, $policies);

        $policyValue = [];
        foreach ($policies as $feature => $allowlist) {
            $policyValue[] = "{$feature}={$allowlist}";
        }

        header('Permissions-Policy: ' . implode(', ', $policyValue));
    }

    /**
     * Set CORS headers
     */
    public static function setCORS(array $options = []): void
    {
        $defaults = [
            'origin' => '*',
            'methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'headers' => 'Content-Type, Authorization, X-Requested-With',
            'credentials' => false,
            'max_age' => 86400
        ];

        $options = array_merge($defaults, $options);

        header("Access-Control-Allow-Origin: {$options['origin']}");
        header("Access-Control-Allow-Methods: {$options['methods']}");
        header("Access-Control-Allow-Headers: {$options['headers']}");

        if ($options['credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }

        header("Access-Control-Max-Age: {$options['max_age']}");
    }

    /**
     * Check if request is HTTPS
     */
    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            ($_SERVER['SERVER_PORT'] ?? 80) == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Remove server information headers
     */
    public static function removeServerHeaders(): void
    {
        header_remove('X-Powered-By');
        header_remove('Server');

        // Set custom server header
        header('Server: GuepardoSys');
    }

    /**
     * Set security headers for API responses
     */
    public static function setApiHeaders(): void
    {
        self::setXContentTypeOptions();
        self::setXFrameOptions();
        self::removeServerHeaders();

        // Set JSON content type
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Set cache control headers
     */
    public static function setCacheControl(string $directive = 'no-cache, no-store, must-revalidate'): void
    {
        header("Cache-Control: {$directive}");
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Alias methods for common naming conventions
     */
    public static function contentSecurityPolicy(string $policy): void
    {
        $directives = [];
        // Parse policy string into directives array
        $parts = explode(';', $policy);
        foreach ($parts as $part) {
            $part = trim($part);
            if (!empty($part)) {
                $tokens = explode(' ', $part, 2);
                $directive = trim($tokens[0]);
                $value = isset($tokens[1]) ? trim($tokens[1]) : '';
                $directives[$directive] = $value;
            }
        }
        self::setContentSecurityPolicy($directives);
    }

    public static function xFrameOptions(string $value = 'DENY'): void
    {
        self::setXFrameOptions($value);
    }

    public static function xContentTypeOptions(): void
    {
        self::setXContentTypeOptions();
    }

    public static function xXssProtection(): void
    {
        self::setXSSProtection();
    }

    public static function referrerPolicy(string $policy = 'strict-origin-when-cross-origin'): void
    {
        self::setReferrerPolicy($policy);
    }

    public static function strictTransportSecurity(int $maxAge = 31536000, bool $includeSubDomains = true, bool $preload = false): void
    {
        self::setStrictTransportSecurity($maxAge, $includeSubDomains);
    }

    public static function featurePolicy(string $policy): void
    {
        // Feature-Policy is deprecated in favor of Permissions-Policy
        header("Feature-Policy: $policy");
    }

    public static function permissionsPolicy(string $policy): void
    {
        header("Permissions-Policy: $policy");
    }

    public static function removeServerHeader(): void
    {
        self::removeServerHeaders();
    }

    public static function custom(string $name, string $value): void
    {
        header("$name: $value");
    }

    public static function setForDevelopment(): void
    {
        // Development-friendly security headers
        self::setXFrameOptions('SAMEORIGIN');
        self::setXContentTypeOptions();
        self::setXSSProtection();
        self::setReferrerPolicy('strict-origin-when-cross-origin');
    }

    public static function setForProduction(): void
    {
        // Production security headers
        self::setAll();
        self::setStrictTransportSecurity();
    }
}
