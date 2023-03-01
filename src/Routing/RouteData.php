<?php

namespace Gaslawork\Routing;

class RouteData implements RouteDataInterface
{

    /**
     * @var string&class-string
     */
    protected $controller;

    /**
     * @var null|string
     */
    protected $action;

    /**
     * @var array<string,string>
     */
    protected $params;

    /**
     * @param class-string $controller
     * @param null|string $action
     * @param array<string,string> $params
     * @return void
     */
    public function __construct(
        string $controller,
        ?string $action,
        array $params
    ) {
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * @return string&class-string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * Get a parameter from after executing the check().
     *
     * @param string $name
     * @return null|string
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Get all the parameters from after executing the check().
     *
     * @return array<string,string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

}
