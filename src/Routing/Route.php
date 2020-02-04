<?php

namespace Gaslawork\Routing;

class Route implements RouteInterface, RouteDataInterface {

    protected $route;
    protected $handler;
    protected $exploded_route;
    protected $defaults = [];
    protected $required;
    protected $whitelist;
    protected $blacklist;
    protected $params = [];
    protected $controller;
    protected $action;


    public function __construct(string $route, string $handler)
    {
        $this->route = $route;
        $this->handler = $handler;
    }


    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
        return $this;
    }


    public function setRequired(array $required)
    {
        $this->required = $required;
        return $this;
    }


    public function setWhitelist(array $whitelist)
    {
        $this->whitelist = $whitelist;
        return $this;
    }


    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;
        return $this;
    }


    protected function getRouteExploded()
    {
        if ($this->exploded_route !== null)
        {
            return $this->exploded_route;
        }

        return $this->exploded_route = explode("/", trim($this->route, "/"));
    }


    public function check(RequestUri $url, $method): ?RouteDataInterface
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


    protected function parseHandler(string $handler)
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


    public function getController()
    {
        if ($this->controller !== null)
        {
            return $this->controller;
        }

        return $this->controller = $this->parseHandler($this->handler);
    }


    public function getAction()
    {
        if ($this->action !== null)
        {
            return $this->action;
        }

        return $this->action = $this->getParam("action");
    }


    public function getParam($name)
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


    public function getParams()
    {
        return $this->params + $this->defaults;
    }


    protected function getParamForHandler($name)
    {
        if (isset($this->params[$name]))
        {
            if (empty($this->params[$name]))
            {
                throw new \Gaslawork\Exception\UndefinedRouteHandlerParameterException(
                    "The parameter $name is needed by the route's handler but is undefined or empty."
                );
            }

            return $this->params[$name];
        }

        if (isset($this->defaults[$name]))
        {
            if (empty($this->defaults[$name]))
            {
                throw new \Gaslawork\Exception\UndefinedRouteHandlerParameterException(
                    "The parameter $name is needed by the route's handler but is undefined or empty."
                );
            }

            return $this->defaults[$name];
        }

        throw new \Gaslawork\Exception\UndefinedRouteHandlerParameterException(
            "The parameter $name is needed by the route's handler but is undefined or empty."
        );
    }

}
