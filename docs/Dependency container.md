---
title: Dependency container
permalink: /dependency-container/

---
# Dependency container

You do not have to use a dependency container if you do not want to. You can skip this section if you don't want to use a dependency container in Gaslawork (and if you are not curious about how it works).

Gaslawork ships with its own dependency container, and you are free to change to another one as long as it implements the [PSR-11](https://www.php-fig.org/psr/psr-11/) interfaces. This section is going to explain how to use Gaslawork's build-in dependency container, but I'm also going to describe how to change to another one further down.

## Setting up services

There are two ways of setting up the container, and you can mix them both. The first way is to pass the container to the App constructor:

```php
$container = (new \Gaslawork\Container)
    ->add("db", function($c){
        $pdo = new \PDO("...");
        return $pdo;
    });

$app = new \Gaslawork\App($routes, $container);
```

And the other way is to get the Container object from the App object:

```php
$app = new \Gaslawork\App($routes);

$app->getContainer()
    ->add("db", function($c){
        $pdo = new \PDO("...");
        return $pdo;
    });
```

If you would have passed a Container to the constructor, that object would have been returned by `getContainer()`, but in the example above `getContainer()` creates a new Container object since none has been created yet.

## Getting a service

You can get a service from a controller:

```php
class Index extends \Gaslawork\Controller {

    public function indexAction()
    {
        $db = $this->get("db");
        // Or:
        $db = $this->db;
    }

}
```

You can also fetch a service using the App object:

```php
$db = \Gaslawork\App::current()->get("db");
```

Or straight from the Container object:

```php
$container = \Gaslawork\App::current()->getContainer(); // Or however you get hold of the Container
$db = $container->get("db");
```

## Properties

You can add properties into the container:

```php
$container = (new \Gaslawork\Container)
    ->set("database_username", "myuser")
    ->set("database_password", "mypass");
```

## Nesting

The container can be used to inject dependencies to services.

```php
$container = (new \Gaslawork\Container)
    ->set("database_username", "myuser")
    ->set("database_password", "mypass")
    ->set("db", function($c){
        return new \PDO(
            "...",
            $c->get("database_username"),
            $c->get("database_password")
        );
    });
```

## Has

You can check whether a service exists in the container:

```php
class Index extends \Gaslawork\Controller {

    public function indexAction()
    {
        $this->has("db"); // bool
        // Or:
        \Gaslawork\App::current()->has("db"); // bool
    }

}
```

## Factory

Gaslawor's container is singleton, meaning that the same instance of the service is going to be returned by `get()` every time. If you want a new instance to be created on each `get()`, then we will use `factory()` instead of `set()`:

```php
$container = (new \Gaslawork\Container)
    ->factory("mailer", function($c){
       return new MyCoolMailer;
    });

$container->get("mailer");
```

## Using another container library

It is as simple of passing a different Container object to the App constructor. Here is how to use [Pimple](https://pimple.symfony.com/):

```php
$pimple = new \Pimple\Container;

$pimple["database_username"] = "myuser";
$pimple["database_password"] = "mypass";
$pimple["db"] = function($c){
    return new \PDO(
        "...",
        $c["database_username"],
        $c["database_password"]
    )
};

/*
Pimple is not PSR-11 compliant by default, but ships with a wrapper class:
*/
$app = new \Gaslawork\App(
    $routes,
    new \Pimple\Psr11\Container($pimple)
);
```

You are going to be able to access the services the same way as with Gaslawork's Container: via the controller or via the App instance.