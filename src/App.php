<?php

namespace Gaslawork;

class App {

    protected $router;
    protected $dependencies = array();
    protected $loaded_dependencies = array();
    protected $_index_file;
    public $base_url = "/";
    public $index_file;
    public $action_prefix;
    public $action_suffix = "Action";

    protected static $instance;


    public static function instance(Routing\Routes $router = null)
    {
        if (self::$instance === null)
        {
            return new self($router);
        }

        if ($router !== null)
        {
            return self::current()
                ->setRouter($router);
        }

        return self::current();
    }


    public static function current()
    {
        return self::$instance;
    }


    public function __construct(Routing\Routes $router)
    {
        $this->router = $router;

        if (self::$instance !== null)
        {
            throw new Exception\InstanceAlreadyExistException("An instance of App already exists.");
        }

        self::$instance = $this;
    }


    public function setRouter(Routing\Routes $router)
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


    public function findAndExecuteRoute($uri, $http_method)
    {
        $route = $this->router->findRoute($uri, $http_method);

        if ($route === null)
        {
            return print "404";
        }

        new \Gaslawork\Request(
            $route,
            $uri
        );

        $controller_path = $route->getController();

        if ( ! $this->validControllerPath($controller_path))
        {
            return print "404";
        }

        if ( ! class_exists($controller_path))
        {
            return print "404";
        }

        $controller = new $controller_path;

        $action = $route->getAction();

        if ($action !== null)
        {
            $action = $this->action_prefix
                .$action
                .$this->action_suffix;

            if ( ! method_exists($controller, $action))
            {
                return print "404";
            }

            return $controller->$action();
        }

        return $controller();
    }


    public function run()
    {
        return $this->findAndExecuteRoute(
            $this->getUri(),
            (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null)
        );
    }


    public function set($name, callable $callable)
    {
        $this->dependencies[$name] = $callable;

        unset($this->loaded_dependencies[$name]);

        return $this;
    }


    public function get($name)
    {
        if (isset($this->loaded_dependencies[$name]))
        {
            return $this->loaded_dependencies[$name];
        }

        if ( ! isset($this->dependencies[$name]))
        {
            throw new Exception\NonExistingDependencyException("Dependency $name does not exist.");
        }

        return $this->loaded_dependencies[$name] = $this->dependencies[$name]();
    }


    public function has($name)
    {
        return isset($this->dependencies[$name]);
    }

}
