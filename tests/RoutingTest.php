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
            ->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("/");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindCustomController()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindCustomControllerAndAction()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("/hello/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
        $this->assertEquals("world", $route->getAction());
    }


    public function testFindDefaultWithoutControllerInTarget()
    {
        $routes = (new Router)
            ->add(new Route("/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("/");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindCustomActionWithoutControllerInTarget()
    {
        $routes = (new Router)
            ->add(new Route("/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("hello", $route->getAction());
    }


    public function testFindDefaultWithoutActionInTarget()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:id", "\Application\Controller\\"));

        $route = $routes->find("/");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindCustomControllerWithoutActionInTarget()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:id", "\Application\Controller\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutes()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:id", "\Hello\\"))
            ->add(new Route("/world/:controller/:id", "\World\\"));

        $route = $routes->find("/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\World\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesAgain()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:id", "\Hello\\"))
            ->add(new Route("/world/:controller/:id", "\World\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Hello\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomController()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:id", "\Hello\\"))
            ->add(new Route("/world/:controller/:id", "\World\\"));

        $route = $routes->find("/world/foo");

        $this->assertTrue($route !== null);

        $this->assertEquals("\World\Foo", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomControllerAgain()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:id", "\Hello\\"))
            ->add(new Route("/world/:controller/:id", "\World\\"));

        $route = $routes->find("/hello/foo");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Hello\Foo", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomControllerAndAction()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
            ->add(new Route("/world/:controller/:action/:id", "\World\\"));

        $route = $routes->find("/world/foo/bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\World\Foo", $route->getController());
        $this->assertEquals("bar", $route->getAction());
    }


    public function testFindingCorrectOfTwoRoutesWithCustomControllerAndActionAgain()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
            ->add(new Route("/world/:controller/:action/:id", "\World\\"));

        $route = $routes->find("/hello/foo/bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Hello\Foo", $route->getController());
        $this->assertEquals("bar", $route->getAction());
    }


    public function testFindingCorrectOfTwoComplexRoutes()
    {
        $routes = (new Router)
            ->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
            ->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

        $route = $routes->find("/d/hello/e/world/f");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Hello", $route->getController());
        $this->assertEquals("world", $route->getAction());
    }


    public function testFindingCorrectOfTwoComplexRoutesAgain()
    {
        $routes = (new Router)
            ->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
            ->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

        $route = $routes->find("/a/hello/b/world/c");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
        $this->assertEquals("world", $route->getAction());
    }


    public function testFindingRouteWhereActionIsBeforeController()
    {
        $routes = (new Router)
            ->add(new Route("/:action/:controller/:id", "\Application\Controller\\"));

        $route = $routes->find("/hello/world");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\World", $route->getController());
        $this->assertEquals("hello", $route->getAction());
    }


    public function testNotFindingRoute()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:controller/:id", "\Application\Controller\\"));

        $route = $routes->find("/world");

        $this->assertNull($route);
    }


    public function testFindingFirstRouteWhenSeveralMatches()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:action/:id", "\First\\"))
            ->add(new Route("/:controller/:action/:id", "\Second\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
    }


    public function testFindingFirstRouteWhenSeveralMatchesAgain()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:action/:id", "\First\\"))
            ->add(new Route("/hello/:action/:id", "\Second\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
    }


    public function testFindingFirstRouteWhenSeveralMatchesOnceAgain()
    {
        $routes = (new Router)
            ->add(new Route("/hello/:action/:id", "\First\\"))
            ->add(new Route("/:controller/:action/:id", "\Second\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Index", $route->getController());
    }


    public function testFindingRouteWithoutSlashPrefix()
    {
        $routes = (new Router)
            ->add(new Route(":controller/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("/hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
    }


    public function testFindingRouteWithoutSlashPrefixAgain()
    {
        $routes = (new Router)
            ->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
    }


    public function testFindingRouteWithoutSlashPrefixOnceAgain()
    {
        $routes = (new Router)
            ->add(new Route(":controller/:action/:id", "\Application\Controller\\"));

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Application\Controller\Hello", $route->getController());
    }


    public function testWhtelist()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setWhitelist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
                    ->setWhitelist(array(
                        "controller" => array("bar"),
                    ))
            )
            ->add(new Route(":controller/:action/:id", "\Third\\"));

        $route = $routes->find("hello");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Third\Hello", $route->getController());
    }


    public function testWhtelistCustomParameter()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\\"))
                    ->setWhitelist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(new Route(":controller/:id", "\Second\\"));

        $route = $routes->find("hello/12");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
        $this->assertEquals("12", $route->getParam("id"));
    }


    public function testWhtelistCustomParameterNoMatch()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\\"))
                    ->setWhitelist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(new Route(":controller/:id", "\Second\\"));

        $route = $routes->find("hello/13");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Hello", $route->getController());
    }


    public function testWhitelistSeveralValues()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setBlacklist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setBlacklist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setBlacklist(array(
                        "controller" => array("foo"),
                    ))
            )
            ->add(
                (new Route(":controller/:action/:id", "\Second\\"))
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
                (new Route(":controller/:action/:id", "\First\\"))
                    ->setBlacklist(array(
                        "controller" => array("bar"),
                    ))
            )
            ->add(new Route(":controller/:action/:id", "\Second\\"));

        $route = $routes->find("bar");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Bar", $route->getController());
    }


    public function testBlacklistCustomParameter()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\\"))
                    ->setBlacklist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(new Route(":controller/:id", "\Second\\"));

        $route = $routes->find("hello/1024");

        $this->assertTrue($route !== null);

        $this->assertEquals("\First\Hello", $route->getController());
        $this->assertEquals("1024", $route->getParam("id"));
    }


    public function testBlacklistCustomParameterNoMatch()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\First\\"))
                    ->setBlacklist(array(
                        "id" => array("12"),
                    ))
            )
            ->add(new Route(":controller/:id", "\Second\\"));

        $route = $routes->find("hello/12");

        $this->assertTrue($route !== null);

        $this->assertEquals("\Second\Hello", $route->getController());
    }


    public function testBlacklistSeveralValues()
    {
        $routes = (new Router)
            ->add(
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:id", "\Controller\\"))
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
                (new Route(":controller/:id1/:id2", "\Controller\\"))
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
                (new Route(":controller/:id1/:id2", "\Controller\\"))
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
                (new Route(":controller/:id1/:id2", "\Controller\\"))
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
        $route = (new Route("/:controller/:action", "\Controller\\"))
            ->setDefaults(array(
                "controller" => "defaultcontroller",
                "action" => "defaultaction",
            ));

        $route->check(new RequestUri("/"), null);

        $this->assertEquals("\Controller\Defaultcontroller", $route->getController());
        $this->assertEquals("defaultaction", $route->getAction());
    }


    public function testChangeMissingDefaultController()
    {
        $routes = (new Router)->add(
            (new Route("/:controller/:action", "\Controller\\"))
                ->setDefaults(array(
                    "action" => "defaultaction",
                ))
        );

        $route = $routes->find("/");

        $this->assertNull($route);
    }


    public function testChangeMissingDefaultAction()
    {
        $routes = (new Router)->add(
            (new Route("/:controller/:action", "\Controller\\"))
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
            (new Route("/:controller/:action/:id", "\Controller\\"))
                ->setDefaults(array(
                    "controller" => "index",
                    "action" => "index",
                    "id" => "123"
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
        $this->assertEquals("123", $route->getParam("id"));
    }


    public function testDefaultCustomParameterButMissingInPath()
    {
        $routes = (new Router)->add(
            (new Route("/:controller/:action", "\Controller\\"))
                ->setDefaults(array(
                    "controller" => "index",
                    "action" => "index",
                    "id" => "123"
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Index", $route->getController());
        $this->assertEquals("index", $route->getAction());
        $this->assertEquals("123", $route->getParam("id"));
    }


    public function testMissingControllerInUriButSetDefault()
    {
        $routes = (new Router)->add(
            (new Route("/:action", "\Controller\\"))
                ->setDefaults(array(
                    "controller" => "hello",
                    "action" => "index",
                ))
        );

        $route = $routes->find("/");

        $this->assertEquals("\Controller\Hello", $route->getController());
        $this->assertEquals("index", $route->getAction());
    }


    public function testRequiredFail()
    {
        $route = (new Route("/:controller/:id", "\Controller\\"))
            ->setRequired(array("id"));

        $this->assertNull(
            $route->check(new RequestUri("/"), null)
        );
    }


    public function testRequiredFailAgain()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
            ->setRequired(array("id2"));

        $this->assertNull(
            $route->check(new RequestUri("/hello/world"), null)
        );
    }


    public function testRequiredFailTwoParams()
    {
        $route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
            ->setRequired(array("id1", "id2"));

        $this->assertNull(
            $route->check(new RequestUri("/hello/world"), null)
        );
    }


    public function testRequiredSuccess()
    {
        $route = (new Route("/:controller/:id", "\Controller\\"))
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
        $route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
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
        $route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
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
        $route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
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

}
