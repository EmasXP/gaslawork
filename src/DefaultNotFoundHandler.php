<?php

namespace Gaslawork;

use \Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use Gaslawork\Exception\NotFoundException;

class DefaultNotFoundHandler implements NotFoundHandlerInterface
{

    public function __invoke(NotFoundException $e): ResponseInterface
    {
        $response = new Response(404);

        $body = $response->getBody();

        $body->write("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n");
        $body->write("<title>404 Not Found</title>\n");
        $body->write("<h1>Not Found</h1>\n");
        $body->write("<p>The requested URL ");
        $uri = $e->getUri();
        if (!empty($uri)) {
            $body->write("<i>" . htmlspecialchars($uri) . "</i> ");
        }
        $body->write("was not found on the server.</p>");

        $body->write($e->getMessage());

        return $response;
    }

}
