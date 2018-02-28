<?php

namespace Gaslawork\Routing;


class Routes implements RoutesInterface {

	protected $routes = array();


	public function add($route)
	{
		$this->routes[] = $route;
		return $this;
	}


	public function find_route($url, $method = null)
	{
		$route_url = new RouteUrl($url);

		foreach ($this->routes as $route)
		{
			if ( ! $route->check_route($route_url, $method))
			{
				continue;
			}

			return $route;
		}

		return null;
	}
}
