<?php

namespace Gaslawork;

use Closure;
use Gaslawork\Exception\ContainerEntryNotFoundException;
use Gaslawork\Exception\ContainerEntryUsedException;
use Gaslawork\Exception\ContainerIdInvalidTypeException;


class Container implements \Psr\Container\ContainerInterface {

    /** @var array<string,mixed> */
    protected $entries = [];

    /** @var array<string,bool> */
    protected $is_loaded = [];

    /** @var array<string,bool> */
    protected $is_factory = [];

    /** @var array<string,bool> */
    protected $is_used = [];


    /**
     * Set a service to the container.
     *
     * @param string $id
     * @param mixed $entry
     * @return $this
     * @throws ContainerEntryUsedException
     */
    public function set(string $id, $entry)
    {
        if (isset($this->is_used[$id]))
        {
            throw new ContainerEntryUsedException("Service cannot be changed once it's used.");
        }

        $this->entries[$id] = $entry;

        unset($this->is_factory[$id]);

        return $this;
    }


    /**
     * Get a service from the container.
     *
     * @param string $id
     * @return mixed
     * @throws ContainerIdInvalidTypeException
     * @throws ContainerEntryNotFoundException
     */
    public function get($id)
    {
        if ( ! is_string($id))
        {
            throw new ContainerIdInvalidTypeException("The id must be a string.");
        }

        if (isset($this->is_loaded[$id]))
        {
            return $this->entries[$id];
        }

        if ( ! isset($this->entries[$id]))
        {
            throw new ContainerEntryNotFoundException("Service $id does not exist.");
        }

        $entry = $this->entries[$id];
        $this->is_used[$id] = true;

        if (isset($this->is_factory[$id]))
        {
            return $entry($this);
        }

        $this->is_loaded[$id] = true;

        if ( ! ($entry instanceof \Closure))
        {
            return $entry;
        }

        return $this->entries[$id] = $entry($this);
    }


    /**
     * Check if the container has a service.
     *
     * @param string $id
     * @return bool
     * @throws ContainerIdInvalidTypeException
     */
    public function has($id): bool
    {
        if ( ! is_string($id))
        {
            throw new ContainerIdInvalidTypeException("The id must be a string.");
        }

        return array_key_exists($id, $this->entries);
    }


    /**
     * Add a factory to the container.
     *
     * @param string $id
     * @param Closure $closure
     * @return $this
     * @throws ContainerEntryUsedException
     */
    public function factory(string $id, \Closure $closure)
    {
        $this->set($id, $closure);
        $this->is_factory[$id] = true;
        return $this;
    }

}
