<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use \Gaslawork\Routing\Routes;
use \Gaslawork\Routing\Route;


final class RoutingTest extends TestCase {

	public function testFindDefault()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindCustomController()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindCustomControllerAndAction()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
		$this->assertEquals("worldAction", $route->getAction());
	}


	public function testFindDefaultWithoutControllerInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindCustomActionWithoutControllerInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("helloAction", $route->getAction());
	}


	public function testFindDefaultWithoutActionInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Index", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindCustomControllerWithoutActionInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\Hello", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutes()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\World\Index", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Hello\Index", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomController()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/world/foo");

		$this->assertTrue($route !== null);

		$this->assertEquals("\World\Foo", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/hello/foo");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Hello\Foo", $route->getController());
		$this->assertEquals("indexAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAndAction()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:action/:id", "\World\\"));

		$route = $routes->findRoute("/world/foo/bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\World\Foo", $route->getController());
		$this->assertEquals("barAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAndActionAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:action/:id", "\World\\"));

		$route = $routes->findRoute("/hello/foo/bar");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Hello\Foo", $route->getController());
		$this->assertEquals("barAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoComplexRoutes()
	{
		$routes = (new Routes)
			->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
			->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

		$route = $routes->findRoute("/d/hello/e/world/f");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Second\Hello", $route->getController());
		$this->assertEquals("worldAction", $route->getAction());
	}


	public function testFindingCorrectOfTwoComplexRoutesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
			->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

		$route = $routes->findRoute("/a/hello/b/world/c");

		$this->assertTrue($route !== null);

		$this->assertEquals("\First\Hello", $route->getController());
		$this->assertEquals("worldAction", $route->getAction());
	}


	public function testFindingRouteWhereActionIsBeforeController()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello/world");

		$this->assertTrue($route !== null);

		$this->assertEquals("\Application\Controller\World", $route->getController());
		$this->assertEquals("helloAction", $route->getAction());
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

}
