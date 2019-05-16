<?php

namespace Gaslawork;

class Request {

	protected $route;
	protected $uri;
	protected $base_url;

	protected static $instance;


	public static function current()
	{
		return self::$instance;
	}


	public function __construct(
		Routing\RouteInterface $route,
		$uri
	)
	{
		$this->route = $route;
		$this->uri = $uri;

		return self::$instance = $this;
	}


	public function getParam($name)
	{
		return $this->route
			->getParam($name);
	}


	public function getParams()
	{
		return $this->route
			->getParams();
	}

}
