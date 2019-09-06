<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use Gaslawork\App;
use Gaslawork\Routing\Router;
use Gaslawork\Routing\Route;


final class AppTest extends TestCase {

    public function testNonExistingRoute()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("No route found for URI");

        $app = \Gaslawork\App::instance(new Router);
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["nonexisting", "GET"]);
    }

    public function testInvalidController()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("The controller path \Controller\... is invalid");

        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller", "\Controller\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["...", "GET"]);
    }

    public function testNonExistingController()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("The controller \Controller\Hello does not exist");

        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller", "\Controller\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["hello", "GET"]);
    }

    public function testNonExistingAction()
    {
        $this->expectException(\Gaslawork\Exception\NotFoundException::class);
        $this->expectExceptionMessage("Method nonexistingAction does not exist in \Gaslawork\Tests\Dummycontroller");

        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller/:action", "\Gaslawork\Tests\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/nonexisting", "GET"]);
    }

    public function testDefaultNotFoundHandler()
    {
        $expected = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n<title>404 Not Found</title>\n<h1>Not Found</h1>\n<p>The requested URL <i>uri</i> was not found on the server.</p>";
        $this->expectOutputString($expected);

        $app = \Gaslawork\App::instance(new Router);
        $e = new \Gaslawork\Exception\NotFoundException("uri", "Notfound!");
        PHPUnitUtil::callMethod($app, "handleNotFoundException", [$e]);
    }

    public function testDefaultNotFoundHandlerNonExistingRoute()
    {
        $expected = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n<title>404 Not Found</title>\n<h1>Not Found</h1>\n<p>The requested URL <i>nonexisting</i> was not found on the server.</p>";
        $this->expectOutputString($expected);

        $app = \Gaslawork\App::instance(new Router);

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

        $app = \Gaslawork\App::instance(new Router, $container);
        $e = new \Gaslawork\Exception\NotFoundException("theuri", "Notfound!");
        PHPUnitUtil::callMethod($app, "handleNotFoundException", [$e]);
    }

    public function testWorkingRequestResponse()
    {
        $this->expectOutputString("World!");

        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller/:action", "\Gaslawork\Tests\\"))
        );

        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/hello", "GET"]);
    }

    public function testFetchParams()
    {
        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller/:action/:id1/:id2", "\Gaslawork\Tests\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/silent/first/second", "GET"]);

        $this->assertEquals("first", \Gaslawork\Request::current()->getParam("id1"));
        $this->assertEquals("second", \Gaslawork\Request::current()->getParam("id2"));

        $this->assertEquals(
            [
                "id1" => "first",
                "id2" => "second",
                "controller" => "Dummycontroller",
                "action" => "silent",
            ],
            \Gaslawork\Request::current()->getParams()
        );
    }

    public function testFetchParamFromController()
    {
        $this->expectOutputString("mootest");

        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller/:action/:id", "\Gaslawork\Tests\\"))
        );

        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/echoId/mootest", "GET"]);
    }

    public function testFetchNonExistingParams()
    {
        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller/:action", "\Gaslawork\Tests\\"))
        );
        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/silent", "GET"]);

        $this->assertNull(\Gaslawork\Request::current()->getParam("id1"));
        $this->assertNull(\Gaslawork\Request::current()->getParam("id2"));

        $this->assertEquals(
            [
                "controller" => "Dummycontroller",
                "action" => "silent",
            ],
            \Gaslawork\Request::current()->getParams()
        );
    }

    public function testFetchNonExisstingParamFromController()
    {
        $this->expectOutputString("");

        $app = \Gaslawork\App::instance(
            (new Router)
                ->add(new Route("/:controller/:action/:no", "\Gaslawork\Tests\\"))
        );

        PHPUnitUtil::callMethod($app, "findAndExecuteRoute", ["Dummycontroller/echoId/mootest", "GET"]);
    }
}
