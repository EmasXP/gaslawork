<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use \Gaslawork\Routing\Router;
use \Gaslawork\Routing\Route;
use \Gaslawork\Routing\RequestUri;


final class RoutingTest extends TestCase {

    public function testFindDefault()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:action/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindCustomController()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:action/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindCustomControllerAndAction()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:action/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
        $this->assertEquals("worldAction", $route->getAction());
    }


    public function testFindDefaultWithoutControllerInTarget()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:action/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindCustomActionWithoutControllerInTarget()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:action/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("helloAction", $route->getAction());
    }


    public function testFindDefaultWithoutActionInTarget()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindCustomControllerWithoutActionInTarget()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutes()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:id", "\Hello\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/world/:controller/:id", "\World\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\World\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:id", "\Hello\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/world/:controller/:id", "\World\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Hello\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomController()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:id", "\Hello\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/world/:controller/:id", "\World\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/world/foo");

        $this->assertTrue($route !== null);

        $this->assertEquals("\World\Foo", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomControllerAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:id", "\Hello\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/world/:controller/:id", "\World\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello/foo");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Hello\Foo", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomControllerAndAction()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:action/:id", "\Hello\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/world/:controller/:action/:id", "\World\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/world/foo/bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\World\Foo", $route->getController());
        $this->assertEquals("barAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomControllerAndActionAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:action/:id", "\Hello\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/world/:controller/:action/:id", "\World\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello/foo/bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Hello\Foo", $route->getController());
        $this->assertEquals("barAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoComplexRoutes()
    {
        $routes = (new Router)
            ->add(
                (new Route("/a/:controller/b/:action/c/:id", "\First\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/d/:controller/e/:action/f/:id", "\Second\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/d/hello/e/world/f");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Hello", $route->getController());
        $this->assertEquals("worldAction", $route->getAction());
    }


    public function testFindingCorrectOfTwoComplexRoutesAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/a/:controller/b/:action/c/:id", "\First\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/d/:controller/e/:action/f/:id", "\Second\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/a/hello/b/world/c");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
        $this->assertEquals("worldAction", $route->getAction());
    }


    public function testFindingRouteWhereActionIsBeforeController()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:action/:controller/:id", "\Application\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\World", $route->getController());
        $this->assertEquals("helloAction", $route->getAction());
    }


    public function testNotFindingRoute()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:controller/:id", "\Application\Controller\\"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/world");

        $this->assertNull($route);
    }


    public function testFindingFirstRouteWhenSeveralMatches()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/:controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
    }


    public function testFindingFirstRouteWhenSeveralMatchesAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/hello/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
    }


    public function testFindingFirstRouteWhenSeveralMatchesOnceAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/hello/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            )
            ->add(
                (new Route("/:controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Index", $route->getController());
    }


    public function testFindingRouteWithoutSlashPrefix()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\Application\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
    }


    public function testFindingRouteWithoutSlashPrefixAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:action/:id", "\Application\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
    }


    public function testFindingRouteWithoutSlashPrefixOnceAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\Application\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
    }


    public function testWhtelist()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("bar"),
                    ))
            );

        $route = $routes->find("bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Bar", $route->getController());
    }


    public function testWhtelistAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("bar"),
                    ))
            );

        $route = $routes->find("foo");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Foo", $route->getController());
    }


    public function testWhtelistNoMatch()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("bar"),
                    ))
            );

        $route = $routes->find("hello");

        $this->assertNull($route);
    }


    public function testWhtelistMatchGenericRoute()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "controller" => array("bar"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Third\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Third\Hello", $route->getController());
    }


    public function testWhtelistCustomParameter()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(
                (new Route(":controller/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello/12");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
        $this->assertEquals("12", $route->getParam("id"));
    }


    public function testWhtelistCustomParameterNoMatch()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(
                (new Route(":controller/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello/13");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Hello", $route->getController());
    }


    public function testWhitelistSeveralValues()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id" => array("123", "abc"),
                    ))
            );

        $route = $routes->find("hello/123");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Controller\Hello", $route->getController());
        $this->assertEquals("123", $route->getParam("id"));
    }


    public function testWhitelistSeveralValuesAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id" => array("123", "abc"),
                    ))
            );

        $route = $routes->find("hello/abc");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Controller\Hello", $route->getController());
        $this->assertEquals("abc", $route->getParam("id"));
    }


    public function testWhitelistSeveralValuesOnceAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id" => array("123", "abc"),
                    ))
            );

        $route = $routes->find("hello/world");

        $this->assertNull($route);
    }


    public function testBlacklist()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("bar"),
                    ))
            );

        $route = $routes->find("foo");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Foo", $route->getController());
    }


    public function testBlacklistAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("bar"),
                    ))
            );

        $route = $routes->find("bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Bar", $route->getController());
    }


    public function testBlacklistOnceAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("bar"),
                    ))
            );

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
    }


    public function testBlacklistMatchGenericRoute()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "controller" => array("bar"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Bar", $route->getController());
    }


    public function testBlacklistCustomParameter()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(
                (new Route(":controller/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello/1024");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
        $this->assertEquals("1024", $route->getParam("id"));
    }


    public function testBlacklistCustomParameterNoMatch()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(
                (new Route(":controller/:id", "\Second\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("hello/12");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Hello", $route->getController());
    }


    public function testBlacklistSeveralValues()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "id" => array("123", "abc"),
                    ))
            );

        $route = $routes->find("hello/123");

        $this->assertNull($route);
    }


    public function testBlacklistSeveralValuesAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "id" => array("123", "abc"),
                    ))
            );

        $route = $routes->find("hello/abc");

        $this->assertNull($route);
    }


    public function testBlacklistSeveralValuesOnceAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setBlacklist(array(
                        "id" => array("123", "abc"),
                    ))
            );

        $route = $routes->find("hello/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Controller\Hello", $route->getController());
        $this->assertEquals("world", $route->getParam("id"));
    }


    public function testWhitelistAndBlacklistCombination()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id" => array("123"),
                    ))
                    ->setBlacklist(array(
                        "id" => array("123"),
                    ))
            );

        $route = $routes->find("hello/123");

        $this->assertNull($route);
    }


    public function testWhitelistAndBlacklistCombinationAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id1/:id2", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id1" => array("123"),
                    ))
                    ->setBlacklist(array(
                        "id2" => array("abc"),
                    ))
            );

        $route = $routes->find("hello/123/abcd");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Controller\Hello", $route->getController());
        $this->assertEquals("123", $route->getParam("id1"));
        $this->assertEquals("abcd", $route->getParam("id2"));
    }


    public function testWhitelistAndBlacklistCombinationOnceAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id1/:id2", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id1" => array("123"),
                    ))
                    ->setBlacklist(array(
                        "id2" => array("abc"),
                    ))
            );

        $route = $routes->find("hello/123/abc");

        $this->assertNull($route);
    }


    public function testWhitelistAndBlacklistCombinationYetAgain()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id1/:id2", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
                    ->setWhitelist(array(
                        "id1" => array("123"),
                    ))
                    ->setBlacklist(array(
                        "id2" => array("abc"),
                    ))
            );

        $route = $routes->find("hello/1234/abcd");

        $this->assertNull($route);
    }


    public function testChangeDefaultControllerAndAction()
    {
        $route = (new Route("/:controller/:action", "\Controller\{+controller}", "{action}Action"))
            ->setDefaults(array(
                "controller" => "defaultcontroller",
                "action" => "defaultaction",
            ));

        $route->check(new RequestUri("/"), null);

        $this->assertEquals("\Controller\Defaultcontroller", $route->getController());
        $this->assertEquals("defaultactionAction", $route->getAction());
    }


    public function testChangeMissingDefaultAction()
    {
        $routes = (new Router)->add(
            (new Route("/:controller/:action", "\Controller\{+controller}"))
                ->setDefaults(array(
                    "controller" => "defaultcontroller",
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Defaultcontroller", $route->getController());
        $this->assertNull($route->getAction());
    }


    public function testDefaultCustomParameter()
    {
        $routes = (new Router)->add(
            (new Route("/:controller/:action/:id", "\Controller\{+controller}", "{action}Action"))
                ->setDefaults(array(
                    "controller" => "index",
                    "action" => "index",
                    "id" => "123"
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
        $this->assertEquals("123", $route->getParam("id"));
    }


    public function testDefaultCustomParameterButMissingInPath()
    {
        $routes = (new Router)->add(
            (new Route("/:controller/:action", "\Controller\{+controller}", "{action}Action"))
                ->setDefaults(array(
                    "controller" => "index",
                    "action" => "index",
                    "id" => "123"
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Index", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
        $this->assertEquals("123", $route->getParam("id"));
    }


    public function testMissingControllerInUriButSetDefault()
    {
        $routes = (new Router)->add(
            (new Route("/:action", "\Controller\{+controller}", "{action}Action"))
                ->setDefaults(array(
                    "controller" => "hello",
                    "action" => "index",
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Hello", $route->getController());
        $this->assertEquals("indexAction", $route->getAction());
    }


    public function testRequiredFail()
    {
        $route = (new Route("/:controller/:id", "\Controller\{+controller}"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
            ->setRequired(array("id"));

        $this->assertNull(
            $route->check(new RequestUri("/"), null)
        );
    }


    public function testRequiredFailAgain()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\{+controller}"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
            ->setRequired(array("id2"));

        $this->assertNull(
            $route->check(new RequestUri("/hello/world"), null)
        );
    }


    public function testRequiredFailTwoParams()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\{+controller}"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
            ->setRequired(array("id1", "id2"));

        $this->assertNull(
            $route->check(new RequestUri("/hello/world"), null)
        );
    }


    public function testRequiredSuccess()
    {
        $route = (new Route("/:controller/:id", "\Controller\{+controller}"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
            ->setRequired(array("id"));

        $route_data = $route->check(new RequestUri("/hello/world"), null);

        $this->assertNotNull($route_data);
        $this->assertInstanceOf(
            \Gaslawork\Routing\RouteDataInterface::class,
            $route_data
        );
    }


    public function testRequiredSuccessAgain()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\{+controller}"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
            ->setRequired(array("id2"));

        $route_data = $route->check(new RequestUri("/hello/world/foo"), null);

        $this->assertNotNull($route_data);
        $this->assertInstanceOf(
            \Gaslawork\Routing\RouteDataInterface::class,
            $route_data
        );
    }


    public function testRequiredSuccessTwoParams()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\{+controller}"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
            ->setRequired(array("id1", "id2"));

        $route_data = $route->check(new RequestUri("/hello/world/foo"), null);

        $this->assertNotNull($route_data);
        $this->assertInstanceOf(
            \Gaslawork\Routing\RouteDataInterface::class,
            $route_data
        );
    }


    public function testRequiredSuccessTwoParamsWithDefaultOnSecond()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\{+controller}"))
            ->setRequired(array("id1", "id2"))
            ->setDefaults(array(
                "controller" => "index",
                "id2" => "bar",
            ));

        $route_data = $route->check(new RequestUri("/hello/world"), null);

        $this->assertNotNull($route_data);
        $this->assertInstanceOf(
            \Gaslawork\Routing\RouteDataInterface::class,
            $route_data
        );
    }


    public function testRouteUriExplode()
    {
        $this->assertEquals(
            array("hello", "world"),
            (new RequestUri("hello/world"))
                ->getExploded()
        );
    }


    public function testRouteUriExplodeUsingBackslash()
    {
        $this->assertEquals(
            array("hello", "world"),
            (new RequestUri("hello\world"))
                ->getExploded()
        );
    }


    public function testRouteUriExplodeSlashPrefixAndSuffix()
    {
        $this->assertEquals(
            array("hello", "world"),
            (new RequestUri("/hello/world/"))
                ->getExploded()
        );
    }


    public function testRouteUriExplodeBackslashPrefixAndSuffix()
    {
        $this->assertEquals(
            array("hello", "world"),
            (new RequestUri("\\hello/world\\"))
                ->getExploded()
        );
    }


    public function testRouteUriExplodeMultipleSlashes()
    {
        $this->assertEquals(
            array("hello", "world", "foo", "bar"),
            (new RequestUri("////\\//hello/world\\foo/bar\\\\\\//"))
                ->getExploded()
        );
    }


    public function testFetchParamsAdvancedRoute()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:foo/:controller/:hello/:action/:world", "\Controller\{+controller}", "{action}Action"))
                    ->setDefaults([
                        "hello" => "ThisShouldNotBeFoundInThisTest",
                        "action" => "ThisShouldNotBeFoundEither",
                        "world" => "lmn",
                    ])
            );

        $route = $routes->find("abc/def/ghi/ijk");

        $this->assertTrue($route !== null);

        $this->assertEquals("abc", $route->getParam("foo"));
        $this->assertEquals("\Controller\\Def", $route->getController());
        $this->assertEquals("ghi", $route->getParam("hello"));
        $this->assertEquals("ijkAction", $route->getAction());
        $this->assertEquals("lmn", $route->getParam("world"));

        $this->assertEquals(
            [
                "foo" => "abc",
                "controller" => "def",
                "hello" => "ghi",
                "action" => "ijk",
                "world" => "lmn",
            ],
            $route->getParams()
        );
    }


    public function testFetchNonExistingParams()
    {
        $routes = (new Router)
            ->add(
                (new Route("/:controller/:id/:id2", "\Controller\{+controller}"))
                    ->setDefaults([
                        "controller" => "Index",
                        "action" => "index",
                    ])
            );

        $route = $routes->find("abc/def");

        $this->assertTrue($route !== null);

        $this->assertNull($route->getParam("foo"));
        $this->assertNull($route->getParam("id2"));
        $this->assertNull($route->getParam("id3"));
    }


    public function testRouteHandler()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route_data = $route->check(new RequestUri("/hello/world"), "GET");
        $this->assertEquals("\Controller\Hello\World", $route_data->getController());
    }


    public function testRouteHandlerUsingDefaultValues()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "Pizza",
        ]);
        $route_data = $route->check(new RequestUri("/hello"), "GET");
        $this->assertEquals("\Controller\Hello\Pizza", $route_data->getController());
    }


    public function testRouteHandlerUsingLowercaseDefaultValues()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/hello"), "GET");
        $this->assertEquals("\Controller\Hello\Pizza", $route_data->getController());
    }


    public function testRouteHandlerWithTrailingSlashInUri()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/hello/"), "GET");
        $this->assertEquals("\Controller\Hello\Pizza", $route_data->getController());
    }


    public function testRouteHandlerWithDoubleTrailingSlashInUri()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/hello//"), "GET");
        $this->assertEquals("\Controller\Hello\Pizza", $route_data->getController());
    }


    public function testRouteHandlerWithDoubleSlashAndParameterInUri()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/hello//moo"), "GET");
        $this->assertEquals("\Controller\Hello\Pizza", $route_data->getController());
    }


    public function testRouteHandlerUriOverrideDefaults()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "directory" => "kebab",
            "controller" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/hello/world"), "GET");
        $this->assertEquals("\Controller\Hello\World", $route_data->getController());
    }


    public function testRouteHandlerCaseMod()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{controller}");
        $route_data = $route->check(new RequestUri("/hello/world"), "GET");
        $this->assertEquals("\Controller\Hello\world", $route_data->getController());
    }


    public function testRouteHandlerCaseModUsingDefault()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{controller}");
        $route->setDefaults([
            "directory" => "kebab",
            "controller" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/"), "GET");
        $this->assertEquals("\Controller\Kebab\pizza", $route_data->getController());
    }


    public function testRouteHandlerCaseModUsingDefaultAgain()
    {
        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{controller}");
        $route->setDefaults([
            "directory" => "Kebab",
            "controller" => "Pizza",
        ]);
        $route_data = $route->check(new RequestUri("/"), "GET");
        $this->assertEquals("\Controller\Kebab\Pizza", $route_data->getController());
    }


    public function testRouteHandlerMissingHandlerParameter()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route_data = $route->check(new RequestUri("/hello/"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWithDoubleTrailingSlashInUri()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route_data = $route->check(new RequestUri("/hello//"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWithDoubleSlashAndParamInUri()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route_data = $route->check(new RequestUri("/hello//moo"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWhenDefaultIsEmpty()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "",
        ]);
        $route_data = $route->check(new RequestUri("/hello"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWhenDefaultIsEmptyWithDoubleTrailingSlash()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "",
        ]);
        $route_data = $route->check(new RequestUri("/hello//"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWhenDefaultIsEmptyWithDoubleSlashAndParameter()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => "",
        ]);
        $route_data = $route->check(new RequestUri("/hello//moo"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWhenDefaultIsNull()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => null
        ]);
        $route_data = $route->check(new RequestUri("/hello"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWhenDefaultIsNullWithDoubleTrailingSlash()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => null,
        ]);
        $route_data = $route->check(new RequestUri("/hello//"), "GET");
        $route_data->getController();
    }


    public function testRouteHandlerMissingHandlerParameterWhenDefaultIsNullWithDoubleSlashAndParameter()
    {
        $this->expectException(\Gaslawork\Exception\UndefinedRouteHandlerParameterException::class);
        $this->expectExceptionMessage("The parameter controller is needed by the route's handler but is undefined or empty.");

        $route = new Route("/:directory/:controller/:id", "\Controller\{+directory}\{+controller}");
        $route->setDefaults([
            "controller" => null,
        ]);
        $route_data = $route->check(new RequestUri("/hello//moo"), "GET");
        $route_data->getController();
    }


    public function testActionRouteHandler()
    {
        $route = new Route(":action", "", "action_{action}");
        $route->setDefaults([
            "action" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/"), "GET");
        $this->assertEquals("action_pizza", $route_data->getAction());
    }


    public function testNullActionRouteHandler()
    {
        $route = new Route(":action", "", null);
        $route->setDefaults([
            "action" => "pizza",
        ]);
        $route_data = $route->check(new RequestUri("/"), "GET");
        $this->assertNull($route_data->getAction());
    }


    public function testGetMinimumParts(): void
    {
        $this->assertEquals(
            1,
            (new Route("", ""))->getMinimumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/", ""))->getMinimumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/moo", ""))->getMinimumParts()
        );

        $this->assertEquals(
            1,
            (new Route("moo", ""))->getMinimumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/moo/", ""))->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/foo", ""))->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("moo/foo", ""))->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/foo/", ""))->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/foo/:bar", ""))->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/foo/:bar/:baz", ""))->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/foo/:bar/:baz/", ""))->getMinimumParts()
        );

        $this->assertEquals(
            4,
            (new Route("/moo/foo/:bar/baz/", ""))->getMinimumParts()
        );

        $this->assertEquals(
            4,
            (new Route("/moo/foo/:bar/baz/:hello", ""))->getMinimumParts()
        );

        $this->assertEquals(
            6,
            (new Route("/moo/foo/:bar/baz/:hello/world", ""))->getMinimumParts()
        );
    }

    public function testGetMinimumPartsWithRequiredParams(): void
    {
        $this->assertEquals(
            1,
            (new Route("/:foo", ""))
                ->setRequired([
                    "foo",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/hello/:foo", ""))
                ->setRequired([
                    "foo",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/hello/:foo/:bar", ""))
                ->setRequired([
                    "foo",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            3,
            (new Route("/hello/:foo/:bar", ""))
                ->setRequired([
                    "foo",
                    "bar",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            4,
            (new Route("/hello/:foo/:bar/world", ""))
                ->setRequired([
                    "foo",
                    "bar",
                ])
                ->getMinimumParts()
        );
    }

    public function testGetMinimumPartsWithRequiredParamsAndDefaults(): void
    {
        $this->assertEquals(
            1,
            (new Route("/:foo", ""))
                ->setRequired([
                    "foo",
                ])
                ->setDefaults([
                    "foo" => "123",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/:foo/:bar", ""))
                ->setRequired([
                    "foo",
                    "bar",
                ])
                ->setDefaults([
                    "foo" => "123",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/:foo/:bar", ""))
                ->setRequired([
                    "foo",
                    "bar",
                ])
                ->setDefaults([
                    "foo" => "123",
                    "bar" => "123",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/:foo/:hello/:bar", ""))
                ->setRequired([
                    "foo",
                    "bar",
                ])
                ->setDefaults([
                    "foo" => "123",
                    "bar" => "123",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/:foo/:hello/:bar", ""))
                ->setRequired([
                    "foo",
                    "bar",
                    "hello",
                ])
                ->setDefaults([
                    "foo" => "123",
                    "bar" => "123",
                ])
                ->getMinimumParts()
        );

        $this->assertEquals(
           1,
            (new Route("/:foo/:hello/:bar", ""))
                ->setRequired([
                    "foo",
                    "bar",
                    "hello",
                ])
                ->setDefaults([
                    "foo" => "123",
                    "bar" => "123",
                    "hello" => "123",
                ])
                ->getMinimumParts()
        );
    }

    public function testGetMaximumParts(): void
    {
        $this->assertEquals(
            1,
            (new Route("", ""))->getMaximumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/", ""))->getMaximumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/moo", ""))->getMaximumParts()
        );

        $this->assertEquals(
            1,
            (new Route("/moo/", ""))->getMaximumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/root", ""))->getMaximumParts()
        );

        $this->assertEquals(
            2,
            (new Route("/moo/root/", ""))->getMaximumParts()
        );

        $this->assertEquals(
            2,
            (new Route("moo/root/", ""))->getMaximumParts()
        );

        $this->assertEquals(
            2,
            (new Route("moo/root", ""))->getMaximumParts()
        );

        $this->assertEquals(
            5,
            (new Route("/:foo/bar/:hello/world/:pizza", ""))
                ->setRequired([
                    "foo",
                ])
                ->setDefaults([
                    "foo" => "123",
                    "hello" => "123",
                ])
                ->getMaximumParts()
        );

        $this->assertEquals(
            5,
            (new Route("/:foo/bar/:hello/world/:pizza", ""))
                ->setRequired([
                    "hello",
                ])
                ->setDefaults([
                    "pizza" => "123",
                ])
                ->getMaximumParts()
        );
    }

}
