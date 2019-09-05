---
title: Gaslawork documentation
---

# Gaslawork documentation

This is the documentation for the Gaslawork PHP micro web framework. The goal of Gaslawork is to be as quick as possible. Each piece of the framework is built using a lot of thought and time (and anxiety), so each piece adds flexibility while loosing minimum performance.

## Hello world

```php
use \Gaslawork\Routing\Router;
use \Gaslawork\Routing\Route;

$routes = (new Router)
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));

$app = new \Gaslawork\App($routes);

$app->run();
```

```php
namespace Controller;

class Index extends \Gaslawork\Controller {

    public function indexAction()
    {
        print "Hello, world!";
    }

}
```

This is how you say "Hello, world!" in Gaslawork. These are the basic concepts:

* [Routing]({{ site.baseurl }}/routing): The router routes an incoming request URL to a _Controller_ and an _Action_.
* [Controllers]({{ site.baseurl }}/controllers): A controller is a PHP class containing _Actions_.
* Actions: An action is a method of the controller. Here is where you process the request and generate a response.

If you are coming from another PHP framework, you might notice that Gaslawork are not using response objects.

## Audience

_TODO_