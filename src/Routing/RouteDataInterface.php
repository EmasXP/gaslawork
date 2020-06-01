<?php

namespace Gaslawork\Routing;

interface RouteDataInterface {

    public function getController(): string;

    public function getAction(): ?string;

    public function getParam(string $name);

    public function getParams(): array;

}
