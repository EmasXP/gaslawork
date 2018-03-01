<?php

namespace Gaslawork\Routing;

interface RouteInterface {

	public function getTarget();

	public function getParam($name);

	public function getParams();

}
