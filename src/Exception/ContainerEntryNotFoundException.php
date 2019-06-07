<?php

namespace Gaslawork\Exception;

class ContainerEntryNotFoundException
extends GaslaworkException
implements \Psr\Container\NotFoundExceptionInterface
{
}