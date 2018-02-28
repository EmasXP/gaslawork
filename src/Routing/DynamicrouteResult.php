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


	public function get_target()
	{
		return $this->target;
	}


	public function get_param($name)
	{
		if ( ! isset($this->params[$name]))
		{
			return null;
		}

		return $this->params[$name];
	}


	public function get_params()
	{
		return $this->params;
	}

}
