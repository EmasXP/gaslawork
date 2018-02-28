<?php

namespace Gaslawork\Routing;

interface RouteTargetInterface {

	public function getController();

	public function getAction();

}

