<?php

namespace Gaslawork\Routing;

interface RouteInterface {

    public function check(RequestUri $url, $method): ?RouteDataInterface;

}
