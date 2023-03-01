<?php

namespace Gaslawork\Container;

class ClassName
{

    /**
     * @var class-string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return class-string
     */
    public function getName()
    {
        return $this->name;
    }

}
