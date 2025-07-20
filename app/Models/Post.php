<?php
namespace App\Models;

use GuepardoSys\Core\BaseModel;

class Post extends BaseModel
{
    protected string $table = 'posts';
    protected array $fillable = ['title', 'slug', 'content', 'user_id', 'category_id', 'created_at', 'updated_at'];

    /**
     * Get the author of the post
     */
    public function author()
    {
        return User::find($this->user_id);
    }

    /**
     * Get the category of the post
     */
    public function category()
    {
        return Category::find($this->category_id);
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return trim($slug, '-');
    }

    /**
     * Override create to handle timestamps and slug generation
     */
    public static function create(array $data): static
    {
        // Generate slug if not provided
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = self::generateSlug($data['title']);
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
        // Generate slug if title is updated
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = self::generateSlug($data['title']);
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($data);
    }

    /**
     * Get excerpt of content
     */
    public function getExcerpt(int $length = 150): string
    {
        $content = strip_tags($this->content);
        if (strlen($content) <= $length) {
            return $content;
        }
        return substr($content, 0, $length) . '...';
    }

    /**
     * Validate post data
     */
    public static function validate(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($data['title']) < 3) {
            $errors['title'] = 'Title must be at least 3 characters long';
        }

        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        } elseif (strlen($data['content']) < 10) {
            $errors['content'] = 'Content must be at least 10 characters long';
        }

        if (empty($data['category_id'])) {
            $errors['category_id'] = 'Category is required';
        }

        return $errors;
    }
} 