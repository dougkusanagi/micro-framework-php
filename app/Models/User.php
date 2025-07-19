<?php

namespace App\Models;

use GuepardoSys\Core\BaseModel;

/**
 * User Model
 */
class User extends BaseModel
{
    protected string $table = 'users';
    protected array $fillable = [
        'name',
        'email',
        'password',
        'created_at',
        'updated_at'
    ];

    /**
     * Find user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email): ?static
    {
        return self::where('email', $email)->firstInstance();
    }

    /**
     * Hash password before saving
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password'] ?? '');
    }

    /**
     * Get user's display name
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->attributes['name'] ?? 'Anonymous';
    }

    /**
     * Override create to handle timestamps and password hashing
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data): static
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    /**
     * Override update to handle timestamps
     *
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($data);
    }

    /**
     * Validate user data
     *
     * @param array $data
     * @return array Array of validation errors
     */
    public static function validate(array $data): array
    {
        $errors = [];

        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            // Check if email already exists
            $existingUser = self::findByEmail($data['email']);
            if ($existingUser) {
                $errors['email'] = 'Email already exists';
            }
        }

        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters long';
        }

        return $errors;
    }
}
