<?php

namespace Gaslawork\Routing;

class Route implements RouteInterface, RouteDataInterface {

    protected $route;
    protected $namespace_prefix;
    protected $exploded_route;
    protected $defaults = array(
        "controller" => "index",
        "action" => "index",
    );
    protected $required;
    protected $whitelist;
    protected $blacklist;
    protected $params = array();
    protected $controller;
    protected $action;


    public function __construct($route, $namespace_prefix)
    {
        $this->route = $route;
        $this->namespace_prefix = $namespace_prefix;
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

        $params = array();

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

        if (
            (
                ! isset($params["controller"])
                || empty($params["controller"])
            )
            && (
                ! isset($this->defaults["controller"])
                || empty($this->defaults["controller"])
            )
        )
        {
            return null;
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

        $class_path = $this->namespace_prefix;

        if (
            strlen($class_path) == 0
            || $class_path[strlen($class_path)-1] != "\\"
        )
        {
            $class_path .= "\\";
        }

        if (isset($this->params["directory"]))
        {
            $class_path .= $this->params["directory"]."\\";
        }

        $class_path .= ucfirst($this->getParam("controller"));

        return $this->controller = $class_path;
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
