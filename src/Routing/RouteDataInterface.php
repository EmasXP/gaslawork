<?php

namespace Gaslawork\Routing;

interface RouteDataInterface {

    public function getController();

    public function getAction();

    public function getParam($name);

    public function getParams();

}
