<?php

namespace Gaslawork\Routing;


class Routes {

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
	 * @return RouteInterface
	 */
	public function findRoute($uri, $method = null)
	{
		$route_uri = new RequestUri($uri);

		foreach ($this->routes as $route)
		{
			if ( ! $route->checkRoute($route_uri, $method))
			{
				continue;
			}

			return $route;
		}

		return null;
	}
}
