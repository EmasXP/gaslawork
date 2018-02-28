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

		$route = $routes->findRoute("");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Index", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindCustomController()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Hello", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindCustomControllerAndAction()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello/world");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Hello", $target->getController());
		$this->assertEquals("action_world", $target->getAction());
	}


	public function testFindDefaultWithoutControllerInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Index", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindCustomActionWithoutControllerInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Index", $target->getController());
		$this->assertEquals("action_hello", $target->getAction());
	}


	public function testFindDefaultWithoutActionInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Index", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindCustomControllerWithoutActionInTarget()
	{
		$routes = (new Routes)
			->add(new Route("/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\Hello", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindingCorrectOfTwoRoutes()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/world");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\World\Index", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindingCorrectOfTwoRoutesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/hello");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Hello\Index", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomController()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/world/foo");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\World\Foo", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:id", "\World\\"));

		$route = $routes->findRoute("/hello/foo");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Hello\Foo", $target->getController());
		$this->assertEquals("action_index", $target->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAndAction()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:action/:id", "\World\\"));

		$route = $routes->findRoute("/world/foo/bar");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\World\Foo", $target->getController());
		$this->assertEquals("action_bar", $target->getAction());
	}


	public function testFindingCorrectOfTwoRoutesWithCustomControllerAndActionAgain()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:action/:id", "\Hello\\"))
			->add(new Route("/world/:controller/:action/:id", "\World\\"));

		$route = $routes->findRoute("/hello/foo/bar");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Hello\Foo", $target->getController());
		$this->assertEquals("action_bar", $target->getAction());
	}


	public function testFindingCorrectOfTwoComplexRoutes()
	{
		$routes = (new Routes)
			->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
			->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

		$route = $routes->findRoute("/d/hello/e/world/f");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Second\Hello", $target->getController());
		$this->assertEquals("action_world", $target->getAction());
	}


	public function testFindingCorrectOfTwoComplexRoutesAgain()
	{
		$routes = (new Routes)
			->add(new Route("/a/:controller/b/:action/c/:id", "\First\\"))
			->add(new Route("/d/:controller/e/:action/f/:id", "\Second\\"));

		$route = $routes->findRoute("/a/hello/b/world/c");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\First\Hello", $target->getController());
		$this->assertEquals("action_world", $target->getAction());
	}


	public function testFindingRouteWhereActionIsBeforeController()
	{
		$routes = (new Routes)
			->add(new Route("/:action/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/hello/world");

		$this->assertTrue($route !== null);

		$target = $route->getTarget();

		$this->assertEquals("\Application\Controller\World", $target->getController());
		$this->assertEquals("action_hello", $target->getAction());
	}


	public function testNotFindingRoute()
	{
		$routes = (new Routes)
			->add(new Route("/hello/:controller/:id", "\Application\Controller\\"));

		$route = $routes->findRoute("/world");

		$this->assertNull($route);
	}

}
