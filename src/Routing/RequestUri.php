<?php

namespace Gaslawork\Routing;

class RequestUri {

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


	public function getExploded()
	{
		if ($this->exploded_url === null)
		{
			$this->exploded_url = explode("/", trim($this->url, "/"));
		}

		return $this->exploded_url;
	}
}
