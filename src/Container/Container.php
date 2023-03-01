<?php

namespace Gaslawork\Container;

use \Psr\Container\ContainerInterface;

/**
 * This container makes a difference between classes/factories and properties. That means that get()
 * and has() only works for classes. The equivalents for properties are getProperty() and
 * hasProperty(). There are several reasons for this, first off it's separating concerns, and by
 * doing like this the IDE always know what it's going to get from get(). It's also slightly faster
 * doing it like this.
 */
class Container implements ContainerInterface
{

    /**
     * @var array<string,mixed>
     */
    protected $properties = [];

    /**
     * @var array<string,\Closure>
     */
    protected $entries = [];

    /**
     * @var array<string,true>
     */
    protected $is_singleton = [];

    /**
     * @var array<string,object>
     */
    protected $instances = [];

    /**
     * @var array<string,AutoWire<mixed>>
     */
    protected $autowire_cache = [];

    /**
     * @param string $name
     * @param mixed $value
     * @return Container
     */
    public function setProperty(string $name, $value): self
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getProperty(string $name)
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        // TODO: Proper exception
        throw new \Exception("The value $name does not exist.");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @param \Closure(Container):T $closure
     * @return Container
     */
    public function setCreate(string $id, \Closure $closure): self
    {
        $this->entries[$id] = $closure;
        unset($this->is_singleton[$id]);
        unset($this->instances[$id]);
        unset($this->autowire_cache[$id]);
        return $this;
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @param \Closure(Container):T $closure
     * @return Container
     */
    public function setSingleton(string $id, \Closure $closure): self
    {
        $this->entries[$id] = $closure;
        $this->is_singleton[$id] = true;
        unset($this->instances[$id]);
        unset($this->autowire_cache[$id]);
        return $this;
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function get($id)
    {
        if (!is_string($id)) {
            //throw new ContainerIdInvalidTypeException("The id must be a string.");
            // TODO: Proper exception
            throw new \Exception("The id must be a string.");
        }

        if (isset($this->entries[$id])) {
            if (!isset($this->is_singleton[$id])) {
                return $this->entries[$id]($this);
            }

            if (isset($this->instances[$id])) {
                /** @var T */
                return $this->instances[$id];
            }

            return $this->instances[$id] = $this->entries[$id]($this);
        }

        $autowire = (
            $this->autowire_cache[$id] ?? $this->autowire_cache[$id] = new AutoWire($id)
        );

        return $autowire->create($this);
    }

    /**
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function has($id)
    {
        if (!is_string($id)) {
            //throw new ContainerIdInvalidTypeException("The id must be a string.");
            // TODO: Proper exception
            throw new \Exception("The id must be a string.");
        }

        return isset($this->entries[$id]);
    }

    public function clearSingletons(): self
    {
        $this->instances = [];
        return $this;
    }

}
