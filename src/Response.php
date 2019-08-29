<?php

namespace Gaslawork;

class Response {

    public static function status(int $code)
    {
        http_response_code($code);
    }

}
