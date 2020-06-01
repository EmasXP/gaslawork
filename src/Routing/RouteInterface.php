<?php

namespace Gaslawork\Routing;

interface RouteInterface {

    public function check(RequestUri $url, ?string $method): ?RouteDataInterface;

}
