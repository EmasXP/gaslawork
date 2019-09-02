<?php

namespace Gaslawork\Routing;

interface RouteInterface {

    public function checkRoute(RequestUri $url, $method): ?RouteDataInterface;

}
