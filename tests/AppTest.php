<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use Gaslawork\App;
use Gaslawork\Routing\Routes;
use Gaslawork\Routing\Route;


final class AppTest extends TestCase {

    public function testNonExistingRoute()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("No route found for URI");

        $app = \Gaslawork\App::instance(new Routes);
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["nonexisting", "GET"]);
    }

    public function testInvalidController()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("The controller path \Controller\... is invalid");

        $app = \Gaslawork\App::instance(
            (new Routes)
                ->add(new Route("/:controller", "\Controller\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["...", "GET"]);
    }

    public function testNonExistingController()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("The controller \Controller\Hello does not exist");

        $app = \Gaslawork\App::instance(
            (new Routes)
                ->add(new Route("/:controller", "\Controller\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["hello", "GET"]);
    }

    public function testNonExistingAction()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("Method nonexistingAction does not exist in \Gaslawork\Tests\Dummycontroller");

        $app = \Gaslawork\App::instance(
            (new Routes)
                ->add(new Route("/:controller/:action", "\Gaslawork\Tests\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/nonexisting", "GET"]);
    }

    public function testDefaultNotFoundHandler()
    {
        $expected = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n<title>404 Not Found</title>\n<h1>Not Found</h1>\n<p>The requested URL <i>uri</i> was not found on the server.</p>";
        $this->expectOutputString($expected);

        $app = \Gaslawork\App::instance(new Routes);
        $e = new \Gaslawork\Exception\NotFoundException("uri", "Notfound!");
        PHPUnitUtil::callMethod($app, "handleNotFoundException", [$e]);
    }

    public function testDefaultNotFoundHandlerNonExistingRoute()
    {
        $expected = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n<title>404 Not Found</title>\n<h1>Not Found</h1>\n<p>The requested URL <i>nonexisting</i> was not found on the server.</p>";
        $this->expectOutputString($expected);

        $app = \Gaslawork\App::instance(new Routes);

        try
        {
            PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["nonexisting", "GET"]);
        }
        catch (\Gaslawork\Exception\NotFoundException $e)
        {
            PHPUnitUtil::callMethod($app, "handleNotFoundException", [$e]);
        }
    }

    public function testCustomNotFoundHandler()
    {
        $expected = "Not found: theuri";
        $this->expectOutputString($expected);

        $container = (new \Gaslawork\Container)
            ->set(
                "notFoundHandler",
                function($c)
                {
                    return function(\Gaslawork\Exception\NotFoundException $e)
                    {
                        print "Not found: ".$e->getUri();
                    };
                }
            );

        $app = \Gaslawork\App::instance(new Routes, $container);
        $e = new \Gaslawork\Exception\NotFoundException("theuri", "Notfound!");
        PHPUnitUtil::callMethod($app, "handleNotFoundException", [$e]);
    }

    public function testWorkingRequestResponse()
    {
        $this->expectOutputString("World!");

        $app = \Gaslawork\App::instance(
            (new Routes)
                ->add(new Route("/:controller/:action", "\Gaslawork\Tests\\"))
        );

        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/hello", "GET"]);
    }

}
