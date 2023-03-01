<?php

namespace Gaslawork;

use Exception;
use Gaslawork\Container\Container;
use Gaslawork\Exception\InstanceAlreadyExistException;
use Gaslawork\Exception\NotFoundException;
use Gaslawork\Routing\RouterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;

class App
{

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param RouterInterface $router
     * @return void
     * @throws InstanceAlreadyExistException
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

        $this->container = new Container;

        $this->container->setSingleton(
            \Psr\Http\Message\RequestInterface::class,
            function (Container $c) {
                return $c->getProperty("app.request");
            }
        );

        // Non-singleton in case this value is used by a router
        $this->container->setCreate(
            Routing\RouteDataInterface::class,
            function (Container $c) {
                return $c->getProperty("app.route-data");
            }
        );

        $this->container->setProperty("app.container", $this->container);

        $this->container->setSingleton(
            Container::class,
            function (Container $c) {
                return $c->getProperty("app.container");
            }
        );

        $this->container->setSingleton(
            \Psr\Container\ContainerInterface::class,
            function (Container $c) {
                return $c->getProperty("app.container");
            }
        );

        $this->container->setProperty("app.instance", $this);

        $this->container->setSingleton(
            App::class,
            function (Container $c) {
                return $c->getProperty("app.instance");
            }
        );

        // Non-singleton because it's quicker, and it's only going to happen once per request anyway
        $this->container->setCreate(
            NotFoundHandlerInterface::class,
            function (Container $c) {
                return $c->get(DefaultNotFoundHandler::class);
            }
        );
    }

    /**
     * Set the router object.
     *
     * @param RouterInterface $router
     * @return $this
     */
    public function setRouter(RouterInterface $router): self
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get the router object.
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Find the correct controller and action from the router and execute it.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \Exception
     * @throws \Error
     */
    protected function findAndExecuteRoute(RequestInterface $request): ResponseInterface
    {
        $route_data = $this->router->find($request);

        if ($route_data === null) {
            throw new NotFoundException(
                $request->getUri()->getPath(),
                "No route found for URI"
            );
        }

        $this->container->setProperty("app.route-data", $route_data);

        /** @var string&class-string */
        $controller_path = $route_data->getController();

        try
        {
            $controller = $this->container
                ->get($controller_path);
        } catch (\Error | \Exception $e) {
            if (!class_exists($controller_path)) {
                throw new NotFoundException(
                    $request->getUri()->getPath(),
                    "The controller $controller_path does not exist"
                );
            }

            throw $e;
        }

        $action = $route_data->getAction();

        if ($action !== null) {
            try
            {
                return $controller->$action();
            } catch (\Error $e) {
                if (!method_exists($controller, $action)) {
                    throw new NotFoundException(
                        $request->getUri()->getPath(),
                        "Method $action does not exist in $controller_path"
                    );
                }

                throw $e;
            }
        }

        return $controller();
    }

    public function executeRequest(RequestInterface $request): ResponseInterface
    {
        $this->container->setProperty("app.request", $request);

        try
        {
            return $this->findAndExecuteRoute($request);
        } catch (NotFoundException $e) {
            return $this->container->get(NotFoundHandlerInterface::class)($e);
        } finally {
            $this->container->clearSingletons();
        }
    }

    /**
     * Get the dependency container.
     *
     * This method creates a new instance of the built-in Container if a container has not been
     * specified.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @template T
     * @param class-string<T> $name
     * @return T
     * @throws Exception
     * @throws ReflectionException
     */
    public function get(string $name)
    {
        return $this->container->get($name);
    }

    /**
     * Check if an entry exist in the dependency container.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->container->has($name);
    }

}
