<?php

namespace GuepardoSys\Core;

use PDO;
use PDOException;
use GuepardoSys\Core\Database;
use GuepardoSys\Core\Cache;

/**
 * Base Model Class with query caching
 */
abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $attributes = [];
    protected static ?Cache $cache = null;
    protected int $cacheTtl = 3600; // 1 hour default

    public function __construct()
    {
        $this->db = Database::getConnection();

        // Set table name by convention if not set
        if (!isset($this->table)) {
            $this->table = $this->getTableName();
        }

        // Initialize cache if not already done
        if (self::$cache === null) {
            self::$cache = new Cache();
        }
    }

    /**
     * Get table name from class name
     */
    protected function getTableName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        // Convert CamelCase to snake_case and pluralize
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));

        // Simple pluralization
        if (substr($tableName, -1) === 'y') {
            $tableName = substr($tableName, 0, -1) . 'ies';
        } elseif (substr($tableName, -1) === 's') {
            $tableName .= 'es';
        } else {
            $tableName .= 's';
        }

        return $tableName;
    }

    /**
     * Find a record by ID with caching
     *
     * @param mixed $id
     * @return static|null
     */
    public static function find($id): ?static
    {
        $instance = new static();
        $cacheKey = "model:{$instance->table}:find:{$id}";

        return self::$cache->remember($cacheKey, function () use ($instance, $id) {
            $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1");
            $stmt->execute(['id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $instance->attributes = $data;
                return $instance;
            }

            return null;
        }, $instance->cacheTtl);
    }

    /**
     * Get all records with caching
     *
     * @return array
     */
    public static function all(): array
    {
        $instance = new static();
        $cacheKey = "model:{$instance->table}:all";

        return self::$cache->remember($cacheKey, function () use ($instance) {
            $stmt = $instance->db->query("SELECT * FROM {$instance->table}");
            $results = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $model = new static();
                $model->attributes = $row;
                $results[] = $model;
            }

            return $results;
        }, $instance->cacheTtl);
    }

    /**
     * Find records by condition
     *
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public static function where(string $column, $value): array
    {
        $instance = new static();
        $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE {$column} = :value");
        $stmt->execute(['value' => $value]);
        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new static();
            $model->attributes = $row;
            $results[] = $model;
        }

        return $results;
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data): static
    {
        $instance = new static();

        // Filter only fillable fields
        $fillableData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $instance->fillable)) {
                $fillableData[$key] = $value;
            }
        }

        if (empty($fillableData)) {
            throw new \InvalidArgumentException('No fillable fields provided');
        }

        $columns = implode(', ', array_keys($fillableData));
        $placeholders = ':' . implode(', :', array_keys($fillableData));

        $sql = "INSERT INTO {$instance->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $instance->db->prepare($sql);

        if ($stmt->execute($fillableData)) {
            $instance->attributes = $fillableData;
            $instance->attributes[$instance->primaryKey] = $instance->db->lastInsertId();

            // Invalidate cache for this table
            $instance->invalidateTableCache();

            return $instance;
        }

        throw new PDOException('Failed to create record');
    }

    /**
     * Update the record
     *
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new \RuntimeException('Cannot update record without primary key');
        }

        // Filter only fillable fields
        $fillableData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $fillableData[$key] = $value;
            }
        }

        if (empty($fillableData)) {
            return false;
        }

        $setParts = [];
        foreach (array_keys($fillableData) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE {$this->primaryKey} = :id";
        $fillableData['id'] = $this->attributes[$this->primaryKey];

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($fillableData);

        if ($result) {
            $this->attributes = array_merge($this->attributes, $data);
            // Invalidate cache for this table
            $this->invalidateTableCache();
        }

        return $result;
    }

    /**
     * Delete the record
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new \RuntimeException('Cannot delete record without primary key');
        }

        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute(['id' => $this->attributes[$this->primaryKey]]);

        if ($result) {
            // Invalidate cache for this table
            $this->invalidateTableCache();
        }

        return $result;
    }

    /**
     * Get attribute value
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set attribute value
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Check if attribute exists
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get all attributes
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get JSON representation
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->attributes);
    }

    /**
     * Invalidate cache for this table
     */
    protected function invalidateTableCache(): void
    {
        $patterns = [
            "model:{$this->table}:all",
            "model:{$this->table}:find:*",
            "model:{$this->table}:where:*"
        ];

        foreach ($patterns as $pattern) {
            // For simple cache keys, remove directly
            if (strpos($pattern, '*') === false) {
                self::$cache->forget($pattern);
            } else {
                // For wildcard patterns, we need to flush the entire cache for simplicity
                // In a more advanced implementation, you could track cache keys
                self::$cache->flush();
                break;
            }
        }
    }

    /**
     * Clear model cache manually
     */
    public static function clearCache(): void
    {
        $instance = new static();
        $instance->invalidateTableCache();
    }

    /**
     * Set cache TTL for this model
     */
    public function setCacheTtl(int $ttl): static
    {
        $this->cacheTtl = $ttl;
        return $this;
    }
}
