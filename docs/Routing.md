---
#layout: page
title: Routing
permalink: /routing/

---
# Routing

Gaslawork uses dynamic routing. Here is an example using one single route:

```php
use Gaslawork\Routing\Router;
use Gaslawork\Routing\Route;

$routes = (new Router)
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));
```

In a perfect world this is the only route you will ever add.

The `Router` object will hold all the routes, and we `add` a `Route` object to it.

## Deep dive into the internals

I'm now going to explain how the routing internals work. You do not _need_ to know this, but I find it comforting knowing the internals of the framework I'm using, and maybe you do too:

- Gaslawork internally calls the `find()` of the routing (`Router`) object.
- `Router` iterates through all the routes added and calls `check()` on them. We have added the built-in `Route` in this example, but you are free to write your own route classes. I'll describe how to do that in a later section.
- `check()` returns a `RouteDataInterface` object on success (and `NULL` otherwise), and that "route data" object is returned back to Gaslawork. Side note: `Route::check()` actually returns `$this` since the `Route` class implements `RouteDataInterface`.
- The path to the controller (and in this case also the action) is fetched by calling `getController()` and `getAction()` on the "route data" object.
- Gaslawork creates an object of the controller and calls the action.

## Parameters (and special parameters)

In the example above we have put `/:controller/:action/:id` as the "target", and `\Controller\\` as the "namespace prefix". Parts that begins with `:` are considered as "parameters", and there are three special parameters:

- :directory
- :controller
- :action

These three parameters decide which controller and action should be executed. These two parameters has default values:

- :controller - `index`
- :action - `index`

The defaults are going to be used if a parameter is not set by the URL.

The namespace prefix is where Gaslawork should be looking for the controllers.

Here are some examples of URL's - using the route we defined in the example above:

| URL      | Controller (Class) | Action (Method) |
| -------- | ------------------ | --------------- |
| /        | \Controller\Index  | indexAction     |
| /foo     | \Controller\Foo    | indexAction     |
| /foo/bar | \Controller\Foo    | barAction       |

You may have noticed in the previous example that the action (method) names are suffixed with `Action`. This is because of security. For example, calling `index()` on the `Index` class is treated as calling the constructor.

### Fetching parameters

Parameters can be fetched by the controller or via the request object's `getParam()` method.

```php
class Index extends \Gaslawork\Controller {

    public function indexAction()
    {
        print $this->getParam("id");
        // Or:
        print \Gaslawork\Request::current()->getParam("id");
    }

}
```

### Default parameters

All parameters can have default values.

```php
$routes = (new Router)->add(
    (new Route("/:controller/:action/:id", "\Controller\\"))
        ->setDefaults([
            "controller" => "welcome",
            "action" => "hello",
        ])
);
```

The example above will have the default `controller` to be `welcome` and the default `action` to be `hello`. That means that the URI `/` will match to the controller `\Controller\Welcome\` and the action `helloAction` will be called.

You can set defaults for all parameters:

```php
$routes = (new Router)->add(
    new Route("/:controller/:action/:id", "\Controller\\"))
        ->setDefaults([
            "controller" => "welcome",
            "action" => "hello",
            "id" => "123",
        ])
);
```

This will make the `id` parameter to be `123` until the parameter is set in the URI.

Since the `setDefaults()` method overwrites all preexisting defaults, you can remove the default of `controller` and `action`. Be careful though, a route that is missing the controller is treated as invalid. A missing action will call the `__invoke()` method of the controller. This is of course perfectly valid and some developers prefer it.

You can also set a default for a parameter that is not in the "target":

```php
$routes = (new Router)->add(
    (new Route("/:action", "\Controller\\"))
        ->setDefaults([
            "controller" => "hello",
            "action" => "index",
        ])
);
```

In this example the controller will always be `\Controller\Hello`. You can use this idea to totally remove actions from your application (or a specific route), and only use controllers:

```php
$routes = (new Router)->add(
    (new Route("/:controller", "\Controller\\"))
        ->setDefaults([
            "controller" => "index",
        ])
);
```

This route does not have an action, and can never have, so the `__invoke()` method of the controller will always be called.

### White listing parameters

```php
$routes = (new Router)
    ->add(
        (new Route("/:controller/:action/:id", "\Controller\Special\\"))
            ->setWhitelist([
                "controller" => ["foo"],
            ])
    )
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));
```

We are creating a `Rotue` object and call `setWhitelist()` on it. We pass a dictionary to that method where the key is the name of the parameter. The value of the dictionary is an array of allowed values of the parameter. All other values than is specified here will not match the route.

The example above will call the controller `\Controller\Special\Foo` when the `controller` parameter is `foo`.

You can white list all parameters, not just the special onces.

### Black listing parameters

```php
$routes = (new Router)
    ->add(
        (new Route("/:controller/:action/:id", "\Controller\\"))
            ->setBlacklist([
                "controller" => ["foo"],
            ])
    )
    ->add(new Route("/:controller/:action/:id", "\Controller\Special\\"));
```

This time we call the `setBlacklist()` method on the `Route` object. We pass a dictionary to that method where the key is the name of the parameter and the value is an array of non-allowed parameter values.

In this example above the controller `\Controller\Bar` will be called if the `controller` parameter is `bar`, but the controller `\Controller\Special\Foo` will be called when the `controller` parameter is `foo`.

### Setting parameters as required

```php
$routes = (new Router)
    ->add(
        (new Route("/:controller/:action/:id", "\Controller\WithId\\"))
            ->setRequired(["id"])
    )
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));
```

The example above sets the `id` parameter to be required. So the URI `/Hello/World/123` will match the first route and the controller `\Controller\WithId\Hello\` will be used.

## Advanced routing

This is a fully working route:

`/hello/:action/world/:controller/foo/:id`

An example URL that matches this route is:

`/hello/abc/world/def/foo`

The controller will be `def` and the action will be `abc`.

## Why dynamic routing is used

Many other frameworks choose to map URLs to controllers but Gaslawork's routes are more performant when having a lot of URLs. PHP is executing the whole code every time, which means that the router's objects needs to be built from scratch each time, wasting resources on things that are never going to be used. After all, when visiting a page only one route is the correct one, and all the other ones are created but never used. Even when using cache the objects needs to be unserialized to be used, making it slower the more URLs you have.

## To write about

* :directory

