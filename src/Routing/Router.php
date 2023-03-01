<?php

namespace Gaslawork\Routing;

use Psr\Http\Message\RequestInterface;

class Router implements RouterInterface
{

    /**
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * Add a route to the router.
     *
     * @param RouteInterface $route
     * @return $this
     */
    public function add(RouteInterface $route)
    {
        $this->routes[] = $route;
        return $this;
    }

    /**
     * Find the first matching route.
     *
     * @param RequestInterface $request
     * @return RouteDataInterface|null
     */
    public function find(RequestInterface $request): ?RouteDataInterface
    {
        $request_uri = new RequestUri($request);

        foreach ($this->routes as $route) {
            $route_data = $route->check($request_uri, $request);

            if ($route_data !== null) {
                return $route_data;
            }
        }

        return null;
    }

}
