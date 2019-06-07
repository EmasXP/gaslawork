<?php

namespace Gaslawork;

class Container implements \Psr\Container\ContainerInterface {

    protected $entries = [];
    protected $is_loaded = [];
    protected $is_factory = [];
    protected $is_used = [];


    public function set(string $id, $entry)
    {
        if (isset($this->is_used[$id]))
        {
            throw new Exception\ContainerEntryUsedException("Service cannot be changed once it's used.");
        }

        $this->entries[$id] = $entry;

        unset($this->is_factory[$id]);

        return $this;
    }


    public function get($id)
    {
        if ( ! is_string($id))
        {
            throw new Exception\ContainerIdInvalidTypeException("The id must be a string.");
        }

        if (isset($this->is_loaded[$id]))
        {
            return $this->entries[$id];
        }

        if ( ! isset($this->entries[$id]))
        {
            throw new Exception\ContainerEntryNotFoundException("Service $id does not exist.");
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


    public function has($id)
    {
        if ( ! is_string($id))
        {
            throw new Exception\ContainerIdInvalidTypeException("The id must be a string.");
        }

        return array_key_exists($id, $this->entries);
    }


    public function factory(string $id, \Closure $closure)
    {
        $this->set($id, $closure);
        $this->is_factory[$id] = true;
        return $this;
    }

}
