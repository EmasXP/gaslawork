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
}
