<?php

namespace Gaslawork\Routing;


class Routes {

	protected $routes = array();


	public function add(RouteInterface $route)
	{
		$this->routes[] = $route;
		return $this;
	}


	public function findRoute($url, $method = null)
	{
		$route_url = new RouteUrl($url);

		foreach ($this->routes as $route)
		{
			if ( ! $route->checkRoute($route_url, $method))
			{
				continue;
			}

			return $route;
		}

		return null;
	}
}
