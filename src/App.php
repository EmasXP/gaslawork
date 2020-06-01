<?php

namespace Gaslawork;

use Gaslawork\Exception\ContainerEntryNotFoundException;
use Gaslawork\Exception\GaslaworkException;
use Gaslawork\Exception\InstanceAlreadyExistException;
use Gaslawork\Exception\NotFoundException;
use Gaslawork\Routing\RouterInterface;
use Psr\Container\ContainerInterface;


class App {

    /** @var RouterInterface */
    protected $router;

    /** @var ContainerInterface|null */
    protected $container;

    /** @var string|null */
    protected $_index_file;

    /** @var string */
    public $base_url = "/";

    /** @var string|null */
    public $index_file;

    /** @var string|null */
    public $action_prefix;

    /** @var string|null */
    public $action_suffix = "Action";

    /** @var static|null */
    protected static $instance;


    /**
     * Get the current instance, or create a new instance.
     *
     * @param RouterInterface|null $router
     * @param ContainerInterface|null $container
     * @return self|null
     */
    public static function instance(
        RouterInterface $router = null,
        ContainerInterface $container = null
    )
    {
        $app = self::current();

        if ($app === null)
        {
            if ($router === null)
            {
                throw new GaslaworkException("The router must be specified when creating new App.");
            }

            return new self($router, $container);
        }

        if ($router !== null)
        {
            $app->setRouter($router);
        }

        if ($container !== null)
        {
            $app->setContainer($container);
        }

        return $app;
    }


    /**
     * Get the current instance.
     *
     * @return self|null
     */
    public static function current()
    {
        return self::$instance;
    }


    public function __construct(
        RouterInterface $router,
        ContainerInterface $container = null
    )
    {
        $this->router = $router;
        $this->container = $container;

        if (self::$instance !== null)
        {
            throw new InstanceAlreadyExistException("An instance of App already exists.");
        }

        self::$instance = $this;
    }


    /**
     * Set the router object.
     *
     * @param RouterInterface $router
     * @return $this
     */
    public function setRouter(RouterInterface $router)
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
     * Get the index file.
     *
     * @return string|null
     */
    public function getIndexFile(): ?string
    {
        if ($this->index_file)
        {
            return $this->index_file;
        }

        if ($this->_index_file)
        {
            return $this->_index_file;
        }

        if ( ! isset($_SERVER["SCRIPT_NAME"]))
        {
            return null;
        }

        return $this->_index_file = basename($_SERVER["SCRIPT_NAME"]);
    }


    /**
     * Removes the base url from the passed URI.
     *
     * @param string $uri
     * @return string
     */
    protected function stripBaseUrlFromUri(string $uri): string
    {
        $base_url = parse_url($this->base_url, PHP_URL_PATH);

        if ( ! empty($base_url))
        {
            if (strpos($uri, $base_url) === 0)
            {
                $uri = substr($uri, strlen($base_url));
            }
        }

        $index_file = $this->getIndexFile();

        if (
            $index_file !== null
            && strpos($uri, $index_file) === 0
        )
        {
            return substr($uri, strlen($index_file));
        }

        return $uri;
    }


    /**
     * Get the current URI.
     *
     * @return string
     * @throws GaslaworkException
     */
    protected function getUri(): string
    {
        if (isset($_SERVER["PATH_INFO"]))
        {
            return $_SERVER["PATH_INFO"];
        }

        if (isset($_SERVER["REQUEST_URI"]))
        {
            $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

            if ($uri)
            {
                return $this->stripBaseUrlFromUri(rawurldecode($uri));
            }
        }

        if (isset($_SERVER["PHP_SELF"]))
        {
            return $this->stripBaseUrlFromUri($_SERVER["PHP_SELF"]);
        }

        throw new GaslaworkException("Unable to detect the URI.");
    }


