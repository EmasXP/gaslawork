<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use \Gaslawork\Routing\Routes;
use \Gaslawork\Routing\Route;
use \Gaslawork\Routing\RequestUri;


final class RoutingTest extends TestCase {

	public function testFindDefault()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindCustomController()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindCustomControllerAndAction()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
		$this->assertEquals("world", $route->getAction());
	}


	public function testFindDefaultWithoutControllerInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindCustomActionWithoutControllerInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("hello", $route->getAction());
	}


	public function testFindDefaultWithoutActionInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindCustomControllerWithoutActionInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutes()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\World\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Hello\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomController()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/world/foo");

		$this->assertTrue($route !== null);

		$this->assertEquals("\World\Foo", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/hello/foo");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Hello\Foo", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAndAction()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:action/:id", "\World\\"));

		$route = $routes->findRoute("/world/foo/bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\World\Foo", $route->getController());
		$this->assertEquals("bar", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAndActionAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:action/:id", "\World\\"));

		$route = $routes->findRoute("/hello/foo/bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Hello\Foo", $route->getController());
		$this->assertEquals("bar", $route->getAction());
	}


	public function testFindingCorrectOfTwoComplexRoutes()
	{
		$routes = (new Routes)
			->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
			->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

		$route = $routes->findRoute("/d/hello/e/world/f");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Hello", $route->getController());
		$this->assertEquals("world", $route->getAction());
	}


	public function testFindingCorrectOfTwoComplexRoutesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
			->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

		$route = $routes->findRoute("/a/hello/b/world/c");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
		$this->assertEquals("world", $route->getAction());
	}


	public function testFindingRouteWhereActionIsBeforeController()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\World", $route->getController());
		$this->assertEquals("hello", $route->getAction());
	}


	public function testNotFindingRoute()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/world");

		$this->assertNull($route);
	}


	public function testFindingFirstRouteWhenSeveralMatches()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\First\\"))
			->add(new Route("/:controller/:action/:id", "\Second\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
	}


	public function testFindingFirstRouteWhenSeveralMatchesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\First\\"))
			->add(new Route("/hello/:action/:id", "\Second\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
	}


	public function testFindingFirstRouteWhenSeveralMatchesOnceAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:action/:id", "\First\\"))
			->add(new Route("/:controller/:action/:id", "\Second\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Index", $route->getController());
	}


	public function testFindingRouteWithoutSlashPrefix()
	{
		$routes = (new Routes)
			->add(new Route(":controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
	}


	public function testFindingRouteWithoutSlashPrefixAgain()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
	}


	public function testFindingRouteWithoutSlashPrefixOnceAgain()
	{
		$routes = (new Routes)
			->add(new Route(":controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
	}


	public function testWhtelist()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Bar", $route->getController());
	}


	public function testWhtelistAgain()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("foo");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Foo", $route->getController());
	}


	public function testWhtelistNoMatch()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("hello");

		$this->assertNull($route);
	}


	public function testWhtelistMatchGenericRoute()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Third\Hello", $route->getController());
	}


	public function testWhtelistCustomParameter()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\First\\"))
					->setWhitelist(array(
						"id" => array("12"),
					))
			)
			->add(new Route(":controller/:id", "\Second\\"));

		$route = $routes->findRoute("hello/12");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
		$this->assertEquals("12", $route->getParam("id"));
	}


	public function testWhtelistCustomParameterNoMatch()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\First\\"))
					->setWhitelist(array(
						"id" => array("12"),
					))
			)
			->add(new Route(":controller/:id", "\Second\\"));

		$route = $routes->findRoute("hello/13");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Hello", $route->getController());
	}


	public function testWhitelistSeveralValues()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setWhitelist(array(
						"id" => array("123", "abc"),
					))
			);

		$route = $routes->findRoute("hello/123");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Controller\Hello", $route->getController());
		$this->assertEquals("123", $route->getParam("id"));
	}


	public function testWhitelistSeveralValuesAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setWhitelist(array(
						"id" => array("123", "abc"),
					))
			);

		$route = $routes->findRoute("hello/abc");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Controller\Hello", $route->getController());
		$this->assertEquals("abc", $route->getParam("id"));
	}


	public function testWhitelistSeveralValuesOnceAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setWhitelist(array(
						"id" => array("123", "abc"),
					))
			);

		$route = $routes->findRoute("hello/world");

		$this->assertNull($route);
	}


	public function testBlacklist()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("foo");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Foo", $route->getController());
	}


	public function testBlacklistAgain()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Bar", $route->getController());
	}


	public function testBlacklistOnceAgain()
	{
		$routes = (new Routes)
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

		$route = $routes->findRoute("hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
	}


	public function testBlacklistMatchGenericRoute()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:action/:id", "\First\\"))
					->setBlacklist(array(
						"controller" => array("bar"),
					))
			)
			->add(new Route(":controller/:action/:id", "\Second\\"));

		$route = $routes->findRoute("bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Bar", $route->getController());
	}


	public function testBlacklistCustomParameter()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\First\\"))
					->setBlacklist(array(
						"id" => array("12"),
					))
			)
			->add(new Route(":controller/:id", "\Second\\"));

		$route = $routes->findRoute("hello/1024");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
		$this->assertEquals("1024", $route->getParam("id"));
	}


	public function testBlacklistCustomParameterNoMatch()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\First\\"))
					->setBlacklist(array(
						"id" => array("12"),
					))
			)
			->add(new Route(":controller/:id", "\Second\\"));

		$route = $routes->findRoute("hello/12");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Hello", $route->getController());
	}


	public function testBlacklistSeveralValues()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setBlacklist(array(
						"id" => array("123", "abc"),
					))
			);

		$route = $routes->findRoute("hello/123");

		$this->assertNull($route);
	}


	public function testBlacklistSeveralValuesAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setBlacklist(array(
						"id" => array("123", "abc"),
					))
			);

		$route = $routes->findRoute("hello/abc");

		$this->assertNull($route);
	}


	public function testBlacklistSeveralValuesOnceAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setBlacklist(array(
						"id" => array("123", "abc"),
					))
			);

		$route = $routes->findRoute("hello/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Controller\Hello", $route->getController());
		$this->assertEquals("world", $route->getParam("id"));
	}


	public function testWhitelistAndBlacklistCombination()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id", "\Controller\\"))
					->setWhitelist(array(
						"id" => array("123"),
					))
					->setBlacklist(array(
						"id" => array("123"),
					))
			);

		$route = $routes->findRoute("hello/123");

		$this->assertNull($route);
	}


	public function testWhitelistAndBlacklistCombinationAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id1/:id2", "\Controller\\"))
					->setWhitelist(array(
						"id1" => array("123"),
					))
					->setBlacklist(array(
						"id2" => array("abc"),
					))
			);

		$route = $routes->findRoute("hello/123/abcd");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Controller\Hello", $route->getController());
		$this->assertEquals("123", $route->getParam("id1"));
		$this->assertEquals("abcd", $route->getParam("id2"));
	}


	public function testWhitelistAndBlacklistCombinationOnceAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id1/:id2", "\Controller\\"))
					->setWhitelist(array(
						"id1" => array("123"),
					))
					->setBlacklist(array(
						"id2" => array("abc"),
					))
			);

		$route = $routes->findRoute("hello/123/abc");

		$this->assertNull($route);
	}


	public function testWhitelistAndBlacklistCombinationYetAgain()
	{
		$routes = (new Routes)
			->add(
				(new Route(":controller/:id1/:id2", "\Controller\\"))
					->setWhitelist(array(
						"id1" => array("123"),
					))
					->setBlacklist(array(
						"id2" => array("abc"),
					))
			);

		$route = $routes->findRoute("hello/1234/abcd");

		$this->assertNull($route);
	}


	public function testChangeDefaultControllerAndAction()
	{
		$route = (new Route("/:controller/:action", "\Controller\\"))
			->setDefaults(array(
				"controller" => "defaultcontroller",
				"action" => "defaultaction",
			));

		$route->checkRoute(new RequestUri("/"), null);

		$this->assertEquals("\Controller\Defaultcontroller", $route->getController());
		$this->assertEquals("defaultaction", $route->getAction());
	}


	public function testChangeMissingDefaultController()
	{
		$routes = (new Routes)->add(
			(new Route("/:controller/:action", "\Controller\\"))
				->setDefaults(array(
					"action" => "defaultaction",
				))
		);

		$route = $routes->findRoute("/");

		$this->assertNull($route);
	}


	public function testChangeMissingDefaultAction()
	{
		$routes = (new Routes)->add(
			(new Route("/:controller/:action", "\Controller\\"))
				->setDefaults(array(
					"controller" => "defaultcontroller",
				))
		);

		$route = $routes->findRoute("/");

		$this->assertEquals("\Controller\Defaultcontroller", $route->getController());
		$this->assertNull($route->getAction());
	}


	public function testDefaultCustomParameter()
	{
		$routes = (new Routes)->add(
			(new Route("/:controller/:action/:id", "\Controller\\"))
				->setDefaults(array(
					"controller" => "index",
					"action" => "index",
					"id" => "123"
				))
		);

		$route = $routes->findRoute("/");

		$this->assertEquals("\Controller\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
		$this->assertEquals("123", $route->getParam("id"));
	}


	public function testDefaultCustomParameterButMissingInPath()
	{
		$routes = (new Routes)->add(
			(new Route("/:controller/:action", "\Controller\\"))
				->setDefaults(array(
					"controller" => "index",
					"action" => "index",
					"id" => "123"
				))
		);

		$route = $routes->findRoute("/");

		$this->assertEquals("\Controller\Index", $route->getController());
		$this->assertEquals("index", $route->getAction());
		$this->assertEquals("123", $route->getParam("id"));
	}


	public function testMissingControllerInUriButSetDefault()
	{
		$routes = (new Routes)->add(
			(new Route("/:action", "\Controller\\"))
				->setDefaults(array(
					"controller" => "hello",
					"action" => "index",
				))
		);

		$route = $routes->findRoute("/");

		$this->assertEquals("\Controller\Hello", $route->getController());
		$this->assertEquals("index", $route->getAction());
	}


	public function testRequiredFail()
	{
		$route = (new Route("/:controller/:id", "\Controller\\"))
			->setRequired(array("id"));

		$this->assertFalse(
			$route->checkRoute(new RequestUri("/"), null)
		);
	}


	public function testRequiredFailAgain()
	{
		$route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
			->setRequired(array("id2"));

		$this->assertFalse(
			$route->checkRoute(new RequestUri("/hello/world"), null)
		);
	}


	public function testRequiredFailTwoParams()
	{
		$route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
			->setRequired(array("id1", "id2"));

		$this->assertFalse(
			$route->checkRoute(new RequestUri("/hello/world"), null)
		);
	}


	public function testRequiredSuccess()
	{
		$route = (new Route("/:controller/:id", "\Controller\\"))
			->setRequired(array("id"));

		$this->assertTrue(
			$route->checkRoute(new RequestUri("/hello/world"), null)
		);
	}


	public function testRequiredSuccessAgain()
	{
		$route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
			->setRequired(array("id2"));

		$this->assertTrue(
			$route->checkRoute(new RequestUri("/hello/world/foo"), null)
		);
	}


	public function testRequiredSuccessTwoParams()
	{
		$route = (new Route("/:controller/:id1/:id2", "\Controller\\"))
			->setRequired(array("id1", "id2"));

		$this->assertTrue(
			$route->checkRoute(new RequestUri("/hello/world/foo"), null)
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

		$this->assertTrue(
			$route->checkRoute(new RequestUri("/hello/world"), null)
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
