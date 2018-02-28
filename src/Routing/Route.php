<?php

namespace Gaslawork\Routing;

class Route implements RouteInterface {

	protected $route;
	protected $namespace_prefix;
	protected $exploded_route;
	protected $defaults = array(
		"controller" => "index",
		"action" => "index",
	);
	protected $required;
	protected $whitelist;
	protected $blacklist;
	protected $params = array();
	protected $target;
	protected $action_prefix = "action_";
	protected $action_suffix;


	public function __construct($route, $namespace_prefix)
	{
		$this->route = $route;
		$this->namespace_prefix = $namespace_prefix;
	}


	public function set_defaults(array $defaults)
	{
		$this->defaults = $defaults;
		return $this;
	}


	public function set_required(array $required)
	{
		$this->required = $required;
		return $this;
	}


	public function set_whitelist(array $whitelist)
	{
		$this->whitelist = $whitelist;
		return $this;
	}


	public function set_blacklist(array $blacklist)
	{
		$this->blacklist = $blacklist;
		return $this;
	}


	public function set_action_prefix($action_prefix)
	{
		$this->action_prefix = $action_prefix;
		return $this;
	}


	public function set_action_suffix($action_suffix)
	{
		$this->action_suffix = $action_suffix;
		return $this;
	}


	protected function get_route_exploded()
	{
		if ($this->exploded_route !== null)
		{
			return $this->exploded_route;
		}

		return $this->exploded_route = explode("/", rtrim($this->route, "/"));
	}


	public function check_route(RouteUrl $url, $method)
	{
		$exploded = $this->get_route_exploded();
		$url_exploded = $url->get_exploded();

		$params = array();

		foreach ($exploded as $i => $piece)
		{
			if (
				strlen($piece) > 0
				&& $piece[0] == ":"
			)
			{
				$param_name = substr($piece, 1);
				$param_value = null;

				if (
					isset($url_exploded[$i])
					&& ! empty($url_exploded[$i])
				)
				{
					$param_value = $url_exploded[$i];
				}
				elseif (
					$this->defaults !== null
					&& isset($this->defaults[$param_name])
				)
				{
					$param_value = $this->defaults[$param_name];
				}
				elseif (
					$this->required !== null
					&& ! in_array($param_name, $this->required)
				)
				{
					return false;
				}

				if (
					$this->whitelist !== null
					&& isset($this->whitelist[$param_name])
					&& ! in_array($param_value, $this->whitelist[$param_name])
				)
				{
					return false;
				}
				elseif (
					$this->blacklist !== null
					&& isset($this->blacklist[$param_name])
					&& in_array($param_value, $this->blacklist[$param_name])
				)
				{
					return false;
				}

				$params[$param_name] = $param_value;
			}
			elseif(
				! isset($url_exploded[$i])
				|| $url_exploded[$i] != $piece
			)
			{
				return false;
			}
		}

		if (
			(
				! isset($params["controller"])
				|| empty($params["controller"])
			)
			&& (
				! isset($this->defaults["controller"])
				|| empty($this->defaults["controller"])
			)
		)
		{
			return false;
		}

		$this->params = $params;

		return true;
	}


	public function get_target()
	{
		if ($this->target !== null)
		{
			return $this->target;
		}

		$class_path = $this->namespace_prefix;

		if (
			strlen($class_path) == 0
			|| $class_path[strlen($class_path)-1] != "\\"
		)
		{
			$class_path .= "\\";
		}

		if (isset($this->params["directory"]))
		{
			$class_path .= $this->params["directory"]."\\";
		}

		$class_path .= ucfirst($this->get_param("controller"));

		$action = $this->get_param("action");

		if ($action !== null)
		{
			$class_path .= "->"
				.$this->action_prefix
				.$action
				.$this->action_suffix;
		}

		return $this->target = $class_path;
	}


	public function get_param($name)
	{
		if (isset($this->params[$name]))
		{
			return $this->params[$name];
		}

		if (isset($this->defaults[$name]))
		{
			return $this->defaults[$name];
		}

		return null;
	}


	public function get_params()
	{
		return $this->params + $this->defaults;
	}


}
