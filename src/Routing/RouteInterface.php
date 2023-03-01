<?php

namespace Gaslawork\Routing;

use Psr\Http\Message\RequestInterface;

interface RouteInterface
{

    public function check(RequestUri $url, RequestInterface $request): ?RouteDataInterface;

    public function getMinimumParts(): int;

    public function getMaximumParts(): int;

}
