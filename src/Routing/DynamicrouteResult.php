<?php

namespace Gaslawork\Routing;


class DynamicrouteResult implements RouteInterface {

	protected $target;
	protected $params;


	public function __construct($target, $params)
	{
		$this->target = $target;
		$this->params = $params;
	}


	public function getTarget()
	{
		return $this->target;
	}


	public function getParam($name)
	{
		if ( ! isset($this->params[$name]))
		{
			return null;
		}

		return $this->params[$name];
	}


	public function getParams()
	{
		return $this->params;
	}

}
