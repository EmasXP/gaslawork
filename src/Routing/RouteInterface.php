<?php

namespace Gaslawork\Routing;

interface RouteInterface {

	public function checkRoute(RouteUrl $url, $method);

	public function getController();

	public function getAction();

	public function getParam($name);

	public function getParams();

}
