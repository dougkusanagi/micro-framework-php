<?php
namespace GuepardoSys\Core;

interface FactoryInterface
{
    public function make(array $attributes = []);
    public function create(array $attributes = []);
}

abstract class BaseFactory implements FactoryInterface
{
    abstract public function definition(): array;

    public function make(array $attributes = [])
    {
        return array_merge($this->definition(), $attributes);
    }

    public function create(array $attributes = [])
    {
        $data = $this->make($attributes);
        // You should implement the actual model creation logic here
        // For example: return User::create($data);
        if (method_exists($this, 'modelClass')) {
            $modelClass = $this->modelClass();
            return $modelClass::create($data);
        }
        return $data;
    }

    public function bulkMake(int $count, array $attributes = [])
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = $this->make($attributes);
        }
        return $items;
    }

    public function bulkCreate(int $count, array $attributes = [])
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = $this->create($attributes);
        }
        return $items;
    }
} 