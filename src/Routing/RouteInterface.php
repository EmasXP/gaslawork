<?php

namespace Gaslawork\Routing;

interface RouteInterface {

	public function checkRoute(RequestUri $url, $method);

	public function getController();

	public function getAction();

	public function getParam($name);

	public function getParams();

}
