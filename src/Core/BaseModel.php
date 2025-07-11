<?php

namespace GuepardoSys\Core;

use PDO;
use PDOException;
use GuepardoSys\Core\Database;
use GuepardoSys\Core\Cache\CacheFacade;

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
    protected int $cacheTtl = 3600; // 1 hour default
    protected bool $exists = false;

    // Query builder properties
    protected array $wheres = [];
    protected ?string $orderByColumn = null;
    protected ?string $orderByDirection = null;
    protected ?int $limitValue = null;

    public function __construct()
    {
        $this->db = Database::getConnection();

        // Set table name by convention if not set
        if (!isset($this->table)) {
            $this->table = $this->getTableName();
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

        return CacheFacade::remember($cacheKey, function () use ($instance, $id) {
            $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1");
            $stmt->execute(['id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $instance->attributes = $data;
                $instance->exists = true;
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

        return CacheFacade::remember($cacheKey, function () use ($instance) {
            $stmt = $instance->db->query("SELECT * FROM {$instance->table}");
            $results = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $model = new static();
                $model->attributes = $row;
                $model->exists = true;
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
    public static function where(string $column, $value): static
    {
        $instance = new static();
        $instance->wheres[] = ['column' => $column, 'value' => $value];
        return $instance;
    }

    /**
     * Get first record from query (instance method)
     */
    public function firstInstance(): ?static
    {
        $this->limitValue = 1;
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get first record (static method)
     */
    public static function first(): ?static
    {
        $instance = new static();
        $instance->limitValue = 1;
        $results = $instance->get();
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Execute the query and get results
     */
    public function get(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($this->wheres)) {
            $conditions = [];
            foreach ($this->wheres as $i => $where) {
                $paramName = "param_{$i}";
                $conditions[] = "{$where['column']} = :{$paramName}";
                $params[$paramName] = $where['value'];
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if ($this->orderByColumn) {
            $sql .= " ORDER BY {$this->orderByColumn} {$this->orderByDirection}";
        }

        if ($this->limitValue) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new static();
            $model->attributes = $row;
            $model->exists = true;
            $results[] = $model;
        }

        return $results;
    }

    /**
     * Count records
     */
    public static function count(): int
    {
        $instance = new static();
        $stmt = $instance->db->query("SELECT COUNT(*) as count FROM {$instance->table}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }


    /**
     * Order by column
     */
    public static function orderBy(string $column, string $direction = 'ASC'): static
    {
        $instance = new static();
        $instance->orderByColumn = $column;
        $instance->orderByDirection = strtoupper($direction);
        return $instance;
    }

    /**
     * Limit results
     */
    public static function limit(int $limit): static
    {
        $instance = new static();
        $instance->limitValue = $limit;
        return $instance;
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
                CacheFacade::forget($pattern);
            } else {
                // For wildcard patterns, we need to flush the entire cache for simplicity
                // In a more advanced implementation, you could track cache keys
                CacheFacade::flush();
                break;
            }
        }
    }

    /**
     * Save the model (create or update)
     */
    public function save(): bool
    {
        if ($this->exists && isset($this->attributes[$this->primaryKey])) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }

    /**
     * Perform insert operation
     */
    protected function performInsert(): bool
    {
        $fillableData = [];
        foreach ($this->attributes as $key => $value) {
            if (in_array($key, $this->fillable) && $key !== $this->primaryKey) {
                $fillableData[$key] = $value;
            }
        }

        if (empty($fillableData)) {
            return false;
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        $fillableData['created_at'] = $now;
        $fillableData['updated_at'] = $now;

        $columns = implode(', ', array_keys($fillableData));
        $placeholders = ':' . implode(', :', array_keys($fillableData));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($fillableData)) {
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            $this->attributes['created_at'] = $now;
            $this->attributes['updated_at'] = $now;
            $this->exists = true;
            $this->invalidateTableCache();
            return true;
        }

        return false;
    }

    /**
     * Perform update operation
     */
    protected function performUpdate(): bool
    {
        $fillableData = [];
        foreach ($this->attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $fillableData[$key] = $value;
            }
        }

        if (empty($fillableData)) {
            return false;
        }

        // Add updated timestamp
        $now = date('Y-m-d H:i:s');
        $fillableData['updated_at'] = $now;

        $setParts = [];
        foreach (array_keys($fillableData) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE {$this->primaryKey} = :id";
        $fillableData['id'] = $this->attributes[$this->primaryKey];

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($fillableData);

        if ($result) {
            $this->attributes['updated_at'] = $now;
            $this->invalidateTableCache();
        }

        return $result;
    }

    /**
     * Fill model with data
     */
    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table;
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
