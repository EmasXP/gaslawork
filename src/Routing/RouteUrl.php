<?php

namespace Gaslawork\Routing;

class RouteUrl {

	protected $url;
	protected $exploded_url;


	public function __construct($url)
	{
		$this->url = $url;
	}


	public function get()
	{
		return $this->url;
	}


	public function get_exploded()
	{
		if ($this->exploded_url === null)
		{
			$this->exploded_url = explode("/", rtrim($this->url, "/"));
		}

		return $this->exploded_url;
	}
}
