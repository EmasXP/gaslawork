---
title: Gaslawork documentation
---

# Gaslawork documentation

This is the documentation for the Gaslawork PHP micro web framework. The goal of Gaslawork is to be as quick as possible. Each piece of the framework is built using a lot of thought and time (and anxiety), so each piece adds flexibility while loosing minimal performance. 

## Hello world

```php
use \Gaslawork\Routing\Routes;
use \Gaslawork\Routing\Route;

$routes = (new Routes)
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

* [Routes]({{ site.baseurl }}/routing): Routes an incoming request URL to a _Controller_ and _Action_.
* [Controller]({{ site.baseurl }}/controllers): A controller is a PHP class containing _Actions_.
* Action: A action is a method of the controller. Here is where you process the request and returns a response.

If you are coming from another PHP framework, you might notice that Gaslawork are not using request objects.