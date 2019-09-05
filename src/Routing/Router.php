<?php

namespace Gaslawork\Routing;


class Router implements RouterInterface {

    protected $routes = array();


    public function add(RouteInterface $route)
    {
        $this->routes[] = $route;
        return $this;
    }


    /**
     * Find the first matching route.
     *
     * @param string $uri
     * @param string $method
     * @return RouteDataInterface|null
     */
    public function find($uri, $method = null): ?RouteDataInterface
    {
        $route_uri = new RequestUri($uri);

        foreach ($this->routes as $route)
        {
            $route_data = $route->check($route_uri, $method);

            if ($route_data !== null)
            {
                return $route_data;
            }
        }

        return null;
    }
}
