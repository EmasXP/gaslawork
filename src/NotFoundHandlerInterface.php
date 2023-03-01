<?php

namespace Gaslawork;

use Gaslawork\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

interface NotFoundHandlerInterface
{

    public function __invoke(NotFoundException $e): ResponseInterface;

}