    /**
     * Validates the name of the controller.
     *
     * It allows all the allowed characters of a class name, and also allows backslashes
     * to make it work with namespaces.
     *
     * This is needed to make sure the auto loader does not try to load illegal things,
     * for example "Controller\..\Hello". That is of course not an allowed name, but if
     * we are unlucky the auto loader might still try to open that file to see if the
     * class exists.
     *
     * @param string $controller
     * @return boolean
     */
    protected function validControllerPath($controller): bool
    {
        return (bool)preg_match(
            "/^[a-zA-Z\\\_\x7f-\xff][a-zA-Z0-9\\\_\x7f-\xff]*$/",
            $controller
        );
    }


    /**
     * Find the correct controller and action from the router and execute it.
     *
     * @param string $uri
     * @param string|null $http_method
     * @return mixed
     * @throws NotFoundException
     */
    protected function findAndExecuteRoute(
        string $uri,
        ?string $http_method
    )
    {
        $route_data = $this->router->find($uri, $http_method);

        if ($route_data === null)
        {
            throw new NotFoundException(
                $uri,
                "No route found for URI"
            );
        }

        new Request(
            $route_data,
            $uri
        );

        $controller_path = $route_data->getController();

        if ( ! $this->validControllerPath($controller_path))
        {
            throw new NotFoundException(
                $uri,
                "The controller path $controller_path is invalid"
            );
        }

        if ( ! class_exists($controller_path))
        {
            throw new NotFoundException(
                $uri,
                "The controller $controller_path does not exist"
            );
        }

        $controller = new $controller_path;

        $action = $route_data->getAction();

        if ($action !== null)
        {
            $action = $this->action_prefix
                .$action
                .$this->action_suffix;

            if ( ! method_exists($controller, $action))
            {
                throw new NotFoundException(
                    $uri,
                    "Method $action does not exist in $controller_path"
                );
            }

            return $controller->$action();
        }

        return $controller();
    }


    /**
     * Handle a 404 Not Found exception.
     *
     * @param NotFoundException $e
     * @return void
     * @throws ContainerEntryNotFoundException
     */
    protected function handleNotFoundException(NotFoundException $e)
    {
        if ($this->has("notFoundHandler"))
        {
            $this->get("notFoundHandler")($e);
            return;
        }

        $this->defaultNotFoundHandler($e);
    }


    /**
     * Run the application. Finds the correct controller and action from the router and executes it.
     * This method handles 404 Not Found exceptions.
     *
     * @return mixed
     * @throws ContainerEntryNotFoundException
     * @throws GaslaworkException
     */
    public function run()
    {
        try
        {
            return $this->findAndExecuteRoute(
                $this->getUri(),
                (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null)
            );
        }
        catch (Exception\NotFoundException $e)
        {
            $this->handleNotFoundException($e);
        }
    }


    /**
     * The default handler of 404 Not Found. This method is used if a custom one has not been
     * specified.
     *
     * @param NotFoundException $e
     * @return void
     */
    protected function defaultNotFoundHandler(NotFoundException $e)
    {
        Response::status(404);

        print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n";
        print "<title>404 Not Found</title>\n";
        print "<h1>Not Found</h1>\n";
        print "<p>The requested URL ";
        $uri = $e->getUri();
        if ( ! empty($uri))
        {
            print "<i>".htmlspecialchars($uri)."</i> ";
        }
        print "was not found on the server.</p>";
    }


    /**
     * Set the dependency container.
     *
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }


    /**
     * Get the dependency container.
     *
     * This method creates a new instance of the built-in Container if a container has not been
     * specified.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container === null)
        {
            $this->container = new Container;
        }

        return $this->container;
    }


    /**
     * Get an entry from the dependency container.
     *
     * @param string $name
     * @return mixed
     * @throws ContainerEntryNotFoundException
     */
    public function get($name)
    {
        if ($this->container === null)
        {
            throw new ContainerEntryNotFoundException("$name cannot be fetched since no container has been created.");
        }

        return $this->container->get($name);
    }


    /**
     * Check if an entry exist in the dependency container.
     *
     * @param string $name
     * @return bool
     */
    public function has($name): bool
    {
        if ($this->container === null)
        {
            return false;
        }

        return $this->container->has($name);
    }

}
