<?php
namespace App\Models;

use GuepardoSys\Core\BaseModel;

class Category extends BaseModel
{
    protected string $table = 'categories';
    protected array $fillable = ['name', 'slug', 'created_at', 'updated_at'];

    /**
     * Get posts in this category
     */
    public function posts()
    {
        return Post::where('category_id', $this->id)->get();
    }

    /**
     * Generate slug from name
     */
    public static function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        return trim($slug, '-');
    }

    /**
     * Override create to handle timestamps and slug generation
     */
    public static function create(array $data): static
    {
        // Generate slug if not provided
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = self::generateSlug($data['name']);
        }

        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    /**
     * Override update to handle timestamps
     */
    public function update(array $data): bool
    {
        // Generate slug if name is updated
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = self::generateSlug($data['name']);
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($data);
    }

    /**
     * Validate category data
     */
    public static function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        return $errors;
    }
} 