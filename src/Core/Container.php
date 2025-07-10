<?php

namespace GuepardoSys\Core;

/**
 * Simple Dependency Injection Container
 */
class Container
{
    /**
     * Container bindings
     */
    private array $bindings = [];

    /**
     * Singleton instances
     */
    private array $instances = [];

    /**
     * Bind a class or interface to the container
     */
    public function bind(string $abstract, callable|string|null $concrete = null, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => $singleton,
        ];
    }

    /**
     * Bind a singleton to the container
     */
    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Bind an existing instance to the container
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve a class from the container
     */
    public function resolve(string $abstract): mixed
    {
        // Return singleton instance if it exists
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get binding or use the abstract as concrete
        $binding = $this->bindings[$abstract] ?? ['concrete' => $abstract, 'singleton' => false];
        $concrete = $binding['concrete'];

        // Build the instance
        $instance = $this->build($concrete);

        // Store singleton instance
        if ($binding['singleton']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Build an instance of the given concrete
     */
    private function build(callable|string $concrete): mixed
    {
        // Handle callable
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        // Handle class name
        if (is_string($concrete)) {
            if (!class_exists($concrete)) {
                throw new \Exception("Class {$concrete} does not exist.");
            }

            $reflector = new \ReflectionClass($concrete);

            // Check if class is instantiable
            if (!$reflector->isInstantiable()) {
                throw new \Exception("Class {$concrete} is not instantiable.");
            }

            $constructor = $reflector->getConstructor();

            // If no constructor, just create instance
            if ($constructor === null) {
                return new $concrete;
            }

            // Resolve constructor dependencies
            $dependencies = $this->resolveDependencies($constructor->getParameters());

            return $reflector->newInstanceArgs($dependencies);
        }

        throw new \Exception("Unable to build instance.");
    }

    /**
     * Resolve constructor dependencies
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                // No type hint, check for default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve parameter {$parameter->getName()}");
                }
            } elseif ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                // Type hinted class
                $dependencies[] = $this->resolve($type->getName());
            } else {
                // Primitive type or unknown, check for default
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve primitive parameter {$parameter->getName()}");
                }
            }
        }

        return $dependencies;
    }

    /**
     * Check if abstract is bound
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Check if a service exists in the container
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}
