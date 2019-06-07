<?php

namespace Gaslawork;

class Controller {

    public function getParam($name)
    {
        return Request::current()
            ->getParam($name);
    }


    public function getParams()
    {
        return Request::current()
            ->getParams();
    }


    public function __get($name)
    {
        return App::current()->get($name);
    }


    public function get($name)
    {
        return App::current()->get($name);
    }


    public function has($name)
    {
        return App::current()->has($name);
    }

}
