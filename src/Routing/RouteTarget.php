<?php

namespace Gaslawork\Routing;

class RouteTarget implements RouteTargetInterface {

	protected $controller;
	protected $action;


	public function __construct($controller, $action = null)
	{
		$this->controller = $controller;
		$this->action = $action;
	}


	public function getController()
	{
		return $this->controller;
	}


	public function getAction()
	{
		return $this->action;
	}

}

