---
title: Dependency container
permalink: /dependency-container/

---
# Dependency container

Gaslawork ships with its own dependency container, and you are forced to live with that one. The reason is that instances in the container needs to be freed once a request is made, so old instances are not there to ruin your day when the next request comes in.

## Setting up services

There are two ways of setting up the container, and you can mix them both. The first way is to pass the container to the App constructor:

```php
$app = \Gaslawork\App(...);

$app->getContainer()
    ->setSingleton(\PDO::class, function($c){
        $pdo = new \PDO("...");
        return $pdo;
    });
```

## Getting a service

You can get a service from a controller:

```php
class Index {
    
    public $db;
    
    public function __construct(
    	\PDO $db
    )
    {
        $this->db = $db;
    }

    public function indexAction()
    {
        $this->db;
    }

}
```

You can also fetch a service using the App object:

```php
$db = \Gaslawork\App::current()->get(\PDO::class);
```

Or straight from the Container object:

```php
$container = \Gaslawork\App::current()->getContainer(); // Or however you get hold of the Container
$db = $container->get(\PDO::class);
```

## Singleton versus new instances

The example above uses the `setSingleton()` method. To create a new instance, use `setCreate()` instead:

```php
$app = \Gaslawork\App(...);

$app->getContainer()
    ->setCreate(\PDO::class, function($c){
        $pdo = new \PDO("...");
        return $pdo;
    });
```

This will create a new instance of `PDO` every time it is requested from the container.

The singleton instances are going to be removed after the response is made.

## Properties

You can add properties into the container:

```php
$app->getContainer()
    ->setProperty("database_username", "myuser")
    ->setProperty("database_password", "mypass");
```

And to use the properties:

```php
$app->getContainer()
    ->setSingleton(\PDO::class, function($c){
        $username = $c->getProperty("database_username");
        $password = $c->getProperty("database_password");
        $pdo = new \PDO("...");
        return $pdo;
    });
```

## Nesting

The container can be used to inject dependencies to services.

```php
$app->getContainer()
    ->setProperty("database_username", "myuser")
    ->setProperty("database_password", "mypass")
    ->setSingleton(\PDO::class, function($c){
        $username = $c->getProperty("database_username");
        $password = $c->getProperty("database_password");
        $pdo = new \PDO("...");
        return $pdo;
    });
```

## has() and hasProperty()

You can check whether a service or property exists in the container:

```php
\Gaslawork\App::current()->has(\PDO::class); // bool
\Gaslawork\App::current()->hasProperty("database_username"); // bool
```

## Pre-defined services

* `\Psr\Http\Message\RequestInterface::class` - The current request.
* `\Gaslawork\Container` - The container.
* `\Psr\Container\ContainerInterface` - Also the container, but by interface instead of the concrete type.
* `\Gaslawork\Routing\RouteDataInterface` - The "route data" for the current route. Read more about that in the [Routing]({{ site.baseurl }}/routing) document.
* `\Gaslawork\App` - The `App` instance.
* `\Gaslawork\NotFoundHandlerInterface` - Used to render the 404 page. Read more about that in the [404 Not Found]({{ site.baseurl }}//404-not-found/) document.

That means that you can for example receive the container as a dependency injection to your controller:

```php
use \Gaslawork\Container;

class Index {
    
    public $container;
    
    public function __construct(
    	Container $container
    )
    {
        $this->container = $container;
    }

    public function indexAction()
    {
        $db = $this->container
            ->get(\PDO::class);
    }

}
```

## Special properties

* `app.container` - The `Container` instance
* `app.instance` - The `App` instance
* `app.request` - Only exist during a request
* `app.route-data` - Only exist during a request

## Auto wire

The container can build instances that are not defined.

```php
$app->getContainer()
    ->setSingleton(\PDO::class, function($c){
        return new \PDO("...");
    });
```

```php
class Example {
    
    public function __construct(PDO $db)
    {
        // ...
    }
    
}
```

```php
\Gaslawork\App::current()->get(Example::class); // An instance of Example with the PDO passed
```
