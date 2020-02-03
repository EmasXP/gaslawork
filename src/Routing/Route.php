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


    public function getController()
    {
        if ($this->controller !== null)
        {
            return $this->controller;
        }

        $controller = "";
        $param_name = "";
        $reading_param = false;
        $param_mod = null;

        $handler_length = strlen($this->handler);

        for ($i = 0; $i < $handler_length; $i++)
        {
            $char = $this->handler[$i];

            if ($reading_param)
            {
                if ($param_mod === null)
                {
                    if ($char == "+")
                    {
                        $param_mod = $char;
                        continue;
                    }

                    $param_mod = false;
                }

                if ($char != "}")
                {
                    $param_name .= $char;
                    continue;
                }

                $param_value = $this->getParam($param_name);

                if ($param_value !== null)
                {
                    if ($param_mod == "+")
                    {
                        $controller .= ucfirst($param_value);
                    }
                    else
                    {
                        $controller .= $param_value;
                    }
                }
                $param_name = "";
                $reading_param = false;
                continue;
            }

            elseif ($char == "{")
            {
                $reading_param = true;
                $param_mod = null;
                continue;
            }

            $controller .= $char;
        }

        return $this->controller = $controller;
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


}
