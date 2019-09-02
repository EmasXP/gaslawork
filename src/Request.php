<?php

namespace Gaslawork;

class Request {

    protected $route_data;
    protected $uri;
    protected $base_url;

    protected static $instance;


    public static function current()
    {
        return self::$instance;
    }


    public function __construct(
        Routing\RouteDataInterface $route_data,
        $uri
    )
    {
        $this->route_data = $route_data;
        $this->uri = $uri;

        self::$instance = $this;
    }


    public function getParam($name)
    {
        return $this->route_data
            ->getParam($name);
    }


    public function getParams()
    {
        return $this->route_data
            ->getParams();
    }

}
