<?php

namespace Gaslawork;

class App {

    protected $router;
    protected $container;
    protected $_index_file;
    public $base_url = "/";
    public $index_file;
    public $action_prefix;
    public $action_suffix = "Action";

    protected static $instance;


    public static function instance(
        Routing\Router $router = null,
        \Psr\Container\ContainerInterface $container = null
    )
    {
        if (self::$instance === null)
        {
            return new self($router, $container);
        }

        $app = self::current();

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


    public static function current()
    {
        return self::$instance;
    }


    public function __construct(
        Routing\Router $router,
        \Psr\Container\ContainerInterface $container = null
    )
    {
        $this->router = $router;
        $this->container = $container;

        if (self::$instance !== null)
        {
            throw new Exception\InstanceAlreadyExistException("An instance of App already exists.");
        }

        self::$instance = $this;
    }


    public function setRouter(Routing\Router $router)
    {
        $this->router = $router;
        return $this;
    }


    public function getRouter()
    {
        return $this->router;
    }


    public function getIndexFile()
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


    protected function stripBaseUrlFromUri($uri)
    {
        $base_url = parse_url($this->base_url, PHP_URL_PATH);

        if ( ! empty($base_url))
        {
            if (strpos($uri, $base_url) === 0)
            {
                $uri = substr($uri, strlen($base_url));
            }
        }

        if (
            $this->getIndexFile()
            && strpos($uri, $this->getIndexFile()) === 0
        )
        {
            return substr($uri, strlen($this->getIndexFile()));
        }

        return $uri;
    }


    protected function getUri()
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
    protected function validControllerPath($controller)
    {
        return (bool)preg_match(
            "/^[a-zA-Z\\\_\x7f-\xff][a-zA-Z0-9\\\_\x7f-\xff]*$/",
            $controller
        );
    }


    protected function findAndExecuteRoute($uri, $http_method)
    {
        $route_data = $this->router->find($uri, $http_method);

        if ($route_data === null)
        {
            throw new \Gaslawork\Exception\NotFoundException(
                $uri,
                "No route found for URI"
            );
        }

        new \Gaslawork\Request(
            $route_data,
            $uri
        );

        $controller_path = $route_data->getController();

        if ( ! $this->validControllerPath($controller_path))
        {
            throw new \Gaslawork\Exception\NotFoundException(
                $uri,
                "The controller path $controller_path is invalid"
            );
        }

        if ( ! class_exists($controller_path))
        {
            throw new \Gaslawork\Exception\NotFoundException(
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
                throw new \Gaslawork\Exception\NotFoundException(
                    $uri,
                    "Method $action does not exist in $controller_path"
                );
            }

            return $controller->$action();
        }

        return $controller();
    }


    protected function handleNotFoundException(\Gaslawork\Exception\NotFoundException $e)
    {
        if ($this->has("notFoundHandler"))
        {
            $this->get("notFoundHandler")($e);
            return;
        }

        $this->defaultNotFoundHandler($e);
    }


    public function run()
    {
        try
        {
            return $this->findAndExecuteRoute(
                $this->getUri(),
                (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null)
            );
        }
        catch (\Gaslawork\Exception\NotFoundException $e)
        {
            $this->handleNotFoundException($e);
        }
    }


    protected function defaultNotFoundHandler(\Gaslawork\Exception\NotFoundException $e)
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


    public function setContainer(\Psr\Container\ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }


    public function getContainer()
    {
        if ($this->container === null)
        {
            $this->container = new Container;
        }

        return $this->container;
    }


    public function get($name)
    {
        if ($this->container === null)
        {
            throw new Exception\ContainerEntryNotFoundException("$name cannot be fetched since no container has been created.");
        }

        return $this->container->get($name);
    }


    public function has($name)
    {
        if ($this->container === null)
        {
            return false;
        }

        return $this->container->has($name);
    }

}
