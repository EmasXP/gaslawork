<?php

namespace Gaslawork;

class Controller {

	public function get_param($name)
	{
		return Request::current()
			->get_param($name);
	}


	public function params()
	{
		return Request::current()
			->get_params();
	}


	public function __get($name)
	{
		if (App::current()->has($name))
		{
			return App::current()->get($name);
		}

		$trace = debug_backtrace();

		trigger_error(
			"Undefined property: "
				.get_class($trace[0]["object"])
				."::$"
				.$name
				." in ".$trace[0]["file"]
				." on line ".$trace[0]["line"],
			E_USER_NOTICE
		);

		return null;
	}

}
