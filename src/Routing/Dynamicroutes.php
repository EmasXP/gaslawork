<?php

namespace Gaslawork\Routing;

class Dynamicroutes {

	protected $namespace_prefix;
	protected $default_controller = "index";
	protected $default_action = "index";
	protected $action_prefix = "action_";
	protected $action_suffix;


	public function __construct($namespace_prefix)
	{
		$this->namespace_prefix = $namespace_prefix;
	}


	public function findRoute($url, $method = null)
	{
		$route_url = new RouteUrl($url);

		$exploded = $route_url->getExploded();

		$controller = $this->default_controller;
		$action = $this->default_action;

		if (count($exploded) >= 2)
		{
			$controller = $exploded[1];
		}

		if (count($exploded) >= 3)
		{
			$action = $exploded[2];
		}

		$class_path = $this->namespace_prefix.ucfirst($controller);

		spl_autoload($class_path);

		if ( ! class_exists($class_path))
		{
			return null;
		}

		$object = new $class_path;

		$action_name = $this->action_prefix.$action.$this->action_suffix;

		if ( ! method_exists($object, $action_name))
		{
			return null;
		}

		$path = $class_path."->".$action_name;

		return new DynamicrouteResult(
			$path,
			array_slice($exploded, 3)
		);
	}

}
