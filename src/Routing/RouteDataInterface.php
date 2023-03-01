<?php

namespace Gaslawork\Routing;

interface RouteDataInterface {

    public function getController(): string;

    public function getAction(): ?string;

    /**
     * @param string $name
     * @return null|string
     */
    public function getParam(string $name);

    /**
     * @return array<string,string>
     */
    public function getParams(): array;

}
