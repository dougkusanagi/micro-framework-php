<?php

namespace App\Models;

use GuepardoSys\Core\BaseModel;

/**
 * Product Model
 */
class Product extends BaseModel
{
    protected string $table = 'products';
    
    protected array $fillable = [
        // TODO: Add fillable fields
        'name',
        'created_at',
        'updated_at'
    ];

    /**
     * Get all active records
     *
     * @return array
     */
    public static function active(): array
    {
        $instance = new static();
        $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE active = 1");
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $model = new static();
            $model->attributes = $row;
            $results[] = $model;
        }
        
        return $results;
    }

    /**
     * Validate model data
     *
     * @param array $data
     * @return array Array of validation errors
     */
    public static function validate(array $data): array
    {
        $errors = [];

        // TODO: Add validation rules
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        return $errors;
    }

    /**
     * Override create to handle timestamps
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data): static
    {
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
        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($data);
    }
}
