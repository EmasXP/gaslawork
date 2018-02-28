<?php

namespace Gaslawork;

class App {

	protected $router;
	protected $dependencies = array();
	protected $loaded_dependencies = array();
	public $base_url;

	protected static $instance;


	public static function instance($router = null)
	{
		if (self::$instance === null)
		{
			return new self($router);
		}

		if ($router !== null)
		{
			return self::current()
				->setRouter($router);
		}

		return self::current();
	}


	public static function current()
	{
		return self::$instance;
	}


	public function __construct($router)
	{
		$this->router = $router;

		if (self::$instance !== null)
		{
			throw new Exception\InstanceAlreadyExistException("An instance of App already exists.");
		}

		self::$instance = $this;
	}


	public function setRouter($router)
	{
		$this->router = $router;
		return $this;
	}


	public function getRouter()
	{
		return $this->router;
	}


	protected function getUri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']))
		{
			return null;
		}

		$uri = $_SERVER['REQUEST_URI'];

		if (false !== $pos = strpos($uri, '?'))
		{
			$uri = substr($uri, 0, $pos);
		}

		$uri_decoded = rawurldecode($uri);

		if ($this->base_url !== null)
		{
			return substr($uri_decoded, strlen($this->base_url));
		}

		return $uri_decoded;
	}


	public function findAndExecuteRoute($uri, $http_method)
	{
		$route = $this->router->findRoute($uri, $http_method);

		if ($route === null)
		{
			return print "404";
		}

		new \Gaslawork\Request(
			$route,
			$uri
		);

		$target = explode("->", $route->getTarget());

		//spl_autoload($target[0]);

		if ( ! class_exists($target[0]))
		{
			return print "404";
		}

		$controller = new $target[0];

		if (count($target) > 1)
		{
			if ( ! method_exists($controller, $target[1]))
			{
				return print "404";
			}

			return $controller->{$target[1]}();
		}

		return $controller();
	}


	public function run()
	{
		return $this->findAndExecuteRoute(
			$this->getUri(),
			(isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null)
		);
	}


	public function set($name, callable $callable)
	{
		$this->dependencies[$name] = $callable;

		unset($this->loaded_dependencies[$name]);

		return $this;
	}


	public function get($name)
	{
		if (isset($this->loaded_dependencies[$name]))
		{
			return $this->loaded_dependencies[$name];
		}

		if ( ! isset($this->dependencies[$name]))
		{
			throw new Exception\NonExistingDependencyException("Dependency $name does not exist.");
		}

		return $this->loaded_dependencies[$name] = $this->dependencies[$name]();
	}


	public function has($name)
	{
		return isset($this->dependencies[$name]);
	}

}
