<?php

namespace Gaslawork\Container;

/**
 * @template T
 */
class AutoWire
{

    /**
     * @var class-string<T>
     */
    protected $class;

    /**
     * @var \ReflectionClass<T&object>
     */
    protected $reflection;

    /**
     * @var (ClassName|mixed)[]
     */
    protected $args = [];

    /**
     * @param string $class
     * @return void
     * @throws UnsatisfiableDependencyException
     */
    public function __construct(string $class)
    {
        $this->class = $class;
        $this->reflection = new \ReflectionClass($class);
        $this->compile();
    }

    /**
     * @return void
     * @throws UnsatisfiableDependencyException
     */
    protected function compile()
    {
        $constructor = $this->reflection->getConstructor();

        $this->args = [];

        if (!$constructor) {
            return;
        }

        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (
                $type === null
                || !$type instanceof \ReflectionNamedType
                || $type->isBuiltin()
            ) {
                if ($parameter->isDefaultValueAvailable()) {
                    $this->args[] = $parameter->getDefaultValue();
                    continue;
                }

                if (
                    $type === null
                    || !$type instanceof \ReflectionNamedType
                ) {
                    throw new UnsatisfiableDependencyException("Cannot satisfy dependency \$" . $parameter->getName() . " of " . $this->class);
                }

                throw new UnsatisfiableDependencyException("Cannot satisfy dependency " . $type->getName() . " \$" . $parameter->getName() . " of " . $this->class);
            }

            $this->args[] = new ClassName($type->getName());
        }
    }

    /**
     * @return T
     * @throws UnsatisfiableDependencyException
     */
    public function create(Container $container)
    {
        $args = [];

        foreach ($this->args as $arg) {
            $args[] = (
                $arg instanceof ClassName
                ? $container->get($arg->getName())
                : $arg
            );
        }

        /** @var T */
        return $this->reflection
            ->newInstanceArgs($args);
    }

}
