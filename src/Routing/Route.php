<?php

namespace Gaslawork\Routing;

use Gaslawork\Exception\UndefinedRouteHandlerParameterException;


class Route implements RouteInterface, RouteDataInterface {

    /** @var string */
    protected $route;

    /** @var string */
    protected $handler;

    /** @var string[]|null */
    protected $exploded_route;

    /** @var array<string,mixed> */
    protected $defaults = [];

    /** @var string[] */
    protected $required;

    /** @var array<string,mixed[]>|null */
    protected $whitelist;

    /** @var array<string,mixed[]>|null */
    protected $blacklist;

    /** @var array<string,mixed>  */
    protected $params = [];

    /** @var string|null */
    protected $controller;

    /** @var string|null */
    protected $action;


    public function __construct(string $route, string $handler)
    {
        $this->route = $route;
        $this->handler = $handler;
    }


    /**
     * Set the defaults of the parameters.
     *
     * @param array<string,mixed> $defaults
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
        return $this;
    }


    /**
     * Set the required parameters.
     *
     * @param string[] $required
     * @return $this
     */
    public function setRequired(array $required)
    {
        $this->required = $required;
        return $this;
    }


    /**
     * Set the white list.
     *
     * @param array<string,mixed[]> $whitelist
     * @return $this
     */
    public function setWhitelist(array $whitelist)
    {
        $this->whitelist = $whitelist;
        return $this;
    }


    /**
     * Set the black list.
     *
     * @param array<string,mixed[]> $blacklist
     * @return $this
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;
        return $this;
    }


    /**
     * Get the route exploded by "/".
     *
     * @return string[]
     */
    protected function getRouteExploded(): array
    {
        if ($this->exploded_route !== null)
        {
            return $this->exploded_route;
        }

        return $this->exploded_route = explode("/", trim($this->route, "/"));
    }


    /**
     * Check if the route matches the passed URL.
     *
     * @param RequestUri $url
     * @param string|null $method
     * @return null|RouteDataInterface
     */
    public function check(
        RequestUri $url,
        ?string $method
    ): ?RouteDataInterface
    {
        $exploded = $this->getRouteExploded();
        $url_exploded = $url->getExploded();

        $params = [];

        foreach ($exploded as $i => $piece)
        {
            if (
                strlen($piece) > 0
                && $piece[0] == ":"
            )
            {
                $param_name = substr($piece, 1);
                $param_value = null;

                if (
                    isset($url_exploded[$i])
                    && ! empty($url_exploded[$i])
                )
                {
                    $param_value = $url_exploded[$i];
                }
                elseif (
                    $this->defaults !== null
                    && isset($this->defaults[$param_name])
                )
                {
                    $param_value = $this->defaults[$param_name];
                }
                elseif (
                    $this->required !== null
                    && in_array($param_name, $this->required)
                )
                {
                    return null;
                }

                if (
                    $this->whitelist !== null
                    && isset($this->whitelist[$param_name])
                    && ! in_array($param_value, $this->whitelist[$param_name])
                )
                {
                    return null;
                }
                elseif (
                    $this->blacklist !== null
                    && isset($this->blacklist[$param_name])
                    && in_array($param_value, $this->blacklist[$param_name])
                )
                {
                    return null;
                }

                $params[$param_name] = $param_value;
            }
            elseif(
                ! isset($url_exploded[$i])
                || $url_exploded[$i] != $piece
            )
            {
                return null;
            }
        }

        $this->params = $params;

        return $this;
    }


    /**
     * This method parses/renders a handler string using the route's parameters as variables.
     *
     * An handler string can be for example "\Controller\{+controller}". Let's say the "controller"
     * parameter is "foo", then the response from this method is going to be "\Controller\Foo.
     *
     * @param string $handler
     * @return string
     * @throws UndefinedRouteHandlerParameterException
     */
    protected function parseHandler(string $handler): string
    {
        /*
        You might look at this code and think "well, this is an odd implementation" - and you are
        quite right, but it's the fastest one.

        It first splits the handler by {, and then again by }. Let's take an example:
        \Controller\{+directory}\{+controller}

        The first indent indent in this list is the $parts, and the second indent is the $parts'
        $subparts:
            "\Controller\"
                "\Controller\"
            "+directory}\"
                "+directory"
                "\"
            "+controller}"
                "+controller"
                ""
        */

        $parts = explode("{", $handler);

        $number_of_parts = count($parts);

        if ($number_of_parts == 1)
        {
            return $parts[0];
        }

        $out = $parts[0];

        for ($i = 1; $i < $number_of_parts; $i++)
        {
            $subparts = explode("}", $parts[$i]);

            if (count($subparts) == 1)
            {
                $out .= $parts[$i];
                continue;
            }

            if ($subparts[0][0] == "+")
            {
                $out .= ucfirst($this->getParamForHandler(substr($subparts[0], 1)))
                    .$subparts[1];
            }
            else
            {
                $out .= $this->getParamForHandler($subparts[0])
                    .$subparts[1];
            }
        }

        return $out;
    }


    /**
     * Get the controller found from after executing the check().
     *
     * @return string
     * @throws UndefinedRouteHandlerParameterException
     */
    public function getController(): string
    {
        if ($this->controller !== null)
        {
            return $this->controller;
        }

        return $this->controller = $this->parseHandler($this->handler);
    }


    /**
     * Get the action found from after executing the check().
     *
     * @return string|null
     */
    public function getAction(): ?string
    {
        if ($this->action !== null)
        {
            return $this->action;
        }

        return $this->action = $this->getParam("action");
    }


    /**
     * Get a parameter from after executing the check().
     *
     * @param string $name
     * @return mixed
     */
    public function getParam(string $name)
    {
        if (isset($this->params[$name]))
        {
            return $this->params[$name];
        }

        if (isset($this->defaults[$name]))
        {
            return $this->defaults[$name];
        }

        return null;
    }


    /**
     * Get all the parameters from after executing the check().
     *
     * @return mixed[]
     */
    public function getParams(): array
    {
        return $this->params + $this->defaults;
    }


    /**
     * Gets a parameter and throw exception if the parameter is not set or is empty. This method
     * is used by the parseHandler() method.
     *
     * @param string $name
     * @return mixed
     * @throws UndefinedRouteHandlerParameterException
     */
    protected function getParamForHandler(string $name)
    {
        if (isset($this->params[$name]))
        {
            if (empty($this->params[$name]))
            {
                throw new UndefinedRouteHandlerParameterException(
                    "The parameter $name is needed by the route's handler but is undefined or empty."
                );
            }

            return $this->params[$name];
        }

        if (isset($this->defaults[$name]))
        {
            if (empty($this->defaults[$name]))
            {
                throw new UndefinedRouteHandlerParameterException(
                    "The parameter $name is needed by the route's handler but is undefined or empty."
                );
            }

            return $this->defaults[$name];
        }

        throw new UndefinedRouteHandlerParameterException(
            "The parameter $name is needed by the route's handler but is undefined or empty."
        );
    }

}
