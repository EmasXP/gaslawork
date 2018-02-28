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
		$route,
		$uri
	)
	{
		$this->route = $route;
		$this->uri = $uri;

		return self::$instance = $this;
	}


	public function get_param($name)
	{
		return $this->route
			->get_param($name);
	}


	public function get_params()
	{
		return $this->route
			->get_params();
	}

}
