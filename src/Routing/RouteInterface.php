<?php

namespace Gaslawork\Routing;


interface RouteInterface {

	public function get_target();

	public function get_param($name);

	public function get_params();

}
