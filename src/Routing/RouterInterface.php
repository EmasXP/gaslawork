<?php

namespace Gaslawork\Routing;

interface RouterInterface {

    public function find($uri, $method = null): ?RouteDataInterface;

}
