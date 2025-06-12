<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionNamedType;
use ReflectionUnionType;

class Container implements ContainerInterface
{
    /**
     * @var array<string, callable|string>
     */
    private array $bindings = [];

    /**
     * @var array<string, object>
     */
    private array $singletons = [];

    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    /**
     * @var array<string, mixed>
     */
    private array $primitives = [];

    /**
     * Retrieve an entry from the container.
     *
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    public function get(string $id): mixed
    {
        // Check singleton cache
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        // Check for aliases
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // Check for bindings
        if (isset($this->bindings[$id])) {
            $entry = $this->bindings[$id];

            $object = is_callable($entry)
                ? $entry($this)
                : $this->resolve($entry);

            return $object;
        }

        return $this->resolve($id);
    }

    /**
     * Determines if the container has the given binding.
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Bind an entry to the container.
     *
     * @param string $id
     * @param callable|string $concrete
     */
    public function bind(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    /**
     * Bind a singleton to the container.
     *
     * @param string $id
     * @param callable|string $concrete
     */
    public function singleton(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = function ($container) use ($concrete, $id) {
            if (!isset($this->singletons[$id])) {
                $this->singletons[$id] = is_callable($concrete)
                    ? $concrete($container)
                    : $container->resolve($concrete);
            }
            return $this->singletons[$id];
        };
    }

    /**
     * Alias an interface or class to another concrete class.
     */
    public function alias(string $abstract, string $concrete): void
    {
        $this->aliases[$abstract] = $concrete;
    }

    /**
     * Bind a primitive (scalar) value to be injected.
     */
    public function bindPrimitive(string $name, mixed $value): void
    {
        $this->primitives[$name] = $value;
    }

    /**
     * Resolve and instantiate a class and its dependencies.
     *
     * @throws ContainerException
     */
    public function resolve(string $id): object
    {
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        if (isset($this->bindings[$id])) {
            return $this->get($id); // important for interfaces and bindings
        }

        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class '{$id}' is not instantiable.");
        }

        $constructor = $reflectionClass->getConstructor();

        if (!$constructor || !$constructor->getParameters()) {
            return new $id;
        }

        $dependencies = array_map(function (ReflectionParameter $param) use ($id) {
            $paramName = $param->getName();
            $type = $param->getType();

            if ($type instanceof ReflectionUnionType) {
                throw new ContainerException("Cannot resolve '{$id}': union types are not supported for parameter '{$paramName}'.");
            }

            if ($type instanceof ReflectionNamedType) {
                if (!$type->isBuiltin()) {
                    return $this->get($type->getName());
                }

                // primitive (int, string, etc.)
                if (isset($this->primitives[$paramName])) {
                    return $this->primitives[$paramName];
                }

                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                throw new ContainerException("Cannot resolve primitive parameter '{$paramName}' of '{$id}'. No binding or default value found.");
            }

            throw new ContainerException("Cannot resolve parameter '{$paramName}' of '{$id}' due to unsupported type.");
        }, $constructor->getParameters());

        return $reflectionClass->newInstanceArgs($dependencies);
    }
}
