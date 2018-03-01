<?php

namespace Gaslawork\Routing;

interface RouteInterface {

	public function checkRoute(RouteUrl $url, $method);

	public function getTarget();

	public function getParam($name);

	public function getParams();

}
