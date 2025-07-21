<?php

namespace GuepardoSys\Core\Security;

class Hash
{
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public static function make(string $value, array $options = []): string
    {
        $salt = env('APP_KEY');
        $cost = $options['cost'] ?? 12;

        return password_hash($value . $salt, PASSWORD_BCRYPT, [
            'cost' => $cost,
        ]);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @return bool
     */
    public static function check(string $value, string $hashedValue): bool
    {
        $salt = env('APP_KEY');
        return password_verify($value . $salt, $hashedValue);
    }
}
