<?php

namespace Gaslawork\Exception;

class NotFoundException extends GaslaworkException {

    protected $uri;


    public function __construct($uri, $message = "")
    {
        parent::__construct($message);
        $this->uri = $uri;
    }


    public function getUri()
    {
        return $this->uri;
    }

}
