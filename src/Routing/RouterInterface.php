<?php

namespace Gaslawork\Routing;

use Psr\Http\Message\RequestInterface;

interface RouterInterface
{

    public function find(RequestInterface $request): ?RouteDataInterface;

}
